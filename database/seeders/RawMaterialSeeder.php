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
                'name' => 'Cotton Fabric',
                'category' => 'raw_material',
                'type' => 'Fabric',
                'color' => 'White',
                'quantity' => 1000,
                'unit' => 'meters',
                'min_stock_level' => 200,
                'price_per_unit' => 150,
                'supplier' => 'Textile Suppliers Ltd',
                'description' => 'High-quality cotton fabric for clothing production',
                'location' => 'Warehouse A, Shelf 1'
            ],
            [
                'name' => 'Sewing Machine',
                'category' => 'machine',
                'type' => 'Equipment',
                'color' => 'Black',
                'quantity' => 5,
                'unit' => 'units',
                'min_stock_level' => 2,
                'price_per_unit' => 25000,
                'supplier' => 'Industrial Equipment Co',
                'description' => 'Professional sewing machine for garment production',
                'location' => 'Production Floor'
            ],
            [
                'name' => 'Thread',
                'category' => 'raw_material',
                'type' => 'Sewing Supplies',
                'color' => 'Black',
                'quantity' => 500,
                'unit' => 'spools',
                'min_stock_level' => 100,
                'price_per_unit' => 25,
                'supplier' => 'Sewing Supplies Inc',
                'description' => 'High-strength polyester thread',
                'location' => 'Warehouse A, Shelf 2'
            ],
            [
                'name' => 'Computer',
                'category' => 'gadget',
                'type' => 'Electronics',
                'color' => 'Silver',
                'quantity' => 3,
                'unit' => 'units',
                'min_stock_level' => 1,
                'price_per_unit' => 45000,
                'supplier' => 'Tech Solutions Ltd',
                'description' => 'Design workstation for pattern making',
                'location' => 'Design Department'
            ],
            [
                'name' => 'Buttons',
                'category' => 'raw_material',
                'type' => 'Accessories',
                'color' => 'White',
                'quantity' => 2000,
                'unit' => 'pieces',
                'min_stock_level' => 500,
                'price_per_unit' => 2,
                'supplier' => 'Accessories World',
                'description' => 'Standard white buttons for shirts',
                'location' => 'Warehouse B, Shelf 1'
            ]
        ];

        foreach ($materials as $material) {
            RawMaterial::create($material);
        }
    }
}
