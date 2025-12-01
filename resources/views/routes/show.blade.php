@extends('layouts.app')

@section('title', 'Detalhes da Rota - TMS SaaS')
@section('page-title', 'Detalhes da Rota')

@push('styles')
@include('shared.styles')
@endpush

@section('content')
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">{{ $route->name }}</h1>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="{{ route('routes.edit', $route) }}" class="btn-primary">Editar</a>
        <a href="{{ route('routes.index') }}" class="btn-secondary">Voltar</a>
        @if($route->status !== 'in_progress')
        <form action="{{ route('routes.destroy', $route) }}" method="POST" style="display: inline;" onsubmit="return confirm('Tem certeza que deseja excluir esta rota? Esta ação não pode ser desfeita.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-secondary" style="background-color: rgba(244, 67, 54, 0.2); color: #f44336; border: 1px solid rgba(244, 67, 54, 0.3);">
                <i class="fas fa-trash"></i> Excluir
            </button>
        </form>
        @endif
    </div>
</div>

<div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px; margin-bottom: 30px;">
    <h3 style="color: var(--cor-acento); margin-bottom: 20px;">Informações da Rota</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
        <div>
            <span style="color: rgba(245, 245, 245, 0.7);">Motorista:</span>
            <span style="color: var(--cor-texto-claro); font-weight: 600;">
                @if($route->driver)
                    <a href="{{ route('drivers.show', $route->driver) }}" style="color: var(--cor-acento); text-decoration: none;">{{ $route->driver->name }}</a>
                @else
                    N/A
                @endif
            </span>
        </div>
        @if($route->vehicle)
        <div>
            <span style="color: rgba(245, 245, 245, 0.7);">Veículo:</span>
            <span style="color: var(--cor-texto-claro); font-weight: 600;">
                <a href="{{ route('vehicles.show', $route->vehicle) }}" style="color: var(--cor-acento); text-decoration: none;">{{ $route->vehicle->formatted_plate }}</a>
                @if($route->vehicle->brand && $route->vehicle->model)
                    <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;"> - {{ $route->vehicle->brand }} {{ $route->vehicle->model }}</span>
                @endif
            </span>
        </div>
        @endif
        <div>
            <span style="color: rgba(245, 245, 245, 0.7);">Status:</span>
            <span class="status-badge">{{ $route->status_label }}</span>
        </div>
        <div>
            <span style="color: rgba(245, 245, 245, 0.7);">Data Agendada:</span>
            <span style="color: var(--cor-texto-claro); font-weight: 600;">{{ $route->scheduled_date->format('d/m/Y') }}</span>
        </div>
        <div>
            <span style="color: rgba(245, 245, 245, 0.7);">Cargas:</span>
            <span style="color: var(--cor-texto-claro); font-weight: 600;">{{ $route->shipments->count() }}</span>
        </div>
        @if($route->estimated_distance)
        <div>
            <span style="color: rgba(245, 245, 245, 0.7);">Distância Estimada:</span>
            <span style="color: var(--cor-texto-claro); font-weight: 600;">{{ number_format($route->estimated_distance, 2, ',', '.') }} km</span>
        </div>
        @endif
        @if($route->estimated_duration)
        <div>
            <span style="color: rgba(245, 245, 245, 0.7);">Duração Estimada:</span>
            <span style="color: var(--cor-texto-claro); font-weight: 600;">{{ round($route->estimated_duration / 60) }}h {{ $route->estimated_duration % 60 }}min</span>
        </div>
        @endif
        @if($route->settings && isset($route->settings['estimated_fuel_consumption']))
        <div>
            <span style="color: rgba(245, 245, 245, 0.7);">Consumo de Combustível Estimado:</span>
            <span style="color: var(--cor-texto-claro); font-weight: 600;">{{ number_format($route->settings['estimated_fuel_consumption'], 2, ',', '.') }} L</span>
        </div>
        @endif
    </div>
</div>

