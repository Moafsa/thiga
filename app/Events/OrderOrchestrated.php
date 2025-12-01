<?php

namespace App\Events;

use App\Models\Client;
use App\Models\Proposal;
use App\Models\Route;
use App\Models\Shipment;
use App\Models\Tenant;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderOrchestrated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Tenant $tenant,
        public Client $customer,
        public Proposal $proposal,
        public Shipment $shipment,
        public ?Route $route,
        public array $context = []
    ) {
    }
}















