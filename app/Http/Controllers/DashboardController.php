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
    public function index(Request $request)
    {
        // Get basic stats
        $stats = [
            'totalOrders' => Order::count(),
            'activeOrders' => Order::whereIn('status', ['pending', 'in_production'])->count(),
            'totalArtisans' => Artisan::count(),
            'activeArtisans' => Artisan::where('status', 'active')->count(),
            'totalProducts' => Product::count(),
            'inStockProducts' => Product::where('quantity', '>', 0)->count(),
            'totalRevenue' => number_format(Order::where('status', 'dispatched')
                ->selectRaw('SUM(total_quantity * wages_per_unit) as total')
                ->value('total') ?? 0, 2),
            'monthlyRevenue' => number_format(Order::where('status', 'dispatched')
                ->whereMonth('created_at', Carbon::now()->month)
                ->selectRaw('SUM(total_quantity * wages_per_unit) as total')
                ->value('total') ?? 0, 2)
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
        $inventoryStatus = Product::select('id', 'name', 'quantity', 'updated_at')
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'quantity' => $product->quantity,
                    'stock_level' => $this->getStockLevel($product->quantity),
                    'updated_at' => $product->updated_at
                ];
            });

        // Get recent activities
        $recentActivities = $this->getRecentActivities();

        // Get monthly revenue data
        $year = $request->input('year', Carbon::now()->year);
        $monthlyData = [];
        $currentDate = Carbon::createFromFormat('Y', $year)->startOfYear();

        for ($i = 0; $i < 12; $i++) {
            $month = $currentDate->copy()->addMonths($i);
            $revenue = Order::where('status', 'dispatched')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->selectRaw('SUM(total_quantity * wages_per_unit) as total')
                ->value('total') ?? 0;

            $monthlyData[] = [
                'month' => $month->format('M Y'),
                'revenue' => $revenue
            ];
        }

        // Get order status distribution
        $statusDistribution = Order::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->map(function ($item) {
                return [
                    'status' => ucfirst(str_replace('_', ' ', $item->status)),
                    'count' => $item->count
                ];
            });

        return response()->json([
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'topArtisans' => $topArtisans,
            'inventoryStatus' => $inventoryStatus,
            'recentActivities' => $recentActivities,
            'monthly_data' => $monthlyData,
            'status_distribution' => $statusDistribution
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
