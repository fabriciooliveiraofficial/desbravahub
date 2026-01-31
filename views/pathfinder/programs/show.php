<?php
/**
 * Pathfinder: Program Detail with Steps
 * DESIGN: Deep Glass HUD v3.0
 */
?>
<div class="hud-wrapper">
    <!-- Header/Title -->
    <header class="hud-header">
        <div style="display: flex; align-items: center; gap: 16px;">
            <a href="<?= base_url($tenant['slug'] . '/aprendizado') ?>" class="hud-action-btn" style="position: static; transform: none; width: 40px; height: 40px;">
                <span class="material-icons-round">arrow_back</span>
            </a>
            <div>
                <h1 class="hud-title" style="font-size: 1.5rem; line-height: 1.2; margin-bottom: 4px;"><?= htmlspecialchars($program['name']) ?></h1>
                <div class="hud-subtitle" style="font-size: 0.9rem;">
                    <?= $program['type'] === 'class' ? 'Classe Regular' : 'Especialidade / Programa' ?>
                    <?php if ($program['category_name']): ?>
                        • <?= htmlspecialchars($program['category_name']) ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Program Stats & Progress -->
    <div class="hud-stats">
        <?php
        $totalSteps = count($steps);
        $completedSteps = 0;
        $answeredSteps = 0;
        foreach ($steps as $s) {
            $st = $s['response']['status'] ?? 'pending';
            if ($st === 'approved') $completedSteps++;
            if ($st !== 'pending' && $st !== 'not_started') $answeredSteps++;
        }
        $progPercent = $totalSteps > 0 ? round(($completedSteps / $totalSteps) * 100) : 0;
        $engPercent = $totalSteps > 0 ? round(($answeredSteps / $totalSteps) * 100) : 0;
        ?>
        <div class="hud-stat-card primary">
            <div class="plate-header">
                <div>
                    <div class="hud-stat-value" style="font-size: 1.5rem;"><?= $engPercent ?>%</div>
                    <div class="hud-stat-label">Progresso</div>
                </div>
                <i class="material-icons-round hud-stat-icon" style="color: var(--accent-cyan)">pie_chart</i>
            </div>
            <div class="hud-progress" style="background: rgba(255,255,255,0.05); position: relative;">
                <!-- Answered Bar (Engagement) -->
                <div class="hud-progress-bar" style="width: <?= $engPercent ?>%; background: var(--accent-cyan); opacity: 0.3; position: absolute;"></div>
                <!-- Approved Bar (Real Progress) -->
                <div class="hud-progress-bar" style="width: <?= $progPercent ?>%; background: var(--accent-cyan); position: relative;"></div>
            </div>
            <div style="display: flex; justify-content: space-between; margin-top: 8px; font-size: 0.75rem; color: var(--hud-text-dim);">
                <span>CONCLUÍDO (APROVADO)</span>
                <span><?= $progPercent ?>%</span>
            </div>
        </div>

        <div class="hud-stat-card">
            <div>
                <div class="hud-stat-value" style="font-size: 1.5rem;"><?= count($steps) ?></div>
                <div class="hud-stat-label">Requisitos</div>
            </div>
            <i class="material-icons-round hud-stat-icon" style="color: var(--accent-purple)">list</i>
        </div>

        <div class="hud-stat-card">
            <div>
                <div class="hud-stat-value" style="font-size: 1.5rem;">+<?= number_format($program['xp_reward']) ?></div>
                <div class="hud-stat-label">XP Recompensa</div>
            </div>
            <i class="material-icons-round hud-stat-icon" style="color: var(--accent-green)">bolt</i>
        </div>
    </div>
    
    <!-- Finalize Button (If 100% engaged but not submitted) -->
    <?php if ($engPercent >= 100 && $program['user_status'] === 'in_progress'): ?>
        <div class="hud-stat-card" style="margin-top: 16px; grid-column: 1 / -1; background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(34, 211, 238, 0.1)); border: 1px solid rgba(34, 211, 238, 0.3); flex-direction: row; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 16px;">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(34, 211, 238, 0.1); display: flex; align-items: center; justify-content: center;">
                    <i class="material-icons-round" style="color: var(--accent-cyan); font-size: 1.8rem;">task_alt</i>
                </div>
                <div>
                    <div class="hud-stat-value" style="font-size: 1.1rem; color: #fff;">Missão Completa!</div>
                    <div class="hud-stat-label" style="font-size: 0.75rem;">Você respondeu todos os requisitos.</div>
                </div>
            </div>
            <button class="action-button-hud program-submit-btn" data-program-id="<?= $program['id'] ?>" style="padding: 10px 20px; font-size: 0.75rem;">
                <span class="material-icons-round" style="font-size: 1rem;">send</span> ENVIAR PARA AVALIAÇÃO
            </button>
        </div>
    <?php endif; ?>
