<?php

namespace App\Listeners;

use App\Events\MdfeIssuanceRequested;
use App\Jobs\SendMdfeToMittJob;
use Illuminate\Support\Facades\Log;

class ProcessMdfeIssuance
{
    /**
     * Handle the event.
     */
    public function handle(MdfeIssuanceRequested $event): void
    {
        Log::info('Processing MDF-e issuance request', [
            'route_id' => $event->route->id,
            'fiscal_document_id' => $event->fiscalDocument->id,
        ]);

        // Update status to processing
        $event->fiscalDocument->update(['status' => 'processing']);

        // Dispatch job to send MDF-e to Mitt
        SendMdfeToMittJob::dispatch($event->route, $event->fiscalDocument)
            ->onQueue('fiscal');
    }
}






















