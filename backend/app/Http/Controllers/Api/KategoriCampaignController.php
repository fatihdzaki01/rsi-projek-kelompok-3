<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KategoriCampaign;
use Illuminate\Http\JsonResponse;

class KategoriCampaignController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $categories = KategoriCampaign::where('is_active', true)
            ->orderBy('nama_kategori')
            ->get(['id_kategori', 'nama_kategori', 'deskripsi']);

        return $this->success($categories);
    }
}
