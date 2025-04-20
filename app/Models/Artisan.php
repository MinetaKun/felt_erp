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
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function attendance()
    {
        return $this->hasMany(Attendance::class);
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
