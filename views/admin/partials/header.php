<?php
/**
 * Admin Header Partial
 * Requires: $pageTitle (string), $user (array), $tenant (array)
 */
?>
<header class="admin-header">
    <!-- Left Side: Icon & Title -->
    <div class="header-title-group">
        <div class="header-icon-box">
            <span class="material-icons-outlined"><?= $pageIcon ?? 'dashboard' ?></span>
        </div>
        <h2 class="header-title"><?= $pageTitle ?? 'Painel' ?></h2>
    </div>

    <!-- Right Side: Actions -->
    <div class="header-actions">
        <!-- Theme Toggle -->
        <button class="theme-toggle" id="theme-toggle" title="Alternar Tema">
            <span class="material-icons-outlined" id="theme-icon-dark" style="display: block;">dark_mode</span>
            <span class="material-icons-outlined" id="theme-icon-light" style="display: none;">light_mode</span>
        </button>

        <!-- User Info & Logout -->
        <div class="user-actions">
            <div class="user-info">
                <span class="user-name"><?= htmlspecialchars($user['name']) ?></span>
                <span class="user-email"><?= htmlspecialchars($user['email']) ?></span>
            </div>
            <a href="<?= base_url($tenant['slug'] . '/logout') ?>" class="btn-logout">Sair</a>
        </div>
    </div>
</header>