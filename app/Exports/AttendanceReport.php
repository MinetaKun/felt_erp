<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class AttendanceReport implements FromCollection, WithHeadings, WithStyles
{
    protected $attendances;
    protected $format;

    public function __construct($attendances, $format = 'csv')
    {
        $this->attendances = $attendances;
        $this->format = $format;
    }

    public function collection()
    {
        $data = [];
        foreach ($this->attendances as $attendance) {
            $attendanceable = $attendance->attendanceable;
            $name = $attendanceable ? $attendanceable->name : 'Deleted User';
            $type = $attendanceable ? class_basename($attendanceable) : 'Unknown';

            $data[] = [
                'ID' => $attendance->id,
                'Name' => $name,
                'Type' => $type,
                'Date' => $attendance->date->format('Y-m-d'),
                'Status' => $attendance->status,
                'Check In' => $attendance->check_in ? $attendance->check_in->format('H:i:s') : 'N/A',
                'Check Out' => $attendance->check_out ? $attendance->check_out->format('H:i:s') : 'N/A',
                'Total Hours' => $attendance->total_hours ?? 'N/A'
            ];
        }
        return new Collection($data);
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Type',
            'Date',
            'Status',
            'Check In',
            'Check Out',
            'Total Hours'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
