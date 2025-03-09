<?php

// app/Models/PettyCash.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PettyCash extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'pan_bill',
        'est_bill',
        'category',
        'particular',
        'cash_in',
        'cash_out',
        'balance'
    ];

    // Optional: Define a mutator to calculate balance after each transaction
    public static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            $latestBalance = PettyCash::latest()->first();
            $model->balance = $latestBalance ? $latestBalance->balance + $model->cash_in - $model->cash_out : $model->cash_in - $model->cash_out;
            $model->save();
        });
    }
}