<!-- Route Map -->
@if($route->shipments->isNotEmpty())
<div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px; margin-bottom: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
        <h3 style="color: var(--cor-acento); margin: 0;">
            <i class="fas fa-map-marked-alt"></i>
            Mapa da Rota
        </h3>
        <div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
            <div id="route-options" style="display: none;">
                <label style="color: rgba(245, 245, 245, 0.7); margin-right: 10px;">Opções de Rota:</label>
                <select id="route-selector" style="padding: 8px 12px; border-radius: 5px; background-color: var(--cor-principal); color: var(--cor-texto-claro); border: 1px solid rgba(255, 255, 255, 0.1);">
                </select>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <label style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Modo de Visualização:</label>
                <select id="map-style-selector" style="padding: 8px 12px; border-radius: 5px; background-color: var(--cor-principal); color: var(--cor-texto-claro); border: 1px solid rgba(255, 255, 255, 0.1); cursor: pointer;">
                    <option value="uber">Modo Uber</option>
                    <option value="google">Google Maps</option>
                </select>
            </div>
        </div>
    </div>
    <div id="route-info" style="background-color: var(--cor-principal); padding: 15px; border-radius: 8px; margin-bottom: 15px; display: none;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
            <div>
                <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Distância:</span>
                <span id="route-distance" style="color: var(--cor-texto-claro); font-weight: 600; margin-left: 5px;"></span>
            </div>
            <div>
                <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Tempo Estimado:</span>
                <span id="route-duration" style="color: var(--cor-texto-claro); font-weight: 600; margin-left: 5px;"></span>
            </div>
            <div>
                <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Tipo:</span>
                <span id="route-type" style="color: var(--cor-texto-claro); font-weight: 600; margin-left: 5px;"></span>
            </div>
        </div>
    </div>
    <div id="route-map" style="width: 100%; height: 500px; border-radius: 10px; overflow: hidden;"></div>
</div>
@endif

