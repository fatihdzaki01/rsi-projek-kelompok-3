<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Komunitas;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use ApiResponse;

    /**
     * a. POST /api/v1/auth/register/komunitas  (Public)
     * Mendaftarkan user role KOMUNITAS sekaligus membuat baris komunitas.
     */
    public function registerKomunitas(Request $request): JsonResponse
    {
        // ERR-KOM-REG-02: Berkas / field persyaratan belum lengkap
        try {
            $data = $request->validate([
                'email'                  => ['required', 'email', 'max:150'],
                'password'               => ['required', 'string', 'min:8'],
                'password_confirmation'  => ['required', 'same:password'],
                'username'               => ['required', 'string', 'max:50'],
                'nama_penanggung_jawab'  => ['required', 'string', 'max:150'],
                'nama_lembaga'           => ['required', 'string', 'max:150'],
                'id_jenis_lembaga'       => ['required', 'integer', 'exists:jenis_lembaga,id_jenis'],
                'deskripsi'              => ['nullable', 'string'],
                'kode_wilayah'           => ['required', 'string', 'exists:wilayah,kode'],
                'rt'                     => ['nullable', 'string', 'max:5'],
                'rw'                     => ['nullable', 'string', 'max:5'],
                'kode_pos'               => ['nullable', 'string', 'max:10'],
                'alamat_detail'          => ['nullable', 'string'],
                'nomor_kontak'           => ['nullable', 'string', 'max:20'],
                'link_medsos'            => ['nullable', 'string', 'max:255'],
                'foto_lembaga_url'       => ['required', 'string', 'max:255'],
                'nama_bank'              => ['required', 'string', 'max:100'],
                'nomor_rekening'         => ['required', 'string', 'max:50'],
                'foto_buku_rekening_url' => ['required', 'string', 'max:255'],
            ]);
        } catch (ValidationException $e) {
            return $this->error('ERR-KOM-REG-02', 'Berkas persyaratan belum lengkap', 400);
        }

        // ERR-KOM-REG-01: Email sudah digunakan (users.email UNIQUE)
        if (User::where('email', $data['email'])->orWhere('username', $data['username'])->exists()) {
            return $this->error('ERR-KOM-REG-01', 'Email sudah digunakan', 400);
        }

        // Transaksi: buat user + komunitas dalam 1 unit kerja
        $idKomunitas = DB::transaction(function () use ($data) {
            $user = User::create([
                'username'      => $data['username'],
                'email'         => $data['email'],
                'password_hash' => Hash::make($data['password']),
                'role'          => User::ROLE_KOMUNITAS,
                'is_active'     => true,
                'is_verified'   => false,
                'nama_lengkap'  => $data['nama_penanggung_jawab'],
                'kode_wilayah'  => $data['kode_wilayah'],
            ]);

            $komunitas = Komunitas::create([
                'id_user'                => $user->id_user,
                'id_jenis_lembaga'       => $data['id_jenis_lembaga'],
                'nama_lembaga'           => $data['nama_lembaga'],
                'deskripsi'              => $data['deskripsi'] ?? null,
                'kode_wilayah'           => $data['kode_wilayah'],
                'rt'                     => $data['rt'] ?? null,
                'rw'                     => $data['rw'] ?? null,
                'kode_pos'               => $data['kode_pos'] ?? null,
                'alamat_detail'          => $data['alamat_detail'] ?? null,
                'nomor_kontak'           => $data['nomor_kontak'] ?? null,
                'link_medsos'            => $data['link_medsos'] ?? null,
                'foto_lembaga_url'       => $data['foto_lembaga_url'],
                'nama_bank'              => $data['nama_bank'],
                'nomor_rekening'         => $data['nomor_rekening'],
                'foto_buku_rekening_url' => $data['foto_buku_rekening_url'],
                'status'                 => Komunitas::STATUS_MENUNGGU,
            ]);

            return $komunitas->id_komunitas;
        });

        return $this->success([
            'id_komunitas' => $idKomunitas,
            'message'      => 'Pendaftaran berhasil, menunggu verifikasi superadmin',
        ], 201);
    }
}
