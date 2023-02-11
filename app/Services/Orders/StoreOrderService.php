<?php

namespace App\Services\Orders;

use App\Events\OrderCreatedEvent;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StoreOrderService
{
    /**
     * @var SupportCollection
     */
    protected SupportCollection $productsById;

    /**
     * @param array $products
     */
    public function __construct(array $products)
    {
        $this->productsById = collect($products)
            ->keyBy('product_id')
            ->map(function (array $product) {
                return ['quantity' => $product['quantity']];
            });
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        $productIds = $this->productsById->keys();
        $productsQuery = Product::query()->whereIn('id', $productIds);

        if (count($productIds) !== $productsQuery->count()) {
            throw ValidationException::withMessages(['products' => 'One or more products not found']);
        }

        try {
            DB::beginTransaction();
    
            $products = $productsQuery->with(['ingredients.stock', 'ingredients.openingStock'])->lockForUpdate()->get();
            if (!$this->isOrderValid($products)) {
                throw ValidationException::withMessages(['products' => 'One or more products are not available please check the stock.']);
            }
    
            $order = Order::create(['user_id' => 1]);

            $order->orderProducts()->attach($this->productsById);
    
            $products->each(function (Product $product) {
                OrderProductService::updateStock($product, $this->productsById[$product->id]['quantity']);
            });
    
            DB::commit();
        } catch(\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        
        event(new OrderCreatedEvent($order->id));
    }

    /**
     * @param Collection $products
     * 
     * @return boolean
     */
    public function isOrderValid(Collection $products): bool
    {
        $ingredients = $products->reduce(function (Collection $acc, Product $product) {
            return $acc->merge($product->ingredients);
        }, Collection::make([]));

        return OrderProductIngredientService::ingredientsAvailable(
            $ingredients,
            $this->getIngredientsWithQuantities($products)
        );
    }

    /**
     * get object representation for each ingredient
     * and it's corresponding quantity needed for the order.
     * 
     * @param Collection $products
     * 
     * @return array<string, int>
     */
    protected function getIngredientsWithQuantities(Collection $products)
    {
        $ingredientsQuantities = [];

        foreach ($products as $product) {
            foreach ($product->ingredients as $ingredient) {
                if (!isset($ingredientsQuantities[$ingredient->id])) {
                    $ingredientsQuantities[$ingredient->id] = 0;
                }
                $ingredientsQuantities[$ingredient->id] = $ingredientsQuantities[$ingredient->id] + ($this->productsById[$product->id]['quantity'] * $ingredient->pivot->quantity);
            }
        }

        return $ingredientsQuantities;
    }
}
