<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderAssignment;
use App\Models\Artisan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    public function run()
    {
        // Ensure we have some artisans
        $artisans = Artisan::all();
        if ($artisans->isEmpty()) {
            Artisan::factory()->count(20)->create();
            $artisans = Artisan::all();
        }

        // Get admin user for approvals/dispatch
        $admin = User::firstOrCreate(
            ['email' => 'sadmin@sadmin.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password')
            ]
        );

        // Product configurations
        $products = [
            [
                'name' => 'Cat Cave - Small',
                'sizes' => ['XS', 'S', 'M'],
                'weights' => [0.5, 0.7, 1.0],
                'wages_range' => [8.0, 12.0]
            ],
            [
                'name' => 'Cat Cave - Medium',
                'sizes' => ['S', 'M', 'L'],
                'weights' => [1.0, 1.3, 1.6],
                'wages_range' => [12.0, 18.0]
            ],
            [
                'name' => 'Cat Cave - Large',
                'sizes' => ['M', 'L', 'XL'],
                'weights' => [1.5, 1.8, 2.2],
                'wages_range' => [15.0, 25.0]
            ],
            [
                'name' => 'Wool Blanket',
                'sizes' => ['S', 'M', 'L'],
                'weights' => [1.2, 1.5, 1.8],
                'wages_range' => [20.0, 35.0]
            ],
            [
                'name' => 'Pet Bed',
                'sizes' => ['S', 'M', 'L'],
                'weights' => [1.0, 1.5, 2.0],
                'wages_range' => [25.0, 40.0]
            ]
        ];

        $colors = ['Natural', 'Black', 'White', 'Gray', 'Brown', 'Red', 'Blue', 'Green', 'Multi-color'];

        // Create 50-100 sample orders
        $orderCount = rand(50, 100);

        for ($i = 0; $i < $orderCount; $i++) {
            $productConfig = $products[array_rand($products)];
            $size = $productConfig['sizes'][array_rand($productConfig['sizes'])];
            $weight = $productConfig['weights'][array_rand($productConfig['weights'])];
            $wages = rand($productConfig['wages_range'][0] * 10, $productConfig['wages_range'][1] * 10) / 10;

            $orderDate = Carbon::now()->subDays(rand(0, 60));
            $dueDate = $orderDate->copy()->addDays(rand(7, 90));

            $order = Order::create([
                'order_id' => 'ORD-' . Str::upper(Str::random(8)),
                'product_name' => $productConfig['name'],
                'size' => $size,
                'wool_color' => $colors[array_rand($colors)],
                'weight' => $weight,
                'total_quantity' => rand(10, 200),
                'due_date' => $dueDate,
                'wages_per_unit' => $wages,
                'status' => 'pending', // Default status, will update later
                'client_details' => [
                    'name' => fake()->name(),
                    'email' => fake()->email(),
                    'phone' => fake()->phoneNumber(),
                    'address' => fake()->address(),
                    'company' => rand(0, 1) ? fake()->company() : null
                ],
                'notes' => rand(0, 3) ? fake()->sentence() : null,
                'created_at' => $orderDate,
                'updated_at' => $orderDate,
            ]);

            // Assign to artisans (1-5 artisans per order)
            $artisanCount = rand(1, min(5, $artisans->count()));
            $selectedArtisans = $artisans->random($artisanCount);

            $remainingQuantity = $order->total_quantity;
            $orderStatus = 'pending';
            $allAssignmentsCompleted = true;

            foreach ($selectedArtisans as $artisan) {
                $assignedQty = ($artisan->id === $selectedArtisans->last()->id)
                    ? $remainingQuantity
                    : rand(1, $remainingQuantity);

                $assignmentStatus = 'pending';
                $completedQty = 0;
                $approvedQty = 0;
                $rejectedQty = 0;
                $approvedAt = null;
                $dispatchedAt = null;
                $rejectionReason = null;

                // Randomly progress some assignments
                if (rand(0, 1)) {
                    $completedQty = rand(0, $assignedQty);
                    $assignmentStatus = $completedQty > 0 ? 'completed' : 'in_production';

                    if ($completedQty > 0 && rand(0, 1)) {
                        $approvedQty = rand(max(0, $completedQty - 5), $completedQty);
                        $rejectedQty = $completedQty - $approvedQty;
                        $assignmentStatus = 'approved';
                        $approvedAt = $orderDate->copy()->addDays(rand(1, 30));

                        if ($rejectedQty > 0) {
                            $rejectionReason = fake()->sentence();
                        }

                        if (rand(0, 1)) {
                            $assignmentStatus = 'dispatched';
                            $dispatchedAt = $approvedAt->copy()->addDays(rand(1, 7));
                        }
                    }
                }

                OrderAssignment::create([
                    'order_id' => $order->id,
                    'artisan_id' => $artisan->id,
                    'assigned_quantity' => $assignedQty,
                    'completed_quantity' => $completedQty,
                    'approved_quantity' => $approvedQty,
                    'rejected_quantity' => $rejectedQty,
                    'approved_at' => $approvedAt,
                    'approved_by' => $approvedAt ? $admin->id : null,
                    'dispatched_at' => $dispatchedAt,
                    'dispatched_by' => $dispatchedAt ? $admin->id : null,
                    'status' => $assignmentStatus,
                    'rejection_reason' => $rejectionReason,
                    'created_at' => $orderDate,
                    'updated_at' => $dispatchedAt ?? $approvedAt ?? $orderDate,
                ]);

                $remainingQuantity -= $assignedQty;

                if ($assignmentStatus !== 'completed' && $assignmentStatus !== 'dispatched') {
                    $allAssignmentsCompleted = false;
                }
            }

            // Update order status based on assignments
            $newOrderStatus = $order->status;

            if ($allAssignmentsCompleted) {
                $newOrderStatus = 'dispatched';
            } elseif ($order->assignments()->where('status', 'approved')->exists()) {
                $newOrderStatus = 'approved';
            } elseif ($order->assignments()->where('status', 'completed')->exists()) {
                $newOrderStatus = 'in_production';
            } elseif ($order->assignments()->sum('assigned_quantity') >= $order->total_quantity) {
                $newOrderStatus = 'in_production';
            }

            if ($newOrderStatus !== $order->status) {
                $order->update([
                    'status' => $newOrderStatus,
                    'updated_at' => $orderDate->copy()->addDays(rand(1, 30))
                ]);
            }
        }

        $orders = [
            [
                'order_id' => 'ORD-' . strtoupper(uniqid()),
                'product_name' => 'Wool Scarf',
                'size' => 'Medium',
                'wool_color' => 'Natural',
                'weight' => 0.5,
                'total_quantity' => 100,
                'due_date' => Carbon::now()->addDays(30),
                'status' => 'in_production',
                'wages_per_unit' => 50,
                'client_name' => 'Test Client 1',
                'notes' => 'Test order for productivity tracking',
            ],
            [
                'order_id' => 'ORD-' . strtoupper(uniqid()),
                'product_name' => 'Wool Blanket',
                'size' => 'Large',
                'wool_color' => 'Gray',
                'weight' => 1.5,
                'total_quantity' => 50,
                'due_date' => Carbon::now()->addDays(45),
                'status' => 'pending',
                'wages_per_unit' => 100,
                'client_name' => 'Test Client 2',
                'notes' => 'Test order for productivity tracking',
            ],
        ];

        foreach ($orders as $order) {
            Order::create($order);
        }
    }
}
