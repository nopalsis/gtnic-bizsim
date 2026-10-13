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
                'employee_count' => 4,
                'salary_per_employee' => 2800000.00,
                'monthly_fixed_cost' => 4500000.00,
                'monthly_max_capacity' => 5000,
            ]
        );
    }
}
