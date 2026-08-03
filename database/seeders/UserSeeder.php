<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserWilayahScope;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Account Administrator (Bypass Scope - Akses Nasional & Seluruh Wilayah)
        User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Administrator DPMPTSP',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => 'approved',
                'email_verified_at' => now(),
            ]
        );

        // 2. Account Operator 1: Operator Provinsi SUMUT (Scope: Prov. Sumatera Utara + All Kab/Kota under Sumut)
        $opSumut = User::firstOrCreate(
            ['email' => 'operator@gmail.com'],
            [
                'name' => 'Operator SUMUT',
                'password' => Hash::make('password'),
                'role' => 'operator',
                'status' => 'approved',
                'email_verified_at' => now(),
            ]
        );

        UserWilayahScope::firstOrCreate([
            'user_id' => $opSumut->id,
            'provinsi_id' => 12, // Sumatera Utara
            'kabupaten_id' => null,
        ]);

        // 3. Account Operator 2: Operator Kota Medan (Scope: Khusus Kota Medan)
        $opMedan = User::firstOrCreate(
            ['email' => 'operatormedan@gmail.com'],
            [
                'name' => 'Operator Kota Medan',
                'password' => Hash::make('password'),
                'role' => 'operator',
                'status' => 'approved',
                'email_verified_at' => now(),
            ]
        );

        UserWilayahScope::firstOrCreate([
            'user_id' => $opMedan->id,
            'provinsi_id' => 12, // Sumatera Utara
            'kabupaten_id' => 1271, // Kota Medan
        ]);
    }
}
