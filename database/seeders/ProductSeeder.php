<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            // ==========================================
            // Kategori: Sepatu Pria (Men's Leather Shoes)
            // ==========================================
            [
                'sku' => 'SKP-OXF-01',
                'name' => 'Oxford Classic Full-Grain Black',
                'category' => 'Sepatu Pria',
                'unit' => 'pasang',
                'base_price' => 850000.00,
                'cost_price' => 420000.00,
                'current_stock' => 24,
                'min_stock_alert' => 5,
                'is_active' => true,
            ],
            [
                'sku' => 'SKP-DRB-02',
                'name' => 'Derby Brogue Heritage Tan Leather',
                'category' => 'Sepatu Pria',
                'unit' => 'pasang',
                'base_price' => 780000.00,
                'cost_price' => 390000.00,
                'current_stock' => 18,
                'min_stock_alert' => 5,
                'is_active' => true,
            ],
            [
                'sku' => 'SKP-LFR-03',
                'name' => 'Penny Loafer Italian Crust Burgundy',
                'category' => 'Sepatu Pria',
                'unit' => 'pasang',
                'base_price' => 720000.00,
                'cost_price' => 360000.00,
                'current_stock' => 22,
                'min_stock_alert' => 5,
                'is_active' => true,
            ],
            [
                'sku' => 'SKP-BT-04',
                'name' => 'Chelsea Boots Genuine Pull-Up Brown',
                'category' => 'Sepatu Pria',
                'unit' => 'pasang',
                'base_price' => 950000.00,
                'cost_price' => 480000.00,
                'current_stock' => 15,
                'min_stock_alert' => 4,
                'is_active' => true,
            ],
            [
                'sku' => 'SKP-CHK-05',
                'name' => 'Chukka Boots Crazy Horse Antique',
                'category' => 'Sepatu Pria',
                'unit' => 'pasang',
                'base_price' => 880000.00,
                'cost_price' => 440000.00,
                'current_stock' => 16,
                'min_stock_alert' => 4,
                'is_active' => true,
            ],
            [
                'sku' => 'SKP-SNK-06',
                'name' => 'Minimalist Leather Sneaker White Nappa',
                'category' => 'Sepatu Pria',
                'unit' => 'pasang',
                'base_price' => 650000.00,
                'cost_price' => 310000.00,
                'current_stock' => 30,
                'min_stock_alert' => 6,
                'is_active' => true,
            ],
            [
                'sku' => 'SKP-MNK-07',
                'name' => 'Double Monkstrap Cognac Calfskin',
                'category' => 'Sepatu Pria',
                'unit' => 'pasang',
                'base_price' => 820000.00,
                'cost_price' => 410000.00,
                'current_stock' => 12,
                'min_stock_alert' => 4,
                'is_active' => true,
            ],

            // ==========================================
            // Kategori: Sepatu Wanita (Women's Leather Shoes)
            // ==========================================
            [
                'sku' => 'SKW-MJN-01',
                'name' => 'Mary Jane Vintage Buckle Polish Black',
                'category' => 'Sepatu Wanita',
                'unit' => 'pasang',
                'base_price' => 580000.00,
                'cost_price' => 280000.00,
                'current_stock' => 20,
                'min_stock_alert' => 5,
                'is_active' => true,
            ],
            [
                'sku' => 'SKW-MLS-02',
                'name' => 'Heeled Leather Mules Caramel Tan',
                'category' => 'Sepatu Wanita',
                'unit' => 'pasang',
                'base_price' => 540000.00,
                'cost_price' => 260000.00,
                'current_stock' => 25,
                'min_stock_alert' => 5,
                'is_active' => true,
            ],
            [
                'sku' => 'SKW-OXF-03',
                'name' => 'Women Wingtip Oxford Two-Tone',
                'category' => 'Sepatu Wanita',
                'unit' => 'pasang',
                'base_price' => 620000.00,
                'cost_price' => 290000.00,
                'current_stock' => 14,
                'min_stock_alert' => 4,
                'is_active' => true,
            ],
            [
                'sku' => 'SKW-ANK-04',
                'name' => 'Ankle Boots Waxed Suede Taupe',
                'category' => 'Sepatu Wanita',
                'unit' => 'pasang',
                'base_price' => 750000.00,
                'cost_price' => 370000.00,
                'current_stock' => 12,
                'min_stock_alert' => 4,
                'is_active' => true,
            ],
            [
                'sku' => 'SKW-PMP-05',
                'name' => 'Classic Block Heel Pumps Mahogany',
                'category' => 'Sepatu Wanita',
                'unit' => 'pasang',
                'base_price' => 590000.00,
                'cost_price' => 275000.00,
                'current_stock' => 18,
                'min_stock_alert' => 5,
                'is_active' => true,
            ],

            // ==========================================
            // Kategori: Sandal Kulit Pria (Men's Leather Sandals)
            // ==========================================
            [
                'sku' => 'SDK-SLP-01',
                'name' => 'Sandal Slop Kulit Nabati (Veg-Tan Natural)',
                'category' => 'Sandal Kulit Pria',
                'unit' => 'pasang',
                'base_price' => 320000.00,
                'cost_price' => 140000.00,
                'current_stock' => 35,
                'min_stock_alert' => 8,
                'is_active' => true,
            ],
            [
                'sku' => 'SDK-JPT-02',
                'name' => 'Sandal Jepit Casual Crazy Horse Saddle',
                'category' => 'Sandal Kulit Pria',
                'unit' => 'pasang',
                'base_price' => 260000.00,
                'cost_price' => 110000.00,
                'current_stock' => 40,
                'min_stock_alert' => 8,
                'is_active' => true,
            ],
            [
                'sku' => 'SDK-BCK-03',
                'name' => 'Sandal Gesper Dobel (Two-Strap Buckle Brown)',
                'category' => 'Sandal Kulit Pria',
                'unit' => 'pasang',
                'base_price' => 380000.00,
                'cost_price' => 165000.00,
                'current_stock' => 28,
                'min_stock_alert' => 6,
                'is_active' => true,
            ],
            [
                'sku' => 'SDK-GLD-04',
                'name' => 'Sandal Tali Backstrap Traveling Olive Leather',
                'category' => 'Sandal Kulit Pria',
                'unit' => 'pasang',
                'base_price' => 350000.00,
                'cost_price' => 150000.00,
                'current_stock' => 22,
                'min_stock_alert' => 6,
                'is_active' => true,
            ],

            // ==========================================
            // Kategori: Sandal Kulit Wanita (Women's Leather Sandals)
            // ==========================================
            [
                'sku' => 'SDW-SLD-01',
                'name' => 'Slide Sandals Soft Nappa Leather Tan',
                'category' => 'Sandal Kulit Wanita',
                'unit' => 'pasang',
                'base_price' => 290000.00,
                'cost_price' => 125000.00,
                'current_stock' => 32,
                'min_stock_alert' => 8,
                'is_active' => true,
            ],
            [
                'sku' => 'SDW-TLI-02',
                'name' => 'Sandal Tali Silang (Criss-Cross Strap Honey)',
                'category' => 'Sandal Kulit Wanita',
                'unit' => 'pasang',
                'base_price' => 310000.00,
                'cost_price' => 135000.00,
                'current_stock' => 26,
                'min_stock_alert' => 6,
                'is_active' => true,
            ],
            [
                'sku' => 'SDW-WDG-03',
                'name' => 'Leather Cork Wedge Sandals Havana',
                'category' => 'Sandal Kulit Wanita',
                'unit' => 'pasang',
                'base_price' => 420000.00,
                'cost_price' => 190000.00,
                'current_stock' => 18,
                'min_stock_alert' => 5,
                'is_active' => true,
            ],

            // ==========================================
            // Kategori: Perawatan & Aksesoris Kulit
            // ==========================================
            [
                'sku' => 'ACC-BLS-01',
                'name' => 'Leather Balsam & Beeswax Polish 100ml',
                'category' => 'Perawatan & Aksesoris',
                'unit' => 'botol',
                'base_price' => 75000.00,
                'cost_price' => 25000.00,
                'current_stock' => 60,
                'min_stock_alert' => 10,
                'is_active' => true,
            ],
            [
                'sku' => 'ACC-WLT-02',
                'name' => 'Dompet Bifold Kulit Sapi Asli Pull-Up',
                'category' => 'Perawatan & Aksesoris',
                'unit' => 'pcs',
                'base_price' => 185000.00,
                'cost_price' => 70000.00,
                'current_stock' => 35,
                'min_stock_alert' => 8,
                'is_active' => true,
            ],
            [
                'sku' => 'ACC-BLT-03',
                'name' => 'Ikat Pinggang Kulit Selapis Solid Brass Buckle',
                'category' => 'Perawatan & Aksesoris',
                'unit' => 'pcs',
                'base_price' => 240000.00,
                'cost_price' => 95000.00,
                'current_stock' => 25,
                'min_stock_alert' => 6,
                'is_active' => true,
            ],
            [
                'sku' => 'ACC-KCH-04',
                'name' => 'Gantungan Kunci & Cardholder Kulit Sisa (Upcycled)',
                'category' => 'Perawatan & Aksesoris',
                'unit' => 'pcs',
                'base_price' => 45000.00,
                'cost_price' => 12000.00,
                'current_stock' => 50,
                'min_stock_alert' => 10,
                'is_active' => true,
            ],

            // ==========================================
            // Kategori: Bahan Baku Kulit (Raw Leather Hides)
            // ==========================================
            [
                'sku' => 'RAW-LEA-001',
                'name' => 'Kulit Nabati Tan Natural A-Grade',
                'category' => 'Bahan Baku Kulit',
                'unit' => 'sq ft',
                'base_price' => 45000.00,
                'cost_price' => 38000.00,
                'current_stock' => 120,
                'min_stock_alert' => 50,
                'is_active' => true,
            ],
            [
                'sku' => 'RAW-LEA-009',
                'name' => 'Kulit Crazy Horse Antique',
                'category' => 'Bahan Baku Kulit',
                'unit' => 'sq ft',
                'base_price' => 52000.00,
                'cost_price' => 44000.00,
                'current_stock' => 65,
                'min_stock_alert' => 30,
                'is_active' => true,
            ],
            [
                'sku' => 'RAW-LEA-015',
                'name' => 'Kulit Pull-Up Dark Brown 5-6oz',
                'category' => 'Bahan Baku Kulit',
                'unit' => 'sq ft',
                'base_price' => 50000.00,
                'cost_price' => 42000.00,
                'current_stock' => 18,
                'min_stock_alert' => 25,
                'is_active' => true,
            ],

            // ==========================================
            // Kategori: Komponen Sol & Hak (Shoe Soles)
            // ==========================================
            [
                'sku' => 'CMP-SOL-004',
                'name' => 'Sol Karet Dainite Import',
                'category' => 'Komponen Sol',
                'unit' => 'pasang',
                'base_price' => 210000.00,
                'cost_price' => 165000.00,
                'current_stock' => 3,
                'min_stock_alert' => 15,
                'is_active' => true,
            ],
            [
                'sku' => 'CMP-SOL-008',
                'name' => 'Sol Karet Vibram Lugged',
                'category' => 'Komponen Sol',
                'unit' => 'pasang',
                'base_price' => 250000.00,
                'cost_price' => 195000.00,
                'current_stock' => 4,
                'min_stock_alert' => 12,
                'is_active' => true,
            ],

            // ==========================================
            // Kategori: Bahan Pendukung (Chemicals & Glue)
            // ==========================================
            [
                'sku' => 'SUP-CHM-002',
                'name' => 'Lem Sepatu Super Bond 500ml',
                'category' => 'Bahan Pendukung',
                'unit' => 'kaleng',
                'base_price' => 55000.00,
                'cost_price' => 45000.00,
                'current_stock' => 2,
                'min_stock_alert' => 10,
                'is_active' => true,
            ],
        ];

        foreach ($products as $item) {
            Product::updateOrCreate(
                ['sku' => $item['sku']],
                $item
            );
        }
    }
}
