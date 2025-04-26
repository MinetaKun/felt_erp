<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Payroll extends Model
{
    protected $fillable = [
        'artisan_id',
        'month',
        'year',
        'basic_salary',
        'ot_pay',
        'order_based_wages',
        'allowances',
        'advance_deductions',
        'net_salary',
        'status',
        'paid_at',
        'paid_by'
    ];

    protected $casts = [
        'month' => 'integer',
        'year' => 'integer',
        'basic_salary' => 'decimal:2',
        'ot_pay' => 'decimal:2',
        'order_based_wages' => 'decimal:2',
        'allowances' => 'decimal:2',
        'advance_deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'paid_at' => 'datetime'
    ];

    public function artisan()
    {
        return $this->belongsTo(Artisan::class);
    }

    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public static function calculateMonthlyPayroll($artisan, $month, $year)
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Get all order assignments for the month
        $assignments = OrderAssignment::where('artisan_id', $artisan->id)
            ->whereBetween('approved_at', [$startDate, $endDate])
            ->where('status', 'dispatched')
            ->get();

        // Calculate order-based wages
        $orderBasedWages = $assignments->sum(function ($assignment) {
            return $assignment->approved_quantity * $assignment->order->wages_per_unit;
        });

        // Calculate OT pay (assuming 1.5x rate for OT)
        $otPay = $assignments->sum(function ($assignment) {
            return $assignment->ot_hours * ($assignment->order->wages_per_unit * 1.5);
        });

        // Calculate holiday deductions
        $holidayDeductions = $assignments->sum(function ($assignment) {
            return $assignment->holiday_hours * $assignment->order->wages_per_unit;
        });

        // Calculate lunch deductions
        $lunchDeductions = $assignments->sum('lunch_deduction');

        // Calculate final wages
        $finalWages = $orderBasedWages + $otPay - $holidayDeductions - $lunchDeductions;

        // If artisan has basic salary, use that instead
        if ($artisan->is_production_based === false && $artisan->basic_salary) {
            $finalWages = $artisan->basic_salary;
        }

        return [
            'basic_salary' => $artisan->basic_salary ?? 0,
            'ot_pay' => $otPay,
            'order_based_wages' => $orderBasedWages,
            'allowances' => 0, // Can be customized based on requirements
            'advance_deductions' => 0, // Can be customized based on requirements
            'net_salary' => $finalWages
        ];
    }
}
