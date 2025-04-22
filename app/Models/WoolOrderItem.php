<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WoolOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'wool_order_id',
        'wool_type',
        'color',
        'quantity',
        'unit',
        'unit_price',
        'total_price',
        'specifications'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2'
    ];

    public function order()
    {
        return $this->belongsTo(WoolOrder::class);
    }

    public function stock()
    {
        return $this->hasOne(WoolStock::class);
    }
}
