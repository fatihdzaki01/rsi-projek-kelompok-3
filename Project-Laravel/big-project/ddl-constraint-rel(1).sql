-- ============================================================
-- DATABASE: projek_mandat_3
-- BERDASARKAN: DDL asli dari dokumen (tidak ada perubahan)
-- ============================================================
CREATE DATABASE projek_mandat;
\connect projek_mandat_3;

-- ============================================================
-- SECTION 1: TABEL REFERENSI
-- ============================================================

CREATE TABLE wilayah (
    kode VARCHAR(13) NOT NULL PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    level SMALLINT GENERATED ALWAYS AS (
        array_length(string_to_array(kode, '.'), 1)
    ) STORED
);

CREATE TABLE kategori_campaign (
    id_kategori    INT PRIMARY KEY,
    nama_kategori  VARCHAR(100) NOT NULL,
    deskripsi      TEXT,
    is_active      BOOLEAN NOT NULL DEFAULT TRUE
);

CREATE TABLE jenis_lembaga (
    id_jenis   INT PRIMARY KEY,
    nama_jenis VARCHAR(100) NOT NULL
);

CREATE TABLE jenis_dokumen (
    id_jenis_dok              INT PRIMARY KEY,
    nama_dokumen              VARCHAR(100) NOT NULL,
    deskripsi                 TEXT,
    wajib_untuk_jenis_lembaga VARCHAR(50),
    is_opsional               BOOLEAN NOT NULL DEFAULT FALSE
);

-- ============================================================
-- SECTION 2: USERS
-- ============================================================

DROP TABLE IF EXISTS users CASCADE;

CREATE TABLE users (
    -- Primary Key
    id_user            SERIAL PRIMARY KEY,

    -- Credentials
    username           VARCHAR(50)  NOT NULL UNIQUE,
    email              VARCHAR(150) NOT NULL UNIQUE,
    password_hash      VARCHAR(255) NOT NULL,

    -- Role & Status
    role               VARCHAR(20)  NOT NULL DEFAULT 'DONATUR',
    is_active          BOOLEAN      NOT NULL DEFAULT TRUE,
    is_verified        BOOLEAN      NOT NULL DEFAULT FALSE,

    -- Profile
    foto_profil_url    VARCHAR(255),
    nama_lengkap       VARCHAR(150),
    nomor_telepon      VARCHAR(20),
    jenis_kelamin      CHAR(1),
    tanggal_lahir      DATE,
    kode_wilayah       VARCHAR(13),

    -- Timestamps
    created_at         TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at         TIMESTAMP NOT NULL DEFAULT NOW(),
    deleted_at         TIMESTAMP,

    -- Constraints
    CONSTRAINT chk_role_valid
        CHECK (role IN ('DONATUR', 'KOMUNITAS', 'SUPERADMIN')),

    CONSTRAINT chk_jenis_kelamin_valid
        CHECK (jenis_kelamin IN ('L', 'P') OR jenis_kelamin IS NULL),

    CONSTRAINT chk_tanggal_lahir_valid
        CHECK (tanggal_lahir IS NULL OR tanggal_lahir < CURRENT_DATE),

    CONSTRAINT fk_users_wilayah
        FOREIGN KEY (kode_wilayah)
        REFERENCES wilayah(kode)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);

-- Indexes
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_wilayah ON users(kode_wilayah);
CREATE INDEX idx_users_active ON users(is_active) WHERE deleted_at IS NULL;

-- Comments
COMMENT ON TABLE users IS
'Master data pengguna sistem (Donatur, Komunitas, SuperAdmin)';

COMMENT ON COLUMN users.role IS
'Role user: DONATUR (donor), KOMUNITAS (org account), SUPERADMIN (platform admin)';

COMMENT ON COLUMN users.kode_wilayah IS
'Domisili user - untuk KOMUNITAS akan override dengan domisili lembaga';

-- ============================================================
-- SECTION 3: KOMUNITAS
-- ============================================================

DROP TABLE IF EXISTS komunitas CASCADE;

CREATE TABLE komunitas (
    -- Primary Key
    id_komunitas           SERIAL PRIMARY KEY,

    -- Foreign Key (One-to-One with users)
    id_user                INT NOT NULL UNIQUE,
    id_jenis_lembaga       INT NOT NULL,

    -- Data Lembaga
    nama_lembaga           VARCHAR(150) NOT NULL,
    deskripsi              TEXT,

    -- Alamat
    kode_wilayah           VARCHAR(13)  NOT NULL,
    rt                     VARCHAR(5),
    rw                     VARCHAR(5),
    kode_pos               VARCHAR(10),
    alamat_detail          TEXT,

    -- Kontak
    nomor_kontak           VARCHAR(20),
    link_medsos            VARCHAR(255),

    -- Dokumentasi
    foto_lembaga_url       VARCHAR(255) NOT NULL,

    -- Rekening
    nama_bank              VARCHAR(100) NOT NULL,
    nomor_rekening         VARCHAR(50)  NOT NULL,
    foto_buku_rekening_url VARCHAR(255) NOT NULL,

    -- Status & Review
    status                 VARCHAR(20)  NOT NULL DEFAULT 'menunggu',
    alasan_penolakan       TEXT,
    direview_oleh          INT,

    -- Timestamps
    created_at             TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at             TIMESTAMP NOT NULL DEFAULT NOW(),
    deleted_at             TIMESTAMP,

    -- Constraints
    CONSTRAINT fk_komunitas_user
        FOREIGN KEY (id_user)
        REFERENCES users(id_user)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_komunitas_jenis_lembaga
        FOREIGN KEY (id_jenis_lembaga)
        REFERENCES jenis_lembaga(id_jenis)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_komunitas_wilayah
        FOREIGN KEY (kode_wilayah)
        REFERENCES wilayah(kode)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_komunitas_direview_oleh
        FOREIGN KEY (direview_oleh)
        REFERENCES users(id_user)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    -- Status workflow validation
    CONSTRAINT chk_status_valid
        CHECK (status IN ('menunggu', 'aktif', 'ditolak', 'dinonaktifkan')),

    -- Alasan penolakan wajib jika ditolak
    CONSTRAINT chk_alasan_penolakan
        CHECK (
            (status = 'ditolak' AND alasan_penolakan IS NOT NULL AND TRIM(alasan_penolakan) != '') OR
            (status != 'ditolak')
        ),

    -- Reviewer wajib jika sudah direview (aktif/ditolak)
    CONSTRAINT chk_direview_oleh
        CHECK (
            (status IN ('aktif', 'ditolak', 'dinonaktifkan') AND direview_oleh IS NOT NULL) OR
            (status = 'menunggu')
        )
);

-- Indexes
CREATE INDEX idx_komunitas_user ON komunitas(id_user);
CREATE INDEX idx_komunitas_status ON komunitas(status);
CREATE INDEX idx_komunitas_wilayah ON komunitas(kode_wilayah);
CREATE INDEX idx_komunitas_jenis ON komunitas(id_jenis_lembaga);

-- Comments
COMMENT ON TABLE komunitas IS
'Data organisasi/lembaga yang mengelola campaign. One-to-one dengan users (role=KOMUNITAS)';

COMMENT ON COLUMN komunitas.id_user IS
'FK ke users (UNIQUE) - 1 user hanya bisa punya 1 komunitas';

