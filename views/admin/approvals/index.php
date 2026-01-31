<?php
/**
 * Admin: Evaluation Center
 * 
 * High-end interface for evaluating step submissions.
 */
$totalPendingItems = array_sum(array_column($pendingQueue, 'pending_count'));
$uniqueUnits = count(array_unique(array_column($pendingQueue, 'unit_name')));
?>

<div class="evaluation-layout">
    <!-- Page Toolbar (Filters & Tabs) -->
    <div class="page-toolbar" style="margin-bottom: 32px; display: flex; justify-content: space-between; align-items: center; background: var(--bg-sidebar); border: 1px solid var(--border-color); border-radius: 12px; padding: 16px 24px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <p style="color: var(--text-secondary); font-size: 0.95rem; font-weight: 500; margin: 0;">
                <span class="material-icons-round" style="vertical-align: middle; font-size: 1.1rem; color: var(--primary);">info</span>
                Gestão tática de requisitos e validação de progresso.
            </p>
        </div>
        
        <div style="display: flex; gap: 8px;">
            <button class="btn-toolbar primary" onclick="showEvalTab('pending')" id="btn-tab-pending">
                <span class="material-icons-round">analytics</span>
                Fila Ativa
            </button>
            <button class="btn-toolbar secondary" onclick="showEvalTab('history')" id="btn-tab-history">
                <span class="material-icons-round">history</span>
                Histórico
            </button>
        </div>
    </div>

    <!-- Dynamic Stats Grid -->
    <section class="hero-section">
        <div class="stat-glass-card">
            <div class="stat-icon-box" style="color: #fbbf24;">
                <span class="material-icons-round">pending_actions</span>
            </div>
            <div>
                <div class="stat-value"><?= count($pendingQueue) ?></div>
                <div class="stat-label">Sessões Pendentes</div>
            </div>
        </div>
        
        <div class="stat-glass-card">
            <div class="stat-icon-box" style="color: var(--eval-cyan);">
                <span class="material-icons-round">rule</span>
            </div>
            <div>
                <div class="stat-value"><?= $totalPendingItems ?></div>
                <div class="stat-label">Itens Aguardando</div>
            </div>
        </div>

        <div class="stat-glass-card">
            <div class="stat-icon-box" style="color: var(--eval-purple);">
                <span class="material-icons-round">tour</span>
            </div>
            <div>
                <div class="stat-value"><?= $uniqueUnits ?></div>
                <div class="stat-label">Unidades em Fila</div>
            </div>
        </div>
    </section>

    <!-- Pending Queue Display -->
    <div id="pending-tab">
        <?php if (empty($pendingQueue)): ?>
            <div class="stat-glass-card" style="justify-content: center; padding: 100px 40px; flex-direction: column; text-align: center;">
                <span class="material-icons-round" style="font-size: 4rem; color: #10b981; opacity: 0.3; margin-bottom: 24px;">verified_user</span>
                <h3 style="margin: 0; font-size: 1.5rem;">Criptografia Silenciosa...</h3>
                <p style="color: var(--text-secondary); margin-top: 10px;">Todos os requisitos foram validados. Nenhum dado pendente.</p>
            </div>
        <?php else: ?>
            <div class="queue-grid">
                <?php foreach ($pendingQueue as $idx => $q): ?>
                    <div class="queue-card" style="animation: fadeInUp 0.4s ease-out forwards; animation-delay: <?= $idx * 0.05 ?>s; opacity: 0;">
                        <div class="student-profile" style="display: flex; align-items: center; gap: 16px;">
                            <div class="avatar-glow" style="width: 64px; height: 64px; border-radius: 20px; padding: 3px; background: linear-gradient(45deg, var(--eval-cyan), var(--eval-purple));">
                                <div class="avatar-inner" style="width: 100%; height: 100%; border-radius: 17px; background: var(--bg-card); display: flex; align-items: center; justify-content: center; overflow: hidden; font-weight: 800; font-family: 'Outfit';">
                                    <?php if ($q['avatar_url']): ?>
                                        <img src="<?= $q['avatar_url'] ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                    <?php else: ?>
                                        <?= strtoupper(substr($q['user_name'] ?? 'U', 0, 1)) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div style="flex: 1;">
                                <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin: 0; letter-spacing: -0.01em;">
                                    <?= htmlspecialchars($q['user_name']) ?>
                                </h3>
                                <div style="display: flex; align-items: center; gap: 6px; font-size: 0.8rem; color: var(--text-secondary); margin-top: 4px;">
                                    <span class="material-icons-round" style="font-size: 0.9rem;">hub</span>
                                    <?= htmlspecialchars($q['unit_name'] ?? 'Equipe Principal') ?>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 0.7rem; color: var(--text-secondary); font-weight: 700; text-transform: uppercase;">Último Sinal</div>
                                <div style="font-family: 'JetBrains Mono'; color: var(--eval-cyan); font-weight: 600;">
                                    <?= date('d/m H:i', strtotime($q['last_submission'])) ?>
                                </div>
                            </div>
                        </div>

                        <div class="program-focus-box">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(139, 92, 246, 0.08); color: var(--eval-purple); display: flex; align-items: center; justify-content: center;">
                                        <span class="material-icons-round" style="font-size: 1.2rem;"><?= $q['program_icon'] ?? 'bookmark' ?></span>
                                    </div>
                                    <div style="font-weight: 700; font-family: 'Outfit'; color: var(--text-main);"><?= htmlspecialchars($q['program_name']) ?></div>
                                </div>
                                <div class="pending-count-tag" style="background: rgba(139, 92, 246, 0.1); color: var(--eval-purple); padding: 4px 12px; border-radius: 20px; font-size: 0.7rem; font-weight: 800;"><?= $q['pending_count'] ?> pendentes</div>
                            </div>

                            <div style="margin-top: 16px;">
                                <div style="display: flex; justify-content: space-between; font-size: 0.7rem; font-weight: 700; color: var(--text-secondary); margin-bottom: 8px; text-transform: uppercase;">
                                    <span>Progresso Global</span>
                                    <span style="color: var(--eval-cyan)"><?= $q['progress_percent'] ?>%</span>
                                </div>
                                <div style="height: 8px; background: var(--bg-main); border-radius: 4px; overflow: hidden;">
                                    <div style="height: 100%; background: linear-gradient(to right, var(--eval-cyan), #3b82f6); width: <?= $q['progress_percent'] ?>%; border-radius: 4px; transition: width 1s ease-out;"></div>
                                </div>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 60px; gap: 12px;">
                            <a href="<?= base_url($tenant['slug'] . '/admin/aprovacoes/' . $q['progress_id'] . '/review') ?>" class="btn-eval-cyan">
                                <span class="material-icons-round" style="font-size: 1.2rem;">assignment_turned_in</span>
                                AVALIAR REQUISITOS
                            </a>
                            <button class="btn-check-all" onclick="bulkApproveProgram(<?= $q['progress_id'] ?>)" 
                                    title="Aprovar todos os requisitos deste programa de uma vez"
                                    style="background: var(--bg-main); border: 1px solid var(--border-color); color: var(--text-secondary); border-radius: 14px; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; justify-content: center;">
                                <span class="material-icons-round">done_all</span>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- History Display -->
    <div id="history-tab" style="display: none;">
        <div class="stat-glass-card" style="display: block; padding: 0; overflow: hidden;">
            <?php if (empty($recentApprovals)): ?>
                <div style="padding: 60px; text-align: center; color: var(--text-secondary);">Nenhum log de atividade detectado.</div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th>Evento</th>
                                <th>Sujeito</th>
                                <th>Validador</th>
                                <th>Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentApprovals as $log): ?>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 12px;">
                                            <div style="width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; background: <?= $log['action'] === 'approved' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)' ?>; color: <?= $log['action'] === 'approved' ? '#10b981' : '#f87171' ?>;">
                                                <span class="material-icons-round" style="font-size: 1.1rem;"><?= $log['action'] === 'approved' ? 'check_circle' : 'cancel' ?></span>
                                            </div>
                                            <span style="font-weight: 600; font-size: 0.9rem; color: var(--text-main);"><?= $log['action'] === 'approved' ? 'APROVADO' : 'REJEITADO' ?></span>
                                        </div>
                                    </td>
                                    <td style="font-weight: 500; color: var(--text-main);"><?= htmlspecialchars($log['user_name'] ?? 'N/A') ?></td>
                                    <td style="color: var(--text-secondary);"><?= htmlspecialchars($log['reviewer_name'] ?? 'Auto-System') ?></td>
                                    <td style="font-family: 'JetBrains Mono'; font-size: 0.85rem; color: var(--eval-cyan);">
                                        <?= date('d/m/Y H:i', strtotime($log['created_at'])) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="toast modern-toast" id="eval-toast" style="position: fixed; bottom: 30px; right: 30px; z-index: 999999; display: none; background: white; padding: 16px 24px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); font-weight: 600;"></div>

