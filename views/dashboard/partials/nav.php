<?php
/**
 * Premium Bottom Navigation - macOS Dock Style
 * Senior UI/UX Design with micro-interactions
 */
$currentPath = $_SERVER['REQUEST_URI'];
$isHome = strpos($currentPath, '/dashboard') !== false && strpos($currentPath, '/atividades') === false;
$isClasses = strpos($currentPath, '/aprendizado') !== false;
$isAgenda = strpos($currentPath, '/eventos') !== false;
$isDesafios = strpos($currentPath, '/provas') !== false;
$isPerfil = strpos($currentPath, '/perfil') !== false;
?>

<nav class="dock-premium" id="dockNav">
    <div class="dock-inner">
        <!-- QG / Home -->
        <a href="<?= base_url($tenant['slug'] . '/dashboard') ?>" 
           class="dock-item <?= $isHome ? 'active' : '' ?>" data-tooltip="Quartel General">
            <div class="dock-icon-wrap">
                <span class="material-icons-round">rocket_launch</span>
                <div class="icon-glow"></div>
            </div>
            <span class="dock-label">QG</span>
            <?php if ($isHome): ?><span class="active-dot"></span><?php endif; ?>
        </a>

        <!-- Missões -->
        <a href="<?= base_url($tenant['slug'] . '/aprendizado') ?>" 
           class="dock-item <?= $isClasses ? 'active' : '' ?>" 
           data-tooltip="Minhas Missões"
           hx-boost="false">
            <div class="dock-icon-wrap">
                <span class="material-icons-round">auto_stories</span>
                <div class="icon-glow"></div>
            </div>
            <span class="dock-label">Missões</span>
            <?php if ($isClasses): ?><span class="active-dot"></span><?php endif; ?>
        </a>

        <!-- Agenda -->
        <a href="<?= base_url($tenant['slug'] . '/eventos') ?>" 
           class="dock-item <?= $isAgenda ? 'active' : '' ?>" data-tooltip="Agenda do Clube">
            <div class="dock-icon-wrap">
                <span class="material-icons-round">calendar_month</span>
                <div class="icon-glow"></div>
            </div>
            <span class="dock-label">Agenda</span>
            <?php if ($isAgenda): ?><span class="active-dot"></span><?php endif; ?>
        </a>

        <!-- Desafios -->
        <a href="<?= base_url($tenant['slug'] . '/provas') ?>" 
           class="dock-item <?= $isDesafios ? 'active' : '' ?>" data-tooltip="Desafios & Missões">
            <div class="dock-icon-wrap">
                <span class="material-icons-round">emoji_events</span>
                <div class="icon-glow"></div>
            </div>
            <span class="dock-label">Desafios</span>
            <?php if ($isDesafios): ?><span class="active-dot"></span><?php endif; ?>
        </a>

        <!-- Perfil -->
        <a href="<?= base_url($tenant['slug'] . '/perfil') ?>" 
           class="dock-item <?= $isPerfil ? 'active' : '' ?>" data-tooltip="Meu QG Pessoal">
            <div class="dock-icon-wrap">
                <span class="material-icons-round">shield</span>
                <div class="icon-glow"></div>
            </div>
            <span class="dock-label">Perfil</span>
            <?php if ($isPerfil): ?><span class="active-dot"></span><?php endif; ?>
        </a>
    </div>
</nav>

<style>
/* ============ PREMIUM DOCK NAVIGATION ============ */
.dock-premium {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 1000;
    /* Consistent 16px margins on all screen sizes */
    padding: 0 16px 16px;
    display: flex;
    justify-content: center;
    pointer-events: none;
}

.dock-inner {
    display: flex;
    align-items: flex-end;
    justify-content: center;
    gap: 4px;
    padding: 10px 16px 12px;
    /* Dark Glass Aesthetic */
    background: linear-gradient(135deg, rgba(26, 26, 46, 0.9), rgba(22, 33, 62, 0.9));
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-radius: 24px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    box-shadow: 
        0 20px 40px rgba(0, 0, 0, 0.4),
        0 0 0 1px rgba(255, 255, 255, 0.05),
        inset 0 1px 0 rgba(255, 255, 255, 0.1);
    pointer-events: auto;
    animation: dockSlideIn 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
    width: fit-content;
    max-width: 100%;
}

@keyframes dockSlideIn {
    from { transform: translateY(100%) scale(0.9); opacity: 0; }
    to { transform: translateY(0) scale(1); opacity: 1; }
}

