<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Services\Orders\StoreOrderService;

class OrderController extends Controller
{
    /**
     * @param StoreOrderRequest $request
     * 
     * @return void
     */
    public function store(StoreOrderRequest $request)
    {
        return (new StoreOrderService($request->products))->handle();
    }
}
