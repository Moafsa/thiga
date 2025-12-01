const APP_SHELL_CACHE = 'driver-app-shell-v2';
const RUNTIME_CACHE = 'driver-runtime-v2';
const OFFLINE_URL = '/offline.html';
const PRECACHE_URLS = [
  OFFLINE_URL,
  '/driver/dashboard',
  '/manifest.json',
  '/icons/icon-192x192.png',
];
const API_QUEUE_DB = 'driver-sync';
const API_QUEUE_STORE = 'requests';

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(APP_SHELL_CACHE).then((cache) => cache.addAll(PRECACHE_URLS)).catch((error) => {
      console.error('[SW] Failed to precache app shell', error);
    })
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    (async () => {
      const keys = await caches.keys();
      await Promise.all(
        keys
          .filter((key) => ![APP_SHELL_CACHE, RUNTIME_CACHE].includes(key))
          .map((key) => caches.delete(key))
      );
      await cleanupQueue();
      await self.clients.claim();
    })()
  );
});

self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);

  if (request.method === 'POST' && isDriverApi(request)) {
    event.respondWith(
      fetch(request.clone())
        .catch(() => queueRequest(request))
    );
    return;
  }

  if (request.method === 'GET') {
    if (request.mode === 'navigate') {
      event.respondWith(
        fetch(request)
          .then((response) => {
            const copy = response.clone();
            caches.open(RUNTIME_CACHE).then((cache) => cache.put(request, copy));
            return response;
          })
          .catch(async () => {
            const cached = await caches.match(request);
            if (cached) {
              return cached;
            }
            const offlineFallback = await caches.match(OFFLINE_URL);
            return offlineFallback || Response.error();
          })
      );
      return;
    }

    if (url.origin === self.location.origin) {
      event.respondWith(
        caches.match(request).then((cached) => {
          const networkFetch = fetch(request)
            .then((response) => {
              if (response && response.status === 200) {
                const copy = response.clone();
                caches.open(RUNTIME_CACHE).then((cache) => cache.put(request, copy));
              }
              return response;
            })
            .catch(() => cached);

          return cached || networkFetch;
        })
      );
      return;
    }

    if (url.hostname.includes('googleapis.com') || url.hostname.includes('gstatic.com') || url.hostname.includes('cdnjs.cloudflare.com')) {
      event.respondWith(
        caches.match(request).then((cached) => {
          const fetchPromise = fetch(request)
            .then((response) => {
              if (response && response.status === 200) {
                caches.open(RUNTIME_CACHE).then((cache) => cache.put(request, response.clone()));
              }
              return response.clone();
            })
            .catch(() => cached);

          return cached || fetchPromise;
        })
      );
      return;
    }
  }
});

self.addEventListener('sync', (event) => {
  if (event.tag === 'driver-sync') {
    event.waitUntil(processQueue());
  }
});

self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
});

self.addEventListener('push', (event) => {
  const options = {
    body: event.data ? event.data.text() : 'Nova notificação',
    icon: '/icons/icon-192x192.png',
    badge: '/icons/icon-96x96.png',
    vibrate: [200, 100, 200],
    tag: 'tms-notification',
    requireInteraction: false,
  };

  event.waitUntil(self.registration.showNotification('TMS SaaS', options));
});

self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  event.waitUntil(clients.openWindow('/driver/dashboard'));
});

function isDriverApi(request) {
  const url = new URL(request.url);
  return url.origin === self.location.origin && url.pathname.startsWith('/api/driver/');
}

async function queueRequest(request) {
  try {
    const db = await openQueueDb();
    const bodyText = await request.clone().text();
    const headers = {};
    request.headers.forEach((value, key) => {
      headers[key] = value;
    });

    await new Promise((resolve, reject) => {
      const tx = db.transaction(API_QUEUE_STORE, 'readwrite');
      tx.oncomplete = resolve;
      tx.onerror = () => reject(tx.error);
      const store = tx.objectStore(API_QUEUE_STORE);
      store.add({
        url: request.url,
        method: request.method,
        headers,
        body: bodyText,
        timestamp: Date.now(),
      });
    });

    if (self.registration.sync) {
      await self.registration.sync.register('driver-sync');
    }

    return new Response(
      JSON.stringify({
        queued: true,
        offline: true,
        message: 'Ação registrada para sincronização quando a conexão retornar.',
      }),
      { status: 202, headers: { 'Content-Type': 'application/json' } }
    );
  } catch (error) {
    console.error('[SW] Failed to queue request', error);
    return new Response(JSON.stringify({ queued: false, offline: true }), {
      status: 503,
      headers: { 'Content-Type': 'application/json' },
    });
  }
}

async function processQueue() {
  const db = await openQueueDb();

  const pending = await new Promise((resolve, reject) => {
    const tx = db.transaction(API_QUEUE_STORE, 'readonly');
    tx.onerror = () => reject(tx.error);
    const store = tx.objectStore(API_QUEUE_STORE);
    const items = [];
    store.openCursor().onsuccess = (event) => {
      const cursor = event.target.result;
      if (cursor) {
        items.push({ id: cursor.primaryKey, ...cursor.value });
        cursor.continue();
      } else {
        resolve(items);
      }
    };
  });

  for (const item of pending) {
    try {
      const response = await fetch(item.url, {
        method: item.method,
        headers: item.headers,
        body: item.method !== 'GET' ? item.body : undefined,
      });

      if (!response.ok) {
        throw new Error(`Server responded with ${response.status}`);
      }

      await removeFromQueue(db, item.id);
    } catch (error) {
      console.warn('[SW] Background sync failed, will retry later', error);
      break;
    }
  }
}

async function cleanupQueue() {
  const db = await openQueueDb();
  await new Promise((resolve, reject) => {
    const tx = db.transaction(API_QUEUE_STORE, 'readwrite');
    tx.onerror = () => reject(tx.error);
    const store = tx.objectStore(API_QUEUE_STORE);
    const now = Date.now();
    store.openCursor().onsuccess = (event) => {
      const cursor = event.target.result;
      if (cursor) {
        const item = cursor.value;
        if (now - item.timestamp > 1000 * 60 * 60 * 24) {
          cursor.delete();
        }
        cursor.continue();
      } else {
        resolve();
      }
    };
  });
}

function removeFromQueue(db, id) {
  return new Promise((resolve, reject) => {
    const tx = db.transaction(API_QUEUE_STORE, 'readwrite');
    tx.onerror = () => reject(tx.error);
    tx.oncomplete = resolve;
    tx.objectStore(API_QUEUE_STORE).delete(id);
  });
}

function openQueueDb() {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open(API_QUEUE_DB, 1);
    request.onerror = () => reject(request.error);
    request.onupgradeneeded = () => {
      if (!request.result.objectStoreNames.contains(API_QUEUE_STORE)) {
        request.result.createObjectStore(API_QUEUE_STORE, { autoIncrement: true });
      }
    };
    request.onsuccess = () => resolve(request.result);
  });
}



