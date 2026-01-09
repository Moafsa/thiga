/**
 * Monitoring Dashboard with Mapbox
 * Complete migration from Google Maps to Mapbox
 */

let monitoringMap = null;
let driverMarkers = {};
let routePolylines = [];
let shipmentMarkers = [];

// Initialize monitoring map with Mapbox
async function initMonitoringMapbox() {
    // Wait a bit for DOM to be ready
    const mapContainer = document.getElementById('monitoring-map');
    if (!mapContainer) {
        console.error('Map container #monitoring-map not found');
        // Retry after a short delay
        setTimeout(() => {
            const retryContainer = document.getElementById('monitoring-map');
            if (retryContainer) {
                console.log('Map container found on retry, initializing...');
                initMonitoringMapbox();
            } else {
                console.error('Map container still not found after retry');
            }
        }, 500);
        return;
    }

    if (typeof mapboxgl === 'undefined') {
        console.error('Mapbox GL JS not loaded');
        mapContainer.innerHTML = '<div style="padding: 20px; text-align: center; color: #fff;"><p>⚠️ Mapbox GL JS não carregado</p><p style="font-size: 0.9em; opacity: 0.8;">Aguarde o carregamento ou recarregue a página.</p></div>';
        return null;
    }

    if (typeof MapboxHelper === 'undefined') {
        console.error('MapboxHelper not loaded');
        mapContainer.innerHTML = '<div style="padding: 20px; text-align: center; color: #fff;"><p>⚠️ MapboxHelper não carregado</p><p style="font-size: 0.9em; opacity: 0.8;">Aguarde o carregamento ou recarregue a página.</p></div>';
        return null;
    }

    if (!window.mapboxAccessToken) {
        console.error('Mapbox access token not configured');
        mapContainer.innerHTML = '<div style="padding: 20px; text-align: center; color: #fff;"><p>⚠️ Mapbox access token não configurado</p><p style="font-size: 0.9em; opacity: 0.8;">Verifique a configuração no servidor.</p></div>';
        return null;
    }

    const authToken = document.querySelector('meta[name="api-token"]')?.content || localStorage.getItem('auth_token');

    // Initialize map centered on São Paulo
    monitoringMap = new MapboxHelper('monitoring-map', {
        center: [-46.6333, -23.5505], // [lng, lat]
        zoom: 10,
        accessToken: window.mapboxAccessToken,
        apiBaseUrl: '/api/maps',
        authToken: authToken,
        onLoad: async (map) => {
            console.log('Monitoring map loaded');
            await loadRoutesAndShipmentsMapbox();
            setTimeout(() => {
                loadDriverLocationsMapbox();
            }, 1000);
        }
    });

    window.monitoringMap = monitoringMap;
}

// Load driver locations with Mapbox
async function loadDriverLocationsMapbox() {
    if (!monitoringMap) {
        console.warn('Map not initialized');
        return;
    }

    try {
        const response = await fetch('/monitoring/driver-locations');
        const drivers = await response.json();

        // Remove markers for inactive drivers
        const currentDriverIds = new Set(drivers.map(d => d.id));
        Object.keys(driverMarkers).forEach(driverId => {
            if (!currentDriverIds.has(parseInt(driverId))) {
                if (driverMarkers[driverId]) {
                    monitoringMap.removeMarker(driverMarkers[driverId]);
                    delete driverMarkers[driverId];
                }
            }
        });

        // Add or update markers for active drivers
        for (const driver of drivers) {
            const lat = driver.latitude || driver.current_latitude;
            const lng = driver.longitude || driver.current_longitude;
            
            if (!lat || !lng) continue;

            const position = {
                lat: parseFloat(lat),
                lng: parseFloat(lng)
            };

            const driverName = driver.name || driver.user?.name || 'Motorista';
            const driverInfo = `
                <div style="padding: 10px; min-width: 200px;">
                    <h4 style="margin: 0 0 10px 0; color: #FF6B35;">${driverName}</h4>
                    <p style="margin: 5px 0; color: #666;"><strong>Status:</strong> <span style="color: #4CAF50;">Online</span></p>
                    ${driver.phone || driver.user?.phone ? `<p style="margin: 5px 0; color: #666;"><strong>Telefone:</strong> ${driver.phone || driver.user.phone}</p>` : ''}
                    ${driver.current_location ? `<p style="margin: 5px 0; color: #666;"><strong>Local:</strong> ${driver.current_location}</p>` : ''}
                    <p style="margin: 5px 0; color: #666; font-size: 0.9em;">Atualizado: agora</p>
                </div>
            `;

            if (driverMarkers[driver.id]) {
                // Remove old marker and create new one (Mapbox markers are immutable)
                monitoringMap.removeMarker(driverMarkers[driver.id]);
            }
            
            // Create new marker
            driverMarkers[driver.id] = monitoringMap.addMarker(position, {
                title: driverName,
                color: '#FF0000',
                size: 32,
                content: driverInfo
            });
        }

        // Fit bounds will be called after routes are loaded
    } catch (error) {
        console.error('Error loading driver locations:', error);
    }
}

