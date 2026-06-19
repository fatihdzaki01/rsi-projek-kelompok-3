-- ==========================================================
-- SCRIPT DATA DUMMY BERBAGIVE (MINIMAL 5 DATA PER TABEL)
-- ==========================================================

-- 1. wilayah
INSERT INTO wilayah (kode, nama) VALUES
('33', 'Jawa Tengah'),
('33.72', 'Kota Surakarta'),
('33.72.01', 'Banjarsari'),
('33.72.02', 'Jebres'),
('33.72.03', 'Laweyan');

-- 2. jenis_lembaga
INSERT INTO jenis_lembaga (id_jenis, nama_jenis) VALUES
(1, 'Yayasan'),
(2, 'Panti Asuhan'),
(3, 'Komunitas Mahasiswa'),
(4, 'Organisasi Pemuda'),
(5, 'Lembaga Swadaya Masyarakat');

-- 3. jenis_dokumen
INSERT INTO jenis_dokumen (id_jenis_dok, nama_dokumen, deskripsi, wajib_untuk_jenis_lembaga, is_opsional) VALUES
(1, 'KTP Ketua', 'Kartu Tanda Penduduk Ketua', NULL, FALSE),
(2, 'Akta Pendirian', 'Akta Notaris Pendirian', '1', FALSE),
(3, 'NPWP Lembaga', 'Nomor Pokok Wajib Pajak', '1', FALSE),
(4, 'Surat Keterangan Domisili', 'SKD dari kelurahan', NULL, TRUE),
(5, 'Surat Keputusan Kepengurusan', 'SK Kepengurusan terbaru', '3', FALSE);

-- 4. kategori_campaign
INSERT INTO kategori_campaign (id_kategori, nama_kategori, deskripsi, is_active) VALUES
(1, 'Pendidikan', 'Bantuan biaya sekolah dan fasilitas belajar', TRUE),
(2, 'Kesehatan', 'Bantuan biaya pengobatan dan fasilitas medis', TRUE),
(3, 'Bencana Alam', 'Bantuan tanggap darurat bencana', TRUE),
(4, 'Pembangunan', 'Pembangunan tempat ibadah atau fasilitas umum', TRUE),
(5, 'Sosial Kemanusiaan', 'Bantuan sembako dan kebutuhan hidup', TRUE);

