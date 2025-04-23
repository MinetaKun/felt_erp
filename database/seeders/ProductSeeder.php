<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $products = [
            [
                'name' => 'Woolen Sweater',
                'price' => 150.00,
                'quantity' => 50,
                'size' => 'M',
                'color' => 'Navy Blue',
                'details' => 'Hand-knitted woolen sweater with traditional patterns',
                'image_path' => 'products/sweater.jpg'
            ],
            [
                'name' => 'Woolen Scarf',
                'price' => 80.00,
                'quantity' => 100,
                'size' => 'L',
                'color' => 'Red',
                'details' => 'Hand-knitted woolen scarf with fringe ends',
                'image_path' => 'products/scarf.jpg'
            ],
            [
                'name' => 'Woolen Cap',
                'price' => 40.00,
                'quantity' => 200,
                'size' => 'S',
                'color' => 'Black',
                'details' => 'Hand-knitted woolen cap with pom-pom',
                'image_path' => 'products/cap.jpg'
            ],
            [
                'name' => 'Woolen Gloves',
                'price' => 60.00,
                'quantity' => 150,
                'size' => 'M',
                'color' => 'Gray',
                'details' => 'Hand-knitted woolen gloves with ribbed cuffs',
                'image_path' => 'products/gloves.jpg'
            ],
            [
                'name' => 'Woolen Socks',
                'price' => 30.00,
                'quantity' => 300,
                'size' => 'L',
                'color' => 'White',
                'details' => 'Hand-knitted woolen socks with reinforced heels',
                'image_path' => 'products/socks.jpg'
            ],
            [
                'name' => 'Woolen Shawl',
                'price' => 200.00,
                'quantity' => 25,
                'size' => 'XL',
                'color' => 'Purple',
                'details' => 'Hand-knitted woolen shawl with intricate patterns',
                'image_path' => 'products/shawl.jpg'
            ],
            [
                'name' => 'Woolen Blanket',
                'price' => 350.00,
                'quantity' => 15,
                'size' => 'Queen',
                'color' => 'Beige',
                'details' => 'Hand-knitted woolen blanket with geometric patterns',
                'image_path' => 'products/blanket.jpg'
            ],
            [
                'name' => 'Woolen Poncho',
                'price' => 180.00,
                'quantity' => 30,
                'size' => 'L',
                'color' => 'Green',
                'details' => 'Hand-knitted woolen poncho with fringe details',
                'image_path' => 'products/poncho.jpg'
            ],
            [
                'name' => 'Woolen Headband',
                'price' => 25.00,
                'quantity' => 75,
                'size' => 'One Size',
                'color' => 'Pink',
                'details' => 'Hand-knitted woolen headband with decorative knot',
                'image_path' => 'products/headband.jpg'
            ],
            [
                'name' => 'Woolen Mittens',
                'price' => 45.00,
                'quantity' => 40,
                'size' => 'M',
                'color' => 'Blue',
                'details' => 'Hand-knitted woolen mittens with thumb hole',
                'image_path' => 'products/mittens.jpg'
            ]
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
