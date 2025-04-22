<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RawMaterial extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'color',
        'quantity',
        'unit',
        'min_stock_level',
        'price_per_unit',
        'supplier',
        'description',
        'location'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'min_stock_level' => 'decimal:2',
        'price_per_unit' => 'decimal:2'
    ];

    // Check if stock is low
    public function isLowStock()
    {
        return $this->quantity <= $this->min_stock_level;
    }

    // Update stock quantity
    public function updateStock($quantity, $operation = 'add')
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
        if ($this->isLowStock()) {
            return 'low';
        } elseif ($this->quantity <= ($this->min_stock_level * 1.5)) {
            return 'warning';
        }
        return 'good';
    }
}
