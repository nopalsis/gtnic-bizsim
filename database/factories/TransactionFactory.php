<?php

namespace Database\Factories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = fake()->dateTimeBetween('-1 month', 'now');
        $amount = fake()->randomElement([25000, 50000, 75000, 120000, 180000, 250000]);
        $discount = fake()->randomElement([0, 0, 0, 5000, 10000]);
        $net = max(0, $amount - $discount);

        return [
            'invoice_number' => 'INV-'.fake()->unique()->numerify('2026####'),
            'transaction_date' => $date,
            'sales_person_name' => fake()->randomElement(['Budi Santoso', 'Siti Rahmawati', 'Fajar Nugraha', 'Dewi Lestari']),
            'total_amount' => $amount,
            'total_discount' => $discount,
            'net_amount' => $net,
        ];
    }
}
