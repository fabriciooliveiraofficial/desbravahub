/**
 * DesbravaHub PWA Installer
 * Handles install prompts for Android, Desktop, and iOS
 */
if (typeof PWAInstaller === 'undefined') {
    class PWAInstaller {
        constructor() {
            this.promptEvent = null;
            this.modal = null;
            this.isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
            this.isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;

            this.init();
        }

        init() {
            // If already installed, don't show anything
            if (this.isStandalone) {
                console.log('[PWA] App is already installed (standalone mode).');
                return;
            }

            // Check dismissal preference (don't show if dismissed in last 7 days)
            const dismissedAt = localStorage.getItem('pwa_dismissed_at');
            if (dismissedAt) {
                const daysSinceDismiss = (Date.now() - parseInt(dismissedAt)) / (1000 * 60 * 60 * 24);
                if (daysSinceDismiss < 7) {
                    console.log('[PWA] Install prompt dismissed recently.');
                    return;
                }
            }

            // Listen for standard install prompt (Chrome/Android)
            window.addEventListener('beforeinstallprompt', (e) => {
                console.log('[PWA] Captured beforeinstallprompt');
                e.preventDefault();
                this.promptEvent = e;
                this.showModal();
            });

            // For iOS, show after a delay if not standalone
            if (this.isIOS) {
                setTimeout(() => this.showModal(), 3000);
            }
        }

        injectStyles() {
            if (document.getElementById('pwa-styles')) return;

            const css = `
            .pwa-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.4);
                backdrop-filter: blur(4px);
                z-index: 10000;
                display: flex;
                align-items: center;
                justify-content: center;
                opacity: 0;
                transition: opacity 0.3s ease;
            }
            .pwa-modal {
                background: #ffffff;
                border: 1px solid rgba(255, 255, 255, 0.2);
                width: 90%;
                max-width: 400px;
                padding: 30px;
                border-radius: 24px;
                box-shadow: 0 20px 50px -10px rgba(0, 0, 0, 0.25);
                text-align: center;
                transform: translateY(20px);
                transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                position: relative;
                font-family: 'Inter', system-ui, -apple-system, sans-serif;
            }
            .dark .pwa-modal {
                background: #1e293b;
                border-color: rgba(255, 255, 255, 0.05);
                color: #f8fafc;
            }
            .pwa-icon-box {
                width: 80px;
                height: 80px;
                margin: 0 auto 20px;
                border-radius: 20px;
                overflow: hidden;
                box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.1);
            }
            .pwa-icon-box img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }
            .pwa-title {
                font-size: 1.5rem;
                font-weight: 700;
                margin-bottom: 8px;
                color: #0f172a;
            }
            .dark .pwa-title { color: #f8fafc; }
            .pwa-desc {
                font-size: 0.95rem;
                color: #64748b;
                margin-bottom: 24px;
                line-height: 1.5;
            }
            .dark .pwa-desc { color: #94a3b8; }
            .pwa-btn {
                background: #06b6d4;
                color: white;
                border: none;
                padding: 14px 28px;
                font-size: 1rem;
                font-weight: 600;
                border-radius: 14px;
                cursor: pointer;
                width: 100%;
                transition: all 0.2s;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
            }
            .pwa-btn:hover {
                background: #0891b2;
                transform: translateY(-2px);
                box-shadow: 0 10px 20px -5px rgba(6, 182, 212, 0.4);
            }
            .pwa-btn:active { transform: translateY(0); }
            
            .pwa-close {
                position: absolute;
                top: 15px;
                right: 15px;
                background: transparent;
                border: none;
                color: #cbd5e1;
                cursor: pointer;
                padding: 5px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.2s;
            }
            .pwa-close:hover {
                background: rgba(0,0,0,0.05);
                color: #64748b;
            }
            
            .ios-instructions {
                margin-top: 20px;
                padding: 15px;
                background: #f8fafc;
                border-radius: 12px;
                font-size: 0.85rem;
                text-align: left;
                color: #334155;
            }
            .dark .ios-instructions {
                background: rgba(0,0,0,0.2);
                color: #e2e8f0;
            }
            .ios-step {
                display: flex;
                align-items: center;
                gap: 10px;
                margin-bottom: 8px;
            }
            .ios-icon {
                font-size: 1.2rem;
                color: #3b82f6;
            }
        `;
            const style = document.createElement('style');
            style.id = 'pwa-styles';
            style.textContent = css;
            document.head.appendChild(style);
        }

        createModal() {
            this.injectStyles();

            const overlay = document.createElement('div');
            overlay.className = 'pwa-overlay';

            const content = `
            <div class="pwa-modal">
                <button class="pwa-close" id="pwa-close-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
                
                <div class="pwa-icon-box">
                    <img src="/assets/images/icon-192.png" alt="DesbravaHub Icon">
                </div>
                
                <h2 class="pwa-title">Instalar App</h2>
                <p class="pwa-desc">
                    Instale o DesbravaHub para melhor desempenho, acesso rápido e funcionalidades offline.
                </p>

                ${this.isIOS ? `
                    <div class="ios-instructions">
                        <div class="ios-step">
                            <span>1. Toque no botão Compartilhar</span>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><polyline points="16 6 12 2 8 6"/><line x1="12" y1="2" x2="12" y2="15"/></svg>
                        </div>
                        <div class="ios-step">
                            <span>2. Selecione "Adicionar à Tela de Início"</span>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                        </div>
                    </div>
                ` : `
                    <button class="pwa-btn" id="pwa-install-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                        Instalar Agora
                    </button>
                `}
            </div>
        `;

            overlay.innerHTML = content;
            document.body.appendChild(overlay);
            this.modal = overlay;

            // Event Listeners for Modal
            document.getElementById('pwa-close-btn').addEventListener('click', () => this.hideModal(true));

            // Android/Chrome Install Action
            const installBtn = document.getElementById('pwa-install-btn');
            if (installBtn) {
                installBtn.addEventListener('click', () => {
                    this.hideModal(false); // Hide but don't count as 'dismissed' for logic preference
                    if (this.promptEvent) {
                        this.promptEvent.prompt();
                        this.promptEvent.userChoice.then((choiceResult) => {
                            if (choiceResult.outcome === 'accepted') {
                                console.log('[PWA] User accepted the install prompt');
                            } else {
                                console.log('[PWA] User dismissed the install prompt');
                            }
                            this.promptEvent = null;
                        });
                    }
                });
            }
        }

        showModal() {
            if (!this.modal) this.createModal();

            // Wait a bit for DOM to apply then fade in
            requestAnimationFrame(() => {
                this.modal.style.opacity = '1';
                this.modal.querySelector('.pwa-modal').style.transform = 'translateY(0)';
            });
        }

        hideModal(savePreference = false) {
            if (!this.modal) return;

            this.modal.style.opacity = '0';
            this.modal.querySelector('.pwa-modal').style.transform = 'translateY(20px)';

            setTimeout(() => {
                this.modal.remove();
                this.modal = null;
            }, 300);

            if (savePreference) {
                localStorage.setItem('pwa_dismissed_at', Date.now());
            }
        }
    }

    // Initialize on load
    window.addEventListener('load', () => {
        new PWAInstaller();
    });
} // End of 'if (typeof PWAInstaller === 'undefined')' guard
