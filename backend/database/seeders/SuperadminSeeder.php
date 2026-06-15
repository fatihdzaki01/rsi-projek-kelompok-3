<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SuperadminSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insertOrIgnore([
            'username'       => 'Superadmin',
            'email'          => 'superadmin@berbagive.com',
            'password_hash'  => Hash::make('Admin123!'),
            'role'           => 'SUPERADMIN',
            'is_active'      => true,
            'is_verified'    => true,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
    }
}
