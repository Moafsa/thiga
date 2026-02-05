<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Tenant;
use App\Services\WhatsAppAiService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MonitorClientActivity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clients:monitor-activity';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitora atividade dos clientes e dispara interações de IA (Retenção e Cobrança)';

    protected WhatsAppAiService $aiService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(WhatsAppAiService $aiService)
    {
        parent::__construct();
        $this->aiService = $aiService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Iniciando monitoramento de clientes...');

        $tenants = Tenant::where('is_active', true)->get();

        foreach ($tenants as $tenant) {
            $this->info("Processando Tenant: {$tenant->name} ({$tenant->id})");

            // Get settings or defaults
            $settings = $tenant->settings['ai_monitoring'] ?? [];
            $inactivityDays = $settings['inactivity_days'] ?? 30;
            $overdueDays = $settings['overdue_days'] ?? 3;
            $enabled = $settings['enabled'] ?? true; // Default to true or check if turned on

            if (!$enabled) {
                $this->info("Monitoramento desativado para este tenant.");
                continue;
            }

            $this->processInactivity($tenant, $inactivityDays);
            $this->processOverdueInvoices($tenant, $overdueDays);
            $this->processStalledProposals($tenant, 2); // Default to 2 days for proposal follow-up, or make configurable
        }

        $this->info('Monitoramento concluído.');
        return 0;
    }

    protected function processInactivity(Tenant $tenant, int $days)
    {
        $thresholdDate = Carbon::now()->subDays($days);

        // Find clients with no shipments since threshold
        // And who haven't been contacted recently (e.g., last 7 days) to avoid spam
        $clients = Client::where('tenant_id', $tenant->id)
            ->whereDoesntHave('shipments', function ($query) use ($thresholdDate) {
                $query->where('created_at', '>=', $thresholdDate);
            })
            // Optimization: Only check clients who have at least one shipment ever (real clients)
            ->has('shipments')
            // Ideally check a 'last_ai_contact_at' column, but for now assuming we don't spam if no interaction
            ->get();

        foreach ($clients as $client) {
            // Spam check (placeholder logic, assuming we track this somewhere)
            // if ($client->last_ai_contact_at && $client->last_ai_contact_at->diffInDays(now()) < 7) continue;

            $this->info("Cliente Inativo: {$client->name} (Última carga há +{$days} dias)");

            try {
                $this->aiService->sendRetentionMessage($tenant, $client, $days);
                // $client->update(['last_ai_contact_at' => now()]);
            } catch (\Exception $e) {
                Log::error("Erro ao enviar mensagem de retenção: " . $e->getMessage());
            }
        }
    }

    protected function processOverdueInvoices(Tenant $tenant, int $days)
    {
        $overdueLimit = Carbon::now()->subDays($days);

        $invoices = Invoice::where('tenant_id', $tenant->id)
            ->where('status', 'open') // Assuming 'open' status for unpaid
            ->where('due_date', '<=', $overdueLimit)
            // Check if client was already reminded recently
            ->with('client')
            ->get();

        foreach ($invoices as $invoice) {
            $client = $invoice->client;
            if (!$client)
                continue;

            $this->info("Fatura Vencida: {$client->name} (Vencim: {$invoice->due_date->format('d/m/Y')})");

            try {
                $this->aiService->sendCollectionMessage($tenant, $client, $invoice, $days);
                // timestamp contact
            } catch (\Exception $e) {
                Log::error("Erro ao enviar cobrança: " . $e->getMessage());
            }
        }
    }

    protected function processStalledProposals(Tenant $tenant, int $days)
    {
        $limitDate = Carbon::now()->subDays($days);

        $proposals = \App\Models\Proposal::where('tenant_id', $tenant->id)
            ->whereIn('status', ['draft', 'sent'])
            ->where('created_at', '<=', $limitDate)
            ->where('updated_at', '<=', $limitDate)
            ->with('client')
            ->get();

        foreach ($proposals as $proposal) {
            $client = $proposal->client;
            if (!$client)
                continue;

            $this->info("Proposta Parada: #{$proposal->proposal_number} (Cliente: {$client->name})");

            try {
                $this->aiService->sendProposalFollowUpMessage($tenant, $client, $proposal, $days);
            } catch (\Exception $e) {
                Log::error("Erro ao enviar follow-up de proposta: " . $e->getMessage());
            }
        }
    }
}
