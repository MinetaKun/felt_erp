<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PettyCashCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'description',
        'is_active',
    ];

    public function transactions()
    {
        return $this->hasMany(PettyCashTransaction::class, 'category_id');
    }
}
