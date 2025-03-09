<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model {
    use HasFactory;

    protected $fillable = ['product_name', 'quantity', 'wool_color', 'size_cm', 'wage_per_piece', 'status'];

    // Relationship with OrderArtisanAssignment
    public function assignments() {
        return $this->hasMany(OrderArtisanAssignment::class);
    }
}
