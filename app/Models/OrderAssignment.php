<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'artisan_id',
        'assigned_quantity',
        'completed_quantity',
        'approved_quantity',
        'rejected_quantity',
        'approved_at',
        'approved_by',
        'dispatched_at',
        'dispatched_by',
        'status',
        'rejection_reason',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'dispatched_at' => 'datetime',
    ];

    // Relationships
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function artisan()
    {
        return $this->belongsTo(Artisan::class);
    }

    // Update the relationship methods to match the column names
    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function dispatchedByUser()
    {
        return $this->belongsTo(User::class, 'dispatched_by');
    }

    // Calculate remaining quantity to be completed
    public function getRemainingQuantityAttribute()
    {
        return $this->assigned_quantity - $this->completed_quantity;
    }

    // Calculate completion percentage
    public function getCompletionPercentageAttribute()
    {
        if ($this->assigned_quantity == 0) return 0;
        return round(($this->completed_quantity / $this->assigned_quantity) * 100);
    }

    // Calculate approval percentage
    public function getApprovalPercentageAttribute()
    {
        if ($this->completed_quantity == 0) return 0;
        return round(($this->approved_quantity / $this->completed_quantity) * 100);
    }

    // Calculate rejection percentage
    public function getRejectionPercentageAttribute()
    {
        if ($this->completed_quantity == 0) return 0;
        return round(($this->rejected_quantity / $this->completed_quantity) * 100);
    }

    // Check if assignment is fully completed
    public function getIsCompletedAttribute()
    {
        return $this->completed_quantity >= $this->assigned_quantity;
    }

    // Check if assignment is fully approved
    public function getIsApprovedAttribute()
    {
        return $this->approved_quantity >= $this->completed_quantity;
    }

    // Check if assignment is dispatched
    public function getIsDispatchedAttribute()
    {
        return $this->status === 'dispatched';
    }
}
