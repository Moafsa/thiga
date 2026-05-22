<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Route;
use App\Models\WhatsAppIntegration;
use App\Services\WuzApiService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CheckProactiveRouteCosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'route:check-costs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Proactively asks operations team via WhatsApp for missing route costs';

    /**
     * Execute the console command.
     */
    public function handle(WuzApiService $wuzApiService)
    {
        // Find routes that are completed or in progress, created in the last 48 hours, but have no route expenses
        $routes = Route::whereIn('status', ['in_progress', 'completed'])
            ->where('created_at', '>=', Carbon::now()->subHours(48))
            ->whereDoesntHave('routeExpenses')
            ->with(['tenant', 'user']) // 'user' is the operator who created it
            ->get();

        if ($routes->isEmpty()) {
            $this->info('No routes with missing costs found.');
            return;
        }

        foreach ($routes as $route) {
            $user = $route->user;

            // If the user who created it has no phone, we can't text them
            if (!$user || empty($user->phone)) {
                continue;
            }

            // Verify if tenant has an active WhatsApp Integration
            $integration = WhatsAppIntegration::where('tenant_id', $route->tenant_id)
                ->where('status', WhatsAppIntegration::STATUS_CONNECTED)
                ->first();

            if (!$integration || !$integration->getUserToken()) {
                continue;
            }

            $phone = $this->normalizePhone($user->phone);

            $message = "Olá {$user->name}!\n\n";
            $message .= "Notei que a *Rota #{$route->id}* está em andamento/concluída, mas ainda não foram lançados os custos de viagem (pedágio, chapa, combustível).\n\n";
            $message .= "Pode me enviar os valores agora? (ex: '200 de diesel e 50 de pedágio')";

            try {
                $wuzApiService->sendTextMessage($integration->getUserToken(), $phone, $message);
                Log::info("Proactive cost message sent for route {$route->id} to operator {$user->name}");
            } catch (\Exception $e) {
                Log::error("Failed to send proactive cost message for route {$route->id}: " . $e->getMessage());
            }
        }

        $this->info("Checked {$routes->count()} routes.");
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
