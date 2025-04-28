<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WoolUsageTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $usageRecords = [
            [
                'date' => Carbon::now()->subDays(5),
                'bill_number' => 'WU-2024-001',
                'department' => 'Knitting',
                'color_number' => 'C101',
                'roll_count' => 10,
                'total_kg' => 25.5,
                'remarks' => 'Merino wool for sweater production',
                'usage_purpose' => 'Production',
                'supplier_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'date' => Carbon::now()->subDays(4),
                'bill_number' => 'WU-2024-002',
                'department' => 'Dyeing',
                'color_number' => 'C102',
                'roll_count' => 5,
                'total_kg' => 12.8,
                'remarks' => 'Alpaca wool for scarf dyeing',
                'usage_purpose' => 'Production',
                'supplier_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'date' => Carbon::now()->subDays(3),
                'bill_number' => 'WU-2024-003',
                'department' => 'Weaving',
                'color_number' => 'C103',
                'roll_count' => 8,
                'total_kg' => 18.2,
                'remarks' => 'Cashmere wool for blanket weaving',
                'usage_purpose' => 'Production',
                'supplier_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'date' => Carbon::now()->subDays(2),
                'bill_number' => 'WU-2024-004',
                'department' => 'Quality Control',
                'color_number' => 'C104',
                'roll_count' => 2,
                'total_kg' => 5.0,
                'remarks' => 'Merino wool for quality testing',
                'usage_purpose' => 'Testing',
                'supplier_id' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'date' => Carbon::now()->subDay(),
                'bill_number' => 'WU-2024-005',
                'department' => 'Knitting',
                'color_number' => 'C105',
                'roll_count' => 15,
                'total_kg' => 30.0,
                'remarks' => 'Wool blend for sock production',
                'usage_purpose' => 'Production',
                'supplier_id' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'date' => Carbon::now(),
                'bill_number' => 'WU-2024-006',
                'department' => 'Weaving',
                'color_number' => 'C106',
                'roll_count' => 6,
                'total_kg' => 15.5,
                'remarks' => 'Shetland wool for poncho weaving',
                'usage_purpose' => 'Production',
                'supplier_id' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'date' => Carbon::now()->subDays(10),
                'bill_number' => 'WU-2024-007',
                'department' => 'Research',
                'color_number' => 'C107',
                'roll_count' => 3,
                'total_kg' => 7.5,
                'remarks' => 'Wool blend for new product development',
                'usage_purpose' => 'Research',
                'supplier_id' => 7,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'date' => Carbon::now()->subDays(7),
                'bill_number' => 'WU-2024-008',
                'department' => 'Knitting',
                'color_number' => 'C108',
                'roll_count' => 12,
                'total_kg' => 28.0,
                'remarks' => 'Merino wool for glove production',
                'usage_purpose' => 'Production',
                'supplier_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'date' => Carbon::now()->subDays(6),
                'bill_number' => 'WU-2024-009',
                'department' => 'Dyeing',
                'color_number' => 'C109',
                'roll_count' => 4,
                'total_kg' => 10.0,
                'remarks' => 'Alpaca wool for beanie dyeing',
                'usage_purpose' => 'Production',
                'supplier_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'date' => Carbon::now()->subDays(8),
                'bill_number' => 'WU-2024-010',
                'department' => 'Quality Control',
                'color_number' => 'C110',
                'roll_count' => 1,
                'total_kg' => 2.5,
                'remarks' => 'Cashmere wool for quality assurance',
                'usage_purpose' => 'Testing',
                'supplier_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('wool_usages')->insert($usageRecords);
    }
}
