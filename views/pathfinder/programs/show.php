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
        <div class="hud-stat-card primary tech-plate vibrant-cyan stagger-1" style="flex: 2; padding: 24px;">
            <div class="plate-header" style="margin-bottom: 8px;">
                <div>
                    <div class="hud-stat-value" style="font-size: 2.8rem; line-height: 1;"><?= $engPercent ?>%</div>
                    <div class="hud-stat-label">SINCRONIZAÇÃO DE DADOS</div>
                </div>
                <i class="material-icons-round hud-stat-icon" style="opacity: 1; color: var(--accent-cyan); filter: drop-shadow(0 0 8px var(--accent-cyan)); font-size: 2.5rem;">hub</i>
            </div>
            
            <div style="margin: 16px 0;">
                <div class="hud-progress" style="background: rgba(0,0,0,0.3); height: 8px; border-radius: 100px; overflow: hidden; border: 1px solid rgba(255,255,255,0.05); position: relative;">
                    <!-- Answered Bar (Engagement) -->
                    <div class="hud-progress-bar" style="width: <?= $engPercent ?>%; background: var(--accent-cyan); opacity: 0.3; position: absolute; height: 100%;"></div>
                    <!-- Approved Bar (Real Progress) -->
                    <div class="hud-progress-bar" style="width: <?= $progPercent ?>%; background: linear-gradient(90deg, #22d3ee, #06b6d4); position: relative; height: 100%; box-shadow: 0 0 10px var(--accent-cyan);"></div>
                </div>
            </div>

            <div style="display: flex; justify-content: space-between; margin-top: 2px;">
                <span style="font-size: 0.65rem; font-weight: 800; color: var(--hud-text-dim);">ÍNDICE DE VALIDAÇÃO</span>
                <span style="font-size: 0.75rem; font-weight: 900; color: #fff;"><?= $progPercent ?>%</span>
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 16px; flex: 1.2;">
            <div class="hud-stat-card tech-plate vibrant-purple stagger-2" style="padding: 20px;">
                <div class="plate-header" style="margin-bottom: 0;">
                    <div>
                        <div class="hud-stat-value" style="font-size: 1.8rem; color: #a78bfa;"><?= count($steps) ?></div>
                        <div class="hud-stat-label">TOTAL DE MÓDULOS</div>
                    </div>
                    <i class="material-icons-round hud-stat-icon" style="color: #8b5cf6; opacity: 1; font-size: 1.8rem;">inventory_2</i>
                </div>
            </div>

            <div class="hud-stat-card tech-plate vibrant-green stagger-3" style="padding: 20px;">
                <div class="plate-header" style="margin-bottom: 0;">
                    <div>
                        <div class="hud-stat-value" style="font-size: 1.8rem; color: var(--accent-green);">+<?= number_format($program['xp_reward']) ?></div>
                        <div class="hud-stat-label">RECOMPENSA XP</div>
                    </div>
                    <i class="material-icons-round hud-stat-icon" style="color: var(--accent-green); opacity: 1; font-size: 1.8rem;">auto_awesome</i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Finalize Button (If 100% engaged but not submitted) -->
    <?php if ($engPercent >= 100 && $program['user_status'] === 'in_progress'): ?>
        <div class="tech-plate vibrant-orange stagger-4" style="margin-top: 24px; padding: 24px; display: flex; justify-content: space-between; align-items: center;">
            <div class="status-line"></div>
            <div style="display: flex; align-items: center; gap: 20px;">
                <div style="width: 56px; height: 56px; border-radius: 12px; background: rgba(249, 115, 22, 0.1); border: 1px solid rgba(249, 115, 22, 0.3); display: flex; align-items: center; justify-content: center;">
                    <i class="material-icons-round" style="color: #f97316; font-size: 2rem; filter: drop-shadow(0 0 8px #f97316);">verified</i>
                </div>
                <div>
                    <div class="hud-stat-value" style="font-size: 1.2rem; color: #fff;">MISSÃO CONCLUÍDA!</div>
                    <div class="hud-stat-label">O QG aguarda o envio dos dados para avaliação final.</div>
                </div>
            </div>
            <button class="hud-btn primary program-submit-btn" data-program-id="<?= $program['id'] ?>" style="background: linear-gradient(135deg, #f97316, #fb923c); box-shadow: 0 4px 15px rgba(249, 115, 22, 0.3);">
                <i class="material-icons-round">send</i> ENVIAR AGORA
            </button>
        </div>
    <?php endif; ?>

    <!-- Steps List -->
    <section class="hud-section" style="margin-top: 40px;">
        <div class="hud-section-header">
            <h2 class="hud-section-title" style="font-size: 1.1rem; color: var(--accent-cyan);">SEQUÊNCIA OPERACIONAL</h2>
            <div class="hud-section-count"><?= $totalSteps ?> REQUISITOS</div>
        </div>
        
        <div class="hud-grid" style="grid-template-columns: 1fr; gap: 16px;">
            <?php foreach ($steps as $index => $step):
                $response = $step['response'];
                $status = $response['status'] ?? 'pending';
                
                // Vibrant Selection
                $vClass = match ($status) {
                    'submitted' => 'vibrant-orange',
                    'approved' => 'vibrant-green',
                    'rejected' => 'vibrant-red',
                    default => 'vibrant-cyan'
                };

                $config = match ($status) {
                    'submitted' => ['color' => '#fbbf24', 'icon' => 'hourglass_empty', 'label' => 'EM ANÁLISE'],
                    'approved' => ['color' => '#34d399', 'icon' => 'verified', 'label' => 'APROVADO'],
                    'rejected' => ['color' => '#f87171', 'icon' => 'error_outline', 'label' => 'REVISÃO'],
                    default => ['color' => 'var(--accent-cyan)', 'icon' => 'radio_button_unchecked', 'label' => 'DISPONÍVEL']
                };
                ?>
                <div class="tech-plate learning-card <?= $vClass ?> stagger-<?= ($index % 4) + 1 ?>" 
                     onclick="openStepModal(<?= $step['id'] ?>)" 
                     style="cursor: pointer; padding: 0; min-height: 100px; display: flex;">
                    
                    <div class="status-line"></div>

                    <!-- Number Column -->
                    <div style="width: 80px; display: flex; align-items: center; justify-content: center; position: relative;">
                         <div style="font-family: 'JetBrains Mono', monospace; font-weight: 900; font-size: 1.2rem; opacity: 0.3; color: <?= $config['color'] ?>;">
                            <?= str_pad($index + 1, 2, '0', STR_PAD_LEFT) ?>
                        </div>
                    </div>

                    <!-- Content Column -->
                    <div class="plate-content" style="flex: 1; padding: 24px 0; display: flex; flex-direction: column; gap: 8px;">
                        <!-- Status Tag -->
                        <div style="display: flex; align-items: center; gap: 10px;">
                             <div class="hud-badge" style="font-size: 0.6rem; color: <?= $config['color'] ?>; border-color: <?= $config['color'] ?>; background: rgba(0,0,0,0.2);">
                                <?= $config['label'] ?>
                            </div>
                            <?php if ($step['is_required']): ?>
                                <span style="font-size: 0.6rem; font-weight: 800; color: #f87171; letter-spacing: 0.1em;">[OBRIGATÓRIO]</span>
                            <?php endif; ?>
                        </div>

                        <!-- Title -->
                        <h3 class="plate-title" style="font-size: 1.1rem; line-height: 1.3;"><?= htmlspecialchars($step['title']) ?></h3>
                    </div>

                    <!-- Icon Column -->
                    <div style="width: 70px; display: flex; align-items: center; justify-content: center;">
                        <i class="material-icons-round" style="color: <?= $config['color'] ?>; font-size: 1.8rem; opacity: 0.8;"><?= $config['icon'] ?></i>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <style>
        .learning-card {
            border-radius: 20px !important;
            transition: all 0.3s cubic-bezier(0.2, 0.8, 0.2, 1);
        }
        .learning-card:hover {
            background: rgba(255, 255, 255, 0.1) !important;
            transform: scale(1.01) translateX(10px) !important;
        }
        .learning-card:active {
            transform: scale(0.98) !important;
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