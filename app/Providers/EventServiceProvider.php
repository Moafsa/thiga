<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\CteIssuanceRequested;
use App\Events\MdfeIssuanceRequested;
use App\Events\OrderOrchestrated;
use App\Listeners\ProcessCteIssuance;
use App\Listeners\ProcessMdfeIssuance;
use App\Listeners\SendOrderOrchestrationNotification;
use App\Models\Shipment;
use App\Observers\ShipmentObserver;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        
        // Fiscal events
        CteIssuanceRequested::class => [
            ProcessCteIssuance::class,
        ],
        
        MdfeIssuanceRequested::class => [
            ProcessMdfeIssuance::class,
        ],

        OrderOrchestrated::class => [
            SendOrderOrchestrationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        // Register Shipment Observer
        Shipment::observe(ShipmentObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}


