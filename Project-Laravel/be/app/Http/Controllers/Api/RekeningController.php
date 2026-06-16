<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use App\Models\VerifikasiRekening;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RekeningController extends Controller
{
    use ApiResponse;

    /**
     * d. GET /api/v1/komunitas/rekening/riwayat  (JWT: komunitas)
     */
    public function riwayat(Request $request): JsonResponse
    {
        $komunitas = $request->user()->komunitas;

        $riwayat = VerifikasiRekening::where('id_komunitas', $komunitas->id_komunitas)
            ->orderByDesc('created_at')
            ->get(['id_verif', 'nama_bank_baru', 'nomor_rekening_baru', 'created_at', 'status', 'alasan_penolakan']);

        if ($riwayat->isEmpty()) {
            return $this->error('ERR-REK-01', 'Belum ada riwayat perubahan rekening', 404);
        }

        return $this->success([
            'rekening_aktif' => [
                'nama_bank'              => $komunitas->nama_bank,
                'nomor_rekening'         => $komunitas->nomor_rekening,
                'foto_buku_rekening_url' => $komunitas->foto_buku_rekening_url,
            ],
            'riwayat_perubahan' => $riwayat,
        ]);
    }

    /**
     * e. POST /api/v1/komunitas/rekening/ajukan-perubahan  (JWT: komunitas)
     */
    public function ajukanPerubahan(Request $request): JsonResponse
    {
        $komunitas = $request->user()->komunitas;

        // ERR-REK-02: data tidak lengkap
        $validator = validator($request->all(), [
            'nama_bank_baru'         => ['required', 'string', 'max:100'],
            'nomor_rekening_baru'    => ['required', 'string', 'max:50'],
            'foto_buku_rekening_url' => ['required', 'string', 'max:255'],
            'alasan_perubahan'       => ['required', 'string'],
        ]);
        if ($validator->fails()) {
            return $this->error('ERR-REK-02', 'Data rekening tidak lengkap', 400);
        }
        $data = $validator->validated();

        // ERR-REK-03: masih ada pengajuan menunggu (unique index DB juga mencegah)
        $adaPending = VerifikasiRekening::where('id_komunitas', $komunitas->id_komunitas)
            ->where('status', VerifikasiRekening::STATUS_MENUNGGU)
            ->exists();
        if ($adaPending) {
            return $this->error('ERR-REK-03', 'Masih ada pengajuan perubahan rekening yang menunggu verifikasi', 409);
        }

        $verif = DB::transaction(function () use ($komunitas, $data) {
            $verif = VerifikasiRekening::create([
                'id_komunitas'           => $komunitas->id_komunitas,
                'nama_bank_baru'         => $data['nama_bank_baru'],
                'nomor_rekening_baru'    => $data['nomor_rekening_baru'],
                'foto_buku_rekening_url' => $data['foto_buku_rekening_url'],
                'alasan_perubahan'       => $data['alasan_perubahan'],
                'status'                 => VerifikasiRekening::STATUS_MENUNGGU,
                'created_at'             => now(),
            ]);

            // Notifikasi ke superadmin (mirror trg_notif_superadmin_rekening)
            Notifikasi::create([
                'id_penerima_komunitas' => $komunitas->id_komunitas,
                'judul'                 => 'Pengajuan perubahan rekening',
                'pesan'                 => 'Pengajuan perubahan rekening menunggu verifikasi superadmin.',
                'tipe'                  => 'verifikasi',
                'related_verifikasi_id' => $verif->id_verif,
                'is_read'               => false,
                'created_at'            => now(),
                'expires_at'            => Carbon::now()->addDays(Notifikasi::ttlDaysFor('verifikasi')),
            ]);

            return $verif;
        });

        return $this->success([
            'id_verif' => $verif->id_verif,
            'message'  => 'Perubahan rekening diajukan, menunggu verifikasi superadmin',
        ]);
    }
}
