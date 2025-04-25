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
        'pan_number',
        'department_id',
        'profile_photo',
        'citizenship_photo',
        'status',
        'bank_account_number',
        'skills'
    ];

    protected $casts = [
        'skills' => 'array'
    ];

    public static $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:artisans,email',
        'phone_number' => 'required|string|max:20',
        'pan_number' => 'required|string|max:20|unique:artisans,pan_number',
        'bank_account_number' => 'required|string|max:50|unique:artisans,bank_account_number',
        'department_id' => 'required|exists:departments,id',
        'status' => 'sometimes|in:active,inactive',
        'skills' => 'nullable|array',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function attendances()
    {
        return $this->morphMany(Attendance::class, 'attendanceable');
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
