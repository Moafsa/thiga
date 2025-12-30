<?php

namespace App\Http\Controllers;

use App\Models\Route;
use App\Models\Shipment;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\Branch;
use App\Models\Company;
use App\Models\FiscalDocument;
use App\Models\CteXml;
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
            'has_cte_xml_numbers' => $request->has('cte_xml_numbers'),
            'cte_xml_numbers_count' => $request->has('cte_xml_numbers') ? count($request->cte_xml_numbers) : 0,
            'has_addresses' => $request->has('addresses'),
            'addresses_count' => $request->has('addresses') ? count($request->addresses) : 0,
            'addresses_keys' => $request->has('addresses') ? array_keys($request->addresses) : [],
            'addresses_data' => $request->has('addresses') ? $request->addresses : [],
            'all_input' => $request->except(['_token']),
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
                'cte_xml_numbers' => 'nullable|array',
                'cte_xml_numbers.*' => 'string',
                'addresses' => 'nullable|array|min:1',
                'addresses.*.address' => 'required_with:addresses|string|max:255',
                'addresses.*.city' => 'required_with:addresses|string|max:255',
                'addresses.*.state' => 'required_with:addresses|string|size:2',
                'addresses.*.zip_code' => 'nullable|string|max:10',
                'addresses.*.recipient_name' => 'nullable|string|max:255',
                'addresses.*.freight_value' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string',
            ]);

            // Validate that at least one start address method is provided
            if (empty($validated['branch_id']) && empty($validated['start_address_type'])) {
                return back()->withErrors(['start_address_type' => 'É necessário selecionar um Depósito/Filial, usar a localização atual ou informar um endereço manual.'])->withInput();
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

            // Process CT-e XML numbers if provided
            if ($request->has('cte_xml_numbers') && !empty($request->cte_xml_numbers)) {
                $xmlNumbers = array_filter($request->cte_xml_numbers);
                
                if (!empty($xmlNumbers)) {
                    \Log::info('Processing CT-e XML numbers', [
                        'xml_numbers_count' => count($xmlNumbers),
                        'route_id' => $route->id,
                    ]);
                    
                    try {
                        $createdShipments = $this->processCteXmlNumbers($xmlNumbers, $tenant, $xmlParser, $route);
                        
                        \Log::info('CT-e XML numbers processed', [
                            'total_numbers' => count($xmlNumbers),
                            'created_shipments_count' => count($createdShipments),
                        ]);
                        
                        if (empty($createdShipments)) {
                            DB::rollBack();
                            \Log::warning('No shipments created from CT-e XML numbers', [
                                'total_numbers' => count($xmlNumbers),
                            ]);
                            return back()->withErrors(['cte_xml_numbers' => 'Falha ao processar todos os números de XML. Verifique se os XMLs existem e não foram usados.'])->withInput();
                        }
                        
                        $hasShipments = true;
                        // Total revenue will be updated automatically by ShipmentObserver
                    } catch (\Exception $e) {
                        DB::rollBack();
                        \Log::error('Error processing CT-e XML numbers', [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                            'total_numbers' => count($xmlNumbers),
                        ]);
                        return back()->withErrors(['cte_xml_numbers' => 'Erro ao processar números de XML: ' . $e->getMessage()])->withInput();
                    }
                }
            }

            // Process addresses if provided
            if ($request->has('addresses') && !empty($request->addresses)) {
                try {
                    \Log::info('Processing addresses', [
                        'addresses_count' => count($request->addresses),
                        'addresses_keys' => array_keys($request->addresses),
                        'addresses_data' => $request->addresses,
                    ]);
                    
                    $createdShipments = $this->processAddresses($request->addresses, $tenant, $route);
                    
                    \Log::info('Addresses processed', [
                        'created_shipments_count' => count($createdShipments),
                    ]);
                    
                    if (!empty($createdShipments)) {
                        $hasShipments = true;
                        // Total revenue will be updated automatically by ShipmentObserver
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
                    // Update total revenue manually because bulk update() doesn't fire model events
                    $route->calculateTotalRevenue();
                    $totalRevenue = $route->shipments()->sum('value') ?? 0;
                    $route->update([
                        'settings' => array_merge($route->settings ?? [], [
                            'total_cte_value' => $totalRevenue,
                        ]),
                    ]);
                }
            }

            // Validate that route has at least one shipment
            if (!$hasShipments) {
                DB::rollBack();
                \Log::warning('Route created without shipments');
                return back()->withErrors(['error' => 'A rota deve ter pelo menos um endereço, shipment existente ou número de XML de CT-e.'])->withInput();
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
                    DB::rollBack();
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
                    DB::rollBack();
                    return back()->withErrors(['start_address' => 'Não foi possível geocodificar o endereço informado. Verifique os dados e tente novamente.'])->withInput();
                }
            }

            // CRITICAL: Update route with start coordinates (MUST be depot/branch, NEVER pickup address)
            if (!$startLat || !$startLng) {
                DB::rollBack();
                \Log::error('CRITICAL: Route created without start coordinates (depot/branch)', [
                    'route_id' => $route->id,
                    'start_address_type' => $startAddressType,
                    'branch_id' => $validated['branch_id'] ?? null,
                ]);
                return back()->withErrors(['error' => 'Não foi possível determinar o ponto de partida da rota. O ponto de partida DEVE ser o depósito/filial, nunca o remetente. Verifique o endereço do depósito/filial.'])->withInput();
            }
            
            // Verify that start coordinates are from depot/branch, not from pickup address
            $branch = null;
            if ($startAddressType === 'branch' && $validated['branch_id']) {
                $branch = Branch::find($validated['branch_id']);
            }
            
            \Log::info('Setting route origin as depot/branch (NOT pickup address)', [
                'route_id' => $route->id,
                'start_address_type' => $startAddressType,
                'branch_id' => $branch->id ?? null,
                'branch_name' => $branch->name ?? null,
                'branch_city' => $branch->city ?? null,
                'origin_coordinates' => ['lat' => $startLat, 'lng' => $startLng],
            ]);
            
            try {
                $route->update([
                    'start_latitude' => $startLat,
                    'start_longitude' => $startLng,
                ]);

                // Calculate multiple route options (will set end coordinates)
                // This is done outside transaction to avoid blocking if API call fails
                // We'll commit first, then calculate routes
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Error updating route start coordinates', [
                    'route_id' => $route->id,
                    'error' => $e->getMessage(),
                ]);
                return back()->withErrors(['error' => 'Erro ao atualizar coordenadas da rota: ' . $e->getMessage()])->withInput();
            }
            
            // Commit transaction before calculating routes (to avoid long-running transaction)
            DB::commit();
            
            // Now calculate routes (outside transaction)
            try {
                $this->calculateMultipleRouteOptions($route);
                
                // Verify end coordinates are set - MUST ALWAYS be depot/branch (return to origin)
                $route->refresh();
                if (!$route->end_latitude || !$route->end_longitude) {
                    // CRITICAL: End coordinates MUST ALWAYS be depot/branch (return to origin)
                    $route->update([
                        'end_latitude' => $startLat,
                        'end_longitude' => $startLng,
                    ]);
                    \Log::info('Route end coordinates set to origin (depot/branch - return)', [
                        'route_id' => $route->id,
                    ]);
                } else {
                    // Ensure end coordinates are depot/branch, not last shipment
                    $route->update([
                        'end_latitude' => $startLat,
                        'end_longitude' => $startLng,
                    ]);
                    \Log::info('Route end coordinates updated to origin (depot/branch - return)', [
                        'route_id' => $route->id,
                    ]);
                }
            } catch (\Exception $e) {
                // Log error but don't fail route creation if route calculation fails
                \Log::error('Error calculating route options', [
                    'route_id' => $route->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                // Route is already created, so we continue
            }
            
            // Update vehicle status if vehicle is assigned (outside transaction)
            if ($route->vehicle_id) {
                try {
                    $vehicle = Vehicle::find($route->vehicle_id);
                    if ($vehicle && $vehicle->status === 'available') {
                        $vehicle->update(['status' => 'in_use']);
                    }
                } catch (\Exception $e) {
                    \Log::error('Error updating vehicle status', [
                        'route_id' => $route->id,
                        'vehicle_id' => $route->vehicle_id,
                        'error' => $e->getMessage(),
                    ]);
                    // Don't fail route creation if vehicle update fails
                }
            }

            \Log::info('Route created successfully', [
                'route_id' => $route->id,
                'has_cte_xml_numbers' => $request->has('cte_xml_numbers'),
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
        $googleMapsService = app(GoogleMapsService::class);
        $failedFiles = [];

        foreach ($files as $index => $file) {
            try {
                \Log::info('Processing XML file', [
                    'file_index' => $index + 1,
                    'total_files' => count($files),
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
                
                // Build full addresses for geocoding
                $pickupFullAddress = trim(implode(', ', array_filter([
                    $cteData['origin']['address'] ?? '',
                    $cteData['origin']['number'] ?? '',
                    $cteData['origin']['complement'] ?? '',
                    $cteData['origin']['neighborhood'] ?? '',
                    $cteData['origin']['city'] ?? '',
                    $cteData['origin']['state'] ?? '',
                    $cteData['origin']['zip_code'] ?? '',
                ])));
                
                $deliveryFullAddress = trim(implode(', ', array_filter([
                    $cteData['destination']['address'] ?? '',
                    $cteData['destination']['number'] ?? '',
                    $cteData['destination']['complement'] ?? '',
                    $cteData['destination']['neighborhood'] ?? '',
                    $cteData['destination']['city'] ?? '',
                    $cteData['destination']['state'] ?? '',
                    $cteData['destination']['zip_code'] ?? '',
                ])));
                
                // Geocode addresses to get coordinates
                $pickupCoords = null;
                $deliveryCoords = null;
                
                if ($pickupFullAddress) {
                    $pickupCoords = $googleMapsService->geocode($pickupFullAddress);
                    if ($pickupCoords) {
                        \Log::info('Pickup address geocoded', [
                            'address' => $pickupFullAddress,
                            'coordinates' => ['lat' => $pickupCoords['latitude'], 'lng' => $pickupCoords['longitude']],
                        ]);
                    } else {
                        \Log::warning('Failed to geocode pickup address', [
                            'address' => $pickupFullAddress,
                        ]);
                    }
                }
                
                if ($deliveryFullAddress) {
                    $deliveryCoords = $googleMapsService->geocode($deliveryFullAddress);
                    if ($deliveryCoords) {
                        \Log::info('Delivery address geocoded', [
                            'address' => $deliveryFullAddress,
                            'coordinates' => ['lat' => $deliveryCoords['latitude'], 'lng' => $deliveryCoords['longitude']],
                        ]);
                    } else {
                        \Log::warning('Failed to geocode delivery address', [
                            'address' => $deliveryFullAddress,
                        ]);
                    }
                }
                
                // Build addresses for display (without coordinates)
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
                    'pickup_latitude' => $pickupCoords['latitude'] ?? null,
                    'pickup_longitude' => $pickupCoords['longitude'] ?? null,
                    'delivery_address' => $deliveryAddress ?: ($cteData['destination']['address'] ?? ''),
                    'delivery_city' => $cteData['destination']['city'] ?? '',
                    'delivery_state' => $cteData['destination']['state'] ?? '',
                    'delivery_zip_code' => $cteData['destination']['zip_code'] ?? '',
                    'delivery_latitude' => $deliveryCoords['latitude'] ?? null,
                    'delivery_longitude' => $deliveryCoords['longitude'] ?? null,
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
                    'has_delivery_coords' => !empty($deliveryCoords),
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
                $failedFiles[] = [
                    'filename' => $file->getClientOriginalName(),
                    'error' => $e->getMessage(),
                ];
                
                \Log::error('Falha ao processar arquivo XML de CT-e', [
                    'file_index' => $index + 1,
                    'total_files' => count($files),
                    'arquivo' => $file->getClientOriginalName(),
                    'erro' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                
                // Continue processing other files instead of throwing exception
                // This allows partial success - process valid XMLs even if some fail
            }
        }

        // Log summary
        \Log::info('XML files processing completed', [
            'total_files' => count($files),
            'successful' => count($createdShipments),
            'failed' => count($failedFiles),
            'failed_files' => $failedFiles,
        ]);

        // If all files failed, throw exception
        if (empty($createdShipments) && !empty($failedFiles)) {
            $errorMessages = array_map(function($file) {
                return $file['filename'] . ': ' . $file['error'];
            }, $failedFiles);
            throw new \Exception('Falha ao processar todos os arquivos XML: ' . implode('; ', $errorMessages));
        }

        // If some files failed, log warning but continue
        if (!empty($failedFiles)) {
            \Log::warning('Alguns arquivos XML falharam no processamento', [
                'failed_count' => count($failedFiles),
                'successful_count' => count($createdShipments),
                'failed_files' => $failedFiles,
            ]);
        }

        return $createdShipments;
    }

    /**
     * Process CT-e XML numbers and create shipments
     */
    protected function processCteXmlNumbers(array $xmlNumbers, $tenant, CteXmlParserService $xmlParser, Route $route): array
    {
        $createdShipments = [];
        $googleMapsService = app(GoogleMapsService::class);
        $failedNumbers = [];

        foreach ($xmlNumbers as $xmlNumber) {
            try {
                $xmlNumber = trim($xmlNumber);
                if (empty($xmlNumber)) {
                    continue;
                }

                \Log::info('Processing CT-e XML number', [
                    'xml_number' => $xmlNumber,
                    'route_id' => $route->id,
                ]);

                // Find CT-e XML by number
                $cteXml = CteXml::where('tenant_id', $tenant->id)
                    ->where('cte_number', $xmlNumber)
                    ->first();

                if (!$cteXml) {
                    throw new \Exception("CT-e XML número {$xmlNumber} não encontrado.");
                }

                if ($cteXml->is_used) {
                    throw new \Exception("CT-e XML número {$xmlNumber} já foi usado.");
                }

                // Get XML content
                $xmlContent = null;
                if ($cteXml->xml_url) {
                    try {
                        if (strpos($cteXml->xml_url, 'local:') === 0) {
                            $localPath = str_replace('local:', '', $cteXml->xml_url);
                            if (Storage::disk('local')->exists($localPath)) {
                                $xmlContent = Storage::disk('local')->get($localPath);
                            }
                        }
                    } catch (\Exception $e) {
                        \Log::warning('Failed to get XML from storage', [
                            'xml_url' => $cteXml->xml_url,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                if (!$xmlContent && $cteXml->xml) {
                    $xmlContent = $cteXml->xml;
                }

                if (!$xmlContent) {
                    throw new \Exception("Não foi possível obter o conteúdo do XML número {$xmlNumber}.");
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

                // Build full addresses for geocoding
                $pickupFullAddress = trim(implode(', ', array_filter([
                    $cteData['origin']['address'] ?? '',
                    $cteData['origin']['number'] ?? '',
                    $cteData['origin']['complement'] ?? '',
                    $cteData['origin']['neighborhood'] ?? '',
                    $cteData['origin']['city'] ?? '',
                    $cteData['origin']['state'] ?? '',
                    $cteData['origin']['zip_code'] ?? '',
                ])));

                $deliveryFullAddress = trim(implode(', ', array_filter([
                    $cteData['destination']['address'] ?? '',
                    $cteData['destination']['number'] ?? '',
                    $cteData['destination']['complement'] ?? '',
                    $cteData['destination']['neighborhood'] ?? '',
                    $cteData['destination']['city'] ?? '',
                    $cteData['destination']['state'] ?? '',
                    $cteData['destination']['zip_code'] ?? '',
                ])));

                // Geocode addresses to get coordinates
                $pickupCoords = null;
                $deliveryCoords = null;

                if ($pickupFullAddress) {
                    $pickupCoords = $googleMapsService->geocode($pickupFullAddress);
                    if ($pickupCoords) {
                        \Log::info('Pickup address geocoded', [
                            'address' => $pickupFullAddress,
                            'coordinates' => ['lat' => $pickupCoords['latitude'], 'lng' => $pickupCoords['longitude']],
                        ]);
                    } else {
                        \Log::warning('Failed to geocode pickup address', [
                            'address' => $pickupFullAddress,
                        ]);
                    }
                }

                if ($deliveryFullAddress) {
                    $deliveryCoords = $googleMapsService->geocode($deliveryFullAddress);
                    if ($deliveryCoords) {
                        \Log::info('Delivery address geocoded', [
                            'address' => $deliveryFullAddress,
                            'coordinates' => ['lat' => $deliveryCoords['latitude'], 'lng' => $deliveryCoords['longitude']],
                        ]);
                    } else {
                        \Log::warning('Failed to geocode delivery address', [
                            'address' => $deliveryFullAddress,
                        ]);
                    }
                }

                // Build addresses for display (without coordinates)
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

                // Create shipment
                $trackingNumber = 'THG' . strtoupper(Str::random(8));
                $shipment = Shipment::create([
                    'tenant_id' => $tenant->id,
                    'route_id' => $route->id,
                    'sender_client_id' => $senderClient->id,
                    'receiver_client_id' => $receiverClient->id,
                    'tracking_number' => $trackingNumber,
                    'tracking_code' => $trackingNumber,
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
                    'pickup_latitude' => $pickupCoords['latitude'] ?? null,
                    'pickup_longitude' => $pickupCoords['longitude'] ?? null,
                    'delivery_address' => $deliveryAddress ?: ($cteData['destination']['address'] ?? ''),
                    'delivery_city' => $cteData['destination']['city'] ?? '',
                    'delivery_state' => $cteData['destination']['state'] ?? '',
                    'delivery_zip_code' => $cteData['destination']['zip_code'] ?? '',
                    'delivery_latitude' => $deliveryCoords['latitude'] ?? null,
                    'delivery_longitude' => $deliveryCoords['longitude'] ?? null,
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

                \Log::info('Shipment created from CT-e XML number', [
                    'shipment_id' => $shipment->id,
                    'xml_number' => $xmlNumber,
                    'access_key' => $cteData['access_key'] ?? 'N/A',
                    'has_delivery_coords' => !empty($deliveryCoords),
                ]);

                // Get XML path from CteXml or save if needed
                $xmlPath = $cteXml->xml_url;
                if (!$xmlPath) {
                    $xmlPath = $this->saveXmlToStorage($xmlContent, $cteData['access_key'] ?? 'cte-' . $shipment->id, $tenant->id);
                    $cteXml->update(['xml_url' => $xmlPath]);
                }

                // Create fiscal document
                FiscalDocument::create([
                    'tenant_id' => $tenant->id,
                    'shipment_id' => $shipment->id,
                    'route_id' => $route->id,
                    'document_type' => 'cte',
                    'access_key' => $cteData['access_key'],
                    'status' => 'authorized',
                    'xml_url' => $xmlPath,
                    'xml' => $xmlPath ? null : $xmlContent,
                    'authorized_at' => now(),
                ]);

                // Mark CT-e XML as used
                $cteXml->markAsUsed($route->id);

                \Log::info('CT-e XML marked as used', [
                    'xml_number' => $xmlNumber,
                    'route_id' => $route->id,
                ]);

                $createdShipments[] = $shipment;
            } catch (\Exception $e) {
                $failedNumbers[] = [
                    'xml_number' => $xmlNumber,
                    'error' => $e->getMessage(),
                ];

                \Log::error('Failed to process CT-e XML number', [
                    'xml_number' => $xmlNumber,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        // Log summary
        \Log::info('CT-e XML numbers processing completed', [
            'total_numbers' => count($xmlNumbers),
            'successful' => count($createdShipments),
            'failed' => count($failedNumbers),
            'failed_numbers' => $failedNumbers,
        ]);

        // If all numbers failed, throw exception
        if (empty($createdShipments) && !empty($failedNumbers)) {
            $errorMessages = array_map(function($item) {
                return $item['xml_number'] . ': ' . $item['error'];
            }, $failedNumbers);
            throw new \Exception('Falha ao processar todos os números de XML: ' . implode('; ', $errorMessages));
        }

        // If some numbers failed, log warning but continue
        if (!empty($failedNumbers)) {
            \Log::warning('Some CT-e XML numbers failed to process', [
                'failed_count' => count($failedNumbers),
                'successful_count' => count($createdShipments),
                'failed_numbers' => $failedNumbers,
            ]);
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
        
        \Log::info('processAddresses called', [
            'addresses_count' => count($addresses),
            'addresses_keys' => array_keys($addresses),
        ]);
        
        // Normalize array to sequential indices to handle non-sequential keys
        $addresses = array_values($addresses);
        
        \Log::info('After array_values', [
            'addresses_count' => count($addresses),
            'addresses_keys' => array_keys($addresses),
        ]);
        
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
        
        // Load branch relationship if not already loaded
        if (!$route->relationLoaded('branch') && $route->branch_id) {
            $route->load('branch');
        }
        
        // Get branch information for pickup address
        $branch = $route->branch;
        $pickupAddress = [];
        if ($branch) {
            $pickupAddress = [
                'address' => trim(implode(', ', array_filter([
                    $branch->address ?? '',
                    $branch->address_number ?? '',
                    $branch->neighborhood ?? '',
                ]))),
                'city' => $branch->city ?? '',
                'state' => $branch->state ?? '',
                'zip_code' => $branch->postal_code ?? '',
            ];
        }
        
        // Process each address as a separate delivery destination
        // Pickup is always at the depot/branch (defaultSender), delivery is at each address
        foreach ($addresses as $index => $deliveryAddress) {
            \Log::info('Processing address', [
                'index' => $index,
                'delivery_address' => $deliveryAddress,
            ]);
            
            // Geocode addresses
            // If branch exists, use branch address for pickup, otherwise use delivery address
            if (!empty($pickupAddress['address'])) {
                $pickupFullAddress = trim(implode(', ', array_filter([
                    $pickupAddress['address'] ?? '',
                    $pickupAddress['city'] ?? '',
                    $pickupAddress['state'] ?? '',
                    $pickupAddress['zip_code'] ?? '',
                ])));
                // Use branch coordinates if available
                $pickupCoords = $branch->latitude && $branch->longitude 
                    ? ['latitude' => $branch->latitude, 'longitude' => $branch->longitude]
                    : $googleMapsService->geocode($pickupFullAddress);
            } else {
                // Fallback: use delivery address as pickup if no branch
                $pickupFullAddress = trim(implode(', ', array_filter([
                    $deliveryAddress['address'] ?? '',
                    $deliveryAddress['city'] ?? '',
                    $deliveryAddress['state'] ?? '',
                    $deliveryAddress['zip_code'] ?? '',
                ])));
                $pickupCoords = $googleMapsService->geocode($pickupFullAddress);
            }
            
            $deliveryFullAddress = trim(implode(', ', array_filter([
                $deliveryAddress['address'] ?? '',
                $deliveryAddress['city'] ?? '',
                $deliveryAddress['state'] ?? '',
                $deliveryAddress['zip_code'] ?? '',
            ])));
            
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
                'title' => 'Entrega ' . ($index + 1) . ' - ' . ($deliveryAddress['recipient_name'] ?? $deliveryAddress['city'] ?? 'Destinatário'),
                'recipient_name' => $deliveryAddress['recipient_name'] ?? 'Destinatário',
                'recipient_address' => $deliveryAddress['address'] ?? '',
                'recipient_city' => $deliveryAddress['city'] ?? '',
                'recipient_state' => $deliveryAddress['state'] ?? '',
                'recipient_zip_code' => $deliveryAddress['zip_code'] ?? '',
                'pickup_address' => !empty($pickupAddress['address']) ? $pickupAddress['address'] : ($deliveryAddress['address'] ?? ''),
                'pickup_city' => !empty($pickupAddress['city']) ? $pickupAddress['city'] : ($deliveryAddress['city'] ?? ''),
                'pickup_state' => !empty($pickupAddress['state']) ? $pickupAddress['state'] : ($deliveryAddress['state'] ?? ''),
                'pickup_zip_code' => !empty($pickupAddress['zip_code']) ? $pickupAddress['zip_code'] : ($deliveryAddress['zip_code'] ?? ''),
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
                'shipment_type' => $deliveryAddress['shipment_type'] ?? 'delivery',
                'delivery_notes' => 'Shipment created from address input',
                'freight_value' => isset($deliveryAddress['freight_value']) && $deliveryAddress['freight_value'] !== '' ? (float) $deliveryAddress['freight_value'] : null,
                'value' => isset($deliveryAddress['freight_value']) && $deliveryAddress['freight_value'] !== '' ? (float) $deliveryAddress['freight_value'] : 0,
                'goods_value' => isset($deliveryAddress['freight_value']) && $deliveryAddress['freight_value'] !== '' ? (float) $deliveryAddress['freight_value'] : 0,
            ]);
            
            $createdShipments[] = $shipment;
            
            \Log::info('Shipment created from address', [
                'shipment_id' => $shipment->id,
                'index' => $index,
                'delivery_address' => $deliveryAddress['address'] ?? 'N/A',
                'freight_value' => $shipment->freight_value,
                'value' => $shipment->value,
                'goods_value' => $shipment->goods_value,
            ]);
        }
        
        \Log::info('Finished processing addresses', [
            'total_shipments_created' => count($createdShipments),
        ]);
        
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
        try {
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
                // Wrap in try-catch to prevent 404 if calculation fails
                try {
                    if (!$route->route_options || empty($route->route_options)) {
                        $this->calculateMultipleRouteOptions($route);
                        $route->refresh();
                    }
                } catch (\Exception $e) {
                    \Log::error('Error calculating route options in show method', [
                        'route_id' => $route->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    // Continue without route options - don't fail the page
                }
            }
            
            // Get MDF-e if exists
            $mdfe = FiscalDocument::where('route_id', $route->id)
                ->where('document_type', 'mdfe')
                ->orderBy('created_at', 'desc')
                ->first();

            return view('routes.show', compact('route', 'mdfe'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('Route not found', [
                'route_id' => $route->id ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
            abort(404, 'Rota não encontrada.');
        } catch (\Exception $e) {
            \Log::error('Error showing route', [
                'route_id' => $route->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('routes.index')
                ->with('error', 'Erro ao carregar a rota: ' . $e->getMessage());
        }
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

        // Handle different update types
        $updateType = $request->input('update_type');
        
        if ($updateType === 'planned_times') {
            $validated = $request->validate([
                'planned_departure_datetime' => 'nullable|date',
                'planned_arrival_datetime' => 'nullable|date|after_or_equal:planned_departure_datetime',
            ]);
            
            $route->update($validated);
            return redirect()->route('routes.show', $route)
                ->with('success', 'Horários planejados atualizados com sucesso!');
        }
        
        if ($updateType === 'actual_times') {
            $validated = $request->validate([
                'actual_departure_datetime' => 'nullable|date',
                'actual_arrival_datetime' => 'nullable|date|after_or_equal:actual_departure_datetime',
            ]);
            
            $route->update($validated);
            return redirect()->route('routes.show', $route)
                ->with('success', 'Horários reais atualizados com sucesso!');
        }
        
        if ($updateType === 'diarias') {
            $validated = $request->validate([
                'driver_diarias_count' => 'nullable|integer|min:0',
                'driver_diaria_value' => 'nullable|numeric|min:0',
            ]);
            
            $route->update($validated);
            return redirect()->route('routes.show', $route)
                ->with('success', 'Controle de diárias atualizado com sucesso!');
        }
        
        if ($updateType === 'deposits') {
            $validated = $request->validate([
                'deposit_toll' => 'nullable|numeric|min:0',
                'deposit_expenses' => 'nullable|numeric|min:0',
                'deposit_fuel' => 'nullable|numeric|min:0',
            ]);
            
            $route->update($validated);
            return redirect()->route('routes.show', $route)
                ->with('success', 'Controle de depósitos atualizado com sucesso!');
        }
        
        // Standard route update
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
        
        // Calculate and update total revenue if shipments changed
        $route->update($validated);
        
        // Recalculate total revenue if shipments were updated
        if ($request->has('shipment_ids')) {
            $totalRevenue = $route->shipments()->sum('value') ?? 0;
            $route->update(['total_revenue' => $totalRevenue]);
        }

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

            // IMPORTANT: Only add DELIVERY addresses as waypoints
            // Pickup addresses (remetentes) are NOT waypoints
            // The route starts from depot/branch and goes to destinations only
            if ($shipment->delivery_latitude && $shipment->delivery_longitude) {
                $waypoints[] = [
                    'lat' => $shipment->delivery_latitude,
                    'lng' => $shipment->delivery_longitude,
                    'type' => 'delivery',
                    'shipment_id' => $shipment->id,
                    'tracking_number' => $shipment->tracking_number,
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
            // CRITICAL: End coordinates MUST ALWAYS be depot/branch (return to origin)
            $route->update([
                'estimated_distance' => round($totalDistance / 1000, 2), // Convert to km
                'estimated_duration' => round($totalDuration / 60), // Convert to minutes
                'start_latitude' => $waypoints[0]['lat'],
                'start_longitude' => $waypoints[0]['lng'],
                'end_latitude' => $waypoints[0]['lat'], // Always return to depot/branch
                'end_longitude' => $waypoints[0]['lng'], // Always return to depot/branch
            ]);

            // Calculate fuel consumption if vehicle is assigned
            if ($route->vehicle_id) {
                $vehicle = Vehicle::find($route->vehicle_id);
                if ($vehicle) {
                    // Use vehicle's fuel consumption per km (from database or default based on vehicle type)
                    $fuelConsumptionPerKm = $vehicle->getFuelConsumptionPerKm();

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

        // CRITICAL: Update end coordinates to depot/branch (return to origin)
        // The route MUST always return to depot/branch, never end at last shipment
        if ($route->start_latitude && $route->start_longitude) {
            $route->update([
                'end_latitude' => $route->start_latitude,
                'end_longitude' => $route->start_longitude,
            ]);
        } elseif ($route->branch && $route->branch->latitude && $route->branch->longitude) {
            $route->update([
                'end_latitude' => $route->branch->latitude,
                'end_longitude' => $route->branch->longitude,
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
        try {
            \Log::info('Starting route options calculation', [
                'route_id' => $route->id,
                'route_name' => $route->name,
            ]);

            // CRITICAL: Origin MUST ALWAYS be depot/branch (filial/depósito), NEVER pickup addresses (remetentes)
            // The route starts from depot, goes to nearest destination, then that destination becomes origin for next
            
            // Priority 1: Use route->start_latitude (should be depot/branch)
            $originLat = $route->start_latitude;
            $originLng = $route->start_longitude;

        // Priority 2: If not set, get from branch (depot/filial) - THIS IS THE CORRECT ORIGIN
        if ((!$originLat || !$originLng) && $route->branch) {
            $originLat = $route->branch->latitude;
            $originLng = $route->branch->longitude;
            
            // Update route with branch coordinates if not set
            if (!$route->start_latitude || !$route->start_longitude) {
                $route->update([
                    'start_latitude' => $originLat,
                    'start_longitude' => $originLng,
                ]);
            }
            
            \Log::info('Using branch (depot/filial) coordinates as origin', [
                'route_id' => $route->id,
                'branch_id' => $route->branch->id,
                'branch_name' => $route->branch->name,
                'branch_city' => $route->branch->city,
                'coordinates' => ['lat' => $originLat, 'lng' => $originLng],
            ]);
        }
        
        // Priority 3: Try to get from driver current location (fallback only, not ideal)
        if ((!$originLat || !$originLng) && $route->driver) {
            $originLat = $route->driver->current_latitude;
            $originLng = $route->driver->current_longitude;
            if ($originLat && $originLng) {
                \Log::warning('Using driver current location as origin (fallback - should use depot/branch!)', [
                    'route_id' => $route->id,
                    'driver_id' => $route->driver->id,
                    'coordinates' => ['lat' => $originLat, 'lng' => $originLng],
                ]);
            }
        }

        // CRITICAL: Must have origin from depot/branch, not from pickup address
        if (!$originLat || !$originLng) {
            \Log::error('CRITICAL ERROR: Cannot calculate route - NO ORIGIN (depot/branch) coordinates set!', [
                'route_id' => $route->id,
                'has_branch' => $route->branch !== null,
                'branch_id' => $route->branch_id,
                'has_driver' => $route->driver !== null,
                'route_start_lat' => $route->start_latitude,
                'route_start_lng' => $route->start_longitude,
            ]);
            return;
        }
        
        // Log confirmation that origin is depot/branch
        \Log::info('Route origin confirmed as depot/branch (NOT pickup address)', [
            'route_id' => $route->id,
            'origin' => ['lat' => $originLat, 'lng' => $originLng],
            'branch_id' => $route->branch_id,
            'branch_name' => $route->branch->name ?? 'N/A',
            'branch_city' => $route->branch->city ?? 'N/A',
        ]);

        // Get all shipments for the route
        $allShipments = $route->shipments()->orderBy('id')->get();
        
        \Log::info('Checking shipments for route calculation', [
            'route_id' => $route->id,
            'total_shipments' => $allShipments->count(),
        ]);

        // Get shipments with delivery addresses (coordinates)
        $shipments = $route->shipments()->whereNotNull('delivery_latitude')
            ->whereNotNull('delivery_longitude')
            ->orderBy('id')
            ->get();

        // Log shipments without coordinates for debugging
        $shipmentsWithoutCoords = $allShipments->filter(function($shipment) {
            return empty($shipment->delivery_latitude) || empty($shipment->delivery_longitude);
        });

        if ($shipmentsWithoutCoords->isNotEmpty()) {
            \Log::warning('Some shipments are missing delivery coordinates', [
                'route_id' => $route->id,
                'count' => $shipmentsWithoutCoords->count(),
                'shipment_ids' => $shipmentsWithoutCoords->pluck('id')->toArray(),
            ]);
        }

        if ($shipments->isEmpty()) {
            \Log::warning('Cannot calculate route options: no shipments with coordinates', [
                'route_id' => $route->id,
                'total_shipments' => $allShipments->count(),
                'shipments_without_coords' => $shipmentsWithoutCoords->count(),
            ]);
            return;
        }

        \Log::info('Found shipments with coordinates', [
            'route_id' => $route->id,
            'shipments_count' => $shipments->count(),
            'shipment_ids' => $shipments->pluck('id')->toArray(),
        ]);

        // Build destinations from DELIVERY addresses (destinatários)
        // IMPORTANT: We use DELIVERY addresses, NOT pickup addresses (remetentes)
        // The route goes: Depot → Nearest Destinatário → Next Nearest Destinatário → ...
        $destinations = [];
        foreach ($shipments as $shipment) {
            $destinations[] = [
                'lat' => $shipment->delivery_latitude,
                'lng' => $shipment->delivery_longitude,
                'shipment_id' => $shipment->id,
                'tracking_number' => $shipment->tracking_number,
            ];
        }

        // Optimize route sequentially: each destination becomes origin for next nearest
        // This is different from Google Maps optimizeWaypoints which optimizes all at once
        $routeOptimizationService = app(\App\Services\RouteOptimizationService::class);
        $optimizedDestinations = $routeOptimizationService->optimizeSequentialRoute(
            $originLat,
            $originLng,
            $destinations
        );

        // Convert optimized destinations to waypoints
        $waypoints = $optimizedDestinations;
        
        \Log::info('Route optimized sequentially', [
            'route_id' => $route->id,
            'original_count' => count($destinations),
            'optimized_count' => count($optimizedDestinations),
            'optimized_order' => array_map(function($d) {
                return $d['shipment_id'] ?? 'unknown';
            }, $optimizedDestinations),
        ]);

        // CRITICAL: Destination MUST ALWAYS be depot/branch (return to origin)
        // The route must return to depot after all deliveries
        // Route flow: Depot → Nearest Destinatário → Next Nearest → ... → Depot (return)
        $destinationLat = $originLat;
        $destinationLng = $originLng;
        
        \Log::info('Route destination set to depot/branch (return to origin)', [
            'route_id' => $route->id,
            'destination' => ['lat' => $destinationLat, 'lng' => $destinationLng],
            'origin' => ['lat' => $originLat, 'lng' => $originLng],
        ]);
        
        // Update route with end coordinates (always depot/branch)
        $route->update([
            'end_latitude' => $destinationLat,
            'end_longitude' => $destinationLng,
        ]);

        \Log::info('Calculating route options', [
            'route_id' => $route->id,
            'origin' => ['lat' => $originLat, 'lng' => $originLng],
            'destination' => ['lat' => $destinationLat, 'lng' => $destinationLng],
            'waypoints_count' => count($waypoints),
        ]);

        // Get vehicle for toll calculation
        $vehicle = $route->vehicle;
        
        // Calculate multiple routes with optimization
        $googleMapsService = app(GoogleMapsService::class);
        $routeOptions = $googleMapsService->calculateMultipleRoutes(
            $originLat,
            $originLng,
            $destinationLat,
            $destinationLng,
            $waypoints,
            $vehicle
        );

        if (!empty($routeOptions)) {
            // Compare routes and find the best one
            $routeComparisonService = app(\App\Services\RouteComparisonService::class);
            $comparison = $routeComparisonService->getRecommendation($routeOptions, 'balanced');
            
            // Add comparison data to route options
            foreach ($routeOptions as &$option) {
                $option['is_recommended'] = ($option['option'] === $comparison['best_option']);
            }
            
            // Save optimized waypoint order if available
            $optimizedWaypointOrder = null;
            if ($comparison['best_route'] && isset($comparison['best_route']['waypoint_order'])) {
                $optimizedWaypointOrder = $comparison['best_route']['waypoint_order'];
            }
            
            // Save sequential optimization order (shipment_ids in optimized order)
            $sequentialOptimizedOrder = array_map(function($d) {
                return $d['shipment_id'] ?? null;
            }, $optimizedDestinations);
            
            // Calculate total CT-e values (sum of all shipment values) - This is the total revenue
            $totalCteValue = $route->shipments()->sum('value') ?? 0;
            $totalGoodsValue = $route->shipments()->sum('goods_value') ?? 0;
            
            \Log::info('Route CT-e values calculated', [
                'route_id' => $route->id,
                'total_cte_value' => $totalCteValue,
                'total_goods_value' => $totalGoodsValue,
                'shipments_count' => $route->shipments()->count(),
            ]);
            
            // Update route with options and comparison
            $route->update([
                'route_options' => $routeOptions,
                'total_revenue' => $totalCteValue, // Total revenue from CT-e values
                'settings' => array_merge($route->settings ?? [], [
                    'route_comparison' => $comparison,
                    'optimized_waypoint_order' => $optimizedWaypointOrder,
                    'sequential_optimized_order' => $sequentialOptimizedOrder, // Order of shipment_ids from sequential optimization
                    'best_route_option' => $comparison['best_option'],
                    'total_cte_value' => $totalCteValue,
                    'total_goods_value' => $totalGoodsValue,
                ]),
            ]);

            // Save planned path from the selected/best route option
            if (isset($comparison['best_route']) && !empty($comparison['best_route'])) {
                $routePathService = app(\App\Services\RoutePathService::class);
                $bestOption = $routeOptions[$comparison['best_option'] - 1] ?? null;
                if ($bestOption) {
                    $routePathService->savePlannedPath($route, $bestOption);
                }
            }
            
            \Log::info('Route options calculated successfully with optimization', [
                'route_id' => $route->id,
                'options_count' => count($routeOptions),
                'best_option' => $comparison['best_option'],
                'best_cost' => $comparison['best_route']['estimated_cost'] ?? null,
                'optimized_order' => $optimizedWaypointOrder !== null,
            ]);
        } else {
            \Log::warning('No route options returned from Google Maps API', [
                'route_id' => $route->id,
                'waypoints_count' => count($waypoints),
            ]);
        }
        } catch (\Exception $e) {
            \Log::error('Error calculating route options', [
                'route_id' => $route->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Don't throw exception - let the page load without route options
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
                'message' => 'Depósito/Filial criado com sucesso!',
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
                'message' => 'Erro ao criar Depósito/Filial: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Authorize access to route
     */
    protected function authorizeAccess(Route $route)
    {
        $tenant = Auth::user()->tenant;
        
        if (!$tenant) {
            abort(403, 'Usuário não possui tenant associado.');
        }
        
        if (!$route || !$route->exists) {
            abort(404, 'Rota não encontrada.');
        }
        
        if ($route->tenant_id !== $tenant->id) {
            abort(403, 'Acesso não autorizado a esta rota.');
        }
    }
}

