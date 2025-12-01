<?php

namespace App\Events;

use App\Models\Route;
use App\Models\FiscalDocument;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MdfeIssuanceRequested
{
    use Dispatchable, SerializesModels;

    /**
     * The route for which MDF-e is being requested.
     */
    public Route $route;

    /**
     * The fiscal document created for this MDF-e.
     */
    public FiscalDocument $fiscalDocument;

    /**
     * Create a new event instance.
     */
    public function __construct(Route $route, FiscalDocument $fiscalDocument)
    {
        $this->route = $route;
        $this->fiscalDocument = $fiscalDocument;
    }
}






