<!-- Fiscal Document Section -->
<div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px; margin-bottom: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3 style="color: var(--cor-acento); margin: 0;">
            <i class="fas fa-file-invoice"></i>
            Documento Fiscal (MDF-e)
        </h3>
        <div style="display: flex; gap: 10px;">
            @if($mdfe && $mdfe->mitt_id)
                <form action="{{ route('fiscal.sync-mdfe', $route) }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn-secondary" id="sync-mdfe-btn" 
                            onclick="this.disabled=true; this.innerHTML='<i class=\'fas fa-sync fa-spin\'></i> Sincronizando...';">
                        <i class="fas fa-sync"></i>
                        Sincronizar do Mitt
                    </button>
                </form>
            @endif
            @if($route->shipments->count() > 0)
                @php
                    $allCtesAuthorized = $route->shipments->every(function($shipment) {
                        return $shipment->hasAuthorizedCte();
                    });
                @endphp
                @if(!$mdfe || !$mdfe->isAuthorized())
                    <form action="{{ route('fiscal.issue-mdfe', $route) }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn-primary" id="issue-mdfe-btn" 
                                {{ !$allCtesAuthorized ? 'disabled title="Todas as cargas devem ter CT-e autorizado"' : '' }}
                                onclick="this.disabled=true; this.innerHTML='<i class=\'fas fa-spinner fa-spin\'></i> Processando...';">
                            <i class="fas fa-file-invoice"></i>
                            @if($mdfe && $mdfe->isProcessing())
                                Processando MDF-e...
                            @else
                                Emitir MDF-e
                            @endif
                        </button>
                    </form>
                @endif
            @endif
        </div>
    </div>

    @if($mdfe)
        <div style="background-color: var(--cor-principal); padding: 20px; border-radius: 10px; margin-bottom: 20px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 15px;">
                <div>
                    <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Status:</span>
                    <span class="status-badge" style="background-color: {{ $mdfe->status === 'authorized' ? 'rgba(76, 175, 80, 0.2)' : ($mdfe->status === 'rejected' ? 'rgba(244, 67, 54, 0.2)' : 'rgba(255, 193, 7, 0.2)') }}; color: {{ $mdfe->status === 'authorized' ? '#4caf50' : ($mdfe->status === 'rejected' ? '#f44336' : '#ffc107') }};">
                        {{ $mdfe->status_label }}
                    </span>
                </div>
                @if($mdfe->access_key)
                    <div>
                        <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Chave de Acesso:</span>
                        <span style="color: var(--cor-texto-claro); font-family: monospace; font-size: 0.85em;">{{ $mdfe->access_key }}</span>
                    </div>
                @endif
                @if($mdfe->mitt_number)
                    <div>
                        <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Número:</span>
                        <span style="color: var(--cor-texto-claro);">{{ $mdfe->mitt_number }}</span>
                    </div>
                @endif
                @if($mdfe->authorized_at)
                    <div>
                        <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Autorizado em:</span>
                        <span style="color: var(--cor-texto-claro);">{{ $mdfe->authorized_at->format('d/m/Y H:i') }}</span>
                    </div>
                @endif
            </div>
            
            @if($mdfe->pdf_url || $mdfe->xml_url)
                <div style="display: flex; gap: 10px; margin-top: 15px;">
                    @if($mdfe->pdf_url)
                        <a href="{{ $mdfe->pdf_url }}" target="_blank" class="btn-secondary" style="padding: 8px 16px;">
                            <i class="fas fa-file-pdf"></i> Ver PDF
                        </a>
                    @endif
                    @if($mdfe->xml_url)
                        <a href="{{ $mdfe->xml_url }}" target="_blank" class="btn-secondary" style="padding: 8px 16px;">
                            <i class="fas fa-code"></i> Ver XML
                        </a>
                    @endif
                </div>
            @endif
            
            @if($mdfe->error_message)
                <div style="margin-top: 15px; padding: 15px; background-color: rgba(244, 67, 54, 0.2); border-radius: 5px; border-left: 4px solid #f44336;">
                    <p style="color: #f44336; margin: 0;">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Erro:</strong> {{ $mdfe->error_message }}
                    </p>
                </div>
            @endif
        </div>
        @include('fiscal.timeline', ['fiscalDocument' => $mdfe, 'documentType' => 'mdfe'])
    @else
        @if($route->shipments->count() > 0)
            @php
                $allCtesAuthorized = $route->shipments->every(function($shipment) {
                    return $shipment->hasAuthorizedCte();
                });
            @endphp
            @if(!$allCtesAuthorized)
                <div style="padding: 20px; background-color: rgba(255, 193, 7, 0.2); border-radius: 10px; border-left: 4px solid #ffc107;">
                    <p style="color: var(--cor-texto-claro); margin: 0;">
                        <i class="fas fa-exclamation-triangle"></i>
                        Todas as cargas desta rota devem ter CT-e autorizado antes de emitir o MDF-e.
                    </p>
                </div>
            @else
                <div style="text-align: center; padding: 40px; color: rgba(245, 245, 245, 0.7);">
                    <i class="fas fa-file-invoice" style="font-size: 3em; margin-bottom: 15px; opacity: 0.3;"></i>
                    <p>Nenhum MDF-e emitido ainda. Clique em "Emitir MDF-e" para iniciar o processo de emissão.</p>
                </div>
            @endif
        @else
            <div style="text-align: center; padding: 40px; color: rgba(245, 245, 245, 0.7);">
                <i class="fas fa-file-invoice" style="font-size: 3em; margin-bottom: 15px; opacity: 0.3;"></i>
                <p>Adicione cargas a esta rota antes de emitir o MDF-e.</p>
            </div>
        @endif
    @endif
</div>

