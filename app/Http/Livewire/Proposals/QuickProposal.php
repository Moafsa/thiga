<?php

namespace App\Http\Livewire\Proposals;

use App\Models\Client;
use App\Models\FreightTable;
use App\Models\Proposal;
use App\Services\FreightCalculationService;
use App\Services\MapsService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class QuickProposal extends Component
{
    public $origin;
    public $destination;
    public $weight;
    public $invoice_value;
    public $client_id;
    /** @var string Busca por nome, CNPJ ou telefone e texto exibido quando cliente selecionado */
    public $clientSearch = '';
    public $salesperson_id;

    // New Dimension Fields
    public $height;
    public $width;
    public $length;

    public $calculationResult = null;
    public $mapData = null;
    public $errorMessage = null;

    protected $rules = [
        'origin' => 'required|string',
        'destination' => 'required|string',
        'weight' => 'required|numeric|min:0.1',
        'invoice_value' => 'required|numeric|min:0',
        'client_id' => 'required|exists:clients,id',
        'height' => 'nullable|numeric|min:0',
        'width' => 'nullable|numeric|min:0',
        'length' => 'nullable|numeric|min:0',
    ];



    public function mount()
    {
        $user = Auth::user();

        // Try to find the salesperson record for the logged-in user
        $salesperson = \App\Models\Salesperson::where('user_id', $user->id)
            ->where('tenant_id', $user->tenant_id)
            ->first();

        if (!$salesperson) {
            // Fallback: Find the first salesperson for this tenant
            $salesperson = \App\Models\Salesperson::where('tenant_id', $user->tenant_id)->first();
        }

        if (!$salesperson) {
            // CRITICAL FALLBACK: Create a salesperson profile for the current user if NONE exists
            // This prevents "No query results for model Salesperson"
            $salesperson = \App\Models\Salesperson::create([
                'tenant_id' => $user->tenant_id,
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'commission_rate' => 0,
                'max_discount_percentage' => 0,
                'is_active' => true,
            ]);
        }

        $this->salesperson_id = $salesperson->id;

        // Default origin to tenant address if available
        if ($user->tenant) {
            $addressParts = array_filter([
                $user->tenant->address,
                $user->tenant->city,
                $user->tenant->state
            ]);
            if (!empty($addressParts)) {
                $this->origin = implode(', ', $addressParts);
            }
        }
    }

    /**
     * Ao digitar na busca, se já tinha um cliente selecionado e o texto mudou, limpa a seleção.
     */
    public function updatedClientSearch($value)
    {
        if ($this->client_id && trim($value) !== '') {
            $client = Client::find($this->client_id);
            if ($client && $client->name !== trim($value)) {
                $this->client_id = null;
            }
        }
    }

    /**
     * Seleciona o cliente e preenche a origem com o endereço dele, se existir.
     */
    public function selectClient(int $id)
    {
        $client = Client::where('tenant_id', Auth::user()->tenant_id)
            ->with(['addresses' => fn($q) => $q->orderByDesc('is_default')->orderBy('id')])
            ->find($id);

        if (!$client) {
            return;
        }

        $this->client_id = $id;
        $this->clientSearch = $client->name;

        $originAddress = $this->getOriginFromClient($client);
        if ($originAddress !== null && $originAddress !== '') {
            $this->origin = $originAddress;
        }
    }

    /**
     * Retorna o endereço formatado do cliente (endereço principal ou primeiro de addresses).
     */
    private function getOriginFromClient(Client $client): ?string
    {
        $addr = $client->addresses()->where('is_default', true)->first()
            ?? $client->addresses()->first();

        if ($addr) {
            return $addr->formatted_address;
        }

        $parts = array_filter([
            $client->address,
            $client->city,
            $client->state
        ]);

        return empty($parts) ? null : implode(', ', $parts);
    }

    public function calculate()
    {
        $this->validate();
        $this->errorMessage = null;
        $this->calculationResult = null;
        $this->mapData = null;

        try {
            $tenant = Auth::user()->tenant;

            // Calculate Cubage provided dimensions
            $cubage = 0;
            if ($this->height && $this->width && $this->length) {
                // Dimensions in cm -> convert to m³
                // (H * W * L) / 1,000,000 if cm, assuming inputs are usually cm or m? 
                // Let's assume inputs are in cm (common in Brazil logistic).
                $cubage = ($this->height * $this->width * $this->length) / 1000000;
            }

            // 1. Calculate Freight
            $freightService = app(FreightCalculationService::class);
            $result = $freightService->calculate(
                $tenant,
                $this->destination,
                $this->weight,
                $cubage,
                $this->invoice_value,
                ['client_id' => $this->client_id]
            );

            $this->calculationResult = $result;

            // 2. Get Map Data (Route)
            $mapsService = app(MapsService::class);

            // Geocode origin and destination to get coordinates
            $originCoords = $mapsService->geocode($this->origin);
            $destCoords = $mapsService->geocode($this->destination);

            if ($originCoords && $destCoords) {
                // Calculate route
                $route = $mapsService->calculateRoute(
                    $originCoords['latitude'],
                    $originCoords['longitude'],
                    $destCoords['latitude'],
                    $destCoords['longitude'],
                    [], // waypoints
                    ['alternatives' => true] // options
                );

                if ($route) {
                    $this->mapData = [
                        'origin' => $originCoords,
                        'destination' => $destCoords,
                        'route' => $route
                    ];

                    // Emit event for frontend to draw the map
                    $this->emit('mapDataUpdated', $this->mapData);
                }
            }

        } catch (\Exception $e) {
            $this->errorMessage = 'Não foi possível calcular o frete: ' . $e->getMessage();
        }
    }

    public function createProposal()
    {
        if (!$this->calculationResult) {
            return;
        }

        try {
            $tenant = Auth::user()->tenant;

            // Prepare data for service
            $data = [
                'client_id' => $this->client_id,
                'salesperson_id' => $this->salesperson_id,
                'title' => 'Cotação Rápida - ' . $this->destination,
                'description' => "Cotação gerada via Cotação Rápida.\nOrigem: {$this->origin}\nDestino: {$this->destination}",
                'weight' => $this->weight,
                'base_value' => $this->calculationResult['total'],
                'final_value' => $this->calculationResult['total'],
                'origin_address' => $this->origin,
                'destination_address' => $this->destination,
                'origin_latitude' => $this->mapData['origin']['latitude'] ?? null,
                'origin_longitude' => $this->mapData['origin']['longitude'] ?? null,
                'destination_latitude' => $this->mapData['destination']['latitude'] ?? null,
                'destination_longitude' => $this->mapData['destination']['longitude'] ?? null,
                // Add any other required fields for the service
            ];

            $service = app(\App\Services\ProposalService::class);
            $proposal = $service->createProposal($tenant, $data); // quick proposal usually doesn't send notifs immediately? Or maybe yes? Let's assume false for now as user is on screen.

            return redirect()->route('proposals.show', $proposal);

        } catch (\Exception $e) {
            $this->errorMessage = 'Erro ao criar proposta: ' . $e->getMessage();
        }
    }

    // ... (existing properties)

    public function render()
    {
        $clients = $this->searchClientsForDropdown();
        return view('livewire.proposals.quick-proposal', [
            'clients' => $clients
        ])->extends('layouts.app')->section('content');
    }

    private function searchClientsForDropdown()
    {
        $tenantId = Auth::user()->tenant_id;
        $query = Client::where('tenant_id', $tenantId)
            // ->listed() // Assuming listed() exists, usually for active clients
            ->orderBy('name');

        $q = trim($this->clientSearch ?? '');

        if (strlen($q) >= 2) {
            $query->where(function ($builder) use ($q) {
                $builder->where('name', 'like', '%' . $q . '%');
                $clean = preg_replace('/\D/', '', $q);
                if (strlen($clean) >= 8) {
                    $builder->orWhere('phone_e164', 'like', '%' . $clean . '%');
                }
                $builder->orWhere('phone', 'like', '%' . $q . '%');
                $builder->orWhere('cnpj', 'like', '%' . $q . '%');
            });
        }

        return $query->limit(50)->get(['id', 'name', 'phone', 'cnpj', 'address', 'city', 'state']);
    }
}
