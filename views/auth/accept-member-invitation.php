<?php
/**
 * Public: Accept Member Invitation
 */
$pageTitle = 'Aceitar Convite';
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - <?= htmlspecialchars($invitation['club_name']) ?></title>
    <style>
        :root {
            --bg-dark: #1a1a2e;
            --bg-darker: #16213e;
            --bg-card: rgba(255, 255, 255, 0.05);
            --border-light: rgba(255, 255, 255, 0.1);
            --text-primary: #e0e0e0;
            --text-secondary: #888;
            --accent-cyan: #00d9ff;
            --accent-green: #00ff88;
            --accent-danger: #ff6b6b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, var(--bg-dark) 0%, var(--bg-darker) 100%);
            min-height: 100vh;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 500px;
        }

        .invitation-card {
            background: var(--bg-card);
            border: 1px solid var(--border-light);
            border-radius: 20px;
            padding: 40px;
            text-align: center;
        }

        .logo {
            font-size: 3rem;
            margin-bottom: 16px;
        }

        .club-name {
            font-size: 1.5rem;
            font-weight: 700;
            background: linear-gradient(90deg, var(--accent-cyan), var(--accent-green));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 24px;
        }

        .welcome-text {
            color: var(--text-secondary);
            margin-bottom: 24px;
            line-height: 1.6;
        }

        .custom-message {
            background: rgba(0, 217, 255, 0.1);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            border-left: 4px solid var(--accent-cyan);
            text-align: left;
        }

        .custom-message p {
            font-style: italic;
            margin-bottom: 8px;
        }

        .custom-message .author {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 14px 16px;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid var(--border-light);
            border-radius: 10px;
            color: var(--text-primary);
            font-size: 1rem;
            transition: all 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent-cyan);
            box-shadow: 0 0 0 3px rgba(0, 217, 255, 0.1);
        }

        .form-hint {
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin-top: 6px;
        }

        .email-display {
            background: rgba(0, 0, 0, 0.2);
            padding: 14px 16px;
            border-radius: 10px;
            color: var(--text-secondary);
            font-family: monospace;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--accent-cyan), var(--accent-green));
            border: none;
            border-radius: 10px;
            color: #0a0a14;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 24px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0, 217, 255, 0.3);
        }

        .error-message {
            background: rgba(255, 107, 107, 0.1);
            border: 1px solid rgba(255, 107, 107, 0.3);
            color: var(--accent-danger);
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: left;
        }

        .features {
            display: flex;
            justify-content: space-around;
            margin: 24px 0;
            padding: 20px 0;
            border-top: 1px solid var(--border-light);
            border-bottom: 1px solid var(--border-light);
        }

        .feature {
            text-align: center;
        }

        .feature-icon {
            font-size: 1.5rem;
            margin-bottom: 4px;
        }

        .feature-label {
            font-size: 0.8rem;
            color: var(--text-secondary);
        }

        @media (max-width: 480px) {
            .invitation-card {
                padding: 24px;
            }

            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="invitation-card">
            <div class="logo">⚡</div>
            <div class="club-name"><?= htmlspecialchars($invitation['club_name']) ?></div>

            <p class="welcome-text">
                <?php if ($invitation['name']): ?>
                    Olá <strong><?= htmlspecialchars($invitation['name']) ?></strong>!
                <?php else: ?>
                    Olá!
                <?php endif; ?>
                Você foi convidado para fazer parte do nosso clube de desbravadores!
            </p>

            <?php if ($invitation['custom_message']): ?>
                <div class="custom-message">
                    <p>"<?= htmlspecialchars($invitation['custom_message']) ?>"</p>
                </div>
            <?php endif; ?>

            <div class="features">
                <div class="feature">
                    <div class="feature-icon">🏅</div>
                    <div class="feature-label">Especialidades</div>
                </div>
                <div class="feature">
                    <div class="feature-icon">📚</div>
                    <div class="feature-label">Classes</div>
                </div>
                <div class="feature">
                    <div class="feature-icon">🏕️</div>
                    <div class="feature-label">Eventos</div>
                </div>
            </div>

            <?php if (!empty($error)): ?>
                <div class="error-message">
                    ❌ <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Email</label>
                    <div class="email-display"><?= htmlspecialchars($invitation['email']) ?></div>
                </div>

                <div class="form-group">
                    <label>Seu Nome Completo *</label>
                    <input type="text" name="name" class="form-control" placeholder="Digite seu nome"
                        value="<?= htmlspecialchars($invitation['name'] ?? $_POST['name'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label>Data de Nascimento</label>
                    <input type="date" name="birth_date" class="form-control"
                        value="<?= htmlspecialchars($_POST['birth_date'] ?? '') ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Criar Senha *</label>
                        <input type="password" name="password" class="form-control" placeholder="Mínimo 6 caracteres"
                            minlength="6" required>
                    </div>

                    <div class="form-group">
                        <label>Confirmar *</label>
                        <input type="password" name="password_confirm" class="form-control" placeholder="Repita a senha"
                            minlength="6" required>
                    </div>
                </div>

                <button type="submit" class="btn">
                    🎉 Criar Conta e Entrar
                </button>
            </form>
        </div>
    </div>
</body>

</html>