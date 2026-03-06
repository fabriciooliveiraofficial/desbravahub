<?php
/**
 * Unified Notification Bell Partial
 * Requires: $tenant (array), $unreadCount (int, optional)
 */
$bellUnreadCount = $unreadCount ?? 0;
?>
<a href="<?= base_url($tenant['slug'] . '/notificacoes') ?>" class="hud-action-btn notification-bell-link" id="notificationBtn" title="Notificações">
    <div class="bell-wrapper">
        <span class="material-icons-round">notifications</span>
        <?php if ($bellUnreadCount > 0): ?>
            <span class="hud-notification-badge"><?= $bellUnreadCount > 9 ? '9+' : $bellUnreadCount ?></span>
        <?php endif; ?>
    </div>
</a>

<style>
.bell-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
}

.hud-notification-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: var(--danger, #ef4444);
    color: white;
    font-size: 0.65rem;
    font-weight: 900;
    min-width: 18px;
    height: 18px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 4px;
    border: 2px solid rgba(0,0,0,0.2);
    box-shadow: 0 0 10px rgba(239, 68, 68, 0.4);
    animation: badgePop 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

@keyframes badgePop {
    from { transform: scale(0); }
    to { transform: scale(1); }
}

/* Unified styling for both member HUD and Admin header */
.hud-top-bar .notification-bell-link,
.admin-header .notification-bell-link {
    background: rgba(255, 255, 255, 0.05);
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: rgba(255, 255, 255, 0.7);
    transition: all 0.2s;
    text-decoration: none;
    border: 1px solid rgba(255, 255, 255, 0.08);
}

.hud-top-bar .notification-bell-link:hover,
.admin-header .notification-bell-link:hover {
    background: rgba(255, 255, 255, 0.1);
    color: var(--accent-cyan, #00d9ff);
    border-color: rgba(0, 217, 255, 0.3);
}

.admin-header .notification-bell-link {
    width: 40px;
    height: 40px;
    margin-right: 8px;
}

.notification-bell-link .material-icons-round {
    font-size: 24px;
}
</style>