COMMENT ON COLUMN komunitas.status IS
'Status verifikasi: menunggu (default), aktif (verified), ditolak (rejected), dinonaktifkan (suspended)';

COMMENT ON CONSTRAINT chk_alasan_penolakan ON komunitas IS
'Alasan penolakan wajib diisi dan tidak boleh kosong jika status = ditolak';

COMMENT ON CONSTRAINT chk_direview_oleh ON komunitas IS
'direview_oleh wajib diisi jika status sudah aktif/ditolak/dinonaktifkan';

-- ============================================================
-- SECTION 4: DOKUMEN KOMUNITAS
-- ============================================================

DROP TABLE IF EXISTS dokumen_komunitas CASCADE;

CREATE TABLE dokumen_komunitas (
    -- Primary Key
    id_dokumen   SERIAL PRIMARY KEY,

    -- Foreign Keys
    id_komunitas INT NOT NULL,
    id_jenis_dok INT NOT NULL,

    -- File Info
    file_url     VARCHAR(255) NOT NULL,

    -- Status Verifikasi
    status_verifikasi VARCHAR(20) DEFAULT 'menunggu',
    alasan_penolakan  TEXT,
    diverifikasi_oleh INT,

    -- Timestamps
    uploaded_at  TIMESTAMP NOT NULL DEFAULT NOW(),
    verified_at  TIMESTAMP,

    -- Constraints
    CONSTRAINT fk_dokumen_komunitas
        FOREIGN KEY (id_komunitas)
        REFERENCES komunitas(id_komunitas)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_dokumen_jenis_dok
        FOREIGN KEY (id_jenis_dok)
        REFERENCES jenis_dokumen(id_jenis_dok)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_dokumen_diverifikasi_oleh
        FOREIGN KEY (diverifikasi_oleh)
        REFERENCES users(id_user)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    -- File URL tidak boleh kosong
    CONSTRAINT chk_file_url_not_empty
        CHECK (TRIM(file_url) != ''),

    -- Status verifikasi validation
    CONSTRAINT chk_status_verifikasi_valid
        CHECK (status_verifikasi IN ('menunggu', 'diverifikasi', 'ditolak')),

    -- Alasan penolakan wajib jika ditolak
    CONSTRAINT chk_alasan_penolakan
        CHECK (
            (status_verifikasi = 'ditolak' AND alasan_penolakan IS NOT NULL AND TRIM(alasan_penolakan) != '') OR
            (status_verifikasi != 'ditolak')
        ),

    -- Verified_at wajib jika sudah diverifikasi/ditolak
    CONSTRAINT chk_verified_at
        CHECK (
            (status_verifikasi IN ('diverifikasi', 'ditolak') AND verified_at IS NOT NULL) OR
            (status_verifikasi = 'menunggu')
        ),

    -- Verifikator wajib jika sudah diverifikasi/ditolak
    CONSTRAINT chk_diverifikasi_oleh
        CHECK (
            (status_verifikasi IN ('diverifikasi', 'ditolak') AND diverifikasi_oleh IS NOT NULL) OR
            (status_verifikasi = 'menunggu')
        ),

    -- CRITICAL: Tidak boleh upload dokumen duplicate
    CONSTRAINT uq_komunitas_jenis_dok
        UNIQUE (id_komunitas, id_jenis_dok)
);

-- Indexes
CREATE INDEX idx_dokumen_komunitas ON dokumen_komunitas(id_komunitas);
CREATE INDEX idx_dokumen_jenis_dok ON dokumen_komunitas(id_jenis_dok);
CREATE INDEX idx_dokumen_status ON dokumen_komunitas(status_verifikasi);
CREATE INDEX idx_dokumen_uploaded ON dokumen_komunitas(uploaded_at DESC);

-- Comments
COMMENT ON TABLE dokumen_komunitas IS
'Dokumen legalitas komunitas (NPWP, Akta, SK, dll). Setiap jenis dokumen hanya boleh 1 per komunitas.';

COMMENT ON COLUMN dokumen_komunitas.status_verifikasi IS
'Status verifikasi dokumen: menunggu (default), diverifikasi (approved), ditolak (rejected)';

COMMENT ON CONSTRAINT uq_komunitas_jenis_dok ON dokumen_komunitas IS
'CRITICAL: 1 komunitas hanya boleh punya 1 dokumen per jenis. Tidak boleh duplicate.';

COMMENT ON CONSTRAINT chk_alasan_penolakan ON dokumen_komunitas IS
'Alasan penolakan wajib diisi jika status = ditolak';

-- ============================================================
-- SECTION 5: CAMPAIGN
-- ============================================================

DROP TABLE IF EXISTS campaign CASCADE;

CREATE TABLE campaign (
    -- Primary Key
    id_campaign                       SERIAL PRIMARY KEY,

    -- Foreign Keys
    id_komunitas                      INT NOT NULL,
    id_kategori                       INT NOT NULL,
    kode_wilayah                      VARCHAR(13) NOT NULL,

    -- Campaign Info
    judul                             VARCHAR(255) NOT NULL,
    deskripsi                         TEXT,
    foto_campaign_url                 VARCHAR(255) NOT NULL,

    -- Target & Dana
    target_dana                       BIGINT NOT NULL,
    dana_terkumpul                    BIGINT NOT NULL DEFAULT 0,
    saldo_tersedia                    BIGINT NOT NULL DEFAULT 0,
    saldo_terkunci                    BIGINT NOT NULL DEFAULT 0,

    -- Distribusi
    tipe_distribusi                   VARCHAR(20) NOT NULL DEFAULT 'individual',
    target_audiens                    VARCHAR(150),
    total_penerima_manfaat            INT NOT NULL DEFAULT 0,

    -- Pencairan Stats
    jumlah_pencairan_approve          INT NOT NULL DEFAULT 0,
    potongan_platform_sudah_dipotong  BOOLEAN NOT NULL DEFAULT FALSE,

    -- Timeline
    tanggal_mulai                     DATE,
    tanggal_selesai                   DATE,

    -- Status & Review
    status                            VARCHAR(30) NOT NULL DEFAULT 'menunggu_review',
    alasan_penolakan                  TEXT,
    url_rab                           VARCHAR(255),
    direview_oleh                     INT,

    -- Timestamps
    created_at                        TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at                        TIMESTAMP NOT NULL DEFAULT NOW(),
    deleted_at                        TIMESTAMP,

    -- Constraints
    CONSTRAINT fk_campaign_komunitas
        FOREIGN KEY (id_komunitas)
        REFERENCES komunitas(id_komunitas)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_campaign_kategori
        FOREIGN KEY (id_kategori)
        REFERENCES kategori_campaign(id_kategori)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_campaign_wilayah
        FOREIGN KEY (kode_wilayah)
        REFERENCES wilayah(kode)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_campaign_direview_oleh
        FOREIGN KEY (direview_oleh)
        REFERENCES users(id_user)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    -- Business Rules
    CONSTRAINT chk_target_dana_minimal
        CHECK (target_dana >= 10000000),

    CONSTRAINT chk_dana_terkumpul_positif
        CHECK (dana_terkumpul >= 0),

    CONSTRAINT chk_saldo_positif
        CHECK (saldo_tersedia >= 0 AND saldo_terkunci >= 0),

    CONSTRAINT chk_total_penerima_positif
        CHECK (total_penerima_manfaat >= 0),

    CONSTRAINT chk_jumlah_pencairan_positif
        CHECK (jumlah_pencairan_approve >= 0),

    -- CRITICAL: Dana terkumpul max 120% dari target
    CONSTRAINT chk_dana_terkumpul_max_120pct
        CHECK (dana_terkumpul <= target_dana * 1.2),

    -- Saldo valid (modified version - see below)
    CONSTRAINT chk_saldo_valid
        CHECK (
            saldo_tersedia >= 0 AND
            saldo_terkunci >= 0 AND
            saldo_tersedia + saldo_terkunci <= dana_terkumpul
        ),

    -- Tipe distribusi validation
    CONSTRAINT chk_tipe_distribusi_valid
        CHECK (tipe_distribusi IN ('individual', 'kolektif')),

    -- Campaign individual wajib ada target_audiens
    CONSTRAINT chk_target_audiens_individual
        CHECK (
            (tipe_distribusi = 'individual' AND target_audiens IS NOT NULL) OR
            (tipe_distribusi = 'kolektif')
        ),

    -- Status validation
    CONSTRAINT chk_status_valid
        CHECK (status IN (
            'menunggu_review', 'aktif', 'selesai',
            'ditolak', 'nonaktif', 'ditutup_permanen'
        )),

    -- Alasan penolakan wajib jika ditolak
    CONSTRAINT chk_alasan_penolakan
        CHECK (
            (status = 'ditolak' AND alasan_penolakan IS NOT NULL AND TRIM(alasan_penolakan) != '') OR
            (status != 'ditolak')
        ),

    -- Timeline validation
    CONSTRAINT chk_tanggal_valid
        CHECK (
            tanggal_selesai IS NULL OR
            tanggal_mulai IS NULL OR
            tanggal_selesai >= tanggal_mulai
        )
);

