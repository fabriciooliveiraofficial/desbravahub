<?php
/**
 * Admin: Assignment Details View
 * Shows detailed logs and progress for a specific mission
 */

$specialty = $assignment['specialty'] ?? [];
$pathfinder = $assignment['user_name'] ?? 'Desbravador';

// Calculate progress
$totalReqs = count($requirements);
$completedReqs = array_reduce($requirements, function($count, $req) {
    return $count + ($req['status'] === 'approved' ? 1 : 0);
}, 0);
$progressPercent = $totalReqs > 0 ? round(($completedReqs / $totalReqs) * 100) : 0;
?>

<div class="main-content">
    <div class="page-header">
        <div class="header-actions">
            <a href="<?= base_url($tenant['slug'] . '/admin/especialidades/god-mode') ?>" class="btn btn-ghost">
                <i class="fa-solid fa-arrow-left"></i> Voltar ao Mission Control
            </a>
        </div>
        <h1><i class="fa-solid fa-clipboard-check"></i> Detalhes da Missão</h1>
        <p class="subtitle">Histórico completo e progresso detalhado</p>
    </div>

    <div class="content-wrapper" style="max-width: 1000px; margin: 0 auto;">
        <!-- Assignment Info Card -->
        <div class="card">
            <div class="card-header">
                <h2><?= htmlspecialchars($specialty['name'] ?? 'Especialidade') ?></h2>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px;">
                    <div>
                        <strong>Desbravador:</strong><br>
                        <?= htmlspecialchars($pathfinder) ?>
                    </div>
                    <div>
                        <strong>Status:</strong><br>
                        <span class="badge badge-<?= $assignment['status'] === 'completed' ? 'success' : ($assignment['status'] === 'in_progress' ? 'warning' : 'secondary') ?>">
                            <?= ucfirst($assignment['status']) ?>
                        </span>
                    </div>
                    <div>
                        <strong>Progresso:</strong><br>
                        <?= $completedReqs ?>/<?= $totalReqs ?> (<?= $progressPercent ?>%)
                    </div>
                    <div>
                        <strong>Atribuído em:</strong><br>
                        <?= date('d/m/Y', strtotime($assignment['created_at'])) ?>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div style="background: #e0e7ff; border-radius: 10px; height: 8px; overflow: hidden;">
                    <div style="background: linear-gradient(90deg, #6366f1, #22d3ee); height: 100%; width: <?= $progressPercent ?>%; transition: width 0.3s;"></div>
                </div>
            </div>
        </div>

        <!-- Requirements Progress -->
        <div class="card" style="margin-top: 20px;">
            <div class="card-header">
                <h3>Requisitos (<?= $totalReqs ?>)</h3>
            </div>
            <div class="card-body">
                <?php if (empty($requirements)): ?>
                    <p style="text-align: center; color: #94a3b8; padding: 40px;">Nenhum requisito cadastrado.</p>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <?php foreach ($requirements as $req): ?>
                            <div style="padding: 16px; border: 2px solid <?= $req['status'] === 'approved' ? '#4ade80' : ($req['status'] === 'pending' ? '#e2e8f0' : '#fbbf24') ?>; border-radius: 12px; background: <?= $req['status'] === 'approved' ? '#f0fdf4' : '#ffffff' ?>;">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                    <div style="flex: 1;">
                                        <strong style="color: #1e293b;"><?= $req['order_num'] ?>. <?= htmlspecialchars($req['title']) ?></strong>
                                        <?php if (!empty($req['description'])): ?>
                                            <p style="font-size: 0.9rem; color: #64748b; margin-top: 6px;"><?= nl2br(htmlspecialchars($req['description'])) ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($req['answer'])): ?>
                                            <div style="margin-top: 10px; padding: 10px; background: #f8fafc; border-radius: 8px;">
                                                <strong style="font-size: 0.85rem; color: #64748b;">Resposta:</strong>
                                                <p style="margin-top: 4px; color: #1e293b;"><?= nl2br(htmlspecialchars($req['answer'])) ?></p>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($req['feedback'])): ?>
                                            <div style="margin-top: 10px; padding: 10px; background: #fffbeb; border-radius: 8px; border-left: 3px solid #f59e0b;">
                                                <strong style="font-size: 0.85rem; color: #92400e;">Feedback do Líder:</strong>
                                                <p style="margin-top: 4px; color: #78350f;"><?= nl2br(htmlspecialchars($req['feedback'])) ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <span class="badge badge-<?= $req['status'] === 'approved' ? 'success' : ($req['status'] === 'pending' ? 'secondary' : 'warning') ?>" style="margin-left: 12px;">
                                        <?= $req['status'] === 'approved' ? '✅ Aprovado' : ($req['status'] === 'pending' ? '⏳ Pendente' : '🔄 Submetido') ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Activity Logs -->
        <div class="card" style="margin-top: 20px;">
            <div class="card-header">
                <h3>Histórico de Atividades</h3>
            </div>
            <div class="card-body">
                <?php if (empty($logs)): ?>
                    <p style="text-align: center; color: #94a3b8; padding: 40px;">Nenhuma atividade registrada ainda.</p>
                <?php else: ?>
                    <div style="position: relative;">
                        <!-- Timeline Line -->
                        <div style="position: absolute; left: 20px; top: 0; bottom: 0; width: 2px; background: #e2e8f0;"></div>
                        
                        <?php foreach ($logs as $log): ?>
                            <div style="position: relative; padding-left: 50px; margin-bottom: 20px;">
                                <!-- Timeline Dot -->
                                <div style="position: absolute; left: 12px; width: 16px; height: 16px; border-radius: 50%; background: <?= $log['status'] === 'approved' ? '#4ade80' : '#6366f1' ?>; border: 3px solid white; box-shadow: 0 0 0 2px #e2e8f0;"></div>
                                
                                <div>
                                    <div style="font-weight: 700; color: #1e293b; margin-bottom: 4px;">
                                        <?= htmlspecialchars($log['requirement_title'] ?? 'Atualização') ?>
                                    </div>
                                    <div style="font-size: 0.85rem; color: #64748b; margin-bottom: 8px;">
                                        <?= date('d/m/Y H:i', strtotime($log['updated_at'])) ?>
                                    </div>
                                    <?php if (!empty($log['answer'])): ?>
                                        <div style="font-size: 0.9rem; color: #475569; background: #f8fafc; padding: 10px; border-radius: 8px;">
                                            <?= nl2br(htmlspecialchars($log['answer'])) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
    .badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 700;
        white-space: nowrap;
    }
    .badge-success { background: #dcfce7; color: #166534; }
    .badge-warning { background: #fef3c7; color: #92400e; }
    .badge-secondary { background: #f1f5f9; color: #475569; }
</style>
