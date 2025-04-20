<?php

namespace App\Exports;

use App\Models\PettyCashTransaction;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Http\Request;

class PettyCashTransactionsExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    public function query()
    {
        $query = PettyCashTransaction::with('category');

        // Apply filters
        if ($this->request->has('category_id')) {
            $query->byCategory($this->request->category_id);
        }

        if ($this->request->has('start_date') && $this->request->has('end_date')) {
            $query->dateRange($this->request->start_date, $this->request->end_date);
        } elseif ($this->request->has('start_date')) {
            $query->dateRange($this->request->start_date, null);
        } elseif ($this->request->has('end_date')) {
            $query->dateRange(null, $this->request->end_date);
        }

        if ($this->request->has('type')) {
            if ($this->request->type === 'income') {
                $query->income();
            } elseif ($this->request->type === 'expense') {
                $query->expense();
            }
        }

        return $query->orderBy('transaction_date', 'desc');
    }

    /**
     * @var PettyCashTransaction $transaction
     */
    public function map($transaction): array
    {
        return [
            $transaction->transaction_date->format('Y-m-d'),
            $transaction->bs_date ? $transaction->bs_date->format('Y-m-d') : '',
            $transaction->pan_bill_no,
            $transaction->est_bill_no,
            $transaction->category->name,
            $transaction->particulars,
            $transaction->cash_in > 0 ? $transaction->cash_in : '',
            $transaction->cash_out > 0 ? $transaction->cash_out : '',
            $transaction->vat_amount,
            $transaction->vat_percentage,
            $transaction->is_vat_included ? 'Yes' : 'No',
            $transaction->reference_no,
            $transaction->notes,
        ];
    }

    public function headings(): array
    {
        return [
            'Transaction Date',
            'BS Date',
            'PAN Bill No',
            'EST Bill No',
            'Category',
            'Particulars',
            'Cash In',
            'Cash Out',
            'VAT Amount',
            'VAT Percentage',
            'VAT Included',
            'Reference No',
            'Notes',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
