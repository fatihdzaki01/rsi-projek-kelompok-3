<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriCampaignSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['id_kategori' => 1, 'nama_kategori' => 'Bencana', 'deskripsi' => 'Bantuan bencana alam dan kemanusiaan', 'is_active' => true],
            ['id_kategori' => 2, 'nama_kategori' => 'Pendidikan', 'deskripsi' => 'Beasiswa dan bantuan pendidikan', 'is_active' => true],
            ['id_kategori' => 3, 'nama_kategori' => 'Kesehatan', 'deskripsi' => 'Bantuan biaya kesehatan dan pengobatan', 'is_active' => true],
            ['id_kategori' => 4, 'nama_kategori' => 'Sosial', 'deskripsi' => 'Bantuan sosial dan kemanusiaan', 'is_active' => true],
            ['id_kategori' => 5, 'nama_kategori' => 'Lingkungan', 'deskripsi' => 'Pelestarian lingkungan dan penghijauan', 'is_active' => true],
            ['id_kategori' => 6, 'nama_kategori' => 'Hewan', 'deskripsi' => 'Penyelamatan dan kesejahteraan hewan', 'is_active' => true],
            ['id_kategori' => 7, 'nama_kategori' => 'Ibadah', 'deskripsi' => 'Pembangunan dan renovasi tempat ibadah', 'is_active' => true],
            ['id_kategori' => 8, 'nama_kategori' => 'Modal Usaha', 'deskripsi' => 'Bantuan modal untuk UMKM', 'is_active' => true],
        ];

        DB::table('kategori_campaign')->upsert($data, 'id_kategori');
    }
}
