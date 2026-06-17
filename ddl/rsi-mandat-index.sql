-- ============================================================
-- INDEXES
-- ============================================================

-- users
CREATE INDEX idx_users_wilayah ON users(kode_wilayah);
CREATE INDEX idx_users_active ON users(is_active) WHERE deleted_at IS NULL;

-- komunitas
CREATE INDEX idx_komunitas_user ON komunitas(id_user);
CREATE INDEX idx_komunitas_status ON komunitas(status);
CREATE INDEX idx_komunitas_wilayah ON komunitas(kode_wilayah);
CREATE INDEX idx_komunitas_jenis ON komunitas(id_jenis_lembaga);

-- dokumen_komunitas
CREATE INDEX idx_dokumen_komunitas ON dokumen_komunitas(id_komunitas);
CREATE INDEX idx_dokumen_jenis_dok ON dokumen_komunitas(id_jenis_dok);
CREATE INDEX idx_dokumen_status ON dokumen_komunitas(status_verifikasi);
CREATE INDEX idx_dokumen_uploaded ON dokumen_komunitas(uploaded_at DESC);

-- campaign
CREATE INDEX idx_campaign_komunitas ON campaign(id_komunitas);
CREATE INDEX idx_campaign_kategori ON campaign(id_kategori);
CREATE INDEX idx_campaign_wilayah ON campaign(kode_wilayah);
CREATE INDEX idx_campaign_tipe ON campaign(tipe_distribusi);
CREATE INDEX idx_campaign_active ON campaign(status) WHERE status = 'aktif';

-- donasi
CREATE INDEX idx_donasi_user ON donasi(id_user);
CREATE INDEX idx_donasi_campaign ON donasi(id_campaign);
CREATE INDEX idx_donasi_status ON donasi(status_pembayaran);
CREATE INDEX idx_donasi_created ON donasi(created_at DESC);
CREATE INDEX idx_donasi_berhasil ON donasi(id_campaign, status_pembayaran) WHERE status_pembayaran = 'berhasil';

-- pencairan_dana
CREATE INDEX idx_pencairan_campaign ON pencairan_dana(id_campaign);
CREATE INDEX idx_pencairan_komunitas ON pencairan_dana(id_komunitas);
CREATE INDEX idx_pencairan_status ON pencairan_dana(status);
CREATE INDEX idx_pencairan_urutan ON pencairan_dana(id_campaign, urutan_ke);
CREATE INDEX idx_pencairan_laporan ON pencairan_dana(id_laporan_dana);

-- potongan_platform
CREATE INDEX idx_potongan_campaign ON potongan_platform(id_campaign);
CREATE INDEX idx_potongan_dipotong_pada ON potongan_platform(dipotong_pada DESC);

-- laporan_penggunaan_dana
CREATE INDEX idx_laporan_pencairan ON laporan_penggunaan_dana(id_pencairan);
CREATE INDEX idx_laporan_status ON laporan_penggunaan_dana(status_verifikasi);
CREATE INDEX idx_laporan_tanggal ON laporan_penggunaan_dana(tanggal_laporan DESC);
CREATE INDEX idx_laporan_pencairan_status ON laporan_penggunaan_dana(id_pencairan, status_verifikasi);
CREATE INDEX idx_laporan_verified ON laporan_penggunaan_dana(id_pencairan) WHERE status_verifikasi = 'diverifikasi';

-- penerima_manfaat
CREATE INDEX idx_penerima_laporan ON penerima_manfaat(id_laporan);
CREATE INDEX idx_penerima_wilayah ON penerima_manfaat(kode_wilayah);
CREATE INDEX idx_penerima_nama ON penerima_manfaat(nama);

-- update_campaign
CREATE INDEX idx_update_campaign ON update_campaign(id_campaign);
CREATE INDEX idx_update_komunitas ON update_campaign(id_komunitas);
CREATE INDEX idx_update_created ON update_campaign(created_at DESC);
CREATE INDEX idx_update_pinned ON update_campaign(is_pinned) WHERE is_pinned = TRUE;

-- foto_update
CREATE INDEX idx_foto_update ON foto_update(id_update);
CREATE INDEX idx_foto_urutan ON foto_update(id_update, urutan);

-- verifikasi_rekening
CREATE INDEX idx_verifikasi_komunitas ON verifikasi_rekening(id_komunitas);
CREATE INDEX idx_verifikasi_status ON verifikasi_rekening(status);
CREATE INDEX idx_verifikasi_direview ON verifikasi_rekening(direview_oleh);
CREATE INDEX idx_verifikasi_created ON verifikasi_rekening(created_at DESC);
CREATE UNIQUE INDEX idx_verifikasi_menunggu_per_komunitas ON verifikasi_rekening(id_komunitas) WHERE status = 'menunggu';

-- follow_komunitas
CREATE INDEX idx_follow_user ON follow_komunitas(id_user, is_active);
CREATE INDEX idx_follow_komunitas ON follow_komunitas(id_komunitas, is_active);
CREATE INDEX idx_follow_active ON follow_komunitas(is_active, followed_at DESC);
CREATE UNIQUE INDEX idx_follow_user_komunitas_active ON follow_komunitas(id_user, id_komunitas) WHERE is_active = TRUE;

-- notifikasi
CREATE INDEX idx_notif_user_active ON notifikasi(id_penerima_user, is_archived, is_read, created_at DESC) WHERE id_penerima_user IS NOT NULL;
CREATE INDEX idx_notif_komunitas_active ON notifikasi(id_penerima_komunitas, is_archived, is_read, created_at DESC) WHERE id_penerima_komunitas IS NOT NULL;
CREATE INDEX idx_notif_expires ON notifikasi(expires_at, is_archived) WHERE is_archived = FALSE;
CREATE INDEX idx_notif_tipe ON notifikasi(tipe, created_at DESC);
CREATE INDEX idx_notif_unread ON notifikasi(id_penerima_user, is_read) WHERE is_read = FALSE AND is_archived = FALSE;

-- token
CREATE INDEX idx_personal_access_tokens_tokenable_id ON personal_access_tokens(tokenable_id);
CREATE INDEX idx_personal_access_tokens_token ON personal_access_tokens(token);