<?php
/**
 * Header Partial - Top Bar
 * DESIGN: Pixel-Perfect Reference (Center Logo, Left Greeting)
 */
?>
<header class="hud-top-bar-v3">
    <div class="header-inner-v3">
        
        <!-- Left: User Greeting -->
        <div class="header-greeting">
            <span class="greeting-text">BEM-VINDO DE VOLTA,</span>
            <?php 
                $nameParts = explode(' ', trim($user['name']));
                $lastName = count($nameParts) > 1 ? end($nameParts) : $nameParts[0];
            ?>
            <span class="greeting-name"><?= strtoupper(htmlspecialchars($lastName)) ?>!</span>
        </div>

        <!-- Center: Main Brand Logo -->
        <a href="<?= base_url($tenant['slug'] . '/dashboard') ?>" class="header-brand-center">
            <div class="brand-icon-center">
                <i class="fas fa-compass"></i>
            </div>
            <div class="brand-text-wrapper">
                <span class="brand-title"><?= strtoupper(htmlspecialchars($tenant['name'])) ?></span>
                <span class="brand-subtitle"><?= htmlspecialchars($tenant['description'] ?? 'Portal do Desbravador') ?></span>
            </div>
        </a>

        <!-- Right: Actions -->
        <div class="header-actions-v3">
            <!-- Notification Bell (Round) -->
            <?php require BASE_PATH . '/views/partials/notification_bell.php'; ?>
            
            <!-- Profile Picture -->
            <a href="<?= base_url($tenant['slug'] . '/perfil') ?>" class="header-avatar-link">
                <?php if (!empty($user['avatar_url'])): ?>
                    <img src="<?= $user['avatar_url'] ?>" alt="<?= htmlspecialchars($user['name']) ?>" class="header-avatar-img">
                <?php else: ?>
                    <div class="header-avatar-fallback">
                        <?= strtoupper(substr($user['name'], 0, 1)) ?>
                    </div>
                <?php endif; ?>
            </a>
        </div>
    </div>
</header>

<style>
.hud-top-bar-v3 {
    width: 100%;
    padding: 24px 40px;
    z-index: 900;
}

.header-inner-v3 {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
}

/* Left Greeting */
.header-greeting {
    flex: 1;
    display: flex;
    gap: 6px;
    align-items: center;
    font-size: 1rem;
    letter-spacing: 0.5px;
}
.greeting-text {
    color: #cbd5e1;
    font-weight: 700;
}
.greeting-name {
    color: var(--accent-cyan, #06b6d4);
    font-weight: 800;
}

/* Center Brand */
.header-brand-center {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    text-decoration: none;
}
.brand-icon-center {
    font-size: 32px;
    color: var(--neon-green, #34d399);
    filter: drop-shadow(0 0 10px rgba(52, 211, 153, 0.4));
}
.brand-text-wrapper {
    display: flex;
    flex-direction: column;
}
.brand-title {
    color: var(--accent-cyan, #22d3ee); /* Brighter Cyan */
    font-size: 1.25rem;
    font-weight: 800;
    letter-spacing: 1px;
    line-height: 1.2;
}
.brand-subtitle {
    color: #64748b;
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.5px;
}

/* Right Actions */
.header-actions-v3 {
    flex: 1;
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 16px;
}

.header-avatar-link {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    overflow: hidden;
    border: 2px solid rgba(255,255,255,0.1);
    transition: all 0.3s ease;
}
.header-avatar-link:hover {
    border-color: var(--accent-cyan);
    box-shadow: 0 0 15px rgba(6, 182, 212, 0.3);
}
.header-avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.header-avatar-fallback {
    width: 100%;
    height: 100%;
    background: var(--accent-cyan);
    color: #0B1121;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 1.2rem;
}

/* Base override for the notification bell to make it rounded */
.header-actions-v3 .notification-bell-btn {
    width: 44px;
    height: 44px;
    border-radius: 50% !important;
    display: flex;
    align-items: center;
    justify-content: center;
}

@media (max-width: 1024px) {
    .header-greeting { display: none; }
    .header-brand-center { justify-content: flex-start; }
    .hud-top-bar-v3 { padding: 16px 24px; }
}
@media (max-width: 768px) {
    .brand-subtitle { display: none; }
    .brand-icon-center { font-size: 24px; }
    .brand-title { font-size: 1.1rem; }
}
</style>