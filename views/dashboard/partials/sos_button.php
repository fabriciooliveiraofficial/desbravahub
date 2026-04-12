<?php
/**
 * SOS Button - Floating Emergency Component
 * 
 * A panic button that captures user geolocation and sends
 * an immediate SOS alert to all club leaders/admins.
 * Visible on mobile as a small floating button.
 */
?>

<!-- SOS Floating Button -->
<div id="sos-button-wrapper" class="sos-wrapper">
    <button id="sos-trigger" class="sos-btn" aria-label="Botão de Emergência SOS" title="EMERGÊNCIA SOS">
        <span class="sos-icon">🆘</span>
    </button>
</div>

<!-- SOS Confirmation Modal -->
<div id="sos-modal" class="sos-modal" style="display: none;">
    <div class="sos-modal-backdrop" onclick="sosCancel()"></div>
    <div class="sos-modal-content">
        <div class="sos-modal-header">
            <span class="sos-alert-icon">🚨</span>
            <h3>ALERTA DE EMERGÊNCIA</h3>
        </div>
        <p class="sos-modal-desc">Ao confirmar, todos os líderes do clube serão notificados imediatamente com sua localização.</p>

        <div id="sos-gps-status" class="sos-gps-status"></div>

        <div id="sos-countdown" class="sos-countdown" style="display:none;">
            <div class="sos-countdown-ring">
                <svg viewBox="0 0 40 40">
                    <circle class="sos-ring-bg" cx="20" cy="20" r="18"/>
                    <circle id="sos-ring-fill" class="sos-ring-active" cx="20" cy="20" r="18"/>
                </svg>
                <span id="sos-countdown-num">5</span>
            </div>
            <p>Enviando em <strong id="sos-countdown-text">5</strong> segundos...</p>
            <button class="sos-cancel-btn" onclick="sosCancel()">
                <span class="material-icons-round">close</span> Cancelar
            </button>
        </div>

        <div id="sos-confirm-actions" class="sos-actions">
            <button class="sos-confirm-btn" onclick="sosStartCountdown()">
                <span class="material-icons-round">warning</span> ENVIAR SOS AGORA
            </button>
            <button class="sos-dismiss-btn" onclick="sosCancel()">Cancelar</button>
        </div>

        <div id="sos-sending" class="sos-sending" style="display:none;">
            <div class="sos-spinner"></div>
            <p>Capturando localização e enviando alerta...</p>
        </div>

        <div id="sos-result" class="sos-result" style="display:none;">
            <span class="sos-result-icon">✅</span>
            <p id="sos-result-text">SOS enviado com sucesso!</p>
            <button class="sos-dismiss-btn" onclick="sosCancel()">Fechar</button>
        </div>
    </div>
</div>

<style>
/* ============ SOS BUTTON ============ */
.sos-wrapper {
    position: fixed;
    /* Default position if no saved coords */
    bottom: 100px;
    right: 16px;
    z-index: 999;
    touch-action: none; /* Prevent scrolling while dragging on mobile */
}

@media (min-width: 1024px) {
    .sos-wrapper {
        bottom: 24px;
        right: 24px;
    }
}

.sos-btn {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    border: 2px solid rgba(239, 68, 68, 0.5);
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.15), rgba(185, 28, 28, 0.2));
    backdrop-filter: blur(12px);
    cursor: grab;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    animation: sosPulse 3s ease-in-out infinite;
    user-select: none;
    -webkit-user-drag: none;
}

.sos-btn:active {
    cursor: grabbing;
}

.sos-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 25px rgba(239, 68, 68, 0.5);
}

.sos-icon {
    font-size: 1.3rem;
}

@keyframes sosPulse {
    0%, 100% { box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3); }
    50% { box-shadow: 0 4px 25px rgba(239, 68, 68, 0.5); }
}

/* ============ SOS MODAL ============ */
.sos-modal {
    position: fixed;
    inset: 0;
    z-index: 10001;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.sos-modal-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(6px);
}

