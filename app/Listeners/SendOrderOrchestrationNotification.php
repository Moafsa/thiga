<?php

namespace App\Listeners;

use App\Events\OrderOrchestrated;
use App\Services\WhatsAppAiService;
use Illuminate\Support\Facades\Log;

class SendOrderOrchestrationNotification
{
    public function __construct(
        protected WhatsAppAiService $whatsAppAiService
    ) {
    }

    public function handle(OrderOrchestrated $event): void
    {
        $notifications = $event->context['notifications'] ?? [];

        if (!($notifications['whatsapp_enqueued'] ?? false)) {
            return;
        }

        try {
            $this->whatsAppAiService->sendOrderSummaryMessage(
                tenant: $event->tenant,
                customer: $event->customer,
                proposal: $event->proposal,
                shipment: $event->shipment,
                context: [
                    'notifications' => $notifications,
                    'calculation' => $event->context['calculation'] ?? [],
                    'metadata' => $event->context['metadata'] ?? [],
                ]
            );
        } catch (\Throwable $e) {
            Log::error('Failed to send WhatsApp order summary', [
                'tenant_id' => $event->tenant->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}















