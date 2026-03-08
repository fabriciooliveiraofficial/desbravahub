/**
 * DesbravaHub Push Notifications
 * Client-side push notification management.
 * V1.0.9 - Force sync and debugged keys
 */

class PushNotifications {
    constructor() {
        this.publicKey = null;
        this.registration = null;
        this.initialized = false;
        this.apiEndpoint = null;
        this.debug = true;
    }

    log(...args) {
        if (this.debug) console.log('[PushNotifications]', ...args);
    }

    /**
     * Initialize push notifications
     */
    async init(publicKey, apiEndpoint = null) {
        if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
            console.warn('Push notifications not supported');
            return false;
        }

        this.publicKey = publicKey;
        this.apiEndpoint = apiEndpoint;

        try {
            // Register service worker
            this.registration = await navigator.serviceWorker.register('/sw.js');
            this.log('Service Worker registered');

            // Force update SW
            await this.registration.update();

            // Wait for ready and active
            const readyReg = await navigator.serviceWorker.ready;

            // Ensure SW is active before proceeding
            if (!readyReg.active) {
                await new Promise(resolve => {
                    readyReg.installing?.addEventListener('statechange', e => {
                        if (e.target.state === 'activated') resolve();
                    });
                    readyReg.waiting?.addEventListener('statechange', e => {
                        if (e.target.state === 'activated') resolve();
                    });
                    // Fallback timeout
                    setTimeout(resolve, 2000);
                });
            }

            this.registration = readyReg;
            this.initialized = true;

            // Auto-sync
            if (Notification.permission === 'granted' && this.apiEndpoint) {
                this.sync();
            }

            return true;
        } catch (err) {
            console.error('SW registration failed:', err);
            return false;
        }
    }

    isSupported() {
        return 'serviceWorker' in navigator && 'PushManager' in window;
    }

    async sync() {
        if (!this.initialized || !this.apiEndpoint) return;

        try {
            const existingSub = await this.getSubscription();

            if (existingSub) {
                // Subscription exists in browser — always heartbeat to server.
                // Handles: server DB resets, FCM key rotation (Chrome refreshes endpoints
                // periodically), and cases where the subscribe POST failed silently before.
                this.log('Heartbeating subscription to server...');
                const response = await fetch(this.apiEndpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(existingSub)
                });
                this.log(response.ok ? 'Heartbeat OK' : 'Heartbeat server error');
                return;
            }

            // No subscription in browser — create one (permission already granted at this point)
            this.log('No subscription found — creating new...');
            const subscription = await this.registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: this.urlBase64ToUint8Array(this.publicKey)
            });
            const res = await fetch(this.apiEndpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(subscription)
            });
            this.log(res.ok ? 'New subscription saved' : 'Failed to save subscription');
        } catch (e) {
            this.log('Sync failed:', e.message);
        }
    }

    async requestPermission() {
        const permission = await Notification.requestPermission();
        this.log('Permission:', permission);
        return permission;
    }

    async getSubscription() {
        if (!this.registration) return null;
        return await this.registration.pushManager.getSubscription();
    }

    /**
     * Get current permission status
     */
    getPermissionStatus() {
        if (!('Notification' in window)) return 'unsupported';
        return Notification.permission;
    }

    /**
     * Show local notification (for testing)
     */
    async showLocal(title, options = {}) {
        if (Notification.permission !== 'granted') return;
        const sw = await navigator.serviceWorker.ready;
        return sw.showNotification(title, {
            icon: '/assets/images/icon-192.png',
            badge: '/assets/images/badge-72.png',
            vibrate: [100, 50, 100],
            ...options
        });
    }

    async subscribe(apiEndpoint) {
        if (!this.initialized) throw new Error('Not initialized');

        const permission = await this.requestPermission();
        if (permission !== 'granted') throw new Error('Permission denied');

        try {
            // Reuse existing browser subscription if available — do NOT unsubscribe first.
            // Unsubscribing destroys the endpoint on the push service immediately, creating a
            // window where pushes sent before the new subscription reaches the server are lost.
            let subscription = await this.getSubscription();
            if (!subscription) {
                this.log('Creating new subscription...');
                subscription = await this.registration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: this.urlBase64ToUint8Array(this.publicKey)
                });
            } else {
                this.log('Reusing existing subscription:', subscription.endpoint.slice(-20));
            }

            this.log('Subscription object:', !!subscription);

            // Send to server
            const response = await fetch(apiEndpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(subscription)
            });

            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                throw new Error(errorData.error || 'Server error');
            }

            this.log('Subscribed successfully');
            return subscription;
        } catch (err) {
            console.error('Subscribe error:', err);
            throw err;
        }
    }

    urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding)
            .replace(/-/g, '+')
            .replace(/_/g, '/');

        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    /**
     * Set up listener for messages from Service Worker
     */
    setupSwListener() {
        if (!('serviceWorker' in navigator)) return;

        navigator.serviceWorker.addEventListener('message', event => {
            if (event.data && event.data.type === 'PUSH_NOTIFICATION') {
                this.log('Message from SW:', event.data);
                this.handleIncomingPush(event.data);
            }
        });
    }

    /**
     * Handle incoming push data (trigger toast/sound)
     */
    handleIncomingPush(payload) {
        const { data, priority } = payload;
        if (!window.toast) {
            this.log('Toast system not available');
            return;
        }

        window.toast.show({
            title: data.title,
            message: data.body,
            type: data.type || 'info',
            priority: priority || data.priority || 'normal',
            position: priority === 'critical' || data.priority === 'critical' ? 'center' : 'default',
            icon: data.icon,
            onClick: data.url ? () => { window.location.href = data.url; } : null
        });

        // Mark notification as read in DB immediately to prevent duplicate toast from polling.
        const notifId = data?.data?.notification_id;
        if (notifId && window.toast?.apiUrl) {
            fetch(`${window.toast.apiUrl}/${notifId}/read`, {
                method: 'POST',
                credentials: 'include'
            }).catch(() => {});
        }

        // Notify other components (like badge poller)
        document.dispatchEvent(new CustomEvent('push-notification-received', { detail: payload }));
    }
}

// Global instance
const pushNotifications = new PushNotifications();
pushNotifications.setupSwListener();
