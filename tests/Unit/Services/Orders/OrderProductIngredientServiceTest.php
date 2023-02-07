<?php

namespace Tests\Unit\Services\Orders;

use App\Models\Ingredient;
use App\Models\OpeningStock;
use App\Models\Stock;
use App\Services\Orders\OrderProductIngredientService;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class OrderProductIngredientServiceTest extends TestCase
{
    public function testIngredientsAvailableWithAvailableIngredients()
    {
        $ingredient1 = Ingredient::make();
        $ingredient2 = Ingredient::make();
        $stock1 = Stock::make(['quantity' => 1000]);
        $stock2 = Stock::make(['quantity' => 1000]);
        $ingredient1->stock = $stock1;
        $ingredient2->stock = $stock2;
        $ingredient1->id = 1;
        $ingredient2->id = 2;


        $this->assertTrue(
            OrderProductIngredientService::ingredientsAvailable(Collection::make([$ingredient1, $ingredient2]), [1 => 1000, 2 => 1000])
        );
    }

    public function testFalseIngredientsAvailableWithLowIngredients()
    {
        $ingredient1 = Ingredient::make();
        $ingredient2 = Ingredient::make();
        $stock1 = Stock::make(['quantity' => 1000]);
        $stock2 = Stock::make(['quantity' => 1000]);
        $ingredient1->stock = $stock1;
        $ingredient2->stock = $stock2;
        $ingredient1->id = 1;
        $ingredient2->id = 2;


        $this->assertFalse(
            OrderProductIngredientService::ingredientsAvailable(Collection::make([$ingredient1, $ingredient2]), [1 => 2000, 2 => 2000])
        );
    }

    public function testShouldNotifyLowStockIfLowIngredient()
    {
        $ingredient = Ingredient::make();
        $stock = Stock::make(['quantity' => 400, 'notified' => false]);
        $openingStock = OpeningStock::make(['quantity' => 1000]);
        $ingredient->stock = $stock;
        $ingredient->openingStock = $openingStock;

        $this->assertTrue(OrderProductIngredientService::shouldNotifyLowStock($ingredient, 50));
    }

    public function testShouldNotifyLowStockIfIngredientAvailable()
    {
        $ingredient = Ingredient::make();
        $stock = Stock::make(['quantity' => 700, 'notified' => false]);
        $openingStock = OpeningStock::make(['quantity' => 1000]);
        $ingredient->stock = $stock;
        $ingredient->openingStock = $openingStock;

        $this->assertfalse(OrderProductIngredientService::shouldNotifyLowStock($ingredient, 50));
    }

    
}