.sos-modal-content {
    position: relative;
    background: linear-gradient(135deg, #1a1a2e, #16213e);
    border: 1px solid rgba(239, 68, 68, 0.3);
    border-radius: 20px;
    padding: 32px 24px;
    max-width: 360px;
    width: 100%;
    text-align: center;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5), 0 0 40px rgba(239, 68, 68, 0.15);
    animation: sosModalIn 0.3s ease;
}

@keyframes sosModalIn {
    from { transform: scale(0.9); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}

.sos-modal-header {
    margin-bottom: 16px;
}

.sos-alert-icon {
    font-size: 2.5rem;
    display: block;
    margin-bottom: 8px;
}

.sos-modal-header h3 {
    font-size: 1.1rem;
    font-weight: 900;
    color: #f87171;
    letter-spacing: 0.1em;
    margin: 0;
}

.sos-modal-desc {
    font-size: 0.8rem;
    color: #94a3b8;
    line-height: 1.5;
    margin-bottom: 24px;
}

/* Countdown */
.sos-countdown {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;
}

.sos-countdown p {
    font-size: 0.8rem;
    color: #94a3b8;
}

.sos-countdown-ring {
    position: relative;
    width: 70px;
    height: 70px;
}

.sos-countdown-ring svg {
    width: 100%;
    height: 100%;
    transform: rotate(-90deg);
}

.sos-ring-bg {
    fill: none;
    stroke: rgba(255, 255, 255, 0.06);
    stroke-width: 3;
}

.sos-ring-active {
    fill: none;
    stroke: #ef4444;
    stroke-width: 3;
    stroke-linecap: round;
    stroke-dasharray: 113;
    stroke-dashoffset: 0;
    transition: stroke-dashoffset 1s linear;
}

#sos-countdown-num {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 900;
    color: #f87171;
}

/* Buttons */
.sos-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.sos-confirm-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 14px 24px;
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: #fff;
    border: none;
    border-radius: 14px;
    font-size: 0.85rem;
    font-weight: 800;
    cursor: pointer;
    letter-spacing: 0.05em;
    box-shadow: 0 4px 20px rgba(239, 68, 68, 0.4);
    transition: all 0.3s ease;
}

.sos-confirm-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(239, 68, 68, 0.6);
}

.sos-dismiss-btn, .sos-cancel-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 12px 24px;
    background: rgba(255, 255, 255, 0.05);
    color: #94a3b8;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
}

.sos-cancel-btn:hover, .sos-dismiss-btn:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
}

/* Sending State */
.sos-sending {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;
}

.sos-sending p {
    font-size: 0.8rem;
    color: #94a3b8;
}

