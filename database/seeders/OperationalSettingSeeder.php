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
                'workshop_name' => 'OptimaBiz Leathercraft Workshop',
                'employee_count' => 3, // 3 Pengrajin terampil (Pak Bambang, Budi Santoso, Pak Hadi)
                'salary_per_employee' => 3200000.00, // Standar upah pengrajin
                'monthly_fixed_cost' => 6500000.00, // Sewa workshop, listrik mesin gerinda, kompresor, perlengkapan
                'monthly_max_capacity' => 350, // Kapasitas produksi fisik bulanan 350 pasang
            ]
        );
    }
}
