<?php
/**
 * Provas Review - Vibrant Light Edition v1.0
 * High Contrast, Efficiency-Focused Task Queue
 */
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

<style>
    :root {
        --font-primary: 'Inter', sans-serif;
        --bg-body: #f1f5f9;
        --bg-card: #ffffff;
        --text-dark: #0f172a;
        --text-medium: #334155;
        --text-light: #475569;
        
        --accent-approve: #10b981;
        --accent-reject: #ef4444;
        --accent-changes: #3b82f6;
        --border-color: #cbd5e1;
    }

    body {
        background-color: var(--bg-body) !important;
        font-family: var(--font-primary);
        color: var(--text-dark);
    }

    .proofs-wrapper {
        max-width: 1000px;
        margin: 0 auto;
        padding-bottom: 60px;
        animation: fadeIn 0.4s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Empty State */
    .empty-state-card {
        background: white;
        border-radius: 32px;
        border: 2px solid var(--border-color);
        padding: 80px 40px;
        text-align: center;
        box-shadow: 0 10px 25px -10px rgba(0,0,0,0.05);
    }

    .empty-icon-box {
        width: 100px;
        height: 100px;
        background: #dcfce7;
        color: #15803d;
        border-radius: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 24px;
        font-size: 3rem;
    }

    .empty-state-card h3 {
        font-size: 1.75rem;
        font-weight: 800;
        margin: 0 0 8px 0;
        color: #000;
    }

    .empty-state-card p {
        color: var(--text-medium);
        font-weight: 500;
        font-size: 1.1rem;
    }

    /* Proof Cards */
    .proofs-grid {
        display: grid;
        gap: 24px;
    }

    .proof-card {
        background: white;
        border-radius: 24px;
        border: 2px solid var(--border-color);
        padding: 32px;
        display: flex;
        flex-direction: column;
        gap: 24px;
        transition: all 0.2s;
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
    }

    .proof-card:hover { border-color: #94a3b8; }

    /* Kind Badge Strip */
    .kind-badge-side {
        position: absolute;
        top: 0;
        left: 0;
        bottom: 0;
        width: 8px;
    }

    /* Header Section */
    .proof-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }

    .header-info h3 {
        font-size: 1.35rem;
        font-weight: 800;
        margin: 0 0 8px 0;
        color: #000;
        letter-spacing: -0.025em;
    }

    .submitter-box {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .avatar-mini {
        width: 36px;
        height: 36px;
        background: #f1f5f9;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        color: var(--text-dark);
        border: 1px solid var(--border-color);
    }

    .submitted-text {
        font-size: 0.95rem;
        color: var(--text-medium);
        font-weight: 500;
    }

    .submitted-text strong { color: var(--text-dark); }

    /* Content Box */
    .proof-content-area {
        background: #f8fafc;
        border: 2px solid #e2e8f0;
        border-radius: 16px;
        padding: 24px;
        position: relative;
    }

    .content-label {
        position: absolute;
        top: -12px;
        left: 20px;
        background: white;
        padding: 0 10px;
        font-size: 0.75rem;
        font-weight: 800;
        color: var(--text-light);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border: 2px solid #e2e8f0;
        border-radius: 100px;
    }

    .text-content {
        font-size: 1.05rem;
        line-height: 1.6;
        color: var(--text-dark);
        white-space: pre-wrap;
    }

    .link-content {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 700;
        color: #2563eb;
        text-decoration: none;
        word-break: break-all;
    }
    .link-content:hover { text-decoration: underline; }

    /* Actions */
    .proof-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .btn-review {
        flex: 1;
        min-width: 160px;
        padding: 14px 24px;
        border-radius: 14px;
        font-weight: 800;
        font-size: 0.95rem;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: all 0.2s;
    }

    .btn-approve {
        background: var(--accent-approve);
        color: white;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
    }
    .btn-approve:hover { transform: translateY(-2px); box-shadow: 0 8px 16px rgba(16, 185, 129, 0.3); }

    .btn-reject {
        background: #fee2e2;
        color: var(--accent-reject);
        border: 2px solid #fecaca;
    }
    .btn-reject:hover { background: #fecaca; }

    .btn-changes {
        background: #dbeafe;
        color: var(--accent-changes);
        border: 2px solid #bfdbfe;
    }
    .btn-changes:hover { background: #bfdbfe; }

    /* Modal Styling Overlay */
    #inputModalOverlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.85);
        backdrop-filter: blur(8px);
        z-index: 2000;
        align-items: center;
        justify-content: center;
    }

    .custom-modal {
        background: white;
        border-radius: 24px;
        border: 2px solid var(--border-color);
        width: 100%;
        max-width: 500px;
        padding: 32px;
        animation: modalScale 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    @keyframes modalScale {
        from { transform: scale(0.9); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }

    @media (max-width: 768px) {
        .proof-header { flex-direction: column; gap: 16px; }
        .proof-actions { flex-direction: column; }
    }
</style>

<div class="proofs-wrapper">
    
    <?php if (empty($proofs)): ?>
        <div class="empty-state-card">
            <div class="empty-icon-box">
                <span class="material-icons-round">task_alt</span>
            </div>
            <h3>Nenhuma prova pendente!</h3>
            <p>Você revisou tudo por enquanto. Ótimo trabalho!</p>
        </div>
    <?php else: ?>

        <div class="proofs-grid">
            <?php foreach ($proofs as $proof): ?>
                <div class="proof-card" id="proof-<?= $proof['kind'] ?>-<?= $proof['id'] ?>">
                    <!-- Side Kind Indicator -->
                    <div class="kind-badge-side" style="background: <?= $proof['kind'] === 'program' ? '#06b6d4' : '#6366f1' ?>;"></div>

                    <!-- Header -->
                    <div class="proof-header">
                        <div class="header-info">
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                                <span style="font-size: 0.7rem; font-weight: 900; color: <?= $proof['kind'] === 'program' ? '#06b6d4' : '#6366f1' ?>; text-transform: uppercase; letter-spacing: 0.1em;">
                                    <?= $proof['kind'] === 'program' ? '🚀 Programa' : '🎯 Atividade' ?>
                                </span>
                            </div>
                            <h3><?= htmlspecialchars($proof['item_title']) ?></h3>
                            <div class="submitter-box">
                                <div class="avatar-mini">
                                    <?= strtoupper(substr($proof['user_name'], 0, 1)) ?>
                                </div>
                                <div class="submitted-text">
                                    Enviado por <strong><?= htmlspecialchars($proof['user_name']) ?></strong>
                                    <div style="font-size: 0.8rem; color: var(--text-light);">
                                        <?= date('d/m/Y \à\s H:i', strtotime($proof['submitted_at'])) ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Type Badge -->
                        <?php 
                            $typeIcon = $proof['type'] === 'url' ? 'link' : ($proof['type'] === 'upload' ? 'file_present' : 'notes');
                            $typeLabel = $proof['type'] === 'text' ? 'RESPOSTA' : ($proof['type'] === 'url' ? 'LINK' : 'ARQUIVO');
                        ?>
                        <div style="display: flex; align-items: center; gap: 6px; padding: 6px 14px; background: #f1f5f9; border-radius: 100px; font-size: 0.75rem; font-weight: 800; border: 1px solid var(--border-color);">
                            <span class="material-icons-round" style="font-size: 16px;"><?= $typeIcon ?></span>
                            <?= $typeLabel ?>
                        </div>
                    </div>

                    <!-- Content Area -->
                    <div class="proof-content-area">
                        <span class="content-label">Evidência enviada</span>
                        
                        <?php if ($proof['type'] === 'url'): ?>
                            <a href="<?= htmlspecialchars($proof['content']) ?>" target="_blank" class="link-content">
                                <span class="material-icons-round">open_in_new</span>
                                <?= htmlspecialchars($proof['content']) ?>
                            </a>
                        <?php elseif ($proof['type'] === 'upload'): ?>
                            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <span class="material-icons-round" style="font-size: 32px; color: var(--text-light);">insert_drive_file</span>
                                    <div style="font-weight: 700;">Arquivo de Mídia</div>
                                </div>
                                <a href="<?= base_url($proof['content']) ?>" target="_blank" class="btn-review btn-changes" style="flex: 0; min-width: auto;">
                                    Visualizar Arquivo
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="text-content">"<?= htmlspecialchars($proof['content']) ?>"</div>
                        <?php endif; ?>
                    </div>

                    <!-- Actions -->
                    <div class="proof-actions">
                        <button class="btn-review btn-approve" onclick="reviewProof(<?= $proof['id'] ?>, 'approved', '<?= $proof['kind'] ?>')">
                            <span class="material-icons-round">check_circle</span>
                            Aprovar Prova
                        </button>
                        <button class="btn-review btn-changes" onclick="requestChanges(<?= $proof['id'] ?>, '<?= $proof['kind'] ?>')">
                            <span class="material-icons-round">edit_note</span>
                            Pedir Ajustes
                        </button>
                        <button class="btn-review btn-reject" onclick="reviewProof(<?= $proof['id'] ?>, 'rejected', '<?= $proof['kind'] ?>')">
                            <span class="material-icons-round">cancel</span>
                            Rejeitar
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>
</div>

<!-- Custom Input Modal -->
<div id="inputModalOverlay">
    <div class="custom-modal">
        <h3 id="iModalTitle" style="margin:0 0 12px; font-size:1.5rem; font-weight:900; color:#000; letter-spacing:-0.01em;"></h3>
        <p id="iModalMsg" style="color:var(--text-medium); margin:0 0 20px; font-weight:500;"></p>
        
        <label style="display:block; font-size:0.85rem; font-weight:800; text-transform:uppercase; color:var(--text-light); margin-bottom:8px;">Mensagem para o desbravador</label>
        <textarea id="iModalInput" rows="4" placeholder="Explique o que precisa ser corrigido..."
            style="width:100%; padding:16px; background:#f8fafc; border:2px solid var(--border-color); border-radius:16px; color:var(--text-dark); font-size:1rem; font-family:inherit; resize:vertical; outline:none; transition:border-color 0.2s;"></textarea>
        
        <div style="display:flex; gap:12px; justify-content:flex-end; margin-top:24px;">
            <button onclick="closeInputModal(null)"
                style="padding:14px 28px; border-radius:100px; font-weight:800; cursor:pointer; border:2px solid var(--border-color); background:white; color:var(--text-dark);">
                Cancelar
            </button>
            <button id="iModalOk" onclick="submitInputModal()"
                style="padding:14px 28px; border-radius:100px; font-weight:800; cursor:pointer; border:none; background:var(--accent-changes); color:white; box-shadow:0 4px 12px rgba(59, 130, 246, 0.3);">
            </button>
        </div>
    </div>
</div>

<script>
    var toast = window.toast = window.toast || new (window.ToastNotification || ToastNotification)();

    async function reviewProof(proofId, action, kind = 'activity', comment = null) {
        try {
            const formData = new FormData();
            formData.append('action', action);
            formData.append('kind', kind);
            if (comment) formData.append('comment', comment);

            const response = await fetch(`<?= base_url($tenant['slug']) ?>/admin/proofs/${proofId}/review`, {
                method: 'POST',
                body: formData,
                headers: { 'Accept': 'application/json' }
            });

            const data = await response.json();

            if (data.message || data.success) {
                toast.success('Sucesso', `Prova ${action === 'approved' ? 'aprovada' : 'processada'}`);
                document.getElementById(`proof-${kind}-${proofId}`).style.opacity = '0';
                setTimeout(() => {
                    document.getElementById(`proof-${kind}-${proofId}`).remove();
                    if (document.querySelectorAll('.proof-card').length === 0) {
                        location.reload();
                    }
                }, 300);
            } else {
                toast.error('Erro', data.error);
            }
        } catch (err) {
            toast.error('Erro', 'Erro de conexão');
        }
    }

    function requestChanges(proofId, kind = 'activity') {
        showInputModal({
            title: 'Solicitar Alterações',
            message: 'O que o desbravador precisa corrigir nesta evidência?',
            placeholder: 'Ex: A imagem está muito escura, tente tirar outra com mais luz...',
            okText: 'Enviar Solicitação'
        }).then(comment => {
            if (comment) {
                reviewProof(proofId, 'requested_changes', kind, comment);
            }
        });
    }

    function showInputModal(options) {
        return new Promise((resolve) => {
            const { title, message, placeholder = '', okText = 'OK' } = options;
            const modal = document.getElementById('inputModalOverlay');
            
            document.getElementById('iModalTitle').textContent = title;
            document.getElementById('iModalMsg').textContent = message;
            document.getElementById('iModalInput').placeholder = placeholder;
            document.getElementById('iModalInput').value = '';
            document.getElementById('iModalOk').textContent = okText;

            window.inputModalResolve = resolve;
            modal.style.display = 'flex';
            document.getElementById('iModalInput').focus();
        });
    }

    function closeInputModal(value) {
        document.getElementById('inputModalOverlay').style.display = 'none';
        if (window.inputModalResolve) {
            window.inputModalResolve(value);
            window.inputModalResolve = null;
        }
    }

    function submitInputModal() {
        const value = document.getElementById('iModalInput').value.trim();
        closeInputModal(value || null);
    }
</script>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
<?php require BASE_PATH . '/views/admin/partials/footer.php'; ?>

