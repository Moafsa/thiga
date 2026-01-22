<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientAddress;
use App\Models\ClientUser;
use App\Models\FreightTable;
use App\Models\Salesperson;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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
            ->with(['salesperson', 'addresses', 'user']);

        if ($request->filled('excluidos') && $request->excluidos === '1') {
            $query->excludedFromListing();
        } else {
            $query->listed();
        }

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

        if ($request->filled('marker')) {
            $query->where('marker', $request->marker);
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
     * Store a newly created client.
     * Creates a User automatically (email + phone) for login via code (phone or email).
     */
    public function store(Request $request)
    {
        $tenant = Auth::user()->tenant;

        $rules = [
            'name' => 'required|string|max:255',
            'cnpj' => 'nullable|string|max:18',
            'email' => ['required_without:phone', 'nullable', 'email', 'max:255'],
            'phone' => 'required_without:email|nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|size:2',
            'zip_code' => 'nullable|string|max:10',
            'salesperson_id' => 'nullable|exists:salespeople,id',
            'is_active' => 'boolean',
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
        ];

        if ($request->filled('email')) {
            $rules['email'][] = 'unique:users,email';
        }

        $validated = $request->validate($rules);

        $validated['tenant_id'] = $tenant->id;
        $validated['is_active'] = $request->has('is_active') ? true : false;
        $validated['marker'] = $request->input('marker', 'bronze');

        $addresses = $validated['addresses'] ?? [];
        unset($validated['addresses']);

        $client = DB::transaction(function () use ($validated, $addresses, $tenant, $request) {
            $client = Client::create($validated);

            foreach ($addresses as $addressData) {
                $addressData['client_id'] = $client->id;
                ClientAddress::create($addressData);
            }

            $phoneDigits = $validated['phone'] ? preg_replace('/\D/', '', $validated['phone']) : null;
            $userEmail = $validated['email']
                ? Str::lower($validated['email'])
                : 'client+' . $tenant->id . '+' . ($phoneDigits ?? '0') . '@tms.local';

            if (User::where('email', $userEmail)->exists()) {
                throw ValidationException::withMessages([
                    'email' => __('O e-mail informado já está em uso por outro usuário.'),
                ]);
            }

            $user = User::create([
                'name' => $validated['name'],
                'email' => $userEmail,
                'password' => Hash::make(Str::random(32)),
                'tenant_id' => $tenant->id,
                'phone' => $phoneDigits,
                'is_active' => true,
            ]);

            if ($user->hasRole('Client') === false) {
                try {
                    $user->assignRole('Client');
                } catch (\Throwable $e) {
                    // Role may not exist yet; continue
                }
            }

            ClientUser::create([
                'client_id' => $client->id,
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
            ]);

            $client->forceFill(['user_id' => $user->id])->save();

            return $client;
        });

        return redirect()->route('clients.show', $client)
            ->with('success', 'Cliente criado com sucesso! O usuário para login (código por telefone ou e-mail) foi gerado automaticamente.');
    }

    /**
     * Display the specified client
     */
    public function show(Client $client)
    {
        $this->authorizeAccess($client);

        $client->load(['salesperson', 'addresses', 'shipments', 'proposals', 'invoices', 'freightTables']);

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

        $client->load('addresses', 'freightTables');
        
        $freightTables = FreightTable::where('tenant_id', $tenant->id)
            ->active()
            ->orderBy('destination_name')
            ->get();

        return view('clients.edit', compact('client', 'salespeople', 'states', 'freightTables'));
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
            'freight_table_ids' => 'nullable|array',
            'freight_table_ids.*' => 'exists:freight_tables,id',
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
        $validated['marker'] = $request->input('marker', $client->marker ?? 'bronze');

        $addresses = $validated['addresses'] ?? [];
        unset($validated['addresses']);
        unset($validated['user_id']);

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

        // Sync freight tables
        $freightTableIds = $request->input('freight_table_ids', []);
        $client->freightTables()->sync($freightTableIds);

        return redirect()->route('clients.show', $client)
            ->with('success', 'Client updated successfully!');
    }

    /**
     * Excluir cliente da listagem do tenant (não exclui globalmente).
     * O cliente permanece no sistema; deixa de aparecer na listagem de clientes.
     */
    public function destroy(Client $client)
    {
        $this->authorizeAccess($client);

        if (Schema::hasColumn('clients', 'excluded_from_listing_at')) {
            $client->update(['excluded_from_listing_at' => now()]);
        }

        return redirect()->route('clients.index')
            ->with('success', 'Cliente removido da listagem. Ele não será exibido na lista de clientes, mas permanece no sistema (propostas, entregas etc.).');
    }

    /**
     * Reincluir na listagem o cliente que foi excluído da listagem.
     */
    public function restoreListing(Client $client)
    {
        $this->authorizeAccess($client);

        if (Schema::hasColumn('clients', 'excluded_from_listing_at')) {
            $client->update(['excluded_from_listing_at' => null]);
        }

        return redirect()->route('clients.index')
            ->with('success', 'Cliente incluído novamente na listagem.');
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

















