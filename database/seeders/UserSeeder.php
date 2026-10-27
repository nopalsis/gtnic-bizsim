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
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $staffMembers = [
            ['name' => 'Kang Asep (Sales Pengrajin)', 'email' => 'asep@optimabiz.test'],
            ['name' => 'Pak Bambang (Workshop Head)', 'email' => 'bambang@optimabiz.test'],
            ['name' => 'Rian (Wholesale & Store)', 'email' => 'rian@optimabiz.test'],
            ['name' => 'Siti (Admin Toko & Reseller)', 'email' => 'siti@optimabiz.test'],
        ];

        foreach ($staffMembers as $staff) {
            User::updateOrCreate(
                ['email' => $staff['email']],
                [
                    'name' => $staff['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