-- Indexes
CREATE INDEX idx_campaign_komunitas ON campaign(id_komunitas);
CREATE INDEX idx_campaign_kategori ON campaign(id_kategori);
CREATE INDEX idx_campaign_status ON campaign(status);
CREATE INDEX idx_campaign_wilayah ON campaign(kode_wilayah);
CREATE INDEX idx_campaign_tipe ON campaign(tipe_distribusi);
CREATE INDEX idx_campaign_active ON campaign(status) WHERE status = 'aktif';

-- Comments
COMMENT ON TABLE campaign IS
'Master data campaign fundraising. Constraint ketat untuk saldo dan dana terkumpul.';

COMMENT ON CONSTRAINT chk_dana_terkumpul_max_120pct ON campaign IS
'Dana terkumpul maksimal 120% dari target (overfunding limit)';

COMMENT ON COLUMN campaign.saldo_tersedia IS
'Dana yang siap dicairkan';

COMMENT ON COLUMN campaign.saldo_terkunci IS
'Dana yang sedang dalam proses pencairan (status menunggu_review/disetujui)';

-- ============================================================
-- SECTION 6: DONASI
-- ============================================================

DROP TABLE IF EXISTS donasi CASCADE;

CREATE TABLE donasi (
    -- Primary Key
    id_donasi          SERIAL PRIMARY KEY,

    -- Foreign Keys
    id_user            INT NOT NULL,
    id_campaign        INT NOT NULL,

    -- Donasi Info
    nominal            BIGINT NOT NULL,
    metode_pembayaran  VARCHAR(20) NOT NULL,

    -- Privacy
    nama_tampil        VARCHAR(100),
    is_anonim          BOOLEAN NOT NULL DEFAULT TRUE,

    -- Status & Bukti
    status_pembayaran  VARCHAR(10) NOT NULL DEFAULT 'pending',
    bukti_pdf_url      VARCHAR(255),

    -- Timestamp
    created_at         TIMESTAMP NOT NULL DEFAULT NOW(),

    -- Constraints
    CONSTRAINT fk_donasi_user
        FOREIGN KEY (id_user)
        REFERENCES users(id_user)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_donasi_campaign
        FOREIGN KEY (id_campaign)
        REFERENCES campaign(id_campaign)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    -- Nominal positif
    CONSTRAINT chk_nominal_positif
        CHECK (nominal > 0),

    -- Metode pembayaran validation
    CONSTRAINT chk_metode_pembayaran_valid
        CHECK (metode_pembayaran IN (
            'qris', 'gopay', 'ovo', 'shopeepay',
            'bca', 'mandiri', 'bni'
        )),

    -- Status pembayaran validation
    CONSTRAINT chk_status_pembayaran_valid
        CHECK (status_pembayaran IN ('pending', 'berhasil', 'gagal')),

    -- CRITICAL: Jika anonim, nama_tampil harus NULL
    CONSTRAINT chk_nama_tampil_anonim
        CHECK (
            (is_anonim = TRUE AND nama_tampil IS NULL) OR
            (is_anonim = FALSE)
        ),

    -- CRITICAL: Jika berhasil, wajib ada bukti PDF
    CONSTRAINT chk_bukti_pdf_berhasil
        CHECK (
            (status_pembayaran = 'berhasil' AND bukti_pdf_url IS NOT NULL) OR
            (status_pembayaran IN ('pending', 'gagal'))
        )
);

-- Indexes
CREATE INDEX idx_donasi_user ON donasi(id_user);
CREATE INDEX idx_donasi_campaign ON donasi(id_campaign);
CREATE INDEX idx_donasi_status ON donasi(status_pembayaran);
CREATE INDEX idx_donasi_created ON donasi(created_at DESC);
CREATE INDEX idx_donasi_berhasil ON donasi(id_campaign, status_pembayaran)
    WHERE status_pembayaran = 'berhasil';

-- Comments
COMMENT ON TABLE donasi IS
'Transaksi donasi dari user ke campaign. Hanya donasi berhasil yang masuk ke dana_terkumpul.';

COMMENT ON CONSTRAINT chk_nama_tampil_anonim ON donasi IS
'Jika anonim, nama_tampil harus NULL';

COMMENT ON CONSTRAINT chk_bukti_pdf_berhasil ON donasi IS
'Donasi berhasil wajib punya bukti PDF';

-- ============================================================
-- SECTION 7: PENCAIRAN DANA
-- ============================================================

DROP TABLE IF EXISTS pencairan_dana CASCADE;

