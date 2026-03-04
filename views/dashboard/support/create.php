<?php
/**
 * Criar Novo Chamado de Suporte
 */
?>

<style>
    .support-form-wrapper {
        padding: 20px;
        max-width: 800px;
        margin: 0 auto;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--accent-cyan);
        text-decoration: none;
        margin-bottom: 24px;
        font-family: 'JetBrains Mono', monospace;
        font-weight: 700;
        font-size: 0.9rem;
        transition: all 0.3s;
    }

    .back-link:hover {
        transform: translateX(-4px);
        text-shadow: 0 0 10px var(--accent-cyan);
    }

    .form-container {
        background: var(--hud-glass-panel);
        border: 1px solid var(--hud-glass-border);
        border-radius: 20px;
        padding: 30px;
        backdrop-filter: blur(15px);
    }

    .page-title {
        font-size: 1.6rem;
        font-weight: 800;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 12px;
        color: var(--text);
    }

    .form-group {
        margin-bottom: 20px;
    }

    label {
        display: block;
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 10px;
        color: var(--text-muted);
    }

    input,
    textarea,
    select {
        width: 100%;
        padding: 14px 16px;
        border: 1px solid var(--hud-glass-border);
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.03);
        color: var(--text);
        font-size: 1rem;
        font-family: inherit;
        transition: all 0.3s;
    }

    input:focus,
    textarea:focus,
    select:focus {
        outline: none;
        border-color: var(--accent-cyan);
        background: rgba(0, 217, 255, 0.05);
        box-shadow: 0 0 15px rgba(0, 217, 255, 0.1);
    }

    textarea {
        min-height: 150px;
        resize: vertical;
    }

    select {
        cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='rgba(255,255,255,0.5)' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 16px center;
        background-size: 18px;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    @media (max-width: 600px) {
        .form-row {
            grid-template-columns: 1fr;
        }
    }

    .file-upload {
        border: 2px dashed var(--hud-glass-border);
        border-radius: 12px;
        padding: 30px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
        background: rgba(255, 255, 255, 0.01);
    }

    .file-upload:hover {
        border-color: var(--accent-cyan);
        background: rgba(0, 217, 255, 0.02);
    }

    .file-upload input {
        display: none;
    }

    .file-upload-icon {
        font-size: 32px;
        margin-bottom: 12px;
        color: var(--accent-cyan);
    }

    .file-list {
        margin-top: 12px;
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .file-item {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        background: rgba(0, 217, 255, 0.1);
        border: 1px solid rgba(0, 217, 255, 0.2);
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--accent-cyan);
    }

    .btn-submit {
        width: 100%;
        margin-top: 10px;
    }

    .btn-submit:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
    }
</style>

<div class="support-form-wrapper">
    <a href="<?= base_url($tenant['slug'] . '/suporte') ?>" class="back-link">
        <span class="material-icons-round" style="font-size: 1.2rem; vertical-align: middle;">arrow_back</span>
        VOLTAR PARA CHAMADOS
    </a>

    <div class="form-container">
        <h1 class="page-title">
            <span class="material-icons-round">add_circle_outline</span>
            Novo Chamado
        </h1>

        <form id="ticketForm">
            <div class="form-row">
                <div class="form-group">
                    <label for="category">Categoria</label>
                    <select id="category" name="category" class="hud-input" required>
                        <option value="question">❓ Dúvida</option>
                        <option value="bug">🐛 Bug / Erro</option>
                        <option value="suggestion">💡 Sugestão</option>
                        <option value="improvement">🚀 Melhoria</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="priority">Prioridade</label>
                    <select id="priority" name="priority" class="hud-input" required>
                        <option value="low">🟢 Baixa</option>
                        <option value="medium" selected>🟡 Média</option>
                        <option value="high">🔴 Alta</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="subject">Assunto</label>
                <input type="text" id="subject" name="subject" class="hud-input" placeholder="Resumo do problema ou solicitação" required>
            </div>

            <div class="form-group">
                <label for="description">Descrição Detalhada</label>
                <textarea id="description" name="description" class="hud-input" placeholder="Descreva o problema em detalhes. Inclua passos para reproduzir, se for um bug." required></textarea>
            </div>

            <div class="form-group">
                <label for="related_module">Módulo Relacionado (opcional)</label>
                <select id="related_module" name="related_module" class="hud-input">
                    <option value="">Selecione...</option>
                    <option value="dashboard">Dashboard</option>
                    <option value="atividades">Especialidades</option>
                    <option value="provas">Provas</option>
                    <option value="eventos">Eventos</option>
                    <option value="conquistas">Conquistas</option>
                    <option value="perfil">Perfil</option>
                    <option value="admin">Painel Admin</option>
                    <option value="login">Login/Registro</option>
                    <option value="outro">Outro</option>
                </select>
            </div>

            <div class="form-group">
                <label>Anexos (opcional)</label>
                <div class="file-upload" id="fileUpload">
                    <div class="file-upload-icon">
                        <span class="material-icons-round" style="font-size: 48px;">cloud_upload</span>
                    </div>
                    <p style="font-weight: 700; color: var(--text);">Fazer upload de arquivos</p>
                    <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 4px;">PNG, JPG, PDF, MP4 (máx. 10MB)</p>
                    <input type="file" id="attachments" name="attachments[]" multiple accept="image/*,video/*,.pdf">
                </div>
                <div class="file-list" id="fileList"></div>
            </div>

            <button type="submit" class="hud-btn primary btn-submit" id="submitBtn">
                Enviar Chamado
            </button>
        </form>
    </div>
</div>

<script>
    const form = document.getElementById('ticketForm');
    const fileUpload = document.getElementById('fileUpload');
    const fileInput = document.getElementById('attachments');
    const fileList = document.getElementById('fileList');
    const submitBtn = document.getElementById('submitBtn');

    fileUpload.addEventListener('click', () => fileInput.click());

    fileInput.addEventListener('change', updateFileList);

    function updateFileList() {
        fileList.innerHTML = '';
        for (const file of fileInput.files) {
            const item = document.createElement('span');
            item.className = 'file-item';
            item.innerHTML = `<span class="material-icons-round" style="font-size: 14px;">attachment</span> ${file.name}`;
            fileList.appendChild(item);
        }
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="material-icons-round spin" style="font-size: 1.2rem; vertical-align: middle;">sync</span> Enviando...';

        const formData = new FormData(form);

        try {
            const response = await fetch('<?= base_url($tenant['slug'] . '/suporte') ?>', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                if (typeof toast !== 'undefined') toast.success('Sucesso', data.message);
                setTimeout(() => window.location.href = data.redirect, 1000);
            } else {
                if (typeof toast !== 'undefined') toast.error('Erro', data.error || 'Erro ao enviar');
                else alert(data.error || 'Erro ao enviar');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Enviar Chamado';
            }
        } catch (err) {
            if (typeof toast !== 'undefined') toast.error('Erro', 'Erro de conexão');
            else alert('Erro de conexão');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Enviar Chamado';
        }
    });
</script>