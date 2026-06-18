<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\FollowKomunitas;
use App\Models\Komunitas;
use App\Traits\HasImageUpload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class KomunitasProfilController extends Controller
{
    use HasImageUpload;
    /**
     * b. GET /api/v1/komunitas/{id}/profil-publik  (Public)
     */
    public function profilPublik(Request $request, int $id): JsonResponse
    {
        $cacheKey = "community:profile:{$id}";

        $cached = Cache::remember($cacheKey, 60, function () use ($id) {
            $komunitas = Komunitas::where('id_komunitas', $id)
                ->where('status', Komunitas::STATUS_AKTIF)
                ->first();

            if (!$komunitas) {
                return null;
            }

            $campaignAktif = Campaign::where('id_komunitas', $id)
                ->where('status', Campaign::STATUS_AKTIF)
                ->get(['id_campaign', 'judul', 'target_dana', 'dana_terkumpul', 'tanggal_selesai']);

            $campaignSelesaiCount = Campaign::where('id_komunitas', $id)
                ->where('status', Campaign::STATUS_SELESAI)
                ->count();

            $campaignSelesaiList = Campaign::where('id_komunitas', $id)
                ->where('status', Campaign::STATUS_SELESAI)
                ->get(['id_campaign', 'judul', 'target_dana', 'dana_terkumpul']);

            $totalFollower = $komunitas->followers()
                ->where('is_active', true)
                ->count();

            $totalDanaDiterima = (int) Campaign::where('id_komunitas', $id)->sum('dana_terkumpul');

            return [
                'id_komunitas'            => $komunitas->id_komunitas,
                'nama_lembaga'            => $komunitas->nama_lembaga,
                'deskripsi'               => $komunitas->deskripsi,
                'alamat_detail'           => $komunitas->alamat_detail,
                'nomor_kontak'            => $komunitas->nomor_kontak,
                'link_medsos'             => $komunitas->link_medsos,
                'foto_lembaga_url'        => $komunitas->foto_lembaga_url,
                'kode_wilayah'            => $komunitas->kode_wilayah,
                'status'                  => $komunitas->status,
                'created_at'              => $komunitas->created_at,
                'total_follower'          => $totalFollower,
                'total_dana_diterima'     => $totalDanaDiterima,
                'total_campaign_aktif'    => count($campaignAktif),
                'total_campaign_selesai'  => $campaignSelesaiCount,
                'daftar_campaign_aktif'   => $campaignAktif,
                'daftar_campaign_selesai' => $campaignSelesaiList,
            ];
        });

        if ($cached === null) {
            return ApiResponse::error('Komunitas tidak tersedia', null, 404);
        }

        // Compute is_following per-request (cannot be cached)
        $isFollowing = false;
        $user = $request->user();
        if ($user) {
            $follow = FollowKomunitas::where('id_user', $user->id_user ?? $user->id)
                ->where('id_komunitas', $id)
                ->where('is_active', true)
                ->first();
            $isFollowing = $follow !== null;
        }
        $cached['is_following'] = $isFollowing;

        return ApiResponse::success($cached);
    }

    /**
     * c. GET /api/v1/communities/profile  (Auth: komunitas)
     */
    public function profilSaya(Request $request): JsonResponse
    {
        $komunitas = $request->user()->komunitas;
        if (!$komunitas) {
            return ApiResponse::error('Data komunitas tidak ditemukan', null, 404);
        }

        $id = $komunitas->id_komunitas;

        $campaignAktif = Campaign::where('id_komunitas', $id)
            ->where('status', Campaign::STATUS_AKTIF)
            ->get(['id_campaign', 'judul', 'target_dana', 'dana_terkumpul', 'tanggal_selesai']);

        $campaignSelesai = Campaign::where('id_komunitas', $id)
            ->where('status', Campaign::STATUS_SELESAI)
            ->count();

        $totalFollower = $komunitas->followers()
            ->where('is_active', true)
            ->count();

        $totalDanaDiterima = (int) Campaign::where('id_komunitas', $id)->sum('dana_terkumpul');

        return ApiResponse::success([
            'id_komunitas'           => $komunitas->id_komunitas,
            'nama_lembaga'           => $komunitas->nama_lembaga,
            'deskripsi'              => $komunitas->deskripsi,
            'alamat_detail'          => $komunitas->alamat_detail,
            'nomor_kontak'           => $komunitas->nomor_kontak,
            'link_medsos'            => $komunitas->link_medsos,
            'foto_lembaga_url'       => $komunitas->foto_lembaga_url,
            'kode_wilayah'           => $komunitas->kode_wilayah,
            'status'                 => $komunitas->status,
            'created_at'             => $komunitas->created_at,
            'total_follower'         => $totalFollower,
            'total_dana_diterima'    => $totalDanaDiterima,
            'total_campaign_aktif'   => count($campaignAktif),
            'total_campaign_selesai' => $campaignSelesai,
            'daftar_campaign_aktif'  => $campaignAktif,
        ]);
    }

    /**
     * d. PATCH /api/v1/communities/profile  (JWT: komunitas)
     * Hanya field tertentu yang boleh diubah. Nama lembaga TIDAK bisa diubah di sini.
     */
    public function updateProfil(Request $request): JsonResponse
    {
        $komunitas = $request->user()->komunitas;
        if (!$komunitas) {
            return ApiResponse::error('Hanya akun komunitas yang dapat mengakses fitur ini', ['error_code' => 'ERR-EDIT-KOM-01'], 403);
        }

        // ERR-EDIT-KOM-02: perubahan nama organisasi harus via superadmin
        if ($request->has('nama_lembaga')) {
            return ApiResponse::error('Perubahan nama organisasi harus diajukan ke superadmin', ['error_code' => 'ERR-EDIT-KOM-02'], 403);
        }

        $data = $request->validate([
            'deskripsi'        => ['sometimes', 'nullable', 'string'],
            'alamat_detail'    => ['sometimes', 'nullable', 'string', 'max:255'],
            'nomor_kontak'     => ['sometimes', 'nullable', 'string', 'max:20'],
            'link_medsos'      => ['sometimes', 'nullable', 'string', 'max:255'],
            'foto_lembaga'     => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'foto_lembaga_url' => ['sometimes', 'nullable', 'string', 'max:255'],
        ]);

        $fotoLembagaUrl = $komunitas->foto_lembaga_url;
        if ($request->hasFile('foto_lembaga')) {
            $fotoLembagaUrl = $this->uploadImage($request->file('foto_lembaga'), 'community-photos');
        }

        $updateData = array_merge(
            array_intersect_key($data, array_flip(['deskripsi', 'alamat_detail', 'nomor_kontak', 'link_medsos'])),
            ['foto_lembaga_url' => $fotoLembagaUrl]
        );

        if (empty($updateData)) {
            return ApiResponse::error('Mohon lengkapi profil yang wajib diisi', ['error_code' => 'ERR-EDIT-KOM-01'], 400);
        }

        $komunitas->fill($updateData)->save();

        return ApiResponse::success([
            'message'        => 'Profil berhasil diperbarui',
            'updated_fields' => array_keys($data),
        ]);
    }
}
