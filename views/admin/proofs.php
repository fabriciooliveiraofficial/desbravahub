<?php
/**
 * Provas Review - Master Design v2.0 (Light Edition)
 * High Contrast, Efficiency-Focused Task Queue
 */
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
<script src="https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js"></script>

<style>
    :root {
        /* Master Design Light Palette */
        --bg-main: #F3F4F6;
        --bg-card: #FFFFFF;
        --text-main: #1F2937;
        --text-muted: #6B7280;
        --accent-primary: #06b6d4; /* Cyan 500 */
        --accent-secondary: #0891b2; /* Cyan 600 */
        --accent-success: #10b981;
        --accent-warning: #f59e0b;
        --accent-danger: #ef4444;
        --border-color: #E5E7EB;
        --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        --card-shadow-hover: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    body {
        background-color: var(--bg-main) !important;
        font-family: 'Inter', sans-serif;
        color: var(--text-main);
        min-height: 100vh;
        margin: 0;
    }

    .proofs-wrapper {
        max-width: 1000px;
        margin: 0 auto;
        padding: 40px 20px 80px;
        animation: fadeIn 0.4s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Section Header */
    .section-header {
        margin-bottom: 32px;
    }

    .section-header h1 {
        font-family: 'Outfit', sans-serif;
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--text-main);
        margin: 0 0 4px 0;
        letter-spacing: -0.01em;
    }

    .section-header p {
        color: var(--text-muted);
        font-size: 1rem;
        margin: 0;
    }

    /* Proof Cards */
    .proofs-grid {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .proof-card {
        background: var(--bg-card);
        border-radius: 16px;
        border: 1px solid var(--border-color);
        box-shadow: var(--card-shadow);
        padding: 20px;
        display: grid;
        grid-template-columns: 1fr;
        gap: 16px;
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
    }

    .proof-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--card-shadow-hover);
        border-color: var(--accent-primary);
    }

    .proof-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; bottom: 0;
        width: 4px;
        background: var(--accent-primary);
        opacity: 0.8;
    }

    /* Card Header */
    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .avatar-circle {
        width: 36px;
        height: 36px;
        background: #f1f5f9;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: var(--accent-primary);
        font-size: 0.9rem;
        border: 1.5px solid var(--border-color);
    }

    .user-details h3 {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-main);
    }

    .submission-time {
        font-size: 0.75rem;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .badge-container {
        display: flex;
        gap: 8px;
    }

    .badge {
        padding: 5px 12px;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .badge-primary { background: #e0f2fe; color: #0369a1; } /* Program */
    .badge-secondary { background: #f3e8ff; color: #7e22ce; } /* Activity */
    .badge-type { background: #f1f5f9; color: #475569; } /* Content type */

    /* Content Area */
    .content-box {
        background: #F9FAFB;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 20px;
    }

    .content-title {
        font-family: 'Outfit', sans-serif;
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-main);
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* QA Blocks for Programs */
    .qa-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .qa-item {
        border-bottom: 1px solid #ECEEEF;
        padding-bottom: 12px;
    }
    .qa-item:last-child { border: none; padding-bottom: 0; }

    .question-label {
        font-size: 0.8rem;
        font-weight: 700;
        color: var(--text-muted);
        text-transform: uppercase;
        margin-bottom: 4px;
    }

    .answer-text {
        font-size: 1rem;
        line-height: 1.5;
        color: var(--text-main);
    }

    /* Media Handling */
    .media-preview-container {
        margin-top: 16px;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid var(--border-color);
    }

    .media-img {
        width: 100%;
        max-height: 400px;
        object-fit: contain;
        background: #000;
        display: block;
    }

    .media-file {
        padding: 16px;
        background: #fff;
        display: flex;
        align-items: center;
        gap: 12px;
        color: var(--text-main);
        text-decoration: none;
        font-weight: 600;
    }

    .media-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--accent-primary);
        text-decoration: none;
        font-weight: 700;
        margin-top: 8px;
    }

    /* Action Buttons */
    .action-group {
        display: flex;
        gap: 12px;
    }

    .btn {
        height: 40px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 0.85rem;
        padding: 0 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
    }

    .btn-approve {
        background: var(--accent-primary);
        color: #fff;
        flex: 2;
    }
    .btn-approve:hover {
        background: var(--accent-secondary);
        transform: translateY(-1px);
        box-shadow: 0 4px 6px -1px rgba(6, 182, 212, 0.4);
    }

    .btn-changes {
        background: #FEF3C7;
        color: #92400E;
        flex: 1;
    }
    .btn-changes:hover { background: #FDE68A; }

    .btn-reject {
        background: #FEE2E2;
        color: #B91C1C;
        padding: 0 16px;
    }
    .btn-reject:hover { background: #FECACA; }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 40px;
        background: var(--bg-card);
        border-radius: 24px;
        border: 2px dashed var(--border-color);
    }

    .empty-state iconify-icon {
        font-size: 64px;
        color: var(--accent-primary);
        margin-bottom: 16px;
    }

    /* SweetAlert Customization */
    .swal2-popup { border-radius: 24px !important; }

    @media (max-width: 640px) {
        .card-header { flex-direction: column; }
        .action-group { flex-direction: column; }
        .btn { width: 100%; }
    }
</style>

<div class="proofs-wrapper">
    <div class="section-header" style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 20px;">
        <div>
            <h1>Fila de Aprovações</h1>
            <p>Revise as evidências enviadas pelos desbravadores.</p>
        </div>
        
        <div style="display: flex; background: white; padding: 4px; border-radius: 12px; border: 1px solid var(--border-color);">
            <button class="btn-tab active" onclick="switchProofTab('pending')" id="tab-pending" style="height: 36px; padding: 0 16px; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; transition: all 0.2s;">
                Pendentes
            </button>
            <button class="btn-tab" onclick="switchProofTab('history')" id="tab-history" style="height: 36px; padding: 0 16px; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; transition: all 0.2s; background: transparent; color: var(--text-muted);">
                Histórico
            </button>
        </div>
    </div>

    <div id="pending-container">
        <?php if (empty($proofs)): ?>
            <div class="empty-state">
                <iconify-icon icon="heroicons:sparkles"></iconify-icon>
                <h2 style="font-family:'Outfit'; font-weight:800; margin-bottom:8px;">Tudo Limpo!</h2>
                <p style="color:var(--text-muted); margin:0;">Não há evidências pendentes para revisão no momento.</p>
            </div>
        <?php else: ?>
            <div class="proofs-grid">
                <?php foreach ($proofs as $proof): ?>
                    <div class="proof-card" id="proof-<?= $proof['kind'] ?>-<?= $proof['id'] ?>">
                        <!-- Card Top -->
                        <div class="card-header">
                            <div class="user-info">
                                <div class="avatar-circle">
                                    <?= strtoupper(substr($proof['user_name'], 0, 1)) ?>
                                </div>
                                <div class="user-details">
                                    <h3><?= htmlspecialchars($proof['user_name']) ?></h3>
                                    <div class="submission-time">
                                        <iconify-icon icon="heroicons:clock"></iconify-icon>
                                        <?= date('d/m/Y H:i', strtotime($proof['submitted_at'])) ?>
                                    </div>
                                </div>
                            </div>

                            <div class="badge-container">
                                <span class="badge <?= $proof['kind'] === 'program' ? 'badge-primary' : 'badge-secondary' ?>">
                                    <?= $proof['kind'] === 'program' ? 'Programa' : 'Atividade' ?>
                                </span>
                                <?php 
                                    $typeIcon = $proof['type'] === 'url' ? 'heroicons:link' : ($proof['type'] === 'upload' ? 'heroicons:document' : 'heroicons:document-text');
                                    $typeLabel = $proof['type'] === 'text' ? 'Texto' : ($proof['type'] === 'url' ? 'Link' : 'Arquivo');
                                ?>
                                <span class="badge badge-type">
                                    <iconify-icon icon="<?= $typeIcon ?>" style="vertical-align: middle;"></iconify-icon>
                                    <?= $typeLabel ?>
                                </span>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="content-box">
                            <div class="content-title">
                                <iconify-icon icon="heroicons:academic-cap" style="color:var(--accent-primary)"></iconify-icon>
                                <?= htmlspecialchars($proof['item_title']) ?>
                            </div>

                            <?php if ($proof['kind'] === 'program' && !empty($proof['structured_content'])): ?>
                                <div class="qa-list">
                                    <?php foreach ($proof['structured_content'] as $item): ?>
                                        <div class="qa-item">
                                            <div class="question-label"><?= htmlspecialchars($item['question']) ?></div>
                                            <div class="answer-text"><?= nl2br(htmlspecialchars($item['answer'])) ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Additional Content (Links/Uploads/Activity Text) -->
                            <div style="margin-top: <?= !empty($proof['structured_content']) ? '20px' : '0' ?>;">
                                <?php if ($proof['type'] === 'url'): ?>
                                    <?php 
                                        $rawUrl = $proof['content'];
                                        $embedHtml = null;
                                        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $rawUrl, $match)) {
                                            $embedHtml = '<iframe width="100%" height="315" src="https://www.youtube.com/embed/'.$match[1].'" frameborder="0" allowfullscreen style="border:none;"></iframe>';
                                        }
                                    ?>
                                    <?php if ($embedHtml): ?>
                                        <div class="media-preview-container"><?= $embedHtml ?></div>
                                    <?php endif; ?>
                                    <a href="<?= htmlspecialchars($rawUrl) ?>" target="_blank" class="media-link">
                                        <iconify-icon icon="heroicons:arrow-top-right-on-square"></iconify-icon>
                                        Acessar Link Externo
                                    </a>

                                <?php elseif ($proof['type'] === 'upload'): ?>
                                    <?php 
                                        $proofPath = (string)$proof['content'];
                                        // Handle legacy storage paths
                                        if (strpos($proofPath, 'storage/proofs/') === 0) {
                                            $proofPath = '/' . $proofPath;
                                        }
                                        $ext = pathinfo($proofPath, PATHINFO_EXTENSION);
                                        $isImg = in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'webp', 'gif']);
                                        $fullUrl = base_url($proofPath);
                                    ?>
                                    <div class="media-preview-container">
                                        <?php if ($isImg): ?>
                                            <img src="<?= $fullUrl ?>" class="media-img">
                                        <?php else: ?>
                                            <div class="media-file">
                                                <iconify-icon icon="heroicons:document-arrow-down" style="font-size:24px; color:var(--accent-primary);"></iconify-icon>
                                                <span>Documento de Entrega (.<?= strtoupper($ext) ?>)</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <a href="<?= $fullUrl ?>" target="_blank" class="media-link">
                                        <iconify-icon icon="heroicons:magnifying-glass-plus"></iconify-icon>
                                        Visualizar em Tela Cheia
                                    </a>

                                <?php elseif ($proof['kind'] === 'activity' && $proof['type'] === 'text'): ?>
                                    <div class="answer-text" style="padding: 12px; background: white; border-radius: 8px; border: 1px solid #ECEEEF; font-style: italic;">
                                        "<?= htmlspecialchars($proof['content']) ?>"
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Hub Toggle -->
                        <div style="padding: 12px 16px; background: rgba(245, 158, 11, 0.05); border: 1px solid rgba(245, 158, 11, 0.2); border-radius: 12px; margin-bottom: 4px; display: flex; align-items: center; justify-content: space-between; gap: 10px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <input type="checkbox" id="public_<?= $proof['kind'] ?>_<?= $proof['id'] ?>" 
                                       <?= !empty($proof['show_public']) ? 'checked' : '' ?>
                                       style="width: 20px; height: 20px; accent-color: var(--accent-amber); cursor: pointer;">
                                <label for="public_<?= $proof['kind'] ?>_<?= $proof['id'] ?>" style="font-weight: 700; cursor: pointer; color: var(--text-main); font-size: 0.9rem; user-select: none;">
                                    <iconify-icon icon="solar:stars-line-duotone" style="color: var(--accent-amber); vertical-align: middle; margin-right: 4px; font-size: 1.2rem;"></iconify-icon>
                                    Destacar evidência no Hub Público do Clube
                                </label>
                            </div>
                            <button onclick="updateActivityCuration(<?= $proof['id'] ?>, '<?= $proof['kind'] ?>')" class="btn" style="height: 32px; background: var(--accent-amber); color: white; padding: 0 12px; border-radius: 8px; font-weight: 700; font-size: 0.8rem; display: flex; align-items: center; gap: 6px;">
                                <iconify-icon icon="solar:diskette-bold-duotone" style="font-size: 1rem;"></iconify-icon>
                                Salvar
                            </button>
                        </div>

                        <!-- Card Actions -->
                        <div class="action-group">
                            <button class="btn btn-approve" onclick="reviewProof(<?= $proof['id'] ?>, 'approved', '<?= $proof['kind'] ?>')">
                                <iconify-icon icon="heroicons:check-badge"></iconify-icon>
                                Aprovar Conquista
                            </button>
                            <button class="btn btn-changes" onclick="requestChanges(<?= $proof['id'] ?>, '<?= $proof['kind'] ?>')">
                                <iconify-icon icon="heroicons:pencil-square"></iconify-icon>
                                Solicitar Ajustes
                            </button>
                            <button class="btn btn-reject" onclick="reviewProof(<?= $proof['id'] ?>, 'rejected', '<?= $proof['kind'] ?>')">
                                <iconify-icon icon="heroicons:x-circle"></iconify-icon>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- History Tab (Initially Hidden) -->
    <div id="history-container" style="display: none;">
        <div style="padding: 40px; text-align: center; color: var(--text-muted); background: white; border-radius: 20px; border: 1px solid var(--border-color);">
            <iconify-icon icon="solar:history-bold-duotone" style="font-size: 48px; opacity: 0.3; margin-bottom: 16px;"></iconify-icon>
            <p>Carregando histórico de avaliações...</p>
        </div>
    </div>
