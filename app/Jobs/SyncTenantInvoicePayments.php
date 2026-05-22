<?php

namespace App\Jobs;

use App\Models\TenantInvoice;
use App\Services\AsaasService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncTenantInvoicePayments implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 300;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting tenant invoice payment sync job');

        try {
            $asaasService = app(AsaasService::class);

            // Get invoices that haven't been paid yet but have Asaas payment ID
            $invoices = TenantInvoice::where('status', '!=', 'paid')
                ->where('status', '!=', 'cancelled')
                ->whereNotNull('asaas_payment_id')
                ->get();

            Log::info("Syncing " . $invoices->count() . " tenant invoice payments");

            $syncedCount = 0;
            $errorCount = 0;

            foreach ($invoices as $invoice) {
                try {
                    // Get payment status from Asaas
                    $payment = $asaasService->getPayment($invoice->asaas_payment_id);

                    if (!$payment) {
                        Log::warning('Payment not found in Asaas', [
                            'payment_id' => $invoice->asaas_payment_id,
                        ]);
                        continue;
                    }

                    $status = $payment['status'] ?? null;

                    // Update invoice based on payment status
                    switch ($status) {
                        case 'RECEIVED':
                        case 'CONFIRMED':
                            $invoice->markAsPaid();
                            Log::info("Payment synced - marked as paid", [
                                'invoice_id' => $invoice->id,
                                'payment_id' => $invoice->asaas_payment_id,
                            ]);
                            $syncedCount++;
                            break;

                        case 'OVERDUE':
                            if ($invoice->status !== 'overdue') {
                                $invoice->markAsOverdue();
                                Log::info("Payment synced - marked as overdue", [
                                    'invoice_id' => $invoice->id,
                                ]);
                                $syncedCount++;
                            }
                            break;

                        case 'PENDING':
                            // No change needed
                            break;

                        default:
                            Log::debug("Payment status unchanged", [
                                'invoice_id' => $invoice->id,
                                'status' => $status,
                            ]);
                    }

                } catch (\Exception $e) {
                    Log::error("Failed to sync payment", [
                        'invoice_id' => $invoice->id,
                        'payment_id' => $invoice->asaas_payment_id,
                        'error' => $e->getMessage(),
                    ]);
                    $errorCount++;
                }
            }

            Log::info("Tenant invoice payment sync completed", [
                'synced' => $syncedCount,
                'errors' => $errorCount,
            ]);

        } catch (\Exception $e) {
            Log::error("Tenant invoice payment sync job failed", [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
