<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\TenantInvoice;
use App\Models\SplitBilling;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class TenantInvoiceService
{
    /**
     * Generate monthly invoice for a tenant based on their subscription
     */
    public function generateMonthlyInvoice(Subscription $subscription): TenantInvoice
    {
        DB::beginTransaction();

        try {
            $tenant = $subscription->tenant;
            $plan = $subscription->plan;

            // Calculate split amount
            $splitAmount = $this->calculateSplitAmount($plan->price, $plan->split_percentage);
            $periodStart = now()->startOfMonth();
            $periodEnd = now()->endOfMonth();
            $dueDate = now()->addDays(10);

            // Create the invoice
            $invoice = TenantInvoice::create([
                'tenant_id' => $tenant->id,
                'subscription_id' => $subscription->id,
                'invoice_number' => TenantInvoice::generateInvoiceNumber(),
                'type' => 'plan_subscription',
                'base_amount' => $plan->price,
                'split_percentage' => $plan->split_percentage,
                'split_amount' => $splitAmount,
                'total_amount' => $plan->price,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'issue_date' => now(),
                'due_date' => $dueDate,
                'status' => 'draft',
                'metadata' => [
                    'plan_name' => $plan->name,
                    'billing_cycle' => $plan->billing_cycle,
                    'features' => $plan->features,
                    'limits' => $plan->limits,
                ],
            ]);

            // Record split billing for tracking
            SplitBilling::create([
                'tenant_id' => $tenant->id,
                'tenant_invoice_id' => $invoice->id,
                'reference_type' => 'plan_subscription',
                'reference_id' => $subscription->id,
                'base_amount' => $plan->price,
                'split_percentage' => $plan->split_percentage,
                'commission_amount' => $splitAmount,
                'status' => 'calculated',
                'calculation_date' => now()->toDateString(),
            ]);

            DB::commit();

            Log::info('Tenant invoice generated', [
                'invoice_id' => $invoice->id,
                'tenant_id' => $tenant->id,
                'invoice_number' => $invoice->invoice_number,
                'amount' => $plan->price,
                'commission' => $splitAmount,
            ]);

            return $invoice;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to generate tenant invoice', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Send invoice to Asaas (create payment in Asaas)
     */
    public function sendToAsaas(TenantInvoice $invoice): array
    {
        try {
            // Use superadmin's Asaas service to charge the tenant
            $asaasService = app(AsaasService::class);

            // Check tenant has Asaas customer ID
            if (!$invoice->tenant->asaas_customer_id) {
                throw new \Exception("Tenant does not have Asaas customer ID configured");
            }

            // Prepare payment data
            $paymentData = [
                'customer' => $invoice->tenant->asaas_customer_id,
                'billingType' => 'BOLETO',
                'value' => (float) $invoice->total_amount,
                'dueDate' => $invoice->due_date->format('Y-m-d'),
                'description' => "Plan: {$invoice->metadata['plan_name']}",
                'externalReference' => $invoice->invoice_number,
                'discount' => [
                    'type' => 'FIXED',
                    'value' => 0,
                ],
                'fine' => [
                    'type' => 'FIXED',
                    'value' => 0,
                ],
                'interest' => [
                    'type' => 'SIMPLE',
                    'value' => 0,
                ],
            ];

            // Create payment in Asaas
            $response = $asaasService->createPayment($paymentData);

            // Update invoice with Asaas reference
            $invoice->update([
                'asaas_payment_id' => $response['id'] ?? null,
                'status' => 'issued',
            ]);

            Log::info('Tenant invoice sent to Asaas', [
                'invoice_number' => $invoice->invoice_number,
                'asaas_payment_id' => $response['id'] ?? null,
            ]);

            return $response;

        } catch (\Exception $e) {
            Log::error('Failed to send invoice to Asaas', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Process payment webhook from Asaas
     * Called when superadmin receives payment from tenant
     */
    public function processPaymentWebhook(array $webhookData): void
    {
        try {
            $paymentId = $webhookData['payment']['id'] ?? null;
            if (!$paymentId) {
                Log::warning('Payment ID not found in webhook');
                return;
            }

            $tenantInvoice = TenantInvoice::where('asaas_payment_id', $paymentId)->first();
            if (!$tenantInvoice) {
                Log::warning('Tenant invoice not found for payment', [
                    'asaas_payment_id' => $paymentId,
                ]);
                return;
            }

            $event = $webhookData['event'] ?? null;

            DB::beginTransaction();

            try {
                switch ($event) {
                    case 'PAYMENT_CONFIRMED':
                    case 'PAYMENT_RECEIVED':
                        $tenantInvoice->markAsPaid();

                        // Update split billing
                        SplitBilling::where('tenant_invoice_id', $tenantInvoice->id)
                            ->update([
                                'status' => 'paid',
                                'payment_date' => now(),
                            ]);

                        Log::info('Tenant invoice marked as paid', [
                            'invoice_number' => $tenantInvoice->invoice_number,
                        ]);
                        break;

                    case 'PAYMENT_OVERDUE':
                        $tenantInvoice->markAsOverdue();
                        Log::info('Tenant invoice marked as overdue', [
                            'invoice_number' => $tenantInvoice->invoice_number,
                        ]);
                        break;

                    case 'PAYMENT_DELETED':
                        $tenantInvoice->cancel();
                        Log::info('Tenant invoice cancelled', [
                            'invoice_number' => $tenantInvoice->invoice_number,
                        ]);
                        break;

                    default:
                        Log::info('Unhandled webhook event', [
                            'event' => $event,
                            'payment_id' => $paymentId,
                        ]);
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Failed to process payment webhook', [
                'webhook_data' => $webhookData,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Calculate split amount
     */
    private function calculateSplitAmount(float $baseAmount, float $splitPercentage): float
    {
        return round($baseAmount * ($splitPercentage / 100), 2);
    }

    /**
     * Get total commission earned from a tenant
     */
    public function getTotalCommissionByTenant(Tenant $tenant, $monthYear = null): float
    {
        $query = SplitBilling::where('tenant_id', $tenant->id)
            ->where('status', 'paid');

        if ($monthYear) {
            $query->whereMonth('payment_date', '=', $monthYear->month)
                ->whereYear('payment_date', '=', $monthYear->year);
        }

        return (float) $query->sum('commission_amount');
    }

    /**
     * Get total billed amount across all invoices
     */
    public function getTotalBilledAmount(): float
    {
        return (float) TenantInvoice::where('status', 'paid')->sum('total_amount');
    }

    /**
     * Get total commission received
     */
    public function getTotalCommissionReceived(): float
    {
        return (float) TenantInvoice::where('status', 'paid')->sum('split_amount');
    }

    /**
     * Get invoices by status
     */
    public function getInvoicesByStatus(string $status, $limit = 10)
    {
        return TenantInvoice::where('status', $status)
            ->orderBy('due_date', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get overdue invoices
     */
    public function getOverdueInvoices()
    {
        return TenantInvoice::overdue()
            ->orderBy('due_date', 'asc')
            ->get();
    }

    /**
     * Send invoice reminders for due invoices
     */
    public function sendDueReminders(): int
    {
        $dueInvoices = TenantInvoice::where('status', 'issued')
            ->where('due_date', '<=', now()->addDays(3))
            ->where('due_date', '>', now())
            ->get();

        $count = 0;
        foreach ($dueInvoices as $invoice) {
            try {
                // TODO: Implement reminder notification
                $count++;
                Log::info('Reminder sent for invoice', [
                    'invoice_id' => $invoice->id,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send reminder', [
                    'invoice_id' => $invoice->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $count;
    }
}
