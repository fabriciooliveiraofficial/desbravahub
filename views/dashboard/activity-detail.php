<?php
/**
 * Activity Detail - Detalhes da Missão
 * DESIGN: Deep Glass HUD v3.0 (Content Only)
 */
?>
<div class="hud-wrapper">
    <!-- HUD Header -->
    <header class="hud-header">
        <div>
            <div style="font-size: 0.8rem; color: var(--accent-cyan); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 4px;">
                <a href="<?= base_url($tenant['slug'] . '/atividades') ?>" style="color: inherit; text-decoration: none;">
                    <i class="fas fa-chevron-left" style="font-size: 0.7rem;"></i> VOLTAR
                </a>
            </div>
            <h1 class="hud-title"><?= htmlspecialchars($activity['title']) ?></h1>
            <div class="hud-subtitle">
                <span class="hud-badge" style="color: var(--accent-green); border-color: var(--accent-green)">+<?= $activity['xp_reward'] ?> XP</span>
                <?php if ($activity['is_outdoor']): ?>
                    <span class="hud-badge" style="color: var(--accent-warning); border-color: var(--accent-warning)">🏕️ Outdoor</span>
                <?php endif; ?>
                <span class="hud-badge">NV. <?= $activity['min_level'] ?>+</span>
            </div>
        </div>
    </header>

    <div class="hud-grid" style="grid-template-columns: 1fr;">
        <!-- Briefing Section -->
        <section class="tech-plate" style="grid-column: 1 / -1;">
            <div class="plate-header">
                <div class="plate-content">
                    <h3 class="plate-title">Briefing da Missão</h3>
                </div>
                <i class="material-icons-round plate-icon">assignment</i>
            </div>
            <div style="font-size: 0.95rem; line-height: 1.6; color: var(--hud-text-primary); margin-top: 16px;">
                <?= nl2br(htmlspecialchars($activity['description'] ?? 'Sem descrição disponivel.')) ?>
            </div>

            <?php if (!empty($activity['instructions'])): ?>
                <div style="margin-top: 24px; padding-top: 24px; border-top: 1px dashed var(--hud-glass-border);">
                    <h4 style="color: var(--accent-cyan); margin-bottom: 12px; font-size: 1rem;">Protocolos Operacionais</h4>
                    <div style="font-size: 0.9rem; line-height: 1.6; color: var(--hud-text-dim);">
                        <?= nl2br(htmlspecialchars($activity['instructions'])) ?>
                    </div>
                </div>
            <?php endif; ?>
        </section>

        <!-- Prerequisites -->
        <?php if (!empty($prerequisites)): ?>
            <section class="tech-plate" style="border-left: 3px solid var(--accent-warning);">
                <div class="plate-header">
                    <div class="plate-content">
                        <h3 class="plate-title">Requisitos Prévios</h3>
                    </div>
                    <i class="material-icons-round plate-icon" style="color: var(--accent-warning)">lock</i>
                </div>
                <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 16px;">
                    <?php foreach ($prerequisites as $prereq): 
                        $isDone = $prereq['user_completed'] ?? false;
                    ?>
                        <div style="display: flex; align-items: center; gap: 10px; padding: 10px; background: rgba(0,0,0,0.2); border-radius: 4px; border: 1px solid <?= $isDone ? 'var(--accent-green)' : 'var(--hud-glass-border)' ?>">
                            <i class="material-icons-round" style="color: <?= $isDone ? 'var(--accent-green)' : 'var(--text-muted)' ?>">
                                <?= $isDone ? 'check_circle' : 'radio_button_unchecked' ?>
                            </i>
                            <span style="color: <?= $isDone ? 'var(--hud-text-primary)' : 'var(--hud-text-dim)' ?>">
                                <?= htmlspecialchars($prereq['title']) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- Action Panel -->
        <section class="tech-plate" style="border-left: 3px solid var(--accent-cyan);">
            <div class="plate-header">
                <div class="plate-content">
                    <h3 class="plate-title">Status da Missão</h3>
                </div>
                <i class="material-icons-round plate-icon" style="color: var(--accent-cyan)">radar</i>
            </div>

            <div style="margin-top: 20px; text-align: center;">
                <?php if (!$userActivity): ?>
                    <form action="<?= base_url($tenant['slug'] . '/api/activities/' . $activity['id'] . '/start') ?>" method="POST">
                        <?= csrf_field() ?>
                        <button type="submit" class="hud-btn primary" style="width: 100%; justify-content: center; font-size: 1.1rem; padding: 16px;">
                            <i class="material-icons-round" style="margin-right: 8px;">rocket_launch</i>
                            INICIAR MISSÃO
                        </button>
                    </form>
                <?php elseif ($userActivity['status'] === 'in_progress'): ?>
                    <form id="proofForm">
                        <?= csrf_field() ?>
                        <input type="hidden" name="activity_id" value="<?= $activity['id'] ?>">

                        <div style="margin-bottom: 20px; text-align: left;">
                            <label style="display: block; color: var(--hud-text-dim); margin-bottom: 8px; font-size: 0.8rem; text-transform: uppercase;">Tipo de Comprovante</label>
                            <div style="display: flex; gap: 10px;">
                                <button type="button" class="hud-btn proof-type-btn active" data-type="url" style="flex: 1; justify-content: center;">🔗 Link</button>
                                <button type="button" class="hud-btn proof-type-btn" data-type="upload" style="flex: 1; justify-content: center; background: transparent; border: 1px solid var(--hud-glass-border);">📎 Arquivo</button>
                            </div>
                        </div>

                        <div id="urlField" style="margin-bottom: 20px; text-align: left;">
                            <input type="url" name="url" class="hud-input" placeholder="Cole o link aqui (YouTube, Instagram...)" style="width: 100%;">
                        </div>

                        <div id="uploadField" style="margin-bottom: 20px; text-align: left; display: none;">
                            <input type="file" name="file" class="hud-input" accept="image/*,video/*,.pdf" style="width: 100%;">
                        </div>

                        <div id="previewContainer" style="margin-bottom: 20px; text-align: left;"></div>

                        <button type="submit" class="hud-btn primary" style="width: 100%; justify-content: center; padding: 16px;">
                            <i class="material-icons-round" style="margin-right: 8px;">send</i>
                            ENVIAR RELATÓRIO
                        </button>
                    </form>
                <?php else: ?>
                    <div style="padding: 20px; background: rgba(0,255,136,0.1); border-radius: 8px; border: 1px solid var(--accent-green);">
                        <i class="material-icons-round" style="font-size: 3rem; color: var(--accent-green); margin-bottom: 10px;">military_tech</i>
                        <h3 style="color: var(--accent-green); margin-bottom: 8px;">MISSÃO CUMPRIDA</h3>
                        <p>Recompensa de <?= $activity['xp_reward'] ?> XP creditada.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>

