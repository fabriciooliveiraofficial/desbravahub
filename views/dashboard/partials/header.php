<?php
/**
 * Header Partial - Top Bar
 * DESIGN: Deep Glass HUD v3.0 (Transparent/Minimal)
 */
?>
<header class="hud-top-bar">
    <div class="header-inner">
        <!-- Logo Area -->
        <a href="<?= base_url($tenant['slug'] . '/dashboard') ?>" class="hud-brand">
            <div class="hud-logo-icon" style="background: rgba(0, 217, 255, 0.1); border: 1px solid rgba(0, 217, 255, 0.3); box-shadow: 0 0 15px rgba(0, 217, 255, 0.2);">
                <span class="material-icons-round" style="color: var(--accent-cyan); filter: drop-shadow(0 0 5px var(--accent-cyan));">bolt</span>
            </div>
            <div class="hud-brand-text">
                <span class="brand-name" style="font-weight: 900; letter-spacing: 0.1em;"><?= strtoupper(htmlspecialchars($tenant['name'])) ?></span>
                <span class="brand-tagline" style="color: var(--accent-cyan); font-weight: 800; opacity: 0.8;">OPERATIONAL.UNIT</span>
            </div>
        </a>

        <!-- Action Buttons -->
        <div class="header-actions">
            <!-- Notification Bell -->
            <a href="<?= base_url($tenant['slug'] . '/notificacoes') ?>" class="hud-action-btn" id="notificationBtn">
                <span class="material-icons-round">notifications</span>
                <?php if (($unreadCount ?? 0) > 0): ?>
                    <span class="hud-notification-badge"><?= ($unreadCount ?? 0) > 9 ? '9+' : ($unreadCount ?? 0) ?></span>
                <?php endif; ?>
            </a>

            <!-- User Avatar -->
            <a href="<?= base_url($tenant['slug'] . '/perfil') ?>" class="hud-action-btn avatar-btn">
                <div class="hud-avatar-ring">
                    <div class="hud-avatar-inner">
                        <?= strtoupper(substr($user['name'], 0, 1)) ?>
                    </div>
                </div>
            </a>
        </div>
    </div>
</header>