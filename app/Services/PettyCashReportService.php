<?php

namespace App\Services;

use App\Models\PettyCashTransaction;
use App\Models\PettyCashCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PettyCashReportService
{
    /**
     * Generate a PDF report for transactions
     *
     * @param Request $request
     * @return \Barryvdh\DomPDF\PDF
     */
    public function generateTransactionReport(Request $request)
    {
        // Build query with filters
        $query = PettyCashTransaction::with('category');

        // Apply filters
        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('type') && $request->type) {
            if ($request->type === 'income') {
                $query->where('cash_in', '>', 0);
            } elseif ($request->type === 'expense') {
                $query->where('cash_out', '>', 0);
            }
        }

        if ($request->has('start_date') && $request->start_date) {
            $query->where('transaction_date', '>=', Carbon::parse($request->start_date)->startOfDay());
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->where('transaction_date', '<=', Carbon::parse($request->end_date)->endOfDay());
        }

        // Get transactions
        $transactions = $query->orderBy('transaction_date', 'desc')->get();

        // Calculate totals
        $totalIncome = $transactions->sum('cash_in');
        $totalExpense = $transactions->sum('cash_out');
        $balance = $totalIncome - $totalExpense;

        // Group by category
        $byCategory = $transactions->groupBy('category.name')
            ->map(function ($items) {
                return [
                    'count' => $items->count(),
                    'cash_in' => $items->sum('cash_in'),
                    'cash_out' => $items->sum('cash_out'),
                    'type' => $items->first()->cash_in > 0 ? 'income' : 'expense'
                ];
            });

        // Get report title based on date range
        $reportTitle = 'Petty Cash Transaction Report';
        if ($request->has('start_date') && $request->has('end_date')) {
            $startDate = Carbon::parse($request->start_date)->format('M d, Y');
            $endDate = Carbon::parse($request->end_date)->format('M d, Y');
            $reportTitle .= " ($startDate - $endDate)";
        } elseif ($request->has('start_date')) {
            $startDate = Carbon::parse($request->start_date)->format('M d, Y');
            $reportTitle .= " (From $startDate)";
        } elseif ($request->has('end_date')) {
            $endDate = Carbon::parse($request->end_date)->format('M d, Y');
            $reportTitle .= " (Until $endDate)";
        }

        // Generate PDF
        $pdf = PDF::loadView('petty-cash.reports.transactions', [
            'transactions' => $transactions,
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'balance' => $balance,
            'byCategory' => $byCategory,
            'reportTitle' => $reportTitle,
            'generatedAt' => Carbon::now()->format('M d, Y H:i:s'),
            'filters' => [
                'category' => $request->has('category_id') && $request->category_id ?
                    PettyCashCategory::find($request->category_id)->name : 'All',
                'type' => $request->has('type') && $request->type ?
                    ucfirst($request->type) : 'All',
                'start_date' => $request->has('start_date') && $request->start_date ?
                    Carbon::parse($request->start_date)->format('M d, Y') : 'Any',
                'end_date' => $request->has('end_date') && $request->end_date ?
                    Carbon::parse($request->end_date)->format('M d, Y') : 'Any',
            ]
        ]);

        // Set paper size and orientation
        $pdf->setPaper('a4', 'landscape');

        return $pdf;
    }
}
