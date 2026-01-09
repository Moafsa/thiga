/**
 * Driver Route Map - Mapbox Implementation
 * Replaces Google Maps with Mapbox for driver dashboard
 */

let mapHelper;
let driverMarker;
let realtimeTracking;
let deliveryMarkers = [];

// Initialize route map with Mapbox
// NOTE: This function should be called AFTER window.routeShipments, window.routeOriginLat, etc. are defined
async function initRouteMap() {
    // Also expose globally for compatibility
    window.initRouteMapFromDriverScript = initRouteMap;
    const mapContainer = document.getElementById('route-map');
    if (!mapContainer) {
        console.warn('driver-route-map.js: Map container not found');
        return;
    }

    // Check if Mapbox is available
    if (typeof MapboxHelper === 'undefined') {
        mapContainer.innerHTML = '<div style="padding: 20px; text-align: center;"><p>Mapbox não carregado. Verifique a conexão.</p></div>';
        return;
    }

    if (!window.mapboxAccessToken) {
        mapContainer.innerHTML = '<div style="padding: 20px; text-align: center;"><p>Mapbox access token não configurado.</p></div>';
        return;
    }

    // Get driver current location
    const driverLat = window.driverCurrentLat || null;
    const driverLng = window.driverCurrentLng || null;
    
    // Get route origin (depot/branch) - EXACTLY like route-map-mapbox.js
    const routeOriginLat = window.routeOriginLat || null;
    const routeOriginLng = window.routeOriginLng || null;
    
    // Get shipments - EXACTLY like route-map-mapbox.js
    const shipments = window.routeShipments || [];
    
    // Also support deliveryLocations for backward compatibility
    const deliveryLocations = window.deliveryLocations || [];

    // Determine map center
    let center = [-46.6333, -23.5505]; // [lng, lat] - São Paulo default
    
    if (routeOriginLat && routeOriginLng) {
        center = [parseFloat(routeOriginLng), parseFloat(routeOriginLat)];
    } else if (driverLat && driverLng) {
        center = [parseFloat(driverLng), parseFloat(driverLat)];
    } else if (deliveryLocations.length > 0 && deliveryLocations[0].lat && deliveryLocations[0].lng) {
        center = [parseFloat(deliveryLocations[0].lng), parseFloat(deliveryLocations[0].lat)];
    }

    // Get auth token
    const authToken = getAuthToken();

    // Always initialize map - it can show driver location even without route
    // The map will show whatever data is available (driver location, route, shipments, etc.)
    
    // If no data at all, show message but still initialize map (for future location updates)
    const hasAnyData = (routeOriginLat && routeOriginLng) || 
                      (driverLat && driverLng) || 
                      (shipments.length > 0) || 
                      (deliveryLocations.length > 0);
    
    if (!hasAnyData) {
        console.log('No route data available, but initializing map for future location updates');
        // Still initialize map - it will show São Paulo default center
        // When driver location is updated, it will show on the map
    }

    // Initialize Mapbox
    mapHelper = new MapboxHelper('route-map', {
        center: center,
        zoom: hasAnyData ? 12 : 10, // Zoom out more if no data
        accessToken: window.mapboxAccessToken,
        apiBaseUrl: '/api/maps',
        authToken: authToken,
        onLoad: async (map) => {
            console.log('Map loaded');
            await addMarkersAndRoute();
        }
    });

    window.mapHelper = mapHelper; // For compatibility
    window.routeMap = mapHelper; // Also expose as routeMap for compatibility

    async function addMarkersAndRoute() {
        console.log('Adding markers and route...', {
            driverLat,
            driverLng,
            routeOriginLat,
            routeOriginLng,
            shipmentsCount: shipments.length,
            deliveryLocationsCount: deliveryLocations.length
        });

        // If no data at all, show a message overlay but keep map visible
        const hasAnyData = (routeOriginLat && routeOriginLng) || 
                          (driverLat && driverLng) || 
                          (shipments.length > 0) || 
                          (deliveryLocations.length > 0);
        
        if (!hasAnyData) {
            console.log('No route data available. Map is visible but empty.');
            // Don't return - let the map show with default center (São Paulo)
            // This allows the map to be ready when location updates come in
        }

        // Add origin marker (depot/branch) - EXACTLY like route-map-mapbox.js
        if (routeOriginLat && routeOriginLng) {
            mapHelper.addMarker({
                lat: parseFloat(routeOriginLat),
                lng: parseFloat(routeOriginLng)
            }, {
                title: window.routeOriginName || 'Ponto de Partida',
                color: '#FF6B35',
                size: 32,
                content: `<div><strong>${window.routeOriginName || 'Ponto de Partida'}</strong></div>`
            });
        }

        // Add driver location marker
        if (driverLat && driverLng) {
            driverMarker = mapHelper.addMarker({
                lat: parseFloat(driverLat),
                lng: parseFloat(driverLng)
            }, {
                title: 'Sua Localização Atual',
                color: '#2196F3',
                size: 32,
                content: '<p>Motorista</p>'
            });
            window.driverMarker = driverMarker; // Expose globally
        }

        // Add shipment markers - EXACTLY like route-map-mapbox.js
        shipments.forEach((shipment, index) => {
            // Delivery marker
            if (shipment.delivery_lat && shipment.delivery_lng) {
                // Different colors based on status
                let markerColor = '#4CAF50'; // Green for delivered
                if (shipment.status === 'pending' || shipment.status === 'scheduled') {
                    markerColor = '#FFC107'; // Yellow for pending
                } else if (shipment.status === 'picked_up' || shipment.status === 'in_transit') {
                    markerColor = '#2196F3'; // Blue for in transit
                } else if (shipment.status === 'exception') {
                    markerColor = '#F44336'; // Red for exception
                }

                const marker = mapHelper.addMarker({
                    lat: parseFloat(shipment.delivery_lat),
                    lng: parseFloat(shipment.delivery_lng)
                }, {
                    title: `Entrega: ${shipment.tracking_number || shipment.id}`,
                    color: markerColor,
                    size: 28,
                    content: `<div><strong>Entrega</strong><br><small>${shipment.tracking_number || ''}</small></div>`
                });
                deliveryMarkers.push(marker);
            }
        });

        // Calculate and draw route - EXACTLY like route-map-mapbox.js
        if (routeOriginLat && routeOriginLng && shipments.length > 0) {
            const origin = {
                lat: parseFloat(routeOriginLat),
                lng: parseFloat(routeOriginLng)
            };

            // Build waypoints from deliveries - EXACTLY like route-map-mapbox.js
            const deliveryWaypoints = shipments
                .filter(s => s.delivery_lat && s.delivery_lng)
                .map(s => ({
                    lat: parseFloat(s.delivery_lat),
                    lng: parseFloat(s.delivery_lng)
                }));

            if (deliveryWaypoints.length > 0) {
                // All deliveries as waypoints, origin as final destination (return to base)
                const waypoints = deliveryWaypoints; // All deliveries as waypoints
                const returnDestination = origin; // Return to origin

                console.log('Drawing route with return to base:', { origin, destination: returnDestination, waypointsCount: waypoints.length });

                try {
                    await mapHelper.drawRoute(origin, returnDestination, waypoints, {
                        color: '#FF6B35',
                        width: 6,
                        opacity: 0.8
                    });
                    console.log('Route drawn successfully with return to base');
                } catch (error) {
                    console.error('Error drawing route:', error);
                    console.error('Error details:', error.message, error.stack);
                }
            } else {
                console.warn('No valid delivery waypoints found');
            }
        } else {
            console.warn('Cannot draw route - missing data:', {
                hasOrigin: !!(routeOriginLat && routeOriginLng),
                hasShipments: shipments.length > 0
            });
        }

        // Fit bounds to show all markers - EXACTLY like route-map-mapbox.js
        const allPositions = [];
        if (routeOriginLat && routeOriginLng) {
            allPositions.push({ lat: parseFloat(routeOriginLat), lng: parseFloat(routeOriginLng) });
        }
        if (driverLat && driverLng) {
            allPositions.push({ lat: parseFloat(driverLat), lng: parseFloat(driverLng) });
        }
        shipments.forEach(s => {
            if (s.delivery_lat && s.delivery_lng) {
                allPositions.push({ lat: parseFloat(s.delivery_lat), lng: parseFloat(s.delivery_lng) });
            }
        });
        
        if (allPositions.length > 0) {
            mapHelper.fitBounds(allPositions);
        }
    }

    // Initialize real-time tracking
    if (typeof RealTimeTracking !== 'undefined' && window.tenantId && window.driverId) {
        realtimeTracking = new RealTimeTracking({
            tenantId: window.tenantId,
            driverId: window.driverId,
            routeId: window.routeId || null,
            mapHelper: mapHelper,
            onLocationUpdate: (location) => {
                console.log('Location updated:', location);
                
                // Update driver marker
                if (driverMarker) {
                    mapHelper.updateMarker(driverMarker, {
                        lat: location.latitude,
                        lng: location.longitude
                    });
                } else {
                    driverMarker = mapHelper.addMarker({
                        lat: location.latitude,
                        lng: location.longitude
                    }, {
                        title: 'Sua Localização',
                        color: '#2196F3',
                        size: 32
                    });
                }

                // Update UI if needed
                updateLocationDisplay(location);
            }
        });
    }

    window.realtimeTracking = realtimeTracking;
}

