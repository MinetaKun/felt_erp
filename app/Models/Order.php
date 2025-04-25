<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Order extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'order_id',
        'product_name',
        'size',
        'wool_color',
        'weight',
        'total_quantity',
        'due_date',
        'status',
        'wages_per_unit',
        'client_name',
        'notes',
        'product_photo',
    ];

    protected $casts = [
        'due_date' => 'date',
        'weight' => 'decimal:2',
        'wages_per_unit' => 'decimal:2',
    ];

    // Relationships
    public function assignments()
    {
        return $this->hasMany(OrderAssignment::class);
    }

    // Calculate the total assigned quantity
    public function getTotalAssignedQuantityAttribute()
    {
        return $this->assignments->sum('assigned_quantity');
    }

    // Calculate the total completed quantity
    public function getTotalCompletedQuantityAttribute()
    {
        return $this->assignments->sum('completed_quantity');
    }

    // Calculate the total approved quantity
    public function getTotalApprovedQuantityAttribute()
    {
        return $this->assignments->sum('approved_quantity');
    }

    // Calculate the remaining quantity to be assigned
    public function getRemainingQuantityAttribute()
    {
        return $this->total_quantity - $this->total_assigned_quantity;
    }

    // Calculate the total wages for this order
    public function getTotalWagesAttribute()
    {
        return $this->total_approved_quantity * $this->wages_per_unit;
    }

    // Check if the order is fully assigned
    public function getIsFullyAssignedAttribute()
    {
        return $this->total_assigned_quantity >= $this->total_quantity;
    }

    // Check if the order is fully completed
    public function getIsFullyCompletedAttribute()
    {
        return $this->total_completed_quantity >= $this->total_quantity;
    }

    // Check if the order is fully approved
    public function getIsFullyApprovedAttribute()
    {
        return $this->total_approved_quantity >= $this->total_quantity;
    }

    // Scope for filtering orders by status
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Scope for filtering orders by due date
    public function scopeDueBefore($query, $date)
    {
        return $query->where('due_date', '<=', $date);
    }
}
