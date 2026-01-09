/**
 * Route Map with Mapbox - For routes/show.blade.php
 */

let routeMapHelper;
let routeMarkers = [];
let routePolyline = null;

// Initialize route map with Mapbox
async function initRouteMap() {
    const mapContainer = document.getElementById('route-map');
    if (!mapContainer) return;

    // Check if Mapbox is available
    if (typeof MapboxHelper === 'undefined') {
        mapContainer.innerHTML = '<div style="padding: 20px; text-align: center;"><p>Mapbox não carregado. Recarregue a página.</p></div>';
        return;
    }

    if (!window.mapboxAccessToken) {
        mapContainer.innerHTML = '<div style="padding: 20px; text-align: center;"><p>Mapbox access token não configurado.</p></div>';
        return;
    }

    // Get route data from global variables
    const routeOriginLat = window.routeOriginLat || null;
    const routeOriginLng = window.routeOriginLng || null;
    const shipments = window.routeShipments || [];
    const routeId = window.routeId || null;

    // Determine map center
    let center = [-46.6333, -23.5505]; // [lng, lat] - São Paulo default
    
    if (routeOriginLat && routeOriginLng) {
        center = [parseFloat(routeOriginLng), parseFloat(routeOriginLat)];
    } else if (shipments.length > 0 && shipments[0].delivery_lat && shipments[0].delivery_lng) {
        center = [parseFloat(shipments[0].delivery_lng), parseFloat(shipments[0].delivery_lat)];
    }

    // Get auth token
    const authToken = getAuthToken();

    // Initialize Mapbox
    routeMapHelper = new MapboxHelper('route-map', {
        center: center,
        zoom: 12,
        accessToken: window.mapboxAccessToken,
        apiBaseUrl: '/api/maps',
        authToken: authToken,
        onLoad: async (map) => {
            console.log('Route map loaded');
            await addMarkersAndRoute();
        }
    });

    window.routeMapHelper = routeMapHelper;

    async function addMarkersAndRoute() {
        // Add origin marker (depot/branch)
        if (routeOriginLat && routeOriginLng) {
            routeMapHelper.addMarker({
                lat: parseFloat(routeOriginLat),
                lng: parseFloat(routeOriginLng)
            }, {
                title: window.routeOriginName || 'Ponto de Partida',
                color: '#FF6B35',
                size: 32,
                content: `<div><strong>${window.routeOriginName || 'Ponto de Partida'}</strong></div>`
            });
        }

        // Add shipment markers (pickup and delivery)
        shipments.forEach((shipment, index) => {
            // Pickup marker
            if (shipment.pickup_lat && shipment.pickup_lng) {
                routeMapHelper.addMarker({
                    lat: parseFloat(shipment.pickup_lat),
                    lng: parseFloat(shipment.pickup_lng)
                }, {
                    title: `Coleta: ${shipment.tracking_number || shipment.id}`,
                    color: '#2196F3',
                    size: 24,
                    content: `<div><strong>Coleta</strong><br><small>${shipment.tracking_number || ''}</small></div>`
                });
            }

            // Delivery marker
            if (shipment.delivery_lat && shipment.delivery_lng) {
                routeMapHelper.addMarker({
                    lat: parseFloat(shipment.delivery_lat),
                    lng: parseFloat(shipment.delivery_lng)
                }, {
                    title: `Entrega: ${shipment.tracking_number || shipment.id}`,
                    color: '#4CAF50',
                    size: 28,
                    content: `<div><strong>Entrega</strong><br><small>${shipment.tracking_number || ''}</small></div>`
                });
            }
        });

        // Calculate and draw route
        if (routeOriginLat && routeOriginLng && shipments.length > 0) {
            const origin = {
                lat: parseFloat(routeOriginLat),
                lng: parseFloat(routeOriginLng)
            };

            // Build waypoints from deliveries
            const deliveryWaypoints = shipments
                .filter(s => s.delivery_lat && s.delivery_lng)
                .map(s => ({
                    lat: parseFloat(s.delivery_lat),
                    lng: parseFloat(s.delivery_lng)
                }));

            if (deliveryWaypoints.length > 0) {
                // Last delivery as destination, others as waypoints
                const destination = deliveryWaypoints[deliveryWaypoints.length - 1];
                const waypoints = deliveryWaypoints.slice(0, -1);

                try {
                    const routeData = await routeMapHelper.drawRoute(origin, destination, waypoints, {
                        color: '#FF6B35',
                        width: 6,
                        opacity: 0.8
                    });
                    routePolyline = routeData;
                } catch (error) {
                    console.error('Error drawing route:', error);
                }
            }
        }

        // Fit bounds to show all markers
        const allPositions = [];
        if (routeOriginLat && routeOriginLng) {
            allPositions.push({ lat: parseFloat(routeOriginLat), lng: parseFloat(routeOriginLng) });
        }
        shipments.forEach(s => {
            if (s.pickup_lat && s.pickup_lng) {
                allPositions.push({ lat: parseFloat(s.pickup_lat), lng: parseFloat(s.pickup_lng) });
            }
            if (s.delivery_lat && s.delivery_lng) {
                allPositions.push({ lat: parseFloat(s.delivery_lat), lng: parseFloat(s.delivery_lng) });
            }
        });
        
        if (allPositions.length > 0) {
            routeMapHelper.fitBounds(allPositions);
        }
    }
}

// Helper function
function getAuthToken() {
    const metaTag = document.querySelector('meta[name="api-token"]');
    if (metaTag) {
        return metaTag.getAttribute('content');
    }
    return localStorage.getItem('auth_token');
}

// Callback for compatibility
window.initRouteMapCallback = function() {
    initRouteMap();
};

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    if (typeof MapboxHelper !== 'undefined') {
        initRouteMap();
    } else {
        setTimeout(() => {
            if (typeof MapboxHelper !== 'undefined') {
                initRouteMap();
            } else {
                console.error('MapboxHelper not loaded');
            }
        }, 500);
    }
});
