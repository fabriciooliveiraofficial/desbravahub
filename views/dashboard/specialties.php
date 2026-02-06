<?php
/**
 * Pathfinder: My Assigned Specialties (HUD Redesign - Content Only)
 */
?>
<div class="hud-wrapper">
    <!-- HUD Header -->
    <header class="hud-header">
        <div>
            <h1 class="hud-title">Central de Missões</h1>
            <div class="hud-subtitle">Sistema de Especialidades v2.0</div>
        </div>
    </header>

    <?php
    $countPending = count($grouped['pending']);
    $countProgress = count($grouped['in_progress']);
    $countReview = count($grouped['pending_review']);
    $countCompleted = count($grouped['completed']);
    $totalAssigned = $countPending + $countProgress + $countReview + $countCompleted;
    ?>

    <!-- Stats Grid -->
    <div class="hud-stats">
        <!-- Primary -->
        <div class="hud-stat-card primary">
            <div>
                <div class="hud-stat-value" style="color: var(--accent-cyan)"><?= $countProgress + $countReview ?></div>
                <div class="hud-stat-label">Incursões Ativas</div>
            </div>
            <i class="fas fa-satellite-dish hud-stat-icon"></i>
            <div class="hud-progress" style="margin-left: 0; margin-top: 8px; height: 3px;">
                <?php 
                $avgProgress = 0;
                if ($totalAssigned > 0) {
                    $sum = 0;
                    foreach ($assignments as $a) {
                        if ($a['type_label'] === 'program') {
                            $sum += $a['progress_percent'] ?? 0;
                        } else {
                            $calc = \App\Services\SpecialtyService::calculateProgress($a['id'], $a['specialty_id']);
                            $sum += $calc['percentage'];
                        }
                    }
                    $avgProgress = round($sum / $totalAssigned);
                }
                ?>
                <div class="hud-progress-bar" style="width: <?= $avgProgress ?>%; background: var(--accent-cyan); box-shadow: 0 0 10px var(--accent-cyan);"></div>
            </div>
        </div>

        <div class="hud-stat-card">
            <div>
                <div class="hud-stat-value" style="color: var(--accent-warning)"><?= $countPending ?></div>
                <div class="hud-stat-label">Pendentes</div>
            </div>
        </div>

        <div class="hud-stat-card">
            <div>
                <div class="hud-stat-value" style="color: #ffd700"><?= $countReview ?></div>
                <div class="hud-stat-label">Em Avaliação</div>
            </div>
        </div>

        <div class="hud-stat-card">
            <div>
                <div class="hud-stat-value" style="color: var(--accent-green)"><?= $countCompleted ?></div>
                <div class="hud-stat-label">Concluídas</div>
            </div>
        </div>
    </div>

    <?php if ($totalAssigned === 0): ?>
        <div class="empty-state" style="text-align: center; border: 1px dashed var(--hud-border); padding: 40px; border-radius: 4px;">
            <span class="material-icons-round" style="font-size: 3rem; opacity: 0.3; display: block; margin-bottom: 12px;">radar</span>
            <h3 class="hud-section-title">Nenhuma missão ativa</h3>
            <p class="hud-subtitle">Aguardando novas atribuições</p>
        </div>
    <?php else: ?>

        <!-- PENDING -->
        <?php if (!empty($grouped['pending'])): ?>
            <section class="hud-section">
                <div class="hud-section-header" style="border-color: var(--accent-warning)">
                    <h2 class="hud-section-title" style="color: var(--accent-warning)">Inicialização Pendente</h2>
                    <span class="hud-section-count"><?= count($grouped['pending']) ?></span>
                </div>
                <div class="hud-grid">
                    <?php foreach ($grouped['pending'] as $index => $a): ?>
                        <a href="<?= base_url($tenant['slug'] . '/especialidades/' . $a['id']) ?>" 
                            class="tech-plate type-pending"
                            style="animation-delay: <?= $index * 50 ?>ms">
                            <div class="status-line"></div>
                            <div class="plate-header">
                                <div class="plate-content">
                                    <div class="plate-category"><?= htmlspecialchars($a['specialty']['category']['name'] ?? 'Geral') ?></div>
                                    <h3 class="plate-title"><?= htmlspecialchars($a['specialty']['name']) ?></h3>
                                </div>
                                <?php 
                                $icon = $a['specialty']['badge_icon'] ?? '📘';
                                if (str_contains($icon, 'fa-')): ?>
                                    <i class="<?= $icon ?> plate-icon"></i>
                                <?php else: ?>
                                    <span class="plate-icon"><?= $icon ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="plate-data">
                                <div class="data-point">
                                    <span class="data-label">Prazo</span>
                                    <span class="data-value"><?= (!empty($a['due_date'])) ? date('d/m', strtotime($a['due_date'])) : 'N/A' ?></span>
                                </div>
                                <div class="data-point" style="align-items: flex-end;">
                                    <span class="hud-badge" style="color: var(--accent-warning)">Iniciar</span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- IN PROGRESS -->
        <?php if (!empty($grouped['in_progress'])): ?>
            <section class="hud-section">
                <div class="hud-section-header">
                    <h2 class="hud-section-title" style="color: var(--accent-cyan)">Em Execução</h2>
                    <span class="hud-section-count"><?= count($grouped['in_progress']) ?></span>
                </div>
                <div class="hud-grid">
                    <?php foreach ($grouped['in_progress'] as $index => $a): ?>
                        <a href="<?= base_url($tenant['slug'] . '/especialidades/' . $a['id']) ?>" 
                            class="tech-plate type-in_progress"
                            style="animation-delay: <?= $index * 50 ?>ms">
                            <div class="status-line"></div>
                            <div class="plate-header">
                                <div class="plate-content">
                                    <div class="plate-category"><?= htmlspecialchars($a['specialty']['category']['name'] ?? 'Geral') ?></div>
                                    <h3 class="plate-title"><?= htmlspecialchars($a['specialty']['name']) ?></h3>
                                </div>
                                <?php 
                                $icon = $a['specialty']['badge_icon'] ?? '📘';
                                if (str_contains($icon, 'fa-')): ?>
                                    <i class="<?= $icon ?> plate-icon"></i>
                                <?php else: ?>
                                    <span class="plate-icon"><?= $icon ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <?php 
                            if ($a['type_label'] === 'program') {
                                $engPercent = $a['total_steps'] > 0 ? round(($a['answered_steps'] / $a['total_steps']) * 100) : 0;
                                $progPercent = $a['progress_percent'] ?? 0;
                            } else {
                                $calc = \App\Services\SpecialtyService::calculateProgress($a['id'], $a['specialty_id']);
                                $engPercent = $calc['answered_percentage'];
                                $progPercent = $calc['percentage'];
                            }
                            ?>
                            <div class="hud-progress" style="color: var(--accent-cyan); background: rgba(0,0,0,0.3); height: 6px; border-radius: 100px; overflow: hidden; position: relative;">
                                <div class="hud-progress-bar" style="width: <?= $engPercent ?>%; opacity: 0.3; position: absolute; height: 100%;"></div>
                                <div class="hud-progress-bar" style="width: <?= $progPercent ?>%; position: relative; height: 100%; box-shadow: 0 0 8px currentColor;"></div>
                            </div>

                            <div class="plate-data">
                                <div class="data-point">
                                    <span class="data-label">Status</span>
                                    <span class="data-value" style="color: var(--accent-cyan)">ATIVO</span>
                                </div>
                                <div class="data-point" style="align-items: flex-end;">
                                    <span class="data-label">XP Potencial</span>
                                    <span class="data-value">+<?= $a['specialty']['xp_reward'] ?? 0 ?></span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- PENDING REVIEW -->
        <?php if (!empty($grouped['pending_review'])): ?>
            <section class="hud-section">
                <div class="hud-section-header" style="border-color: #ffd700">
                    <h2 class="hud-section-title" style="color: #ffd700">Aguardando Aprovação</h2>
                    <span class="hud-section-count"><?= count($grouped['pending_review']) ?></span>
                </div>
                <div class="hud-grid">
                    <?php foreach ($grouped['pending_review'] as $index => $a): ?>
                        <a href="<?= base_url($tenant['slug'] . '/especialidades/' . $a['id']) ?>" 
                            class="tech-plate type-pending_review"
                            style="animation-delay: <?= $index * 50 ?>ms">
                            <div class="status-line"></div>
                            <div class="plate-header">
                                <div class="plate-content">
                                    <div class="plate-category"><?= htmlspecialchars($a['specialty']['category']['name'] ?? 'Geral') ?></div>
                                    <h3 class="plate-title"><?= htmlspecialchars($a['specialty']['name']) ?></h3>
                                </div>
                                <i class="fas fa-clock plate-icon" style="color: #ffd700"></i>
                            </div>
                            
                            <div class="hud-progress" style="color: #ffd700">
                                <div class="hud-progress-bar" style="width: 100%; opacity: 0.5;"></div>
                            </div>

                            <div class="plate-data">
                                <div class="data-point">
                                    <span class="data-label">Enviado</span>
                                    <span class="data-value"><?= (!empty($a['updated_at'])) ? date('d/m', strtotime($a['updated_at'])) : '-' ?></span>
                                </div>
                                <div class="data-point" style="align-items: flex-end;">
                                    <span class="hud-badge" style="color: #ffd700">Analisando</span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- COMPLETED -->
        <?php if (!empty($grouped['completed'])): ?>
            <section class="hud-section">
                <div class="hud-section-header" style="border-color: var(--accent-green)">
                    <h2 class="hud-section-title" style="color: var(--accent-green)">Missões Cumpridas</h2>
                    <span class="hud-section-count"><?= count($grouped['completed']) ?></span>
                </div>
                <div class="hud-grid">
                    <?php foreach ($grouped['completed'] as $index => $a): ?>
                        <a href="<?= base_url($tenant['slug'] . '/especialidades/' . $a['id']) ?>" 
                            class="tech-plate type-completed"
                            style="animation-delay: <?= $index * 50 ?>ms; opacity: 0.8">
                            <div class="status-line"></div>
                            <div class="plate-header">
                                <div class="plate-content">
                                    <div class="plate-category"><?= htmlspecialchars($a['specialty']['category']['name'] ?? 'Geral') ?></div>
                                    <h3 class="plate-title"><?= htmlspecialchars($a['specialty']['name']) ?></h3>
                                </div>
                                <i class="fas fa-check-circle plate-icon" style="color: var(--accent-green)"></i>
                            </div>
                            
                            <div class="plate-data">
                                <div class="data-point">
                                    <span class="data-label">Concluído</span>
                                    <span class="data-value"><?= (!empty($a['completed_at'])) ? date('d/m/Y', strtotime($a['completed_at'])) : '-' ?></span>
                                </div>
                                <div class="data-point" style="align-items: flex-end;">
                                    <span class="data-value" style="color: var(--accent-green)">+<?= $a['xp_earned'] ?> XP</span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

    <?php endif; ?>
</div>