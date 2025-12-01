<?php $__env->startSection('title', 'Monitoramento - TMS SaaS'); ?>
<?php $__env->startSection('page-title', 'Monitoramento em Tempo Real'); ?>

<?php $__env->startPush('styles'); ?>
<?php echo $__env->make('shared.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
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
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
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
                <option value="google">Google Maps</option>
                <option value="uber">Modo Uber</option>
            </select>
        </div>
        <div id="monitoring-map"></div>
    </div>

    <div class="monitoring-panel">
        <div class="stats-grid">
            <div class="stat-item">
                <h3><?php echo e($activeDrivers->count()); ?></h3>
                <p>Motoristas Ativos</p>
            </div>
        <div class="stat-item">
            <h3><?php echo e($activeRoutes->count()); ?></h3>
            <p>Rotas Ativas</p>
        </div>
            <div class="stat-item">
                <h3><?php echo e($shipmentsInTransit->count()); ?></h3>
                <p>Em Trânsito</p>
            </div>
            <div class="stat-item">
                <h3><?php echo e($activeRoutes->sum(function($route) { return $route->shipments->count(); })); ?></h3>
                <p>Total de Cargas</p>
            </div>
        </div>

        <h3 style="color: var(--cor-acento); margin-bottom: 15px;">
            <i class="fas fa-users"></i> Motoristas Ativos
        </h3>
        <?php $__empty_1 = true; $__currentLoopData = $activeDrivers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $driver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="driver-card" data-driver-id="<?php echo e($driver->id); ?>" onclick="focusDriver(<?php echo e($driver->id); ?>)">
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <div style="flex: 1;">
                        <h4 style="color: var(--cor-texto-claro); margin: 0 0 5px 0;"><?php echo e($driver->name); ?></h4>
                        <?php if($driver->phone): ?>
                            <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.85em; margin: 0;">
                                <i class="fas fa-phone"></i> <?php echo e($driver->phone); ?>

                            </p>
                        <?php endif; ?>
                        <?php if($driver->routes->first()): ?>
                            <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.85em; margin: 5px 0 0 0;">
                                <i class="fas fa-route"></i> <?php echo e($driver->routes->first()->name); ?>

                            </p>
                        <?php endif; ?>
                    </div>
                    <span class="status-badge" style="background-color: rgba(76, 175, 80, 0.2); color: #4caf50;">
                        <i class="fas fa-circle" style="font-size: 0.7em;"></i> Online
                    </span>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div style="text-align: center; padding: 40px; color: rgba(245, 245, 245, 0.7);">
                <i class="fas fa-user-slash" style="font-size: 3em; margin-bottom: 15px; opacity: 0.3;"></i>
                <p>Nenhum motorista ativo no momento</p>
            </div>
        <?php endif; ?>

        <h3 style="color: var(--cor-acento); margin-top: 30px; margin-bottom: 15px;">
            <i class="fas fa-route"></i> Rotas Ativas
        </h3>
        <?php $__empty_1 = true; $__currentLoopData = $activeRoutes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $route): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="route-card" style="cursor: pointer;" onclick="focusRoute(<?php echo e($route->id); ?>)">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                    <h4 style="color: var(--cor-texto-claro); margin: 0 0 5px 0;"><?php echo e($route->name); ?></h4>
                    <span class="status-badge" style="font-size: 0.75em;">
                        <?php if($route->status === 'in_progress'): ?>
                            Em Andamento
                        <?php else: ?>
                            Agendada
                        <?php endif; ?>
                    </span>
                </div>
                <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin: 5px 0;">
                    <?php if($route->driver): ?>
                        <i class="fas fa-user"></i> <?php echo e($route->driver->name); ?>

                    <?php else: ?>
                        <i class="fas fa-user"></i> Sem motorista
                    <?php endif; ?>
                </p>
                <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin: 5px 0;">
                    <i class="fas fa-box"></i> <?php echo e($route->shipments->count()); ?> <?php echo e($route->shipments->count() == 1 ? 'carga' : 'cargas'); ?>

                </p>
                <?php if($route->started_at): ?>
                <p style="color: rgba(76, 175, 80, 0.8); font-size: 0.85em; margin: 5px 0;">
                    <i class="fas fa-play-circle"></i> Iniciada: <?php echo e($route->started_at->format('d/m/Y H:i')); ?>

                </p>
                <?php endif; ?>
                <?php if($route->completed_at): ?>
                <p style="color: rgba(76, 175, 80, 0.8); font-size: 0.85em; margin: 5px 0;">
                    <i class="fas fa-check-circle"></i> Finalizada: <?php echo e($route->completed_at->format('d/m/Y H:i')); ?>

                </p>
                <?php endif; ?>
                <?php if($route->estimated_distance): ?>
                <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.85em; margin: 5px 0;">
                    <i class="fas fa-route"></i> <?php echo e(number_format($route->estimated_distance, 2, ',', '.')); ?> km
                </p>
                <?php endif; ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div style="text-align: center; padding: 40px; color: rgba(245, 245, 245, 0.7);">
                <i class="fas fa-route" style="font-size: 3em; margin-bottom: 15px; opacity: 0.3;"></i>
                <p>Nenhuma rota ativa no momento</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    let map;
    let driverMarkers = {};
    let routePolylines = {};
    let shipmentMarkers = [];
    let bounds;
    let currentMapStyle = 'google'; // Default to Google Maps style
    
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
        google: [
            {
                featureType: 'poi',
                elementType: 'labels',
                stylers: [{ visibility: 'off' }]
            }
        ]
    };

    // Initialize Google Maps
    function initMap() {
        if (!document.getElementById('monitoring-map')) {
            console.error('Map container not found');
            return;
        }

        // Load saved map style preference or default to 'google'
        currentMapStyle = localStorage.getItem('monitoringMapStyle') || 'google';

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
        
        // Reload routes and markers with new style
        loadRoutesAndShipments();
        loadDriverLocations();
    }

    // Load Google Maps API
    function loadGoogleMaps() {
        const apiKey = '<?php echo e(config("services.google_maps.api_key")); ?>';
        if (!apiKey) {
            console.error('Google Maps API key not configured');
            document.getElementById('monitoring-map').innerHTML = '<div style="padding: 20px; text-align: center; color: #fff;"><p>Google Maps API key not configured. Please set GOOGLE_MAPS_API_KEY in your .env file.</p></div>';
            return;
        }

        const script = document.createElement('script');
        script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&libraries=geometry,places,directions&language=pt-BR&callback=initMap`;
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

    // Load driver locations
    function loadDriverLocations() {
        if (!map) return;
        
        fetch('<?php echo e(route("monitoring.driver-locations")); ?>')
            .then(response => response.json())
            .then(drivers => {
                // Remove old markers
                Object.values(driverMarkers).forEach(marker => marker.setMap(null));
                driverMarkers = {};
                bounds = new google.maps.LatLngBounds();

                if (drivers.length === 0) {
                    return;
                }

                // Add markers for each driver
                drivers.forEach(driver => {
                    if (driver.latitude && driver.longitude) {
                        const position = { lat: driver.latitude, lng: driver.longitude };
                        
                        // Marker style based on current map style
                        const markerStyle = currentMapStyle === 'uber' ? {
                            scale: 12,
                            strokeWeight: 3
                        } : {
                            scale: 10,
                            strokeWeight: 2
                        };

                        const marker = new google.maps.Marker({
                            position: position,
                            map: map,
                            icon: {
                                path: google.maps.SymbolPath.CIRCLE,
                                scale: markerStyle.scale,
                                fillColor: '#FF0000',
                                fillOpacity: 1,
                                strokeColor: '#FFFFFF',
                                strokeWeight: markerStyle.strokeWeight
                            },
                            title: driver.name
                        });

                        let infoContent = `<div style="padding: 10px; min-width: 200px;">
                            <h4 style="margin: 0 0 10px 0; color: var(--cor-acento);">${driver.name}</h4>`;
                        if (driver.phone) {
                            infoContent += `<p style="margin: 5px 0; color: #666;"><i class="fas fa-phone"></i> ${driver.phone}</p>`;
                        }
                        if (driver.active_route) {
                            infoContent += `<p style="margin: 5px 0; color: #666;"><i class="fas fa-route"></i> ${driver.active_route.name}</p>`;
                            infoContent += `<p style="margin: 5px 0; color: #666;"><small>${driver.active_route.shipments_count} ${driver.active_route.shipments_count === 1 ? 'carga' : 'cargas'}</small></p>`;
                        }
                        infoContent += `<p style="margin: 10px 0 0 0; font-size: 0.85em; color: #999;">Última atualização: ${new Date(driver.last_update).toLocaleString('pt-BR')}</p>`;
                        infoContent += `</div>`;

                        const infoWindow = new google.maps.InfoWindow({
                            content: infoContent
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

    // Load routes and shipments with route paths
    function loadRoutesAndShipments() {
        if (!map || !bounds) return;
        
        // Clear existing route renderers
        if (window.routeRenderers) {
            window.routeRenderers.forEach(renderer => renderer.setMap(null));
        }
        window.routeRenderers = [];
        
        <?php if($activeRoutes->count() > 0): ?>
            <?php $__currentLoopData = $activeRoutes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $route): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if($route->shipments->count() > 0): ?>
                    const route<?php echo e($route->id); ?>Waypoints = [];
                    
                    <?php $__currentLoopData = $route->shipments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shipment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($shipment->pickup_latitude && $shipment->pickup_longitude): ?>
                            const pickupPos<?php echo e($shipment->id); ?> = { lat: <?php echo e($shipment->pickup_latitude); ?>, lng: <?php echo e($shipment->pickup_longitude); ?> };
                            // Marker style based on current map style
                            const pickupMarkerStyle<?php echo e($shipment->id); ?> = currentMapStyle === 'uber' ? {
                                scale: 12,
                                fillColor: '#1a73e8',
                                strokeWeight: 3
                            } : {
                                scale: 8,
                                fillColor: '#2196F3',
                                strokeWeight: 2
                            };
                            
                            const pickupMarker<?php echo e($shipment->id); ?> = new google.maps.Marker({
                                position: pickupPos<?php echo e($shipment->id); ?>,
                                map: map,
                                icon: {
                                    path: google.maps.SymbolPath.CIRCLE,
                                    scale: pickupMarkerStyle<?php echo e($shipment->id); ?>.scale,
                                    fillColor: pickupMarkerStyle<?php echo e($shipment->id); ?>.fillColor,
                                    fillOpacity: 1,
                                    strokeColor: '#FFFFFF',
                                    strokeWeight: pickupMarkerStyle<?php echo e($shipment->id); ?>.strokeWeight
                                },
                                title: 'Coleta: <?php echo e($shipment->tracking_number); ?>'
                            });
                            
                            const pickupInfo<?php echo e($shipment->id); ?> = new google.maps.InfoWindow({
                                content: `<div style="padding: 10px; min-width: 200px;">
                                    <h4 style="margin: 0 0 10px 0; color: var(--cor-acento);">Coleta: <?php echo e($shipment->tracking_number); ?></h4>
                                    <p style="margin: 5px 0; color: #666;"><?php echo e($shipment->pickup_address); ?></p>
                                    <p style="margin: 5px 0; color: #666;"><?php echo e($shipment->pickup_city); ?>, <?php echo e($shipment->pickup_state); ?></p>
                                </div>`
                            });
                            
                            pickupMarker<?php echo e($shipment->id); ?>.addListener('click', () => pickupInfo<?php echo e($shipment->id); ?>.open(map, pickupMarker<?php echo e($shipment->id); ?>));
                            shipmentMarkers.push(pickupMarker<?php echo e($shipment->id); ?>);
                            bounds.extend(pickupPos<?php echo e($shipment->id); ?>);
                            route<?php echo e($route->id); ?>Waypoints.push(pickupPos<?php echo e($shipment->id); ?>);
                        <?php endif; ?>

                        <?php if($shipment->delivery_latitude && $shipment->delivery_longitude): ?>
                            const deliveryPos<?php echo e($shipment->id); ?> = { lat: <?php echo e($shipment->delivery_latitude); ?>, lng: <?php echo e($shipment->delivery_longitude); ?> };
                            // Marker style based on current map style
                            const deliveryMarkerStyle<?php echo e($shipment->id); ?> = currentMapStyle === 'uber' ? {
                                scale: 12,
                                fillColor: '#34a853',
                                strokeWeight: 3
                            } : {
                                scale: 8,
                                fillColor: '#4CAF50',
                                strokeWeight: 2
                            };
                            
                            const deliveryMarker<?php echo e($shipment->id); ?> = new google.maps.Marker({
                                position: deliveryPos<?php echo e($shipment->id); ?>,
                                map: map,
                                icon: {
                                    path: google.maps.SymbolPath.CIRCLE,
                                    scale: deliveryMarkerStyle<?php echo e($shipment->id); ?>.scale,
                                    fillColor: deliveryMarkerStyle<?php echo e($shipment->id); ?>.fillColor,
                                    fillOpacity: 1,
                                    strokeColor: '#FFFFFF',
                                    strokeWeight: deliveryMarkerStyle<?php echo e($shipment->id); ?>.strokeWeight
                                },
                                title: 'Entrega: <?php echo e($shipment->tracking_number); ?>'
                            });
                            
                            const deliveryInfo<?php echo e($shipment->id); ?> = new google.maps.InfoWindow({
                                content: `<div style="padding: 10px; min-width: 200px;">
                                    <h4 style="margin: 0 0 10px 0; color: var(--cor-acento);">Entrega: <?php echo e($shipment->tracking_number); ?></h4>
                                    <p style="margin: 5px 0; color: #666;"><?php echo e($shipment->delivery_address); ?></p>
                                    <p style="margin: 5px 0; color: #666;"><?php echo e($shipment->delivery_city); ?>, <?php echo e($shipment->delivery_state); ?></p>
                                </div>`
                            });
                            
                            deliveryMarker<?php echo e($shipment->id); ?>.addListener('click', () => deliveryInfo<?php echo e($shipment->id); ?>.open(map, deliveryMarker<?php echo e($shipment->id); ?>));
                            shipmentMarkers.push(deliveryMarker<?php echo e($shipment->id); ?>);
                            bounds.extend(deliveryPos<?php echo e($shipment->id); ?>);
                            route<?php echo e($route->id); ?>Waypoints.push(deliveryPos<?php echo e($shipment->id); ?>);
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    
                    // Draw route path using Directions API
                    if (route<?php echo e($route->id); ?>Waypoints.length > 1) {
                        drawRoutePath(route<?php echo e($route->id); ?>Waypoints, '<?php echo e($route->id); ?>', '<?php echo e($route->name); ?>');
                    }
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            
            // Fit bounds to show all routes
            if (Object.keys(driverMarkers).length === 0 && shipmentMarkers.length > 0) {
                map.fitBounds(bounds);
            }
        <?php endif; ?>
    }

    // Draw route path using Directions API
    function drawRoutePath(waypoints, routeId, routeName) {
        if (waypoints.length < 2 || typeof google === 'undefined' || typeof google.maps === 'undefined') return;

        // Route style based on current map style
        const routeStyle = currentMapStyle === 'uber' ? {
            strokeColor: '#1a73e8',
            strokeOpacity: 1.0,
            strokeWeight: 6
        } : {
            strokeColor: '#FF6B35',
            strokeOpacity: 0.7,
            strokeWeight: 4
        };

        const directionsService = new google.maps.DirectionsService();
        const directionsRenderer = new google.maps.DirectionsRenderer({
            map: map,
            suppressMarkers: true,
            polylineOptions: routeStyle
        });

        // Store renderer for cleanup
        if (!window.routeRenderers) {
            window.routeRenderers = [];
        }
        window.routeRenderers.push(directionsRenderer);

        const waypointsArray = waypoints.slice(1, -1).map(wp => ({
            location: { lat: wp.lat, lng: wp.lng },
            stopover: true
        }));

        const request = {
            origin: { lat: waypoints[0].lat, lng: waypoints[0].lng },
            destination: { lat: waypoints[waypoints.length - 1].lat, lng: waypoints[waypoints.length - 1].lng },
            waypoints: waypointsArray.length > 0 ? waypointsArray : undefined,
            travelMode: google.maps.TravelMode.DRIVING,
            unitSystem: google.maps.UnitSystem.METRIC,
            language: 'pt-BR'
        };

        directionsService.route(request, function(result, status) {
            if (status === 'OK') {
                directionsRenderer.setDirections(result);
                
                const route = result.routes[0];
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
        
        <?php $__currentLoopData = $activeRoutes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $route): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            if (<?php echo e($route->id); ?> === routeId) {
                <?php $__currentLoopData = $route->shipments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shipment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if($shipment->pickup_latitude && $shipment->pickup_longitude): ?>
                        bounds.extend({ lat: <?php echo e($shipment->pickup_latitude); ?>, lng: <?php echo e($shipment->pickup_longitude); ?> });
                        hasRoute = true;
                    <?php endif; ?>
                    <?php if($shipment->delivery_latitude && $shipment->delivery_longitude): ?>
                        bounds.extend({ lat: <?php echo e($shipment->delivery_latitude); ?>, lng: <?php echo e($shipment->delivery_longitude); ?> });
                        hasRoute = true;
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            }
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        
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
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>








<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/resources/views/monitoring/index.blade.php ENDPATH**/ ?>