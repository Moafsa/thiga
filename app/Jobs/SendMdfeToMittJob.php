<?php

namespace App\Jobs;

use App\Models\Route;
use App\Models\FiscalDocument;
use App\Services\MittService;
use App\Services\ShipmentTimelineService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendMdfeToMittJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 180;
    public $backoff = [10, 30, 60];

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Route $route,
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
            Log::info('Sending MDF-e to Mitt', [
                'route_id' => $this->route->id,
                'fiscal_document_id' => $this->fiscalDocument->id,
            ]);

            // Prepare MDF-e data for Mitt API
            $mdfeData = $this->prepareMdfeData();

            // Send to Mitt
            $response = $mittService->issueMdfe($mdfeData);

            // Update fiscal document with Mitt response
            $this->fiscalDocument->update([
                'mitt_id' => $response['id'] ?? null,
                'mitt_number' => $response['number'] ?? null,
                'access_key' => $response['access_key'] ?? null,
                'sent_at' => now(),
                'mitt_response' => $response,
                'status' => 'processing', // Will be updated via webhook when authorized
            ]);

            // Record MDF-e issuance in timeline for all route shipments
            foreach ($this->route->shipments as $shipment) {
                $timelineService->recordEvent(
                    $shipment,
                    'mdfe_issued',
                    "MDF-e enviado para emissão",
                    null,
                    null,
                    null,
                    ['mdfe_id' => $this->fiscalDocument->id, 'mitt_id' => $response['id'] ?? null]
                );
            }

            Log::info('MDF-e sent to Mitt successfully', [
                'fiscal_document_id' => $this->fiscalDocument->id,
                'mitt_id' => $response['id'] ?? null,
            ]);
        } catch (\Exception $e) {
            $attempt = $this->attempts();
            
            Log::error('Failed to send MDF-e to Mitt', [
                'route_id' => $this->route->id,
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
                Log::info('MDF-e job will retry', [
                    'fiscal_document_id' => $this->fiscalDocument->id,
                    'next_attempt' => $attempt + 1,
                ]);
            }

            throw $e;
        }
    }

    /**
     * Prepare MDF-e data in Mitt API format
     */
    protected function prepareMdfeData(): array
    {
        $shipments = $this->route->shipments()->with(['cte', 'senderClient', 'receiverClient'])->get();
        $driver = $this->route->driver;

        $cteIds = [];
        foreach ($shipments as $shipment) {
            $cte = $shipment->cte();
            if ($cte && $cte->isAuthorized() && $cte->access_key) {
                $cteIds[] = $cte->access_key;
            }
        }

        return [
            'tenant_id' => $this->route->tenant_id,
            'route_id' => $this->route->id,
            'webhook_url' => url('/api/webhooks/mitt'),

            // Driver and vehicle
            'driver' => [
                'name' => $driver->name,
                'document' => $driver->document,
                'cnh_number' => $driver->cnh_number,
                'cnh_category' => $driver->cnh_category,
            ],

            'vehicle' => [
                'plate' => $driver->vehicle_plate,
                'model' => $driver->vehicle_model,
                'color' => $driver->vehicle_color,
            ],

            // Route data
            'route' => [
                'name' => $this->route->name,
                'scheduled_date' => $this->route->scheduled_date->format('Y-m-d'),
                'start_time' => $this->route->start_time->format('H:i:s'),
                'start_latitude' => $this->route->start_latitude,
                'start_longitude' => $this->route->start_longitude,
                'estimated_distance' => $this->route->estimated_distance,
            ],

            // CT-e access keys (all CT-es in this route)
            'cte_access_keys' => $cteIds,

            // Shipments count
            'shipments_count' => $shipments->count(),
        ];
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('MDF-e job failed permanently', [
            'route_id' => $this->route->id,
            'fiscal_document_id' => $this->fiscalDocument->id,
            'error' => $exception->getMessage(),
        ]);

        $this->fiscalDocument->update([
            'status' => 'error',
            'error_message' => 'Failed after ' . $this->tries . ' attempts: ' . $exception->getMessage(),
        ]);
    }
}

