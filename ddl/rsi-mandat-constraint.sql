-- CONSTRAINTS (PK, FK, UNIQUE, CHECK)

-- PRIMARY KEYS
ALTER TABLE wilayah ADD CONSTRAINT pk_wilayah PRIMARY KEY (kode);
ALTER TABLE kategori_campaign ADD CONSTRAINT pk_kategori_campaign PRIMARY KEY (id_kategori);
ALTER TABLE jenis_lembaga ADD CONSTRAINT pk_jenis_lembaga PRIMARY KEY (id_jenis);
ALTER TABLE jenis_dokumen ADD CONSTRAINT pk_jenis_dokumen PRIMARY KEY (id_jenis_dok);
ALTER TABLE users ADD CONSTRAINT pk_users PRIMARY KEY (id_user);
ALTER TABLE komunitas ADD CONSTRAINT pk_komunitas PRIMARY KEY (id_komunitas);
ALTER TABLE dokumen_komunitas ADD CONSTRAINT pk_dokumen_komunitas PRIMARY KEY (id_dokumen);
ALTER TABLE campaign ADD CONSTRAINT pk_campaign PRIMARY KEY (id_campaign);
ALTER TABLE donasi ADD CONSTRAINT pk_donasi PRIMARY KEY (id_donasi);
ALTER TABLE pencairan_dana ADD CONSTRAINT pk_pencairan_dana PRIMARY KEY (id_pencairan);
ALTER TABLE potongan_platform ADD CONSTRAINT pk_potongan_platform PRIMARY KEY (id_potongan);
ALTER TABLE laporan_penggunaan_dana ADD CONSTRAINT pk_laporan_penggunaan_dana PRIMARY KEY (id_laporan);
ALTER TABLE penerima_manfaat ADD CONSTRAINT pk_penerima_manfaat PRIMARY KEY (id_penerima);
ALTER TABLE update_campaign ADD CONSTRAINT pk_update_campaign PRIMARY KEY (id_update);
ALTER TABLE foto_update ADD CONSTRAINT pk_foto_update PRIMARY KEY (id_foto);
ALTER TABLE verifikasi_rekening ADD CONSTRAINT pk_verifikasi_rekening PRIMARY KEY (id_verif);
ALTER TABLE follow_komunitas ADD CONSTRAINT pk_follow_komunitas PRIMARY KEY (id_follow);
ALTER TABLE notifikasi ADD CONSTRAINT pk_notifikasi PRIMARY KEY (id_notif);

-- UNIQUE CONSTRAINTS
ALTER TABLE users ADD CONSTRAINT uq_users_username UNIQUE (username);
ALTER TABLE users ADD CONSTRAINT uq_users_email UNIQUE (email);
ALTER TABLE komunitas ADD CONSTRAINT uq_komunitas_id_user UNIQUE (id_user);
ALTER TABLE dokumen_komunitas ADD CONSTRAINT uq_komunitas_jenis_dok UNIQUE (id_komunitas, id_jenis_dok);
ALTER TABLE pencairan_dana ADD CONSTRAINT uq_campaign_urutan UNIQUE (id_campaign, urutan_ke);
ALTER TABLE potongan_platform ADD CONSTRAINT uq_potongan_campaign UNIQUE (id_campaign);
ALTER TABLE foto_update ADD CONSTRAINT uq_update_urutan UNIQUE (id_update, urutan);
ALTER TABLE follow_komunitas ADD CONSTRAINT uq_follow_user_komunitas UNIQUE (id_user, id_komunitas);

