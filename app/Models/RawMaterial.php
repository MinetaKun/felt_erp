<?php

namespace App\Models;

use App\Mail\LowStockAlert;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RawMaterial extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'category',
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

    // Get stock status
    public function getStockStatusAttribute()
    {
        if ($this->quantity <= $this->min_stock_level) {
            return 'low';
        } elseif ($this->quantity <= ($this->min_stock_level * 1.5)) {
            return 'warning';
        }
        return 'good';
    }

    // Check if stock is low
    public function isLowStock()
    {
        return $this->quantity <= $this->min_stock_level;
    }

    // Update stock quantity
    public function updateStock($quantity, $operation = 'add')
    {
        $previousQuantity = $this->quantity;

        if ($operation === 'add') {
            $this->quantity += $quantity;
        } else {
            $this->quantity -= $quantity;
        }
        $this->save();

        // Check if stock status changed to low
        if ($previousQuantity > $this->min_stock_level && $this->isLowStock()) {
            $this->checkStockStatus();
        }
    }

    // Check stock status and send notification if needed
    protected function checkStockStatus()
    {
        if ($this->isLowStock()) {
            $this->sendLowStockNotification();
        }
    }

    // Send low stock notification
    protected function sendLowStockNotification()
    {
        try {
            // Log the low stock alert
            Log::warning("Low stock alert for {$this->name}: Current quantity ({$this->quantity} {$this->unit}) is below minimum level ({$this->min_stock_level} {$this->unit})");

            // Send email notification
            $inventoryManagerEmail = config('inventory.manager_email', 'inventory@example.com');
            Mail::to($inventoryManagerEmail)->send(new LowStockAlert($this));
        } catch (\Exception $e) {
            Log::error("Failed to send low stock notification for {$this->name}: " . $e->getMessage());
        }
    }
}