CREATE TABLE pencairan_dana (
    -- Primary Key
    id_pencairan              SERIAL PRIMARY KEY,

    -- Foreign Keys
    id_campaign               INT NOT NULL,
    id_komunitas              INT NOT NULL,
    id_laporan_dana           INT,

    -- Urutan & Nominal
    urutan_ke                 INT NOT NULL,
    nominal_diajukan          BIGINT NOT NULL,
    nominal_disetujui         BIGINT,

    -- Keterangan & Proposal
    keterangan                TEXT NOT NULL,
    url_proposal              VARCHAR(255) NOT NULL,

    -- Rekening Tujuan
    nama_bank_tujuan          VARCHAR(50) NOT NULL,
    nomor_rekening_tujuan     VARCHAR(20) NOT NULL,

    -- Bukti Transfer
    bukti_transfer_url        VARCHAR(255),

    -- Status & Review
    status                    VARCHAR(20) NOT NULL DEFAULT 'menunggu_review',
    alasan_penolakan          TEXT,
    direview_oleh             INT,

    -- Timestamps
    tanggal_pengajuan         TIMESTAMP NOT NULL DEFAULT NOW(),
    tanggal_keputusan         TIMESTAMP,

    -- Constraints
    CONSTRAINT fk_pencairan_campaign
        FOREIGN KEY (id_campaign)
        REFERENCES campaign(id_campaign)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_pencairan_komunitas
        FOREIGN KEY (id_komunitas)
        REFERENCES komunitas(id_komunitas)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_pencairan_direview_oleh
        FOREIGN KEY (direview_oleh)
        REFERENCES users(id_user)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    -- Urutan validation
    CONSTRAINT chk_urutan_valid
        CHECK (urutan_ke BETWEEN 1 AND 5),

    -- Nominal validation
    CONSTRAINT chk_nominal_diajukan_positif
        CHECK (nominal_diajukan > 0),

    CONSTRAINT chk_nominal_disetujui_positif
        CHECK (nominal_disetujui IS NULL OR nominal_disetujui > 0),

    -- CRITICAL: Nominal disetujui <= nominal diajukan
    CONSTRAINT chk_nominal_disetujui_max
        CHECK (
            nominal_disetujui IS NULL OR
            nominal_disetujui <= nominal_diajukan
        ),

    -- Status validation
    CONSTRAINT chk_status_valid
        CHECK (status IN (
            'menunggu_review', 'disetujui', 'ditolak', 'selesai'
        )),

    -- Bukti transfer wajib jika selesai
    CONSTRAINT chk_bukti_transfer_selesai
        CHECK (
            (status = 'selesai' AND bukti_transfer_url IS NOT NULL) OR
            (status IN ('menunggu_review', 'disetujui', 'ditolak'))
        ),

    -- Alasan penolakan wajib jika ditolak
    CONSTRAINT chk_alasan_penolakan_ditolak
        CHECK (
            (status = 'ditolak' AND alasan_penolakan IS NOT NULL AND TRIM(alasan_penolakan) != '') OR
            (status IN ('menunggu_review', 'disetujui', 'selesai'))
        ),

    -- Nominal disetujui wajib jika disetujui/selesai
    CONSTRAINT chk_nominal_disetujui_wajib
        CHECK (
            (status IN ('disetujui', 'selesai') AND nominal_disetujui IS NOT NULL) OR
            (status IN ('menunggu_review', 'ditolak'))
        ),

    -- Tanggal keputusan wajib jika sudah ada keputusan
    CONSTRAINT chk_tanggal_keputusan
        CHECK (
            (status IN ('disetujui', 'ditolak', 'selesai') AND tanggal_keputusan IS NOT NULL) OR
            (status = 'menunggu_review')
        ),

    -- UNIQUE: Kombinasi campaign + urutan harus unique
    CONSTRAINT uq_campaign_urutan
        UNIQUE (id_campaign, urutan_ke)
);

-- Indexes
CREATE INDEX idx_pencairan_campaign ON pencairan_dana(id_campaign);
CREATE INDEX idx_pencairan_komunitas ON pencairan_dana(id_komunitas);
CREATE INDEX idx_pencairan_status ON pencairan_dana(status);
CREATE INDEX idx_pencairan_urutan ON pencairan_dana(id_campaign, urutan_ke);
CREATE INDEX idx_pencairan_laporan ON pencairan_dana(id_laporan_dana);

-- Comments
COMMENT ON TABLE pencairan_dana IS
'Pengajuan pencairan dana dari komunitas. Max 5x per campaign, urutan sequential.';

COMMENT ON COLUMN pencairan_dana.id_laporan_dana IS
'FK ke laporan dari pencairan SEBELUMNYA (N-1). NULL untuk urutan 1, WAJIB untuk urutan >= 2.';

COMMENT ON COLUMN pencairan_dana.urutan_ke IS
'Urutan pencairan (1-5). Harus sequential dan unique per campaign.';

COMMENT ON CONSTRAINT uq_campaign_urutan ON pencairan_dana IS
'Tidak boleh ada urutan kembar dalam 1 campaign';

COMMENT ON CONSTRAINT chk_nominal_disetujui_max ON pencairan_dana IS
'Nominal disetujui tidak boleh lebih besar dari nominal diajukan';

-- ============================================================
-- SECTION 8: POTONGAN PLATFORM
-- ============================================================

DROP TABLE IF EXISTS potongan_platform CASCADE;

CREATE TABLE potongan_platform (
    -- Primary Key
    id_potongan          SERIAL PRIMARY KEY,

    -- Foreign Key (UNIQUE)
    id_campaign          INT NOT NULL UNIQUE,

    -- Data Potongan
    total_dana_terkumpul BIGINT NOT NULL,
    persentase_potongan  DECIMAL(5,2) NOT NULL DEFAULT 1.00,
    nominal_potongan     BIGINT NOT NULL,

    -- Metadata
    dipotong_oleh        INT,
    catatan              TEXT,

    -- Timestamps
    dipotong_pada        TIMESTAMP NOT NULL DEFAULT NOW(),

    -- Constraints
    CONSTRAINT fk_potongan_campaign
        FOREIGN KEY (id_campaign)
        REFERENCES campaign(id_campaign)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_potongan_dipotong_oleh
        FOREIGN KEY (dipotong_oleh)
        REFERENCES users(id_user)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    -- Total dana terkumpul harus positif
    CONSTRAINT chk_total_dana_positif
        CHECK (total_dana_terkumpul > 0),

    -- Persentase potongan harus antara 0-100
    CONSTRAINT chk_persentase_valid
        CHECK (persentase_potongan >= 0 AND persentase_potongan <= 100),

    -- Nominal potongan harus positif
    CONSTRAINT chk_nominal_positif
        CHECK (nominal_potongan >= 0),

    -- Modified calculation with cap
    CONSTRAINT chk_nominal_calculation_with_cap
        CHECK (
            (nominal_potongan <= (total_dana_terkumpul * persentase_potongan / 100) + 1)
            AND (nominal_potongan <= 50000)
        ),

    -- Tanggal dipotong tidak boleh masa depan
    CONSTRAINT chk_dipotong_pada_valid
        CHECK (dipotong_pada <= NOW())
);

-- Indexes
CREATE INDEX idx_potongan_campaign ON potongan_platform(id_campaign);
CREATE INDEX idx_potongan_dipotong_pada ON potongan_platform(dipotong_pada DESC);

-- Comments
COMMENT ON TABLE potongan_platform IS
'Pencatatan fee platform (default 1%). 1 campaign max 1 potongan, dipotong saat campaign selesai.';

COMMENT ON COLUMN potongan_platform.total_dana_terkumpul IS
'Snapshot dana_terkumpul saat potongan dieksekusi';

COMMENT ON COLUMN potongan_platform.persentase_potongan IS
'Persentase fee platform (default 1.00%). Bisa disesuaikan per campaign.';

COMMENT ON COLUMN potongan_platform.nominal_potongan IS
'Nominal potongan yang dipotong = total_dana_terkumpul * persentase_potongan / 100';

