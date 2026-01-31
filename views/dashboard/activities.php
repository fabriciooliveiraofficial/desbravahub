<?php
/**
 * Activities Page - Central de Missões
 * DESIGN: Deep Glass HUD v3.0 (Content Only)
 */
?>
<div class="hud-wrapper">
    <!-- HUD Header -->
    <header class="hud-header">
        <div>
            <h1 class="hud-title">Central de Missões</h1>
            <div class="hud-subtitle">Escolha sua próxima aventura e ganhe XP!</div>
        </div>
    </header>

    <?php if (!empty($grouped['in_progress'])): ?>
        <section class="hud-section">
            <div class="hud-section-header" style="border-color: var(--accent-warning)">
                <h2 class="hud-section-title" style="color: var(--accent-warning)">Missões em Andamento</h2>
            </div>
            <div class="hud-grid" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));">
                <?php foreach ($grouped['in_progress'] as $i => $activity): ?>
                    <a href="<?= base_url($tenant['slug'] . '/atividades/' . $activity['id']) ?>" 
                       class="tech-plate type-in_progress" 
                       style="animation-delay: <?= $i * 0.1 ?>s">
                        <div class="status-line"></div>
                        
                        <div class="plate-header">
                            <div class="plate-content">
                                <div class="plate-category"><?= $activity['is_outdoor'] ? 'Outdoor' : 'Teórica' ?></div>
                                <h3 class="plate-title"><?= htmlspecialchars($activity['title']) ?></h3>
                            </div>
                            <span style="font-size: 1.5rem;"><?= $activity['is_outdoor'] ? '🏕️' : '📋' ?></span>
                        </div>

                        <?php 
                        $prog = $activity['progress_percent'] ?? 0;
                        ?>
                        <div class="hud-progress" style="margin-top: 16px;">
                            <div class="hud-progress-bar" style="width: <?= $prog ?>%"></div>
                        </div>

                        <div class="plate-data" style="margin-top: 16px;">
                            <span class="hud-badge" style="color: var(--accent-warning); width: 100%; justify-content: center;">
                                <i class="material-icons-round" style="font-size: 1rem; margin-right: 6px;">play_arrow</i>
                                CONTINUAR
                            </span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if (!empty($grouped['available'])): ?>
        <section class="hud-section">
            <div class="hud-section-header" style="border-color: var(--accent-cyan)">
                <h2 class="hud-section-title" style="color: var(--accent-cyan)">Quests Disponíveis</h2>
            </div>
            <div class="hud-grid" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));">
                <?php foreach ($grouped['available'] as $i => $activity): ?>
                    <a href="<?= base_url($tenant['slug'] . '/atividades/' . $activity['id']) ?>" 
                       class="tech-plate" 
                       style="animation-delay: <?= $i * 0.1 ?>s">
                        
                        <div class="plate-header">
                            <div class="plate-content">
                                <div class="plate-category"><?= $activity['is_outdoor'] ? 'Outdoor' : 'Teórica' ?></div>
                                <h3 class="plate-title"><?= htmlspecialchars($activity['title']) ?></h3>
                            </div>
                            <span style="font-size: 1.5rem; opacity: 0.7;"><?= $activity['is_outdoor'] ? '🏕️' : '📋' ?></span>
                        </div>

                        <p style="color: var(--text-muted); font-size: 0.8rem; margin: 12px 0;">
                            <?= htmlspecialchars(substr($activity['description'] ?? '', 0, 80)) ?>...
                        </p>

                        <div class="plate-data">
                            <div class="data-point">
                                <span class="data-label">Recompensa</span>
                                <span class="data-value" style="color: var(--accent-green)">+<?= $activity['xp_reward'] ?> XP</span>
                            </div>
                            <div class="data-point" style="align-items: flex-end;">
                                <span class="hud-badge" style="color: var(--accent-cyan)">INICIAR</span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if (!empty($grouped['completed'])): ?>
        <section class="hud-section">
            <div class="hud-section-header" style="border-color: var(--accent-green)">
                <h2 class="hud-section-title" style="color: var(--accent-green)">Histórico de Missões</h2>
            </div>
            <div class="hud-grid" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));">
                <?php foreach ($grouped['completed'] as $i => $activity): ?>
                    <div class="tech-plate type-completed" style="animation-delay: <?= $i * 0.1 ?>s; opacity: 0.7;">
                        <div class="status-line"></div>
                        <div class="plate-header">
                            <div class="plate-content">
                                <h3 class="plate-title"><?= htmlspecialchars($activity['title']) ?></h3>
                            </div>
                            <i class="material-icons-round" style="color: var(--accent-green)">check_circle</i>
                        </div>
                        <div class="plate-data" style="margin-top: 12px;">
                            <span class="data-value" style="color: var(--accent-green); font-size: 0.8rem;">MISSÃO CUMPRIDA</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if (empty($grouped['in_progress']) && empty($grouped['available']) && empty($grouped['completed'])): ?>
        <div class="empty-state-hud">
            <span class="material-icons-round empty-icon-hud">search_off</span>
            <h3 class="hud-section-title">Nenhuma missão disponível</h3>
            <p class="hud-subtitle">Aguarde novas ordens do comando.</p>
        </div>
    <?php endif; ?>
</div>