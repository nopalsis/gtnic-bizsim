<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Sepatu Pria',
                'type' => 'finished',
                'prefix_sku' => 'FG-SHO',
                'description' => 'Sepatu formal, derby, oxford, boots kulit asli pria',
            ],
            [
                'name' => 'Sepatu Wanita',
                'type' => 'finished',
                'prefix_sku' => 'FG-SHO',
                'description' => 'Sepatu flat, heels, loafer, dan boots wanita kulit',
            ],
            [
                'name' => 'Sandal Kulit Pria',
                'type' => 'finished',
                'prefix_sku' => 'FG-SND',
                'description' => 'Sandal slide, strap, dan selop kulit pria',
            ],
            [
                'name' => 'Sandal Kulit Wanita',
                'type' => 'finished',
                'prefix_sku' => 'FG-SND',
                'description' => 'Sandal kasual, gladiator, dan tali kulit wanita',
            ],
            [
                'name' => 'Bahan Baku Kulit',
                'type' => 'raw',
                'prefix_sku' => 'RAW-LEA',
                'description' => 'Kulit sapi lembaran pull-up, nabati/veg-tan, crazy horse, suede',
            ],
            [
                'name' => 'Komponen Sol',
                'type' => 'raw',
                'prefix_sku' => 'CMP-SOL',
                'description' => 'Outsole karet, microtech, leather sole, hak kayu/karet',
            ],
            [
                'name' => 'Bahan Pendukung',
                'type' => 'raw',
                'prefix_sku' => 'SUP-CHM',
                'description' => 'Lem kuning/putih, cairan primer, paku sol, benang wax',
            ],
            [
                'name' => 'Perawatan & Aksesoris',
                'type' => 'raw',
                'prefix_sku' => 'ACC-PRD',
                'description' => 'Semir leather balsam, sikat bulu kuda, tali sepatu, dust bag',
            ],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(
                ['name' => $cat['name']],
                [
                    'slug' => Str::slug($cat['name']),
                    'type' => $cat['type'],
                    'prefix_sku' => $cat['prefix_sku'],
                    'description' => $cat['description'],
                    'is_active' => true,
                ]
            );
        }
    }
}

