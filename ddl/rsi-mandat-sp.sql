-- ============================================================
-- TABEL PERSONAL ACCESS TOKENS (untuk manajemen token pengguna)
-- ============================================================


-- ============================================================
-- STORED PROCEDURES (LENGKAP)
-- ============================================================

-- ============================================================
-- 1. DONATION FLOW
-- ============================================================

-- Procedure: create donation (pending)
CREATE OR REPLACE PROCEDURE sp_create_donation(
    IN p_id_user INT,
    IN p_id_campaign INT,
    IN p_nominal BIGINT,
    IN p_metode_pembayaran VARCHAR(20),
    IN p_is_anonim BOOLEAN,
    IN p_nama_tampil VARCHAR(100)
)
LANGUAGE plpgsql
AS $$
DECLARE
    v_campaign_status campaign_status;
BEGIN

    -- User harus aktif
    IF NOT EXISTS (
        SELECT 1
        FROM users
        WHERE id_user = p_id_user
          AND is_active = TRUE
    ) THEN
        RAISE EXCEPTION 'User tidak aktif atau tidak ditemukan';
    END IF;

    -- Lock campaign
    SELECT status
    INTO v_campaign_status
    FROM campaign
    WHERE id_campaign = p_id_campaign
    FOR UPDATE;

    IF NOT FOUND THEN
        RAISE EXCEPTION 'Campaign tidak ditemukan';
    END IF;

    IF v_campaign_status <> 'aktif' THEN
        RAISE EXCEPTION 'Campaign tidak aktif';
    END IF;

    IF p_nominal < 5000 THEN
        RAISE EXCEPTION 'Minimal donasi adalah Rp5.000';
    END IF;

    INSERT INTO donasi (
        id_user,
        id_campaign,
        nominal,
        metode_pembayaran,
        nama_tampil,
        is_anonim,
        status_pembayaran
    )
    VALUES (
        p_id_user,
        p_id_campaign,
        p_nominal,
        LOWER(TRIM(p_metode_pembayaran)),
        CASE
            WHEN p_is_anonim THEN NULL
            ELSE TRIM(p_nama_tampil)
        END,
        p_is_anonim,
        'pending'
    );

END;
$$;

-- Procedure: confirm payment (pending -> berhasil)
CREATE OR REPLACE PROCEDURE sp_confirm_payment(IN p_id_donasi INT)
LANGUAGE plpgsql
AS $$
DECLARE
    v_nominal BIGINT;
    v_campaign_id INT;
    v_status payment_status;
BEGIN
    SELECT nominal, id_campaign, status_pembayaran
    INTO v_nominal, v_campaign_id, v_status
    FROM donasi
    WHERE id_donasi = p_id_donasi
    FOR UPDATE;

    IF NOT FOUND THEN
        RAISE EXCEPTION 'Donasi tidak ditemukan';
    END IF;

    IF v_status <> 'pending' THEN
        RAISE EXCEPTION 'Status donasi harus pending. Status saat ini: %', v_status;
    END IF;

    UPDATE donasi SET status_pembayaran = 'berhasil' WHERE id_donasi = p_id_donasi;

    UPDATE campaign
    SET dana_terkumpul = dana_terkumpul + v_nominal,
        saldo_tersedia = saldo_tersedia + v_nominal
    WHERE id_campaign = v_campaign_id;
END;
$$;

-- Procedure: fail payment (pending -> gagal)
CREATE OR REPLACE PROCEDURE sp_fail_payment(IN p_id_donasi INT)
LANGUAGE plpgsql
AS $$
DECLARE
    v_status payment_status;
BEGIN
    SELECT status_pembayaran INTO v_status
    FROM donasi
    WHERE id_donasi = p_id_donasi
    FOR UPDATE;

    IF NOT FOUND THEN
        RAISE EXCEPTION 'Donasi tidak ditemukan';
    END IF;

    IF v_status <> 'pending' THEN
        RAISE EXCEPTION 'Hanya donasi dengan status pending yang dapat digagalkan';
    END IF;

    UPDATE donasi SET status_pembayaran = 'gagal' WHERE id_donasi = p_id_donasi;
