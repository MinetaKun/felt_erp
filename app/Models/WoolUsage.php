<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WoolUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'bill_number',
        'department',
        'color_number',
        'roll_count',
        'total_kg',
        'remarks',
        'usage_purpose',
        'supplier_id'
    ];

    protected $casts = [
        'date' => 'date',
        'roll_count' => 'integer',
        'total_kg' => 'decimal:2'
    ];

    public function supplier()
    {
        return $this->belongsTo(WoolSupplier::class);
    }
}
