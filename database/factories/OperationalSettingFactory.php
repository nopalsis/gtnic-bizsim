<?php

namespace Database\Factories;

use App\Models\OperationalSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OperationalSetting>
 */
class OperationalSettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_count' => fake()->numberBetween(2, 6),
            'salary_per_employee' => 2750000.00,
            'monthly_fixed_cost' => 3500000.00,
            'monthly_max_capacity' => 4500,
        ];
    }
}
