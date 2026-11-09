<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class StockMovementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        StockMovement::truncate();
        Schema::enableForeignKeyConstraints();

        if (Product::count() === 0) {
            $this->call(ProductSeeder::class);
        }

        $products = Product::all();
        $finishedProducts = $products->filter(fn ($p) => $p->base_price > 0);

        $wasteNotesPool = [
            'Reject QC: kulit sobek saat proses seset (skiving) bagian pinggir upper',
            'Cacat oven lem: penarikan sol manual (lasting) renggang dan gagal rekat',
            'Kulit berkerut parah & serat pecah saat proses pengeleman upper ke insole',
            'Bercak cacat alami pada lembaran kulit yang baru terlihat setelah dipotong',
            'Jahitan loncat & jarum patah merusak pola kulit jadi',
            'Goresan pisau pola terlalu dalam saat memotong lembaran kulit pull-up',
        ];

        // 1. Stok Awal Setup Sistem (90 hari lalu)
        foreach ($products as $product) {
            StockMovement::firstOrCreate(
                [
                    'product_id' => $product->id,
                    'type' => 'in',
                    'notes' => 'Stok awal setup sistem bengkel sepatu & sandal',
                ],
                [
                    'quantity' => $product->current_stock + 40,
                    'movement_date' => now()->subDays(90)->toDateString(),
                ]
            );
        }

        // 2. Batch Produksi Masuk Berkala Selama 3 Bulan (Setiap 10-14 Hari)
        for ($day = 80; $day >= 5; $day -= 12) {
            foreach ($finishedProducts->random(min(4, $finishedProducts->count())) as $prod) {
                StockMovement::create([
                    'product_id' => $prod->id,
                    'type' => 'in',
                    'quantity' => rand(10, 25),
                    'movement_date' => now()->subDays($day)->toDateString(),
                    'notes' => 'Selesai batch produksi workshop pengrajin',
                ]);
            }
        }

        // 3. Catatan Limbah / Waste Kulit Berkala Selama 3 Bulan
        // Tersebar dari 85 hari lalu sampai 2 hari lalu
        $wasteDays = [85, 76, 68, 59, 50, 42, 33, 25, 18, 11, 5, 2];
        foreach ($wasteDays as $idx => $daysAgo) {
            $prod = $finishedProducts->random();
            $wasteQty = rand(1, 3);
            $note = $wasteNotesPool[$idx % count($wasteNotesPool)];

            StockMovement::create([
                'product_id' => $prod->id,
                'type' => 'waste',
                'quantity' => $wasteQty,
                'movement_date' => now()->subDays($daysAgo)->toDateString(),
                'notes' => $note,
            ]);
        }

        // 4. Koreksi Stock Opname Bulanan
        $adjustmentDays = [60, 30, 3];
        foreach ($adjustmentDays as $daysAgo) {
            $prod = $finishedProducts->random();
            StockMovement::create([
                'product_id' => $prod->id,
                'type' => 'adjustment',
                'quantity' => 1,
                'movement_date' => now()->subDays($daysAgo)->toDateString(),
                'notes' => 'Koreksi hasil stock opname fisik gudang workshop',
            ]);
        }
    }
}
