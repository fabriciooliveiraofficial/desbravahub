const CACHE_VERSION = 'desbravahub-v22';
const STATIC_CACHE = CACHE_VERSION + '-static';
const DYNAMIC_CACHE = CACHE_VERSION + '-dynamic';
const OFFLINE_URL = '/offline.html';
const FETCH_TIMEOUT = 10000; // 10 seconds timeout for network requests

// Core assets to pre-cache on install
const PRECACHE_ASSETS = [
    '/assets/css/app.css',
    '/assets/css/hud-theme.css',
    '/assets/js/toast.js',
    '/assets/js/push-notifications.js',
    '/assets/js/pwa-install.js',
    '/assets/js/offline-sync.js',
    '/offline.html'
];

// ==========================================
// INSTALL - Pre-cache core shell
// ==========================================
self.addEventListener('install', (event) => {
    console.log('[SW v22] Installing...');
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then(cache => {
                console.log('[SW v22] Pre-caching core assets');
                return cache.addAll(PRECACHE_ASSETS);
            })
            .catch(err => console.warn('[SW v22] Pre-cache failed (non-critical):', err))
    );
    self.skipWaiting();
});

// ==========================================
// ACTIVATE - Clean old caches
// ==========================================
self.addEventListener('activate', (event) => {
    console.log('[SW v22] Activated');
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames
                    .filter(name => name !== STATIC_CACHE && name !== DYNAMIC_CACHE)
                    .map(name => {
                        console.log('[SW v22] Deleting old cache:', name);
                        return caches.delete(name);
                    })
            );
        })
    );
    self.clients.claim();
});

// ==========================================
// FETCH - Stale-While-Revalidate + Offline
// ==========================================
self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    // Skip non-GET, cross-origin, chrome-extension, etc.
    if (request.method !== 'GET') return;
    if (url.origin !== self.location.origin) return;

    // Strategy selection based on request type
    if (isStaticAsset(url.pathname)) {
        // STATIC ASSETS → Cache First, Network Fallback
        event.respondWith(cacheFirst(request));
    } else if (isAPIRoute(url.pathname)) {
        // API ROUTES → Network First, Cache Fallback
        event.respondWith(networkFirst(request));
    } else if (isNavigationRequest(request)) {
        // HTML PAGES → Stale-While-Revalidate
        event.respondWith(staleWhileRevalidate(request));
    }
});

// ==========================================
// CACHING STRATEGIES
// ==========================================

/**
 * Fetch helper with timeout
 */
async function fetchWithTimeout(resource, options = {}) {
    const { timeout = FETCH_TIMEOUT } = options;

    const controller = new AbortController();
    const id = setTimeout(() => controller.abort(), timeout);

    try {
        const response = await fetch(resource, {
            ...options,
            signal: controller.signal
        });
        clearTimeout(id);
        return response;
    } catch (error) {
        clearTimeout(id);
        throw error;
    }
}

/**
 * Cache First - For static assets (CSS, JS, images, fonts)
 */
async function cacheFirst(request) {
    const cached = await caches.match(request);
    if (cached) return cached;

    try {
        const networkResponse = await fetchWithTimeout(request);
        if (networkResponse.ok) {
            const cache = await caches.open(STATIC_CACHE);
            cache.put(request, networkResponse.clone());
        }
        return networkResponse;
    } catch (err) {
        // Asset not available offline
        return new Response('', { status: 408, statusText: 'Offline' });
    }
}

/**
 * Network First - For API calls (fresh data preferred)
 */
async function networkFirst(request) {
    try {
        const networkResponse = await fetchWithTimeout(request);
        if (networkResponse.ok) {
            const cache = await caches.open(DYNAMIC_CACHE);
            cache.put(request, networkResponse.clone());
        }
        return networkResponse;
    } catch (err) {
        const cached = await caches.match(request);
        if (cached) return cached;
        return new Response(JSON.stringify({ offline: true, error: 'Sem conexão ou tempo esgotado' }), {
            headers: { 'Content-Type': 'application/json' }
        });
    }
}

/**
 * Stale-While-Revalidate - For HTML pages
 * Serves cached version instantly, updates cache in background
 */
