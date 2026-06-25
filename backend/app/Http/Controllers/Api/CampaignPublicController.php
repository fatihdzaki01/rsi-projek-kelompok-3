<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Donasi;
use App\Models\UpdateCampaign;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CampaignPublicController extends Controller
{
    use ApiResponse;

    public function show($id): JsonResponse
    {
        $cacheKey = "campaign:public:{$id}";

        $data = Cache::remember($cacheKey, 30, function () use ($id) {
            $campaign = Campaign::with(['komunitas', 'kategori', 'wilayah'])
                ->where('id_campaign', $id)
                ->whereIn('status', ['aktif', 'selesai'])
                ->first();

            if (!$campaign) {
                return null;
            }

            $timelineDana = $this->getTimelineDana($campaign->id_campaign);
            $updatePost = $this->getUpdatePost($campaign->id_campaign);

            $hariTersisa = $campaign->tanggal_selesai
                ? max(0, now()->diffInDays(\Carbon\Carbon::parse($campaign->tanggal_selesai), false))
                : 0;

            return [
                'campaign' => [
                    'id_campaign' => $campaign->id_campaign,
                    'judul' => $campaign->judul,
                    'nama_lembaga' => $campaign->komunitas->nama_lembaga ?? null,
                    'deskripsi' => $campaign->deskripsi,
                    'foto_campaign_url' => $campaign->foto_campaign_url,
                    'target_dana' => $campaign->target_dana,
                    'dana_terkumpul' => $campaign->dana_terkumpul,
                    'jumlah_donatur' => $this->getDonaturCount($campaign->id_campaign),
                    'nama_kategori' => $campaign->kategori->nama_kategori ?? null,
                    'hari_tersisa' => $hariTersisa,
                    'tanggal_mulai' => $campaign->tanggal_mulai,
                    'tanggal_selesai' => $campaign->tanggal_selesai,
                    'status' => $campaign->status,
                    'tombol_donasi_aktif' => $campaign->status === 'aktif',
                    'id_kategori' => $campaign->id_kategori,
                    'kode_wilayah' => $campaign->kode_wilayah,
                    'tipe_distribusi' => $campaign->tipe_distribusi,
                    'target_audiens' => $campaign->target_audiens,
                    'total_penerima_manfaat' => $campaign->total_penerima_manfaat,
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
            ];
        });

        if ($data === null) {
            return response()->json([
                'status' => 'error',
                'data' => null,
                'message' => 'Halaman tidak tersedia',
                'errors' => ['code' => 'ERR-MON-01'],
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => $data,
            'message' => 'Monitoring campaign berhasil ditampilkan',
            'errors' => null,
        ], 200);
    }

    public function donors(Request $request, int $id): JsonResponse
    {
        $campaign = Campaign::where('id_campaign', $id)
            ->whereIn('status', ['aktif', 'selesai'])
            ->first();

        if (!$campaign) {
            return $this->error('ERR-DONOR-01', 'Campaign tidak ditemukan', 404);
        }

        $perPage = min((int) $request->query('per_page', 15), 100);
        $page = max(1, (int) $request->query('page', 1));

        $query = Donasi::where('id_campaign', $id)
            ->where('status_pembayaran', Donasi::STATUS_BERHASIL)
            ->orderByDesc('created_at');

        $total = $query->count();
        $items = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

        $donors = $items->map(function ($donasi) {
            return [
                'id_donasi'   => $donasi->id_donasi,
                'nama'        => $donasi->is_anonim ? 'Anonim' : ($donasi->user->username ?? 'User'),
                'nominal'     => $donasi->nominal,
                'tanggal'     => $donasi->created_at,
                'is_anonim'   => $donasi->is_anonim,
            ];
        });

        return $this->success([
            'data'  => $donors,
            'meta'  => [
                'current_page' => $page,
                'per_page'     => $perPage,
                'total'        => $total,
                'last_page'    => (int) ceil($total / max($perPage, 1)),
            ],
        ]);
    }

    public function complete(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $komunitas = $user->komunitas;

        $campaign = Campaign::where('id_campaign', $id)->first();

        if (!$campaign) {
            return $this->error('ERR-CAMP-05', 'Campaign tidak ditemukan', 404);
        }

        if (!$komunitas || $campaign->id_komunitas !== $komunitas->id_komunitas) {
            return $this->error('ERR-CAMP-06', 'Akses ditolak', 403);
        }

        if ($campaign->status !== Campaign::STATUS_AKTIF) {
            return $this->error('ERR-CAMP-07', 'Hanya campaign aktif yang dapat diselesaikan', 400);
        }

        $campaign->update(['status' => Campaign::STATUS_SELESAI]);

        return $this->success([
            'id_campaign' => $campaign->id_campaign,
            'status'      => $campaign->status,
            'message'     => 'Campaign berhasil diselesaikan',
        ]);
    }

    private function getDonaturCount(int $campaignId): int
    {
        return Donasi::where('id_campaign', $campaignId)
            ->where('status_pembayaran', Donasi::STATUS_BERHASIL)
            ->distinct('id_user')
            ->count('id_user');
    }

    private function getTimelineDana($campaignId): array
    {
        return Donasi::query()
            ->where('id_campaign', $campaignId)
            ->where('status_pembayaran', Donasi::STATUS_BERHASIL)
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
        return UpdateCampaign::query()
            ->where('id_campaign', $campaignId)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($update) {
                return [
                    'id_update'  => $update->id_update,
                    'judul'      => $update->judul_update,
                    'deskripsi'  => $update->konten,
                    'foto_url'   => $update->foto->first()->foto_url ?? null,
                    'created_at' => $update->created_at,
                ];
            })
            ->toArray();
    }
}
