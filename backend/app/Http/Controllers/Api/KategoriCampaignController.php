<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\KategoriCampaign;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class KategoriCampaignController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = KategoriCampaign::where('is_active', true)
            ->leftJoin('campaign', 'campaign.id_kategori', '=', 'kategori_campaign.id_kategori')
            ->select('kategori_campaign.id_kategori', 'kategori_campaign.nama_kategori', 'kategori_campaign.deskripsi',
                DB::raw('COUNT(campaign.id_campaign) as total_campaign'))
            ->groupBy('kategori_campaign.id_kategori', 'kategori_campaign.nama_kategori', 'kategori_campaign.deskripsi')
            ->orderBy('kategori_campaign.nama_kategori')
            ->get();

        return ApiResponse::success($categories);
    }
}
