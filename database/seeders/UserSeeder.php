<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hasPayrollColumns = Schema::hasColumn('users', 'salary_type');

        // Daftar tepat 8 pengguna: 1 Superadmin, 1 Admin, 3 Sales, 3 Pengrajin
        $users = [
            // 1. Super Admin (1 orang)
            [
                'name' => 'Pemilik Workshop (Super Admin)',
                'email' => 'owner@optimabiz.test',
                'role' => 'superadmin',
                'phone' => '+62 811-0000-1111',
                'salary_type' => 'fixed_monthly',
                'base_salary' => 6000000,
                'piece_rate' => 0,
            ],
            // 2. Admin Operasional (1 orang)
            [
                'name' => 'Dewi (Admin Operasional)',
                'email' => 'admin@optimabiz.test',
                'role' => 'admin',
                'phone' => '+62 811-3322-1100',
                'salary_type' => 'fixed_monthly',
                'base_salary' => 3200000,
                'piece_rate' => 0,
            ],
            // 3. Sales (3 orang)
            [
                'name' => 'Kang Asep (Sales Retail)',
                'email' => 'asep@optimabiz.test',
                'role' => 'sales',
                'phone' => '+62 811-2345-6789',
                'salary_type' => 'fixed_monthly',
                'base_salary' => 3000000,
                'piece_rate' => 0,
            ],
            [
                'name' => 'Rian (Sales Grosir & Partai)',
                'email' => 'rian@optimabiz.test',
                'role' => 'sales',
                'phone' => '+62 878-3344-5566',
                'salary_type' => 'hybrid',
                'base_salary' => 2500000,
                'piece_rate' => 5000,
            ],
            [
                'name' => 'Siti (Sales Reseller & Toko)',
                'email' => 'siti@optimabiz.test',
                'role' => 'sales',
                'phone' => '+62 819-0123-4567',
                'salary_type' => 'fixed_monthly',
                'base_salary' => 2800000,
                'piece_rate' => 0,
            ],
            // 4. Pengrajin / Craftsman (3 orang)
            [
                'name' => 'Pak Bambang (Tukang Pola & Seset)',
                'email' => 'bambang@optimabiz.test',
                'role' => 'craftsman',
                'phone' => '+62 812-9876-5432',
                'salary_type' => 'piece_rate',
                'base_salary' => 0,
                'piece_rate' => 35000,
            ],
            [
                'name' => 'Budi Santoso (Tukang Jahit Upper)',
                'email' => 'budi@optimabiz.test',
                'role' => 'craftsman',
                'phone' => '+62 856-1122-3344',
                'salary_type' => 'piece_rate',
                'base_salary' => 0,
                'piece_rate' => 30000,
            ],
            [
                'name' => 'Pak Hadi (Tukang Sol & Lasting)',
                'email' => 'hadi@optimabiz.test',
                'role' => 'craftsman',
                'phone' => '+62 821-4455-6677',
                'salary_type' => 'piece_rate',
                'base_salary' => 0,
                'piece_rate' => 32000,
            ],
        ];

        $validEmails = array_column($users, 'email');
        User::whereNotIn('email', $validEmails)->delete();

        foreach ($users as $userData) {
            $data = [
                'name' => $userData['name'],
                'role' => $userData['role'],
                'phone' => $userData['phone'],
                'password' => Hash::make('password123'),
                'is_active' => true,
                'email_verified_at' => now(),
            ];

            if ($hasPayrollColumns) {
                $data['salary_type'] = $userData['salary_type'];
                $data['base_salary'] = $userData['base_salary'];
                $data['piece_rate'] = $userData['piece_rate'];
            }

            User::updateOrCreate(
                ['email' => $userData['email']],
                $data
            );
        }
    }
}
