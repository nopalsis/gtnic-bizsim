<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockMovement>
 */
class StockMovementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'type' => fake()->randomElement(['in', 'out', 'waste', 'adjustment']),
            'quantity' => fake()->numberBetween(5, 50),
            'movement_date' => fake()->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'notes' => fake()->randomElement([
                'Penerimaan stok dari supplier',
                'Penyesuaian stok opname',
                'Barang rusak atau kadaluarsa',
                'Penjualan di luar sistem',
            ]),
        ];
    }
}
