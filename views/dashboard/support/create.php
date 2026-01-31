<?php
/**
 * Criar Novo Chamado de Suporte
 */

use App\Core\App;

$tenant = App::tenant();
$user = App::user();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Chamado | <?= htmlspecialchars($tenant['name']) ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
    <style>
        .form-container {
            max-width: 600px;
            margin: 0 auto;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 8px;
            color: var(--text);
        }

        input,
        textarea,
        select {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.02);
            color: var(--text);
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.3s;
        }

        input:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 217, 255, 0.1);
        }

        textarea {
            min-height: 150px;
            resize: vertical;
        }

        select {
            cursor: pointer;
        }

        select option {
            background: var(--dark-bg);
            color: var(--text);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .file-upload {
            border: 2px dashed var(--border);
            border-radius: 12px;
            padding: 24px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .file-upload:hover {
            border-color: var(--primary);
        }

        .file-upload input {
            display: none;
        }

        .file-upload-icon {
            font-size: 32px;
            margin-bottom: 8px;
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
            gap: 6px;
            padding: 6px 12px;
            background: rgba(0, 217, 255, 0.1);
            border-radius: 20px;
            font-size: 0.85rem;
        }

        .file-remove {
            cursor: pointer;
            color: #ff6b6b;
        }

        .btn-submit {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: var(--dark-bg);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0, 217, 255, 0.4);
        }

        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--text-muted);
            text-decoration: none;
            margin-bottom: 20px;
        }

        .back-link:hover {
            color: var(--primary);
        }
    </style>
</head>

<body>
    <?php require BASE_PATH . '/views/dashboard/partials/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <a href="<?= base_url($tenant['slug'] . '/suporte') ?>" class="back-link">← Voltar</a>

            <div class="form-container">
                <h1 class="page-title">➕ Novo Chamado</h1>

                <form id="ticketForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="category">Categoria</label>
                            <select id="category" name="category" required>
                                <option value="question">❓ Dúvida</option>
                                <option value="bug">🐛 Bug / Erro</option>
                                <option value="suggestion">💡 Sugestão</option>
                                <option value="improvement">🚀 Melhoria</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="priority">Prioridade</label>
                            <select id="priority" name="priority" required>
                                <option value="low">🟢 Baixa</option>
                                <option value="medium" selected>🟡 Média</option>
                                <option value="high">🔴 Alta</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="subject">Assunto</label>
                        <input type="text" id="subject" name="subject" placeholder="Resumo do problema ou solicitação"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="description">Descrição Detalhada</label>
                        <textarea id="description" name="description"
                            placeholder="Descreva o problema em detalhes. Inclua passos para reproduzir, se for um bug."
                            required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="related_module">Módulo Relacionado (opcional)</label>
                        <select id="related_module" name="related_module">
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
                            <div class="file-upload-icon">📎</div>
                            <p>Clique ou arraste arquivos aqui</p>
                            <p style="font-size: 0.8rem; color: var(--text-muted);">PNG, JPG, PDF, MP4 (máx. 10MB)</p>
                            <input type="file" id="attachments" name="attachments[]" multiple
                                accept="image/*,video/*,.pdf">
                        </div>
                        <div class="file-list" id="fileList"></div>
                    </div>

                    <button type="submit" class="btn-submit" id="submitBtn">
                        ✉️ Enviar Chamado
                    </button>
                </form>
            </div>
        </div>
    </main>

    <?php require BASE_PATH . '/views/dashboard/partials/nav.php'; ?>

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
                item.innerHTML = `📄 ${file.name}`;
                fileList.appendChild(item);
            }
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            submitBtn.disabled = true;
            submitBtn.textContent = 'Enviando...';

            const formData = new FormData(form);

            try {
                const response = await fetch('<?= base_url($tenant['slug'] . '/suporte') ?>', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    alert(data.error || 'Erro ao enviar');
                    submitBtn.disabled = false;
                    submitBtn.textContent = '✉️ Enviar Chamado';
                }
            } catch (err) {
                alert('Erro de conexão');
                submitBtn.disabled = false;
                submitBtn.textContent = '✉️ Enviar Chamado';
            }
        });
    </script>
</body>

</html>