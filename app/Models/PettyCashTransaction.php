<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PettyCashTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'transaction_date',
        'bs_date',
        'pan_bill_no',
        'est_bill_no',
        'category_id',
        'particulars',
        'cash_in',
        'cash_out',
        'vat_amount',
        'vat_percentage',
        'is_vat_included',
        'reference_no',
        'notes',

    ];

    protected $casts = [
        'transaction_date' => 'date',
        'bs_date' => 'date',
        'cash_in' => 'decimal:2',
        'cash_out' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'is_vat_included' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(PettyCashCategory::class, 'category_id');
    }
}
