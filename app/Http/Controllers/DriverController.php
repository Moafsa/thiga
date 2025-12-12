<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DriverController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of drivers
     */
    public function index(Request $request)
    {
        $tenant = Auth::user()->tenant;
        
        if (!$tenant) {
            return redirect()->route('login')->with('error', 'User does not have an associated tenant.');
        }

        $query = Driver::where('tenant_id', $tenant->id);

        // Filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('document', 'like', "%{$search}%")
                  ->orWhere('vehicle_plate', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === '1');
        }

        $drivers = $query->orderBy('name')->paginate(20);

        return view('drivers.index', compact('drivers'));
    }

    /**
     * Show the form for creating a new driver
     */
    public function create()
    {
        $cnhCategories = ['A', 'B', 'C', 'D', 'E', 'AB', 'AC', 'AD', 'AE'];
        
        return view('drivers.create', compact('cnhCategories'));
    }

    /**
     * Store a newly created driver
     */
    public function store(Request $request)
    {
        $tenant = Auth::user()->tenant;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'document' => 'nullable|string|max:20',
            'cnh_number' => 'nullable|string|max:20',
            'cnh_category' => 'nullable|string|max:5',
            'cnh_expiry_date' => 'nullable|date',
            'vehicle_plate' => 'nullable|string|max:10',
            'vehicle_model' => 'nullable|string|max:255',
            'vehicle_color' => 'nullable|string|max:50',
            'status' => 'nullable|string|in:available,busy,offline,on_break',
            'is_active' => 'boolean',
            'location_tracking_enabled' => 'boolean',
        ]);

        $validated['tenant_id'] = $tenant->id;
        $validated['is_active'] = $request->has('is_active') ? true : false;
        $validated['location_tracking_enabled'] = $request->has('location_tracking_enabled') ? true : false;
        $validated['status'] = $validated['status'] ?? 'available';
        
        // user_id é opcional - pode ser criado depois se necessário para login
        $validated['user_id'] = null;

        $driver = Driver::create($validated);

        return redirect()->route('drivers.show', $driver)
            ->with('success', 'Driver created successfully!');
    }

    /**
     * Display the specified driver
     */
    public function show(Driver $driver)
    {
        $this->authorizeAccess($driver);

        $driver->load(['routes', 'shipments', 'locationTrackings', 'vehicles']);

        return view('drivers.show', compact('driver'));
    }

    /**
     * Show the form for editing the specified driver
     */
    public function edit(Driver $driver)
    {
        $this->authorizeAccess($driver);

        $cnhCategories = ['A', 'B', 'C', 'D', 'E', 'AB', 'AC', 'AD', 'AE'];

        return view('drivers.edit', compact('driver', 'cnhCategories'));
    }

    /**
     * Update the specified driver
     */
    public function update(Request $request, Driver $driver)
    {
        $this->authorizeAccess($driver);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'document' => 'nullable|string|max:20',
            'cnh_number' => 'nullable|string|max:20',
            'cnh_category' => 'nullable|string|max:5',
            'cnh_expiry_date' => 'nullable|date',
            'vehicle_plate' => 'nullable|string|max:10',
            'vehicle_model' => 'nullable|string|max:255',
            'vehicle_color' => 'nullable|string|max:50',
            'status' => 'nullable|string|in:available,busy,offline,on_break',
            'is_active' => 'boolean',
            'location_tracking_enabled' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;
        $validated['location_tracking_enabled'] = $request->has('location_tracking_enabled') ? true : false;

        $driver->update($validated);

        return redirect()->route('drivers.show', $driver)
            ->with('success', 'Driver updated successfully!');
    }

    /**
     * Remove the specified driver
     */
    public function destroy(Driver $driver)
    {
        $this->authorizeAccess($driver);

        // Check if driver has routes or shipments
        if ($driver->routes()->count() > 0 || $driver->shipments()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot delete driver with associated routes or shipments.']);
        }

        $driver->delete();

        return redirect()->route('drivers.index')
            ->with('success', 'Driver deleted successfully!');
    }

    /**
     * Authorize access to driver
     */
    protected function authorizeAccess(Driver $driver)
    {
        $tenant = Auth::user()->tenant;
        
        if ($driver->tenant_id !== $tenant->id) {
            abort(403, 'Unauthorized access to this driver.');
        }
    }
}






