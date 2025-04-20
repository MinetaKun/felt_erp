<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Department extends Model
{
    use HasFactory;
    // Define the fillable fields for mass assignment
    protected $fillable = ['name'];

    // Define the relationship with artisans
    public function artisans()
    {
        return $this->hasMany(Artisan::class);
    }
}