// Load routes and shipments with Mapbox
async function loadRoutesAndShipmentsMapbox() {
    if (!monitoringMap) {
        console.warn('Map not initialized');
        return;
    }

    try {
        // Get routes and shipments from server (passed from Blade)
        const routes = window.monitoringRoutes || [];
        
        if (!routes || routes.length === 0) {
            console.log('No routes to display');
            return;
        }

        // Clear existing routes and markers
        routePolylines.forEach(polyline => {
            if (polyline.remove) polyline.remove();
        });
        routePolylines = [];

        shipmentMarkers.forEach(marker => {
            monitoringMap.removeMarker(marker);
        });
        shipmentMarkers = [];

        // Draw routes
        for (const route of routes) {
            if (!route.start_latitude || !route.start_longitude) continue;

            const origin = {
                lat: parseFloat(route.start_latitude),
                lng: parseFloat(route.start_longitude)
            };

            // Get delivery locations from shipments
            const deliveries = route.shipments
                ?.filter(s => s.delivery_latitude && s.delivery_longitude)
                .map(s => ({
                    lat: parseFloat(s.delivery_latitude),
                    lng: parseFloat(s.delivery_longitude)
                })) || [];

            if (deliveries.length > 0) {
                // All deliveries as waypoints, origin as final destination (return to base)
                const waypoints = deliveries; // All deliveries as waypoints
                const returnDestination = origin; // Return to origin

                try {
                    const polyline = await monitoringMap.drawRoute(origin, returnDestination, waypoints, {
                        color: '#FF6B35',
                        width: 6,
                        opacity: 0.8
                    });
                    routePolylines.push(polyline);
                } catch (error) {
                    console.error('Error drawing route:', error);
                }
            }

            // Add origin marker
            monitoringMap.addMarker(origin, {
                title: `Rota: ${route.name || route.id}`,
                color: '#9C27B0',
                size: 28
            });

            // Add shipment markers (pickup and delivery)
            if (route.shipments) {
                route.shipments.forEach(shipment => {
                    // Pickup marker
                    if (shipment.pickup_latitude && shipment.pickup_longitude) {
                        shipmentMarkers.push(
                            monitoringMap.addMarker({
                                lat: parseFloat(shipment.pickup_latitude),
                                lng: parseFloat(shipment.pickup_longitude)
                            }, {
                                title: `Coleta: ${shipment.tracking_number}`,
                                color: '#2196F3',
                                size: 24
                            })
                        );
                    }

                    // Delivery marker
                    if (shipment.delivery_latitude && shipment.delivery_longitude) {
                        shipmentMarkers.push(
                            monitoringMap.addMarker({
                                lat: parseFloat(shipment.delivery_latitude),
                                lng: parseFloat(shipment.delivery_longitude)
                            }, {
                                title: `Entrega: ${shipment.tracking_number}`,
                                color: '#4CAF50',
                                size: 28
                            })
                        );
                    }
                });
            }
        }

        // Fit bounds to show all routes
        const allPositions = [];
        routes.forEach(route => {
            if (route.start_latitude && route.start_longitude) {
                allPositions.push({
                    lat: parseFloat(route.start_latitude),
                    lng: parseFloat(route.start_longitude)
                });
            }
            if (route.shipments) {
                route.shipments.forEach(s => {
                    if (s.pickup_latitude && s.pickup_longitude) {
                        allPositions.push({
                            lat: parseFloat(s.pickup_latitude),
                            lng: parseFloat(s.pickup_longitude)
                        });
                    }
                    if (s.delivery_latitude && s.delivery_longitude) {
                        allPositions.push({
                            lat: parseFloat(s.delivery_latitude),
                            lng: parseFloat(s.delivery_longitude)
                        });
                    }
                });
            }
        });

        if (allPositions.length > 0) {
            monitoringMap.fitBounds(allPositions);
        }
    } catch (error) {
        console.error('Error loading routes and shipments:', error);
    }
}

// Auto-refresh driver locations
let monitoringRefreshInterval = null;

