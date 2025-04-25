<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Artisan;
use App\Models\Product;
use App\Models\OrderAssignment;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Get basic stats
        $stats = [
            'totalOrders' => Order::count(),
            'activeOrders' => Order::whereIn('status', ['pending', 'in_production'])->count(),
            'totalArtisans' => Artisan::count(),
            'activeArtisans' => Artisan::where('status', 'active')->count(),
            'totalProducts' => Product::count(),
            'inStockProducts' => Product::where('stock_quantity', '>', 0)->count(),
            'totalRevenue' => Order::where('status', 'dispatched')->sum('total_amount'),
            'monthlyRevenue' => Order::where('status', 'dispatched')
                ->whereMonth('created_at', Carbon::now()->month)
                ->sum('total_amount')
        ];

        // Get recent orders
        $recentOrders = Order::with('assignments')
            ->latest()
            ->take(5)
            ->get();

        // Get top performing artisans
        $topArtisans = Artisan::with(['department', 'orderAssignments'])
            ->get()
            ->map(function ($artisan) {
                $assignments = $artisan->orderAssignments;
                $totalAssignments = $assignments->count();
                $completedAssignments = $assignments->whereIn('status', ['completed', 'dispatched'])->count();
                $totalApprovedQuantity = $assignments->whereIn('status', ['approved', 'dispatched'])->sum('approved_quantity');

                return [
                    'id' => $artisan->id,
                    'name' => $artisan->name,
                    'profile_photo' => $artisan->profile_photo,
                    'department' => $artisan->department,
                    'orders' => [
                        'completion_rate' => $totalAssignments > 0 ? round(($completedAssignments / $totalAssignments) * 100, 2) : 0
                    ],
                    'products' => [
                        'total_approved' => $totalApprovedQuantity
                    ]
                ];
            })
            ->sortByDesc(function ($artisan) {
                return $artisan['orders']['completion_rate'];
            })
            ->take(5)
            ->values();

        // Get inventory status
        $inventoryStatus = Product::select('id', 'name', 'category', 'stock_quantity', 'updated_at')
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category' => $product->category,
                    'quantity' => $product->stock_quantity,
                    'stock_level' => $this->getStockLevel($product->stock_quantity),
                    'updated_at' => $product->updated_at
                ];
            });

        // Get recent activities
        $recentActivities = $this->getRecentActivities();

        return response()->json([
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'topArtisans' => $topArtisans,
            'inventoryStatus' => $inventoryStatus,
            'recentActivities' => $recentActivities
        ]);
    }

    private function getStockLevel($quantity)
    {
        if ($quantity <= 10) return 'low';
        if ($quantity <= 50) return 'medium';
        return 'high';
    }

    private function getRecentActivities()
    {
        $activities = [];

        // Get recent orders
        $recentOrders = Order::latest()->take(3)->get();
        foreach ($recentOrders as $order) {
            $activities[] = [
                'id' => 'order_' . $order->id,
                'type' => 'order',
                'description' => "New order #{$order->order_id} created for {$order->product_name}",
                'created_at' => $order->created_at
            ];
        }

        // Get recent artisan activities
        $recentAssignments = OrderAssignment::with('artisan')
            ->latest()
            ->take(3)
            ->get();
        foreach ($recentAssignments as $assignment) {
            $activities[] = [
                'id' => 'assignment_' . $assignment->id,
                'type' => 'artisan',
                'description' => "{$assignment->artisan->name} completed {$assignment->completed_quantity} units",
                'created_at' => $assignment->updated_at
            ];
        }

        // Get recent inventory updates
        $recentProducts = Product::where('updated_at', '>=', Carbon::now()->subDays(7))
            ->latest()
            ->take(3)
            ->get();
        foreach ($recentProducts as $product) {
            $activities[] = [
                'id' => 'product_' . $product->id,
                'type' => 'inventory',
                'description' => "Stock updated for {$product->name}",
                'created_at' => $product->updated_at
            ];
        }

        // Sort activities by date
        usort($activities, function ($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        return array_slice($activities, 0, 5);
    }
}
