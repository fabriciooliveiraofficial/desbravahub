<?php
/**
 * Landing Page - Página pública do clube
 */

use App\Core\App;

$tenant = App::tenant();
$tenantName = $tenant['name'] ?? 'Clube de Desbravadores';
$tenantDescription = $tenant['description'] ?? 'Bem-vindo ao nosso clube de Desbravadores!';
$tenantLogo = $tenant['logo_url'] ?? null;

// Buscar estatísticas públicas
$stats = [];
try {
    $stats = [
        'members' => db_fetch_column("SELECT COUNT(*) FROM users WHERE tenant_id = ? AND status = 'active'", [$tenant['id']]) ?? 0,
        'activities' => db_fetch_column("SELECT COUNT(*) FROM activities WHERE tenant_id = ? AND status = 'active'", [$tenant['id']]) ?? 0,
    ];
} catch (Exception $e) {
    $stats = ['members' => 0, 'activities' => 0];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tenantName) ?> | DesbravaHub</title>
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
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--dark-bg);
            color: var(--text);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Hero Section */
        .hero {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 40px 20px;
            position: relative;
            background:
                radial-gradient(ellipse at 30% 20%, rgba(0, 217, 255, 0.15) 0%, transparent 50%),
                radial-gradient(ellipse at 70% 80%, rgba(0, 255, 136, 0.1) 0%, transparent 50%),
                var(--dark-bg);
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.02'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            pointer-events: none;
        }

        .logo {
            width: 120px;
            height: 120px;
            border-radius: 24px;
            object-fit: cover;
            margin-bottom: 24px;
            box-shadow: 0 8px 32px rgba(0, 217, 255, 0.3);
            border: 2px solid var(--primary);
        }

        .logo-placeholder {
            width: 120px;
            height: 120px;
            border-radius: 24px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            margin-bottom: 24px;
            box-shadow: 0 8px 32px rgba(0, 217, 255, 0.3);
        }

        h1 {
            font-size: clamp(2rem, 6vw, 3.5rem);
            font-weight: 800;
            margin-bottom: 16px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .tagline {
            font-size: 1.2rem;
            color: var(--text-muted);
            max-width: 500px;
            margin-bottom: 40px;
            line-height: 1.6;
        }

        /* Stats */
        .stats {
            display: flex;
            gap: 40px;
            margin-bottom: 50px;
        }

        .stat {
            text-align: center;
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stat-label {
            font-size: 0.9rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Buttons */
        .buttons {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 16px 32px;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: var(--dark-bg);
            box-shadow: 0 4px 20px rgba(0, 217, 255, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 30px rgba(0, 217, 255, 0.5);
        }

        .btn-secondary {
            background: var(--card-bg);
            color: var(--text);
            border: 1px solid var(--border);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--primary);
        }

        /* Features */
        .features {
            padding: 80px 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .features h2 {
            text-align: center;
            font-size: 2rem;
            margin-bottom: 50px;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
        }

        .feature-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 32px;
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            border-color: var(--primary);
            transform: translateY(-4px);
        }

        .feature-icon {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            background: linear-gradient(135deg, rgba(0, 217, 255, 0.2), rgba(0, 255, 136, 0.2));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 20px;
        }

        .feature-card h3 {
            font-size: 1.2rem;
            margin-bottom: 10px;
            color: var(--text);
        }

        .feature-card p {
            color: var(--text-muted);
            line-height: 1.6;
        }

        /* Footer */
        footer {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-muted);
            font-size: 0.9rem;
            border-top: 1px solid var(--border);
        }

        footer a {
            color: var(--primary);
            text-decoration: none;
        }

        @media (max-width: 600px) {
            .stats {
                flex-direction: column;
                gap: 20px;
            }

            .buttons {
                flex-direction: column;
                width: 100%;
                padding: 0 20px;
            }

            .btn {
                justify-content: center;
            }
        }
    </style>
</head>

<body>
    <!-- Hero Section -->
    <section class="hero">
        <?php if ($tenantLogo): ?>
            <img src="<?= htmlspecialchars($tenantLogo) ?>" alt="<?= htmlspecialchars($tenantName) ?>" class="logo">
        <?php else: ?>
            <div class="logo-placeholder">⚡</div>
        <?php endif; ?>

        <h1><?= htmlspecialchars($tenantName) ?></h1>
        <p class="tagline"><?= htmlspecialchars($tenantDescription) ?></p>

        <div class="stats">
            <div class="stat">
                <div class="stat-value"><?= number_format($stats['members']) ?></div>
                <div class="stat-label">Membros</div>
            </div>
            <div class="stat">
                <div class="stat-value"><?= number_format($stats['activities']) ?></div>
                <div class="stat-label">Especialidades</div>
            </div>
        </div>

        <div class="buttons">
            <a href="<?= base_url($tenant['slug'] . '/login') ?>" class="btn btn-primary">
                🚀 Entrar
            </a>
            <a href="<?= base_url($tenant['slug'] . '/register') ?>" class="btn btn-secondary">
                📝 Criar Conta
            </a>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <h2>✨ O que você encontra aqui</h2>

        <div class="feature-grid">
            <div class="feature-card">
                <div class="feature-icon">🎯</div>
                <h3>Especialidades Progressivas</h3>
                <p>Complete especialidades e ganhe XP para subir de nível. Cada conquista te aproxima do próximo
                    desafio.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">🏆</div>
                <h3>Conquistas e Badges</h3>
                <p>Desbloqueie conquistas especiais ao atingir metas e completar desafios únicos.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">📊</div>
                <h3>Ranking do Clube</h3>
                <p>Veja sua posição no ranking e compare seu progresso com outros desbravadores.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">📱</div>
                <h3>Provas Digitais</h3>
                <p>Envie fotos, vídeos ou links de redes sociais como prova das suas realizações.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">📅</div>
                <h3>Eventos e Agenda</h3>
                <p>Fique por dentro dos próximos eventos e acampamentos do clube.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">🔔</div>
                <h3>Notificações</h3>
                <p>Receba avisos sobre novas especialidades, provas aprovadas e eventos importantes.</p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <p>Powered by <a href="#">DesbravaHub</a> • <?= date('Y') ?></p>
    </footer>
</body>

</html>