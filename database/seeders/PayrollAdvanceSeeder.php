<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Artisan;
use App\Models\PayrollAdvance;
use Carbon\Carbon;

class PayrollAdvanceSeeder extends Seeder
{
    public function run()
    {
        // Get all artisans
        $artisans = Artisan::all();

        // Create advances for each artisan
        foreach ($artisans as $artisan) {
            // Create 3 random advances for each artisan
            for ($i = 0; $i < 3; $i++) {
                PayrollAdvance::create([
                    'artisan_id' => $artisan->id,
                    'amount' => rand(1000, 5000),
                    'date' => Carbon::now()->subDays(rand(1, 30)),
                    'notes' => 'Advance payment for ' . $artisan->name
                ]);
            }
        }
    }
}
