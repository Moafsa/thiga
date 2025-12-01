<?php

namespace App\Events;

use App\Models\Shipment;
use App\Models\FiscalDocument;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CteIssuanceRequested
{
    use Dispatchable, SerializesModels;

    /**
     * The shipment for which CT-e is being requested.
     */
    public Shipment $shipment;

    /**
     * The fiscal document created for this CT-e.
     */
    public FiscalDocument $fiscalDocument;

    /**
     * Create a new event instance.
     */
    public function __construct(Shipment $shipment, FiscalDocument $fiscalDocument)
    {
        $this->shipment = $shipment;
        $this->fiscalDocument = $fiscalDocument;
    }
}






















