<?php
/**
 * Admin: Review Assignment Progress with Requirements
 */
use App\Services\SpecialtyService;

$pageTitle = 'Revisar Missão';
$pageIcon = 'assignment_ind';

// Load requirements with proper unified progress data
$requirements = SpecialtyService::getRequirementsWithProgress((int)$assignment['id'], $assignment['specialty_id']);
$specialty = $assignment['specialty'];
$pathfinder = $pathfinder ?? ['name' => 'Usuário', 'email' => ''];

// Calculate stats
$totalReqs = count($requirements);
$completedReqs = 0;
$pendingReview = 0;
foreach ($requirements as $req) {
    if (($req['status'] ?? '') === 'approved')
        $completedReqs++;
    if (($req['status'] ?? '') === 'answered' || ($req['status'] ?? '') === 'submitted')
        $pendingReview++;
}
$progressPercent = $totalReqs > 0 ? round(($completedReqs / $totalReqs) * 100) : 0;
?>

<style>
    /* ============ Page Hero & Back Link ============ */
    .page-hero {
        margin-bottom: 24px;
    }
    
    @media (max-width: 768px) {
        .page-hero { padding-top: 60px; }
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: var(--text-muted);
        text-decoration: none;
        font-size: 0.875rem;
        font-weight: 500;
        margin-bottom: 16px;
        transition: all 0.2s;
    }

    .back-link:hover {
        color: var(--primary);
        transform: translateX(-4px);
    }

    /* ============ Assignment Summary Card ============ */
    .summary-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 24px;
        position: relative;
        overflow: hidden;
    }

    .spec-badge-large {
        font-size: 3.5rem;
        flex-shrink: 0;
        filter: drop-shadow(0 4px 12px rgba(0,0,0,0.1));
    }

    .summary-info h1 {
        font-size: 1.5rem;
        font-weight: 800;
        margin: 0 0 8px 0;
        color: var(--text-main);
    }

    .user-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 12px;
        background: var(--bg-dark);
        border-radius: 50px;
        border: 1px solid var(--border-color);
        font-size: 0.85rem;
        color: var(--text-secondary);
        font-weight: 600;
    }

    /* ============ Progress Section ============ */
    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 16px;
        text-align: center;
    }

    .stat-card .val {
        display: block;
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--text-main);
    }

    .stat-card .label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-muted);
        font-weight: 600;
    }

    /* ============ Requirements List ============ */
    .section-title {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 32px 0 16px;
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-main);
    }

    .req-grid {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .req-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 20px;
        transition: all 0.2s;
        border-left: 4px solid var(--border-color);
    }

    .req-card:hover {
        border-color: var(--primary);
        box-shadow: var(--shadow-sm);
    }

    .req-card.approved { border-left-color: var(--accent-emerald); background: rgba(16, 185, 129, 0.02); }
    .req-card.answered { border-left-color: var(--primary); background: rgba(6, 182, 212, 0.02); }
    .req-card.rejected { border-left-color: #ef4444; }

    .req-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 12px;
    }

    .req-body {
        font-size: 0.9375rem;
        line-height: 1.6;
        color: var(--text-main);
        flex: 1;
    }

    .req-num {
        font-weight: 800;
        color: var(--primary);
        margin-right: 8px;
    }

    /* ============ Proof Section ============ */
    .proof-box {
        margin-top: 16px;
        padding: 16px;
        background: var(--bg-dark);
        border-radius: 8px;
        border: 1px solid var(--border-color);
    }

    .proof-label {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--text-muted);
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* ============ Actions ============ */
    .action-btns {
        display: flex;
        gap: 8px;
        margin-top: 16px;
    }

    .btn-action {
        flex: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.875rem;
        cursor: pointer;
        transition: all 0.2s;
        border: 1px solid transparent;
    }

    .btn-approve { background: var(--accent-emerald); color: white; }
    .btn-approve:hover { background: #059669; }

    .btn-reject { background: transparent; border-color: #ef4444; color: #ef4444; }
    .btn-reject:hover { background: #ef4444; color: white; }

    .finish-section {
        margin-top: 40px;
        padding: 24px;
        background: var(--bg-sidebar);
        border-radius: 16px;
        border: 1px solid var(--border-color);
        text-align: center;
    }

    .btn-complete {
        width: 100%;
        max-width: 400px;
        padding: 16px;
        background: linear-gradient(135deg, var(--primary), var(--primary-hover));
        color: white;
        border: none;
        border-radius: 12px;
        font-weight: 800;
        font-size: 1.1rem;
        cursor: pointer;
        box-shadow: var(--shadow-cyan);
        transition: all 0.2s;
    }

    .btn-complete:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(6, 182, 212, 0.4);
    }
</style>

<div class="page-hero">
    <a href="<?= base_url($tenant['slug'] . '/admin/especialidades/atribuicoes') ?>" class="back-link">
        <span class="material-icons-round" style="font-size: 18px;">chevron_left</span>
        Voltar para Atribuições
    </a>

    <div class="summary-card" style="border-left: 6px solid <?= $specialty['category']['color'] ?? 'var(--primary)' ?>;">
        <div class="spec-badge-large">
            <?php 
            $icon = $specialty['badge_icon'] ?? '🎖️';
            if (str_starts_with($icon, 'fa-')): ?>
                <i class="<?= htmlspecialchars($icon) ?>"></i>
            <?php elseif (str_contains($icon, ':')): ?>
                <?php $iconName = explode(' ', $icon)[0]; ?>
                <iconify-icon icon="<?= htmlspecialchars($iconName) ?>"></iconify-icon>
            <?php else: ?>
                <?= $icon ?>
            <?php endif; ?>
        </div>
        <div class="summary-info">
            <h1><?= htmlspecialchars($specialty['name']) ?></h1>
            <div class="user-pill">
                <span class="material-icons-round" style="font-size: 16px;">person</span>
                <?= htmlspecialchars($pathfinder['name']) ?>
            </div>
        </div>
    </div>

    <div class="stats-container">
        <div class="stat-card">
            <span class="val"><?= $progressPercent ?>%</span>
            <span class="label">Concluído</span>
        </div>
        <div class="stat-card">
            <span class="val" style="color: var(--accent-emerald);"><?= $completedReqs ?></span>
            <span class="label">Aprovados</span>
        </div>
        <div class="stat-card">
            <span class="val" style="color: var(--primary);"><?= $pendingReview ?></span>
            <span class="label">Pendentes</span>
        </div>
    </div>

    <div class="section-title">
        <span class="material-icons-round">rule</span>
        Lista de Requisitos
    </div>

    <div class="req-grid">
        <?php foreach ($requirements as $i => $req): ?>
            <?php 
                $status = $req['status'] ?? 'pending';
                // Map 'answered' (from DB) to 'submitted' (UI label)
                $uiStatus = $status === 'answered' ? 'submitted' : $status;
                $hasStatus = $status !== 'pending';
            ?>
            <div class="req-card <?= $status ?>" id="req-<?= $req['progress_id'] ?? 'virtual-'.$i ?>">
                <div class="req-header">
                    <div class="req-body">
                        <span class="req-num"><?= $i + 1 ?>.</span>
                        <?= htmlspecialchars($req['description']) ?>
                    </div>
                    <?php if ($hasStatus): ?>
                        <div class="status-pill <?= $uiStatus ?>">
                           <?php
                                if($status === 'approved') echo '✨ Aprovado';
                                elseif($status === 'answered' || $status === 'submitted') echo '⏳ Revisar';
                                elseif($status === 'rejected') echo '❌ Rejeitado';
                           ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($req['answer']) || !empty($req['file_path'])): ?>
                    <div class="proof-box">
                        <div class="proof-label">
                            <span class="material-icons-round" style="font-size: 14px;">attachment</span>
                            Evidência enviada
                        </div>
                        
                        <?php if (!empty($req['answer'])): ?>
                            <div style="margin-bottom: 12px; color: var(--text-main); font-size: 0.9rem;">
                                <?= nl2br(htmlspecialchars($req['answer'])) ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($req['file_path'])): ?>
                            <?php 
                                $fullUrl = base_url($req['file_path']);
                                $ext = strtolower(pathinfo($req['file_path'], PATHINFO_EXTENSION));
                                $isImg = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
                            ?>
                            <?php if ($isImg): ?>
                                <img src="<?= $fullUrl ?>" style="max-width: 100%; border-radius: 8px; border: 1px solid var(--border-color); cursor: pointer;" onclick="window.open('<?= $fullUrl ?>', '_blank')">
                            <?php else: ?>
                                <a href="<?= $fullUrl ?>" target="_blank" class="user-pill" style="text-decoration: none; color: var(--primary);">
                                    <span class="material-icons-round">description</span>
                                    Ver arquivo anexo
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if ($status === 'answered' || $status === 'submitted'): ?>
                            <div class="action-btns">
                                <button class="btn-action btn-approve" onclick="approveRequirement(<?= $req['progress_id'] ?>)">
                                    <span class="material-icons-round">check_circle</span>
                                    Aprovar
                                </button>
                                <button class="btn-action btn-reject" onclick="showRejectModal(<?= $req['progress_id'] ?>)">
                                    <span class="material-icons-round">cancel</span>
                                    Rejeitar
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($completedReqs === $totalReqs && $totalReqs > 0 && $assignment['status'] !== 'completed'): ?>
        <div class="finish-section">
            <h3 style="margin-bottom: 16px;">Missão Cumprida!</h3>
            <p style="color: var(--text-muted); margin-bottom: 24px;">Todos os requisitos foram aprovados. Você já pode oficializar a conclusão desta especialidade.</p>
            <button class="btn-complete" onclick="completeAssignment(<?= $assignment['id'] ?>)">
                CONCLUIR E ATRIBUIR XP
            </button>
        </div>
    <?php endif; ?>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    async function approveRequirement(progressId) {
        if (!progressId) return;

        try {
            const response = await fetch(`<?= base_url($tenant['slug'] . '/admin/especialidades/aprovar-requisito/') ?>${progressId}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            });

            const result = await response.json();
            if (result.success) {
                showToast('✨ Requisito aprovado com sucesso!', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(result.error || 'Erro ao aprovar requisito', 'error');
            }
        } catch (error) {
            showToast('Erro de conexão com o servidor', 'error');
        }
    }

    async function showRejectModal(progressId) {
        const { value: feedback } = await Swal.fire({
            title: 'Rejeitar Requisito',
            input: 'textarea',
            inputLabel: 'Feedback para o desbravador',
            inputPlaceholder: 'Diga por que o requisito foi rejeitado e o que precisa ser corrigido...',
            inputAttributes: { 'aria-label': 'Feedback' },
            showCancelButton: true,
            confirmButtonText: 'Confirmar Rejeição',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#ef4444',
            inputValidator: (value) => {
                if (!value) return 'Você precisa fornecer um feedback!';
            }
        });

        if (feedback) {
            rejectRequirement(progressId, feedback);
        }
    }

    async function rejectRequirement(progressId, feedback) {
        try {
            const response = await fetch(`<?= base_url($tenant['slug'] . '/admin/especialidades/rejeitar-requisito/') ?>${progressId}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ feedback })
            });

            const result = await response.json();
            if (result.success) {
                showToast('Requisito rejeitado', 'info');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(result.error || 'Erro ao rejeitar requisito', 'error');
            }
        } catch (error) {
            showToast('Erro de conexão com o servidor', 'error');
        }
    }

    async function completeAssignment(assignmentId) {
        const confirm = await Swal.fire({
            title: 'Concluir Especialidade?',
            text: 'Isso enviará o certificado digital e atribuirá o XP ao desbravador.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sim, concluir!',
            cancelButtonText: 'Agora não',
            confirmButtonColor: 'var(--primary)'
        });

        if (confirm.isConfirmed) {
            try {
                const response = await fetch(`<?= base_url($tenant['slug'] . '/admin/especialidades/concluir-atribuicao/') ?>${assignmentId}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' }
                });

                const result = await response.json();
                if (result.success) {
                    await Swal.fire('Parabéns!', `A especialidade foi concluída e o desbravador recebeu ${result.xp} XP.`, 'success');
                    const _url = '<?= base_url($tenant['slug'] . '/admin/especialidades/atribuicoes') ?>';
                    const _a = document.createElement('a');
                    _a.href = _url;
                    _a.style.display = 'none';
                    document.body.appendChild(_a);
                    _a.click();
                    requestAnimationFrame(() => { try { document.body.removeChild(_a); } catch(e) {} });
                } else {
                    showToast(result.error || 'Erro ao concluir missão', 'error');
                }
            } catch (error) {
                showToast('Erro de conexão com o servidor', 'error');
            }
        }
    }

    function showToast(message, type = 'success') {
        // Use standard platform toast if available, or fallback to SweetAlert
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
        Toast.fire({
            icon: type,
            title: message
        });
    }
</script>