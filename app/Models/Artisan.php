<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class Artisan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'basic_salary',
        'pan_number',
        'department_id',
        'profile_photo',
        'citizenship_photo',
        'status',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }

    public function orderAssignments()
    {
        return $this->hasMany(OrderAssignment::class);
    }

    public function updateStatus()
    {
        // Check if artisan has any active assignments
        $hasActiveAssignments = $this->orderAssignments()
            ->whereIn('status', ['pending', 'in_production', 'completed', 'approved'])
            ->exists();

        // Update status based on assignments
        $this->status = $hasActiveAssignments ? 'active' : 'inactive';
        $this->save();

        return $this->status;
    }

    public function resolveRouteBinding($value, $field = null)
    {
        $artisan = $this->where($field ?? 'id', $value)->first();

        if (!$artisan) {
            throw new ModelNotFoundException("Artisan with ID {$value} not found.");
        }

        return $artisan;
    }
}
