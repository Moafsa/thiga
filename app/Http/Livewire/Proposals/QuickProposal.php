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
    public $salesperson_id;

    public $calculationResult = null;
    public $mapData = null;
    public $errorMessage = null;

    protected $rules = [
        'origin' => 'required|string',
        'destination' => 'required|string',
        'weight' => 'required|numeric|min:0.1',
        'invoice_value' => 'required|numeric|min:0',
        'client_id' => 'required|exists:clients,id',
    ];

    public function mount()
    {
        $user = Auth::user();
        $this->salesperson_id = $user->id; // Assuming user is salesperson or linked to one

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

    public function calculate()
    {
        $this->validate();
        $this->errorMessage = null;
        $this->calculationResult = null;
        $this->mapData = null;

        try {
            $tenant = Auth::user()->tenant;

            // 1. Calculate Freight
            $freightService = app(FreightCalculationService::class);
            $result = $freightService->calculate(
                $tenant,
                $this->destination,
                $this->weight,
                0, // Cubage optional for quick quote
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
                    $destCoords['longitude']
                );

                if ($route) {
                    $this->mapData = [
                        'origin' => $originCoords,
                        'destination' => $destCoords,
                        'route' => $route
                    ];
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
                'salesperson_id' => Auth::id(), // Or $this->salesperson_id
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

    public function render()
    {
        $clients = Client::where('tenant_id', Auth::user()->tenant_id)->orderBy('name')->get();
        return view('livewire.proposals.quick-proposal', [
            'clients' => $clients
        ]);
    }
}
