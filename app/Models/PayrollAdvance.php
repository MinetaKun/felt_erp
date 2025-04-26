<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollAdvance extends Model
{
    use HasFactory;

    protected $fillable = [
        'artisan_id',
        'amount',
        'date',
        'notes'
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2'
    ];

    public function artisan()
    {
        return $this->belongsTo(Artisan::class);
    }
}
