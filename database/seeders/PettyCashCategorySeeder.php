<?php

namespace Database\Seeders;

use App\Models\PettyCashCategory;
use Illuminate\Database\Seeder;

class PettyCashCategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            [
                'name' => 'CASH ON HAND',
                'type' => 'income',
                'description' => 'Initial cash balance and other money received',
                'is_active' => true
            ],
            [
                'name' => 'KITCHEN EXP',
                'type' => 'expense',
                'description' => 'Kitchen supplies and food expenses',
                'is_active' => true
            ],
            [
                'name' => 'WATER',
                'type' => 'expense',
                'description' => 'Water utility bills',
                'is_active' => true
            ],
            [
                'name' => 'FUEL',
                'type' => 'expense',
                'description' => 'Vehicle fuel and transportation costs',
                'is_active' => true
            ],
            [
                'name' => 'ELECTRICITY',
                'type' => 'expense',
                'description' => 'Electricity utility bills',
                'is_active' => true
            ],
            [
                'name' => 'PRODUCTION SUPPLIES',
                'type' => 'expense',
                'description' => 'Raw materials and production inputs',
                'is_active' => true
            ],
            [
                'name' => 'REPAIR AND MAINTENANCE',
                'type' => 'expense',
                'description' => 'Equipment and facility maintenance',
                'is_active' => true
            ],
            [
                'name' => 'FIXED ASSETS',
                'type' => 'expense',
                'description' => 'Purchase of capital equipment',
                'is_active' => true
            ],
            [
                'name' => 'WAGES',
                'type' => 'expense',
                'description' => 'Daily or weekly worker wages',
                'is_active' => true
            ],
            [
                'name' => 'ADVANCE',
                'type' => 'expense',
                'description' => 'Staff cash advances',
                'is_active' => true
            ],
            [
                'name' => 'SALARY',
                'type' => 'expense',
                'description' => 'Monthly staff salaries',
                'is_active' => true
            ],
            [
                'name' => 'OFFICE EXP',
                'type' => 'expense',
                'description' => 'General office expenses',
                'is_active' => true
            ],
            [
                'name' => 'STATIONERY',
                'type' => 'expense',
                'description' => 'Office stationery and supplies',
                'is_active' => true
            ],
            [
                'name' => 'ADVANCE RETURN',
                'type' => 'income',
                'description' => 'Returned staff advances',
                'is_active' => true
            ],
            [
                'name' => 'PERFORMANCE APPRAISAL',
                'type' => 'expense',
                'description' => 'Employee performance bonuses',
                'is_active' => true
            ],
            [
                'name' => 'ADVANCE AMOUNT',
                'type' => 'expense',
                'description' => 'Additional staff cash advances',
                'is_active' => true
            ],
            [
                'name' => 'CURRENT ASSETS',
                'type' => 'expense',
                'description' => 'Short-term asset purchases',
                'is_active' => true
            ],
        ];

        foreach ($categories as $category) {
            PettyCashCategory::firstOrCreate(
                ['name' => $category['name']],
                $category
            );
        }
    }
}
