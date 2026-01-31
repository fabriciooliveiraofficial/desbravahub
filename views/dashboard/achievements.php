<?php
/**
 * Achievements Page - Conquistas e Medalhas
 * DESIGN: Deep Glass HUD v3.0 (Content Only)
 */
?>
<div class="hud-wrapper">
    <!-- HUD Header -->
    <header class="hud-header">
        <div>
            <h1 class="hud-title">Galeria de Conquistas</h1>
            <div class="hud-subtitle">Suas medalhas e honrarias</div>
        </div>
    </header>

    <!-- Summary Stats -->
    <div class="hud-stats">
        <div class="hud-stat-card primary">
            <div>
                <div class="hud-stat-value" style="color: var(--accent-warning)"><?= count($earned_achievements) ?></div>
                <div class="hud-stat-label">Conquistadas</div>
            </div>
            <i class="material-icons-round hud-stat-icon" style="color: var(--accent-warning)">emoji_events</i>
        </div>
        <div class="hud-stat-card">
            <div>
                <div class="hud-stat-value" style="color: var(--hud-text-dim)"><?= count($all_achievements) - count($earned_achievements) ?></div>
                <div class="hud-stat-label">Restantes</div>
            </div>
            <i class="material-icons-round hud-stat-icon" style="color: var(--hud-text-dim)">lock</i>
        </div>
    </div>

    <!-- Achievements Grid -->
    <section class="hud-section">
        <div class="hud-grid" style="grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));">
            <?php foreach ($all_achievements as $index => $achievement): 
                $isEarned = in_array($achievement['id'], array_column($earned_achievements, 'id'));
                $earnedDate = $isEarned ? ($achievement['earned_at'] ?? '2023') : null;
            ?>
                <div class="tech-plate" style="text-align: center; padding: 20px; <?= $isEarned ? '' : 'opacity: 0.5; filter: grayscale(1);' ?>">
                    <div style="font-size: 3rem; margin-bottom: 12px; filter: drop-shadow(0 0 10px rgba(255,215,0,0.3));">
                        <?= $achievement['icon'] ?? '🏆' ?>
                    </div>
                    
                    <h3 class="plate-title" style="font-size: 0.9rem; margin-bottom: 6px;">
                        <?= htmlspecialchars($achievement['name']) ?>
                    </h3>
                    
                    <?php if ($isEarned): ?>
                        <span class="hud-badge" style="color: var(--accent-warning); border-color: var(--accent-warning); font-size: 0.7rem;">
                            DESBLOQUEADO
                        </span>
                    <?php else: ?>
                        <span class="hud-badge" style="color: var(--hud-text-dim); font-size: 0.7rem;">
                            BLOQUEADO
                        </span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>