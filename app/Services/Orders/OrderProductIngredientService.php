<?php

namespace App\Services\Orders;

use App\Models\Ingredient;
use Illuminate\Database\Eloquent\Collection;

class OrderProductIngredientService
{
    /**
     * @param Collection $ingredients
     * @param array $ingredientsQuantities
     * 
     * @return boolean
     */
    public static function ingredientsAvailable(Collection $ingredients, array $ingredientsQuantities): bool
    {
        return $ingredients->every(function (Ingredient $ingredient) use ($ingredientsQuantities) {
            return $ingredient->stock->quantity - $ingredientsQuantities[$ingredient->id] >= 0;
        });
    }

    /**
     * @param Ingredient $ingredient
     * @param int $productQuantity
     * 
     * @return void
     */
    public static function updateStock(Ingredient $ingredient, int $productQuantity): void
    {
        $ingredient->stock()->decrement('quantity', $ingredient->pivot->quantity * $productQuantity);
    }

    /**
     * @param Ingredient $ingredient
     * @param int $percent
     * 
     * @return bool
     */
    public static function shouldNotifyLowStock(Ingredient $ingredient, int $percent = 50): bool
    {
        return $ingredient->stock->quantity <= (int) $ingredient->openingStock->quantity * $percent / 100 && !$ingredient->stock->notified;
    }
}