-- 5. users (Semua password adalah 'password' ter-enkripsi bcrypt standard)
INSERT INTO users (username, email, password_hash, role, is_active, is_verified, foto_profil_url, nama_lengkap, nomor_telepon, jenis_kelamin, tanggal_lahir, kode_wilayah) VALUES
('superadmin', 'admin@berbagive.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'SUPERADMIN', TRUE, TRUE, NULL, 'Super Administrator', '081234567890', 'L', '1990-01-01', '33.72'),
('komunitas1', 'komunitas1@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'KOMUNITAS', TRUE, TRUE, NULL, 'Admin Komunitas 1', '081234567891', 'P', '1992-02-02', '33.72.01'),
('komunitas2', 'komunitas2@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'KOMUNITAS', TRUE, TRUE, NULL, 'Admin Komunitas 2', '081234567892', 'L', '1995-03-03', '33.72.02'),
('donatur1', 'donatur1@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'DONATUR', TRUE, TRUE, NULL, 'Donatur Baik 1', '081234567893', 'L', '1998-04-04', '33.72.03'),
('donatur2', 'donatur2@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'DONATUR', TRUE, TRUE, NULL, 'Donatur Baik 2', '081234567894', 'P', '1999-05-05', '33.72'),
('donatur3', 'donatur3@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'DONATUR', TRUE, TRUE, NULL, 'Donatur Baik 3', '081234567895', 'L', '2000-06-06', '33.72.01'),
('donatur4', 'donatur4@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'DONATUR', TRUE, TRUE, NULL, 'Donatur Baik 4', '081234567896', 'P', '2001-07-07', '33.72.02'),
('donatur5', 'donatur5@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'DONATUR', TRUE, TRUE, NULL, 'Donatur Baik 5', '081234567897', 'L', '2002-08-08', '33.72.03');

-- 6. komunitas
INSERT INTO komunitas (id_user, id_jenis_lembaga, nama_lembaga, deskripsi, kode_wilayah, rt, rw, kode_pos, alamat_detail, nomor_kontak, link_medsos, foto_lembaga_url, nama_bank, nomor_rekening, foto_buku_rekening_url, status) VALUES
(2, 1, 'Yayasan Kasih Ibu', 'Yayasan yang peduli anak yatim', '33.72.01', '01', '02', '57131', 'Jl. Merdeka No 1', '0811111111', 'ig:kasihibu', 'default_lembaga.png', 'BCA', '1234567890', 'buku1.png', 'diverifikasi'),
(3, 3, 'BEM UNS', 'Badan Eksekutif Mahasiswa', '33.72.02', '03', '04', '57126', 'Kampus UNS Kentingan', '0822222222', 'ig:bemuns', 'default_lembaga.png', 'Mandiri', '0987654321', 'buku2.png', 'diverifikasi'),
(2, 2, 'Panti Asuhan Mulia', 'Panti asuhan anak yatim piatu', '33.72.03', '05', '06', '57141', 'Jl. Slamet Riyadi No 10', '0833333333', 'ig:pantimulia', 'default_lembaga.png', 'BRI', '1122334455', 'buku3.png', 'diverifikasi'),
(3, 4, 'Karang Taruna Maju', 'Organisasi pemuda desa', '33.72', '07', '08', '57111', 'Balai Desa Maju', '0844444444', 'ig:tarunamaju', 'default_lembaga.png', 'BNI', '5566778899', 'buku4.png', 'menunggu'),
(2, 5, 'LSM Lingkungan Sehat', 'LSM peduli lingkungan', '33.72.01', '09', '10', '57132', 'Jl. Hijau Raya No 5', '0855555555', 'ig:lingkungansehat', 'default_lembaga.png', 'BSI', '9988776655', 'buku5.png', 'diverifikasi');

-- 7. dokumen_komunitas
INSERT INTO dokumen_komunitas (id_komunitas, id_jenis_dok, file_url, status_verifikasi) VALUES
(1, 1, 'ktp_1.pdf', 'diverifikasi'),
(2, 5, 'sk_2.pdf', 'diverifikasi'),
(3, 1, 'ktp_3.pdf', 'diverifikasi'),
(4, 1, 'ktp_4.pdf', 'menunggu_verifikasi'),
(5, 2, 'akta_5.pdf', 'diverifikasi');

-- 8. campaign
INSERT INTO campaign (id_komunitas, id_kategori, kode_wilayah, judul, deskripsi, foto_campaign_url, target_dana, dana_terkumpul, tipe_distribusi, status, tanggal_mulai, tanggal_selesai) VALUES
(1, 1, '33.72.01', 'Bantu Sekolah Anak Yatim', 'Penggalangan dana untuk SPP 50 anak panti.', 'camp1.png', 50000000, 10000000, 'individual', 'aktif', '2026-06-01', '2026-07-01'),
(2, 3, '33.72.02', 'Bantu Korban Banjir', 'Sembako untuk korban banjir.', 'camp2.png', 20000000, 5000000, 'komunitas', 'aktif', '2026-06-10', '2026-06-30'),
(3, 2, '33.72.03', 'Operasi Jantung Bayi Budi', 'Biaya rumah sakit dedek Budi.', 'camp3.png', 100000000, 100000000, 'individual', 'selesai', '2026-01-01', '2026-02-01'),
(5, 4, '33.72.01', 'Pembangunan Masjid Desa', 'Renovasi masjid at-taqwa.', 'camp4.png', 75000000, 0, 'komunitas', 'menunggu_review', '2026-07-01', '2026-12-01'),
(1, 5, '33.72.01', 'Sembako Lansia Dhuafa', 'Sembako bulanan lansia.', 'camp5.png', 10000000, 2000000, 'komunitas', 'aktif', '2026-06-15', '2026-08-15');

-- 9. donasi
INSERT INTO donasi (id_user, id_campaign, nominal, metode_pembayaran, nama_tampil, is_anonim, status_pembayaran) VALUES
(4, 1, 1000000, 'bca_va', 'Hamba Allah', TRUE, 'berhasil'),
(5, 1, 500000, 'bni_va', 'Donatur 2', FALSE, 'berhasil'),
(6, 2, 2000000, 'bri_va', 'Donatur 3', FALSE, 'berhasil'),
(7, 2, 1000000, 'mandiri_va', 'Hamba Allah', TRUE, 'berhasil'),
(8, 3, 50000000, 'bca_va', 'Orang Baik', TRUE, 'berhasil'),
(4, 3, 50000000, 'bni_va', 'Donatur 1', FALSE, 'berhasil'),
(5, 5, 1000000, 'qris', 'Donatur 2', FALSE, 'berhasil'),
(6, 5, 1000000, 'qris', 'Hamba Allah', TRUE, 'berhasil'),
(7, 1, 500000, 'bca_va', 'Donatur 4', FALSE, 'pending'),
(8, 2, 1000000, 'bri_va', 'Orang Baik', TRUE, 'gagal');

-- (Opsional/Trigger) Menyesuaikan dana terkumpul
UPDATE campaign SET dana_terkumpul = (SELECT COALESCE(SUM(nominal), 0) FROM donasi WHERE id_campaign = campaign.id_campaign AND status_pembayaran = 'berhasil');
UPDATE campaign SET saldo_tersedia = dana_terkumpul;

-- 10. pencairan_dana
INSERT INTO pencairan_dana (id_campaign, id_komunitas, urutan_ke, nominal_diajukan, nominal_disetujui, keterangan, url_proposal, nama_bank_tujuan, nomor_rekening_tujuan, status) VALUES
(3, 3, 1, 50000000, 50000000, 'Pencairan tahap 1 untuk DP rumah sakit', 'proposal1.pdf', 'BRI', '1122334455', 'disetujui'),
(3, 3, 2, 50000000, 50000000, 'Pencairan pelunasan', 'proposal2.pdf', 'BRI', '1122334455', 'disetujui'),
(1, 1, 1, 5000000, NULL, 'Pembayaran SPP termin 1', 'prop3.pdf', 'BCA', '1234567890', 'menunggu_review'),
(2, 2, 1, 2000000, 2000000, 'Beli Sembako darurat', 'prop4.pdf', 'Mandiri', '0987654321', 'disetujui'),
(5, 1, 1, 1000000, NULL, 'Sembako lansia bulan pertama', 'prop5.pdf', 'BCA', '1234567890', 'menunggu_review');

-- 11. laporan_penggunaan_dana
INSERT INTO laporan_penggunaan_dana (id_pencairan, jumlah_penerima, deskripsi_penggunaan, total_realisasi, file_dokumentasi_url, status_verifikasi) VALUES
(1, 1, 'Pembayaran DP Rumah Sakit', 50000000, 'kwitansi1.pdf', 'diverifikasi'),
(2, 1, 'Pelunasan biaya pasca operasi', 50000000, 'kwitansi2.pdf', 'diverifikasi'),
(4, 50, 'Sembako untuk 50 KK', 2000000, 'foto_sembako.pdf', 'menunggu_verifikasi');

-- 12. penerima_manfaat
INSERT INTO penerima_manfaat (id_laporan, nama, nik, kode_wilayah, nominal_diterima) VALUES
(1, 'Budi Santoso', '3372012345678901', '33.72.03', 50000000),
(2, 'Budi Santoso', '3372012345678901', '33.72.03', 50000000),
(3, 'Pak RT 01', '3372022345678902', '33.72.02', 400000),
(3, 'Ibu Warteg', '3372022345678903', '33.72.02', 400000),
(3, 'Kang Sayur', '3372022345678904', '33.72.02', 400000);

-- 13. potongan_platform
INSERT INTO potongan_platform (id_campaign, total_dana_terkumpul, persentase_potongan, nominal_potongan, dipotong_oleh, catatan) VALUES
(1, 1500000, 1.00, 15000, 1, 'Potongan operasional'),
(2, 3000000, 1.00, 30000, 1, 'Potongan operasional'),
(3, 100000000, 1.00, 1000000, 1, 'Potongan operasional'),
(4, 0, 1.00, 0, 1, 'N/A'),
(5, 2000000, 1.00, 20000, 1, 'Potongan operasional');

-- 14. update_campaign
INSERT INTO update_campaign (id_campaign, id_komunitas, judul_update, konten, is_pinned) VALUES
(3, 3, 'Operasi Berjalan Lancar', 'Alhamdulillah operasi dedek Budi lancar.', TRUE),
(3, 3, 'Masa Pemulihan', 'Budi sudah boleh pulang.', FALSE),
(1, 1, 'Penyaluran SPP Tahap 1', 'SPP 10 anak pertama telah disalurkan.', FALSE),
(2, 2, 'Sembako telah disebar', 'Terima kasih, 50 paket sembako sudah sampai di pengungsian.', TRUE),
(5, 1, 'Mulai distribusi bulan ini', 'Kami akan segera membeli beras dan minyak goreng.', FALSE);

-- 15. foto_update
INSERT INTO foto_update (id_update, foto_url, caption, urutan) VALUES
(1, 'update1.png', 'Foto Budi di RS', 1),
(2, 'update2.png', 'Budi di rumah', 1),
(3, 'update3.png', 'Penyerahan SPP', 1),
(4, 'update4.png', 'Distribusi sembako di tenda', 1),
(5, 'update5.png', 'Persiapan sembako', 1);

-- 16. verifikasi_rekening
INSERT INTO verifikasi_rekening (id_komunitas, nama_bank_baru, nomor_rekening_baru, foto_buku_rekening_url, alasan_perubahan, status) VALUES
(1, 'BSI', '1111222233', 'buku_baru1.png', 'Migrasi rekening yayasan', 'menunggu'),
(2, 'BCA', '4444555566', 'buku_baru2.png', 'Bank limit kecil', 'diverifikasi'),
(3, 'Mandiri', '7777888899', 'buku_baru3.png', 'Mudah akses', 'ditolak'),
(4, 'BNI', '1010101010', 'buku_baru4.png', 'Kolektif panitia', 'menunggu'),
(5, 'BRI', '2020202020', 'buku_baru5.png', 'Rekening khusus', 'diverifikasi');

-- 17. follow_komunitas
INSERT INTO follow_komunitas (id_user, id_komunitas, is_active) VALUES
(4, 1, TRUE),
(5, 1, TRUE),
(6, 2, TRUE),
(7, 3, TRUE),
(8, 5, TRUE);

-- 18. notifikasi
INSERT INTO notifikasi (id_penerima_user, id_penerima_komunitas, judul, pesan, tipe, expires_at) VALUES
(4, NULL, 'Donasi Berhasil', 'Terima kasih atas donasi Anda.', 'INFO', '2026-12-31'),
(5, NULL, 'Campaign Selesai', 'Campaign yang Anda donasikan telah selesai.', 'INFO', '2026-12-31'),
(NULL, 1, 'Pencairan Disetujui', 'Pencairan dana Rp5.000.000 disetujui.', 'INFO', '2026-12-31'),
(NULL, 2, 'Laporan Diterima', 'Laporan penggunaan dana Anda diverifikasi.', 'INFO', '2026-12-31'),
(4, NULL, 'Update Campaign', 'Ada update baru dari campaign Anda.', 'INFO', '2026-12-31');
