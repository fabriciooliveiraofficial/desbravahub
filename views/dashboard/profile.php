<?php
/**
 * Profile - Meu QG Pessoal
 * DESIGN: Deep Glass HUD v3.0 (Content Only)
 */
?>
<style>
    /* Profile Specifics */
    .profile-hud-card {
        background: var(--hud-glass-panel);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid var(--hud-glass-border);
        border-radius: 24px;
        padding: 40px 24px;
        text-align: center;
        max-width: 500px;
        margin: 0 auto 40px;
        box-shadow: 0 20px 50px rgba(0,0,0,0.3);
        position: relative;
        overflow: hidden;
    }

    /* Hexagon Avatar Border using CSS clip-path or simple border */
    .profile-avatar-hud {
        width: 120px;
        height: 120px;
        margin: 0 auto 20px;
        position: relative;
    }

    .avatar-img-hud {
        width: 100%;
        height: 100%;
        border-radius: 50%; /* Fallback */
        background: linear-gradient(135deg, var(--accent-cyan), var(--hud-bg-radial-start));
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        font-weight: 800;
        color: #fff;
        border: 2px solid var(--accent-cyan);
        box-shadow: 0 0 30px rgba(0, 217, 255, 0.2);
    }

    .level-badge-hud {
        position: absolute;
        bottom: 0;
        right: 0;
        background: var(--hud-bg-linear-end);
        border: 1px solid var(--accent-cyan);
        color: var(--accent-cyan);
        padding: 4px 10px;
        border-radius: 8px;
        font-weight: 800;
        font-size: 0.8rem;
        box-shadow: 0 4px 10px rgba(0,0,0,0.5);
    }

    .profile-name-hud {
        font-size: 1.8rem;
        font-weight: 800;
        color: #fff;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        text-shadow: 0 2px 10px rgba(0,0,0,0.5);
    }

    .profile-role-hud {
        color: var(--hud-text-dim);
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.2em;
        margin-bottom: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    /* Stats Row */
    .stats-row-hud {
        display: flex;
        justify-content: center;
        gap: 24px;
        margin-bottom: 40px;
        padding-bottom: 30px;
        border-bottom: 1px solid var(--hud-glass-border);
    }

    .stat-item-hud {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .stat-val-hud {
        font-size: 1.4rem;
        font-weight: 800;
        color: #fff;
        line-height: 1;
        margin-bottom: 6px;
        font-family: 'JetBrains Mono', monospace;
    }

    .stat-lbl-hud {
        font-size: 0.65rem;
        text-transform: uppercase;
        color: var(--hud-text-dim);
        font-weight: 700;
        letter-spacing: 0.1em;
    }

    /* Menu Actions */
    .menu-group-hud {
        text-align: left;
    }

    .menu-item-hud {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px;
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid transparent;
        border-radius: 8px;
        margin-bottom: 12px;
        transition: all 0.2s;
        text-decoration: none;
        color: var(--hud-text-primary);
    }

    .menu-item-hud:hover {
        background: rgba(255, 255, 255, 0.05);
        border-color: var(--accent-cyan);
        transform: translateX(4px);
    }

    .menu-left { display: flex; align-items: center; gap: 16px; }
    
    .menu-icon { 
        color: var(--accent-cyan); 
        opacity: 0.7; 
        font-size: 1.2rem;
    }

    .menu-text {
        font-weight: 700;
        font-size: 0.9rem;
        letter-spacing: 0.05em;
    }

    .menu-right {
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.8rem;
        color: var(--hud-text-dim);
    }

    .btn-logout-hud {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: 100%;
        margin-top: 32px;
        padding: 16px;
        background: rgba(239, 68, 68, 0.1);
        border: 1px solid rgba(239, 68, 68, 0.3);
        color: #ef4444;
        font-weight: 700;
        text-decoration: none;
        border-radius: 8px;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.1em;
        transition: all 0.2s;
    }

    .btn-logout-hud:hover {
        background: rgba(239, 68, 68, 0.2);
        box-shadow: 0 0 15px rgba(239, 68, 68, 0.2);
    }
</style>

<div class="hud-wrapper">
    <header class="hud-header" style="justify-content: center; border-bottom: 0; background: none;">
        <!-- Clean header for focused profile view -->
    </header>

    <div class="profile-hud-card">
        <!-- Decoration Lines -->
        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 2px; background: linear-gradient(90deg, transparent, var(--accent-cyan), transparent); opacity: 0.5;"></div>

        <div class="profile-avatar-hud">
            <div class="avatar-img-hud">
                <?= strtoupper(substr($user['name'], 0, 1)) ?>
            </div>
            <div class="level-badge-hud">
                LVL <?= $progress['level']['number'] ?? 1 ?>
            </div>
        </div>

        <h2 class="profile-name-hud"><?= htmlspecialchars($user['name']) ?></h2>
        <div class="profile-role-hud">
            <span class="material-icons-round" style="font-size: 14px; color: var(--accent-cyan)">verified</span>
            Desbravador Oficial
        </div>

        <div class="stats-row-hud">
            <div class="stat-item-hud">
                <span class="stat-val-hud" style="color: var(--accent-cyan);"><?= number_format($progress['xp'] ?? 0) ?></span>
                <span class="stat-lbl-hud">XP TOTAL</span>
            </div>
            <div class="stat-item-hud">
                <span class="stat-val-hud"><?= $progress['activities']['completed'] ?? 0 ?></span>
                <span class="stat-lbl-hud">MISSÕES</span>
            </div>
            <div class="stat-item-hud">
                <span class="stat-val-hud" style="color: var(--accent-warning);">
                    <?= count(array_filter($achievements ?? [], fn($a) => $a['earned_at'])) ?>
                </span>
                <span class="stat-lbl-hud">INSÍGNIAS</span>
            </div>
        </div>

        <div class="menu-group-hud">
            <div class="menu-item-hud">
                <div class="menu-left">
                    <span class="material-icons-round menu-icon">shield</span>
                    <span class="menu-text">UNIDADE</span>
                </div>
                <span class="menu-right" style="color: #fff;"><?= htmlspecialchars($tenant['name']) ?></span>
            </div>

            <?php if (!empty($user['pathfinder_class'])): ?>
            <div class="menu-item-hud">
                <div class="menu-left">
                    <span class="material-icons-round menu-icon">school</span>
                    <span class="menu-text">CLASSE</span>
                </div>
                <span class="menu-right"><?= htmlspecialchars($user['pathfinder_class']) ?></span>
            </div>
            <?php endif; ?>

            <div class="menu-item-hud">
                <div class="menu-left">
                    <span class="material-icons-round menu-icon">calendar_today</span>
                    <span class="menu-text">ALISTAMENTO</span>
                </div>
                <span class="menu-right"><?= date('d/m/Y', strtotime($user['created_at'])) ?></span>
            </div>

             <div class="menu-item-hud">
                <div class="menu-left">
                    <span class="material-icons-round menu-icon">settings</span>
                    <span class="menu-text">CONFIGURAÇÕES</span>
                </div>
                <span class="menu-right"><i class="fas fa-chevron-right" style="font-size: 0.7rem"></i></span>
            </div>
        </div>

        <a href="<?= base_url($tenant['slug'] . '/logout') ?>" class="btn-logout-hud" hx-boost="false">
            <span class="material-icons-round" style="font-size: 1rem;">logout</span>
            ENCERRAR SESSÃO
        </a>
    </div>
</div>
