/**
 * DesbravaHub Toast Notification System
 * 
 * Provides non-blocking toast notifications with:
 * - Auto-dismiss
 * - Stack management
 * - Click actions
 * - Priority styling
 */

// Guard clause to prevent redeclaration when loaded multiple times
if (typeof window.ToastNotification !== 'undefined') {
    console.log('[Toast] Already loaded, skipping redeclaration.');
} else {
    window.ToastNotification = class ToastNotification {
        constructor(options = {}) {
            this.container = null;
            this.pollInterval = options.pollInterval || 30000; // 30 seconds
            this.pollTimer = null;
            this.apiUrl = options.apiUrl || '';
            this.maxToasts = options.maxToasts || 5;
            this.defaultDuration = options.defaultDuration || 5000;

            this.init();
        }

        init() {
            const setup = () => {
                if (document.getElementById('toast-container')) {
                    this.container = document.getElementById('toast-container');
                    return;
                }

                // Create container
                this.container = document.createElement('div');
                this.container.id = 'toast-container';
                this.container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-width: 400px;
        `;
                document.body.appendChild(this.container);

                // Add styles
                this.addStyles();
            };

            if (document.body) {
                setup();
            } else {
                document.addEventListener('DOMContentLoaded', setup);
            }
        }

        addStyles() {
            const style = document.createElement('style');
            style.textContent = `
            .toast {
                background: rgba(26, 26, 46, 0.95);
                border: 1px solid rgba(255, 255, 255, 0.1);
                border-radius: 12px;
                padding: 16px 20px;
                color: #e0e0e0;
                backdrop-filter: blur(10px);
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
                display: flex;
                align-items: flex-start;
                gap: 12px;
                animation: toastSlideIn 0.3s ease;
                cursor: pointer;
                transition: transform 0.2s, opacity 0.2s;
            }
            
            .toast:hover {
                transform: translateX(-5px);
            }
            
            .toast.toast-exit {
                animation: toastSlideOut 0.3s ease forwards;
            }
            
            .toast-icon {
                font-size: 20px;
                flex-shrink: 0;
            }
            
            .toast-content {
                flex: 1;
                min-width: 0;
            }
            
            .toast-title {
                font-weight: 600;
                margin-bottom: 4px;
                color: #fff;
            }
            
            .toast-message {
                font-size: 14px;
                color: #aaa;
                line-height: 1.4;
            }
            
            .toast-close {
                background: none;
                border: none;
                color: #666;
                cursor: pointer;
                padding: 0;
                font-size: 18px;
                line-height: 1;
                transition: color 0.2s;
            }
            
            .toast-close:hover {
                color: #fff;
            }
            
            .toast-success { border-left: 4px solid #00ff88; }
            .toast-success .toast-icon { color: #00ff88; }
            
            .toast-error { border-left: 4px solid #ff6b6b; }
            .toast-error .toast-icon { color: #ff6b6b; }
            
            .toast-warning { border-left: 4px solid #f7b32b; }
            .toast-warning .toast-icon { color: #f7b32b; }
            
            .toast-info { border-left: 4px solid #00d9ff; }
            .toast-info .toast-icon { color: #00d9ff; }
            
            .toast-critical {
                border-left: 4px solid #ff6b6b;
                background: rgba(255, 100, 100, 0.1);
            }
            
            @keyframes toastSlideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            @keyframes toastSlideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }
        `;
            document.head.appendChild(style);
        }

        /**
         * Show a toast notification
         */
        show(options) {
            const {
                title = '',
                message = '',
                type = 'info',
                duration = this.defaultDuration,
                onClick = null,
                priority = 'normal'
            } = options;

            // Limit number of toasts
            while (this.container.children.length >= this.maxToasts) {
                this.dismiss(this.container.firstChild);
            }

            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            if (priority === 'critical') {
                toast.classList.add('toast-critical');
            }

            const icons = {
                success: '✓',
                error: '✕',
                warning: '⚠',
                info: 'ℹ',
            };

            toast.innerHTML = `
            <span class="toast-icon">${icons[type] || icons.info}</span>
            <div class="toast-content">
                <div class="toast-title">${this.escapeHtml(title)}</div>
                <div class="toast-message">${this.escapeHtml(message)}</div>
            </div>
            <button class="toast-close" aria-label="Close">×</button>
        `;

            // Close button
            toast.querySelector('.toast-close').addEventListener('click', (e) => {
                e.stopPropagation();
                this.dismiss(toast);
            });

            // Click action
            if (onClick) {
                toast.addEventListener('click', () => {
                    onClick();
                    this.dismiss(toast);
                });
            }

            this.container.appendChild(toast);

            // Auto dismiss
            if (duration > 0) {
                setTimeout(() => this.dismiss(toast), duration);
            }

            return toast;
        }

        /**
         * Dismiss a toast
         */
        dismiss(toast) {
            if (!toast || !toast.parentNode) return;

            toast.classList.add('toast-exit');
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 300);
        }

        /**
         * Convenience methods
         */
        success(title, message, options = {}) {
            return this.show({ ...options, title, message, type: 'success' });
        }

        error(title, message, options = {}) {
            return this.show({ ...options, title, message, type: 'error' });
        }

        warning(title, message, options = {}) {
            return this.show({ ...options, title, message, type: 'warning' });
        }

        info(title, message, options = {}) {
            return this.show({ ...options, title, message, type: 'info' });
        }

        /**
         * Start polling for new notifications
         */
        startPolling() {
            if (!this.apiUrl) return;

            this.poll();
            this.pollTimer = setInterval(() => this.poll(), this.pollInterval);
        }

        /**
         * Stop polling
         */
        stopPolling() {
            if (this.pollTimer) {
                clearInterval(this.pollTimer);
                this.pollTimer = null;
            }
        }

        /**
         * Poll for new notifications
         */
        async poll() {
            try {
                const response = await fetch(`${this.apiUrl}/unread`, {
                    credentials: 'include',
                    headers: { 'Accept': 'application/json' }
                });

                if (!response.ok) return;

                const data = await response.json();

                if (data.notifications && data.notifications.length > 0) {
                    // Show new notifications as toasts
                    data.notifications.forEach(notification => {
                        this.showNotification(notification);
                    });
                }
            } catch (err) {
                console.error('Toast polling error:', err);
            }
        }

        /**
         * Show notification from API
         */
        showNotification(notification) {
            const typeMap = {
                'achievement': 'success',
                'activity': 'info',
                'event': 'info',
                'system': 'warning',
                'broadcast': 'info',
            };

            const type = typeMap[notification.type] || 'info';
            const data = notification.data ? JSON.parse(notification.data) : {};

            this.show({
                title: notification.title,
                message: notification.message,
                type: type,
                priority: notification.priority,
                duration: notification.priority === 'critical' ? 0 : this.defaultDuration,
                onClick: data.link ? () => { window.location.href = data.link; } : null,
            });

            // Mark as read
            if (this.apiUrl && notification.id) {
                fetch(`${this.apiUrl}/${notification.id}/read`, {
                    method: 'POST',
                    credentials: 'include',
                }).catch(() => { });
            }
        }

        /**
         * Escape HTML to prevent XSS
         */
        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    }

    // Export for use
    if (typeof module !== 'undefined' && module.exports) {
        module.exports = ToastNotification;
    }
} // End of guard clause

// Ensure global instance and helper exist even if script is re-run
(function () {
    // Assign ToastNotification to window if it's not already there
    if (typeof window.ToastNotification === 'undefined') {
        window.ToastNotification = ToastNotification;
    }

    // Create global instance if it doesn't exist
    if (!window.toast) {
        try {
            window.toast = new window.ToastNotification();
        } catch (e) {
            console.error('[Toast] Failed to create global instance:', e);
        }
    }

    // Create global helper for simple notifications if it doesn't exist
    if (window.toast && !window.showToast) {
        window.showToast = function (message, type = 'info', title = '') {
            if (!title) {
                title = type.charAt(0).toUpperCase() + type.slice(1);
            }
            window.toast.show({ title, message, type });
        };
    }
})();
