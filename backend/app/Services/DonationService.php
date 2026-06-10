<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class DonationService
{
    public function getHistory(int $userId, int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;

        $data = DB::select(
            'SELECT id_donasi, judul_campaign, nominal, status_pembayaran, created_at
             FROM v_user_donation_history
             WHERE id_user = ?
             ORDER BY created_at DESC
             LIMIT ? OFFSET ?',
            [$userId, $perPage, $offset]
        );

        $total = DB::selectOne(
            'SELECT COUNT(*) as total FROM v_user_donation_history WHERE id_user = ?',
            [$userId]
        )->total;

        return ['data' => $data, 'total' => (int) $total];
    }

    public function createDonation(int $userId, array $payload): object
    {
        DB::statement('CALL sp_create_donation(?, ?, ?, ?, ?, ?)', [
            $userId,
            $payload['id_campaign'],
            $payload['nominal'],
            $payload['metode_pembayaran'],
            $payload['is_anonim'] ?? true,
            $payload['nama_tampil'] ?? null,
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
            'SELECT id_donasi, nomor_transaksi, judul_campaign, nominal, metode_pembayaran, status_pembayaran, created_at
             FROM v_user_donation_history
             WHERE id_user = ? AND id_donasi = ?',
            [$userId, $idDonasi]
        );

        return $row ?: null;
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
        return DB::selectOne(
            'SELECT id_donasi, nomor_transaksi, tanggal_transaksi, judul_campaign,
                    nominal, metode_pembayaran, nama_tampil, bukti_pdf_url, id_user
             FROM v_donation_receipt
             WHERE id_donasi = ?',
            [$idDonasi]
        ) ?: null;
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
}