<script>
    function showEvalTab(tab) {
        document.getElementById('pending-tab').style.display = tab === 'pending' ? 'block' : 'none';
        document.getElementById('history-tab').style.display = tab === 'history' ? 'block' : 'none';
        
        const btnPending = document.getElementById('btn-tab-pending');
        const btnHistory = document.getElementById('btn-tab-history');

        if (tab === 'pending') {
            btnPending.classList.replace('secondary', 'primary');
            btnHistory.classList.replace('primary', 'secondary');
        } else {
            btnHistory.classList.replace('secondary', 'primary');
            btnPending.classList.replace('primary', 'secondary');
        }
    }

    async function bulkApproveProgram(id) {
        const confirmed = await sconfirm('Deseja validar todos os requisitos pendentes nesta sessão?', 'Aprovação em Massa');
        if (!confirmed) return;
        try {
            const resp = await fetch(`/<?= $tenant['slug'] ?>/admin/aprovacoes/${id}/bulk-approve-program`, { method: 'POST' });
            const data = await resp.json();
            if (data.success) { 
                await swal(data.message, 'Sucesso');
                location.reload(); 
            }
            else { 
                swal(data.error || 'Erro operacional', 'Houve um problema');
            }
        } catch (err) { 
            console.error(err);
            swal('Falha na conexão', 'Erro de Conexão'); 
        }
    }

    function showEvalToast(msg, type = 'success') {
        const toast = document.getElementById('eval-toast');
        if (!toast) return;
        toast.textContent = msg;
        toast.style.display = 'block';
        toast.style.borderLeft = `4px solid ${type === 'error' ? '#f87171' : 'var(--eval-cyan)'}`;
        toast.style.color = 'var(--text-main)'; // Ensure visibility in dark mode since inline styles were removed
        toast.style.background = 'var(--bg-card)';
        setTimeout(() => toast.style.display = 'none', 4000);
    }
</script>