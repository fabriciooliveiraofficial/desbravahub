<?php
/**
 * Cadastro de Clube - Formulário de Registro
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Clube | DesbravaHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #00d9ff;
            --secondary: #00ff88;
            --dark-bg: #0a0a1a;
            --card-bg: rgba(255, 255, 255, 0.03);
            --border: rgba(255, 255, 255, 0.08);
            --text: #e0e0e0;
            --text-muted: #888;
            --error: #ff6b6b;
            --success: #00ff88;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--dark-bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            background:
                radial-gradient(ellipse at 30% 20%, rgba(0, 217, 255, 0.1) 0%, transparent 50%),
                radial-gradient(ellipse at 70% 80%, rgba(0, 255, 136, 0.08) 0%, transparent 50%),
                var(--dark-bg);
        }

        .back-link {
            position: absolute;
            top: 24px;
            left: 24px;
            color: var(--text-muted);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: color 0.3s;
        }

        .back-link:hover {
            color: var(--primary);
        }

        .register-container {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 48px;
            max-width: 480px;
            width: 100%;
        }

        .logo {
            text-align: center;
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 32px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        h1 {
            font-size: 1.8rem;
            text-align: center;
            margin-bottom: 8px;
        }

        .subtitle {
            text-align: center;
            color: var(--text-muted);
            margin-bottom: 32px;
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

        input {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.02);
            color: var(--text);
            font-size: 1rem;
            transition: all 0.3s;
        }

        input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 217, 255, 0.1);
        }

        input::placeholder {
            color: var(--text-muted);
        }

        .slug-preview {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-top: 6px;
        }

        .slug-preview span {
            color: var(--primary);
        }

        .section-divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 28px 0;
        }

        .section-divider::before,
        .section-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        .section-divider span {
            font-size: 0.8rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn {
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

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0, 217, 255, 0.4);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .error-list {
            background: rgba(255, 107, 107, 0.1);
            border: 1px solid var(--error);
            border-radius: 10px;
            padding: 14px;
            margin-bottom: 20px;
        }

        .error-list li {
            color: var(--error);
            font-size: 0.9rem;
            margin-left: 16px;
        }

        .success-message {
            background: rgba(0, 255, 136, 0.1);
            border: 1px solid var(--success);
            border-radius: 10px;
            padding: 20px;
            text-align: center;
        }

        .success-message h3 {
            color: var(--success);
            margin-bottom: 8px;
        }

        .success-message a {
            color: var(--primary);
        }

        @media (max-width: 520px) {
            .register-container {
                padding: 32px 24px;
            }
        }
    </style>
</head>

<body>
    <a href="<?= base_url() ?>" class="back-link">← Voltar</a>

    <div class="register-container">
        <div class="logo">⚡ DesbravaHub</div>

        <h1>Cadastrar Clube</h1>
        <p class="subtitle">Crie o ambiente do seu clube de Desbravadores</p>

        <form id="registerForm">
            <div class="form-group">
                <label for="club_name">Nome do Clube</label>
                <input type="text" id="club_name" name="club_name" placeholder="Ex: Clube Estrela Guia" required>
            </div>

            <div class="form-group">
                <label for="slug">URL do Clube (slug)</label>
                <input type="text" id="slug" name="slug" placeholder="ex: estrela-guia" pattern="[a-z0-9-]+" required>
                <p class="slug-preview">Seu clube ficará em: <span id="slugPreview">seuclube</span>.desbravahub.com.br
                </p>
            </div>

            <div class="section-divider">
                <span>Administrador</span>
            </div>

            <div class="form-group">
                <label for="admin_name">Nome do Administrador</label>
                <input type="text" id="admin_name" name="admin_name" placeholder="Seu nome completo" required>
            </div>

            <div class="form-group">
                <label for="admin_email">Email</label>
                <input type="email" id="admin_email" name="admin_email" placeholder="seu@email.com" required>
            </div>

            <div class="form-group">
                <label for="admin_password">Senha</label>
                <input type="password" id="admin_password" name="admin_password" placeholder="Mínimo 8 caracteres"
                    minlength="8" required>
            </div>

            <ul class="error-list" id="errorList" style="display: none;"></ul>

            <button type="submit" class="btn" id="submitBtn">
                ✨ Cadastrar Clube
            </button>
        </form>

        <div class="success-message" id="successMessage" style="display: none;">
            <h3>🎉 Clube Cadastrado!</h3>
            <p>Seu clube foi criado com sucesso. Você será redirecionado...</p>
        </div>
    </div>

    <script>
        const form = document.getElementById('registerForm');
        const slugInput = document.getElementById('slug');
        const slugPreview = document.getElementById('slugPreview');
        const errorList = document.getElementById('errorList');
        const successMessage = document.getElementById('successMessage');
        const submitBtn = document.getElementById('submitBtn');

        // Update slug preview
        slugInput.addEventListener('input', (e) => {
            let value = e.target.value.toLowerCase().replace(/[^a-z0-9-]/g, '');
            e.target.value = value;
            slugPreview.textContent = value || 'seuclube';
        });

        // Generate slug from club name
        document.getElementById('club_name').addEventListener('blur', (e) => {
            if (!slugInput.value) {
                const slug = e.target.value
                    .toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-|-$/g, '');
                slugInput.value = slug;
                slugPreview.textContent = slug || 'seuclube';
            }
        });

        // Submit form
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            submitBtn.disabled = true;
            submitBtn.textContent = 'Cadastrando...';
            errorList.style.display = 'none';

            const formData = new FormData(form);

            try {
                const response = await fetch('<?= base_url("cadastrar-clube") ?>', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    form.style.display = 'none';
                    successMessage.style.display = 'block';

                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 2000);
                } else {
                    errorList.innerHTML = '';
                    const errors = data.errors || [data.error];
                    errors.forEach(err => {
                        const li = document.createElement('li');
                        li.textContent = err;
                        errorList.appendChild(li);
                    });
                    errorList.style.display = 'block';
                    submitBtn.disabled = false;
                    submitBtn.textContent = '✨ Cadastrar Clube';
                }
            } catch (err) {
                errorList.innerHTML = '<li>Erro de conexão. Tente novamente.</li>';
                errorList.style.display = 'block';
                submitBtn.disabled = false;
                submitBtn.textContent = '✨ Cadastrar Clube';
            }
        });
    </script>
</body>

</html>