<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Campaign tsvector (judul + deskripsi)
        DB::statement("
            ALTER TABLE campaign
            ADD COLUMN search_vector TSVECTOR
            GENERATED ALWAYS AS (
                setweight(to_tsvector('indonesian', COALESCE(judul, '')), 'A') ||
                setweight(to_tsvector('indonesian', COALESCE(deskripsi, '')), 'B')
            ) STORED
        ");
        DB::statement('CREATE INDEX IF NOT EXISTS idx_campaign_search ON campaign USING GIN (search_vector)');

        // Komunitas tsvector (nama_lembaga + deskripsi)
        DB::statement("
            ALTER TABLE komunitas
            ADD COLUMN search_vector TSVECTOR
            GENERATED ALWAYS AS (
                setweight(to_tsvector('indonesian', COALESCE(nama_lembaga, '')), 'A') ||
                setweight(to_tsvector('indonesian', COALESCE(deskripsi, '')), 'B')
            ) STORED
        ");
        DB::statement('CREATE INDEX IF NOT EXISTS idx_komunitas_search ON komunitas USING GIN (search_vector)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS idx_campaign_search');
        DB::statement('ALTER TABLE campaign DROP COLUMN IF EXISTS search_vector');

        DB::statement('DROP INDEX IF EXISTS idx_komunitas_search');
        DB::statement('ALTER TABLE komunitas DROP COLUMN IF EXISTS search_vector');
    }
};
