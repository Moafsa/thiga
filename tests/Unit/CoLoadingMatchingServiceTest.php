<?php

namespace Tests\Unit;

use App\Models\Plan;
use App\Models\Route;
use App\Models\RouteCapacityOffer;
use App\Models\Tenant;
use App\Models\Vehicle;
use App\Services\CoLoadingMatchingService;
use App\Services\MapsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoLoadingMatchingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $matchingService;
    protected $tenantA;
    protected $tenantB;
    protected $route;
    protected $vehicle;

    protected function setUp(): void
    {
        parent::setUp();

        // Instantiate MapsService mock
        $mapsServiceMock = $this->createMock(MapsService::class);
        $mapsServiceMock->method('geocode')->willReturn([
            'latitude' => -29.1678,
            'longitude' => -51.1794,
            'formatted_address' => 'Caxias do Sul, RS'
        ]);

        $this->matchingService = new CoLoadingMatchingService($mapsServiceMock);

        // Setup test database entities
        $plan = Plan::create([
            'name' => 'Plano Teste',
            'description' => 'Plano de teste',
            'price' => 99.90,
            'billing_cycle' => 'monthly',
            'features' => [],
            'limits' => [],
            'is_active' => true,
            'is_popular' => false,
            'sort_order' => 1,
        ]);

        $this->tenantA = Tenant::create([
            'name' => 'Transportadora A',
            'cnpj' => '11111111000100',
            'domain' => 'tenant-a',
            'plan_id' => $plan->id,
            'is_active' => true,
            'subscription_status' => 'active',
        ]);

        $this->tenantB = Tenant::create([
            'name' => 'Transportadora B',
            'cnpj' => '22222222000199',
            'domain' => 'tenant-b',
            'plan_id' => $plan->id,
            'is_active' => true,
            'subscription_status' => 'active',
        ]);

        // Create vehicle with payload capacities
        $this->vehicle = Vehicle::create([
            'tenant_id' => $this->tenantA->id,
            'plate' => 'ABC1234',
            'vehicle_type' => 'truck',
            'capacity_weight' => 5000,
            'capacity_volume' => 30.00,
            'is_active' => true,
        ]);

        // Create driver to satisfy routes table constraint
        $driver = \App\Models\Driver::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Motorista Teste',
            'email' => 'driver@example.com',
            'phone' => '+5511999999999',
            'phone_e164' => '5511999999999',
            'document' => '12345678909',
            'cnh_number' => '12345678901',
            'cnh_category' => 'D',
            'cnh_expiry_date' => '2028-12-31',
            'is_active' => true,
        ]);

        // Create route from Porto Alegre to São Paulo
        $this->route = Route::create([
            'tenant_id' => $this->tenantA->id,
            'driver_id' => $driver->id,
            'vehicle_id' => $this->vehicle->id,
            'name' => 'Rota Porto Alegre -> Sao Paulo',
            'scheduled_date' => '2026-05-28',
            'start_time' => '08:00',
            'status' => 'scheduled',
            'start_city' => 'Porto Alegre',
            'start_state' => 'RS',
            'start_latitude' => -30.0346,
            'start_longitude' => -51.2177,
            'end_latitude' => -23.5505,
            'end_longitude' => -46.6333,
        ]);
    }

    /**
     * Test Haversine distance formula correctness
     */
    public function testHaversineDistanceIsAccurate()
    {
        // Distance between Porto Alegre and São Paulo is approx 850 km
        $distance = $this->matchingService->haversine(
            -30.0346, -51.2177, // POA
            -23.5505, -46.6333  // SP
        );

        $this->assertGreaterThan(800, $distance);
        $this->assertLessThan(900, $distance);
    }

    /**
     * Test dynamic pricing math: base costs, detour compensation, platform splits
     */
    public function testDynamicPricingCalculation()
    {
        $offer = RouteCapacityOffer::create([
            'tenant_id' => $this->tenantA->id,
            'route_id' => $this->route->id,
            'offered_weight' => 2000,
            'offered_volume' => 10.0,
            'price_per_kg' => 2.00,  // R$ 2.00 / kg
            'price_per_m3' => 150.00, // R$ 150.00 / m3
            'min_price' => 200.00,
            'status' => 'active',
        ]);

        // Scenario 1: Weight pricing exceeds volume (500kg vs 1.5m3)
        // weight cost: 500 * 2 = 1000. volume cost: 1.5 * 150 = 225. Base = 1000.
        // detour: 50km * 3.50 = 175. Subtotal = 1175.
        // Platform fee: 1175 * 0.1 = 117.5. Final total = 1292.50.
        $pricing = $this->matchingService->calculateDynamicPrice($offer, 500, 1.5, 50.0);

        $this->assertEquals(1000.00, $pricing['amount_base']);
        $this->assertEquals(175.00, $pricing['amount_detour_cost']);
        $this->assertEquals(117.50, $pricing['amount_platform_fee']);
        $this->assertEquals(1292.50, $pricing['amount_final']);
        $this->assertEquals(1175.00, $pricing['carrier_payout']);
    }

    /**
     * Test route match compatibility checks physical weight and volume capacities
     */
    public function testMatchingFilterBlocksExcessCapacityRequests()
    {
        $offer = RouteCapacityOffer::create([
            'tenant_id' => $this->tenantA->id,
            'route_id' => $this->route->id,
            'offered_weight' => 1000, // Offer limited to 1000kg
            'offered_volume' => 5.0,
            'price_per_kg' => 1.00,
            'price_per_m3' => 50.00,
            'min_price' => 100.00,
            'status' => 'active',
        ]);

        // Request 1200kg (exceeds remaining route vehicle capacity offer)
        $cargoData = [
            'pickup_city' => 'Caxias do Sul',
            'pickup_state' => 'RS',
            'pickup_latitude' => -29.1678,
            'pickup_longitude' => -51.1794,
            'delivery_city' => 'Sao Paulo',
            'delivery_state' => 'SP',
            'delivery_latitude' => -23.5505,
            'delivery_longitude' => -46.6333,
            'weight' => 1200,
            'volume' => 2.0,
            'booker_tenant_id' => $this->tenantB->id,
        ];

        $matches = $this->matchingService->findMatchingRoutes($cargoData);
        $this->assertCount(0, $matches);
    }
}
