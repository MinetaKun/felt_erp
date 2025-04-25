<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Artisan;
use App\Models\Order;
use App\Models\OrderAssignment;
use App\Models\Attendance;
use Carbon\Carbon;

class ArtisanProductivitySeeder extends Seeder
{
    public function run(): void
    {
        // Create departments if they don't exist
        $departments = [
            'Pottery',
            'Weaving',
            'Woodwork',
            'Textiles',
            'Handicrafts',
            'Jewelry'
        ];

        $departmentIds = [];
        foreach ($departments as $deptName) {
            $dept = Department::firstOrCreate(['name' => $deptName]);
            $departmentIds[$deptName] = $dept->id;
        }

        // Create artisans
        $artisans = [
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'phone_number' => '1234567890',
                'pan_number' => 'ABCDE1234F',
                'department_id' => $departmentIds['Pottery'],
                'status' => 'active',
                'skills' => json_encode(['pottery', 'painting'])
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'phone_number' => '2345678901',
                'pan_number' => 'FGHIJ5678K',
                'department_id' => $departmentIds['Weaving'],
                'status' => 'active',
                'skills' => json_encode(['weaving', 'dyeing'])
            ],
            [
                'name' => 'Bob Johnson',
                'email' => 'bob@example.com',
                'phone_number' => '3456789012',
                'pan_number' => 'LMNOP9012Q',
                'department_id' => $departmentIds['Woodwork'],
                'status' => 'active',
                'skills' => json_encode(['woodwork', 'carving'])
            ]
        ];

        foreach ($artisans as $artisanData) {
            $artisan = Artisan::firstOrCreate(
                ['email' => $artisanData['email']],
                $artisanData
            );

            // Create orders
            $order = Order::create([
                'order_id' => 'ORD-' . strtoupper(uniqid()),
                'product_name' => 'Test Product',
                'size' => 'Medium',
                'wool_color' => 'Natural',
                'weight' => 2.5,
                'total_quantity' => 20,
                'due_date' => Carbon::now()->addDays(30),
                'status' => 'in_production',
                'wages_per_unit' => 100,
                'client_name' => 'Test Client',
                'notes' => 'Test order for productivity tracking'
            ]);

            // Create order assignments
            OrderAssignment::create([
                'order_id' => $order->id,
                'artisan_id' => $artisan->id,
                'assigned_quantity' => 10,
                'completed_quantity' => 5,
                'approved_quantity' => 3,
                'rejected_quantity' => 2,
                'status' => 'in_production',
                'rejection_reason' => 'Quality issues'
            ]);

            // Create attendance records for the past few days
            for ($i = 0; $i < 5; $i++) {
                Attendance::create([
                    'attendanceable_id' => $artisan->id,
                    'attendanceable_type' => Artisan::class,
                    'date' => Carbon::now()->subDays($i),
                    'status' => 'present',
                    'check_in' => Carbon::now()->subDays($i)->setHour(9)->setMinute(0),
                    'check_out' => Carbon::now()->subDays($i)->setHour(17)->setMinute(0)
                ]);
            }
        }
    }
}