-- FOREIGN KEYS
ALTER TABLE users ADD CONSTRAINT fk_users_wilayah FOREIGN KEY (kode_wilayah) REFERENCES wilayah(kode) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE komunitas ADD CONSTRAINT fk_komunitas_user FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE komunitas ADD CONSTRAINT fk_komunitas_jenis_lembaga FOREIGN KEY (id_jenis_lembaga) REFERENCES jenis_lembaga(id_jenis) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE komunitas ADD CONSTRAINT fk_komunitas_wilayah FOREIGN KEY (kode_wilayah) REFERENCES wilayah(kode) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE komunitas ADD CONSTRAINT fk_komunitas_direview_oleh FOREIGN KEY (direview_oleh) REFERENCES users(id_user) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE dokumen_komunitas ADD CONSTRAINT fk_dokumen_komunitas FOREIGN KEY (id_komunitas) REFERENCES komunitas(id_komunitas) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE dokumen_komunitas ADD CONSTRAINT fk_dokumen_jenis_dok FOREIGN KEY (id_jenis_dok) REFERENCES jenis_dokumen(id_jenis_dok) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE dokumen_komunitas ADD CONSTRAINT fk_dokumen_diverifikasi_oleh FOREIGN KEY (diverifikasi_oleh) REFERENCES users(id_user) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE campaign ADD CONSTRAINT fk_campaign_komunitas FOREIGN KEY (id_komunitas) REFERENCES komunitas(id_komunitas) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE campaign ADD CONSTRAINT fk_campaign_kategori FOREIGN KEY (id_kategori) REFERENCES kategori_campaign(id_kategori) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE campaign ADD CONSTRAINT fk_campaign_wilayah FOREIGN KEY (kode_wilayah) REFERENCES wilayah(kode) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE campaign ADD CONSTRAINT fk_campaign_direview_oleh FOREIGN KEY (direview_oleh) REFERENCES users(id_user) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE donasi ADD CONSTRAINT fk_donasi_user FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE donasi ADD CONSTRAINT fk_donasi_campaign FOREIGN KEY (id_campaign) REFERENCES campaign(id_campaign) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE pencairan_dana ADD CONSTRAINT fk_pencairan_campaign FOREIGN KEY (id_campaign) REFERENCES campaign(id_campaign) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE pencairan_dana ADD CONSTRAINT fk_pencairan_komunitas FOREIGN KEY (id_komunitas) REFERENCES komunitas(id_komunitas) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE pencairan_dana ADD CONSTRAINT fk_pencairan_direview_oleh FOREIGN KEY (direview_oleh) REFERENCES users(id_user) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE pencairan_dana ADD CONSTRAINT fk_pencairan_laporan FOREIGN KEY (id_laporan_dana) REFERENCES laporan_penggunaan_dana(id_laporan) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE potongan_platform ADD CONSTRAINT fk_potongan_campaign FOREIGN KEY (id_campaign) REFERENCES campaign(id_campaign) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE potongan_platform ADD CONSTRAINT fk_potongan_dipotong_oleh FOREIGN KEY (dipotong_oleh) REFERENCES users(id_user) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE laporan_penggunaan_dana ADD CONSTRAINT fk_laporan_pencairan FOREIGN KEY (id_pencairan) REFERENCES pencairan_dana(id_pencairan) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE laporan_penggunaan_dana ADD CONSTRAINT fk_laporan_diverifikasi_oleh FOREIGN KEY (diverifikasi_oleh) REFERENCES users(id_user) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE penerima_manfaat ADD CONSTRAINT fk_penerima_laporan FOREIGN KEY (id_laporan) REFERENCES laporan_penggunaan_dana(id_laporan) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE penerima_manfaat ADD CONSTRAINT fk_penerima_wilayah FOREIGN KEY (kode_wilayah) REFERENCES wilayah(kode) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE update_campaign ADD CONSTRAINT fk_update_campaign FOREIGN KEY (id_campaign) REFERENCES campaign(id_campaign) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE update_campaign ADD CONSTRAINT fk_update_komunitas FOREIGN KEY (id_komunitas) REFERENCES komunitas(id_komunitas) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE foto_update ADD CONSTRAINT fk_foto_update FOREIGN KEY (id_update) REFERENCES update_campaign(id_update) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE verifikasi_rekening ADD CONSTRAINT fk_verifikasi_komunitas FOREIGN KEY (id_komunitas) REFERENCES komunitas(id_komunitas) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE verifikasi_rekening ADD CONSTRAINT fk_verifikasi_direview_oleh FOREIGN KEY (direview_oleh) REFERENCES users(id_user) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE follow_komunitas ADD CONSTRAINT fk_follow_user FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE follow_komunitas ADD CONSTRAINT fk_follow_komunitas FOREIGN KEY (id_komunitas) REFERENCES komunitas(id_komunitas) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE notifikasi ADD CONSTRAINT fk_notif_penerima_user FOREIGN KEY (id_penerima_user) REFERENCES users(id_user) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE notifikasi ADD CONSTRAINT fk_notif_penerima_komunitas FOREIGN KEY (id_penerima_komunitas) REFERENCES komunitas(id_komunitas) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE notifikasi ADD CONSTRAINT fk_notif_pengirim_user FOREIGN KEY (id_pengirim_user) REFERENCES users(id_user) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE notifikasi ADD CONSTRAINT fk_notif_campaign FOREIGN KEY (related_campaign_id) REFERENCES campaign(id_campaign) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE notifikasi ADD CONSTRAINT fk_notif_donasi FOREIGN KEY (related_donasi_id) REFERENCES donasi(id_donasi) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE notifikasi ADD CONSTRAINT fk_notif_update FOREIGN KEY (related_update_id) REFERENCES update_campaign(id_update) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE notifikasi ADD CONSTRAINT fk_notif_verifikasi FOREIGN KEY (related_verifikasi_id) REFERENCES verifikasi_rekening(id_verif) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE notifikasi ADD CONSTRAINT fk_notif_pencairan FOREIGN KEY (related_pencairan_id) REFERENCES pencairan_dana(id_pencairan) ON DELETE SET NULL ON UPDATE CASCADE;

