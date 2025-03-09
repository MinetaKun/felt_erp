<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderArtisanAssignment extends Model
{
    use HasFactory;

    protected $fillable = ['order_id', 'employee_id', 'assigned_quantity'];

    // Relationship with Order
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Relationship with Employee (Artisan)
    public function artisan()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
