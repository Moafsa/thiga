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

        // Find payment in our database
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























