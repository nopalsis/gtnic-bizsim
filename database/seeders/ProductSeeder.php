<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Product::truncate();
        Schema::enableForeignKeyConstraints();

        // Daftar produk dengan nama yang mudah dipahami dan banderol harga <= Rp 250.000
        $products = [
            // ==========================================
            // Kategori: Sandal Kulit Pria (Banderol <= 250k)
            // ==========================================
            [
                'sku' => 'SND-SLP-01',
                'name' => 'Sandal Kulit Selop Pria',
                'category' => 'Sandal Kulit Pria',
                'unit' => 'pasang',
                'base_price' => 145000.00,
                'cost_price' => 75000.00,
                'current_stock' => 28,
                'min_stock_alert' => 6,
                'is_active' => true,
            ],
            [
                'sku' => 'SND-JPT-02',
                'name' => 'Sandal Kulit Jepit Pria',
                'category' => 'Sandal Kulit Pria',
                'unit' => 'pasang',
                'base_price' => 120000.00,
                'cost_price' => 60000.00,
                'current_stock' => 35,
                'min_stock_alert' => 8,
                'is_active' => true,
            ],
            [
                'sku' => 'SND-TLI-03',
                'name' => 'Sandal Kulit Tali Kasual',
                'category' => 'Sandal Kulit Pria',
                'unit' => 'pasang',
                'base_price' => 135000.00,
                'cost_price' => 68000.00,
                'current_stock' => 22,
                'min_stock_alert' => 5,
                'is_active' => true,
            ],

            // ==========================================
            // Kategori: Sepatu Pria (Banderol <= 250k)
            // ==========================================
            [
                'sku' => 'SPT-PNT-01',
                'name' => 'Sepatu Kulit Formal Pantofel',
                'category' => 'Sepatu Pria',
                'unit' => 'pasang',
                'base_price' => 245000.00,
                'cost_price' => 125000.00,
                'current_stock' => 18,
                'min_stock_alert' => 5,
                'is_active' => true,
            ],
            [
                'sku' => 'SPT-LFR-02',
                'name' => 'Sepatu Kulit Kasual Loafer',
                'category' => 'Sepatu Pria',
                'unit' => 'pasang',
                'base_price' => 210000.00,
                'cost_price' => 110000.00,
                'current_stock' => 24,
                'min_stock_alert' => 5,
                'is_active' => true,
            ],
            [
                'sku' => 'SPT-SLP-03',
                'name' => 'Sepatu Kulit Slop Santai',
                'category' => 'Sepatu Pria',
                'unit' => 'pasang',
                'base_price' => 185000.00,
                'cost_price' => 95000.00,
                'current_stock' => 16,
                'min_stock_alert' => 4,
                'is_active' => true,
            ],

            // ==========================================
            // Kategori: Sandal & Sepatu Wanita (Banderol <= 250k)
            // ==========================================
            [
                'sku' => 'SND-WNT-01',
                'name' => 'Sandal Kulit Tali Wanita',
                'category' => 'Sandal Kulit Wanita',
                'unit' => 'pasang',
                'base_price' => 130000.00,
                'cost_price' => 65000.00,
                'current_stock' => 25,
                'min_stock_alert' => 6,
                'is_active' => true,
            ],
            [
                'sku' => 'SPT-FLT-01',
                'name' => 'Sepatu Kulit Flat Wanita',
                'category' => 'Sepatu Wanita',
                'unit' => 'pasang',
                'base_price' => 195000.00,
                'cost_price' => 100000.00,
                'current_stock' => 19,
                'min_stock_alert' => 4,
                'is_active' => true,
            ],

            // ==========================================
            // Kategori: Bahan Baku & Pendukung
            // ==========================================
            [
                'sku' => 'RAW-LEA-01',
                'name' => 'Kulit Sapi Pull-Up Lembaran',
                'category' => 'Bahan Baku Kulit',
                'unit' => 'feet',
                'base_price' => 0.00,
                'cost_price' => 35000.00,
                'current_stock' => 140,
                'min_stock_alert' => 30,
                'is_active' => true,
            ],
            [
                'sku' => 'RAW-LEA-02',
                'name' => 'Kulit Sapi Nabati Lembaran',
                'category' => 'Bahan Baku Kulit',
                'unit' => 'feet',
                'base_price' => 0.00,
                'cost_price' => 42000.00,
                'current_stock' => 90,
                'min_stock_alert' => 25,
                'is_active' => true,
            ],
            [
                'sku' => 'CMP-SOL-01',
                'name' => 'Sol Karet Sandal & Sepatu',
                'category' => 'Komponen Sol',
                'unit' => 'pasang',
                'base_price' => 0.00,
                'cost_price' => 20000.00,
                'current_stock' => 160,
                'min_stock_alert' => 40,
                'is_active' => true,
            ],
            [
                'sku' => 'SUP-CHM-01',
                'name' => 'Lem Kuning Khusus Kulit (Kaleng)',
                'category' => 'Bahan Pendukung',
                'unit' => 'kaleng',
                'base_price' => 0.00,
                'cost_price' => 55000.00,
                'current_stock' => 18,
                'min_stock_alert' => 5,
                'is_active' => true,
            ],
            [
                'sku' => 'ACC-PRD-01',
                'name' => 'Semir Balsam Perawatan Kulit',
                'category' => 'Perawatan & Aksesoris',
                'unit' => 'pcs',
                'base_price' => 45000.00,
                'cost_price' => 22000.00,
                'current_stock' => 45,
                'min_stock_alert' => 10,
                'is_active' => true,
            ],
        ];

        foreach ($products as $p) {
            Product::create($p);
        }
    }
}