END;
$$;

-- Procedure: expire payment (pending -> expired)
CREATE OR REPLACE PROCEDURE sp_expire_payment(IN p_id_donasi INT)
LANGUAGE plpgsql
AS $$
DECLARE
    v_status payment_status;
BEGIN
    SELECT status_pembayaran INTO v_status
    FROM donasi
    WHERE id_donasi = p_id_donasi
    FOR UPDATE;

    IF NOT FOUND THEN
        RAISE EXCEPTION 'Donasi tidak ditemukan';
    END IF;

    IF v_status <> 'pending' THEN
        RAISE EXCEPTION 'Hanya donasi dengan status pending yang dapat kadaluarsa';
    END IF;

    UPDATE donasi SET status_pembayaran = 'expired' WHERE id_donasi = p_id_donasi;
END;
$$;

-- ============================================================
-- 2. WITHDRAWAL FLOW (PENCAIRAN DANA)
-- ============================================================

-- Procedure: submit withdrawal request
CREATE OR REPLACE PROCEDURE sp_submit_withdrawal(
    IN p_campaign_id INT,
    IN p_komunitas_id INT,
    IN p_nominal BIGINT,
    IN p_keterangan TEXT,
    IN p_url_proposal VARCHAR(255),
    IN p_nama_bank VARCHAR(50),
    IN p_nomor_rekening VARCHAR(20),
    IN p_id_laporan_dana INT DEFAULT NULL
)
LANGUAGE plpgsql
AS $$
DECLARE
    v_target BIGINT;
    v_terkumpul BIGINT;
    v_saldo BIGINT;
    v_jumlah_approve INT;
    v_pending_count INT;
    v_urutan INT;
    v_status campaign_status;
BEGIN

    SELECT
        target_dana,
        dana_terkumpul,
        saldo_tersedia,
        jumlah_pencairan_approve,
        status
    INTO
        v_target,
        v_terkumpul,
        v_saldo,
        v_jumlah_approve,
        v_status
    FROM campaign
    WHERE id_campaign = p_campaign_id
      AND id_komunitas = p_komunitas_id
    FOR UPDATE;

    IF NOT FOUND THEN
        RAISE EXCEPTION 'Campaign tidak ditemukan atau bukan milik komunitas';
    END IF;

    IF v_status <> 'aktif' THEN
        RAISE EXCEPTION 'Pencairan hanya dapat dilakukan pada campaign aktif';
    END IF;

    IF p_nominal <= 0 THEN
        RAISE EXCEPTION 'Nominal pencairan harus lebih dari 0';
    END IF;

    IF v_terkumpul < (v_target * 0.10) THEN
        RAISE EXCEPTION 'Dana terkumpul belum mencapai 10%% target';
    END IF;

    IF v_jumlah_approve >= 5 THEN
        RAISE EXCEPTION 'Batas pencairan telah tercapai';
    END IF;

    SELECT COUNT(*)
    INTO v_pending_count
    FROM pencairan_dana
    WHERE id_campaign = p_campaign_id
      AND status = 'menunggu_review';

    IF v_pending_count > 0 THEN
        RAISE EXCEPTION 'Masih ada pengajuan yang sedang direview';
    END IF;

    IF p_nominal > v_saldo THEN
        RAISE EXCEPTION 'Saldo tersedia tidak mencukupi';
    END IF;

    v_urutan := v_jumlah_approve + 1;

    INSERT INTO pencairan_dana (
        id_campaign,
        id_komunitas,
        id_laporan_dana,
        urutan_ke,
        nominal_diajukan,
        keterangan,
        url_proposal,
        nama_bank_tujuan,
        nomor_rekening_tujuan,
        status
    )
    VALUES (
        p_campaign_id,
        p_komunitas_id,
        p_id_laporan_dana,
        v_urutan,
        p_nominal,
        p_keterangan,
        p_url_proposal,
        p_nama_bank,
        p_nomor_rekening,
        'menunggu_review'
    );

    UPDATE campaign
    SET saldo_tersedia = saldo_tersedia - p_nominal,
        saldo_terkunci = saldo_terkunci + p_nominal
    WHERE id_campaign = p_campaign_id;

