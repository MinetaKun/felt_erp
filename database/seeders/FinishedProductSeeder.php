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
                'size' => 'M',
                'quantity' => 50,
                'price' => 150.00,
                'wage_per_unit' => 30.00,
                'description' => 'Hand-knitted woolen sweater with traditional patterns',
                'location' => 'Warehouse A',
                'status' => 'in_stock'
            ],
            [
                'name' => 'Woolen Scarf',
                'type' => 'Scarf',
                'size' => 'L',
                'quantity' => 100,
                'price' => 80.00,
                'wage_per_unit' => 15.00,
                'description' => 'Hand-knitted woolen scarf with fringe ends',
                'location' => 'Warehouse A',
                'status' => 'in_stock'
            ],
            [
                'name' => 'Woolen Cap',
                'type' => 'Cap',
                'size' => 'S',
                'quantity' => 200,
                'price' => 40.00,
                'wage_per_unit' => 8.00,
                'description' => 'Hand-knitted woolen cap with pom-pom',
                'location' => 'Warehouse A',
                'status' => 'in_stock'
            ],
            [
                'name' => 'Woolen Gloves',
                'type' => 'Gloves',
                'size' => 'M',
                'quantity' => 150,
                'price' => 60.00,
                'wage_per_unit' => 12.00,
                'description' => 'Hand-knitted woolen gloves with ribbed cuffs',
                'location' => 'Warehouse A',
                'status' => 'in_stock'
            ],
            [
                'name' => 'Woolen Socks',
                'type' => 'Socks',
                'size' => 'L',
                'quantity' => 300,
                'price' => 30.00,
                'wage_per_unit' => 6.00,
                'description' => 'Hand-knitted woolen socks with reinforced heels',
                'location' => 'Warehouse A',
                'status' => 'in_stock'
            ],
            [
                'name' => 'Woolen Shawl',
                'type' => 'Shawl',
                'size' => 'XL',
                'quantity' => 25,
                'price' => 200.00,
                'wage_per_unit' => 40.00,
                'description' => 'Hand-knitted woolen shawl with intricate patterns',
                'location' => 'Warehouse A',
                'status' => 'in_stock'
            ],
            [
                'name' => 'Woolen Blanket',
                'type' => 'Blanket',
                'size' => 'Queen',
                'quantity' => 15,
                'price' => 350.00,
                'wage_per_unit' => 70.00,
                'description' => 'Hand-knitted woolen blanket with geometric patterns',
                'location' => 'Warehouse A',
                'status' => 'in_stock'
            ],
            [
                'name' => 'Woolen Poncho',
                'type' => 'Poncho',
                'size' => 'L',
                'quantity' => 30,
                'price' => 180.00,
                'wage_per_unit' => 35.00,
                'description' => 'Hand-knitted woolen poncho with fringe details',
                'location' => 'Warehouse A',
                'status' => 'in_stock'
            ],
            [
                'name' => 'Woolen Headband',
                'type' => 'Headband',
                'size' => 'One Size',
                'quantity' => 75,
                'price' => 25.00,
                'wage_per_unit' => 5.00,
                'description' => 'Hand-knitted woolen headband with decorative knot',
                'location' => 'Warehouse A',
                'status' => 'in_stock'
            ],
            [
                'name' => 'Woolen Mittens',
                'type' => 'Mittens',
                'size' => 'M',
                'quantity' => 40,
                'price' => 45.00,
                'wage_per_unit' => 9.00,
                'description' => 'Hand-knitted woolen mittens with thumb hole',
                'location' => 'Warehouse A',
                'status' => 'in_stock'
            ]
        ];

        foreach ($products as $product) {
            FinishedProduct::create($product);
        }
    }
}
