<?php

namespace App\Listeners;

use App\Events\OrderCreatedEvent;
use App\Mail\LowIngredientStockEmail;
use App\Models\Ingredient;
use App\Models\Order;
use App\Services\Orders\OrderProductService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Mail;

class StockEmailNotifier implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\OrderCreatedEvent  $event
     * @return void
     */
    public function handle(OrderCreatedEvent $event)
    {
        /**
         * @var Collection
         */
        $products = Order::find($event->orderId)->orderProducts()->with(['ingredients.stock', 'ingredients.openingStock'])->get();

        $ingredients = collect([]);

        foreach($products as $product) {
            $ingredients = $ingredients->merge(OrderProductService::getLowStockIngredientByPercentToNotify($product, 50));
        }

        $ingredients->unique('id')->each(function (Ingredient $ingredient) {
            Mail::to("merchant@example.com")->send(new LowIngredientStockEmail($ingredient));
            $ingredient->stock()->update(['notified' => true]);
        });
    }
}
