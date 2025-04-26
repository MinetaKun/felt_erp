<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class ArtisansReport implements FromCollection, WithHeadings
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
        if ($this->type === 'productivity') {
            return $this->data->map(function ($artisan) {
                return [
                    'ID' => $artisan->id,
                    'Name' => $artisan->name,
                    'Department' => $artisan->department->name ?? 'N/A',
                    'Total Orders' => $artisan->orders->count(),
                    'Completed Orders' => $artisan->orders->where('status', 'completed')->count(),
                    'Pending Orders' => $artisan->orders->where('status', 'pending')->count(),
                    'Productivity Rate' => number_format(($artisan->orders->where('status', 'completed')->count() / max(1, $artisan->orders->count())) * 100, 2) . '%'
                ];
            });
        } else { // attendance
            return $this->data->map(function ($attendance) {
                return [
                    'ID' => $attendance->artisan->id,
                    'Name' => $attendance->artisan->name,
                    'Date' => $attendance->date,
                    'Check In' => $attendance->check_in,
                    'Check Out' => $attendance->check_out,
                    'Status' => $attendance->status,
                    'Remarks' => $attendance->remarks
                ];
            });
        }
    }

    public function headings(): array
    {
        if ($this->type === 'productivity') {
            return [
                'ID',
                'Name',
                'Department',
                'Total Orders',
                'Completed Orders',
                'Pending Orders',
                'Productivity Rate'
            ];
        } else { // attendance
            return [
                'ID',
                'Name',
                'Date',
                'Check In',
                'Check Out',
                'Status',
                'Remarks'
            ];
        }
    }

    public function downloadPDF($filename)
    {
        // TODO: Implement PDF generation
        throw new \Exception('PDF generation not implemented yet');
    }
}
