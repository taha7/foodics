<?php

namespace App\Providers;

use App\Events\OrderCreatedEvent;
use App\Listeners\StockEmailNotifier;
use App\Models\OpeningStock;
use App\Observers\OpeningStockObserver;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        OrderCreatedEvent::class => [
            StockEmailNotifier::class
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
         OpeningStock::observe(OpeningStockObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
