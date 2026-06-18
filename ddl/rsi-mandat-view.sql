-- ============================================================
-- VIEWS
-- ============================================================

-- Public campaign detail view
CREATE OR REPLACE VIEW v_campaign_public_detail AS
SELECT
    c.id_campaign,
    c.judul,
    c.deskripsi,
    c.foto_campaign_url,
    c.target_dana,
    c.dana_terkumpul,
    c.target_audiens,
    c.tanggal_mulai,
    c.tanggal_selesai,
    c.status,
    k.id_komunitas,
    k.nama_lembaga,
    k.foto_lembaga_url,
    kc.nama_kategori,
    w.nama AS lokasi,
    (c.dana_terkumpul::FLOAT / NULLIF(c.target_dana,0)::FLOAT) * 100 AS persentase_pencapaian,
    (SELECT COUNT(DISTINCT id_user) FROM donasi WHERE id_campaign = c.id_campaign AND status_pembayaran = 'berhasil') AS jumlah_donatur
FROM campaign c
JOIN komunitas k ON c.id_komunitas = k.id_komunitas
JOIN kategori_campaign kc ON c.id_kategori = kc.id_kategori
JOIN wilayah w ON c.kode_wilayah = w.kode
WHERE c.status IN ('aktif', 'selesai');

-- Internal campaign detail (for community)
CREATE OR REPLACE VIEW v_campaign_internal_detail AS
SELECT
    c.id_campaign,
    c.id_komunitas,
    c.judul,
    c.status,
    c.target_dana,
    c.dana_terkumpul AS total_dana_masuk,
    c.saldo_tersedia + c.saldo_terkunci AS saldo_tersisa,

    (
        SELECT COALESCE(SUM(nominal_disetujui), 0)
        FROM pencairan_dana
        WHERE id_campaign = c.id_campaign
          AND status = 'selesai'
    ) AS total_dana_dicairkan,

    COALESCE(pp.nominal_potongan, 0) AS potongan_platform,

    c.url_rab AS dokumen_rab

FROM campaign c
LEFT JOIN potongan_platform pp
    ON c.id_campaign = pp.id_campaign;

-- Community profile view (active only)
CREATE OR REPLACE VIEW v_community_profile AS
SELECT
    k.id_komunitas,
    k.nama_lembaga,
    k.deskripsi,
    k.foto_lembaga_url,
    k.alamat_detail,
    k.nomor_kontak,
    k.created_at AS tanggal_bergabung,
    (SELECT COUNT(*) FROM follow_komunitas WHERE id_komunitas = k.id_komunitas AND is_active = TRUE) AS total_follower,
    (SELECT COALESCE(SUM(dana_terkumpul), 0) FROM campaign WHERE id_komunitas = k.id_komunitas AND status IN ('aktif', 'selesai')) AS total_dana_diterima,
    (SELECT COUNT(*) FROM campaign WHERE id_komunitas = k.id_komunitas AND status = 'aktif') AS total_campaign_aktif,
    (SELECT COUNT(*) FROM campaign WHERE id_komunitas = k.id_komunitas AND status = 'selesai') AS total_campaign_selesai
FROM komunitas k
WHERE k.status = 'aktif';

-- Community dashboard statistics
CREATE OR REPLACE VIEW v_community_dashboard_stats AS
SELECT
    k.id_komunitas,
    COUNT(c.id_campaign) FILTER (WHERE c.status = 'aktif') AS total_campaign_aktif,
    COUNT(c.id_campaign) FILTER (WHERE c.status = 'selesai') AS total_campaign_selesai,
    COUNT(c.id_campaign) FILTER (WHERE c.status = 'menunggu_review') AS total_campaign_review,
    COUNT(c.id_campaign) FILTER (WHERE c.status = 'ditolak') AS total_campaign_ditolak,
    COALESCE(SUM(c.dana_terkumpul), 0) AS total_dana_terkumpul,
    COALESCE(SUM(c.saldo_tersedia + c.saldo_terkunci), 0) AS total_saldo_tersisa,
    (SELECT COUNT(DISTINCT id_user)
     FROM donasi d
     JOIN campaign cmp ON d.id_campaign = cmp.id_campaign
     WHERE cmp.id_komunitas = k.id_komunitas AND d.status_pembayaran = 'berhasil') AS total_donatur_unik
