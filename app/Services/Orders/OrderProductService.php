<?php

namespace App\Services\Orders;

use App\Models\Ingredient;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class OrderProductService
{
    /**
     * @param Product $product
     * @param int $quantity
     * 
     * @return void
     */
    public static function updateStock(Product $product, int $quantity): void
    {
        $product->ingredients->each(function (Ingredient $ingredient) use ($quantity) {
            OrderProductIngredientService::updateStock($ingredient, $quantity);
        });
    }

    public static function getLowStockIngredientByPercentToNotify(Product $product, int $percent = 50): Collection
    {
        return $product->ingredients->filter(function (Ingredient $ingredient) use ($percent) {
            return OrderProductIngredientService::shouldNotifyLowStock($ingredient, $percent);
        });
    }
}
