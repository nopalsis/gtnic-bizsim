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
            // Beverages
            [
                'name' => 'Espresso Single Shot',
                'category' => 'Beverage',
                'base_price' => 18000.00,
                'cost_price' => 5000.00,
                'current_stock' => 120,
                'min_stock_alert' => 20,
                'is_active' => true,
            ],
            [
                'name' => 'Americano',
                'category' => 'Beverage',
                'base_price' => 22000.00,
                'cost_price' => 6000.00,
                'current_stock' => 95,
                'min_stock_alert' => 20,
                'is_active' => true,
            ],
            [
                'name' => 'Caffe Latte',
                'category' => 'Beverage',
                'base_price' => 28000.00,
                'cost_price' => 9000.00,
                'current_stock' => 80,
                'min_stock_alert' => 15,
                'is_active' => true,
            ],
            [
                'name' => 'Cappuccino',
                'category' => 'Beverage',
                'base_price' => 28000.00,
                'cost_price' => 9500.00,
                'current_stock' => 75,
                'min_stock_alert' => 15,
                'is_active' => true,
            ],
            [
                'name' => 'Caramel Macchiato',
                'category' => 'Beverage',
                'base_price' => 32000.00,
                'cost_price' => 11000.00,
                'current_stock' => 60,
                'min_stock_alert' => 15,
                'is_active' => true,
            ],
            [
                'name' => 'Matcha Green Tea Latte',
                'category' => 'Beverage',
                'base_price' => 30000.00,
                'cost_price' => 10000.00,
                'current_stock' => 50,
                'min_stock_alert' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'Signature Blend Iced Coffee',
                'category' => 'Beverage',
                'base_price' => 25000.00,
                'cost_price' => 12000.00,
                'current_stock' => 65,
                'min_stock_alert' => 15,
                'is_active' => true,
            ],
            [
                'name' => 'Earl Grey Artisan Tea',
                'category' => 'Beverage',
                'base_price' => 20000.00,
                'cost_price' => 5000.00,
                'current_stock' => 45,
                'min_stock_alert' => 10,
                'is_active' => true,
            ],

            // Foods & Pastry
            [
                'name' => 'French Butter Croissant',
                'category' => 'Food',
                'base_price' => 22000.00,
                'cost_price' => 8000.00,
                'current_stock' => 30,
                'min_stock_alert' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'Almond Cream Croissant',
                'category' => 'Food',
                'base_price' => 28000.00,
                'cost_price' => 11000.00,
                'current_stock' => 25,
                'min_stock_alert' => 8,
                'is_active' => true,
            ],
            [
                'name' => 'Dark Chocolate Fudge Brownie',
                'category' => 'Food',
                'base_price' => 20000.00,
                'cost_price' => 7000.00,
                'current_stock' => 35,
                'min_stock_alert' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'Beef Pastrami Bagel Sandwich',
                'category' => 'Food',
                'base_price' => 42000.00,
                'cost_price' => 18000.00,
                'current_stock' => 15,
                'min_stock_alert' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Smoked Chicken Panini',
                'category' => 'Food',
                'base_price' => 38000.00,
                'cost_price' => 16000.00,
                'current_stock' => 18,
                'min_stock_alert' => 5,
                'is_active' => true,
            ],

            // Coffee Beans (Retail / Raw Material)
            [
                'name' => 'GTNIC House Blend Whole Beans 250g',
                'category' => 'Coffee Beans',
                'base_price' => 85000.00,
                'cost_price' => 45000.00,
                'current_stock' => 40,
                'min_stock_alert' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'Single Origin Aceh Gayo 250g',
                'category' => 'Coffee Beans',
                'base_price' => 110000.00,
                'cost_price' => 55000.00,
                'current_stock' => 25,
                'min_stock_alert' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Single Origin Toraja Sapan 250g',
                'category' => 'Coffee Beans',
                'base_price' => 105000.00,
                'cost_price' => 52000.00,
                'current_stock' => 20,
                'min_stock_alert' => 5,
                'is_active' => true,
            ],

            // Merchandise
            [
                'name' => 'GTNIC Matte Ceramic Mug 350ml',
                'category' => 'Merchandise',
                'base_price' => 65000.00,
                'cost_price' => 30000.00,
                'current_stock' => 30,
                'min_stock_alert' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'GTNIC Vacuum Insulated Tumbler 500ml',
                'category' => 'Merchandise',
                'base_price' => 135000.00,
                'cost_price' => 60000.00,
                'current_stock' => 20,
                'min_stock_alert' => 5,
                'is_active' => true,
            ],
        ];

        foreach ($products as $item) {
            Product::updateOrCreate(
                ['name' => $item['name']],
                $item
            );
        }
    }
}
