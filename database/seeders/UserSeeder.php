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
            ['email' => 'admin@gtnic.test'],
            [
                'name' => 'Admin GTNIC',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $staffMembers = [
            ['name' => 'Budi Santoso', 'email' => 'budi@gtnic.test'],
            ['name' => 'Siti Rahmawati', 'email' => 'siti@gtnic.test'],
            ['name' => 'Fajar Nugraha', 'email' => 'fajar@gtnic.test'],
            ['name' => 'Dewi Lestari', 'email' => 'dewi@gtnic.test'],
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
