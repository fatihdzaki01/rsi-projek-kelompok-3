<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class CommunityDashboardService
{
    public function getSummary(int $idKomunitas): array
    {
        $stats = DB::selectOne(
            'SELECT total_campaign_aktif, total_campaign_selesai, total_donatur_unik,
                    total_dana_terkumpul, total_saldo_tersisa
             FROM v_community_dashboard_stats
             WHERE id_komunitas = ?',
            [$idKomunitas]
        );

        $pencairanPending = DB::select(
            'SELECT p.id_pencairan, p.id_campaign, c.judul AS judul_campaign,
                    p.nominal_diajukan, p.tanggal_pengajuan
             FROM pencairan_dana p
             JOIN campaign c ON c.id_campaign = p.id_campaign
             WHERE p.id_komunitas = ? AND p.status = ?
             ORDER BY p.tanggal_pengajuan DESC',
            [$idKomunitas, 'menunggu_review']
        );

        return [
            'total_campaign_aktif'     => $stats->total_campaign_aktif ?? 0,
            'total_campaign_selesai'   => $stats->total_campaign_selesai ?? 0,
            'total_donatur_unik'       => $stats->total_donatur_unik ?? 0,
            'total_dana_terkumpul'     => $stats->total_dana_terkumpul ?? 0,
            'total_saldo_tersisa'      => $stats->total_saldo_tersisa ?? 0,
            'daftar_pencairan_pending' => $pencairanPending,
        ];
    }

    public function getDonationChart(int $idKomunitas, ?int $campaignId, ?string $startDate, ?string $endDate): array
    {
        $rows = DB::select(
            'SELECT * FROM fn_get_donation_chart(?, ?, ?, ?)',
            [
                $idKomunitas,
                $campaignId,
                $startDate ?? date('Y-m-d', strtotime('-30 days')),
                $endDate   ?? date('Y-m-d'),
            ]
        );

        $total = array_sum(array_column($rows, 'total_donasi'));

        return [
            'total_donasi_periode' => $total,
            'data_donasi_harian'   => $rows,
        ];
    }

    public function getCampaignFinance(int $idCampaign): array
    {
        $financial = DB::selectOne(
            'SELECT dana_terkumpul, saldo_tersedia, jumlah_donatur
             FROM v_campaign_financial_summary
             WHERE id_campaign = ?',
            [$idCampaign]
        );

        $withdrawal = DB::selectOne(
            'SELECT sisa_kesempatan, total_dicairkan, saldo_terkunci
             FROM v_campaign_withdrawal_summary
             WHERE id_campaign = ?',
            [$idCampaign]
        );

        $potongan = DB::selectOne(
            'SELECT nominal_potongan, persentase_potongan
             FROM potongan_platform
             WHERE id_campaign = ?
             ORDER BY dipotong_pada DESC LIMIT 1',
            [$idCampaign]
        );

        $riwayatPencairan = DB::select(
            'SELECT p.id_pencairan, p.urutan_ke, p.nominal_diajukan, p.nominal_disetujui,
                    p.status, p.tanggal_pengajuan, p.tanggal_keputusan
             FROM pencairan_dana p
             WHERE p.id_campaign = ?
             ORDER BY p.urutan_ke ASC',
            [$idCampaign]
        );

        return [
            'total_dana_terkumpul'      => $financial->dana_terkumpul ?? 0,
            'total_dana_dicairkan'      => $withdrawal->total_dicairkan ?? 0,
            'saldo_tersisa'             => $financial->saldo_tersedia ?? 0,
            'jumlah_donatur'            => $financial->jumlah_donatur ?? 0,
            'sisa_kesempatan_pencairan' => $withdrawal->sisa_kesempatan ?? 0,
            'potongan_platform'         => $potongan ? [
                'nominal_potongan'    => $potongan->nominal_potongan,
                'persentase_potongan' => $potongan->persentase_potongan,
            ] : null,
            'riwayat_pencairan'         => $riwayatPencairan,
        ];
    }
}
