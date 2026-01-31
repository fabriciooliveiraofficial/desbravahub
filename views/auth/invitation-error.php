<?php
/**
 * Public: Invitation Error
 */
$pageTitle = 'Convite Inválido';
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <style>
        :root {
            --bg-dark: #1a1a2e;
            --bg-darker: #16213e;
            --bg-card: rgba(255, 255, 255, 0.05);
            --border-light: rgba(255, 255, 255, 0.1);
            --text-primary: #e0e0e0;
            --text-secondary: #888;
            --accent-cyan: #00d9ff;
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

        .error-card {
            background: var(--bg-card);
            border: 1px solid var(--border-light);
            border-radius: 20px;
            padding: 48px;
            text-align: center;
            max-width: 420px;
        }

        .error-icon {
            font-size: 4rem;
            margin-bottom: 24px;
        }

        h1 {
            font-size: 1.5rem;
            margin-bottom: 16px;
            color: var(--accent-danger);
        }

        p {
            color: var(--text-secondary);
            line-height: 1.6;
            margin-bottom: 32px;
        }

        .btn {
            display: inline-block;
            padding: 14px 32px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid var(--border-light);
            border-radius: 10px;
            color: var(--text-primary);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
        }

        .btn:hover {
            background: rgba(255, 255, 255, 0.15);
        }
    </style>
</head>

<body>
    <div class="error-card">
        <div class="error-icon">❌</div>
        <h1>Convite Inválido</h1>
        <p><?= htmlspecialchars($error ?? 'O convite não foi encontrado ou já expirou.') ?></p>
        <a href="/" class="btn">Voltar ao Início</a>
    </div>
</body>

</html>