<script>
    // Local Proof Logic embedded to avoid external dependencies breaking
    document.addEventListener('DOMContentLoaded', () => {
        const proofForm = document.getElementById('proofForm');
        if (!proofForm) return;

        const urlField = document.getElementById('urlField');
        const uploadField = document.getElementById('uploadField');
        const previewContainer = document.getElementById('previewContainer');
        const typeBtns = document.querySelectorAll('.proof-type-btn');

        // Toggle Type
        typeBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                typeBtns.forEach(b => {
                    b.classList.remove('active');
                    b.style.background = 'transparent';
                    b.style.border = '1px solid var(--hud-glass-border)';
                });
                btn.classList.add('active');
                btn.style.background = 'var(--hud-bg-card)';
                btn.style.borderColor = 'var(--accent-cyan)';

                const type = btn.dataset.type;
                if (type === 'url') {
                    urlField.style.display = 'block';
                    uploadField.style.display = 'none';
                } else {
                    urlField.style.display = 'none';
                    uploadField.style.display = 'block';
                }
                previewContainer.innerHTML = '';
            });
        });

        // Set initial state
        document.querySelector('.proof-type-btn[data-type="url"]').click();

        // Submit Handler
        proofForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = proofForm.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            
            btn.disabled = true;
            btn.innerHTML = 'Enviando...';

            const formData = new FormData(proofForm);
            const activeType = document.querySelector('.proof-type-btn.active').dataset.type;
            formData.append('type', activeType);

            try {
                const response = await fetch('<?= base_url($tenant['slug'] . '/api/proofs/submit') ?>', {
                    method: 'POST',
                    body: formData,
                    headers: { 'Accept': 'application/json' }
                });
                const data = await response.json();

                if (data.success || data.message) {
                    await swal(data.message || 'Relatório enviado com sucesso!', 'Sucesso');
                    location.reload();
                } else {
                    swal(data.error || 'Erro ao enviar', 'Houve um problema');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            } catch (err) {
                swal('Erro de comunicação com o QG.', 'Erro de Conexão');
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        });
    });
</script>