COMMENT ON CONSTRAINT fk_potongan_campaign ON potongan_platform IS
'FK ke campaign (UNIQUE). 1 campaign hanya boleh 1 potongan.';

-- ============================================================
-- SECTION 9: LAPORAN PENGGUNAAN DANA
-- ============================================================

DROP TABLE IF EXISTS laporan_penggunaan_dana CASCADE;

CREATE TABLE laporan_penggunaan_dana (
    -- Primary Key
    id_laporan              SERIAL PRIMARY KEY,

    -- Foreign Key
    id_pencairan            INT NOT NULL,

    -- Data Laporan
    jumlah_penerima         INT NOT NULL DEFAULT 0,
    deskripsi_penggunaan    TEXT NOT NULL,
    total_realisasi         DECIMAL(15,2) NOT NULL,

    -- Dokumentasi
    file_dokumentasi_url    VARCHAR(500),

    -- Status & Verifikasi
    status_verifikasi       VARCHAR(25) NOT NULL DEFAULT 'menunggu_verifikasi',
    alasan_penolakan        TEXT,
    diverifikasi_oleh       INT,

    -- Timestamps
    tanggal_laporan         TIMESTAMP NOT NULL DEFAULT NOW(),
    tanggal_verifikasi      TIMESTAMP,
    created_at              TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at              TIMESTAMP NOT NULL DEFAULT NOW(),

    -- Constraints
    CONSTRAINT fk_laporan_pencairan
        FOREIGN KEY (id_pencairan)
        REFERENCES pencairan_dana(id_pencairan)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_laporan_diverifikasi_oleh
        FOREIGN KEY (diverifikasi_oleh)
        REFERENCES users(id_user)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    -- Status validation
    CONSTRAINT chk_status_verifikasi_valid
        CHECK (status_verifikasi IN (
            'menunggu_verifikasi',
            'diverifikasi',
            'ditolak'
        )),

    -- Jumlah penerima tidak boleh negatif
    CONSTRAINT chk_jumlah_penerima_positif
        CHECK (jumlah_penerima >= 0),

    -- Total realisasi harus positif
    CONSTRAINT chk_total_realisasi_positif
        CHECK (total_realisasi >= 0),

    -- Alasan penolakan wajib jika ditolak
    CONSTRAINT chk_alasan_penolakan
        CHECK (
            (status_verifikasi = 'ditolak' AND alasan_penolakan IS NOT NULL AND TRIM(alasan_penolakan) != '') OR
            (status_verifikasi != 'ditolak')
        ),

    -- Tanggal laporan tidak boleh masa depan
    CONSTRAINT chk_tanggal_laporan_valid
        CHECK (tanggal_laporan <= NOW()),

    -- Tanggal verifikasi wajib jika sudah diverifikasi/ditolak
    CONSTRAINT chk_tanggal_verifikasi
        CHECK (
            (status_verifikasi IN ('diverifikasi', 'ditolak') AND tanggal_verifikasi IS NOT NULL) OR
            (status_verifikasi = 'menunggu_verifikasi')
        ),

    -- Verifikator wajib jika sudah diverifikasi/ditolak
    CONSTRAINT chk_diverifikasi_oleh
        CHECK (
            (status_verifikasi IN ('diverifikasi', 'ditolak') AND diverifikasi_oleh IS NOT NULL) OR
            (status_verifikasi = 'menunggu_verifikasi')
        )
);

-- Indexes
CREATE INDEX idx_laporan_pencairan ON laporan_penggunaan_dana(id_pencairan);
CREATE INDEX idx_laporan_status ON laporan_penggunaan_dana(status_verifikasi);
CREATE INDEX idx_laporan_tanggal ON laporan_penggunaan_dana(tanggal_laporan DESC);
CREATE INDEX idx_laporan_pencairan_status ON laporan_penggunaan_dana(id_pencairan, status_verifikasi);
CREATE INDEX idx_laporan_verified ON laporan_penggunaan_dana(id_pencairan)
    WHERE status_verifikasi = 'diverifikasi';

-- Comments
COMMENT ON TABLE laporan_penggunaan_dana IS
'Laporan penggunaan dana dari setiap pencairan. Wajib untuk pencairan kedua dst.';

COMMENT ON COLUMN laporan_penggunaan_dana.id_pencairan IS
'FK ke pencairan_dana - laporan untuk pencairan mana';

COMMENT ON COLUMN laporan_penggunaan_dana.jumlah_penerima IS
'Jumlah penerima manfaat. 0 untuk campaign kolektif (infrastruktur, dll). Harus match dengan COUNT(penerima_manfaat).';

COMMENT ON COLUMN laporan_penggunaan_dana.total_realisasi IS
'Total dana yang benar-benar terpakai. Harus <= nominal_disetujui dan = SUM(penerima_manfaat.nominal_diterima)';

-- ============================================================
-- SECTION 10: PENERIMA MANFAAT
-- ============================================================

DROP TABLE IF EXISTS penerima_manfaat CASCADE;

CREATE TABLE penerima_manfaat (
    -- Primary Key
    id_penerima         SERIAL PRIMARY KEY,

    -- Foreign Key
    id_laporan          INT NOT NULL,

    -- Data Penerima
    nama                VARCHAR(255) NOT NULL,
    nik                 VARCHAR(16) NOT NULL,
    kode_wilayah        VARCHAR(13) NOT NULL,
    nominal_diterima    DECIMAL(15,2) NOT NULL,

    -- Timestamp
    created_at          TIMESTAMP NOT NULL DEFAULT NOW(),

    -- Constraints
    CONSTRAINT fk_penerima_laporan
        FOREIGN KEY (id_laporan)
        REFERENCES laporan_penggunaan_dana(id_laporan)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_penerima_wilayah
        FOREIGN KEY (kode_wilayah)
        REFERENCES wilayah(kode)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    -- NIK format validation (16 digit)
    CONSTRAINT chk_nik_format
        CHECK (nik ~ '^[0-9]{16}$'),

    -- Nominal harus positif
    CONSTRAINT chk_nominal_positif
        CHECK (nominal_diterima > 0)
);

-- Indexes
CREATE INDEX idx_penerima_laporan ON penerima_manfaat(id_laporan);
CREATE INDEX idx_penerima_nik ON penerima_manfaat(nik);
CREATE INDEX idx_penerima_wilayah ON penerima_manfaat(kode_wilayah);
CREATE INDEX idx_penerima_nama ON penerima_manfaat(nama);

-- Comments
COMMENT ON TABLE penerima_manfaat IS
'Detail penerima manfaat individual dari setiap laporan. Hanya untuk campaign individual (Pendidikan, Sosial, Kesehatan).';

COMMENT ON COLUMN penerima_manfaat.id_laporan IS
'FK ke laporan_penggunaan_dana - penerima dari laporan mana';

COMMENT ON COLUMN penerima_manfaat.nik IS
'NIK penerima (16 digit). UNIQUE per campaign, boleh duplikat lintas campaign.';

COMMENT ON COLUMN penerima_manfaat.kode_wilayah IS
'Kode wilayah domisili penerima (level kota/kabupaten). 80% lokal campaign, 20% luar daerah.';

COMMENT ON COLUMN penerima_manfaat.nominal_diterima IS
'Nominal dana yang diterima penerima. SUM per laporan harus = laporan.total_realisasi';

