<?php

namespace Tests\Integration\Listeners;

use App\Events\OrderCreatedEvent;
use App\Listeners\StockEmailNotifier;
use App\Mail\LowIngredientStockEmail;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\Stock;
use App\Models\User;
use App\Services\Orders\StoreOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class StockEmailNotifierTest extends TestCase
{
    use RefreshDatabase;

    public function testItSendsAnEmailIfLowIngredientStock()
    {
        Mail::fake();
        
        // Order quantity that makes the ingredient stock low
        $this->createApplicationOrder(200, 150, 1);

        $event = new OrderCreatedEvent(1);
        $listener = new StockEmailNotifier();
        $listener->handle($event);

        Mail::assertSent(LowIngredientStockEmail::class);
    }

    public function testItNeverSendsAnEmailIfTheStock() {
        Mail::fake();
        
        // Order quantity that makes the ingredient stock low
        $this->createApplicationOrder(200, 150, 1);
        Stock::query()->where('ingredient_id', 1)->update(['notified' => true]);

        $event = new OrderCreatedEvent(1);
        $listener = new StockEmailNotifier();
        $listener->handle($event);

        Mail::assertNotSent(LowIngredientStockEmail::class);
    }

    public function assertNotSendTwiceForTheSameIngredient() {
        Mail::fake();
        
        // Order quantity that makes the ingredient stock low
        $this->createApplicationOrder(2000, 150, 1);

        $event = new OrderCreatedEvent(1);
        $listener = new StockEmailNotifier();
        $listener->handle($event);

        Mail::assertNotSent(LowIngredientStockEmail::class);
    }


    /**
     * @param int $openingStock
     * @param int $productIngredientQuantity
     * @param int $orderQuantity
     * 
     * @return void
     */
    protected function createApplicationOrder(int $openingStock = 200, $productIngredientQuantity = 150, $orderQuantity = 1, $productOptions = [], $ingredientOptions = [])
    {
        Event::fake([
            OrderCreatedEvent::class
        ]);

        User::factory()->create();

        $burger = Product::create(['name' => 'Burger']);

        $beef = Ingredient::create(['name' => 'Beef']);

        $beef->openingStock()->create(['quantity' => $openingStock]);

        $burger->ingredients()->attach($beef->id, ['quantity' => $productIngredientQuantity]);

        (new StoreOrderService(
            [
                ['product_id' => $burger->id, 'quantity' => $orderQuantity]
            ]
        ))->handle();
    }
}
