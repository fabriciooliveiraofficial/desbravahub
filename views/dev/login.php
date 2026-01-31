<?php
/**
 * Developer Login
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dev Login | DesbravaHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
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
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--dark-bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background:
                radial-gradient(ellipse at 20% 30%, rgba(0, 255, 136, 0.1) 0%, transparent 50%),
                var(--dark-bg);
        }

        .login-container {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }

        .logo {
            text-align: center;
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 8px;
            background: linear-gradient(90deg, var(--secondary), var(--primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        h1 {
            text-align: center;
            font-size: 1.5rem;
            margin-bottom: 32px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 0.9rem;
            margin-bottom: 8px;
        }

        input {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.02);
            color: var(--text);
            font-size: 1rem;
        }

        input:focus {
            outline: none;
            border-color: var(--secondary);
        }

        .btn {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--secondary), var(--primary));
            color: var(--dark-bg);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn:hover {
            box-shadow: 0 4px 20px rgba(0, 255, 136, 0.4);
        }

        .error {
            background: rgba(255, 107, 107, 0.1);
            border: 1px solid #ff6b6b;
            border-radius: 8px;
            padding: 12px;
            color: #ff6b6b;
            font-size: 0.9rem;
            margin-bottom: 20px;
            display: none;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="logo">🛠️ Dev Portal</div>
        <h1>DesbravaHub</h1>

        <div class="error" id="error"></div>

        <form id="loginForm">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>

            <div class="form-group">
                <label>Senha</label>
                <input type="password" name="password" required>
            </div>

            <button type="submit" class="btn">Entrar</button>
        </form>
    </div>

    <script>
        const form = document.getElementById('loginForm');
        const error = document.getElementById('error');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            error.style.display = 'none';

            const formData = new FormData(form);

            try {
                const response = await fetch('<?= base_url("dev/login") ?>', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    error.textContent = data.error;
                    error.style.display = 'block';
                }
            } catch (err) {
                error.textContent = 'Erro de conexão';
                error.style.display = 'block';
            }
        });
    </script>
</body>

</html>