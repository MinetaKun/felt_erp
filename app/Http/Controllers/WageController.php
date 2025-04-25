<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderAssignment;
use App\Models\Artisan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WageController extends Controller
{
    public function calculations(Request $request)
    {
        try {
            $query = OrderAssignment::query()
                ->select([
                    'order_assignments.id',
                    'artisans.name as artisan_name',
                    'orders.order_id as order_number',
                    'orders.product_name',
                    'order_assignments.approved_quantity as quantity',
                    'orders.wages_per_unit',
                    DB::raw('CAST(order_assignments.approved_quantity AS DECIMAL(10,2)) * CAST(orders.wages_per_unit AS DECIMAL(10,2)) as total_wages'),
                    'order_assignments.approved_at as approval_date',
                    'order_assignments.dispatched_at as dispatch_date',
                    'order_assignments.dispatch_method',
                    'order_assignments.dispatch_notes'
                ])
                ->join('orders', 'order_assignments.order_id', '=', 'orders.id')
                ->join('artisans', 'order_assignments.artisan_id', '=', 'artisans.id')
                ->where('order_assignments.status', 'dispatched')
                ->where('order_assignments.approved_quantity', '>', 0);

            // Apply search filter
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('artisans.name', 'like', '%' . $search . '%')
                        ->orWhere('orders.order_id', 'like', '%' . $search . '%');
                });
            }

            // Apply date range filter
            if ($request->filled('date_range')) {
                $now = Carbon::now();
                switch ($request->date_range) {
                    case 'today':
                        $query->whereDate('order_assignments.approved_at', $now->toDateString());
                        break;
                    case 'week':
                        $query->whereBetween('order_assignments.approved_at', [
                            $now->startOfWeek()->toDateString(),
                            $now->endOfWeek()->toDateString()
                        ]);
                        break;
                    case 'month':
                        $query->whereBetween('order_assignments.approved_at', [
                            $now->startOfMonth()->toDateString(),
                            $now->endOfMonth()->toDateString()
                        ]);
                        break;
                    case 'year':
                        $query->whereBetween('order_assignments.approved_at', [
                            $now->startOfYear()->toDateString(),
                            $now->endOfYear()->toDateString()
                        ]);
                        break;
                }
            }

            // Apply sorting
            if ($request->filled('sort')) {
                $sortParts = explode(':', $request->sort);
                if (count($sortParts) === 2) {
                    $column = $sortParts[0];
                    $direction = $sortParts[1];

                    // Map frontend sort fields to database columns
                    $sortMap = [
                        'approval_date' => 'order_assignments.approved_at',
                        'artisan_name' => 'artisans.name',
                        'total_wages' => 'total_wages'
                    ];

                    if (isset($sortMap[$column])) {
                        $query->orderBy($sortMap[$column], $direction);
                    }
                }
            } else {
                $query->orderBy('order_assignments.approved_at', 'desc');
            }

            // Log the SQL query for debugging
            Log::info('Wage Calculation Query:', ['sql' => $query->toSql(), 'bindings' => $query->getBindings()]);

            $wages = $query->get();

            // Ensure numeric values
            $wages = $wages->map(function ($wage) {
                $wage->quantity = (float)$wage->quantity;
                $wage->wages_per_unit = (float)$wage->wages_per_unit;
                $wage->total_wages = (float)$wage->total_wages;
                return $wage;
            });

            // Log the results for debugging
            Log::info('Wage Calculation Results:', ['count' => $wages->count(), 'data' => $wages->toArray()]);

            return response()->json([
                'success' => true,
                'data' => $wages
            ]);
        } catch (\Exception $e) {
            Log::error('Wage Calculation Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error calculating wages',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function export(Request $request)
    {
        try {
            $query = OrderAssignment::query()
                ->select([
                    'artisans.name as artisan_name',
                    'orders.order_id as order_number',
                    'orders.product_name',
                    'order_assignments.approved_quantity as quantity',
                    'orders.wages_per_unit',
                    DB::raw('CAST(order_assignments.approved_quantity AS DECIMAL(10,2)) * CAST(orders.wages_per_unit AS DECIMAL(10,2)) as total_wages'),
                    'order_assignments.approved_at as approval_date',
                    'order_assignments.dispatched_at as dispatch_date',
                    'order_assignments.dispatch_method',
                    'order_assignments.dispatch_notes'
                ])
                ->join('orders', 'order_assignments.order_id', '=', 'orders.id')
                ->join('artisans', 'order_assignments.artisan_id', '=', 'artisans.id')
                ->where('order_assignments.status', 'dispatched')
                ->where('order_assignments.approved_quantity', '>', 0);

            // Apply search filter
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('artisans.name', 'like', '%' . $search . '%')
                        ->orWhere('orders.order_id', 'like', '%' . $search . '%');
                });
            }

            // Apply date range filter
            if ($request->filled('date_range')) {
                $now = Carbon::now();
                switch ($request->date_range) {
                    case 'today':
                        $query->whereDate('order_assignments.approved_at', $now->toDateString());
                        break;
                    case 'week':
                        $query->whereBetween('order_assignments.approved_at', [
                            $now->startOfWeek()->toDateString(),
                            $now->endOfWeek()->toDateString()
                        ]);
                        break;
                    case 'month':
                        $query->whereBetween('order_assignments.approved_at', [
                            $now->startOfMonth()->toDateString(),
                            $now->endOfMonth()->toDateString()
                        ]);
                        break;
                    case 'year':
                        $query->whereBetween('order_assignments.approved_at', [
                            $now->startOfYear()->toDateString(),
                            $now->endOfYear()->toDateString()
                        ]);
                        break;
                }
            }

            // Apply sorting
            if ($request->filled('sort')) {
                $sortParts = explode(':', $request->sort);
                if (count($sortParts) === 2) {
                    $column = $sortParts[0];
                    $direction = $sortParts[1];

                    // Map frontend sort fields to database columns
                    $sortMap = [
                        'approval_date' => 'order_assignments.approved_at',
                        'artisan_name' => 'artisans.name',
                        'total_wages' => 'total_wages'
                    ];

                    if (isset($sortMap[$column])) {
                        $query->orderBy($sortMap[$column], $direction);
                    }
                }
            } else {
                $query->orderBy('order_assignments.approved_at', 'desc');
            }

            $wages = $query->get();

            // Ensure numeric values
            $wages = $wages->map(function ($wage) {
                $wage->quantity = (float)$wage->quantity;
                $wage->wages_per_unit = (float)$wage->wages_per_unit;
                $wage->total_wages = (float)$wage->total_wages;
                return $wage;
            });

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="wages-export-' . Carbon::now()->format('Y-m-d') . '.csv"',
            ];

            $callback = function () use ($wages) {
                $file = fopen('php://output', 'w');

                // Add headers
                fputcsv($file, [
                    'Artisan',
                    'Order Number',
                    'Product',
                    'Quantity',
                    'Wage per Unit',
                    'Total Wages',
                    'Approval Date',
                    'Dispatch Date',
                    'Dispatch Method',
                    'Dispatch Notes'
                ]);

                // Add data
                foreach ($wages as $wage) {
                    fputcsv($file, [
                        $wage->artisan_name,
                        $wage->order_number,
                        $wage->product_name,
                        $wage->quantity,
                        $wage->wages_per_unit,
                        $wage->total_wages,
                        $wage->approval_date,
                        $wage->dispatch_date,
                        $wage->dispatch_method,
                        $wage->dispatch_notes
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            Log::error('Wage Export Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error exporting wages',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function report(Request $request)
    {
        try {
            $query = OrderAssignment::query()
                ->select([
                    DB::raw('DATE(order_assignments.approved_at) as period'),
                    DB::raw('COUNT(DISTINCT order_assignments.artisan_id) as total_artisans'),
                    DB::raw('COUNT(DISTINCT order_assignments.order_id) as total_orders'),
                    DB::raw('SUM(CAST(order_assignments.approved_quantity AS DECIMAL(10,2)) * CAST(orders.wages_per_unit AS DECIMAL(10,2))) as total_wages'),
                    DB::raw('AVG(CAST(order_assignments.approved_quantity AS DECIMAL(10,2)) * CAST(orders.wages_per_unit AS DECIMAL(10,2))) as average_wage')
                ])
                ->join('orders', 'order_assignments.order_id', '=', 'orders.id')
                ->join('artisans', 'order_assignments.artisan_id', '=', 'artisans.id')
                ->where('order_assignments.status', 'dispatched')
                ->where('order_assignments.approved_quantity', '>', 0);

            // Apply date range filter
            if ($request->filled('date_range')) {
                $now = Carbon::now();
                switch ($request->date_range) {
                    case 'today':
                        $query->whereDate('order_assignments.approved_at', $now->toDateString());
                        break;
                    case 'week':
                        $query->whereBetween('order_assignments.approved_at', [
                            $now->startOfWeek()->toDateString(),
                            $now->endOfWeek()->toDateString()
                        ]);
                        break;
                    case 'month':
                        $query->whereBetween('order_assignments.approved_at', [
                            $now->startOfMonth()->toDateString(),
                            $now->endOfMonth()->toDateString()
                        ]);
                        break;
                    case 'year':
                        $query->whereBetween('order_assignments.approved_at', [
                            $now->startOfYear()->toDateString(),
                            $now->endOfYear()->toDateString()
                        ]);
                        break;
                }
            }

            // Group by period based on the group_by parameter
            if ($request->filled('group_by')) {
                switch ($request->group_by) {
                    case 'weekly':
                        $query->select([
                            DB::raw('YEARWEEK(order_assignments.approved_at) as period'),
                            DB::raw('COUNT(DISTINCT order_assignments.artisan_id) as total_artisans'),
                            DB::raw('COUNT(DISTINCT order_assignments.order_id) as total_orders'),
                            DB::raw('SUM(CAST(order_assignments.approved_quantity AS DECIMAL(10,2)) * CAST(orders.wages_per_unit AS DECIMAL(10,2))) as total_wages'),
                            DB::raw('AVG(CAST(order_assignments.approved_quantity AS DECIMAL(10,2)) * CAST(orders.wages_per_unit AS DECIMAL(10,2))) as average_wage')
                        ])
                            ->groupBy(DB::raw('YEARWEEK(order_assignments.approved_at)'));
                        break;
                    case 'monthly':
                        $query->select([
                            DB::raw('DATE_FORMAT(order_assignments.approved_at, "%Y-%m") as period'),
                            DB::raw('COUNT(DISTINCT order_assignments.artisan_id) as total_artisans'),
                            DB::raw('COUNT(DISTINCT order_assignments.order_id) as total_orders'),
                            DB::raw('SUM(CAST(order_assignments.approved_quantity AS DECIMAL(10,2)) * CAST(orders.wages_per_unit AS DECIMAL(10,2))) as total_wages'),
                            DB::raw('AVG(CAST(order_assignments.approved_quantity AS DECIMAL(10,2)) * CAST(orders.wages_per_unit AS DECIMAL(10,2))) as average_wage')
                        ])
                            ->groupBy(DB::raw('DATE_FORMAT(order_assignments.approved_at, "%Y-%m")'));
                        break;
                    case 'yearly':
                        $query->select([
                            DB::raw('YEAR(order_assignments.approved_at) as period'),
                            DB::raw('COUNT(DISTINCT order_assignments.artisan_id) as total_artisans'),
                            DB::raw('COUNT(DISTINCT order_assignments.order_id) as total_orders'),
                            DB::raw('SUM(CAST(order_assignments.approved_quantity AS DECIMAL(10,2)) * CAST(orders.wages_per_unit AS DECIMAL(10,2))) as total_wages'),
                            DB::raw('AVG(CAST(order_assignments.approved_quantity AS DECIMAL(10,2)) * CAST(orders.wages_per_unit AS DECIMAL(10,2))) as average_wage')
                        ])
                            ->groupBy(DB::raw('YEAR(order_assignments.approved_at)'));
                        break;
                    default: // daily
                        $query->groupBy(DB::raw('DATE(order_assignments.approved_at)'));
                }
            } else {
                $query->groupBy(DB::raw('DATE(order_assignments.approved_at)'));
            }

            // Apply sorting
            $query->orderBy('period', 'desc');

            $report = $query->get();

            // Ensure numeric values
            $report = $report->map(function ($item) {
                $item->total_wages = (float)$item->total_wages;
                $item->average_wage = (float)$item->average_wage;
                $item->total_artisans = (int)$item->total_artisans;
                $item->total_orders = (int)$item->total_orders;
                return $item;
            });

            $response = [
                'success' => true,
                'data' => [
                    'report' => $report
                ]
            ];

            // If include_artisans is true, fetch artisan-specific data
            if ($request->boolean('include_artisans')) {
                $artisanQuery = OrderAssignment::query()
                    ->select([
                        'artisans.id as artisan_id',
                        'artisans.name as artisan_name',
                        DB::raw('DATE_FORMAT(order_assignments.approved_at, "%Y-%m") as month'),
                        DB::raw('COUNT(DISTINCT order_assignments.order_id) as total_orders'),
                        DB::raw('SUM(order_assignments.approved_quantity) as total_quantity'),
                        DB::raw('SUM(CAST(order_assignments.approved_quantity AS DECIMAL(10,2)) * CAST(orders.wages_per_unit AS DECIMAL(10,2))) as total_wages')
                    ])
                    ->join('orders', 'order_assignments.order_id', '=', 'orders.id')
                    ->join('artisans', 'order_assignments.artisan_id', '=', 'artisans.id')
                    ->where('order_assignments.status', 'dispatched')
                    ->where('order_assignments.approved_quantity', '>', 0);

                // Apply the same date range filter
                if ($request->filled('date_range')) {
                    $now = Carbon::now();
                    switch ($request->date_range) {
                        case 'today':
                            $artisanQuery->whereDate('order_assignments.approved_at', $now->toDateString());
                            break;
                        case 'week':
                            $artisanQuery->whereBetween('order_assignments.approved_at', [
                                $now->startOfWeek()->toDateString(),
                                $now->endOfWeek()->toDateString()
                            ]);
                            break;
                        case 'month':
                            $artisanQuery->whereBetween('order_assignments.approved_at', [
                                $now->startOfMonth()->toDateString(),
                                $now->endOfMonth()->toDateString()
                            ]);
                            break;
                        case 'year':
                            $artisanQuery->whereBetween('order_assignments.approved_at', [
                                $now->startOfYear()->toDateString(),
                                $now->endOfYear()->toDateString()
                            ]);
                            break;
                    }
                }

                $artisanQuery->groupBy('artisans.id', 'artisans.name', DB::raw('DATE_FORMAT(order_assignments.approved_at, "%Y-%m")'))
                    ->orderBy('artisans.name')
                    ->orderBy('month', 'desc');

                $artisans = $artisanQuery->get();

                // Ensure numeric values
                $artisans = $artisans->map(function ($item) {
                    $item->total_wages = (float)$item->total_wages;
                    $item->total_quantity = (float)$item->total_quantity;
                    $item->total_orders = (int)$item->total_orders;
                    return $item;
                });

                $response['data']['artisans'] = $artisans;
            }

            return response()->json($response);
        } catch (\Exception $e) {
            Log::error('Wage Report Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error generating wage report',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function exportReport(Request $request)
    {
        try {
            $query = OrderAssignment::query()
                ->select([
                    DB::raw('DATE(order_assignments.approved_at) as period'),
                    DB::raw('COUNT(DISTINCT order_assignments.artisan_id) as total_artisans'),
                    DB::raw('COUNT(DISTINCT order_assignments.order_id) as total_orders'),
                    DB::raw('SUM(CAST(order_assignments.approved_quantity AS DECIMAL(10,2)) * CAST(orders.wages_per_unit AS DECIMAL(10,2))) as total_wages'),
                    DB::raw('AVG(CAST(order_assignments.approved_quantity AS DECIMAL(10,2)) * CAST(orders.wages_per_unit AS DECIMAL(10,2))) as average_wage')
                ])
                ->join('orders', 'order_assignments.order_id', '=', 'orders.id')
                ->join('artisans', 'order_assignments.artisan_id', '=', 'artisans.id')
                ->where('order_assignments.status', 'dispatched')
                ->where('order_assignments.approved_quantity', '>', 0);

            // Apply date range filter
            if ($request->filled('date_range')) {
                $now = Carbon::now();
                switch ($request->date_range) {
                    case 'today':
                        $query->whereDate('order_assignments.approved_at', $now->toDateString());
                        break;
                    case 'week':
                        $query->whereBetween('order_assignments.approved_at', [
                            $now->startOfWeek()->toDateString(),
                            $now->endOfWeek()->toDateString()
                        ]);
                        break;
                    case 'month':
                        $query->whereBetween('order_assignments.approved_at', [
                            $now->startOfMonth()->toDateString(),
                            $now->endOfMonth()->toDateString()
                        ]);
                        break;
                    case 'year':
                        $query->whereBetween('order_assignments.approved_at', [
                            $now->startOfYear()->toDateString(),
                            $now->endOfYear()->toDateString()
                        ]);
                        break;
                }
            }

            // Group by period based on the group_by parameter
            if ($request->filled('group_by')) {
                switch ($request->group_by) {
                    case 'weekly':
                        $query->select([
                            DB::raw('YEARWEEK(order_assignments.approved_at) as period'),
                            DB::raw('COUNT(DISTINCT order_assignments.artisan_id) as total_artisans'),
                            DB::raw('COUNT(DISTINCT order_assignments.order_id) as total_orders'),
                            DB::raw('SUM(CAST(order_assignments.approved_quantity AS DECIMAL(10,2)) * CAST(orders.wages_per_unit AS DECIMAL(10,2))) as total_wages'),
                            DB::raw('AVG(CAST(order_assignments.approved_quantity AS DECIMAL(10,2)) * CAST(orders.wages_per_unit AS DECIMAL(10,2))) as average_wage')
                        ])
                            ->groupBy(DB::raw('YEARWEEK(order_assignments.approved_at)'));
                        break;
                    case 'monthly':
                        $query->select([
                            DB::raw('DATE_FORMAT(order_assignments.approved_at, "%Y-%m") as period'),
                            DB::raw('COUNT(DISTINCT order_assignments.artisan_id) as total_artisans'),
                            DB::raw('COUNT(DISTINCT order_assignments.order_id) as total_orders'),
                            DB::raw('SUM(CAST(order_assignments.approved_quantity AS DECIMAL(10,2)) * CAST(orders.wages_per_unit AS DECIMAL(10,2))) as total_wages'),
                            DB::raw('AVG(CAST(order_assignments.approved_quantity AS DECIMAL(10,2)) * CAST(orders.wages_per_unit AS DECIMAL(10,2))) as average_wage')
                        ])
                            ->groupBy(DB::raw('DATE_FORMAT(order_assignments.approved_at, "%Y-%m")'));
                        break;
                    case 'yearly':
                        $query->select([
                            DB::raw('YEAR(order_assignments.approved_at) as period'),
                            DB::raw('COUNT(DISTINCT order_assignments.artisan_id) as total_artisans'),
                            DB::raw('COUNT(DISTINCT order_assignments.order_id) as total_orders'),
                            DB::raw('SUM(CAST(order_assignments.approved_quantity AS DECIMAL(10,2)) * CAST(orders.wages_per_unit AS DECIMAL(10,2))) as total_wages'),
                            DB::raw('AVG(CAST(order_assignments.approved_quantity AS DECIMAL(10,2)) * CAST(orders.wages_per_unit AS DECIMAL(10,2))) as average_wage')
                        ])
                            ->groupBy(DB::raw('YEAR(order_assignments.approved_at)'));
                        break;
                    default: // daily
                        $query->groupBy(DB::raw('DATE(order_assignments.approved_at)'));
                }
            } else {
                $query->groupBy(DB::raw('DATE(order_assignments.approved_at)'));
            }

            // Apply sorting
            $query->orderBy('period', 'desc');

            $report = $query->get();

            // Ensure numeric values
            $report = $report->map(function ($item) {
                $item->total_wages = (float)$item->total_wages;
                $item->average_wage = (float)$item->average_wage;
                $item->total_artisans = (int)$item->total_artisans;
                $item->total_orders = (int)$item->total_orders;
                return $item;
            });

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="wage-report-' . Carbon::now()->format('Y-m-d') . '.csv"',
            ];

            $callback = function () use ($report) {
                $file = fopen('php://output', 'w');

                // Add headers
                fputcsv($file, [
                    'Period',
                    'Total Artisans',
                    'Total Orders',
                    'Total Wages',
                    'Average Wage'
                ]);

                // Add data
                foreach ($report as $item) {
                    fputcsv($file, [
                        $item->period,
                        $item->total_artisans,
                        $item->total_orders,
                        $item->total_wages,
                        $item->average_wage
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            Log::error('Wage Report Export Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error exporting wage report',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