<?php 
// Fallback if status is already submitted but progress is 0% in DB
// (This covers the user case where they submitted but review hasn't started)
?>

    <!-- Steps List -->
    <section class="hud-section" style="margin-top: 32px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 20px;">
            <h2 class="hud-section-title" style="margin: 0; font-size: 0.95rem; letter-spacing: 0.1em; opacity: 0.9;">ROTEIRO DE ATIVIDADES</h2>
            <div style="font-size: 0.7rem; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.05em;">
                Sequência Operacional
            </div>
        </div>
        
        <div class="hud-grid" style="grid-template-columns: 1fr; gap: 16px;">
            <?php foreach ($steps as $index => $step):
                $response = $step['response'];
                $status = $response['status'] ?? 'pending';
                
                // Detailed Status Config
                $config = match ($status) {
                    'submitted' => ['color' => '#f59e0b', 'icon' => 'hourglass_top', 'label' => 'Em Análise', 'bg' => 'rgba(245, 158, 11, 0.1)'],
                    'approved' => ['color' => '#10b981', 'icon' => 'check_circle', 'label' => 'Aprovado', 'bg' => 'rgba(16, 185, 129, 0.1)'],
                    'rejected' => ['color' => '#ef4444', 'icon' => 'error', 'label' => 'Revisar', 'bg' => 'rgba(239, 68, 68, 0.1)'],
                    default => ['color' => 'var(--text-secondary)', 'icon' => 'radio_button_unchecked', 'label' => 'Pendente', 'bg' => 'rgba(255, 255, 255, 0.03)']
                };

                $isLocked = false; // Implement logic later if sequential unlocking exists
                ?>
                <div class="tech-plate learning-card" onclick="openStepModal(<?= $step['id'] ?>)" 
                     style="
                        cursor: pointer; position: relative; overflow: hidden; 
                        border-left: 4px solid <?= $config['color'] === 'var(--text-secondary)' ? 'transparent' : $config['color'] ?>;
                        padding: 0; min-height: 100px; display: flex;
                        background: linear-gradient(145deg, rgba(20, 20, 30, 0.6), rgba(20, 20, 30, 0.8)) !important;
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
                                <?= str_pad($index + 1, 2, '0', STR_PAD_LEFT) ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Content Column -->
                    <div class="plate-content" style="flex: 1; padding: 20px 0 20px 0; display: flex; flex-direction: column; gap: 10px;">
                        
                        <!-- Top Meta: XP & Status (Badge Row) -->
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span class="hud-badge" style="
                                font-size: 0.7rem; padding: 2px 8px; border-radius: 6px; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700;
                                background: rgba(255,255,255,0.03); color: var(--text-secondary); border: 1px solid rgba(255,255,255,0.05);
                            ">
                                <?= $step['points'] ?> XP
                            </span>
                            <?php if ($step['is_required']): ?>
                                <span class="hud-badge" style="
                                    font-size: 0.7rem; padding: 2px 8px; border-radius: 6px; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700;
                                    color: var(--accent-cyan); background: rgba(6, 182, 212, 0.08); border: 1px solid rgba(6, 182, 212, 0.15);
                                ">
                                    Obrigatório
                                </span>
                            <?php endif; ?>
                            
                            <!-- Detailed Status -->
                            <?php if ($status !== 'not_started'): ?>
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
                        "><?= htmlspecialchars(mb_strimwidth($step['title'], 0, 60, "...")) ?></h3>
                        
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
        </div>
    </section>

    <style>
        .learning-card {
            background: linear-gradient(145deg, rgba(30, 30, 46, 0.8), rgba(20, 20, 35, 0.9)) !important;
            backdrop-filter: blur(10px);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 24px !important; /* Design System: Rounded Cards */
            overflow: hidden; /* Ensure content obeys radius */
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

<!-- Step Modal (Reused Logic) -->
<div class="modal-overlay" id="stepModal" onclick="closeModal(event)" style="z-index: 10000;">
    <div class="modal hud-modal" onclick="event.stopPropagation()" style="background: #1e1e2d; border: 1px solid rgba(255,255,255,0.1); max-width: 600px; border-radius: 28px; overflow: hidden; display: flex; flex-direction: column; max-height: 90vh;">
        <div class="modal-header" style="background: rgba(255,255,255,0.02); border-bottom: 1px solid rgba(255,255,255,0.05); padding: 24px 24px 20px 24px; flex-shrink: 0;">
            <h2 id="modalTitle" style="color: #f1f5f9; font-size: 1.1rem; line-height: 1.5; font-weight: 600; margin: 0; padding-right: 24px;">Requisito</h2>
            <button class="modal-close" onclick="closeModal()" style="color: #94a3b8; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 50%; background: rgba(255,255,255,0.05); border: none; cursor: pointer; transition: all 0.2s;">×</button>
        </div>
        <div class="modal-body" id="modalBody" style="color: #ccc; padding: 0; overflow-y: auto;">
            <div class="modal-loading" style="padding: 40px; text-align: center;">⏳ Carregando módulos...</div>
        </div>
    </div>
</div>

<style>
/* HUD specific modal overrides */
.hud-modal {
    box-shadow: 0 40px 80px -20px rgba(0, 0, 0, 0.6);
}
</style>

<script>
    if (typeof tenantSlug === 'undefined') {
        var tenantSlug = '<?= $tenant['slug'] ?>';
    }

    async function openStepModal(stepId) {
        document.getElementById('stepModal').classList.add('active');
        document.getElementById('modalBody').innerHTML = '<div class="modal-loading">⏳ Acessando banco de dados...</div>';

        try {
            const resp = await fetch(`/${tenantSlug}/aprendizado/step/${stepId}/modal`);
            const data = await resp.json();

            if (data.success) {
                document.getElementById('modalTitle').textContent = data.step.title;
                document.getElementById('modalBody').innerHTML = data.html;
            } else {
                document.getElementById('modalBody').innerHTML = '<p style="color: var(--accent-red);">Erro: ' + (data.error || 'Falha ao carregar') + '</p>';
            }
        } catch (err) {
            document.getElementById('modalBody').innerHTML = '<p style="color: var(--accent-red);">Erro de conexão com o servidor.</p>';
        }
    }

    function closeModal(e) {
        if (e && e.target !== e.currentTarget) return;
        const modal = document.getElementById('stepModal');
        modal.classList.remove('active');
        // Small delay to let transition finish before clearing content
        setTimeout(() => {
            if (!modal.classList.contains('active')) {
                document.getElementById('modalBody').innerHTML = '';
            }
        }, 300);
    }

    async function submitStepForm(stepId, form, status = 'submitted') {
        const formData = new FormData(form);
        formData.append('status', status);

        const btn = (event && event.submitter) || (event && event.target ? event.target.closest('button') : null) || form.querySelector('.btn-submit');
        if (!btn) {
            console.error('Submit button not found');
            return;
        }
        const originalText = btn.innerHTML;
        
        // Disable all buttons in modal during submission
        const allBtns = form.querySelectorAll('.btn-submit');
        allBtns.forEach(b => b.disabled = true);
        
        btn.innerHTML = '<span class="material-icons-round spin" style="font-size: 1rem;">sync</span> ' + (status === 'draft' ? 'Salvando...' : 'Enviando...');

        try {
            const resp = await fetch(`/${tenantSlug}/aprendizado/step/${stepId}/submit`, {
                method: 'POST',
                body: formData
            });
            const data = await resp.json();

            if (data.success) {
                // Use Toast from layout
                if (typeof showToast !== 'undefined') {
                    showToast(data.message, 'success');
                } else if (typeof toast !== 'undefined' && toast.success) {
                    toast.success('Sucesso', data.message);
                } else {
                    swal(data.message, 'Sucesso');
                }
                closeModal();
                setTimeout(() => location.reload(), 1500);
            } else {
                if (typeof showToast !== 'undefined') {
                    showToast(data.error || 'Erro ao realizar ação', 'error');
                } else if (typeof toast !== 'undefined' && toast.error) {
                    toast.error('Erro', data.error || 'Erro ao realizar ação');
                } else {
                    swal(data.error || 'Erro desconhecido', 'Erro');
                }
                allBtns.forEach(b => b.disabled = false);
                btn.innerHTML = originalText;
            }
        } catch (err) {
            console.error(err);
            allBtns.forEach(b => b.disabled = false);
            btn.innerHTML = originalText;
            if (typeof showToast !== 'undefined') {
                showToast('Erro de conexão', 'error');
            } else if (typeof toast !== 'undefined' && toast.error) {
                toast.error('Erro', 'Erro de conexão');
            } else {
                swal('Erro de conexão', 'Erro');
            }
        }
    }
</script>