<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class WoolReport implements FromCollection, WithHeadings
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
        if ($this->type === 'inventory') {
            return $this->data->map(function ($wool) {
                return [
                    'ID' => $wool->id,
                    'Type' => $wool->type,
                    'Color' => $wool->color,
                    'Supplier' => $wool->supplier->name ?? 'N/A',
                    'Quantity' => $wool->quantity,
                    'Unit' => $wool->unit,
                    'Price' => $wool->price,
                    'Total Value' => $wool->quantity * $wool->price,
                    'Last Updated' => $wool->updated_at->format('Y-m-d')
                ];
            });
        } else { // usage
            return $this->data->map(function ($usage) {
                return [
                    'ID' => $usage->id,
                    'Date' => $usage->created_at->format('Y-m-d'),
                    'Type' => $usage->type,
                    'Color' => $usage->color,
                    'Quantity Used' => $usage->quantity,
                    'Unit' => $usage->unit,
                    'Purpose' => $usage->purpose,
                    'Used By' => $usage->used_by
                ];
            });
        }
    }

    public function headings(): array
    {
        if ($this->type === 'inventory') {
            return [
                'ID',
                'Type',
                'Color',
                'Supplier',
                'Quantity',
                'Unit',
                'Price',
                'Total Value',
                'Last Updated'
            ];
        } else { // usage
            return [
                'ID',
                'Date',
                'Type',
                'Color',
                'Quantity Used',
                'Unit',
                'Purpose',
                'Used By'
            ];
        }
    }

    public function downloadPDF($filename)
    {
        // TODO: Implement PDF generation
        throw new \Exception('PDF generation not implemented yet');
    }
} 