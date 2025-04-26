<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class InventoryReport implements FromCollection, WithHeadings, WithStyles
{
    protected $inventory;
    protected $format;

    public function __construct($inventory, $format = 'csv')
    {
        $this->inventory = $inventory;
        $this->format = $format;
    }

    public function collection()
    {
        $data = [];
        foreach ($this->inventory as $item) {
            $data[] = [
                'Item ID' => $item->id,
                'Name' => $item->name,
                'Category' => $item->category,
                'Quantity' => $item->quantity,
                'Unit' => $item->unit,
                'Minimum Stock' => $item->minimum_stock,
                'Last Updated' => $item->updated_at->format('Y-m-d H:i:s')
            ];
        }
        return new Collection($data);
    }

    public function headings(): array
    {
        return [
            'Item ID',
            'Name',
            'Category',
            'Quantity',
            'Unit',
            'Minimum Stock',
            'Last Updated'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
