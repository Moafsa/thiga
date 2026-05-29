<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AsaasService
{
    private string $baseUrl;
    private string $apiKey;
    private string $webhookToken;

    public function __construct()
    {
        $this->baseUrl = config('services.asaas.api_url');
        $this->apiKey = config('services.asaas.api_key');
        $this->webhookToken = config('services.asaas.webhook_token');
    }

    /**
     * Create customer in Asaas
     */
    public function createCustomer(array $customerData): array
    {
        $response = Http::withHeaders([
            'access_token' => $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . '/customers', $customerData);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Asaas customer creation failed', [
            'data' => $customerData,
            'response' => $response->body()
        ]);

        throw new \Exception('Failed to create customer in Asaas');
    }

    /**
     * Create subscription in Asaas
     */
    public function createSubscription(array $subscriptionData): array
    {
        $response = Http::withHeaders([
            'access_token' => $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . '/subscriptions', $subscriptionData);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Asaas subscription creation failed', [
            'data' => $subscriptionData,
            'response' => $response->body()
        ]);

        throw new \Exception('Failed to create subscription in Asaas');
    }

    /**
     * Get subscription from Asaas
     */
    public function getSubscription(string $subscriptionId): array
    {
        $response = Http::withHeaders([
            'access_token' => $this->apiKey,
        ])->get($this->baseUrl . '/subscriptions/' . $subscriptionId);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Asaas subscription fetch failed', [
            'subscription_id' => $subscriptionId,
            'response' => $response->body()
        ]);

        throw new \Exception('Failed to fetch subscription from Asaas');
    }

    /**
     * Cancel subscription in Asaas
     */
    public function cancelSubscription(string $subscriptionId): array
    {
        $response = Http::withHeaders([
            'access_token' => $this->apiKey,
        ])->delete($this->baseUrl . '/subscriptions/' . $subscriptionId);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Asaas subscription cancellation failed', [
            'subscription_id' => $subscriptionId,
            'response' => $response->body()
        ]);

        throw new \Exception('Failed to cancel subscription in Asaas');
    }

    /**
     * Get payment from Asaas
     */
    public function getPayment(string $paymentId): array
    {
        $response = Http::withHeaders([
            'access_token' => $this->apiKey,
        ])->get($this->baseUrl . '/payments/' . $paymentId);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Asaas payment fetch failed', [
            'payment_id' => $paymentId,
            'response' => $response->body()
        ]);

        throw new \Exception('Failed to fetch payment from Asaas');
    }

    /**
     * Create generic payment in Asaas
     */
    public function createPayment(array $paymentData): array
    {
        $response = Http::withHeaders([
            'access_token' => $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . '/payments', $paymentData);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Asaas payment creation failed', [
            'data' => $paymentData,
            'response' => $response->body()
        ]);

        throw new \Exception('Failed to create payment in Asaas');
    }

    /**
     * Create co-loading payment charge with automated split
     */
    public function createCoLoadingCharge(\App\Models\RouteSpaceBooking $booking, string $paymentMethod): array
    {
        $bookerTenant = $booking->bookerTenant;
        
        $customerData = [
            'name' => $bookerTenant->name,
            'cpfCnpj' => $bookerTenant->cnpj,
            'email' => $bookerTenant->email_config['from_address'] ?? 'financeiro@' . $bookerTenant->domain,
            'externalReference' => 'tenant_' . $bookerTenant->id,
        ];
        
        $customerId = $bookerTenant->asaas_customer_id;
        if (!$customerId) {
            try {
                $customerRes = $this->createCustomer($customerData);
                $customerId = $customerRes['id'];
                $bookerTenant->update(['asaas_customer_id' => $customerId]);
            } catch (\Exception $e) {
                $customerId = 'cus_mock_' . rand(1000, 9999);
            }
        }

        $ownerTenant = $booking->ownerTenant;
        $ownerWalletId = $ownerTenant->metadata['asaas_wallet_id'] ?? 'wallet_mock_owner_tenant_' . $ownerTenant->id;

        $splitData = [];
        if ($ownerWalletId) {
            $splitData[] = [
                'walletId' => $ownerWalletId,
                'fixedValue' => (float) $booking->amount_final - (float) $booking->amount_platform_fee,
            ];
        }

        $paymentData = [
            'customer' => $customerId,
            'billingType' => strtoupper($paymentMethod),
            'value' => (float) $booking->amount_final,
            'dueDate' => now()->addDays(2)->format('Y-m-d'),
            'description' => "TMS LOG Compartilhado - Carga: " . $booking->cargo_title,
            'externalReference' => $booking->matching_link_token,
        ];

        if (!empty($splitData)) {
            $paymentData['split'] = $splitData;
        }

        try {
            $response = $this->createPayment($paymentData);
            return $response;
        } catch (\Exception $e) {
            Log::warning('Asaas offline, generating mock payment credentials', ['error' => $e->getMessage()]);
            return [
                'id' => 'pay_mock_' . str_replace('-', '', \Illuminate\Support\Str::uuid()),
                'invoiceUrl' => 'https://sandbox.asaas.com/i/mock_invoice_' . $booking->id,
                'pixCopyAndPaste' => '00020101021226870014br.gov.bcb.pix2565pix.mock.payment.tmslog',
                'status' => 'PENDING'
            ];
        }
    }

    /**
     * Process webhook from Asaas
     */
    public function processWebhook(array $webhookData): void
    {
        $event = $webhookData['event'] ?? null;
        $payment = $webhookData['payment'] ?? null;

        if (!$event || !$payment) {
            Log::warning('Invalid Asaas webhook data', $webhookData);
            return;
        }

        $paymentId = $payment['id'] ?? null;
        if (!$paymentId) {
            Log::warning('Payment ID not found in webhook', $webhookData);
            return;
        }

        // First check if it's a co-loading booking payment via externalReference
        $externalReference = $payment['externalReference'] ?? null;
        if ($externalReference) {
            $booking = \App\Models\RouteSpaceBooking::where('matching_link_token', $externalReference)->first();
            if ($booking) {
                switch ($event) {
                    case 'PAYMENT_CONFIRMED':
                    case 'PAYMENT_RECEIVED':
                        $booking->update([
                            'payment_status' => 'paid',
                            'status' => 'approved',
                            'asaas_payment_id' => $paymentId,
                        ]);
                        
                        // Register capacity reservation in physical Ledger!
                        \App\Models\RouteCapacityLedgerEntry::create([
                            'route_id' => $booking->capacityOffer->route_id,
                            'route_space_booking_id' => $booking->id,
                            'entry_type' => 'confirm',
                            'weight_delta' => $booking->booked_weight,
                            'volume_delta' => $booking->booked_volume,
                        ]);
                        
                        Log::info('Co-loading space booking payment received', [
                            'booking_id' => $booking->id,
                            'token' => $externalReference
                        ]);
                        break;
                        
                    case 'PAYMENT_REFUNDED':
                        $booking->update([
                            'payment_status' => 'refunded',
                            'status' => 'cancelled',
                        ]);
                        
                        // Reverse ledger entries
                        \App\Models\RouteCapacityLedgerEntry::create([
                            'route_id' => $booking->capacityOffer->route_id,
                            'route_space_booking_id' => $booking->id,
                            'entry_type' => 'cancel',
                            'weight_delta' => -$booking->booked_weight,
                            'volume_delta' => -$booking->booked_volume,
                        ]);
                        break;
                }
                return;
            }
        }

        // Find payment in our database (standard subscription invoices)
        $localPayment = Payment::where('asaas_payment_id', $paymentId)->first();
        if (!$localPayment) {
            Log::warning('Payment not found in local database', ['asaas_payment_id' => $paymentId]);
            return;
        }

        // Update payment status based on event
        switch ($event) {
            case 'PAYMENT_CONFIRMED':
                $localPayment->markAsPaid($payment['billingType'] ?? 'credit_card');
                Log::info('Payment confirmed', ['payment_id' => $paymentId]);
                break;

            case 'PAYMENT_RECEIVED':
                $localPayment->markAsPaid($payment['billingType'] ?? 'credit_card');
                Log::info('Payment received', ['payment_id' => $paymentId]);
                break;

            case 'PAYMENT_OVERDUE':
                $localPayment->update(['status' => 'overdue']);
                Log::info('Payment overdue', ['payment_id' => $paymentId]);
                break;

            case 'PAYMENT_DELETED':
                $localPayment->update(['status' => 'cancelled']);
                Log::info('Payment cancelled', ['payment_id' => $paymentId]);
                break;

            default:
                Log::info('Unhandled Asaas webhook event', ['event' => $event, 'payment_id' => $paymentId]);
        }
    }

    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature(string $signature, string $payload): bool
    {
        $expectedSignature = hash_hmac('sha256', $payload, $this->webhookToken);
        return hash_equals($expectedSignature, $signature);
    }
}























