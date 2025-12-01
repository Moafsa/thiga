<?php

namespace App\Listeners;

use App\Events\CteIssuanceRequested;
use App\Jobs\SendCteToMittJob;
use Illuminate\Support\Facades\Log;

class ProcessCteIssuance
{
    /**
     * Handle the event.
     */
    public function handle(CteIssuanceRequested $event): void
    {
        Log::info('Processing CT-e issuance request', [
            'shipment_id' => $event->shipment->id,
            'fiscal_document_id' => $event->fiscalDocument->id,
        ]);

        // Update status to processing
        $event->fiscalDocument->update(['status' => 'processing']);

        // Dispatch job to send CT-e to Mitt
        SendCteToMittJob::dispatch($event->shipment, $event->fiscalDocument)
            ->onQueue('fiscal');
    }
}






















