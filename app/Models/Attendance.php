<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    // Define the fillable fields for mass assignment
    protected $table = 'attendance';
    protected $fillable = ['artisan_id', 'date', 'check_in', 'check_out', 'status'];

    // Define the relationship with artisan
    public function artisan()
    {
        return $this->belongsTo(Artisan::class);
    }
}
