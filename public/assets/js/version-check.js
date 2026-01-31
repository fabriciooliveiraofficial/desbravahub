/**
 * DesbravaHub Version Check Module
 * 
 * Handles client-side version checking with:
 * - localStorage version tracking
 * - Update notification toast
 * - Optional force update
 */

class VersionCheck {
    constructor(options = {}) {
        this.apiUrl = options.apiUrl || '';
        this.currentVersion = localStorage.getItem('app_version') || '1.0.0';
        this.checkInterval = options.checkInterval || 300000; // 5 minutes
        this.onUpdate = options.onUpdate || null;
        this.toast = options.toast || null;
        this.timer = null;
    }

    /**
     * Initialize version checking
     */
    init() {
        this.check();
        this.timer = setInterval(() => this.check(), this.checkInterval);
    }

    /**
     * Stop version checking
     */
    stop() {
        if (this.timer) {
            clearInterval(this.timer);
            this.timer = null;
        }
    }

    /**
     * Check for updates
     */
    async check() {
        if (!this.apiUrl) return;

        try {
            const response = await fetch(`${this.apiUrl}/check?version=${encodeURIComponent(this.currentVersion)}`, {
                credentials: 'include',
                headers: { 'Accept': 'application/json' }
            });

            if (!response.ok) return;

            const data = await response.json();

            if (data.needs_update) {
                this.handleUpdate(data);
            }
        } catch (err) {
            console.error('Version check error:', err);
        }
    }

    /**
     * Handle update notification
     */
    handleUpdate(data) {
        if (data.force_update) {
            this.forceUpdate(data);
        } else {
            this.showUpdateNotification(data);
        }
    }

    /**
     * Show update notification
     */
    showUpdateNotification(data) {
        if (this.toast) {
            this.toast.show({
                title: 'Atualização Disponível',
                message: `Versão ${data.latest_version} disponível. Clique para atualizar.`,
                type: 'info',
                duration: 0, // Don't auto-dismiss
                onClick: () => this.applyUpdate(data.latest_version),
            });
        }

        if (this.onUpdate) {
            this.onUpdate(data);
        }
    }

    /**
     * Force update (for breaking changes)
     */
    forceUpdate(data) {
        // Show modal overlay
        const overlay = document.createElement('div');
        overlay.id = 'force-update-overlay';
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 99999;
        `;

        overlay.innerHTML = `
            <div style="
                background: #1a1a2e;
                border: 1px solid rgba(255,255,255,0.1);
                border-radius: 16px;
                padding: 40px;
                max-width: 500px;
                text-align: center;
                color: #e0e0e0;
            ">
                <h2 style="
                    font-size: 1.8rem;
                    margin-bottom: 16px;
                    background: linear-gradient(90deg, #00d9ff, #00ff88);
                    -webkit-background-clip: text;
                    -webkit-text-fill-color: transparent;
                ">⚡ Atualização Obrigatória</h2>
                <p style="margin-bottom: 20px; color: #888;">
                    Uma nova versão (${this.escapeHtml(data.latest_version)}) está disponível 
                    com mudanças importantes.
                </p>
                ${data.breaking_changes ? `
                    <div style="
                        background: rgba(255,100,100,0.1);
                        border: 1px solid rgba(255,100,100,0.3);
                        border-radius: 8px;
                        padding: 12px;
                        margin-bottom: 20px;
                        text-align: left;
                        font-size: 14px;
                        color: #ff6b6b;
                    ">
                        <strong>Mudanças:</strong><br>
                        ${this.escapeHtml(data.breaking_changes)}
                    </div>
                ` : ''}
                <button id="force-update-btn" style="
                    background: linear-gradient(90deg, #00d9ff, #00ff88);
                    color: #1a1a2e;
                    border: none;
                    padding: 14px 32px;
                    border-radius: 8px;
                    font-size: 1rem;
                    font-weight: 600;
                    cursor: pointer;
                ">Atualizar Agora</button>
            </div>
        `;

        document.body.appendChild(overlay);

        document.getElementById('force-update-btn').addEventListener('click', () => {
            this.applyUpdate(data.latest_version);
        });
    }

    /**
     * Apply update
     */
    applyUpdate(newVersion) {
        // Update localStorage
        localStorage.setItem('app_version', newVersion);

        // Clear any cached data
        if ('caches' in window) {
            caches.keys().then(names => {
                names.forEach(name => caches.delete(name));
            });
        }

        // Reload page
        window.location.reload(true);
    }

    /**
     * Get current version
     */
    getVersion() {
        return this.currentVersion;
    }

    /**
     * Set current version (call after successful load)
     */
    setVersion(version) {
        this.currentVersion = version;
        localStorage.setItem('app_version', version);
    }

    /**
     * Escape HTML
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Export
if (typeof module !== 'undefined' && module.exports) {
    module.exports = VersionCheck;
}
