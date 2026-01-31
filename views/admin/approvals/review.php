<?php
/**
 * Admin: Focused Program Review (Standalone)
 * 
 * Specialized interface for evaluating all requirements of a single program.
 * Reverted to standalone to ensure 100% style persistence during HTMX transitions.
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revisão de Requisitos | DesbravaHub</title>
    
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@700;800&family=JetBrains+Mono:wght@600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?= asset_url('css/admin.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= asset_url('css/evaluations.css') ?>?v=<?= time() ?>">
    
    <!-- HTMX -->
    <script src="https://unpkg.com/htmx.org@1.9.10"></script>
</head>
<body hx-boost="true">
    <?php require BASE_PATH . '/views/admin/partials/sidebar.php'; ?>

    <main class="admin-main">
        <div class="eval-workspace">
            <header class="eval-review-header">
                <div style="display: flex; align-items: center; gap: 32px;">
                    <a href="<?= base_url($tenant['slug'] . '/admin/aprovacoes') ?>" class="btn-back" style="color: #64748b; display: flex; align-items: center; gap: 10px; text-decoration: none; font-weight: 600; font-size: 0.9rem; transition: all 0.2s; background: #f8fafc; padding: 8px 16px; border-radius: 12px; border: 1px solid #e2e8f0;">
                        <span class="material-icons-round">grid_view</span>
                        Voltar à Fila
                    </a>
                    <div class="header-student" style="display: flex; align-items: center; gap: 16px;">
                        <div class="student-avatar" style="width: 48px; height: 48px; border-radius: 14px; background: linear-gradient(135deg, var(--eval-cyan), var(--eval-purple)); display: flex; align-items: center; justify-content: center; font-weight: 800; font-family: 'Outfit'; color: #fff; box-shadow: 0 4px 12px rgba(6, 182, 212, 0.2);">
                            <?php if ($progress['avatar_url']): ?>
                                <img src="<?= $progress['avatar_url'] ?>" style="width: 100%; height: 100%; border-radius: 14px; object-fit: cover;">
                            <?php else: ?>
                                <?= strtoupper(substr($progress['user_name'] ?? 'U', 0, 1)) ?>
                            <?php endif; ?>
                        </div>
                        <div>
                            <div style="font-weight: 700; font-size: 1.25rem; font-family: 'Outfit'; line-height: 1; color: #0f172a;"><?= htmlspecialchars($progress['user_name']) ?></div>
                            <div style="font-size: 0.85rem; color: #64748b; margin-top: 4px;">Progresso em: <span style="color: var(--eval-cyan); font-weight: 600;"><?= htmlspecialchars($progress['program_name']) ?></span></div>
                        </div>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 32px;">
                    <div style="text-align: right;">
                        <div style="font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase;">Status</div>
                        <div style="font-family: 'JetBrains Mono'; font-size: 1.1rem; color: var(--eval-cyan); font-weight: 700;">
                            <?= $progress['progress_percent'] ?>%
                        </div>
                    </div>
                    <button class="btn-eval-cyan" style="font-size: 0.85rem; padding: 12px 24px;" onclick="approveAllNow()">
                        <span class="material-icons-round">done_all</span>
                        APROVAR TUDO
                    </button>
                </div>
            </header>

            <aside class="eval-review-sidebar">
                <div style="font-family: 'Outfit'; font-size: 0.75rem; font-weight: 800; letter-spacing: 0.1em; color: #64748b; text-transform: uppercase; margin-bottom: 8px; display: flex; align-items: center; gap: 8px; padding-left: 8px;">
                    <span class="material-icons-round" style="font-size: 1rem;">list_alt</span>
                    REQUISITOS
                </div>
                
                <?php foreach ($steps as $step): ?>
                    <div class="step-item <?= $step['response_status'] === 'submitted' ? 'active' : '' ?>" 
                         onclick="loadStep(<?= $step['id'] ?>)" 
                         id="step-nav-<?= $step['id'] ?>"
                         data-json='<?= json_encode($step, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'>
                        <span class="material-icons-round step-status-icon status-<?= $step['response_status'] ?? 'not_started' ?>" 
                              style="color: <?= $step['response_status'] === 'approved' ? '#10b981' : ($step['response_status'] === 'rejected' ? '#ef4444' : ($step['response_status'] === 'submitted' ? '#f59e0b' : '#94a3b8')) ?>">
                            <?php 
                            switch($step['response_status']) {
                                case 'approved': echo 'check_circle'; break;
                                case 'submitted': echo 'radio_button_checked'; break;
                                case 'rejected': echo 'error'; break;
                                default: echo 'radio_button_unchecked';
                            }
                            ?>
                        </span>
                        <div class="step-info">
                            <span class="step-name"><?= htmlspecialchars($step['title']) ?></span>
                            <div style="display: flex; align-items: center; gap: 8px; margin-top: 4px;">
                                <span style="background: rgba(15, 23, 42, 0.05); color: #64748b; padding: 2px 8px; border-radius: 6px; font-size: 0.7rem; font-weight: 800; font-family: 'JetBrains Mono';"><?= $step['points'] ?> PTS</span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </aside>

            <main class="eval-review-main" id="step-main-area">
                <div id="step-content" style="width: 100%; max-width: 850px; animation: fadeIn 0.4s ease-out;">
                    <div class="empty-state-radar">
                        <span class="material-icons-round">radar</span>
                        <h2 class="outfit" style="font-size: 2rem; font-weight: 800; opacity: 1;">Aguardando Seleção...</h2>
                        <p style="font-size: 1.1rem; opacity: 1;">Selecione um requisito para iniciar a validação.</p>
                    </div>
                </div>
            </main>

            <footer class="eval-review-footer" id="reviewer-actions" style="display: none;">
                <div style="flex: 1;">
                    <textarea id="feedback-text" placeholder="Adicionar feedback tático ou observações..." style="width: 100%; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 18px; padding: 16px 24px; color: #0f172a; font-family: inherit; resize: none; height: 64px; transition: all 0.2s; font-size: 1rem;"></textarea>
                </div>
                <div style="display: flex; gap: 16px;">
                    <button onclick="processAction('reject')" style="padding: 12px 24px; border-radius: 14px; font-weight: 800; font-family: 'Outfit'; cursor: pointer; border: 1px solid #fee2e2; background: #fff; color: #ef4444; display: flex; align-items: center; gap: 12px; transition: all 0.2s; font-size: 0.9rem; text-transform: uppercase;">
                        <span class="material-icons-round">keyboard_return</span>
                        Solicitar Ajuste
                    </button>
                    <button class="btn-eval-cyan" onclick="processAction('approve')" style="font-size: 0.9rem; padding: 14px 28px;">
                        <span class="material-icons-round">verified</span>
                        Aprovar Item
                    </button>
                </div>
            </footer>
        </div>
    </main>

    <script>
        window.currentStepId = null;
        window.currentResponseId = null;
        window.tenantSlug = '<?= $tenant['slug'] ?>';
        window.progressId = <?= $progress['id'] ?>;

        window.loadStep = function(id) {
            const el = document.getElementById('step-nav-' + id);
            if (!el) return;
            const data = JSON.parse(el.dataset.json);
            window.currentStepId = id;
            window.currentResponseId = data.response_id;
            
            document.querySelectorAll('.step-item').forEach(i => i.classList.remove('active'));
            el.classList.add('active');
            
            const content = document.getElementById('step-content');
            content.style.opacity = '0';
            
            setTimeout(() => {
                let html = `
                    <div style="font-family: 'Outfit'; font-size: 2rem; font-weight: 800; margin-bottom: 32px; color: #0f172a; line-height: 1.2;">${data.title}</div>
                    ${data.description ? `
                        <div style="font-family: 'Outfit'; font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase; margin-bottom: 12px; letter-spacing: 0.05em;">Objetivo do Requisito</div>
                        <div style="color: #475569; line-height: 1.7; margin-bottom: 48px; font-size: 1.1rem; background: #ffffff; padding: 24px; border-radius: 20px; border: 1px solid rgba(0,0,0,0.03);">${data.description.replace(/\n/g, '<br>')}</div>
                    ` : ''}
                    
                    <div style="font-family: 'Outfit'; font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase; margin-bottom: 12px; letter-spacing: 0.05em;">Evidência Enviada</div>
                    <div style="background: #ffffff; padding: 32px; border-radius: 24px; border: 1px solid rgba(0,0,0,0.05); margin-bottom: 40px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
                        ${data.response_text ? `<div style="font-size: 1.15rem; line-height: 1.8; color: #1e293b;">${data.response_text.replace(/\n/g, '<br>')}</div>` : '<div style="color: #94a3b8; font-style: italic;">Nenhum texto enviado pelo desbravador.</div>'}
                        ${(data.response_url || data.response_file) ? `
                            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; margin-top: 32px;">
                                ${data.response_url ? `<a href="${data.response_url}" target="_blank" style="background: #f8fafc; border-radius: 16px; padding: 18px; display: flex; align-items: center; gap: 12px; color: var(--eval-cyan); text-decoration: none; border: 1px solid #e2e8f0; font-weight: 600; font-size: 0.9rem;"><span class="material-icons-round">link</span>Acessar Link Externo</a>` : ''}
                                ${data.response_file ? `<a href="${data.response_file}" target="_blank" style="background: #f8fafc; border-radius: 16px; padding: 18px; display: flex; align-items: center; gap: 12px; color: var(--eval-cyan); text-decoration: none; border: 1px solid #e2e8f0; font-weight: 600; font-size: 0.9rem;"><span class="material-icons-round">photo_library</span>Visualizar Mídia</a>` : ''}
                            </div>
                        ` : ''}
                    </div>
                `;
                content.innerHTML = html;
                content.style.opacity = '1';
                document.getElementById('step-main-area').scrollTo({ top: 0, behavior: 'smooth' });
            }, 100);

            const actions = document.getElementById('reviewer-actions');
            if (data.response_status === 'submitted') { 
                actions.style.display = 'flex'; 
                document.getElementById('feedback-text').value = ''; 
            } else { 
                actions.style.display = 'none'; 
            }
        }

        window.processAction = async function(type) {
            if (!window.currentResponseId) return;
            const feedback = document.getElementById('feedback-text').value;
            try {
                const resp = await fetch(`/${window.tenantSlug}/admin/aprovacoes/${window.currentResponseId}/${type}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ feedback })
                });
                const data = await resp.json();
                if (data.success) {
                    const nav = document.getElementById('step-nav-' + window.currentStepId);
                    const navIcon = nav.querySelector('.step-status-icon');
                    navIcon.className = `material-icons-round step-status-icon status-${type === 'approve' ? 'approved' : 'rejected'}`;
                    navIcon.textContent = type === 'approve' ? 'check_circle' : 'error';
                    navIcon.style.color = type === 'approve' ? '#10b981' : '#ef4444';
                    
                    const nextItem = [...document.querySelectorAll('.step-item')]
                        .find((item, idx, arr) => {
                            const prevIdx = arr.findIndex(i => i.id === 'step-nav-' + window.currentStepId);
                            return idx > prevIdx && item.querySelector('.status-submitted');
                        });
                        
                    if (nextItem) nextItem.click(); 
                    else location.reload();
                }
            } catch (err) { swal('Erro na comunicação com o servidor.', 'Erro'); }
        }

        window.approveAllNow = async function() {
            const confirmed = await sconfirm('Deseja aprovar todos os itens pendentes deste programa agora?', 'Aprovação em Massa');
            if (!confirmed) return;
            try {
                const resp = await fetch(`/${window.tenantSlug}/admin/aprovacoes/${window.progressId}/bulk-approve-program`, { method: 'POST' });
                const data = await resp.json();
                if (data.success) location.reload();
            } catch (err) { swal('Erro operacional.', 'Erro'); }
        }

        window.initReview = function() {
            const firstPending = document.querySelector('.step-item .status-submitted')?.closest('.step-item');
            if (firstPending) firstPending.click();
        }

        document.addEventListener('DOMContentLoaded', window.initReview);
        
        if (!window.reviewListenersInitialized) {
            document.body.addEventListener('htmx:afterSwap', (e) => {
                if (typeof window.initReview === 'function' && (e.detail.target.id === 'main-content' || e.detail.target.tagName === 'BODY')) {
                    window.initReview();
                }
            });
            window.reviewListenersInitialized = true;
        }
    </script>
</body>
</html>
