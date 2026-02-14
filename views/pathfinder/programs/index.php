<?php
/**
 * Learning Center - Programs Index
 * DESIGN: Deep Glass HUD v4.0 (Filtered Edition)
 */
?>
<div class="hud-wrapper">
    <header class="hud-header">
        <div>
            <h1 class="hud-title">Centro de Treinamento</h1>
            <div class="hud-subtitle">Selecione uma categoria para progredir em sua jornada</div>
        </div>
    </header>

    <?php if (empty($programs)): ?>
        <div class="empty-state-hud">
            <span class="material-icons-round empty-icon-hud">school</span>
            <h3 class="hud-section-title">Nenhum programa disponível</h3>
            <p class="hud-subtitle">Aguarde instruções do seu instrutor.</p>
        </div>
    <?php else: ?>
        
        <!-- Tech Tabs Navigation -->
        <div class="hud-tabs-container stagger-1" style="margin-left: auto; margin-right: auto; margin-top: -10px;">
            <button class="hud-tab program-filter-tab active" data-filter-type="specialty">
                <div class="tab-glow"></div>
                <span class="material-icons-round">star</span>
                <span class="tab-text">Especialidades</span>
            </button>
            <button class="hud-tab program-filter-tab" data-filter-type="class">
                <div class="tab-glow"></div>
                <span class="material-icons-round">military_tech</span>
                <span class="tab-text">Classes</span>
            </button>
        </div>

        <div class="hud-grid" id="programs-grid" style="grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px;">
            <?php foreach ($programs as $index => $program): ?>
                <?php 
                $totalSteps = $program['total_steps'] ?? 0;
                $answeredSteps = $program['answered_steps'] ?? 0;
                $approvedSteps = $program['approved_steps'] ?? 0;
                $rejectedSteps = $program['rejected_steps'] ?? 0;
                
                // Robust Status Check
                $rawStatus = strtolower(trim($program['user_status'] ?? 'not_started'));
                $isSubmitted = in_array($rawStatus, ['submitted', 'completed', 'approved']);
                
                // Override status if there are rejected steps (backend should already revert, but be defensive)
                $hasRejections = $rejectedSteps > 0;
                if ($hasRejections && $isSubmitted) {
                    $rawStatus = 'in_progress';
                    $isSubmitted = false;
                }
                
                $canSubmit = ($totalSteps > 0 && $answeredSteps >= $totalSteps) && !$isSubmitted;
                $engPercent = $totalSteps > 0 ? round(($answeredSteps / $totalSteps) * 100) : 0;
                $appPercent = $program['progress_percent'] ?? 0;
                $catType = $program['category_type'] ?? 'specialty';
                $vibrantClass = ($catType === 'class') ? 'vibrant-purple' : 'vibrant-cyan';
                ?>
                <div class="tech-plate program-card <?= $vibrantClass ?> stagger-<?= ($index % 4) + 1 ?>" 
                   data-href="<?= base_url($tenant['slug'] . '/aprendizado/' . $program['id']) ?>"
                   data-program-id="<?= $program['id'] ?>"
                   data-category-type="<?= $catType ?>"
                   style="cursor: pointer; padding: 28px; <?= $catType === 'specialty' ? '' : 'display: none;' ?>">
                    
                    <div class="status-line" <?= $hasRejections ? 'style="background: linear-gradient(90deg, #ef4444, #f87171); box-shadow: 0 0 12px rgba(239, 68, 68, 0.4);"' : '' ?>></div>
                    
                    <div class="plate-header">
                        <div class="plate-content">
                            <div class="plate-category" style="color: <?= $catType === 'class' ? '#a78bfa' : 'var(--accent-cyan)' ?>; font-weight: 900;">
                                <?= htmlspecialchars($program['category_name'] ?? ($catType === 'class' ? 'CLASSE REGULAR' : 'ESPECIALIDADE')) ?>
                            </div>
                            <h3 class="plate-title" style="font-size: 1.4rem; margin-top: 8px;"><?= htmlspecialchars($program['name']) ?></h3>
                        </div>
                        <i class="material-icons-round plate-icon" style="color: <?= $catType === 'class' ? '#8b5cf6' : 'var(--accent-cyan)' ?>; opacity: 1;">
                            <?= $program['category_icon'] ?? ($catType === 'class' ? 'military_tech' : 'hotel_class') ?>
                        </i>
                    </div>

                    <div style="margin: 24px 0;">
                        <div class="hud-progress" style="background: rgba(0,0,0,0.4); height: 10px; border-radius: 100px; border: 1px solid rgba(255,255,255,0.05); position: relative; overflow: hidden;">
                            <!-- Engagement Bar (Sent) - Increased opacity for visibility -->
                            <div class="hud-progress-bar" style="width: <?= $engPercent ?>%; background: <?= $catType === 'class' ? '#8b5cf6' : 'var(--accent-cyan)' ?>; opacity: 0.6; position: absolute; height: 100%;"></div>
                            <!-- Approved Bar (Valid) -->
                            <div class="hud-progress-bar" style="width: <?= $appPercent ?>%; background: linear-gradient(90deg, <?= $catType === 'class' ? '#a78bfa, #8b5cf6' : '#22d3ee, #06b6d4' ?>); position: relative; height: 100%; box-shadow: 0 0 12px currentColor;"></div>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-top: 10px;">
                            <?php 
                            // Smart Label Logic v2 — handles all states
                            $displayPercent = $appPercent;
                            $displayLabel = 'DESEMPENHO OPERACIONAL';
                            $displayColor = '#fff';

                            if ($hasRejections) {
                                // Rejected state — show how many need fixing
                                $displayPercent = $appPercent;
                                $displayLabel = "⚠ REVISÃO NECESSÁRIA <span style='opacity:0.7; font-weight:400;'>($rejectedSteps " . ($rejectedSteps > 1 ? 'itens' : 'item') . ")</span>";
                                $displayColor = '#f87171';
                            } elseif ($appPercent == 0 && $engPercent > 0) {
                                $displayPercent = $engPercent;
                                $displayLabel = "EM ANÁLISE (QG) <span style='opacity:0.7; font-weight:400;'>($answeredSteps/$totalSteps)</span>";
                                $displayColor = '#fbbf24';
                            } elseif ($appPercent > 0 && $appPercent < 100) {
                                $displayLabel = "APROVADOS <span style='opacity:0.7; font-weight:400;'>($approvedSteps/$totalSteps)</span>";
                                $displayColor = 'var(--accent-cyan)';
                            } elseif ($appPercent >= 100) {
                                $displayLabel = 'MISSÃO COMPLETA';
                                $displayColor = '#10b981';
                            } elseif ($appPercent == 0 && $engPercent == 0 && $answeredSteps > 0) {
                                $displayLabel = "ERRO CÁLCULO ($answeredSteps/$totalSteps)";
                                $displayColor = '#f87171';
                            }
                            ?>
                            <span style="font-size: 0.65rem; font-weight: 800; color: var(--hud-text-dim);"><?= $displayLabel ?></span>
                            <span style="font-size: 0.75rem; font-weight: 900; color: <?= $displayColor ?>;"><?= $displayPercent ?>%</span>
                        </div>
                    </div>

                    <div class="plate-data" style="margin-bottom: 24px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px;">
                        <div class="data-point">
                            <span class="data-label">Status Operacional</span>
                            <?php
                            $statusDisplay = str_replace('_', ' ', $rawStatus);
                            $statusColor = '#fff';
                            $statusIcon = '';
                            if ($hasRejections) {
                                $statusDisplay = 'REVISÃO PENDENTE';
                                $statusColor = '#f87171';
                                $statusIcon = '🔴';
                            } elseif ($rawStatus === 'submitted') {
                                $statusDisplay = 'ENVIADO';
                                $statusColor = '#fbbf24';
                                $statusIcon = '🟡';
                            } elseif ($rawStatus === 'completed' || $rawStatus === 'approved') {
                                $statusDisplay = 'CONCLUÍDO';
                                $statusColor = '#10b981';
                                $statusIcon = '🟢';
                            } elseif ($rawStatus === 'in_progress') {
                                $statusDisplay = 'EM PROGRESSO';
                                $statusColor = 'var(--accent-cyan)';
                            }
                            ?>
                            <span class="data-value" style="font-size: 0.8rem; color: <?= $statusColor ?>; text-transform: uppercase; font-weight: 900;">
                                <?= $statusIcon ?> <?= $statusDisplay ?>
                            </span>
                        </div>
                        <div class="data-point" style="align-items: flex-end;">
                            <div class="hud-badge" style="color: var(--accent-green); border-color: rgba(0, 255, 136, 0.3); background: rgba(0, 255, 136, 0.05);">
                                XP: +<?= $program['xp_reward'] ?? 100 ?>
                            </div>
                        </div>
                    </div>
                    
                    <?php
                    $btnLabel = 'ENVIAR MISSÃO';
                    $btnIcon = 'rocket_launch';
                    $btnStyle = '';
                    if ($hasRejections) {
                        $btnIcon = 'rebase_edit';
                        $btnLabel = 'REENVIAR CORREÇÕES';
                        $canSubmit = false; // Must fix and resubmit individual steps
                        $btnStyle = 'background: linear-gradient(135deg, #ef4444, #dc2626); box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);';
                    } elseif ($isSubmitted) {
                        $btnIcon = 'verified';
                        $btnLabel = ($rawStatus === 'submitted') ? 'MISSÃO ENVIADA' : 'MISSÃO CONCLUÍDA';
                    } elseif ($rawStatus === 'rejected') {
                        $btnIcon = 'rebase_edit';
                        $btnLabel = 'REENVIAR CORREÇÕES';
                        $canSubmit = true;
                    } elseif (!$canSubmit) {
                        $btnIcon = 'radio_button_unchecked';
                        $btnLabel = 'REQUISITOS PENDENTES';
                    }
                    ?>
                    <button type="button" 
                            class="hud-btn primary program-submit-btn <?= $canSubmit ? '' : 'secondary' ?>"
                            data-progress-id="<?= $program['progress_id'] ?>"
                            <?= $canSubmit ? '' : 'disabled' ?>
                            style="width: 100%; z-index: 10; padding: 12px; font-size: 0.75rem; justify-content: center; <?= $btnStyle ?: ($canSubmit ? 'box-shadow: 0 4px 15px rgba(6, 182, 212, 0.3);' : 'box-shadow: none;') ?>">
                        <i class="material-icons-round" style="font-size: 1.1rem;"><?= $btnIcon ?></i>
                        <?= $btnLabel ?>
                    </button>
                </div>
            <?php endforeach; ?>

            <!-- Empty category state (hidden by default) -->
            <div id="empty-category-notice" class="empty-state-hud stagger-4" style="display: none; grid-column: 1 / -1; width: 100%;">
                <span class="material-icons-round empty-icon-hud" style="font-size: 5rem;">radar</span>
                <h3 class="hud-section-title">SETOR VAZIO</h3>
                <p class="hud-subtitle">Nenhuma missão foi atribuída a este setor de treinamento.</p>
            </div>
        </div>

