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
            pitch: options.pitch !== undefined ? options.pitch : 60, // default 3D tilt!
            bearing: options.bearing !== undefined ? options.bearing : -15, // default rotation/bearing!
            ...options
        };

        // Inject custom styles if not already present
        if (!document.getElementById('mapbox-helper-custom-styles')) {
            const style = document.createElement('style');
            style.id = 'mapbox-helper-custom-styles';
            style.innerHTML = `
                .fullscreen-mode {
                    position: fixed !important;
                    top: 0 !important;
                    left: 0 !important;
                    width: 100vw !important;
                    height: 100vh !important;
                    z-index: 99999 !important;
                    border-radius: 0 !important;
                    margin: 0 !important;
                    padding: 0 !important;
                }
                .fullscreen-mode #monitoring-map,
                .fullscreen-mode #route-map,
                .fullscreen-mode .mapboxgl-map {
                    height: 100% !important;
                    width: 100% !important;
                }
                .custom-fullscreen-control button {
                    background-color: transparent !important;
                }
                .custom-fullscreen-control button:hover i {
                    color: #FF6B35 !important;
                }
            `;
            document.head.appendChild(style);
        }

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
            pitch: this.options.pitch,
            bearing: this.options.bearing,
            ...this.options.mapOptions
        });

        // Add navigation controls
        this.map.addControl(new mapboxgl.NavigationControl(), 'top-right');

        // Add Custom Resilient Fullscreen control (expand map)
        this.map.addControl(new CustomFullscreenControl(this), 'top-right');

        // Add Style Switcher Control
        this.map.addControl(new StyleSwitcherControl(this), 'top-right');

        // Markers and popups storage
        this.markers = [];
        this.popups = [];
        this.sources = {};
        this.layers = {};

        // Wait for map to load
        this.map.on('load', () => {
            this.enable3DBuildings();
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
        const sourceData = {
            type: 'geojson',
            data: {
                type: 'Feature',
                properties: {},
                geometry: {
                    type: 'LineString',
                    coordinates: coordinates
                }
            }
        };
        this.map.addSource(sourceId, sourceData);
        this.sources[sourceId] = sourceData;

        // Add route layer
        const layerData = {
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
        };
        this.map.addLayer(layerData);
        this.layers[layerId] = layerData;

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
     * Draw arbitrary polyline (like a history path) directly without routing API
     */
    drawPolyline(path, options = {}) {
        if (!path || path.length < 2) return null;

        const coordinates = path.map(p => [(p.lng || p.longitude), (p.lat || p.latitude)]);
        const sourceId = options.sourceId || 'custom-polyline';
        const layerId = options.layerId || 'custom-polyline-layer';

        // Remove existing route if any
        if (this.map.getLayer(layerId)) {
            this.map.removeLayer(layerId);
        }
        if (this.map.getSource(sourceId)) {
            this.map.removeSource(sourceId);
        }

        const sourceData = {
            type: 'geojson',
            data: {
                type: 'Feature',
                properties: {},
                geometry: {
                    type: 'LineString',
                    coordinates: coordinates
                }
            }
        };
        this.map.addSource(sourceId, sourceData);
        this.sources[sourceId] = sourceData;

        const layerData = {
            id: layerId,
            type: 'line',
            source: sourceId,
            layout: {
                'line-join': 'round',
                'line-cap': 'round'
            },
            paint: {
                'line-color': options.color || '#2196F3',
                'line-width': options.width || 4,
                'line-opacity': options.opacity || 0.8
            }
        };
        this.map.addLayer(layerData);
        this.layers[layerId] = layerData;

        return { sourceId, layerId };
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

        // Get current pitch/bearing or fallback to defaults
        const targetPitch = this.map ? this.map.getPitch() || this.options.pitch || 60 : 60;
        const targetBearing = this.map ? this.map.getBearing() || this.options.bearing || -15 : -15;

        this.map.fitBounds(bounds, {
            padding: 50,
            maxZoom: 16,
            pitch: targetPitch,
            bearing: targetBearing
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
     * Change map style dynamically
     */
    setStyle(styleName) {
        const styles = {
            'streets': 'mapbox://styles/mapbox/streets-v12',
            'dark': 'mapbox://styles/mapbox/dark-v11',
            'light': 'mapbox://styles/mapbox/light-v11',
            'satellite': 'mapbox://styles/mapbox/satellite-streets-v12',
            'outdoors': 'mapbox://styles/mapbox/outdoors-v12',
            'navigation': 'mapbox://styles/mapbox/navigation-day-v1'
        };

        const styleUrl = styles[styleName] || styleName;
        if (this.map) {
            // Save current pitch and bearing
            const currentPitch = this.map.getPitch();
            const currentBearing = this.map.getBearing();

            this.map.setStyle(styleUrl);
            
            // Re-draw layers if necessary (Mapbox removes custom layers/sources on style changes)
            this.map.once('style.load', () => {
                this.recreateCustomLayers();
                this.enable3DBuildings();
                
                // Restore pitch and bearing
                this.map.setPitch(currentPitch);
                this.map.setBearing(currentBearing);
            });
        }
    }

    /**
     * Re-add stored custom sources and layers
     */
    recreateCustomLayers() {
        for (const [sourceId, sourceData] of Object.entries(this.sources)) {
            if (!this.map.getSource(sourceId)) {
                this.map.addSource(sourceId, sourceData);
            }
        }
        for (const [layerId, layerData] of Object.entries(this.layers)) {
            if (!this.map.getLayer(layerId)) {
                this.map.addLayer(layerData);
            }
        }
    }

    /**
     * Enable 3D buildings extrusion
     */
    enable3DBuildings() {
        if (!this.map) return;
        
        const add3D = () => {
            const layers = this.map.getStyle().layers;
            const labelLayerId = layers.find(
                (layer) => layer.type === 'symbol' && layer.layout['text-field']
            )?.id;

            if (this.map.getLayer('3d-buildings')) {
                this.map.removeLayer('3d-buildings');
            }

            this.map.addLayer(
                {
                    'id': '3d-buildings',
                    'source': 'composite',
                    'source-layer': 'building',
                    'filter': ['==', 'extrude', 'true'],
                    'type': 'fill-extrusion',
                    'minzoom': 13,
                    'paint': {
                        'fill-extrusion-color': '#aaa',
                        'fill-extrusion-height': [
                            'interpolate',
                            ['linear'],
                            ['zoom'],
                            13,
                            0,
                            13.05,
                            ['get', 'height']
                        ],
                        'fill-extrusion-base': [
                            'interpolate',
                            ['linear'],
                            ['zoom'],
                            13,
                            0,
                            13.05,
                            ['get', 'min_height']
                        ],
                        'fill-extrusion-opacity': 0.6
                    }
                },
                labelLayerId
            );
        };

        if (this.map.isStyleLoaded()) {
            add3D();
        } else {
            this.map.once('style.load', add3D);
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

class StyleSwitcherControl {
    constructor(helper) {
        this.helper = helper;
    }

    onAdd(map) {
        this.map = map;
        this.container = document.createElement('div');
        this.container.className = 'mapboxgl-ctrl mapboxgl-ctrl-group style-switcher-control';
        this.container.style.padding = '5px';
        this.container.style.backgroundColor = '#1e293b'; // slate-dark style
        this.container.style.borderRadius = '8px';
        this.container.style.border = '1px solid rgba(255, 255, 255, 0.15)';
        this.container.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.4)';
        this.container.style.display = 'flex';
        this.container.style.flexDirection = 'column';
        this.container.style.gap = '5px';

        const styleButton = document.createElement('button');
        styleButton.className = 'mapboxgl-ctrl-icon';
        styleButton.style.display = 'flex';
        styleButton.style.alignItems = 'center';
        styleButton.style.justifyContent = 'center';
        styleButton.style.width = '29px';
        styleButton.style.height = '29px';
        styleButton.style.border = 'none';
        styleButton.style.backgroundColor = 'transparent';
        styleButton.style.cursor = 'pointer';
        styleButton.style.color = '#FF6B35'; // orange brand color
        styleButton.innerHTML = '<i class="fas fa-layer-group" style="font-size: 1.1em;"></i>';
        styleButton.title = 'Alterar Estilo do Mapa';

        const styleMenu = document.createElement('div');
        styleMenu.style.display = 'none';
        styleMenu.style.flexDirection = 'column';
        styleMenu.style.gap = '4px';
        styleMenu.style.marginTop = '5px';
        styleMenu.style.width = '130px';

        const addOption = (label, value, iconClass) => {
            const btn = document.createElement('button');
            btn.innerHTML = `<i class="${iconClass}" style="width: 16px; margin-right: 6px;"></i> ${label}`;
            btn.style.width = '100%';
            btn.style.padding = '8px 10px';
            btn.style.fontSize = '0.75em';
            btn.style.textAlign = 'left';
            btn.style.border = 'none';
            btn.style.borderRadius = '4px';
            btn.style.backgroundColor = 'rgba(255,255,255,0.05)';
            btn.style.color = '#fff';
            btn.style.cursor = 'pointer';
            btn.style.transition = 'all 0.2s';
            
            btn.addEventListener('mouseenter', () => { 
                btn.style.backgroundColor = 'rgba(255,255,255,0.15)'; 
                btn.style.color = '#FF6B35';
            });
            btn.addEventListener('mouseleave', () => { 
                btn.style.backgroundColor = 'rgba(255,255,255,0.05)'; 
                btn.style.color = '#fff';
            });
            btn.addEventListener('click', () => {
                this.helper.setStyle(value);
                styleMenu.style.display = 'none';
            });
            styleMenu.appendChild(btn);
        };

        addOption('Ruas (Padrão)', 'streets', 'fas fa-map');
        addOption('Premium Dark', 'dark', 'fas fa-moon');
        addOption('Satélite Real', 'satellite', 'fas fa-globe-americas');
        addOption('Navegação 3D', 'navigation', 'fas fa-navigation');

        styleButton.addEventListener('click', () => {
            styleMenu.style.display = styleMenu.style.display === 'none' ? 'flex' : 'none';
        });

        // Close menu if clicked outside
        document.addEventListener('click', (e) => {
            if (!this.container.contains(e.target)) {
                styleMenu.style.display = 'none';
            }
        });

        this.container.appendChild(styleButton);
        this.container.appendChild(styleMenu);
        return this.container;
    }

    onRemove() {
        this.container.parentNode.removeChild(this.container);
        this.map = undefined;
    }
}

class CustomFullscreenControl {
    constructor(helper) {
        this.helper = helper;
    }

    onAdd(map) {
        this.map = map;
        this.container = document.createElement('div');
        this.container.className = 'mapboxgl-ctrl mapboxgl-ctrl-group custom-fullscreen-control';
        
        const btn = document.createElement('button');
        btn.className = 'mapboxgl-ctrl-icon';
        btn.type = 'button';
        btn.title = 'Tela Cheia';
        btn.style.display = 'flex';
        btn.style.alignItems = 'center';
        btn.style.justifyContent = 'center';
        btn.style.width = '29px';
        btn.style.height = '29px';
        btn.style.border = 'none';
        btn.style.backgroundColor = 'transparent';
        btn.style.cursor = 'pointer';
        btn.innerHTML = '<i class="fas fa-expand" style="font-size: 1.1em; color: #475569;"></i>';

        this.isFullscreen = false;

        btn.addEventListener('click', () => {
            const mapEl = this.helper.container;
            const containerEl = mapEl.closest('.map-container') || mapEl;
            
            if (!this.isFullscreen) {
                containerEl.classList.add('fullscreen-mode');
                btn.innerHTML = '<i class="fas fa-compress" style="font-size: 1.1em; color: #FF6B35;"></i>';
                btn.title = 'Sair da Tela Cheia';
                this.isFullscreen = true;
                
                this.escListener = (e) => {
                    if (e.key === 'Escape') {
                        btn.click();
                    }
                };
                document.addEventListener('keydown', this.escListener);
            } else {
                containerEl.classList.remove('fullscreen-mode');
                btn.innerHTML = '<i class="fas fa-expand" style="font-size: 1.1em; color: #475569;"></i>';
                btn.title = 'Tela Cheia';
                this.isFullscreen = false;
                
                if (this.escListener) {
                    document.removeEventListener('keydown', this.escListener);
                    this.escListener = null;
                }
            }
            
            // Trigger resize event
            setTimeout(() => {
                map.resize();
            }, 100);
            
            // Additional check after transition
            setTimeout(() => {
                map.resize();
            }, 500);
        });

        this.container.appendChild(btn);
        return this.container;
    }

    onRemove() {
        if (this.escListener) {
            document.removeEventListener('keydown', this.escListener);
        }
        this.container.parentNode.removeChild(this.container);
        this.map = undefined;
    }
}

// Export for use in modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = MapboxHelper;
}
