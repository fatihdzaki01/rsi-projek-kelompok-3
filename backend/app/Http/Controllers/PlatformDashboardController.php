<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\PlatformStatisticsRequest;
use App\Services\PlatformDashboardService;
use Illuminate\Http\JsonResponse;

class PlatformDashboardController extends Controller
{
    public function __construct(private PlatformDashboardService $service) {}

    public function statistics(PlatformStatisticsRequest $request): JsonResponse
    {
        $data = $this->service->getStatistics(
            $request->tanggal_mulai,
            $request->tanggal_selesai,
        );

        return ApiResponse::success($data, 'Statistik platform berhasil ditampilkan');
    }
}
