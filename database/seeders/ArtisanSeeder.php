<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Artisan;

class ArtisanSeeder extends Seeder
{
    public function run()
    {
        Artisan::factory()->count(10)->create();
    }
}
