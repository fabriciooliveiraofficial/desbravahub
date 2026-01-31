const CACHE_NAME = 'desbravahub-v13'; // Deep link support
const urlsToCache = [
    '/assets/css/app.css',
    '/assets/js/toast.js',
    '/assets/js/push-notifications.js'
];

// Install event - cache assets
self.addEventListener('install', (event) => {
    console.log('[SW] Installing version', CACHE_NAME);
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => cache.addAll(urlsToCache))
    );
    self.skipWaiting();
});

// Activate event - clean old caches
self.addEventListener('activate', (event) => {
    console.log('[SW] Activated');
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.filter((name) => name !== CACHE_NAME)
                    .map((name) => caches.delete(name))
            );
        })
    );
    self.clients.claim();
});

// Push notification received
self.addEventListener('push', (event) => {
    console.log('[SW v13] Push Received!', event);

    // Default notification data
    let data = {
        title: 'DesbravaHub',
        body: 'Você tem uma nova notificação!',
        icon: '/assets/images/icon-192.png',
        badge: '/assets/images/badge-72.png'
    };

    if (event.data) {
        try {
            const rawText = event.data.text();
            console.log('[SW v13] Push data raw:', rawText);
            const json = JSON.parse(rawText);
            data = { ...data, ...json };

            // Handle body/message aliasing from PHP side
            if (json.message && !json.body) {
                data.body = json.message;
            }
        } catch (e) {
            console.error('[SW v13] JSON parse error:', e);
            data.body = event.data ? event.data.text() : 'Nova notificação';
        }
    }

    console.log('[SW v13] Showing notification:', data.title, data.body);

    const options = {
        body: data.body,
        icon: data.icon,
        badge: data.badge,
        vibrate: [200, 100, 200],
        data: {
            url: data.url || '/'
        },
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
            .then(() => console.log('[SW v13] Notification shown successfully'))
            .catch(err => console.error('[SW v13] Failed to show notification:', err))
    );
});

// Notification click handler
self.addEventListener('notificationclick', (event) => {
    console.log('[SW v13] Notification clicked:', event.action);
    event.notification.close();

    // If user clicked "close" action, just close notification
    if (event.action === 'close') return;

    // Get URL from notification data
    const targetUrl = event.notification.data?.url || '/';
    console.log('[SW v13] Opening URL:', targetUrl);

    // Simply open the URL in a new window/tab - most reliable method
    event.waitUntil(
        clients.openWindow(targetUrl)
            .then(() => console.log('[SW v13] Window opened successfully'))
            .catch(err => {
                console.error('[SW v13] openWindow failed:', err);
                // Try focusing existing window as fallback
                return clients.matchAll({ type: 'window' })
                    .then(windowClients => {
                        if (windowClients.length > 0) {
                            windowClients[0].focus();
                        }
                    });
            })
    );
});

// Fetch event
self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') return;

    const url = new URL(event.request.url);
    if (url.origin !== self.location.origin) return;

    // Don't cache API or dynamic routes
    if (url.pathname.includes('/api/') || url.pathname.includes('/admin/') || url.pathname.includes('/dashboard/')) return;

    event.respondWith(
        caches.match(event.request)
            .then((response) => {
                if (response) return response;
                return fetch(event.request);
            })
    );
});
