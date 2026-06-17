-- ============================================================
-- SCHEMA DDL - TABEL TANPA CONSTRAINT
-- ============================================================

-- CREATE DATABASE berbagive;

-- ============================================================
-- ENUM TYPES
-- ============================================================

CREATE TYPE payment_status AS ENUM (
    'pending',
    'berhasil',
    'gagal',
    'expired'
);

CREATE TYPE campaign_status AS ENUM (
    'menunggu_review',
    'aktif',
    'nonaktif',
    'ditolak',
    'selesai',
    'ditutup_permanen'
);

CREATE TYPE withdrawal_status AS ENUM (
    'menunggu_review',
    'disetujui',
    'ditolak',
    'selesai'
);

CREATE TYPE verification_status AS ENUM (
    'diverifikasi',
    'menunggu_verifikasi',
    'ditolak',
    'menunggu'
);


-- SECTION 1: TABEL REFERENSI

CREATE TABLE wilayah (
    kode VARCHAR(13) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    level SMALLINT GENERATED ALWAYS AS (array_length(string_to_array(kode, '.'), 1)) STORED
);

CREATE TABLE kategori_campaign (
    id_kategori    INT NOT NULL,
    nama_kategori  VARCHAR(100) NOT NULL,
    deskripsi      TEXT,
    is_active      BOOLEAN NOT NULL DEFAULT TRUE
);

CREATE TABLE jenis_lembaga (
    id_jenis   INT NOT NULL,
    nama_jenis VARCHAR(100) NOT NULL
);

CREATE TABLE jenis_dokumen (
    id_jenis_dok              INT NOT NULL,
    nama_dokumen              VARCHAR(100) NOT NULL,
    deskripsi                 TEXT,
    wajib_untuk_jenis_lembaga VARCHAR(50),
    is_opsional               BOOLEAN NOT NULL DEFAULT FALSE
);

-- SECTION 2: USERS

CREATE TABLE users (
    id_user            SERIAL NOT NULL,
    username           VARCHAR(50)  NOT NULL,
    email              VARCHAR(150) NOT NULL,
    password_hash      VARCHAR(255) NOT NULL,
    role               VARCHAR(20)  NOT NULL DEFAULT 'DONATUR',
    is_active          BOOLEAN      NOT NULL DEFAULT TRUE,
    is_verified        BOOLEAN      NOT NULL DEFAULT FALSE,
    foto_profil_url    VARCHAR(255),
    nama_lengkap       VARCHAR(150),
    nomor_telepon      VARCHAR(20),
    jenis_kelamin      CHAR(1),
    tanggal_lahir      DATE,
    kode_wilayah       VARCHAR(13),
    created_at         TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at         TIMESTAMP NOT NULL DEFAULT NOW(),
    deleted_at         TIMESTAMP
);

-- SECTION 3: KOMUNITAS

CREATE TABLE komunitas (
    id_komunitas           SERIAL NOT NULL,
    id_user                INT NOT NULL,
    id_jenis_lembaga       INT NOT NULL,
    nama_lembaga           VARCHAR(150) NOT NULL,
    deskripsi              TEXT,
    kode_wilayah           VARCHAR(13)  NOT NULL,
    rt                     VARCHAR(5),
    rw                     VARCHAR(5),
    kode_pos               VARCHAR(10),
    alamat_detail          TEXT,
    nomor_kontak           VARCHAR(20),
    link_medsos            VARCHAR(255),
    foto_lembaga_url       VARCHAR(255) NOT NULL,
    nama_bank              VARCHAR(100) NOT NULL,
    nomor_rekening         VARCHAR(50)  NOT NULL,
    foto_buku_rekening_url VARCHAR(255) NOT NULL,
    status                 VARCHAR(20)  NOT NULL DEFAULT 'menunggu',
    alasan_penolakan       TEXT,
    direview_oleh          INT,
    created_at             TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at             TIMESTAMP NOT NULL DEFAULT NOW(),
    deleted_at             TIMESTAMP
);

-- SECTION 4: DOKUMEN KOMUNITAS

CREATE TABLE dokumen_komunitas (
    id_dokumen        SERIAL NOT NULL,
    id_komunitas      INT NOT NULL,
    id_jenis_dok      INT NOT NULL,
    file_url          VARCHAR(255) NOT NULL,
    status_verifikasi verification_status
                        NOT NULL
                        DEFAULT 'menunggu_verifikasi',
    alasan_penolakan  TEXT,
    diverifikasi_oleh INT,
    uploaded_at       TIMESTAMP NOT NULL DEFAULT NOW(),
    verified_at       TIMESTAMP
);

