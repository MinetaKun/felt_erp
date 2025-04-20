<?php

namespace App\Imports;

use App\Models\PettyCashTransaction;
use App\Models\PettyCashCategory;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Carbon\Carbon;

class PettyCashTransactionsImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Find or create category
        $category = PettyCashCategory::firstOrCreate(
            ['name' => $row['category']],
            [
                'type' => $row['cash_in'] > 0 ? 'income' : 'expense',
                'description' => '',
                'is_active' => true
            ]
        );

        return new PettyCashTransaction([
            'transaction_date' => $this->transformDate($row['transaction_date']),
            'bs_date' => isset($row['bs_date']) ? $this->transformDate($row['bs_date']) : null,
            'pan_bill_no' => $row['pan_bill_no'] ?? null,
            'est_bill_no' => $row['est_bill_no'] ?? null,
            'category_id' => $category->id,
            'particulars' => $row['particulars'],
            'cash_in' => $row['cash_in'] ?? 0,
            'cash_out' => $row['cash_out'] ?? 0,
            'vat_amount' => $row['vat_amount'] ?? 0,
            'vat_percentage' => $row['vat_percentage'] ?? null,
            'is_vat_included' => isset($row['vat_included']) && strtolower($row['vat_included']) === 'yes',
            'reference_no' => $row['reference_no'] ?? null,
            'notes' => $row['notes'] ?? null,
        ]);
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'transaction_date' => 'required',
            'category' => 'required',
            'particulars' => 'required',
            '*.cash_in' => 'numeric|min:0',
            '*.cash_out' => 'numeric|min:0',
            '*.vat_amount' => 'nullable|numeric|min:0',
            '*.vat_percentage' => 'nullable|numeric|min:0|max:100',
        ];
    }

    /**
     * Transform a date value into a Carbon object.
     *
     * @param $value
     * @return \Carbon\Carbon|null
     */
    public function transformDate($value)
    {
        if (empty($value)) {
            return null;
        }

        try {
            try {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value));
            } catch (\Exception $e) {
                return Carbon::parse($value);
            }
        } catch (\Exception $e) {
            return Carbon::parse($value);
        }
    }
}
