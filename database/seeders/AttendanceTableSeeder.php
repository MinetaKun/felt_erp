<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\Artisan;
use App\Models\Department;
use Carbon\Carbon;

class AttendanceTableSeeder extends Seeder
{
    public function run()
    {
        // Check if we have a department, if not create one
        $department = Department::first();
        if (!$department) {
            $department = Department::create([
                'name' => 'Default Department',
                'description' => 'Default department for testing'
            ]);
        }

        // Ensure we have artisans
        $artisan = Artisan::first();
        if (!$artisan) {
            $artisan = Artisan::create([
                'name' => 'Test Artisan',
                'email' => 'artisan@example.com',
                'phone_number' => '1234567890',
                'department_id' => $department->id
            ]);
        }

        // Generate attendance for the last 30 days
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now();

        for ($date = $startDate; $date <= $endDate; $date->addDay()) {
            // Skip weekends
            if ($date->isWeekend()) {
                continue;
            }

            // Create attendance for artisan
            Attendance::create([
                'attendanceable_id' => $artisan->id,
                'attendanceable_type' => Artisan::class,
                'date' => $date->format('Y-m-d'),
                'status' => $this->getRandomStatus(),
                'remarks' => $this->getRandomRemarks()
            ]);
        }
    }

    private function getRandomStatus()
    {
        $statuses = ['present', 'absent', 'late'];
        return $statuses[array_rand($statuses)];
    }

    private function getRandomRemarks()
    {
        $remarks = [
            'On time',
            'Late due to traffic',
            'Sick leave',
            'Personal leave',
            'Work from home',
            null
        ];
        return $remarks[array_rand($remarks)];
    }
}
