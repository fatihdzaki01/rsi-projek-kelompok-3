<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WilayahSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            // ── DKI Jakarta ─────────────────────────────────────
            ['kode' => '31', 'nama' => 'DKI Jakarta'],
            ['kode' => '31.01', 'nama' => 'Jakarta Pusat'],
            ['kode' => '31.01.001', 'nama' => 'Gambir'],
            ['kode' => '31.01.002', 'nama' => 'Menteng'],
            ['kode' => '31.01.003', 'nama' => 'Sawah Besar'],
            ['kode' => '31.01.004', 'nama' => 'Senen'],
            ['kode' => '31.01.005', 'nama' => 'Cempaka Putih'],
            ['kode' => '31.01.006', 'nama' => 'Johar Baru'],
            ['kode' => '31.01.007', 'nama' => 'Tanah Abang'],
            ['kode' => '31.01.008', 'nama' => 'Kemayoran'],

            ['kode' => '31.02', 'nama' => 'Jakarta Selatan'],
            ['kode' => '31.02.001', 'nama' => 'Kebayoran Baru'],
            ['kode' => '31.02.002', 'nama' => 'Kebayoran Lama'],
            ['kode' => '31.02.003', 'nama' => 'Pasar Minggu'],
            ['kode' => '31.02.004', 'nama' => 'Cilandak'],
            ['kode' => '31.02.005', 'nama' => 'Tebet'],
            ['kode' => '31.02.006', 'nama' => 'Mampang Prapatan'],

            ['kode' => '31.03', 'nama' => 'Jakarta Timur'],
            ['kode' => '31.03.001', 'nama' => 'Jatinegara'],
            ['kode' => '31.03.002', 'nama' => 'Duren Sawit'],
            ['kode' => '31.03.003', 'nama' => 'Cakung'],
            ['kode' => '31.03.004', 'nama' => 'Pulo Gadung'],
            ['kode' => '31.03.005', 'nama' => 'Matraman'],

            // ── Jawa Barat ──────────────────────────────────────
            ['kode' => '32', 'nama' => 'Jawa Barat'],
            ['kode' => '32.01', 'nama' => 'Kota Bandung'],
            ['kode' => '32.01.001', 'nama' => 'Bandung Wetan'],
            ['kode' => '32.01.002', 'nama' => 'Cibeunying Kidul'],
            ['kode' => '32.01.003', 'nama' => 'Coblong'],
            ['kode' => '32.01.004', 'nama' => 'Lengkong'],
            ['kode' => '32.01.005', 'nama' => 'Sumur Bandung'],

            ['kode' => '32.02', 'nama' => 'Kota Bogor'],
            ['kode' => '32.02.001', 'nama' => 'Bogor Tengah'],
            ['kode' => '32.02.002', 'nama' => 'Bogor Timur'],
            ['kode' => '32.02.003', 'nama' => 'Bogor Utara'],
            ['kode' => '32.02.004', 'nama' => 'Tanah Sareal'],

            ['kode' => '32.03', 'nama' => 'Kabupaten Bandung'],
            ['kode' => '32.03.001', 'nama' => 'Cileunyi'],
            ['kode' => '32.03.002', 'nama' => 'Cimenyan'],
            ['kode' => '32.03.003', 'nama' => 'Rancaekek'],
            ['kode' => '32.03.004', 'nama' => 'Bojongsoang'],

            // ── Jawa Timur ──────────────────────────────────────
            ['kode' => '35', 'nama' => 'Jawa Timur'],
            ['kode' => '35.01', 'nama' => 'Kota Surabaya'],
            ['kode' => '35.01.001', 'nama' => 'Gubeng'],
            ['kode' => '35.01.002', 'nama' => 'Tegalsari'],
            ['kode' => '35.01.003', 'nama' => 'Wonokromo'],
            ['kode' => '35.01.004', 'nama' => 'Sawahan'],
            ['kode' => '35.01.005', 'nama' => 'Sukolilo'],

            ['kode' => '35.02', 'nama' => 'Kota Malang'],
            ['kode' => '35.02.001', 'nama' => 'Klojen'],
            ['kode' => '35.02.002', 'nama' => 'Lowokwaru'],
            ['kode' => '35.02.003', 'nama' => 'Blimbing'],
            ['kode' => '35.02.004', 'nama' => 'Sukun'],

            ['kode' => '35.03', 'nama' => 'Kabupaten Malang'],
            ['kode' => '35.03.001', 'nama' => 'Kepanjen'],
            ['kode' => '35.03.002', 'nama' => 'Singosari'],
            ['kode' => '35.03.003', 'nama' => 'Lawang'],
            ['kode' => '35.03.004', 'nama' => 'Batu'],
        ];

        DB::table('wilayah')->upsert($data, 'kode');
    }
}
