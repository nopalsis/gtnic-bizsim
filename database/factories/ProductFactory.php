<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $costPrice = fake()->randomElement([8000, 10000, 15000, 20000, 35000, 50000]);
        $markup = fake()->randomFloat(2, 1.4, 2.2);
        $basePrice = round(($costPrice * $markup) / 1000) * 1000;

        return [
            'name' => fake()->words(2, true).' '.fake()->randomElement(['Blend', 'Latte', 'Brew', 'Cake', 'Beans']),
            'category' => fake()->randomElement(['Beverage', 'Food', 'Coffee Beans', 'Merchandise']),
            'base_price' => $basePrice,
            'cost_price' => $costPrice,
            'current_stock' => fake()->numberBetween(10, 100),
            'min_stock_alert' => fake()->numberBetween(5, 15),
            'is_active' => true,
        ];
    }
}
