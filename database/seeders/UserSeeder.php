<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'owner@optimabiz.test'],
            [
                'name' => 'Pemilik Workshop OptimaBiz',
                'role' => 'admin',
                'password' => Hash::make('password123'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $staffMembers = [
            ['name' => 'Kang Asep (Sales Pengrajin)', 'email' => 'asep@optimabiz.test', 'role' => 'sales'],
            ['name' => 'Pak Bambang (Workshop Head)', 'email' => 'bambang@optimabiz.test', 'role' => 'craftsman'],
            ['name' => 'Rian (Wholesale & Store)', 'email' => 'rian@optimabiz.test', 'role' => 'sales'],
            ['name' => 'Siti (Admin Toko & Reseller)', 'email' => 'siti@optimabiz.test', 'role' => 'sales'],
        ];

        foreach ($staffMembers as $staff) {
            User::updateOrCreate(
                ['email' => $staff['email']],
                [
                    'name' => $staff['name'],
                    'role' => $staff['role'],
                    'password' => Hash::make('password123'),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