-- CHECK CONSTRAINTS

-- users
ALTER TABLE users ADD CONSTRAINT chk_role_valid CHECK (role IN ('DONATUR', 'KOMUNITAS', 'SUPERADMIN'));
ALTER TABLE users ADD CONSTRAINT chk_jenis_kelamin_valid CHECK (jenis_kelamin IN ('L', 'P') OR jenis_kelamin IS NULL);
ALTER TABLE users ADD CONSTRAINT chk_tanggal_lahir_valid CHECK (tanggal_lahir IS NULL OR tanggal_lahir < CURRENT_DATE);

-- komunitas

ALTER TABLE komunitas ADD CONSTRAINT chk_alasan_penolakan CHECK (
    (status = 'ditolak' AND alasan_penolakan IS NOT NULL AND TRIM(alasan_penolakan) != '') OR
    (status != 'ditolak')
);
ALTER TABLE komunitas ADD CONSTRAINT chk_direview_oleh CHECK (
    (status IN ('aktif', 'ditolak', 'dinonaktifkan') AND direview_oleh IS NOT NULL) OR
    (status = 'menunggu')
);

-- dokumen_komunitas
ALTER TABLE dokumen_komunitas ADD CONSTRAINT chk_file_url_not_empty CHECK (TRIM(file_url) != '');
ALTER TABLE dokumen_komunitas ADD CONSTRAINT chk_alasan_penolakan CHECK (
    (status_verifikasi = 'ditolak' AND alasan_penolakan IS NOT NULL AND TRIM(alasan_penolakan) != '') OR
    (status_verifikasi != 'ditolak')
);
ALTER TABLE dokumen_komunitas ADD CONSTRAINT chk_verified_at CHECK (
    (status_verifikasi IN ('diverifikasi', 'ditolak') AND verified_at IS NOT NULL) OR
    (status_verifikasi = 'menunggu')
);
ALTER TABLE dokumen_komunitas ADD CONSTRAINT chk_diverifikasi_oleh CHECK (
    (status_verifikasi IN ('diverifikasi', 'ditolak') AND diverifikasi_oleh IS NOT NULL) OR
    (status_verifikasi = 'menunggu')
);

