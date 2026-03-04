<?php
/**
 * Página de Ranking de Unidades
 * DESIGN: Deep Glass HUD v3.0 (Content Only)
 */
?>
<style>
    .your-position {
        background: var(--gradient-secondary, linear-gradient(135deg, #8b5cf6, #6d28d9));
        border-radius: 16px;
        padding: 20px;
        text-align: center;
        margin-bottom: 24px;
        color: #fff;
        box-shadow: 0 10px 30px rgba(139, 92, 246, 0.25);
    }

    .your-position .pos-label {
        font-size: var(--fs-label);
        text-transform: uppercase;
        letter-spacing: 0.1em;
        opacity: 0.85;
        margin-bottom: 4px;
    }

    .leaderboard-full {
        background: var(--bg-card, var(--hud-glass-panel));
        border: 1px solid var(--border-light, var(--hud-glass-border));
        border-radius: 16px;
        overflow: hidden;
        backdrop-filter: blur(10px);
    }

    .leaderboard-row {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 18px;
        border-bottom: 1px solid var(--border-light, var(--hud-glass-border));
        transition: background 0.2s;
    }

    .leaderboard-row:hover { background: rgba(255, 255, 255, 0.02); }
    .leaderboard-row:last-child { border-bottom: none; }
    .leaderboard-row.is-yours { background: rgba(139, 92, 246, 0.08); border-left: 3px solid var(--accent-purple, #8b5cf6); }
    .leaderboard-row.top-3 { background: rgba(247, 179, 43, 0.03); }

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

    .unit-info {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .unit-mascot-circle {
        width: 44px;
        height: 44px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        border: 1px solid rgba(255, 255, 255, 0.1);
        flex-shrink: 0;
    }

    .unit-details {
        min-width: 0;
    }

    .unit-details h3 {
        font-size: var(--fs-card-title);
        margin-bottom: 2px;
        font-weight: 700;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .unit-meta {
        font-size: var(--fs-caption);
        color: var(--text-secondary, var(--hud-text-dim));
    }

    .unit-stats { text-align: right; flex-shrink: 0; }

    .unit-xp {
        font-family: var(--font-mono);
        font-size: 1.1rem;
        font-weight: 800;
        color: var(--accent-cyan);
    }

    .xp-label {
        font-size: var(--fs-label);
        color: var(--text-muted, var(--hud-text-dim));
        text-transform: uppercase;
        font-weight: 700;
    }

    .unit-badge {
        font-family: var(--font-heading);
        font-size: 0.55rem;
        background: var(--accent-purple, #8b5cf6);
        color: #fff;
        padding: 2px 6px;
        border-radius: 20px;
        vertical-align: middle;
        margin-left: 4px;
        display: inline-block;
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 0.05em;
    }

    .info-card {
        margin-top: 24px;
        background: rgba(0, 217, 255, 0.04);
        border: 1px solid rgba(0, 217, 255, 0.1);
        border-radius: 12px;
        padding: 16px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .info-card strong {
        color: #fff;
        display: block;
        margin-bottom: 4px;
        font-family: var(--font-heading);
        font-size: var(--fs-caption);
    }

    .info-card p {
        font-size: var(--fs-caption);
        color: var(--text-secondary, var(--hud-text-dim));
        line-height: 1.5;
        margin: 0;
    }
</style>

<header class="page-header">
    <h1>🏆 Ranking das Unidades</h1>
    <p>A disputa pelo topo do Quartel General!</p>
</header>

<div class="ranking-tabs">
    <a href="<?= base_url($tenant['slug'] . '/ranking') ?>" class="ranking-tab">Geral (Membros)</a>
    <a href="<?= base_url($tenant['slug'] . '/ranking-unidades') ?>" class="ranking-tab active">Unidades</a>
    <a href="<?= base_url($tenant['slug'] . '/ranking-recrutamento') ?>" class="ranking-tab">Recrutamento</a>
</div>

<?php if ($unitPosition): ?>
    <div class="your-position">
        <div class="pos-label">Sua Unidade</div>
        <h2>#<?= $unitPosition ?></h2>
        <p>Posição geral no ranking de unidades</p>
    </div>
<?php endif; ?>

<div class="leaderboard-full">
    <?php if (empty($unitLeaderboard)): ?>
        <div style="padding: 40px; text-align: center; color: var(--hud-text-dim);">
            <i class="material-icons-round" style="font-size: 3rem; opacity: 0.3; display: block; margin-bottom: 12px;">group_off</i>
            <p>Ainda não há dados de unidades suficientes para o ranking.</p>
        </div>
    <?php else: ?>
        <?php foreach ($unitLeaderboard as $index => $unit): ?>
            <?php
            $position = $index + 1;
            $isYours = $unit['id'] == ($userUnitId ?? 0);
            $isTop3 = $position <= 3;
            ?>
            <div class="leaderboard-row <?= $isYours ? 'is-yours' : '' ?> <?= $isTop3 ? 'top-3' : '' ?>">
                <div class="rank <?= $position === 1 ? 'gold' : ($position === 2 ? 'silver' : ($position === 3 ? 'bronze' : '')) ?>">
                    <?php if ($position === 1): ?>🥇<?php elseif ($position === 2): ?>🥈<?php elseif ($position === 3): ?>🥉<?php else: ?>#<?= $position ?><?php endif; ?>
                </div>

                <div class="unit-info">
                    <div class="unit-mascot-circle" style="border-bottom: 3px solid <?= $unit['color'] ?? 'var(--accent-cyan)' ?>;">
                        <?php 
                        $mascot = $unit['mascot'] ?? '🛡️';
                        if (str_contains($mascot, ':')): ?>
                            <iconify-icon icon="<?= htmlspecialchars($mascot) ?>" style="font-size: 1.6rem;"></iconify-icon>
                        <?php else: ?>
                            <?= $mascot ?>
                        <?php endif; ?>
                    </div>
                    <div class="unit-details">
                        <h3>
                            <?= htmlspecialchars($unit['name']) ?>
                            <?php if ($isYours): ?>
                                <span class="unit-badge">SUA UNIDADE</span>
                            <?php endif; ?>
                        </h3>
                        <div class="unit-meta">Total XP Acumulado</div>
                    </div>
                </div>

                <div class="unit-stats">
                    <div class="unit-xp"><?= number_format($unit['total_xp']) ?></div>
                    <div class="xp-label">PONTOS</div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div class="info-card">
    <i class="material-icons-round" style="color: var(--accent-cyan); flex-shrink: 0;">info</i>
    <div>
        <strong>Como funciona o ranking?</strong>
        <p>O ranking das unidades é baseado na soma total de XP de todos os membros ativos da unidade. 
        Quanto mais atividades sua unidade completar, mais alto vocês subirão no ranking geral do clube!</p>
    </div>
</div>
