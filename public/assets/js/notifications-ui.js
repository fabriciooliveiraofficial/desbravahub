/**
 * Notification UI Integration
 * Handles badge polling and live updates across member/admin panels.
 */
(function () {
    const BADGE_POLL_INTERVAL = 30000; // 30 seconds
    let apiEndpoint = null;
    let pollTimer = null;

    function updateBadge(count) {
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
            // Force poll request to update counts
            htmx.ajax('GET', location.pathname + '?poll=1', {
                target: 'body',
                swap: 'none'
            });

            // If on Admin Dashboard, also refresh the notification board
            const board = document.getElementById('notification-board-container');
            if (board && typeof htmx !== 'undefined') {
                htmx.ajax('GET', window.location.href, {
                    select: '#notification-board-container',
                    target: '#notification-board-container',
                    swap: 'outerHTML'
                });
            }
            badge.style.display = 'flex';
        } else if (badge) {
            badge.style.display = 'none';
        }
    }

    async function pollBadge() {
        if (!apiEndpoint) return;
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
        apiEndpoint = `/${slug}/api/notifications`;

        // Initial fetch + interval
        pollBadge();
        if (pollTimer) clearInterval(pollTimer);
        pollTimer = setInterval(pollBadge, BADGE_POLL_INTERVAL);

        // Listen for real-time updates from SW (via PushNotifications bridge)
        document.addEventListener('push-notification-received', () => {
            // Delay slightly to allow DB update
            setTimeout(pollBadge, 1000);
        });
    };
})();
