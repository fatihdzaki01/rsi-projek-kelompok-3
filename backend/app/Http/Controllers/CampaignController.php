<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Services\CampaignService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function __construct(private CampaignService $service) {}

    public function donors(Request $request, int $id): JsonResponse
    {
        $userId      = auth()->user()->id_user;
        $idKomunitas = $this->service->getKomunitasIdByUser($userId);

        $campaign = $this->service->getCampaignOwner($id);

        if (!$campaign) {
            return ApiResponse::error('Campaign tidak ditemukan', 404, 'ERR-CAMPAIGN-02');
        }

        if ($campaign->id_komunitas !== $idKomunitas) {
            return ApiResponse::error('Anda bukan pemilik campaign', 403, 'ERR-CAMPAIGN-01');
        }

        $page    = max(1, (int) $request->get('page', 1));
        $perPage = min((int) $request->get('per_page', 15), 100);
        $result  = $this->service->getDonors($id, $page, $perPage);

        return ApiResponse::success([
            'data'       => $result['data'],
            'pagination' => [
                'page'     => $page,
                'per_page' => $perPage,
                'total'    => $result['total'],
            ],
        ], 'Daftar donatur berhasil ditampilkan');
    }
}
