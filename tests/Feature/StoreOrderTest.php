<?php

namespace Tests\Feature;

use App\Events\OrderCreatedEvent;
use App\Listeners\StockEmailNotifier;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class StoreOrderTest extends TestCase
{
    use RefreshDatabase;

    public function testItValidatesProducts()
    {
        $this->json('POST', '/api/orders', [])->assertJsonValidationErrors(['products']);
        $this->json('POST', '/api/orders', ['products' => ''])->assertJsonValidationErrors(['products']);
        $this->json('POST', '/api/orders', ['products' => ['']])->assertJsonValidationErrors(['products.0.quantity']);
        $this->json('POST', '/api/orders', ['products' => [['quantity' => 'ss']]])->assertJsonValidationErrors(['products.0.quantity']);
        $this->json('POST', '/api/orders', ['products' => [['quantity' => -1]]])->assertJsonValidationErrors(['products.0.quantity']);
        $this->json('POST', '/api/orders', ['products' => [['quantity' => 1]]])->assertJsonValidationErrors(['products.0.product_id']);
        $this->json('POST', '/api/orders', ['products' => [['quantity' => 1, 'product_id' => 1]]])->assertJsonValidationErrors(['products']);
    }

    public function testItValidatesThereIsNotEnoughQuantityOfIngredients()
    {
        $burger = Product::create(['name' => 'Burger']);
        $beef = Ingredient::create(['name' => 'Beef']);
        $beef->openingStock()->create(['quantity' => 100]); // available in stock
        $burger->ingredients()->attach($beef->id, ['quantity' => 150]); // What one burger needs

        $this->json('POST', '/api/orders', ['products' => [['quantity' => 1, 'product_id' => 1]]])->assertJsonValidationErrors(['products']);
    }

    public function testItValidatesIf2ProductsHaveTheSameIngredientButEnoughForBoth()
    {
        $burger = Product::create(['name' => 'Burger']);
        $newBurger = Product::create(['name' => 'Burger']);
        $beef = Ingredient::create(['name' => 'Beef']);
        $beef->openingStock()->create(['quantity' => 200]); // available in stock
        $burger->ingredients()->attach($beef->id, ['quantity' => 150]); // What one burger needs
        $newBurger->ingredients()->attach($beef->id, ['quantity' => 150]); // What one new burger needs

        $this->json('POST', '/api/orders', ['products' => [
            ['quantity' => 1, 'product_id' => 1], // burger
            ['quantity' => 1, 'product_id' => 2] // new burger
        ]])->assertJsonValidationErrors(['products']);
    }

    public function testItCanStoreOrder()
    {
        Event::fake([
            OrderCreatedEvent::class
        ]);

        $user = User::factory()->create();
        $burger = Product::create(['name' => 'Burger']);

        $beef = Ingredient::create(['name' => 'Beef']);
        $cheese = Ingredient::create(['name' => 'Cheese']);
        $onion = Ingredient::create(['name' => 'Onion']);

        $beef->openingStock()->create(['quantity' => 20 * 1000]);
        $cheese->openingStock()->create(['quantity' => 5 * 1000]);
        $onion->openingStock()->create(['quantity' => 1 * 1000]);

        $burger->ingredients()->attach($beef->id, ['quantity' => 150]);
        $burger->ingredients()->attach($cheese->id, ['quantity' => 30]);
        $burger->ingredients()->attach($onion->id, ['quantity' => 20]);

        $this->json('POST', '/api/orders', ['products' => [
            ['product_id' => 1, 'quantity' => 2]
        ]]);

        Event::assertDispatched(OrderCreatedEvent::class);
        Event::assertListening(
            OrderCreatedEvent::class,
            StockEmailNotifier::class
        );

        $this->assertDatabaseHas('orders', ['user_id' => $user->id]);
        $this->assertDatabaseHas('order_products', ['order_id' => 1, 'product_id' => $burger->id]);
        $this->assertDatabaseHas('stocks', ['ingredient_id' => $beef->id, 'quantity' => 19700]);
        $this->assertDatabaseHas('stocks', ['ingredient_id' => $cheese->id, 'quantity' => 4940]);
        $this->assertDatabaseHas('stocks', ['ingredient_id' => $onion->id, 'quantity' => 960]);
    }


}
