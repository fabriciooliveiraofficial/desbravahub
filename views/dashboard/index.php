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
        <!-- XP Card (Heroic) -->
        <div class="hud-stat-card primary tech-plate vibrant-cyan stagger-1" style="flex: 2; padding: 28px;">
            <div class="plate-header">
                <div>
                    <div class="hud-stat-value" style="font-size: 3.5rem; line-height: 1;"><?= number_format($progress['xp'] ?? 0) ?></div>
                    <div class="hud-stat-label" style="font-size: 0.9rem; letter-spacing: 0.2em;">EXP ACUMULADO</div>
                </div>
                <i class="material-icons-round hud-stat-icon" style="font-size: 3rem; opacity: 1; filter: drop-shadow(0 0 10px var(--accent-cyan));">bolt</i>
            </div>
            
            <?php 
                $currentXp = $progress['xp'] ?? 0;
                $nextLevelXp = $progress['next_level_xp'] ?? 100;
                $progressPercent = min(100, ($currentXp / max(1, $nextLevelXp)) * 100);
            ?>
            
            <div style="margin: 20px 0;">
                <div class="hud-progress" style="height: 12px; background: rgba(0,0,0,0.3); border-radius: 100px; overflow: hidden; border: 1px solid rgba(255,255,255,0.05);">
                    <div class="hud-progress-bar" style="width: <?= $progressPercent ?>%; background: linear-gradient(90deg, var(--accent-cyan), #0ea5e9); box-shadow: 0 0 15px var(--accent-cyan); height: 100%;"></div>
                </div>
            </div>

            <div class="plate-data" style="border:0; padding:0; margin-top:8px; grid-template-columns: 1fr 1fr;">
                <div class="data-point">
                    <span class="data-label">Nível Atual</span>
                    <span class="data-value" style="font-size: 1.2rem; color: #fff;">NV. <?= is_array($progress['level'] ?? 1) ? 1 : ($progress['level'] ?? 1) ?></span>
                </div>
                <div class="data-point" style="align-items: flex-end">
                    <span class="data-label">Próximo Nível</span>
                    <span class="data-value" style="font-size: 1.2rem; color: #fff;"><?= number_format($nextLevelXp) ?> <span style="font-size: 0.7rem; opacity: 0.5;">XP</span></span>
                </div>
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 20px; flex: 1.2;">
            <!-- Streak Card (Vibrant Orange) -->
            <div class="hud-stat-card tech-plate vibrant-orange stagger-2" style="padding: 24px;">
                <div class="plate-header" style="margin-bottom: 8px;">
                    <div>
                        <div class="hud-stat-value" style="color: var(--accent-warning); font-size: 2.8rem;"><?= $progress['streak'] ?? 0 ?></div>
                        <div class="hud-stat-label">DIAS DE STREAK</div>
                    </div>
                    <i class="material-icons-round hud-stat-icon" style="color: var(--accent-warning); filter: drop-shadow(0 0 8px var(--accent-warning)); opacity: 1;">local_fire_department</i>
                </div>
                <div class="plate-data" style="border:0; padding:0; margin-top:0;">
                      <div class="data-point">
                        <span class="data-label">Status Especial</span>
                        <div class="hud-badge" style="color: <?= ($progress['streak'] ?? 0) > 0 ? 'var(--accent-warning)' : 'var(--hud-text-dim)' ?>; width: fit-content; background: rgba(249, 115, 22, 0.1); border-color: rgba(249, 115, 22, 0.3);">
                            <?= ($progress['streak'] ?? 0) > 0 ? 'STATUS: ON FIRE' : 'REATIVAR AGORA' ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Achievements Card (Vibrant Green) -->
            <div class="hud-stat-card tech-plate vibrant-green stagger-3" style="padding: 24px;">
                <div class="plate-header" style="margin-bottom: 8px;">
                    <div>
                        <div class="hud-stat-value" style="color: var(--accent-green); font-size: 2.8rem;"><?= $insigniaCount ?></div>
                        <div class="hud-stat-label">INSÍGNIAS</div>
                    </div>
                    <i class="material-icons-round hud-stat-icon" style="color: var(--accent-green); filter: drop-shadow(0 0 8px var(--accent-green)); opacity: 1;">military_tech</i>
                </div>
                <div class="plate-data" style="border:0; padding:0; margin-top:0;">
                    <div class="data-point">
                      <span class="data-label">Colação de Grau</span>
                      <span class="data-value" style="font-size: 0.8rem; opacity: 0.7;">TOTAL CONQUISTADO</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Missions -->
    <section class="hud-section" style="margin-top: 40px;">
        <div class="hud-section-header">
            <h2 class="hud-section-title" style="font-size: 1.4rem; letter-spacing: 0.1em; color: var(--accent-cyan);">MISSÕES ATIVAS</h2>
            <a href="<?= base_url($tenant['slug'] . '/atividades') ?>" style="margin-left: auto; font-size: 0.75rem; color: #fff; background: rgba(255,255,255,0.05); padding: 6px 14px; border-radius: 100px; text-transform: uppercase; letter-spacing: 0.1em; text-decoration: none; font-weight: 800; border: 1px solid rgba(255,255,255,0.1);">
                ARQUIVO COMPLETO <i class="fas fa-arrow-right" style="font-size: 0.6rem; margin-left: 6px;"></i>
            </a>
        </div>

        <?php if (empty($inProgress)): ?>
            <div class="empty-state-hud stagger-4">
                <span class="material-icons-round empty-icon-hud" style="font-size: 5rem;">radar</span>
                <h3 class="hud-section-title">SEM ATIVIDADE NO RADAR</h3>
                <p class="hud-subtitle">Nenhuma missão em curso. O QG aguarda suas ordens.</p>
                <a href="<?= base_url($tenant['slug'] . '/atividades') ?>" class="hud-btn primary" style="margin-top: 24px;">
                    INICIAR NOVA INCURSÃO
                </a>
            </div>
        <?php else: ?>
            <div class="hud-grid">
                <?php foreach ($inProgress as $idx => $activity): 
                    $type = $activity['type_label'] ?? 'activity';
                    $baseRoute = match($type) {
                        'specialty' => '/especialidades/',
                        'program' => '/aprendizado/',
                        default => '/atividades/'
                    };
                    $linkUrl = base_url($tenant['slug'] . $baseRoute . $activity['id']);
                ?>
                    <a href="<?= $linkUrl ?>" 
                        class="tech-plate type-in_progress vibrant-cyan stagger-<?= ($idx % 4) + 1 ?>">
                        <div class="status-line"></div>
                        <div class="plate-header">
                            <div class="plate-content">
                                <div class="plate-category" style="color: var(--accent-cyan); font-weight: 900;"><?= ($activity['type_label'] ?? '') === 'specialty' ? 'ESPECIALIDADE' : 'MISSÃO OFICIAL' ?></div>
                                <h3 class="plate-title" style="font-size: 1.3rem; margin-top: 8px;"><?= htmlspecialchars($activity['title']) ?></h3>
                            </div>
                            <i class="material-icons-round plate-icon" style="color: var(--accent-cyan);">rocket_launch</i>
                        </div>
                        
                        <?php 
                        $tSteps = $activity['total_steps'] ?? 0;
                        $aSteps = $activity['answered_steps'] ?? 0;
                        $ePercent = $tSteps > 0 ? round(($aSteps / $tSteps) * 100) : 0;
                        $apPercent = $activity['progress_percent'] ?? 0;
                        ?>
                        <div style="margin-top: 24px;">
                            <div class="hud-progress" style="background: rgba(0,0,0,0.4); position: relative; height: 10px; border-radius: 4px; overflow: hidden; border: 1px solid rgba(255,255,255,0.05);">
                                <div class="hud-progress-bar" style="width: <?= $ePercent ?>%; background: var(--accent-cyan); opacity: 0.2; position: absolute; height: 100%;"></div>
                                <div class="hud-progress-bar" style="width: <?= $apPercent ?>%; background: linear-gradient(90deg, #22d3ee, #06b6d4); position: relative; height: 100%; box-shadow: 0 0 10px var(--accent-cyan);"></div>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-top: 8px;">
                                <span style="font-size: 0.65rem; font-weight: 800; color: var(--hud-text-dim);">CARGA DE DADOS</span>
                                <span style="font-size: 0.65rem; font-weight: 900; color: var(--accent-cyan);"><?= $apPercent ?>%</span>
                            </div>
                        </div>

                        <div class="plate-data" style="margin-top: 24px; border-top: 1px solid rgba(255,255,255,0.1);">
                            <div class="data-point">
                                <span class="data-label">RECOMPENSA XP</span>
                                <span class="data-value" style="color: var(--accent-green); font-size: 1.1rem;">+<?= $activity['xp'] ?? ($activity['xp_reward'] ?? 50) ?> <span style="font-size: 0.6rem; opacity: 0.6;">XP</span></span>
                            </div>
                            <div class="data-point" style="align-items: flex-end;">
                                <div class="hud-badge" style="color: var(--accent-cyan); border-radius: 4px; border-width: 2px;">ON-MISSION</div>
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
            <?php if ($nextEvent): ?>
                <div class="tech-plate type-pending">
                    <div class="status-line" style="background: #f472b6;"></div>
                    <div class="plate-header">
                        <div class="plate-content">
                            <div class="plate-category"><?= htmlspecialchars($nextEvent['type'] ?? 'Evento') ?></div>
                            <h3 class="plate-title"><?= htmlspecialchars($nextEvent['title']) ?></h3>
                        </div>
                        <i class="material-icons-round plate-icon">event</i>
                    </div>
                    <div class="plate-data">
                        <div class="data-point">
                            <span class="data-label">Data</span>
                            <span class="data-value"><?= date('d/m, H:i', strtotime($nextEvent['start_datetime'])) ?></span>
                        </div>
                        <div class="data-point" style="align-items: flex-end;">
                            <span class="data-value"><?= htmlspecialchars($nextEvent['location'] ?? 'Sede Local') ?></span>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="tech-plate" style="border-color: rgba(244, 114, 182, 0.2); background: rgba(244, 114, 182, 0.05);">
                    <div class="plate-header" style="justify-content: center; padding: 20px;">
                        <div class="plate-content" style="text-align: center;">
                            <div class="hud-subtitle">Nenhum evento agendado</div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>
