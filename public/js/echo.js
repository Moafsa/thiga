/**
 * Laravel Echo Configuration
 * For real-time broadcasting
 * Fallback version that doesn't require build tools
 */

(function() {
    'use strict';
    
    // Function to load Pusher from CDN
    function loadPusher() {
        return new Promise(function(resolve, reject) {
            if (typeof window.Pusher !== 'undefined') {
                resolve(window.Pusher);
                return;
            }
            
            const script = document.createElement('script');
            script.src = 'https://js.pusher.com/8.2.0/pusher.min.js';
            script.onload = function() {
                resolve(window.Pusher);
            };
            script.onerror = function() {
                reject(new Error('Failed to load Pusher'));
            };
            document.head.appendChild(script);
        });
    }
    
    // Function to load Laravel Echo from CDN
    function loadEcho() {
        return new Promise(function(resolve, reject) {
            if (typeof window.Echo !== 'undefined') {
                resolve(window.Echo);
                return;
            }
            
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/laravel-echo@1.15.0/dist/echo.iife.js';
            script.onload = function() {
                resolve(window.Echo);
            };
            script.onerror = function() {
                reject(new Error('Failed to load Laravel Echo'));
            };
            document.head.appendChild(script);
        });
    }
    
    // Initialize Echo when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeEcho);
    } else {
        initializeEcho();
    }
    
    function initializeEcho() {
        // Check if Echo is already available
        if (typeof window.Echo !== 'undefined') {
            console.log('Laravel Echo already loaded');
            return;
        }
        
        // Try to load Pusher and Echo
        loadPusher()
            .then(function() {
                return loadEcho();
            })
            .then(function() {
                // Initialize Echo with configuration
                if (typeof window.Echo !== 'undefined' && typeof window.Pusher !== 'undefined') {
                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                        const pusherKey = window.pusherKey || window.PUSHER_APP_KEY || null;
                        const pusherCluster = window.pusherCluster || window.PUSHER_APP_CLUSTER || 'us2';
                        
                        // Only initialize if we have a valid Pusher key
                        if (!pusherKey || pusherKey === 'your-pusher-key' || pusherKey === '') {
                            console.warn('Pusher key not configured. Real-time tracking disabled.');
                            // Create a stub Echo object to prevent errors
                            window.Echo = {
                                channel: function() {
                                    return {
                                        listen: function() {},
                                        stopListening: function() {}
                                    };
                                },
                                private: function() {
                                    return {
                                        listen: function() {},
                                        stopListening: function() {}
                                    };
                                }
                            };
                            return;
                        }
                        
                        window.Echo = new Echo({
                            broadcaster: 'pusher',
                            key: pusherKey,
                            cluster: pusherCluster,
                            forceTLS: true,
                            encrypted: true,
                            authEndpoint: '/broadcasting/auth',
                            auth: {
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken || ''
                                }
                            }
                        });
                        
                        console.log('Laravel Echo initialized successfully');
                    } catch (error) {
                        console.warn('Laravel Echo initialization failed:', error);
                        // Create a stub Echo object to prevent errors
                        window.Echo = {
                            channel: function() {
                                return {
                                    listen: function() {},
                                    stopListening: function() {}
                                };
                            },
                            private: function() {
                                return {
                                    listen: function() {},
                                    stopListening: function() {}
                                };
                            }
                        };
                    }
                }
            })
            .catch(function(error) {
                console.warn('Failed to load real-time libraries:', error);
                console.warn('Real-time tracking will be disabled');
                // Create a stub Echo object to prevent errors
                window.Echo = {
                    channel: function() {
                        return {
                            listen: function() {},
                            stopListening: function() {}
                        };
                    },
                    private: function() {
                        return {
                            listen: function() {},
                            stopListening: function() {}
                        };
                    }
                };
            });
    }
})();
