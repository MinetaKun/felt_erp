<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\Artisan;
use App\Models\PayrollAdvance;
use App\Models\OrderAssignment;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\PDF;
use Carbon\Carbon;
use League\Csv\Writer;
use SplTempFileObject;
use Illuminate\Support\Facades\Log;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $query = Payroll::with(['artisan', 'paidBy']);

        if ($request->filled('month') && $request->filled('year')) {
            $query->where('month', $request->month)
                ->where('year', $request->year);
        }

        if ($request->filled('artisan_id')) {
            $query->where('artisan_id', $request->artisan_id);
        }

        $payrolls = $query->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $payrolls
        ]);
    }

    public function generateMonthlyPayroll(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2000'
        ]);

        $artisans = Artisan::all();
        $generatedPayrolls = [];

        DB::beginTransaction();
        try {
            foreach ($artisans as $artisan) {
                $payrollData = Payroll::calculateMonthlyPayroll($artisan, $request->month, $request->year);

                $payroll = Payroll::create([
                    'artisan_id' => $artisan->id,
                    'month' => $request->month,
                    'year' => $request->year,
                    'basic_salary' => $payrollData['basic_salary'],
                    'ot_pay' => $payrollData['ot_pay'],
                    'order_based_wages' => $payrollData['order_based_wages'],
                    'allowances' => $payrollData['allowances'],
                    'advance_deductions' => $payrollData['advance_deductions'],
                    'net_salary' => $payrollData['net_salary'],
                    'status' => 'pending'
                ]);

                $generatedPayrolls[] = $payroll;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Monthly payroll generated successfully',
                'data' => $generatedPayrolls
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate payroll',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function markAsPaid(Request $request, $id)
    {
        $payroll = Payroll::findOrFail($id);

        $payroll->update([
            'status' => 'paid',
            'paid_at' => now(),
            'paid_by' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payroll marked as paid successfully',
            'data' => $payroll
        ]);
    }

    public function generateSalarySheet(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2000'
        ]);

        $payrolls = Payroll::with(['artisan'])
            ->where('month', $request->month)
            ->where('year', $request->year)
            ->get();

        $pdf = PDF::loadView('payroll.salary-sheet', [
            'payrolls' => $payrolls,
            'month' => Carbon::create($request->year, $request->month, 1)->format('F Y')
        ]);

        return $pdf->download('salary-sheet-' . $request->month . '-' . $request->year . '.pdf');
    }

    public function generateCashPaymentSheet(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
        ]);

        $startDate = Carbon::createFromFormat('Y-m-d', $request->start_date);
        $endDate = Carbon::createFromFormat('Y-m-d', $request->end_date);
        $month = $startDate->format('F Y');

        // First get the wages from order assignments
        $artisanWages = DB::table('order_assignments')
            ->join('orders', 'order_assignments.order_id', '=', 'orders.id')
            ->whereBetween('order_assignments.approved_at', [$startDate, $endDate])
            ->where('order_assignments.status', 'dispatched')
            ->where('order_assignments.approved_quantity', '>', 0)
            ->groupBy('order_assignments.artisan_id')
            ->select(
                'order_assignments.artisan_id',
                DB::raw('SUM(order_assignments.approved_quantity * orders.wages_per_unit) as total_wages')
            );

        // Get advances
        $artisanAdvances = DB::table('payroll_advances')
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy('artisan_id')
            ->select(
                'artisan_id',
                DB::raw('SUM(amount) as total_advances')
            );

        $salaryCalculations = DB::table('salary_calculations')
            ->join('artisans', 'salary_calculations.artisan_id', '=', 'artisans.id')
            ->leftJoinSub($artisanWages, 'wages', function ($join) {
                $join->on('salary_calculations.artisan_id', '=', 'wages.artisan_id');
            })
            ->leftJoinSub($artisanAdvances, 'advances', function ($join) {
                $join->on('salary_calculations.artisan_id', '=', 'advances.artisan_id');
            })
            ->where('salary_calculations.start_date', '>=', $startDate)
            ->where('salary_calculations.end_date', '<=', $endDate)
            ->whereNull('artisans.bank_account_number')
            ->select(
                'salary_calculations.*',
                'artisans.name as artisan_name',
                'artisans.phone_number',
                DB::raw('COALESCE(wages.total_wages, 0) as wages'),
                DB::raw('COALESCE(advances.total_advances, 0) as advance'),
                DB::raw('(
                    COALESCE(salary_calculations.salary, 0) + 
                    COALESCE(salary_calculations.food_allowance, 0) + 
                    COALESCE(salary_calculations.allowances, 0) + 
                    COALESCE(wages.total_wages, 0) - 
                    COALESCE(advances.total_advances, 0)
                ) as net_salary')
            )
            ->get();

        $pdf = PDF::loadView('payroll.cash-payment-sheet', [
            'payrolls' => $salaryCalculations,
            'month' => $month
        ]);

        return $pdf->download('cash-payment-sheet-' . $startDate->format('F-Y') . '.pdf');
    }

    public function generateBankTransferSheet(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
        ]);

        $startDate = Carbon::createFromFormat('Y-m-d', $request->start_date);
        $endDate = Carbon::createFromFormat('Y-m-d', $request->end_date);
        $month = $startDate->format('F Y');

        // First get the wages from order assignments
        $artisanWages = DB::table('order_assignments')
            ->join('orders', 'order_assignments.order_id', '=', 'orders.id')
            ->whereBetween('order_assignments.approved_at', [$startDate, $endDate])
            ->where('order_assignments.status', 'dispatched')
            ->where('order_assignments.approved_quantity', '>', 0)
            ->groupBy('order_assignments.artisan_id')
            ->select(
                'order_assignments.artisan_id',
                DB::raw('SUM(order_assignments.approved_quantity * orders.wages_per_unit) as total_wages')
            );

        // Get advances
        $artisanAdvances = DB::table('payroll_advances')
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy('artisan_id')
            ->select(
                'artisan_id',
                DB::raw('SUM(amount) as total_advances')
            );

        $salaryCalculations = DB::table('salary_calculations')
            ->join('artisans', 'salary_calculations.artisan_id', '=', 'artisans.id')
            ->leftJoinSub($artisanWages, 'wages', function ($join) {
                $join->on('salary_calculations.artisan_id', '=', 'wages.artisan_id');
            })
            ->leftJoinSub($artisanAdvances, 'advances', function ($join) {
                $join->on('salary_calculations.artisan_id', '=', 'advances.artisan_id');
            })
            ->where('salary_calculations.start_date', '>=', $startDate)
            ->where('salary_calculations.end_date', '<=', $endDate)
            ->whereNotNull('artisans.bank_account_number')
            ->select(
                'salary_calculations.*',
                'artisans.name as artisan_name',
                'artisans.bank_account_number',
                DB::raw('COALESCE(wages.total_wages, 0) as wages'),
                DB::raw('COALESCE(advances.total_advances, 0) as advance'),
                DB::raw('(
                    COALESCE(salary_calculations.salary, 0) + 
                    COALESCE(salary_calculations.food_allowance, 0) + 
                    COALESCE(salary_calculations.allowances, 0) + 
                    COALESCE(wages.total_wages, 0) - 
                    COALESCE(advances.total_advances, 0)
                ) as net_salary')
            )
            ->get();

        $pdf = PDF::loadView('payroll.bank-transfer-sheet', [
            'payrolls' => $salaryCalculations,
            'month' => $month
        ]);

        return $pdf->download('bank-transfer-sheet-' . $startDate->format('F-Y') . '.pdf');
    }

    public function exportBankTransferCSV(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2000'
        ]);

        $payrolls = Payroll::with(['artisan'])
            ->where('month', $request->month)
            ->where('year', $request->year)
            ->whereHas('artisan', function ($query) {
                $query->whereNotNull('bank_account_number');
            })
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="bank-transfer-' . $request->month . '-' . $request->year . '.csv"',
        ];

        $callback = function () use ($payrolls) {
            $file = fopen('php://output', 'w');

            // Add headers
            fputcsv($file, [
                'Account Holder Name',
                'Account Number',
                'Bank Name',
                'Amount'
            ]);

            // Add data
            foreach ($payrolls as $payroll) {
                fputcsv($file, [
                    $payroll->artisan->name,
                    $payroll->artisan->bank_account_number,
                    $payroll->artisan->bank_name,
                    $payroll->net_salary
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // Admin Payroll Methods
    public function getAdminPayroll(Request $request)
    {
        $payrolls = Payroll::with('artisan')
            ->where('type', 'admin')
            ->where('month', $request->month)
            ->where('year', $request->year)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $payrolls
        ]);
    }

    public function saveAdminPayroll(Request $request)
    {
        $validated = $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer',
            'payrolls' => 'required|array',
            'payrolls.*.artisan_id' => 'required|exists:artisans,id',
            'payrolls.*.salary' => 'required|numeric',
            'payrolls.*.food_allowance' => 'required|numeric',
            'payrolls.*.felting_wages' => 'required|numeric',
            'payrolls.*.needling_wages' => 'required|numeric',
            'payrolls.*.allowances' => 'required|numeric',
            'payrolls.*.advance' => 'required|numeric',
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['payrolls'] as $payrollData) {
                Payroll::updateOrCreate(
                    [
                        'artisan_id' => $payrollData['artisan_id'],
                        'month' => $validated['month'],
                        'year' => $validated['year'],
                        'type' => 'admin'
                    ],
                    $payrollData
                );
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Admin payroll saved successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to save admin payroll: ' . $e->getMessage()
            ], 500);
        }
    }

    // Worker Payroll Methods
    public function getWorkerPayroll(Request $request)
    {
        $payrolls = Payroll::with('artisan')
            ->where('type', 'worker')
            ->where('month', $request->month)
            ->where('year', $request->year)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $payrolls
        ]);
    }

    public function saveWorkerPayroll(Request $request)
    {
        $validated = $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer',
            'payrolls' => 'required|array',
            'payrolls.*.artisan_id' => 'required|exists:artisans,id',
            'payrolls.*.production_wages' => 'required|numeric',
            'payrolls.*.allowances' => 'required|numeric',
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['payrolls'] as $payrollData) {
                Payroll::updateOrCreate(
                    [
                        'artisan_id' => $payrollData['artisan_id'],
                        'month' => $validated['month'],
                        'year' => $validated['year'],
                        'type' => 'worker'
                    ],
                    $payrollData
                );
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Worker payroll saved successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to save worker payroll: ' . $e->getMessage()
            ], 500);
        }
    }

    // Artisan Advances Methods
    public function getAdvances(Request $request)
    {
        $query = PayrollAdvance::with('artisan');

        if ($request->filled('start_date')) {
            $query->where('date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('date', '<=', $request->end_date);
        }

        $advances = $query->get();

        // If format is specified, generate export
        if ($request->has('format')) {
            if ($advances->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No advances found for the selected period'
                ], 404);
            }

            if ($request->format === 'pdf') {
                try {
                    $pdf = PDF::loadView('payroll.advances-report', [
                        'advances' => $advances,
                        'start_date' => $request->start_date,
                        'end_date' => $request->end_date,
                        'total_amount' => $advances->sum('amount')
                    ]);

                    $filename = 'artisan-advances-report_' . date('Y-m-d_H-i-s') . '.pdf';
                    return $pdf->download($filename);
                } catch (\Exception $e) {
                    Log::error('PDF Export Error: ' . $e->getMessage());
                    return response()->json([
                        'success' => false,
                        'message' => 'Error generating PDF: ' . $e->getMessage()
                    ], 500);
                }
            } else {
                try {
                    $csv = Writer::createFromFileObject(new SplTempFileObject());

                    $csv->insertOne([
                        'S.N.',
                        'Artisan Name',
                        'Amount',
                        'Date',
                        'Notes'
                    ]);

                    foreach ($advances as $index => $advance) {
                        $csv->insertOne([
                            $index + 1,
                            $advance->artisan->name,
                            number_format($advance->amount, 2),
                            date('d M Y', strtotime($advance->date)),
                            $advance->notes ?? ''
                        ]);
                    }

                    $filename = 'artisan-advances-report_' . date('Y-m-d_H-i-s') . '.csv';
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
        }

        // Return JSON response for data retrieval
        return response()->json([
            'success' => true,
            'data' => $advances
        ]);
    }

    public function generateAdvanceReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'format' => 'required|in:pdf,csv'
        ]);

        $query = PayrollAdvance::with('artisan');

        if ($request->filled('start_date')) {
            $query->where('date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('date', '<=', $request->end_date);
        }

        $advances = $query->get();

        if ($advances->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No advances found for the selected period'
            ], 404);
        }

        if ($request->format === 'pdf') {
            try {
                $pdf = PDF::loadView('payroll.advances-report', [
                    'advances' => $advances,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'total_amount' => $advances->sum('amount')
                ]);

                $filename = 'artisan-advances-report_' . date('Y-m-d_H-i-s') . '.pdf';
                return $pdf->download($filename);
            } catch (\Exception $e) {
                Log::error('PDF Export Error: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Error generating PDF: ' . $e->getMessage()
                ], 500);
            }
        } else {
            try {
                $csv = Writer::createFromFileObject(new SplTempFileObject());

                $csv->insertOne([
                    'S.N.',
                    'Artisan Name',
                    'Amount',
                    'Date',
                    'Notes'
                ]);

                foreach ($advances as $index => $advance) {
                    $csv->insertOne([
                        $index + 1,
                        $advance->artisan->name,
                        number_format($advance->amount, 2),
                        date('d M Y', strtotime($advance->date)),
                        $advance->notes ?? ''
                    ]);
                }

                $filename = 'artisan-advances-report_' . date('Y-m-d_H-i-s') . '.csv';
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
    }

    public function saveAdvances(Request $request)
    {
        $validated = $request->validate([
            'advances' => 'required|array',
            'advances.*.id' => 'nullable|exists:payroll_advances,id',
            'advances.*.artisan_id' => 'required|exists:artisans,id',
            'advances.*.amount' => 'required|numeric',
            'advances.*.date' => 'required|date',
            'advances.*.notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['advances'] as $advanceData) {
                // Format the date to Y-m-d format
                $date = date('Y-m-d', strtotime($advanceData['date']));

                if (isset($advanceData['id'])) {
                    // Update existing advance
                    PayrollAdvance::where('id', $advanceData['id'])->update([
                        'artisan_id' => $advanceData['artisan_id'],
                        'amount' => $advanceData['amount'],
                        'date' => $date,
                        'notes' => $advanceData['notes'] ?? null
                    ]);
                } else {
                    // Create new advance
                    PayrollAdvance::create([
                        'artisan_id' => $advanceData['artisan_id'],
                        'amount' => $advanceData['amount'],
                        'date' => $date,
                        'notes' => $advanceData['notes'] ?? null
                    ]);
                }
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Advances saved successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to save advances: ' . $e->getMessage()
            ], 500);
        }
    }

    // Cash Payment Methods
    public function getCashPayments(Request $request)
    {
        $payments = Payroll::with('artisan')
            ->where('month', $request->month)
            ->where('year', $request->year)
            ->whereHas('artisan', function ($query) {
                $query->whereNull('bank_account_number');
            })
            ->get();

        return response()->json([
            'success' => true,
            'data' => $payments
        ]);
    }

    public function exportCashPaymentCSV(Request $request)
    {
        $payments = Payroll::with('artisan')
            ->where('month', $request->month)
            ->where('year', $request->year)
            ->whereHas('artisan', function ($query) {
                $query->whereNull('bank_account_number');
            })
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="cash-payment.csv"',
        ];

        $callback = function () use ($payments) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['S.N.', 'Name', 'Amount (NPR)']);

            foreach ($payments as $index => $payment) {
                fputcsv($file, [
                    $index + 1,
                    $payment->artisan->name,
                    $payment->total_amount
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function getSalaryCalculations(Request $request)
    {
        $query = Artisan::with(['orderAssignments.order']);

        // Get wages for specific artisan if ID is provided
        if ($request->has('artisan_id')) {
            $query->where('id', $request->artisan_id);
        }

        $artisans = $query->get()->map(function ($artisan) use ($request) {
            // Get saved salary calculations
            $salaryCalculation = DB::table('salary_calculations')
                ->where('artisan_id', $artisan->id)
                ->when($request->has('start_date') && !empty($request->start_date), function ($q) use ($request) {
                    $q->where('start_date', '>=', $request->start_date);
                })
                ->when($request->has('end_date') && !empty($request->end_date), function ($q) use ($request) {
                    $q->where('end_date', '<=', $request->end_date);
                })
                ->first();

            // Calculate wages from orders
            $wages = $artisan->orderAssignments()
                ->with('order')
                ->when($request->has('start_date') && !empty($request->start_date), function ($q) use ($request) {
                    $q->where('approved_at', '>=', $request->start_date);
                })
                ->when($request->has('end_date') && !empty($request->end_date), function ($q) use ($request) {
                    $q->where('approved_at', '<=', $request->end_date);
                })
                ->where('status', 'dispatched')
                ->where('approved_quantity', '>', 0)
                ->get();

            $totalWages = $wages->sum(function ($assignment) {
                return $assignment->approved_quantity * $assignment->order->wages_per_unit;
            });

            // Get advances
            $advances = PayrollAdvance::where('artisan_id', $artisan->id)
                ->when($request->has('start_date') && !empty($request->start_date), function ($q) use ($request) {
                    $q->where('date', '>=', $request->start_date);
                })
                ->when($request->has('end_date') && !empty($request->end_date), function ($q) use ($request) {
                    $q->where('date', '<=', $request->end_date);
                })
                ->sum('amount');

            return [
                'id' => $artisan->id,
                'name' => $artisan->name,
                'salary' => $salaryCalculation ? $salaryCalculation->salary : $artisan->basic_salary,
                'food_allowance' => $salaryCalculation ? $salaryCalculation->food_allowance : 0,
                'wages' => (float)$totalWages,
                'allowances' => $salaryCalculation ? $salaryCalculation->allowances : 0,
                'advance' => (float)$advances,
                'net_salary' => (float)(
                    ($salaryCalculation ? $salaryCalculation->salary : $artisan->basic_salary) +
                    ($salaryCalculation ? $salaryCalculation->food_allowance : 0) +
                    $totalWages +
                    ($salaryCalculation ? $salaryCalculation->allowances : 0) -
                    $advances
                )
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $artisans
        ]);
    }

    public function saveSalaryCalculation(Request $request)
    {
        $request->validate([
            'employees' => 'required|array',
            'employees.*.artisan_id' => 'required|exists:artisans,id',
            'employees.*.salary' => 'nullable|numeric',
            'employees.*.food_allowance' => 'nullable|numeric',
            'employees.*.allowances' => 'nullable|numeric',
            'employees.*.advance' => 'nullable|numeric',
            'employees.*.start_date' => 'required|date',
            'employees.*.end_date' => 'required|date'
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->employees as $employee) {
                DB::table('salary_calculations')
                    ->updateOrInsert(
                        [
                            'artisan_id' => $employee['artisan_id'],
                            'start_date' => $employee['start_date'],
                            'end_date' => $employee['end_date']
                        ],
                        [
                            'salary' => $employee['salary'],
                            'food_allowance' => $employee['food_allowance'],
                            'allowances' => $employee['allowances'],
                            'updated_at' => now()
                        ]
                    );
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Salary calculations saved successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to save salary calculations: ' . $e->getMessage()
            ], 500);
        }
    }

    public function exportSalaryCalculations(Request $request)
    {
        $query = Artisan::with(['department', 'orderAssignments']);

        // Apply filters
        if ($request->has('department') && !empty($request->department)) {
            $query->where('department_id', $request->department);
        }

        $artisans = $query->get()->map(function ($artisan, $index) use ($request) {
            // Get saved salary calculations
            $salaryCalculation = DB::table('salary_calculations')
                ->where('artisan_id', $artisan->id)
                ->when($request->has('start_date') && !empty($request->start_date), function ($q) use ($request) {
                    $q->where('start_date', '>=', $request->start_date);
                })
                ->when($request->has('end_date') && !empty($request->end_date), function ($q) use ($request) {
                    $q->where('end_date', '<=', $request->end_date);
                })
                ->first();

            // Calculate wages from orders
            $wages = $artisan->orderAssignments()
                ->with('order')
                ->when($request->has('start_date') && !empty($request->start_date), function ($q) use ($request) {
                    $q->where('approved_at', '>=', $request->start_date);
                })
                ->when($request->has('end_date') && !empty($request->end_date), function ($q) use ($request) {
                    $q->where('approved_at', '<=', $request->end_date);
                })
                ->where('status', 'dispatched')
                ->where('approved_quantity', '>', 0)
                ->get();

            $totalWages = $wages->sum(function ($assignment) {
                return $assignment->approved_quantity * $assignment->order->wages_per_unit;
            });

            // Get advances
            $advances = PayrollAdvance::where('artisan_id', $artisan->id)
                ->when($request->has('start_date') && !empty($request->start_date), function ($q) use ($request) {
                    $q->where('date', '>=', $request->start_date);
                })
                ->when($request->has('end_date') && !empty($request->end_date), function ($q) use ($request) {
                    $q->where('date', '<=', $request->end_date);
                })
                ->sum('amount');

            $salary = $salaryCalculation ? $salaryCalculation->salary : $artisan->basic_salary;
            $foodAllowance = $salaryCalculation ? $salaryCalculation->food_allowance : 0;
            $allowances = $salaryCalculation ? $salaryCalculation->allowances : 0;
            $netSalary = $salary + $foodAllowance + $totalWages + $allowances - $advances;

            return [
                'S.N.' => $index + 1,
                'Name' => $artisan->name,
                'Salary' => 'Rs.' . number_format($salary, 2),
                'Food Allowance' => 'Rs.' . number_format($foodAllowance, 2),
                'Wages' => 'Rs.' . number_format($totalWages, 2),
                'Allowances' => 'Rs.' . number_format($allowances, 2),
                'Advance' => 'Rs.' . number_format($advances, 2),
                'Net Salary' => 'Rs.' . number_format($netSalary, 2)
            ];
        });

        // Generate CSV
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="salary-calculations.csv"',
        ];

        $callback = function () use ($artisans) {
            $file = fopen('php://output', 'w');

            // Add headers
            fputcsv($file, [
                'S.N.',
                'Name',
                'Salary',
                'Food Allowance',
                'Wages',
                'Allowances',
                'Advance',
                'Net Salary'
            ]);

            // Add data
            foreach ($artisans as $artisan) {
                fputcsv($file, [
                    $artisan['S.N.'],
                    $artisan['Name'],
                    $artisan['Salary'],
                    $artisan['Food Allowance'],
                    $artisan['Wages'],
                    $artisan['Allowances'],
                    $artisan['Advance'],
                    $artisan['Net Salary']
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
