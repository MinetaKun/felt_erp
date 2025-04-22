<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinishedProduct extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'color',
        'size',
        'quantity',
        'price',
        'order_id',
        'description',
        'location',
        'status'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2'
    ];

    // Relationships
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Update quantity
    public function updateQuantity($quantity, $operation = 'add')
    {
        if ($operation === 'add') {
            $this->quantity += $quantity;
        } else {
            $this->quantity -= $quantity;
        }
        $this->save();
    }

    // Check if product is available
    public function isAvailable()
    {
        return $this->status === 'available' && $this->quantity > 0;
    }

    // Get total value of the product
    public function getTotalValue()
    {
        return $this->quantity * $this->price;
    }

    // Update status
    public function updateStatus($status)
    {
        $this->status = $status;
        $this->save();
    }
}