FROM komunitas k
LEFT JOIN campaign c ON k.id_komunitas = c.id_komunitas
GROUP BY k.id_komunitas;

-- User profile view
CREATE OR REPLACE VIEW v_user_profile AS
SELECT
    u.id_user,
    u.username,
    u.email,
    u.role,
    u.is_active,
    u.is_verified,
    u.foto_profil_url,
    u.nama_lengkap,
    u.nomor_telepon,
    u.jenis_kelamin,
    u.tanggal_lahir,
    u.kode_wilayah,
    w.nama AS nama_wilayah,
    u.created_at,
    u.updated_at
FROM users u
LEFT JOIN wilayah w ON u.kode_wilayah = w.kode
WHERE u.deleted_at IS NULL;

-- User authentication identity view
CREATE OR REPLACE VIEW v_user_auth_identity AS
SELECT
    u.id_user,
    u.username,
    u.email,
    u.password_hash,
    u.role,
    u.is_active,
    u.is_verified,
    u.deleted_at
FROM users u
WHERE u.deleted_at IS NULL;

-- Donor list (users with role 'DONATUR')
CREATE OR REPLACE VIEW v_donor_list AS
SELECT
    u.id_user,
    u.username,
    u.nama_lengkap,
    u.email,
    u.foto_profil_url,
    u.nomor_telepon,
    u.created_at AS tanggal_bergabung,
    u.is_active,
    u.is_verified,
    COUNT(d.id_donasi) AS total_transaksi_donasi,
    COALESCE(SUM(d.nominal), 0) AS total_nominal_donasi
FROM users u
LEFT JOIN donasi d ON d.id_user = u.id_user AND d.status_pembayaran = 'berhasil'
WHERE u.role = 'DONATUR'
GROUP BY u.id_user, u.username, u.nama_lengkap, u.email, u.foto_profil_url, u.nomor_telepon, u.created_at, u.is_active, u.is_verified;

CREATE OR REPLACE VIEW v_community_admin_list AS
SELECT
    k.id_komunitas,
    k.nama_lembaga,
    jl.nama_jenis AS jenis_lembaga,
    u.email,
    k.nomor_kontak,
    k.status AS status_komunitas,
    k.created_at AS tanggal_bergabung,
    COUNT(DISTINCT c.id_campaign) AS total_campaign,
    COUNT(DISTINCT CASE
        WHEN c.status = 'aktif'
        THEN c.id_campaign
    END) AS total_campaign_aktif,
    COALESCE(SUM(d.nominal), 0) AS total_donasi_diterima
FROM komunitas k
LEFT JOIN users u
    ON u.id_user = k.id_user
LEFT JOIN jenis_lembaga jl
    ON jl.id_jenis = k.id_jenis_lembaga
LEFT JOIN campaign c
    ON c.id_komunitas = k.id_komunitas
LEFT JOIN donasi d
    ON d.id_campaign = c.id_campaign
   AND d.status_pembayaran = 'berhasil'
GROUP BY
    k.id_komunitas,
    k.nama_lembaga,
    jl.nama_jenis,
    u.email,
    k.nomor_kontak,
    k.status,
    k.created_at;

-- User donation history
CREATE OR REPLACE VIEW v_user_donation_history AS
SELECT
    d.id_donasi,
    d.id_user,
    d.id_campaign,
    c.judul AS judul_campaign,
    CONCAT('DON-', LPAD(d.id_donasi::TEXT, 8, '0')) AS nomor_transaksi,
    d.nominal,
    d.metode_pembayaran,
    d.status_pembayaran,
    d.is_anonim,
    d.created_at
FROM donasi d
JOIN campaign c ON c.id_campaign = d.id_campaign;

-- Donation receipt view
CREATE OR REPLACE VIEW v_donation_receipt AS
SELECT
    d.id_donasi,
    CONCAT('DON-', LPAD(d.id_donasi::TEXT, 8, '0')) AS nomor_transaksi,
    d.id_user,
    u.username,
    c.judul AS judul_campaign,
    d.nominal,
    d.metode_pembayaran,
    d.status_pembayaran,
    d.bukti_pdf_url,
    d.created_at AS tanggal_transaksi,
    CASE WHEN d.is_anonim = TRUE THEN 'Anonim' ELSE COALESCE(d.nama_tampil, u.username) END AS nama_tampil
