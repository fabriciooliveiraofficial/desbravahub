<?php
/**
 * Email Composer - Vibrant Light Edition v1.0
 * High Contrast, Clean Writing Experience
 */
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    :root {
        --font-primary: 'Inter', sans-serif;
        --bg-body: #f1f5f9;
        --bg-card: #ffffff;
        --text-dark: #0f172a;
        --text-medium: #334155;
        --text-light: #64748b;
        
        --accent-primary: #06b6d4;
        --accent-hover: #0891b2;
        --border-color: #cbd5e1;
    }

    body {
        background-color: var(--bg-body) !important;
        font-family: var(--font-primary);
        color: var(--text-dark);
    }

    .composer-container {
        max-width: 900px;
        margin: 0 auto;
        padding-bottom: 60px;
        animation: fadeIn 0.4s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Header Alignments */
    .page-toolbar {
        margin-bottom: 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--text-medium);
        text-decoration: none;
        font-weight: 600;
        padding: 8px 16px;
        border-radius: 8px;
        transition: all 0.2s;
        background: white;
        border: 1px solid var(--border-color);
    }
    .btn-back:hover {
        background: #f8fafc;
        color: var(--text-dark);
    }

    .smtp-warning {
        background: #fffbeb;
        border: 1px solid #fcd34d;
        color: #92400e;
        padding: 16px;
        border-radius: 12px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 12px;
        font-weight: 500;
    }
    .smtp-warning a {
        color: #b45309;
        text-decoration: underline;
    }

    /* Composer Card */
    .composer-card {
        background: #ffffff;
        border-radius: 24px;
        border: 2px solid var(--border-color); /* Visible Border */
        box-shadow: 0 10px 30px -10px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    /* Recipient Ribbon */
    .recipient-ribbon {
        background: #f8fafc;
        border-bottom: 1px solid var(--border-color);
        padding: 20px 32px;
    }

    .ribbon-label {
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-light);
        font-weight: 700;
        margin-bottom: 12px;
        display: block;
    }

    .type-grid {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .type-option {
        /* Hidden Radio */
        position: relative;
        cursor: pointer;
    }
    
    .type-option input {
        display: none;
    }

    .type-card {
        padding: 10px 16px;
        border-radius: 12px;
        border: 2px solid #e2e8f0;
        background: white;
        color: var(--text-medium);
        font-weight: 600;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
    }

    .type-option input:checked + .type-card {
        border-color: var(--accent-primary);
        background: #ecfeff; /* Light Cyan */
        color: #0e7490; /* Dark Cyan */
    }

    .type-option:hover .type-card {
        border-color: #cbd5e1;
        transform: translateY(-2px);
    }

    /* Recipient Selectors - Specific Design */
    .selector-area {
        margin-top: 16px;
        padding: 16px;
        background: white;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        display: none; /* JS toggles this */
    }

    .selector-area.active {
        display: block;
        animation: slideDown 0.2s ease-out;
    }

    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-5px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .user-scroll {
        max-height: 200px;
        overflow-y: auto;
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 8px;
    }

    .user-pill {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 12px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        cursor: pointer;
        transition: background 0.1s;
    }
    .user-pill:hover { background: #f8fafc; }
    .user-pill input { accent-color: var(--accent-primary); }
    
    .pill-avatar {
        width: 24px; height: 24px;
        background: #e0f2fe; color: #0369a1;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.75rem; font-weight: 700;
    }
    .pill-name { font-size: 0.9rem; font-weight: 500; color: var(--text-dark); overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }

    .selector-select {
        width: 100%;
        padding: 12px;
        border-radius: 8px;
        border: 2px solid #e2e8f0;
        font-size: 1rem;
        color: var(--text-dark);
        outline: none;
    }
    .selector-select:focus { border-color: var(--accent-primary); }

    /* Main Content Area */
    .composer-main {
        padding: 32px;
    }

    .subject-line {
        margin-bottom: 24px;
    }
    
    .subject-input {
        width: 100%;
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-dark);
        border: none;
        border-bottom: 2px solid #e2e8f0;
        padding: 12px 0;
        outline: none;
        transition: border-color 0.2s;
    }
    .subject-input::placeholder { color: #cbd5e1; }
    .subject-input:focus { border-color: var(--accent-primary); }

    .editor-box {
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        transition: border-color 0.2s;
    }
    .editor-box:focus-within { border-color: var(--accent-primary); }

    .toolbar {
        background: #f8fafc;
        padding: 10px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        gap: 8px;
    }

    .tool-btn {
        background: white;
        border: 1px solid #cbd5e1;
        width: 32px; height: 32px;
        border-radius: 6px;
        display: flex; align-items: center; justify-content: center;
        color: var(--text-medium);
        cursor: pointer;
        font-weight: 700;
        font-size: 0.9rem;
        transition: all 0.1s;
    }
    .tool-btn:hover { background: #f1f5f9; color: var(--text-dark); }

    .editor-textarea {
        width: 100%;
        min-height: 400px;
        padding: 20px;
        border: none;
        outline: none;
        font-size: 1.05rem;
        line-height: 1.7;
        color: var(--text-dark);
        resize: vertical;
        font-family: inherit;
    }

    /* Actions Footer */
    .composer-footer {
        padding: 20px 32px;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 16px;
    }

    .btn-send {
        background: var(--accent-primary);
        color: white;
        padding: 12px 32px;
        border-radius: 100px;
        font-weight: 700;
        border: none;
        font-size: 1rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: transform 0.2s, box-shadow 0.2s;
        box-shadow: 0 4px 6px -1px rgba(6, 182, 212, 0.3);
    }
    .btn-send:hover {
        transform: translateY(-2px);
        background: var(--accent-hover);
        box-shadow: 0 10px 15px -3px rgba(6, 182, 212, 0.4);
    }
    
    .btn-draft {
        background: white;
        color: var(--text-medium);
        padding: 12px 24px;
        border-radius: 100px;
        font-weight: 600;
        border: 2px solid #e2e8f0;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-draft:hover { background: #f1f5f9; border-color: #cbd5e1; color: var(--text-dark); }

</style>

<div class="composer-container">
    <div class="page-toolbar">
        <div class="page-info">
            <a href="<?= base_url($tenant['slug'] . '/admin/email') ?>" class="btn-toolbar secondary">
                <span class="material-icons-round" style="font-size: 18px;">arrow_back</span>
                Voltar
            </a>
        </div>
    </div>

    <?php if (!$smtpConfigured): ?>
        <div class="smtp-warning">
            <span class="material-icons-round">warning</span>
            <span>SMTP não configurado. <a href="<?= base_url($tenant['slug'] . '/admin/email/settings') ?>">Configure agora</a> para enviar emails.</span>
        </div>
    <?php endif; ?>

    <form id="emailForm" class="composer-card">
        
        <!-- Recipient Ribbon -->
        <div class="recipient-ribbon">
            <span class="ribbon-label">Destinatários</span>
            <div class="type-grid">
                <label class="type-option">
                    <input type="radio" name="recipient_type_radio" value="individual" checked>
                    <div class="type-card">
                        <span class="material-icons-round" style="font-size: 18px;">person</span>
                        Individual
                    </div>
                </label>
                <label class="type-option">
                    <input type="radio" name="recipient_type_radio" value="role">
                    <div class="type-card">
                        <span class="material-icons-round" style="font-size: 18px;">badge</span>
                        Por Cargo
                    </div>
                </label>
                <label class="type-option">
                    <input type="radio" name="recipient_type_radio" value="unit">
                    <div class="type-card">
                        <span class="material-icons-round" style="font-size: 18px;">grid_view</span>
                        Por Unidade
                    </div>
                </label>
                <label class="type-option">
                    <input type="radio" name="recipient_type_radio" value="all">
                    <div class="type-card">
                        <span class="material-icons-round" style="font-size: 18px;">campaign</span>
                        Todos
                    </div>
                </label>
            </div>

            <!-- Dynamic Selectors -->
            <!-- Individual -->
            <div class="selector-area active" id="selector-individual">
                <div class="user-scroll">
                    <?php foreach ($users as $u): ?>
                        <label class="user-pill">
                            <input type="checkbox" name="recipients[]" value="<?= $u['id'] ?>">
                            <div class="pill-avatar"><?= strtoupper(substr($u['name'], 0, 1)) ?></div>
                            <div class="pill-name" title="<?= htmlspecialchars($u['email']) ?>"><?= htmlspecialchars($u['name']) ?></div>
                        </label>
                    <?php endforeach; ?>
                </div>
                <div style="margin-top: 10px; font-weight: 600; color: var(--accent-primary); font-size: 0.9rem;" class="selected-count">
                    0 pessoas selecionadas
                </div>
            </div>

            <!-- Role -->
            <div class="selector-area" id="selector-role">
                <select class="selector-select" name="role_recipient">
                    <option value="">Selecione um Cargo...</option>
                    <?php foreach ($roles as $r): ?>
                        <option value="<?= htmlspecialchars($r['name']) ?>">
                            <?= htmlspecialchars($r['display_name']) ?> (<?= $r['user_count'] ?> usuários)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Unit -->
            <div class="selector-area" id="selector-unit">
                <select class="selector-select" name="unit_recipient">
                    <option value="">Selecione uma Unidade...</option>
                    <?php foreach ($units as $unit): ?>
                        <option value="<?= $unit['id'] ?>"><?= htmlspecialchars($unit['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- All -->
            <div class="selector-area" id="selector-all">
                <div style="text-align: center; color: var(--text-medium); font-weight: 500;">
                    <span style="font-size: 2rem; display: block; margin-bottom: 8px;">📢</span>
                    Você está prestes a enviar uma mensagem para <strong>todos os <?= count($users) ?> membros</strong> do clube.
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="composer-main">
            <div class="subject-line">
                <input type="text" name="subject" class="subject-input" placeholder="Assunto do Email" required>
            </div>

            <div class="editor-box">
                <div class="toolbar">
                    <button type="button" class="tool-btn" onclick="insertTag('b')" title="Negrito">B</button>
                    <button type="button" class="tool-btn" onclick="insertTag('i')" title="Itálico"><i>I</i></button>
                    <button type="button" class="tool-btn" onclick="insertTag('u')" title="Sublinhado"><u>U</u></button>
                    <div style="width: 1px; background: #e2e8f0; margin: 0 4px;"></div>
                    <button type="button" class="tool-btn" onclick="insertLink()" title="Link">🔗</button>
                    <button type="button" class="tool-btn" onclick="insertHeading()" title="Título">H1</button>
                </div>
                <textarea name="body" class="editor-textarea" placeholder="Escreva sua mensagem aqui... Comece com uma saudação calorosa!" required></textarea>
            </div>
        </div>

        <input type="hidden" name="recipient_type" id="recipientType" value="individual">

        <!-- Footer Actions -->
        <div class="composer-footer">
            <button type="button" class="btn-draft" onclick="saveDraft()">
                <span class="material-icons-round" style="font-size: 18px; vertical-align: middle;">save</span>
                Salvar Rascunho
            </button>
            <button type="submit" class="btn-send" <?= !$smtpConfigured ? 'disabled' : '' ?>>
                Enviar Mensagem
                <span class="material-icons-round">send</span>
            </button>
        </div>

    </form>
</div>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
<script src="<?= asset_url('js/toast.js') ?>"></script>
<script>
{
    window.tenantSlug = '<?= $tenant['slug'] ?>';
    
    // Type Switch Logic
    const typeRadios = document.querySelectorAll('input[name="recipient_type_radio"]');
    const recipientTypeInput = document.getElementById('recipientType');
    const selectorAreas = document.querySelectorAll('.selector-area');

    typeRadios.forEach(radio => {
        radio.addEventListener('change', (e) => {
            const type = e.target.value;
            recipientTypeInput.value = type;
            
            // Hide all selectors
            selectorAreas.forEach(area => area.classList.remove('active'));
            
            // Show new selector
            const targetSelector = document.getElementById(`selector-${type}`);
            if (targetSelector) {
                targetSelector.classList.add('active');
            }
        });
    });

    // Count Selected
    const userCheckboxes = document.querySelectorAll('input[name="recipients[]"]');
    const countDisplay = document.querySelector('.selected-count');

    userCheckboxes.forEach(cb => {
        cb.addEventListener('change', () => {
            const count = document.querySelectorAll('input[name="recipients[]"]:checked').length;
            countDisplay.innerText = `${count} pessoas selecionadas`;
        });
    });

    // Editor Helpers
    function insertTag(tag) {
        const textarea = document.querySelector('[name="body"]');
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const text = textarea.value;
        const selected = text.substring(start, end);
        textarea.value = text.substring(0, start) + `<${tag}>${selected}</${tag}>` + text.substring(end);
        textarea.focus();
    }

    // Attach to window so onclick handlers work
    window.insertTag = insertTag;

    function insertLink() {
        const url = prompt('URL do link:');
        if (url) {
            const textarea = document.querySelector('[name="body"]');
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const text = textarea.value;
            const selected = text.substring(start, end) || 'clique aqui';
            textarea.value = text.substring(0, start) + `<a href="${url}">${selected}</a>` + text.substring(end);
            textarea.focus();
        }
    }
    window.insertLink = insertLink;

    function insertHeading() {
        const textarea = document.querySelector('[name="body"]');
        const start = textarea.selectionStart;
        const text = textarea.value;
        textarea.value = text.substring(0, start) + '<h2></h2>' + text.substring(start);
        textarea.focus();
    }
    window.insertHeading = insertHeading;

    // Submit Logic
    const emailForm = document.getElementById('emailForm');
    if (emailForm) {
        emailForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.querySelector('.btn-send');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = 'Enviando...';

            const formData = new FormData(e.target);
            formData.append('action', 'send');
            
            // Handle arrays manually if needed for different types
            const currentType = recipientTypeInput.value;
            
            try {
                const response = await fetch(`/${window.tenantSlug}/admin/email/send`, {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                
                if (data.success) {
                    if (window.showToast) {
                        window.showToast('Email enviado com sucesso!', 'success');
                    }
                    setTimeout(() => {
                        window.location.href = `/${window.tenantSlug}/admin/email/inbox`;
                    }, 1000);
                } else {
                    if (window.showToast) {
                        window.showToast(data.error, 'error', 'Erro no envio');
                    } else {
                        alert(data.error);
                    }
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            } catch (err) {
                if (window.showToast) {
                    window.showToast('Erro de conexão com o servidor', 'error', 'Erro de Rede');
                } else {
                    alert('Erro de conexão');
                }
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        });
    }

    async function saveDraft() {
        const formData = new FormData(document.getElementById('emailForm'));
        formData.append('action', 'draft');
        
        try {
            const response = await fetch(`/${window.tenantSlug}/admin/email/send`, {method: 'POST', body: formData});
            const data = await response.json();
            if(data.success) {
                if (window.showToast) window.showToast('Rascunho salvo com sucesso!', 'success');
            } else {
                 if (window.showToast) window.showToast(data.error, 'error');
            }
        } catch(e) { 
            if (window.showToast) window.showToast('Erro ao salvar rascunho', 'error');
        }
    }
    window.saveDraft = saveDraft;
}
</script>

