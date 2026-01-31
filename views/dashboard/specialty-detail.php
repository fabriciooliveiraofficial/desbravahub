<?php
/**
 * Specialty Detail - HUD Design v3.0
 * Matching Programs/Classes layout
 */

use App\Services\SpecialtyService;

// Get requirements with progress
$requirementsWithProgress = SpecialtyService::getRequirementsWithProgress(
    (int) $assignment['id'],
    $assignment['specialty_id']
);

// Calculate progress (Approved only for percentage, but we track drafts too visually)
$totalReqs = count($requirementsWithProgress);
$completedReqs = array_reduce($requirementsWithProgress, function($count, $req) {
    return $count + ($req['status'] === 'approved' ? 1 : 0);
}, 0);
$answeredReqs = array_reduce($requirementsWithProgress, function($count, $req) {
    return $count + ($req['status'] !== 'pending' && $req['status'] !== 'not_started' ? 1 : 0);
}, 0);

$progressPercent = $totalReqs > 0 ? round(($completedReqs / $totalReqs) * 100) : 0;
$engagementPercent = $totalReqs > 0 ? round(($answeredReqs / $totalReqs) * 100) : 0;
?>

<div class="hud-wrapper">
    <!-- Header/Title -->
    <header class="hud-header">
        <div style="display: flex; align-items: center; gap: 16px;">
            <a href="<?= base_url($tenant['slug'] . '/dashboard') ?>" class="hud-action-btn" style="position: static; transform: none; width: 40px; height: 40px;">
                <span class="material-icons-round">arrow_back</span>
            </a>
            <div>
                <h1 class="hud-title" style="font-size: 1.5rem; line-height: 1.2; margin-bottom: 4px;"><?= htmlspecialchars($specialty['name'] ?? 'Especialidade') ?></h1>
                <div class="hud-subtitle" style="font-size: 0.9rem;">
                    Especialidade • <?= htmlspecialchars($specialty['area'] ?? 'Geral') ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Stats & Progress -->
    <div class="hud-stats">
        <div class="hud-stat-card primary">
            <div class="plate-header">
                <div>
                    <div class="hud-stat-value" style="font-size: 1.5rem;"><?= $progressPercent ?>%</div>
                    <div class="hud-stat-label">Concluído</div>
                </div>
                <i class="material-icons-round hud-stat-icon" style="color: var(--accent-cyan)">pie_chart</i>
            </div>
            <div class="hud-progress" style="background: rgba(255,255,255,0.05); position: relative;">
                 <!-- Answered Bar (Engagement) -->
                <div class="hud-progress-bar" style="width: <?= $engagementPercent ?>%; background: var(--accent-cyan); opacity: 0.3; position: absolute;"></div>
                <!-- Approved Bar (Real Progress) -->
                <div class="hud-progress-bar" style="width: <?= $progressPercent ?>%; background: var(--accent-cyan); position: relative;"></div>
            </div>
            <div style="display: flex; justify-content: space-between; margin-top: 8px; font-size: 0.75rem; color: var(--hud-text-dim);">
                <span>PROGRESSO</span>
                <span><?= $engagementPercent ?>%</span>
            </div>
        </div>

        <div class="hud-stat-card">
            <div>
                <div class="hud-stat-value" style="font-size: 1.5rem;"><?= $totalReqs ?></div>
                <div class="hud-stat-label">Requisitos</div>
            </div>
            <i class="material-icons-round hud-stat-icon" style="color: var(--accent-purple)">list</i>
        </div>

        <div class="hud-stat-card">
            <div>
                <div class="hud-stat-value" style="font-size: 1.5rem;">+<?= number_format($specialty['xp_reward'] ?? 100) ?></div>
                <div class="hud-stat-label">XP Recompensa</div>
            </div>
            <i class="material-icons-round hud-stat-icon" style="color: var(--accent-green)">bolt</i>
        </div>
    </div>

    <!-- Requirements List -->
    <section class="hud-section" style="margin-top: 32px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 20px;">
            <h2 class="hud-section-title" style="margin: 0; font-size: 0.95rem; letter-spacing: 0.1em; opacity: 0.9;">ROTEIRO DE ATIVIDADES</h2>
            <div style="font-size: 0.7rem; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.05em;">
                Sequência Operacional
            </div>
        </div>
        
        <div class="hud-grid" style="grid-template-columns: 1fr; gap: 16px;">
            <?php if (empty($requirementsWithProgress)): ?>
                <div class="tech-plate" style="text-align: center; padding: 40px; color: var(--text-secondary);">
                    <span class="material-icons-round" style="font-size: 3rem; margin-bottom: 16px; opacity: 0.5;">assignment</span>
                    <p>Nenhum requisito cadastrado.</p>
                </div>
            <?php else: ?>
                <?php foreach ($requirementsWithProgress as $index => $req):
                    $status = $req['status'] ?? 'pending';
                    
                    // Detailed Status Config
                    $config = match ($status) {
                        'submitted' => ['color' => '#f59e0b', 'icon' => 'hourglass_top', 'label' => 'Em Análise', 'bg' => 'rgba(245, 158, 11, 0.1)'],
                        'approved' => ['color' => '#10b981', 'icon' => 'check_circle', 'label' => 'Aprovado', 'bg' => 'rgba(16, 185, 129, 0.1)'],
                        'rejected' => ['color' => '#ef4444', 'icon' => 'error', 'label' => 'Revisar', 'bg' => 'rgba(239, 68, 68, 0.1)'],
                        'draft' => ['color' => '#94a3b8', 'icon' => 'edit_note', 'label' => 'Rascunho', 'bg' => 'rgba(148, 163, 184, 0.1)'],
                        default => ['color' => 'var(--text-secondary)', 'icon' => 'radio_button_unchecked', 'label' => 'Pendente', 'bg' => 'rgba(255, 255, 255, 0.03)']
                    };
                    ?>
                    
                    <!-- Note: Clicking opens modal logic which we might need to verify exists for specialties -->
                    <!-- For now, using the 'start' or 'learn' logic if available, or just a generic container -->
                    <div class="tech-plate learning-card" 
                         onclick="window.location.href='<?= base_url($tenant['slug'] . '/especialidades/' . $assignment['id'] . '/requisito/' . $req['id']) ?>'"
                         style="
                            cursor: pointer; position: relative; overflow: hidden; 
                            border-left: 4px solid <?= $config['color'] === 'var(--text-secondary)' ? 'transparent' : $config['color'] ?>;
                            padding: 0; min-height: 100px; display: flex;
                            background: linear-gradient(145deg, rgba(20, 20, 30, 0.6), rgba(20, 20, 30, 0.8)) !important;
                            animation-delay: <?= $index * 0.05 ?>s;
                         ">
                        
                        <!-- Background Progress Hint -->
                        <?php if ($status === 'approved'): ?>
                            <div style="position: absolute; inset: 0; background: linear-gradient(90deg, <?= $config['bg'] ?>, transparent 40%); opacity: 0.2; pointer-events: none;"></div>
                        <?php endif; ?>

                        <!-- Number Column -->
                        <div style="
                            width: 70px; display: flex; align-items: flex-start; justify-content: center;
                            padding-top: 24px; flex-shrink: 0;
                        ">
                            <div style="
                                width: 32px; height: 32px; 
                                border-radius: 10px; 
                                background: rgba(255,255,255,0.03); 
                                color: <?= $config['color'] ?>;
                                display: flex; align-items: center; justify-content: center;
                                font-family: 'JetBrains Mono', monospace; font-weight: 700; font-size: 0.9rem;
                                border: 1px solid rgba(255,255,255,0.05);
                            ">
                                <?php if ($status === 'approved'): ?>
                                    <span class="material-icons-round" style="font-size: 1rem;">check</span>
                                <?php else: ?>
                                    <?= str_pad($req['order_num'] ?? ($index + 1), 2, '0', STR_PAD_LEFT) ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Content Column -->
                        <div class="plate-content" style="flex: 1; padding: 20px 0 20px 0; display: flex; flex-direction: column; gap: 10px;">
                            
                            <!-- Top Meta -->
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span class="hud-badge" style="
                                    font-size: 0.7rem; padding: 2px 8px; border-radius: 6px; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700;
                                    background: rgba(255,255,255,0.03); color: var(--text-secondary); border: 1px solid rgba(255,255,255,0.05);
                                ">
                                    <?= $req['points'] ?? 10 ?> XP
                                </span>
                                <?php if (!empty($req['is_required'])): ?>
                                    <span class="hud-badge" style="
                                        font-size: 0.7rem; padding: 2px 8px; border-radius: 6px; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700;
                                        color: var(--accent-cyan); background: rgba(6, 182, 212, 0.08); border: 1px solid rgba(6, 182, 212, 0.15);
                                    ">
                                        Obrigatório
                                    </span>
                                <?php else: ?>
                                    <span class="hud-badge" style="
                                        font-size: 0.7rem; padding: 2px 8px; border-radius: 6px; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700;
                                        color: var(--text-secondary); background: rgba(255,255,255,0.05);
                                    ">
                                        Opcional
                                    </span>
                                <?php endif; ?>
                                
                                <!-- Detailed Status -->
                                <?php if ($status !== 'pending' && $status !== 'not_started'): ?>
                                    <div style="display: flex; align-items: center; gap: 4px; border: 1px solid <?= $config['bg'] ?>; padding: 2px 8px; border-radius: 6px; background: rgba(0,0,0,0.2);"> 
                                        <div style="width: 6px; height: 6px; border-radius: 50%; background: <?= $config['color'] ?>; box-shadow: 0 0 5px <?= $config['color'] ?>;"></div>
                                        <span style="font-size: 0.65rem; color: <?= $config['color'] ?>; font-weight: 700; text-transform: uppercase;">
                                            <?= $config['label'] ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Title -->
                            <h3 class="plate-title" style="
                                font-size: 1rem; line-height: 1.4em; margin: 0;
                                display: -webkit-box !important; -webkit-line-clamp: 2 !important; -webkit-box-orient: vertical !important; overflow: hidden !important;
                                max-height: 2.8em; font-weight: 500; color: #f1f5f9; letter-spacing: 0.01em;
                            "><?= htmlspecialchars(mb_strimwidth($req['title'], 0, 60, "...")) ?></h3>
                            
                        </div>

                        <!-- Action Column -->
                        <div style="
                            width: 50px; display: flex; align-items: center; justify-content: center;
                            padding-right: 12px;
                        ">
                            <div class="card-arrow" style="
                                width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;
                                background: transparent; border-radius: 50%; border: 1px solid rgba(255,255,255,0.05);
                                color: var(--text-secondary); transition: all 0.2s;
                            ">
                                <span class="material-icons-round" style="font-size: 1.2rem;">chevron_right</span>
                            </div>
                        </div>

                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <style>
        .learning-card {
            background: linear-gradient(145deg, rgba(30, 30, 46, 0.8), rgba(20, 20, 35, 0.9)) !important;
            backdrop-filter: blur(10px);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 24px !important; 
            overflow: hidden; 
        }
        .learning-card:hover {
            transform: translateY(-2px) !important;
            background: linear-gradient(145deg, rgba(40, 40, 60, 0.9), rgba(25, 25, 45, 0.95)) !important;
            border-color: rgba(255,255,255,0.1);
            box-shadow: 0 8px 24px rgba(0,0,0,0.3);
        }
        .learning-card:hover .card-arrow {
            background: var(--accent-cyan);
            color: #fff;
        }
        .learning-card:active {
            transform: scale(0.99) !important;
        }
    </style>
</div>