END;
$$;

-- Procedure: review withdrawal (approve/reject)
CREATE OR REPLACE PROCEDURE sp_review_withdrawal(
    IN p_id_pencairan INT,
    IN p_action VARCHAR(10),
    IN p_reviewer_id INT,
    IN p_alasan_penolakan TEXT DEFAULT NULL
)
LANGUAGE plpgsql
AS $$
DECLARE
    v_campaign_id INT;
    v_nominal BIGINT;
    v_status withdrawal_status;
BEGIN
    SELECT id_campaign, nominal_diajukan, status
    INTO v_campaign_id, v_nominal, v_status
    FROM pencairan_dana
    WHERE id_pencairan = p_id_pencairan
    FOR UPDATE;

    IF NOT FOUND THEN
        RAISE EXCEPTION 'Pengajuan pencairan tidak ditemukan';
    END IF;

    IF v_status <> 'menunggu_review' THEN
        RAISE EXCEPTION 'Pengajuan sudah direview sebelumnya';
    END IF;

    IF LOWER(p_action) = 'approve' THEN
        UPDATE pencairan_dana
        SET nominal_disetujui = nominal_diajukan,
            status = 'disetujui',
            direview_oleh = p_reviewer_id,
            tanggal_keputusan = NOW()
        WHERE id_pencairan = p_id_pencairan;

        UPDATE campaign
        SET saldo_terkunci = saldo_terkunci - v_nominal,
            jumlah_pencairan_approve = jumlah_pencairan_approve + 1
        WHERE id_campaign = v_campaign_id;

    ELSIF LOWER(p_action) = 'reject' THEN
        IF p_alasan_penolakan IS NULL OR TRIM(p_alasan_penolakan) = '' THEN
            RAISE EXCEPTION 'Alasan penolakan wajib diisi';
        END IF;

        UPDATE pencairan_dana
        SET status = 'ditolak',
            alasan_penolakan = p_alasan_penolakan,
            direview_oleh = p_reviewer_id,
            tanggal_keputusan = NOW()
        WHERE id_pencairan = p_id_pencairan;

        UPDATE campaign
        SET saldo_terkunci = saldo_terkunci - v_nominal,
            saldo_tersedia = saldo_tersedia + v_nominal
        WHERE id_campaign = v_campaign_id;

    ELSE
        RAISE EXCEPTION 'Action harus approve atau reject';
    END IF;
END;
$$;

-- Procedure: cancel withdrawal (membatalkan pengajuan yang masih menunggu_review)
CREATE OR REPLACE PROCEDURE sp_cancel_withdrawal(
    IN p_id_pencairan INT,
    IN p_alasan_batal TEXT DEFAULT 'Dibatalkan oleh komunitas'
)
LANGUAGE plpgsql
AS $$
DECLARE
    v_campaign_id INT;
    v_nominal BIGINT;
    v_status withdrawal_status;
BEGIN
    SELECT id_campaign, nominal_diajukan, status
    INTO v_campaign_id, v_nominal, v_status
    FROM pencairan_dana
    WHERE id_pencairan = p_id_pencairan
    FOR UPDATE;

    IF NOT FOUND THEN
        RAISE EXCEPTION 'Pengajuan pencairan tidak ditemukan';
    END IF;

    IF v_status <> 'menunggu_review' THEN
        RAISE EXCEPTION 'Hanya pengajuan dengan status menunggu_review yang dapat dibatalkan';
    END IF;

    -- Ubah status menjadi ditolak dengan alasan pembatalan
    UPDATE pencairan_dana
    SET status = 'ditolak',
        alasan_penolakan = p_alasan_batal,
        tanggal_keputusan = NOW()
    WHERE id_pencairan = p_id_pencairan;

    -- Kembalikan saldo dari terkunci ke tersedia
    UPDATE campaign
    SET saldo_terkunci = saldo_terkunci - v_nominal,
        saldo_tersedia = saldo_tersedia + v_nominal
    WHERE id_campaign = v_campaign_id;
