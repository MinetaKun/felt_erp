<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RawMaterialTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $materials = [
            // Wool Materials
            [
                'name' => 'Merino Wool',
                'category' => 'Wool',
                'type' => 'Raw Wool',
                'color' => 'Natural',
                'quantity' => 500.00,
                'unit' => 'kg',
                'min_stock_level' => 100.00,
                'price_per_unit' => 1500.00,
                'supplier' => 'Australian Wool Company',
                'description' => 'High-quality merino wool for fine textiles',
                'location' => 'Warehouse A - Section 1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Alpaca Wool',
                'category' => 'Wool',
                'type' => 'Raw Wool',
                'color' => 'Natural',
                'quantity' => 300.00,
                'unit' => 'kg',
                'min_stock_level' => 50.00,
                'price_per_unit' => 2000.00,
                'supplier' => 'Peruvian Alpaca Farms',
                'description' => 'Premium alpaca wool for luxury items',
                'location' => 'Warehouse A - Section 2',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Dyes
            [
                'name' => 'Natural Indigo',
                'category' => 'Dye',
                'type' => 'Natural Dye',
                'color' => 'Blue',
                'quantity' => 50.00,
                'unit' => 'kg',
                'min_stock_level' => 10.00,
                'price_per_unit' => 3000.00,
                'supplier' => 'Organic Dyes Co.',
                'description' => 'Natural indigo for traditional dyeing',
                'location' => 'Warehouse B - Section 1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Madder Root',
                'category' => 'Dye',
                'type' => 'Natural Dye',
                'color' => 'Red',
                'quantity' => 40.00,
                'unit' => 'kg',
                'min_stock_level' => 8.00,
                'price_per_unit' => 2500.00,
                'supplier' => 'Organic Dyes Co.',
                'description' => 'Natural red dye from madder root',
                'location' => 'Warehouse B - Section 1',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Tools and Equipment
            [
                'name' => 'Felting Needles',
                'category' => 'Tools',
                'type' => 'Equipment',
                'color' => null,
                'quantity' => 1000.00,
                'unit' => 'pieces',
                'min_stock_level' => 200.00,
                'price_per_unit' => 50.00,
                'supplier' => 'Craft Tools Ltd.',
                'description' => 'High-quality felting needles for wool processing',
                'location' => 'Warehouse C - Section 1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Spinning Wheels',
                'category' => 'Tools',
                'type' => 'Equipment',
                'color' => null,
                'quantity' => 10.00,
                'unit' => 'pieces',
                'min_stock_level' => 2.00,
                'price_per_unit' => 15000.00,
                'supplier' => 'Traditional Crafts Equipment',
                'description' => 'Professional spinning wheels for yarn production',
                'location' => 'Warehouse C - Section 2',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Chemicals
            [
                'name' => 'Wool Scour',
                'category' => 'Chemical',
                'type' => 'Cleaning Agent',
                'color' => null,
                'quantity' => 200.00,
                'unit' => 'liters',
                'min_stock_level' => 40.00,
                'price_per_unit' => 800.00,
                'supplier' => 'Textile Chemicals Inc.',
                'description' => 'Specialized cleaning agent for wool processing',
                'location' => 'Warehouse D - Section 1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Mordant',
                'category' => 'Chemical',
                'type' => 'Dye Fixative',
                'color' => null,
                'quantity' => 100.00,
                'unit' => 'kg',
                'min_stock_level' => 20.00,
                'price_per_unit' => 1200.00,
                'supplier' => 'Textile Chemicals Inc.',
                'description' => 'Chemical fixative for natural dyes',
                'location' => 'Warehouse D - Section 1',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Packaging Materials
            [
                'name' => 'Plastic Bags',
                'category' => 'Packaging',
                'type' => 'Storage',
                'color' => 'Clear',
                'quantity' => 5000.00,
                'unit' => 'pieces',
                'min_stock_level' => 1000.00,
                'price_per_unit' => 2.00,
                'supplier' => 'Packaging Solutions',
                'description' => 'Clear plastic bags for material storage',
                'location' => 'Warehouse E - Section 1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cardboard Boxes',
                'category' => 'Packaging',
                'type' => 'Storage',
                'color' => 'Brown',
                'quantity' => 200.00,
                'unit' => 'pieces',
                'min_stock_level' => 50.00,
                'price_per_unit' => 100.00,
                'supplier' => 'Packaging Solutions',
                'description' => 'Standard size boxes for material storage',
                'location' => 'Warehouse E - Section 2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('raw_materials')->insert($materials);
    }
}