-- campaign
ALTER TABLE campaign ADD CONSTRAINT chk_target_dana_minimal CHECK (target_dana > 0);
ALTER TABLE campaign ADD CONSTRAINT chk_dana_terkumpul_positif CHECK (dana_terkumpul >= 0);
ALTER TABLE campaign ADD CONSTRAINT chk_saldo_positif CHECK (saldo_tersedia >= 0 AND saldo_terkunci >= 0);
ALTER TABLE campaign ADD CONSTRAINT chk_total_penerima_positif CHECK (total_penerima_manfaat >= 0);
ALTER TABLE campaign ADD CONSTRAINT chk_jumlah_pencairan_positif CHECK (jumlah_pencairan_approve >= 0);
ALTER TABLE campaign ADD CONSTRAINT chk_dana_terkumpul_max_120pct CHECK (dana_terkumpul <= target_dana * 1.2);
ALTER TABLE campaign ADD CONSTRAINT chk_saldo_valid CHECK (saldo_tersedia >= 0 AND saldo_terkunci >= 0 AND saldo_tersedia + saldo_terkunci <= dana_terkumpul);
ALTER TABLE campaign ADD CONSTRAINT chk_tipe_distribusi_valid CHECK (tipe_distribusi IN ('individual', 'kolektif'));
ALTER TABLE campaign ADD CONSTRAINT chk_target_audiens_individual CHECK ( ( tipe_distribusi = 'individual' AND target_audiens IS NOT NULL AND TRIM(target_audiens) <> '') OR tipe_distribusi = 'kolektif' );
ALTER TABLE campaign ADD CONSTRAINT chk_status_valid CHECK (status IN ('menunggu_review', 'aktif', 'selesai', 'ditolak', 'nonaktif', 'ditutup_permanen'));
ALTER TABLE campaign ADD CONSTRAINT chk_alasan_penolakan CHECK (
    (status = 'ditolak' AND alasan_penolakan IS NOT NULL AND TRIM(alasan_penolakan) != '') OR
    (status != 'ditolak')
);
ALTER TABLE campaign ADD CONSTRAINT chk_tanggal_valid CHECK (tanggal_selesai IS NULL OR tanggal_mulai IS NULL OR tanggal_selesai >= tanggal_mulai);

-- donasi
ALTER TABLE donasi ADD CONSTRAINT chk_nominal_minimum CHECK (nominal >= 5000);
ALTER TABLE donasi ADD CONSTRAINT chk_metode_pembayaran_valid CHECK (metode_pembayaran IN ('qris', 'gopay', 'ovo', 'shopeepay', 'bca', 'mandiri', 'bni'));
ALTER TABLE donasi ADD CONSTRAINT chk_nama_tampil_anonim CHECK ((is_anonim = TRUE AND nama_tampil IS NULL)OR( is_anonim = FALSE AND nama_tampil IS NOT NULL AND TRIM(nama_tampil) <> ''));

-- pencairan_dana
ALTER TABLE pencairan_dana ADD CONSTRAINT chk_urutan_valid CHECK (urutan_ke BETWEEN 1 AND 5);
ALTER TABLE pencairan_dana ADD CONSTRAINT chk_nominal_diajukan_positif CHECK (nominal_diajukan > 0);
ALTER TABLE pencairan_dana ADD CONSTRAINT chk_nominal_disetujui_positif CHECK (nominal_disetujui IS NULL OR nominal_disetujui > 0);
ALTER TABLE pencairan_dana ADD CONSTRAINT chk_nominal_disetujui_max CHECK (nominal_disetujui IS NULL OR nominal_disetujui <= nominal_diajukan);
ALTER TABLE pencairan_dana ADD CONSTRAINT chk_status_valid CHECK (status IN ('menunggu_review', 'disetujui', 'ditolak', 'selesai'));
ALTER TABLE pencairan_dana ADD CONSTRAINT chk_bukti_transfer_selesai CHECK ((status = 'selesai' AND bukti_transfer_url IS NOT NULL) OR (status IN ('menunggu_review', 'disetujui', 'ditolak')));
ALTER TABLE pencairan_dana ADD CONSTRAINT chk_alasan_penolakan_ditolak CHECK ((status = 'ditolak' AND alasan_penolakan IS NOT NULL AND TRIM(alasan_penolakan) != '') OR (status IN ('menunggu_review', 'disetujui', 'selesai')));
ALTER TABLE pencairan_dana ADD CONSTRAINT chk_nominal_disetujui_wajib CHECK ((status IN ('disetujui', 'selesai') AND nominal_disetujui IS NOT NULL) OR (status IN ('menunggu_review', 'ditolak')));
ALTER TABLE pencairan_dana ADD CONSTRAINT chk_tanggal_keputusan CHECK ((status IN ('disetujui', 'ditolak', 'selesai') AND tanggal_keputusan IS NOT NULL) OR (status = 'menunggu_review'));