END;
$$;

-- Procedure: complete withdrawal (transfer done)
CREATE OR REPLACE PROCEDURE sp_complete_withdrawal(
    IN p_id_pencairan INT,
    IN p_bukti_transfer_url VARCHAR(255)
)
LANGUAGE plpgsql
AS $$
DECLARE
    v_status withdrawal_status;
BEGIN
    SELECT status INTO v_status
    FROM pencairan_dana
    WHERE id_pencairan = p_id_pencairan
    FOR UPDATE;

    IF NOT FOUND THEN
        RAISE EXCEPTION 'Pencairan tidak ditemukan';
    END IF;

    IF v_status <> 'disetujui' THEN
        RAISE EXCEPTION 'Hanya pencairan berstatus disetujui yang dapat diselesaikan';
    END IF;

    IF p_bukti_transfer_url IS NULL OR TRIM(p_bukti_transfer_url) = '' THEN
        RAISE EXCEPTION 'Bukti transfer wajib diisi';
    END IF;

    UPDATE pencairan_dana
    SET status = 'selesai',
        bukti_transfer_url = p_bukti_transfer_url
    WHERE id_pencairan = p_id_pencairan;
END;
$$;

-- ============================================================
-- 3. PLATFORM FEE
-- ============================================================

CREATE OR REPLACE PROCEDURE sp_apply_platform_fee(
    IN p_campaign_id INT,
    IN p_admin_id INT
)
LANGUAGE plpgsql
AS $$
DECLARE
    v_status campaign_status;
    v_dana_terkumpul BIGINT;
    v_fee BIGINT;
    v_sudah_dipotong BOOLEAN;
BEGIN
    SELECT status, dana_terkumpul, potongan_platform_sudah_dipotong
    INTO v_status, v_dana_terkumpul, v_sudah_dipotong
    FROM campaign
    WHERE id_campaign = p_campaign_id
    FOR UPDATE;

    IF NOT FOUND THEN
        RAISE EXCEPTION 'Campaign tidak ditemukan';
    END IF;

    IF v_status <> 'selesai' THEN
        RAISE EXCEPTION 'Potongan platform hanya dapat dilakukan pada campaign selesai';
    END IF;

    IF v_sudah_dipotong THEN
        RAISE EXCEPTION 'Potongan platform sudah pernah dilakukan';
    END IF;

    IF v_dana_terkumpul <= 0 THEN
        RETURN;
    END IF;

    v_fee := LEAST(FLOOR(v_dana_terkumpul * 0.01), 50000);

    INSERT INTO potongan_platform (id_campaign, total_dana_terkumpul, persentase_potongan, nominal_potongan, dipotong_oleh)
    VALUES (p_campaign_id, v_dana_terkumpul, 1.00, v_fee, p_admin_id);

    UPDATE campaign
    SET potongan_platform_sudah_dipotong = TRUE,
        saldo_tersedia = saldo_tersedia - v_fee
    WHERE id_campaign = p_campaign_id;
END;
$$;

-- ============================================================
-- 4. CAMPAIGN REVIEW (APPROVE/REJECT)
-- ============================================================

CREATE OR REPLACE PROCEDURE sp_review_campaign(
    IN p_id_campaign INT,
    IN p_action VARCHAR(10),
    IN p_reviewer_id INT,
    IN p_alasan_penolakan TEXT DEFAULT NULL
)
LANGUAGE plpgsql
AS $$
DECLARE
    v_status campaign_status;