/* Dock Item */
.dock-item {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    padding: 8px 12px;
    text-decoration: none;
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    border-radius: 16px;
}

.dock-item:hover {
    transform: translateY(-8px) scale(1.15);
}

/* Icon Wrapper with Gradient Background */
.dock-icon-wrap {
    position: relative;
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 14px;
    /* Darker button background */
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.05);
    transition: all 0.3s ease;
}

.dock-icon-wrap .material-icons-round {
    font-size: 26px;
    color: #64748b;
    transition: all 0.3s ease;
    position: relative;
    z-index: 2;
}

.icon-glow {
    position: absolute;
    inset: 0;
    border-radius: 14px;
    opacity: 0;
    transition: opacity 0.3s ease;
}

/* Hover Effects */
.dock-item:hover .dock-icon-wrap {
    background: linear-gradient(135deg, #6366f1, #818cf8);
    box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4);
    transform: rotate(-5deg);
}

.dock-item:hover .dock-icon-wrap .material-icons-round {
    color: #fff;
    transform: scale(1.1);
}

.dock-item:hover .icon-glow {
    opacity: 1;
    background: radial-gradient(circle at center, rgba(99, 102, 241, 0.3), transparent 70%);
}

/* Label */
.dock-label {
    font-size: 0.65rem;
    font-weight: 800;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    transition: all 0.3s ease;
    font-family: 'Nunito', sans-serif;
}

.dock-item:hover .dock-label {
    color: #6366f1;
    transform: scale(1.1);
}

/* Active State */
.dock-item.active .dock-icon-wrap {
    background: linear-gradient(135deg, #6366f1, #4f46e5);
    box-shadow: 0 6px 20px rgba(99, 102, 241, 0.5);
}

.dock-item.active .dock-icon-wrap .material-icons-round {
    color: #fff;
}

.dock-item.active .dock-label {
    color: #6366f1;
    font-weight: 900;
}

.dock-item.active:hover {
    transform: translateY(-8px) scale(1.1);
}

.dock-item.active:hover .dock-icon-wrap {
    transform: rotate(0deg);
}

/* Active Indicator Dot */
.active-dot {
    position: absolute;
    bottom: -2px;
    width: 6px;
    height: 6px;
    background: linear-gradient(135deg, #6366f1, #4f46e5);
    border-radius: 50%;
    box-shadow: 0 0 10px rgba(99, 102, 241, 0.8);
    animation: dotPulse 2s ease-in-out infinite;
}

@keyframes dotPulse {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.5); opacity: 0.7; }
}

/* Tooltip (optional enhancement) */
.dock-item::before {
    content: attr(data-tooltip);
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%) translateY(10px);
    padding: 6px 12px;
    background: rgba(30, 27, 75, 0.95);
    color: #fff;
    font-size: 0.75rem;
    font-weight: 700;
    border-radius: 8px;
    white-space: nowrap;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
    pointer-events: none;
    z-index: 100;
}

.dock-item:hover::before {
    opacity: 1;
    visibility: visible;
    transform: translateX(-50%) translateY(-8px);
}

