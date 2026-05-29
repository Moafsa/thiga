<?php

namespace App\Jobs;

use App\Models\Shipment;
use App\Models\Tenant;
use App\Services\WhatsAppNotificationService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AlertOverdueShipmentsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 120;

    public function handle(WhatsAppNotificationService $whatsApp): void
    {
        Log::info('[AlertOverdueShipments] Starting overdue shipment check.');

        $overdueShipments = Shipment::query()
            ->whereNotIn('status', ['delivered', 'cancelled'])
            ->whereNotNull('delivery_date')
            ->where('delivery_date', '<', Carbon::today())
            ->with(['tenant', 'senderClient', 'driver'])
            ->get();

        if ($overdueShipments->isEmpty()) {
            Log::info('[AlertOverdueShipments] No overdue shipments found.');
            return;
        }

        // Group by tenant to send one summary per tenant
        $byTenant = $overdueShipments->groupBy('tenant_id');

        foreach ($byTenant as $tenantId => $shipments) {
            $tenant = $shipments->first()->tenant;

            if (!$tenant) {
                continue;
            }

            // Find admin users with phone
            $adminUsers = $tenant->users()
                ->whereNotNull('phone')
                ->where('is_active', true)
                ->whereHas('roles', fn($q) => $q->whereIn('name', ['Admin Tenant', 'Operacional']))
                ->limit(3)
                ->get();

            if ($adminUsers->isEmpty()) {
                Log::warning("[AlertOverdueShipments] No admin users with phone for tenant {$tenantId}");
                continue;
            }

            $count    = $shipments->count();
            $listText = $shipments->take(5)->map(fn($s) =>
                "• {$s->tracking_number} — {$s->title} (vencida em {$s->delivery_date->format('d/m')})"
            )->join("\n");

            $message = "⚠️ *Alerta TMS LOG* — {$count} carga(s) atrasada(s):\n\n{$listText}"
                . ($count > 5 ? "\n...e mais " . ($count - 5) . " outras." : "")
                . "\n\nAcesse o painel para tomar ação: " . config('app.url') . "/shipments";

            foreach ($adminUsers as $user) {
                try {
                    $whatsApp->sendMessage($tenant, $user->phone, $message);
                    Log::info("[AlertOverdueShipments] Alert sent to {$user->phone} for tenant {$tenantId}");
                } catch (\Throwable $e) {
                    Log::error("[AlertOverdueShipments] Failed to send alert to {$user->phone}: {$e->getMessage()}");
                }
            }
        }

        Log::info("[AlertOverdueShipments] Finished. Checked {$overdueShipments->count()} overdue shipments across {$byTenant->count()} tenants.");
    }
}
