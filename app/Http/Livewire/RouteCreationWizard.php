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
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RouteCreationWizard extends Component
{
    use WithFileUploads;

    // Basic Data
    public $name;
    public $scheduled_date;
    public $start_time = '08:00';
    public $driver_id;
    public $vehicle_id;
    public $branch_id;
    public $origin_branch;
    public $is_custom_origin = false;
    public $manual_cte_numbers;
    public $cteErrorMessage = '';
    public $start_address_type = 'branch';

    // Shipments/Cargo
    public $selectedShipments = [];
    public $selectedCargo = [];
    public $searchShipment = '';
    public $selectAll = false;

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

    public function updatedXmlFiles($value = null)
    {
        $this->processXmlFiles();
    }

    private function extractXmlStringsFromFile($file): array
    {
        $contents = [];
        try {
            $filePath = $file->getRealPath();
            $clientExt = strtolower($file->getClientOriginalExtension());

            if ($clientExt === 'zip' || $file->getMimeType() === 'application/zip' || $file->getMimeType() === 'application/x-zip-compressed') {
                $zip = new \ZipArchive();
                if ($zip->open($filePath) === true) {
                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        $filename = $zip->getNameIndex($i);
                        if (strtolower(pathinfo($filename, PATHINFO_EXTENSION)) === 'xml') {
                            $stream = $zip->getStream($filename);
                            if ($stream) {
                                $contents[] = stream_get_contents($stream);
                                fclose($stream);
                            }
                        }
                    }
                    $zip->close();
                }
            } else {
                $raw = file_get_contents($filePath);
                if (!empty($raw)) {
                    $contents[] = $raw;
                }
            }
        } catch (\Exception $e) {
            \Log::error('Erro extraindo arquivo para XML: ' . $e->getMessage());
        }

        return $contents;
    }

    public function processXmlFiles()
    {
        @set_time_limit(600);
        @ini_set('memory_limit', '512M');

        $tenant = Auth::user()->tenant;
        $xmlParser = new CteXmlParserService();

        $defaultClient = \App\Models\Client::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Cliente Padrão (Sistema)'],
            ['address' => 'N/A', 'city' => 'N/A', 'state' => 'XX', 'zip_code' => '00000000', 'is_active' => true]
        );

        foreach ($this->xml_files as $file) {
            $xmlContents = $this->extractXmlStringsFromFile($file);

            foreach ($xmlContents as $xmlContent) {
                try {
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

                    if (!in_array($shipment->id, $this->selectedShipments)) {
                        $this->selectedShipments[] = $shipment->id;
                    }

                } catch (\Exception $e) {
                    \Log::error('Erro processando XML no criador de rotas: ' . $e->getMessage());
                }
            }
        }

        $this->xml_files = []; // reset
        $this->calculateTotals();
        $this->autoGenerateRouteName();
    }

    public function updatedOriginBranch($value = null)
    {
        $this->autoGenerateRouteName();
    }

    public function updatedManualCteNumbers($value = null)
    {
        $this->processManualCteNumbers();
    }

    public function processManualCteNumbers()
    {
        $this->cteErrorMessage = '';

        if (empty($this->manual_cte_numbers)) return;

        $tenant = Auth::user()->tenant;
        $cteTokens = preg_split('/[^0-9A-Za-z]+/', $this->manual_cte_numbers, -1, PREG_SPLIT_NO_EMPTY);
        if (empty($cteTokens)) return;

        $newlySelectedIds = [];
        $notFoundTokens = [];
        $alreadyUsedTokens = [];

        foreach ($cteTokens as $token) {
            $cleanNum = trim($token);
            if (empty($cleanNum)) continue;

            // 1. Search in Shipment table
            $shipment = Shipment::where('tenant_id', $tenant->id)
                ->where(function ($q) use ($cleanNum) {
                    $q->where('tracking_number', $cleanNum)
                      ->orWhere('tracking_number', 'CTE-' . $cleanNum)
                      ->orWhere('tracking_number', '#' . $cleanNum)
                      ->orWhere('title', 'like', "%{$cleanNum}%");
                })->first();

            if ($shipment) {
                if ($shipment->route_id !== null) {
                    $alreadyUsedTokens[] = $cleanNum;
                    continue;
                }
                if (!in_array($shipment->id, $this->selectedShipments)) {
                    $newlySelectedIds[] = $shipment->id;
                }
                continue;
            }

            // 2. If not found in Shipment table, search in CteXml table
            $cteXml = CteXml::where('tenant_id', $tenant->id)
                ->where(function ($q) use ($cleanNum) {
                    $q->where('cte_number', $cleanNum)
                      ->orWhere('access_key', 'like', "%{$cleanNum}%");
                })->first();

            if ($cteXml) {
                if ($cteXml->is_used) {
                    $alreadyUsedTokens[] = $cleanNum;
                    continue;
                }

                $this->syncLegacyXmls();
                $shipment = Shipment::where('tenant_id', $tenant->id)
                    ->where('tracking_number', $cteXml->cte_number)
                    ->first();

                if ($shipment) {
                    if ($shipment->route_id !== null) {
                        $alreadyUsedTokens[] = $cleanNum;
                        continue;
                    }
                    if (!in_array($shipment->id, $this->selectedShipments)) {
                        $newlySelectedIds[] = $shipment->id;
                    }
                    continue;
                }
            }

            // If not found anywhere
            $notFoundTokens[] = $cleanNum;
        }

        $errorMessages = [];
        if (!empty($alreadyUsedTokens)) {
            $usedStr = implode(', ', array_unique($alreadyUsedTokens));
            $errorMessages[] = "⛔ CT-e(s) já vinculados a outra rota: {$usedStr}. Não é possível reutilizá-los em uma nova rota.";
        }
        if (!empty($notFoundTokens)) {
            $missingStr = implode(', ', array_unique($notFoundTokens));
            $errorMessages[] = "⚠️ CT-e(s) não encontrado(s) no sistema: {$missingStr}. Por favor, verifique os números digitados ou faça o upload do arquivo XML correspondente.";
        }

        if (!empty($errorMessages)) {
            $this->cteErrorMessage = implode('<br>', $errorMessages);
        }

        if (!empty($newlySelectedIds)) {
            $this->selectedShipments = array_values(array_unique(array_merge($this->selectedShipments, $newlySelectedIds)));
            $this->calculateTotals();
            $this->autoGenerateRouteName();
        }
    }

    public function updatedSelectedShipments($value = null)
    {
        $this->calculateTotals();
    }

    private function calculateTotals()
    {
        $shipments = Shipment::whereIn('id', $this->selectedShipments)->get();
        $this->total_value = $shipments->sum('value');
        $this->total_weight = $shipments->sum('weight');
        $this->autoGenerateRouteName();
    }

    public function autoGenerateRouteName()
    {
        if (empty($this->selectedShipments)) {
            return;
        }

        $shipments = Shipment::whereIn('id', $this->selectedShipments)->get();
        if ($shipments->isEmpty()) return;

        // Origin label
        $firstShipment = $shipments->first();
        $originCity = $firstShipment->pickup_city ?: 'Origem';
        $originState = $firstShipment->pickup_state ?: '';
        
        $originLabel = !empty($this->origin_branch) 
            ? $this->origin_branch 
            : ($originState ? "{$originCity}/{$originState}" : $originCity);

        // Unique destinations
        $destinations = $shipments->map(function ($s) {
            if ($s->delivery_city && $s->delivery_state) {
                return "{$s->delivery_city}/{$s->delivery_state}";
            }
            return $s->delivery_city ?: $s->recipient_name;
        })->filter()->unique()->values();

        if ($destinations->count() === 1) {
            $destLabel = $destinations->first();
            $autoName = "Rota {$originLabel} → {$destLabel}";
        } elseif ($destinations->count() > 1) {
            $destLabel = $destinations->take(2)->implode(' + ');
            if ($destinations->count() > 2) {
                $extraCount = $destinations->count() - 2;
                $destLabel .= " (+{$extraCount} cidades)";
            }
            $autoName = "Rota {$originLabel} → {$destLabel}";
        } else {
            $autoName = "Rota {$originLabel} ({$shipments->count()} Cargas)";
        }

        // Auto-fill if name is empty or starts with Rota
        if (empty($this->name) || str_starts_with($this->name, 'Rota ')) {
            $this->name = $autoName;
        }
    }

    public function save()
    {
        try {
            $this->validate([
                'name' => 'required|min:3',
                'scheduled_date' => 'required|date',
            ]);

            $tenant = Auth::user()?->tenant ?: \Spatie\Multitenancy\Models\Tenant::current();
            if (!$tenant && Auth::user()?->tenant_id) {
                $tenant = \App\Models\Tenant::find(Auth::user()->tenant_id);
            }
            if (!$tenant) {
                $tenant = \App\Models\Tenant::first();
            }

            if (!$tenant) {
                $this->addError('name', 'Nenhum tenant ativo encontrado para esta sessão.');
                return;
            }

            $mapsService = app(\App\Services\MapsService::class);

            $branchId = !empty($this->branch_id) ? $this->branch_id : null;
            $branch = null;
            $startLat = null;
            $startLng = null;

            if ($branchId) {
                $branch = \App\Models\Branch::find($branchId);
            }

            if (!$branch) {
                $branch = \App\Models\Branch::where('tenant_id', $tenant->id)->first();
                if ($branch) {
                    $branchId = $branch->id;
                }
            }

            if ($branch) {
                if ($branch->latitude && $branch->longitude) {
                    $startLat = (float) $branch->latitude;
                    $startLng = (float) $branch->longitude;
                } else {
                    $branchFullAddr = trim(implode(', ', array_filter([
                        $branch->address,
                        $branch->address_number,
                        $branch->neighborhood,
                        $branch->city,
                        $branch->state,
                        $branch->postal_code,
                    ])));
                    if (!empty($branchFullAddr)) {
                        $geocodedBranch = $mapsService->geocode($branchFullAddr);
                        if ($geocodedBranch) {
                            $startLat = (float) $geocodedBranch['latitude'];
                            $startLng = (float) $geocodedBranch['longitude'];
                            $branch->update(['latitude' => $startLat, 'longitude' => $startLng]);
                        }
                    }
                }
            }

            if (!$startLat || !$startLng) {
                $startLat = -23.550520;
                $startLng = -46.633308;
            }

            $routeData = [
                'tenant_id' => $tenant->id,
                'name' => $this->name,
                'scheduled_date' => $this->scheduled_date,
                'start_time' => $this->start_time ?: '08:00',
                'driver_id' => !empty($this->driver_id) ? $this->driver_id : null,
                'vehicle_id' => !empty($this->vehicle_id) ? $this->vehicle_id : null,
                'branch_id' => $branchId,
                'start_latitude' => $startLat,
                'start_longitude' => $startLng,
                'end_latitude' => $startLat,
                'end_longitude' => $startLng,
                'status' => 'scheduled',
            ];

            if (Schema::hasColumn('routes', 'origin_branch')) {
                $routeData['origin_branch'] = !empty($this->origin_branch) ? $this->origin_branch : null;
            }

            $route = \App\Models\Route::create($routeData);

            if (!empty($this->selectedShipments)) {
                $shipmentUpdateData = [
                    'route_id' => $route->id,
                    'status' => 'scheduled'
                ];
                if (Schema::hasColumn('shipments', 'origin_branch')) {
                    $shipmentUpdateData['origin_branch'] = $this->origin_branch;
                }
                Shipment::whereIn('id', $this->selectedShipments)->update($shipmentUpdateData);
            }

            if (!empty($this->manual_cte_numbers)) {
                $cteNumbers = preg_split('/[^0-9A-Za-z]+/', $this->manual_cte_numbers, -1, PREG_SPLIT_NO_EMPTY);

                $defaultSender = \App\Models\Client::firstOrCreate(
                    ['tenant_id' => $tenant->id, 'name' => 'Remetente Padrão'],
                    ['is_active' => true]
                );
                $defaultReceiver = \App\Models\Client::firstOrCreate(
                    ['tenant_id' => $tenant->id, 'name' => 'Destinatário Padrão'],
                    ['is_active' => true]
                );

                $startAddress = $branch ? trim(implode(', ', array_filter([$branch->address, $branch->address_number]))) : 'Endereço de Origem';
                $startCity = $branch ? $branch->city : 'Cidade de Origem';
                $startState = $branch ? $branch->state : 'SP';
                $startZip = $branch ? ($branch->postal_code ?? '00000-000') : '00000-000';

                foreach ($cteNumbers as $cteNum) {
                    $cteNum = trim($cteNum);
                    if (empty($cteNum)) continue;

                    // 1. Check if existing Shipment already exists for this CTE number
                    $existingShipment = Shipment::where('tenant_id', $tenant->id)
                        ->where(function($q) use ($cteNum) {
                            $q->where('tracking_number', $cteNum)
                              ->orWhere('tracking_number', 'CTE-' . $cteNum)
                              ->orWhere('title', 'like', "%{$cteNum}%");
                        })
                        ->first();

                    if ($existingShipment) {
                        $updateData = ['route_id' => $route->id, 'status' => 'scheduled'];
                        if (Schema::hasColumn('shipments', 'origin_branch')) {
                            $updateData['origin_branch'] = $this->origin_branch;
                        }
                        $existingShipment->update($updateData);
                        continue;
                    }

                    // 2. Check if CteXml exists for this CTE number
                    $cteXml = \App\Models\CteXml::where('tenant_id', $tenant->id)
                        ->where('cte_number', $cteNum)
                        ->first();

                    $delivLat = null;
                    $delivLng = null;
                    $delivAddress = 'Endereço de Entrega';
                    $delivCity = $startCity;
                    $delivState = $startState;
                    $delivZip = $startZip;

                    if ($cteXml) {
                        try {
                            $xmlParser = app(\App\Services\CteXmlParserService::class);
                            $xmlContent = null;
                            if ($cteXml->xml_url && strpos($cteXml->xml_url, 'local:') === 0) {
                                $localPath = str_replace('local:', '', $cteXml->xml_url);
                                if (Storage::disk('local')->exists($localPath)) {
                                    $xmlContent = Storage::disk('local')->get($localPath);
                                }
                            }
                            if (!$xmlContent && $cteXml->xml) {
                                $xmlContent = $cteXml->xml;
                            }
                            if ($xmlContent) {
                                $cteData = $xmlParser->parseXml($xmlContent);
                                if (!empty($cteData['destination']['city'])) {
                                    $delivCity = $cteData['destination']['city'];
                                }
                                if (!empty($cteData['destination']['state'])) {
                                    $delivState = $cteData['destination']['state'];
                                }
                                if (!empty($cteData['destination']['address'])) {
                                    $delivAddress = $cteData['destination']['address'];
                                }
                                if (!empty($cteData['destination']['zip_code'])) {
                                    $delivZip = $cteData['destination']['zip_code'];
                                }
                            }
                        } catch (\Exception $ex) {
                            Log::warning('Error parsing CteXml in wizard: ' . $ex->getMessage());
                        }
                    }

                    // Geocode delivery location to obtain latitude & longitude for Mapbox/Google Maps
                    if ($delivCity && $delivState) {
                        $fullDelivAddr = trim(implode(', ', array_filter([$delivAddress !== 'Endereço de Entrega' ? $delivAddress : '', $delivCity, $delivState])));
                        $geocodedDeliv = $mapsService->geocode($fullDelivAddr);
                        if ($geocodedDeliv) {
                            $delivLat = (float) $geocodedDeliv['latitude'];
                            $delivLng = (float) $geocodedDeliv['longitude'];
                        }
                    }

                    if (!$delivLat || !$delivLng) {
                        $delivLat = $startLat;
                        $delivLng = $startLng;
                    }

                    $manualShipmentData = [
                        'tenant_id' => $tenant->id,
                        'route_id' => $route->id,
                        'sender_client_id' => $defaultSender->id,
                        'receiver_client_id' => $defaultReceiver->id,
                        'tracking_number' => 'CTE-' . $cteNum,
                        'tracking_code' => 'CTE-' . $cteNum,
                        'title' => 'CT-e #' . $cteNum,
                        'status' => 'scheduled',
                        'shipment_type' => 'delivery',
                        'value' => 0,
                        'recipient_name' => 'CT-e #' . $cteNum,
                        'pickup_address' => $startAddress ?: 'Endereço de Origem',
                        'pickup_city' => $startCity ?: 'Cidade de Origem',
                        'pickup_state' => $startState ?: 'SP',
                        'pickup_zip_code' => $startZip ?: '00000-000',
                        'pickup_latitude' => $startLat,
                        'pickup_longitude' => $startLng,
                        'delivery_address' => $delivAddress,
                        'delivery_city' => $delivCity,
                        'delivery_state' => $delivState,
                        'delivery_zip_code' => $delivZip,
                        'delivery_latitude' => $delivLat,
                        'delivery_longitude' => $delivLng,
                        'pickup_date' => $route->scheduled_date ?? now()->format('Y-m-d'),
                        'pickup_time' => '08:00',
                        'delivery_date' => $route->scheduled_date ?? now()->format('Y-m-d'),
                        'delivery_time' => '18:00',
                    ];

                    if (Schema::hasColumn('shipments', 'origin_branch')) {
                        $manualShipmentData['origin_branch'] = $this->origin_branch;
                    }

                    Shipment::create($manualShipmentData);
                }
            }

            // Calculate route options (waypoints, distance, duration, map path)
            try {
                $controller = app(\App\Http\Controllers\RouteController::class);
                $controller->calculateMultipleRouteOptions($route);
            } catch (\Exception $ex) {
                Log::warning('Error calculating route options in wizard: ' . $ex->getMessage());
            }

            return redirect()->route('routes.show', $route->id)
                ->with('success', 'Rota criada com sucesso!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('RouteCreationWizard save error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            $this->addError('name', 'Erro ao salvar rota: ' . $e->getMessage());
        }
    }

    public function mount()
    {
        $this->scheduled_date = now()->format('Y-m-d');
        $this->start_time = now()->format('H:i');
        $this->syncLegacyXmls();
    }

    public function syncLegacyXmls()
    {
        try {
            $tenant = Auth::user()?->tenant ?: \Spatie\Multitenancy\Models\Tenant::current();
            if (!$tenant) return;

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
                    Log::error('Erro ao sincronizar XML legado: ' . $e->getMessage());
                }
            }
        } catch (\Throwable $e) {
            Log::error('Error in syncLegacyXmls: ' . $e->getMessage());
        }
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $tenant = Auth::user()?->tenant ?: \Spatie\Multitenancy\Models\Tenant::current();
            if (!$tenant) return;
            $like = \Illuminate\Support\Facades\DB::getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $shipmentsQuery = Shipment::where('tenant_id', $tenant->id)->whereNull('route_id');
            if (!empty($this->searchShipment)) {
                $search = trim($this->searchShipment);
                $shipmentsQuery->where(function ($q) use ($search, $like) {
                    if (is_numeric($search)) {
                        $q->where('id', (int)$search);
                    }
                    $q->orWhere('tracking_number', $like, "%{$search}%")
                        ->orWhere('title', $like, "%{$search}%")
                        ->orWhere('tracking_code', $like, "%{$search}%")
                        ->orWhere('recipient_name', $like, "%{$search}%")
                        ->orWhere('cte_number', $like, "%{$search}%")
                        ->orWhere('invoice_number', $like, "%{$search}%")
                        ->orWhere('delivery_city', $like, "%{$search}%")
                        ->orWhere('pickup_city', $like, "%{$search}%");
                });
            }
            $allIds = $shipmentsQuery->pluck('id')->toArray();
            $this->selectedShipments = array_values(array_unique(array_merge($this->selectedShipments, $allIds)));
        } else {
            $this->selectedShipments = [];
        }
        $this->calculateTotals();
    }

    public function render()
    {
        $tenant = Auth::user()->tenant;
        $like = \Illuminate\Support\Facades\DB::getDriverName() === 'pgsql' ? 'ilike' : 'like';

        $shipmentsQuery = Shipment::where('tenant_id', $tenant->id)->whereNull('route_id');

        if (!empty($this->searchShipment)) {
            $search = trim($this->searchShipment);
            $shipmentsQuery->where(function ($q) use ($search, $like) {
                if (is_numeric($search)) {
                    $q->where('id', (int)$search);
                }
                $q->orWhere('tracking_number', $like, "%{$search}%")
                    ->orWhere('title', $like, "%{$search}%")
                    ->orWhere('tracking_code', $like, "%{$search}%")
                    ->orWhere('recipient_name', $like, "%{$search}%")
                    ->orWhere('cte_number', $like, "%{$search}%")
                    ->orWhere('invoice_number', $like, "%{$search}%")
                    ->orWhere('delivery_city', $like, "%{$search}%")
                    ->orWhere('pickup_city', $like, "%{$search}%");
            });
            // When actively searching, search across the ENTIRE system without taking limit 200
            $availableShipments = $shipmentsQuery->orderBy('created_at', 'desc')->get();
        } else {
            // When not searching, show 200 recent shipments
            $availableShipments = $shipmentsQuery->orderBy('created_at', 'desc')->take(200)->get();
        }

        // Always ensure selected shipments are present in $availableShipments list so they appear in the table with green 'Add' badge
        if (!empty($this->selectedShipments)) {
            $selectedObj = Shipment::whereIn('id', $this->selectedShipments)->get();
            $availableShipments = $availableShipments->merge($selectedObj)->unique('id')->values();
        }

        $companies = \App\Models\Company::where('tenant_id', $tenant->id)->where('is_active', true)->get();
        $branches = Branch::where('tenant_id', $tenant->id)->where('is_active', true)->get();

        $originOptions = collect();
        foreach ($companies as $comp) {
            $name = $comp->trade_name ?: $comp->name;
            $location = $comp->city && $comp->state ? " ({$comp->city}/{$comp->state})" : '';
            $originOptions->push(['label' => "🏢 Empresa: {$name}{$location}", 'value' => "{$name}{$location}"]);
        }
        foreach ($branches as $br) {
            $location = $br->city && $br->state ? " ({$br->city}/{$br->state})" : '';
            $originOptions->push(['label' => "📍 Filial: {$br->name}{$location}", 'value' => "{$br->name}{$location}"]);
        }

        return view('livewire.route-creation-wizard', [
            'drivers' => Driver::where('tenant_id', $tenant->id)->where('is_active', true)->get(),
            'vehicles' => Vehicle::where('tenant_id', $tenant->id)->where('is_active', true)->get(),
            'branches' => $branches,
            'originOptions' => $originOptions,
            'availableShipments' => $availableShipments,
            'availableCargo' => AvailableCargo::where('tenant_id', $tenant->id)->where('status', 'available')->get(),
        ]);
    }
}
