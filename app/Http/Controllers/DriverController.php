<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

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
            return redirect()->route('login')->with('error', 'Usuário não possui tenant associado.');
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
                ->with('error', 'Usuário não possui tenant associado.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
            'password' => 'nullable|string|min:8',
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

        try {
            DB::beginTransaction();

            // Normalize phone number
            $normalizedPhone = Driver::normalizePhone($validated['phone']);
            if (!$normalizedPhone) {
                throw new \Exception('Telefone inválido.');
            }

            // Check if phone already exists for this tenant
            $existingDriver = Driver::where('tenant_id', $tenant->id)
                ->where('phone_e164', $normalizedPhone)
                ->first();
            
            if ($existingDriver) {
                throw new \Exception('Já existe um motorista com este telefone neste tenant.');
            }

            // Generate email from phone if not provided
            $email = $validated['email'] ?? $this->generateEmailFromPhone($normalizedPhone, $tenant);

            // Validate email uniqueness
            $existingUser = User::where('tenant_id', $tenant->id)
                ->where('email', $email)
                ->first();
            
            if ($existingUser) {
                // If email exists, append a suffix
                $email = $this->generateEmailFromPhone($normalizedPhone, $tenant, true);
            }

            // Generate password if not provided
            $password = $request->filled('password') 
                ? $request->password 
                : Str::random(12);

            // Create user for the driver
            $user = User::create([
                'name' => $validated['name'],
                'email' => $email,
                'password' => Hash::make($password),
                'tenant_id' => $tenant->id,
                'phone' => $normalizedPhone,
                'is_active' => true,
            ]);

            // Assign Driver role
            $user->assignRole('Driver');

            // Prepare driver data
            $driverData = [
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
                'name' => $validated['name'],
                'email' => $email,
                'phone' => $validated['phone'],
                'is_active' => $request->has('is_active') ? true : false,
                'location_tracking_enabled' => $request->has('location_tracking_enabled') ? true : false,
                'status' => $validated['status'] ?? 'available',
            ];

            // Add optional fields if provided
            $optionalFields = ['document', 'cnh_number', 'cnh_category', 'cnh_expiry_date', 'vehicle_plate', 'vehicle_model', 'vehicle_color'];
            foreach ($optionalFields as $field) {
                if (isset($validated[$field]) && $validated[$field] !== '' && $validated[$field] !== null) {
                    $driverData[$field] = $validated[$field];
                }
            }

            $driver = Driver::create($driverData);

            DB::commit();

            $message = 'Motorista criado com sucesso! O usuário foi criado e pode fazer login usando o telefone via WhatsApp.';
            if (!$request->filled('password')) {
                $message .= ' Uma senha temporária foi gerada.';
            }

            return redirect()->route('drivers.show', $driver)
                ->with('success', $message)
                ->with('temp_password', $request->filled('password') ? null : $password)
                ->with('temp_password_info', !$request->filled('password'));
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->withErrors(['error' => 'Erro ao criar motorista: ' . $e->getMessage()]);
        }
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
        $tenant = Auth::user()->tenant;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
            'password' => 'nullable|string|min:8',
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

        try {
            DB::beginTransaction();

            // Normalize phone number
            $normalizedPhone = Driver::normalizePhone($validated['phone']);
            if (!$normalizedPhone) {
                throw new \Exception('Telefone inválido.');
            }

            // Check if phone already exists for another driver in this tenant
            $existingDriver = Driver::where('tenant_id', $tenant->id)
                ->where('phone_e164', $normalizedPhone)
                ->where('id', '!=', $driver->id)
                ->first();
            
            if ($existingDriver) {
                throw new \Exception('Já existe outro motorista com este telefone neste tenant.');
            }

            // Generate email from phone if not provided
            $email = $validated['email'] ?? $this->generateEmailFromPhone($normalizedPhone, $tenant);

            // Update or create user
            if ($driver->user_id) {
                $user = User::findOrFail($driver->user_id);
                
                // Check email uniqueness if changing
                if ($user->email !== $email) {
                    $existingUser = User::where('tenant_id', $tenant->id)
                        ->where('email', $email)
                        ->where('id', '!=', $user->id)
                        ->first();
                    
                    if ($existingUser) {
                        $email = $this->generateEmailFromPhone($normalizedPhone, $tenant, true);
                    }
                }

                $user->update([
                    'name' => $validated['name'],
                    'email' => $email,
                    'phone' => $normalizedPhone,
                ]);

                // Update password if provided
                if ($request->filled('password')) {
                    $user->update([
                        'password' => Hash::make($validated['password']),
                    ]);
                }
            } else {
                // Create user if driver doesn't have one
                $password = $request->filled('password') 
                    ? $validated['password'] 
                    : Str::random(12);

                // Validate email uniqueness
                $existingUser = User::where('tenant_id', $tenant->id)
                    ->where('email', $email)
                    ->first();
                
                if ($existingUser) {
                    $email = $this->generateEmailFromPhone($normalizedPhone, $tenant, true);
                }

                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $email,
                    'password' => Hash::make($password),
                    'tenant_id' => $tenant->id,
                    'phone' => $normalizedPhone,
                    'is_active' => true,
                ]);

                $user->assignRole('Driver');
                $validated['user_id'] = $user->id;
            }

            // Prepare driver data
            $driverData = [
                'name' => $validated['name'],
                'email' => $email,
                'phone' => $validated['phone'],
                'is_active' => $request->has('is_active') ? true : false,
                'location_tracking_enabled' => $request->has('location_tracking_enabled') ? true : false,
            ];

            if (isset($validated['status'])) {
                $driverData['status'] = $validated['status'];
            }

            // Add optional fields if provided
            $optionalFields = ['document', 'cnh_number', 'cnh_category', 'cnh_expiry_date', 'vehicle_plate', 'vehicle_model', 'vehicle_color'];
            foreach ($optionalFields as $field) {
                if (isset($validated[$field]) && $validated[$field] !== '' && $validated[$field] !== null) {
                    $driverData[$field] = $validated[$field];
                }
            }

            $driver->update($driverData);

            DB::commit();

            return redirect()->route('drivers.show', $driver)
                ->with('success', 'Motorista atualizado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->withErrors(['error' => 'Erro ao atualizar motorista: ' . $e->getMessage()]);
        }
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
            ->with('success', 'Motorista excluído com sucesso!');
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

    /**
     * Generate email from phone number
     */
    protected function generateEmailFromPhone(string $phone, $tenant, bool $withSuffix = false): string
    {
        // Remove country code and format
        $phoneDigits = preg_replace('/\D/', '', $phone);
        $phoneDigits = ltrim($phoneDigits, '55'); // Remove Brazil country code
        
        $domain = $tenant->domain ?? 'driver';
        $suffix = $withSuffix ? '.' . time() : '';
        
        return "driver.{$phoneDigits}@{$domain}.local{$suffix}";
    }
}







