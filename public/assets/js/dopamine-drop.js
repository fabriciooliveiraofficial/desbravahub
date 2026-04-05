/**
 * DOPAMINE DROP ENGINE — V2.0
 * 
 * Gamification celebration overlay triggered in real-time.
 * Works on ANY page. No server-side rendering needed.
 * 
 * Features:
 *  - Canvas Confetti (60fps GPU-accelerated)
 *  - Lottie 3D Medal Animation
 *  - XP Number Slot Machine (counter spin)
 *  - Haptic Feedback (navigator.vibrate)
 *  - Victory Sound (synth-based, no external file needed)
 */
window.DopamineDrop = (function () {

    let _active = false;
    let _confettiLoaded = false;

    // ── Confetti loader (lazy, CDN) ──
    function _ensureConfetti() {
        return new Promise(resolve => {
            if (window.confetti) { _confettiLoaded = true; return resolve(); }
            const s = document.createElement('script');
            s.src = 'https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js';
            s.onload = () => { _confettiLoaded = true; resolve(); };
            s.onerror = () => resolve(); // graceful fallback
            document.head.appendChild(s);
        });
    }

    // ── Lottie loader (lazy, CDN) ──
    function _ensureLottie() {
        return new Promise(resolve => {
            if (customElements.get('lottie-player')) return resolve();
            const s = document.createElement('script');
            s.src = 'https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js';
            s.onload = () => resolve();
            s.onerror = () => resolve();
            document.head.appendChild(s);
        });
    }

    // ── Victory Sound (Web Audio API — no external file) ──
    function _playVictorySound() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            // Chord: C5 + E5 + G5 arpeggio
            const notes = [523.25, 659.25, 783.99, 1046.50];
            notes.forEach((freq, i) => {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sine';
                osc.frequency.value = freq;
                gain.gain.setValueAtTime(0, ctx.currentTime + i * 0.08);
                gain.gain.linearRampToValueAtTime(0.15, ctx.currentTime + i * 0.08 + 0.05);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + i * 0.08 + 0.6);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start(ctx.currentTime + i * 0.08);
                osc.stop(ctx.currentTime + i * 0.08 + 0.6);
            });
        } catch (e) { /* AudioContext not supported */ }
    }

    // ── Confetti Burst ──
    function _fireConfetti() {
        if (!window.confetti) return;
        const count = 200;
        const defaults = { origin: { y: 0.7 }, zIndex: 200001 };
        const fire = (ratio, opts) => {
            confetti({ ...defaults, ...opts, particleCount: Math.floor(count * ratio) });
        };
        // Golden + Cyan colors
        const colors = ['#fbbf24', '#00d9ff', '#10b981', '#ffffff'];
        fire(0.25, { spread: 26, startVelocity: 55, colors });
        fire(0.2,  { spread: 60, colors });
        fire(0.35, { spread: 100, decay: 0.91, scalar: 0.8, colors });
        fire(0.1,  { spread: 120, startVelocity: 25, decay: 0.92, scalar: 1.2, colors });
        fire(0.1,  { spread: 120, startVelocity: 45, colors });

        // Second burst from sides
        setTimeout(() => {
            confetti({ particleCount: 60, angle: 60, spread: 55, origin: { x: 0, y: 0.6 }, colors, zIndex: 200001 });
            confetti({ particleCount: 60, angle: 120, spread: 55, origin: { x: 1, y: 0.6 }, colors, zIndex: 200001 });
        }, 400);
    }

    // ── XP Slot Machine Counter ──
    function _animateXpCounter(el, target) {
        if (!el || target <= 0) return;
        const duration = 2200;
        const start = performance.now();
        const update = (now) => {
            const elapsed = now - start;
            const progress = Math.min(elapsed / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3); // ease-out cubic
            const current = Math.floor(eased * target);
            el.textContent = current.toLocaleString('pt-BR');

            // Haptic pulses every 50 XP
            if (current % 50 === 0 && navigator.vibrate) navigator.vibrate(3);

            if (progress < 1) {
                requestAnimationFrame(update);
            } else {
                el.textContent = target.toLocaleString('pt-BR');
                el.style.transform = 'scale(1.3)';
                el.style.color = 'var(--dd-color, #00d9ff)';
                setTimeout(() => { el.style.transform = 'scale(1)'; }, 250);
            }
        };
        requestAnimationFrame(update);
    }

    // ── BUILD THE OVERLAY ──
    function _buildOverlay(title, message, xpReward, heroColor, lottieUrl) {
        // Inject CSS if not present
        if (!document.getElementById('dopamine-drop-css')) {
            const style = document.createElement('style');
            style.id = 'dopamine-drop-css';
            style.textContent = `
                .dd-overlay {
                    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
                    background: rgba(11, 17, 33, 0.92);
                    backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px);
                    z-index: 200000;
                    display: flex; align-items: center; justify-content: center;
                    padding: 20px;
                    animation: ddFadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1);
                }
                .dd-card {
                    position: relative; width: 100%; max-width: 480px;
                    background: rgba(20, 30, 48, 0.7);
                    border: 1px solid rgba(255, 255, 255, 0.1);
                    border-radius: 32px; padding: 48px 32px; text-align: center;
                    overflow: hidden;
                    box-shadow: 0 40px 100px rgba(0,0,0,0.8), inset 0 1px 0 rgba(255,255,255,0.05);
                    animation: ddSlideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.15s both;
                }
                .dd-shine {
                    position: absolute; top: -50%; left: -50%; width: 200%; height: 200%;
                    background: radial-gradient(circle at center, rgba(255,255,255,0.04) 0%, transparent 70%);
                    animation: ddShine 8s linear infinite; pointer-events: none;
                }
                .dd-lottie-wrap {
                    position: relative; z-index: 2; margin: 0 auto 24px;
                    width: 180px; height: 180px;
                    filter: drop-shadow(0 0 40px var(--dd-color));
                    animation: ddPop 1s cubic-bezier(0.175, 0.885, 0.32, 1.275) 0.4s both;
                }
                .dd-ring {
                    position: absolute; top: 50%; left: 50%; width: 160px; height: 160px;
                    margin: -80px 0 0 -80px; border-radius: 50%;
                    border: 2px solid var(--dd-color); opacity: 0.3;
                    animation: ddRing 2s infinite;
                }
                .dd-subtitle {
                    color: var(--dd-color); font-size: 0.75rem; font-weight: 900;
                    letter-spacing: 3px; margin-bottom: 12px; opacity: 0.9;
                    text-transform: uppercase;
                }
                .dd-title {
                    color: #fff; font-size: 1.8rem; font-weight: 900;
                    line-height: 1.15; margin-bottom: 14px;
                    text-shadow: 0 4px 15px rgba(0,0,0,0.5);
                }
                .dd-message {
                    color: #94a3b8; font-size: 1rem; line-height: 1.5; margin-bottom: 24px;
                }
                .dd-xp-box {
                    display: inline-flex; flex-direction: column; align-items: center;
                    background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);
                    padding: 14px 36px; border-radius: 20px; margin-bottom: 28px;
                }
                .dd-xp-label {
                    font-size: 0.6rem; font-weight: 900; color: var(--dd-color);
                    letter-spacing: 2px; margin-bottom: 4px;
                }
                .dd-xp-row { display: flex; align-items: baseline; gap: 4px; color: #fff; }
                .dd-xp-plus { font-size: 1.2rem; font-weight: 900; color: var(--dd-color); }
                .dd-xp-num {
                    font-size: 2.4rem; font-weight: 900;
                    font-family: 'JetBrains Mono', 'Fira Code', monospace;
                    transition: transform 0.2s, color 0.2s;
                }
                .dd-xp-unit { font-size: 0.9rem; font-weight: 800; opacity: 0.6; }
                .dd-btn {
                    background: var(--dd-color); color: #0B1121; border: none;
                    padding: 16px 44px; border-radius: 16px; font-size: 1rem;
                    font-weight: 800; display: inline-flex; align-items: center;
                    gap: 12px; cursor: pointer; transition: all 0.3s;
                    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
                    text-transform: uppercase; letter-spacing: 1px;
                }
                .dd-btn:hover {
                    transform: translateY(-4px) scale(1.05);
                    filter: brightness(1.15);
                    box-shadow: 0 20px 40px rgba(0,0,0,0.5);
                }
                @keyframes ddFadeIn { from { opacity: 0; } to { opacity: 1; } }
                @keyframes ddSlideUp {
                    from { opacity: 0; transform: translateY(50px) scale(0.88); }
                    to   { opacity: 1; transform: translateY(0) scale(1); }
                }
                @keyframes ddPop {
                    0%   { transform: scale(0) rotate(-30deg); }
                    100% { transform: scale(1) rotate(0deg); }
                }
                @keyframes ddRing {
                    0%   { transform: scale(1); opacity: 0.5; }
                    100% { transform: scale(1.6); opacity: 0; }
                }
                @keyframes ddShine { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
                @media (max-width: 480px) {
                    .dd-card { padding: 36px 20px; border-radius: 24px; }
                    .dd-title { font-size: 1.4rem; }
                    .dd-lottie-wrap { width: 140px; height: 140px; }
                }
            `;
            document.head.appendChild(style);
        }

        const overlay = document.createElement('div');
        overlay.className = 'dd-overlay';
        overlay.style.setProperty('--dd-color', heroColor);
        overlay.innerHTML = `
            <div class="dd-card">
                <div class="dd-shine"></div>
                <div class="dd-lottie-wrap">
                    <lottie-player src="${lottieUrl}" background="transparent"
                        speed="1" style="width:100%;height:100%;" loop autoplay></lottie-player>
                    <div class="dd-ring"></div>
                </div>
                <div class="dd-subtitle">NOVA CONQUISTA DESBLOQUEADA</div>
                <div class="dd-title">${_escHtml(title)}</div>
                <div class="dd-message">${_escHtml(message)}</div>
                ${xpReward > 0 ? `
                    <div class="dd-xp-box">
                        <span class="dd-xp-label">BÔNUS DE EXPERIÊNCIA</span>
                        <div class="dd-xp-row">
                            <span class="dd-xp-plus">+</span>
                            <span class="dd-xp-num" id="dd-xp-counter">0</span>
                            <span class="dd-xp-unit">XP</span>
                        </div>
                    </div>
                ` : ''}
                <button class="dd-btn" id="dd-dismiss-btn">
                    MUITO BOM!
                    <span class="material-icons-round" style="font-size:20px;">celebration</span>
                </button>
            </div>
        `;
        return overlay;
    }

    function _escHtml(str) {
        const d = document.createElement('div');
        d.textContent = str || '';
        return d.innerHTML;
    }

    // ── PUBLIC: Trigger the Dopamine Drop ──
    async function trigger(opts = {}) {
        if (_active) return; // prevent stacking
        _active = true;

        const title    = opts.title || '🏆 Nova Conquista!';
        const message  = opts.message || 'Parabéns pela sua conquista épica!';
        const xpReward = parseInt(opts.xpReward) || 0;
        const notifId  = opts.notifId || null;
        const readUrl  = opts.readUrl || null;
        const type     = opts.type || 'achievement';

        // Determine color + lottie based on type/title
        let heroColor = '#00d9ff';
        let lottieUrl = 'https://lottie.host/c9dba2cc-3e91-4566-9f1a-621a02fd1f76/NI8ShZU01f.json'; // TROPHY (Default)

        if (title.includes('Especialidade') || title.includes('ADRA')) {
            heroColor = '#10b981';
            lottieUrl = 'https://lottie.host/ccfc785a-3937-49cb-9dec-d5524a85e02f/rG3kf2ymFt.json'; // BADGE
        } else if (title.includes('Classe') || title.includes('Programa')) {
            heroColor = '#8b5cf6';
            lottieUrl = 'https://lottie.host/ccfc785a-3937-49cb-9dec-d5524a85e02f/rG3kf2ymFt.json'; // STAR
        } else if (type === 'level_up') {
            heroColor = '#fbbf24';
            lottieUrl = 'https://lottie.host/ccfc785a-3937-49cb-9dec-d5524a85e02f/rG3kf2ymFt.json'; // LEVEL UP
        }

        // Load deps in parallel
        await Promise.all([_ensureConfetti(), _ensureLottie()]);

        // Build and inject overlay
        const overlay = _buildOverlay(title, message, xpReward, heroColor, lottieUrl);
        document.body.appendChild(overlay);

        // T+500ms: Fire confetti + sound + haptics + XP counter
        setTimeout(() => {
            _fireConfetti();
            _playVictorySound();
            if (navigator.vibrate) navigator.vibrate([80, 30, 80, 30, 120]);
            _animateXpCounter(document.getElementById('dd-xp-counter'), xpReward);
        }, 600);

        // Dismiss handler
        const btn = document.getElementById('dd-dismiss-btn');
        if (btn) {
            btn.addEventListener('click', () => {
                // Mark notification as read
                if (notifId && readUrl) {
                    fetch(`${readUrl}/${notifId}/read`, {
                        method: 'POST',
                        headers: { 'X-Background-Request': '1' }
                    }).catch(() => {});
                }

                overlay.style.transition = 'opacity 0.4s, transform 0.4s';
                overlay.style.opacity = '0';
                overlay.style.transform = 'scale(1.08)';
                setTimeout(() => { overlay.remove(); _active = false; }, 400);

                // Refresh notification badge
                if (window.notificationCenter && typeof window.notificationCenter._silentPoll === 'function') {
                    window.notificationCenter._silentPoll();
                }
            });
        }
    }

    return { trigger, isActive: () => _active };
})();
