<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donasi', function (Blueprint $table) {
            $table->text('pesan')->nullable()->after('nama_tampil');
        });

        DB::statement("
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
            AS \$\$
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
            \$\$;
        ");
    }

    public function down(): void
    {
        Schema::table('donasi', function (Blueprint $table) {
            $table->dropColumn('pesan');
        });

        DB::statement("
            CREATE OR REPLACE FUNCTION sp_create_donation(
                p_id_user integer,
                p_id_campaign integer,
                p_nominal bigint,
                p_metode_pembayaran character varying,
                p_is_anonim boolean,
                p_nama_tampil character varying
            )
            RETURNS void
            LANGUAGE plpgsql
            AS \$\$
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
                    nama_tampil, is_anonim, status_pembayaran
                )
                VALUES (
                    p_id_user, p_id_campaign, p_nominal, LOWER(TRIM(p_metode_pembayaran)),
                    CASE WHEN p_is_anonim THEN NULL ELSE TRIM(p_nama_tampil) END,
                    p_is_anonim,
                    'pending'
                );
            END;
            \$\$;
        ");
    }
};
