<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Artisan;
use App\Models\OrderAssignment;

class ArtisanSeeder extends Seeder
{
    public function run()
    {
        // Create artisans with factory
        $artisans = Artisan::factory()->count(10)->create();

        // Update status based on assignments
        foreach ($artisans as $artisan) {
            // Check if artisan has any assignments
            $hasAssignments = OrderAssignment::where('artisan_id', $artisan->id)->exists();

            // Update status
            $artisan->status = $hasAssignments ? 'active' : 'inactive';
            $artisan->save();
        }
    }
}
