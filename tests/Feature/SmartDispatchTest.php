<?php

namespace Tests\Feature;

use App\Http\Livewire\SmartDispatch;
use App\Models\Driver;
use App\Models\Route;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SmartDispatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_renders_correctly()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(SmartDispatch::class)
            ->assertStatus(200);
    }

    public function test_it_loads_demands_and_resources()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create some shipments (demands)
        Shipment::factory()->count(3)->create([
            'status' => 'pending',
            'route_id' => null,
            'driver_id' => null
        ]);

        // Create a driver (resource)
        Driver::factory()->create(['is_active' => true]);

        Livewire::test(SmartDispatch::class)
            ->call('loadData')
            ->assertSet('allDemands', function ($demands) {
                return count($demands) === 3;
            })
            ->assertSet('allResources', function ($resources) {
                return count($resources) === 1;
            });
    }

    public function test_it_saves_dispatch_assignments()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $driver = Driver::factory()->create(['is_active' => true]);
        $shipments = Shipment::factory()->count(2)->create([
            'status' => 'pending'
        ]);

        // Assignments structure: [driver_id => [demand_id_1, demand_id_2]]
        $assignments = [
            $driver->id => [
                (string) $shipments[0]->id,
                (string) $shipments[1]->id
            ]
        ];

        Livewire::test(SmartDispatch::class)
            ->call('saveDispatch', $assignments);

        // Assert Route was created
        $this->assertDatabaseHas('routes', [
            'driver_id' => $driver->id,
            'status' => 'pending' // Assuming default status
        ]);

        $route = Route::where('driver_id', $driver->id)->first();

        // Assert Shipments were updated
        foreach ($shipments as $shipment) {
            $this->assertDatabaseHas('shipments', [
                'id' => $shipment->id,
                'route_id' => $route->id,
                'driver_id' => $driver->id,
                'status' => 'in_transit' // Or whatever logic you have
            ]);
        }
    }
}
