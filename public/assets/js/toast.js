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
                // Always add styles first (with check to avoid duplicates)
                if (!document.getElementById('toast-notification-styles')) {
                    this.addStyles();
                }

                if (document.getElementById('toast-container')) {
                    this.container = document.getElementById('toast-container');
                    return;
                }

                // Create container
                this.container = document.createElement('div');
                this.container.id = 'toast-container';
                document.body.appendChild(this.container);
            };

            if (document.body) {
                setup();
            } else {
                document.addEventListener('DOMContentLoaded', setup);
            }
        }

        addStyles() {
            const style = document.createElement('style');
            style.id = 'toast-notification-styles';
            style.textContent = `
            #toast-container {
                position: fixed !important;
                top: 24px !important;
                right: 24px !important;
                z-index: 99999 !important;
                display: flex !important;
                flex-direction: column !important;
                gap: 12px !important;
                width: 360px !important;
                max-width: 90vw !important;
                pointer-events: none !important;
            }

            .toast {
                /* Reset & Base */
                all: initial; 
                display: flex !important;
                flex-direction: column !important;
                position: relative !important;
                pointer-events: auto !important;
                box-sizing: border-box !important;
                width: 100% !important;
                
                /* HUD v3.0 Aesthetic */
                background: rgba(15, 23, 42, 0.95) !important;
                backdrop-filter: blur(16px) !important;
                -webkit-backdrop-filter: blur(16px) !important;
                border: 1px solid rgba(255, 255, 255, 0.1) !important;
                border-left: 4px solid var(--toast-accent, #3b82f6) !important;
                border-radius: 12px !important;
                padding: 16px !important;
                color: #f8fafc !important;
                font-family: 'Inter', system-ui, sans-serif !important;
                
                /* Shadow & Glow */
                box-shadow: 
                    0 10px 25px -5px rgba(0, 0, 0, 0.6),
                    0 0 0 1px rgba(255, 255, 255, 0.05),
                    inset 0 1px 0 rgba(255, 255, 255, 0.1) !important;
                
                /* Animation */
                animation: toastSlideIn 0.4s cubic-bezier(0.2, 0.8, 0.2, 1) !important;
                transition: all 0.3s ease !important;
            }

            .toast * {
                box-sizing: border-box !important;
                font-family: inherit !important;
            }

            .toast:hover {
                transform: translateY(-2px) !important;
                box-shadow: 
                    0 20px 30px -10px rgba(0, 0, 0, 0.7),
                    0 0 0 1px rgba(255, 255, 255, 0.1) !important;
                background: rgba(15, 23, 42, 0.98) !important;
            }

            .toast-header {
                display: flex !important;
                align-items: center !important;
                gap: 12px !important;
                margin-bottom: 8px !important;
            }

            .toast-icon {
                width: 24px !important;
                height: 24px !important;
                min-width: 24px !important;
                color: var(--toast-accent) !important;
                filter: drop-shadow(0 0 8px var(--toast-accent)) !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
            }

            .toast-title {
                flex: 1 !important;
                font-family: 'Nunito', sans-serif !important;
                font-weight: 800 !important;
                font-size: 0.95rem !important;
                text-transform: uppercase !important;
                letter-spacing: 0.05em !important;
                color: #fff !important;
                line-height: 1.2 !important;
            }

            .toast-close {
                background: transparent !important;
                border: none !important;
                color: rgba(255, 255, 255, 0.4) !important;
                cursor: pointer !important;
                padding: 4px !important;
                font-size: 1.2rem !important;
                line-height: 1 !important;
                transition: color 0.2s !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                margin: -4px -4px 0 0 !important;
            }

            .toast-close:hover {
                color: #fff !important;
            }

            .toast-body {
                color: #94a3b8 !important;
                font-size: 0.9rem !important;
                line-height: 1.5 !important;
                padding-left: 0 !important; /* Aligned with edge, not indented */
                white-space: pre-wrap !important;
            }

            /* Buttons Container */
            .toast-actions {
                display: flex !important;
                gap: 12px !important;
                margin-top: 16px !important;
                padding-top: 12px !important;
                border-top: 1px solid rgba(255, 255, 255, 0.05) !important;
            }

            .toast-btn {
                padding: 8px 16px !important;
                border-radius: 6px !important;
                font-size: 0.8rem !important;
                font-weight: 700 !important;
                cursor: pointer !important;
                transition: all 0.2s !important;
                text-transform: uppercase !important;
                letter-spacing: 0.05em !important;
                border: none !important;
            }

            .toast-btn-primary {
                background: var(--toast-accent) !important;
                color: #0f172a !important; /* Contrast text */
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2) !important;
            }

            .toast-btn-primary:hover {
                filter: brightness(1.1) !important;
                transform: translateY(-1px) !important;
            }

            .toast-btn-secondary {
                background: rgba(255, 255, 255, 0.05) !important;
                border: 1px solid rgba(255, 255, 255, 0.1) !important;
                color: #cbd5e1 !important;
            }

            .toast-btn-secondary:hover {
                background: rgba(255, 255, 255, 0.1) !important;
                color: #fff !important;
            }

            /* Animations */
            @keyframes toastSlideIn {
                from { transform: translateX(100%) scale(0.95); opacity: 0; }
                to { transform: translateX(0) scale(1); opacity: 1; }
            }

            @keyframes toastSlideOut {
                to { transform: translateX(100%) scale(0.95); opacity: 0; }
            }

            .toast.toast-exit {
                animation: toastSlideOut 0.3s forwards !important;
            }

            /* Variants */
            .toast-success { --toast-accent: #10b981; }
            .toast-error { --toast-accent: #ef4444; }
            .toast-warning { --toast-accent: #f59e0b; }
            .toast-info { --toast-accent: #3b82f6; }
            
            .toast-critical { 
                --toast-accent: #ef4444; 
                border: 1px solid rgba(239, 68, 68, 0.3) !important;
                box-shadow: 0 0 20px rgba(239, 68, 68, 0.1) !important;
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
                priority = 'normal',
                icon = null
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

            // Enhanced Icon Logic
            let iconHtml = '';

            const icons = {
                success: `<svg class="toast-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`,
                error: `<svg class="toast-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`,
                warning: `<svg class="toast-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>`,
                info: `<svg class="toast-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`,
                question: `<svg class="toast-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`
            };

            if (icon) {
                if (icon.startsWith('fa-')) {
                    // Backwards compatibility for FA classes (try to map known ones)
                    if (icon.includes('check')) iconHtml = icons.success;
                    else if (icon.includes('xmark') || icon.includes('times')) iconHtml = icons.error;
                    else if (icon.includes('exclamation') || icon.includes('warning')) iconHtml = icons.warning;
                    else if (icon.includes('question')) iconHtml = icons.question;
                    else iconHtml = `<i class="${icon} toast-icon"></i>`; // Fallback to class
                } else if (icon.includes('<svg')) {
                    iconHtml = icon; // Already SVG
                } else {
                    iconHtml = `<span class="toast-icon">${icon}</span>`; // Text/Emoji
                }
            } else {
                iconHtml = icons[type] || icons.info;
            }

            toast.innerHTML = `
            <div class="toast-header">
                ${iconHtml}
                <div class="toast-title">${this.escapeHtml(title)}</div>
                <button class="toast-close" aria-label="Close">
                    <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
            <div class="toast-body">${this.escapeHtml(message)}</div>
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
         * Show a confirmation toast
         * Returns a Promise that resolves to true (confirm) or false (cancel)
         */
        confirm(title, message, options = {}) {
            return new Promise((resolve) => {
                const toast = this.show({
                    ...options,
                    title,
                    message,
                    type: options.type || 'warning',
                    duration: 0, // Never auto-dismiss
                    priority: 'critical',
                    icon: options.icon || 'fa-solid fa-circle-question'
                });

                // Add footer with buttons
                const content = toast; // Append directly to toast container (flex column)
                const footer = document.createElement('div');
                footer.className = 'toast-actions';

                const btnConfirm = document.createElement('button');
                btnConfirm.className = 'toast-btn toast-btn-primary';
                btnConfirm.innerText = options.confirmText || 'Confirmar';
                if (options.confirmBg) btnConfirm.style.background = options.confirmBg;

                const btnCancel = document.createElement('button');
                btnCancel.className = 'toast-btn toast-btn-secondary';
                btnCancel.innerText = options.cancelText || 'Cancelar';

                btnConfirm.onclick = (e) => {
                    e.stopPropagation();
                    this.dismiss(toast);
                    resolve(true);
                };

                btnCancel.onclick = (e) => {
                    e.stopPropagation();
                    this.dismiss(toast);
                    resolve(false);
                };

                footer.appendChild(btnConfirm);
                footer.appendChild(btnCancel);
                content.appendChild(footer);
            });
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
