/**
 * BLACK BOX DIAGNOSTIC — NC-DEBUG-PRO
 * 
 * Captures all notification lifecycle events and errors.
 * Sends logs to /api/debug/log.
 */
(function() {
    const DEBUG_ID = 'NC_DEBUG_' + Date.now();
    const TENANT_SLUG = window.location.pathname.split('/')[1] || '';
    const LOG_ENDPOINT = `/${TENANT_SLUG}/api/debug/log`;

    console.log(`[NC-DEBUG] Diagnostic Active. Session: ${DEBUG_ID}`);

    function remoteLog(level, category, message, data = null) {
        const payload = {
            session: DEBUG_ID,
            timestamp: new Date().toISOString(),
            url: window.location.href,
            level,
            category,
            message,
            data
        };

        // Fail silent but log to console locally
        console.log(`[NC-PRO-LOG][${category}] ${message}`, data || '');

        fetch(LOG_ENDPOINT, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Background-Request': '1' },
            body: JSON.stringify(payload)
        }).catch(() => {});
    }

    // 1. Error Capture
    window.onerror = function(msg, url, line, col, error) {
        remoteLog('ERROR', 'RUNTIME', msg, { url, line, col, stack: error?.stack });
    };
    window.onunhandledrejection = function(event) {
        remoteLog('ERROR', 'PROMISE', event.reason?.message || 'Unhandle Rejection', { reason: event.reason });
    };

    // 2. Fetch Hijack (Specifically for notification calls)
    const originalFetch = window.fetch;
    window.fetch = async function(...args) {
        const url = args[0] || '';
        const isNotifCall = typeof url === 'string' && (url.includes('api/notifications') || url.includes('api/debug/log'));
        
        if (isNotifCall && !url.includes('api/debug/log')) {
            remoteLog('INFO', 'NETWORK_REQ', `Calling: ${url}`, { options: args[1] });
        }

        try {
            const response = await originalFetch.apply(this, args);
            
            if (isNotifCall && !url.includes('api/debug/log')) {
                const clone = response.clone();
                clone.json().then(data => {
                    remoteLog('SUCCESS', 'NETWORK_RES', `Responded: ${url}`, { 
                        status: response.status,
                        data 
                    });
                }).catch(() => {
                    remoteLog('WARN', 'NETWORK_RES_ERR', `Could not parse JSON from: ${url}`, { status: response.status });
                });
            }
            return response;
        } catch (err) {
            if (isNotifCall) {
                remoteLog('ERROR', 'NETWORK_FAIL', `Fetch failed for: ${url}`, { error: err.message });
            }
            throw err;
        }
    };

    // 3. Monitor DopamineDrop Initialization
    let checkCount = 0;
    const interval = setInterval(() => {
        checkCount++;
        const ddExists = typeof window.DopamineDrop !== 'undefined';
        const ncExists = typeof window.notificationCenter !== 'undefined';
        
        if (ddExists && ncExists) {
            remoteLog('INFO', 'INIT', 'DopamineDrop and NotificationCenter are READY');
            
            // Wrap DopamineDrop.trigger to capture calls
            const originalTrigger = window.DopamineDrop.trigger;
            window.DopamineDrop.trigger = function(opts) {
                remoteLog('ACTION', 'CELEBRATION_TRIGGER', `DopamineDrop.trigger called: ${opts.title}`, opts);
                return originalTrigger.apply(this, arguments);
            };

            clearInterval(interval);
        } else if (checkCount > 20) { // Limit to 10 seconds
            remoteLog('WARN', 'INIT_TIMEOUT', `Initialization stalled. DD: ${ddExists}, NC: ${ncExists}`);
            clearInterval(interval);
        }
    }, 500);

    // 4. Initial State
    remoteLog('INFO', 'PAGE_LOAD', 'Diagnostic script loaded');

})();
