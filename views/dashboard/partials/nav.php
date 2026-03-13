<?php
/**
 * Sidebar Navigation
 * DESIGN: Pixel-Perfect Reference (Vertical Sidebar)
 */
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pathSegments = array_values(array_filter(explode('/', trim($currentPath, '/'))));

// The pattern is [optional_prefixes...]/tenant_slug/page_name/...
$tenantSlug = $tenant['slug'] ?? '';
$pageSegment = '';

// Find the tenant slug in the URL segments
$tenantIndex = array_search($tenantSlug, $pathSegments);

if ($tenantIndex !== false && isset($pathSegments[$tenantIndex + 1])) {
    // The page name is the segment immediately following the tenant slug
    $pageSegment = $pathSegments[$tenantIndex + 1];
} elseif ($tenantIndex === false && !empty($pathSegments)) {
    // Fallback: If tenant slug isn't found (e.g., custom domains, though rare here), 
    // try to guess based on known routes or default to the last segment
    $pageSegment = end($pathSegments); 
}

$isHome       = ($pageSegment === 'dashboard');
$isTrilhas    = ($pageSegment === 'trilhas');
$isClasses    = ($pageSegment === 'aprendizado');
$isAgenda     = ($pageSegment === 'eventos');
$isConquistas = ($pageSegment === 'conquistas');
$isRanking    = (strpos($pageSegment, 'ranking') === 0);
$isPerfil     = ($pageSegment === 'perfil');
?>

<aside class="sidebar-v3">
    <div class="sidebar-logo">
        <div class="brand-icon">
            <i class="fas fa-compass"></i>
        </div>
    </div>

    <nav class="sidebar-nav">
        <!-- Dashboard / Home -->
        <a href="<?= base_url($tenant['slug'] . '/dashboard') ?>"
           class="nav-item <?= $isHome ? 'active' : '' ?>" data-nav="home">
            <span class="material-icons-round">grid_view</span>
            <span class="nav-label">Home</span>
        </a>

        <!-- Skills -> Trilhas -->
        <a href="<?= base_url($tenant['slug'] . '/trilhas') ?>"
           class="nav-item <?= $isTrilhas ? 'active' : '' ?>" data-nav="trilhas" hx-boost="false">
            <span class="material-icons-round">layers</span>
            <span class="nav-label">Trilhas</span>
        </a>

        <!-- Events -> Agenda -->
        <a href="<?= base_url($tenant['slug'] . '/eventos') ?>"
           class="nav-item <?= $isAgenda ? 'active' : '' ?>" data-nav="agenda">
            <span class="material-icons-round">calendar_today</span>
            <span class="nav-label">Agenda</span>
        </a>

        <!-- Badges / Desafios -->
        <a href="<?= base_url($tenant['slug'] . '/conquistas') ?>"
           class="nav-item <?= $isConquistas ? 'active' : '' ?>" data-nav="conquistas">
            <span class="material-icons-round">emoji_events</span>
            <span class="nav-label">Desafios</span>
        </a>

        <!-- Ranking -->
        <a href="<?= base_url($tenant['slug'] . '/ranking') ?>"
           class="nav-item <?= $isRanking ? 'active' : '' ?>" data-nav="ranking">
            <span class="material-icons-round">leaderboard</span>
            <span class="nav-label">Ranking</span>
        </a>

        <!-- Profile / Perfil -->
        <a href="<?= base_url($tenant['slug'] . '/perfil') ?>"
           class="nav-item <?= $isPerfil ? 'active' : '' ?>" data-nav="perfil">
            <span class="material-icons-round">person_outline</span>
            <span class="nav-label">Perfil</span>
        </a>
    </nav>

    <div class="sidebar-bottom">
        <a href="<?= base_url('auth/logout') ?>" class="nav-item" title="Sair">
            <span class="material-icons-round">logout</span>
        </a>
    </div>
</aside>

<style>
/* Variable Defaults */
:root {
    --sidebar-width: 100px;
    --neon-green: #34d399; /* Vibrant green from image */
    --neon-green-glow: rgba(52, 211, 153, 0.4);
    --dark-bg: #0B1121; /* Deep space blue */
    --panel-bg: rgba(20, 30, 48, 0.6);
}

