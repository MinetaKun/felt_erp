<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\Artisan;

class AttendanceTableSeeder extends Seeder
{
    public function run()
    {
        $artisans = Artisan::all();

        if ($artisans->count() < 3) {
            throw new \Exception('Not enough artisans to seed attendance table. Please seed artisans first.');
        }

        Attendance::create([
            'artisan_id' => $artisans[0]->id, // John Doe
            'date' => '2025-03-23',
            'check_in' => '08:00:00',
            'check_out' => '17:00:00',
            'status' => 'present',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Attendance::create([
            'artisan_id' => $artisans[1]->id, // Jane Smith
            'date' => '2025-03-23',
            'check_in' => '09:30:00',
            'check_out' => '17:00:00',
            'status' => 'late',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Attendance::create([
            'artisan_id' => $artisans[2]->id, // Mike Johnson
            'date' => '2025-03-23',
            'check_in' => null,
            'check_out' => null,
            'status' => 'absent',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
