<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DonationService
{
    public function getHistory(int $userId, int $page, int $perPage, string $search = '', string $status = ''): array
    {
        $offset = ($page - 1) * $perPage;

        $where  = 'WHERE id_user = ?';
        $params = [$userId];

        if ($search !== '') {
            $where .= ' AND (judul_campaign LIKE ? OR nomor_transaksi LIKE ?)';
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        if ($status !== '' && $status !== 'semua') {
            $where .= ' AND status_pembayaran = ?';
            $params[] = $status;
        }

        $data = DB::select(
            "SELECT id_donasi, judul_campaign, nominal, status_pembayaran, created_at
             FROM v_user_donation_history
             {$where}
             ORDER BY created_at DESC
             LIMIT ? OFFSET ?",
            [...$params, $perPage, $offset]
        );

        $total = DB::selectOne(
            "SELECT COUNT(*) as total FROM v_user_donation_history {$where}",
            $params
        )->total;

        return ['data' => $data, 'total' => (int) $total];
    }

    public function createDonation(int $userId, array $payload): object
    {
        DB::statement('CALL sp_create_donation(?, ?, ?, ?, ?, ?, ?)', [
            $userId,
            $payload['id_campaign'],
            $payload['nominal'],
            $payload['metode_pembayaran'],
            $payload['is_anonim'] ?? true,
            $payload['nama_tampil'] ?? null,
            $payload['pesan'] ?? null,
        ]);

        // Ambil donasi terbaru milik user ini
        $donasi = DB::selectOne(
            'SELECT id_donasi, nomor_transaksi, status_pembayaran, metode_pembayaran, created_at
             FROM v_user_donation_history
             WHERE id_user = ?
             ORDER BY created_at DESC
             LIMIT 1',
            [$userId]
        );

        return $donasi;
    }

    public function getById(int $userId, int $idDonasi): ?object
    {
        $row = DB::selectOne(
            'SELECT id_donasi, nomor_transaksi, id_campaign, judul_campaign, nominal,
                    metode_pembayaran, status_pembayaran, is_anonim, created_at
             FROM v_user_donation_history
             WHERE id_user = ? AND id_donasi = ?',
            [$userId, $idDonasi]
        );

        if (!$row) return null;

        $extra = DB::selectOne(
            'SELECT nama_tampil, pesan, bukti_pdf_url FROM donasi WHERE id_donasi = ?',
            [$idDonasi]
        );

        $row->nama_tampil = $extra->nama_tampil ?? null;
        $row->pesan = $extra->pesan ?? null;
        $row->bukti_pdf_url = $extra->bukti_pdf_url ?? null;

        return $row;
    }

    public function existsDonation(int $idDonasi): bool
    {
        $row = DB::selectOne(
            'SELECT id_donasi FROM v_user_donation_history WHERE id_donasi = ?',
            [$idDonasi]
        );
        return $row !== null;
    }

    public function getReceipt(int $idDonasi): ?object
    {
        $row = DB::selectOne(
            'SELECT d.id_donasi, r.nomor_transaksi, r.tanggal_transaksi, r.judul_campaign,
                    r.nominal, r.metode_pembayaran, r.nama_tampil, r.bukti_pdf_url, r.id_user,
                    d.status_pembayaran
             FROM v_donation_receipt r
             JOIN donasi d ON d.id_donasi = r.id_donasi
             WHERE r.id_donasi = ?',
            [$idDonasi]
        );
        return $row ?: null;
    }

    public function updatePaymentStatus(int $idDonasi, string $status): object
    {
        if ($status === 'berhasil') {
            DB::statement('CALL sp_confirm_payment(?)', [$idDonasi]);

            // Cek apakah campaign perlu platform fee
            $campaign = DB::selectOne(
                'SELECT c.id_campaign, c.status, c.potongan_platform_sudah_dipotong
                 FROM donasi d
                 JOIN campaign c ON c.id_campaign = d.id_campaign
                 WHERE d.id_donasi = ?',
                [$idDonasi]
            );

            if (
                $campaign &&
                $campaign->status === 'selesai' &&
                !$campaign->potongan_platform_sudah_dipotong
            ) {
                DB::statement('CALL sp_apply_platform_fee(?, ?)', [
                    $campaign->id_campaign,
                    auth()->user()->id_user,
                ]);
            }

            $this->generateReceiptPdf($idDonasi);

            $campaignInfo = DB::selectOne(
                'SELECT id_komunitas FROM donasi d
                 JOIN campaign c ON c.id_campaign = d.id_campaign
                 WHERE d.id_donasi = ?',
                [$idDonasi]
            );
            if ($campaignInfo?->id_komunitas) {
                Cache::forget("community:profile:{$campaignInfo->id_komunitas}");
            }
        } else {
            DB::statement('CALL sp_fail_payment(?)', [$idDonasi]);
        }

        $donasi = DB::selectOne(
            'SELECT id_donasi, status_pembayaran FROM donasi WHERE id_donasi = ?',
            [$idDonasi]
        );

        return (object) [
            'id_donasi'          => $donasi->id_donasi,
            'status_pembayaran'  => $donasi->status_pembayaran,
            'tanggal_verifikasi' => now()->toIso8601String(),
        ];
    }

    public function generateReceiptPdf(int $idDonasi): ?string
    {
        $receipt = DB::selectOne(
            'SELECT id_donasi, nomor_transaksi, tanggal_transaksi, judul_campaign,
                    nominal, metode_pembayaran, nama_tampil, bukti_pdf_url
             FROM v_donation_receipt
             WHERE id_donasi = ?',
            [$idDonasi]
        );

        if (!$receipt || $receipt->bukti_pdf_url) {
            return $receipt?->bukti_pdf_url;
        }

        $pdf = Pdf::loadView('pdf.donation-receipt', [
            'nomor_transaksi'   => $receipt->nomor_transaksi,
            'tanggal_transaksi' => $receipt->tanggal_transaksi,
            'judul_campaign'    => $receipt->judul_campaign,
            'nominal'           => $receipt->nominal,
            'metode_pembayaran' => $receipt->metode_pembayaran,
            'nama_tampil'       => $receipt->nama_tampil,
        ]);

        $filename = 'receipts/donation-' . $idDonasi . '.pdf';
        $path = 'public/' . $filename;

        Storage::put($path, $pdf->output());

        $url = Storage::url($filename);

        DB::update(
            'UPDATE donasi SET bukti_pdf_url = ? WHERE id_donasi = ?',
            [$url, $idDonasi]
        );

        return $url;
    }

    public function downloadReceiptPdf(int $idDonasi): ?\Barryvdh\DomPDF\PDF
    {
        $receipt = DB::selectOne(
            'SELECT id_donasi, nomor_transaksi, tanggal_transaksi, judul_campaign,
                    nominal, metode_pembayaran, nama_tampil
             FROM v_donation_receipt
             WHERE id_donasi = ?',
            [$idDonasi]
        );

        if (!$receipt) return null;

        return Pdf::loadView('pdf.donation-receipt', [
            'nomor_transaksi'   => $receipt->nomor_transaksi,
            'tanggal_transaksi' => $receipt->tanggal_transaksi,
            'judul_campaign'    => $receipt->judul_campaign,
            'nominal'           => $receipt->nominal,
            'metode_pembayaran' => $receipt->metode_pembayaran,
            'nama_tampil'       => $receipt->nama_tampil,
        ]);
    }
}