// Helper function
function getAuthToken() {
    const metaTag = document.querySelector('meta[name="api-token"]');
    if (metaTag) {
        return metaTag.getAttribute('content');
    }
    return localStorage.getItem('auth_token');
}

function updateLocationDisplay(location) {
    // Update any UI elements that show location info
    const locationElement = document.getElementById('current-location');
    if (locationElement) {
        locationElement.textContent = `${location.latitude.toFixed(6)}, ${location.longitude.toFixed(6)}`;
    }
}

// Auto-update location from browser geolocation
if (navigator.geolocation) {
    navigator.geolocation.watchPosition(async function(position) {
        const routeId = window.routeId || null;
        
                // Update location on server
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            const headers = {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            };
            
            if (csrfToken) {
                headers['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
            }
            
            const authToken = getAuthToken();
            if (authToken) {
                headers['Authorization'] = 'Bearer ' + authToken;
            }
            
            const response = await fetch('/driver/location/update', {
                method: 'POST',
                headers: headers,
                credentials: 'same-origin',
                body: JSON.stringify({
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude,
                    accuracy: position.coords.accuracy,
                    route_id: routeId,
                })
            });

            if (response.ok) {
                const data = await response.json();
                console.log('Location updated on server:', data);
                
                // Update marker immediately for better UX
                if (mapHelper && driverMarker) {
                    mapHelper.updateMarker(driverMarker, {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    });
                } else if (mapHelper) {
                    driverMarker = mapHelper.addMarker({
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    }, {
                        title: 'Sua Localização',
                        color: '#2196F3',
                        size: 32
                    });
                }
            }
        } catch (error) {
            console.error('Error updating location:', error);
        }
    }, function(error) {
        console.error('Geolocation error:', error);
    }, {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 0
    });
}

// Callback function (for compatibility)
window.initRouteMapCallback = function() {
    initRouteMap();
};

// DO NOT auto-initialize here!
// The dashboard.blade.php will call initRouteMap() after defining
// window.routeShipments, window.routeOriginLat, etc.
// Auto-initialization is disabled to prevent race conditions.
