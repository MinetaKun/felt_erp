<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WoolStock extends Model
{
    use HasFactory;

    protected $table = 'wool_stock';

    protected $fillable = [
        'wool_order_item_id',
        'wool_type',
        'color',
        'quantity',
        'unit',
        'unit_price',
        'received_date',
        'notes'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'received_date' => 'date'
    ];

    public function orderItem()
    {
        return $this->belongsTo(WoolOrderItem::class);
    }
}
