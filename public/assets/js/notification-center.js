/**
 * Notification Center JS - ELITE EDITION (V9.1)
 * 
 * Performance & UX Upgrades:
 * - Single-Request Bulk Actions
 * - ACTIVE DOM Desintegration (rows are destroyed to free memory)
 * - Anti-Ghost-Checks (only unread items are selectable)
 * - HTMX-Resistant Endpoint Fallbacks
 */
if (typeof window.NotificationCenter === 'undefined') {
window.NotificationCenter = class {
    constructor(opts = {}) {
        this._opts        = opts;
        this._api         = opts.apiUrl;
        this._unreadUrl   = opts.unreadUrl;
        this._readAllUrl  = opts.readAllUrl;
        // Se o HTMX não injetou a bulkReadUrl a tempo, monta a URL caindo para a _api base:
        this._bulkUrl     = opts.bulkReadUrl || (this._api + '/bulk-read');
        this._tenant      = opts.tenant || '';
        
        this._btn         = document.getElementById(opts.buttonId || 'notificationBtn');
        this._badge       = document.getElementById(opts.badgeId || 'notif-badge');
        this._popover     = null;
        this._pollTimer   = null;
        this._initialCount = opts.initialCount || 0;

        // Persistence: save celebrated IDs so we don't repeat across page loads
        this._celebratedKey = `nc_celebrated_${this._tenant}`;
        try {
            const saved = localStorage.getItem(this._celebratedKey);
            this._celebratedIds = new Set(JSON.parse(saved || '[]'));
        } catch(e) { this._celebratedIds = new Set(); }
        
        console.log('[NC V9.2] Elite + Dopamine Drop Engine Active');
        this._init();
        this._unlockAudio();
    }

    _unlockAudio() {
        const unlock = () => {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            if (ctx.state === 'suspended') ctx.resume();
            document.removeEventListener('click', unlock);
            document.removeEventListener('keydown', unlock);
        };
        document.addEventListener('click', unlock, { once: true });
        document.addEventListener('keydown', unlock, { once: true });
    }

    _saveCelebrated(id) {
        this._celebratedIds.add(id);
        try {
            const arr = Array.from(this._celebratedIds).slice(-100);
            localStorage.setItem(this._celebratedKey, JSON.stringify(arr));
        } catch(e) {}
    }

    _init() {
        if (!this._btn) return;
        this._ensurePopover();

        // Apply any initial count from PHP immediately
        if (this._initialCount > 0) {
            this._updateBadge(this._initialCount);
        }

        this._btn.addEventListener('click', (e) => {
            e.preventDefault(); e.stopPropagation();
            this.toggle();
        });

        document.addEventListener('click', (e) => {
            if (this._popover?.classList.contains('active')) {
                if (!this._popover.contains(e.target) && !this._btn.contains(e.target)) {
                    this.hide();
                }
            }
        });

        // ELITE DELEGATION
        this._popover.addEventListener('click', (e) => {
            const t = e.target;
            
            // X CLOSE
            if (t.id === 'nc-close-btn' || t.closest('#nc-close-btn')) {
                e.stopPropagation(); this.hide(); return;
            }

            // MARK ALL
            if (t.id === 'nc-mark-all-btn' || t.closest('#nc-mark-all-btn')) {
                e.stopPropagation(); this._markAllRead(); return;
            }

            // BULK READ
            if (t.id === 'nc-bulk-read-btn' || t.closest('#nc-bulk-read-btn')) {
                e.stopPropagation(); this._markSelectedRead(); return;
            }

            // SELECT ALL TOGGLE
            if (t.id === 'nc-select-all-check') {
                this._popover.querySelectorAll('.nc-check-input').forEach(c => c.checked = t.checked);
                this._updateBulkHeader(); return;
            }

            // CHECKBOX CLICK
            if (t.classList.contains('nc-check-input')) {
                this._updateBulkHeader(); return;
            }

            // UNREAD DOT (Instant Read)
            const dot = t.closest('.nc-unread-indicator');
            if (dot) {
                e.stopPropagation(); this._handleMarkAsRead(dot.closest('.nc-item')); return;
            }

            // ITEM NAV
            const item = t.closest('.nc-item');
            if (item) {
                e.preventDefault(); this._handleItemClick(item);
            }
        });

        this._updateBadge(this._initialCount);
        this._startPolling();
        this._injectStyles();
    }

    _ensurePopover() {
        let pop = document.getElementById('notification-popover');
        if (!pop) {
            pop = document.createElement('div');
            pop.id = 'notification-popover';
            pop.className = 'notification-popover-v9';
            (document.getElementById('notif-bell-container') || this._btn.parentElement).appendChild(pop);
        }
        this._popover = pop;
    }

    toggle() { this._popover.classList.contains('active') ? this.hide() : this.show(); }
    show() { this._popover.classList.add('active'); this._btn.classList.add('active'); this.fetchNotifications(); }
    hide() { this._popover.classList.remove('active'); this._btn.classList.remove('active'); }

    async fetchNotifications() {
        this._popover.innerHTML = '<div class="nc-loading"><i class="fas fa-circle-notch fa-spin"></i> Otimizando...</div>';
        try {
            const res = await fetch(this._api);
            const data = await res.json();
            this._render(data.notifications || []);
            if (typeof data.unread_count !== 'undefined') this._updateBadge(data.unread_count);
        } catch (e) { this._popover.innerHTML = '<div class="nc-error">Erro na conexão 📡</div>'; }
    }

    _buildHeader() {
        return `
            <div class="nc-v9-header">
                <div class="nc-v9-top">
                    <span class="nc-v9-tag">CENTRAL DESBRAVAHUB</span>
                    <button class="nc-v9-x" id="nc-close-btn">&times;</button>
                </div>
                <div class="nc-v9-title-row">
                    <span class="nc-v9-title">Avisos</span>
                    <button class="nc-v9-clean" id="nc-mark-all-btn">Limpar Tudo</button>
                </div>
                <div class="nc-v9-bulk-bar" id="nc-bulk-actions" style="display: none;">
                    <label class="nc-v9-all-label">
                        <input type="checkbox" id="nc-select-all-check"> Selecionar Todos
                    </label>
                    <button class="nc-v9-bulk-btn" id="nc-bulk-read-btn">Marcar Lidos</button>
                </div>
            </div>
        `;
    }

    _render(notifications) {
        if (notifications.length === 0) {
            this._popover.innerHTML = this._buildHeader() + '<div class="nc-empty"><i class="fas fa-check-circle"></i> Sem novos avisos!</div>';
            return;
        }

        let listHtml = '<div class="nc-v9-list">';
        notifications.forEach(n => {
            const isUnread = n.is_unread == 1 || n.is_unread === true;
            
            // Fix: Parse data if it's a string from the API
            let meta = n.data;
            if (typeof meta === 'string') {
                try { meta = JSON.parse(meta); } catch(e) { meta = {}; }
            }

            listHtml += `
                <div class="nc-v9-row ${isUnread ? 'nc-unread' : ''}" data-id="${n.id}">
                    <div class="nc-v9-check-track">
                        <input type="checkbox" class="nc-check-input" data-id="${n.id}">
                    </div>
                    <div class="nc-item" data-id="${n.id}" data-url="${meta?.link || '#'}" data-is-unread="${isUnread ? '1' : '0'}">
                        <div class="nc-v9-icon">${meta?.icon || '🔔'}</div>
                        <div class="nc-v9-body">
                            <div class="nc-v9-subj">${n.title}</div>
                            <div class="nc-v9-msg">${n.message}</div>
                            <div class="nc-v9-time">${this._formatTime(n.created_at)}</div>
                        </div>
                        ${isUnread ? `<div class="nc-unread-indicator"></div>` : ''}
                    </div>
                </div>
            `;
        });
        listHtml += '</div>';
        this._popover.innerHTML = this._buildHeader() + listHtml;
        this._updateBulkHeader();
    }

    async _handleItemClick(el) {
        const url = el.dataset.url;
        if (el.dataset.isUnread === '1') await this._handleMarkAsRead(el);
        if (url && url !== '#' && !url.includes('javascript:')) window.location.href = url;
    }

    async _handleMarkAsRead(el) {
        if (!el) return;
        const row = el.closest('.nc-v9-row');
        
        // ELITE OPTIMISTIC: DOM DESINTEGRATION
        if (row && row.classList.contains('nc-unread')) {
            row.classList.add('nc-v9-row--removing');
            setTimeout(() => {
                row.remove();
                // If this was the last notification, show empty state immediately
                if (this._popover && this._popover.querySelectorAll('.nc-v9-row').length === 0) {
                    this._render([]);
                }
            }, 350); 
            
            const cur = this._badgeCount();
            this._updateBadge(Math.max(0, cur - 1));
        }

        try {
            fetch(`${this._api}/${el.dataset.id}/read`, { method: 'POST', headers: { 'X-Background-Request': '1' } });
        } catch (e) {}
    }

    async _markAllRead() {
        this._popover.querySelectorAll('.nc-v9-row').forEach(row => {
            row.classList.add('nc-v9-row--removing');
        });
        
        setTimeout(() => {
            if (this._popover) this._popover.innerHTML = ''; // Fast clear
            this._render([]); // Show empty state correctly
        }, 350);

        const selAll = document.getElementById('nc-select-all-check');
        if (selAll) selAll.checked = false;

        this._updateBadge(0);
        this._updateBulkHeader();
        
        try { fetch(this._readAllUrl, { method: 'POST', headers: { 'X-Background-Request': '1' } }); } catch (e) {}
    }

    async _markSelectedRead() {
        const checked = [...this._popover.querySelectorAll('.nc-check-input:checked')];
        if (checked.length === 0) return;

        const ids = checked.map(c => c.dataset.id);
        let removedCount = 0;

        checked.forEach(chk => {
            const row = chk.closest('.nc-v9-row');
            if (row) {
                // Conta apenas os que eram não lidos para abater do badge
                if (row.classList.contains('nc-unread')) {
                    removedCount++;
                }
                // Desintegra a linha independentemente do status
                row.classList.add('nc-v9-row--removing'); 
                setTimeout(() => row.remove(), 350);     
            }
            // Cleanup visually
            chk.checked = false;
        });

        const selAll = document.getElementById('nc-select-all-check');
        if (selAll) selAll.checked = false;

        this._updateBadge(Math.max(0, this._badgeCount() - removedCount));
        this._updateBulkHeader(); // This hides the blue action bar immediately

        try {
            await fetch(this._bulkUrl, { 
                method: 'POST', 
                headers: { 'Content-Type': 'application/json', 'X-Background-Request': '1' },
                body: JSON.stringify({ ids })
            });
        } catch (e) {
            console.error('[NC] Bulk mark final fail:', e);
        }
    }

    _updateBulkHeader() {
        const bulk = document.getElementById('nc-bulk-actions');
        if (!bulk) return;
        const count = this._popover.querySelectorAll('.nc-check-input:checked').length;
        bulk.style.display = count > 0 ? 'flex' : 'none';
        bulk.classList.toggle('active', count > 0);
    }

    _updateBadge(count) {
        if (!this._badge) return;
        this._badge.style.display = count <= 0 ? 'none' : 'flex';
        this._badge.textContent = count > 9 ? '9+' : count;
    }

    _badgeCount() {
        const t = this._badge?.textContent || '0';
        return t === '9+' ? 10 : (parseInt(t) || 0);
    }

    _formatTime(s) {
        if (!s) return '';
        const d = new Date(s.replace(' ', 'T')), n = new Date();
        const diff = Math.floor((n - d) / 1000);
        if (diff < 60) return 'Agora';
        if (diff < 3600) return `Há ${Math.floor(diff / 60)} min`;
        if (diff < 86400) return `Há ${Math.floor(diff / 3600)} h`;
        return d.toLocaleDateString();
    }

    _startPolling() {
        if (this._pollTimer) clearInterval(this._pollTimer);

        // INITIAL CHECK: Trigger Dopamine Drop for any existing hero notifications
        this._checkForHeroNotifications();

        this._pollTimer = setInterval(async () => {
            try {
                const res = await fetch(this._unreadUrl);
                const data = await res.json();
                const newCount = data.count;
                const oldCount = this._badgeCount();

                if (newCount !== oldCount && !this._popover.classList.contains('active')) {
                    this._updateBadge(newCount);
                }

                // If count increased, check for hero-worthy notifications
                if (newCount > oldCount) {
                    this._checkForHeroNotifications();
                }
            } catch (e) {}
        }, 30000);
    }

    async _checkForHeroNotifications() {
        if (!window.DopamineDrop) return;
        try {
            const res = await fetch(this._api);
            const data = await res.json();
            const notifications = data.notifications || [];

            for (const n of notifications) {
                // Only trigger for achievement/level_up types
                const isHero = n.type === 'achievement' || n.type === 'level_up';
                if (!isHero) continue;
                if (this._celebratedIds.has(String(n.id))) continue;

                // Allow celebrating if UNREAD or if it's VERY RECENT (within 5 mins)
                const isUnread = n.read_at === null || n.is_unread == 1 || n.is_unread === true;
                let isRecent = false;
                try {
                    const created = new Date(n.created_at.replace(' ', 'T')).getTime();
                    const now = new Date().getTime();
                    isRecent = (now - created) < 5 * 60 * 1000;
                } catch(e) {}

                if (isUnread || isRecent) {
                    let meta = n.data;
                    if (typeof meta === 'string') {
                        try { meta = JSON.parse(meta); } catch(e) { meta = {}; }
                    }

                    if (!window.DopamineDrop.isActive()) {
                        console.log('[Dopamine] Triggering for ID:', n.id);
                        this._saveCelebrated(String(n.id));

                        window.DopamineDrop.trigger({
                            title: n.title || '🏆 Nova Conquista!',
                            message: n.message || '',
                            xpReward: meta?.xp_reward || meta?.xp || 0,
                            notifId: n.id,
                            readUrl: this._api,
                            type: n.type
                        });
                        break; // Only one per poll
                    }
                }
            }
        } catch (e) {
            console.error('[NC] Hero check failed:', e);
        }
    }

    // Exposed for external callers (e.g. after hero modal dismiss)
    async _silentPoll() {
        try {
            const res = await fetch(this._unreadUrl);
            const data = await res.json();
            this._updateBadge(data.count || 0);
        } catch(e) {}
    }

    _injectStyles() {
        const id = 'nc-styles-v9'; if (document.getElementById(id)) return;
        const s = document.createElement('style'); s.id = id;
        s.textContent = `
            .notification-popover-v9 {
                position: absolute; top: calc(100% + 15px); right: 0; width: 380px;
                background: rgba(13, 20, 30, 0.98); backdrop-filter: blur(28px);
                border-radius: 24px; border: 1px solid rgba(0, 217, 255, 0.2);
                box-shadow: 0 40px 100px rgba(0,0,0,0.8); opacity: 0; visibility: hidden;
                transform: translateY(20px) scale(0.96); transition: all 0.4s cubic-bezier(0.1, 0.9, 0.1, 1); 
                z-index: 100000; overflow: hidden;
            }
            .notification-popover-v9.active { opacity: 1; visibility: visible; transform: translateY(0) scale(1); }
            
            @media (max-width: 480px) {
                .notification-popover-v9 { position: fixed; top: 12px; left: 12px; right: 12px; bottom: 12px; width: auto; height: auto; }
                .nc-v9-list { max-height: none !important; flex: 1; }
                .notification-popover-v9.active { display: flex; flex-direction: column; }
            }

            .nc-v9-header { background: rgba(255,255,255,0.03); border-bottom: 1px solid rgba(255,255,255,0.08); }
            .nc-v9-top { display: flex; justify-content: space-between; align-items: center; padding: 10px 18px; background: rgba(0,0,0,0.2); }
            .nc-v9-tag { font-size: 0.6rem; font-weight: 900; letter-spacing: 2.5px; color: #58a6ff; opacity: 0.8; }
            .nc-v9-x { background: none; border: none; color: #fff; font-size: 1.6rem; cursor: pointer; opacity: 0.5; transition: 0.2s; padding: 0; line-height: 1; }
            .nc-v9-x:hover { opacity: 1; color: #ff7b72; transform: scale(1.2); }

            .nc-v9-title-row { display: flex; justify-content: space-between; align-items: center; padding: 15px 18px; }
            .nc-v9-title { font-weight: 800; font-size: 1.2rem; color: #f0f6fc; }
            .nc-v9-clean { background: none; border: none; color: #00d9ff; font-weight: 800; font-size: 0.8rem; cursor: pointer; transition: 0.2s; border-bottom: 1px solid transparent; }
            .nc-v9-clean:hover { border-color: #00d9ff; }

            .nc-v9-bulk-bar { padding: 12px 18px; background: #00d9ff; display: flex; justify-content: space-between; align-items: center; animation: ncSlideIn 0.3s ease; }
            @keyframes ncSlideIn { from { transform: translateY(-100%); } to { transform: translateY(0); } }
            .nc-v9-all-label { font-size: 0.75rem; color: #0d1117; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 8px; }
            .nc-v9-bulk-btn { background: #0d1117; color: #00d9ff; border: none; padding: 6px 14px; border-radius: 12px; font-weight: 900; font-size: 0.75rem; cursor: pointer; }

            .nc-v9-list { max-height: 480px; overflow-y: auto; scrollbar-width: thin; }
            .nc-v9-list::-webkit-scrollbar { width: 4px; }
            .nc-v9-list::-webkit-scrollbar-thumb { background: rgba(0, 217, 255, 0.2); }

            .nc-v9-row { display: flex; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.04); transition: max-height 0.35s ease, opacity 0.25s ease, padding 0.35s ease; overflow: hidden; padding: 0 18px; }
            /* This is the key fixed rule: removing standard properties smoothly before DOM deletion */
            .nc-v9-row--removing { max-height: 0 !important; opacity: 0 !important; padding-top: 0 !important; padding-bottom: 0 !important; pointer-events: none; border-bottom-color: transparent !important; }
            
            .nc-v9-row:hover { background: rgba(0, 217, 255, 0.03); }
            .nc-unread { background: rgba(0, 217, 255, 0.05); }

            .nc-v9-check-track { width: 35px; min-width: 35px; display: flex; align-items: center; justify-content: flex-start; }
            .nc-check-input { width: 20px; height: 20px; cursor: pointer; accent-color: #00d9ff; margin: 0; }
            
            .nc-item { flex: 1; display: flex; gap: 18px; padding: 18px 0; cursor: pointer; }
            .nc-v9-icon { width: 44px; height: 44px; min-width: 44px; border-radius: 14px; background: rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center; font-size: 1.4rem; border: 1px solid rgba(255,255,255,0.03); }
            .nc-unread .nc-v9-icon { border-color: rgba(0, 217, 255, 0.25); background: rgba(0, 217, 255, 0.08); color: #00d9ff; }
            
            .nc-v9-body { flex: 1; min-width: 0; }
            .nc-v9-subj { font-weight: 800; font-size: 0.92rem; color: #f1f5f9; margin-bottom: 3px; }
            .nc-v9-msg { font-size: 0.82rem; color: #8b949e; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
            .nc-v9-time { font-size: 0.72rem; color: #57606a; margin-top: 6px; font-weight: 800; }
            
            .nc-unread-indicator { width: 12px; height: 12px; min-width: 12px; border-radius: 50%; background: #00d9ff; box-shadow: 0 0 12px #00d9ff; margin-left: 15px; align-self: center; transition: 0.3s; cursor: pointer; }
            .nc-unread-indicator:hover { transform: scale(1.4); background: #ff4d4d; box-shadow: 0 0 15px #ff4d4d; }

            .nc-v9-footer { display: flex; align-items: center; justify-content: center; padding: 18px; color: #58a6ff; font-weight: 800; font-size: 0.85rem; text-decoration: none; border-top: 1px solid rgba(255,255,255,0.06); background: rgba(0,0,0,0.1); }
        `;
        document.head.appendChild(s);
    }
}
}