@if($route->shipments->count() > 0)
<div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px; margin-bottom: 30px;">
    <h3 style="color: var(--cor-acento); margin-bottom: 20px;">Cargas ({{ $route->shipments->count() }})</h3>
    <div style="display: grid; gap: 15px;">
        @foreach($route->shipments as $shipment)
            <div style="background-color: var(--cor-principal); padding: 20px; border-radius: 10px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="flex: 1;">
                        <h4 style="color: var(--cor-texto-claro); margin-bottom: 5px;">{{ $shipment->tracking_number }}</h4>
                        <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">{{ $shipment->title }}</p>
                        <div style="margin-top: 10px; display: flex; gap: 10px; align-items: center;">
                            <span class="status-badge" style="background-color: rgba(255, 107, 53, 0.2); color: var(--cor-acento); font-size: 0.85em;">
                                {{ ucfirst(str_replace('_', ' ', $shipment->status)) }}
                            </span>
                            @if($shipment->hasAuthorizedCte())
                                <span class="status-badge" style="background-color: rgba(76, 175, 80, 0.2); color: #4caf50; font-size: 0.85em;">
                                    <i class="fas fa-check-circle"></i> CT-e Autorizado
                                </span>
                            @else
                                <span class="status-badge" style="background-color: rgba(255, 193, 7, 0.2); color: #ffc107; font-size: 0.85em;">
                                    <i class="fas fa-clock"></i> CT-e Pendente
                                </span>
                            @endif
                        </div>
                    </div>
                    <a href="{{ route('shipments.show', $shipment) }}" class="btn-secondary" style="padding: 8px 16px; margin-left: 15px;">
                        Ver
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- CT-e XML Files Section -->
@php
    $cteDocuments = $route->shipments->flatMap(function($shipment) {
        return $shipment->fiscalDocuments->where('document_type', 'cte')->where('status', 'authorized');
    });
@endphp

@if($cteDocuments->count() > 0)
<div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px;">
    <h3 style="color: var(--cor-acento); margin-bottom: 20px;">
        <i class="fas fa-file-code"></i>
        CT-e XML Files ({{ $cteDocuments->count() }})
    </h3>
    <div style="display: grid; gap: 15px;">
        @foreach($cteDocuments as $cte)
            <div style="background-color: var(--cor-principal); padding: 20px; border-radius: 10px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="flex: 1;">
                        <h4 style="color: var(--cor-texto-claro); margin-bottom: 5px;">
                            CT-e {{ $cte->access_key ? substr($cte->access_key, 0, 8) . '...' : 'N/A' }}
                        </h4>
                        @if($cte->shipment)
                            <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">
                                Carga: {{ $cte->shipment->tracking_number }}
                            </p>
                        @endif
                        @if($cte->access_key)
                            <p style="color: rgba(245, 245, 245, 0.6); font-size: 0.85em; font-family: monospace; margin-top: 5px;">
                                {{ $cte->access_key }}
                            </p>
                        @endif
                        @if($cte->authorized_at)
                            <p style="color: rgba(245, 245, 245, 0.6); font-size: 0.85em; margin-top: 5px;">
                                Autorizado em: {{ $cte->authorized_at->format('d/m/Y H:i') }}
                            </p>
                        @endif
                    </div>
                    <div style="display: flex; gap: 10px; margin-left: 15px;">
                        @if($cte->xml_url || $cte->xml)
                            <a href="{{ route('routes.download-cte-xml', ['route' => $route->id, 'fiscalDocument' => $cte->id]) }}" 
                               class="btn-secondary" 
                               style="padding: 8px 16px;"
                               download>
                                <i class="fas fa-download"></i> Baixar XML
                            </a>
                        @endif
                        @if($cte->xml_url)
                            <a href="{{ $cte->xml_url }}" 
                               target="_blank" 
                               class="btn-secondary" 
                               style="padding: 8px 16px;">
                                <i class="fas fa-external-link-alt"></i> Ver XML
                            </a>
                        @endif
                        @if($cte->pdf_url)
                            <a href="{{ $cte->pdf_url }}" 
                               target="_blank" 
                               class="btn-secondary" 
                               style="padding: 8px 16px;">
                                <i class="fas fa-file-pdf"></i> PDF
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif
@endif

@if(session('success'))
    <div class="alert alert-success">
        <i class="fas fa-check mr-2"></i>
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-error">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        {{ $errors->first() }}
    </div>
@endif

