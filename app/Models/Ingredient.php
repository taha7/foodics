<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function openingStock()
    {
        return $this->hasOne(OpeningStock::class);
    }

    public function stock()
    {
        return $this->hasOne(stock::class);
    }
}
