<?php return; ?>
<?php
/**
 * Hero Celebration Modal - THE DOPAMINE DROP EDITION
 * Displays for high-priority unread achievements (specialties, classes).
 */
$hero = $achievementHero ?? null;
if (!$hero) return;

$heroData = json_decode($hero['data'] ?? '{}', true);
$heroType = $hero['type'] ?? 'achievement';
$heroIcon = 'emoji_events';
$heroColor = '#00d9ff'; // Cyan default
$lottieUrl = 'https://assets3.lottiefiles.com/packages/lf20_toum4v.json'; // Default Trophy

if (strpos($hero['title'] ?? '', 'Especialidade') !== false) {
    $heroIcon = 'military_tech';
    $heroColor = '#10b981'; // Green
    $lottieUrl = 'https://assets10.lottiefiles.com/packages/lf20_wwmreunm.json'; // Badge/Medal
} elseif (strpos($hero['title'] ?? '', 'Classe') !== false) {
    $heroIcon = 'verified';
    $heroColor = '#8b5cf6'; // Purple
    $lottieUrl = 'https://assets5.lottiefiles.com/packages/lf20_5unp9x.json'; // Star/Class
} elseif ($heroType === 'level_up') {
    $heroIcon = 'trending_up';
    $heroColor = '#fbbf24'; // Gold
    $lottieUrl = 'https://assets2.lottiefiles.com/packages/lf20_obhbebg1.json'; // Level Up
}

// Extract XP Bonus if exists
$xpBonus = $heroData['xp_reward'] ?? $heroData['xp'] ?? 0;
?>

<!-- GAMIFICATION LIBRARIES -->
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>
<script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>

<div id="achievement-hero-overlay" class="hero-overlay">
    <div class="hero-modal-content">
        <!-- Shine Effect -->
        <div class="hero-shine"></div>
        
        <!-- Icon HUD Container (Enhanced with Lottie) -->
        <div class="hero-icon-container" style="--hero-color: <?= $heroColor ?>">
            <div class="lottie-wrapper">
                <lottie-player 
                    src="<?= $lottieUrl ?>" 
                    background="transparent" 
                    speed="1" 
                    style="width: 200px; height: 200px;" 
                    loop 
                    autoplay>
                </lottie-player>
            </div>
            <div class="hero-icon-ring"></div>
            <div class="hero-icon-particles"></div>
        </div>

        <!-- Text Content -->
        <div class="hero-text">
            <h3 class="hero-subtitle">NOVA CONQUISTA DESBLOQUEADA</h3>
            <h2 class="hero-title"><?= htmlspecialchars(mb_strtoupper($hero['title'] ?? '')) ?></h2>
            <p class="hero-message"><?= htmlspecialchars($hero['message'] ?? '') ?></p>
            
            <?php if ($xpBonus > 0): ?>
                <div class="hero-xp-badge">
                    <span class="xp-label">BÔNUS DE EXPERIÊNCIA</span>
                    <div class="xp-value-container">
                        <span class="xp-plus">+</span>
                        <span id="hero-xp-counter" class="xp-number">0</span>
                        <span class="xp-unit">XP</span>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Action Button -->
        <div class="hero-actions">
            <button id="hero-celebrate-btn" class="hero-btn" data-id="<?= $hero['id'] ?>">
                MUITO BOM!
                <span class="material-icons-round">celebration</span>
            </button>
        </div>
    </div>
</div>

<audio id="hero-victory-sound" src="<?= base_url('assets/audio/victory.mp3') ?>" preload="auto"></audio>

<style>
.hero-overlay {
    position: fixed;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(11, 17, 33, 0.9);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    animation: heroFadeIn 0.8s cubic-bezier(0.16, 1, 0.3, 1);
}

.hero-modal-content {
    position: relative;
    width: 100%;
    max-width: 500px;
    background: rgba(20, 30, 48, 0.6);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 32px;
    padding: 48px 32px;
    text-align: center;
    overflow: hidden;
    box-shadow: 0 40px 100px rgba(0, 0, 0, 0.8), inset 0 1px 0 rgba(255, 255, 255, 0.05);
    animation: heroSlideUp 1s cubic-bezier(0.16, 1, 0.3, 1) 0.2s both;
}

.hero-shine {
    position: absolute;
    top: -50%; left: -50%; width: 200%; height: 200%;
    background: radial-gradient(circle at center, rgba(255, 255, 255, 0.05) 0%, transparent 70%);
    animation: heroShineRotate 10s linear infinite;
    pointer-events: none;
}

.hero-icon-container {
    position: relative;
    width: 120px;
    height: 120px;
    margin: 0 auto 32px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.hero-main-icon {
    font-size: 64px !important;
    color: var(--hero-color);
    text-shadow: 0 0 30px var(--hero-color);
    z-index: 2;
    animation: heroIconPop 1.2s cubic-bezier(0.175, 0.885, 0.32, 1.275) 0.5s both;
}

.hero-icon-ring {
    position: absolute;
    width: 100%; height: 100%;
    border-radius: 50%;
    border: 2px solid var(--hero-color);
    opacity: 0.3;
    animation: heroRingPulse 2s infinite;
}

.hero-text { margin-bottom: 40px; }
.hero-subtitle {
    color: var(--hero-color);
    font-size: 0.8rem;
    font-weight: 800;
    letter-spacing: 3px;
    margin-bottom: 12px;
    opacity: 0.8;
}
.hero-title {
    color: #fff;
    font-size: 2rem;
    font-weight: 900;
    line-height: 1.1;
    margin-bottom: 16px;
    text-shadow: 0 4px 10px rgba(0,0,0,0.5);
}
.hero-message {
    color: #94a3b8;
    font-size: 1rem;
    line-height: 1.5;
    margin-bottom: 24px;
}

.hero-xp-badge {
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.08);
    padding: 12px 32px;
    border-radius: 20px;
    margin-top: 10px;
}

