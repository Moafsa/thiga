/**
 * Mapbox Helper - Unified Maps Service
 * Replaces Google Maps with Mapbox GL JS
 * All map operations go through backend API
 */

class MapboxHelper {
    constructor(containerId, options = {}) {
        this.containerId = containerId;
        this.container = document.getElementById(containerId);
        if (!this.container) {
            console.error(`Map container ${containerId} not found`);
            return;
        }

        // Get access token from config
        this.accessToken = window.mapboxAccessToken || options.accessToken;
        if (!this.accessToken) {
            this.container.innerHTML = '<div style="padding: 20px; text-align: center;"><p>Mapbox access token não configurado.</p></div>';
            return;
        }

        mapboxgl.accessToken = this.accessToken;

        // Default options
        this.options = {
            center: options.center || [-46.6333, -23.5505], // [lng, lat] - São Paulo
            zoom: options.zoom || 12,
            style: options.style || 'mapbox://styles/mapbox/streets-v12',
            ...options
        };

        // Ensure container is empty (remove any legacy overlay/messages)
        try {
            this.container.innerHTML = '';
        } catch (e) {
            // ignore
        }

        // Check if map already exists in this container (prevent duplicate initialization)
        if (this.container.querySelector('.mapboxgl-map')) {
            console.warn(`Map already exists in container ${containerId}, skipping initialization`);
            return;
        }
        
        // Initialize map
        this.map = new mapboxgl.Map({
            container: containerId,
            center: this.options.center,
            zoom: this.options.zoom,
            style: this.options.style,
            ...this.options.mapOptions
        });

        // Add navigation controls
        this.map.addControl(new mapboxgl.NavigationControl(), 'top-right');

        // Markers and popups storage
        this.markers = [];
        this.popups = [];
        this.sources = {};
        this.layers = {};

        // Wait for map to load
        this.map.on('load', () => {
            if (options.onLoad) {
                options.onLoad(this.map);
            }
        });

        // API base URL
        this.apiBaseUrl = options.apiBaseUrl || '/api/maps';
        this.authToken = options.authToken || this.getAuthToken();
    }

    /**
     * Get auth token from meta tag or localStorage
     */
    getAuthToken() {
        const metaTag = document.querySelector('meta[name="api-token"]');
        if (metaTag) {
            return metaTag.getAttribute('content');
        }
        return localStorage.getItem('auth_token');
    }

    /**
     * Make authenticated API request
     */
    async apiRequest(endpoint, data) {
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        };

