<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\StoreDonationRequest;
use App\Http\Requests\UpdatePaymentStatusRequest;
use App\Services\DonationService;
use App\Models\Notifikasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DonationController extends Controller
{
    public function __construct(private DonationService $service) {}

    public function history(Request $request): JsonResponse
    {
        $userId  = auth()->user()->id_user;
        $page    = max(1, (int) $request->get('page', 1));
        $perPage = min((int) $request->get('per_page', 15), 100);
        $search  = $request->get('search', '');
        $status  = $request->get('status', '');

        $result = $this->service->getHistory($userId, $page, $perPage, $search, $status);

        return ApiResponse::success([
            'data'       => $result['data'] ?? [],
            'pagination' => [
                'page'     => $page,
                'per_page' => $perPage,
                'total'    => $result['total'] ?? 0,
            ],
        ], 'Riwayat donasi berhasil ditampilkan');
    }

    public function store(StoreDonationRequest $request): JsonResponse
    {
        $userId  = auth()->user()->id_user;
        $payload = $request->validated();

        // Cek campaign aktif
        $campaign = DB::selectOne(
            'SELECT id_campaign, status FROM campaign WHERE id_campaign = ? AND deleted_at IS NULL',
            [$payload['id_campaign']]
        );

        if (!$campaign) {
            return ApiResponse::error('Campaign tidak ditemukan', 'ERR-DON-02', 404);
        }

        if ($campaign->status !== 'aktif') {
            return ApiResponse::error('Campaign tidak menerima donasi', 'ERR-DON-02', 403);
        }

        if ($payload['nominal'] < 5000) {
            return ApiResponse::error('Nominal donasi tidak valid', 'ERR-DON-01', 400);
        }

        try {
            $donasi = $this->service->createDonation($userId, $payload);
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            if (str_contains($msg, 'Campaign tidak aktif')) {
                return ApiResponse::error('Campaign tidak menerima donasi', 'ERR-DON-02', 403);
            }
            if (str_contains($msg, 'User tidak aktif')) {
                return ApiResponse::error('Akun tidak aktif', 'ERR-DON-09', 403);
            }
            if (str_contains($msg, 'check constraint') || str_contains($msg, 'chk_nama_tampil')) {
                return ApiResponse::error('Nama tampil wajib diisi jika tidak anonim', 'ERR-DON-01', 400);
            }
            return ApiResponse::error('Gagal membuat donasi', 'ERR-DON-10', 500);
        }

        $paymentToken = (string) \Illuminate\Support\Str::uuid();

        // Notifikasi pending ke donatur
        $campaignTitle = DB::table('campaign')->where('id_campaign', $payload['id_campaign'])->value('judul') ?? 'campaign ini';
        Notifikasi::kirim([
            'id_penerima_user' => $userId,
            'judul' => 'Donasi menunggu pembayaran',
            'pesan' => 'Donasi sebesar Rp ' . number_format($payload['nominal'], 0, ',', '.') . ' untuk "' . $campaignTitle . '" sedang menunggu pembayaran.',
            'tipe' => 'donasi_pending',
            'related_donasi_id' => $donasi->id_donasi,
        ]);

        return ApiResponse::success([
            'id_donasi'              => $donasi->id_donasi,
            'nomor_transaksi'        => $donasi->nomor_transaksi,
            'status_pembayaran'      => $donasi->status_pembayaran,
            'batas_waktu_pembayaran' => now()->addHours(24)->toIso8601String(),
            'metode_pembayaran'      => $donasi->metode_pembayaran,
            'payment_token'          => $paymentToken,
            'mock_payment_url'       => url('/api/v1/donations/' . $donasi->id_donasi . '/mock-pay?token=' . $paymentToken),
        ], 'Donasi berhasil dibuat', 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $userId = auth()->user()->id_user;
        $donasi = $this->service->getById($userId, $id);

        if (!$donasi) {
            if ($this->service->existsDonation($id)) {
                return ApiResponse::error('Akses ditolak', 'ERR-DON-06', 403);
            }
            return ApiResponse::error('Transaksi tidak ditemukan', 'ERR-DON-07', 404);
        }

        return ApiResponse::success($donasi, 'Detail transaksi berhasil ditampilkan');
    }

    public function receipt(Request $request, int $id): JsonResponse
    {
        $receipt = $this->service->getReceipt($id);

        if (!$receipt) {
            return ApiResponse::error('Bukti donasi tidak ditemukan', 'ERR-DON-05', 404);
        }

        if ($receipt->id_user !== auth()->user()->id_user) {
            return ApiResponse::error('Anda tidak memiliki akses ke bukti donasi ini', 'ERR-DON-04', 403);
        }

        if (!$receipt->bukti_pdf_url && $receipt->status_pembayaran === 'berhasil') {
            $url = $this->service->generateReceiptPdf($id);
            $receipt->bukti_pdf_url = $url ?? $receipt->bukti_pdf_url;
        }

        return ApiResponse::success([
            'id_donasi'          => $receipt->id_donasi,
            'nomor_transaksi'    => $receipt->nomor_transaksi,
            'tanggal_transaksi'  => $receipt->tanggal_transaksi,
            'judul_campaign'     => $receipt->judul_campaign,
            'nominal'            => $receipt->nominal,
            'metode_pembayaran'  => $receipt->metode_pembayaran,
            'nama_tampil'        => $receipt->nama_tampil,
            'bukti_pdf_url'      => $receipt->bukti_pdf_url,
        ], 'Bukti donasi berhasil ditemukan');
    }

    public function receiptPdf(Request $request, int $id): \Illuminate\Http\Response
    {
        $receipt = $this->service->getReceipt($id);

        if (!$receipt) {
            return ApiResponse::error('Bukti donasi tidak ditemukan', 'ERR-DON-05', 404);
        }

        if ($receipt->id_user !== auth()->user()->id_user) {
            return ApiResponse::error('Anda tidak memiliki akses ke bukti donasi ini', 'ERR-DON-04', 403);
        }

        $pdf = $this->service->downloadReceiptPdf($id);

        if (!$pdf) {
            return ApiResponse::error('Gagal membuat bukti donasi', 'ERR-DON-08', 500);
        }

        return $pdf->download('bukti-donasi-' . $id . '.pdf');
    }

    public function updatePaymentStatus(UpdatePaymentStatusRequest $request, int $id): JsonResponse
    {
        $donasi = DB::selectOne(
            'SELECT id_donasi, status_pembayaran FROM donasi WHERE id_donasi = ?',
            [$id]
        );

        if (!$donasi) {
            return ApiResponse::error('Data donasi tidak ditemukan', 'ERR-DON-03', 404);
        }

        if ($donasi->status_pembayaran !== 'pending') {
            if ($donasi->status_pembayaran === $request->status_pembayaran) {
                return ApiResponse::success([
                    'id_donasi' => $donasi->id_donasi,
                    'status_pembayaran' => $donasi->status_pembayaran,
                    'tanggal_verifikasi' => now()->toIso8601String(),
                ], 'Status pembayaran sudah sesuai');
            }
            return ApiResponse::error('Status donasi sudah final dan tidak dapat diubah', 'ERR-DON-09', 400);
        }

        try {
            $result = $this->service->updatePaymentStatus($id, $request->status_pembayaran);
        } catch (\Exception $e) {
            return ApiResponse::error('Gagal memperbarui status pembayaran', 'ERR-DON-10', 500);
        }

        // Notifikasi ke donatur + komunitas + superadmin
        $donasiData = DB::selectOne(
            'SELECT d.id_donasi, d.id_campaign, d.id_user, d.nominal, d.metode_pembayaran, d.is_anonim,
                    c.id_komunitas, c.judul as campaign_judul, u.nama_lengkap as donatur_nama
             FROM donasi d
             JOIN campaign c ON c.id_campaign = d.id_campaign
             LEFT JOIN users u ON u.id_user = d.id_user
             WHERE d.id_donasi = ?',
            [$id]
        );

        if ($donasiData) {
            $donorName = $donasiData->is_anonim ? 'Anonim' : ($donasiData->donatur_nama ?? 'Donatur');
            $campaignJudul = $donasiData->campaign_judul ?? 'campaign';
            $nominal = number_format($donasiData->nominal, 0, ',', '.');

            if ($request->status_pembayaran === 'berhasil') {
                // Notifikasi ke donatur
                Notifikasi::kirim([
                    'id_penerima_user' => $donasiData->id_user,
                    'judul' => 'Donasi berhasil',
                    'pesan' => 'Donasi sebesar Rp ' . $nominal . ' untuk "' . $campaignJudul . '" berhasil. Terima kasih!',
                    'tipe' => 'donasi_berhasil',
                    'related_donasi_id' => $donasiData->id_donasi,
                    'related_campaign_id' => $donasiData->id_campaign,
                ]);

                // Notifikasi ke komunitas penerima
                if ($donasiData->id_komunitas) {
                    Notifikasi::kirim([
                        'id_penerima_komunitas' => $donasiData->id_komunitas,
                        'judul' => 'Donasi masuk',
                        'pesan' => 'Donasi dari ' . $donorName . ' sebesar Rp ' . $nominal . ' dengan metode ' . ($donasiData->metode_pembayaran ?? '-') . ' untuk "' . $campaignJudul . '".',
                        'tipe' => 'donasi_masuk',
                        'related_donasi_id' => $donasiData->id_donasi,
                        'related_campaign_id' => $donasiData->id_campaign,
                    ]);
                }

                // Notifikasi ke superadmin
                $superadminIds = \App\Models\User::where('role', 'SUPERADMIN')->pluck('id_user');
                foreach ($superadminIds as $saId) {
                    Notifikasi::kirim([
                        'id_penerima_user' => $saId,
                        'judul' => 'Donasi baru',
                        'pesan' => 'Donasi Rp ' . $nominal . ' dari ' . $donorName . ' untuk "' . $campaignJudul . '".',
                        'tipe' => 'donasi_masuk',
                        'related_donasi_id' => $donasiData->id_donasi,
                        'related_campaign_id' => $donasiData->id_campaign,
                    ]);
                }
            } else {
                // Notifikasi gagal ke donatur
                Notifikasi::kirim([
                    'id_penerima_user' => $donasiData->id_user,
                    'judul' => 'Donasi gagal',
                    'pesan' => 'Donasi untuk "' . $campaignJudul . '" gagal diproses.',
                    'tipe' => 'donasi_gagal',
                    'related_donasi_id' => $donasiData->id_donasi,
                ]);
            }
        }

        return ApiResponse::success($result, 'Status pembayaran berhasil diperbarui');
    }
}
