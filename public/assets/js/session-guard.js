/**
 * Session Guard - Per-Tab Session Isolation
 * 
 * Uses sessionStorage (per-tab) to ensure each browser tab
 * maintains its own independent session, even on the same domain.
 * 
 * How it works:
 * 1. On login, the token is stored in sessionStorage (per-tab)
 * 2. Every HTMX/fetch request sends the token via Authorization header
 * 3. The server prioritizes the Authorization header over cookies
 * 4. On hard-refresh, if the cookie user doesn't match, redirect to login
 */
(function () {
    'use strict';

    const STORAGE_KEY_TOKEN = 'dh_auth_token';
    const STORAGE_KEY_USER_ID = 'dh_user_id';

    const SessionGuard = {
        /**
         * Store session credentials after login
         */
        storeSession(token, userId) {
            try {
                sessionStorage.setItem(STORAGE_KEY_TOKEN, token);
                sessionStorage.setItem(STORAGE_KEY_USER_ID, String(userId));
            } catch (e) {
                console.warn('[SessionGuard] sessionStorage not available:', e);
            }
        },

        /**
         * Clear session credentials (logout)
         */
        clearSession() {
            try {
                sessionStorage.removeItem(STORAGE_KEY_TOKEN);
                sessionStorage.removeItem(STORAGE_KEY_USER_ID);
            } catch (e) {
                // Ignore
            }
        },

        /**
         * Get the stored auth token for this tab
         */
        getToken() {
            try {
                return sessionStorage.getItem(STORAGE_KEY_TOKEN);
            } catch (e) {
                return null;
            }
        },

        /**
         * Get the stored user ID for this tab
         */
        getUserId() {
            try {
                return sessionStorage.getItem(STORAGE_KEY_USER_ID);
            } catch (e) {
                return null;
            }
        },

        /**
         * Initialize: attach HTMX header injection + session mismatch detection
         */
        init(renderedUserId) {
            // --- HTMX: Inject Authorization header on every request ---
            document.body.addEventListener('htmx:configRequest', function (event) {
                const token = SessionGuard.getToken();
                if (token) {
                    event.detail.headers['Authorization'] = 'Bearer ' + token;
                }
            });

            // --- Fetch wrapper: Inject Authorization header on fetch calls ---
            const originalFetch = window.fetch;
            window.fetch = function (url, options) {
                options = options || {};
                const token = SessionGuard.getToken();
                if (token) {
                    options.headers = options.headers || {};
                    // Don't overwrite if already set
                    if (!options.headers['Authorization']) {
                        options.headers['Authorization'] = 'Bearer ' + token;
                    }
                }
                return originalFetch.call(this, url, options);
            };

            // --- Session Mismatch Detection (for hard-refresh) ---
            if (renderedUserId) {
                const storedUserId = SessionGuard.getUserId();

                if (storedUserId && String(storedUserId) !== String(renderedUserId)) {
                    // The cookie resolved to a different user than this tab expects
                    console.warn('[SessionGuard] Session mismatch detected.',
                        'Tab expects userId:', storedUserId,
                        'Server rendered userId:', renderedUserId);

                    // Clear the stale tab session and redirect to login
                    SessionGuard.clearSession();

                    // Extract tenant slug from URL
                    const pathParts = window.location.pathname.split('/').filter(Boolean);
                    const tenantSlug = pathParts[0] || '';

                    if (tenantSlug) {
                        window.location.href = '/' + tenantSlug + '/login';
                        return; // Stop execution
                    }
                }
            }

            // --- Handle HTMX 401 responses (session expired or invalid) ---
            document.body.addEventListener('htmx:responseError', function (event) {
                if (event.detail.xhr && event.detail.xhr.status === 401) {
                    console.warn('[SessionGuard] 401 received, clearing session.');
                    SessionGuard.clearSession();

                    try {
                        const response = JSON.parse(event.detail.xhr.responseText);
                        if (response.redirect) {
                            window.location.href = response.redirect;
                            return;
                        }
                    } catch (e) { /* ignore */ }

                    // Fallback: extract tenant from URL
                    const pathParts = window.location.pathname.split('/').filter(Boolean);
                    const tenantSlug = pathParts[0] || '';
                    if (tenantSlug) {
                        window.location.href = '/' + tenantSlug + '/login';
                    }
                }
            });

            console.log('[SessionGuard] Initialized. Token present:', !!SessionGuard.getToken());
        }
    };

    // Export globally
    window.SessionGuard = SessionGuard;
})();