-- SECTION 5: CAMPAIGN
CREATE TABLE campaign (
    id_campaign                       SERIAL NOT NULL,
    id_komunitas                      INT NOT NULL,
    id_kategori                       INT NOT NULL,
    kode_wilayah                      VARCHAR(13) NOT NULL,
    judul                             VARCHAR(255) NOT NULL,
    deskripsi                         TEXT,
    foto_campaign_url                 VARCHAR(255) NOT NULL,
    target_dana                       BIGINT NOT NULL,
    dana_terkumpul                    BIGINT NOT NULL DEFAULT 0,
    saldo_tersedia                    BIGINT NOT NULL DEFAULT 0,
    saldo_terkunci                    BIGINT NOT NULL DEFAULT 0,
    tipe_distribusi                   VARCHAR(20) NOT NULL DEFAULT 'individual',
    target_audiens                    VARCHAR(150),
    total_penerima_manfaat            INT NOT NULL DEFAULT 0,
    jumlah_pencairan_approve          INT NOT NULL DEFAULT 0,
    potongan_platform_sudah_dipotong  BOOLEAN NOT NULL DEFAULT FALSE,
    tanggal_mulai                     DATE,
    tanggal_selesai                   DATE,
    status                            campaign_status
                                      NOT NULL
                                      DEFAULT 'menunggu_review',
    alasan_penolakan                  TEXT,
    url_rab                           VARCHAR(255),
    direview_oleh                     INT,
    created_at                        TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at                        TIMESTAMP NOT NULL DEFAULT NOW(),
    deleted_at                        TIMESTAMP
);

ALTER TABLE campaign
ALTER COLUMN status TYPE campaign_status
USING status::campaign_status;

-- SECTION 6: DONASI
-- ============================================================
-- ENUM: PAYMENT STATUS
-- ============================================================

CREATE TABLE donasi (
    id_donasi          SERIAL NOT NULL,
    id_user            INT NOT NULL,
    id_campaign        INT NOT NULL,
    nominal            BIGINT NOT NULL,
    metode_pembayaran  VARCHAR(20) NOT NULL,
    nama_tampil        VARCHAR(100),
    is_anonim          BOOLEAN NOT NULL DEFAULT TRUE,
    status_pembayaran  payment_status
                        NOT NULL
                        DEFAULT 'pending',
    bukti_pdf_url      VARCHAR(255),
    created_at         TIMESTAMP NOT NULL DEFAULT NOW()
);

-- SECTION 7: PENCAIRAN DANA

CREATE TABLE pencairan_dana (
    id_pencairan              SERIAL NOT NULL,
    id_campaign               INT NOT NULL,
    id_komunitas              INT NOT NULL,
    id_laporan_dana           INT,
    urutan_ke                 INT NOT NULL,
    nominal_diajukan          BIGINT NOT NULL,
    nominal_disetujui         BIGINT,
    keterangan                TEXT NOT NULL,
    url_proposal              VARCHAR(255) NOT NULL,
    nama_bank_tujuan          VARCHAR(50) NOT NULL,
    nomor_rekening_tujuan     VARCHAR(20) NOT NULL,
    bukti_transfer_url        VARCHAR(255),
    status                    withdrawal_status
                              NOT NULL
                              DEFAULT 'menunggu_review',
    alasan_penolakan          TEXT,
    direview_oleh             INT,
    tanggal_pengajuan         TIMESTAMP NOT NULL DEFAULT NOW(),
    tanggal_keputusan         TIMESTAMP
);

-- SECTION 8: POTONGAN PLATFORM

CREATE TABLE potongan_platform (
    id_potongan          SERIAL NOT NULL,
    id_campaign          INT NOT NULL,
    total_dana_terkumpul BIGINT NOT NULL,
    persentase_potongan  DECIMAL(5,2) NOT NULL DEFAULT 1.00,
    nominal_potongan     BIGINT NOT NULL,
    dipotong_oleh        INT,
    catatan              TEXT,
    dipotong_pada        TIMESTAMP NOT NULL DEFAULT NOW()
);

-- SECTION 9: LAPORAN PENGGUNAAN DANA

CREATE TABLE laporan_penggunaan_dana (
    id_laporan              SERIAL NOT NULL,
    id_pencairan            INT NOT NULL,
    jumlah_penerima         INT NOT NULL DEFAULT 0,
    deskripsi_penggunaan    TEXT NOT NULL,
    total_realisasi         DECIMAL(15,2) NOT NULL,
    file_dokumentasi_url    VARCHAR(500),
    status_verifikasi       verification_status
                            NOT NULL
                            DEFAULT 'menunggu_verifikasi',
    alasan_penolakan        TEXT,
    diverifikasi_oleh       INT,
    tanggal_laporan         TIMESTAMP NOT NULL DEFAULT NOW(),
    tanggal_verifikasi      TIMESTAMP,
    created_at              TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at              TIMESTAMP NOT NULL DEFAULT NOW()
);

