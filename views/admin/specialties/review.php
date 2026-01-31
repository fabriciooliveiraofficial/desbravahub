<?php
/**
 * Admin: Review Assignment Progress with Requirements
 */
$pageTitle = 'Revisar Atribuição';
$specialty = $assignment['specialty'];
$requirements = $specialty['requirements'] ?? [];
$progress = $requirementsProgress ?? [];

// Calculate stats
$totalReqs = count($requirements);
$completedReqs = 0;
$pendingReview = 0;
foreach ($progress as $p) {
    if ($p['status'] === 'approved')
        $completedReqs++;
    if ($p['status'] === 'submitted')
        $pendingReview++;
}
$progressPercent = $totalReqs > 0 ? round(($completedReqs / $totalReqs) * 100) : 0;
?>
<style>
    .review-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 0 10px;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--text-secondary);
        text-decoration: none;
        margin-bottom: 20px;
        font-size: 0.9rem;
    }

    .back-link:hover {
        color: var(--accent-cyan);
    }

    .assignment-header {
        background: var(--bg-card);
        border: 1px solid var(--border-light);
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 20px;
        display: flex;
        gap: 14px;
        flex-wrap: wrap;
    }

    .specialty-icon {
        font-size: 2.5rem;
        flex-shrink: 0;
    }

    .header-info {
        flex: 1;
        min-width: 200px;
    }

    .header-info h1 {
        margin: 0 0 6px;
        font-size: 1.2rem;
        line-height: 1.3;
    }

    .header-meta {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        color: var(--text-secondary);
        font-size: 0.85rem;
    }

    .user-info {
        background: var(--bg-card);
        border: 1px solid var(--border-light);
        border-radius: 10px;
        padding: 14px 16px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
    }

    .user-details h3 {
        margin: 0 0 4px;
        font-size: 1rem;
    }

    .user-details span {
        color: var(--text-secondary);
        font-size: 0.85rem;
    }

    .progress-section {
        background: var(--bg-card);
        border: 1px solid var(--border-light);
        border-radius: 10px;
        padding: 16px;
        margin-bottom: 20px;
    }

    .progress-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        font-size: 0.9rem;
    }

    .progress-bar {
        height: 8px;
        background: rgba(0, 0, 0, 0.3);
        border-radius: 4px;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--accent-cyan), var(--accent-green));
        border-radius: 4px;
    }

    .stats-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        margin-top: 14px;
    }

    .stat-box {
        padding: 10px;
        background: rgba(0, 0, 0, 0.2);
        border-radius: 8px;
        text-align: center;
    }

    .stat-value {
        font-size: 1.3rem;
        font-weight: 700;
    }

    .stat-label {
        font-size: 0.75rem;
        color: var(--text-secondary);
    }

    .requirements-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .requirement-card {
        background: var(--bg-card);
        border: 1px solid var(--border-light);
        border-radius: 10px;
        padding: 14px;
        border-left: 4px solid var(--border-light);
    }

    .requirement-card.pending {
        border-left-color: #ff9800;
    }

    .requirement-card.submitted {
        border-left-color: #9c27b0;
        background: rgba(156, 39, 176, 0.05);
    }

    .requirement-card.approved {
        border-left-color: var(--accent-green);
        background: rgba(0, 255, 136, 0.05);
    }

    .requirement-card.rejected {
        border-left-color: #f44336;
    }

    .requirement-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 10px;
        flex-wrap: wrap;
    }

    .requirement-number {
        font-weight: 700;
        color: var(--accent-cyan);
        margin-right: 6px;
    }

    .requirement-text {
        flex: 1;
        min-width: 200px;
        line-height: 1.4;
        font-size: 0.9rem;
    }

    .status-badge {
        padding: 3px 10px;
        border-radius: 16px;
        font-size: 0.75rem;
        font-weight: 500;
        white-space: nowrap;
    }

    .status-badge.pending {
        background: rgba(255, 152, 0, 0.2);
        color: #ff9800;
    }

    .status-badge.submitted {
        background: rgba(156, 39, 176, 0.2);
        color: #9c27b0;
    }

    .status-badge.approved {
        background: rgba(0, 255, 136, 0.2);
        color: var(--accent-green);
    }

    .status-badge.rejected {
        background: rgba(244, 67, 54, 0.2);
        color: #f44336;
    }

    .proof-section {
        background: rgba(0, 0, 0, 0.2);
        border-radius: 8px;
        padding: 12px;
        margin-top: 10px;
    }

    .proof-section h4 {
        margin: 0 0 10px;
        font-size: 0.85rem;
        color: var(--text-secondary);
    }

    .proof-content {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
        flex-wrap: wrap;
        word-break: break-all;
    }

    .proof-content a {
        color: var(--accent-cyan);
        font-size: 0.85rem;
    }

    .review-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .btn-approve {
        padding: 8px 16px;
        background: var(--accent-green);
        border: none;
        border-radius: 6px;
        color: var(--bg-primary);
        font-weight: 600;
        cursor: pointer;
        font-size: 0.85rem;
    }

    .btn-reject {
        padding: 8px 16px;
        background: transparent;
        border: 2px solid #f44336;
        border-radius: 6px;
        color: #f44336;
        font-weight: 600;
        cursor: pointer;
        font-size: 0.85rem;
    }

    .feedback-input {
        width: 100%;
        padding: 10px;
        background: rgba(0, 0, 0, 0.3);
        border: 1px solid var(--border-light);
        border-radius: 6px;
        color: var(--text-primary);
        margin-top: 10px;
        display: none;
        font-size: 0.9rem;
    }

    .feedback-input.active {
        display: block;
    }

    .complete-btn {
        display: block;
        width: 100%;
        padding: 14px;
        background: linear-gradient(135deg, var(--accent-cyan), var(--accent-green));
        border: none;
        border-radius: 10px;
        color: var(--bg-primary);
        font-weight: 700;
        font-size: 1rem;
        cursor: pointer;
        margin-top: 20px;
    }

    .complete-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .toast {
        position: fixed;
        bottom: 20px;
        right: 20px;
        padding: 14px 20px;
        background: var(--accent-green);
        color: var(--bg-primary);
        border-radius: 8px;
        font-weight: 600;
        display: none;
        z-index: 1000;
        max-width: 90%;
    }

    .toast.show {
        display: block;
        animation: slideIn 0.3s ease;
    }

    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }

        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    /* Responsive */
    @media (max-width: 600px) {
        .admin-main {
            padding: 16px 10px;
        }

        .review-container {
            padding: 0;
        }

        .assignment-header {
            padding: 14px;
        }

        .specialty-icon {
            font-size: 2rem;
        }

        .header-info h1 {
            font-size: 1.1rem;
        }

        .stats-row {
            gap: 8px;
        }

        .stat-value {
            font-size: 1.1rem;
        }

        .requirement-card {
            padding: 12px;
        }

        .review-actions {
            flex-direction: column;
        }

        .btn-approve,
        .btn-reject {
            width: 100%;
            text-align: center;
        }
    }
