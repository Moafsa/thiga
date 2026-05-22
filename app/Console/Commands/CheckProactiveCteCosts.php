<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Shipment;
use App\Models\WhatsAppIntegration;
use App\Services\WuzApiService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CheckProactiveCteCosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cte:check-costs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Proactively asks operations team via WhatsApp for missing individual CT-e costs';

    /**
     * Execute the console command.
     */
    public function handle(WuzApiService $wuzApiService)
    {
        // Find shipments (CT-es) that are delivered in the last 48 hours, but have no direct expenses
        $shipments = Shipment::whereIn('status', ['delivered'])
            ->where('delivered_at', '>=', Carbon::now()->subHours(48))
            ->whereDoesntHave('shipmentExpenses')
            ->with(['tenant'])
            ->get();

        if ($shipments->isEmpty()) {
            $this->info('No CT-es with missing costs found.');
            return;
        }

        // To avoid spamming, we will group shipments by tenant and ask the admin/commercial user
        $shipmentsByTenant = $shipments->groupBy('tenant_id');

        foreach ($shipmentsByTenant as $tenantId => $tenantShipments) {
            $integration = WhatsAppIntegration::where('tenant_id', $tenantId)
                ->where('status', WhatsAppIntegration::STATUS_CONNECTED)
                ->first();

            if (!$integration || !$integration->getUserToken()) {
                continue;
            }

            // Find an admin user to ask
            $adminUser = \App\Models\User::where('tenant_id', $tenantId)
                ->whereNotNull('phone')
                ->role(['Admin Tenant', 'Comercial'])
                ->first();

            if (!$adminUser) {
                continue;
            }

            $phone = $this->normalizePhone($adminUser->phone);

            // Group up to 5 CT-es per message
            $chunks = $tenantShipments->chunk(5);

            foreach ($chunks as $chunk) {
                $message = "Olá {$adminUser->name}!\n\n";
                $message .= "As seguintes Cargas/CT-es foram entregues recentemente, mas não possuem custos individuais lançados (ex: terceirização, coleta extra):\n\n";
                
                foreach ($chunk as $shipment) {
                    $ident = $shipment->cte_number ?: $shipment->tracking_number;
                    $message .= "- Carga/CT-e *{$ident}* (Destino: {$shipment->delivery_city})\n";
                }
                
                $message .= "\nSe houver custos, me responda com os valores (ex: 'CT-e 1234 teve 100 de taxa de dificuldade').";

                try {
                    $wuzApiService->sendTextMessage($integration->getUserToken(), $phone, $message);
                    Log::info("Proactive CTe cost message sent to admin {$adminUser->name}");
                } catch (\Exception $e) {
                    Log::error("Failed to send proactive CTe cost message: " . $e->getMessage());
                }
            }
        }

        $this->info("Checked {$shipments->count()} CT-es.");
    }

    protected function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);
        if (strlen($phone) === 11 && substr($phone, 0, 2) === '11') {
            $phone = '55' . $phone;
        }
        return $phone;
    }
}
