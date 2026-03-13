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

    /**
     * URL patterns for background/fire-and-forget requests.
     * A 401 on these must NEVER trigger a window redirect — they are not user-initiated.
     */
    const BACKGROUND_PATTERNS = [
        /\/api\/notifications(\/|$|\?)/,
        /\/api\/push(\/|$|\?)/,
        /\/unread$/,
        /\/api\/badges/,
    ];

    function isBackgroundRequest(url, options) {
        if (options?.headers?.['X-Background-Request']) return true;
        return BACKGROUND_PATTERNS.some(p => p.test(url));
    }

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
         * @param {string|number} renderedUserId The ID of the authenticated user
         * @param {string} serverToken The current session token (hydration from cookies)
         */
        init(renderedUserId, serverToken) {
            // --- Hydration Phase: Convert Cookie Session -> Tab Session ---
            if (serverToken && !SessionGuard.getToken()) {
                console.log('[SessionGuard] Hydrating new tab from server token...');
                SessionGuard.storeSession(serverToken, renderedUserId);
            }

            // --- HTMX: Inject Authorization header on every request ---
            document.body.addEventListener('htmx:configRequest', function (event) {
                // Only send token to internal API/routes
                const url = event.detail.path || '';
                const isInternal = !url.startsWith('http') || url.includes(window.location.host);

                const token = SessionGuard.getToken();
                if (token && isInternal) {
                    event.detail.headers['Authorization'] = 'Bearer ' + token;
                }
            });

            // --- Fetch wrapper: Inject auth headers + AJAX identity on internal calls ---
            const originalFetch = window.fetch;
            window.fetch = function (url, options) {
                options = options || {};
                const token = SessionGuard.getToken();

                // Detection: Is this an external request? (e.g. Iconify API)
                const urlString = (typeof url === 'string') ? url : (url.url || '');
                const isInternal = !urlString.startsWith('http') || urlString.includes(window.location.host);

                if (isInternal) {
                    options.headers = options.headers || {};

                    // 1. Always send session cookie so PHP $_SESSION stays in sync
                    if (!options.credentials) {
                        options.credentials = 'same-origin';
                    }

                    // 2. Identify as AJAX so backend returns JSON errors instead of redirects
                    if (!options.headers['X-Requested-With']) {
                        options.headers['X-Requested-With'] = 'XMLHttpRequest';
                    }
                    if (!options.headers['Accept']) {
                        options.headers['Accept'] = 'application/json';
                    }

                    // 3. Bearer token (don't overwrite if already set)
                    if (token && !options.headers['Authorization']) {
                        options.headers['Authorization'] = 'Bearer ' + token;
                    }
                }
                // Intercept 401 responses on non-background requests and redirect to login
                return originalFetch.call(this, url, options).then(response => {
                    if (response.status === 401 && isInternal && !isBackgroundRequest(urlString, options)) {
                        response.clone().json().then(json => {
                            if (json.redirect) {
                                SessionGuard.clearSession();
                                window.location.href = json.redirect;
                            }
                        }).catch(() => { /* non-JSON 401, ignore */ });
                    }
                    return response;
                });
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
                    // Ignore 401s from background/non-navigational HTMX requests
                    const requestUrl = event.detail.requestConfig?.path || event.detail.pathInfo?.requestPath || '';
                    if (isBackgroundRequest(requestUrl, {})) return;

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
