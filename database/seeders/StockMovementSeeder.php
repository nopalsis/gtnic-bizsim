<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Database\Seeder;

class StockMovementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Product::count() === 0) {
            $this->call(ProductSeeder::class);
        }

        $products = Product::all();

        foreach ($products as $product) {
            StockMovement::firstOrCreate(
                [
                    'product_id' => $product->id,
                    'type' => 'in',
                    'notes' => 'Stok awal setup sistem',
                ],
                [
                    'quantity' => $product->current_stock,
                    'movement_date' => now()->subDays(30)->toDateString(),
                ]
            );

            StockMovement::firstOrCreate(
                [
                    'product_id' => $product->id,
                    'type' => 'in',
                    'notes' => 'Pengadaan stok dari supplier utama',
                ],
                [
                    'quantity' => 25,
                    'movement_date' => now()->subDays(14)->toDateString(),
                ]
            );

            if ($product->category === 'Food') {
                StockMovement::firstOrCreate(
                    [
                        'product_id' => $product->id,
                        'type' => 'waste',
                        'notes' => 'Produk melewati batas kedaluwarsa',
                    ],
                    [
                        'quantity' => 2,
                        'movement_date' => now()->subDays(5)->toDateString(),
                    ]
                );
            }

            if ($product->id % 3 === 0) {
                StockMovement::firstOrCreate(
                    [
                        'product_id' => $product->id,
                        'type' => 'adjustment',
                        'notes' => 'Koreksi hasil stock opname berkala',
                    ],
                    [
                        'quantity' => 1,
                        'movement_date' => now()->subDays(2)->toDateString(),
                    ]
                );
            }
        }
    }
}
