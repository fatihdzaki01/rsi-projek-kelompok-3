-- ============================================================
-- DUMMY DATA GENERATOR UNTUK PROJEK MANDAT
-- Menambahkan data contoh untuk semua tabel referensi dan transaksi
-- ============================================================

-- 1. WILAYAH
INSERT INTO wilayah (kode, nama) VALUES 
('31', 'DKI Jakarta'),
('31.71', 'Jakarta Selatan'),
('31.72', 'Jakarta Timur'),
('32', 'Jawa Barat'),
('32.73', 'Kota Bandung');

-- 2. KATEGORI CAMPAIGN
INSERT INTO kategori_campaign (id_kategori, nama_kategori, deskripsi, is_active) VALUES 
(1, 'Pendidikan', 'Bantuan pendidikan, beasiswa, dan renovasi sekolah', TRUE),
(2, 'Kesehatan', 'Bantuan biaya pengobatan dan alat kesehatan', TRUE),
(3, 'Bencana Alam', 'Bantuan untuk korban bencana alam', TRUE),
(4, 'Sosial', 'Bantuan untuk panti asuhan dan kesejahteraan', TRUE);

-- 3. JENIS LEMBAGA
INSERT INTO jenis_lembaga (id_jenis, nama_jenis) VALUES 
(1, 'Yayasan'),
(2, 'Panti Asuhan'),
(3, 'Komunitas Sosial'),
(4, 'Lembaga Amil Zakat');

-- 4. JENIS DOKUMEN
INSERT INTO jenis_dokumen (id_jenis_dok, nama_dokumen, deskripsi, wajib_untuk_jenis_lembaga, is_opsional) VALUES 
(1, 'KTP Ketua', 'Scan KTP Ketua Lembaga yang masih berlaku', NULL, FALSE),
(2, 'Akta Notaris', 'Akta Pendirian Lembaga yang sah', '1', FALSE),
(3, 'NPWP Lembaga', 'NPWP Atas Nama Lembaga', NULL, FALSE);

-- 5. USERS (Password default adalah 'password' yang di-hash dengan Bcrypt bawaan Laravel)
INSERT INTO users (username, email, password_hash, role, is_active, is_verified, nama_lengkap, kode_wilayah) VALUES 
('admin_pusat', 'admin@mandat.com', '$2y$12$1g.miD7..6zqHOSJ24tqd.0J1.fsYhWvCahsZCDi89gEFvsd7Bzn2', 'SUPERADMIN', TRUE, TRUE, 'Super Admin Pusat', '31.71'),
('yayasan_peduli', 'kontak@yayasanpeduli.com', '$2y$12$1g.miD7..6zqHOSJ24tqd.0J1.fsYhWvCahsZCDi89gEFvsd7Bzn2', 'KOMUNITAS', TRUE, TRUE, 'Budi Santoso', '31.71'),
('donatur_baik', 'donatur1@gmail.com', '$2y$12$1g.miD7..6zqHOSJ24tqd.0J1.fsYhWvCahsZCDi89gEFvsd7Bzn2', 'DONATUR', TRUE, TRUE, 'Hamba Allah', '32.73');

-- 6. KOMUNITAS
-- Asumsi yayasan_peduli mendapat id_user = 2
INSERT INTO komunitas (id_user, id_jenis_lembaga, nama_lembaga, deskripsi, kode_wilayah, alamat_detail, nomor_kontak, foto_lembaga_url, nama_bank, nomor_rekening, foto_buku_rekening_url, status, direview_oleh) VALUES 
(2, 1, 'Yayasan Peduli Anak Bangsa', 'Yayasan yang fokus pada pendidikan anak yatim dhuafa', '31.71', 'Jl. Sudirman No 123, Jakarta Selatan', '081234567890', 'https://placehold.co/600x400/png?text=Foto+Lembaga', 'BCA', '1234567890', 'https://placehold.co/600x400/png?text=Buku+Rekening', 'aktif', 1);

-- 7. DOKUMEN KOMUNITAS
-- Asumsi id_komunitas = 1
INSERT INTO dokumen_komunitas (id_komunitas, id_jenis_dok, file_url, status_verifikasi, diverifikasi_oleh, verified_at) VALUES 
(1, 1, 'https://placehold.co/600x400/png?text=KTP+Ketua', 'diverifikasi', 1, NOW()),
(1, 2, 'https://placehold.co/600x400/png?text=Akta+Notaris', 'diverifikasi', 1, NOW()),
(1, 3, 'https://placehold.co/600x400/png?text=NPWP+Lembaga', 'diverifikasi', 1, NOW());

