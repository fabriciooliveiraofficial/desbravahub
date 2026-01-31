<?php
/**
 * Dashboard - Painel do Desbravador
 * DESIGN: Deep Glass HUD v3.0 (Content Only)
 */
?>
<div class="hud-wrapper">
    
    <!-- HUD Header -->
    <header class="hud-header">
        <div>
            <h1 class="hud-title">QG.COMMAND</h1>
            <div class="hud-subtitle">Status Operacional: Online</div>
        </div>
    </header>

    <!-- XP & Level Stats -->
    <div class="hud-stats">
        <!-- XP Card -->
        <div class="hud-stat-card primary">
            <div class="plate-header">
                <div>
                    <div class="hud-stat-value"><?= number_format($progress['xp'] ?? 0) ?></div>
                    <div class="hud-stat-label">EXP ACUMULADO</div>
                </div>
                <i class="material-icons-round hud-stat-icon">bolt</i>
            </div>
            <?php 
                $currentXp = $progress['xp'] ?? 0;
                $nextLevelXp = $progress['next_level_xp'] ?? 100;
                $progressPercent = min(100, ($currentXp / max(1, $nextLevelXp)) * 100);
            ?>
            <div class="hud-progress">
                <div class="hud-progress-bar" style="width: <?= $progressPercent ?>%; color: var(--accent-cyan)"></div>
            </div>
            <div class="plate-data" style="border:0; padding:0; margin-top:8px">
                <div class="data-point">
                    <span class="data-label">Nível Atual</span>
                    <span class="data-value">NV. <?= is_array($progress['level'] ?? 1) ? 1 : ($progress['level'] ?? 1) ?></span>
                </div>
                <div class="data-point" style="align-items: flex-end">
                    <span class="data-label">Próximo Nível</span>
                    <span class="data-value"><?= number_format($nextLevelXp) ?> XP</span>
                </div>
            </div>
        </div>

        <!-- Streak Card -->
        <div class="hud-stat-card">
            <div>
                <div class="hud-stat-value" style="color: var(--accent-warning)">7</div>
                <div class="hud-stat-label">DIAS DE STREAK</div>
            </div>
            <i class="material-icons-round hud-stat-icon" style="color: var(--accent-warning)">local_fire_department</i>
            <div class="plate-data" style="border:0; padding:0; margin-top:8px">
                    <div class="data-point">
                    <span class="data-label">Status</span>
                    <span class="hud-badge" style="color: var(--accent-warning); width: fit-content">ON FIRE</span>
                </div>
            </div>
        </div>

        <!-- Achievements -->
        <div class="hud-stat-card">
            <div>
                <div class="hud-stat-value" style="color: var(--accent-green)">12</div>
                <div class="hud-stat-label">INSÍGNIAS</div>
            </div>
            <i class="material-icons-round hud-stat-icon" style="color: var(--accent-green)">military_tech</i>
        </div>
    </div>

    <!-- Active Missions -->
    <section class="hud-section">
        <div class="hud-section-header">
            <h2 class="hud-section-title">Missões Ativas</h2>
            <a href="<?= base_url($tenant['slug'] . '/atividades') ?>" style="margin-left: auto; font-size: 0.7rem; color: var(--accent-cyan); text-transform: uppercase; letter-spacing: 0.1em; text-decoration: none; font-weight: 700;">
                Ver Todas <i class="fas fa-chevron-right" style="font-size: 0.6rem; margin-left: 4px;"></i>
            </a>
        </div>

        <?php if (empty($inProgress)): ?>
            <div class="empty-state-hud">
                <span class="material-icons-round empty-icon-hud">inbox</span>
                <h3 class="hud-section-title">Sem missões ativas</h3>
                <p class="hud-subtitle">Aguardando novas ordens do comando.</p>
                <a href="<?= base_url($tenant['slug'] . '/atividades') ?>" style="display: inline-block; margin-top: 16px; padding: 10px 20px; border: 1px solid var(--accent-cyan); border-radius: 4px; color: var(--accent-cyan); text-transform: uppercase; font-size: 0.75rem; font-weight: 700; text-decoration: none; letter-spacing: 0.1em;">
                    Iniciar Nova Missão
                </a>
            </div>
        <?php else: ?>
            <div class="hud-grid">
                <?php foreach ($inProgress as $activity): 
                    $type = $activity['type_label'] ?? 'activity';
                    $baseRoute = match($type) {
                        'specialty' => '/especialidades/',
                        'program' => '/aprendizado/',
                        default => '/atividades/'
                    };
                    $linkUrl = base_url($tenant['slug'] . $baseRoute . $activity['id']);
                ?>
                    <a href="<?= $linkUrl ?>" 
                        class="tech-plate type-in_progress">
                        <div class="status-line"></div>
                        <div class="plate-header">
                            <div class="plate-content">
                                <div class="plate-category"><?= ($activity['type_label'] ?? '') === 'specialty' ? 'Especialidade' : 'Missão' ?></div>
                                <h3 class="plate-title"><?= htmlspecialchars($activity['title']) ?></h3>
                            </div>
                            <i class="material-icons-round plate-icon">radar</i>
                        </div>
                        
                        <?php 
                        $tSteps = $activity['total_steps'] ?? 0;
                        $aSteps = $activity['answered_steps'] ?? 0;
                        $ePercent = $tSteps > 0 ? round(($aSteps / $tSteps) * 100) : 0;
                        $apPercent = $activity['progress_percent'] ?? 0;
                        ?>
                        <div class="hud-progress" style="background: rgba(255,255,255,0.05); position: relative; margin-top: 16px;">
                            <div class="hud-progress-bar" style="width: <?= $ePercent ?>%; background: var(--accent-cyan); opacity: 0.3; position: absolute;"></div>
                            <div class="hud-progress-bar" style="width: <?= $apPercent ?>%; background: var(--accent-cyan); position: relative;"></div>
                        </div>

                        <div class="plate-data">
                            <div class="data-point">
                                <span class="data-label">XP Potencial</span>
                                <span class="data-value" style="color: var(--accent-green)">+<?= $activity['xp'] ?? ($activity['xp_reward'] ?? 50) ?></span>
                            </div>
                            <div class="data-point" style="align-items: flex-end;">
                                <span class="hud-badge" style="color: var(--accent-cyan)">Em Andamento</span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <!-- Recent Events/Agenda Preview -->
    <section class="hud-section">
        <div class="hud-section-header" style="border-color: #f472b6;">
            <h2 class="hud-section-title">Próximos Eventos</h2>
        </div>
        
        <div class="hud-grid">
                <!-- Placeholder for events -->
            <div class="tech-plate type-pending">
                <div class="status-line" style="background: #f472b6;"></div>
                <div class="plate-header">
                    <div class="plate-content">
                        <div class="plate-category">Reunião Regular</div>
                        <h3 class="plate-title">Treinamento de Campo</h3>
                    </div>
                    <i class="material-icons-round plate-icon">event</i>
                </div>
                <div class="plate-data">
                    <div class="data-point">
                        <span class="data-label">Data</span>
                        <span class="data-value">Domingo, 09:00</span>
                    </div>
                    <div class="data-point" style="align-items: flex-end;">
                            <span class="data-value">Sede Local</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
