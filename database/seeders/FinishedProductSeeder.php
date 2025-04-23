<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FinishedProduct;

class FinishedProductSeeder extends Seeder
{
    public function run()
    {
        $products = [
            [
                'name' => 'Woolen Sweater',
                'type' => 'Sweater',
                'color' => 'Navy Blue',
                'size' => 'M',
                'quantity' => 50,
                'price' => 150.00,
                'description' => 'Hand-knitted woolen sweater',
                'location' => 'Warehouse B',
                'status' => 'in_stock'
            ],
            [
                'name' => 'Woolen Scarf',
                'type' => 'Scarf',
                'color' => 'Red',
                'size' => 'L',
                'quantity' => 100,
                'price' => 80.00,
                'description' => 'Hand-knitted woolen scarf',
                'location' => 'Warehouse B',
                'status' => 'in_stock'
            ],
            [
                'name' => 'Woolen Cap',
                'type' => 'Cap',
                'color' => 'Black',
                'size' => 'S',
                'quantity' => 200,
                'price' => 40.00,
                'description' => 'Hand-knitted woolen cap',
                'location' => 'Warehouse B',
                'status' => 'in_stock'
            ],
            [
                'name' => 'Woolen Gloves',
                'type' => 'Gloves',
                'color' => 'Gray',
                'size' => 'M',
                'quantity' => 150,
                'price' => 60.00,
                'description' => 'Hand-knitted woolen gloves',
                'location' => 'Warehouse B',
                'status' => 'in_stock'
            ],
            [
                'name' => 'Woolen Socks',
                'type' => 'Socks',
                'color' => 'White',
                'size' => 'L',
                'quantity' => 300,
                'price' => 30.00,
                'description' => 'Hand-knitted woolen socks',
                'location' => 'Warehouse B',
                'status' => 'in_stock'
            ]
        ];

        foreach ($products as $product) {
            FinishedProduct::create($product);
        }
    }
}
