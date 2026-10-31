<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class StaffSeeder extends Seeder
{
    /**
     * Jalankan seed data untuk staf workshop OptimaBiz (Staff Directory).
     */
    public function run(): void
    {
        $hasRole = Schema::hasColumn('users', 'role');
        $hasPhone = Schema::hasColumn('users', 'phone');
        $hasStatus = Schema::hasColumn('users', 'status');
        $hasAvatar = Schema::hasColumn('users', 'avatar_url');

        $staffMembers = [
            [
                'name' => 'Joseph Mitchell',
                'email' => 'joseph.m@optimabiz.id',
                'role' => 'Administrator',
                'phone' => '+62 811-2345-6789',
                'status' => 'active',
                'avatar_url' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuBUao7l0jXgTwugGmBV_qJ0Ci4sX0sLFnG8W5DLx0BjAymRfdGLpYqmZefNwz0ZudB-v0nUy81q0TobHQWOPqRxSRRWTg7FUJaO4Qo9zYNw1KPdRkDltkZAO30Y4YPzaiK1dsZ5QpFflJt2WsRcP1pPznJI4Sl7bDHZrgJryv8uXpWTpvLQ9MFqWNhdwXpNWx1giJ0V1_gtsYRBIH0A8sF2DRVZ6tqJy2MIa0-_Mjs',
            ],
            [
                'name' => 'Sarah Jenkins',
                'email' => 'sarah.j@optimabiz.id',
                'role' => 'Manager',
                'phone' => '+62 812-9876-5432',
                'status' => 'active',
                'avatar_url' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuC9mZLEira2cpylt0FGs1GY94K1wqu_7o29TqS52-MLrwdg9_fR2kFjMuDaissBaAzR3ss3EW2ZPTHY9LVwgtu_iduArI7HY1dHsl2s7QccwTH1cu9lvKGd5jq1iZ-_azetECn417U6k_lS40ezL8BpaoT0BZZoeiYkE9UOYIXl1hCKBN-vgoI8dyhSKldIp1M4R7uyip-vO3p5RrUBIUWBARnv14c1dH1DnKmvToY',
            ],
            [
                'name' => 'Budi Santoso',
                'email' => 'budi.s@optimabiz.id',
                'role' => 'Operational Staff',
                'phone' => '+62 856-1122-3344',
                'status' => 'on_leave',
                'avatar_url' => null,
            ],
            [
                'name' => 'Maya Putri',
                'email' => 'maya.p@optimabiz.id',
                'role' => 'Manager',
                'phone' => '+62 813-7788-9900',
                'status' => 'active',
                'avatar_url' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuCs_6IyoQY47t8rltMImtusSeIQZt83wOiVHyFADczvr9X_15bc4gf7QLUQR-m2JtNldal3QLIER2hQ2pSx0bbZgUc_EgqMmGlzjogc49SI4fujcCVPfjACQw6jC33-YGz34LlwHQOTfGJdTNuZohBvPKNybrY0r_BUwT8H_wiwJY-IbXjtwAQQdvwKEybbQDAqXWGQglz_v5RwdwixxYuW5hQvy6IhrSSnOFg037g',
            ],
            [
                'name' => 'Alex Rivera',
                'email' => 'alex.tech@optimabiz.id',
                'role' => 'Contractor',
                'phone' => '+62 821-4455-6677',
                'status' => 'active',
                'avatar_url' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDuqbYXwyCYxmVJwDLhjGglnof47pLeDnkto2vWcmbnNyI01F_WiXWrx6iYm7E0eferfB6PcZAH4-Pve9RncYAY6Vxup1uqBg8E3amwq4tzG0yVfgZcUn2RYdyl-U-4-_2An0gfke-j5R-yJz-OOWf5e933Ehl6YfiB6IR8sqFXUh5RL3WELX9HZ8L7FKod3pM1UTm-KTVCQU0OVnohA9FH3hWbF819xQ0QCT5oqgc',
            ],
            [
                'name' => 'Linda Wu',
                'email' => 'linda.wu@optimabiz.id',
                'role' => 'Manager',
                'phone' => '+62 815-5566-7788',
                'status' => 'active',
                'avatar_url' => null,
            ],
            [
                'name' => 'Rian Kurniawan',
                'email' => 'rian.ops@gmail.com',
                'role' => 'Operational Staff',
                'phone' => '+62 878-3344-5566',
                'status' => 'active',
                'avatar_url' => null,
            ],
            [
                'name' => 'Devi Anggraini',
                'email' => 'devi.fin@optimabiz.id',
                'role' => 'Operational Staff',
                'phone' => '+62 819-0123-4567',
                'status' => 'active',
                'avatar_url' => null,
            ],
        ];

        foreach ($staffMembers as $staff) {
            $data = [
                'name' => $staff['name'],
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ];

            if ($hasRole) {
                $data['role'] = $staff['role'];
            }
            if ($hasPhone) {
                $data['phone'] = $staff['phone'];
            }
            if ($hasStatus) {
                $data['status'] = $staff['status'];
            }
            if ($hasAvatar) {
                $data['avatar_url'] = $staff['avatar_url'];
            }

            User::updateOrCreate(
                ['email' => $staff['email']],
                $data
            );
        }

        // Perbarui akun owner workshop jika sudah ada
        $owner = User::where('email', 'owner@optimabiz.test')->first();
        if ($owner && $hasRole) {
            $ownerUpdate = ['role' => 'Administrator'];
            if ($hasPhone && empty($owner->phone)) {
                $ownerUpdate['phone'] = '+62 811-0000-1111';
            }
            if ($hasStatus && empty($owner->status)) {
                $ownerUpdate['status'] = 'active';
            }
            $owner->update($ownerUpdate);
        }
    }
}

