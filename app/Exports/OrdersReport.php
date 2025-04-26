<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class OrdersReport implements FromCollection, WithHeadings, WithStyles
{
    protected $orders;
    protected $format;

    public function __construct($orders, $format = 'csv')
    {
        $this->orders = $orders;
        $this->format = $format;
    }

    public function collection()
    {
        $data = [];
        foreach ($this->orders as $order) {
            $data[] = [
                'Order ID' => $order->id,
                'Product Name' => $order->product_name,
                'Quantity' => $order->quantity,
                'Status' => ucfirst(str_replace('_', ' ', $order->status)),
                'Created At' => $order->created_at->format('Y-m-d H:i:s'),
                'Updated At' => $order->updated_at->format('Y-m-d H:i:s')
            ];
        }
        return new Collection($data);
    }

    public function headings(): array
    {
        return [
            'Order ID',
            'Product Name',
            'Quantity',
            'Status',
            'Created At',
            'Updated At'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
