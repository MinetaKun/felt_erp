<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class FinancialReport implements FromCollection, WithHeadings
{
    protected $data;
    protected $type;

    public function __construct($data, $type)
    {
        $this->data = $data;
        $this->type = $type;
    }

    public function collection()
    {
        if ($this->type === 'revenue') {
            return $this->data->map(function ($order) {
                return [
                    'Order ID' => $order->id,
                    'Date' => $order->created_at->format('Y-m-d'),
                    'Customer' => $order->customer_name,
                    'Products' => $order->product->name,
                    'Quantity' => $order->quantity,
                    'Unit Price' => $order->unit_price,
                    'Total Amount' => $order->quantity * $order->unit_price,
                    'Status' => $order->status
                ];
            });
        } else { // expenses
            return $this->data->map(function ($expense) {
                return [
                    'Transaction ID' => $expense->id,
                    'Date' => $expense->created_at->format('Y-m-d'),
                    'Category' => $expense->category->name ?? 'N/A',
                    'Description' => $expense->description,
                    'Quantity' => $expense->quantity,
                    'Unit Price' => $expense->unit_price,
                    'Total Amount' => $expense->quantity * $expense->unit_price
                ];
            });
        }
    }

    public function headings(): array
    {
        if ($this->type === 'revenue') {
            return [
                'Order ID',
                'Date',
                'Customer',
                'Products',
                'Quantity',
                'Unit Price',
                'Total Amount',
                'Status'
            ];
        } else { // expenses
            return [
                'Transaction ID',
                'Date',
                'Category',
                'Description',
                'Quantity',
                'Unit Price',
                'Total Amount'
            ];
        }
    }

    public function downloadPDF($filename)
    {
        // TODO: Implement PDF generation
        throw new \Exception('PDF generation not implemented yet');
    }
}
