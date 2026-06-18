#!/bin/bash
# Berbagive Finalization — run AFTER DDL + data inserted
set -e

echo "=== Berbagive Finalization ==="

set -a; source .env; set +a

echo "Creating Laravel framework tables..."
docker compose exec -T db psql -U "${DB_USERNAME:-berbagive}" -d "${DB_DATABASE:-berbagive}" <<'SQL'
BEGIN;

-- Fix personal_access_tokens: DDL misses tokenable_type (Sanctum polymorphic)
ALTER TABLE personal_access_tokens ADD COLUMN IF NOT EXISTS tokenable_type VARCHAR(255);
UPDATE personal_access_tokens SET tokenable_type = 'App\Models\User' WHERE tokenable_type IS NULL;
ALTER TABLE personal_access_tokens ALTER COLUMN tokenable_type SET NOT NULL;

-- Cache (Laravel cache driver)
CREATE TABLE IF NOT EXISTS cache (
    key        VARCHAR(255) PRIMARY KEY,
    value      TEXT NOT NULL,
    expiration INTEGER NOT NULL
);
CREATE TABLE IF NOT EXISTS cache_locks (
    key        VARCHAR(255) PRIMARY KEY,
    owner      VARCHAR(255) NOT NULL,
    expiration INTEGER NOT NULL
);

-- Jobs (queue driver: database)
CREATE TABLE IF NOT EXISTS jobs (
    id           BIGSERIAL PRIMARY KEY,
    queue        VARCHAR(255) NOT NULL,
    payload      TEXT NOT NULL,
    attempts     SMALLINT NOT NULL DEFAULT 0,
    reserved_at  INTEGER,
    available_at INTEGER NOT NULL,
    created_at   INTEGER NOT NULL
);
CREATE INDEX IF NOT EXISTS idx_jobs_queue ON jobs (queue);
CREATE TABLE IF NOT EXISTS job_batches (
    id              VARCHAR(255) PRIMARY KEY,
    name            VARCHAR(255) NOT NULL,
    total_jobs      INTEGER NOT NULL DEFAULT 0,
    pending_jobs    INTEGER NOT NULL DEFAULT 0,
    failed_jobs     INTEGER NOT NULL DEFAULT 0,
    failed_job_ids  TEXT,
    options         TEXT,
    cancelled_at    INTEGER,
    created_at      INTEGER NOT NULL,
    finished_at     INTEGER
);
CREATE TABLE IF NOT EXISTS failed_jobs (
    id         BIGSERIAL PRIMARY KEY,
    uuid       VARCHAR(255) UNIQUE NOT NULL,
    connection TEXT NOT NULL,
    queue      TEXT NOT NULL,
    payload    TEXT NOT NULL,
    exception  TEXT NOT NULL,
    failed_at  TIMESTAMP DEFAULT NOW()
);

