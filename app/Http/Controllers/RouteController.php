<?php

namespace App\Http\Controllers;

use App\Models\Route;
use App\Models\Shipment;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\Branch;
use App\Models\Company;
use App\Models\FiscalDocument;
use App\Models\Client;
use App\Services\CteXmlParserService;
use App\Services\GoogleMapsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RouteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of routes
     */
    public function index(Request $request)
    {
        $tenant = Auth::user()->tenant;
        
        if (!$tenant) {
            return redirect()->route('login')->with('error', 'Usuário não possui tenant associado.');
        }

        $query = Route::where('tenant_id', $tenant->id)
            ->with(['driver:id,name,phone', 'vehicle:id,plate,brand,model', 'shipments:id,route_id,tracking_number,title,status'])
            ->select('id', 'name', 'driver_id', 'vehicle_id', 'status', 'scheduled_date', 'created_at');

        // Filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('scheduled_date')) {
            $query->where('scheduled_date', $request->scheduled_date);
        }

        $routes = $query->orderBy('scheduled_date', 'desc')->paginate(20);

        $drivers = Driver::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $vehicles = Vehicle::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderBy('plate')
            ->get();

        return view('routes.index', compact('routes', 'drivers', 'vehicles'));
    }

    /**
     * Show the form for creating a new route
     */
    public function create()
    {
        $tenant = Auth::user()->tenant;
        
        $drivers = Driver::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->with('vehicles')
            ->orderBy('name')
            ->get();

        $vehicles = Vehicle::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->where('status', 'available')
            ->orderBy('plate')
            ->get();

        // Get active branches (pavilhões) for route origin selection
        $branches = Branch::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->where('is_operational', true)
            ->orderBy('name')
            ->get();

        // Get company for creating new branches
        $company = Company::where('tenant_id', $tenant->id)->first();

        // Buscar shipments disponíveis (sem rota ou com CT-e autorizado)
        // Otimizado: usando subquery ao invés de whereHas para melhor performance
        $availableShipments = Shipment::where('tenant_id', $tenant->id)
            ->whereNull('route_id')
            ->where(function($query) {
                $query->whereExists(function($q) {
                    $q->select(\DB::raw(1))
                      ->from('fiscal_documents')
                      ->whereColumn('fiscal_documents.shipment_id', 'shipments.id')
                      ->where('fiscal_documents.document_type', 'cte')
                      ->where('fiscal_documents.status', 'authorized');
                })
                ->orWhereNotExists(function($q) {
                    $q->select(\DB::raw(1))
                      ->from('fiscal_documents')
                      ->whereColumn('fiscal_documents.shipment_id', 'shipments.id');
                });
            })
            ->orderBy('created_at', 'desc')
            ->limit(100) // Limitar resultados para evitar carregar muitos dados
            ->get();

        return view('routes.create', compact('drivers', 'vehicles', 'branches', 'availableShipments', 'company'));
    }

    /**
     * Store a newly created route
     */
    public function store(Request $request, CteXmlParserService $xmlParser)
    {
        $tenant = Auth::user()->tenant;

        \Log::info('Route store request received', [
            'has_files' => $request->hasFile('cte_xml_files'),
            'files_count' => $request->hasFile('cte_xml_files') ? count($request->file('cte_xml_files')) : 0,
            'all_input' => $request->except(['_token', 'cte_xml_files']),
        ]);

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'branch_id' => 'nullable|exists:branches,id',
                'start_address_type' => 'nullable|in:branch,current_location,manual',
                'start_address' => 'required_if:start_address_type,manual|nullable|string|max:255',
                'start_city' => 'required_if:start_address_type,manual|nullable|string|max:255',
                'start_state' => 'required_if:start_address_type,manual|nullable|string|size:2',
                'start_zip_code' => 'nullable|string|max:10',
                'driver_id' => 'nullable|exists:drivers,id',
                'vehicle_id' => 'nullable|exists:vehicles,id',
                'scheduled_date' => 'nullable|date',
                'start_time' => 'nullable|string',
                'end_time' => 'nullable|string',
                'shipment_ids' => 'nullable|array',
                'shipment_ids.*' => 'exists:shipments,id',
                'cte_xml_files' => 'nullable|array',
                'cte_xml_files.*' => 'file|mimes:xml,text/xml,application/xml|max:10240',
                'addresses' => 'nullable|array',
                'addresses.*.address' => 'required_with:addresses|string|max:255',
                'addresses.*.city' => 'required_with:addresses|string|max:255',
                'addresses.*.state' => 'required_with:addresses|string|size:2',
                'addresses.*.zip_code' => 'nullable|string|max:10',
                'addresses.*.recipient_name' => 'nullable|string|max:255',
                'notes' => 'nullable|string',
            ]);

            // Validate that at least one start address method is provided
            if (empty($validated['branch_id']) && empty($validated['start_address_type'])) {
                return back()->withErrors(['start_address_type' => 'É necessário selecionar um pavilhão, usar a localização atual ou informar um endereço manual.'])->withInput();
            }

            // If using current location, driver must be selected
            if ($validated['start_address_type'] === 'current_location' && empty($validated['driver_id'])) {
                return back()->withErrors(['driver_id' => 'É necessário selecionar um motorista para usar a localização atual.'])->withInput();
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed', [
                'errors' => $e->errors(),
                'input' => $request->except(['_token', 'cte_xml_files']),
            ]);
            return back()->withErrors($e->errors())->withInput();
        }

        // Validate that vehicle belongs to driver if both are provided
        if ($request->filled('vehicle_id') && $request->filled('driver_id')) {
            $driver = Driver::findOrFail($validated['driver_id']);
            $vehicle = Vehicle::findOrFail($validated['vehicle_id']);
            
            // Check if vehicle is assigned to driver
            if (!$driver->vehicles->contains($vehicle->id)) {
                return back()->withErrors(['vehicle_id' => 'O veículo selecionado não está atribuído ao motorista selecionado.'])->withInput();
            }
            
            // Check if vehicle is available
            if (!$vehicle->isAvailable()) {
                return back()->withErrors(['vehicle_id' => 'O veículo selecionado não está disponível.'])->withInput();
            }
        }

        $validated['tenant_id'] = $tenant->id;
        $validated['status'] = 'scheduled';
        
        // Set default scheduled_date if not provided
        if (!isset($validated['scheduled_date']) || empty($validated['scheduled_date'])) {
            $validated['scheduled_date'] = now()->format('Y-m-d');
        }
        
        // Set default start_time if not provided (optional field)
        if (!isset($validated['start_time']) || empty($validated['start_time'])) {
            $validated['start_time'] = null; // Now nullable, so null is acceptable
        }

        DB::beginTransaction();
        try {
            $route = Route::create($validated);
            $hasShipments = false;

            // Process XML files if provided
            if ($request->hasFile('cte_xml_files')) {
                \Log::info('Processing XML files', [
                    'files_count' => count($request->file('cte_xml_files')),
                    'route_id' => $route->id,
                ]);
                
                try {
                    $createdShipments = $this->processXmlFiles($request->file('cte_xml_files'), $tenant, $xmlParser, $route);
                    
                    \Log::info('XML files processed', [
                        'created_shipments_count' => count($createdShipments),
                    ]);
                    
                    if (empty($createdShipments)) {
                        DB::rollBack();
                        \Log::warning('No shipments created from XML files');
                        return back()->withErrors(['cte_xml_files' => 'Falha ao processar arquivos XML. Verifique os arquivos e tente novamente.'])->withInput();
                    }
                    
                    $hasShipments = true;
                } catch (\Exception $e) {
                    DB::rollBack();
                    \Log::error('Error processing XML files', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    return back()->withErrors(['cte_xml_files' => 'Erro ao processar arquivos XML: ' . $e->getMessage()])->withInput();
                }
            } else {
                \Log::info('No XML files provided in request');
            }

            // Process addresses if provided
            if ($request->has('addresses') && !empty($request->addresses)) {
                try {
                    $createdShipments = $this->processAddresses($request->addresses, $tenant, $route);
                    if (!empty($createdShipments)) {
                        $hasShipments = true;
                    }
                } catch (\Exception $e) {
                    DB::rollBack();
                    \Log::error('Error processing addresses', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    return back()->withErrors(['error' => 'Erro ao processar endereços: ' . $e->getMessage()])->withInput();
                }
            }

            // Associate existing shipments to route
            if ($request->has('shipment_ids') && !empty($request->shipment_ids)) {
                $updated = Shipment::whereIn('id', $request->shipment_ids)
                    ->where('tenant_id', $tenant->id)
                    ->update(['route_id' => $route->id]);
                
                if ($updated > 0) {
                    $hasShipments = true;
                }
            }

            // Validate that route has at least one shipment
            if (!$hasShipments) {
                DB::rollBack();
                \Log::warning('Route created without shipments');
                return back()->withErrors(['error' => 'A rota deve ter pelo menos um endereço, shipment existente ou arquivo XML de CT-e.'])->withInput();
            }

            // Determine start address coordinates
            $googleMapsService = app(GoogleMapsService::class);
            $startLat = null;
            $startLng = null;
            $startAddressType = $validated['start_address_type'] ?? 'branch';

            if ($startAddressType === 'branch' && $validated['branch_id']) {
                $branch = Branch::find($validated['branch_id']);
                if ($branch) {
                    // Get branch coordinates if not set
                    if (!$branch->latitude || !$branch->longitude) {
                        $fullAddress = trim(implode(', ', array_filter([
                            $branch->address,
                            $branch->address_number,
                            $branch->neighborhood,
                            $branch->city,
                            $branch->state,
                        ])));
                        
                        $geocoded = $googleMapsService->geocode($fullAddress);
                        if ($geocoded) {
                            $branch->update([
                                'latitude' => $geocoded['latitude'],
                                'longitude' => $geocoded['longitude'],
                            ]);
                        }
                    }
                    $startLat = $branch->latitude;
                    $startLng = $branch->longitude;
                }
            } elseif ($startAddressType === 'current_location' && $validated['driver_id']) {
                $driver = Driver::find($validated['driver_id']);
                if ($driver && $driver->current_latitude && $driver->current_longitude) {
                    $startLat = $driver->current_latitude;
                    $startLng = $driver->current_longitude;
                } else {
                    return back()->withErrors(['driver_id' => 'O motorista selecionado não possui localização atual disponível.'])->withInput();
                }
            } elseif ($startAddressType === 'manual') {
                $fullAddress = trim(implode(', ', array_filter([
                    $validated['start_address'] ?? '',
                    $validated['start_city'] ?? '',
                    $validated['start_state'] ?? '',
                    $validated['start_zip_code'] ?? '',
                ])));
                
                $geocoded = $googleMapsService->geocode($fullAddress);
                if ($geocoded) {
                    $startLat = $geocoded['latitude'];
                    $startLng = $geocoded['longitude'];
                } else {
                    return back()->withErrors(['start_address' => 'Não foi possível geocodificar o endereço informado. Verifique os dados e tente novamente.'])->withInput();
                }
            }

            // Update route with start coordinates
            if ($startLat && $startLng) {
                $route->update([
                    'start_latitude' => $startLat,
                    'start_longitude' => $startLng,
                ]);
            }

            // Calculate multiple route options
            $this->calculateMultipleRouteOptions($route);

            // Update vehicle status if vehicle is assigned
            if ($route->vehicle_id) {
                $vehicle = Vehicle::find($route->vehicle_id);
                if ($vehicle && $vehicle->status === 'available') {
                    $vehicle->update(['status' => 'in_use']);
                }
            }

            DB::commit();

            \Log::info('Route created successfully', [
                'route_id' => $route->id,
                'has_xml_files' => $request->hasFile('cte_xml_files'),
            ]);

            // Redirect to route selection page if route options were calculated
            if ($route->route_options && count($route->route_options) > 0) {
                return redirect()->route('routes.select-route', $route)
                    ->with('success', 'Rota criada! Escolha uma das opções de rota disponíveis.');
            }

            return redirect()->route('routes.show', $route)
                ->with('success', 'Rota criada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error creating route', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->withErrors(['error' => 'Falha ao criar rota: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Process uploaded CT-e XML files and create shipments
     */
    protected function processXmlFiles(array $files, $tenant, CteXmlParserService $xmlParser, Route $route): array
    {
        $createdShipments = [];

        foreach ($files as $file) {
            try {
                \Log::info('Processing XML file', [
                    'filename' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                ]);
                
                $xmlContent = file_get_contents($file->getRealPath());
                
                if (empty($xmlContent)) {
                    throw new \Exception('Arquivo XML vazio ou inválido');
                }
                
                // Parse XML
                $cteData = $xmlParser->parseXml($xmlContent);
                
                if (empty($cteData['origin']['address']) || empty($cteData['destination']['address'])) {
                    throw new \Exception('Não foi possível extrair endereços do XML. Verifique se o arquivo é um CT-e válido.');
                }
                
                // Find or create sender client
                $senderClient = $this->findOrCreateClient($tenant, $cteData['origin']);
                
                // Find or create receiver client
                $receiverClient = $this->findOrCreateClient($tenant, $cteData['destination']);
                
                // Build full addresses
                $pickupAddress = trim(implode(', ', array_filter([
                    $cteData['origin']['address'] ?? '',
                    $cteData['origin']['number'] ?? '',
                    $cteData['origin']['complement'] ?? '',
                ])));
                
                $deliveryAddress = trim(implode(', ', array_filter([
                    $cteData['destination']['address'] ?? '',
                    $cteData['destination']['number'] ?? '',
                    $cteData['destination']['complement'] ?? '',
                ])));
                
                // Create shipment - using only fields that exist in the table
                $trackingNumber = 'THG' . strtoupper(Str::random(8));
                $shipment = Shipment::create([
                    'tenant_id' => $tenant->id,
                    'route_id' => $route->id,
                    'sender_client_id' => $senderClient->id,
                    'receiver_client_id' => $receiverClient->id,
                    'tracking_number' => $trackingNumber,
                    'tracking_code' => $trackingNumber, // Required field
                    'title' => 'CT-e ' . ($cteData['document_number'] ?? substr($cteData['access_key'] ?? 'N/A', 0, 20)),
                    'recipient_name' => $cteData['destination']['name'] ?? 'Destinatário',
                    'recipient_address' => $deliveryAddress ?: ($cteData['destination']['address'] ?? ''),
                    'recipient_city' => $cteData['destination']['city'] ?? '',
                    'recipient_state' => $cteData['destination']['state'] ?? '',
                    'recipient_zip_code' => $cteData['destination']['zip_code'] ?? '',
                    'pickup_address' => $pickupAddress ?: ($cteData['origin']['address'] ?? ''),
                    'pickup_city' => $cteData['origin']['city'] ?? '',
                    'pickup_state' => $cteData['origin']['state'] ?? '',
                    'pickup_zip_code' => $cteData['origin']['zip_code'] ?? '',
                    'delivery_address' => $deliveryAddress ?: ($cteData['destination']['address'] ?? ''),
                    'delivery_city' => $cteData['destination']['city'] ?? '',
                    'delivery_state' => $cteData['destination']['state'] ?? '',
                    'delivery_zip_code' => $cteData['destination']['zip_code'] ?? '',
                    'pickup_date' => $cteData['pickup_date'] ?? $route->scheduled_date,
                    'pickup_time' => '08:00',
                    'delivery_date' => ($cteData['delivery_date'] ?? $route->scheduled_date) . ' 18:00:00',
                    'delivery_time' => '18:00',
                    'value' => $cteData['value'] ?? 0,
                    'goods_value' => $cteData['value'] ?? 0,
                    'weight' => $cteData['weight'] ?? 0,
                    'volume' => $cteData['volume'] ?? 0,
                    'quantity' => $cteData['quantity'] ?? 1,
                    'cte_number' => $cteData['document_number'] ?? null,
                    'status' => 'scheduled',
                    'delivery_notes' => 'Shipment created from CT-e XML',
                ]);
                
                \Log::info('Shipment created from XML', [
                    'shipment_id' => $shipment->id,
                    'access_key' => $cteData['access_key'] ?? 'N/A',
                ]);
                
                // Save XML to MinIO (with fallback to database)
                $xmlPath = $this->saveXmlToStorage($xmlContent, $cteData['access_key'] ?? 'cte-' . $shipment->id, $tenant->id);
                
                // Create fiscal document
                FiscalDocument::create([
                    'tenant_id' => $tenant->id,
                    'shipment_id' => $shipment->id,
                    'route_id' => $route->id,
                    'document_type' => 'cte',
                    'access_key' => $cteData['access_key'],
                    'status' => 'authorized',
                    'xml_url' => $xmlPath,
                    'xml' => $xmlPath ? null : $xmlContent, // Save in DB only if MinIO failed
                    'authorized_at' => now(),
                ]);
                
                \Log::info('Fiscal document created', [
                    'access_key' => $cteData['access_key'] ?? 'N/A',
                    'xml_path' => $xmlPath ?? 'database',
                ]);
                
                $createdShipments[] = $shipment;
            } catch (\Exception $e) {
                \Log::error('Falha ao processar arquivo XML de CT-e', [
                    'arquivo' => $file->getClientOriginalName(),
                    'erro' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                throw $e;
            }
        }

        return $createdShipments;
    }

    /**
     * Process addresses and create shipments
     */
    protected function processAddresses(array $addresses, $tenant, Route $route): array
    {
        $createdShipments = [];
        $googleMapsService = app(GoogleMapsService::class);
        
        // Create a default sender client if doesn't exist
        $defaultSender = Client::where('tenant_id', $tenant->id)
            ->where('name', 'like', '%Remetente%')
            ->first();
        
        if (!$defaultSender) {
            $defaultSender = Client::create([
                'tenant_id' => $tenant->id,
                'name' => 'Remetente Padrão',
                'is_active' => true,
            ]);
        }
        
        // Process addresses in pairs (pickup -> delivery)
        for ($i = 0; $i < count($addresses); $i++) {
            $currentAddress = $addresses[$i];
            $nextAddress = isset($addresses[$i + 1]) ? $addresses[$i + 1] : null;
            
            // If there's a next address, create shipment from current to next
            // Otherwise, create shipment from current to current (single address)
            $pickupAddress = $currentAddress;
            $deliveryAddress = $nextAddress ?: $currentAddress;
            
            // Geocode addresses
            $pickupFullAddress = trim(implode(', ', array_filter([
                $pickupAddress['address'] ?? '',
                $pickupAddress['city'] ?? '',
                $pickupAddress['state'] ?? '',
                $pickupAddress['zip_code'] ?? '',
            ])));
            
            $deliveryFullAddress = trim(implode(', ', array_filter([
                $deliveryAddress['address'] ?? '',
                $deliveryAddress['city'] ?? '',
                $deliveryAddress['state'] ?? '',
                $deliveryAddress['zip_code'] ?? '',
            ])));
            
            $pickupCoords = $googleMapsService->geocode($pickupFullAddress);
            $deliveryCoords = $googleMapsService->geocode($deliveryFullAddress);
            
            // Create or find receiver client
            $receiverClient = $this->findOrCreateClient($tenant, [
                'name' => $deliveryAddress['recipient_name'] ?? 'Destinatário',
                'address' => $deliveryAddress['address'] ?? '',
                'city' => $deliveryAddress['city'] ?? '',
                'state' => $deliveryAddress['state'] ?? '',
                'zip_code' => $deliveryAddress['zip_code'] ?? '',
            ]);
            
            $trackingNumber = 'THG' . strtoupper(Str::random(8));
            $shipment = Shipment::create([
                'tenant_id' => $tenant->id,
                'route_id' => $route->id,
                'sender_client_id' => $defaultSender->id,
                'receiver_client_id' => $receiverClient->id,
                'tracking_number' => $trackingNumber,
                'tracking_code' => $trackingNumber,
                'title' => 'Entrega ' . ($i + 1) . ' - ' . ($deliveryAddress['recipient_name'] ?? $deliveryAddress['city'] ?? 'Destinatário'),
                'recipient_name' => $deliveryAddress['recipient_name'] ?? 'Destinatário',
                'recipient_address' => $deliveryAddress['address'] ?? '',
                'recipient_city' => $deliveryAddress['city'] ?? '',
                'recipient_state' => $deliveryAddress['state'] ?? '',
                'recipient_zip_code' => $deliveryAddress['zip_code'] ?? '',
                'pickup_address' => $pickupAddress['address'] ?? '',
                'pickup_city' => $pickupAddress['city'] ?? '',
                'pickup_state' => $pickupAddress['state'] ?? '',
                'pickup_zip_code' => $pickupAddress['zip_code'] ?? '',
                'pickup_latitude' => $pickupCoords['latitude'] ?? null,
                'pickup_longitude' => $pickupCoords['longitude'] ?? null,
                'delivery_address' => $deliveryAddress['address'] ?? '',
                'delivery_city' => $deliveryAddress['city'] ?? '',
                'delivery_state' => $deliveryAddress['state'] ?? '',
                'delivery_zip_code' => $deliveryAddress['zip_code'] ?? '',
                'delivery_latitude' => $deliveryCoords['latitude'] ?? null,
                'delivery_longitude' => $deliveryCoords['longitude'] ?? null,
                'pickup_date' => $route->scheduled_date,
                'pickup_time' => '08:00',
                'delivery_date' => $route->scheduled_date,
                'delivery_time' => '18:00',
                'status' => 'scheduled',
                'delivery_notes' => 'Shipment created from address input',
            ]);
            
            $createdShipments[] = $shipment;
            
            // Only create one shipment per address pair
            if ($nextAddress) {
                $i++; // Skip next address as it's already used as delivery
            }
        }
        
        return $createdShipments;
    }

    /**
     * Find or create client from address data
     */
    protected function findOrCreateClient($tenant, array $addressData): Client
    {
        $cnpj = isset($addressData['cnpj']) ? preg_replace('/\D/', '', $addressData['cnpj']) : null;
        
        $client = null;
        
        if ($cnpj) {
            $client = Client::where('tenant_id', $tenant->id)
                ->where('cnpj', $cnpj)
                ->first();
        }
        
        if (!$client) {
            $client = Client::create([
                'tenant_id' => $tenant->id,
                'name' => $addressData['name'] ?? 'Unknown',
                'cnpj' => $cnpj,
                'address' => $addressData['address'] ?? '',
                'city' => $addressData['city'] ?? '',
                'state' => $addressData['state'] ?? '',
                'zip_code' => $addressData['zip_code'] ?? '',
                'is_active' => true,
            ]);
        } else {
            // Update address if needed
            $updateData = [];
            if (!empty($addressData['address']) && empty($client->address)) {
                $updateData['address'] = $addressData['address'];
            }
            if (!empty($addressData['city']) && empty($client->city)) {
                $updateData['city'] = $addressData['city'];
            }
            if (!empty($addressData['state']) && empty($client->state)) {
                $updateData['state'] = $addressData['state'];
            }
            if (!empty($addressData['zip_code']) && empty($client->zip_code)) {
                $updateData['zip_code'] = $addressData['zip_code'];
            }
            
            if (!empty($updateData)) {
                $client->update($updateData);
            }
        }
        
        return $client;
    }

    /**
     * Display the specified route
     */
    public function show(Route $route)
    {
        $this->authorizeAccess($route);

        $route->load(['branch', 'driver', 'vehicle', 'shipments.senderClient', 'shipments.receiverClient', 'shipments.fiscalDocuments']);
        
        // Recalculate metrics if not set and route is not locked
        // If route is locked, use selected route option data
        if ($route->is_route_locked && $route->selected_route_option) {
            $selectedRouteData = $route->getSelectedRouteOptionData();
            if ($selectedRouteData && (!$route->estimated_distance || !$route->estimated_duration)) {
                $route->update([
                    'estimated_distance' => $selectedRouteData['distance'] / 1000,
                    'estimated_duration' => round($selectedRouteData['duration'] / 60),
                ]);
                $route->refresh();
            }
        } elseif (!$route->is_route_locked && !$route->estimated_distance && $route->shipments->isNotEmpty()) {
            // Only calculate if route options haven't been calculated yet
            if (!$route->route_options || empty($route->route_options)) {
                $this->calculateMultipleRouteOptions($route);
                $route->refresh();
            }
        }
        
        // Get MDF-e if exists
        $mdfe = FiscalDocument::where('route_id', $route->id)
            ->where('document_type', 'mdfe')
            ->orderBy('created_at', 'desc')
            ->first();

        return view('routes.show', compact('route', 'mdfe'));
    }

    /**
     * Show the form for editing the specified route
     */
    public function edit(Route $route)
    {
        $this->authorizeAccess($route);

        // Check if route is locked and user is not admin
        if ($route->is_route_locked && !Auth::user()->isTenantAdmin() && !Auth::user()->isSuperAdmin()) {
            return redirect()->route('routes.show', $route)
                ->with('error', 'Esta rota foi bloqueada e não pode ser alterada. Apenas administradores podem modificar rotas bloqueadas.');
        }

        $tenant = Auth::user()->tenant;
        
        $drivers = Driver::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->with('vehicles')
            ->orderBy('name')
            ->get();

        $vehicles = Vehicle::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderBy('plate')
            ->get();

        $branches = Branch::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->where('is_operational', true)
            ->orderBy('name')
            ->get();

        $availableShipments = Shipment::where('tenant_id', $tenant->id)
            ->where(function($q) use ($route) {
                $q->whereNull('route_id')
                  ->orWhere('route_id', $route->id);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $route->load(['branch', 'shipments', 'vehicle']);

        return view('routes.edit', compact('route', 'drivers', 'vehicles', 'branches', 'availableShipments'));
    }

    /**
     * Update the specified route
     */
    public function update(Request $request, Route $route)
    {
        $this->authorizeAccess($route);

        // Check if route is locked and user is not admin
        if ($route->is_route_locked && !Auth::user()->isTenantAdmin() && !Auth::user()->isSuperAdmin()) {
            return back()->withErrors(['error' => 'Esta rota foi bloqueada e não pode ser alterada. Apenas administradores podem modificar rotas bloqueadas.'])->withInput();
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'driver_id' => 'required|exists:drivers,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'scheduled_date' => 'required|date',
            'start_time' => 'nullable|string',
            'end_time' => 'nullable|string',
            'status' => 'nullable|string|in:scheduled,in_progress,completed,cancelled',
            'shipment_ids' => 'nullable|array',
            'shipment_ids.*' => 'exists:shipments,id',
            'notes' => 'nullable|string',
        ]);

        // Validate that vehicle belongs to driver if vehicle is provided
        if ($request->filled('vehicle_id')) {
            $driver = Driver::findOrFail($validated['driver_id']);
            $vehicle = Vehicle::findOrFail($validated['vehicle_id']);
            
            // Check if vehicle is assigned to driver
            if (!$driver->vehicles->contains($vehicle->id)) {
                return back()->withErrors(['vehicle_id' => 'O veículo selecionado não está atribuído ao motorista selecionado.'])->withInput();
            }
            
            // Check if vehicle is available (unless route is already in progress)
            if ($route->status !== 'in_progress' && !$vehicle->isAvailable()) {
                return back()->withErrors(['vehicle_id' => 'O veículo selecionado não está disponível.'])->withInput();
            }
        }

        $oldVehicleId = $route->vehicle_id;
        $oldStatus = $route->status;
        
        $route->update($validated);

        // Update vehicle status based on route status
        if ($route->vehicle_id) {
            $vehicle = Vehicle::find($route->vehicle_id);
            if ($vehicle) {
                if ($route->status === 'completed' || $route->status === 'cancelled') {
                    // Free vehicle when route is completed or cancelled
                    if ($vehicle->status === 'in_use') {
                        $vehicle->update(['status' => 'available']);
                    }
                } elseif ($route->status === 'in_progress' && $vehicle->status === 'available') {
                    // Mark vehicle as in use when route starts
                    $vehicle->update(['status' => 'in_use']);
                } elseif ($route->status === 'scheduled' && $vehicle->status === 'available') {
                    // Mark vehicle as in use when scheduled
                    $vehicle->update(['status' => 'in_use']);
                }
            }
        }

        // Free old vehicle if vehicle was changed
        if ($oldVehicleId && $oldVehicleId !== $route->vehicle_id) {
            $oldVehicle = Vehicle::find($oldVehicleId);
            if ($oldVehicle && $oldVehicle->status === 'in_use') {
                // Check if vehicle has other active routes
                $hasOtherActiveRoutes = Route::where('vehicle_id', $oldVehicleId)
                    ->where('id', '!=', $route->id)
                    ->whereIn('status', ['scheduled', 'in_progress'])
                    ->exists();
                
                if (!$hasOtherActiveRoutes) {
                    $oldVehicle->update(['status' => 'available']);
                }
            }
        }

        // Update shipments association
        $tenant = Auth::user()->tenant;
        
        // Remove all shipments from this route
        Shipment::where('route_id', $route->id)
            ->where('tenant_id', $tenant->id)
            ->update(['route_id' => null]);

        // Associate new shipments
        if ($request->has('shipment_ids')) {
            Shipment::whereIn('id', $request->shipment_ids)
                ->where('tenant_id', $tenant->id)
                ->update(['route_id' => $route->id]);
        }

        return redirect()->route('routes.show', $route)
                ->with('success', 'Rota atualizada com sucesso!');
    }

    /**
     * Remove the specified route
     */
    public function destroy(Route $route)
    {
        $this->authorizeAccess($route);

        // Free vehicle if route is deleted
        if ($route->vehicle_id) {
            $vehicle = Vehicle::find($route->vehicle_id);
            if ($vehicle && $vehicle->status === 'in_use') {
                // Check if vehicle has other active routes
                $hasOtherActiveRoutes = Route::where('vehicle_id', $route->vehicle_id)
                    ->where('id', '!=', $route->id)
                    ->whereIn('status', ['scheduled', 'in_progress'])
                    ->exists();
                
                if (!$hasOtherActiveRoutes) {
                    $vehicle->update(['status' => 'available']);
                }
            }
        }

        // Remove shipments from route
        $route->shipments()->update(['route_id' => null]);

        $route->delete();

        return redirect()->route('routes.index')
            ->with('success', 'Rota excluída com sucesso!');
    }

    /**
     * Download CT-e XML file
     */
    public function downloadCteXml(Route $route, FiscalDocument $fiscalDocument)
    {
        $tenant = Auth::user()->tenant;
        
        // Verify route access
        $this->authorizeAccess($route);
        
        // Verify fiscal document belongs to tenant
        if ($fiscalDocument->tenant_id !== $tenant->id) {
            abort(403, 'Acesso não autorizado a este documento fiscal.');
        }
        
        // Verify fiscal document belongs to route
        if ($fiscalDocument->route_id !== $route->id && 
            ($fiscalDocument->shipment && $fiscalDocument->shipment->route_id !== $route->id)) {
            abort(403, 'O documento fiscal não pertence a esta rota.');
        }
        
        // Try to get XML from storage (local or MinIO)
        $xmlContent = null;
        if ($fiscalDocument->xml_url) {
            try {
                // Check if it's local storage (prefixed with 'local:')
                if (strpos($fiscalDocument->xml_url, 'local:') === 0) {
                    $localPath = str_replace('local:', '', $fiscalDocument->xml_url);
                    if (Storage::disk('local')->exists($localPath)) {
                        $xmlContent = Storage::disk('local')->get($localPath);
                    } else {
                        \Log::warning('Arquivo XML não encontrado no storage local', [
                            'path' => $localPath,
                        ]);
                    }
                } else {
                    // Try MinIO (if available)
                    try {
                        if (Storage::disk('minio')->exists($fiscalDocument->xml_url)) {
                            $xmlContent = Storage::disk('minio')->get($fiscalDocument->xml_url);
                        }
                    } catch (\Exception $e) {
                        \Log::warning('Falha ao obter XML do MinIO', [
                            'xml_url' => $fiscalDocument->xml_url,
                            'erro' => $e->getMessage(),
                        ]);
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('Falha ao obter XML do storage', [
                    'xml_url' => $fiscalDocument->xml_url,
                    'erro' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }
        
        // Fallback to database if storage fails (backward compatibility)
        if (!$xmlContent && $fiscalDocument->xml) {
            $xmlContent = $fiscalDocument->xml;
        }
        
        if (!$xmlContent) {
            abort(404, 'Arquivo XML não encontrado.');
        }
        
        $filename = 'cte-' . ($fiscalDocument->access_key ?? $fiscalDocument->id) . '.xml';
        
        return response($xmlContent, 200)
            ->header('Content-Type', 'application/xml; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Calculate route metrics (distance, duration, fuel consumption)
     */
    protected function calculateRouteMetrics(Route $route): void
    {
        $shipments = $route->shipments()->with(['senderClient', 'receiverClient'])->get();
        
        if ($shipments->isEmpty()) {
            return;
        }

        $googleMapsService = app(GoogleMapsService::class);
        $totalDistance = 0; // in meters
        $totalDuration = 0; // in seconds
        $waypoints = [];

        // Collect all pickup and delivery addresses
        foreach ($shipments as $shipment) {
            // Geocode pickup address if not already geocoded
            if (!$shipment->pickup_latitude && $shipment->pickup_address) {
                $pickupAddress = trim(implode(', ', array_filter([
                    $shipment->pickup_address,
                    $shipment->pickup_city,
                    $shipment->pickup_state,
                    $shipment->pickup_zip_code,
                ])));
                
                if ($pickupAddress) {
                    $geocoded = $googleMapsService->geocode($pickupAddress);
                    if ($geocoded) {
                        $shipment->update([
                            'pickup_latitude' => $geocoded['latitude'],
                            'pickup_longitude' => $geocoded['longitude'],
                        ]);
                    }
                }
            }

            // Geocode delivery address if not already geocoded
            if (!$shipment->delivery_latitude && $shipment->delivery_address) {
                $deliveryAddress = trim(implode(', ', array_filter([
                    $shipment->delivery_address,
                    $shipment->delivery_city,
                    $shipment->delivery_state,
                    $shipment->delivery_zip_code,
                ])));
                
                if ($deliveryAddress) {
                    $geocoded = $googleMapsService->geocode($deliveryAddress);
                    if ($geocoded) {
                        $shipment->update([
                            'delivery_latitude' => $geocoded['latitude'],
                            'delivery_longitude' => $geocoded['longitude'],
                        ]);
                    }
                }
            }

            // Add waypoints if coordinates are available
            if ($shipment->pickup_latitude && $shipment->pickup_longitude) {
                $waypoints[] = [
                    'lat' => $shipment->pickup_latitude,
                    'lng' => $shipment->pickup_longitude,
                    'type' => 'pickup',
                    'shipment_id' => $shipment->id,
                ];
            }
            
            if ($shipment->delivery_latitude && $shipment->delivery_longitude) {
                $waypoints[] = [
                    'lat' => $shipment->delivery_latitude,
                    'lng' => $shipment->delivery_longitude,
                    'type' => 'delivery',
                    'shipment_id' => $shipment->id,
                ];
            }
        }

        // Calculate total distance and duration using waypoints
        if (count($waypoints) >= 2) {
            // Calculate distance between consecutive waypoints
            for ($i = 0; $i < count($waypoints) - 1; $i++) {
                $distance = $googleMapsService->calculateDistance(
                    $waypoints[$i]['lat'],
                    $waypoints[$i]['lng'],
                    $waypoints[$i + 1]['lat'],
                    $waypoints[$i + 1]['lng']
                );
                
                if ($distance) {
                    $totalDistance += $distance['distance'];
                    $totalDuration += $distance['duration'];
                }
            }

            // Update route with calculated metrics
            $route->update([
                'estimated_distance' => round($totalDistance / 1000, 2), // Convert to km
                'estimated_duration' => round($totalDuration / 60), // Convert to minutes
                'start_latitude' => $waypoints[0]['lat'],
                'start_longitude' => $waypoints[0]['lng'],
                'end_latitude' => $waypoints[count($waypoints) - 1]['lat'],
                'end_longitude' => $waypoints[count($waypoints) - 1]['lng'],
            ]);

            // Calculate fuel consumption if vehicle is assigned
            if ($route->vehicle_id) {
                $vehicle = Vehicle::find($route->vehicle_id);
                if ($vehicle && $vehicle->fuel_type) {
                    // Average fuel consumption per km based on vehicle type
                    $fuelConsumptionPerKm = match($vehicle->vehicle_type) {
                        'truck' => 0.35, // 35L per 100km = 0.35L/km
                        'van' => 0.12,   // 12L per 100km = 0.12L/km
                        'car' => 0.10,   // 10L per 100km = 0.10L/km
                        default => 0.20, // Default: 20L per 100km = 0.20L/km
                    };

                    $estimatedFuelConsumption = round(($totalDistance / 1000) * $fuelConsumptionPerKm, 2);
                    
                    // Store in route settings
                    $settings = $route->settings ?? [];
                    $settings['estimated_fuel_consumption'] = $estimatedFuelConsumption;
                    $settings['fuel_consumption_per_km'] = $fuelConsumptionPerKm;
                    $route->update(['settings' => $settings]);
                }
            }
        }
    }

    /**
     * Save XML content to MinIO storage (with fallback to local storage)
     */
    protected function saveXmlToStorage(string $xmlContent, string $accessKey, int $tenantId): ?string
    {
        // Temporarily disable MinIO due to Flysystem v3 compatibility issues
        // Use local storage instead until the issue is resolved
        try {
            $filename = 'cte-' . ($accessKey ?: Str::random(16)) . '.xml';
            $path = "tenants/{$tenantId}/cte/{$filename}";
            
            // Use local storage as fallback
            Storage::disk('local')->put($path, $xmlContent);
            
            \Log::info('XML salvo no storage local (MinIO temporariamente desabilitado)', [
                'path' => $path,
                'tenant_id' => $tenantId,
            ]);
            
            // Return path with 'local:' prefix to indicate it's in local storage
            return 'local:' . $path;
        } catch (\Exception $e) {
            \Log::warning('Falha ao salvar XML no storage local', [
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'tenant_id' => $tenantId,
                'access_key' => $accessKey,
            ]);
            return null; // Return null to indicate failure, caller will use DB fallback
        }
    }

    /**
     * Show route selection page
     */
    public function selectRoute(Route $route)
    {
        $this->authorizeAccess($route);

        $route->load(['branch', 'driver', 'shipments']);

        // If route is already locked, redirect to show page
        if ($route->is_route_locked) {
            return redirect()->route('routes.show', $route)
                ->with('info', 'Esta rota já foi escolhida e está bloqueada.');
        }

        // If no route options, calculate them
        if (!$route->route_options || empty($route->route_options)) {
            $this->calculateMultipleRouteOptions($route);
            $route->refresh();
        }

        return view('routes.select-route', compact('route'));
    }

    /**
     * Store selected route option
     */
    public function storeSelectedRoute(Request $request, Route $route)
    {
        $this->authorizeAccess($route);

        // Check if route is already locked
        if ($route->is_route_locked) {
            return back()->withErrors(['error' => 'Esta rota já foi escolhida e não pode ser alterada.'])->withInput();
        }

        $validated = $request->validate([
            'selected_route_option' => 'required|integer|in:1,2,3',
        ]);

        $selectedOption = $validated['selected_route_option'];

        // Verify that the selected option exists
        if (!$route->route_options || !isset($route->route_options[$selectedOption - 1])) {
            return back()->withErrors(['error' => 'Opção de rota inválida.'])->withInput();
        }

        $selectedRouteData = $route->route_options[$selectedOption - 1];

        // Update route with selected option
        $route->update([
            'selected_route_option' => $selectedOption,
            'is_route_locked' => true,
            'estimated_distance' => $selectedRouteData['distance'] / 1000, // Convert to km
            'estimated_duration' => round($selectedRouteData['duration'] / 60), // Convert to minutes
        ]);

        // Update start coordinates if not already set
        if (!$route->start_latitude || !$route->start_longitude) {
            if ($route->branch && $route->branch->latitude && $route->branch->longitude) {
                $route->update([
                    'start_latitude' => $route->branch->latitude,
                    'start_longitude' => $route->branch->longitude,
                ]);
            } elseif ($route->driver && $route->driver->current_latitude && $route->driver->current_longitude) {
                $route->update([
                    'start_latitude' => $route->driver->current_latitude,
                    'start_longitude' => $route->driver->current_longitude,
                ]);
            }
        }

        // Update end coordinates from last shipment
        $lastShipment = $route->shipments()->orderBy('id', 'desc')->first();
        if ($lastShipment && $lastShipment->delivery_latitude && $lastShipment->delivery_longitude) {
            $route->update([
                'end_latitude' => $lastShipment->delivery_latitude,
                'end_longitude' => $lastShipment->delivery_longitude,
            ]);
        }

        return redirect()->route('routes.show', $route)
            ->with('success', 'Rota selecionada e bloqueada com sucesso!');
    }

    /**
     * Calculate multiple route options
     */
    protected function calculateMultipleRouteOptions(Route $route): void
    {
        // Get start coordinates from route
        $originLat = $route->start_latitude;
        $originLng = $route->start_longitude;

        if (!$originLat || !$originLng) {
            // Try to get from branch
            if ($route->branch) {
                $originLat = $route->branch->latitude;
                $originLng = $route->branch->longitude;
            }
            
            // Try to get from driver current location
            if ((!$originLat || !$originLng) && $route->driver) {
                $originLat = $route->driver->current_latitude;
                $originLng = $route->driver->current_longitude;
            }

            if (!$originLat || !$originLng) {
                \Log::warning('Cannot calculate route options: start coordinates not set', [
                    'route_id' => $route->id,
                    'has_branch' => $route->branch !== null,
                    'has_driver' => $route->driver !== null,
                ]);
                return;
            }
        }

        // Get shipments with delivery addresses
        $shipments = $route->shipments()->whereNotNull('delivery_latitude')
            ->whereNotNull('delivery_longitude')
            ->orderBy('id')
            ->get();

        if ($shipments->isEmpty()) {
            \Log::warning('Cannot calculate route options: no shipments with coordinates', ['route_id' => $route->id]);
            return;
        }

        // Build waypoints from shipments
        $waypoints = [];
        foreach ($shipments as $shipment) {
            $waypoints[] = [
                'lat' => $shipment->delivery_latitude,
                'lng' => $shipment->delivery_longitude,
            ];
        }

        // Last shipment destination
        $lastShipment = $shipments->last();
        $destinationLat = $lastShipment->delivery_latitude;
        $destinationLng = $lastShipment->delivery_longitude;

        // Calculate multiple routes
        $googleMapsService = app(GoogleMapsService::class);
        $routeOptions = $googleMapsService->calculateMultipleRoutes(
            $originLat,
            $originLng,
            $destinationLat,
            $destinationLng,
            $waypoints
        );

        if (!empty($routeOptions)) {
            $route->update(['route_options' => $routeOptions]);
        }
    }

    /**
     * Create branch via AJAX
     */
    public function createBranch(Request $request)
    {
        $tenant = Auth::user()->tenant;
        
        // Get or create company for tenant
        $company = Company::where('tenant_id', $tenant->id)->first();
        
        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'É necessário cadastrar uma empresa primeiro. Acesse Configurações > Empresas.',
            ], 400);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'postal_code' => 'required|string|max:10',
            'address' => 'required|string|max:255',
            'address_number' => 'required|string|max:20',
            'complement' => 'nullable|string|max:255',
            'neighborhood' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|size:2',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        try {
            $googleMapsService = app(GoogleMapsService::class);
            
            // Geocode address
            $fullAddress = trim(implode(', ', array_filter([
                $validated['address'],
                $validated['address_number'],
                $validated['neighborhood'],
                $validated['city'],
                $validated['state'],
            ])));
            
            $geocoded = $googleMapsService->geocode($fullAddress);
            
            $branch = Branch::create([
                'tenant_id' => $tenant->id,
                'company_id' => $company->id,
                'name' => $validated['name'],
                'code' => $validated['code'] ?? null,
                'postal_code' => $validated['postal_code'],
                'address' => $validated['address'],
                'address_number' => $validated['address_number'],
                'complement' => $validated['complement'] ?? null,
                'neighborhood' => $validated['neighborhood'],
                'city' => $validated['city'],
                'state' => $validated['state'],
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'latitude' => $geocoded['latitude'] ?? null,
                'longitude' => $geocoded['longitude'] ?? null,
                'is_active' => true,
                'is_operational' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pavilhão criado com sucesso!',
                'branch' => [
                    'id' => $branch->id,
                    'name' => $branch->name,
                    'city' => $branch->city,
                    'state' => $branch->state,
                    'full_address' => $branch->full_address,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Error creating branch', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar pavilhão: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Authorize access to route
     */
    protected function authorizeAccess(Route $route)
    {
        $tenant = Auth::user()->tenant;
        
        if ($route->tenant_id !== $tenant->id) {
            abort(403, 'Acesso não autorizado a esta rota.');
        }
    }
}

