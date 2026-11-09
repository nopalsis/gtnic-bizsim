<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class StaffSeeder extends Seeder
{
    /**
     * Jalankan seed data untuk staf workshop OptimaBiz dengan skema payroll.
     */
    public function run(): void
    {
        $hasPayrollColumns = Schema::hasColumn('users', 'salary_type');

        $staffMembers = [
            // 1. Super Admin (1 user)
            [
                'name' => 'Pemilik Workshop (Super Admin)',
                'email' => 'owner@optimabiz.test',
                'role' => 'superadmin',
                'phone' => '+62 811-0000-1111',
                'salary_type' => 'fixed_monthly',
                'base_salary' => 6000000,
                'piece_rate' => 0,
                'is_active' => true,
            ],
            // 2. Admin (1 user)
            [
                'name' => 'Dewi (Admin Operasional)',
                'email' => 'admin@optimabiz.test',
                'role' => 'admin',
                'phone' => '+62 811-3322-1100',
                'salary_type' => 'fixed_monthly',
                'base_salary' => 3200000,
                'piece_rate' => 0,
                'is_active' => true,
            ],
            // 3. Sales (3 users)
            [
                'name' => 'Kang Asep (Sales Retail)',
                'email' => 'asep@optimabiz.test',
                'role' => 'sales',
                'phone' => '+62 811-2345-6789',
                'salary_type' => 'fixed_monthly',
                'base_salary' => 3000000,
                'piece_rate' => 0,
                'is_active' => true,
            ],
            [
                'name' => 'Rian (Sales Grosir & Partai)',
                'email' => 'rian@optimabiz.test',
                'role' => 'sales',
                'phone' => '+62 878-3344-5566',
                'salary_type' => 'hybrid',
                'base_salary' => 2500000,
                'piece_rate' => 5000,
                'is_active' => true,
            ],
            [
                'name' => 'Siti (Sales Reseller & Toko)',
                'email' => 'siti@optimabiz.test',
                'role' => 'sales',
                'phone' => '+62 819-0123-4567',
                'salary_type' => 'fixed_monthly',
                'base_salary' => 2800000,
                'piece_rate' => 0,
                'is_active' => true,
            ],
            // 4. Craftsman / Pengrajin (3 users)
            [
                'name' => 'Pak Bambang (Tukang Pola & Seset)',
                'email' => 'bambang@optimabiz.test',
                'role' => 'craftsman',
                'phone' => '+62 812-9876-5432',
                'salary_type' => 'piece_rate',
                'base_salary' => 0,
                'piece_rate' => 35000,
                'is_active' => true,
            ],
            [
                'name' => 'Budi Santoso (Tukang Jahit Upper)',
                'email' => 'budi@optimabiz.test',
                'role' => 'craftsman',
                'phone' => '+62 856-1122-3344',
                'salary_type' => 'piece_rate',
                'base_salary' => 0,
                'piece_rate' => 30000,
                'is_active' => true,
            ],
            [
                'name' => 'Pak Hadi (Tukang Sol & Lasting)',
                'email' => 'hadi@optimabiz.test',
                'role' => 'craftsman',
                'phone' => '+62 821-4455-6677',
                'salary_type' => 'piece_rate',
                'base_salary' => 0,
                'piece_rate' => 32000,
                'is_active' => true,
            ],
        ];

        $validEmails = array_column($staffMembers, 'email');
        User::whereNotIn('email', $validEmails)->delete();

        foreach ($staffMembers as $staff) {
            $userData = [
                'name' => $staff['name'],
                'role' => $staff['role'],
                'phone' => $staff['phone'],
                'is_active' => $staff['is_active'],
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ];

            if ($hasPayrollColumns) {
                $userData['salary_type'] = $staff['salary_type'];
                $userData['base_salary'] = $staff['base_salary'];
                $userData['piece_rate'] = $staff['piece_rate'];
            }

            User::updateOrCreate(
                ['email' => $staff['email']],
                $userData
            );
        }
    }
}
