<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\Client;
use App\Models\Route;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ShipmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $tenant = Auth::user()->tenant;
        
        if (!$tenant) {
            return redirect()->route('login')->with('error', 'Usuário não possui tenant associado.');
        }

        $query = Shipment::where('tenant_id', $tenant->id)
            ->with(['senderClient', 'receiverClient', 'route', 'driver']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('client_id')) {
            $query->where('sender_client_id', $request->client_id);
        }

        if ($request->filled('tracking_number')) {
            $query->where('tracking_number', 'like', '%' . $request->tracking_number . '%');
        }

        if ($request->filled('date_from')) {
            $query->where('pickup_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('pickup_date', '<=', $request->date_to);
        }

        $shipments = $query->orderBy('created_at', 'desc')->paginate(20);

        $clients = Client::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('shipments.index', compact('shipments', 'clients'));
    }

    public function create()
    {
        // Using Livewire component instead of traditional form
        // The view will handle the Livewire component rendering
        return view('shipments.create-livewire');
    }

    public function store(Request $request)
    {
        $tenant = Auth::user()->tenant;

        if (!$tenant) {
            return redirect()->route('login')->with('error', 'Usuário não possui tenant associado.');
        }

        $validated = $request->validate([
            'sender_client_id' => 'required|exists:clients,id',
            'receiver_name' => 'required|string|max:255',
            'receiver_phone' => 'nullable|string|max:20',
            'receiver_email' => 'nullable|email|max:255',
            'delivery_address' => 'required|string|max:255',
            'delivery_city' => 'required|string|max:255',
            'delivery_state' => 'required|string|size:2',
            'delivery_zip_code' => 'required|string|max:10',
            'pickup_address' => 'required|string|max:255',
            'pickup_city' => 'required|string|max:255',
            'pickup_state' => 'required|string|size:2',
            'pickup_zip_code' => 'required|string|max:10',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'weight' => 'nullable|numeric|min:0',
            'volume' => 'nullable|numeric|min:0',
            'quantity' => 'nullable|integer|min:1',
            'value' => 'nullable|numeric|min:0',
            'pickup_date' => 'required|date',
            'pickup_time' => 'nullable',
            'delivery_date' => 'required|date|after_or_equal:pickup_date',
            'delivery_time' => 'nullable',
            'notes' => 'nullable|string',
            'freight_value' => 'nullable|numeric|min:0',
        ]);

        $trackingNumber = 'THG' . strtoupper(Str::random(8));

        $receiverClient = Client::where('tenant_id', $tenant->id)
            ->where('name', $validated['receiver_name'])
            ->where('zip_code', $validated['delivery_zip_code'])
            ->first();

        if (!$receiverClient) {
            $receiverClient = Client::create([
                'tenant_id' => $tenant->id,
                'name' => $validated['receiver_name'],
                'phone' => $validated['receiver_phone'] ?? null,
                'email' => $validated['receiver_email'] ?? null,
                'address' => $validated['delivery_address'],
                'city' => $validated['delivery_city'],
                'state' => $validated['delivery_state'],
                'zip_code' => $validated['delivery_zip_code'],
                'is_active' => true,
            ]);
        }

        $shipment = Shipment::create([
            'tenant_id' => $tenant->id,
            'sender_client_id' => $validated['sender_client_id'],
            'receiver_client_id' => $receiverClient->id,
            'tracking_number' => $trackingNumber,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'weight' => $validated['weight'] ?? null,
            'volume' => $validated['volume'] ?? null,
            'quantity' => $validated['quantity'] ?? 1,
            'value' => $validated['value'] ?? null,
            'pickup_address' => $validated['pickup_address'],
            'pickup_city' => $validated['pickup_city'],
            'pickup_state' => $validated['pickup_state'],
            'pickup_zip_code' => $validated['pickup_zip_code'],
            'delivery_address' => $validated['delivery_address'],
            'delivery_city' => $validated['delivery_city'],
            'delivery_state' => $validated['delivery_state'],
            'delivery_zip_code' => $validated['delivery_zip_code'],
            'pickup_date' => $validated['pickup_date'],
            'pickup_time' => $validated['pickup_time'] ?? '08:00',
            'delivery_date' => $validated['delivery_date'],
            'delivery_time' => $validated['delivery_time'] ?? '18:00',
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
            'metadata' => [
                'freight_value' => $validated['freight_value'] ?? null,
            ],
        ]);

        return redirect()->route('shipments.show', $shipment)
            ->with('success', 'Carga criada com sucesso!');
    }

    public function show(Shipment $shipment)
    {
        $this->authorizeAccess($shipment);
        $shipment->load(['senderClient', 'receiverClient', 'route', 'driver', 'deliveryProofs', 'fiscalDocuments']);
        
        // Get CT-e if exists
        $cte = $shipment->cte();
        
        return view('shipments.show', compact('shipment', 'cte'));
    }

    public function edit(Shipment $shipment)
    {
        $this->authorizeAccess($shipment);
        $tenant = Auth::user()->tenant;
        $clients = Client::where('tenant_id', $tenant->id)->where('is_active', true)->orderBy('name')->get();
        $routes = Route::where('tenant_id', $tenant->id)->where('status', '!=', 'completed')->orderBy('scheduled_date')->get();
        $drivers = Driver::where('tenant_id', $tenant->id)->where('is_active', true)->orderBy('name')->get();
        return view('shipments.edit', compact('shipment', 'clients', 'routes', 'drivers'));
    }

    public function update(Request $request, Shipment $shipment)
    {
        $this->authorizeAccess($shipment);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'weight' => 'nullable|numeric|min:0',
            'volume' => 'nullable|numeric|min:0',
            'quantity' => 'nullable|integer|min:1',
            'value' => 'nullable|numeric|min:0',
            'status' => 'required|in:pending,scheduled,picked_up,in_transit,delivered,returned,cancelled',
            'route_id' => 'nullable|exists:routes,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'notes' => 'nullable|string',
        ]);

        $shipment->update($validated);

        if ($request->status === 'picked_up' && !$shipment->picked_up_at) {
            $shipment->update(['picked_up_at' => now()]);
        }

        if ($request->status === 'delivered' && !$shipment->delivered_at) {
            $shipment->update(['delivered_at' => now()]);
        }

        return redirect()->route('shipments.show', $shipment)
            ->with('success', 'Carga atualizada com sucesso!');
    }

    public function destroy(Shipment $shipment)
    {
        $this->authorizeAccess($shipment);

        if ($shipment->status === 'delivered' || $shipment->status === 'in_transit') {
            return redirect()->route('shipments.show', $shipment)
                ->with('error', 'Não é possível excluir uma carga entregue ou em trânsito.');
        }

        $shipment->delete();
        return redirect()->route('shipments.index')
            ->with('success', 'Carga excluída com sucesso!');
    }

    protected function authorizeAccess(Shipment $shipment)
    {
        $tenant = Auth::user()->tenant;
        if (!$tenant || $shipment->tenant_id !== $tenant->id) {
            abort(403, 'Unauthorized access to shipment');
        }
    }
}