-- 8. CAMPAIGN
-- Asumsi id_komunitas = 1
INSERT INTO campaign (id_komunitas, id_kategori, kode_wilayah, judul, deskripsi, foto_campaign_url, target_dana, dana_terkumpul, saldo_tersedia, saldo_terkunci, tipe_distribusi, target_audiens, total_penerima_manfaat, tanggal_mulai, tanggal_selesai, status, direview_oleh) VALUES 
(1, 1, '31.71', 'Bantu 50 Anak Panti Asuhan Tetap Sekolah', 'Banyak anak di panti asuhan kami yang terancam putus sekolah karena kendala biaya.', 'https://placehold.co/800x400/png?text=Campaign+Pendidikan', 50000000, 1500000, 1500000, 0, 'kolektif', NULL, 50, CURRENT_DATE, CURRENT_DATE + INTERVAL '30 days', 'aktif', 1),
(1, 2, '31.71', 'Biaya Operasi Jantung Dek Nanda', 'Nanda membutuhkan operasi jantung segera.', 'https://placehold.co/800x400/png?text=Campaign+Kesehatan', 100000000, 0, 0, 0, 'individual', 'Anak Nanda', 1, CURRENT_DATE, CURRENT_DATE + INTERVAL '60 days', 'aktif', 1);

-- 9. DONASI
-- Asumsi id_user = 3 (donatur), id_campaign = 1
INSERT INTO donasi (id_user, id_campaign, nominal, metode_pembayaran, nama_tampil, is_anonim, status_pembayaran, bukti_pdf_url) VALUES 
(3, 1, 1000000, 'qris', NULL, TRUE, 'berhasil', 'https://example.com/receipt/donasi1.pdf'),
(3, 1, 500000, 'bca', 'Keluarga Bpk. Budi', FALSE, 'berhasil', 'https://example.com/receipt/donasi2.pdf');

-- 10. FOLLOW KOMUNITAS
INSERT INTO follow_komunitas (id_user, id_komunitas, is_active) VALUES 
(3, 1, TRUE);

-- 11. UPDATE CAMPAIGN
INSERT INTO update_campaign (id_campaign, id_komunitas, judul_update, konten, is_pinned) VALUES 
(1, 1, 'Terima kasih Donatur!', 'Dana yang terkumpul sejauh ini sangat membantu pendaftaran ulang 5 anak asuh kami.', TRUE);

-- 12. FOTO UPDATE
INSERT INTO foto_update (id_update, foto_url, caption, urutan) VALUES 
(1, 'https://placehold.co/400x300/png?text=Update+Foto+1', 'Anak-anak sedang belajar bersama', 1);

-- 13. NOTIFIKASI
INSERT INTO notifikasi (id_penerima_komunitas, id_pengirim_user, judul, pesan, tipe, related_donasi_id, related_campaign_id, expires_at) VALUES 
(1, 3, 'Donasi Baru', 'Anda menerima donasi sebesar Rp1.000.000', 'donasi', 1, 1, NOW() + INTERVAL '60 days');

-- 14. PENCAIRAN DANA
INSERT INTO pencairan_dana (id_campaign, id_komunitas, urutan_ke, nominal_diajukan, nominal_disetujui, keterangan, url_proposal, nama_bank_tujuan, nomor_rekening_tujuan, status, direview_oleh, tanggal_keputusan) VALUES 
(1, 1, 1, 500000, 500000, 'Pencairan tahap 1 untuk pendaftaran awal sekolah', 'https://example.com/proposal.pdf', 'BCA', '1234567890', 'disetujui', 1, NOW());

-- Update saldo terkunci pada campaign (karena dicairkan)
UPDATE campaign SET saldo_tersedia = saldo_tersedia - 500000, saldo_terkunci = saldo_terkunci + 500000 WHERE id_campaign = 1;

-- 15. LAPORAN PENGGUNAAN DANA
INSERT INTO laporan_penggunaan_dana (id_pencairan, jumlah_penerima, deskripsi_penggunaan, total_realisasi, file_dokumentasi_url, status_verifikasi, diverifikasi_oleh, tanggal_verifikasi) VALUES 
(1, 5, 'Pembayaran SPP bulan pertama untuk 5 anak panti asuhan', 500000, 'https://example.com/laporan_bukti.pdf', 'diverifikasi', 1, NOW());

-- 16. PENERIMA MANFAAT (Contoh untuk campaign individual / Jika diperlukan)
INSERT INTO penerima_manfaat (id_laporan, nama, nik, kode_wilayah, nominal_diterima) VALUES 
(1, 'Agus', '1234567890123456', '31.71', 100000),
(1, 'Siti', '1234567890123457', '31.71', 100000),
(1, 'Rudi', '1234567890123458', '31.71', 100000),
(1, 'Rina', '1234567890123459', '31.71', 100000),
(1, 'Doni', '1234567890123450', '31.71', 100000);

-- Catatan:
-- Tabel potongan_platform dan verifikasi_rekening tidak diisi agar flow-nya bisa ditest manual dari aplikasi.
