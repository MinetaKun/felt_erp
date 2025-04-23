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
        'size',
        'quantity',
        'price',
        'wage_per_unit',
        'description',
        'location',
        'status'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'wage_per_unit' => 'decimal:2'
    ];

    // Relationships
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Check if product is in stock
    public function isInStock()
    {
        return $this->quantity > 0;
    }

    // Check if product is low in stock
    public function isLowStock()
    {
        return $this->quantity > 0 && $this->quantity <= 10;
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

    // Get stock status
    public function getStockStatus()
    {
        if ($this->quantity === 0) {
            return 'out_of_stock';
        } elseif ($this->isLowStock()) {
            return 'low_stock';
        }
        return 'in_stock';
    }

    // Calculate total value
    public function getTotalValue()
    {
        return $this->quantity * $this->price;
    }

    // Calculate total wages
    public function getTotalWages()
    {
        return $this->quantity * $this->wage_per_unit;
    }

    // Update status
    public function updateStatus($status)
    {
        $this->status = $status;
        $this->save();
    }
}
