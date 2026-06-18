<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE MATERIALIZED VIEW v_platform_summary_mv AS
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
            WITH DATA
        ");

        DB::statement("CREATE UNIQUE INDEX idx_v_platform_summary_mv_row ON v_platform_summary_mv ((true))");
    }

    public function down(): void
    {
        DB::statement("DROP MATERIALIZED VIEW IF EXISTS v_platform_summary_mv CASCADE");
    }
};