@push('scripts')
<script>
    // Auto-refresh fiscal document status if processing
    @if($mdfe && $mdfe->isProcessing())
        setTimeout(function() {
            location.reload();
        }, 10000); // Refresh every 10 seconds
    @endif

    setTimeout(() => {
        const messages = document.querySelectorAll('.alert');
        messages.forEach(msg => msg.remove());
    }, 5000);

    // Initialize route map
    @if($route->shipments->isNotEmpty())
    let routeMap;
    let routeMarkers = [];
    let routePolyline;
    let directionsRenderer;
    let availableRoutes = [];
    let currentRouteIndex = 0;
    let currentMapStyle = 'uber'; // Default to Uber style
    
    // Map style configurations
    const mapStyles = {
        uber: [
            // Uber-like map styling - cleaner, more minimal
            {
                featureType: 'poi',
                elementType: 'labels',
                stylers: [{ visibility: 'off' }]
            },
            {
                featureType: 'poi.business',
                stylers: [{ visibility: 'off' }]
            },
            {
                featureType: 'transit',
                elementType: 'labels',
                stylers: [{ visibility: 'off' }]
            },
            {
                featureType: 'transit.station',
                stylers: [{ visibility: 'off' }]
            },
            {
                featureType: 'road',
                elementType: 'geometry',
                stylers: [{ color: '#ffffff' }]
            },
            {
                featureType: 'road',
                elementType: 'labels.text.fill',
                stylers: [{ color: '#757575' }]
            },
            {
                featureType: 'road.highway',
                elementType: 'geometry',
                stylers: [{ color: '#dadada' }]
            },
            {
                featureType: 'road.highway',
                elementType: 'labels.text.fill',
                stylers: [{ color: '#616161' }]
            },
            {
                featureType: 'water',
                elementType: 'geometry',
                stylers: [{ color: '#c9c9c9' }]
            },
            {
                featureType: 'landscape',
                elementType: 'geometry',
                stylers: [{ color: '#f5f5f5' }]
            },
            {
                featureType: 'administrative',
                elementType: 'labels.text.fill',
                stylers: [{ color: '#757575' }]
            }
        ],
        google: [] // Empty array = default Google Maps style
    };
    
    // Route style configurations
    const routeStyles = {
        uber: {
            strokeColor: '#1a73e8',
            strokeOpacity: 1.0,
            strokeWeight: 6,
            pickupColor: '#1a73e8',
            deliveryColor: '#34a853',
            markerScale: 12,
            markerStrokeWeight: 3
        },
        google: {
            strokeColor: '#4285F4',
            strokeOpacity: 0.8,
            strokeWeight: 5,
            pickupColor: '#2196F3',
            deliveryColor: '#4CAF50',
            markerScale: 10,
            markerStrokeWeight: 2
        }
    };

    function initRouteMap() {
        const mapContainer = document.getElementById('route-map');
        if (!mapContainer) return;

        const apiKey = '{{ config("services.google_maps.api_key") }}';
        if (!apiKey) {
            mapContainer.innerHTML = '<div style="padding: 20px; text-align: center; color: #fff;"><p>Google Maps API key não configurada.</p></div>';
            return;
        }

        // Load Google Maps API with Directions library
        if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
            const script = document.createElement('script');
            script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&libraries=geometry,places,directions&language=pt-BR&callback=initRouteMapCallback`;
            script.async = true;
            script.defer = true;
            document.head.appendChild(script);
            
            window.initRouteMapCallback = function() {
                initRouteMap();
            };
            return;
        }

        // Initialize map
        const center = @if($route->start_latitude && $route->start_longitude)
            { lat: {{ $route->start_latitude }}, lng: {{ $route->start_longitude }} }
        @else
            { lat: -23.5505, lng: -46.6333 } // São Paulo default
        @endif;

        // Load saved map style preference or default to 'uber'
        currentMapStyle = localStorage.getItem('routeMapStyle') || 'uber';
        
        routeMap = new google.maps.Map(mapContainer, {
            center: center,
            zoom: 10,
            mapTypeId: 'roadmap',
            styles: mapStyles[currentMapStyle],
            disableDefaultUI: false,
            zoomControl: true,
            mapTypeControl: currentMapStyle === 'google', // Show map/satellite selector only in Google mode
            mapTypeControlOptions: {
                style: google.maps.MapTypeControlStyle.HORIZONTAL_BAR,
                position: google.maps.ControlPosition.TOP_RIGHT
            },
            scaleControl: false,
            streetViewControl: currentMapStyle === 'google', // Show Street View only in Google mode
            streetViewControlOptions: {
                position: google.maps.ControlPosition.RIGHT_CENTER
            },
            rotateControl: false,
            fullscreenControl: true
        });
        
        // Set selector to current style
        const styleSelector = document.getElementById('map-style-selector');
        if (styleSelector) {
            styleSelector.value = currentMapStyle;
            styleSelector.addEventListener('change', function() {
                currentMapStyle = this.value;
                localStorage.setItem('routeMapStyle', currentMapStyle);
                applyMapStyle(currentMapStyle);
            });
        }

        const bounds = new google.maps.LatLngBounds();
        const waypoints = [];

        // Add markers for each shipment
        @foreach($route->shipments as $shipment)
            @if($shipment->pickup_latitude && $shipment->pickup_longitude)
                const pickupPos{{ $shipment->id }} = { lat: {{ $shipment->pickup_latitude }}, lng: {{ $shipment->pickup_longitude }} };
                const pickupMarker{{ $shipment->id }} = new google.maps.Marker({
                    position: pickupPos{{ $shipment->id }},
                    map: routeMap,
                    icon: {
                        path: google.maps.SymbolPath.CIRCLE,
                        scale: routeStyles[currentMapStyle].markerScale,
                        fillColor: routeStyles[currentMapStyle].pickupColor,
                        fillOpacity: 1,
                        strokeColor: '#FFFFFF',
                        strokeWeight: routeStyles[currentMapStyle].markerStrokeWeight,
                        zIndex: 1000
                    },
                    title: 'Coleta: {{ $shipment->tracking_number }}'
                });
                
                const pickupInfo{{ $shipment->id }} = new google.maps.InfoWindow({
                    content: `<div style="padding: 10px; min-width: 200px;">
                        <h4 style="margin: 0 0 10px 0; color: var(--cor-acento);">Coleta: {{ $shipment->tracking_number }}</h4>
                        <p style="margin: 5px 0; color: #666;">{{ $shipment->pickup_address }}</p>
                        <p style="margin: 5px 0; color: #666;">{{ $shipment->pickup_city }}, {{ $shipment->pickup_state }}</p>
                    </div>`
                });
                
                pickupMarker{{ $shipment->id }}.addListener('click', () => {
                    pickupInfo{{ $shipment->id }}.open(routeMap, pickupMarker{{ $shipment->id }});
                });
                
                // Store marker type for style updates
                pickupMarker{{ $shipment->id }}.markerType = 'pickup';
                routeMarkers.push(pickupMarker{{ $shipment->id }});
                bounds.extend(pickupPos{{ $shipment->id }});
                waypoints.push(pickupPos{{ $shipment->id }});
            @endif

            @if($shipment->delivery_latitude && $shipment->delivery_longitude)
                const deliveryPos{{ $shipment->id }} = { lat: {{ $shipment->delivery_latitude }}, lng: {{ $shipment->delivery_longitude }} };
                const deliveryMarker{{ $shipment->id }} = new google.maps.Marker({
                    position: deliveryPos{{ $shipment->id }},
                    map: routeMap,
                    icon: {
                        path: google.maps.SymbolPath.CIRCLE,
                        scale: routeStyles[currentMapStyle].markerScale,
                        fillColor: routeStyles[currentMapStyle].deliveryColor,
                        fillOpacity: 1,
                        strokeColor: '#FFFFFF',
                        strokeWeight: routeStyles[currentMapStyle].markerStrokeWeight,
                        zIndex: 1000
                    },
                    title: 'Entrega: {{ $shipment->tracking_number }}'
                });
                
                const deliveryInfo{{ $shipment->id }} = new google.maps.InfoWindow({
                    content: `<div style="padding: 10px; min-width: 200px;">
                        <h4 style="margin: 0 0 10px 0; color: var(--cor-acento);">Entrega: {{ $shipment->tracking_number }}</h4>
                        <p style="margin: 5px 0; color: #666;">{{ $shipment->delivery_address }}</p>
                        <p style="margin: 5px 0; color: #666;">{{ $shipment->delivery_city }}, {{ $shipment->delivery_state }}</p>
                    </div>`
                });
                
                deliveryMarker{{ $shipment->id }}.addListener('click', () => {
                    deliveryInfo{{ $shipment->id }}.open(routeMap, deliveryMarker{{ $shipment->id }});
                });
                
                // Store marker type for style updates
                deliveryMarker{{ $shipment->id }}.markerType = 'delivery';
                routeMarkers.push(deliveryMarker{{ $shipment->id }});
                bounds.extend(deliveryPos{{ $shipment->id }});
                waypoints.push(deliveryPos{{ $shipment->id }});
            @endif
        @endforeach

        // Fit map to show all markers with padding (Uber-like spacing)
        if (waypoints.length > 0) {
            routeMap.fitBounds(bounds, {
                top: 50,
                right: 50,
                bottom: 50,
                left: 50
            });
            
            // Calculate route using Directions API to follow roads
            if (waypoints.length > 1) {
                calculateRouteWithDirections(waypoints);
            }
        }
    }

    // Calculate route using Google Directions API with multiple alternatives
    function calculateRouteWithDirections(waypoints) {
        if (waypoints.length < 2) return;

        const directionsService = new google.maps.DirectionsService();
        
        // Initialize directions renderer
        directionsRenderer = new google.maps.DirectionsRenderer({
            map: routeMap,
            suppressMarkers: true, // We already have custom markers
            polylineOptions: {
                strokeColor: routeStyles[currentMapStyle].strokeColor,
                strokeOpacity: routeStyles[currentMapStyle].strokeOpacity,
                strokeWeight: routeStyles[currentMapStyle].strokeWeight,
                icons: [] // Ensure continuous line without dots
            }
        });

        // Build waypoints array (excluding origin and destination)
        const waypointsArray = waypoints.slice(1, -1).map(wp => ({
            location: { lat: wp.lat, lng: wp.lng },
            stopover: true
        }));

        const request = {
            origin: { lat: waypoints[0].lat, lng: waypoints[0].lng },
            destination: { lat: waypoints[waypoints.length - 1].lat, lng: waypoints[waypoints.length - 1].lng },
            waypoints: waypointsArray.length > 0 ? waypointsArray : undefined,
            provideRouteAlternatives: true, // Request alternative routes
            optimizeWaypoints: false, // Keep original order
            travelMode: google.maps.TravelMode.DRIVING,
            unitSystem: google.maps.UnitSystem.METRIC,
            language: 'pt-BR',
            avoidHighways: false,
            avoidTolls: false
        };

        directionsService.route(request, function(result, status) {
            if (status === 'OK') {
                availableRoutes = result.routes;
                
                // Display route options if multiple routes available
                if (availableRoutes.length > 1) {
                    displayRouteOptions(availableRoutes);
                }
                
                // Display first route (will create continuous polyline)
                displayRoute(0);
            } else {
                console.error('Directions request failed:', status);
                // Fallback to simple polyline if Directions API fails
                const style = routeStyles[currentMapStyle];
                routePolyline = new google.maps.Polyline({
                    path: waypoints,
                    geodesic: true,
                    strokeColor: style.strokeColor,
                    strokeOpacity: style.strokeOpacity,
                    strokeWeight: style.strokeWeight,
                    icons: [], // Ensure continuous line without dots
                    zIndex: 100
                });
                routePolyline.setMap(routeMap);
            }
        });
    }

    // Apply map style and update markers/route
    function applyMapStyle(styleName) {
        if (!routeMap) return;
        
        currentMapStyle = styleName;
        
        // Apply map styles and controls
        routeMap.setOptions({
            styles: mapStyles[styleName],
            mapTypeControl: styleName === 'google', // Show map/satellite selector only in Google mode
            streetViewControl: styleName === 'google' // Show Street View only in Google mode
        });
        
        // Update markers
        const style = routeStyles[styleName];
        routeMarkers.forEach((marker) => {
            // Use stored marker type to determine color
            const isPickup = marker.markerType === 'pickup';
            const icon = marker.getIcon();
            
            if (icon && typeof icon === 'object') {
                marker.setIcon({
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: style.markerScale,
                    fillColor: isPickup ? style.pickupColor : style.deliveryColor,
                    fillOpacity: 1,
                    strokeColor: '#FFFFFF',
                    strokeWeight: style.markerStrokeWeight,
                    zIndex: 1000
                });
            }
        });
        
        // Update route polyline if exists
        if (routePolyline) {
            const currentPath = routePolyline.getPath();
            routePolyline.setMap(null);
            
            routePolyline = new google.maps.Polyline({
                path: currentPath,
                geodesic: true,
                strokeColor: style.strokeColor,
                strokeOpacity: style.strokeOpacity,
                strokeWeight: style.strokeWeight,
                icons: [],
                zIndex: 100
            });
            routePolyline.setMap(routeMap);
        }
        
        // If there's a current route displayed, refresh it
        if (availableRoutes.length > 0 && currentRouteIndex !== undefined) {
            displayRoute(currentRouteIndex);
        }
    }

    // Display route options selector
    function displayRouteOptions(routes) {
        const selector = document.getElementById('route-selector');
        const optionsContainer = document.getElementById('route-options');
        
        if (!selector || !optionsContainer) return;
        
        // Clear existing options
        selector.innerHTML = '';
        
        // Add options for each route
        routes.forEach((route, index) => {
            const leg = route.legs[0];
            const distance = leg.distance.text;
            const duration = leg.duration.text;
            const summary = route.summary || `Rota ${index + 1}`;
            
            const option = document.createElement('option');
            option.value = index;
            option.textContent = `Rota ${index + 1}: ${distance} - ${duration}${summary ? ' (' + summary + ')' : ''}`;
            selector.appendChild(option);
        });
        
        // Show selector
        optionsContainer.style.display = 'block';
        
        // Add change listener
        selector.addEventListener('change', function() {
            currentRouteIndex = parseInt(this.value);
            displayRoute(currentRouteIndex);
        });
    }

    // Display specific route
    function displayRoute(index) {
        if (!availableRoutes[index]) return;
        
        const route = availableRoutes[index];
        
        // Calculate total distance and duration
        let totalDistance = 0;
        let totalDuration = 0;
        
        route.legs.forEach(leg => {
            totalDistance += leg.distance.value;
            totalDuration += leg.duration.value;
        });
        
        // Remove existing polyline if any
        if (routePolyline) {
            routePolyline.setMap(null);
        }
        
        // Create continuous polyline from route path (like Uber)
        const path = [];
        route.legs.forEach(leg => {
            leg.steps.forEach(step => {
                step.path.forEach(point => {
                    path.push(point);
                });
            });
        });
        
        const style = routeStyles[currentMapStyle];
        routePolyline = new google.maps.Polyline({
            path: path,
            geodesic: true,
            strokeColor: style.strokeColor,
            strokeOpacity: style.strokeOpacity,
            strokeWeight: style.strokeWeight,
            icons: [], // Ensure continuous line without dots
            zIndex: 100
        });
        routePolyline.setMap(routeMap);
        
        // Update route info
        updateRouteInfo(totalDistance, totalDuration, route.summary);
        
        // Update bounds
        const bounds = new google.maps.LatLngBounds();
        route.overview_path.forEach(path => {
            bounds.extend(path);
        });
        
        // Extend bounds with markers
        routeMarkers.forEach(marker => {
            bounds.extend(marker.getPosition());
        });
        
        // Fit bounds with padding (Uber-like spacing)
        routeMap.fitBounds(bounds, {
            top: 50,
            right: 50,
            bottom: 50,
            left: 50
        });
    }

    // Update route information display
    function updateRouteInfo(distanceMeters, durationSeconds, summary) {
        const distanceKm = (distanceMeters / 1000).toFixed(2);
        const hours = Math.floor(durationSeconds / 3600);
        const minutes = Math.floor((durationSeconds % 3600) / 60);
        
        const distanceEl = document.getElementById('route-distance');
        const durationEl = document.getElementById('route-duration');
        const typeEl = document.getElementById('route-type');
        const infoEl = document.getElementById('route-info');
        
        if (distanceEl) distanceEl.textContent = distanceKm + ' km';
        if (durationEl) durationEl.textContent = hours > 0 ? `${hours}h ${minutes}min` : `${minutes}min`;
        if (typeEl) typeEl.textContent = summary || 'Rota padrão';
        if (infoEl) infoEl.style.display = 'block';
    }

    // Initialize map when page loads
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initRouteMap);
    } else {
        initRouteMap();
    }
    @endif
</script>
@endpush
@endsection


