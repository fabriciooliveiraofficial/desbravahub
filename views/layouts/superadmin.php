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
    <link rel="stylesheet" href="/assets/css/superadmin.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Outfit:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Symbols+Rounded" rel="stylesheet">

    <style>
        /* Layout-specific tweaks only */
        .sa-sidebar {
            width: 280px;
            background: var(--sa-surface);
            border-right: 1px solid var(--sa-border);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 50;
        }

        .sa-brand {
            padding: 2rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-bottom: 1px solid var(--sa-border);
        }

        .sa-nav {
            padding: 2rem 1rem;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .sa-main {
            margin-left: 280px;
            flex: 1;
            min-height: 100vh;
            background: radial-gradient(circle at top right, rgba(139, 92, 246, 0.05), transparent 400px);
        }

        .sa-topbar {
            height: 80px;
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--sa-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            position: sticky;
            top: 0;
            z-index: 40;
        }

        .sa-nav-item {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            padding: 0.875rem 1rem;
            border-radius: 0.875rem;
            color: var(--sa-text-muted);
            text-decoration: none;
            transition: all 0.2s;
            font-weight: 600;
        }

        .sa-nav-item:hover {
            background: rgba(255, 255, 255, 0.03);
            color: white;
        }

        .sa-nav-item.active {
            background: var(--sa-primary);
            color: white;
            box-shadow: 0 4px 12px var(--sa-primary-glow);
        }
    </style>
        
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
            <a href="/super-admin/suporte" class="sa-nav-item <?= strpos($_SERVER['REQUEST_URI'], '/super-admin/suporte') !== false ? 'active' : '' ?>">
                <span class="material-symbols-rounded">support_agent</span>
                Central de Suporte
            </a>
            
            <div style="height: 1px; background: rgba(255,255,255,0.05); margin: 16px 0;"></div>
            <a href="/super-admin/scraper" class="sa-nav-item <?= strpos($_SERVER['REQUEST_URI'], '/super-admin/scraper') !== false ? 'active' : '' ?>">
                <span class="material-symbols-rounded" style="color: var(--sa-neon)">smart_toy</span>
                Super Scraper (IA)
            </a>

            <a href="/super-admin/migracao" class="sa-nav-item <?= strpos($_SERVER['REQUEST_URI'], '/super-admin/migracao') !== false ? 'active' : '' ?>">
                <span class="material-symbols-rounded" style="color: #ef4444">database</span>
                Migração DB
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
                    <img src="<?= htmlspecialchars($user['avatar_url']) ?>" alt="Avatar" style="width:36px; height:36px; border-radius:50%; object-fit:cover; border:2px solid var(--sa-primary);" onerror="this.style.display='none'; this.nextElementSibling.style.display='grid';">
                    <div style="display:none; width:36px; height:36px; border-radius:50%; background:var(--sa-primary); place-items:center; color:white; font-weight:bold;">
                        <?= substr($user['name'] ?? 'A', 0, 1) ?>
                    </div>
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
