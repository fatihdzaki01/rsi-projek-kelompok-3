<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Donasi;
use App\Models\CampaignUpdate;
use Illuminate\Http\JsonResponse;

class CampaignPublicController extends Controller
{
    public function show($id): JsonResponse
    {
        $campaign = Campaign::with(['komunitas', 'kategori', 'wilayah'])
            ->where('id_campaign', $id)
            ->whereIn('status', ['aktif', 'selesai'])
            ->first();

        if (!$campaign) {
            return response()->json([
                'status' => 'error',
                'data' => null,
                'message' => 'Halaman tidak tersedia',
                'errors' => [
                    'code' => 'ERR-MON-01'
                ]
            ], 403);
        }

        $timelineDana = $this->getTimelineDana($campaign->id_campaign);
        $updatePost = $this->getUpdatePost($campaign->id_campaign);

        return response()->json([
            'status' => 'success',
            'data' => [
                'campaign' => [
                    'id_campaign' => $campaign->id_campaign,
                    'judul' => $campaign->judul,
                    'deskripsi' => $campaign->deskripsi,
                    'foto_campaign_url' => $campaign->foto_campaign_url,
                    'target_dana' => $campaign->target_dana,
                    'dana_terkumpul' => $campaign->dana_terkumpul,
                    'tanggal_mulai' => $campaign->tanggal_mulai,
                    'tanggal_selesai' => $campaign->tanggal_selesai,
                    'status' => $campaign->status,
                    'tombol_donasi_aktif' => $campaign->status === 'aktif',
                ],
                'komunitas' => [
                    'id_komunitas' => $campaign->komunitas->id_komunitas ?? null,
                    'nama_lembaga' => $campaign->komunitas->nama_lembaga ?? null,
                    'deskripsi' => $campaign->komunitas->deskripsi ?? null,
                    'foto_lembaga_url' => $campaign->komunitas->foto_lembaga_url ?? null,
                    'nomor_kontak' => $campaign->komunitas->nomor_kontak ?? null,
                    'link_medsos' => $campaign->komunitas->link_medsos ?? null,
                ],
                'timeline_dana' => $timelineDana,
                'update_post' => $updatePost,
            ],
            'message' => 'Monitoring campaign berhasil ditampilkan',
            'errors' => null
        ], 200);
    }

    private function getTimelineDana($campaignId): array
    {
        return Donasi::query()
            ->where('id_campaign', $campaignId)
            ->where('status_pembayaran', 'berhasil')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($donasi) {
                return [
                    'tipe' => 'dana_masuk',
                    'nominal' => $donasi->nominal,
                    'nama_donatur' => $donasi->is_anonim ? 'Anonim' : ($donasi->user->username ?? 'User'),
                    'tanggal' => $donasi->created_at,
                ];
            })
            ->toArray();
    }

    private function getUpdatePost($campaignId): array
    {
        return CampaignUpdate::query()
            ->where('id_campaign', $campaignId)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($update) {
                return [
                    'id_update' => $update->id_update,
                    'judul' => $update->judul,
                    'deskripsi' => $update->deskripsi,
                    'foto_url' => $update->foto_url,
                    'created_at' => $update->created_at,
                ];
            })
            ->toArray();
    }
}