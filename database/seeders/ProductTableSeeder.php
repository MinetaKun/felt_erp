<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProductTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Merino Wool Felt',
                'price' => 29.99,
                'quantity' => 100,
                'size' => '100cm x 100cm',
                'color' => 'Natural White',
                'details' => 'Premium merino wool felt, perfect for crafting and felting projects. 100% natural wool.',
                'image_path' => 'products/merino-wool-felt.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Alpaca Wool Yarn',
                'price' => 19.99,
                'quantity' => 150,
                'size' => '100g',
                'color' => 'Charcoal Grey',
                'details' => 'Luxurious alpaca wool yarn, incredibly soft and warm. Ideal for knitting and crocheting.',
                'image_path' => 'products/alpaca-wool-yarn.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Wool Roving',
                'price' => 24.99,
                'quantity' => 80,
                'size' => '200g',
                'color' => 'Sky Blue',
                'details' => 'Fine wool roving for spinning, felting, and fiber arts. Easy to work with and versatile.',
                'image_path' => 'products/wool-roving.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Wool Felt Sheets',
                'price' => 4.99,
                'quantity' => 200,
                'size' => '20cm x 20cm',
                'color' => 'Assorted Colors',
                'details' => 'Pre-felted wool sheets in various colors. Perfect for small crafts and appliqué work.',
                'image_path' => 'products/wool-felt-sheets.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Wool Batting',
                'price' => 34.99,
                'quantity' => 50,
                'size' => '1m x 2m',
                'color' => 'Natural Cream',
                'details' => '100% wool batting for quilting and crafting. Provides excellent warmth and insulation.',
                'image_path' => 'products/wool-batting.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Wool Fabric',
                'price' => 39.99,
                'quantity' => 75,
                'size' => '1m x 1.5m',
                'color' => 'Forest Green',
                'details' => 'Medium-weight wool fabric, perfect for clothing and home decor. Durable and warm.',
                'image_path' => 'products/wool-fabric.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Wool Felt Balls',
                'price' => 12.99,
                'quantity' => 300,
                'size' => '3cm diameter',
                'color' => 'Rainbow Pack',
                'details' => 'Handmade wool felt balls in assorted colors. Great for crafts, jewelry, and decorations.',
                'image_path' => 'products/wool-felt-balls.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Wool Top',
                'price' => 27.99,
                'quantity' => 60,
                'size' => '100g',
                'color' => 'Burgundy',
                'details' => 'Premium wool top for spinning and felting. Long staple length for smooth spinning.',
                'image_path' => 'products/wool-top.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Wool Blanket',
                'price' => 89.99,
                'quantity' => 25,
                'size' => '150cm x 200cm',
                'color' => 'Navy Blue',
                'details' => 'Luxurious wool blanket, perfect for cold weather. Made from 100% merino wool.',
                'image_path' => 'products/wool-blanket.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Wool Felt Kit',
                'price' => 49.99,
                'quantity' => 40,
                'size' => 'Assorted',
                'color' => 'Multi-color',
                'details' => 'Complete felting kit including wool, needles, and instructions. Perfect for beginners.',
                'image_path' => 'products/wool-felt-kit.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('products')->insert($products);
    }
}
