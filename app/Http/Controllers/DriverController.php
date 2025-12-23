<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\DriverTenantAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

        if (!$tenant) {
            return redirect()->route('drivers.index')
                ->with('error', 'User does not have an associated tenant.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
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

        // Normalize phone number for storage (keep original in 'phone' field)
        $phoneDigits = preg_replace('/\D/', '', $validated['phone']);
        
        // Store normalized phone in phone_e164 field (with DDI if it's a mobile number)
        if (strlen($phoneDigits) >= 10) {
            // If doesn't start with 55 (DDI), add it
            if (!str_starts_with($phoneDigits, '55')) {
                $validated['phone_e164'] = '55' . $phoneDigits;
            } else {
                $validated['phone_e164'] = $phoneDigits;
            }
        }

        $driver = Driver::create($validated);
        $sanitizedEmail = Str::lower(
            "driver+{$tenant->id}+{$phoneDigits}@tms.local"
        );

        // Use updateOrCreate to avoid duplicate key error
        $user = User::updateOrCreate(
            ['email' => $sanitizedEmail],  // Find by email
            [
                'name' => $validated['name'],
                'password' => Hash::make(Str::random(32)),
                'tenant_id' => $tenant->id,
                'phone' => $phoneDigits,
                'is_active' => true,
            ]
        );

        Log::info('User created/updated', [
            'user_id' => $user->id,
            'email' => $user->email,
            'was_recently_created' => $user->wasRecentlyCreated,
        ]);

        // Ensure user has the Driver role
        if (!$user->hasRole('Driver')) {
            $user->assignRole('Driver');
        }

        $driver->forceFill(['user_id' => $user->id])->save();
        
        Log::info('Driver user_id updated', [
            'driver_id' => $driver->id,
            'user_id' => $driver->user_id,
        ]);

        // Use firstOrCreate to avoid duplicate key error
        DriverTenantAssignment::firstOrCreate(
            [
                'driver_id' => $driver->id,
                'tenant_id' => $tenant->id,
            ],
            [
                'user_id' => $user->id,
            ]
        );

        return redirect()->route('drivers.show', $driver)
            ->with('success', 'Driver created successfully!');
    }

    /**
     * Display the specified driver
     */
    public function show(Driver $driver)
    {
        $this->authorizeAccess($driver);

        $driver->load(['primaryPhoto', 'photos', 'routes', 'shipments', 'locationTrackings', 'vehicles']);

        return view('drivers.show', compact('driver'));
    }

    /**
     * Show the form for editing the specified driver
     */
public function edit(Driver $driver)
    {
        $this->authorizeAccess($driver);

        $driver->load(['primaryPhoto', 'photos']);
        
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
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'photo_data' => 'nullable|string', // Base64 image data from camera
            'status' => 'nullable|string|in:available,busy,offline,on_break',
            'is_active' => 'boolean',
            'location_tracking_enabled' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;
        $validated['location_tracking_enabled'] = $request->has('location_tracking_enabled') ? true : false;

        // Update phone_e164 if phone was changed
        if (isset($validated['phone'])) {
            $phoneDigits = preg_replace('/\D/', '', $validated['phone']);
            
            if (strlen($phoneDigits) >= 10) {
                // If doesn't start with 55 (DDI), add it
                if (!str_starts_with($phoneDigits, '55')) {
                    $validated['phone_e164'] = '55' . $phoneDigits;
                } else {
                    $validated['phone_e164'] = $phoneDigits;
                }
            }
        }

        // Handle photo upload with optimization
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($driver->photo_url && \Storage::disk('public')->exists($driver->photo_url)) {
                \Storage::disk('public')->delete($driver->photo_url);
                // Clear cache
                \Cache::forget("photo_cache_{$driver->photo_url}_150");
                \Cache::forget("photo_cache_{$driver->photo_url}_300");
            }
            
            // Store optimized photo
            $extension = $request->file('photo')->getClientOriginalExtension();
            $filename = 'photo_' . time() . '_' . uniqid() . '.' . $extension;
            $path = "drivers/{$driver->tenant_id}/{$driver->id}/{$filename}";
            
            \App\Services\ImageService::storeOptimized($request->file('photo'), $path, 800, 800);
            $validated['photo_url'] = $path;
        } elseif ($request->filled('photo_data')) {
            // Handle base64 photo from camera
            $photoData = $request->input('photo_data');
            
            // Delete old photo if exists
            if ($driver->photo_url && \Storage::disk('public')->exists($driver->photo_url)) {
                \Storage::disk('public')->delete($driver->photo_url);
                // Clear cache
                \Cache::forget("photo_cache_{$driver->photo_url}_150");
                \Cache::forget("photo_cache_{$driver->photo_url}_300");
            }
            
            // Decode and optimize base64 image
            if (preg_match('/^data:image\/(\w+);base64,/', $photoData, $matches)) {
                $extension = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];
                $filename = 'photo_' . time() . '_' . uniqid() . '.' . $extension;
                $path = "drivers/{$driver->tenant_id}/{$driver->id}/{$filename}";
                
                \App\Services\ImageService::storeOptimized($photoData, $path, 800, 800);
                $validated['photo_url'] = $path;
            }
        } elseif ($request->has('remove_photo')) {
            // Remove photo if requested
            if ($driver->photo_url && \Storage::disk('public')->exists($driver->photo_url)) {
                \Storage::disk('public')->delete($driver->photo_url);
                // Clear cache
                \Cache::forget("photo_cache_{$driver->photo_url}_150");
                \Cache::forget("photo_cache_{$driver->photo_url}_300");
            }
            $validated['photo_url'] = null;
        }

        // Remove photo_data from validated (not a database field)
        unset($validated['photo_data']);

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

        // Delete associated user if exists
        if ($driver->user_id) {
            $user = User::find($driver->user_id);
            if ($user) {
                // Delete driver tenant assignments
                DriverTenantAssignment::where('driver_id', $driver->id)->delete();
                
                // Delete the user
                $user->delete();
            }
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






