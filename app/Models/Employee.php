<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    //
    protected $fillable = [
        'user_id',
        'employee_type',
        'department',
        'join_date',
        'skills',
        'citizenship_no',
        'citizenship_photo',
        'pan_no'
    ];

    protected $casts = [
        'skills' => 'array',
        'join_date' => 'date'
    ];

    // Relationship
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    // Accessor for service period
    public function getServicePeriodAttribute()
    {
        return $this->join_date->diffForHumans(null, true);
    }
    // Check if employee is an artisan
    public function isArtisan()
    {
        return $this->employee_type === 'artisan';
    }
}
