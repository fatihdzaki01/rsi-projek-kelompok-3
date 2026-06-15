<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JenisLembagaSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['id_jenis' => 1, 'nama_jenis' => 'Yayasan'],
            ['id_jenis' => 2, 'nama_jenis' => 'Komunitas'],
            ['id_jenis' => 3, 'nama_jenis' => 'LSM'],
            ['id_jenis' => 4, 'nama_jenis' => 'Lembaga Sosial'],
            ['id_jenis' => 5, 'nama_jenis' => 'Organisasi Mahasiswa'],
        ];

        DB::table('jenis_lembaga')->upsert($data, 'id_jenis');
    }
}
