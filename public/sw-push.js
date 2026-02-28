// Thiga TMS - Push Notification Service Worker
self.addEventListener('push', function (event) {
    if (!event.data) return;

    let data;
    try {
        data = event.data.json();
    } catch (e) {
        data = {
            title: 'Thiga Transportes',
            body: event.data.text(),
        };
    }

    const options = {
        body: data.body || '',
        icon: data.icon || '/images/icon-192x192.png',
        badge: data.badge || '/images/badge-72x72.png',
        vibrate: [200, 100, 200],
        tag: data.data?.type || 'general',
        renotify: true,
        requireInteraction: data.data?.type === 'new_route',
        data: data.data || {},
        actions: [],
    };

    // Add context-specific actions
    if (data.data?.type === 'new_route') {
        options.actions = [
            { action: 'view', title: 'Ver Rota', icon: '/images/icon-route.png' },
            { action: 'dismiss', title: 'Depois' },
        ];
    } else if (data.data?.type === 'shipment_update') {
        options.actions = [
            { action: 'view', title: 'Ver Carga' },
        ];
    }

    event.waitUntil(
        self.registration.showNotification(data.title || 'Thiga Transportes', options)
    );
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();

    const action = event.action;
    const data = event.notification.data || {};

    let url = '/driver/dashboard';

    if (action === 'view' && data.action) {
        url = data.action;
    } else if (data.type === 'shipment_update' && data.tracking) {
        url = '/driver/dashboard';
    }

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (clientList) {
            // Focus existing window if available
            for (let client of clientList) {
                if (client.url.includes('/driver') && 'focus' in client) {
                    return client.focus();
                }
            }
            // Open new window
            if (clients.openWindow) {
                return clients.openWindow(url);
            }
        })
    );
});

// Handle subscription change
self.addEventListener('pushsubscriptionchange', function (event) {
    event.waitUntil(
        self.registration.pushManager.subscribe(event.oldSubscription.options)
            .then(function (subscription) {
                return fetch('/api/push/subscribe', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(subscription),
                });
            })
    );
});
