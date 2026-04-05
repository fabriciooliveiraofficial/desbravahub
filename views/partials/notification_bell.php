<?php
/**
 * Notification Bell — abre o Notification Center popover
 * Requires: $tenant (array), $unreadCount (int, optional)
 */
if (!isset($unreadCount)) {
    $notifService = new \App\Services\NotificationService();
    $unreadCount = $notifService->getUnreadCount(\App\Core\App::user()['id'] ?? 0);
}
$bellUnreadCount = $unreadCount ?? 0;

// Rota de destino contextual (admin vs member)
$currentUri  = $_SERVER['REQUEST_URI'] ?? '';
$notifPath   = (strpos($currentUri, '/admin') !== false) ? '/admin/notificacoes' : '/notificacoes';
$tenantSlug  = $tenant['slug'] ?? '';

$ncApiUrl      = base_url($tenantSlug . '/api/notifications');
$ncUnreadUrl   = base_url($tenantSlug . '/api/notifications/unread');
$ncReadAllUrl  = base_url($tenantSlug . '/api/notifications/read-all');
$ncBulkReadUrl = base_url($tenantSlug . '/api/notifications/bulk-read');
$ncAllNotifUrl = base_url($tenantSlug . $notifPath);
?>

<div class="bell-container" id="notif-bell-container">
    <button
        type="button"
        id="notificationBtn"
        class="hud-action-btn notification-bell-btn"
        title="Notificações"
        aria-label="Notificações"
        aria-expanded="false"
        aria-haspopup="true"
    >
        <div class="bell-wrapper">
            <span class="material-icons-round">notifications</span>
            <span
                class="hud-notification-badge"
                id="notif-badge"
                <?= $bellUnreadCount > 0 ? '' : 'style="display:none"' ?>
            ><?= $bellUnreadCount > 9 ? '9+' : ($bellUnreadCount ?: '') ?></span>
        </div>
    </button>
    <!-- Popover injetado pelo NotificationCenter JS -->
</div>

<!-- Áudio de alerta — carregado de forma lazy -->
<audio id="notif-sound" src="<?= base_url('assets/audio/notif.mp3') ?>" preload="none"></audio>

<style>
/* ── Bell Container ── */
.bell-container {
    position: relative;
    display: inline-flex;
}

.bell-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* ── Badge ── */
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
    border: 2px solid rgba(0, 0, 0, 0.2);
    box-shadow: 0 0 10px rgba(239, 68, 68, 0.4);
    animation: badgePop 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    pointer-events: none;
}

@keyframes badgePop {
    from { transform: scale(0); }
    to   { transform: scale(1); }
}

/* ── Pulsing Animation for Unread ── */
.hud-notification-badge.pulse {
    animation: badgePop 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275), 
               badgePulse 2s infinite;
}

@keyframes badgePulse {
    0% {
        box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
        transform: scale(1);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(239, 68, 68, 0);
        transform: scale(1.1);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
        transform: scale(1);
    }
}

/* ── Bell Button — Member HUD ── */
.hud-top-bar .notification-bell-btn,
.hud-top-bar-v3 .notification-bell-btn,
.admin-header .notification-bell-btn {
    background: rgba(0, 217, 255, 0.07);
    width: 42px;
    height: 42px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--accent-cyan, #00d9ff);
    transition: all 0.2s;
    border: 1px solid rgba(0, 217, 255, 0.25);
    box-shadow: 0 0 10px rgba(0, 217, 255, 0.08), inset 0 1px 0 rgba(255,255,255,0.05);
    cursor: pointer;
    text-decoration: none;
    padding: 0;
}

.hud-top-bar .notification-bell-btn:hover,
.hud-top-bar-v3 .notification-bell-btn:hover,
.admin-header .notification-bell-btn:hover,
.hud-top-bar .notification-bell-btn[aria-expanded="true"],
.hud-top-bar-v3 .notification-bell-btn[aria-expanded="true"],
.admin-header .notification-bell-btn[aria-expanded="true"] {
    background: rgba(0, 217, 255, 0.15);
    color: #00d9ff;
    border-color: rgba(0, 217, 255, 0.5);
    box-shadow: 0 0 18px rgba(0, 217, 255, 0.25), inset 0 1px 0 rgba(255,255,255,0.08);
    transform: scale(1.05);
}

/* ── Admin header override ── */
.admin-header .notification-bell-btn {
    width: 40px;
    height: 40px;
    margin-right: 8px;
}

.notification-bell-btn .material-icons-round { font-size: 24px; }
</style>

<script src="<?= asset_url('js/nc-debug-pro.js') ?>"></script>
<script src="<?= asset_url('js/dopamine-drop.js') ?>"></script>
<script src="<?= asset_url('js/notification-center.js') ?>"></script>
<script>
(function () {
    function initNotifCenter() {
        if (typeof window.NotificationCenter === 'undefined') return;

        // Destroy previous instance's polling timer before creating a new one
        // (guards against HTMX re-injecting this partial without a full page reload)
        if (window.notificationCenter && typeof window.notificationCenter._pollTimer !== 'undefined') {
            clearInterval(window.notificationCenter._pollTimer);
            window.notificationCenter._pollTimer = null;
        }

        window.notificationCenter = new window.NotificationCenter({
            buttonId:     'notificationBtn',
            badgeId:      'notif-badge',
            soundId:      'notif-sound',
            apiUrl:       '<?= $ncApiUrl ?>',
            unreadUrl:    '<?= $ncUnreadUrl ?>',
            readAllUrl:   '<?= $ncReadAllUrl ?>',
            bulkReadUrl:  '<?= $ncBulkReadUrl ?>',
            allNotifUrl:  '<?= $ncAllNotifUrl ?>',
            pollInterval: 30000,
            initialCount: <?= (int) $bellUnreadCount ?>,
        });
    }

    // Works for both normal load and HTMX partial swaps
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initNotifCenter);
    } else {
        initNotifCenter();
    }
})();
</script>
