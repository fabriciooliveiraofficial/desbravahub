/**
 * DesbravaHub — Notification Center
 *
 * Glass-morphism popover with:
 *  - On-demand fetch when opened (latest 10 notifications)
 *  - Silent 30s background polling for unread badge count
 *  - Sound plays ONLY when unread count increases
 *  - Optimistic mark-as-read (single + all)
 *  - Relative timestamps in pt-BR
 */

// Guard: prevent "Identifier already declared" on HTMX re-navigation
if (typeof window.NotificationCenter === 'undefined') {

window.NotificationCenter = class NotificationCenter {
    /**
     * @param {Object}  opts
     * @param {string}  opts.buttonId      - ID do botão da sineta
     * @param {string}  opts.badgeId       - ID do span do badge
     * @param {string}  opts.soundId       - ID do elemento <audio>
     * @param {string}  opts.apiUrl        - ex: /slug/api/notifications
     * @param {string}  opts.unreadUrl     - ex: /slug/api/notifications/unread
     * @param {string}  opts.readAllUrl    - ex: /slug/api/notifications/read-all
     * @param {string}  opts.allNotifUrl   - ex: /slug/notificacoes
     * @param {number}  opts.pollInterval  - ms entre polls silenciosos (default 30000)
     * @param {number}  opts.initialCount  - contagem inicial vinda do PHP
     */
    constructor(opts = {}) {
        this._api        = opts.apiUrl      || '';
        this._unreadUrl  = opts.unreadUrl   || '';
        this._readAllUrl = opts.readAllUrl  || '';
        this._allUrl     = opts.allNotifUrl || '#';
        this._interval   = opts.pollInterval || 30000;

        this._isOpen     = false;
        this._loading    = false;
        this._pollTimer  = null;
        this._lastCount  = opts.initialCount ?? -1;

        this._btn     = null;
        this._badge   = null;
        this._sound   = null;
        this._popover = null;

        if (opts.buttonId) {
            this._init(opts.buttonId, opts.badgeId, opts.soundId);
        }
    }

    // ── Bootstrap ──────────────────────────────────────────────────────────────

    _init(buttonId, badgeId, soundId) {
        this._btn   = document.getElementById(buttonId);
        this._badge = document.getElementById(badgeId);
        this._sound = document.getElementById(soundId);
        if (!this._btn) return;

        this._injectStyles();
        this._buildPopover();
        this._attachEvents();
        this._startPolling();
    }

    // ── Popover DOM ────────────────────────────────────────────────────────────

    _buildPopover() {
        this._popover = document.createElement('div');
        this._popover.id        = 'nc-popover';
        this._popover.className = 'nc-popover nc-hidden';
        this._popover.setAttribute('role', 'dialog');
        this._popover.setAttribute('aria-label', 'Central de Notificações');

        this._popover.innerHTML = `
            <div class="nc-header">
                <span class="nc-title">
                    <span class="material-icons-round nc-title-icon">notifications</span>
                    Notificações
                </span>
            </div>
            <div class="nc-body">
                <div class="nc-loading" id="nc-loading">
                    ${[0, 1, 2].map(() => `
                        <div class="nc-skeleton">
                            <div class="nc-sk-icon"></div>
                            <div class="nc-sk-lines">
                                <div class="nc-sk-line nc-sk-title"></div>
                                <div class="nc-sk-line nc-sk-sub"></div>
                            </div>
                        </div>
                    `).join('')}
                </div>
                <div class="nc-list" id="nc-list"></div>
                <div class="nc-empty nc-hidden" id="nc-empty">
                    <span class="material-icons-round nc-empty-icon">notifications_none</span>
                    <p>Tudo em dia! Sem notificações.</p>
                </div>
            </div>
            <div class="nc-footer">
                <a class="nc-view-all" href="${this._allUrl}">
                    Ver todas as notificações
                    <span class="material-icons-round" style="font-size:14px;vertical-align:middle;margin-left:4px;">arrow_forward</span>
                </a>
            </div>
        `;

        const container = this._btn.closest('.bell-container') || this._btn.parentElement;
        container.appendChild(this._popover);

    }

    // ── Events ─────────────────────────────────────────────────────────────────

    _attachEvents() {
        this._btn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this._toggle();
        });

        document.addEventListener('click', (e) => {
            if (this._isOpen && !this._popover.contains(e.target) && e.target !== this._btn) {
                this._close();
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this._isOpen) this._close();
        });
    }

    // ── Open / Close ───────────────────────────────────────────────────────────

    _toggle() { this._isOpen ? this._close() : this._open(); }

    _open() {
        this._popover.classList.remove('nc-hidden');
        // Position with fixed coords so it never clips out of viewport.
        // Compute popWidth in JS (mirrors the CSS clamp) because offsetWidth is 0
        // at this point (element transitions from display:none before nc-visible is set).
        requestAnimationFrame(() => {
            const rect     = this._btn.getBoundingClientRect();
            const margin   = 10;
            const popWidth = Math.round(Math.min(380, Math.max(300, window.innerWidth * 0.9)));
            // Align right edge of popover to right edge of button, then clamp to viewport
            let left = rect.right - popWidth;
            left = Math.max(margin, Math.min(left, window.innerWidth - popWidth - margin));
            this._popover.style.top   = (rect.bottom + margin) + 'px';
            this._popover.style.left  = left + 'px';
            this._popover.style.width = popWidth + 'px';
            this._popover.classList.add('nc-visible');
        });
        this._isOpen = true;
        this._btn.setAttribute('aria-expanded', 'true');
        this._loadNotifications();
    }

    _close() {
        this._popover.classList.remove('nc-visible');
        this._popover.addEventListener('transitionend', () => {
            if (!this._isOpen) this._popover.classList.add('nc-hidden');
        }, { once: true });
        this._isOpen = false;
        this._btn.setAttribute('aria-expanded', 'false');
    }

    // ── Fetch Notifications ────────────────────────────────────────────────────

    async _loadNotifications() {
        if (!this._api || this._loading) return;
        this._loading = true;
        this._showLoading(true);

        try {
            const res = await fetch(`${this._api}?limit=10`, {
                credentials: 'include',
                headers: { 'Accept': 'application/json', 'X-Background-Request': '1' },
            });
            if (!res.ok) return;

            const data = await res.json();
            this._updateBadge(data.unread_count || 0);
            this._lastCount = data.unread_count || 0;
            this._render(data.notifications || []);
        } catch (_) {
            // Silent fail
        } finally {
            this._loading = false;
            this._showLoading(false);
        }
    }

    // ── Render ─────────────────────────────────────────────────────────────────

    _render(notifications) {
        const list  = document.getElementById('nc-list');
        const empty = document.getElementById('nc-empty');
        if (!list || !empty) return;

        if (notifications.length === 0) {
            list.innerHTML = '';
            list.classList.add('nc-hidden');
            empty.classList.remove('nc-hidden');
            return;
        }

        list.classList.remove('nc-hidden');
        empty.classList.add('nc-hidden');
        list.innerHTML = notifications.map(n => this._renderItem(n)).join('');

        list.querySelectorAll('.nc-item').forEach(el => {
            el.addEventListener('click', () => this._handleItemClick(el));
        });
    }

    _renderItem(n) {
        const isUnread = !n.read_at;
        const data     = this._parseData(n.data);
        const url      = data.link || data.url || '';
        const icon     = this._typeIcon(n.type);
        const time     = this._formatTime(n.created_at);

        return `
            <div class="nc-item${isUnread ? ' nc-item--unread' : ''}"
                 data-id="${n.id}"
                 data-url="${this._esc(url)}"
                 ${n.read_at ? 'data-read="1"' : ''}
                 role="button" tabindex="0">
                <div class="nc-item-icon${isUnread ? ' nc-item-icon--unread' : ''}">
                    <span class="material-icons-round">${icon}</span>
                </div>
                <div class="nc-item-body">
                    <div class="nc-item-hd">
                        <span class="nc-item-title">${this._esc(n.title)}</span>
                        ${isUnread ? '<span class="nc-dot"></span>' : ''}
                    </div>
                    <p class="nc-item-msg">${this._linkify(this._esc(n.message))}</p>
                    <span class="nc-item-time">${time}</span>
                </div>
            </div>
        `;
    }

    _handleItemClick(el) {
        const id  = el.dataset.id;
        const url = el.dataset.url;
        if (id && !el.dataset.read) this._markAsRead(id, el);
        if (url) window.location.href = url;
    }

    // ── Mark as Read ───────────────────────────────────────────────────────────

    async _markAsRead(id, el) {
        // Optimistic UI
        el.classList.remove('nc-item--unread');
        el.dataset.read = '1';
        el.querySelector('.nc-dot')?.remove();
        el.querySelector('.nc-item-icon')?.classList.remove('nc-item-icon--unread');

        const cur = this._badgeCount();
        if (cur > 0) this._updateBadge(cur - 1);

        try {
            await fetch(`${this._api}/${id}/read`, {
                method: 'POST',
                credentials: 'include',
                headers: { 'X-Background-Request': '1' },
            });
        } catch (_) {}
    }

    async _markAllRead() {
        // Optimistic UI — remove unread state instantly
        this._popover.querySelectorAll('.nc-item--unread').forEach(el => {
            el.classList.remove('nc-item--unread');
            el.dataset.read = '1';
            el.querySelector('.nc-dot')?.remove();
            el.querySelector('.nc-item-icon')?.classList.remove('nc-item-icon--unread');
        });
        this._updateBadge(0);

        try {
            await fetch(this._readAllUrl, {
                method: 'POST',
                credentials: 'include',
                headers: { 'X-Background-Request': '1' },
            });
            // Refresh list to confirm server state
            this._loadNotifications();
        } catch (_) {}
    }

    // ── Badge ──────────────────────────────────────────────────────────────────

    _updateBadge(count) {
        if (!this._badge) return;
        if (count > 0) {
            this._badge.textContent = count > 9 ? '9+' : count;
            this._badge.style.removeProperty('display');
        } else {
            this._badge.style.display = 'none';
        }
        this._lastCount = count;
    }

    _badgeCount() {
        if (!this._badge || this._badge.style.display === 'none') return 0;
        const raw = this._badge.textContent.trim();
        return raw === '9+' ? 9 : (parseInt(raw, 10) || 0);
    }

    // ── Silent Background Polling ──────────────────────────────────────────────

    _startPolling() {
        if (this._pollTimer) clearInterval(this._pollTimer);
        this._pollTimer = setInterval(() => this._silentPoll(), this._interval);
    }

    async _silentPoll() {
        if (!this._unreadUrl) return;
        try {
            const res = await fetch(this._unreadUrl, {
                credentials: 'include',
                headers: { 'Accept': 'application/json', 'X-Background-Request': '1' },
            });
            if (!res.ok) return;

            const data  = await res.json();
            const count = data.count ?? 0;

            // Play sound only when count goes UP
            if (this._lastCount >= 0 && count > this._lastCount) {
                this._playSound();
            }

            this._updateBadge(count);

            // Refresh list if popover is currently open
            if (this._isOpen) this._loadNotifications();
        } catch (_) {}
    }

    _playSound() {
        if (!this._sound) return;
        this._sound.currentTime = 0;
        this._sound.play().catch(() => {});
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    _showLoading(show) {
        const loading = document.getElementById('nc-loading');
        const list    = document.getElementById('nc-list');
        if (!loading) return;
        if (show) {
            loading.classList.remove('nc-hidden');
            list?.classList.add('nc-hidden');
        } else {
            loading.classList.add('nc-hidden');
        }
    }

    _parseData(raw) {
        if (!raw) return {};
        if (typeof raw === 'object') return raw;
        try { return JSON.parse(raw); } catch (_) { return {}; }
    }

    _typeIcon(type) {
        const map = {
            sos_alert:        'emergency',
            mission_assigned: 'military_tech',
            mission_removed:  'remove_circle',
            broadcast:        'campaign',
            level_up:         'trending_up',
            achievement:      'emoji_events',
            event:            'event',
            test_push:        'science',
        };
        return map[type] || 'notifications';
    }

    _formatTime(dateStr) {
        if (!dateStr) return '';
        const date = new Date(dateStr);
        const diff = (Date.now() - date.getTime()) / 1000;
        if (diff < 60)      return 'Agora há pouco';
        if (diff < 3600)    return `Há ${Math.floor(diff / 60)} min`;
        if (diff < 86400)   return `Há ${Math.floor(diff / 3600)} h`;
        if (diff < 604800)  return `Há ${Math.floor(diff / 86400)} d`;
        return date.toLocaleDateString('pt-BR', { day: '2-digit', month: 'short' });
    }

    _esc(str) {
        if (!str) return '';
        const d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }

    _linkify(text) {
        if (!text) return '';
        return text.replace(/(https?:\/\/[^\s<>"]+)/g,
            url => `<a href="${url}" target="_blank" rel="noopener noreferrer" class="nc-link" onclick="event.stopPropagation()">${url}</a>`
        );
    }

    // ── CSS Injection ──────────────────────────────────────────────────────────

    _injectStyles() {
        if (document.getElementById('nc-styles')) return;
        const s = document.createElement('style');
        s.id = 'nc-styles';
        s.textContent = `
        /* ── Notification Center — alinhado ao design system do painel ── */
        .nc-popover {
            position: fixed;
            width: clamp(300px, 90vw, 380px);
            max-width: calc(100vw - 20px);
            font-family: var(--font-body, 'Nunito', -apple-system, sans-serif);
            background: rgba(20, 30, 48, 0.92);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--panel-border, rgba(255, 255, 255, 0.08));
            border-radius: 20px;
            box-shadow:
                0 20px 60px rgba(0, 0, 0, 0.5),
                0 0 0 1px rgba(6, 182, 212, 0.07),
                inset 0 1px 0 rgba(255, 255, 255, 0.05);
            z-index: 9998;
            display: flex;
            flex-direction: column;
            max-height: 520px;
            overflow: hidden;
            transform-origin: top right;
            transition: opacity 0.18s ease, transform 0.18s ease;
        }
        .nc-popover.nc-hidden  { opacity: 0; transform: scale(0.95) translateY(-6px); pointer-events: none; display: none; }
        .nc-popover.nc-visible { opacity: 1; transform: scale(1) translateY(0); pointer-events: auto; display: flex; }

        /* Header */
        .nc-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 18px;
            border-bottom: 1px solid var(--panel-border, rgba(255, 255, 255, 0.08));
            flex-shrink: 0;
        }
        .nc-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.88rem;
            font-weight: 800;
            color: var(--text-bright, #f8fafc);
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }
        .nc-title-icon {
            font-size: 18px !important;
            color: var(--neon-cyan, var(--accent-cyan, #06b6d4));
        }
        .nc-mark-all {
            background: none;
            border: none;
            color: var(--neon-cyan, var(--accent-cyan, #06b6d4));
            font-family: var(--font-body, 'Nunito', sans-serif);
            font-size: 0.7rem;
            font-weight: 700;
            cursor: pointer;
            padding: 4px 10px;
            border-radius: 8px;
            transition: background 0.2s;
            white-space: nowrap;
        }
        .nc-mark-all:hover { background: rgba(6, 182, 212, 0.12); }

        /* Body */
        .nc-body {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            scrollbar-width: thin;
            scrollbar-color: var(--panel-border, rgba(255, 255, 255, 0.08)) transparent;
        }
        .nc-body::-webkit-scrollbar { width: 4px; }
        .nc-body::-webkit-scrollbar-thumb {
            background: var(--panel-border, rgba(255, 255, 255, 0.08));
            border-radius: 2px;
        }

        /* Item */
        .nc-item {
            display: flex;
            gap: 12px;
            padding: 12px 18px;
            border-bottom: 1px solid var(--panel-border, rgba(255, 255, 255, 0.06));
            cursor: pointer;
            transition: background 0.15s;
            align-items: flex-start;
        }
        .nc-item:last-child { border-bottom: none; }
        .nc-item:hover { background: rgba(255, 255, 255, 0.04); }
        .nc-item--unread { background: rgba(6, 182, 212, 0.05); }
        .nc-item--unread:hover { background: rgba(6, 182, 212, 0.09); }

        .nc-item-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--panel-border, rgba(255, 255, 255, 0.08));
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: var(--text-dim, #94a3b8);
        }
        .nc-item-icon--unread {
            background: rgba(6, 182, 212, 0.1);
            border-color: rgba(6, 182, 212, 0.25);
            color: var(--neon-cyan, #06b6d4);
        }
        .nc-item-icon .material-icons-round { font-size: 16px !important; }

        .nc-item-body { flex: 1; min-width: 0; }
        .nc-item-hd {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 6px;
            margin-bottom: 3px;
        }
        .nc-item-title {
            font-size: 0.78rem;
            font-weight: 800;
            color: var(--text-bright, #f8fafc);
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .nc-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--neon-cyan, #06b6d4);
            flex-shrink: 0;
            box-shadow: 0 0 7px rgba(6, 182, 212, 0.7);
        }
        .nc-item-msg {
            margin: 0;
            font-size: 0.72rem;
            color: var(--text-dim, #94a3b8);
            line-height: 1.45;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        .nc-item-time {
            font-size: 0.67rem;
            color: rgba(148, 163, 184, 0.6);
            margin-top: 5px;
            display: block;
        }
        .nc-link {
            color: var(--neon-cyan, #06b6d4);
            text-decoration: none;
        }

        /* Skeleton */
        .nc-skeleton { display: flex; gap: 12px; padding: 12px 18px; align-items: flex-start; }
        .nc-sk-icon {
            width: 36px; height: 36px; border-radius: 10px;
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--panel-border, rgba(255,255,255,0.08));
            flex-shrink: 0;
            animation: ncShimmer 1.4s ease-in-out infinite;
        }
        .nc-sk-lines { flex: 1; display: flex; flex-direction: column; gap: 8px; padding-top: 4px; }
        .nc-sk-line {
            height: 9px; border-radius: 4px;
            background: rgba(255,255,255,0.05);
            animation: ncShimmer 1.4s ease-in-out infinite;
        }
        .nc-sk-title { width: 58%; }
        .nc-sk-sub   { width: 80%; animation-delay: 0.15s; }
        @keyframes ncShimmer {
            0%, 100% { opacity: 0.3; }
            50%       { opacity: 0.75; }
        }

        /* Empty state */
        .nc-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 36px 20px;
            color: var(--text-dim, #94a3b8);
            gap: 8px;
        }
        .nc-empty-icon { font-size: 2.5rem !important; opacity: 0.35; }
        .nc-empty p { font-size: 0.78rem; margin: 0; }

        /* Footer */
        .nc-footer {
            padding: 10px 18px;
            border-top: 1px solid var(--panel-border, rgba(255, 255, 255, 0.08));
            flex-shrink: 0;
        }
        .nc-view-all {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.73rem;
            font-weight: 700;
            color: var(--neon-cyan, var(--accent-cyan, #06b6d4));
            text-decoration: none;
            padding: 7px;
            border-radius: 10px;
            transition: background 0.2s;
            letter-spacing: 0.02em;
        }
        .nc-view-all:hover { background: rgba(6, 182, 212, 0.08); }

        /* Utility */
        .nc-hidden { display: none !important; }

        /* Mobile */
        @media (max-width: 480px) {
            .nc-popover { width: calc(100vw - 20px); }
        }
        `;
        document.head.appendChild(s);
    }
}

} // end if NotificationCenter undefined

if (typeof module !== 'undefined' && module.exports) {
    module.exports = window.NotificationCenter;
}
