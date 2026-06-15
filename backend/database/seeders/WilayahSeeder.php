<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WilayahSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['kode' => '11', 'nama' => 'Aceh'],
            ['kode' => '12', 'nama' => 'Sumatera Utara'],
            ['kode' => '31', 'nama' => 'DKI Jakarta'],
            ['kode' => '32', 'nama' => 'Jawa Barat'],
            ['kode' => '33', 'nama' => 'Jawa Tengah'],
            ['kode' => '35', 'nama' => 'Jawa Timur'],
            ['kode' => '51', 'nama' => 'Bali'],
            ['kode' => '1101', 'nama' => 'Kota Banda Aceh'],
            ['kode' => '1102', 'nama' => 'Kabupaten Aceh Besar'],
            ['kode' => '1201', 'nama' => 'Kota Medan'],
            ['kode' => '1202', 'nama' => 'Kota Binjai'],
            ['kode' => '3101', 'nama' => 'Kota Jakarta Pusat'],
            ['kode' => '3102', 'nama' => 'Kota Jakarta Selatan'],
            ['kode' => '3201', 'nama' => 'Kota Bandung'],
            ['kode' => '3202', 'nama' => 'Kota Bekasi'],
            ['kode' => '3301', 'nama' => 'Kota Semarang'],
            ['kode' => '3302', 'nama' => 'Kota Solo'],
            ['kode' => '3501', 'nama' => 'Kota Surabaya'],
            ['kode' => '3502', 'nama' => 'Kota Malang'],
            ['kode' => '5101', 'nama' => 'Kota Denpasar'],
            ['kode' => '5102', 'nama' => 'Kabupaten Badung'],
        ];

        foreach ($data as $row) {
            DB::table('wilayah')->updateOrInsert(['kode' => $row['kode']], $row);
        }
    }
}
