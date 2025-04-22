<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WoolOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'wool_supplier_id',
        'order_number',
        'order_date',
        'expected_delivery_date',
        'total_amount',
        'status',
        'notes'
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'total_amount' => 'decimal:2'
    ];

    public function supplier()
    {
        return $this->belongsTo(WoolSupplier::class, 'wool_supplier_id');
    }

    public function items()
    {
        return $this->hasMany(WoolOrderItem::class);
    }
}
