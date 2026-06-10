<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class CampaignService
{
    public function getKomunitasIdByUser(int $userId): ?int
    {
        $row = DB::selectOne(
            'SELECT id_komunitas FROM komunitas WHERE id_user = ? AND deleted_at IS NULL',
            [$userId]
        );
        return $row?->id_komunitas;
    }

    public function getCampaignOwner(int $idCampaign): ?object
    {
        return DB::selectOne(
            'SELECT id_campaign, id_komunitas FROM campaign WHERE id_campaign = ? AND deleted_at IS NULL',
            [$idCampaign]
        ) ?: null;
    }

    public function getDonors(int $idCampaign, int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;

        $data = DB::select(
            'SELECT id_donasi, nama_tampil, nominal, created_at
             FROM v_campaign_donor_monitoring
             WHERE id_campaign = ?
             ORDER BY created_at DESC
             LIMIT ? OFFSET ?',
            [$idCampaign, $perPage, $offset]
        );

        $total = DB::selectOne(
            'SELECT COUNT(*) as total FROM v_campaign_donor_monitoring WHERE id_campaign = ?',
            [$idCampaign]
        )->total;

        return ['data' => $data, 'total' => (int) $total];
    }
}
