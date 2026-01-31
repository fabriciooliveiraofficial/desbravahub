/**
 * DesbravaHub Unified Alert System (UAS)
 * 
 * Replaces native alert() and confirm() with premium, 
 * glassmorphism-inspired UI components.
 */

(function () {
    // Prevent multiple initializations
    if (window.UAS) return;

    class UnifiedAlertSystem {
        constructor() {
            this.activeModal = null;
            this._injectStyles();
        }

        /**
         * alert(message, title)
         * Returns a promise for consistency, though alerts are usually fire-and-forget.
         */
        alert(message, title = 'Aviso') {
            return new Promise((resolve) => {
                this._createModal({
                    title,
                    message,
                    type: 'alert',
                    onConfirm: () => {
                        this._close();
                        resolve(true);
                    }
                });
            });
        }

        /**
         * confirm(message, title)
         * Returns a promise that resolves to true or false.
         */
        confirm(message, title = 'Confirmar Ação') {
            return new Promise((resolve) => {
                this._createModal({
                    title,
                    message,
                    type: 'confirm',
                    onConfirm: () => {
                        this._close();
                        resolve(true);
                    },
                    onCancel: () => {
                        this._close();
                        resolve(false);
                    }
                });
            });
        }

        /**
         * toast(message, type, title)
         * Helper to access the existing toast system if available
         */
        toast(message, type = 'info', title = '') {
            if (window.toast && typeof window.toast.show === 'function') {
                window.toast.show({ title, message, type });
            } else {
                // Fallback to minimal toast if main system not ready
                console.info(`[UAS Toast] ${type.toUpperCase()}: ${message}`);
            }
        }

        _createModal({ title, message, type, onConfirm, onCancel }) {
            this._close(); // Close any existing modal

            const overlay = document.createElement('div');
            overlay.className = 'uas-overlay active';

            const modal = document.createElement('div');
            modal.className = 'uas-modal';

            // Icon based on type
            const icon = type === 'confirm' ? 'help_outline' : 'info_outline';
            const iconColor = type === 'confirm' ? 'var(--accent-cyan)' : 'var(--accent-cyan)';

            modal.innerHTML = `
                <div class="uas-modal-body">
                    <div class="uas-icon-box" style="color: ${iconColor}">
                        <span class="material-icons-round">${icon}</span>
                    </div>
                    <div class="uas-content">
                        <h3 class="uas-title">${title}</h3>
                        <p class="uas-message">${message}</p>
                    </div>
                </div>
                <div class="uas-modal-footer">
                    ${type === 'confirm' ? `
                        <button class="uas-btn uas-btn-cancel">Cancelar</button>
                    ` : ''}
                    <button class="uas-btn uas-btn-confirm">OK</button>
                </div>
            `;

            overlay.appendChild(modal);
            document.body.appendChild(overlay);
            this.activeModal = overlay;

            // Simple reveal animation
            requestAnimationFrame(() => {
                modal.classList.add('visible');
            });

            // Bind events
            modal.querySelector('.uas-btn-confirm').onclick = onConfirm;
            if (type === 'confirm') {
                modal.querySelector('.uas-btn-cancel').onclick = onCancel;
            }

            // Accessibility: Focus the confirm button
            modal.querySelector('.uas-btn-confirm').focus();
        }

        _close() {
            if (this.activeModal) {
                const modal = this.activeModal.querySelector('.uas-modal');
                modal.classList.remove('visible');
                const current = this.activeModal;
                setTimeout(() => {
                    if (current && current.parentNode) {
                        current.parentNode.removeChild(current);
                    }
                }, 200);
                this.activeModal = null;
            }
        }

        _injectStyles() {
            if (document.getElementById('uas-styles')) return;
            const style = document.createElement('style');
            style.id = 'uas-styles';
            style.textContent = `
                .uas-overlay {
                    position: fixed;
                    inset: 0;
                    background: rgba(15, 23, 42, 0.8);
                    backdrop-filter: blur(8px);
                    z-index: 100000;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    opacity: 0;
                    transition: opacity 0.2s ease;
                    padding: 20px;
                }
                .uas-overlay.active {
                    opacity: 1;
                }
                .uas-modal {
                    background: #1e1e2d;
                    border: 1px solid rgba(255,255,255,0.1);
                    border-radius: 28px;
                    width: 100%;
                    max-width: 420px;
                    overflow: hidden;
                    box-shadow: 0 40px 80px -20px rgba(0, 0, 0, 0.6);
                    transform: scale(0.95) translateY(10px);
                    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
                }
                .uas-modal.visible {
                    transform: scale(1) translateY(0);
                }
                .uas-modal-body {
                    padding: 32px 32px 24px 32px;
                    display: flex;
                    gap: 20px;
                }
                .uas-icon-box {
                    width: 48px;
                    height: 48px;
                    background: rgba(6, 182, 212, 0.1);
                    border-radius: 16px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    flex-shrink: 0;
                }
                .uas-icon-box .material-icons-round {
                    font-size: 24px;
                }
                .uas-content {
                    flex: 1;
                }
                .uas-title {
                    margin: 0 0 8px 0;
                    color: #fff;
                    font-family: 'Outfit', sans-serif;
                    font-size: 1.25rem;
                    font-weight: 700;
                }
                .uas-message {
                    margin: 0;
                    color: #94a3b8;
                    font-size: 0.95rem;
                    line-height: 1.6;
                }
                .uas-modal-footer {
                    padding: 16px 32px 32px 32px;
                    display: flex;
                    justify-content: flex-end;
                    gap: 12px;
                }
                .uas-btn {
                    padding: 12px 24px;
                    border-radius: 14px;
                    font-family: 'Outfit', sans-serif;
                    font-weight: 700;
                    font-size: 0.85rem;
                    cursor: pointer;
                    transition: all 0.2s;
                    border: none;
                }
                .uas-btn-confirm {
                    background: var(--accent-cyan);
                    color: #000;
                }
                .uas-btn-confirm:hover {
                    filter: brightness(1.1);
                    transform: translateY(-1px);
                }
                .uas-btn-cancel {
                    background: rgba(255,255,255,0.05);
                    color: #94a3b8;
                    border: 1px solid rgba(255,255,255,0.1);
                }
                .uas-btn-cancel:hover {
                    background: rgba(255,255,255,0.1);
                    color: #fff;
                }
            `;
            document.head.appendChild(style);
        }
    }

    // Create global instance
    window.UAS = new UnifiedAlertSystem();

    // Global helper functions
    window.swal = function (message, title) { return window.UAS.alert(message, title); };
    window.sconfirm = function (message, title) { return window.UAS.confirm(message, title); };
})();
