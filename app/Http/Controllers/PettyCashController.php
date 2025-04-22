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
        // Calculate totals with date range filter
        $totalsQuery = PettyCashTransaction::query();
        if ($request->has('start_date') && $request->has('end_date')) {
            $totalsQuery->whereBetween('transaction_date', [
                $request->start_date,
                $request->end_date,
            ]);
        }
        $totalIncome = $totalsQuery->sum('cash_in');
        $totalExpenses = $totalsQuery->sum('cash_out');
        $balance = $totalIncome - $totalExpenses;

        // Category-wise totals
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
            'total_income' => $totalIncome,
            'total_expenses' => $totalExpenses,
            'category_totals' => $categoryTotals,
            'monthly_data' => $monthlyData,
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
        try {
            // Log the request for debugging
            Log::info('Export request received', ['params' => $request->all()]);

            $query = PettyCashTransaction::with(['category']);

            // Apply filters
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('particulars', 'like', "%{$search}%")
                        ->orWhere('pan_bill_no', 'like', "%{$search}%")
                        ->orWhere('est_bill_no', 'like', "%{$search}%")
                        ->orWhere('reference_no', 'like', "%{$search}%")
                        ->orWhereHas('category', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                });
            }

            if ($request->has('category_id') && !empty($request->category_id)) {
                $query->where('category_id', $request->category_id);
            }

            if ($request->has('date_range') && !empty($request->date_range)) {
                $dates = explode(' to ', $request->date_range);
                $startDate = $dates[0];
                $endDate = $dates[1] ?? $dates[0];
                $query->whereBetween('transaction_date', [$startDate, $endDate]);
            }

            // Order by transaction date and created_at
            $query->orderBy('transaction_date', 'desc')
                ->orderBy('created_at', 'desc');

            $transactions = $query->get();

            Log::info('Found transactions for export', ['count' => $transactions->count()]);

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

            Log::info('CSV generated successfully', ['size' => strlen($csvContent)]);

            return response($csvContent)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Content-Length', strlen($csvContent));
        } catch (\Exception $e) {
            Log::error('CSV Export Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error generating CSV: ' . $e->getMessage()
            ], 500);
        }
    }

    // Remove the apiExport method as it's no longer needed
    public function apiExport(Request $request)
    {
        return $this->export($request);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048', // Max 2MB
        ]);

        $file = $request->file('file');

        // Read the file content first
        $content = file_get_contents($file->getRealPath());
        Log::info('Raw CSV Content', ['content' => $content]);

        // Clean the content by removing outer quotes and newlines
        $content = trim($content, "\"\n\r");
        $content = str_replace("\r\n", "\n", $content);
        $content = str_replace("\r", "\n", $content);

        // Split into lines
        $lines = explode("\n", $content);
        if (count($lines) < 2) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid CSV format. File must contain at least a header row and one data row.'
            ], 400);
        }

        // Process headers
        $headerLine = trim($lines[0], "\"");
        $header = explode(',', $headerLine);
        $header = array_map(function ($item) {
            return trim($item, "\" ");
        }, $header);

        Log::info('Processed Headers', [
            'headers' => $header,
            'headers_count' => count($header),
            'first_header' => $header[0] ?? null,
            'headers_array' => $header
        ]);

        $expectedHeader = [
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
            'Notes'
        ];

        // Clean and normalize headers
        $normalizedHeader = array_map(function ($item) {
            // Remove BOM and other invisible characters
            $item = preg_replace('/[\x{FEFF}\x{200B}-\x{200D}\x{FFFE}]/u', '', $item);
            // Remove quotes and trim
            $item = trim($item, '"\' ');
            // Convert to lowercase and remove any extra spaces
            $item = strtolower(preg_replace('/\s+/', ' ', $item));
            return $item;
        }, $header);

        $normalizedExpectedHeader = array_map(function ($item) {
            return strtolower(trim($item));
        }, $expectedHeader);

        Log::info('Normalized Headers Comparison', [
            'normalized_actual' => $normalizedHeader,
            'normalized_expected' => $normalizedExpectedHeader,
            'missing_headers' => array_diff($normalizedExpectedHeader, $normalizedHeader),
            'header_mapping' => array_combine($normalizedHeader, $header)
        ]);

        // Check for missing headers with more detailed logging
        $missingHeaders = array_diff($normalizedExpectedHeader, $normalizedHeader);
        if (!empty($missingHeaders)) {
            Log::error('Missing CSV Headers', [
                'missing_headers' => $missingHeaders,
                'actual_headers' => $header,
                'expected_headers' => $expectedHeader,
                'normalized_actual' => $normalizedHeader,
                'normalized_expected' => $normalizedExpectedHeader,
                'header_mapping' => array_combine($normalizedHeader, $header),
                'raw_content' => $content
            ]);

            // Convert normalized headers back to original format for error message
            $missingOriginalHeaders = array_map(function ($normalizedHeader) use ($expectedHeader, $normalizedExpectedHeader) {
                return $expectedHeader[array_search($normalizedHeader, $normalizedExpectedHeader)];
            }, $missingHeaders);

            return response()->json([
                'success' => false,
                'error' => 'Invalid CSV header. Missing columns: ' . implode(', ', $missingOriginalHeaders),
                'details' => [
                    'actual_headers' => $header,
                    'expected_headers' => $expectedHeader,
                    'normalized_actual' => $normalizedHeader,
                    'normalized_expected' => $normalizedExpectedHeader
                ]
            ], 400);
        }

        // Process data rows
        $records = [];
        for ($i = 1; $i < count($lines); $i++) {
            $line = trim($lines[$i], "\"");
            $values = explode(',', $line);
            $values = array_map(function ($item) {
                return trim($item, "\" ");
            }, $values);

            if (count($values) === count($header)) {
                $records[] = array_combine($header, $values);
            }
        }

        $errors = [];
        $successCount = 0;
        $rowNumber = 1; // Start at row 1 (after header)

        DB::beginTransaction();

        try {
            foreach ($records as $record) {
                $rowNumber++;

                // Log the raw record data
                Log::info('Processing CSV Row', [
                    'row_number' => $rowNumber,
                    'raw_record' => $record,
                    'transaction_date' => $record['Transaction Date'] ?? null,
                    'record_keys' => array_keys($record),
                    'record_values' => array_values($record)
                ]);

                // Clean and normalize the record data
                $cleanedRecord = [];
                foreach ($record as $key => $value) {
                    // Remove quotes and trim
                    $cleanedKey = trim($key, '"\' ');
                    $cleanedValue = trim($value, '"\' ');
                    $cleanedRecord[$cleanedKey] = $cleanedValue;
                }

                // Validate required fields
                if (empty($cleanedRecord['Transaction Date'])) {
                    $errors[] = "Row {$rowNumber}: Transaction Date is required";
                    Log::error('Missing Transaction Date', [
                        'row_number' => $rowNumber,
                        'record' => $cleanedRecord,
                        'record_keys' => array_keys($cleanedRecord),
                        'record_values' => array_values($cleanedRecord)
                    ]);
                    continue;
                }

                if (empty($cleanedRecord['Category'])) {
                    $errors[] = "Row {$rowNumber}: Category is required";
                    continue;
                }

                if (empty($cleanedRecord['Particulars'])) {
                    $errors[] = "Row {$rowNumber}: Particulars is required";
                    continue;
                }

                if (empty($cleanedRecord['Cash In']) && empty($cleanedRecord['Cash Out'])) {
                    $errors[] = "Row {$rowNumber}: Either Cash In or Cash Out must have a value";
                    continue;
                }

                if (!empty($cleanedRecord['Cash In']) && !empty($cleanedRecord['Cash Out'])) {
                    $errors[] = "Row {$rowNumber}: Cannot have both Cash In and Cash Out values";
                    continue;
                }

                // Find category
                $category = PettyCashCategory::where('name', $cleanedRecord['Category'])->first();
                if (!$category) {
                    $errors[] = "Row {$rowNumber}: Category '{$cleanedRecord['Category']}' not found";
                    continue;
                }

                // Parse dates
                try {
                    $transactionDate = Carbon::parse($cleanedRecord['Transaction Date']);
                } catch (\Exception $e) {
                    $errors[] = "Row {$rowNumber}: Invalid Transaction Date format";
                    continue;
                }

                $bsDate = null;
                if (!empty($cleanedRecord['BS Date'])) {
                    try {
                        $bsDate = Carbon::parse($cleanedRecord['BS Date']);
                    } catch (\Exception $e) {
                        $errors[] = "Row {$rowNumber}: Invalid BS Date format";
                        continue;
                    }
                }

                // Parse numeric values
                $cashIn = !empty($cleanedRecord['Cash In']) ? (float)$cleanedRecord['Cash In'] : 0;
                $cashOut = !empty($cleanedRecord['Cash Out']) ? (float)$cleanedRecord['Cash Out'] : 0;
                $vatPercentage = !empty($cleanedRecord['VAT %']) ? (float)$cleanedRecord['VAT %'] : null;
                $vatAmount = !empty($cleanedRecord['VAT Amount']) ? (float)$cleanedRecord['VAT Amount'] : 0;
                $isVatIncluded = !empty($cleanedRecord['VAT Included']) && strtolower($cleanedRecord['VAT Included']) === 'yes';

                // Create transaction
                try {
                    PettyCashTransaction::create([
                        'transaction_date' => $transactionDate,
                        'bs_date' => $bsDate,
                        'pan_bill_no' => $cleanedRecord['PAN Bill No'] ?? null,
                        'est_bill_no' => $cleanedRecord['Est Bill No'] ?? null,
                        'category_id' => $category->id,
                        'particulars' => $cleanedRecord['Particulars'],
                        'cash_in' => $cashIn,
                        'cash_out' => $cashOut,
                        'vat_percentage' => $vatPercentage,
                        'vat_amount' => $vatAmount,
                        'is_vat_included' => $isVatIncluded,
                        'reference_no' => $cleanedRecord['Reference No'] ?? null,
                        'notes' => $cleanedRecord['Notes'] ?? null,
                    ]);

                    $successCount++;
                } catch (\Exception $e) {
                    $errors[] = "Row {$rowNumber}: " . $e->getMessage();
                }
            }

            if ($successCount > 0) {
                DB::commit();
                $message = "{$successCount} transactions imported successfully";
                if (!empty($errors)) {
                    $message .= " with some errors";
                }

                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'errors' => $errors
                ], !empty($errors) ? 206 : 200); // 206 Partial Content if there are errors
            } else {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'error' => 'No transactions were imported due to errors',
                    'errors' => $errors
                ], 422);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('CSV Import Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'An error occurred during import: ' . $e->getMessage()
            ], 500);
        }
    }

    // Add this new method for API export:

    // Add this method at the end of the class
    public function exportFallback(Request $request)
    {
        // This is a fallback method that will be called if the main export method fails
        Log::info('Export fallback method called', ['params' => $request->all()]);

        try {
            // Get all transactions without any filters
            $transactions = PettyCashTransaction::with(['category'])->get();

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
                'Notes'
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
                    $transaction->notes
                ]);
            }

            $filename = 'petty_cash_transactions_' . date('Y-m-d_H-i-s') . '.csv';
            $csvContent = (string) $csv;

            return response($csvContent)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Content-Length', strlen($csvContent));
        } catch (\Exception $e) {
            Log::error('CSV Export Fallback Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error generating CSV: ' . $e->getMessage()
            ], 500);
        }
    }

    // Add this method to the PettyCashController class
    public function getSampleImport()
    {
        try {
            $csv = Writer::createFromFileObject(new \SplTempFileObject());

            // Insert CSV header
            $csv->insertOne([
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
                'Notes'
            ]);

            // Get some category names for the sample
            $categories = PettyCashCategory::pluck('name')->take(3)->toArray();
            if (empty($categories)) {
                $categories = ['Office Supplies', 'Utilities', 'Salary'];
            }

            // Insert sample data rows
            $csv->insertOne([
                date('Y-m-d'), // Today's date
                '', // BS Date (empty)
                'PAN-12345', // Sample PAN Bill No
                'EST-001', // Sample Est Bill No
                $categories[0], // First category
                'Office supplies purchase', // Sample particulars
                '', // Cash In (empty for expense)
                '1500.00', // Cash Out
                '13', // VAT %
                '195.00', // VAT Amount
                'Yes', // VAT Included
                'REF-001', // Reference No
                'Monthly office supplies' // Notes
            ]);

            $csv->insertOne([
                date('Y-m-d', strtotime('-1 day')), // Yesterday's date
                '', // BS Date (empty)
                '', // PAN Bill No (empty)
                '', // Est Bill No (empty)
                $categories[count($categories) > 1 ? 1 : 0], // Second category or first if only one exists
                'Electricity bill payment', // Sample particulars
                '', // Cash In (empty for expense)
                '2500.00', // Cash Out
                '', // VAT % (empty)
                '', // VAT Amount (empty)
                'No', // VAT Included
                'REF-002', // Reference No
                'Monthly electricity bill' // Notes
            ]);

            $csv->insertOne([
                date('Y-m-d', strtotime('-2 days')), // 2 days ago
                '', // BS Date (empty)
                '', // PAN Bill No (empty)
                '', // Est Bill No (empty)
                $categories[count($categories) > 2 ? 2 : 0], // Third category or first if only one exists
                'Client payment received', // Sample particulars
                '5000.00', // Cash In
                '', // Cash Out (empty for income)
                '', // VAT % (empty)
                '', // VAT Amount (empty)
                'No', // VAT Included
                'REF-003', // Reference No
                'Payment for invoice #123' // Notes
            ]);

            $filename = 'petty_cash_import_sample.csv';
            $csvContent = (string) $csv;

            return response($csvContent)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Content-Length', strlen($csvContent));
        } catch (\Exception $e) {
            Log::error('Sample CSV Generation Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error generating sample CSV: ' . $e->getMessage()
            ], 500);
        }
    }
}
