<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PayrollAdvanceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $advances = [
            // Artisan 1 - Multiple advances
            [
                'artisan_id' => 1,
                'amount' => 5000.00,
                'date' => '2024-01-15',
                'notes' => 'Emergency medical expenses',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'artisan_id' => 1,
                'amount' => 3000.00,
                'date' => '2024-02-20',
                'notes' => 'Home repair advance',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Artisan 2 - Single large advance
            [
                'artisan_id' => 2,
                'amount' => 10000.00,
                'date' => '2024-01-10',
                'notes' => 'Wedding expenses advance',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Artisan 3 - Regular small advances
            [
                'artisan_id' => 3,
                'amount' => 2000.00,
                'date' => '2024-01-05',
                'notes' => 'Monthly transport advance',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'artisan_id' => 3,
                'amount' => 2000.00,
                'date' => '2024-02-05',
                'notes' => 'Monthly transport advance',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Artisan 4 - Emergency advance
            [
                'artisan_id' => 4,
                'amount' => 8000.00,
                'date' => '2024-02-15',
                'notes' => 'Family emergency advance',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Artisan 5 - Multiple small advances
            [
                'artisan_id' => 5,
                'amount' => 1500.00,
                'date' => '2024-01-20',
                'notes' => 'School fees advance',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'artisan_id' => 5,
                'amount' => 1500.00,
                'date' => '2024-02-20',
                'notes' => 'School fees advance',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Artisan 6 - Festival advance
            [
                'artisan_id' => 6,
                'amount' => 5000.00,
                'date' => '2024-01-25',
                'notes' => 'Festival expenses advance',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Artisan 7 - Vehicle repair advance
            [
                'artisan_id' => 7,
                'amount' => 12000.00,
                'date' => '2024-02-10',
                'notes' => 'Vehicle repair advance',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('payroll_advances')->insert($advances);
    }
}
