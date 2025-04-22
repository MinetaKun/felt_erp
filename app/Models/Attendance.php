<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    // Define the fillable fields for mass assignment
    protected $table = 'attendance';
    protected $fillable = [
        'date',
        'check_in',
        'check_out',
        'status',
        'remarks',
        'attendanceable_id',
        'attendanceable_type'
    ];

    protected $casts = [
        'date' => 'date',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
    ];

    // Define the relationship with user
    public function attendanceable()
    {
        return $this->morphTo();
    }

    public function scopeForMonth($query, $year, $month)
    {
        return $query->whereYear('date', $year)
            ->whereMonth('date', $month);
    }

    public function scopeForDepartment($query, $departmentId)
    {
        return $query->whereHasMorph('attendanceable', [Artisan::class], function ($query) use ($departmentId) {
            $query->whereHas('department', function ($q) use ($departmentId) {
                $q->where('id', $departmentId);
            });
        });
    }
}
