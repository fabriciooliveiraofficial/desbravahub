/**
 * Notification UI Integration
 * Handles badge polling and live updates across member/admin panels.
 */
(function () {
    const BADGE_POLL_INTERVAL = 60000; // 60 seconds (was 30s — halved DB load)
    let apiEndpoint = null;
    let pollTimer = null;

    // Returns true if NotificationCenter is already managing the badge —
    // in that case this module skips badge updates to avoid race conditions.
    function ncActive() {
        return !!(window.notificationCenter && window.notificationCenter._btn);
    }

    function updateBadge(count) {
        // Defer to NotificationCenter when it's active
        if (ncActive()) return;

        const bellBtn = document.getElementById('notificationBtn');
        if (!bellBtn) return;

        let badge = bellBtn.querySelector('.hud-notification-badge');

        if (count > 0) {
            if (!badge) {
                const wrapper = bellBtn.querySelector('.bell-wrapper') || bellBtn;
                badge = document.createElement('span');
                badge.className = 'hud-notification-badge';
                wrapper.appendChild(badge);
            }
            badge.textContent = count > 9 ? '9+' : count;
            badge.style.display = 'flex';
        } else if (badge) {
            badge.style.display = 'none';
        }

        // Refresh the notification board panel if visible on the page
        const board = document.getElementById('notification-board-container');
        if (board && typeof htmx !== 'undefined') {
            htmx.ajax('GET', window.location.href, {
                select: '#notification-board-container',
                target: '#notification-board-container',
                swap: 'outerHTML'
            });
        }
    }

    async function pollBadge() {
        if (!apiEndpoint) return;
        // Skip when NotificationCenter is active — it handles polling itself
        if (ncActive()) return;
        // Skip polling while the tab is hidden — saves DB load for background tabs
        if (document.visibilityState === 'hidden') return;
        try {
            const res = await fetch(apiEndpoint, {
                credentials: 'include',
                headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) return;
            const data = await res.json();
            updateBadge(data.unread_count || 0);
        } catch (e) { /* silent */ }
    }

    window.initNotificationUI = function (slug) {
        // If NotificationCenter is already active, skip — it owns the badge and polling
        if (ncActive()) return;

        apiEndpoint = `/${slug}/api/notifications`;

        // Initial fetch + interval
        pollBadge();
        if (pollTimer) clearInterval(pollTimer);
        pollTimer = setInterval(pollBadge, BADGE_POLL_INTERVAL);

        // Resume polling immediately when tab becomes visible again
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') pollBadge();
        });

        // Listen for real-time updates from SW (via PushNotifications bridge)
        document.addEventListener('push-notification-received', () => {
            setTimeout(pollBadge, 1000);
        });
    };
})();
