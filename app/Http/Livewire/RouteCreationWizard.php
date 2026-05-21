<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\Branch;
use App\Models\Shipment;
use App\Models\CteXml;
use App\Models\AvailableCargo;
use App\Services\CteXmlParserService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RouteCreationWizard extends Component
{
    use WithFileUploads;

    // Basic Data
    public $name;
    public $scheduled_date;
    public $driver_id;
    public $vehicle_id;
    public $branch_id;
    public $start_address_type = 'branch';

    // Shipments/Cargo
    public $selectedShipments = [];
    public $selectedCargo = [];

    // Summary & Calculation
    public $total_value = 0;
    public $total_weight = 0;

    // XML Upload
    public $xml_files = [];

    // Manual Cargo
    public $showManualModal = false;
    public $manual_receiver_name;
    public $manual_delivery_city;
    public $manual_delivery_state;
    public $manual_weight;
    public $manual_value;
    public $manual_description;

    public function createManualShipment()
    {
        $this->validate([
            'manual_receiver_name' => 'required|min:3',
            'manual_delivery_city' => 'required',
            'manual_delivery_state' => 'required|size:2',
            'manual_weight' => 'required|numeric|min:0',
            'manual_value' => 'required|numeric|min:0',
        ]);

        $tenant = Auth::user()->tenant;
        $tracking = 'MAN-' . strtoupper(Str::random(8));

        // Get or create a default client for this tenant to satisfy the DB constraint
        $defaultClient = \App\Models\Client::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Cliente Padrão (Sistema)'],
            ['address' => 'N/A', 'city' => 'N/A', 'state' => 'XX', 'zip_code' => '00000000', 'is_active' => true]
        );

        $shipment = Shipment::create([
            'tenant_id' => $tenant->id,
            'sender_client_id' => $defaultClient->id,
            'receiver_client_id' => $defaultClient->id,
            'tracking_number' => $tracking,
            'title' => $this->manual_description ?: 'Carga Avulsa ' . $tracking,
            'weight' => $this->manual_weight,
            'value' => $this->manual_value,
            'receiver_name' => $this->manual_receiver_name,
            'delivery_city' => $this->manual_delivery_city,
            'delivery_state' => strtoupper($this->manual_delivery_state),
            'status' => 'pending',
            'pickup_date' => now()->format('Y-m-d'),
            'pickup_time' => '08:00:00',
            'delivery_date' => now()->format('Y-m-d'),
            'delivery_time' => '18:00:00',
            // Dummy default addresses required by DB schema 
            'pickup_address' => 'Endereço Balcão',
            'pickup_city' => 'Origem',
            'pickup_state' => 'XX',
            'pickup_zip_code' => '00000000',
            'delivery_address' => 'A combinar',
            'delivery_zip_code' => '00000000',
        ]);

        $this->selectedShipments[] = $shipment->id;
        $this->calculateTotals();
        
        // Reset and close
        $this->reset(['manual_receiver_name', 'manual_delivery_city', 'manual_delivery_state', 'manual_weight', 'manual_value', 'manual_description']);
        $this->showManualModal = false;
        
        $this->dispatchBrowserEvent('close-manual-modal');
    }

    public function updatedXmlFiles()
    {
        $this->processXmlFiles();
    }

    public function processXmlFiles()
    {
        $tenant = Auth::user()->tenant;
        $xmlParser = new CteXmlParserService();

        $defaultClient = \App\Models\Client::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Cliente Padrão (Sistema)'],
            ['address' => 'N/A', 'city' => 'N/A', 'state' => 'XX', 'zip_code' => '00000000', 'is_active' => true]
        );

        foreach ($this->xml_files as $file) {
            try {
                $xmlContent = file_get_contents($file->getRealPath());
                if (empty($xmlContent)) continue;

                $cteData = $xmlParser->parseXml($xmlContent);
                if (empty($cteData['document_number'])) continue;

                $cteNumber = $cteData['document_number'];
                $accessKey = $cteData['access_key'] ?? null;

                // Create or find CTE XML
                $existingXml = CteXml::where('tenant_id', $tenant->id)
                    ->where('cte_number', $cteNumber)
                    ->first();

                if (!$existingXml) {
                    $filename = 'cte-' . ($accessKey ?: Str::random(16)) . '.xml';
                    $path = "tenants/{$tenant->id}/cte-xmls/{$filename}";
                    Storage::disk('local')->put($path, $xmlContent);

                    $existingXml = CteXml::create([
                        'tenant_id' => $tenant->id,
                        'cte_number' => $cteNumber,
                        'access_key' => $accessKey,
                        'xml_url' => 'local:' . $path,
                        'is_used' => false,
                    ]);
                }

                // Create Shipment instantly if not exists
                $shipment = Shipment::where('tenant_id', $tenant->id)
                    ->where('tracking_number', $cteNumber)
                    ->first();

                if (!$shipment) {
                    $receiverName = $cteData['destination']['name'] ?? 'Destinatário Desconhecido';
                    $shipment = Shipment::create([
                        'tenant_id' => $tenant->id,
                        'sender_client_id' => $defaultClient->id,
                        'receiver_client_id' => $defaultClient->id,
                        'tracking_number' => $cteNumber,
                        'title' => 'CT-e ' . $cteNumber,
                        'weight' => $cteData['weight'] ?? 0,
                        'volume' => $cteData['volume'] ?? 0,
                        'quantity' => $cteData['quantity'] ?? 1,
                        'value' => $cteData['goods_value'] ?? $cteData['value'] ?? 0,
                        'pickup_address' => $cteData['origin']['address'] ?? 'Endereço Origem',
                        'pickup_city' => $cteData['origin']['city'] ?? 'Cidade',
                        'pickup_state' => $cteData['origin']['state'] ?? 'XX',
                        'pickup_zip_code' => $cteData['origin']['zip_code'] ?? '00000000',
                        'pickup_date' => $cteData['pickup_date'] ?? now()->format('Y-m-d'),
                        'pickup_time' => '08:00:00',
                        'delivery_address' => $cteData['destination']['address'] ?? 'Endereço Destino',
                        'delivery_city' => $cteData['destination']['city'] ?? 'Cidade',
                        'delivery_state' => $cteData['destination']['state'] ?? 'XX',
                        'delivery_zip_code' => $cteData['destination']['zip_code'] ?? '00000000',
                        'delivery_date' => $cteData['delivery_date'] ?? now()->format('Y-m-d'),
                        'delivery_time' => '18:00:00',
                        'status' => 'pending',
                        'freight_value' => $cteData['value'] ?? 0,
                    ]);
                }

                // Add to selected array if not already in
                if (!in_array($shipment->id, $this->selectedShipments)) {
                    $this->selectedShipments[] = $shipment->id;
                }

            } catch (\Exception $e) {
                // Ignore parsing errors for individual files to continue processing others
                \Log::error('Erro lendo XML no Command Center: ' . $e->getMessage());
            }
        }

        $this->xml_files = []; // reset
        $this->calculateTotals();
    }

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
        $this->syncLegacyXmls();
    }

    public function syncLegacyXmls()
    {
        $tenant = Auth::user()->tenant;
        $xmlParser = new CteXmlParserService();
        
        $defaultClient = \App\Models\Client::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Cliente Padrão (Sistema)'],
            ['address' => 'N/A', 'city' => 'N/A', 'state' => 'XX', 'zip_code' => '00000000', 'is_active' => true]
        );

        // Get XMLs that don't have a corresponding shipment
        $unsyncedXmls = CteXml::where('tenant_id', $tenant->id)
            ->where('is_used', false)
            ->get();
            
        // We will do a manual filter for safety
        $shipmentTrackingNumbers = Shipment::where('tenant_id', $tenant->id)->pluck('tracking_number')->toArray();
        
        foreach ($unsyncedXmls as $xmlRecord) {
            if (in_array($xmlRecord->cte_number, $shipmentTrackingNumbers)) {
                continue;
            }

            try {
                $path = str_replace('local:', '', $xmlRecord->xml_url);
                if (Storage::disk('local')->exists($path)) {
                    $xmlContent = Storage::disk('local')->get($path);
                    if (empty($xmlContent)) continue;
                    
                    $cteData = $xmlParser->parseXml($xmlContent);
                    if (empty($cteData['document_number'])) continue;
                    
                    $cteNumber = $cteData['document_number'];
                    
                    Shipment::create([
                        'tenant_id' => $tenant->id,
                        'sender_client_id' => $defaultClient->id,
                        'receiver_client_id' => $defaultClient->id,
                        'tracking_number' => $cteNumber,
                        'title' => 'CT-e ' . $cteNumber,
                        'weight' => $cteData['weight'] ?? 0,
                        'volume' => $cteData['volume'] ?? 0,
                        'quantity' => $cteData['quantity'] ?? 1,
                        'value' => $cteData['goods_value'] ?? $cteData['value'] ?? 0,
                        'pickup_address' => $cteData['origin']['address'] ?? 'Endereço Origem',
                        'pickup_city' => $cteData['origin']['city'] ?? 'Cidade',
                        'pickup_state' => $cteData['origin']['state'] ?? 'XX',
                        'pickup_zip_code' => $cteData['origin']['zip_code'] ?? '00000000',
                        'pickup_date' => $cteData['pickup_date'] ?? now()->format('Y-m-d'),
                        'pickup_time' => '08:00:00',
                        'delivery_address' => $cteData['destination']['address'] ?? 'Endereço Destino',
                        'delivery_city' => $cteData['destination']['city'] ?? 'Cidade',
                        'delivery_state' => $cteData['destination']['state'] ?? 'XX',
                        'delivery_zip_code' => $cteData['destination']['zip_code'] ?? '00000000',
                        'delivery_date' => $cteData['delivery_date'] ?? now()->format('Y-m-d'),
                        'delivery_time' => '18:00:00',
                        'status' => 'pending',
                        'freight_value' => $cteData['value'] ?? 0,
                    ]);
                    
                    $shipmentTrackingNumbers[] = $cteNumber; // prevent duplicate creation in loop
                }
            } catch (\Exception $e) {
                \Log::error('Erro ao sincronizar XML legado: ' . $e->getMessage());
            }
        }
    }

    public function render()
    {
        $tenant = Auth::user()->tenant;

        return view('livewire.route-creation-wizard', [
            'drivers' => Driver::where('tenant_id', $tenant->id)->where('is_active', true)->get(),
            'vehicles' => Vehicle::where('tenant_id', $tenant->id)->where('is_active', true)->get(),
            'branches' => Branch::where('tenant_id', $tenant->id)->where('is_active', true)->get(),
            'availableShipments' => Shipment::where('tenant_id', $tenant->id)->whereNull('route_id')->orderBy('created_at', 'desc')->get(),
            'availableCargo' => AvailableCargo::where('tenant_id', $tenant->id)->where('status', 'available')->get(),
        ]);
    }
}
