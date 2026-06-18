<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // ===== 10 donatur dummy =====
        $donaturIds = [];
        $donaturData = [
            ['username' => 'AndiPratama', 'email' => 'andi@email.com'],
            ['username' => 'BudiSantoso', 'email' => 'budi@email.com'],
            ['username' => 'CitraDewi', 'email' => 'citra@email.com'],
            ['username' => 'DoniWijaya', 'email' => 'doni@email.com'],
            ['username' => 'EvaLestari', 'email' => 'eva@email.com'],
            ['username' => 'FajarHidayat', 'email' => 'fajar@email.com'],
            ['username' => 'GitaPermata', 'email' => 'gita@email.com'],
            ['username' => 'HendraGunawan', 'email' => 'hendra@email.com'],
            ['username' => 'IndahPertiwi', 'email' => 'indah@email.com'],
            ['username' => 'JokoSusilo', 'email' => 'joko@email.com'],
        ];

        foreach ($donaturData as $d) {
            $id = DB::table('users')->insertGetId([
                'username'      => $d['username'],
                'email'         => $d['email'],
                'password_hash' => Hash::make('password123'),
                'role'          => 'DONATUR',
                'is_active'     => true,
                'is_verified'   => true,
                'nama_lengkap'  => $d['username'],
                'created_at'    => now(),
                'updated_at'    => now(),
            ], 'id_user');
            $donaturIds[] = $id;
        }

        // ===== 8 komunitas approved =====
        $komunitasUsers = [];
        $komData = [
            ['username' => 'YayasanSejahtera', 'email' => 'sejahtera@email.com', 'lembaga' => 'Yayasan Sejahtera Bersama', 'jenis' => 1, 'bank' => 'BCA', 'rekening' => '1234567890'],
            ['username' => 'KomunitasPeduli', 'email' => 'peduli@email.com', 'lembaga' => 'Komunitas Peduli Sesama', 'jenis' => 2, 'bank' => 'Mandiri', 'rekening' => '9876543210'],
            ['username' => 'LSMHarapan', 'email' => 'harapan@email.com', 'lembaga' => 'LSM Harapan Bangsa', 'jenis' => 3, 'bank' => 'BNI', 'rekening' => '5556667777'],
            ['username' => 'RumahZakat', 'email' => 'rumahzakat@email.com', 'lembaga' => 'Rumah Zakat Indonesia', 'jenis' => 1, 'bank' => 'BSI', 'rekening' => '1112223334'],
            ['username' => 'KomunitasLiterasi', 'email' => 'literasi@email.com', 'lembaga' => 'Komunitas Literasi Nusantara', 'jenis' => 2, 'bank' => 'BCA', 'rekening' => '2223334445'],
            ['username' => 'YPantiAsuhan', 'email' => 'pantiasuhan@email.com', 'lembaga' => 'Yayasan Panti Asuhan Putra', 'jenis' => 1, 'bank' => 'Mandiri', 'rekening' => '3334445556'],
            ['username' => 'KomunitasDifabel', 'email' => 'difabel@email.com', 'lembaga' => 'Komunitas Peduli Difabel', 'jenis' => 2, 'bank' => 'BNI', 'rekening' => '4445556667'],
            ['username' => 'LSMPelita', 'email' => 'pelita@email.com', 'lembaga' => 'LSM Pelita Harapan', 'jenis' => 3, 'bank' => 'BSI', 'rekening' => '5556667778'],
        ];

        $superadminId = DB::table('users')->where('email', 'superadmin@berbagive.com')->value('id_user');

        $wilayahKomunitas = ['3101', '3201', '3501', '3102', '3202', '3302', '3502', '5101'];

        $komunitasIds = [];
        foreach ($komData as $i => $k) {
            $userId = DB::table('users')->insertGetId([
                'username'      => $k['username'],
                'email'         => $k['email'],
                'password_hash' => Hash::make('password123'),
                'role'          => 'KOMUNITAS',
                'is_active'     => true,
                'is_verified'   => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ], 'id_user');
            $komunitasUsers[] = $userId;

            $komId = DB::table('komunitas')->insertGetId([
                'id_user'          => $userId,
                'id_jenis_lembaga' => $k['jenis'],
                'nama_lembaga'     => $k['lembaga'],
                'deskripsi'        => $i < 3 ? 'Kami bergerak di bidang sosial kemanusiaan' : (['Menyantuni anak yatim dan dhuafa di seluruh Indonesia', 'Meningkatkan minat baca dan literasi masyarakat', 'Merawat dan mendidik anak-anak panti asuhan', 'Memperjuangkan hak dan kemandirian penyandang difabel', 'Memberdayakan masyarakat melalui program sosial dan pendidikan'][$i - 3] ?? 'Kami bergerak di bidang sosial kemanusiaan'),
                'kode_wilayah'     => $wilayahKomunitas[$i] ?? '3101',
                'alamat_detail'    => 'Jl. Contoh No. 123',
                'nomor_kontak'     => '08123456789',
                'link_medsos'      => 'https://instagram.com/' . strtolower(str_replace(' ', '', $k['lembaga'])),
                'foto_lembaga_url' => 'https://placehold.co/400x400/6B3A2A/FFFFFF?text=' . urlencode(explode(' ', $k['lembaga'])[0]),
                'foto_buku_rekening_url' => 'https://placehold.co/600x300/8B4513/FFFFFF?text=Buku+Rekening',
                'nama_bank'        => $k['bank'],
                'nomor_rekening'   => $k['rekening'],
                'status'           => 'aktif',
                'direview_oleh'    => $superadminId,
                'created_at'       => now(),
                'updated_at'       => now(),
            ], 'id_komunitas');
            $komunitasIds[] = $komId;
        }

        // ===== 12 campaign aktif =====
        $campaignData = [
            ['judul' => 'Bantu Pendidikan Anak Yatim', 'kom' => 0, 'kategori' => 2, 'target' => 20000000, 'dana' => 15000000, 'wilayah' => '3101'],
            ['judul' => 'Bantuan Korban Bencana Banjir', 'kom' => 1, 'kategori' => 1, 'target' => 50000000, 'dana' => 30000000, 'wilayah' => '3201'],
            ['judul' => 'Pengobatan untuk Balita Stunting', 'kom' => 2, 'kategori' => 3, 'target' => 15000000, 'dana' => 5000000, 'wilayah' => '3501'],
            ['judul' => 'Renovasi Masjid Desa', 'kom' => 0, 'kategori' => 7, 'target' => 30000000, 'dana' => 25000000, 'wilayah' => '3301'],
            ['judul' => 'Beasiswa Mahasiswa Kurang Mampu', 'kom' => 1, 'kategori' => 2, 'target' => 25000000, 'dana' => 8000000, 'wilayah' => '3202'],
            ['judul' => 'Bantuan Sembako untuk Dhuafa', 'kom' => 3, 'kategori' => 4, 'target' => 12000000, 'dana' => 9000000, 'wilayah' => '3102'],
            ['judul' => 'Taman Baca untuk Anak Pinggiran', 'kom' => 4, 'kategori' => 2, 'target' => 8000000, 'dana' => 3000000, 'wilayah' => '3202'],
            ['judul' => 'Program Makan Sehat untuk Balita', 'kom' => 5, 'kategori' => 3, 'target' => 18000000, 'dana' => 12000000, 'wilayah' => '3302'],
            ['judul' => 'Donasi Alat Bantu Difabel', 'kom' => 6, 'kategori' => 10, 'target' => 25000000, 'dana' => 7000000, 'wilayah' => '3502'],
            ['judul' => 'Penghijauan Hutan Mangrove', 'kom' => 2, 'kategori' => 5, 'target' => 40000000, 'dana' => 22000000, 'wilayah' => '3501'],
            ['judul' => 'Bedah Rumah untuk Lansia', 'kom' => 7, 'kategori' => 4, 'target' => 35000000, 'dana' => 14000000, 'wilayah' => '5101'],
            ['judul' => 'Kursus Keterampilan untuk Ibu Rumah Tangga', 'kom' => 4, 'kategori' => 8, 'target' => 10000000, 'dana' => 4000000, 'wilayah' => '3202'],
        ];

        $campaignIds = [];
        foreach ($campaignData as $c) {
            $id = DB::table('campaign')->insertGetId([
                'id_komunitas'   => $komunitasIds[$c['kom']],
                'id_kategori'    => $c['kategori'],
                'kode_wilayah'   => $c['wilayah'],
                'judul'          => $c['judul'],
                'deskripsi'      => 'Campaign ini bertujuan untuk menggalang dana bagi mereka yang membutuhkan. Mari bersama-sama berbagi kebaikan.',
                'foto_campaign_url' => 'https://placehold.co/800x400/8B4513/FFFFFF?text=' . urlencode($c['judul']),
                'target_dana'    => $c['target'],
                'dana_terkumpul' => $c['dana'],
                'saldo_tersedia' => (int)($c['dana'] * 0.9),
                'saldo_terkunci' => (int)($c['dana'] * 0.1),
                'tipe_distribusi' => 'kolektif',
                'target_audiens' => 'Masyarakat umum',
                'status'         => 'aktif',
                'tanggal_mulai'  => now()->subDays(rand(10, 60)),
                'tanggal_selesai'=> now()->addDays(rand(10, 90)),
                'created_at'     => now()->subDays(rand(1, 30)),
                'updated_at'     => now(),
            ], 'id_campaign');
            $campaignIds[] = $id;
        }

        // ===== 40 donasi mixed =====
        $paymentMethods = ['qris', 'gopay', 'ovo', 'shopeepay', 'bca', 'mandiri', 'bni'];
        $statuses = ['berhasil', 'berhasil', 'berhasil', 'berhasil', 'pending', 'gagal'];

        for ($i = 0; $i < 40; $i++) {
            $donaturId = $donaturIds[array_rand($donaturIds)];
            $campaignId = $campaignIds[array_rand($campaignIds)];
            $nominal = [25000, 50000, 100000, 150000, 200000, 500000][array_rand([25000, 50000, 100000, 150000, 200000, 500000])];
            $status = $statuses[array_rand($statuses)];
            $isAnonim = (bool)random_int(0, 1);

            DB::table('donasi')->insert([
                'id_user'           => $donaturId,
                'id_campaign'       => $campaignId,
                'nominal'           => $nominal,
                'metode_pembayaran' => $paymentMethods[array_rand($paymentMethods)],
                'nama_tampil'       => $isAnonim ? null : 'Donatur #' . $donaturId,
                'is_anonim'         => $isAnonim,
                'status_pembayaran' => $status,
                'created_at'        => now()->subDays(rand(0, 60)),
            ]);
        }

        // ===== 5 notifikasi untuk superadmin =====
        $superadmin = DB::table('users')->where('email', 'superadmin@berbagive.com')->first();

        $notifData = [
            ['judul' => 'Selamat Datang', 'pesan' => 'Selamat bergabung di Berbagive! Anda terdaftar sebagai Superadmin.', 'tipe' => 'system'],
            ['judul' => 'Registrasi Komunitas Baru', 'pesan' => 'Yayasan Sejahtera Bersama telah mendaftar dan menunggu review.', 'tipe' => 'registrasi_komunitas'],
            ['judul' => 'Registrasi Komunitas Baru', 'pesan' => 'Komunitas Peduli Sesama telah mendaftar dan menunggu review.', 'tipe' => 'registrasi_komunitas'],
            ['judul' => 'Campaign Baru', 'pesan' => 'Campaign "Bantu Pendidikan Anak Yatim" menunggu review.', 'tipe' => 'campaign', 'related_campaign_id' => $campaignIds[0] ?? null],
            ['judul' => 'Campaign Baru', 'pesan' => 'Campaign "Bantuan Korban Bencana Banjir" menunggu review.', 'tipe' => 'campaign', 'related_campaign_id' => $campaignIds[1] ?? null],
        ];

        foreach ($notifData as $n) {
            $row = [
                'id_penerima_user' => $superadmin->id_user ?? 1,
                'judul'            => $n['judul'],
                'pesan'            => $n['pesan'],
                'tipe'             => $n['tipe'],
                'is_read'          => false,
                'created_at'       => now()->subHours(rand(1, 72)),
                'expires_at'       => now()->addDays(30),
            ];
            if (isset($n['related_campaign_id'])) {
                $row['related_campaign_id'] = $n['related_campaign_id'];
            }
            DB::table('notifikasi')->insert($row);
        }
    }
}
