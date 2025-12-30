@extends('layouts.app')

@section('title', 'Monitoramento - TMS SaaS')
@section('page-title', 'Monitoramento em Tempo Real')

@push('styles')
@include('shared.styles')
<style>
    .monitoring-dashboard {
        display: grid;
        grid-template-columns: 1fr 350px;
        gap: 20px;
        height: calc(100vh - 200px);
    }

    .map-container {
        background-color: var(--cor-secundaria);
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        position: relative;
    }

    #monitoring-map {
        width: 100%;
        height: 100%;
        min-height: 600px;
    }

    .monitoring-panel {
        background-color: var(--cor-secundaria);
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        overflow-y: auto;
        max-height: calc(100vh - 200px);
    }

    .driver-card {
        background-color: var(--cor-principal);
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 15px;
        border-left: 4px solid var(--cor-acento);
        cursor: pointer;
        transition: transform 0.2s;
    }

    .driver-card:hover {
        transform: translateX(5px);
    }

    .driver-card.active {
        border-left-color: #4caf50;
    }

    .route-card {
        background-color: var(--cor-principal);
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 15px;
        border-left: 4px solid #2196F3;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
        margin-bottom: 20px;
    }

    .stat-item {
        background-color: var(--cor-principal);
        padding: 15px;
        border-radius: 10px;
        text-align: center;
    }

    .stat-item h3 {
        color: var(--cor-acento);
        font-size: 2em;
        margin: 0;
    }

    .stat-item p {
        color: rgba(245, 245, 245, 0.7);
        font-size: 0.9em;
        margin: 5px 0 0 0;
    }

    @media (max-width: 1024px) {
        .monitoring-dashboard {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Monitoramento em Tempo Real</h1>
        <h2>Rastreie motoristas e cargas em tempo real</h2>
    </div>
    <div style="display: flex; gap: 10px;">
        <button id="refresh-locations" class="btn-primary">
            <i class="fas fa-sync-alt"></i> Atualizar
        </button>
    </div>
</div>

<div class="monitoring-dashboard">
    <div class="map-container">
        <div style="position: absolute; top: 15px; left: 15px; z-index: 1000; display: flex; align-items: center; gap: 10px; background-color: rgba(0, 0, 0, 0.6); padding: 10px; border-radius: 8px;">
            <label style="color: rgba(245, 245, 245, 0.9); font-size: 0.9em; margin: 0;">Modo:</label>
            <select id="monitoring-map-style-selector" style="padding: 6px 10px; border-radius: 5px; background-color: var(--cor-principal); color: var(--cor-texto-claro); border: 1px solid rgba(255, 255, 255, 0.2); cursor: pointer; font-size: 0.9em;">
                <option value="uber">Modo Uber</option>
                <option value="google">Google Maps</option>
            </select>
        </div>
        <div id="monitoring-map"></div>
    </div>

    <div class="monitoring-panel">
        <div class="stats-grid">
            <div class="stat-item">
                <h3>{{ $activeDrivers->count() }}</h3>
                <p>Motoristas Ativos</p>
            </div>
        <div class="stat-item">
            <h3>{{ $activeRoutes->count() }}</h3>
            <p>Rotas Ativas</p>
        </div>
            <div class="stat-item">
                <h3>{{ $shipmentsInTransit->count() }}</h3>
                <p>Em Trânsito</p>
            </div>
            <div class="stat-item">
                <h3>{{ $activeRoutes->sum(function($route) { return $route->shipments->count(); }) }}</h3>
                <p>Total de Cargas</p>
            </div>
        </div>

        <h3 style="color: var(--cor-acento); margin-bottom: 15px;">
            <i class="fas fa-users"></i> Motoristas Ativos
        </h3>
        @forelse($activeDrivers as $driver)
            <div class="driver-card" data-driver-id="{{ $driver->id }}">
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <div style="flex: 1; cursor: pointer;" onclick="focusDriver({{ $driver->id }})">
                        <h4 style="color: var(--cor-texto-claro); margin: 0 0 5px 0;">{{ $driver->name }}</h4>
                        @if($driver->phone)
                            <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.85em; margin: 0;">
                                <i class="fas fa-phone"></i> {{ $driver->phone }}
                            </p>
                        @endif
                        @if($driver->routes->first())
                            <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.85em; margin: 5px 0 0 0;">
                                <i class="fas fa-route"></i> {{ $driver->routes->first()->name }}
                            </p>
                        @endif
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 5px; align-items: end;">
                        <span class="status-badge" style="background-color: rgba(76, 175, 80, 0.2); color: #4caf50;">
                            <i class="fas fa-circle" style="font-size: 0.7em;"></i> Online
                        </span>
                        <button 
                            class="toggle-trail-btn" 
                            data-driver-id="{{ $driver->id }}"
                            onclick="toggleDriverTrail({{ $driver->id }}); event.stopPropagation();"
                            title="Mostrar/Ocultar Rastro"
                            style="background: rgba(255, 107, 53, 0.2); border: 1px solid var(--cor-acento); color: var(--cor-acento); padding: 5px 10px; border-radius: 5px; cursor: pointer; font-size: 0.8em; transition: all 0.3s;">
                            <i class="fas fa-map-marked-alt"></i>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div style="text-align: center; padding: 40px; color: rgba(245, 245, 245, 0.7);">
                <i class="fas fa-user-slash" style="font-size: 3em; margin-bottom: 15px; opacity: 0.3;"></i>
                <p>Nenhum motorista ativo no momento</p>
            </div>
        @endforelse

        <h3 style="color: var(--cor-acento); margin-top: 30px; margin-bottom: 15px;">
            <i class="fas fa-route"></i> Rotas Ativas
        </h3>
        @forelse($activeRoutes as $route)
            <div class="route-card" style="cursor: pointer;" onclick="focusRoute({{ $route->id }})">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                    <h4 style="color: var(--cor-texto-claro); margin: 0 0 5px 0;">{{ $route->name }}</h4>
                    <span class="status-badge" style="font-size: 0.75em;">
                        @if($route->status === 'in_progress')
                            Em Andamento
                        @else
                            Agendada
                        @endif
                    </span>
                </div>
                <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin: 5px 0;">
                    @if($route->driver)
                        <i class="fas fa-user"></i> {{ $route->driver->name }}
                    @else
                        <i class="fas fa-user"></i> Sem motorista
                    @endif
                </p>
                <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin: 5px 0;">
                    <i class="fas fa-box"></i> {{ $route->shipments->count() }} {{ $route->shipments->count() == 1 ? 'carga' : 'cargas' }}
                </p>
                @if($route->started_at)
                <p style="color: rgba(76, 175, 80, 0.8); font-size: 0.85em; margin: 5px 0;">
                    <i class="fas fa-play-circle"></i> Iniciada: {{ $route->started_at->format('d/m/Y H:i') }}
                </p>
                @endif
                @if($route->completed_at)
                <p style="color: rgba(76, 175, 80, 0.8); font-size: 0.85em; margin: 5px 0;">
                    <i class="fas fa-check-circle"></i> Finalizada: {{ $route->completed_at->format('d/m/Y H:i') }}
                </p>
                @endif
                @if($route->estimated_distance)
                <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.85em; margin: 5px 0;">
                    <i class="fas fa-route"></i> {{ number_format($route->estimated_distance, 2, ',', '.') }} km
                </p>
                @endif
            </div>
        @empty
            <div style="text-align: center; padding: 40px; color: rgba(245, 245, 245, 0.7);">
                <i class="fas fa-route" style="font-size: 3em; margin-bottom: 15px; opacity: 0.3;"></i>
                <p>Nenhuma rota ativa no momento</p>
            </div>
        @endforelse
    </div>
</div>

@push('scripts')
<script>
    let map;
    let driverMarkers = {};
    let routePolylines = {};
    let shipmentMarkers = [];
    let driverTrails = {}; // Store polyline trails for each driver (yellow - on route)
    let driverOffRouteTrails = {}; // Store polyline trails for drivers off route (red)
    let driverTrailVisibility = {}; // Track visibility state for each driver trail
    let routePaths = {}; // Store route paths for each route to check if driver is on route
    let bounds;
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

    // Initialize Google Maps
    function initMap() {
        if (!document.getElementById('monitoring-map')) {
            console.error('Map container not found');
            return;
        }

        // Load saved map style preference or default to 'uber'
        currentMapStyle = localStorage.getItem('monitoringMapStyle') || 'uber';

        map = new google.maps.Map(document.getElementById('monitoring-map'), {
            center: { lat: -23.5505, lng: -46.6333 }, // São Paulo
            zoom: 10,
            mapTypeId: 'roadmap',
            styles: mapStyles[currentMapStyle],
            disableDefaultUI: false,
            zoomControl: true,
            mapTypeControl: currentMapStyle === 'google',
            mapTypeControlOptions: {
                style: google.maps.MapTypeControlStyle.HORIZONTAL_BAR,
                position: google.maps.ControlPosition.TOP_RIGHT
            },
            scaleControl: false,
            streetViewControl: currentMapStyle === 'google',
            streetViewControlOptions: {
                position: google.maps.ControlPosition.RIGHT_CENTER
            },
            rotateControl: false,
            fullscreenControl: true
        });
        
        // Set selector to current style
        const styleSelector = document.getElementById('monitoring-map-style-selector');
        if (styleSelector) {
            styleSelector.value = currentMapStyle;
            styleSelector.addEventListener('change', function() {
                currentMapStyle = this.value;
                localStorage.setItem('monitoringMapStyle', currentMapStyle);
                applyMonitoringMapStyle(currentMapStyle);
            });
        }

        bounds = new google.maps.LatLngBounds();

        // Load initial data
        loadDriverLocations();
        loadRoutesAndShipments();
    }

    // Apply map style and update markers/routes
    function applyMonitoringMapStyle(styleName) {
        if (!map) return;
        
        currentMapStyle = styleName;
        
        // Apply map styles and controls
        map.setOptions({
            styles: mapStyles[styleName],
            mapTypeControl: styleName === 'google',
            streetViewControl: styleName === 'google'
        });
        
        // Update driver markers
        const style = routeStyles[styleName] || routeStyles.uber;
        Object.values(driverMarkers).forEach(marker => {
            const icon = marker.getIcon();
            if (icon && typeof icon === 'object') {
                marker.setIcon({
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: style.markerScale,
                    fillColor: '#FF0000',
                    fillOpacity: 1,
                    strokeColor: '#FFFFFF',
                    strokeWeight: style.markerStrokeWeight
                });
            }
        });
        
        // Reload routes and markers with new style
        loadRoutesAndShipments();
        loadDriverLocations();
    }

    // Load Google Maps API
    function loadGoogleMaps() {
        const apiKey = '{{ config("services.google_maps.api_key") }}';
        if (!apiKey) {
            console.error('Google Maps API key not configured');
            document.getElementById('monitoring-map').innerHTML = '<div style="padding: 20px; text-align: center; color: #fff;"><p>Google Maps API key not configured. Please set GOOGLE_MAPS_API_KEY in your .env file.</p></div>';
            return;
        }

        const script = document.createElement('script');
        script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&libraries=geometry,places&language=pt-BR&callback=initMap&loading=async`;
        script.async = true;
        script.defer = true;
        script.onerror = function() {
            console.error('Failed to load Google Maps API');
            document.getElementById('monitoring-map').innerHTML = '<div style="padding: 20px; text-align: center; color: #fff;"><p>Failed to load Google Maps. Please check your API key.</p></div>';
        };
        document.head.appendChild(script);
    }

    // Make initMap globally available
    window.initMap = initMap;

    // Load driver locations with trail and custom icon
    function loadDriverLocations() {
        if (!map) return;
        
        fetch('{{ route("monitoring.driver-locations") }}')
            .then(response => response.json())
            .then(drivers => {
                // Remove old markers and trails
                Object.values(driverMarkers).forEach(marker => marker.setMap(null));
                Object.values(driverTrails).forEach(trail => trail.setMap(null));
                Object.values(driverOffRouteTrails).forEach(trail => trail.setMap(null));
                driverMarkers = {};
                driverTrails = {};
                driverOffRouteTrails = {};
                bounds = new google.maps.LatLngBounds();

                if (drivers.length === 0) {
                    return;
                }

                // Add markers and trails for each driver
                drivers.forEach(driver => {
                    if (driver.latitude && driver.longitude) {
                        const position = { lat: driver.latitude, lng: driver.longitude };
                        
                        // Draw trail/path if location history exists
                        // Separate into on-route (yellow) and off-route (red) segments
                        if (driver.location_history && driver.location_history.length > 1) {
                            const allPoints = driver.location_history.map(loc => ({
                                lat: parseFloat(loc.lat),
                                lng: parseFloat(loc.lng)
                            }));
                            
                            // Add current position to trail
                            allPoints.push(position);
                            
                            // Get route path for this driver's active route
                            const routePath = driver.active_route ? routePaths[driver.active_route.id] : null;
                            
                            // Separate points into on-route and off-route
                            const onRoutePoints = [];
                            const offRoutePoints = [];
                            const MAX_DISTANCE_FROM_ROUTE = 100; // meters
                            
                            allPoints.forEach((point, index) => {
                                let isOnRoute = false;
                                
                                if (routePath && routePath.length > 0 && typeof google !== 'undefined' && google.maps && google.maps.geometry) {
                                    // Check distance to nearest point on route using geometry library
                                    let minDistance = Infinity;
                                    const pointLatLng = new google.maps.LatLng(point.lat, point.lng);
                                    
                                    for (let i = 0; i < routePath.length; i++) {
                                        const routePoint = new google.maps.LatLng(routePath[i].lat, routePath[i].lng);
                                        const distance = google.maps.geometry.spherical.computeDistanceBetween(
                                            pointLatLng,
                                            routePoint
                                        );
                                        if (distance < minDistance) {
                                            minDistance = distance;
                                        }
                                    }
                                    
                                    isOnRoute = minDistance <= MAX_DISTANCE_FROM_ROUTE;
                                } else {
                                    // If no route path available or geometry library not loaded, assume on route
                                    isOnRoute = true;
                                }
                                
                                if (isOnRoute) {
                                    // If previous point was off-route, add connection point
                                    if (offRoutePoints.length > 0 && index > 0) {
                                        const lastOffRoute = offRoutePoints[offRoutePoints.length - 1];
                                        onRoutePoints.push(lastOffRoute);
                                    }
                                    onRoutePoints.push(point);
                                } else {
                                    // If previous point was on-route, add connection point
                                    if (onRoutePoints.length > 0 && index > 0) {
                                        const lastOnRoute = onRoutePoints[onRoutePoints.length - 1];
                                        offRoutePoints.push(lastOnRoute);
                                    }
                                    offRoutePoints.push(point);
                                }
                            });
                            
                            // Draw yellow trail for on-route path
                            if (onRoutePoints.length > 1) {
                                const onRouteTrail = new google.maps.Polyline({
                                    path: onRoutePoints,
                                    geodesic: true,
                                    strokeColor: '#FFD700', // Yellow
                                    strokeOpacity: 0.8,
                                    strokeWeight: 4,
                                    zIndex: 500
                                });
                                
                                // Default visibility: visible
                                driverTrailVisibility[driver.id] = driverTrailVisibility[driver.id] !== undefined 
                                    ? driverTrailVisibility[driver.id] 
                                    : true;
                                
                                if (driverTrailVisibility[driver.id]) {
                                    onRouteTrail.setMap(map);
                                }
                                
                                driverTrails[driver.id] = onRouteTrail;
                            }
                            
                            // Draw red trail for off-route path
                            if (offRoutePoints.length > 1) {
                                const offRouteTrail = new google.maps.Polyline({
                                    path: offRoutePoints,
                                    geodesic: true,
                                    strokeColor: '#FF0000', // Red
                                    strokeOpacity: 0.8,
                                    strokeWeight: 4,
                                    zIndex: 501 // Slightly above yellow to be more visible
                                });
                                
                                // Default visibility: visible
                                if (driverTrailVisibility[driver.id] !== false) {
                                    offRouteTrail.setMap(map);
                                }
                                
                                driverOffRouteTrails[driver.id] = offRouteTrail;
                            }
                        }

                        // Create custom icon with driver photo (or placeholder) - lazy loading
                        const driverPhotoUrl = driver.photo_url || ('https://ui-avatars.com/api/?name=' + encodeURIComponent(driver.name) + '&background=FF6B35&color=fff&size=64');
                        
                        // Use placeholder initially for lazy loading
                        const placeholderUrl = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(driver.name.substring(0, 1)) + '&background=FF6B35&color=fff&size=50';
                        
                        const marker = new google.maps.Marker({
                            position: position,
                            map: map,
                            icon: {
                                url: placeholderUrl,
                                scaledSize: new google.maps.Size(50, 50),
                                anchor: new google.maps.Point(25, 25),
                                origin: new google.maps.Point(0, 0)
                            },
                            title: driver.name,
                            zIndex: 1000,
                            optimized: false
                        });
                        
                        // Lazy load actual photo
                        if (driver.photo_url) {
                            const img = new Image();
                            img.onload = function() {
                                marker.setIcon({
                                    url: driverPhotoUrl,
                                    scaledSize: new google.maps.Size(50, 50),
                                    anchor: new google.maps.Point(25, 25),
                                    origin: new google.maps.Point(0, 0)
                                });
                            };
                            img.onerror = function() {
                                // Keep placeholder if image fails to load
                            };
                            img.src = driverPhotoUrl;
                        }

                        // Create tooltip content with route information (lazy loading for photo)
                        const tooltipPhotoUrl = driver.photo_url || ('https://ui-avatars.com/api/?name=' + encodeURIComponent(driver.name) + '&background=FF6B35&color=fff&size=40');
                        let tooltipContent = `<div style="padding: 12px; min-width: 250px; background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                                <img src="${tooltipPhotoUrl}" loading="lazy" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;" onerror="this.src='https://ui-avatars.com/api/?name=' + encodeURIComponent('${driver.name}') + '&background=FF6B35&color=fff&size=40'">
                                <div>
                                    <h4 style="margin: 0; color: #333; font-size: 1.1em;">${driver.name}</h4>
                                    ${driver.phone ? `<p style="margin: 3px 0 0 0; color: #666; font-size: 0.85em;"><i class="fas fa-phone"></i> ${driver.phone}</p>` : ''}
                                </div>
                            </div>`;
                        
                        if (driver.active_route) {
                            tooltipContent += `
                                <div style="border-top: 1px solid #eee; padding-top: 10px; margin-top: 10px;">
                                    <p style="margin: 5px 0; color: #333; font-weight: 600;"><i class="fas fa-route" style="color: #1a73e8;"></i> ${driver.active_route.name}</p>
                                    <p style="margin: 5px 0; color: #666; font-size: 0.9em;">${driver.active_route.shipments_count} ${driver.active_route.shipments_count === 1 ? 'carga' : 'cargas'}</p>
                                    <p style="margin: 5px 0; color: #666; font-size: 0.85em;">Status: <span style="color: ${driver.active_route.status === 'in_progress' ? '#4caf50' : '#ff9800'};">${driver.active_route.status === 'in_progress' ? 'Em Andamento' : 'Agendada'}</span></p>
                                </div>`;
                        }
                        
                        tooltipContent += `
                            <p style="margin: 10px 0 0 0; font-size: 0.75em; color: #999; border-top: 1px solid #eee; padding-top: 8px; margin-top: 8px;">
                                <i class="fas fa-clock"></i> Última atualização: ${new Date(driver.last_update).toLocaleString('pt-BR')}
                            </p>
                        </div>`;

                        // Create info window for click
                        const infoWindow = new google.maps.InfoWindow({
                            content: tooltipContent
                        });

                        // Create tooltip that appears on hover
                        let tooltip = null;
                        marker.addListener('mouseover', function() {
                            if (tooltip) tooltip.close();
                            tooltip = new google.maps.InfoWindow({
                                content: tooltipContent,
                                disableAutoPan: true
                            });
                            tooltip.open(map, marker);
                        });

                        marker.addListener('mouseout', function() {
                            if (tooltip) {
                                tooltip.close();
                                tooltip = null;
                            }
                        });

                        marker.addListener('click', () => {
                            infoWindow.open(map, marker);
                        });

                        driverMarkers[driver.id] = marker;
                        bounds.extend(position);
                    }
                });

                // Fit map to show all drivers
                if (Object.keys(driverMarkers).length > 0) {
                    map.fitBounds(bounds);
                }
            })
            .catch(error => console.error('Error loading driver locations:', error));
    }

    // Load routes and shipments with route paths (using same logic as route show page)
    function loadRoutesAndShipments() {
        if (!map || !bounds) return;
        
        // Clear existing route renderers and shipment markers
        if (window.routeRenderers) {
            window.routeRenderers.forEach(renderer => renderer.setMap(null));
        }
        window.routeRenderers = [];
        shipmentMarkers.forEach(marker => marker.setMap(null));
        shipmentMarkers = [];
        
        @if($activeRoutes->count() > 0)
            @foreach($activeRoutes as $route)
                @if($route->shipments->count() > 0)
                    const route{{ $route->id }}Waypoints = [];
                    
                    // CRITICAL: Add depot/branch as origin (first waypoint) - same logic as route show page
                    @if($route->start_latitude && $route->start_longitude)
                        const originPos{{ $route->id }} = { lat: {{ $route->start_latitude }}, lng: {{ $route->start_longitude }} };
                        const originMarker{{ $route->id }} = new google.maps.Marker({
                            position: originPos{{ $route->id }},
                            map: map,
                            icon: {
                                path: google.maps.SymbolPath.CIRCLE,
                                scale: (routeStyles[currentMapStyle] || routeStyles.uber).markerScale * 1.2,
                                fillColor: '#FF6B35', // Orange for depot/branch
                                fillOpacity: 1,
                                strokeColor: '#FFFFFF',
                                strokeWeight: (routeStyles[currentMapStyle] || routeStyles.uber).markerStrokeWeight + 1,
                                zIndex: 2000
                            },
                            title: 'Ponto de Partida: {{ $route->branch->name ?? "Depósito/Filial" }}'
                        });
                        shipmentMarkers.push(originMarker{{ $route->id }});
                        bounds.extend(originPos{{ $route->id }});
                        route{{ $route->id }}Waypoints.push(originPos{{ $route->id }});
                    @endif
                    
                    // Add only delivery addresses as waypoints (NOT pickups) - same logic as route show page
                    @php
                        $shipments = $route->shipments;
                        $optimizedOrder = $route->settings['sequential_optimized_order'] ?? null;
                        if ($optimizedOrder && is_array($optimizedOrder)) {
                            $shipmentsMap = $shipments->keyBy('id');
                            $orderedShipments = collect();
                            foreach ($optimizedOrder as $shipmentId) {
                                if ($shipmentsMap->has($shipmentId)) {
                                    $orderedShipments->push($shipmentsMap->get($shipmentId));
                                }
                            }
                            foreach ($shipments as $shipment) {
                                if (!in_array($shipment->id, $optimizedOrder)) {
                                    $orderedShipments->push($shipment);
                                }
                            }
                            $shipments = $orderedShipments;
                        }
                    @endphp
                    
                    @foreach($shipments as $shipment)
                        // Show pickup markers but don't add to waypoints
                        @if($shipment->pickup_latitude && $shipment->pickup_longitude)
                            const pickupPos{{ $shipment->id }} = { lat: {{ $shipment->pickup_latitude }}, lng: {{ $shipment->pickup_longitude }} };
                            const pickupStyle{{ $shipment->id }} = routeStyles[currentMapStyle] || routeStyles.uber;
                            
                            const pickupMarker{{ $shipment->id }} = new google.maps.Marker({
                                position: pickupPos{{ $shipment->id }},
                                map: map,
                                icon: {
                                    path: google.maps.SymbolPath.CIRCLE,
                                    scale: pickupStyle{{ $shipment->id }}.markerScale,
                                    fillColor: pickupStyle{{ $shipment->id }}.pickupColor,
                                    fillOpacity: 1,
                                    strokeColor: '#FFFFFF',
                                    strokeWeight: pickupStyle{{ $shipment->id }}.markerStrokeWeight,
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
                            
                            pickupMarker{{ $shipment->id }}.addListener('click', () => pickupInfo{{ $shipment->id }}.open(map, pickupMarker{{ $shipment->id }}));
                            shipmentMarkers.push(pickupMarker{{ $shipment->id }});
                            bounds.extend(pickupPos{{ $shipment->id }});
                            // NOTE: Pickups are NOT added to waypoints - only visual markers
                        @endif

                        // Add delivery addresses as waypoints
                        @if($shipment->delivery_latitude && $shipment->delivery_longitude)
                            const deliveryPos{{ $shipment->id }} = { lat: {{ $shipment->delivery_latitude }}, lng: {{ $shipment->delivery_longitude }} };
                            const deliveryStyle{{ $shipment->id }} = routeStyles[currentMapStyle] || routeStyles.uber;
                            
                            const deliveryMarker{{ $shipment->id }} = new google.maps.Marker({
                                position: deliveryPos{{ $shipment->id }},
                                map: map,
                                icon: {
                                    path: google.maps.SymbolPath.CIRCLE,
                                    scale: deliveryStyle{{ $shipment->id }}.markerScale,
                                    fillColor: deliveryStyle{{ $shipment->id }}.deliveryColor,
                                    fillOpacity: 1,
                                    strokeColor: '#FFFFFF',
                                    strokeWeight: deliveryStyle{{ $shipment->id }}.markerStrokeWeight,
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
                            
                            deliveryMarker{{ $shipment->id }}.addListener('click', () => deliveryInfo{{ $shipment->id }}.open(map, deliveryMarker{{ $shipment->id }}));
                            shipmentMarkers.push(deliveryMarker{{ $shipment->id }});
                            bounds.extend(deliveryPos{{ $shipment->id }});
                            // Add delivery address as waypoint
                            route{{ $route->id }}Waypoints.push(deliveryPos{{ $shipment->id }});
                        @endif
                    @endforeach
                    
                    // Draw route path: Depot → Destinations → Depot (return)
                    if (route{{ $route->id }}Waypoints.length > 1) {
                        drawRoutePathCorrect(route{{ $route->id }}Waypoints, '{{ $route->id }}', '{{ $route->name }}');
                    }
                @endif
            @endforeach
            
            // Fit bounds to show all routes
            if (Object.keys(driverMarkers).length === 0 && shipmentMarkers.length > 0) {
                map.fitBounds(bounds);
            }
        @endif
    }

    // Draw route path using Directions API (correct logic: depot as origin and destination)
    function drawRoutePathCorrect(waypoints, routeId, routeName) {
        if (waypoints.length < 2 || typeof google === 'undefined' || typeof google.maps === 'undefined') return;

        // Route style based on current map style
        const style = routeStyles[currentMapStyle] || routeStyles.uber;
        const routeStyle = {
            strokeColor: style.strokeColor,
            strokeOpacity: style.strokeOpacity,
            strokeWeight: style.strokeWeight
        };

        const directionsService = new google.maps.DirectionsService();
        const directionsRenderer = new google.maps.DirectionsRenderer({
            map: map,
            suppressMarkers: true, // We already have custom markers
            polylineOptions: routeStyle
        });

        // Store renderer for cleanup
        if (!window.routeRenderers) {
            window.routeRenderers = [];
        }
        window.routeRenderers.push(directionsRenderer);

        // CRITICAL: waypoints[0] is the origin (depot/branch)
        // waypoints[1] to waypoints[n] are delivery destinations
        // Destination MUST ALWAYS be depot/branch (waypoints[0]) - return to origin
        const waypointsArray = waypoints.length > 1 
            ? waypoints.slice(1).map(wp => ({
                location: { lat: wp.lat, lng: wp.lng },
                stopover: true
            }))
            : [];

        // Origin is ALWAYS the depot/branch (waypoints[0])
        const origin = { lat: waypoints[0].lat, lng: waypoints[0].lng };
        // Destination is ALWAYS the depot/branch (return to origin)
        const destination = { lat: waypoints[0].lat, lng: waypoints[0].lng };

        const request = {
            origin: origin,
            destination: destination,
            waypoints: waypointsArray.length > 0 ? waypointsArray : undefined,
            travelMode: google.maps.TravelMode.DRIVING,
            unitSystem: google.maps.UnitSystem.METRIC,
            language: 'pt-BR',
            optimizeWaypoints: false // Keep order as specified
        };

        directionsService.route(request, function(result, status) {
            if (status === 'OK') {
                directionsRenderer.setDirections(result);
                
                const route = result.routes[0];
                
                // Extract route path for distance checking
                const path = [];
                route.legs.forEach(leg => {
                    leg.steps.forEach(step => {
                        step.path.forEach(point => {
                            path.push({
                                lat: point.lat(),
                                lng: point.lng()
                            });
                        });
                    });
                });
                
                // Store route path for driver trail checking
                routePaths[routeId] = path;
                
                route.overview_path.forEach(path => {
                    bounds.extend(path);
                });
            } else {
                console.error('Directions request failed for route ' + routeId + ':', status);
            }
        });
    }

    // Focus on specific route
    function focusRoute(routeId) {
        if (!map) return;
        
        const bounds = new google.maps.LatLngBounds();
        let hasRoute = false;
        
        @foreach($activeRoutes as $route)
            if ({{ $route->id }} === routeId) {
                @foreach($route->shipments as $shipment)
                    @if($shipment->pickup_latitude && $shipment->pickup_longitude)
                        bounds.extend({ lat: {{ $shipment->pickup_latitude }}, lng: {{ $shipment->pickup_longitude }} });
                        hasRoute = true;
                    @endif
                    @if($shipment->delivery_latitude && $shipment->delivery_longitude)
                        bounds.extend({ lat: {{ $shipment->delivery_latitude }}, lng: {{ $shipment->delivery_longitude }} });
                        hasRoute = true;
                    @endif
                @endforeach
            }
        @endforeach
        
        if (hasRoute) {
            map.fitBounds(bounds);
            map.setZoom(Math.min(map.getZoom(), 12));
        }
    }

    // Focus on specific driver
    function focusDriver(driverId) {
        const marker = driverMarkers[driverId];
        if (marker) {
            map.setCenter(marker.getPosition());
            map.setZoom(15);
            marker.setAnimation(google.maps.Animation.BOUNCE);
            setTimeout(() => marker.setAnimation(null), 2000);
        }
    }

    // Toggle driver trail visibility
    function toggleDriverTrail(driverId) {
        const trail = driverTrails[driverId];
        const offRouteTrail = driverOffRouteTrails[driverId];
        if (!trail && !offRouteTrail) return;
        
        // Toggle visibility state
        driverTrailVisibility[driverId] = !driverTrailVisibility[driverId];
        
        // Show or hide trails
        if (driverTrailVisibility[driverId]) {
            if (trail) trail.setMap(map);
            if (offRouteTrail) offRouteTrail.setMap(map);
        } else {
            if (trail) trail.setMap(null);
            if (offRouteTrail) offRouteTrail.setMap(null);
        }
        
        // Update button appearance
        const button = document.querySelector(`.toggle-trail-btn[data-driver-id="${driverId}"]`);
        if (button) {
            if (driverTrailVisibility[driverId]) {
                button.style.background = 'rgba(255, 107, 53, 0.2)';
                button.style.borderColor = 'var(--cor-acento)';
                button.style.color = 'var(--cor-acento)';
            } else {
                button.style.background = 'rgba(255, 255, 255, 0.1)';
                button.style.borderColor = 'rgba(255, 255, 255, 0.3)';
                button.style.color = 'rgba(255, 255, 255, 0.5)';
            }
        }
    }

    // Refresh button
    document.getElementById('refresh-locations').addEventListener('click', function() {
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Atualizando...';
        loadDriverLocations();
        setTimeout(() => {
            this.innerHTML = '<i class="fas fa-sync-alt"></i> Atualizar';
        }, 1000);
    });

    // Load Google Maps when page is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadGoogleMaps);
    } else {
        loadGoogleMaps();
    }

    // Auto-refresh every 30 seconds (only after map is initialized)
    let refreshInterval;
    function startAutoRefresh() {
        if (refreshInterval) clearInterval(refreshInterval);
        refreshInterval = setInterval(() => {
            if (map) {
                loadDriverLocations();
            }
        }, 30000);
    }

    // Start auto-refresh after map initialization
    const originalInitMap = window.initMap;
    window.initMap = function() {
        originalInitMap();
        startAutoRefresh();
    };
</script>
@endpush
@endsection







