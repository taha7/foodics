<?php

namespace App\Observers;

use App\Models\OpeningStock;
use App\Models\Stock;

class OpeningStockObserver
{
    /**
     * Handle the OpeningStock "created" event.
     *
     * @param  \App\Models\OpeningStock  $openingStock
     * @return void
     */
    public function created(OpeningStock $openingStock)
    {
        Stock::create(
            $openingStock->only(['quantity', 'ingredient_id'])
        );
    }
}
