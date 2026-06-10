<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\StoreDonationRequest;
use App\Services\DonationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DonationController extends Controller
{
    public function __construct(private DonationService $service) {}

    public function history(Request $request): JsonResponse
    {
        $userId  = auth()->user()->id_user;
        $page    = max(1, (int) $request->get('page', 1));
        $perPage = min((int) $request->get('per_page', 15), 100);

        $result = $this->service->getHistory($userId, $page, $perPage);

        if (empty($result['data'])) {
            return ApiResponse::error('Belum ada riwayat donasi', 404, 'ERR-PROF-DONASI-01');
        }

        return ApiResponse::success([
            'data'       => $result['data'],
            'pagination' => [
                'page'     => $page,
                'per_page' => $perPage,
                'total'    => $result['total'],
            ],
        ], 'Riwayat donasi berhasil ditampilkan');
    }

    public function store(StoreDonationRequest $request): JsonResponse
    {
        $userId  = auth()->user()->id_user;
        $payload = $request->validated();

        // Cek campaign aktif
        $campaign = \Illuminate\Support\Facades\DB::selectOne(
            'SELECT id_campaign, status FROM campaign WHERE id_campaign = ? AND deleted_at IS NULL',
            [$payload['id_campaign']]
        );

        if (!$campaign) {
            return ApiResponse::error('Campaign tidak ditemukan', 404, 'ERR-DON-02');
        }

        if ($campaign->status !== 'aktif') {
            return ApiResponse::error('Campaign tidak menerima donasi', 403, 'ERR-DON-02');
        }

        if ($payload['nominal'] < 5000) {
            return ApiResponse::error('Nominal donasi tidak valid', 400, 'ERR-DON-01');
        }

        $donasi = $this->service->createDonation($userId, $payload);

        return ApiResponse::success([
            'id_donasi'              => $donasi->id_donasi,
            'nomor_transaksi'        => $donasi->nomor_transaksi,
            'status_pembayaran'      => $donasi->status_pembayaran,
            'batas_waktu_pembayaran' => now()->addHours(24)->toIso8601String(),
            'metode_pembayaran'      => $donasi->metode_pembayaran,
        ], 'Donasi berhasil dibuat', 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $userId = auth()->user()->id_user;
        $donasi = $this->service->getById($userId, $id);

        if (!$donasi) {
            // Cek apakah donasi ada tapi bukan milik user ini
            if ($this->service->existsDonation($id)) {
                return ApiResponse::error('Akses ditolak', 403, 'ERR-DON-06');
            }
            return ApiResponse::error('Transaksi tidak ditemukan', 404, 'ERR-DON-07');
        }

        return ApiResponse::success($donasi, 'Detail transaksi berhasil ditampilkan');
    }
}