.sidebar-v3 {
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    width: var(--sidebar-width);
    background: var(--dark-bg);
    border-right: 1px solid rgba(255,255,255,0.05);
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 24px 0;
    z-index: 1000;
}

.sidebar-logo {
    margin-bottom: 40px;
}

.brand-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--accent-cyan, #06b6d4);
    font-size: 28px;
    text-shadow: 0 0 15px rgba(6, 182, 212, 0.5);
}

.sidebar-nav {
    display: flex;
    flex-direction: column;
    gap: 8px;
    width: 100%;
    padding: 0 12px;
}

.nav-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 4px;
    color: #64748b;
    text-decoration: none;
    border-radius: 12px;
    transition: all 0.3s ease;
}

.nav-item .material-icons-round {
    font-size: 26px;
    transition: all 0.3s ease;
}

.nav-item .nav-label {
    font-size: 0.65rem;
    font-family: 'Inter', sans-serif;
    font-weight: 600;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
}

.nav-item:hover {
    color: #cbd5e1;
    background: rgba(255,255,255,0.03);
}

.nav-item.active {
    color: var(--neon-green);
    position: relative;
}

.nav-item.active .material-icons-round {
    filter: drop-shadow(0 0 8px var(--neon-green-glow));
}

.nav-item.active::before {
    content: '';
    position: absolute;
    left: -12px;
    top: 50%;
    transform: translateY(-50%);
    width: 4px;
    height: 24px;
    background: var(--neon-green);
    border-radius: 0 4px 4px 0;
    box-shadow: 0 0 10px var(--neon-green-glow);
}

.sidebar-bottom {
    margin-top: auto;
    width: 100%;
    padding: 0 12px;
}

/* Base Body Adjustments for Sidebar */
body.hud-body {
    padding-left: var(--sidebar-width) !important;
    padding-bottom: 0 !important;
    background: var(--dark-bg);
}

/* Mobile Responsiveness (Hides sidebar, turns to simple bottom nav or drawer) */
@media (max-width: 1024px) {
    .sidebar-v3 {
        width: 80px;
    }
    body.hud-body { padding-left: 80px !important; }
}

@media (max-width: 768px) {
    .sidebar-v3 {
        top: auto;
        bottom: 0;
        width: 100%;
        height: 70px;
        flex-direction: row;
        border-right: none;
        border-top: 1px solid rgba(255,255,255,0.05);
        padding: 0;
    }
    .sidebar-logo, .sidebar-bottom { display: none; }
    .sidebar-nav {
        flex-direction: row;
        justify-content: space-around;
        padding: 0;
        margin: 0;
    }
    .nav-item { padding: 8px 4px; }
    .nav-item.active::before {
        left: 50%; /* Top indicator */
        top: 0;
        transform: translateX(-50%);
        width: 24px;
        height: 3px;
        border-radius: 0 0 4px 4px;
    }
    body.hud-body {
        padding-left: 0 !important;
        padding-bottom: 70px !important;
    }
}
</style>

<script>
(function () {
    // Map of URL page segment → nav item selector
    const NAV_MAP = {
        'dashboard':   '[data-nav="home"]',
        'trilhas':     '[data-nav="trilhas"]',
        'aprendizado': '[data-nav="aprendizado"]',
        'eventos':     '[data-nav="agenda"]',
        'conquistas':  '[data-nav="conquistas"]',
        'ranking':     '[data-nav="ranking"]',
        'perfil':      '[data-nav="perfil"]',
    };

    const tenantSlug = <?= json_encode($tenantSlug) ?>;

    function updateActiveNav() {
        const segments = window.location.pathname.split('/').filter(Boolean);
        const tenantIndex = segments.indexOf(tenantSlug);
        const pageSegment = tenantIndex !== -1 ? (segments[tenantIndex + 1] || '') : '';

        document.querySelectorAll('.sidebar-nav .nav-item').forEach(el => el.classList.remove('active'));

        const selector = NAV_MAP[pageSegment];
        if (selector) {
            const el = document.querySelector(selector);
            if (el) el.classList.add('active');
        }
    }

    // Run on HTMX navigation events
    document.addEventListener('htmx:pushedIntoHistory', updateActiveNav);
    document.addEventListener('htmx:replacedInHistory', updateActiveNav);

    // Run on initial load (covers hard-refresh)
    updateActiveNav();
})();
</script>