<style>
    /* Tabs Navigation Styles */
    .hud-tabs-container {
        display: flex;
        gap: 12px;
        margin-bottom: 32px;
        background: rgba(0, 0, 0, 0.2);
        padding: 6px;
        border-radius: 16px;
        border: 1px solid rgba(255, 255, 255, 0.05);
        max-width: fit-content;
    }

    .hud-tab {
        background: transparent;
        border: none;
        padding: 12px 24px;
        border-radius: 12px;
        color: var(--hud-text-dim);
        font-weight: 800;
        font-size: 0.85rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 10px;
        position: relative;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .hud-tab .material-icons-round {
        font-size: 1.2rem;
        transition: transform 0.3s;
    }

    .hud-tab.active {
        color: #fff;
        background: rgba(255, 255, 255, 0.05);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    .hud-tab.active .tab-glow {
        position: absolute;
        bottom: 0;
        left: 20%;
        right: 20%;
        height: 2px;
        background: var(--accent-cyan);
        box-shadow: 0 0 10px var(--accent-cyan);
        border-radius: 100px;
    }

    .hud-tab.active .material-icons-round {
        color: var(--accent-cyan);
        transform: scale(1.1);
    }

    .hud-tab:hover:not(.active) {
        color: #fff;
        background: rgba(255, 255, 255, 0.02);
    }

    /* Core Action Button */
    .action-button-hud {
        background: linear-gradient(135deg, #6366f1, #22d3ee);
        color: white;
        border: none;
        border-radius: 12px;
        padding: 14px;
        font-size: 0.85rem;
        font-weight: 800;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.2s;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .action-button-hud:hover:not(.disabled) {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(99, 102, 241, 0.4);
    }
    .action-button-hud.disabled {
        background: rgba(255,255,255,0.05);
        color: rgba(255,255,255,0.2);
        cursor: not-allowed;
        box-shadow: none;
        opacity: 0.6;
    }

    @media (max-width: 480px) {
        .hud-tabs-container {
            width: 100%;
            max-width: 100%;
            gap: 8px;
            padding: 4px;
            margin-left: 0;
            margin-right: 0;
        }
        .hud-tab {
            padding: 10px 12px;
            font-size: 0.75rem;
            flex: 1;
            justify-content: center;
        }
        .hud-tab .material-icons-round {
            font-size: 1.1rem;
        }
    }
</style>


    <?php endif; ?>
</div>


