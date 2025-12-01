<?php

namespace App\Jobs;

use App\Models\Shipment;
use App\Models\FiscalDocument;
use App\Services\MittService;
use App\Services\ShipmentTimelineService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendCteToMittJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 120;
    public $backoff = [10, 30, 60];

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Shipment $shipment,
        public FiscalDocument $fiscalDocument
    ) {
        // Make job tenant-aware
        $this->onConnection('database');
    }

    /**
     * Execute the job.
     */
    public function handle(MittService $mittService, ShipmentTimelineService $timelineService): void
    {
        try {
            Log::info('Sending CT-e to Mitt', [
                'shipment_id' => $this->shipment->id,
                'fiscal_document_id' => $this->fiscalDocument->id,
            ]);

            // Prepare CT-e data for Mitt API
            $cteData = $this->prepareCteData();

            // Send to Mitt
            $response = $mittService->issueCte($cteData);

            // Update fiscal document with Mitt response
            $this->fiscalDocument->update([
                'mitt_id' => $response['id'] ?? null,
                'mitt_number' => $response['number'] ?? null,
                'access_key' => $response['access_key'] ?? null,
                'sent_at' => now(),
                'mitt_response' => $response,
                'status' => 'processing', // Will be updated via webhook when authorized
            ]);

            // Record CT-e issuance in timeline
            $timelineService->recordEvent(
                $this->shipment,
                'cte_issued',
                "CT-e enviado para emissão",
                null,
                null,
                null,
                ['cte_id' => $this->fiscalDocument->id, 'mitt_id' => $response['id'] ?? null]
            );

            Log::info('CT-e sent to Mitt successfully', [
                'fiscal_document_id' => $this->fiscalDocument->id,
                'mitt_id' => $response['id'] ?? null,
            ]);
        } catch (\Exception $e) {
            $attempt = $this->attempts();
            
            Log::error('Failed to send CT-e to Mitt', [
                'shipment_id' => $this->shipment->id,
                'fiscal_document_id' => $this->fiscalDocument->id,
                'attempt' => $attempt,
                'max_attempts' => $this->tries,
                'error' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'will_retry' => $attempt < $this->tries,
            ]);

            // Only update status to error if this is the last attempt
            if ($attempt >= $this->tries) {
                $this->fiscalDocument->update([
                    'status' => 'error',
                    'error_message' => 'Failed after ' . $this->tries . ' attempts: ' . $e->getMessage(),
                    'error_details' => [
                        'exception' => get_class($e),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'attempts' => $attempt,
                    ],
                ]);
            } else {
                // Keep status as processing for retry
                Log::info('CT-e job will retry', [
                    'fiscal_document_id' => $this->fiscalDocument->id,
                    'next_attempt' => $attempt + 1,
                ]);
            }

            throw $e;
        }
    }

    /**
     * Prepare CT-e data in Mitt API format
     */
    protected function prepareCteData(): array
    {
        $sender = $this->shipment->senderClient;
        $receiver = $this->shipment->receiverClient;

        return [
            'tenant_id' => $this->shipment->tenant_id,
            'shipment_id' => $this->shipment->id,
            'webhook_url' => url('/api/webhooks/mitt'),

            // Sender (remetente)
            'sender' => [
                'name' => $sender->name,
                'cnpj' => $sender->cnpj,
                'email' => $sender->email,
                'phone' => $sender->phone,
                'address' => $sender->address,
                'city' => $sender->city,
                'state' => $sender->state,
                'zip_code' => $sender->zip_code,
            ],

            // Receiver (destinatário)
            'receiver' => [
                'name' => $receiver->name,
                'cnpj' => $receiver->cnpj,
                'email' => $receiver->email,
                'phone' => $receiver->phone,
                'address' => $this->shipment->delivery_address,
                'city' => $this->shipment->delivery_city,
                'state' => $this->shipment->delivery_state,
                'zip_code' => $this->shipment->delivery_zip_code,
            ],

            // Goods data
            'goods' => [
                'description' => $this->shipment->title,
                'weight' => $this->shipment->weight,
                'volume' => $this->shipment->volume,
                'quantity' => $this->shipment->quantity,
                'value' => $this->shipment->value,
            ],

            // Transport data
            'transport' => [
                'pickup_address' => $this->shipment->pickup_address,
                'pickup_city' => $this->shipment->pickup_city,
                'pickup_state' => $this->shipment->pickup_state,
                'pickup_zip_code' => $this->shipment->pickup_zip_code,
                'pickup_date' => $this->shipment->pickup_date->format('Y-m-d'),
                'pickup_time' => $this->shipment->pickup_time->format('H:i:s'),
                'delivery_address' => $this->shipment->delivery_address,
                'delivery_city' => $this->shipment->delivery_city,
                'delivery_state' => $this->shipment->delivery_state,
                'delivery_zip_code' => $this->shipment->delivery_zip_code,
                'delivery_date' => $this->shipment->delivery_date->format('Y-m-d'),
                'delivery_time' => $this->shipment->delivery_time->format('H:i:s'),
            ],
        ];
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('CT-e job failed permanently', [
            'shipment_id' => $this->shipment->id,
            'fiscal_document_id' => $this->fiscalDocument->id,
            'error' => $exception->getMessage(),
        ]);

        $this->fiscalDocument->update([
            'status' => 'error',
            'error_message' => 'Failed after ' . $this->tries . ' attempts: ' . $exception->getMessage(),
        ]);
    }
}

