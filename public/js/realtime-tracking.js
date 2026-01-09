/**
 * Real-time Driver Tracking with WebSocket
 * Uses Laravel Echo for broadcasting
 */

class RealTimeTracking {
    constructor(options = {}) {
        this.options = {
            tenantId: options.tenantId || null,
            routeId: options.routeId || null,
            driverId: options.driverId || null,
            mapHelper: options.mapHelper || null,
            onLocationUpdate: options.onLocationUpdate || null,
            ...options
        };

        this.echo = null;
        this.channels = [];
        this.driverMarker = null;
        this.locationHistory = [];

        this.init();
    }

    /**
     * Initialize WebSocket connection
     */
    async init() {
        // Check if Laravel Echo is available
        if (typeof Echo === 'undefined') {
            console.warn('Laravel Echo not found. Real-time tracking disabled.');
            // Don't try to load Echo if not available - just disable feature
            return;
        }

        this.connect();
    }

    /**
     * Load Laravel Echo if not available
     */
    loadEcho() {
        // Try to load from CDN or check if it's being loaded
        if (typeof window.io === 'undefined') {
            const socketScript = document.createElement('script');
            socketScript.src = 'https://cdn.socket.io/4.5.0/socket.io.min.js';
            socketScript.onload = () => this.loadEchoLibrary();
            document.head.appendChild(socketScript);
        } else {
            this.loadEchoLibrary();
        }
    }

    /**
     * Load Laravel Echo library
     */
    loadEchoLibrary() {
        const echoScript = document.createElement('script');
        echoScript.src = '/js/echo.js'; // You'll need to create this or use CDN
        echoScript.onload = () => this.connect();
        document.head.appendChild(echoScript);
    }

    /**
     * Connect to WebSocket channels
     */
    connect() {
        if (typeof Echo === 'undefined') {
            console.error('Echo not available. Using polling fallback.');
            this.startPolling();
            return;
        }

        // Initialize Echo
        this.echo = new Echo({
            broadcaster: 'pusher',
            key: window.PUSHER_APP_KEY || process.env.MIX_PUSHER_APP_KEY,
            cluster: window.PUSHER_APP_CLUSTER || process.env.MIX_PUSHER_APP_CLUSTER || 'mt1',
            forceTLS: true,
            encrypted: true,
            authEndpoint: '/broadcasting/auth',
            auth: {
                headers: {
                    'X-CSRF-TOKEN': this.getCsrfToken()
                }
            }
        });

        // Listen to driver location updates
        if (this.options.driverId && this.options.tenantId) {
            const driverChannel = this.echo.private(`tenant.${this.options.tenantId}.driver.${this.options.driverId}`);
            driverChannel.listen('.driver.location.updated', (data) => {
                this.handleLocationUpdate(data);
            });
            this.channels.push(driverChannel);
        }

        // Listen to route updates
        if (this.options.routeId && this.options.tenantId) {
            const routeChannel = this.echo.private(`tenant.${this.options.tenantId}.route.${this.options.routeId}`);
            routeChannel.listen('.driver.location.updated', (data) => {
                this.handleLocationUpdate(data);
            });
            this.channels.push(routeChannel);
        }

        // Listen to admin dashboard (all drivers)
        if (this.options.tenantId) {
            const adminChannel = this.echo.private(`tenant.${this.options.tenantId}.admin.drivers`);
            adminChannel.listen('.driver.location.updated', (data) => {
                this.handleLocationUpdate(data);
            });
            this.channels.push(adminChannel);
        }

        console.log('Real-time tracking connected');
    }

    /**
     * Handle location update from WebSocket
     */
    handleLocationUpdate(data) {
        const location = {
            latitude: data.latitude,
            longitude: data.longitude,
            driver_id: data.driver_id,
            route_id: data.route_id,
            shipment_id: data.shipment_id,
            speed: data.speed,
            heading: data.heading,
            is_moving: data.is_moving,
            timestamp: new Date(data.timestamp)
        };

        // Add to history
        this.locationHistory.push(location);
        
        // Keep only last 100 points
        if (this.locationHistory.length > 100) {
            this.locationHistory.shift();
        }

        // Update map marker
        if (this.options.mapHelper && location.driver_id === this.options.driverId) {
            this.updateDriverMarker(location);
        }

        // Call custom callback
        if (this.options.onLocationUpdate) {
            this.options.onLocationUpdate(location);
        }
    }

    /**
     * Update driver marker on map
     */
    updateDriverMarker(location) {
        const position = {
            lat: location.latitude,
            lng: location.longitude
        };

        if (!this.driverMarker) {
            // Create new marker
            this.driverMarker = this.options.mapHelper.addMarker(position, {
                title: 'Motorista',
                color: '#2196F3',
                size: 32,
                content: `<div>Velocidade: ${location.speed || 0} km/h</div>`
            });
        } else {
            // Update existing marker
            this.options.mapHelper.updateMarker(this.driverMarker, position);
        }

        // Update heading/rotation if available
        if (location.heading !== null && location.heading !== undefined) {
            const element = this.driverMarker.getElement();
            if (element) {
                element.style.transform = `rotate(${location.heading}deg)`;
            }
        }
    }

    /**
     * Fallback: Polling for location updates
     */
    startPolling() {
        if (!this.options.routeId || !this.options.driverId) {
            return;
        }

        const pollInterval = setInterval(async () => {
            try {
                const response = await fetch(`/api/driver/location/history?driver_id=${this.options.driverId}&route_id=${this.options.routeId}&minutes=1&limit=1`);
                const data = await response.json();
                
                if (data.locations && data.locations.length > 0) {
                    const latest = data.locations[data.locations.length - 1];
                    this.handleLocationUpdate({
                        latitude: latest.latitude,
                        longitude: latest.longitude,
                        driver_id: this.options.driverId,
                        route_id: this.options.routeId,
                        speed: latest.speed,
                        heading: latest.heading,
                        is_moving: latest.is_moving,
                        timestamp: latest.tracked_at
                    });
                }
            } catch (error) {
                console.error('Polling error:', error);
            }
        }, 5000); // Poll every 5 seconds

        // Store interval for cleanup
        this.pollInterval = pollInterval;
    }

    /**
     * Get CSRF token
     */
    getCsrfToken() {
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        return metaTag ? metaTag.getAttribute('content') : '';
    }

    /**
     * Get location history
     */
    getLocationHistory() {
        return this.locationHistory;
    }

    /**
     * Draw location history path
     */
    drawHistoryPath(mapHelper, color = '#2196F3', width = 4) {
        if (!mapHelper || this.locationHistory.length < 2) {
            return;
        }

        const coordinates = this.locationHistory.map(loc => ({
            lat: loc.latitude,
            lng: loc.longitude
        }));

        // Draw polyline on map
        mapHelper.fitBounds(coordinates);

        // You can implement polyline drawing here using mapHelper
        // This depends on your MapboxHelper implementation
    }

    /**
     * Disconnect
     */
    disconnect() {
        // Disconnect all channels
        this.channels.forEach(channel => {
            if (channel && typeof channel.stopListening === 'function') {
                channel.stopListening('.driver.location.updated');
            }
        });
        this.channels = [];

        // Stop polling if active
        if (this.pollInterval) {
            clearInterval(this.pollInterval);
        }

        // Disconnect Echo
        if (this.echo) {
            this.echo.disconnect();
            this.echo = null;
        }
    }
}

// Export for use in modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = RealTimeTracking;
}
