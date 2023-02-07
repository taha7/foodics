<?php

namespace Tests\Unit\Services\Orders;

use App\Models\Ingredient;
use App\Models\OpeningStock;
use App\Models\Product;
use App\Models\Stock;
use App\Services\Orders\OrderProductService;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class OrderProductServiceTest extends TestCase
{
    public function testGetLowStockIngredientByPercentToNotify()
    {
        $product = Product::make();

        $ingredient1 = Ingredient::make();
        $stock1 = Stock::make(['quantity' => 700, 'notified' => false]);
        $openingStock1 = OpeningStock::make(['quantity' => 1000]);
        $ingredient1->stock = $stock1;
        $ingredient1->openingStock = $openingStock1;

        $ingredient2 = Ingredient::make();
        $stock2 = Stock::make(['quantity' => 400, 'notified' => false]);
        $openingStock2 = OpeningStock::make(['quantity' => 1000]);
        $ingredient2->stock = $stock2;
        $ingredient2->openingStock = $openingStock2;

        $product->ingredients = Collection::make([$ingredient1, $ingredient2]);


        $this->assertCount(1, OrderProductService::getLowStockIngredientByPercentToNotify($product, 50));
    }
}
