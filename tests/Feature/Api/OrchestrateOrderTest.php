<?php

namespace Tests\Feature\Api;

use App\Models\FreightTable;
use App\Models\Plan;
use App\Models\Salesperson;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class OrchestrateOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_requires_authentication(): void
    {
        $payload = [
            'customer' => [
                'name' => 'ACME LTDA',
                'addresses' => [
                    [
                        'type' => 'pickup',
                        'street' => 'Rua Teste',
                        'city' => 'São Paulo',
                        'state' => 'SP',
                    ],
                ],
            ],
            'freight' => [
                'destination' => 'BELO HORIZONTE - MG',
            ],
            'shipment' => [
                'pickup' => [
                    'address' => 'Rua Teste',
                    'city' => 'São Paulo',
                    'state' => 'SP',
                    'date' => Carbon::now()->addDay()->toDateString(),
                ],
                'delivery' => [
                    'address' => 'Av Teste',
                    'city' => 'Belo Horizonte',
                    'state' => 'MG',
                ],
            ],
        ];

        $this->postJson('/api/mcp/workflows/order', $payload)
            ->assertStatus(401);
    }

    public function test_orchestrates_order_successfully(): void
    {
        $token = 'test-token-123';

        $plan = Plan::create([
            'name' => 'Teste',
            'description' => 'Plano de testes',
            'price' => 0,
            'billing_cycle' => 'monthly',
            'features' => [],
            'limits' => [],
            'is_active' => true,
            'is_popular' => false,
            'sort_order' => 1,
        ]);

        $tenant = Tenant::create([
            'name' => 'Tenant Test',
            'cnpj' => '12345678000100',
            'domain' => 'tenant-test',
            'plan_id' => $plan->id,
            'api_token' => hash('sha256', $token),
            'is_active' => true,
            'subscription_status' => 'active',
        ]);

        $user = User::create([
            'name' => 'Sales User',
            'email' => 'sales@example.com',
            'password' => bcrypt('password'),
            'tenant_id' => $tenant->id,
            'phone' => '+5511988887777',
            'is_active' => true,
        ]);

        $salesperson = Salesperson::create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'name' => 'Sales Person',
            'email' => 'sales@example.com',
            'phone' => '+5511988887777',
            'commission_rate' => 5,
            'max_discount_percentage' => 10,
            'settings' => [],
            'is_active' => true,
        ]);

        FreightTable::create([
            'tenant_id' => $tenant->id,
            'name' => 'BH',
            'description' => 'Tabela BH',
            'is_active' => true,
            'is_default' => true,
            'destination_type' => 'city',
            'destination_name' => 'BELO HORIZONTE - MG',
            'destination_state' => 'MG',
            'weight_0_30' => 120.00,
            'weight_31_50' => 150.00,
            'weight_51_70' => 180.00,
            'weight_71_100' => 250.00,
            'weight_over_100_rate' => 1.50,
            'ctrc_tax' => 10.00,
            'ad_valorem_rate' => 0.0040,
            'gris_rate' => 0.0030,
            'gris_minimum' => 8.70,
            'toll_per_100kg' => 12.95,
            'cubage_factor' => 300,
            'min_freight_rate_vs_nf' => 0.01,
        ]);

        $pickupDate = Carbon::now()->addDay()->toDateString();
        $deliveryDate = Carbon::now()->addDays(3)->toDateString();

        $payload = [
            'customer' => [
                'name' => 'ACME LTDA',
                'document' => '12.345.678/0001-99',
                'email' => 'contato@acme.com',
                'phone' => '+5511999999999',
                'salesperson_id' => $salesperson->id,
                'addresses' => [
                    [
                        'type' => 'pickup',
                        'name' => 'CD São Paulo',
                        'street' => 'Rua das Flores',
                        'number' => '123',
                        'complement' => 'Galpão',
                        'neighborhood' => 'Industrial',
                        'city' => 'São Paulo',
                        'state' => 'SP',
                        'zip_code' => '01000-000',
                        'is_default' => true,
                    ],
                ],
            ],
            'freight' => [
                'destination' => 'BELO HORIZONTE - MG',
                'weight' => 50,
                'cubage' => 0.2,
                'invoice_value' => 1500,
            ],
            'shipment' => [
                'title' => 'Envio teste',
                'pickup' => [
                    'address' => 'Rua das Flores, 123',
                    'city' => 'São Paulo',
                    'state' => 'SP',
                    'zip_code' => '01000-000',
                    'date' => $pickupDate,
                    'time' => '08:00',
                ],
                'delivery' => [
                    'address' => 'Av. Afonso Pena, 1000',
                    'city' => 'Belo Horizonte',
                    'state' => 'MG',
                    'zip_code' => '30130-003',
                    'date' => $deliveryDate,
                    'time' => '18:00',
                    'contact_name' => 'Fulano da Silva',
                    'contact_phone' => '+5531999999999',
                ],
                'items' => [
                    [
                        'description' => 'Caixas',
                        'quantity' => 10,
                        'weight' => 5,
                        'volume' => 0.01,
                        'value' => 100,
                    ],
                ],
                'notes' => 'Agendar com antecedência.',
            ],
            'proposal' => [
                'title' => 'Proposta BH',
                'valid_until' => Carbon::now()->addDays(15)->toDateString(),
            ],
            'route' => [
                'auto_assign' => false,
            ],
            'notifications' => [
                'send_whatsapp' => true,
                'customer_phone' => '+5531999999999',
            ],
            'metadata' => [
                'source' => 'phpunit',
            ],
        ];

        $response = $this->withHeaders([
            'X-Tenant-Token' => $token,
        ])->postJson('/api/mcp/workflows/order', $payload);

        $response->assertStatus(201);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.customer.name', 'ACME LTDA');
        $response->assertJsonPath('data.proposal.status', 'draft');
        $response->assertJsonPath('data.shipment.status', 'pending');
        $response->assertJsonPath('data.notifications.whatsapp_enqueued', true);
    }
}

