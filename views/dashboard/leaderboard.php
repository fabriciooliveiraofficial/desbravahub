<?php
/**
 * Página de Ranking
 * DESIGN: Deep Glass HUD v3.0 (Content Only)
 */
?>
<style>
    .leaderboard-full {
        background: var(--bg-card, var(--hud-glass-panel));
        border: 1px solid var(--border-light, var(--hud-glass-border));
        border-radius: 16px;
        overflow: hidden;
    }

    .leaderboard-row {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 18px;
        border-bottom: 1px solid var(--border-light, var(--hud-glass-border));
        transition: background 0.2s;
    }

    .leaderboard-row:last-child { border-bottom: none; }
    .leaderboard-row:hover { background: rgba(255, 255, 255, 0.02); }
    .leaderboard-row.is-you { background: rgba(0, 217, 255, 0.08); }
    .leaderboard-row.top-3 { background: rgba(247, 179, 43, 0.04); }

    .rank {
        font-family: var(--font-heading);
        font-size: 1.2rem;
        font-weight: 700;
        min-width: 36px;
        text-align: center;
    }

    .rank.gold { color: #FFD700; }
    .rank.silver { color: #C0C0C0; }
    .rank.bronze { color: #CD7F32; }

    .member-info {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .member-avatar-lg {
        width: 42px;
        height: 42px;
        background: var(--gradient-primary, linear-gradient(135deg, var(--accent-cyan), #0ea5e9));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--bg-dark, #0f172a);
        font-family: var(--font-heading);
        font-weight: 700;
        font-size: 1.1rem;
        flex-shrink: 0;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .member-avatar-lg img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .member-details {
        min-width: 0;
    }

    .member-details h3 {
        font-size: var(--fs-card-title);
        margin-bottom: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .member-details span {
        font-size: var(--fs-caption);
        color: var(--text-secondary, var(--hud-text-dim));
    }

    .member-stats {
        text-align: right;
        flex-shrink: 0;
    }

    .member-xp {
        font-family: var(--font-mono);
        font-size: 1rem;
        font-weight: 700;
        color: var(--accent-green);
    }
</style>

<header class="page-header">
    <h1>🏆 Ranking Geral</h1>
    <p>Os desbravadores lendários do clube!</p>
</header>

<div class="ranking-tabs">
    <a href="<?= base_url($tenant['slug'] . '/ranking') ?>" class="ranking-tab active">Geral (Membros)</a>
    <a href="<?= base_url($tenant['slug'] . '/ranking-unidades') ?>" class="ranking-tab">Unidades</a>
    <a href="<?= base_url($tenant['slug'] . '/ranking-recrutamento') ?>" class="ranking-tab">Recrutamento</a>
</div>

<?php if ($userPosition): ?>
    <div class="your-position">
        <h2>#<?= $userPosition ?></h2>
        <p>Sua posição no ranking</p>
    </div>
<?php endif; ?>

<div class="leaderboard-full">
    <?php if (empty($leaderboard)): ?>
        <div style="padding: 40px; text-align: center; color: var(--hud-text-dim);">
            <i class="material-icons-round" style="font-size: 3rem; opacity: 0.3; display: block; margin-bottom: 12px;">leaderboard</i>
            <p>Ainda não há dados suficientes para o ranking.</p>
        </div>
    <?php else: ?>
        <?php foreach ($leaderboard as $index => $member): ?>
            <?php
            $position = $index + 1;
            $isYou = $member['id'] === $user['id'];
            $isTop3 = $position <= 3;
            ?>
            <div class="leaderboard-row <?= $isYou ? 'is-you' : '' ?> <?= $isTop3 ? 'top-3' : '' ?>">
                <div class="rank <?= $position === 1 ? 'gold' : ($position === 2 ? 'silver' : ($position === 3 ? 'bronze' : '')) ?>">
                    <?php if ($position === 1): ?>🥇<?php elseif ($position === 2): ?>🥈<?php elseif ($position === 3): ?>🥉<?php else: ?><?= $position ?><?php endif; ?>
                </div>

                <div class="member-info">
                    <div class="member-avatar-lg">
                        <?php if (!empty($member['avatar_url'])): ?>
                            <img src="<?= htmlspecialchars($member['avatar_url']) ?>" alt="<?= htmlspecialchars($member['name']) ?>" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                            <span style="display:none;"><?= strtoupper(substr($member['name'], 0, 1)) ?></span>
                        <?php else: ?>
                            <?= strtoupper(substr($member['name'], 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                    <div class="member-details">
                        <h3>
                            <?= htmlspecialchars($member['name']) ?>
                            <?= $isYou ? '(você)' : '' ?>
                        </h3>
                        <span>Nível <?= $member['level_number'] ?? 1 ?></span>
                    </div>
                </div>

                <div class="member-stats">
                    <div class="member-xp"><?= number_format($member['xp_points']) ?> XP</div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>