</div>

<!-- SweetAlert2 for Interactions -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    async function reviewProof(proofId, action, kind = 'activity', comment = null) {
        // Confirmation for Rejection
        if (action === 'rejected') {
            const result = await Swal.fire({
                title: 'Tem certeza?',
                text: "Esta ação não pode ser desfeita.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Sim, rejeitar',
                cancelButtonText: 'Cancelar'
            });
            if (!result.isConfirmed) return;
        }

        try {
            const isPublicBox = document.getElementById(`public_${kind}_${proofId}`);
            const isPublic = isPublicBox && isPublicBox.checked ? '1' : '0';

            const formData = new FormData();
            formData.append('action', action);
            formData.append('kind', kind);
            formData.append('show_public', isPublic);
            if (comment) formData.append('comment', comment);

            const response = await fetch(`<?= base_url($tenant['slug']) ?>/admin/proofs/${proofId}/review`, {
                method: 'POST',
                body: formData,
                headers: { 'Accept': 'application/json' }
            });

            const data = await response.json();

            if (data.message || data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Processado!',
                    text: `A prova foi ${action === 'approved' ? 'aprovada' : (action === 'rejected' ? 'rejeitada' : 'enviada para ajustes')}.`,
                    timer: 1500,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });

                const card = document.getElementById(`proof-${kind}-${proofId}`);
                card.style.transform = 'scale(0.95)';
                card.style.opacity = '0';
                
                setTimeout(() => {
                    card.remove();
                    if (document.querySelectorAll('.proof-card').length === 0) {
                        location.reload();
                    }
                }, 300);
            } else {
                Swal.fire('Erro', data.error || 'Erro ao processar ação.', 'error');
            }
        } catch (err) {
            Swal.fire('Erro', 'Erro de conexão com o servidor.', 'error');
        }
    }

    async function requestChanges(proofId, kind = 'activity') {
        const { value: comment } = await Swal.fire({
            title: 'Solicitar Ajustes',
            input: 'textarea',
            inputLabel: 'O que o desbravador deve corrigir?',
            inputPlaceholder: 'Ex: A imagem enviada não comprova a realização...',
            inputAttributes: { 'aria-label': 'Mensagem para o desbravador' },
            showCancelButton: true,
            confirmButtonText: 'Enviar Solicitação',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#06b6d4'
        });

        if (comment) {
            reviewProof(proofId, 'requested_changes', kind, comment);
        }
    }

    function switchProofTab(tab) {
        document.getElementById('pending-container').style.display = tab === 'pending' ? 'block' : 'none';
        document.getElementById('history-container').style.display = tab === 'history' ? 'block' : 'none';
        
        document.getElementById('tab-pending').classList.toggle('active', tab === 'pending');
        document.getElementById('tab-history').classList.toggle('active', tab === 'history');
        
        if (tab === 'pending') {
            document.getElementById('tab-pending').style.background = 'var(--text-main)';
            document.getElementById('tab-pending').style.color = 'white';
            document.getElementById('tab-history').style.background = 'transparent';
            document.getElementById('tab-history').style.color = 'var(--text-muted)';
        } else {
            document.getElementById('tab-history').style.background = 'var(--text-main)';
            document.getElementById('tab-history').style.color = 'white';
            document.getElementById('tab-pending').style.background = 'transparent';
            document.getElementById('tab-pending').style.color = 'var(--text-muted)';
            loadProofHistory();
        }
    }

    // Initialize tab styles
    document.getElementById('tab-pending').style.background = 'var(--text-main)';
    document.getElementById('tab-pending').style.color = 'white';

    async function loadProofHistory() {
        const container = document.getElementById('history-container');
        const tenantSlug = '<?= $tenant['slug'] ?>';
        
        try {
            const resp = await fetch(`/${tenantSlug}/admin/proofs/history`);
            const data = await resp.json();
            
            if (!data.success) throw new Error(data.error || 'Erro ao carregar');
            
            if (data.history.length === 0) {
                container.innerHTML = `
                    <div style="padding: 60px; text-align: center; color: var(--text-muted);">
                        <iconify-icon icon="solar:folder-empty-bold-duotone" style="font-size: 64px; opacity: 0.2; margin-bottom: 16px;"></iconify-icon>
                        <p>Nenhuma avaliação encontrada no histórico.</p>
                    </div>
                `;
                return;
            }

            let html = '<div class="proofs-grid">';
            data.history.forEach(item => {
                const isPublic = item.show_public == 1;
                html += `
                    <div class="proof-card" style="opacity: 0.9;">
                        <div class="card-header">
                            <div class="user-info">
                                <div class="user-details">
                                    <h3 style="font-size: 0.95rem;">${item.user_name}</h3>
                                    <div class="submission-time">${item.reviewed_at}</div>
                                </div>
                            </div>
                            <span class="badge" style="background: ${item.status === 'approved' ? '#d1fae5' : '#fee2e2'}; color: ${item.status === 'approved' ? '#065f46' : '#991b1b'}">
                                ${item.status.toUpperCase()}
                            </span>
                        </div>
                        <div class="content-box" style="padding: 12px;">
                            <div style="font-weight: 700; font-size: 0.85rem; margin-bottom: 4px;">${item.item_title}</div>
                        </div>
                        
                        <div style="padding: 10px 16px; background: rgba(245, 158, 11, 0.05); border: 1px solid rgba(245, 158, 11, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: space-between; gap: 10px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <input type="checkbox" id="curation_${item.kind}_${item.id}" ${isPublic ? 'checked' : ''} style="width: 18px; height: 18px; accent-color: var(--accent-amber); cursor: pointer;">
                                <label for="curation_${item.kind}_${item.id}" style="font-weight: 700; font-size: 0.8rem; cursor: pointer;">Destaque no Hub</label>
                            </div>
                            <button class="btn" onclick="updateActivityCuration(${item.id}, '${item.kind}')" style="height: 32px; background: var(--accent-amber); color: white; padding: 0 10px; font-size: 0.75rem;">
                                <iconify-icon icon="solar:diskette-bold-duotone"></iconify-icon> Salvar
                            </button>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            container.innerHTML = html;
        } catch (e) {
            container.innerHTML = `<div style="padding:40px; color:red; text-align:center;">${e.message}</div>`;
        }
    }

    async function updateActivityCuration(id, kind) {
        const isPublicBox = document.getElementById(`curation_${kind}_${id}`) || document.getElementById(`public_${kind}_${id}`);
        const showPublic = isPublicBox && isPublicBox.checked;
        const tenantSlug = '<?= $tenant['slug'] ?>';
        
        const endpoint = kind === 'program' ? `aprovacoes/${id}/curation` : `proofs/${id}/curation`;

        try {
            const response = await fetch(`/${tenantSlug}/admin/${endpoint}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ show_public: showPublic })
            });

            const data = await response.json();
            if (data.success) {
                Swal.fire({
                    title: 'Atualizado!',
                    text: data.message,
                    icon: 'success',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000
                });
            } else {
                throw new Error(data.error || 'Erro ao atualizar');
            }
        } catch (e) {
            Swal.fire('Erro', e.message, 'error');
        }
    }
</script>

<?php require BASE_PATH . '/views/admin/partials/footer.php'; ?>


