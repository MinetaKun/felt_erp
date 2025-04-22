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

            // Apply filters
            if ($request->filled('artisan')) {
                $query->where('artisans.name', 'like', '%' . $request->artisan . '%');
            }

            if ($request->filled('order')) {
                $query->where('orders.order_id', 'like', '%' . $request->order . '%');
            }

            if ($request->filled('date_from')) {
                $query->whereDate('order_assignments.approved_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('order_assignments.approved_at', '<=', $request->date_to);
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

            // Apply filters
            if ($request->filled('artisan')) {
                $query->where('artisans.name', 'like', '%' . $request->artisan . '%');
            }

            if ($request->filled('order')) {
                $query->where('orders.order_id', 'like', '%' . $request->order . '%');
            }

            if ($request->filled('date_from')) {
                $query->whereDate('order_assignments.approved_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('order_assignments.approved_at', '<=', $request->date_to);
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
}