BEGIN
    SELECT status INTO v_status
    FROM campaign
    WHERE id_campaign = p_id_campaign
    FOR UPDATE;

    IF NOT FOUND THEN
        RAISE EXCEPTION 'Campaign tidak ditemukan';
    END IF;

    IF v_status <> 'menunggu_review' THEN
        RAISE EXCEPTION 'Campaign sudah direview';
    END IF;

    IF LOWER(p_action) = 'approve' THEN
        UPDATE campaign
        SET status = 'aktif',
            direview_oleh = p_reviewer_id,
            updated_at = NOW()
        WHERE id_campaign = p_id_campaign;

    ELSIF LOWER(p_action) = 'reject' THEN
        IF p_alasan_penolakan IS NULL OR TRIM(p_alasan_penolakan) = '' THEN
            RAISE EXCEPTION 'Alasan penolakan wajib diisi';
        END IF;

        UPDATE campaign
        SET status = 'ditolak',
            alasan_penolakan = p_alasan_penolakan,
            direview_oleh = p_reviewer_id,
            updated_at = NOW()
        WHERE id_campaign = p_id_campaign;

    ELSE
        RAISE EXCEPTION 'Action harus approve atau reject';
    END IF;
END;
$$;

-- ============================================================
-- 5. DOKUMEN KOMUNITAS VERIFICATION
-- ============================================================

CREATE OR REPLACE PROCEDURE sp_verify_document(
    IN p_id_dokumen INT,
    IN p_action VARCHAR(10),
    IN p_verifikator_id INT,
    IN p_alasan_penolakan TEXT DEFAULT NULL
)
LANGUAGE plpgsql
AS $$
DECLARE
    v_status verification_status;
BEGIN
    SELECT status_verifikasi INTO v_status
    FROM dokumen_komunitas
    WHERE id_dokumen = p_id_dokumen
    FOR UPDATE;

    IF NOT FOUND THEN
        RAISE EXCEPTION 'Dokumen tidak ditemukan';
    END IF;

    IF v_status NOT IN ('menunggu', 'menunggu_verifikasi') THEN
        RAISE EXCEPTION 'Dokumen sudah diverifikasi';
    END IF;

    IF LOWER(p_action) = 'approve' THEN
        UPDATE dokumen_komunitas
        SET status_verifikasi = 'diverifikasi',
            diverifikasi_oleh = p_verifikator_id,
            verified_at = NOW()
        WHERE id_dokumen = p_id_dokumen;

    ELSIF LOWER(p_action) = 'reject' THEN
        IF p_alasan_penolakan IS NULL OR TRIM(p_alasan_penolakan) = '' THEN
            RAISE EXCEPTION 'Alasan penolakan wajib diisi';
        END IF;

        UPDATE dokumen_komunitas
        SET status_verifikasi = 'ditolak',
            alasan_penolakan = p_alasan_penolakan,
            diverifikasi_oleh = p_verifikator_id,
            verified_at = NOW()
        WHERE id_dokumen = p_id_dokumen;

    ELSE
        RAISE EXCEPTION 'Action harus approve atau reject';
    END IF;
END;
$$;

-- ============================================================
-- 6. LAPORAN PENGGUNAAN DANA VERIFICATION
-- ============================================================

CREATE OR REPLACE PROCEDURE sp_verify_fund_report(
    IN p_id_laporan INT,
    IN p_action VARCHAR(10),
    IN p_verifikator_id INT,
    IN p_alasan_penolakan TEXT DEFAULT NULL
)
LANGUAGE plpgsql
AS $$
DECLARE
    v_status verification_status;
BEGIN
    SELECT status_verifikasi INTO v_status
    FROM laporan_penggunaan_dana
    WHERE id_laporan = p_id_laporan
    FOR UPDATE;

    IF NOT FOUND THEN
        RAISE EXCEPTION 'Laporan tidak ditemukan';
    END IF;

    IF v_status <> 'menunggu_verifikasi' THEN
        RAISE EXCEPTION 'Laporan sudah diverifikasi';
    END IF;

    IF LOWER(p_action) = 'approve' THEN
        UPDATE laporan_penggunaan_dana
        SET status_verifikasi = 'diverifikasi',
            diverifikasi_oleh = p_verifikator_id,
            tanggal_verifikasi = NOW(),
            updated_at = NOW()
        WHERE id_laporan = p_id_laporan;

    ELSIF LOWER(p_action) = 'reject' THEN
        IF p_alasan_penolakan IS NULL OR TRIM(p_alasan_penolakan) = '' THEN
            RAISE EXCEPTION 'Alasan penolakan wajib diisi';
        END IF;

        UPDATE laporan_penggunaan_dana
        SET status_verifikasi = 'ditolak',
            alasan_penolakan = p_alasan_penolakan,
            diverifikasi_oleh = p_verifikator_id,
            tanggal_verifikasi = NOW(),
            updated_at = NOW()
        WHERE id_laporan = p_id_laporan;

    ELSE
        RAISE EXCEPTION 'Action harus approve atau reject';
    END IF;
