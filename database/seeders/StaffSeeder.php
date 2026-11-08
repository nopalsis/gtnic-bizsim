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
            [
                'name' => 'Pemilik Workshop OptimaBiz (Super Admin)',
                'email' => 'owner@optimabiz.test',
                'role' => 'superadmin',
                'phone' => '+62 811-0000-1111',
                'salary_type' => 'fixed_monthly',
                'base_salary' => 5000000,
                'piece_rate' => 0,
                'is_active' => true,
            ],
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
            [
                'name' => 'Kang Asep (Sales Pengrajin)',
                'email' => 'asep@optimabiz.test',
                'role' => 'sales',
                'phone' => '+62 811-2345-6789',
                'salary_type' => 'fixed_monthly',
                'base_salary' => 3000000,
                'piece_rate' => 0,
                'is_active' => true,
            ],
            [
                'name' => 'Pak Bambang (Workshop Head)',
                'email' => 'bambang@optimabiz.test',
                'role' => 'craftsman',
                'phone' => '+62 812-9876-5432',
                'salary_type' => 'fixed_monthly',
                'base_salary' => 3600000,
                'piece_rate' => 0,
                'is_active' => true,
            ],
            [
                'name' => 'Rian (Wholesale & Store)',
                'email' => 'rian@optimabiz.test',
                'role' => 'sales',
                'phone' => '+62 878-3344-5566',
                'salary_type' => 'hybrid',
                'base_salary' => 2000000,
                'piece_rate' => 75000,
                'is_active' => true,
            ],
            [
                'name' => 'Siti (Admin Toko & Reseller)',
                'email' => 'siti@optimabiz.test',
                'role' => 'sales',
                'phone' => '+62 819-0123-4567',
                'salary_type' => 'fixed_monthly',
                'base_salary' => 2800000,
                'piece_rate' => 0,
                'is_active' => true,
            ],
            [
                'name' => 'Budi Santoso (Tukang Pola & Skiving)',
                'email' => 'budi.s@optimabiz.id',
                'role' => 'craftsman',
                'phone' => '+62 856-1122-3344',
                'salary_type' => 'piece_rate',
                'base_salary' => 0,
                'piece_rate' => 25000,
                'is_active' => true,
            ],
            [
                'name' => 'Alex Rivera (Tukang Jahit Upper)',
                'email' => 'alex.tech@optimabiz.id',
                'role' => 'craftsman',
                'phone' => '+62 821-4455-6677',
                'salary_type' => 'piece_rate',
                'base_salary' => 0,
                'piece_rate' => 30000,
                'is_active' => true,
            ],
            [
                'name' => 'Joko Susilo (Lasting & Sol Manual)',
                'email' => 'joko.s@optimabiz.id',
                'role' => 'craftsman',
                'phone' => '+62 813-5566-7788',
                'salary_type' => 'hybrid',
                'base_salary' => 1500000,
                'piece_rate' => 20000,
                'is_active' => true,
            ],
            [
                'name' => 'Hendra Saputra (Finishing & QC)',
                'email' => 'hendra.s@optimabiz.id',
                'role' => 'craftsman',
                'phone' => '+62 812-4433-2211',
                'salary_type' => 'fixed_monthly',
                'base_salary' => 3200000,
                'piece_rate' => 0,
                'is_active' => true,
            ],
        ];

        foreach ($staffMembers as $staff) {
            $data = [
                'name' => $staff['name'],
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ];

            if ($hasPayrollColumns) {
                $data['role'] = $staff['role'];
                $data['phone'] = $staff['phone'];
                $data['salary_type'] = $staff['salary_type'];
                $data['base_salary'] = $staff['base_salary'];
                $data['piece_rate'] = $staff['piece_rate'];
                $data['is_active'] = $staff['is_active'];
            }

            User::updateOrCreate(
                ['email' => $staff['email']],
                $data
            );
        }
    }
}
