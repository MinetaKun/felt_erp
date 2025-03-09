<?php

// app/Http/Controllers/PettyCashController.php

namespace App\Http\Controllers;

use App\Models\PettyCash;
use Illuminate\Http\Request;

class PettyCashController extends Controller
{
    public function index()
    {
        $transactions = PettyCash::all(); // Retrieve all transactions
        return view('pettycash.index', compact('transactions'));
    }

    public function create()
    {
        return view('pettycash.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'category' => 'required|in:CASH ON HAND,KITCHEN EXP,WATER,FUEL,ELECTRICITY,PRODUCTION SUPPLIES,REPAIR AND MAINTENANCE,FIXED ASSETS,WAGES,ADVANCE,SALARY,OFFICE EXP,STATIONERY,ADVANCE RETURN,PERFORMANCE APPRAISAL,ADVANCE AMOUNT,CURRENT ASSETS',
            'particular' => 'required|string|max:255',
            'cash_in' => 'required|numeric',
            'cash_out' => 'required|numeric',
        ]);

        PettyCash::create([
            'date' => $request->date,
            'category' => $request->category,
            'particular' => $request->particular,
            'cash_in' => $request->cash_in,
            'cash_out' => $request->cash_out,
            'balance' => PettyCash::latest()->first() ? PettyCash::latest()->first()->balance + $request->cash_in - $request->cash_out : $request->cash_in - $request->cash_out,
        ]);

        return redirect()->route('pettycash.index');
    }

    public function edit($id)
    {
        $transaction = PettyCash::findOrFail($id);
        return view('pettycash.edit', compact('transaction'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'category' => 'required|in:CASH ON HAND,KITCHEN EXP,WATER,FUEL,ELECTRICITY,PRODUCTION SUPPLIES,REPAIR AND MAINTENANCE,FIXED ASSETS,WAGES,ADVANCE,SALARY,OFFICE EXP,STATIONERY,ADVANCE RETURN,PERFORMANCE APPRAISAL,ADVANCE AMOUNT,CURRENT ASSETS',
            'particular' => 'required|string|max:255',
            'cash_in' => 'required|numeric',
            'cash_out' => 'required|numeric',
        ]);

        $transaction = PettyCash::findOrFail($id);
        $transaction->update([
            'date' => $request->date,
            'category' => $request->category,
            'particular' => $request->particular,
            'cash_in' => $request->cash_in,
            'cash_out' => $request->cash_out,
            'balance' => PettyCash::latest()->first() ? PettyCash::latest()->first()->balance + $request->cash_in - $request->cash_out : $request->cash_in - $request->cash_out,
        ]);

        return redirect()->route('pettycash.index');
    }

    public function destroy($id)
    {
        PettyCash::findOrFail($id)->delete();
        return redirect()->route('pettycash.index');
    }

    public function downloadReport($year, $month)
    {
        $transactions = PettyCash::whereYear('date', $year)->whereMonth('date', $month)->get();
        // Generate CSV or PDF report logic here
        return response()->download(storage_path('reports/petty_cash_report.csv'));
    }
}