async function staleWhileRevalidate(request) {
    const cache = await caches.open(DYNAMIC_CACHE);
    const cached = await cache.match(request);

    // Background fetch with timeout
    const fetchPromise = fetchWithTimeout(request).then(networkResponse => {
        if (networkResponse.ok) {
            cache.put(request, networkResponse.clone());
        }
        return networkResponse;
    }).catch(err => {
        console.warn('[SW v22] Network fetch failed or timed out:', err);
        return null;
    });

    // Return cached immediately if available, or wait for network
    if (cached) {
        // Update in background
        fetchPromise;
        return cached;
    }

    // No cache → must wait for network (with timeout)
    const networkResponse = await fetchPromise;
    if (networkResponse) return networkResponse;

    // Completely offline, no cache → show offline page
    return caches.match(OFFLINE_URL) || new Response('Offline', { status: 503 });
}

// ==========================================
// HELPER FUNCTIONS
// ==========================================

function isStaticAsset(pathname) {
    return /\.(css|js|png|jpg|jpeg|gif|svg|woff2?|ttf|eot|ico|webp)$/i.test(pathname)
        || pathname.startsWith('/assets/');
}

function isAPIRoute(pathname) {
    return pathname.includes('/api/');
}

function isNavigationRequest(request) {
    return request.mode === 'navigate'
        || (request.method === 'GET' && request.headers.get('accept')?.includes('text/html'));
}

// ==========================================
// BACKGROUND SYNC - Offline Form Queue
// ==========================================
self.addEventListener('sync', (event) => {
    if (event.tag === 'offline-form-sync') {
        console.log('[SW v22] Background Sync triggered');
        event.waitUntil(replayOfflineQueue());
    }
});

/**
 * Replay queued offline form submissions from IndexedDB
 */
async function replayOfflineQueue() {
    try {
        const db = await openOfflineDB();
        const tx = db.transaction('offline_queue', 'readwrite');
        const store = tx.objectStore('offline_queue');
        const allRequests = await getAllFromStore(store);

        for (const item of allRequests) {
            try {
                const response = await fetch(item.url, {
                    method: item.method,
                    headers: item.headers,
                    body: item.body
                });

                if (response.ok) {
                    // Remove from queue on success
                    const deleteTx = db.transaction('offline_queue', 'readwrite');
                    deleteTx.objectStore('offline_queue').delete(item.id);
                    console.log('[SW v22] Synced queued request:', item.url);
                }
            } catch (err) {
                console.warn('[SW v22] Retry failed for:', item.url);
                // Will be retried on next sync
            }
        }
    } catch (err) {
        console.error('[SW v22] Replay queue error:', err);
    }
}

function openOfflineDB() {
    return new Promise((resolve, reject) => {
        const req = indexedDB.open('DesbravaHubOffline', 1);
        req.onupgradeneeded = () => {
            const db = req.result;
            if (!db.objectStoreNames.contains('offline_queue')) {
                db.createObjectStore('offline_queue', { keyPath: 'id', autoIncrement: true });
            }
        };
        req.onsuccess = () => resolve(req.result);
        req.onerror = () => reject(req.error);
    });
}

function getAllFromStore(store) {
    return new Promise((resolve, reject) => {
        const req = store.getAll();
        req.onsuccess = () => resolve(req.result);
        req.onerror = () => reject(req.error);
    });
}

// ==========================================
// PUSH NOTIFICATIONS (preserved from v14)
// ==========================================
self.addEventListener('push', (event) => {
    let data = {
        title: 'DesbravaHub',
        body: 'Você tem uma nova notificação!',
        icon: '/assets/images/icon-192.png',
        badge: '/assets/images/badge-72.png'
    };

    if (event.data) {
        try {
            const rawText = event.data.text();
            const json = JSON.parse(rawText);
            data = { ...data, ...json };
            if (json.message && !json.body) {
                data.body = json.message;
            }
        } catch (e) {
            data.body = event.data ? event.data.text() : 'Nova notificação';
        }
    }

    const options = {
        body: data.body,
        icon: data.icon,
        badge: data.badge,
        vibrate: [200, 100, 200],
        data: { url: data.url || '/' },
        actions: [
            { action: 'open', title: 'Abrir' },
            { action: 'close', title: 'Fechar' }
        ],
        tag: 'desbravahub-' + Date.now(),
        renotify: true,
        requireInteraction: true,
        silent: false
    };

    event.waitUntil(
        self.registration.showNotification(data.title, options)
    );
});

// Notification click handler
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    if (event.action === 'close') return;

    const targetUrl = event.notification.data?.url || '/';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(windowClients => {
            for (const client of windowClients) {
                if (new URL(client.url).origin === new URL(targetUrl).origin) {
                    return client.focus().then(() => client.navigate(targetUrl))
                        .catch(() => clients.openWindow ? clients.openWindow(targetUrl) : null);
                }
            }
            if (clients.openWindow) return clients.openWindow(targetUrl);
        })
    );
});
