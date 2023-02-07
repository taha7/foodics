<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = ['user_id'];

    public function orderProducts()
    {
        return $this->belongsToMany(Product::class, 'order_products')->withPivot(['quantity'])->withTimestamps();
    }
}