        // Add CSRF token - REQUIRED for web routes
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfToken) {
            headers['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
        }

        // Add headers for web session authentication
        headers['X-Requested-With'] = 'XMLHttpRequest';
        headers['Accept'] = 'application/json';

        // Add credentials to send cookies (session auth)
        // Note: credentials: 'include' must be set in fetch options, not headers

        try {
            const response = await fetch(`${this.apiBaseUrl}${endpoint}`, {
                method: 'POST',
                headers: headers,
                body: JSON.stringify(data),
                credentials: 'same-origin' // Include cookies for session auth
            });

            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.message || 'API request failed');
            }

            return await response.json();
        } catch (error) {
            console.error('MapboxHelper API error:', error);
            throw error;
        }
    }

    /**
     * Geocode an address
     */
    async geocode(address) {
        try {
            const result = await this.apiRequest('/geocode', { address });
            return {
                latitude: result.latitude,
                longitude: result.longitude,
                formatted_address: result.formatted_address
            };
        } catch (error) {
            console.error('Geocoding failed:', error);
            return null;
        }
    }

    /**
     * Reverse geocode coordinates
     */
    async reverseGeocode(latitude, longitude) {
        try {
            const result = await this.apiRequest('/reverse-geocode', { latitude, longitude });
            return result.formatted_address;
        } catch (error) {
            console.error('Reverse geocoding failed:', error);
            return null;
        }
    }

    /**
     * Calculate route
     */
    async calculateRoute(origin, destination, waypoints = []) {
        try {
            const data = {
                origin_latitude: origin.lat,
                origin_longitude: origin.lng,
                destination_latitude: destination.lat,
                destination_longitude: destination.lng
            };

            if (waypoints.length > 0) {
                data.waypoints = waypoints.map(wp => ({
                    latitude: wp.lat,
                    longitude: wp.lng
                }));
            }

            const result = await this.apiRequest('/route', data);
            return result;
        } catch (error) {
            console.error('Route calculation failed:', error);
            return null;
        }
    }

    /**
     * Add marker
     */
    addMarker(position, options = {}) {
        const { lat, lng } = position;
        const el = document.createElement('div');
        el.className = 'custom-marker';
        el.style.width = (options.size || 32) + 'px';
        el.style.height = (options.size || 32) + 'px';
        el.style.borderRadius = '50%';
        el.style.backgroundColor = options.color || '#2196F3';
        el.style.border = '3px solid white';
        el.style.cursor = 'pointer';
        el.style.boxShadow = '0 2px 4px rgba(0,0,0,0.3)';

        if (options.iconUrl) {
            el.style.backgroundImage = `url(${options.iconUrl})`;
            el.style.backgroundSize = 'cover';
            el.style.backgroundPosition = 'center';
        }

        const marker = new mapboxgl.Marker(el)
            .setLngLat([lng, lat])
            .addTo(this.map);

        if (options.title) {
            marker.setPopup(new mapboxgl.Popup({ offset: 25 })
                .setHTML(`<div style="padding: 5px;"><strong>${options.title}</strong>${options.content || ''}</div>`));
        }

        if (options.onClick) {
            marker.getElement().addEventListener('click', () => {
                options.onClick(marker, position);
            });
        }

        this.markers.push(marker);
        return marker;
    }

    /**
     * Update marker position
     */
    updateMarker(marker, position) {
        if (marker && position) {
            marker.setLngLat([position.lng, position.lat]);
        }
    }

    /**
     * Remove marker
     */
    removeMarker(marker) {
        if (marker) {
            marker.remove();
            const index = this.markers.indexOf(marker);
            if (index > -1) {
                this.markers.splice(index, 1);
            }
        }
    }

    /**
     * Clear all markers
     */
    clearMarkers() {
        this.markers.forEach(marker => marker.remove());
        this.markers = [];
    }

    /**
     * Draw route on map
     */
    async drawRoute(origin, destination, waypoints = [], options = {}) {
        let coordinates;
        let routeData = null; // Declare in outer scope
        
        try {
            // Calculate route via backend API
            routeData = await this.calculateRoute(origin, destination, waypoints);
            
            if (routeData && routeData.polyline) {
                // Decode polyline from API response
                coordinates = this.decodePolyline(routeData.polyline);
            }
        } catch (error) {
            console.warn('API route calculation failed, using straight line fallback:', error.message);
        }
        
        // Fallback: draw straight line if API failed
        if (!coordinates || coordinates.length === 0) {
            console.log('Drawing straight line route (API unavailable)');
            coordinates = [[origin.lng, origin.lat]];
            waypoints.forEach(wp => {
                coordinates.push([wp.lng, wp.lat]);
            });
            coordinates.push([destination.lng, destination.lat]);
        }

        const sourceId = options.sourceId || 'route';
        const layerId = options.layerId || 'route-layer';

        // Remove existing route if any
        if (this.map.getLayer(layerId)) {
            this.map.removeLayer(layerId);
        }
        if (this.map.getSource(sourceId)) {
            this.map.removeSource(sourceId);
        }

        // Add route source
        this.map.addSource(sourceId, {
            type: 'geojson',
            data: {
                type: 'Feature',
                properties: {},
                geometry: {
                    type: 'LineString',
                    coordinates: coordinates
                }
            }
        });

        // Add route layer
        this.map.addLayer({
            id: layerId,
            type: 'line',
            source: sourceId,
            layout: {
                'line-join': 'round',
                'line-cap': 'round'
            },
            paint: {
                'line-color': options.color || '#2196F3',
                'line-width': options.width || 6,
                'line-opacity': options.opacity || 0.8
            }
        });

        // Fit bounds to route
        if (options.fitBounds !== false) {
            this.fitBounds([origin, destination, ...waypoints]);
        }

        return {
            routeData,
            sourceId,
            layerId,
            coordinates
        };
    }

    /**
     * Decode polyline
     * Mapbox returns polyline6 (encoded) or coordinates array
     */
    decodePolyline(encoded) {
        try {
            // If it's already an array of coordinates, return it
            if (Array.isArray(encoded)) {
                // Mapbox format: [[lng, lat], [lng, lat], ...]
                return encoded;
            }
            
            // If it's a string, try to decode
            if (typeof encoded === 'string') {
                // Try using @mapbox/polyline if available
                if (typeof polyline !== 'undefined' && polyline.decode) {
                    return polyline.decode(encoded);
                }
                
                // Fallback: Basic polyline decoder (simplified)
                // For production, include polyline.js: https://www.npmjs.com/package/@mapbox/polyline
                return this.decodePolylineSimple(encoded);
            }
            
            console.warn('Invalid polyline format:', encoded);
            return [];
        } catch (error) {
            console.error('Polyline decode error:', error);
            return [];
        }
    }

    /**
     * Simple polyline decoder (basic implementation)
     * For full support, use @mapbox/polyline library
     */
    decodePolylineSimple(encoded) {
        // This is a very basic decoder
        // For production, use the polyline library
        const coordinates = [];
        let index = 0;
        let lat = 0;
        let lng = 0;

        while (index < encoded.length) {
            let shift = 0;
            let result = 0;
            let byte;

            do {
                byte = encoded.charCodeAt(index++) - 63;
                result |= (byte & 0x1f) << shift;
                shift += 5;
            } while (byte >= 0x20);

            const deltaLat = ((result & 1) !== 0) ? ~(result >> 1) : (result >> 1);
            lat += deltaLat;

            shift = 0;
            result = 0;

            do {
                byte = encoded.charCodeAt(index++) - 63;
                result |= (byte & 0x1f) << shift;
                shift += 5;
            } while (byte >= 0x20);

            const deltaLng = ((result & 1) !== 0) ? ~(result >> 1) : (result >> 1);
            lng += deltaLng;

            coordinates.push([lng * 1e-5, lat * 1e-5]);
        }

        return coordinates;
    }

    /**
     * Fit map bounds to coordinates
     */
    fitBounds(positions) {
        if (!positions || positions.length === 0) return;

        const coordinates = positions
            .filter(p => p && p.lat && p.lng)
            .map(p => [p.lng, p.lat]);

        if (coordinates.length === 0) return;

        const bounds = coordinates.reduce((bounds, coord) => {
            return bounds.extend(coord);
        }, new mapboxgl.LngLatBounds(coordinates[0], coordinates[0]));

        this.map.fitBounds(bounds, {
            padding: 50,
            maxZoom: 15
        });
    }

    /**
     * Set center and zoom
     */
    setCenter(lat, lng, zoom = null) {
        this.map.setCenter([lng, lat]);
        if (zoom !== null) {
            this.map.setZoom(zoom);
        }
    }

    /**
     * Get map instance
     */
    getMap() {
        return this.map;
    }

    /**
     * Destroy map
     */
    destroy() {
        this.clearMarkers();
        if (this.map) {
            this.map.remove();
        }
    }
}

// Export for use in modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = MapboxHelper;
}