</style>

    <!-- Content -->
    <div class="review-container">
        <a href="<?= base_url($tenant['slug'] . '/admin/especialidades/atribuicoes') ?>" class="back-link">
            ← Voltar às Atribuições
        </a>

        <!-- Assignment Header -->
        <div class="assignment-header"
            style="border-left: 4px solid <?= $specialty['category']['color'] ?? '#00d9ff' ?>;">
            <span class="specialty-icon"><?= $specialty['badge_icon'] ?? '📘' ?></span>
            <div class="header-info">
                <h1><?= htmlspecialchars($specialty['name']) ?></h1>
                <div class="header-meta">
                    <span><?= $specialty['category']['icon'] ?? '' ?>
                        <?= htmlspecialchars($specialty['category']['name'] ?? '') ?></span>
                    <span>⏱️ <?= $specialty['duration_hours'] ?? '?' ?>h</span>
                    <span>🌟 <?= $specialty['xp_reward'] ?? 0 ?> XP</span>
                </div>
            </div>
        </div>

        <!-- User Info -->
        <div class="user-info">
            <div class="user-details">
                <h3>👤 <?= htmlspecialchars($pathfinder['name']) ?></h3>
                <span><?= htmlspecialchars($pathfinder['email']) ?></span>
            </div>
            <div>
                <span class="status-badge <?= $assignment['status'] ?>">
                    <?php
                    $statusLabels = [
                        'pending' => 'Pendente',
                        'in_progress' => 'Em Andamento',
                        'pending_review' => 'Aguardando Avaliação',
                        'completed' => 'Concluída'
                    ];
                    echo $statusLabels[$assignment['status']] ?? $assignment['status'];
                    ?>
                </span>
            </div>
        </div>

        <!-- Progress -->
        <div class="progress-section">
            <div class="progress-header">
                <strong>Progresso Geral</strong>
                <span><?= $progressPercent ?>%</span>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?= $progressPercent ?>%;"></div>
            </div>
            <div class="stats-row">
                <div class="stat-box">
                    <div class="stat-value" style="color: var(--accent-green);"><?= $completedReqs ?></div>
                    <div class="stat-label">Aprovados</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value" style="color: #9c27b0;"><?= $pendingReview ?></div>
                    <div class="stat-label">Aguardando</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value"><?= $totalReqs ?></div>
                    <div class="stat-label">Total</div>
                </div>
            </div>
        </div>

        <!-- Requirements List -->
        <h2 style="margin-bottom: 16px;">📝 Requisitos</h2>
        <div class="requirements-list">
            <?php foreach ($requirements as $i => $req): ?>
                <?php
                $reqProgress = $progress[$req['id']] ?? ['status' => 'pending', 'id' => null];
                $status = $reqProgress['status'];
                $hasProof = $status === 'submitted' || $status === 'rejected';
                ?>
                <div class="requirement-card <?= $status ?>" data-req-id="<?= $reqProgress['id'] ?? 0 ?>">
                    <div class="requirement-header">
                        <div class="requirement-text">
                            <span class="requirement-number"><?= $i + 1 ?>.</span>
                            <?= htmlspecialchars($req['description']) ?>
                        </div>
                        <span class="status-badge <?= $status ?>">
                            <?php
                            $statusLabels = [
                                'pending' => 'Pendente',
                                'submitted' => '⏳ Aguardando',
                                'approved' => '✓ Aprovado',
                                'rejected' => 'Rejeitado'
                            ];
                            echo $statusLabels[$status] ?? $status;
                            ?>
                        </span>
                    </div>

                    <?php if ($hasProof && !empty($reqProgress['proof_content'])): ?>
                        <div class="proof-section">
                            <h4>📎 Prova Enviada</h4>
                            <div class="proof-content">
                                <?php if ($reqProgress['proof_type'] === 'url'): ?>
                                    🔗 <a href="<?= htmlspecialchars($reqProgress['proof_content']) ?>" target="_blank">
                                        <?= htmlspecialchars($reqProgress['proof_content']) ?>
                                    </a>
                                <?php else: ?>
                                    📁 <a href="<?= htmlspecialchars($reqProgress['proof_content']) ?>" target="_blank">
                                        Ver arquivo
                                    </a>
                                <?php endif; ?>
                            </div>

                            <?php if ($status === 'submitted'): ?>
                                <div class="review-actions">
                                    <button class="btn-approve" onclick="approveRequirement(<?= $reqProgress['id'] ?>)">
                                        ✓ Aprovar
                                    </button>
                                    <button class="btn-reject" onclick="showRejectFeedback(<?= $reqProgress['id'] ?>)">
                                        ✗ Rejeitar
                                    </button>
                                </div>
                                <input type="text" class="feedback-input" id="feedback-<?= $reqProgress['id'] ?>"
                                    placeholder="Motivo da rejeição..."
                                    onkeypress="if(event.key==='Enter') rejectRequirement(<?= $reqProgress['id'] ?>)">
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($status === 'approved' && !empty($reqProgress['reviewed_at'])): ?>
                        <small style="color: var(--text-secondary); margin-top: 8px; display: block;">
                            ✓ Aprovado em <?= date('d/m/Y H:i', strtotime($reqProgress['reviewed_at'])) ?>
                        </small>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($completedReqs === $totalReqs && $totalReqs > 0 && $assignment['status'] !== 'completed'): ?>
            <button class="complete-btn" onclick="completeAssignment()">
                🎉 Concluir Especialidade e Atribuir XP
            </button>
        <?php endif; ?>
    </div>