FROM donasi d
JOIN users u ON u.id_user = d.id_user
JOIN campaign c ON c.id_campaign = d.id_campaign;

-- Campaign donor monitoring
CREATE OR REPLACE VIEW v_campaign_donor_monitoring AS
SELECT
    c.id_campaign,
    c.judul AS judul_campaign,
    d.id_donasi,
    CASE WHEN d.is_anonim = TRUE THEN 'Anonim' ELSE COALESCE(d.nama_tampil, u.username) END AS nama_tampil,
    d.nominal,
    d.created_at
FROM donasi d
JOIN users u ON u.id_user = d.id_user
JOIN campaign c ON c.id_campaign = d.id_campaign
WHERE d.status_pembayaran = 'berhasil';

-- Campaign financial summary
CREATE OR REPLACE VIEW v_campaign_financial_summary AS
SELECT
    c.id_campaign,
    c.judul,
    c.target_dana,
    c.dana_terkumpul,
    c.saldo_tersedia,
    c.saldo_terkunci,
    COUNT(DISTINCT d.id_user) FILTER (WHERE d.status_pembayaran = 'berhasil') AS jumlah_donatur,
    COUNT(d.id_donasi) FILTER (WHERE d.status_pembayaran = 'berhasil') AS jumlah_donasi
FROM campaign c
LEFT JOIN donasi d ON d.id_campaign = c.id_campaign
GROUP BY c.id_campaign, c.judul, c.target_dana, c.dana_terkumpul, c.saldo_tersedia, c.saldo_terkunci;

-- Platform financial report (current snapshot)
CREATE OR REPLACE VIEW v_platform_financial_report AS
SELECT
    CURRENT_DATE AS tanggal_laporan,
    COALESCE((SELECT SUM(nominal) FROM donasi WHERE status_pembayaran = 'berhasil'), 0) AS total_donasi,
    COALESCE((SELECT SUM(nominal_disetujui) FROM pencairan_dana WHERE status IN ('disetujui', 'selesai')), 0) AS total_pencairan,
    COALESCE((SELECT SUM(nominal_potongan) FROM potongan_platform), 0) AS total_potongan_platform;

-- Campaign withdrawal summary
CREATE OR REPLACE VIEW v_campaign_withdrawal_summary AS
SELECT
    c.id_campaign,
    c.judul,

    c.saldo_tersedia,
    c.saldo_terkunci,

    c.jumlah_pencairan_approve,

    GREATEST(
        0,
        5 - c.jumlah_pencairan_approve
    ) AS sisa_kesempatan,

    COALESCE(
        SUM(pd.nominal_disetujui)
        FILTER (
            WHERE pd.status IN ('disetujui','selesai')
        ),
        0
    ) AS total_dicairkan

FROM campaign c
LEFT JOIN pencairan_dana pd
    ON pd.id_campaign = c.id_campaign

GROUP BY
    c.id_campaign,
    c.judul,
    c.saldo_tersedia,
    c.saldo_terkunci,
    c.jumlah_pencairan_approve;

CREATE OR REPLACE VIEW v_daily_donations AS
SELECT
    DATE(created_at) AS tanggal,
    COUNT(id_donasi) AS total_transaksi,
    COALESCE(SUM(nominal), 0) AS total_donasi
FROM donasi
WHERE status_pembayaran = 'berhasil'
GROUP BY DATE(created_at);


CREATE OR REPLACE VIEW v_daily_users AS
SELECT
    DATE(created_at) AS tanggal,
    COUNT(id_user) AS total_user_baru
FROM users
WHERE deleted_at IS NULL
GROUP BY DATE(created_at);

CREATE OR REPLACE VIEW v_daily_communities AS
SELECT
    DATE(created_at) AS tanggal,
    COUNT(id_komunitas) AS total_komunitas_baru
FROM komunitas
WHERE deleted_at IS NULL
GROUP BY DATE(created_at);

