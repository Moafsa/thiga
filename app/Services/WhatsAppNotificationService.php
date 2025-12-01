<?php

namespace App\Services;

use App\Models\Shipment;
use App\Models\Tenant;
use App\Models\WhatsAppIntegration;
use App\Services\WuzApiService;
use Illuminate\Support\Facades\Log;

class WhatsAppNotificationService
{
    protected WuzApiService $wuzApiService;

    public function __construct(WuzApiService $wuzApiService)
    {
        $this->wuzApiService = $wuzApiService;
    }

    /**
     * Notify client about status change
     * 
     * @param Shipment $shipment
     * @param string $oldStatus
     * @param string $newStatus
     * @return bool
     */
    public function notifyStatusChange(Shipment $shipment, string $oldStatus, string $newStatus): bool
    {
        try {
            $receiverClient = $shipment->receiverClient;
            if (!$receiverClient || !$receiverClient->phone) {
                Log::warning('Cannot notify: receiver client has no phone', [
                    'shipment_id' => $shipment->id,
                ]);
                return false;
            }

            $tenant = $shipment->tenant;
            $integration = $this->getWhatsAppIntegration($tenant);
            if (!$integration) {
                Log::warning('WhatsApp integration not found for tenant', [
                    'tenant_id' => $tenant->id,
                ]);
                return false;
            }

            $message = $this->buildStatusChangeMessage($shipment, $oldStatus, $newStatus);
            $userToken = $integration->getUserToken();

            if (!$userToken) {
                Log::warning('WhatsApp user token not available', [
                    'integration_id' => $integration->id,
                ]);
                return false;
            }

            $phone = $this->formatPhoneNumber($receiverClient->phone);
            $this->wuzApiService->sendTextMessage($userToken, $phone, $message);

            Log::info('WhatsApp status notification sent', [
                'shipment_id' => $shipment->id,
                'status' => $newStatus,
                'phone' => $phone,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp status notification', [
                'shipment_id' => $shipment->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send delivery confirmation request
     * 
     * @param Shipment $shipment
     * @return bool
     */
    public function notifyDeliveryConfirmation(Shipment $shipment): bool
    {
        try {
            $receiverClient = $shipment->receiverClient;
            if (!$receiverClient || !$receiverClient->phone) {
                return false;
            }

            $tenant = $shipment->tenant;
            $integration = $this->getWhatsAppIntegration($tenant);
            if (!$integration) {
                return false;
            }

            $userToken = $integration->getUserToken();
            if (!$userToken) {
                return false;
            }

            $phone = $this->formatPhoneNumber($receiverClient->phone);
            $trackingUrl = route('tracking.show', $shipment->tracking_number);
            
            // Send image if delivery proof exists
            $deliveryProof = $shipment->deliveryProofs()->latest()->first();
            if ($deliveryProof && $deliveryProof->photo_url) {
                $caption = $this->buildDeliveryConfirmationMessage($shipment, $trackingUrl);
                $this->wuzApiService->sendImageMessage($userToken, $phone, $deliveryProof->photo_url, $caption);
            } else {
                $message = $this->buildDeliveryConfirmationMessage($shipment, $trackingUrl);
                $this->wuzApiService->sendTextMessage($userToken, $phone, $message);
            }

            Log::info('WhatsApp delivery confirmation sent', [
                'shipment_id' => $shipment->id,
                'phone' => $phone,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp delivery confirmation', [
                'shipment_id' => $shipment->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Notify about timeline update
     * 
     * @param Shipment $shipment
     * @param string $eventType
     * @param string $description
     * @return bool
     */
    public function notifyTimelineUpdate(Shipment $shipment, string $eventType, string $description): bool
    {
        try {
            $receiverClient = $shipment->receiverClient;
            if (!$receiverClient || !$receiverClient->phone) {
                return false;
            }

            $tenant = $shipment->tenant;
            $integration = $this->getWhatsAppIntegration($tenant);
            if (!$integration) {
                return false;
            }

            $userToken = $integration->getUserToken();
            if (!$userToken) {
                return false;
            }

            $phone = $this->formatPhoneNumber($receiverClient->phone);
            $trackingUrl = route('tracking.show', $shipment->tracking_number);
            
            $message = "📦 *Atualização da sua encomenda*\n\n";
            $message .= "Código de rastreamento: *{$shipment->tracking_number}*\n\n";
            $message .= "{$description}\n\n";
            $message .= "Acompanhe em tempo real: {$trackingUrl}";

            $this->wuzApiService->sendTextMessage($userToken, $phone, $message);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp timeline notification', [
                'shipment_id' => $shipment->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send tracking link
     * 
     * @param Shipment $shipment
     * @return bool
     */
    public function sendTrackingLink(Shipment $shipment): bool
    {
        try {
            $receiverClient = $shipment->receiverClient;
            if (!$receiverClient || !$receiverClient->phone) {
                return false;
            }

            $tenant = $shipment->tenant;
            $integration = $this->getWhatsAppIntegration($tenant);
            if (!$integration) {
                return false;
            }

            $userToken = $integration->getUserToken();
            if (!$userToken) {
                return false;
            }

            $phone = $this->formatPhoneNumber($receiverClient->phone);
            $trackingUrl = route('tracking.show', $shipment->tracking_number);
            
            $message = "📦 *Rastreamento de Encomenda*\n\n";
            $message .= "Olá! Sua encomenda foi cadastrada.\n\n";
            $message .= "Código de rastreamento: *{$shipment->tracking_number}*\n\n";
            $message .= "Acompanhe em tempo real:\n{$trackingUrl}";

            $this->wuzApiService->sendTextMessage($userToken, $phone, $message);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send tracking link', [
                'shipment_id' => $shipment->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Build status change message
     * 
     * @param Shipment $shipment
     * @param string $oldStatus
     * @param string $newStatus
     * @return string
     */
    protected function buildStatusChangeMessage(Shipment $shipment, string $oldStatus, string $newStatus): string
    {
        $statusMessages = [
            'pending' => '⏳ Sua encomenda está aguardando coleta.',
            'scheduled' => '📅 Sua encomenda foi agendada para coleta.',
            'picked_up' => '📦 Sua encomenda foi coletada e está em nosso centro de distribuição.',
            'in_transit' => '🚚 Sua encomenda está em trânsito.',
            'out_for_delivery' => '🚛 Sua encomenda saiu para entrega!',
            'delivered' => '✅ Sua encomenda foi entregue com sucesso!',
            'returned' => '↩️ Sua encomenda foi devolvida.',
            'cancelled' => '❌ Sua encomenda foi cancelada.',
        ];

        $message = "📦 *Atualização da sua encomenda*\n\n";
        $message .= "Código de rastreamento: *{$shipment->tracking_number}*\n\n";
        $message .= $statusMessages[$newStatus] ?? "Status atualizado para: {$newStatus}\n\n";
        
        $trackingUrl = route('tracking.show', $shipment->tracking_number);
        $message .= "Acompanhe em tempo real:\n{$trackingUrl}";

        return $message;
    }

    /**
     * Build delivery confirmation message
     * 
     * @param Shipment $shipment
     * @param string $trackingUrl
     * @return string
     */
    protected function buildDeliveryConfirmationMessage(Shipment $shipment, string $trackingUrl): string
    {
        $message = "✅ *Encomenda Entregue!*\n\n";
        $message .= "Código: *{$shipment->tracking_number}*\n\n";
        $message .= "Sua encomenda foi entregue com sucesso!\n\n";
        $message .= "Por favor, confirme o recebimento através do link:\n{$trackingUrl}\n\n";
        $message .= "Obrigado por escolher nossos serviços! 🎉";

        return $message;
    }

    /**
     * Get WhatsApp integration for tenant
     * 
     * @param Tenant $tenant
     * @return WhatsAppIntegration|null
     */
    protected function getWhatsAppIntegration(Tenant $tenant): ?WhatsAppIntegration
    {
        return WhatsAppIntegration::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Format phone number for WhatsApp (E.164 format)
     * 
     * @param string $phone
     * @return string
     */
    protected function formatPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // If starts with 0, remove it
        if (substr($phone, 0, 1) === '0') {
            $phone = substr($phone, 1);
        }
        
        // If doesn't start with country code, add Brazil code (55)
        if (strlen($phone) <= 10) {
            $phone = '55' . $phone;
        }
        
        return $phone;
    }
}











