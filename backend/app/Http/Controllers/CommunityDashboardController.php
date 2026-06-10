<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\DonationChartRequest;
use App\Services\CampaignService;
use App\Services\CommunityDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommunityDashboardController extends Controller
{
    public function __construct(
        private CommunityDashboardService $service,
        private CampaignService $campaignService,
    ) {}

    private function getKomunitasId(): ?int
    {
        return $this->campaignService->getKomunitasIdByUser(auth()->user()->id_user);
    }

    public function summary(Request $request): JsonResponse
    {
        $idKomunitas = $this->getKomunitasId();

        if (!$idKomunitas) {
            return ApiResponse::error('Akses hanya untuk komunitas', 403, 'ERR-KOM-01');
        }

        $data = $this->service->getSummary($idKomunitas);

        return ApiResponse::success($data, 'Ringkasan dashboard berhasil ditampilkan');
    }

    public function donationChart(DonationChartRequest $request): JsonResponse
    {
        $idKomunitas = $this->getKomunitasId();

        if (!$idKomunitas) {
            return ApiResponse::error('Akses hanya untuk komunitas', 403, 'ERR-KOM-01');
        }

        $data = $this->service->getDonationChart(
            $idKomunitas,
            $request->campaign_id ? (int) $request->campaign_id : null,
            $request->tanggal_mulai,
            $request->tanggal_selesai,
        );

        return ApiResponse::success($data, 'Grafik donasi berhasil ditampilkan');
    }

    public function campaignFinance(Request $request, int $id): JsonResponse
    {
        $idKomunitas = $this->getKomunitasId();

        if (!$idKomunitas) {
            return ApiResponse::error('Akses hanya untuk komunitas', 403, 'ERR-KOM-01');
        }

        $campaign = $this->campaignService->getCampaignOwner($id);

        if (!$campaign) {
            return ApiResponse::error('Campaign tidak ditemukan', 404, 'ERR-CAMPAIGN-02');
        }

        if ($campaign->id_komunitas !== $idKomunitas) {
            return ApiResponse::error('Anda bukan pemilik campaign', 403, 'ERR-CAMPAIGN-01');
        }

        $data = $this->service->getCampaignFinance($id);

        return ApiResponse::success($data, 'Detail keuangan campaign berhasil ditampilkan');
    }
}