function startMonitoringAutoRefresh() {
    if (monitoringRefreshInterval) {
        clearInterval(monitoringRefreshInterval);
    }

    monitoringRefreshInterval = setInterval(() => {
        loadDriverLocationsMapbox();
    }, 30000); // Every 30 seconds
}

function stopMonitoringAutoRefresh() {
    if (monitoringRefreshInterval) {
        clearInterval(monitoringRefreshInterval);
    }
}

// Initialize when page loads
function tryInitMonitoringMap() {
    // Check if map container exists FIRST - only initialize on monitoring page
    const mapContainer = document.getElementById('monitoring-map');
    if (!mapContainer) {
        return false; // Container not ready yet or not on monitoring page
    }
    
    // Only initialize on monitoring page (but allow if container exists, might be loaded dynamically)
    // Commented out strict check - let it try if container exists
    // if (!window.location.pathname.includes('/monitoring')) {
    //     return false;
    // }
    
    // Check all requirements
    const mapboxGLReady = typeof mapboxgl !== 'undefined';
    const helperReady = typeof MapboxHelper !== 'undefined';
    const tokenReady = window.mapboxAccessToken && window.mapboxAccessToken.length > 0;
    
    if (!mapboxGLReady) {
        console.log('Waiting for mapboxgl...');
        return false;
    }
    
    if (!helperReady) {
        console.log('Waiting for MapboxHelper...');
        return false;
    }
    
    if (!tokenReady) {
        console.log('Waiting for mapboxAccessToken...', window.mapboxAccessToken);
        return false;
    }
    
    console.log('✅ All Mapbox dependencies ready. Initializing map...');
    initMonitoringMapbox();
    startMonitoringAutoRefresh();
    return true;
}

// Only run on monitoring page
const isMonitoringPage = window.location.pathname.includes('/monitoring');
if (!isMonitoringPage) {
    // Not on monitoring page, skip initialization
    console.log('monitoring-mapbox.js: Not on monitoring page, skipping initialization');
} else {
    // Wait for DOM to be fully ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            // Try immediately
            if (tryInitMonitoringMap()) {
                return;
            }
    
        // If not ready, wait a bit and try again
        let attempts = 0;
        const maxAttempts = 20;
        const checkInterval = setInterval(() => {
            attempts++;
            if (tryInitMonitoringMap()) {
                clearInterval(checkInterval);
                return;
            }
            
    if (attempts >= maxAttempts) {
        clearInterval(checkInterval);
        console.error('MapboxHelper or access token not available after', maxAttempts, 'attempts');
        console.error('Debug info:', {
            mapboxgl: typeof mapboxgl !== 'undefined',
            MapboxHelper: typeof MapboxHelper !== 'undefined',
            mapboxAccessToken: window.mapboxAccessToken ? 'SET (' + window.mapboxAccessToken.substring(0, 20) + '...)' : 'NOT SET',
            container: document.getElementById('monitoring-map') ? 'FOUND' : 'NOT FOUND',
            pathname: window.location.pathname
        });
        const mapContainer = document.getElementById('monitoring-map');
        if (mapContainer) {
            mapContainer.innerHTML = '<div style="padding: 20px; text-align: center; color: #fff;"><p>⚠️ Erro ao carregar mapa</p><p style="font-size: 0.9em; opacity: 0.8;">Mapbox não está disponível. Recarregue a página.</p></div>';
        }
    }
            }, 200);
        });
    } else {
        // DOM already loaded
        (function() {
            if (tryInitMonitoringMap()) {
                return;
            }
            
            let attempts = 0;
            const maxAttempts = 20;
            const checkInterval = setInterval(() => {
                attempts++;
                if (tryInitMonitoringMap()) {
                    clearInterval(checkInterval);
                    return;
                }
                
                if (attempts >= maxAttempts) {
                    clearInterval(checkInterval);
                    console.error('Failed to initialize monitoring map after', maxAttempts, 'attempts');
                    console.error('Debug info:', {
                        mapboxgl: typeof mapboxgl !== 'undefined',
                        MapboxHelper: typeof MapboxHelper !== 'undefined',
                        mapboxAccessToken: window.mapboxAccessToken ? 'SET' : 'NOT SET',
                        container: document.getElementById('monitoring-map') ? 'FOUND' : 'NOT FOUND'
                    });
                }
            }, 200);
        })();
    }
}

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    stopMonitoringAutoRefresh();
});

// Export functions for manual refresh
window.refreshMonitoringMap = function() {
    loadRoutesAndShipmentsMapbox();
    loadDriverLocationsMapbox();
};
