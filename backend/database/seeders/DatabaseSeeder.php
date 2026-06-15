<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            WilayahSeeder::class,
            JenisLembagaSeeder::class,
            JenisDokumenSeeder::class,
            KategoriCampaignSeeder::class,
            SuperadminSeeder::class,
            DummyDataSeeder::class,
        ]);
    }
}