-- Sanctum tokens
CREATE TABLE IF NOT EXISTS personal_access_tokens (
    id             BIGSERIAL PRIMARY KEY,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id   BIGINT NOT NULL,
    name           VARCHAR(255),
    token          VARCHAR(64) UNIQUE NOT NULL,
    abilities      TEXT,
    last_used_at   TIMESTAMP,
    expires_at     TIMESTAMP,
    created_at     TIMESTAMP DEFAULT NOW(),
    updated_at     TIMESTAMP DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_tokens_tokenable ON personal_access_tokens (tokenable_type, tokenable_id);
CREATE INDEX IF NOT EXISTS idx_tokens_expires ON personal_access_tokens (expires_at);

-- Email verifications
CREATE TABLE IF NOT EXISTS email_verifications (
    id_verifikasi BIGSERIAL PRIMARY KEY,
    id_user       BIGINT NOT NULL REFERENCES users(id_user) ON DELETE CASCADE,
    email         VARCHAR(255) NOT NULL,
    token         VARCHAR(64) NOT NULL,
    expires_at    TIMESTAMP NOT NULL,
    created_at    TIMESTAMP DEFAULT NOW(),
    updated_at    TIMESTAMP DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_email_verif_token ON email_verifications (token);
CREATE INDEX IF NOT EXISTS idx_email_verif_email ON email_verifications (email);

-- Laporan campaign
CREATE TABLE IF NOT EXISTS laporan_campaign (
    id_laporan        BIGSERIAL PRIMARY KEY,
    id_campaign       BIGINT REFERENCES campaign(id_campaign) ON DELETE CASCADE,
    id_update         BIGINT,
    id_user           BIGINT REFERENCES users(id_user) ON DELETE SET NULL,
    guest_ip          VARCHAR(45),
    alasan_laporan    VARCHAR(255) NOT NULL,
    deskripsi_laporan TEXT,
    status            VARCHAR(50) DEFAULT 'menunggu_review',
    created_at        TIMESTAMP DEFAULT NOW(),
    updated_at        TIMESTAMP DEFAULT NOW()
);

-- Sessions (session driver: database)
CREATE TABLE IF NOT EXISTS sessions (
    id            VARCHAR(255) PRIMARY KEY,
    user_id       BIGINT,
    ip_address    VARCHAR(45),
    user_agent    TEXT,
    payload       TEXT NOT NULL,
    last_activity INTEGER NOT NULL
);
CREATE INDEX IF NOT EXISTS idx_sessions_user_id ON sessions (user_id);
CREATE INDEX IF NOT EXISTS idx_sessions_last_activity ON sessions (last_activity);

COMMIT;
SQL

echo "Adding pesan column to donasi..."
docker compose exec -T db psql -U "${DB_USERNAME:-berbagive}" -d "${DB_DATABASE:-berbagive}" -c "
    ALTER TABLE donasi ADD COLUMN IF NOT EXISTS pesan TEXT;
"

echo "Adding fulltext search columns and indexes..."
docker compose exec -T db psql -U "${DB_USERNAME:-berbagive}" -d "${DB_DATABASE:-berbagive}" <<'SQL'
BEGIN;

ALTER TABLE campaign
    ADD COLUMN IF NOT EXISTS search_vector TSVECTOR
    GENERATED ALWAYS AS (
        setweight(to_tsvector('indonesian', COALESCE(judul, '')), 'A') ||
        setweight(to_tsvector('indonesian', COALESCE(deskripsi, '')), 'B')
    ) STORED;
CREATE INDEX IF NOT EXISTS idx_campaign_search ON campaign USING GIN (search_vector);

ALTER TABLE komunitas
    ADD COLUMN IF NOT EXISTS search_vector TSVECTOR
    GENERATED ALWAYS AS (
        setweight(to_tsvector('indonesian', COALESCE(nama_lembaga, '')), 'A') ||
        setweight(to_tsvector('indonesian', COALESCE(deskripsi, '')), 'B')
    ) STORED;
CREATE INDEX IF NOT EXISTS idx_komunitas_search ON komunitas USING GIN (search_vector);

COMMIT;
SQL

echo "Fixing serial sequences after CSV import..."
docker compose exec -T db psql -U "${DB_USERNAME:-berbagive}" -d "${DB_DATABASE:-berbagive}" <<'SQL'
SELECT setval('users_id_user_seq', COALESCE((SELECT MAX(id_user) FROM users), 1), true);
SELECT setval('campaign_id_campaign_seq', COALESCE((SELECT MAX(id_campaign) FROM campaign), 1), true);
SELECT setval('donasi_id_donasi_seq', COALESCE((SELECT MAX(id_donasi) FROM donasi), 1), true);
SELECT setval('komunitas_id_komunitas_seq', COALESCE((SELECT MAX(id_komunitas) FROM komunitas), 1), true);
SELECT setval('notifikasi_id_notif_seq', COALESCE((SELECT MAX(id_notif) FROM notifikasi), 1), true);
SELECT setval('dokumen_komunitas_id_dokumen_seq', COALESCE((SELECT MAX(id_dokumen) FROM dokumen_komunitas), 1), true);
SELECT setval('pencairan_dana_id_pencairan_seq', COALESCE((SELECT MAX(id_pencairan) FROM pencairan_dana), 1), true);
SELECT setval('potongan_platform_id_potongan_seq', COALESCE((SELECT MAX(id_potongan) FROM potongan_platform), 1), true);
SELECT setval('laporan_penggunaan_dana_id_laporan_seq', COALESCE((SELECT MAX(id_laporan) FROM laporan_penggunaan_dana), 1), true);
SELECT setval('update_campaign_id_update_seq', COALESCE((SELECT MAX(id_update) FROM update_campaign), 1), true);
SELECT setval('foto_update_id_foto_seq', COALESCE((SELECT MAX(id_foto) FROM foto_update), 1), true);
SELECT setval('verifikasi_rekening_id_verif_seq', COALESCE((SELECT MAX(id_verif) FROM verifikasi_rekening), 1), true);
SELECT setval('follow_komunitas_id_follow_seq', COALESCE((SELECT MAX(id_follow) FROM follow_komunitas), 1), true);
SELECT setval('migrations_id_seq', COALESCE((SELECT MAX(id) FROM migrations), 1), true);
SQL

echo "Fixing log permissions..."
docker compose exec -T app chmod -R 775 storage/logs 2>/dev/null || true

echo "Updating sp_create_donation PROCEDURE with pesan support..."
docker compose exec -T db psql -U "${DB_USERNAME:-berbagive}" -d "${DB_DATABASE:-berbagive}" <<'SQL'
DROP FUNCTION IF EXISTS sp_create_donation(integer, integer, bigint, character varying, boolean, character varying, text);
DROP PROCEDURE IF EXISTS sp_create_donation(integer, integer, bigint, character varying, boolean, character varying);

CREATE OR REPLACE PROCEDURE sp_create_donation(
    IN p_id_user integer,
    IN p_id_campaign integer,
    IN p_nominal bigint,
    IN p_metode_pembayaran character varying,
    IN p_is_anonim boolean,
    IN p_nama_tampil character varying,
    IN p_pesan text DEFAULT NULL
)
LANGUAGE plpgsql
AS $$
DECLARE
    v_campaign_status campaign_status;
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM users
        WHERE id_user = p_id_user AND is_active = TRUE
    ) THEN
        RAISE EXCEPTION 'User tidak aktif atau tidak ditemukan';
    END IF;

    SELECT status INTO v_campaign_status
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
        id_user, id_campaign, nominal, metode_pembayaran,
        nama_tampil, is_anonim, pesan, status_pembayaran
    )
    VALUES (
        p_id_user, p_id_campaign, p_nominal, LOWER(TRIM(p_metode_pembayaran)),
        CASE WHEN p_is_anonim THEN NULL ELSE TRIM(p_nama_tampil) END,
        p_is_anonim, TRIM(p_pesan), 'pending'
    );
END;
$$;
SQL

echo "Updating sp_create_donation FUNCTION with pesan support..."
docker compose exec -T db psql -U "${DB_USERNAME:-berbagive}" -d "${DB_DATABASE:-berbagive}" <<'SQL'
INSERT INTO migrations (migration, batch) VALUES
    ('0001_01_01_000000_create_users_table', 1),
    ('0001_01_01_000001_create_cache_table', 1),
    ('0001_01_01_000002_create_jobs_table', 1),
    ('2026_06_05_103948_create_personal_access_tokens_table', 1),
    ('2026_06_14_200747_create_email_verifications_table', 1),
    ('2026_06_15_000001_add_pesan_to_donasi_and_update_sp', 1),
    ('2026_06_15_000002_create_laporan_campaign_table', 1),
    ('2026_06_17_065736_add_fulltext_search_to_campaigns_and_komunitas', 1)
ON CONFLICT (migration) DO NOTHING;
SQL

echo "Running artisan optimize..."
docker compose exec -T app php artisan optimize

echo ""
echo "====================================================================="
echo "  Finalization complete!"
echo ""
echo "  Test: curl http://localhost:${HTTP_PORT:-80}/api/v1/health"
echo "====================================================================="
