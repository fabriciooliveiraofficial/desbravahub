<?php
/**
 * Public: Accept Invitation
 */
$pageTitle = 'Aceitar Convite';
$roleLabels = $roleLabels ?? [
    'associate_director' => 'Diretor Associado',
    'counselor' => 'Conselheiro',
    'instructor' => 'Instrutor'
];
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
            max-width: 480px;
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
            margin-bottom: 8px;
        }

        .invitation-text {
            color: var(--text-secondary);
            margin-bottom: 24px;
        }

        .role-badge {
            display: inline-block;
            padding: 10px 24px;
            background: linear-gradient(135deg, var(--accent-cyan), var(--accent-green));
            border-radius: 25px;
            color: #0a0a14;
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 32px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-primary);
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

        .form-control::placeholder {
            color: var(--text-secondary);
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

        .expire-notice {
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid var(--border-light);
        }

        @media (max-width: 480px) {
            .invitation-card {
                padding: 24px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="invitation-card">
            <div class="logo">⚡</div>
            <div class="club-name"><?= htmlspecialchars($invitation['club_name']) ?></div>
            <p class="invitation-text">Você foi convidado para fazer parte da equipe como:</p>

            <div class="role-badge">
                <?= $roleLabels[$invitation['role_name']] ?? $invitation['role_name'] ?>
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
                    <label>Seu Nome Completo</label>
                    <input type="text" name="name" class="form-control" placeholder="Digite seu nome"
                        value="<?= htmlspecialchars($invitation['name'] ?? $_POST['name'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label>Criar Senha</label>
                    <input type="password" name="password" class="form-control" placeholder="Mínimo 6 caracteres"
                        minlength="6" required>
                    <div class="form-hint">Use uma senha forte com letras e números</div>
                </div>

                <div class="form-group">
                    <label>Confirmar Senha</label>
                    <input type="password" name="password_confirm" class="form-control"
                        placeholder="Digite a senha novamente" minlength="6" required>
                </div>

                <button type="submit" class="btn">
                    ✅ Aceitar Convite e Criar Conta
                </button>

                <div class="expire-notice">
                    Este convite expira em <?= date('d/m/Y', strtotime($invitation['expires_at'])) ?>
                </div>
            </form>
        </div>
    </div>
</body>

</html>