.sos-spinner {
    width: 40px;
    height: 40px;
    border: 3px solid rgba(239, 68, 68, 0.2);
    border-top-color: #ef4444;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }

/* Result */
.sos-result {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
}

.sos-result-icon {
    font-size: 2.5rem;
}

.sos-result p {
    font-size: 0.85rem;
    color: #22c55e;
    font-weight: 700;
}

/* GPS Status Indicator */
.sos-gps-status {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    font-size: 0.72rem;
    color: #64748b;
    min-height: 20px;
    margin-bottom: 16px;
    transition: color 0.3s ease;
}
.sos-gps-status.searching { color: #64748b; }
.sos-gps-status.improving { color: #f59e0b; }
.sos-gps-status.gold      { color: #22c55e; }
.sos-gps-status.error     { color: #ef4444; }

.sos-gps-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    display: inline-block;
    flex-shrink: 0;
    background: currentColor;
}
.sos-gps-dot.searching { animation: gpsPulse 1s ease-in-out infinite; }

@keyframes gpsPulse {
    0%, 100% { opacity: 0.3; }
    50%       { opacity: 1; }
}

/* Safe area for iOS */
@supports (padding-bottom: env(safe-area-inset-bottom)) {
    .sos-wrapper {
        bottom: calc(100px + env(safe-area-inset-bottom));
    }
}
</style>

<script>
// ============ SOS BUTTON LOGIC ============
const SOS_GPS_GOLD_STANDARD = 30;  // metros — precisão considerada "excelente"
const SOS_GPS_EXTRA_WAIT    = 10000; // ms máx de espera extra após o countdown

let sosCountdownTimer  = null;
let sosCountdownValue  = 5;
let sosWatchId         = null;   // watchPosition handle
let sosBestPosition    = null;   // melhor fix GPS até agora
let sosGpsExtraResolve = null;   // resolve da promise de espera extra
let sosGpsExtraTimer   = null;
let sosCancelled       = false;

// ============ DRAGGABLE LOGIC ============
const SOSDraggable = {
    wrapper: document.getElementById('sos-button-wrapper'),
    btn: document.getElementById('sos-trigger'),
    active: false,
    currentX: 0,
    currentY: 0,
    initialX: 0,
    initialY: 0,
    xOffset: 0,
    yOffset: 0,
    isDragging: false,
    dragThreshold: 5,
    
    init() {
        if (!this.wrapper || !this.btn) return;

        this.loadPosition();

        // Mouse Events
        this.wrapper.addEventListener('mousedown', (e) => this.dragStart(e), false);
        window.addEventListener('mousemove', (e) => this.drag(e), false);
        window.addEventListener('mouseup', (e) => this.dragEnd(e), false);

        // Touch Events
        this.wrapper.addEventListener('touchstart', (e) => this.dragStart(e), { passive: false });
        window.addEventListener('touchmove', (e) => this.drag(e), { passive: false });
        window.addEventListener('touchend', (e) => this.dragEnd(e), false);
    },

    dragStart(e) {
        if (e.type === 'touchstart') {
            this.initialX = e.touches[0].clientX - this.xOffset;
            this.initialY = e.touches[0].clientY - this.yOffset;
        } else {
            this.initialX = e.clientX - this.xOffset;
            this.initialY = e.clientY - this.yOffset;
        }

        if (e.target === this.btn || this.btn.contains(e.target)) {
            this.active = true;
            this.isDragging = false;
        }
    },

    drag(e) {
        if (this.active) {
            if (e.type === 'touchmove') e.preventDefault();

            let clientX = e.type === 'touchmove' ? e.touches[0].clientX : e.clientX;
            let clientY = e.type === 'touchmove' ? e.touches[0].clientY : e.clientY;

            let moveX = clientX - this.initialX;
            let moveY = clientY - this.initialY;

            // Check threshold
            if (!this.isDragging && (Math.abs(moveX - this.xOffset) > this.dragThreshold || Math.abs(moveY - this.yOffset) > this.dragThreshold)) {
                this.isDragging = true;
                this.btn.style.transition = 'none'; // Disable transition during drag
            }

            if (this.isDragging) {
                // Apply boundaries
                const rect = this.wrapper.getBoundingClientRect();
                const viewportWidth = window.innerWidth;
                const viewportHeight = window.innerHeight;

                // Restrict X
                if (clientX >= 0 && clientX <= viewportWidth) {
                    this.currentX = moveX;
                }
                
                // Restrict Y
                if (clientY >= 0 && clientY <= viewportHeight) {
                    this.currentY = moveY;
                }

                this.setTranslate(this.currentX, this.currentY, this.wrapper);
            }
        }
    },

    dragEnd(e) {
        if (!this.active) return;
        
        this.initialX = this.currentX;
        this.initialY = this.currentY;
        this.active = false;
        
        if (this.isDragging) {
            this.btn.style.transition = 'transform 0.3s ease, box-shadow 0.3s ease';
            this.savePosition();
        }
        
        // Delay resetting isDragging to allow the click event to check it
        setTimeout(() => {
            this.isDragging = false;
        }, 50);
    },

    setTranslate(xPos, yPos, el) {
        this.xOffset = xPos;
        this.yOffset = yPos;
        el.style.transform = `translate3d(${xPos}px, ${yPos}px, 0)`;
    },

    savePosition() {
        const pos = { x: this.xOffset, y: this.yOffset };
        localStorage.setItem('sos_btn_position', JSON.stringify(pos));
    },

    loadPosition() {
        const saved = localStorage.getItem('sos_btn_position');
        if (saved) {
            try {
                const pos = JSON.parse(saved);
                this.currentX = pos.x;
                this.currentY = pos.y;
                this.initialX = pos.x;
                this.initialY = pos.y;
                this.setTranslate(pos.x, pos.y, this.wrapper);
            } catch (e) {
                console.warn('[SOS] Error loading saved position');
            }
        }
    }
};

// Initialize draggable
SOSDraggable.init();

// ---- Abrir modal: inicia warm-up de GPS imediatamente ----
document.getElementById('sos-trigger')?.addEventListener('click', () => {
    if (SOSDraggable.isDragging) return; // Prevent opening if we just finished dragging
    
    sosCancelled = false;
    sosBestPosition = null;

    document.getElementById('sos-modal').style.display = 'flex';
    document.getElementById('sos-confirm-actions').style.display = 'flex';
    document.getElementById('sos-countdown').style.display = 'none';
    document.getElementById('sos-sending').style.display = 'none';
    document.getElementById('sos-result').style.display = 'none';

    sosStartGpsWarmup();
});

// ---- GPS Warm-up: watchPosition para capturar o melhor fix ----
function sosStartGpsWarmup() {
    if (!navigator.geolocation) {
        sosSetGpsStatus('error');
        return;
    }
    sosSetGpsStatus('searching');

    sosWatchId = navigator.geolocation.watchPosition(
        (position) => {
            const acc = position.coords.accuracy;
            // Guarda apenas o fix mais preciso (menor accuracy = melhor)
            if (!sosBestPosition || acc < sosBestPosition.coords.accuracy) {
                sosBestPosition = position;
                const state = acc <= SOS_GPS_GOLD_STANDARD ? 'gold' : 'improving';
                sosSetGpsStatus(state, acc);
            }
            // Se atingiu padrão ouro durante a espera extra, resolve imediatamente
            if (position.coords.accuracy <= SOS_GPS_GOLD_STANDARD && sosGpsExtraResolve) {
                const res = sosGpsExtraResolve;
                sosGpsExtraResolve = null;
                clearTimeout(sosGpsExtraTimer);
                res();
            }
        },
        () => { if (!sosBestPosition) sosSetGpsStatus('error'); },
        { enableHighAccuracy: true, maximumAge: 0, timeout: 30000 }
    );
}

function sosStopGpsWarmup() {
    if (sosWatchId !== null) {
        navigator.geolocation.clearWatch(sosWatchId);
        sosWatchId = null;
    }
    if (sosGpsExtraTimer !== null) {
        clearTimeout(sosGpsExtraTimer);
        sosGpsExtraTimer = null;
    }
    sosGpsExtraResolve = null;
}

function sosSetGpsStatus(state, accuracy) {
    const el = document.getElementById('sos-gps-status');
    if (!el) return;
    const labels = {
        searching: '<span class="sos-gps-dot searching"></span> Capturando GPS...',
        improving: `<span class="sos-gps-dot improving"></span> GPS: ±${Math.round(accuracy)}m`,
        gold:      `<span class="sos-gps-dot gold"></span> GPS preciso ±${Math.round(accuracy)}m`,
        error:     '<span class="sos-gps-dot error"></span> GPS indisponível',
    };
    el.innerHTML  = labels[state] ?? '';
    el.className  = `sos-gps-status ${state}`;
}

// ---- Countdown ----
function sosStartCountdown() {
    document.getElementById('sos-confirm-actions').style.display = 'none';
    document.getElementById('sos-countdown').style.display = 'flex';
    sosCountdownValue = 5;

    const numEl  = document.getElementById('sos-countdown-num');
    const textEl = document.getElementById('sos-countdown-text');
    const ring   = document.getElementById('sos-ring-fill');
    const CIRC   = 113; // 2π × 18

    numEl.textContent  = sosCountdownValue;
    textEl.textContent = sosCountdownValue;
    ring.style.strokeDashoffset = 0;

    sosCountdownTimer = setInterval(() => {
        sosCountdownValue--;
        numEl.textContent  = sosCountdownValue;
        textEl.textContent = sosCountdownValue;
        ring.style.strokeDashoffset = CIRC * ((5 - sosCountdownValue) / 5);

        if (sosCountdownValue <= 0) {
            clearInterval(sosCountdownTimer);
            sosSend();
        }
    }, 1000);
}

// ---- Cancelar ----
function sosCancel() {
    sosCancelled = true;
    clearInterval(sosCountdownTimer);
    sosStopGpsWarmup();
    document.getElementById('sos-modal').style.display = 'none';
}

// ---- Enviar SOS ----
async function sosSend() {
    if (sosCancelled) return;

    document.getElementById('sos-countdown').style.display = 'none';
    document.getElementById('sos-sending').style.display = 'flex';
    const sendingMsg = document.querySelector('.sos-sending p');

    // Se ainda não atingiu padrão ouro, aguarda até SOS_GPS_EXTRA_WAIT ms
    const hasGold = sosBestPosition && sosBestPosition.coords.accuracy <= SOS_GPS_GOLD_STANDARD;
    if (!hasGold && sosWatchId !== null) {
        if (sendingMsg) sendingMsg.textContent = 'Aguardando GPS de alta precisão...';
        await new Promise((resolve) => {
            sosGpsExtraResolve = resolve;
            sosGpsExtraTimer   = setTimeout(() => {
                sosGpsExtraResolve = null;
                resolve();
            }, SOS_GPS_EXTRA_WAIT);
        });
    }

    if (sosCancelled) return;
    sosStopGpsWarmup();

    const lat      = sosBestPosition?.coords.latitude  ?? null;
    const lng      = sosBestPosition?.coords.longitude ?? null;
    const accuracy = sosBestPosition?.coords.accuracy  ?? null;

    if (sendingMsg) sendingMsg.textContent = 'Enviando alerta de emergência...';

    try {
        const tenantSlug = '<?= $tenant['slug'] ?? '' ?>';
        const response = await fetch(`/${tenantSlug}/api/sos/trigger`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ lat, lng, accuracy })
        });

        let data;
        try { data = await response.json(); }
        catch (_) { data = { success: false, error: 'Erro no servidor (resposta inválida). Tente ligar para um líder.' }; }

        document.getElementById('sos-sending').style.display = 'none';
        document.getElementById('sos-result').style.display  = 'flex';

        const resultIcon = document.querySelector('.sos-result-icon');
        const resultText = document.getElementById('sos-result-text');

        if (data.success) {
            resultIcon.textContent = '✅';
            resultText.textContent = data.message || 'SOS enviado com sucesso!';
            resultText.style.color = '#22c55e';
        } else {
            resultIcon.textContent = '⚠️';
            resultText.textContent = data.error || 'Erro ao enviar SOS. Tente ligar para um líder.';
            resultText.style.color = '#f87171';
        }
    } catch (err) {
        console.error('[SOS] Network error:', err);
        document.getElementById('sos-sending').style.display = 'none';
        document.getElementById('sos-result').style.display  = 'flex';
        document.querySelector('.sos-result-icon').textContent   = '⚠️';
        document.getElementById('sos-result-text').textContent   = 'Erro de conexão com o servidor. Tente ligar para um líder.';
        document.getElementById('sos-result-text').style.color   = '#f87171';
    }
}
</script>
