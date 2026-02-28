<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Proposal;
use App\Models\Tenant;
use App\Models\WhatsAppIntegration;
use App\Services\WuzApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

class AdvancedCalculatorTest extends TestCase
{
    use RefreshDatabase;

    protected $tenant;
    protected $domain;
    protected $wuzMock;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Tenant
        $this->tenant = Tenant::factory()->create([
            'domain' => 'test-calc',
            'is_active' => true,
        ]);
        $this->domain = $this->tenant->domain;

        // Create WhatsApp Integration
        WhatsAppIntegration::create([
            'tenant_id' => $this->tenant->id,
            'access_token' => 'fake-token',
            'is_active' => true,
            'status' => 'connected',
        ]);

        // Mock WuzApiService
        $this->wuzMock = Mockery::mock(WuzApiService::class);
        $this->wuzMock->shouldReceive('sendTextMessage')->andReturn(['status' => 'success']);
        $this->app->instance(WuzApiService::class, $this->wuzMock);
    }

    /** @test */
    public function it_can_send_otp()
    {
        $response = $this->postJson("/calculator/{$this->domain}/send-otp", [
            'client_name' => 'John Doe',
            'whatsapp' => '(11) 99999-9999',
            'email' => 'john@example.com'
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // Check if cached
        $phone = Client::normalizePhone('(11) 99999-9999');
        $key = "otp_auth_{$this->tenant->id}_{$phone}";
        $this->assertTrue(Cache::has($key));
    }

    /** @test */
    public function it_can_verify_otp()
    {
        $phone = Client::normalizePhone('(11) 88888-8888');
        $key = "otp_auth_{$this->tenant->id}_{$phone}";
        $code = '123456';
        Cache::put($key, $code, now()->addMinutes(10));

        $response = $this->postJson("/calculator/{$this->domain}/verify-otp", [
            'client_name' => 'Jane Doe',
            'whatsapp' => '(11) 88888-8888', // using clean phone in cache key logic, but input has mask? Controller cleans it.
            'email' => 'jane@example.com',
            'code' => $code
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // Check if cache cleared
        $this->assertFalse(Cache::has($key));
    }

    /** @test */
    public function it_calculates_freight_creates_client_and_proposal()
    {
        // 1. Setup Freight Table (Need a mock or real table)
        // For simplicity, we assume FreightCalculationService is mocked OR we create a table.
        // Let's create a simple table.
        \App\Models\FreightTable::factory()->create([
            'tenant_id' => $this->tenant->id,
            'origin_city' => 'Sao Paulo',
            'destination_city' => 'Rio de Janeiro',
            'price_per_kg' => 1.5,
            'ad_valorem_rate' => 0.5,
            'toll_rate' => 0,
            'min_freight' => 50,
        ]);

        // 2. Perform Request
        $data = [
            'client_name' => 'New Client',
            'whatsapp' => '(11) 77777-7777',
            'email' => 'new@client.com',
            'origin' => 'Sao Paulo',
            'destination' => 'Rio de Janeiro',
            'weight' => 100,
            'invoice_value' => 5000,
            'otp_verified' => true
        ];

        // Mock MapsService or assume internal logic works.
        // We'll mock FreightService to ensure predictable result without full DB setup of cities/CEPs if complex
        // But Controller uses injected service. Let's rely on basic DB setup if possible, or Mock.
        // Given complexity of FreightService, mocking it is safer for Controller test.
        $freightMock = Mockery::mock(\App\Services\FreightCalculationService::class);
        $freightMock->shouldReceive('calculate')->andReturn([
            'total' => 200.00,
            'breakdown' => ['freight_weight' => 150, 'ad_valorem' => 50, 'gris' => 0, 'toll' => 0]
        ]);
        $this->app->instance(\App\Services\FreightCalculationService::class, $freightMock);

        $response = $this->postJson("/calculator/{$this->domain}/calculate", $data);

        $response->assertStatus(200)
            ->assertJson(['success' => true, 'total' => '200,00']);

        // 3. Check Data Created
        // Client
        $this->assertDatabaseHas('clients', [
            'tenant_id' => $this->tenant->id,
            'phone' => '5511777777777', // Normalized
            'email' => 'new@client.com',
            'name' => 'New Client'
        ]);

        // Proposal
        $this->assertDatabaseHas('proposals', [
            'tenant_id' => $this->tenant->id,
            'client_name' => 'New Client',
            'destination_name' => 'Rio de Janeiro',
            'base_value' => 200.00
        ]);
    }
}
