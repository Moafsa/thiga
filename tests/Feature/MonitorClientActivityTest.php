<?php

namespace Tests\Feature;

use App\Console\Commands\MonitorClientActivity;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Proposal;
use App\Models\Shipment;
use App\Models\Tenant;
use App\Services\WhatsAppAiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Mockery;
use Tests\TestCase;

class MonitorClientActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_monitor_clients_command()
    {
        // 1. Setup Data
        $tenant = Tenant::factory()->create([
            'is_active' => true,
            'settings' => [
                'ai_monitoring' => [
                    'enabled' => true,
                    'inactivity_days' => 30,
                    'overdue_days' => 3
                ]
            ]
        ]);

        // Client 1: Inactive (No shipments for 31 days)
        $inactiveClient = Client::factory()->create(['tenant_id' => $tenant->id, 'phone' => '5511999999999']);
        Shipment::factory()->create([
            'tenant_id' => $tenant->id,
            'sender_client_id' => $inactiveClient->id,
            'created_at' => now()->subDays(31)
        ]);

        // Client 2: Overdue Invoice (Due 4 days ago)
        $overdueClient = Client::factory()->create(['tenant_id' => $tenant->id, 'phone' => '5511988888888']);
        Invoice::factory()->create([
            'tenant_id' => $tenant->id,
            'client_id' => $overdueClient->id,
            'status' => 'open',
            'due_date' => now()->subDays(4),
            'total_amount' => 100
        ]);

        // Client 3: Stalled Proposal (Created 3 days ago, still draft)
        $stalledClient = Client::factory()->create(['tenant_id' => $tenant->id, 'phone' => '5511977777777']);
        Proposal::factory()->create([
            'tenant_id' => $tenant->id,
            'client_id' => $stalledClient->id,
            'status' => 'draft',
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(3),
            'final_value' => 500
        ]);

        // 2. Mock AI Service
        $mockAiService = Mockery::mock(WhatsAppAiService::class);

        $mockAiService->shouldReceive('sendRetentionMessage')
            ->once()
            ->with(Mockery::on(fn($t) => $t->id === $tenant->id), Mockery::on(fn($c) => $c->id === $inactiveClient->id), 30);

        $mockAiService->shouldReceive('sendCollectionMessage')
            ->once()
            ->with(Mockery::on(fn($t) => $t->id === $tenant->id), Mockery::on(fn($c) => $c->id === $overdueClient->id), Mockery::any(), 3);

        $mockAiService->shouldReceive('sendProposalFollowUpMessage')
            ->once()
            ->with(Mockery::on(fn($t) => $t->id === $tenant->id), Mockery::on(fn($c) => $c->id === $stalledClient->id), Mockery::any(), 2);

        $this->app->instance(WhatsAppAiService::class, $mockAiService);

        // 3. Run Command
        $this->artisan('clients:monitor-activity')
            ->expectsOutput('Iniciando monitoramento de clientes...')
            ->expectsOutput("Processando Tenant: {$tenant->name} ({$tenant->id})")
            ->expectsOutput("Cliente Inativo: {$inactiveClient->name} (Última carga há +30 dias)")
            ->expectsOutput("Fatura Vencida: {$overdueClient->name} (Vencim: " . now()->subDays(4)->format('d/m/Y') . ")")
            ->expectsOutput("Proposta Parada: #" . Proposal::where('client_id', $stalledClient->id)->first()->proposal_number . " (Cliente: {$stalledClient->name})")
            ->assertExitCode(0);
    }
}
