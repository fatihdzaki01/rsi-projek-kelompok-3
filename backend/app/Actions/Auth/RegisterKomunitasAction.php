<?php

namespace App\Actions\Auth;

use App\Models\DokumenKomunitas;
use App\Models\Komunitas;
use App\Models\User;
use App\Traits\HasImageUpload;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterKomunitasAction
{
    use HasImageUpload;

    public function execute(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'username'      => $data['nama_lembaga'],
                'nama_lengkap'  => $data['nama_pic'] ?? null,
                'email'         => $data['email'],
                'password_hash' => Hash::make($data['password']),
                'role'          => User::ROLE_KOMUNITAS,
                'is_active'     => true,
                'is_verified'   => false,
            ]);

            $komunitas = Komunitas::create([
                'id_user'          => $user->id_user,
                'id_jenis_lembaga' => $data['id_jenis_lembaga'],
                'nama_lembaga'     => $data['nama_lembaga'],
                'deskripsi'        => $data['deskripsi'] ?? null,
                'kode_wilayah'     => $data['kode_wilayah'],
                'alamat_detail'    => $data['alamat_detail'],
                'nomor_kontak'     => $data['nomor_kontak'],
                'link_medsos'      => $data['link_medsos'] ?? null,
                'foto_lembaga_url' => $data['foto_lembaga_url'] ?? null,
                'status'           => Komunitas::STATUS_MENUNGGU,
            ]);

            if (!empty($data['dokumen'])) {
                foreach ($data['dokumen'] as $idJenisDok => $file) {
                    $url = $this->uploadDocument($file, 'dokumen-komunitas');

                    DokumenKomunitas::create([
                        'id_komunitas'      => $komunitas->id_komunitas,
                        'id_jenis_dok'      => (int) $idJenisDok,
                        'file_url'          => $url,
                        'status_verifikasi' => DokumenKomunitas::STATUS_MENUNGGU,
                        'uploaded_at'       => now(),
                    ]);
                }
            }

            return $user;
        });
    }
}
