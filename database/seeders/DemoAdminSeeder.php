<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['username' => 'admin.demo'],
            [
                'name' => 'Demo Administrator',
                'email' => 'admin.demo@example.com',
                'password' => Hash::make('DemoAdmin2026!'),
                'role' => 'admin',
                'status' => 'active',
                'must_change_password' => true,
            ],
        );
    }
}
