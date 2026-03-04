<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\Branch;
use App\Models\Shipment;
use App\Models\AvailableCargo;
use Illuminate\Support\Facades\Auth;

class RouteCreationWizard extends Component
{
    public $step = 1;

    // Step 1: Basic Data
    public $name;
    public $scheduled_date;
    public $driver_id;
    public $vehicle_id;
    public $branch_id;
    public $start_address_type = 'branch';

    // Step 2: Shipments/Cargo
    public $selectedShipments = [];
    public $selectedCargo = [];

    // Step 3: Summary & Calculation
    public $total_value = 0;
    public $total_weight = 0;

    public function updatedSelectedShipments()
    {
        $this->calculateTotals();
    }

    private function calculateTotals()
    {
        $shipments = Shipment::whereIn('id', $this->selectedShipments)->get();
        $this->total_value = $shipments->sum('value');
        $this->total_weight = $shipments->sum('weight');
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|min:3',
            'scheduled_date' => 'required|date',
        ]);

        $tenant = Auth::user()->tenant;

        $route = \App\Models\Route::create([
            'tenant_id' => $tenant->id,
            'name' => $this->name,
            'scheduled_date' => $this->scheduled_date,
            'driver_id' => $this->driver_id,
            'vehicle_id' => $this->vehicle_id,
            'branch_id' => $this->branch_id,
            'status' => 'scheduled',
        ]);

        if (!empty($this->selectedShipments)) {
            Shipment::whereIn('id', $this->selectedShipments)
                ->update(['route_id' => $route->id]);
        }

        return redirect()->route('routes.show', $route->id)
            ->with('success', 'Rota criada com sucesso!');
    }

    public function mount()
    {
        $this->scheduled_date = now()->format('Y-m-d');
    }

    public function nextStep()
    {
        if ($this->step == 1) {
            $this->validate([
                'name' => 'required|min:3',
                'scheduled_date' => 'required|date',
                'branch_id' => 'required_if:start_address_type,branch',
            ]);
        }
        $this->step++;
    }

    public function prevStep()
    {
        $this->step--;
    }

    public function render()
    {
        $tenant = Auth::user()->tenant;

        return view('livewire.route-creation-wizard', [
            'drivers' => Driver::where('tenant_id', $tenant->id)->where('is_active', true)->get(),
            'vehicles' => Vehicle::where('tenant_id', $tenant->id)->where('is_active', true)->get(),
            'branches' => Branch::where('tenant_id', $tenant->id)->where('is_active', true)->get(),
            'availableShipments' => Shipment::where('tenant_id', $tenant->id)->whereNull('route_id')->get(),
            'availableCargo' => AvailableCargo::where('tenant_id', $tenant->id)->where('status', 'available')->get(),
        ]);
    }
}
