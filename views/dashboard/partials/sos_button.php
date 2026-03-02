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
    bottom: 100px;
    right: 16px;
    z-index: 999;
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
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
    transition: all 0.3s ease;
    animation: sosPulse 3s ease-in-out infinite;
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

/* Safe area for iOS */
@supports (padding-bottom: env(safe-area-inset-bottom)) {
    .sos-wrapper {
        bottom: calc(100px + env(safe-area-inset-bottom));
    }
}
</style>

<script>
// ============ SOS BUTTON LOGIC ============
let sosCountdownTimer = null;
let sosCountdownValue = 5;

document.getElementById('sos-trigger')?.addEventListener('click', () => {
    document.getElementById('sos-modal').style.display = 'flex';
    document.getElementById('sos-confirm-actions').style.display = 'flex';
    document.getElementById('sos-countdown').style.display = 'none';
    document.getElementById('sos-sending').style.display = 'none';
    document.getElementById('sos-result').style.display = 'none';
});

function sosStartCountdown() {
    document.getElementById('sos-confirm-actions').style.display = 'none';
    document.getElementById('sos-countdown').style.display = 'flex';
    sosCountdownValue = 5;
    
    const numEl = document.getElementById('sos-countdown-num');
    const textEl = document.getElementById('sos-countdown-text');
    const ringFill = document.getElementById('sos-ring-fill');
    const circumference = 113; // 2 * PI * 18

    numEl.textContent = sosCountdownValue;
    textEl.textContent = sosCountdownValue;
    ringFill.style.strokeDashoffset = 0;

    sosCountdownTimer = setInterval(() => {
        sosCountdownValue--;
        numEl.textContent = sosCountdownValue;
        textEl.textContent = sosCountdownValue;
        ringFill.style.strokeDashoffset = circumference * ((5 - sosCountdownValue) / 5);

        if (sosCountdownValue <= 0) {
            clearInterval(sosCountdownTimer);
            sosSend();
        }
    }, 1000);
}

function sosCancel() {
    clearInterval(sosCountdownTimer);
    document.getElementById('sos-modal').style.display = 'none';
}

async function sosSend() {
    document.getElementById('sos-countdown').style.display = 'none';
    document.getElementById('sos-sending').style.display = 'flex';

    // Get geolocation
    let lat = null, lng = null;
    try {
        const position = await new Promise((resolve, reject) => {
            navigator.geolocation.getCurrentPosition(resolve, reject, {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            });
        });
        lat = position.coords.latitude;
        lng = position.coords.longitude;
    } catch (e) {
        console.warn('[SOS] Geolocation failed:', e.message);
        // Continue without location
    }

    // Send SOS
    try {
        const tenantSlug = '<?= $tenant['slug'] ?? '' ?>';
        const response = await fetch(`/${tenantSlug}/api/sos/trigger`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ lat, lng })
        });

        const data = await response.json();

        document.getElementById('sos-sending').style.display = 'none';
        document.getElementById('sos-result').style.display = 'flex';

        if (data.success) {
            document.getElementById('sos-result-text').textContent = data.message;
        } else {
            document.querySelector('.sos-result-icon').textContent = '⚠️';
            document.getElementById('sos-result-text').textContent = data.error || 'Erro ao enviar SOS';
            document.getElementById('sos-result-text').style.color = '#f87171';
        }
    } catch (err) {
        document.getElementById('sos-sending').style.display = 'none';
        document.getElementById('sos-result').style.display = 'flex';
        document.querySelector('.sos-result-icon').textContent = '⚠️';
        document.getElementById('sos-result-text').textContent = 'Erro de conexão. Tente ligar para um líder.';
        document.getElementById('sos-result-text').style.color = '#f87171';
    }
}
</script>
