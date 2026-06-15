<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JenisDokumenSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'id_jenis_dok' => 1,
                'nama_dokumen' => 'KTP',
                'deskripsi' => 'Kartu Tanda Penduduk pengurus',
                'wajib_untuk_jenis_lembaga' => null,
                'is_opsional' => false,
            ],
            [
                'id_jenis_dok' => 2,
                'nama_dokumen' => 'Akta Pendirian',
                'deskripsi' => 'Akta notaris pendirian lembaga',
                'wajib_untuk_jenis_lembaga' => 'Yayasan',
                'is_opsional' => false,
            ],
            [
                'id_jenis_dok' => 3,
                'nama_dokumen' => 'SK Kemenkumham',
                'deskripsi' => 'Surat Keterangan Kemenkumham',
                'wajib_untuk_jenis_lembaga' => 'Yayasan',
                'is_opsional' => false,
            ],
            [
                'id_jenis_dok' => 4,
                'nama_dokumen' => 'NPWP',
                'deskripsi' => 'Nomor Pokok Wajib Pajak lembaga',
                'wajib_untuk_jenis_lembaga' => null,
                'is_opsional' => true,
            ],
            [
                'id_jenis_dok' => 5,
                'nama_dokumen' => 'Surat Domisili',
                'deskripsi' => 'Surat keterangan domisili lembaga',
                'wajib_untuk_jenis_lembaga' => 'Komunitas',
                'is_opsional' => false,
            ],
        ];

        DB::table('jenis_dokumen')->upsert($data, 'id_jenis_dok');
    }
}
