<?php

namespace Database\Seeders;

use App\Models\OperationalSetting;
use Illuminate\Database\Seeder;

class OperationalSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        OperationalSetting::updateOrCreate(
            ['id' => 1],
            [
                'workshop_name' => 'OptimaBiz Leathercraft & Shoes Workshop',
                'employee_count' => 5, // 5 Pengrajin terampil (pola, seset, jahit upper, pasang sol/bottoming, finishing)
                'salary_per_employee' => 3200000.00, // Rp 3.200.000 / pengrajin
                'monthly_fixed_cost' => 6500000.00, // Sewa bengkel workshop, listrik kompresor & mesin gerinda, lem & perlengkapan
                'monthly_max_capacity' => 500, // Kapasitas produksi 500 pasang sepatu/sandal kulit per bulan
            ]
        );
    }
}
