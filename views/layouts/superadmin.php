<?php
/**
 * Super Admin Layout
 */
$theme = 'dark'; // Force dark mode for Super Admin 
?>
<!DOCTYPE html>
<html lang="pt-BR" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Super Admin') ?> - DesbravaHub</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Outfit:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Symbols+Rounded" rel="stylesheet">
    
    <style>
        :root {
            --sa-primary: #8b5cf6; /* Violet */
            --sa-dark: #0f172a;    /* Slate 900 */
            --sa-surface: #1e293b; /* Slate 800 */
            --sa-neon: #a78bfa;
        }
        
        body {
            background-color: var(--sa-dark);
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
        }

        /* Super Admin Sidebar */
        .sa-sidebar {
            width: 280px;
            background: var(--sa-surface);
            border-right: 1px solid rgba(255,255,255,0.05);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 50;
        }

        .sa-brand {
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        .sa-brand .icon {
            color: var(--sa-primary);
            font-size: 28px;
            filter: drop-shadow(0 0 10px rgba(139, 92, 246, 0.4));
        }

        .sa-brand span {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 1.25rem;
            color: white;
            letter-spacing: -0.5px;
        }

        .sa-nav {
            padding: 24px 16px;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .sa-nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: 12px;
            color: #94a3b8;
            text-decoration: none;
            transition: all 0.2s;
            font-weight: 500;
        }

        .sa-nav-item:hover {
            background: rgba(255,255,255,0.05);
            color: white;
        }

        .sa-nav-item.active {
            background: var(--sa-primary);
            color: white;
            box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
        }

        .sa-nav-item .material-symbols-rounded {
            font-size: 20px;
        }

        /* Main Content */
        .sa-main {
            margin-left: 280px;
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .sa-topbar {
            height: 72px;
            background: rgba(30, 41, 59, 0.8);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255,255,255,0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
            position: sticky;
            top: 0;
            z-index: 40;
        }

        .sa-page-title {
            display: flex;
            align-items: center;
            gap: 12px;
            font-family: 'Outfit', sans-serif;
            font-size: 1.25rem;
            font-weight: 600;
        }

        .sa-page-title .icon {
            color: var(--sa-neon);
        }

        .sa-user {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .sa-user-info span {
            font-size: 0.875rem;
            color: #cbd5e1;
            font-weight: 500;
        }

        .sa-user-badge {
            background: rgba(139, 92, 246, 0.2);
            color: var(--sa-neon);
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-left: 8px;
        }

        .sa-content {
            padding: 32px;
            flex: 1;
        }

        /* Dashboard Cards */
        .sa-card {
            background: var(--sa-surface);
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 16px;
            padding: 24px;
        }

        .sa-stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }

        .sa-stat-card {
            background: linear-gradient(145deg, rgba(30,41,59,1) 0%, rgba(15,23,42,1) 100%);
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 16px;
            padding: 24px;
            position: relative;
            overflow: hidden;
        }

        .sa-stat-card::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: radial-gradient(circle, var(--sa-primary) 0%, transparent 70%);
            opacity: 0.1;
            transform: translate(30%, -30%);
            border-radius: 50%;
        }

        .sa-stat-title {
            color: #94a3b8;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .sa-stat-value {
            font-family: 'Outfit', sans-serif;
            font-size: 2.5rem;
            font-weight: 700;
            color: white;
            line-height: 1;
        }
        
        /* Table Styles */
        .sa-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .sa-table th {
            text-align: left;
            padding: 16px;
            color: #94a3b8;
            font-weight: 500;
            font-size: 0.875rem;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        
        .sa-table td {
            padding: 16px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            color: #cbd5e1;
            font-size: 0.95rem;
        }
        
        .sa-table tbody tr:hover td {
            background: rgba(255,255,255,0.02);
        }
        
        .sa-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 99px;
            font-size: 0.75rem;
            font-weight: 600;
            background: rgba(255,255,255,0.1);
            color: white;
        }
        
        .sa-badge.active { background: rgba(16, 185, 129, 0.2); color: #34d399; }
        .sa-badge.pending { background: rgba(245, 158, 11, 0.2); color: #fbbf24; }
        
    </style>
</head>
<body>

    <!-- Sidebar -->
    <aside class="sa-sidebar">
        <div class="sa-brand">
            <span class="material-symbols-rounded icon">admin_panel_settings</span>
            <span>DesbravaHub <span style="color:var(--sa-primary)">Global</span></span>
        </div>
        
        <nav class="sa-nav">
            <a href="/super-admin/dashboard" class="sa-nav-item <?= strpos($_SERVER['REQUEST_URI'], '/super-admin/dashboard') !== false || $_SERVER['REQUEST_URI'] == '/super-admin' ? 'active' : '' ?>">
                <span class="material-symbols-rounded">dashboard</span>
                Dashboard
            </a>
            <a href="/super-admin/clubs" class="sa-nav-item <?= strpos($_SERVER['REQUEST_URI'], '/super-admin/clubs') !== false ? 'active' : '' ?>">
                <span class="material-symbols-rounded">storefront</span>
                Franquias / Clubes
            </a>
            <a href="/super-admin/users" class="sa-nav-item <?= strpos($_SERVER['REQUEST_URI'], '/super-admin/users') !== false ? 'active' : '' ?>">
                <span class="material-symbols-rounded">group</span>
                Usuários Globais
            </a>
            
            <div style="height: 1px; background: rgba(255,255,255,0.05); margin: 16px 0;"></div>
            
            <a href="/super-admin/scraper" class="sa-nav-item <?= strpos($_SERVER['REQUEST_URI'], '/super-admin/scraper') !== false ? 'active' : '' ?>">
                <span class="material-symbols-rounded" style="color: var(--sa-neon)">smart_toy</span>
                Super Scraper (IA)
            </a>
            
            <a href="/" class="sa-nav-item" style="margin-top: auto;">
                <span class="material-symbols-rounded">arrow_back</span>
                Sair do Painel
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="sa-main">
        <!-- Topbar -->
        <header class="sa-topbar">
            <div class="sa-page-title">
                <span class="material-symbols-rounded icon"><?= $pageIcon ?? 'dashboard' ?></span>
                <?= htmlspecialchars($pageTitle ?? 'Super Admin') ?>
            </div>
            
            <div class="sa-user">
                <div class="sa-user-info">
                    <span><?= htmlspecialchars($user['name'] ?? 'Admin') ?></span>
                    <span class="sa-user-badge">Super Admin</span>
                </div>
                <?php if (!empty($user['avatar_url'])): ?>
                    <img src="<?= htmlspecialchars($user['avatar_url']) ?>" alt="Avatar" style="width:36px; height:36px; border-radius:50%; object-fit:cover; border:2px solid var(--sa-primary);">
                <?php else: ?>
                    <div style="width:36px; height:36px; border-radius:50%; background:var(--sa-primary); display:grid; place-items:center; color:white; font-weight:bold;">
                        <?= substr($user['name'] ?? 'A', 0, 1) ?>
                    </div>
                <?php endif; ?>
            </div>
        </header>

        <!-- Page Content -->
        <div class="sa-content">
            <?= $content ?>
        </div>
    </main>

</body>
</html>
