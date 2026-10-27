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

        $wasteNotesPool = [
            'Reject QC: kulit sobek saat penarikan cetakan kayu (lasting)',
            'Cacat lem: sol terkelupas akibat suhu oven pemanas tidak stabil',
            'Kulit berkerut parah & serat pecah saat proses pengeleman upper',
            'Bercak jamur pada lembaran kulit akibat kelembapan gudang workshop',
            'Reject jahitan: jarum mesin jahit patah & merusak tekstur kulit finish',
            'Goresan pisau seset terlalu dalam pada material kulit pull-up',
        ];

        foreach ($products as $index => $product) {
            // 1. Stok Awal Setup Sistem
            StockMovement::firstOrCreate(
                [
                    'product_id' => $product->id,
                    'type' => 'in',
                    'notes' => 'Stok awal setup sistem bengkel sepatu & sandal',
                ],
                [
                    'quantity' => $product->current_stock,
                    'movement_date' => now()->subDays(30)->toDateString(),
                ]
            );

            // 2. Produksi Masuk Tambahan dari Workshop Pengrajin
            StockMovement::firstOrCreate(
                [
                    'product_id' => $product->id,
                    'type' => 'in',
                    'notes' => 'Batch produksi selesai dari workshop pengrajin',
                ],
                [
                    'quantity' => rand(8, 20),
                    'movement_date' => now()->subDays(rand(10, 20))->toDateString(),
                ]
            );

            // 3. Catatan Waste / Reject Produksi pada Sebagian Produk Sepatu & Sandal
            if ($index % 2 === 0) {
                $noteIndex = $index % count($wasteNotesPool);
                $wasteQty = ($product->category === 'Sepatu Pria' || $product->category === 'Sepatu Wanita') ? rand(1, 2) : rand(1, 3);

                StockMovement::firstOrCreate(
                    [
                        'product_id' => $product->id,
                        'type' => 'waste',
                        'notes' => $wasteNotesPool[$noteIndex],
                    ],
                    [
                        'quantity' => $wasteQty,
                        'movement_date' => now()->subDays(rand(2, 12))->toDateString(),
                    ]
                );
            }

            // 4. Koreksi Stock Opname Berkala
            if ($index % 4 === 0) {
                StockMovement::firstOrCreate(
                    [
                        'product_id' => $product->id,
                        'type' => 'adjustment',
                        'notes' => 'Koreksi hasil stock opname fisik gudang & showroom',
                    ],
                    [
                        'quantity' => 1,
                        'movement_date' => now()->subDays(rand(1, 5))->toDateString(),
                    ]
                );
            }
        }
    }
}
