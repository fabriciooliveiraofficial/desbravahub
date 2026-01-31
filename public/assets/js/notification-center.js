/**
 * DesbravaHub Notification Center
 * 
 * Dropdown notification center component.
 */

class NotificationCenter {
    constructor(options = {}) {
        this.apiUrl = options.apiUrl || '';
        this.container = null;
        this.button = null;
        this.dropdown = null;
        this.badge = null;
        this.notifications = [];
        this.isOpen = false;

        if (options.buttonId) {
            this.init(options.buttonId);
        }
    }

    init(buttonId) {
        this.button = document.getElementById(buttonId);
        if (!this.button) return;

        this.createDropdown();
        this.attachEvents();
        this.loadNotifications();
    }

    createDropdown() {
        // Add badge
        this.badge = document.createElement('span');
        this.badge.className = 'notification-badge';
        this.badge.style.cssText = `
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ff6b6b;
            color: white;
            font-size: 11px;
            font-weight: 600;
            padding: 2px 6px;
            border-radius: 10px;
            display: none;
        `;
        this.button.style.position = 'relative';
        this.button.appendChild(this.badge);

        // Create dropdown
        this.dropdown = document.createElement('div');
        this.dropdown.className = 'notification-dropdown';
        this.dropdown.style.cssText = `
            position: absolute;
            top: 100%;
            right: 0;
            width: 360px;
            max-height: 480px;
            background: rgba(26, 26, 46, 0.98);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
            overflow: hidden;
            display: none;
            z-index: 9998;
        `;

        this.dropdown.innerHTML = `
            <div class="notification-header" style="
                padding: 16px;
                border-bottom: 1px solid rgba(255,255,255,0.1);
                display: flex;
                justify-content: space-between;
                align-items: center;
            ">
                <h3 style="margin: 0; font-size: 16px; color: #fff;">Notificações</h3>
                <button class="mark-all-read" style="
                    background: none;
                    border: none;
                    color: #00d9ff;
                    cursor: pointer;
                    font-size: 13px;
                ">Marcar todas como lidas</button>
            </div>
            <div class="notification-list" style="
                max-height: 400px;
                overflow-y: auto;
            "></div>
            <div class="notification-empty" style="
                padding: 40px 20px;
                text-align: center;
                color: #666;
                display: none;
            ">
                <p>Nenhuma notificação</p>
            </div>
        `;

        this.button.parentElement.style.position = 'relative';
        this.button.parentElement.appendChild(this.dropdown);
    }

    attachEvents() {
        // Toggle dropdown
        this.button.addEventListener('click', (e) => {
            e.stopPropagation();
            this.toggle();
        });

        // Close on outside click
        document.addEventListener('click', (e) => {
            if (this.isOpen && !this.dropdown.contains(e.target)) {
                this.close();
            }
        });

        // Mark all as read
        this.dropdown.querySelector('.mark-all-read').addEventListener('click', () => {
            this.markAllRead();
        });
    }

    toggle() {
        this.isOpen ? this.close() : this.open();
    }

    open() {
        this.dropdown.style.display = 'block';
        this.isOpen = true;
        this.loadNotifications();
    }

    close() {
        this.dropdown.style.display = 'none';
        this.isOpen = false;
    }

    async loadNotifications() {
        if (!this.apiUrl) return;

        try {
            const response = await fetch(this.apiUrl, {
                credentials: 'include',
                headers: { 'Accept': 'application/json' }
            });

            if (!response.ok) return;

            const data = await response.json();
            this.notifications = data.notifications || [];
            this.updateBadge(data.unread_count || 0);
            this.render();
        } catch (err) {
            console.error('Failed to load notifications:', err);
        }
    }

    updateBadge(count) {
        if (count > 0) {
            this.badge.textContent = count > 99 ? '99+' : count;
            this.badge.style.display = 'block';
        } else {
            this.badge.style.display = 'none';
        }
    }

    render() {
        const list = this.dropdown.querySelector('.notification-list');
        const empty = this.dropdown.querySelector('.notification-empty');

        if (this.notifications.length === 0) {
            list.style.display = 'none';
            empty.style.display = 'block';
            return;
        }

        list.style.display = 'block';
        empty.style.display = 'none';

        list.innerHTML = this.notifications.map(n => this.renderItem(n)).join('');

        // Attach click handlers
        list.querySelectorAll('.notification-item').forEach((item, index) => {
            item.addEventListener('click', () => {
                this.handleClick(this.notifications[index]);
            });
        });
    }

    renderItem(notification) {
        const isUnread = !notification.read_at;
        const data = notification.data ? JSON.parse(notification.data) : {};
        const time = this.formatTime(notification.created_at);

        return `
            <div class="notification-item" style="
                padding: 14px 16px;
                border-bottom: 1px solid rgba(255,255,255,0.05);
                cursor: pointer;
                transition: background 0.2s;
                ${isUnread ? 'background: rgba(0, 217, 255, 0.05);' : ''}
            " onmouseover="this.style.background='rgba(255,255,255,0.05)'" 
               onmouseout="this.style.background='${isUnread ? 'rgba(0, 217, 255, 0.05)' : 'transparent'}'">
                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                    <span style="font-weight: 600; color: #fff; font-size: 14px;">
                        ${this.escapeHtml(notification.title)}
                    </span>
                    ${isUnread ? '<span style="width: 8px; height: 8px; background: #00d9ff; border-radius: 50%;"></span>' : ''}
                </div>
                <p style="margin: 0; color: #888; font-size: 13px; line-height: 1.4;">
                    ${this.escapeHtml(notification.message)}
                </p>
                <span style="font-size: 11px; color: #555; margin-top: 6px; display: block;">
                    ${time}
                </span>
            </div>
        `;
    }

    handleClick(notification) {
        // Mark as read
        if (!notification.read_at) {
            this.markAsRead(notification.id);
        }

        // Navigate if has link
        const data = notification.data ? JSON.parse(notification.data) : {};
        if (data.link) {
            window.location.href = data.link;
        }
    }

    async markAsRead(id) {
        try {
            await fetch(`${this.apiUrl}/${id}/read`, {
                method: 'POST',
                credentials: 'include'
            });
            this.loadNotifications();
        } catch (err) {
            console.error('Failed to mark as read:', err);
        }
    }

    async markAllRead() {
        try {
            await fetch(`${this.apiUrl}/read-all`, {
                method: 'POST',
                credentials: 'include'
            });
            this.loadNotifications();
        } catch (err) {
            console.error('Failed to mark all as read:', err);
        }
    }

    formatTime(dateStr) {
        const date = new Date(dateStr);
        const now = new Date();
        const diff = (now - date) / 1000;

        if (diff < 60) return 'Agora';
        if (diff < 3600) return `${Math.floor(diff / 60)}m atrás`;
        if (diff < 86400) return `${Math.floor(diff / 3600)}h atrás`;
        if (diff < 604800) return `${Math.floor(diff / 86400)}d atrás`;

        return date.toLocaleDateString('pt-BR');
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Export
if (typeof module !== 'undefined' && module.exports) {
    module.exports = NotificationCenter;
}