END;
$$;

-- ============================================================
-- 7. CAMPAIGN UPDATE
-- ============================================================

CREATE OR REPLACE PROCEDURE sp_create_update(
    p_id_campaign INT,
    p_id_komunitas INT,
    p_judul VARCHAR,
    p_konten TEXT,
    OUT p_id_update INT
)
LANGUAGE plpgsql
AS $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM campaign WHERE id_campaign = p_id_campaign AND id_komunitas = p_id_komunitas AND status = 'aktif') THEN
        RAISE EXCEPTION 'ERR-MON-08: Tidak dapat membuat update pada campaign ini';
    END IF;

    IF TRIM(p_judul) = '' OR TRIM(p_konten) = '' THEN
        RAISE EXCEPTION 'ERR-MON-05: Judul dan deskripsi wajib diisi';
    END IF;

    INSERT INTO update_campaign (id_campaign, id_komunitas, judul_update, konten)
    VALUES (p_id_campaign, p_id_komunitas, p_judul, p_konten)
    RETURNING id_update INTO p_id_update;
END;
$$;

-- ============================================================
-- 8. FOLLOW/UNFOLLOW KOMUNITAS
-- ============================================================

CREATE OR REPLACE PROCEDURE sp_follow_community(
    p_id_user INT,
    p_id_komunitas INT
)
LANGUAGE plpgsql
AS $$
BEGIN
    -- Sudah follow aktif
    IF EXISTS (
        SELECT 1
        FROM follow_komunitas
        WHERE id_user = p_id_user
          AND id_komunitas = p_id_komunitas
          AND is_active = TRUE
    ) THEN
        RAISE EXCEPTION 'ERR-MON-12: Tidak dapat mengulangi aksi yang sama';
    END IF;
    -- Pernah follow sebelumnya → aktifkan kembali
    IF EXISTS (
        SELECT 1
        FROM follow_komunitas
        WHERE id_user = p_id_user
          AND id_komunitas = p_id_komunitas
          AND is_active = FALSE
    ) THEN
        UPDATE follow_komunitas
        SET is_active = TRUE,
            followed_at = NOW(),
            unfollowed_at = NULL
        WHERE id_user = p_id_user
          AND id_komunitas = p_id_komunitas;
    ELSE
        -- Follow pertama kali
        INSERT INTO follow_komunitas (
            id_user,
            id_komunitas,
            is_active,
            followed_at
        )
        VALUES (
            p_id_user,
            p_id_komunitas,
            TRUE,
            NOW()
        );
    END IF;
END;
$$;

CREATE OR REPLACE PROCEDURE sp_unfollow_community(
    p_id_user INT,
    p_id_komunitas INT,
    p_konfirmasi BOOLEAN
)
LANGUAGE plpgsql
AS $$
BEGIN
    IF NOT p_konfirmasi THEN
        RETURN;
    END IF;

    UPDATE follow_komunitas
    SET is_active = FALSE, unfollowed_at = NOW()
    WHERE id_user = p_id_user AND id_komunitas = p_id_komunitas AND is_active = TRUE;

    IF NOT FOUND THEN
        RAISE EXCEPTION 'ERR-MON-12: Tidak dapat mengulangi aksi yang sama';
    END IF;
END;
$$;

-- ============================================================
-- 9. NOTIFIKASI (MARK READ)
-- ============================================================