-- potongan_platform
ALTER TABLE potongan_platform ADD CONSTRAINT chk_total_dana_positif CHECK (total_dana_terkumpul > 0);
ALTER TABLE potongan_platform ADD CONSTRAINT chk_persentase_valid CHECK (persentase_potongan >= 0 AND persentase_potongan <= 100);
ALTER TABLE potongan_platform ADD CONSTRAINT chk_nominal_positif CHECK (nominal_potongan >= 0);
ALTER TABLE potongan_platform ADD CONSTRAINT chk_nominal_calculation_with_cap CHECK ((nominal_potongan <= (total_dana_terkumpul * persentase_potongan / 100) + 1) AND (nominal_potongan <= 50000));
ALTER TABLE potongan_platform ADD CONSTRAINT chk_dipotong_pada_valid CHECK (dipotong_pada <= NOW());

-- laporan_penggunaan_dana
ALTER TABLE laporan_penggunaan_dana ADD CONSTRAINT chk_jumlah_penerima_positif CHECK (jumlah_penerima >= 0);
ALTER TABLE laporan_penggunaan_dana ADD CONSTRAINT chk_total_realisasi_positif CHECK (total_realisasi >= 0);
ALTER TABLE laporan_penggunaan_dana ADD CONSTRAINT chk_alasan_penolakan CHECK ((status_verifikasi = 'ditolak' AND alasan_penolakan IS NOT NULL AND TRIM(alasan_penolakan) != '') OR (status_verifikasi != 'ditolak'));
ALTER TABLE laporan_penggunaan_dana ADD CONSTRAINT chk_tanggal_laporan_valid CHECK (tanggal_laporan <= NOW());
ALTER TABLE laporan_penggunaan_dana ADD CONSTRAINT chk_tanggal_verifikasi CHECK ((status_verifikasi IN ('diverifikasi', 'ditolak') AND tanggal_verifikasi IS NOT NULL) OR (status_verifikasi = 'menunggu_verifikasi'));
ALTER TABLE laporan_penggunaan_dana ADD CONSTRAINT chk_diverifikasi_oleh CHECK ((status_verifikasi IN ('diverifikasi', 'ditolak') AND diverifikasi_oleh IS NOT NULL) OR (status_verifikasi = 'menunggu_verifikasi'));

-- penerima_manfaat
ALTER TABLE penerima_manfaat ADD CONSTRAINT chk_nik_format CHECK (nik ~ '^[0-9]{16}$');
ALTER TABLE penerima_manfaat ADD CONSTRAINT chk_nominal_positif CHECK (nominal_diterima > 0);

-- update_campaign
ALTER TABLE update_campaign ADD CONSTRAINT chk_judul_not_empty CHECK (TRIM(judul_update) != '');
ALTER TABLE update_campaign ADD CONSTRAINT chk_konten_not_empty CHECK (TRIM(konten) != '');
ALTER TABLE update_campaign ADD CONSTRAINT chk_timestamps_valid CHECK (updated_at >= created_at);

-- foto_update
ALTER TABLE foto_update ADD CONSTRAINT chk_foto_url_not_empty CHECK (TRIM(foto_url) != '');
ALTER TABLE foto_update ADD CONSTRAINT chk_urutan_positif CHECK (urutan > 0);

