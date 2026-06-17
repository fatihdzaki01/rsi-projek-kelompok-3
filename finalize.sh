#!/bin/bash
# Berbagive Finalization — run AFTER DDL + data inserted
set -e

echo "=== Berbagive Finalization ==="

set -a; source .env; set +a

echo "Creating Laravel framework tables..."
docker compose exec -T db psql -U "${DB_USERNAME:-berbagive}" -d "${DB_DATABASE:-berbagive}" <<'SQL'
BEGIN;

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

echo "Updating sp_create_donation FUNCTION with pesan support..."
docker compose exec -T db psql -U "${DB_USERNAME:-berbagive}" -d "${DB_DATABASE:-berbagive}" <<'SQL'
CREATE OR REPLACE FUNCTION sp_create_donation(
    p_id_user integer,
    p_id_campaign integer,
    p_nominal bigint,
    p_metode_pembayaran character varying,
    p_is_anonim boolean,
    p_nama_tampil character varying,
    p_pesan text DEFAULT NULL
)
RETURNS void
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
        p_is_anonim,
        TRIM(p_pesan),
        'pending'
    );
END;
$$;
SQL

echo "Marking all migrations as done..."
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
