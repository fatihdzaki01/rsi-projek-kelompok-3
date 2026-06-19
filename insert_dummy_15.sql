-- ==========================================================
-- SCRIPT DATA DUMMY BERBAGIVE (15 DATA PER TABEL)
-- ==========================================================

-- Pastikan tabel dalam keadaan kosong sebelum menjalankan script ini
-- Jika sebelumnya sudah ada data, disarankan melakukan TRUNCATE pada semua tabel terlebih dahulu.

-- 1. wilayah
INSERT INTO wilayah (kode, nama) VALUES
('33.01', 'Wilayah 1'), ('33.02', 'Wilayah 2'), ('33.03', 'Wilayah 3'), ('33.04', 'Wilayah 4'), ('33.05', 'Wilayah 5'),
('33.06', 'Wilayah 6'), ('33.07', 'Wilayah 7'), ('33.08', 'Wilayah 8'), ('33.09', 'Wilayah 9'), ('33.10', 'Wilayah 10'),
('33.11', 'Wilayah 11'), ('33.12', 'Wilayah 12'), ('33.13', 'Wilayah 13'), ('33.14', 'Wilayah 14'), ('33.15', 'Wilayah 15');

-- 2. jenis_lembaga
INSERT INTO jenis_lembaga (id_jenis, nama_jenis) VALUES
(1, 'Yayasan 1'), (2, 'Yayasan 2'), (3, 'Yayasan 3'), (4, 'Yayasan 4'), (5, 'Yayasan 5'),
(6, 'Yayasan 6'), (7, 'Yayasan 7'), (8, 'Yayasan 8'), (9, 'Yayasan 9'), (10, 'Yayasan 10'),
(11, 'Yayasan 11'), (12, 'Yayasan 12'), (13, 'Yayasan 13'), (14, 'Yayasan 14'), (15, 'Yayasan 15');

-- 3. jenis_dokumen
INSERT INTO jenis_dokumen (id_jenis_dok, nama_dokumen, deskripsi, wajib_untuk_jenis_lembaga, is_opsional) VALUES
(1, 'KTP 1', 'KTP', NULL, FALSE), (2, 'KTP 2', 'KTP', NULL, FALSE), (3, 'KTP 3', 'KTP', NULL, FALSE),
(4, 'KTP 4', 'KTP', NULL, FALSE), (5, 'KTP 5', 'KTP', NULL, FALSE), (6, 'KTP 6', 'KTP', NULL, FALSE),
(7, 'KTP 7', 'KTP', NULL, FALSE), (8, 'KTP 8', 'KTP', NULL, FALSE), (9, 'KTP 9', 'KTP', NULL, FALSE),
(10, 'KTP 10', 'KTP', NULL, FALSE), (11, 'KTP 11', 'KTP', NULL, FALSE), (12, 'KTP 12', 'KTP', NULL, FALSE),
(13, 'KTP 13', 'KTP', NULL, FALSE), (14, 'KTP 14', 'KTP', NULL, FALSE), (15, 'KTP 15', 'KTP', NULL, FALSE);

-- 4. kategori_campaign
INSERT INTO kategori_campaign (id_kategori, nama_kategori, deskripsi, is_active) VALUES
(1, 'Pendidikan 1', 'Desc', TRUE), (2, 'Pendidikan 2', 'Desc', TRUE), (3, 'Pendidikan 3', 'Desc', TRUE),
(4, 'Pendidikan 4', 'Desc', TRUE), (5, 'Pendidikan 5', 'Desc', TRUE), (6, 'Pendidikan 6', 'Desc', TRUE),
(7, 'Pendidikan 7', 'Desc', TRUE), (8, 'Pendidikan 8', 'Desc', TRUE), (9, 'Pendidikan 9', 'Desc', TRUE),
(10, 'Pendidikan 10', 'Desc', TRUE), (11, 'Pendidikan 11', 'Desc', TRUE), (12, 'Pendidikan 12', 'Desc', TRUE),
(13, 'Pendidikan 13', 'Desc', TRUE), (14, 'Pendidikan 14', 'Desc', TRUE), (15, 'Pendidikan 15', 'Desc', TRUE);

