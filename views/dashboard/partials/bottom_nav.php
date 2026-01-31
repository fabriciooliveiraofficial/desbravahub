<?php
/**
 * Bottom Navigation Partial
 * Used for mobile navigation on member dashboard
 */
?>
<nav class="bottom-nav">
    <a href="<?= base_url($tenant['slug'] . '/dashboard') ?>" class="nav-item <?= str_contains($_SERVER['REQUEST_URI'], '/dashboard') ? 'active' : '' ?>">
        <span class="material-icons-round nav-icon">home</span>
        <span class="nav-label">Início</span>
    </a>
    
    <a href="<?= base_url($tenant['slug'] . '/atividades') ?>" class="nav-item <?= str_contains($_SERVER['REQUEST_URI'], '/atividades') ? 'active' : '' ?>">
        <span class="material-icons-round nav-icon">explore</span>
        <span class="nav-label">Atividades</span>
    </a>
    
    <a href="<?= base_url($tenant['slug'] . '/ranking') ?>" class="nav-item <?= str_contains($_SERVER['REQUEST_URI'], '/ranking') ? 'active' : '' ?>">
        <span class="material-icons-round nav-icon">leaderboard</span>
        <span class="nav-label">Ranking</span>
    </a>
    
    <a href="<?= base_url($tenant['slug'] . '/perfil') ?>" class="nav-item <?= str_contains($_SERVER['REQUEST_URI'], '/perfil') ? 'active' : '' ?>">
        <span class="material-icons-round nav-icon">person</span>
        <span class="nav-label">Perfil</span>
    </a>
</nav>

<style>
    .bottom-nav {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(20, 30, 60, 0.95);
        backdrop-filter: blur(12px);
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        display: flex;
        justify-content: space-around;
        padding: 12px 16px 24px; /* Extra padding for safe area */
        z-index: 1000;
        box-shadow: 0 -10px 30px rgba(0, 0, 0, 0.2);
    }

    .nav-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        text-decoration: none;
        color: rgba(255, 255, 255, 0.5);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        min-width: 64px;
        position: relative;
    }

    .nav-icon {
        font-size: 1.6rem;
        transition: transform 0.3s;
    }

    .nav-label {
        font-size: 0.75rem;
        font-weight: 500;
    }

    .nav-item.active {
        color: var(--accent-cyan);
    }

    .nav-item.active .nav-icon {
        transform: translateY(-4px);
        filter: drop-shadow(0 0 8px rgba(0, 217, 255, 0.5));
    }

    /* Active indicator dot */
    .nav-item.active::after {
        content: '';
        position: absolute;
        bottom: -8px;
        width: 4px;
        height: 4px;
        background: var(--accent-cyan);
        border-radius: 50%;
        box-shadow: 0 0 6px var(--accent-cyan);
    }

    .nav-item:hover {
        color: rgba(255, 255, 255, 0.9);
    }

    @media (min-width: 769px) {
        .bottom-nav {
            display: none;
        }
    }
</style>
