<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Account Administrator
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

        // Account Operator
        User::firstOrCreate(
            ['email' => 'operator@gmail.com'],
            [
                'name' => 'Operator Data Ekonomi',
                'password' => Hash::make('password'),
                'role' => 'operator',
                'status' => 'approved',
                'email_verified_at' => now(),
            ]
        );
    }
}
