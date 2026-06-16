<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Komunitas;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KomunitasProfilController extends Controller
{
    use ApiResponse;

    /**
     * b. GET /api/v1/komunitas/{id}/profil-publik  (Public)
     */
    public function profilPublik(int $id): JsonResponse
    {
        $komunitas = Komunitas::where('id_komunitas', $id)
            ->where('status', Komunitas::STATUS_AKTIF)
            ->first();

        if (!$komunitas) {
            return $this->error('ERR-PROF-01', 'Komunitas tidak tersedia', 404);
        }

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

        return $this->success([
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
     * c. GET /api/v1/communities/profile  (Auth: komunitas)
     */
    public function profilSaya(Request $request): JsonResponse
    {
        $komunitas = $request->user()->komunitas;
        if (!$komunitas) {
            return $this->error('ERR-PROF-01', 'Data komunitas tidak ditemukan', 404);
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

        return $this->success([
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
            return $this->error('ERR-EDIT-KOM-01', 'Mohon lengkapi profil yang wajib diisi', 400);
        }

        // ERR-EDIT-KOM-02: perubahan nama organisasi harus via superadmin
        if ($request->has('nama_lembaga')) {
            return $this->error('ERR-EDIT-KOM-02', 'Perubahan nama organisasi harus diajukan ke superadmin', 403);
        }

        $data = $request->validate([
            'deskripsi'        => ['sometimes', 'nullable', 'string'],
            'nomor_kontak'     => ['sometimes', 'nullable', 'string', 'max:20'],
            'link_medsos'      => ['sometimes', 'nullable', 'string', 'max:255'],
            'foto_lembaga_url' => ['sometimes', 'nullable', 'string', 'max:255'],
        ]);

        if (empty($data)) {
            return $this->error('ERR-EDIT-KOM-01', 'Mohon lengkapi profil yang wajib diisi', 400);
        }

        $komunitas->fill($data)->save();

        return $this->success([
            'message'        => 'Profil berhasil diperbarui',
            'updated_fields' => array_keys($data),
        ]);
    }
}
