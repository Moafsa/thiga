<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of vehicles
     */
    public function index(Request $request)
    {
        $tenant = Auth::user()->tenant;
        
        if (!$tenant) {
            return redirect()->route('login')->with('error', 'User does not have an associated tenant.');
        }

        $query = Vehicle::where('tenant_id', $tenant->id)
            ->with(['drivers']);

        // Filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('plate', 'like', "%{$search}%")
                  ->orWhere('renavam', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === '1');
        }

        if ($request->filled('vehicle_type')) {
            $query->where('vehicle_type', $request->vehicle_type);
        }

        $vehicles = $query->orderBy('plate')->paginate(20);

        return view('vehicles.index', compact('vehicles'));
    }

    /**
     * Show the form for creating a new vehicle
     */
    public function create()
    {
        $vehicleTypes = [
            'Caminhão',
            'Carreta',
            'Truck',
            'Van',
            'Utilitário',
            'Moto',
            'Outro'
        ];

        $fuelTypes = [
            'Gasolina',
            'Etanol',
            'Diesel',
            'GNV',
            'Elétrico',
            'Híbrido'
        ];

        return view('vehicles.create', compact('vehicleTypes', 'fuelTypes'));
    }

    /**
     * Store a newly created vehicle
     */
    public function store(Request $request)
    {
        $tenant = Auth::user()->tenant;

        $validated = $request->validate([
            'plate' => [
                'required',
                'string',
                'max:10',
                'regex:/^[A-Z]{3}[0-9][A-Z0-9][0-9]{2}$|^[A-Z]{3}-?[0-9]{4}$/i',
                Rule::unique('vehicles')->where(function ($query) use ($tenant) {
                    return $query->where('tenant_id', $tenant->id);
                })
            ],
            'renavam' => [
                'nullable',
                'string',
                'max:11',
                Rule::unique('vehicles')->where(function ($query) use ($tenant) {
                    return $query->where('tenant_id', $tenant->id);
                })
            ],
            'chassis' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'color' => 'nullable|string|max:50',
            'fuel_type' => 'nullable|string|max:50',
            'vehicle_type' => 'nullable|string|max:100',
            'capacity_weight' => 'nullable|numeric|min:0',
            'capacity_volume' => 'nullable|numeric|min:0',
            'axles' => 'nullable|integer|min:1|max:10',
            'status' => 'nullable|string|in:available,in_use,maintenance,inactive',
            'is_active' => 'boolean',
            'ownership_type' => 'required|string|in:fleet,third_party',
            'insurance_expiry_date' => 'nullable|date',
            'inspection_expiry_date' => 'nullable|date',
            'registration_expiry_date' => 'nullable|date',
            'current_odometer' => 'nullable|integer|min:0',
            'maintenance_interval_km' => 'nullable|integer|min:0',
            'maintenance_interval_days' => 'nullable|integer|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Normalize plate (remove dashes, uppercase)
        $validated['plate'] = strtoupper(preg_replace('/[^A-Z0-9]/', '', $validated['plate']));

        $validated['tenant_id'] = $tenant->id;
        $validated['is_active'] = $request->has('is_active') ? true : false;
        $validated['status'] = $validated['status'] ?? 'available';
        $validated['current_odometer'] = $validated['current_odometer'] ?? 0;

        $vehicle = Vehicle::create($validated);

        return redirect()->route('vehicles.show', $vehicle)
            ->with('success', 'Vehicle created successfully!');
    }

    /**
     * Display the specified vehicle
     */
    public function show(Vehicle $vehicle)
    {
        $this->authorizeAccess($vehicle);

        $vehicle->load([
            'drivers',
            'routes' => function($query) {
                $query->orderBy('scheduled_date', 'desc')->limit(10);
            }
            // 'maintenances' will be loaded when VehicleMaintenance model is created
        ]);

        // Get available drivers for assignment
        $tenant = Auth::user()->tenant;
        $availableDrivers = Driver::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->whereDoesntHave('vehicles', function($query) use ($vehicle) {
                $query->where('vehicles.id', $vehicle->id)
                      ->where('driver_vehicle.is_active', true);
            })
            ->orderBy('name')
            ->get();

        return view('vehicles.show', compact('vehicle', 'availableDrivers'));
    }

    /**
     * Show the form for editing the specified vehicle
     */
    public function edit(Vehicle $vehicle)
    {
        $this->authorizeAccess($vehicle);

        $vehicleTypes = [
            'Caminhão',
            'Carreta',
            'Truck',
            'Van',
            'Utilitário',
            'Moto',
            'Outro'
        ];

        $fuelTypes = [
            'Gasolina',
            'Etanol',
            'Diesel',
            'GNV',
            'Elétrico',
            'Híbrido'
        ];

        return view('vehicles.edit', compact('vehicle', 'vehicleTypes', 'fuelTypes'));
    }

    /**
     * Update the specified vehicle
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        $this->authorizeAccess($vehicle);

        $tenant = Auth::user()->tenant;

        $validated = $request->validate([
            'plate' => [
                'required',
                'string',
                'max:10',
                'regex:/^[A-Z]{3}[0-9][A-Z0-9][0-9]{2}$|^[A-Z]{3}-?[0-9]{4}$/i',
                Rule::unique('vehicles')->where(function ($query) use ($tenant) {
                    return $query->where('tenant_id', $tenant->id);
                })->ignore($vehicle->id)
            ],
            'renavam' => [
                'nullable',
                'string',
                'max:11',
                Rule::unique('vehicles')->where(function ($query) use ($tenant) {
                    return $query->where('tenant_id', $tenant->id);
                })->ignore($vehicle->id)
            ],
            'chassis' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'color' => 'nullable|string|max:50',
            'fuel_type' => 'nullable|string|max:50',
            'vehicle_type' => 'nullable|string|max:100',
            'capacity_weight' => 'nullable|numeric|min:0',
            'capacity_volume' => 'nullable|numeric|min:0',
            'axles' => 'nullable|integer|min:1|max:10',
            'status' => 'nullable|string|in:available,in_use,maintenance,inactive',
            'is_active' => 'boolean',
            'ownership_type' => 'required|string|in:fleet,third_party',
            'insurance_expiry_date' => 'nullable|date',
            'inspection_expiry_date' => 'nullable|date',
            'registration_expiry_date' => 'nullable|date',
            'current_odometer' => 'nullable|integer|min:0',
            'maintenance_interval_km' => 'nullable|integer|min:0',
            'maintenance_interval_days' => 'nullable|integer|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Normalize plate
        $validated['plate'] = strtoupper(preg_replace('/[^A-Z0-9]/', '', $validated['plate']));
        $validated['is_active'] = $request->has('is_active') ? true : false;

        $vehicle->update($validated);

        return redirect()->route('vehicles.show', $vehicle)
            ->with('success', 'Vehicle updated successfully!');
    }

    /**
     * Remove the specified vehicle
     */
    public function destroy(Vehicle $vehicle)
    {
        $this->authorizeAccess($vehicle);

        // Check if vehicle has routes
        if ($vehicle->routes()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot delete vehicle with associated routes.']);
        }

        // Unassign all drivers
        $vehicle->drivers()->updateExistingPivot($vehicle->drivers->pluck('id'), [
            'is_active' => false,
            'unassigned_at' => now(),
        ]);

        $vehicle->delete();

        return redirect()->route('vehicles.index')
            ->with('success', 'Vehicle deleted successfully!');
    }

    /**
     * Assign drivers to vehicle
     */
    public function assignDrivers(Request $request, Vehicle $vehicle)
    {
        $this->authorizeAccess($vehicle);

        $validated = $request->validate([
            'driver_ids' => 'required|array',
            'driver_ids.*' => 'exists:drivers,id',
            'notes' => 'nullable|string|max:500',
        ]);

        $tenant = Auth::user()->tenant;

        // Verify all drivers belong to tenant
        $drivers = Driver::where('tenant_id', $tenant->id)
            ->whereIn('id', $validated['driver_ids'])
            ->get();

        if ($drivers->count() !== count($validated['driver_ids'])) {
            return back()->withErrors(['error' => 'Some drivers are invalid.']);
        }

        // Assign drivers
        foreach ($drivers as $driver) {
            $vehicle->drivers()->syncWithoutDetaching([
                $driver->id => [
                    'assigned_at' => now(),
                    'is_active' => true,
                    'can_drive' => true,
                    'notes' => $validated['notes'] ?? null,
                ]
            ]);
        }

        return back()->with('success', 'Drivers assigned successfully!');
    }

    /**
     * Unassign driver from vehicle
     */
    public function unassignDriver(Request $request, Vehicle $vehicle, Driver $driver)
    {
        $this->authorizeAccess($vehicle);

        if ($vehicle->tenant_id !== $driver->tenant_id) {
            abort(403, 'Unauthorized access.');
        }

        $vehicle->drivers()->updateExistingPivot($driver->id, [
            'is_active' => false,
            'unassigned_at' => now(),
        ]);

        return back()->with('success', 'Driver unassigned successfully!');
    }

    /**
     * Authorize access to vehicle
     */
    protected function authorizeAccess(Vehicle $vehicle)
    {
        $tenant = Auth::user()->tenant;
        
        if ($vehicle->tenant_id !== $tenant->id) {
            abort(403, 'Unauthorized access to this vehicle.');
        }
    }
}

