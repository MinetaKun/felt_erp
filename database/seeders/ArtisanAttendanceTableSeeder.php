<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ArtisanAttendanceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $attendanceRecords = [
            // Artisan 1 - Present for last 5 days
            [
                'artisan_id' => 1,
                'status' => 'present',
                'date' => Carbon::now()->subDays(5),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'artisan_id' => 1,
                'status' => 'present',
                'date' => Carbon::now()->subDays(4),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'artisan_id' => 1,
                'status' => 'present',
                'date' => Carbon::now()->subDays(3),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'artisan_id' => 1,
                'status' => 'present',
                'date' => Carbon::now()->subDays(2),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'artisan_id' => 1,
                'status' => 'present',
                'date' => Carbon::now()->subDay(),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Artisan 2 - Present with one absence
            [
                'artisan_id' => 2,
                'status' => 'present',
                'date' => Carbon::now()->subDays(5),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'artisan_id' => 2,
                'status' => 'present',
                'date' => Carbon::now()->subDays(4),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'artisan_id' => 2,
                'status' => 'absent',
                'date' => Carbon::now()->subDays(3),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'artisan_id' => 2,
                'status' => 'present',
                'date' => Carbon::now()->subDays(2),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'artisan_id' => 2,
                'status' => 'present',
                'date' => Carbon::now()->subDay(),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Artisan 3 - Present with one late
            [
                'artisan_id' => 3,
                'status' => 'present',
                'date' => Carbon::now()->subDays(5),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'artisan_id' => 3,
                'status' => 'present',
                'date' => Carbon::now()->subDays(4),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'artisan_id' => 3,
                'status' => 'late',
                'date' => Carbon::now()->subDays(3),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'artisan_id' => 3,
                'status' => 'present',
                'date' => Carbon::now()->subDays(2),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'artisan_id' => 3,
                'status' => 'present',
                'date' => Carbon::now()->subDay(),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Artisan 4 - Present with one half-day (marked as late)
            [
                'artisan_id' => 4,
                'status' => 'present',
                'date' => Carbon::now()->subDays(5),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'artisan_id' => 4,
                'status' => 'present',
                'date' => Carbon::now()->subDays(4),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'artisan_id' => 4,
                'status' => 'late',
                'date' => Carbon::now()->subDays(3),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'artisan_id' => 4,
                'status' => 'present',
                'date' => Carbon::now()->subDays(2),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'artisan_id' => 4,
                'status' => 'present',
                'date' => Carbon::now()->subDay(),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Artisan 5 - Present with one leave (marked as absent)
            [
                'artisan_id' => 5,
                'status' => 'present',
                'date' => Carbon::now()->subDays(5),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'artisan_id' => 5,
                'status' => 'present',
                'date' => Carbon::now()->subDays(4),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'artisan_id' => 5,
                'status' => 'absent',
                'date' => Carbon::now()->subDays(3),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'artisan_id' => 5,
                'status' => 'present',
                'date' => Carbon::now()->subDays(2),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'artisan_id' => 5,
                'status' => 'present',
                'date' => Carbon::now()->subDay(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('artisan_attendances')->insert($attendanceRecords);
    }
}