/* Color variations for each icon on hover (optional flair) */
.dock-item:nth-child(1):hover .dock-icon-wrap { background: linear-gradient(135deg, #06b6d4, #22d3ee); } /* QG - Cyan */
.dock-item:nth-child(2):hover .dock-icon-wrap { background: linear-gradient(135deg, #8b5cf6, #a78bfa); } /* Missões - Purple/Vibrant */
.dock-item:nth-child(3):hover .dock-icon-wrap { background: linear-gradient(135deg, #f472b6, #ec4899); } /* Agenda - Pink */
.dock-item:nth-child(4):hover .dock-icon-wrap { background: linear-gradient(135deg, #f97316, #fb923c); } /* Desafios - Orange */
.dock-item:nth-child(5):hover .dock-icon-wrap { background: linear-gradient(135deg, #22c55e, #4ade80); } /* Perfil - Green */

/* Keep active gradients consistent */
.dock-item.active .dock-icon-wrap,
.dock-item.active:hover .dock-icon-wrap {
    background: linear-gradient(135deg, #6366f1, #4f46e5) !important;
}

/* Safe Area for iOS */
@supports (padding-bottom: env(safe-area-inset-bottom)) {
    .dock-premium {
        padding-bottom: calc(16px + env(safe-area-inset-bottom));
    }
}

/* Mobile only - hide tooltips (touch devices) */
@media (max-width: 768px) {
    .dock-item::before {
        display: none;
    }
}

/* Ultra-small screens (iPhone SE, ≤360px) - prevent overflow */
@media (max-width: 360px) {
    .dock-inner {
        gap: 0;
        padding: 8px 8px 10px;
    }
    
    .dock-item {
        padding: 6px 4px;
    }
    
    .dock-icon-wrap {
        width: 40px;
        height: 40px;
    }
    
    .dock-icon-wrap .material-icons-round {
        font-size: 22px;
    }
    
    .dock-label {
        font-size: 0.55rem;
        letter-spacing: 0;
    }
}
</style>

<style>
/* ... existing styles ... */

/* Mobile Safety Padding - Main Body */
body {
    padding-bottom: 120px !important; 
}

/* Modal Content Safety Padding */
.modal-body {
    padding-bottom: 100px !important;
}

/* Dynamic State */
.dock-premium {
    transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.4s ease;
}

.dock-premium.dock-hidden {
    transform: translateY(150%);
    opacity: 0;
    pointer-events: none;
}
</style>

<!-- Physical Spacer for good measure -->
<div class="dock-spacer" style="height: 100px; width: 100%; pointer-events: none;"></div>

<script>
// macOS Dock-style magnification effect
document.addEventListener('DOMContentLoaded', () => {
   // ... script keeps running ...
    const dock = document.querySelector('.dock-inner');
    const items = document.querySelectorAll('.dock-item');
    
    if (!dock || items.length === 0) return;
    
    dock.addEventListener('mousemove', (e) => {
        // ... magnification logic ...
        const dockRect = dock.getBoundingClientRect();
        const mouseX = e.clientX - dockRect.left;
        
        items.forEach((item) => {
            const itemRect = item.getBoundingClientRect();
            const itemCenterX = itemRect.left + itemRect.width / 2 - dockRect.left;
            const distance = Math.abs(mouseX - itemCenterX);
            const maxDistance = 150;
            
            if (distance < maxDistance) {
                const scale = 1 + (1 - distance / maxDistance) * 0.2;
                const translateY = (1 - distance / maxDistance) * -8;
                item.style.transform = `translateY(${translateY}px) scale(${scale})`;
            } else {
                item.style.transform = '';
            }
        });
    });
    
    dock.addEventListener('mouseleave', () => {
        items.forEach((item) => {
            item.style.transform = '';
        });
    });

    // ==========================================
    // DYNAMIC SCROLL BEHAVIOR (Immersive Mode)
    // ==========================================
    let isScrolling;
    const nav = document.getElementById('dockNav');

    window.addEventListener('scroll', () => {
        // 1. Hide on Scroll
        if (window.scrollY > 50) { // Only active after small threshold
            nav.classList.add('dock-hidden');
        }

        // 2. Show on Stop (Debounce)
        window.clearTimeout(isScrolling);

        isScrolling = setTimeout(() => {
            nav.classList.remove('dock-hidden');
        }, 250); // Show after 250ms of no scrolling
    }, { passive: true });
});
</script>
<script>
    // ... active state logic ...
    // Handle active state on HTMX navigation
    document.body.addEventListener('htmx:pushedIntoHistory', () => updateActiveDock());
    document.body.addEventListener('htmx:historyRestore', () => updateActiveDock());
    
    // Also update on swap in case URL changed
    document.body.addEventListener('htmx:afterSwap', (evt) => {
        updateActiveDock();
    });

    function updateActiveDock() {
        const path = window.location.pathname;
        const items = document.querySelectorAll('.dock-item');
        
        items.forEach(item => {
            const href = item.getAttribute('href');
            
            // Remove active class
            item.classList.remove('active');
            
            // Remove active-dot if exists
            const dot = item.querySelector('.active-dot');
            if (dot) dot.remove();
            
            // Add active class if match
            const isDashboard = href.includes('/dashboard');
            const isCurrentDashboard = path.includes('/dashboard') && !path.includes('/atividades');
            
            if (isDashboard && isCurrentDashboard) {
                setActive(item);
                return;
            }
            
            if (!isDashboard && path.includes(href.split('/').pop())) {
                setActive(item);
            }
        });
    }
    
    function setActive(item) {
        item.classList.add('active');
        if (!item.querySelector('.active-dot')) {
            const dot = document.createElement('span');
            dot.className = 'active-dot';
            item.appendChild(dot);
        }
    }
</script>
