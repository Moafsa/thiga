<?php

namespace Tests\Feature;

use App\Http\Livewire\SmartDispatch;
use App\Models\Driver;
use App\Models\Proposal;
use App\Models\Shipment;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SmartDispatchBackendTest extends TestCase
{
    use RefreshDatabase;

    protected $tenant;
    protected $user;
    protected $driver;
    protected $vehicle;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->actingAs($this->user);

        $this->vehicle = Vehicle::factory()->create([
            'tenant_id' => $this->tenant->id,
            'capacity_weight' => 1000,
            'is_active' => true,
            'status' => 'available'
        ]);

        $this->driver = Driver::factory()->create([
            'tenant_id' => $this->tenant->id,
            'is_active' => true
        ]);

        $this->driver->vehicles()->attach($this->vehicle->id, ['is_active' => true]);
    }

    /** @test */
    public function it_loads_vehicle_capacity_correctly()
    {
        Livewire::test(SmartDispatch::class)
            ->call('loadData')
            ->assertSet('allResources.0.capacity_weight', 1000.0);
    }

    /** @test */
    public function it_validates_capacity_limit()
    {
        // 1. Create a Shipment heavier than capacity
        $heavyShipment = Shipment::factory()->create([
            'tenant_id' => $this->tenant->id,
            'weight' => 1500, // > 1000
            'status' => 'pending'
        ]);

        $assignments = [
            $this->driver->id => ['ship_' . $heavyShipment->id]
        ];

        Livewire::test(SmartDispatch::class)
            ->call('saveDispatch', $assignments)
            ->assertDispatchedBrowserEvent('show-toast', ['type' => 'error']);

        // Assert no route created
        $this->assertDatabaseCount('routes', 0);
    }

    /** @test */
    public function it_converts_proposal_and_creates_route()
    {
        // 1. Create Accepted Proposal
        $proposal = Proposal::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => 'accepted',
            'weight' => 500,
            'final_value' => 1000
        ]);

        $assignments = [
            $this->driver->id => ['prop_' . $proposal->id]
        ];

        Livewire::test(SmartDispatch::class)
            ->call('saveDispatch', $assignments)
            ->assertDispatchedBrowserEvent('show-toast', ['type' => 'success']);

        // Assertions
        $this->assertDatabaseHas('proposals', [
            'id' => $proposal->id,
            'status' => 'converted'
        ]);

        $this->assertDatabaseHas('routes', [
            'driver_id' => $this->driver->id
        ]);

        // Assert Shipment created from Proposal
        $this->assertDatabaseHas('shipments', [
            'route_id' => \App\Models\Route::first()->id,
            'weight' => 500,
            'value' => 1000,
            'metadata' => json_encode(['proposal_id' => $proposal->id])
        ]);
    }
}
