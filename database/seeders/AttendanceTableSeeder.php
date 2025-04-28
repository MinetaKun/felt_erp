<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $attendanceRecords = [
            // Artisan 1 - Present with normal hours
            [
                'attendanceable_type' => 'App\\Models\\Artisan',
                'attendanceable_id' => 1,
                'date' => Carbon::now()->subDays(5),
                'check_in' => '09:00:00',
                'check_out' => '17:00:00',
                'status' => 'present',
                'remarks' => 'Regular working hours',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'attendanceable_type' => 'App\\Models\\Artisan',
                'attendanceable_id' => 1,
                'date' => Carbon::now()->subDays(4),
                'check_in' => '08:55:00',
                'check_out' => '17:05:00',
                'status' => 'present',
                'remarks' => 'Regular working hours',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Artisan 2 - Late arrival
            [
                'attendanceable_type' => 'App\\Models\\Artisan',
                'attendanceable_id' => 2,
                'date' => Carbon::now()->subDays(5),
                'check_in' => '09:45:00',
                'check_out' => '17:00:00',
                'status' => 'late',
                'remarks' => 'Late arrival due to traffic',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'attendanceable_type' => 'App\\Models\\Artisan',
                'attendanceable_id' => 2,
                'date' => Carbon::now()->subDays(4),
                'check_in' => null,
                'check_out' => null,
                'status' => 'absent',
                'remarks' => 'No show',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Artisan 3 - Half day (marked as late)
            [
                'attendanceable_type' => 'App\\Models\\Artisan',
                'attendanceable_id' => 3,
                'date' => Carbon::now()->subDays(5),
                'check_in' => '09:00:00',
                'check_out' => '13:00:00',
                'status' => 'late',
                'remarks' => 'Left early for personal appointment',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'attendanceable_type' => 'App\\Models\\Artisan',
                'attendanceable_id' => 3,
                'date' => Carbon::now()->subDays(4),
                'check_in' => '09:00:00',
                'check_out' => '17:00:00',
                'status' => 'present',
                'remarks' => 'Regular working hours',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Artisan 4 - Leave (marked as absent)
            [
                'attendanceable_type' => 'App\\Models\\Artisan',
                'attendanceable_id' => 4,
                'date' => Carbon::now()->subDays(5),
                'check_in' => null,
                'check_out' => null,
                'status' => 'absent',
                'remarks' => 'Annual leave',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'attendanceable_type' => 'App\\Models\\Artisan',
                'attendanceable_id' => 4,
                'date' => Carbon::now()->subDays(4),
                'check_in' => '09:00:00',
                'check_out' => '17:00:00',
                'status' => 'present',
                'remarks' => 'Regular working hours',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Artisan 5 - Overtime
            [
                'attendanceable_type' => 'App\\Models\\Artisan',
                'attendanceable_id' => 5,
                'date' => Carbon::now()->subDays(5),
                'check_in' => '09:00:00',
                'check_out' => '19:00:00',
                'status' => 'present',
                'remarks' => 'Overtime work - 2 hours',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'attendanceable_type' => 'App\\Models\\Artisan',
                'attendanceable_id' => 5,
                'date' => Carbon::now()->subDays(4),
                'check_in' => '09:00:00',
                'check_out' => '17:00:00',
                'status' => 'present',
                'remarks' => 'Regular working hours',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('attendance')->insert($attendanceRecords);
    }
}
