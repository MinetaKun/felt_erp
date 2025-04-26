<?php

namespace App\Http\Controllers;

use App\Models\WoolUsage;
use App\Models\WoolSupplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class WoolUsageController extends Controller
{
    public function index(Request $request)
    {
        $query = WoolUsage::with('supplier');

        // Filter by date range
        if ($request->has('start_date') && !empty($request->start_date)) {
            $query->where('date', '>=', $request->start_date);
        }
        if ($request->has('end_date') && !empty($request->end_date)) {
            $query->where('date', '<=', $request->end_date);
        }

        // Filter by supplier
        if ($request->has('supplier_id') && !empty($request->supplier_id)) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $usages = $query->get();

        // Calculate monthly totals
        $monthlyTotals = $usages->groupBy(function ($usage) {
            return Carbon::parse($usage->date)->format('Y-m');
        })->map(function ($group) {
            return $group->sum('total_kg');
        });

        return response()->json([
            'success' => true,
            'data' => [
                'usages' => $usages,
                'monthly_totals' => $monthlyTotals
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'bill_number' => 'required|string|max:50',
            'department' => 'required|string|max:100',
            'color_number' => 'required|string|max:50',
            'roll_count' => 'required|integer|min:1',
            'total_kg' => 'required|numeric|min:0',
            'remarks' => 'nullable|string',
            'usage_purpose' => 'required|string|max:100',
            'supplier_id' => 'required|exists:wool_suppliers,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $usage = WoolUsage::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Wool usage record created successfully',
            'data' => $usage
        ], 201);
    }

    public function show($id)
    {
        $usage = WoolUsage::with('supplier')->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $usage
        ]);
    }

    public function update(Request $request, $id)
    {
        $usage = WoolUsage::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'bill_number' => 'required|string|max:50',
            'department' => 'required|string|max:100',
            'color_number' => 'required|string|max:50',
            'roll_count' => 'required|integer|min:1',
            'total_kg' => 'required|numeric|min:0',
            'remarks' => 'nullable|string',
            'usage_purpose' => 'required|string|max:100',
            'supplier_id' => 'required|exists:wool_suppliers,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $usage->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Wool usage record updated successfully',
            'data' => $usage
        ]);
    }

    public function destroy($id)
    {
        $usage = WoolUsage::findOrFail($id);
        $usage->delete();

        return response()->json([
            'success' => true,
            'message' => 'Wool usage record deleted successfully'
        ]);
    }

    public function export(Request $request)
    {
        $query = WoolUsage::with('supplier');

        // Apply filters
        if ($request->has('start_date') && !empty($request->start_date)) {
            $query->where('date', '>=', $request->start_date);
        }
        if ($request->has('end_date') && !empty($request->end_date)) {
            $query->where('date', '<=', $request->end_date);
        }
        if ($request->has('supplier_id') && !empty($request->supplier_id)) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $usages = $query->get();

        if ($usages->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No data available for export'
            ], 404);
        }

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="wool-usage-report.csv"',
        ];

        $callback = function () use ($usages) {
            $file = fopen('php://output', 'w');

            // Add headers
            fputcsv($file, [
                'S.N.',
                'Date',
                'Bill No',
                'Department',
                'Color No',
                'Roll',
                'Total KG',
                'Remarks',
                'Usage Purpose',
                'Supplier'
            ]);

            // Add data
            foreach ($usages as $index => $usage) {
                fputcsv($file, [
                    $index + 1,
                    $usage->date,
                    $usage->bill_number,
                    $usage->department,
                    $usage->color_number,
                    $usage->roll_count,
                    $usage->total_kg,
                    $usage->remarks,
                    $usage->usage_purpose,
                    $usage->supplier ? $usage->supplier->name : 'N/A'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function summary(Request $request)
    {
        $query = WoolUsage::with('supplier');

        // Apply filters
        if ($request->has('start_date') && !empty($request->start_date)) {
            $query->where('date', '>=', $request->start_date);
        }
        if ($request->has('end_date') && !empty($request->end_date)) {
            $query->where('date', '<=', $request->end_date);
        }

        $usages = $query->get();

        // Calculate monthly totals
        $monthlyTotals = $usages->groupBy(function ($usage) {
            return Carbon::parse($usage->date)->format('Y-m');
        })->map(function ($group) {
            return [
                'total_kg' => $group->sum('total_kg'),
                'usage_count' => $group->count()
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'monthly_summary' => $monthlyTotals
            ]
        ]);
    }

    public function exportSummary(Request $request)
    {
        $query = WoolUsage::with('supplier');

        // Apply filters
        if ($request->has('start_date') && !empty($request->start_date)) {
            $query->where('date', '>=', $request->start_date);
        }
        if ($request->has('end_date') && !empty($request->end_date)) {
            $query->where('date', '<=', $request->end_date);
        }

        $usages = $query->get();

        // Calculate monthly totals
        $monthlyTotals = $usages->groupBy(function ($usage) {
            return Carbon::parse($usage->date)->format('Y-m');
        })->map(function ($group) {
            return [
                'total_kg' => $group->sum('total_kg'),
                'usage_count' => $group->count()
            ];
        });

        if ($monthlyTotals->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No data available for export'
            ], 404);
        }

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="wool-usage-summary.csv"',
        ];

        $callback = function () use ($monthlyTotals) {
            $file = fopen('php://output', 'w');

            // Add headers
            fputcsv($file, [
                'Month',
                'Total Wool (KG)',
                'Usage Count',
                'Average per Usage'
            ]);

            // Add data
            foreach ($monthlyTotals as $month => $data) {
                $date = Carbon::createFromFormat('Y-m', $month);
                fputcsv($file, [
                    $date->format('F Y'),
                    $data['total_kg'],
                    $data['usage_count'],
                    $data['usage_count'] > 0 ? round($data['total_kg'] / $data['usage_count'], 2) : 0
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
