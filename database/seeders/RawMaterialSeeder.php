<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RawMaterial;

class RawMaterialSeeder extends Seeder
{
    public function run()
    {
        $materials = [
            [
                'name' => 'Wool Yarn',
                'type' => 'Natural',
                'color' => 'Natural',
                'quantity' => 1000,
                'unit' => 'kg',
                'min_stock_level' => 200,
                'price_per_unit' => 15.00,
                'supplier' => 'Wool Suppliers Inc.',
                'description' => 'High-quality natural wool yarn',
                'location' => 'Warehouse A'
            ],
            [
                'name' => 'Synthetic Yarn',
                'type' => 'Synthetic',
                'color' => 'White',
                'quantity' => 800,
                'unit' => 'kg',
                'min_stock_level' => 150,
                'price_per_unit' => 10.00,
                'supplier' => 'Synthetic Fibers Co.',
                'description' => 'Durable synthetic yarn',
                'location' => 'Warehouse A'
            ],
            [
                'name' => 'Knitting Machine',
                'type' => 'Industrial',
                'color' => 'Black',
                'quantity' => 5,
                'unit' => 'piece',
                'min_stock_level' => 1,
                'price_per_unit' => 5000.00,
                'supplier' => 'Industrial Machines Ltd.',
                'description' => 'Professional knitting machine',
                'location' => 'Machine Room'
            ],
            [
                'name' => 'Measuring Tape',
                'type' => 'Measuring',
                'color' => 'Yellow',
                'quantity' => 50,
                'unit' => 'piece',
                'min_stock_level' => 10,
                'price_per_unit' => 5.00,
                'supplier' => 'Tools & Gadgets Co.',
                'description' => 'Professional measuring tape',
                'location' => 'Tool Room'
            ],
            [
                'name' => 'Scissors',
                'type' => 'Cutting',
                'color' => 'Silver',
                'quantity' => 30,
                'unit' => 'piece',
                'min_stock_level' => 5,
                'price_per_unit' => 8.00,
                'supplier' => 'Tools & Gadgets Co.',
                'description' => 'Professional fabric scissors',
                'location' => 'Tool Room'
            ]
        ];

        foreach ($materials as $material) {
            RawMaterial::create($material);
        }
    }
}