CREATE OR REPLACE PROCEDURE sp_mark_read_notif(p_id_notif INT, p_id_actor INT)
LANGUAGE plpgsql AS $$
BEGIN
    UPDATE notifikasi
    SET is_read = TRUE, read_at = NOW()
    WHERE id_notif = p_id_notif
      AND (id_penerima_user = p_id_actor OR id_penerima_komunitas = p_id_actor)
      AND is_read = FALSE;
END;
$$;

CREATE OR REPLACE PROCEDURE sp_mark_read_all_notif(p_id_actor INT)
LANGUAGE plpgsql AS $$
BEGIN
    UPDATE notifikasi
    SET is_read = TRUE, read_at = NOW()
    WHERE (id_penerima_user = p_id_actor OR id_penerima_komunitas = p_id_actor)
      AND is_read = FALSE;
END;
$$;

-- ============================================================
-- 10. USER & COMMUNITY STATUS MANAGEMENT (AKTIF/NONAKTIF)
-- ============================================================

-- Procedure: update donor status (activate/deactivate)
CREATE OR REPLACE PROCEDURE sp_update_donor_status(
    IN p_id_user BIGINT,
    IN p_is_active BOOLEAN,
    IN p_id_superadmin BIGINT
)
LANGUAGE plpgsql
AS $$
DECLARE
    v_admin_username VARCHAR(50);
BEGIN
    -- Ambil username superadmin untuk pesan notifikasi
    SELECT username INTO v_admin_username
    FROM users
    WHERE id_user = p_id_superadmin AND role = 'SUPERADMIN';

    IF NOT FOUND THEN
        v_admin_username := 'Superadmin (ID ' || p_id_superadmin || ')';
    END IF;

    UPDATE users
    SET is_active = p_is_active, updated_at = NOW()
    WHERE id_user = p_id_user AND role = 'DONATUR';

    IF NOT FOUND THEN
        RAISE EXCEPTION 'Donatur tidak ditemukan atau bukan role DONATUR';
    END IF;

    IF p_is_active = FALSE THEN
        DELETE FROM personal_access_tokens WHERE tokenable_id = p_id_user;
    END IF;

    INSERT INTO notifikasi (id_penerima_user, judul, pesan, tipe, is_read, created_at, expires_at)
    VALUES (
        p_id_user,
        'Perubahan Status Akun',
        CASE WHEN p_is_active = TRUE
             THEN format('Akun Donatur Anda telah diaktifkan kembali oleh %s.', v_admin_username)
             ELSE format('Akun Donatur Anda telah dinonaktifkan oleh %s.', v_admin_username)
        END,
        'sistem',
        FALSE,
        NOW(),
        NOW() + INTERVAL '90 days'
    );
END;
$$;

-- Procedure: update community status (activate/deactivate)
-- Catatan: kolom status pada tabel komunitas bisa berupa 'aktif', 'menunggu', 'ditolak', 'dinonaktifkan'
CREATE OR REPLACE PROCEDURE sp_update_community_status(
    IN p_id_komunitas BIGINT,
    IN p_is_active BOOLEAN,
    IN p_id_superadmin BIGINT
)
LANGUAGE plpgsql
AS $$
DECLARE
    v_id_user BIGINT;
    v_new_status VARCHAR(20);
    v_admin_username VARCHAR(50);