CREATE OR REPLACE VIEW v_donation_transactions AS
SELECT
    d.id_donasi,
    CONCAT('DON-', LPAD(d.id_donasi::TEXT, 8, '0')) AS nomor_transaksi,
    d.created_at AS tanggal_transaksi,

    d.id_user,
    u.username,
    u.nama_lengkap,

    c.id_campaign,
    c.judul AS judul_campaign,

    k.id_komunitas,
    k.nama_lembaga,

    d.nominal,
    d.metode_pembayaran,
    d.status_pembayaran,
    d.is_anonim

FROM donasi d
JOIN users u
    ON u.id_user = d.id_user
JOIN campaign c
    ON c.id_campaign = d.id_campaign
JOIN komunitas k
    ON k.id_komunitas = c.id_komunitas;


CREATE OR REPLACE VIEW v_withdrawal_transactions AS
SELECT
    pd.id_pencairan,
    pd.tanggal_pengajuan,
    pd.tanggal_keputusan,

    c.id_campaign,
    c.judul AS judul_campaign,

    k.id_komunitas,
    k.nama_lembaga,

    pd.urutan_ke,
    pd.nominal_diajukan,
    pd.nominal_disetujui,

    pd.status,
    pd.keterangan,

    pd.nama_bank_tujuan,
    pd.nomor_rekening_tujuan,

    pd.direview_oleh

FROM pencairan_dana pd
JOIN campaign c
    ON c.id_campaign = pd.id_campaign
JOIN komunitas k
    ON k.id_komunitas = pd.id_komunitas;

CREATE OR REPLACE VIEW v_platform_summary AS
SELECT
    (SELECT COUNT(*) FROM users WHERE deleted_at IS NULL) AS total_users,
    (SELECT COUNT(*) FROM komunitas WHERE deleted_at IS NULL) AS total_komunitas,
    (SELECT COUNT(*) FROM campaign) AS total_campaign,
    (SELECT COUNT(*) FROM donasi WHERE status_pembayaran = 'berhasil') AS total_donasi_berhasil,
    (SELECT COALESCE(SUM(nominal),0)
     FROM donasi
     WHERE status_pembayaran = 'berhasil') AS total_nominal_donasi,
    (SELECT COALESCE(SUM(nominal_disetujui),0)
     FROM pencairan_dana
     WHERE status IN ('disetujui','selesai')) AS total_pencairan;

-- ============================================================
-- MATERIALIZED VIEWS
-- ============================================================

-- Platform summary materialized view (refreshed every 5 min by scheduler)
CREATE MATERIALIZED VIEW IF NOT EXISTS v_platform_summary_mv AS
SELECT
    COALESCE((SELECT COUNT(*) FROM users WHERE deleted_at IS NULL), 0) AS total_users,
    COALESCE((SELECT COUNT(*) FROM users WHERE role = 'DONATUR' AND deleted_at IS NULL), 0) AS total_donatur,
    COALESCE((SELECT COUNT(*) FROM komunitas WHERE deleted_at IS NULL), 0) AS total_komunitas,
    COALESCE((SELECT COUNT(*) FROM campaign), 0) AS total_campaign,
    COALESCE((SELECT COUNT(*) FROM campaign WHERE status = 'aktif'), 0) AS campaign_aktif,
    COALESCE((SELECT COUNT(*) FROM campaign WHERE status = 'selesai'), 0) AS campaign_selesai,
    COALESCE((SELECT COUNT(*) FROM campaign WHERE status = 'menunggu_review'), 0) AS campaign_menunggu_review,
    COALESCE((SELECT COUNT(*) FROM donasi WHERE status_pembayaran = 'berhasil'), 0) AS total_donasi_berhasil,
    COALESCE((SELECT SUM(nominal) FROM donasi WHERE status_pembayaran = 'berhasil'), 0) AS total_nominal_donasi,
    COALESCE((SELECT COUNT(DISTINCT id_user) FROM donasi WHERE status_pembayaran = 'berhasil'), 0) AS total_donatur_aktif,
    COALESCE((SELECT SUM(nominal_disetujui) FROM pencairan_dana WHERE status IN ('disetujui','selesai')), 0) AS total_pencairan
WITH DATA;

CREATE UNIQUE INDEX IF NOT EXISTS idx_v_platform_summary_mv_row ON v_platform_summary_mv ((true));
