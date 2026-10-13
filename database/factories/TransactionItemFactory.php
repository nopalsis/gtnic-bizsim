<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TransactionItem>
 */
class TransactionItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 3);
        $costPrice = 12000.00;
        $basePrice = 25000.00;
        $dealPrice = 25000.00;

        return [
            'transaction_id' => Transaction::factory(),
            'product_id' => Product::factory(),
            'quantity' => $quantity,
            'base_price' => $basePrice,
            'deal_price' => $dealPrice,
            'cost_price' => $costPrice,
            'subtotal' => $quantity * $dealPrice,
        ];
    }
}
