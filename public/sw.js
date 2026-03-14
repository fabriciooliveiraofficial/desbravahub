const CACHE_VERSION = 'desbravahub-v32';
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
    '/assets/audio/notif.mp3',
    '/offline.html'
];

// ==========================================
// INSTALL - Pre-cache core shell
// ==========================================
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then(cache => cache.addAll(PRECACHE_ASSETS))
            .catch(err => console.warn('[SW] Pre-cache failed (non-critical):', err))
    );
    self.skipWaiting();
});

// ==========================================
// ACTIVATE - Clean old caches
// ==========================================
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then(cacheNames =>
            Promise.all(
                cacheNames
                    .filter(name => name !== STATIC_CACHE && name !== DYNAMIC_CACHE)
                    .map(name => caches.delete(name))
            )
        )
    );
    self.clients.claim();
});

// ==========================================
// FETCH - Performance + Security Isolation
// ==========================================
self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    // Skip non-GET, cross-origin, chrome-extension, etc.
    if (request.method !== 'GET') return;
    if (url.origin !== self.location.origin) return;

    // By-pass Service Worker entirely for logout routes.
    // Do NOT call event.respondWith() — let the browser handle the navigation and
    // follow the server redirect natively so the address bar updates correctly.
    if (url.pathname.endsWith('/logout') || url.pathname.includes('/logout/')) {
        return;
    }

    // Strategy selection based on request type
    if (isStaticAsset(url.pathname)) {
        // STATIC ASSETS → Cache First, Network Fallback
        event.respondWith(cacheFirst(request));
    } else if (isAPIRoute(url.pathname)) {
        // API ROUTES → Network First, Cache Fallback
        event.respondWith(networkFirst(request));
    } else if (isNavigationRequest(request)) {
        // HTML PAGES → Network First (Crucial for Data Isolation)
        // We never serve stale HTML to prevent account data leakage
        event.respondWith(networkFirst(request));
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
 * Network First - For API calls and Navigation
 * Serves network results and updates cache for offline fallback
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

        // If it's a navigation request and we are offline with no cache
        if (request.mode === 'navigate') {
            return caches.match(OFFLINE_URL);
        }

        return new Response(JSON.stringify({ offline: true, error: 'Sem conexão ou tempo esgotado' }), {
            headers: { 'Content-Type': 'application/json' }
        });
    }
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
// PUSH NOTIFICATIONS
// ==========================================
self.addEventListener('push', (event) => {
    let data = {
        title: 'DesbravaHub',
        body: 'Você tem uma nova notificação!',
        icon: '/assets/images/icon-192.png',
        badge: '/assets/images/badge-72.png',
        priority: 'normal'
    };

    if (event.data) {
        try {
            const json = JSON.parse(event.data.text());
            data = { ...data, ...json };
            if (json.message && !json.body) data.body = json.message;
        } catch (e) {
            data.body = event.data.text();
        }
    }

    const isCritical = data.priority === 'critical';
    // SOS detection: top-level type OR nested data.sos flag (set by SosController)
    const isSos = data.type === 'sos' || data.data?.sos === true;

    // Morse SOS vibration: · · · — — — · · ·  (dots=100ms, dashes=200ms, gaps=30ms, letter-gaps=200ms)
    const SOS_VIBRATE = [100, 30, 100, 30, 100, 200, 200, 30, 200, 30, 200, 200, 100, 30, 100, 30, 100];

    const options = {
        body: data.body,
        icon: data.icon || '/assets/images/icon-192.png',
        badge: data.badge || '/assets/images/badge-72.png',
        vibrate: isSos ? SOS_VIBRATE : (isCritical ? [300, 100, 300, 100, 300] : [200, 100, 200]),
        data: { url: data.link || data.url || '/', priority: data.priority, type: data.type },
        actions: [
            { action: 'open', title: 'Abrir' },
            { action: 'close', title: 'Fechar' }
        ],
        // SOS: dedicated tag so each new SOS replaces the previous one via renotify.
        // Other critical: shared critical tag. Normal: unique timestamp tag so they stack.
        tag: isSos ? 'sos_alert' : (isCritical ? 'desbravahub-critical' : 'desbravahub-' + Date.now()),
        renotify: true,
        requireInteraction: isCritical || isSos,
        silent: false
    };

    event.waitUntil(
        Promise.all([
            // Always show native OS notification — works when app is closed/minimized/logged out
            self.registration.showNotification(data.title, options).catch(err => {
                console.error('[SW] showNotification failed:', err);
                // Fallback: show generic notification so the browser doesn't auto-close the push
                return self.registration.showNotification('DesbravaHub', {
                    body: 'Você tem uma nova notificação.',
                    icon: '/assets/images/icon-192.png',
                    tag: 'desbravahub-fallback'
                });
            }),
            // Broadcast to open tabs for in-app toast + sound
            self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then(clients => {
                const msg = { type: 'PUSH_NOTIFICATION', data: data, priority: data.priority || 'normal' };
                clients.forEach(client => {
                    try { client.postMessage(msg); } catch (_) { /* tab closed/navigating */ }
                });
            })
        ])
    );
});

// ==========================================
// PUSH SUBSCRIPTION CHANGE
// Fires when the browser/FCM rotates the push endpoint (happens periodically).
// Without this handler, rotated subscriptions are never updated on the server
// and all future pushes fail silently.
// ==========================================
self.addEventListener('pushsubscriptionchange', (event) => {
    event.waitUntil((async () => {
        try {
            let newSub = event.newSubscription;

            // Chrome 72+ provides oldSubscription.options.applicationServerKey.
            // If newSubscription is not provided, re-subscribe using the old VAPID key.
            if (!newSub) {
                const appServerKey = event.oldSubscription?.options?.applicationServerKey;
                if (!appServerKey) {
                    console.warn('[SW] pushsubscriptionchange: no applicationServerKey — cannot resubscribe');
                    return;
                }
                newSub = await self.registration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: appServerKey
                });
            }

            // Send new subscription + old endpoint to server so it can update the record.
            // This endpoint requires no auth — it identifies the user by the old endpoint.
            await fetch('/api/push/resubscribe', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    ...newSub.toJSON(),
                    old_endpoint: event.oldSubscription?.endpoint
                })
            });

            console.log('[SW] Subscription rotated and updated on server');
        } catch (err) {
            console.error('[SW] pushsubscriptionchange failed:', err);
        }
    })());
});

// Notification click handler
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    if (event.action === 'close') return;

    const rawUrl = event.notification.data?.url || '/';
    // Resolve relative URLs (e.g. "/{slug}/admin/aprovacoes") against the SW origin
    // so that new URL() never throws for non-absolute strings.
    const targetUrl = (rawUrl.startsWith('http://') || rawUrl.startsWith('https://'))
        ? rawUrl
        : self.location.origin + (rawUrl.startsWith('/') ? rawUrl : '/' + rawUrl);

    event.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then(windowClients => {
            for (const client of windowClients) {
                if (new URL(client.url).origin === new URL(targetUrl).origin) {
                    return client.focus().then(() => client.navigate(targetUrl))
                        .catch(() => self.clients.openWindow ? self.clients.openWindow(targetUrl) : null);
                }
            }
            if (self.clients.openWindow) return self.clients.openWindow(targetUrl);
        })
    );
});