-- 5. users
-- id 1: SUPERADMIN
-- id 2-16: KOMUNITAS
-- id 17-31: DONATUR
-- password: password ($2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi)
INSERT INTO users (id_user, username, email, password_hash, role, is_active, is_verified, foto_profil_url, nama_lengkap, nomor_telepon, jenis_kelamin, tanggal_lahir, kode_wilayah) VALUES
(1, 'superadmin', 'admin@berbagive.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'SUPERADMIN', TRUE, TRUE, NULL, 'Super Administrator', '0812', 'L', '1990-01-01', '33.01'),
(2, 'komunitas1', 'kom1@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'KOMUNITAS', TRUE, TRUE, NULL, 'Kom 1', '0812', 'L', '1990-01-01', '33.01'),
(3, 'komunitas2', 'kom2@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'KOMUNITAS', TRUE, TRUE, NULL, 'Kom 2', '0812', 'L', '1990-01-01', '33.02'),
(4, 'komunitas3', 'kom3@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'KOMUNITAS', TRUE, TRUE, NULL, 'Kom 3', '0812', 'L', '1990-01-01', '33.03'),
(5, 'komunitas4', 'kom4@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'KOMUNITAS', TRUE, TRUE, NULL, 'Kom 4', '0812', 'L', '1990-01-01', '33.04'),
(6, 'komunitas5', 'kom5@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'KOMUNITAS', TRUE, TRUE, NULL, 'Kom 5', '0812', 'L', '1990-01-01', '33.05'),
(7, 'komunitas6', 'kom6@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'KOMUNITAS', TRUE, TRUE, NULL, 'Kom 6', '0812', 'L', '1990-01-01', '33.06'),
(8, 'komunitas7', 'kom7@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'KOMUNITAS', TRUE, TRUE, NULL, 'Kom 7', '0812', 'L', '1990-01-01', '33.07'),
(9, 'komunitas8', 'kom8@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'KOMUNITAS', TRUE, TRUE, NULL, 'Kom 8', '0812', 'L', '1990-01-01', '33.08'),
(10, 'komunitas9', 'kom9@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'KOMUNITAS', TRUE, TRUE, NULL, 'Kom 9', '0812', 'L', '1990-01-01', '33.09'),
(11, 'komunitas10', 'kom10@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'KOMUNITAS', TRUE, TRUE, NULL, 'Kom 10', '0812', 'L', '1990-01-01', '33.10'),
(12, 'komunitas11', 'kom11@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'KOMUNITAS', TRUE, TRUE, NULL, 'Kom 11', '0812', 'L', '1990-01-01', '33.11'),
(13, 'komunitas12', 'kom12@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'KOMUNITAS', TRUE, TRUE, NULL, 'Kom 12', '0812', 'L', '1990-01-01', '33.12'),
(14, 'komunitas13', 'kom13@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'KOMUNITAS', TRUE, TRUE, NULL, 'Kom 13', '0812', 'L', '1990-01-01', '33.13'),
(15, 'komunitas14', 'kom14@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'KOMUNITAS', TRUE, TRUE, NULL, 'Kom 14', '0812', 'L', '1990-01-01', '33.14'),
(16, 'komunitas15', 'kom15@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'KOMUNITAS', TRUE, TRUE, NULL, 'Kom 15', '0812', 'L', '1990-01-01', '33.15'),
(17, 'donatur1', 'don1@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'DONATUR', TRUE, TRUE, NULL, 'Don 1', '0812', 'L', '1990-01-01', '33.01'),
(18, 'donatur2', 'don2@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'DONATUR', TRUE, TRUE, NULL, 'Don 2', '0812', 'L', '1990-01-01', '33.02'),
(19, 'donatur3', 'don3@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'DONATUR', TRUE, TRUE, NULL, 'Don 3', '0812', 'L', '1990-01-01', '33.03'),
(20, 'donatur4', 'don4@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'DONATUR', TRUE, TRUE, NULL, 'Don 4', '0812', 'L', '1990-01-01', '33.04'),
(21, 'donatur5', 'don5@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'DONATUR', TRUE, TRUE, NULL, 'Don 5', '0812', 'L', '1990-01-01', '33.05'),
(22, 'donatur6', 'don6@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'DONATUR', TRUE, TRUE, NULL, 'Don 6', '0812', 'L', '1990-01-01', '33.06'),
(23, 'donatur7', 'don7@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'DONATUR', TRUE, TRUE, NULL, 'Don 7', '0812', 'L', '1990-01-01', '33.07'),
(24, 'donatur8', 'don8@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'DONATUR', TRUE, TRUE, NULL, 'Don 8', '0812', 'L', '1990-01-01', '33.08'),
(25, 'donatur9', 'don9@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'DONATUR', TRUE, TRUE, NULL, 'Don 9', '0812', 'L', '1990-01-01', '33.09'),
(26, 'donatur10', 'don10@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'DONATUR', TRUE, TRUE, NULL, 'Don 10', '0812', 'L', '1990-01-01', '33.10'),
(27, 'donatur11', 'don11@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'DONATUR', TRUE, TRUE, NULL, 'Don 11', '0812', 'L', '1990-01-01', '33.11'),
(28, 'donatur12', 'don12@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'DONATUR', TRUE, TRUE, NULL, 'Don 12', '0812', 'L', '1990-01-01', '33.12'),
(29, 'donatur13', 'don13@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'DONATUR', TRUE, TRUE, NULL, 'Don 13', '0812', 'L', '1990-01-01', '33.13'),
(30, 'donatur14', 'don14@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'DONATUR', TRUE, TRUE, NULL, 'Don 14', '0812', 'L', '1990-01-01', '33.14'),
(31, 'donatur15', 'don15@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'DONATUR', TRUE, TRUE, NULL, 'Don 15', '0812', 'L', '1990-01-01', '33.15');

-- UPDATE users sequence so next insert works automatically
SELECT setval('users_id_user_seq', 31, true);

-- 6. komunitas
-- PERBAIKAN: status = 'aktif' dan direview_oleh = 1
INSERT INTO komunitas (id_komunitas, id_user, id_jenis_lembaga, nama_lembaga, deskripsi, kode_wilayah, rt, rw, kode_pos, alamat_detail, nomor_kontak, link_medsos, foto_lembaga_url, nama_bank, nomor_rekening, foto_buku_rekening_url, status, direview_oleh) VALUES
(1, 2, 1, 'Yayasan 1', 'Desc', '33.01', '01', '01', '12345', 'Alamat 1', '0812', 'ig', 'foto.png', 'BCA', '123', 'buku.png', 'aktif', 1),
(2, 3, 2, 'Yayasan 2', 'Desc', '33.02', '01', '01', '12345', 'Alamat 2', '0812', 'ig', 'foto.png', 'BCA', '123', 'buku.png', 'aktif', 1),
(3, 4, 3, 'Yayasan 3', 'Desc', '33.03', '01', '01', '12345', 'Alamat 3', '0812', 'ig', 'foto.png', 'BCA', '123', 'buku.png', 'aktif', 1),
(4, 5, 4, 'Yayasan 4', 'Desc', '33.04', '01', '01', '12345', 'Alamat 4', '0812', 'ig', 'foto.png', 'BCA', '123', 'buku.png', 'aktif', 1),
(5, 6, 5, 'Yayasan 5', 'Desc', '33.05', '01', '01', '12345', 'Alamat 5', '0812', 'ig', 'foto.png', 'BCA', '123', 'buku.png', 'aktif', 1),
(6, 7, 6, 'Yayasan 6', 'Desc', '33.06', '01', '01', '12345', 'Alamat 6', '0812', 'ig', 'foto.png', 'BCA', '123', 'buku.png', 'aktif', 1),
(7, 8, 7, 'Yayasan 7', 'Desc', '33.07', '01', '01', '12345', 'Alamat 7', '0812', 'ig', 'foto.png', 'BCA', '123', 'buku.png', 'aktif', 1),
(8, 9, 8, 'Yayasan 8', 'Desc', '33.08', '01', '01', '12345', 'Alamat 8', '0812', 'ig', 'foto.png', 'BCA', '123', 'buku.png', 'aktif', 1),
(9, 10, 9, 'Yayasan 9', 'Desc', '33.09', '01', '01', '12345', 'Alamat 9', '0812', 'ig', 'foto.png', 'BCA', '123', 'buku.png', 'aktif', 1),
(10, 11, 10, 'Yayasan 10', 'Desc', '33.10', '01', '01', '12345', 'Alamat 10', '0812', 'ig', 'foto.png', 'BCA', '123', 'buku.png', 'aktif', 1),
(11, 12, 11, 'Yayasan 11', 'Desc', '33.11', '01', '01', '12345', 'Alamat 11', '0812', 'ig', 'foto.png', 'BCA', '123', 'buku.png', 'aktif', 1),
(12, 13, 12, 'Yayasan 12', 'Desc', '33.12', '01', '01', '12345', 'Alamat 12', '0812', 'ig', 'foto.png', 'BCA', '123', 'buku.png', 'aktif', 1),
(13, 14, 13, 'Yayasan 13', 'Desc', '33.13', '01', '01', '12345', 'Alamat 13', '0812', 'ig', 'foto.png', 'BCA', '123', 'buku.png', 'aktif', 1),
(14, 15, 14, 'Yayasan 14', 'Desc', '33.14', '01', '01', '12345', 'Alamat 14', '0812', 'ig', 'foto.png', 'BCA', '123', 'buku.png', 'aktif', 1),
(15, 16, 15, 'Yayasan 15', 'Desc', '33.15', '01', '01', '12345', 'Alamat 15', '0812', 'ig', 'foto.png', 'BCA', '123', 'buku.png', 'aktif', 1);

SELECT setval('komunitas_id_komunitas_seq', 15, true);

-- 7. dokumen_komunitas
-- PERBAIKAN: diverifikasi_oleh = 1, verified_at = NOW()
INSERT INTO dokumen_komunitas (id_dokumen, id_komunitas, id_jenis_dok, file_url, status_verifikasi, diverifikasi_oleh, verified_at) VALUES
(1, 1, 1, 'file1.pdf', 'diverifikasi', 1, NOW()),
(2, 2, 2, 'file2.pdf', 'diverifikasi', 1, NOW()),
(3, 3, 3, 'file3.pdf', 'diverifikasi', 1, NOW()),
(4, 4, 4, 'file4.pdf', 'diverifikasi', 1, NOW()),
(5, 5, 5, 'file5.pdf', 'diverifikasi', 1, NOW()),
(6, 6, 6, 'file6.pdf', 'diverifikasi', 1, NOW()),
(7, 7, 7, 'file7.pdf', 'diverifikasi', 1, NOW()),
(8, 8, 8, 'file8.pdf', 'diverifikasi', 1, NOW()),
(9, 9, 9, 'file9.pdf', 'diverifikasi', 1, NOW()),
(10, 10, 10, 'file10.pdf', 'diverifikasi', 1, NOW()),
(11, 11, 11, 'file11.pdf', 'diverifikasi', 1, NOW()),
(12, 12, 12, 'file12.pdf', 'diverifikasi', 1, NOW()),
(13, 13, 13, 'file13.pdf', 'diverifikasi', 1, NOW()),
(14, 14, 14, 'file14.pdf', 'diverifikasi', 1, NOW()),
(15, 15, 15, 'file15.pdf', 'diverifikasi', 1, NOW());

SELECT setval('dokumen_komunitas_id_dokumen_seq', 15, true);

-- 8. campaign
INSERT INTO campaign (id_campaign, id_komunitas, id_kategori, kode_wilayah, judul, deskripsi, foto_campaign_url, target_dana, dana_terkumpul, tipe_distribusi, status, tanggal_mulai, tanggal_selesai) VALUES
(1, 1, 1, '33.01', 'Campaign 1', 'Desc', 'foto.png', 10000000, 10000, 'individual', 'aktif', '2026-06-01', '2026-12-31'),
(2, 2, 2, '33.02', 'Campaign 2', 'Desc', 'foto.png', 10000000, 10000, 'individual', 'aktif', '2026-06-01', '2026-12-31'),
(3, 3, 3, '33.03', 'Campaign 3', 'Desc', 'foto.png', 10000000, 10000, 'individual', 'aktif', '2026-06-01', '2026-12-31'),
(4, 4, 4, '33.04', 'Campaign 4', 'Desc', 'foto.png', 10000000, 10000, 'individual', 'aktif', '2026-06-01', '2026-12-31'),
(5, 5, 5, '33.05', 'Campaign 5', 'Desc', 'foto.png', 10000000, 10000, 'individual', 'aktif', '2026-06-01', '2026-12-31'),
(6, 6, 6, '33.06', 'Campaign 6', 'Desc', 'foto.png', 10000000, 10000, 'individual', 'aktif', '2026-06-01', '2026-12-31'),
(7, 7, 7, '33.07', 'Campaign 7', 'Desc', 'foto.png', 10000000, 10000, 'individual', 'aktif', '2026-06-01', '2026-12-31'),
(8, 8, 8, '33.08', 'Campaign 8', 'Desc', 'foto.png', 10000000, 10000, 'individual', 'aktif', '2026-06-01', '2026-12-31'),
(9, 9, 9, '33.09', 'Campaign 9', 'Desc', 'foto.png', 10000000, 10000, 'individual', 'aktif', '2026-06-01', '2026-12-31'),
(10, 10, 10, '33.10', 'Campaign 10', 'Desc', 'foto.png', 10000000, 10000, 'individual', 'aktif', '2026-06-01', '2026-12-31'),
(11, 11, 11, '33.11', 'Campaign 11', 'Desc', 'foto.png', 10000000, 10000, 'individual', 'aktif', '2026-06-01', '2026-12-31'),
(12, 12, 12, '33.12', 'Campaign 12', 'Desc', 'foto.png', 10000000, 10000, 'individual', 'aktif', '2026-06-01', '2026-12-31'),
(13, 13, 13, '33.13', 'Campaign 13', 'Desc', 'foto.png', 10000000, 10000, 'individual', 'aktif', '2026-06-01', '2026-12-31'),
(14, 14, 14, '33.14', 'Campaign 14', 'Desc', 'foto.png', 10000000, 10000, 'individual', 'aktif', '2026-06-01', '2026-12-31'),
(15, 15, 15, '33.15', 'Campaign 15', 'Desc', 'foto.png', 10000000, 10000, 'individual', 'aktif', '2026-06-01', '2026-12-31');

SELECT setval('campaign_id_campaign_seq', 15, true);

-- 9. donasi
INSERT INTO donasi (id_donasi, id_user, id_campaign, nominal, metode_pembayaran, nama_tampil, is_anonim, status_pembayaran) VALUES
(1, 17, 1, 10000, 'bca', 'A', FALSE, 'berhasil'),
(2, 18, 2, 10000, 'bca', 'A', FALSE, 'berhasil'),
(3, 19, 3, 10000, 'bca', 'A', FALSE, 'berhasil'),
(4, 20, 4, 10000, 'bca', 'A', FALSE, 'berhasil'),
(5, 21, 5, 10000, 'bca', 'A', FALSE, 'berhasil'),
(6, 22, 6, 10000, 'bca', 'A', FALSE, 'berhasil'),
(7, 23, 7, 10000, 'bca', 'A', FALSE, 'berhasil'),
(8, 24, 8, 10000, 'bca', 'A', FALSE, 'berhasil'),
(9, 25, 9, 10000, 'bca', 'A', FALSE, 'berhasil'),
(10, 26, 10, 10000, 'bca', 'A', FALSE, 'berhasil'),
(11, 27, 11, 10000, 'bca', 'A', FALSE, 'berhasil'),
(12, 28, 12, 10000, 'bca', 'A', FALSE, 'berhasil'),
(13, 29, 13, 10000, 'bca', 'A', FALSE, 'berhasil'),
(14, 30, 14, 10000, 'bca', 'A', FALSE, 'berhasil'),
(15, 31, 15, 10000, 'bca', 'A', FALSE, 'berhasil');

SELECT setval('donasi_id_donasi_seq', 15, true);

UPDATE campaign SET dana_terkumpul = 10000, saldo_tersedia = 10000;

-- 10. pencairan_dana
-- PERBAIKAN: tambah tanggal_keputusan, direview_oleh
INSERT INTO pencairan_dana (id_pencairan, id_campaign, id_komunitas, urutan_ke, nominal_diajukan, nominal_disetujui, keterangan, url_proposal, nama_bank_tujuan, nomor_rekening_tujuan, status, direview_oleh, tanggal_keputusan) VALUES
(1, 1, 1, 1, 5000, 5000, 'Ket', 'url', 'BCA', '123', 'disetujui', 1, NOW()),
(2, 2, 2, 1, 5000, 5000, 'Ket', 'url', 'BCA', '123', 'disetujui', 1, NOW()),
(3, 3, 3, 1, 5000, 5000, 'Ket', 'url', 'BCA', '123', 'disetujui', 1, NOW()),
(4, 4, 4, 1, 5000, 5000, 'Ket', 'url', 'BCA', '123', 'disetujui', 1, NOW()),
(5, 5, 5, 1, 5000, 5000, 'Ket', 'url', 'BCA', '123', 'disetujui', 1, NOW()),
(6, 6, 6, 1, 5000, 5000, 'Ket', 'url', 'BCA', '123', 'disetujui', 1, NOW()),
(7, 7, 7, 1, 5000, 5000, 'Ket', 'url', 'BCA', '123', 'disetujui', 1, NOW()),
(8, 8, 8, 1, 5000, 5000, 'Ket', 'url', 'BCA', '123', 'disetujui', 1, NOW()),
(9, 9, 9, 1, 5000, 5000, 'Ket', 'url', 'BCA', '123', 'disetujui', 1, NOW()),
(10, 10, 10, 1, 5000, 5000, 'Ket', 'url', 'BCA', '123', 'disetujui', 1, NOW()),
(11, 11, 11, 1, 5000, 5000, 'Ket', 'url', 'BCA', '123', 'disetujui', 1, NOW()),
(12, 12, 12, 1, 5000, 5000, 'Ket', 'url', 'BCA', '123', 'disetujui', 1, NOW()),
(13, 13, 13, 1, 5000, 5000, 'Ket', 'url', 'BCA', '123', 'disetujui', 1, NOW()),
(14, 14, 14, 1, 5000, 5000, 'Ket', 'url', 'BCA', '123', 'disetujui', 1, NOW()),
(15, 15, 15, 1, 5000, 5000, 'Ket', 'url', 'BCA', '123', 'disetujui', 1, NOW());

SELECT setval('pencairan_dana_id_pencairan_seq', 15, true);

-- 11. laporan_penggunaan_dana
-- PERBAIKAN: tambah tanggal_verifikasi, diverifikasi_oleh, tanggal_laporan
INSERT INTO laporan_penggunaan_dana (id_laporan, id_pencairan, jumlah_penerima, deskripsi_penggunaan, total_realisasi, file_dokumentasi_url, status_verifikasi, diverifikasi_oleh, tanggal_verifikasi, tanggal_laporan) VALUES
(1, 1, 1, 'Desc', 5000, 'file', 'diverifikasi', 1, NOW(), NOW()),
(2, 2, 1, 'Desc', 5000, 'file', 'diverifikasi', 1, NOW(), NOW()),
(3, 3, 1, 'Desc', 5000, 'file', 'diverifikasi', 1, NOW(), NOW()),
(4, 4, 1, 'Desc', 5000, 'file', 'diverifikasi', 1, NOW(), NOW()),
(5, 5, 1, 'Desc', 5000, 'file', 'diverifikasi', 1, NOW(), NOW()),
(6, 6, 1, 'Desc', 5000, 'file', 'diverifikasi', 1, NOW(), NOW()),
(7, 7, 1, 'Desc', 5000, 'file', 'diverifikasi', 1, NOW(), NOW()),
(8, 8, 1, 'Desc', 5000, 'file', 'diverifikasi', 1, NOW(), NOW()),
(9, 9, 1, 'Desc', 5000, 'file', 'diverifikasi', 1, NOW(), NOW()),
(10, 10, 1, 'Desc', 5000, 'file', 'diverifikasi', 1, NOW(), NOW()),
(11, 11, 1, 'Desc', 5000, 'file', 'diverifikasi', 1, NOW(), NOW()),
(12, 12, 1, 'Desc', 5000, 'file', 'diverifikasi', 1, NOW(), NOW()),
(13, 13, 1, 'Desc', 5000, 'file', 'diverifikasi', 1, NOW(), NOW()),
(14, 14, 1, 'Desc', 5000, 'file', 'diverifikasi', 1, NOW(), NOW()),
(15, 15, 1, 'Desc', 5000, 'file', 'diverifikasi', 1, NOW(), NOW());

SELECT setval('laporan_penggunaan_dana_id_laporan_seq', 15, true);

-- Link pencairan back to laporan
UPDATE pencairan_dana SET id_laporan_dana = id_pencairan;

-- 12. penerima_manfaat
INSERT INTO penerima_manfaat (id_penerima, id_laporan, nama, nik, kode_wilayah, nominal_diterima) VALUES
(1, 1, 'Penerima 1', '1234567890123456', '33.01', 5000),
(2, 2, 'Penerima 2', '1234567890123456', '33.02', 5000),
(3, 3, 'Penerima 3', '1234567890123456', '33.03', 5000),
(4, 4, 'Penerima 4', '1234567890123456', '33.04', 5000),
(5, 5, 'Penerima 5', '1234567890123456', '33.05', 5000),
(6, 6, 'Penerima 6', '1234567890123456', '33.06', 5000),
(7, 7, 'Penerima 7', '1234567890123456', '33.07', 5000),
(8, 8, 'Penerima 8', '1234567890123456', '33.08', 5000),
(9, 9, 'Penerima 9', '1234567890123456', '33.09', 5000),
(10, 10, 'Penerima 10', '1234567890123456', '33.10', 5000),
(11, 11, 'Penerima 11', '1234567890123456', '33.11', 5000),
(12, 12, 'Penerima 12', '1234567890123456', '33.12', 5000),
(13, 13, 'Penerima 13', '1234567890123456', '33.13', 5000),
(14, 14, 'Penerima 14', '1234567890123456', '33.14', 5000),
(15, 15, 'Penerima 15', '1234567890123456', '33.15', 5000);

SELECT setval('penerima_manfaat_id_penerima_seq', 15, true);

-- 13. potongan_platform
INSERT INTO potongan_platform (id_potongan, id_campaign, total_dana_terkumpul, persentase_potongan, nominal_potongan) VALUES
(1, 1, 10000, 5.00, 500),
(2, 2, 10000, 5.00, 500),
(3, 3, 10000, 5.00, 500),
(4, 4, 10000, 5.00, 500),
(5, 5, 10000, 5.00, 500),
(6, 6, 10000, 5.00, 500),
(7, 7, 10000, 5.00, 500),
(8, 8, 10000, 5.00, 500),
(9, 9, 10000, 5.00, 500),
(10, 10, 10000, 5.00, 500),
(11, 11, 10000, 5.00, 500),
(12, 12, 10000, 5.00, 500),
(13, 13, 10000, 5.00, 500),
(14, 14, 10000, 5.00, 500),
(15, 15, 10000, 5.00, 500);

SELECT setval('potongan_platform_id_potongan_seq', 15, true);

-- 14. update_campaign
INSERT INTO update_campaign (id_update, id_campaign, id_komunitas, judul_update, konten) VALUES
(1, 1, 1, 'Update 1', 'Konten'),
(2, 2, 2, 'Update 2', 'Konten'),
(3, 3, 3, 'Update 3', 'Konten'),
(4, 4, 4, 'Update 4', 'Konten'),
(5, 5, 5, 'Update 5', 'Konten'),
(6, 6, 6, 'Update 6', 'Konten'),
(7, 7, 7, 'Update 7', 'Konten'),
(8, 8, 8, 'Update 8', 'Konten'),
(9, 9, 9, 'Update 9', 'Konten'),
(10, 10, 10, 'Update 10', 'Konten'),
(11, 11, 11, 'Update 11', 'Konten'),
(12, 12, 12, 'Update 12', 'Konten'),
(13, 13, 13, 'Update 13', 'Konten'),
(14, 14, 14, 'Update 14', 'Konten'),
(15, 15, 15, 'Update 15', 'Konten');

SELECT setval('update_campaign_id_update_seq', 15, true);

-- 15. foto_update
INSERT INTO foto_update (id_foto, id_update, foto_url, urutan) VALUES
(1, 1, 'foto1.png', 1),
(2, 2, 'foto2.png', 1),
(3, 3, 'foto3.png', 1),
(4, 4, 'foto4.png', 1),
(5, 5, 'foto5.png', 1),
(6, 6, 'foto6.png', 1),
(7, 7, 'foto7.png', 1),
(8, 8, 'foto8.png', 1),
(9, 9, 'foto9.png', 1),
(10, 10, 'foto10.png', 1),
(11, 11, 'foto11.png', 1),
(12, 12, 'foto12.png', 1),
(13, 13, 'foto13.png', 1),
(14, 14, 'foto14.png', 1),
(15, 15, 'foto15.png', 1);

SELECT setval('foto_update_id_foto_seq', 15, true);

-- 16. verifikasi_rekening
INSERT INTO verifikasi_rekening (id_verif, id_komunitas, nama_bank_baru, nomor_rekening_baru, foto_buku_rekening_url, alasan_perubahan) VALUES
(1, 1, 'BCA', '123', 'foto.png', 'Alasan'),
(2, 2, 'BCA', '123', 'foto.png', 'Alasan'),
(3, 3, 'BCA', '123', 'foto.png', 'Alasan'),
(4, 4, 'BCA', '123', 'foto.png', 'Alasan'),
(5, 5, 'BCA', '123', 'foto.png', 'Alasan'),
(6, 6, 'BCA', '123', 'foto.png', 'Alasan'),
(7, 7, 'BCA', '123', 'foto.png', 'Alasan'),
(8, 8, 'BCA', '123', 'foto.png', 'Alasan'),
(9, 9, 'BCA', '123', 'foto.png', 'Alasan'),
(10, 10, 'BCA', '123', 'foto.png', 'Alasan'),
(11, 11, 'BCA', '123', 'foto.png', 'Alasan'),
(12, 12, 'BCA', '123', 'foto.png', 'Alasan'),
(13, 13, 'BCA', '123', 'foto.png', 'Alasan'),
(14, 14, 'BCA', '123', 'foto.png', 'Alasan'),
(15, 15, 'BCA', '123', 'foto.png', 'Alasan');

SELECT setval('verifikasi_rekening_id_verif_seq', 15, true);

-- 17. follow_komunitas
INSERT INTO follow_komunitas (id_follow, id_user, id_komunitas) VALUES
(1, 17, 1),
(2, 18, 2),
(3, 19, 3),
(4, 20, 4),
(5, 21, 5),
(6, 22, 6),
(7, 23, 7),
(8, 24, 8),
(9, 25, 9),
(10, 26, 10),
(11, 27, 11),
(12, 28, 12),
(13, 29, 13),
(14, 30, 14),
(15, 31, 15);

SELECT setval('follow_komunitas_id_follow_seq', 15, true);

-- 18. notifikasi
INSERT INTO notifikasi (id_notif, id_penerima_user, judul, pesan, tipe, expires_at) VALUES
(1, 17, 'Judul', 'Pesan', 'INFO', '2026-12-31'),
(2, 18, 'Judul', 'Pesan', 'INFO', '2026-12-31'),
(3, 19, 'Judul', 'Pesan', 'INFO', '2026-12-31'),
(4, 20, 'Judul', 'Pesan', 'INFO', '2026-12-31'),
(5, 21, 'Judul', 'Pesan', 'INFO', '2026-12-31'),
(6, 22, 'Judul', 'Pesan', 'INFO', '2026-12-31'),
(7, 23, 'Judul', 'Pesan', 'INFO', '2026-12-31'),
(8, 24, 'Judul', 'Pesan', 'INFO', '2026-12-31'),
(9, 25, 'Judul', 'Pesan', 'INFO', '2026-12-31'),
(10, 26, 'Judul', 'Pesan', 'INFO', '2026-12-31'),
(11, 27, 'Judul', 'Pesan', 'INFO', '2026-12-31'),
(12, 28, 'Judul', 'Pesan', 'INFO', '2026-12-31'),
(13, 29, 'Judul', 'Pesan', 'INFO', '2026-12-31'),
(14, 30, 'Judul', 'Pesan', 'INFO', '2026-12-31'),
(15, 31, 'Judul', 'Pesan', 'INFO', '2026-12-31');

SELECT setval('notifikasi_id_notif_seq', 15, true);