.xp-label {
    font-size: 0.65rem;
    font-weight: 900;
    color: var(--hero-color);
    letter-spacing: 2px;
    margin-bottom: 4px;
}

.xp-value-container {
    display: flex;
    align-items: baseline;
    gap: 4px;
    color: #fff;
}

.xp-plus { font-size: 1.2rem; font-weight: 900; color: var(--hero-color); }
.xp-number { font-size: 2.2rem; font-weight: 900; font-family: 'JetBrains Mono', monospace; }
.xp-unit { font-size: 0.9rem; font-weight: 800; opacity: 0.6; }

.lottie-wrapper {
    position: relative;
    z-index: 2;
    transform: scale(1.2);
    filter: drop-shadow(0 0 30px var(--hero-color));
    animation: heroIconPop 1.2s cubic-bezier(0.175, 0.885, 0.32, 1.275) 0.5s both;
}

.hero-btn {
    background: var(--hero-color);
    color: #0B1121;
    border: none;
    padding: 16px 40px;
    border-radius: 16px;
    font-size: 1rem;
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    transition: all 0.3s;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}
.hero-btn:hover {
    transform: translateY(-4px) scale(1.05);
    filter: brightness(1.1);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
}

@keyframes heroFadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes heroSlideUp { 
    from { opacity: 0; transform: translateY(40px) scale(0.9); } 
    to { opacity: 1; transform: translateY(0) scale(1); } 
}
@keyframes heroIconPop { 
    0% { transform: scale(0); rotate: -45deg; } 
    100% { transform: scale(1); rotate: 0deg; } 
}
@keyframes heroRingPulse {
    0% { transform: scale(1); opacity: 0.5; }
    100% { transform: scale(1.5); opacity: 0; }
}
@keyframes heroShineRotate {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

@media (max-width: 480px) {
    .hero-modal-content { padding: 32px 24px; }
    .hero-title { font-size: 1.5rem; }
}
</style>

<script>
(function() {
    const btn = document.getElementById('hero-celebrate-btn');
    const overlay = document.getElementById('achievement-hero-overlay');
    const sound = document.getElementById('hero-victory-sound');
    const xpCounter = document.getElementById('hero-xp-counter');
    const xpValue = <?= (int)$xpBonus ?>;
    
    // 1. Initial Blast (Confetti)
    const fireConfetti = () => {
        const count = 200;
        const defaults = { origin: { y: 0.7 }, zIndex: 10001 };

        function fire(particleRatio, opts) {
            confetti({
                ...defaults,
                ...opts,
                particleCount: Math.floor(count * particleRatio)
            });
        }

        fire(0.25, { spread: 26, startVelocity: 55 });
        fire(0.2, { spread: 60 });
        fire(0.35, { spread: 100, decay: 0.91, scalar: 0.8 });
        fire(0.1, { spread: 120, startVelocity: 25, decay: 0.92, scalar: 1.2 });
        fire(0.1, { spread: 120, startVelocity: 45 });
    };

    // 2. XP Ticker & Haptics
    const startTicker = () => {
        if (!xpCounter || xpValue <= 0) return;
        
        let start = 0;
        const duration = 2000;
        const startTime = performance.now();

        const update = (now) => {
            const elapsed = now - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Ease out cubic
            const eased = 1 - Math.pow(1 - progress, 3);
            const current = Math.floor(eased * xpValue);
            
            xpCounter.textContent = current;

            // Haptic Feedback (Discrete pulses)
            if (current % 10 === 0 && navigator.vibrate) {
                navigator.vibrate(5);
            }

            if (progress < 1) {
                requestAnimationFrame(update);
            } else {
                xpCounter.style.transform = 'scale(1.2)';
                xpCounter.style.color = 'var(--hero-color)';
                setTimeout(() => xpCounter.style.transform = 'scale(1)', 200);
            }
        };
        requestAnimationFrame(update);
    };

    // Trigger on Load
    setTimeout(() => {
        fireConfetti();
        startTicker();
        if (navigator.vibrate) navigator.vibrate([100, 30, 100]); // Victory Pulse
    }, 800);

    // Audio Logic
    const playVictory = () => {
        if (sound) {
            sound.volume = 0.4;
            sound.play().catch(() => {});
        }
        document.removeEventListener('click', playVictory);
        document.removeEventListener('touchstart', playVictory);
    };
    document.addEventListener('click', playVictory);
    document.addEventListener('touchstart', playVictory);

    if (btn && overlay) {
        btn.addEventListener('click', async () => {
            const notifId = btn.dataset.id;
            if (notifId) {
                fetch(`/<?= $tenant['slug'] ?>/api/notifications/${notifId}/read`, {
                    method: 'POST',
                    credentials: 'include',
                    headers: { 'X-Background-Request': '1' }
                });
            }

            overlay.style.opacity = '0';
            overlay.style.transform = 'scale(1.1)';
            setTimeout(() => overlay.remove(), 500);
            
            if (window.notificationCenter && typeof window.notificationCenter._silentPoll === 'function') {
                window.notificationCenter._silentPoll();
            }
        });
    }
})();
</script>
