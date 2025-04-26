<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WoolUsage;
use App\Models\WoolSupplier;
use Carbon\Carbon;

class WoolUsageSeeder extends Seeder
{
    public function run()
    {
        // First, ensure we have a supplier
        $supplier = WoolSupplier::firstOrCreate(
            ['name' => 'Rajan Dyeing'],
            [
                'contact_person' => 'Rajan',
                'phone' => '1234567890',
                'email' => 'rajan@example.com',
                'address' => 'Kathmandu, Nepal',
                'is_active' => true
            ]
        );

        // Sample departments
        $departments = ['SARITA MISS', 'Production', 'Quality Control', 'Research'];

        // Sample color numbers
        $colorNumbers = ['#20', '#40', '#60', '#80', '#100'];

        // Sample usage purposes
        $usagePurposes = ['Felt', 'Bone', 'Carpet', 'Rug', 'Blanket'];

        // Create sample records for the last 3 months
        for ($i = 0; $i < 30; $i++) {
            $date = Carbon::now()->subDays(rand(0, 90));

            WoolUsage::create([
                'date' => $date,
                'bill_number' => 'BILL-' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT),
                'department' => $departments[array_rand($departments)],
                'color_number' => $colorNumbers[array_rand($colorNumbers)],
                'roll_count' => rand(10, 50),
                'total_kg' => rand(10, 100),
                'usage_purpose' => $usagePurposes[array_rand($usagePurposes)],
                'remarks' => rand(0, 1) ? 'Sample remarks for testing' : null,
                'supplier_id' => $supplier->id
            ]);
        }
    }
}