-- SECTION 10: PENERIMA MANFAAT

CREATE TABLE penerima_manfaat (
    id_penerima         SERIAL NOT NULL,
    id_laporan          INT NOT NULL,
    nama                VARCHAR(255) NOT NULL,
    nik                 VARCHAR(16) NOT NULL,
    kode_wilayah        VARCHAR(13) NOT NULL,
    nominal_diterima    DECIMAL(15,2) NOT NULL,
    created_at          TIMESTAMP NOT NULL DEFAULT NOW()
);

-- SECTION 11: UPDATE CAMPAIGN

CREATE TABLE update_campaign (
    id_update    SERIAL NOT NULL,
    id_campaign  INT NOT NULL,
    id_komunitas INT NOT NULL,
    judul_update VARCHAR(255) NOT NULL,
    konten       TEXT NOT NULL,
    is_pinned    BOOLEAN NOT NULL DEFAULT FALSE,
    created_at   TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at   TIMESTAMP NOT NULL DEFAULT NOW()
);

-- SECTION 12: FOTO UPDATE

CREATE TABLE foto_update (
    id_foto     SERIAL NOT NULL,
    id_update   INT NOT NULL,
    foto_url    VARCHAR(255) NOT NULL,
    caption     VARCHAR(500),
    urutan      INT NOT NULL DEFAULT 1,
    uploaded_at TIMESTAMP NOT NULL DEFAULT NOW()
);

-- SECTION 13: VERIFIKASI REKENING

CREATE TABLE verifikasi_rekening (
    id_verif                SERIAL NOT NULL,
    id_komunitas            INT NOT NULL,
    nama_bank_baru          VARCHAR(100) NOT NULL,
    nomor_rekening_baru     VARCHAR(50) NOT NULL,
    foto_buku_rekening_url  VARCHAR(255) NOT NULL,
    alasan_perubahan        TEXT NOT NULL,
    status                  VARCHAR(20) NOT NULL DEFAULT 'menunggu',
    alasan_penolakan        TEXT,
    direview_oleh           INT,
    created_at              TIMESTAMP NOT NULL DEFAULT NOW(),
    tanggal_keputusan       TIMESTAMP
);

-- SECTION 14: FOLLOW KOMUNITAS

CREATE TABLE follow_komunitas (
    id_follow       SERIAL NOT NULL,
    id_user         INT NOT NULL,
    id_komunitas    INT NOT NULL,
    is_active       BOOLEAN NOT NULL DEFAULT TRUE,
    followed_at     TIMESTAMP NOT NULL DEFAULT NOW(),
    unfollowed_at   TIMESTAMP
);

-- SECTION 15: NOTIFIKASI

CREATE TABLE notifikasi (
    id_notif               SERIAL NOT NULL,
    id_penerima_user       INT,
    id_penerima_komunitas  INT,
    id_pengirim_user       INT,
    judul                  VARCHAR(255) NOT NULL,
    pesan                  TEXT NOT NULL,
    tipe                   VARCHAR(20) NOT NULL,
    related_campaign_id    INT,
    related_donasi_id      INT,
    related_update_id      INT,
    related_verifikasi_id  INT,
    related_pencairan_id   INT,
    is_read                BOOLEAN NOT NULL DEFAULT FALSE,
    read_at                TIMESTAMP,
    created_at             TIMESTAMP NOT NULL DEFAULT NOW(),
    expires_at             TIMESTAMP NOT NULL,
    is_archived            BOOLEAN NOT NULL DEFAULT FALSE,
    archived_at            TIMESTAMP
);

CREATE TABLE IF NOT EXISTS personal_access_tokens (
    id             SERIAL PRIMARY KEY,
    tokenable_id   INT NOT NULL,
    token          VARCHAR(64) NOT NULL UNIQUE,
    name           VARCHAR(255),
    abilities      TEXT,
    last_used_at   TIMESTAMP,
    expires_at     TIMESTAMP,
    created_at     TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at     TIMESTAMP NOT NULL DEFAULT NOW()
);
COMMENT ON TABLE personal_access_tokens IS 'Menyimpan token autentikasi personal (misal Laravel Sanctum) untuk keperluan revoke token saat akun dinonaktifkan.';





-- Di database berbagive (PostgreSQL)
SELECT
    status_verifikasi,
    COUNT(*) AS jumlah,
    SUM(total_realisasi) AS total
FROM laporan_penggunaan_dana
GROUP BY status_verifikasi;


-- Di PostgreSQL (berbagive)
SELECT id_laporan, total_realisasi, status_verifikasi
FROM laporan_penggunaan_dana
ORDER BY id_laporan
LIMIT 10;