-- ============================================================
-- SECTION 11: UPDATE CAMPAIGN
-- ============================================================

DROP TABLE IF EXISTS update_campaign CASCADE;

CREATE TABLE update_campaign (
    -- Primary Key
    id_update    SERIAL PRIMARY KEY,

    -- Foreign Keys
    id_campaign  INT NOT NULL,
    id_komunitas INT NOT NULL,

    -- Content
    judul_update VARCHAR(255) NOT NULL,
    konten       TEXT NOT NULL,

    -- Metadata
    is_pinned    BOOLEAN NOT NULL DEFAULT FALSE,

    -- Timestamps
    created_at   TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at   TIMESTAMP NOT NULL DEFAULT NOW(),

    -- Constraints
    CONSTRAINT fk_update_campaign
        FOREIGN KEY (id_campaign)
        REFERENCES campaign(id_campaign)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_update_komunitas
        FOREIGN KEY (id_komunitas)
        REFERENCES komunitas(id_komunitas)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    -- Judul tidak boleh kosong
    CONSTRAINT chk_judul_not_empty
        CHECK (TRIM(judul_update) != ''),

    -- Konten tidak boleh kosong
    CONSTRAINT chk_konten_not_empty
        CHECK (TRIM(konten) != ''),

    -- Timestamps validation
    CONSTRAINT chk_timestamps_valid
        CHECK (updated_at >= created_at)
);

-- Indexes
CREATE INDEX idx_update_campaign ON update_campaign(id_campaign);
CREATE INDEX idx_update_komunitas ON update_campaign(id_komunitas);
CREATE INDEX idx_update_created ON update_campaign(created_at DESC);
CREATE INDEX idx_update_pinned ON update_campaign(is_pinned) WHERE is_pinned = TRUE;

-- Comments
COMMENT ON TABLE update_campaign IS
'Progress update dari komunitas untuk transparansi campaign. Bisa punya multiple foto.';

COMMENT ON COLUMN update_campaign.is_pinned IS
'Update penting bisa di-pin di bagian atas feed';

COMMENT ON COLUMN update_campaign.konten IS
'Konten update (text/markdown). Wajib diisi dan tidak boleh kosong.';

-- ============================================================
-- SECTION 12: FOTO UPDATE
-- ============================================================

DROP TABLE IF EXISTS foto_update CASCADE;

CREATE TABLE foto_update (
    -- Primary Key
    id_foto     SERIAL PRIMARY KEY,

    -- Foreign Key
    id_update   INT NOT NULL,

    -- File Info
    foto_url    VARCHAR(255) NOT NULL,
    caption     VARCHAR(500),

    -- Urutan foto
    urutan      INT NOT NULL DEFAULT 1,

    -- Timestamps
    uploaded_at TIMESTAMP NOT NULL DEFAULT NOW(),

    -- Constraints
    CONSTRAINT fk_foto_update
        FOREIGN KEY (id_update)
        REFERENCES update_campaign(id_update)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    -- Foto URL tidak boleh kosong
    CONSTRAINT chk_foto_url_not_empty
        CHECK (TRIM(foto_url) != ''),

    -- Urutan harus positif
    CONSTRAINT chk_urutan_positif
        CHECK (urutan > 0),

    -- CRITICAL: Urutan harus unique per update
    CONSTRAINT uq_update_urutan
        UNIQUE (id_update, urutan)
);

-- Indexes
CREATE INDEX idx_foto_update ON foto_update(id_update);
CREATE INDEX idx_foto_urutan ON foto_update(id_update, urutan);

-- Comments
COMMENT ON TABLE foto_update IS
'Multiple foto per update_campaign. Max 10 foto per update (dicek di application logic).';

COMMENT ON COLUMN foto_update.urutan IS
'Urutan foto (1, 2, 3, ...). Unique per update, tidak boleh kembar.';

COMMENT ON COLUMN foto_update.caption IS
'Caption opsional per foto untuk deskripsi tambahan';

COMMENT ON CONSTRAINT uq_update_urutan ON foto_update IS
'CRITICAL: Urutan foto harus unique per update. Tidak boleh ada urutan kembar.';

-- ============================================================
-- SECTION 13: VERIFIKASI REKENING
-- ============================================================

DROP TABLE IF EXISTS verifikasi_rekening CASCADE;

CREATE TABLE verifikasi_rekening (
    -- Primary Key
    id_verif                SERIAL PRIMARY KEY,

    -- Foreign Key
    id_komunitas            INT NOT NULL,

    -- Rekening Baru (yang diajukan)
    nama_bank_baru          VARCHAR(100) NOT NULL,
    nomor_rekening_baru     VARCHAR(50) NOT NULL,
    foto_buku_rekening_url  VARCHAR(255) NOT NULL,

    -- Alasan Perubahan
    alasan_perubahan        TEXT NOT NULL,

    -- Status & Review
    status                  VARCHAR(20) NOT NULL DEFAULT 'menunggu',
    alasan_penolakan        TEXT,
    direview_oleh           INT,

    -- Timestamps
    created_at              TIMESTAMP NOT NULL DEFAULT NOW(),
    tanggal_keputusan       TIMESTAMP,

    -- Constraints
    CONSTRAINT fk_verifikasi_komunitas
        FOREIGN KEY (id_komunitas)
        REFERENCES komunitas(id_komunitas)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_verifikasi_direview_oleh
        FOREIGN KEY (direview_oleh)
        REFERENCES users(id_user)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    -- Status validation
    CONSTRAINT chk_status_valid
        CHECK (status IN ('menunggu', 'disetujui', 'ditolak')),

    -- Nama bank tidak boleh kosong
    CONSTRAINT chk_nama_bank_not_empty
        CHECK (TRIM(nama_bank_baru) != ''),

    -- Nomor rekening tidak boleh kosong
    CONSTRAINT chk_nomor_rekening_not_empty
        CHECK (TRIM(nomor_rekening_baru) != ''),

    -- Foto buku rekening wajib
    CONSTRAINT chk_foto_buku_rekening_not_empty
        CHECK (TRIM(foto_buku_rekening_url) != ''),

    -- Alasan perubahan wajib diisi
    CONSTRAINT chk_alasan_perubahan_not_empty
        CHECK (TRIM(alasan_perubahan) != ''),

    -- Alasan penolakan wajib jika ditolak
    CONSTRAINT chk_alasan_penolakan
        CHECK (
            (status = 'ditolak' AND alasan_penolakan IS NOT NULL AND TRIM(alasan_penolakan) != '') OR
            (status != 'ditolak')
        ),

    -- Reviewer wajib jika sudah ada keputusan
    CONSTRAINT chk_direview_oleh
        CHECK (
            (status IN ('disetujui', 'ditolak') AND direview_oleh IS NOT NULL) OR
            (status = 'menunggu')
        ),

    -- Tanggal keputusan wajib jika sudah ada keputusan
    CONSTRAINT chk_tanggal_keputusan
        CHECK (
            (status IN ('disetujui', 'ditolak') AND tanggal_keputusan IS NOT NULL) OR
            (status = 'menunggu')
        )
);

