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

    /**
     * Synchronize subscription with server
     */
    async sync() {
        if (!this.initialized || !this.apiEndpoint) return;
        this.log('Syncing subscription...');
        try {
            await this.subscribe(this.apiEndpoint);
            this.log('Sync success');
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
            // IMPORTANT: Always unsubscribe first to clear any stale endpoints
            let existingSub = await this.getSubscription();
            if (existingSub) {
                this.log('Unsubscribing old endpoint...');
                await existingSub.unsubscribe();
            }

            // Create fresh subscription
            this.log('Creating new subscription...');
            const subscription = await this.registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: this.urlBase64ToUint8Array(this.publicKey)
            });

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
}

// Global instance
const pushNotifications = new PushNotifications();