BEGIN
    -- Ambil username superadmin untuk pesan notifikasi
    SELECT username INTO v_admin_username
    FROM users
    WHERE id_user = p_id_superadmin AND role = 'SUPERADMIN';

    IF NOT FOUND THEN
        v_admin_username := 'Superadmin (ID ' || p_id_superadmin || ')';
    END IF;

    SELECT id_user INTO v_id_user FROM komunitas WHERE id_komunitas = p_id_komunitas;
    IF v_id_user IS NULL THEN
        RAISE EXCEPTION 'Komunitas tidak ditemukan';
    END IF;

    v_new_status := CASE WHEN p_is_active THEN 'aktif' ELSE 'dinonaktifkan' END;

    UPDATE komunitas
    SET status = v_new_status,
        updated_at = NOW()
    WHERE id_komunitas = p_id_komunitas;

    UPDATE users
    SET is_active = p_is_active,
        updated_at = NOW()
    WHERE id_user = v_id_user;

    IF p_is_active = FALSE THEN
        UPDATE campaign
        SET status = 'nonaktif',
            updated_at = NOW()
        WHERE id_komunitas = p_id_komunitas
          AND status = 'aktif';

        DELETE FROM personal_access_tokens WHERE tokenable_id = v_id_user;
    END IF;

    INSERT INTO notifikasi (id_penerima_user, judul, pesan, tipe, is_read, created_at, expires_at)
    VALUES (
        v_id_user,
        'Perubahan Status Komunitas',
        CASE WHEN p_is_active = TRUE
             THEN format('Akun Komunitas Anda telah diaktifkan kembali oleh %s.', v_admin_username)
             ELSE format('Akun Komunitas Anda telah dinonaktifkan oleh %s. Campaign aktif ikut dinonaktifkan sementara.', v_admin_username)
        END,
        'sistem',
        FALSE,
        NOW(),
        NOW() + INTERVAL '90 days'
    );
END;
$$;


-- sp_finish_campaign
--    Mengubah campaign aktif menjadi selesai (manual atau otomatis)
-- ============================================================

CREATE OR REPLACE PROCEDURE sp_finish_campaign(
    IN p_id_campaign INT,
    IN p_diproses_oleh INT,   -- ID user (biasanya sistem atau admin)
    IN p_force BOOLEAN DEFAULT FALSE  -- jika TRUE, abaikan pengecekan tanggal
)
LANGUAGE plpgsql
AS $$
DECLARE
    v_status campaign_status;
    v_tanggal_selesai DATE;
    v_nama_pemroses VARCHAR(100);
BEGIN
    -- Ambil status dan tanggal selesai campaign
    SELECT status, tanggal_selesai
    INTO v_status, v_tanggal_selesai
    FROM campaign
    WHERE id_campaign = p_id_campaign
    FOR UPDATE;

    IF NOT FOUND THEN
        RAISE EXCEPTION 'Campaign tidak ditemukan';
    END IF;

    -- Validasi: hanya campaign aktif yang bisa diselesaikan
    IF v_status <> 'aktif' THEN
        RAISE EXCEPTION 'Hanya campaign dengan status aktif yang dapat diselesaikan. Status saat ini: %', v_status;
    END IF;

    -- Pengecekan tanggal selesai (kecuali dipaksa)
    IF NOT p_force AND (v_tanggal_selesai IS NULL OR v_tanggal_selesai > CURRENT_DATE) THEN
        RAISE EXCEPTION 'Campaign belum mencapai tanggal selesai. Tanggal selesai: %', v_tanggal_selesai;
    END IF;

    -- Update status menjadi selesai
    UPDATE campaign
    SET status = 'selesai',
        updated_at = NOW()
    WHERE id_campaign = p_id_campaign;

    -- (Opsional) Catat log atau notifikasi ke komunitas
    SELECT nama_lengkap INTO v_nama_pemroses
    FROM users
    WHERE id_user = p_diproses_oleh;

    IF v_nama_pemroses IS NULL THEN
        v_nama_pemroses := 'Sistem';
    END IF;

    -- Kirim notifikasi ke komunitas pemilik campaign
    INSERT INTO notifikasi (
        id_penerima_komunitas,
        judul,
        pesan,
        tipe,
        is_read,
        created_at,
        expires_at,
        related_campaign_id
    )
    SELECT
        c.id_komunitas,
        'Campaign Selesai',
        format('Campaign "%s" telah dinyatakan selesai oleh %s.', c.judul, v_nama_pemroses),
        'campaign',
        FALSE,
        NOW(),
        NOW() + INTERVAL '90 days',
        c.id_campaign
    FROM campaign c
    WHERE c.id_campaign = p_id_campaign;

END;
$$;