-- Indexes
CREATE INDEX idx_verifikasi_komunitas ON verifikasi_rekening(id_komunitas);
CREATE INDEX idx_verifikasi_status ON verifikasi_rekening(status);
CREATE INDEX idx_verifikasi_direview ON verifikasi_rekening(direview_oleh);
CREATE INDEX idx_verifikasi_created ON verifikasi_rekening(created_at DESC);

-- CRITICAL: Hanya boleh 1 request 'menunggu' per komunitas
CREATE UNIQUE INDEX idx_verifikasi_menunggu_per_komunitas
    ON verifikasi_rekening(id_komunitas)
    WHERE status = 'menunggu';

-- Comments
COMMENT ON TABLE verifikasi_rekening IS
'Request perubahan rekening komunitas. Harus diverifikasi SuperAdmin untuk keamanan.';

COMMENT ON COLUMN verifikasi_rekening.alasan_perubahan IS
'Alasan mengapa komunitas ingin ganti rekening. Wajib diisi untuk audit trail.';

COMMENT ON COLUMN verifikasi_rekening.foto_buku_rekening_url IS
'Foto buku rekening baru sebagai bukti kepemilikan';

COMMENT ON INDEX idx_verifikasi_menunggu_per_komunitas IS
'CRITICAL: 1 komunitas hanya boleh punya 1 request dengan status menunggu. Tidak boleh double request.';

COMMENT ON CONSTRAINT chk_alasan_perubahan_not_empty ON verifikasi_rekening IS
'Alasan perubahan wajib diisi untuk audit trail dan keamanan';

-- ============================================================
-- SECTION 14: FOLLOW KOMUNITAS
-- ============================================================

DROP TABLE IF EXISTS follow_komunitas CASCADE;

CREATE TABLE follow_komunitas (
    -- Primary Key
    id_follow       SERIAL PRIMARY KEY,

    -- Foreign Keys
    id_user         INT NOT NULL,
    id_komunitas    INT NOT NULL,

    -- Status
    is_active       BOOLEAN NOT NULL DEFAULT TRUE,

    -- Timestamps
    followed_at     TIMESTAMP NOT NULL DEFAULT NOW(),
    unfollowed_at   TIMESTAMP,

    -- Constraints
    CONSTRAINT fk_follow_user
        FOREIGN KEY (id_user)
        REFERENCES users(id_user)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_follow_komunitas
        FOREIGN KEY (id_komunitas)
        REFERENCES komunitas(id_komunitas)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    -- unfollowed_at hanya boleh ada jika is_active = FALSE
    CONSTRAINT chk_unfollowed_at_requires_inactive
        CHECK (
            (is_active = FALSE AND unfollowed_at IS NOT NULL) OR
            (is_active = TRUE AND unfollowed_at IS NULL)
        ),

    -- unfollowed_at harus >= followed_at
    CONSTRAINT chk_unfollow_after_follow
        CHECK (unfollowed_at IS NULL OR unfollowed_at >= followed_at)
);

-- Indexes
CREATE INDEX idx_follow_user
    ON follow_komunitas(id_user, is_active);

CREATE INDEX idx_follow_komunitas
    ON follow_komunitas(id_komunitas, is_active);

CREATE INDEX idx_follow_active
    ON follow_komunitas(is_active, followed_at DESC);

-- Unique constraint
CREATE UNIQUE INDEX idx_follow_user_komunitas_active
    ON follow_komunitas(id_user, id_komunitas)
    WHERE is_active = TRUE;

-- Comments
COMMENT ON TABLE follow_komunitas IS
'Follow relationship antara user dan komunitas. Soft delete pattern dengan is_active untuk track unfollow. Re-follow akan insert record baru untuk keep history.';

COMMENT ON COLUMN follow_komunitas.is_active IS
'Status follow. FALSE jika user sudah unfollow. Re-follow akan insert record baru dengan is_active=TRUE.';

COMMENT ON COLUMN follow_komunitas.unfollowed_at IS
'Timestamp kapan user unfollow. NULL jika masih aktif follow.';

COMMENT ON INDEX idx_follow_user_komunitas_active IS
'CRITICAL: User hanya boleh punya 1 follow aktif per komunitas. Multiple records allowed untuk history.';

-- ============================================================
-- SECTION 15: NOTIFIKASI
-- ============================================================

DROP TABLE IF EXISTS notifikasi CASCADE;

