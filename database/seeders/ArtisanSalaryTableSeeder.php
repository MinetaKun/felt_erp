<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ArtisanSalaryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $salaries = [
            // Artisan 1 - Regular salary with allowances
            [
                'artisan_id' => 1,
                'salary' => 25000.00,
                'food_allowance' => 3000.00,
                'allowances' => 2000.00,
                'start_date' => '2023-01-01',
                'end_date' => '2023-12-31',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'artisan_id' => 1,
                'salary' => 27000.00,
                'food_allowance' => 3500.00,
                'allowances' => 2500.00,
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Artisan 2 - Higher salary with more allowances
            [
                'artisan_id' => 2,
                'salary' => 30000.00,
                'food_allowance' => 4000.00,
                'allowances' => 3000.00,
                'start_date' => '2023-01-01',
                'end_date' => '2023-12-31',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'artisan_id' => 2,
                'salary' => 32000.00,
                'food_allowance' => 4500.00,
                'allowances' => 3500.00,
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Artisan 3 - Entry level salary
            [
                'artisan_id' => 3,
                'salary' => 20000.00,
                'food_allowance' => 2500.00,
                'allowances' => 1500.00,
                'start_date' => '2023-06-01',
                'end_date' => '2023-12-31',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'artisan_id' => 3,
                'salary' => 22000.00,
                'food_allowance' => 3000.00,
                'allowances' => 2000.00,
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Artisan 4 - Experienced artisan
            [
                'artisan_id' => 4,
                'salary' => 35000.00,
                'food_allowance' => 4500.00,
                'allowances' => 4000.00,
                'start_date' => '2023-01-01',
                'end_date' => '2023-12-31',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'artisan_id' => 4,
                'salary' => 38000.00,
                'food_allowance' => 5000.00,
                'allowances' => 4500.00,
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Artisan 5 - Part-time artisan
            [
                'artisan_id' => 5,
                'salary' => 15000.00,
                'food_allowance' => 2000.00,
                'allowances' => 1000.00,
                'start_date' => '2023-03-01',
                'end_date' => '2023-12-31',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'artisan_id' => 5,
                'salary' => 18000.00,
                'food_allowance' => 2500.00,
                'allowances' => 1500.00,
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Artisan 6 - New hire
            [
                'artisan_id' => 6,
                'salary' => 22000.00,
                'food_allowance' => 3000.00,
                'allowances' => 2000.00,
                'start_date' => '2023-09-01',
                'end_date' => '2023-12-31',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'artisan_id' => 6,
                'salary' => 24000.00,
                'food_allowance' => 3500.00,
                'allowances' => 2500.00,
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Artisan 7 - Senior artisan
            [
                'artisan_id' => 7,
                'salary' => 40000.00,
                'food_allowance' => 5000.00,
                'allowances' => 5000.00,
                'start_date' => '2023-01-01',
                'end_date' => '2023-12-31',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'artisan_id' => 7,
                'salary' => 42000.00,
                'food_allowance' => 5500.00,
                'allowances' => 5500.00,
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('salary_calculations')->insert($salaries);
    }
}
