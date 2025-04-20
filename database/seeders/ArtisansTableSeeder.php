<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Artisan;
use App\Models\Department;

class ArtisansTableSeeder extends Seeder
{
    public function run()
    {
        $departments = Department::all();

        if ($departments->count() < 3) {
            throw new \Exception('Not enough departments to seed artisans table. Please seed departments first.');
        }

        Artisan::create([
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'phone_number' => '1234567890',
            'profile_photo' => null,
            'citizenship_photo' => null,
            'pan_number' => 'ABCDE1234F',
            'department_id' => $departments[0]->id, // Production
            'basic_salary' => 50000.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Artisan::create([
            'name' => 'Jane Smith',
            'email' => 'jane.smith@example.com',
            'phone_number' => '1234567891',
            'profile_photo' => null,
            'citizenship_photo' => null,
            'pan_number' => 'FGHIJ5678K',
            'department_id' => $departments[1]->id, // Quality Control
            'basic_salary' => 45000.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Artisan::create([
            'name' => 'Mike Johnson',
            'email' => 'mike.johnson@example.com',
            'phone_number' => '1234567892',
            'profile_photo' => null,
            'citizenship_photo' => null,
            'pan_number' => 'LMNOP9012Q',
            'department_id' => $departments[2]->id, // Logistics
            'basic_salary' => 40000.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