CREATE TABLE notifikasi (
    -- Primary Key
    id_notif               SERIAL PRIMARY KEY,

    -- Penerima (XOR: exactly one must be NOT NULL)
    id_penerima_user       INT,
    id_penerima_komunitas  INT,

    -- Pengirim/Actor (siapa yang trigger notifikasi)
    id_pengirim_user       INT,

    -- Content
    judul                  VARCHAR(255) NOT NULL,
    pesan                  TEXT NOT NULL,
    tipe                   VARCHAR(20) NOT NULL
                             CHECK (tipe IN (
                                 'donasi',
                                 'pencairan',
                                 'withdrawal',
                                 'campaign',
                                 'update_campaign',
                                 'follow',
                                 'laporan',
                                 'verifikasi',
                                 'sistem'
                             )),

    -- References untuk build URL di aplikasi
    related_campaign_id    INT,
    related_donasi_id      INT,
    related_update_id      INT,
    related_verifikasi_id  INT,
    related_pencairan_id   INT,

    -- Read tracking
    is_read                BOOLEAN NOT NULL DEFAULT FALSE,
    read_at                TIMESTAMP,

    -- TTL & Archive
    created_at             TIMESTAMP NOT NULL DEFAULT NOW(),
    expires_at             TIMESTAMP NOT NULL,
    is_archived            BOOLEAN NOT NULL DEFAULT FALSE,
    archived_at            TIMESTAMP,

    -- Constraints

    -- CRITICAL: Penerima XOR (exactly one: user OR komunitas)
    CONSTRAINT chk_penerima_xor
        CHECK (
            (id_penerima_user IS NOT NULL AND id_penerima_komunitas IS NULL) OR
            (id_penerima_user IS NULL AND id_penerima_komunitas IS NOT NULL)
        ),

    -- read_at hanya boleh ada jika is_read = TRUE
    CONSTRAINT chk_read_at_requires_is_read
        CHECK (
            (is_read = TRUE AND read_at IS NOT NULL) OR
            (is_read = FALSE AND read_at IS NULL)
        ),

    -- archived_at hanya boleh ada jika is_archived = TRUE
    CONSTRAINT chk_archived_at_requires_is_archived
        CHECK (
            (is_archived = TRUE AND archived_at IS NOT NULL) OR
            (is_archived = FALSE AND archived_at IS NULL)
        ),

    -- expires_at harus >= created_at
    CONSTRAINT chk_expires_after_created
        CHECK (expires_at >= created_at),

    -- read_at harus >= created_at
    CONSTRAINT chk_read_after_created
        CHECK (read_at IS NULL OR read_at >= created_at),

    -- archived_at harus >= created_at
    CONSTRAINT chk_archived_after_created
        CHECK (archived_at IS NULL OR archived_at >= created_at),

    -- Tipe 'donasi' harus punya related_donasi_id
    CONSTRAINT chk_donasi_requires_donasi_id
        CHECK (
            tipe != 'donasi' OR related_donasi_id IS NOT NULL
        ),

    -- Tipe 'follow' harus punya id_pengirim_user (siapa yang follow)
    CONSTRAINT chk_follow_requires_pengirim
        CHECK (
            tipe != 'follow' OR id_pengirim_user IS NOT NULL
        ),

    -- Tipe 'pencairan' atau 'withdrawal' harus punya related_pencairan_id
    CONSTRAINT chk_pencairan_requires_pencairan_id
        CHECK (
            tipe NOT IN ('pencairan', 'withdrawal') OR related_pencairan_id IS NOT NULL
        ),

    -- Tipe 'update_campaign' harus punya related_update_id
    CONSTRAINT chk_update_requires_update_id
        CHECK (
            tipe != 'update_campaign' OR related_update_id IS NOT NULL
        ),

    -- Tipe 'verifikasi' harus punya related_verifikasi_id
    CONSTRAINT chk_verifikasi_requires_verifikasi_id
        CHECK (
            tipe != 'verifikasi' OR related_verifikasi_id IS NOT NULL
        ),

    -- Tipe 'campaign' harus punya related_campaign_id
    CONSTRAINT chk_campaign_requires_campaign_id
        CHECK (
            tipe != 'campaign' OR related_campaign_id IS NOT NULL
        ),

    -- Foreign Keys
    CONSTRAINT fk_notif_penerima_user
        FOREIGN KEY (id_penerima_user)
        REFERENCES users(id_user)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_notif_penerima_komunitas
        FOREIGN KEY (id_penerima_komunitas)
        REFERENCES komunitas(id_komunitas)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_notif_pengirim_user
        FOREIGN KEY (id_pengirim_user)
        REFERENCES users(id_user)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT fk_notif_campaign
        FOREIGN KEY (related_campaign_id)
        REFERENCES campaign(id_campaign)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_notif_donasi
        FOREIGN KEY (related_donasi_id)
        REFERENCES donasi(id_donasi)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_notif_update
        FOREIGN KEY (related_update_id)
        REFERENCES update_campaign(id_update)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_notif_verifikasi
        FOREIGN KEY (related_verifikasi_id)
        REFERENCES verifikasi_rekening(id_verif)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_notif_pencairan
        FOREIGN KEY (related_pencairan_id)
        REFERENCES pencairan_dana(id_pencairan)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

-- Indexes
CREATE INDEX idx_notif_user_active
    ON notifikasi(id_penerima_user, is_archived, is_read, created_at DESC)
    WHERE id_penerima_user IS NOT NULL;

CREATE INDEX idx_notif_komunitas_active
    ON notifikasi(id_penerima_komunitas, is_archived, is_read, created_at DESC)
    WHERE id_penerima_komunitas IS NOT NULL;

CREATE INDEX idx_notif_expires
    ON notifikasi(expires_at, is_archived)
    WHERE is_archived = FALSE;

CREATE INDEX idx_notif_tipe
    ON notifikasi(tipe, created_at DESC);

CREATE INDEX idx_notif_unread
    ON notifikasi(id_penerima_user, is_read)
    WHERE is_read = FALSE AND is_archived = FALSE;

-- Comments
COMMENT ON TABLE notifikasi IS
'Notifikasi untuk user dan komunitas. Penerima enforce XOR (hanya user ATAU komunitas). TTL system: semua notifikasi wajib punya expires_at. Soft archive dengan is_archived (manual set oleh aplikasi).';

COMMENT ON COLUMN notifikasi.id_penerima_user IS
'Penerima notifikasi (user). XOR dengan id_penerima_komunitas - hanya boleh satu yang NOT NULL.';

COMMENT ON COLUMN notifikasi.id_penerima_komunitas IS
'Penerima notifikasi (komunitas). XOR dengan id_penerima_user - hanya boleh satu yang NOT NULL.';

COMMENT ON COLUMN notifikasi.id_pengirim_user IS
'Actor/sender yang trigger notifikasi. Contoh: user yang follow, user yang donasi, etc. NULL untuk system-generated notifications.';

COMMENT ON COLUMN notifikasi.tipe IS
'Tipe notifikasi: donasi (donasi baru), pencairan (pencairan diproses), withdrawal (dana dicairkan), campaign (campaign approved/rejected), update_campaign (update baru), follow (follower baru), laporan (laporan campaign), verifikasi (verifikasi rekening), sistem (system announcement)';

COMMENT ON COLUMN notifikasi.expires_at IS
'TTL (Time To Live). Notifikasi kadaluarsa setelah tanggal ini. Duration based on tipe: follow=30d, donasi=60d, pencairan/withdrawal=90d, update_campaign=45d, verifikasi=90d, sistem=180d, default=90d. Set saat insert.';

COMMENT ON COLUMN notifikasi.is_archived IS
'Soft archive flag. Set manual oleh aplikasi (user action atau background job). Notifikasi archived tidak tampil di UI inbox.';

COMMENT ON COLUMN notifikasi.related_campaign_id IS
'Reference ke campaign terkait. Untuk build URL di aplikasi. Required untuk tipe=campaign.';

COMMENT ON COLUMN notifikasi.related_donasi_id IS
'Reference ke donasi terkait. Required untuk tipe=donasi.';

COMMENT ON COLUMN notifikasi.related_update_id IS
'Reference ke update_campaign terkait. Required untuk tipe=update_campaign.';

COMMENT ON COLUMN notifikasi.related_verifikasi_id IS
'Reference ke verifikasi_rekening terkait. Required untuk tipe=verifikasi.';

COMMENT ON COLUMN notifikasi.related_pencairan_id IS
'Reference ke pencairan_dana terkait. Required untuk tipe=pencairan atau withdrawal.';

COMMENT ON CONSTRAINT chk_penerima_xor ON notifikasi IS
'CRITICAL: Exactly one penerima (user XOR komunitas). Tidak boleh keduanya NULL atau keduanya NOT NULL.';

COMMENT ON CONSTRAINT chk_donasi_requires_donasi_id ON notifikasi IS
'Business rule: Notifikasi tipe donasi wajib punya related_donasi_id untuk traceability.';

COMMENT ON CONSTRAINT chk_follow_requires_pengirim ON notifikasi IS
'Business rule: Notifikasi tipe follow wajib punya id_pengirim_user (siapa yang follow) untuk personalisasi pesan.';

-- ============================================================
-- HELPER FUNCTION
-- ============================================================

CREATE OR REPLACE FUNCTION get_notification_ttl(notif_tipe VARCHAR)
RETURNS INTERVAL AS $$
BEGIN
    RETURN CASE notif_tipe
        WHEN 'follow' THEN INTERVAL '30 days'
        WHEN 'donasi' THEN INTERVAL '60 days'
        WHEN 'pencairan' THEN INTERVAL '90 days'
        WHEN 'withdrawal' THEN INTERVAL '90 days'
        WHEN 'update_campaign' THEN INTERVAL '45 days'
        WHEN 'verifikasi' THEN INTERVAL '90 days'
        WHEN 'sistem' THEN INTERVAL '180 days'
        ELSE INTERVAL '90 days'
    END;
END;
$$ LANGUAGE plpgsql IMMUTABLE;

COMMENT ON FUNCTION get_notification_ttl(VARCHAR) IS
'Helper function untuk calculate TTL duration based on notification type. Returns INTERVAL untuk ditambahkan ke created_at.';

-- ============================================================
-- END OF DDL SCRIPT
-- ============================================================