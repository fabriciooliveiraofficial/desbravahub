/**
 * DesbravaHub - Offline Sync Manager
 * 
 * Client-side IndexedDB helper for queuing form submissions when offline.
 * The Service Worker's Background Sync will replay these when connection returns.
 */
const OfflineSync = {
    DB_NAME: 'DesbravaHubOffline',
    DB_VERSION: 1,
    STORE_NAME: 'offline_queue',

    /**
     * Open the IndexedDB database
     */
    openDB() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(this.DB_NAME, this.DB_VERSION);
            request.onupgradeneeded = (event) => {
                const db = event.target.result;
                if (!db.objectStoreNames.contains(this.STORE_NAME)) {
                    db.createObjectStore(this.STORE_NAME, { keyPath: 'id', autoIncrement: true });
                }
            };
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    },

    /**
     * Queue a form submission for later sync
     * @param {string} url - The endpoint URL
     * @param {string} method - HTTP method (POST, PUT, etc.)
     * @param {object|FormData} data - The form data
     */
    async queue(url, method, data) {
        try {
            const db = await this.openDB();
            const tx = db.transaction(this.STORE_NAME, 'readwrite');
            const store = tx.objectStore(this.STORE_NAME);

            let body = data;
            let headers = {};

            if (data instanceof FormData) {
                // Convert FormData to a serializable format
                const formObj = {};
                for (const [key, value] of data.entries()) {
                    // Skip file uploads in offline mode (too large for IndexedDB)
                    if (value instanceof File) {
                        formObj[key] = `[FILE: ${value.name}]`;
                    } else {
                        formObj[key] = value;
                    }
                }
                body = JSON.stringify(formObj);
                headers = { 'Content-Type': 'application/json' };
            } else if (typeof data === 'object') {
                body = JSON.stringify(data);
                headers = { 'Content-Type': 'application/json' };
            }

            store.add({
                url: url,
                method: method,
                body: body,
                headers: headers,
                timestamp: Date.now()
            });

            await new Promise((resolve, reject) => {
                tx.oncomplete = resolve;
                tx.onerror = reject;
            });

            console.log('[OfflineSync] Queued:', method, url);

            // Request Background Sync if available
            if ('serviceWorker' in navigator && 'SyncManager' in window) {
                const reg = await navigator.serviceWorker.ready;
                await reg.sync.register('offline-form-sync');
                console.log('[OfflineSync] Background Sync registered');
            }

            return true;
        } catch (err) {
            console.error('[OfflineSync] Queue failed:', err);
            return false;
        }
    },

    /**
     * Get the number of pending items in the queue
     */
    async getPendingCount() {
        try {
            const db = await this.openDB();
            const tx = db.transaction(this.STORE_NAME, 'readonly');
            const store = tx.objectStore(this.STORE_NAME);
            return new Promise((resolve) => {
                const req = store.count();
                req.onsuccess = () => resolve(req.result);
                req.onerror = () => resolve(0);
            });
        } catch {
            return 0;
        }
    },

    /**
     * Intercept a fetch call: if online, fetch normally. If offline, queue it.
     * @param {string} url
     * @param {object} options - fetch options (method, body, headers)
     * @returns {Promise<Response|boolean>}
     */
    async smartFetch(url, options = {}) {
        if (navigator.onLine) {
            return fetch(url, options);
        }

        // Offline: queue the request
        const queued = await this.queue(url, options.method || 'POST', options.body);

        if (queued && typeof window.toast !== 'undefined') {
            window.toast.info('Salvo Offline', 'Sua resposta será enviada automaticamente quando a conexão voltar.');
        }

        return queued;
    }
};

// Expose globally
window.OfflineSync = OfflineSync;

// ==========================================
// ONLINE/OFFLINE STATUS INDICATOR
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
    // Create status indicator
    const indicator = document.createElement('div');
    indicator.id = 'offline-indicator';
    indicator.innerHTML = '<span class="material-icons-round" style="font-size:14px">cloud_off</span> Modo Offline';
    indicator.style.cssText = `
        position: fixed; top: 8px; left: 50%; transform: translateX(-50%);
        background: rgba(239, 68, 68, 0.9); color: #fff; padding: 6px 16px;
        border-radius: 20px; font-size: 0.7rem; font-weight: 800; z-index: 10000;
        display: none; align-items: center; gap: 6px; backdrop-filter: blur(8px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3); font-family: 'Nunito', sans-serif;
    `;
    document.body.appendChild(indicator);

    function updateStatus() {
        indicator.style.display = navigator.onLine ? 'none' : 'flex';
    }

    window.addEventListener('online', () => {
        updateStatus();
        if (typeof window.toast !== 'undefined') {
            window.toast.success('Conectado!', 'Sincronizando dados...');
        }
    });

    window.addEventListener('offline', () => {
        updateStatus();
        if (typeof window.toast !== 'undefined') {
            window.toast.warning('Sem Conexão', 'Você está offline. Os dados serão salvos localmente.');
        }
    });

    updateStatus();
});
