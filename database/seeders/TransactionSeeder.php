<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Product::count() === 0) {
            $this->call(ProductSeeder::class);
        }

        $salesPersons = ['Budi Santoso', 'Siti Rahmawati', 'Fajar Nugraha', 'Dewi Lestari'];

        for ($i = 1; $i <= 20; $i++) {
            $invoiceNumber = sprintf('INV-202610-%04d', $i);
            $daysAgo = (int) floor((20 - $i) * 1.5);
            $transactionDate = now()->subDays($daysAgo)->setHour(rand(8, 20))->setMinute(rand(0, 59))->setSecond(rand(0, 59));

            Transaction::firstOrCreate(
                ['invoice_number' => $invoiceNumber],
                [
                    'transaction_date' => $transactionDate,
                    'sales_person_name' => $salesPersons[($i - 1) % count($salesPersons)],
                    'total_amount' => 0.00,
                    'total_discount' => 0.00,
                    'net_amount' => 0.00,
                ]
            );
        }

        $this->call(TransactionItemSeeder::class);
    }
}