-- verifikasi_rekening
ALTER TABLE verifikasi_rekening ADD CONSTRAINT chk_nama_bank_not_empty CHECK (TRIM(nama_bank_baru) != '');
ALTER TABLE verifikasi_rekening ADD CONSTRAINT chk_nomor_rekening_not_empty CHECK (TRIM(nomor_rekening_baru) != '');
ALTER TABLE verifikasi_rekening ADD CONSTRAINT chk_foto_buku_rekening_not_empty CHECK (TRIM(foto_buku_rekening_url) != '');
ALTER TABLE verifikasi_rekening ADD CONSTRAINT chk_alasan_perubahan_not_empty CHECK (TRIM(alasan_perubahan) != '');
ALTER TABLE verifikasi_rekening ADD CONSTRAINT chk_alasan_penolakan CHECK ((status = 'ditolak' AND alasan_penolakan IS NOT NULL AND TRIM(alasan_penolakan) != '') OR (status != 'ditolak'));
ALTER TABLE verifikasi_rekening ADD CONSTRAINT chk_direview_oleh CHECK ((status IN ('disetujui', 'ditolak') AND direview_oleh IS NOT NULL) OR (status = 'menunggu'));
ALTER TABLE verifikasi_rekening ADD CONSTRAINT chk_tanggal_keputusan CHECK ((status IN ('disetujui', 'ditolak') AND tanggal_keputusan IS NOT NULL) OR (status = 'menunggu'));

-- follow_komunitas
ALTER TABLE follow_komunitas ADD CONSTRAINT chk_unfollowed_at_requires_inactive CHECK ((is_active = FALSE AND unfollowed_at IS NOT NULL) OR (is_active = TRUE AND unfollowed_at IS NULL));
ALTER TABLE follow_komunitas ADD CONSTRAINT chk_unfollow_after_follow CHECK (unfollowed_at IS NULL OR unfollowed_at >= followed_at);

-- notifikasi
ALTER TABLE notifikasi ADD CONSTRAINT chk_penerima_xor CHECK ((id_penerima_user IS NOT NULL AND id_penerima_komunitas IS NULL) OR (id_penerima_user IS NULL AND id_penerima_komunitas IS NOT NULL));
ALTER TABLE notifikasi ADD CONSTRAINT chk_read_at_requires_is_read CHECK ((is_read = TRUE AND read_at IS NOT NULL) OR (is_read = FALSE AND read_at IS NULL));
ALTER TABLE notifikasi ADD CONSTRAINT chk_archived_at_requires_is_archived CHECK ((is_archived = TRUE AND archived_at IS NOT NULL) OR (is_archived = FALSE AND archived_at IS NULL));
ALTER TABLE notifikasi ADD CONSTRAINT chk_expires_after_created CHECK (expires_at >= created_at);
ALTER TABLE notifikasi ADD CONSTRAINT chk_read_after_created CHECK (read_at IS NULL OR read_at >= created_at);
ALTER TABLE notifikasi ADD CONSTRAINT chk_archived_after_created CHECK (archived_at IS NULL OR archived_at >= created_at);
ALTER TABLE notifikasi ADD CONSTRAINT chk_donasi_requires_donasi_id CHECK (tipe != 'donasi' OR related_donasi_id IS NOT NULL);
ALTER TABLE notifikasi ADD CONSTRAINT chk_follow_requires_pengirim CHECK (tipe != 'follow' OR id_pengirim_user IS NOT NULL);
ALTER TABLE notifikasi ADD CONSTRAINT chk_pencairan_requires_pencairan_id CHECK (tipe NOT IN ('pencairan', 'withdrawal') OR related_pencairan_id IS NOT NULL);
ALTER TABLE notifikasi ADD CONSTRAINT chk_update_requires_update_id CHECK (tipe != 'update_campaign' OR related_update_id IS NOT NULL);
ALTER TABLE notifikasi ADD CONSTRAINT chk_verifikasi_requires_verifikasi_id CHECK (tipe != 'verifikasi' OR related_verifikasi_id IS NOT NULL);
ALTER TABLE notifikasi ADD CONSTRAINT chk_campaign_requires_campaign_id CHECK (tipe != 'campaign' OR related_campaign_id IS NOT NULL);

