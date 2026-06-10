<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class PlatformDashboardService
{
    public function getStatistics(?string $startDate, ?string $endDate): array
    {
        $summary = DB::selectOne('SELECT * FROM v_platform_summary');

        $dateWhere = $startDate && $endDate
            ? 'WHERE tanggal BETWEEN ? AND ?'
            : '';
        $params = $startDate && $endDate ? [$startDate, $endDate] : [];

        $donations   = DB::select("SELECT tanggal, total_transaksi, total_donasi FROM v_daily_donations $dateWhere ORDER BY tanggal ASC", $params);
        $users       = DB::select("SELECT tanggal, total_user_baru FROM v_daily_users $dateWhere ORDER BY tanggal ASC", $params);
        $communities = DB::select("SELECT tanggal, total_komunitas_baru FROM v_daily_communities $dateWhere ORDER BY tanggal ASC", $params);

        return [
            'total_donasi'          => $summary->total_nominal_donasi ?? 0,
            'jumlah_user_baru'      => array_sum(array_column($users, 'total_user_baru')),
            'jumlah_komunitas_baru' => array_sum(array_column($communities, 'total_komunitas_baru')),
            'data_grafik_donasi'    => $donations,
            'data_grafik_user'      => $users,
            'data_grafik_komunitas' => $communities,
        ];
    }
}
