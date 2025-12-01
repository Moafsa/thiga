<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientAddress;
use App\Models\Salesperson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of clients
     */
    public function index(Request $request)
    {
        $tenant = Auth::user()->tenant;
        
        if (!$tenant) {
            return redirect()->route('login')->with('error', 'User does not have an associated tenant.');
        }

        $query = Client::where('tenant_id', $tenant->id)
            ->with(['salesperson', 'addresses']);

        // Filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('cnpj', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('city')) {
            $query->where('city', 'like', "%{$request->city}%");
        }

        if ($request->filled('state')) {
            $query->where('state', $request->state);
        }

        if ($request->filled('salesperson_id')) {
            $query->where('salesperson_id', $request->salesperson_id);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === '1');
        }

        $clients = $query->orderBy('name')->paginate(20);

        $salespeople = Salesperson::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $states = [
            'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO',
            'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI',
            'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'
        ];

        return view('clients.index', compact('clients', 'salespeople', 'states'));
    }

    /**
     * Show the form for creating a new client
     */
    public function create()
    {
        $tenant = Auth::user()->tenant;
        
        $salespeople = Salesperson::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $states = [
            'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO',
            'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI',
            'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'
        ];

        return view('clients.create', compact('salespeople', 'states'));
    }

    /**
     * Store a newly created client
     */
    public function store(Request $request)
    {
        $tenant = Auth::user()->tenant;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'cnpj' => 'nullable|string|max:18',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|size:2',
            'zip_code' => 'nullable|string|max:10',
            'salesperson_id' => 'nullable|exists:salespeople,id',
            'is_active' => 'boolean',
            // Address fields
            'addresses' => 'nullable|array',
            'addresses.*.type' => 'required_with:addresses|string|in:pickup,delivery',
            'addresses.*.name' => 'required_with:addresses|string|max:255',
            'addresses.*.address' => 'required_with:addresses|string|max:255',
            'addresses.*.number' => 'required_with:addresses|string|max:20',
            'addresses.*.complement' => 'nullable|string|max:255',
            'addresses.*.neighborhood' => 'required_with:addresses|string|max:255',
            'addresses.*.city' => 'required_with:addresses|string|max:255',
            'addresses.*.state' => 'required_with:addresses|string|size:2',
            'addresses.*.zip_code' => 'required_with:addresses|string|max:10',
            'addresses.*.is_default' => 'boolean',
        ]);

        $validated['tenant_id'] = $tenant->id;
        $validated['is_active'] = $request->has('is_active') ? true : false;

        $addresses = $validated['addresses'] ?? [];
        unset($validated['addresses']);

        $client = Client::create($validated);

        // Create addresses
        foreach ($addresses as $addressData) {
            $addressData['client_id'] = $client->id;
            ClientAddress::create($addressData);
        }

        return redirect()->route('clients.show', $client)
            ->with('success', 'Client created successfully!');
    }

    /**
     * Display the specified client
     */
    public function show(Client $client)
    {
        $this->authorizeAccess($client);

        $client->load(['salesperson', 'addresses', 'shipments', 'proposals', 'invoices']);

        return view('clients.show', compact('client'));
    }

    /**
     * Show the form for editing the specified client
     */
    public function edit(Client $client)
    {
        $this->authorizeAccess($client);

        $tenant = Auth::user()->tenant;
        
        $salespeople = Salesperson::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $states = [
            'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO',
            'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI',
            'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'
        ];

        $client->load('addresses');

        return view('clients.edit', compact('client', 'salespeople', 'states'));
    }

    /**
     * Update the specified client
     */
    public function update(Request $request, Client $client)
    {
        $this->authorizeAccess($client);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'cnpj' => 'nullable|string|max:18',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|size:2',
            'zip_code' => 'nullable|string|max:10',
            'salesperson_id' => 'nullable|exists:salespeople,id',
            'is_active' => 'boolean',
            // Address fields
            'addresses' => 'nullable|array',
            'addresses.*.id' => 'nullable|exists:client_addresses,id',
            'addresses.*.type' => 'required_with:addresses|string|in:pickup,delivery',
            'addresses.*.name' => 'required_with:addresses|string|max:255',
            'addresses.*.address' => 'required_with:addresses|string|max:255',
            'addresses.*.number' => 'required_with:addresses|string|max:20',
            'addresses.*.complement' => 'nullable|string|max:255',
            'addresses.*.neighborhood' => 'required_with:addresses|string|max:255',
            'addresses.*.city' => 'required_with:addresses|string|max:255',
            'addresses.*.state' => 'required_with:addresses|string|size:2',
            'addresses.*.zip_code' => 'required_with:addresses|string|max:10',
            'addresses.*.is_default' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;

        $addresses = $validated['addresses'] ?? [];
        unset($validated['addresses']);

        $client->update($validated);

        // Update or create addresses
        $existingAddressIds = [];
        foreach ($addresses as $addressData) {
            if (isset($addressData['id'])) {
                $address = ClientAddress::where('id', $addressData['id'])
                    ->where('client_id', $client->id)
                    ->first();
                if ($address) {
                    unset($addressData['id']);
                    $address->update($addressData);
                    $existingAddressIds[] = $address->id;
                }
            } else {
                $addressData['client_id'] = $client->id;
                $newAddress = ClientAddress::create($addressData);
                $existingAddressIds[] = $newAddress->id;
            }
        }

        // Delete addresses that were removed
        ClientAddress::where('client_id', $client->id)
            ->whereNotIn('id', $existingAddressIds)
            ->delete();

        return redirect()->route('clients.show', $client)
            ->with('success', 'Client updated successfully!');
    }

    /**
     * Remove the specified client
     */
    public function destroy(Client $client)
    {
        $this->authorizeAccess($client);

        // Check if client has shipments
        if ($client->shipments()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot delete client with associated shipments.']);
        }

        $client->addresses()->delete();
        $client->delete();

        return redirect()->route('clients.index')
            ->with('success', 'Client deleted successfully!');
    }

    /**
     * Authorize access to client
     */
    protected function authorizeAccess(Client $client)
    {
        $tenant = Auth::user()->tenant;
        
        if ($client->tenant_id !== $tenant->id) {
            abort(403, 'Unauthorized access to this client.');
        }
    }
}

















