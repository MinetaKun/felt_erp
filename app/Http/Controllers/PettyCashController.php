<?php

namespace App\Http\Controllers;

use App\Models\PettyCashCategory;
use App\Models\PettyCashTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use League\Csv\Reader;
use League\Csv\Writer;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class PettyCashController extends Controller
{
    public function index(Request $request)
    {
        $query = PettyCashTransaction::with(['category'])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('particulars', 'like', "%$search%")
                    ->orWhere('pan_bill_no', 'like', "%$search%")
                    ->orWhere('est_bill_no', 'like', "%$search%")
                    ->orWhere('reference_no', 'like', "%$search%")
                    ->orWhereHas('category', function ($q) use ($search) {
                        $q->where('name', 'like', "%$search%");
                    });
            });
        }

        if ($request->has('category_id') && $request->category_id != '') {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('date_range') && $request->date_range != '') {
            $dates = explode(' to ', $request->date_range);
            $startDate = $dates[0];
            $endDate = $dates[1] ?? $dates[0];
            $query->whereBetween('transaction_date', [$startDate, $endDate]);
        }

        $transactions = $query->paginate($request->per_page ?? 25);

        return response()->json([
            'transactions' => $transactions,
            'categories' => PettyCashCategory::all(),
        ]);
    }

    public function dashboard(Request $request)
    {
        $balance = PettyCashTransaction::sum(DB::raw('cash_in - cash_out'));

        // Category-wise totals - fixed version
        $categoryTotals = PettyCashCategory::with(['transactions' => function ($query) use ($request) {
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->whereBetween('transaction_date', [
                    $request->start_date,
                    $request->end_date,
                ]);
            }
        }])
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'total_in' => $category->transactions->sum('cash_in'),
                    'total_out' => $category->transactions->sum('cash_out'),
                ];
            });

        // Monthly totals for chart
        $year = $request->input('year', Carbon::now()->year);
        $monthlyData = [];
        $currentDate = Carbon::createFromFormat('Y', $year)->startOfYear();

        for ($i = 0; $i < 12; $i++) {
            $month = $currentDate->copy()->addMonths($i);
            $monthQuery = PettyCashTransaction::whereYear('transaction_date', $month->year)
                ->whereMonth('transaction_date', $month->month);

            if ($request->has('start_date') && $request->has('end_date')) {
                $monthQuery->whereBetween('transaction_date', [
                    $request->start_date,
                    $request->end_date,
                ]);
            }

            $income = $monthQuery->sum('cash_in') ?? 0;
            $expense = $monthQuery->sum('cash_out') ?? 0;

            $monthlyData[] = [
                'month' => $month->format('M Y'),
                'income' => $income,
                'expense' => $expense,
            ];
        }

        // Top 10 expenses
        $topExpenses = PettyCashTransaction::with(['category'])
            ->where('cash_out', '>', 0)
            ->when($request->has('start_date') && $request->has('end_date'), function ($query) use ($request) {
                $query->whereBetween('transaction_date', [
                    $request->start_date,
                    $request->end_date,
                ]);
            })
            ->orderBy('cash_out', 'desc')
            ->limit(10)
            ->get();

        // Recent transactions
        $recentTransactions = PettyCashTransaction::with(['category'])
            ->when($request->has('start_date') && $request->has('end_date'), function ($query) use ($request) {
                $query->whereBetween('transaction_date', [
                    $request->start_date,
                    $request->end_date,
                ]);
            })
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(25)
            ->get();

        return response()->json([
            'balance' => $balance,
            'category_totals' => $categoryTotals,
            'monthly_data' => $monthlyData,
            'top_expenses' => $topExpenses,
            'recent_transactions' => $recentTransactions,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'transaction_date' => 'required|date',
            'bs_date' => 'nullable|date',
            'pan_bill_no' => 'nullable|string|max:50',
            'est_bill_no' => 'nullable|string|max:50',
            'category_id' => 'required|exists:petty_cash_categories,id',
            'particulars' => 'required|string|max:255',
            'cash_in' => 'required_without:cash_out|numeric|min:0',
            'cash_out' => 'required_without:cash_in|numeric|min:0',
            'vat_percentage' => 'nullable|numeric|min:0|max:100',
            'is_vat_included' => 'boolean',
            'reference_no' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        // Calculate VAT if applicable
        if ($request->has('vat_percentage') && $request->vat_percentage > 0 && ($request->cash_out > 0 || $request->cash_in > 0)) {
            if ($request->is_vat_included) {
                // VAT is included in the amount
                $amount = $request->cash_out > 0 ? $request->cash_out : $request->cash_in;
                $vatAmount = $amount - ($amount / (1 + ($request->vat_percentage / 100)));
                $validated['vat_amount'] = $vatAmount;
            } else {
                // VAT is added to the amount
                $validated['vat_amount'] = ($request->cash_out > 0 ? $request->cash_out : $request->cash_in) * ($request->vat_percentage / 100);
            }
        } else {
            $validated['vat_amount'] = 0;
        }

        // Set created_by to the authenticated user's ID if available, otherwise null


        $transaction = PettyCashTransaction::create($validated);

        return response()->json([
            'message' => 'Transaction created successfully',
            'transaction' => $transaction->load(['category']),
        ]);
    }

    public function update(Request $request, PettyCashTransaction $transaction)
    {
        $validated = $request->validate([
            'transaction_date' => 'required|date',
            'bs_date' => 'nullable|date',
            'pan_bill_no' => 'nullable|string|max:50',
            'est_bill_no' => 'nullable|string|max:50',
            'category_id' => 'required|exists:petty_cash_categories,id',
            'particulars' => 'required|string|max:255',
            'cash_in' => 'required_without:cash_out|numeric|min:0',
            'cash_out' => 'required_without:cash_in|numeric|min:0',
            'vat_percentage' => 'nullable|numeric|min:0|max:100',
            'is_vat_included' => 'boolean',
            'reference_no' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        // Calculate VAT if applicable
        if ($request->has('vat_percentage') && $request->vat_percentage > 0 && ($request->cash_out > 0 || $request->cash_in > 0)) {
            if ($request->is_vat_included) {
                // VAT is included in the amount
                $amount = $request->cash_out > 0 ? $request->cash_out : $request->cash_in;
                $vatAmount = $amount - ($amount / (1 + ($request->vat_percentage / 100)));
                $validated['vat_amount'] = $vatAmount;
            } else {
                // VAT is added to the amount
                $validated['vat_amount'] = ($request->cash_out > 0 ? $request->cash_out : $request->cash_in) * ($request->vat_percentage / 100);
            }
        } else {
            $validated['vat_amount'] = 0;
        }

        $transaction->update($validated);

        return response()->json([
            'message' => 'Transaction updated successfully',
            'transaction' => $transaction->load(['category']),
        ]);
    }

    public function destroy(PettyCashTransaction $transaction)
    {
        $transaction->delete();

        return response()->json([
            'message' => 'Transaction deleted successfully',
        ]);
    }

    public function show(PettyCashTransaction $transaction)
    {
        return response()->json([
            'transaction' => $transaction->load(['category']),
        ]);
    }
    public function export(Request $request)
    {
        $query = PettyCashTransaction::with(['category'])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc');

        // Apply filters (same as index method)
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('particulars', 'like', "%$search%")
                    ->orWhere('pan_bill_no', 'like', "%$search%")
                    ->orWhere('est_bill_no', 'like', "%$search%")
                    ->orWhere('reference_no', 'like', "%$search%")
                    ->orWhereHas('category', function ($q) use ($search) {
                        $q->where('name', 'like', "%$search%");
                    });
            });
        }

        if ($request->has('category_id') && $request->category_id != '') {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('date_range') && $request->date_range != '') {
            $dates = explode(' to ', $request->date_range);
            $startDate = $dates[0];
            $endDate = $dates[1] ?? $dates[0];
            $query->whereBetween('transaction_date', [$startDate, $endDate]);
        }

        try {
            $transactions = $query->get();

            if ($transactions->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No transactions found to export'
                ], 404);
            }

            $csv = Writer::createFromFileObject(new \SplTempFileObject());

            // Insert CSV header
            $csv->insertOne([
                'ID',
                'Transaction Date',
                'BS Date',
                'PAN Bill No',
                'Est Bill No',
                'Category',
                'Particulars',
                'Cash In',
                'Cash Out',
                'VAT %',
                'VAT Amount',
                'VAT Included',
                'Reference No',
                'Notes',
                'Created At',
                'Updated At'
            ]);

            // Insert data rows
            foreach ($transactions as $transaction) {
                $csv->insertOne([
                    $transaction->id,
                    $transaction->transaction_date,
                    $transaction->bs_date,
                    $transaction->pan_bill_no,
                    $transaction->est_bill_no,
                    $transaction->category ? $transaction->category->name : 'N/A',
                    $transaction->particulars,
                    $transaction->cash_in,
                    $transaction->cash_out,
                    $transaction->vat_percentage,
                    $transaction->vat_amount,
                    $transaction->is_vat_included ? 'Yes' : 'No',
                    $transaction->reference_no,
                    $transaction->notes,
                    $transaction->created_at,
                    $transaction->updated_at,
                ]);
            }

            $filename = 'petty_cash_transactions_' . date('Y-m-d_H-i-s') . '.csv';
            $csvContent = (string) $csv;

            return response($csvContent)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Content-Length', strlen($csvContent));
        } catch (\Exception $e) {
            Log::error('CSV Export Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error generating CSV: ' . $e->getMessage()
            ], 500);
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048', // Max 2MB
        ]);

        $file = $request->file('file');
        $csv = Reader::createFromPath($file->getRealPath(), 'r');
        $csv->setHeaderOffset(0); // First row is header

        $header = $csv->getHeader();
        $expectedHeader = [
            'ID',
            'Transaction Date',
            'BS Date',
            'PAN Bill No',
            'Est Bill No',
            'Category',
            'Particulars',
            'Cash In',
            'Cash Out',
            'VAT %',
            'VAT Amount',
            'VAT Included',
            'Reference No',
            'Notes',
            'Created At',
            'Updated At'
        ];

        if (array_diff($expectedHeader, $header)) {
            return response()->json(['error' => 'Invalid CSV header'], 400);
        }

        $records = $csv->getRecords();
        $errors = [];
        $successCount = 0;

        foreach ($records as $index => $record) {
            $validator = Validator::make($record, [
                'Transaction Date' => 'required|date',
                'BS Date' => 'nullable|date',
                'PAN Bill No' => 'nullable|string|max:50',
                'Est Bill No' => 'nullable|string|max:50',
                'Category' => 'required|string|exists:petty_cash_categories,name',
                'Particulars' => 'required|string|max:255',
                'Cash In' => 'required_without:Cash Out|numeric|min:0',
                'Cash Out' => 'required_without:Cash In|numeric|min:0',
                'VAT %' => 'nullable|numeric|min:0|max:100',
                'VAT Amount' => 'nullable|numeric|min:0',
                'VAT Included' => 'nullable|in:Yes,No,yes,no',
                'Reference No' => 'nullable|string|max:50',
                'Notes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                $errors[] = "Row " . ($index + 2) . ": " . implode(', ', $validator->errors()->all());
                continue;
            }

            $category = PettyCashCategory::where('name', $record['Category'])->first();
            if (!$category) {
                $errors[] = "Row " . ($index + 2) . ": Category '" . $record['Category'] . "' not found";
                continue;
            }

            $data = [
                'transaction_date' => $record['Transaction Date'],
                'bs_date' => $record['BS Date'] ?: null,
                'pan_bill_no' => $record['PAN Bill No'] ?: null,
                'est_bill_no' => $record['Est Bill No'] ?: null,
                'category_id' => $category->id,
                'particulars' => $record['Particulars'],
                'cash_in' => $record['Cash In'] ?: 0,
                'cash_out' => $record['Cash Out'] ?: 0,
                'vat_percentage' => $record['VAT %'] ?: null,
                'vat_amount' => $record['VAT Amount'] ?: 0,
                'is_vat_included' => in_array(strtolower($record['VAT Included']), ['yes', 'y']) ? 1 : 0,
                'reference_no' => $record['Reference No'] ?: null,
                'notes' => $record['Notes'] ?: null,
                'created_at' => $record['Created At'] ?: now(),
                'updated_at' => $record['Updated At'] ?: now(),
            ];

            try {
                PettyCashTransaction::updateOrCreate(
                    ['id' => $record['ID'] ?: null], // Update if ID exists, create if not
                    $data
                );
                $successCount++;
            } catch (\Exception $e) {
                $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
            }
        }

        return response()->json([
            'message' => "Imported $successCount transactions successfully",
            'errors' => $errors,
        ], $errors ? 206 : 200); // 206 Partial Content if there are errors
    }
}
