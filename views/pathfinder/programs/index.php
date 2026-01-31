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
        <div class="hud-tabs-container">
            <button class="hud-tab program-filter-tab active" data-filter-type="specialty">
                <div class="tab-glow"></div>
                <span class="material-icons-round">star_outline</span>
                <span class="tab-text">Especialidades</span>
            </button>
            <button class="hud-tab program-filter-tab" data-filter-type="class">
                <div class="tab-glow"></div>
                <span class="material-icons-round">military_tech</span>
                <span class="tab-text">Classes</span>
            </button>
        </div>

        <div class="hud-grid" id="programs-grid" style="grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));">
            <?php foreach ($programs as $index => $program): ?>
                <?php 
                $totalSteps = $program['total_steps'] ?? 0;
                $answeredSteps = $program['answered_steps'] ?? 0;
                $isSubmitted = in_array($program['user_status'], ['submitted', 'completed', 'approved']);
                $canSubmit = ($totalSteps > 0 && $answeredSteps >= $totalSteps) && !$isSubmitted;
                $engPercent = $totalSteps > 0 ? round(($answeredSteps / $totalSteps) * 100) : 0;
                $appPercent = $program['progress_percent'] ?? 0;
                ?>
                <div class="tech-plate program-card" 
                   data-href="<?= base_url($tenant['slug'] . '/aprendizado/' . $program['id']) ?>"
                   data-program-id="<?= $program['id'] ?>"
                   data-category-type="<?= $program['category_type'] ?? 'specialty' ?>"
                   style="cursor: pointer; animation-delay: <?= $index * 0.05 ?>s; <?= ($program['category_type'] ?? 'specialty') === 'specialty' ? '' : 'display: none;' ?>">
                    
                    <div class="plate-header">
                        <div class="plate-content">
                            <div class="plate-category"><?= htmlspecialchars($program['category_name'] ?? 'Programa Oficial') ?></div>
                            <h3 class="plate-title"><?= htmlspecialchars($program['name']) ?></h3>
                        </div>
                        <i class="material-icons-round plate-icon" style="color: <?= $program['category_color'] ?? 'var(--accent-cyan)' ?>"><?= $program['category_icon'] ?? 'menu_book' ?></i>
                    </div>

                    <div style="margin: 16px 0;">
                        <div class="hud-progress" style="background: rgba(255,255,255,0.05);">
                            <div class="hud-progress-bar" style="width: <?= $engPercent ?>%; background: var(--accent-cyan); opacity: 0.3; position: absolute;"></div>
                            <div class="hud-progress-bar" style="width: <?= $appPercent ?>%; background: var(--accent-cyan); position: relative;"></div>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-top: 8px; font-size: 0.75rem; color: var(--hud-text-dim);">
                            <span>PROGRESSO</span>
                            <span><?= $engPercent ?>%</span>
                        </div>
                    </div>

                    <div class="plate-data" style="margin-bottom: 16px;">
                        <div class="data-point">
                            <span class="data-label">Status</span>
                            <span class="data-value" style="font-size: 0.75rem; text-transform: uppercase; color: var(--accent-cyan);">
                                <?= str_replace('_', ' ', $program['user_status']) ?>
                            </span>
                        </div>
                        <div class="data-point" style="align-items: flex-end;">
                            <span class="hud-badge" style="color: var(--accent-cyan); border-radius: 4px; padding: 4px 8px;">DETALHES</span>
                        </div>
                    </div>
                    
                    <button type="button" 
                            class="action-button-hud program-submit-btn <?= $canSubmit ? '' : 'disabled' ?>"
                            data-program-id="<?= $program['id'] ?>"
                            <?= $canSubmit ? '' : 'disabled' ?>
                            style="width: 100%; margin-top: auto; z-index: 10;">
                        <span class="material-icons-round" style="font-size: 1.1rem;"><?= $isSubmitted ? 'task_alt' : 'send' ?></span>
                        <?= $isSubmitted ? 'Processando / Concluído' : 'Enviar Resposta' ?>
                    </button>
                </div>
            <?php endforeach; ?>

            <!-- Empty category state (hidden by default) -->
            <div id="empty-category-notice" class="empty-state-hud" style="display: none; grid-column: 1 / -1; width: 100%;">
                <span class="material-icons-round empty-icon-hud" style="font-size: 4rem;">folder_open</span>
                <h3 class="hud-section-title">Nada aqui ainda</h3>
                <p class="hud-subtitle">Nenhum item desta categoria atribuído a você.</p>
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
</style>


    <?php endif; ?>
</div>
