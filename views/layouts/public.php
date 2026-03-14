<?php
/**
 * Public Layout
 * Base layout for unauthenticated public facing pages (Landing Page, Public Events)
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Sanity Guard: Handle external dev-tool noise (like refresh.js on 8081) -->
    <script>
        (function() {
            const _WebSocket = window.WebSocket;
            window.WebSocket = function(url, protocols) {
                if (typeof url === 'string' && url.includes(':8081')) {
                    return { close:()=>{}, send:()=>{}, addEventListener:()=>{}, removeEventListener:()=>{}, readyState: 3 };
                }
                return new _WebSocket(url, protocols);
            };
            window.WebSocket.prototype = _WebSocket.prototype;
            window.WebSocket.CONNECTING = 0; window.WebSocket.OPEN = 1; window.WebSocket.CLOSING = 2; window.WebSocket.CLOSED = 3;
        })();
    </script>
    <title><?= htmlspecialchars($pageTitle ?? 'DesbravaHub') ?></title>
    
    <meta name="description" content="<?= htmlspecialchars($metaDescription ?? 'Plataforma oficial do clube. Acompanhe nossas especialidades, eventos e conquistas!') ?>">
    <meta name="keywords" content="desbravadores, clube, especialidades, eventos, escotismo, juventude">
    <meta name="author" content="DesbravaHub">
    <meta name="robots" content="index, follow">

    <!-- OpenGraph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= htmlspecialchars((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]") ?>">
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle ?? 'DesbravaHub') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($metaDescription ?? 'Plataforma oficial do clube. Acompanhe nossas especialidades, eventos e conquistas!') ?>">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?= htmlspecialchars((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]") ?>">
    <meta property="twitter:title" content="<?= htmlspecialchars($pageTitle ?? 'DesbravaHub') ?>">
    <meta property="twitter:description" content="<?= htmlspecialchars($metaDescription ?? 'Plataforma oficial do clube. Acompanhe nossas especialidades, eventos e conquistas!') ?>">
    <?php if (isset($profile['logo_url'])): ?>
        <meta property="og:image" content="<?= htmlspecialchars($profile['logo_url']) ?>">
    <?php endif; ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    
    <style>
        :root {
            /* Layout Constants */
            --header-h: 70px;
            --t-color: color 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            --t-bg: background-color 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            --t-border: border-color 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* Neobank Inspired Palette - Theme Aware */
        [data-theme='light'] {
            --primary: #8b5cf6; /* Nu Purple */
            --primary-dark: #7c3aed;
            --secondary: #10b981; /* Success Green */
            --dark-bg: #f8fafc;
            --surface: #ffffff;
            --surface-hover: #f1f5f9;
            --border: rgba(0, 0, 0, 0.08);
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-muted: #64748b;
            --accent: #8b5cf6;
            --accent-hover: #7c3aed;
            --accent-glow: rgba(139, 92, 246, 0.1);
            --bg-main: #f8fafc;
            --bg-surface: #ffffff;
            --bg-elevated: #f1f5f9;
            --nav-bg: rgba(248, 250, 252, 0.8);
        }

        [data-theme='dark'] {
            --primary: #8b5cf6;
            --primary-dark: #7c3aed;
            --secondary: #10b981;
            --dark-bg: #0f111a;
            --surface: #1e212f;
            --surface-hover: #2a2d3d;
            --border: rgba(255, 255, 255, 0.08);
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --accent: #8b5cf6;
            --accent-hover: #7c3aed;
            --accent-glow: rgba(139, 92, 246, 0.15);
            --bg-main: #0f111a;
            --bg-surface: #1e212f;
            --bg-elevated: #2a2d3d;
            --nav-bg: rgba(15, 17, 26, 0.8);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background-color: var(--dark-bg);
            color: var(--text-primary);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            transition: var(--t-bg), var(--t-color);
        }

        a { color: inherit; text-decoration: none; }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
        }

        /* Nav Header */
        nav.public-nav {
            position: fixed;
            top: 0; left: 0; right: 0;
            height: 70px;
            background: var(--nav-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            z-index: 1000;
            display: flex;
            align-items: center;
            transition: var(--t-bg), var(--t-border);
        }

        .nav-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }

        .nav-brand {
            font-weight: 700;
            font-size: 1.25rem;
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--text-primary);
        }

        .nav-brand img {
            height: 32px;
            width: 32px;
            border-radius: 8px;
            object-fit: cover;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            outline: none;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 12px rgba(139, 92, 246, 0.2);
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(139, 92, 246, 0.3);
        }

        .btn-secondary {
            background: var(--surface);
            color: var(--text-primary);
            border: 1px solid var(--border);
        }

        .btn-secondary:hover {
            background: var(--surface-hover);
            border-color: rgba(255,255,255,0.2);
        }

        .btn-sm { padding: 8px 16px; font-size: 0.85rem; border-radius: 8px; }

        /* Main Layout */
        main {
            padding-top: 70px;
            min-height: calc(100vh - 80px);
        }

        footer {
            padding: 32px 24px;
            text-align: center;
            color: var(--text-muted);
            font-size: 0.85rem;
            border-top: 1px solid var(--border);
        }

        /* Toast Notifications */
        .toast-container {
            position: fixed;
            bottom: 24px; max-width: 400px; width: calc(100% - 48px);
            left: 50%; transform: translateX(-50%);
            z-index: 9999;
            display: flex; flex-direction: column; gap: 8px;
        }

        .toast {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px;
            display: flex; align-items: flex-start; gap: 12px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.4);
            animation: slideUp 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .toast.success .material-icons-round { color: var(--secondary); }
        .toast.error .material-icons-round { color: #ef4444; }
        
        @keyframes slideUp {
            from { transform: translateY(100%); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* Cards */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow: hidden;
            transition: transform 0.2s, border-color 0.2s;
        }
        .card:hover {
            border-color: rgba(255, 255, 255, 0.15);
            transform: translateY(-4px);
        }

        /* Form Elements */
        .form-group { margin-bottom: 16px; }
        .form-group label {
            display: block; margin-bottom: 8px;
            font-size: 0.85rem; color: var(--text-secondary); font-weight: 500;
        }
        .form-control {
            width: 100%; padding: 12px 16px;
            background: rgba(0,0,0,0.2);
            border: 1px solid var(--border);
            border-radius: 12px;
            color: var(--text-primary);
            font-family: inherit; font-size: 0.95rem;
            transition: all 0.2s;
        }
        .form-control:focus {
            outline: none; border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.15);
        }

        /* Badges */
        .badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 4px 10px; border-radius: 20px;
            font-size: 0.75rem; font-weight: 600;
        }
        .badge-primary { background: rgba(139, 92, 246, 0.15); color: #c4b5fd; border: 1px solid rgba(139, 92, 246, 0.3); }
        .badge-success { background: rgba(16, 185, 129, 0.15); color: #6ee7b7; border: 1px solid rgba(16, 185, 129, 0.3); }

        @media (max-width: 768px) {
            .container { padding: 0 16px; }
        }

        /* Theme Switcher 3D Effect */
        .theme-toggle-wrapper {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 2000;
            perspective: 1000px;
        }

        .theme-toggle {
            background: var(--surface);
            border: 1px solid var(--border);
            color: var(--text-primary);
            width: 56px;
            height: 56px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1), inset 0 -3px 6px rgba(0,0,0,0.1);
            transform-style: preserve-3d;
            transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s, border-color 0.3s;
        }

        .theme-toggle:hover {
            transform: scale(1.1) rotateY(15deg);
            box-shadow: 0 15px 25px rgba(0,0,0,0.15), inset 0 -3px 6px rgba(0,0,0,0.1);
            border-color: var(--primary);
        }

        .theme-toggle.flipping {
            animation: flip3D 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        @keyframes flip3D {
            0% { transform: scale(1) rotateY(0deg); }
            50% { transform: scale(1.2) rotateY(180deg); }
            100% { transform: scale(1) rotateY(360deg); }
        }

        .theme-toggle iconify-icon {
            font-size: 1.8rem;
            transform: translateZ(20px);
        }
    </style>

    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <script>
        // Critical: Set theme immediately to avoid flash
        (function() {
            const saved = localStorage.getItem('hub_theme') || 'dark';
            document.documentElement.setAttribute('data-theme', saved);
        })();
    </script>
</head>
<body>

    <nav class="public-nav">
        <div class="container nav-content">
            <a href="<?= base_url('c/' . $profile['slug']) ?>" class="nav-brand">
                <?php if (!empty($profile['logo_url'])): ?>
                    <img src="<?= htmlspecialchars($profile['logo_url']) ?>" alt="Logo">
                <?php else: ?>
                    <span class="material-icons-round" style="color: var(--primary);">local_fire_department</span>
                <?php endif; ?>
                <?= htmlspecialchars($profile['display_name']) ?>
            </a>
            
            <?php if (!\App\Core\App::user()): ?>
                <a href="<?= base_url($profile['slug'] . '/login') ?>" class="btn btn-secondary btn-sm">
                    Entrar no Painel
                </a>
            <?php else: ?>
                <a href="<?= base_url($profile['slug'] . '/dashboard') ?>" class="btn btn-primary btn-sm">
                    Meu Painel
                </a>
            <?php endif; ?>
        </div>
    </nav>

    <main>
        <?= $content ?>
    </main>

    <div class="theme-toggle-wrapper">
        <div class="theme-toggle" id="themeToggle" onclick="toggleTheme()">
            <iconify-icon icon="solar:moon-bold-duotone" id="themeIcon"></iconify-icon>
        </div>
    </div>

    <footer>
        <div class="container">
            &copy; <?= date('Y') ?> <?= htmlspecialchars($profile['display_name']) ?>.<br>
            Powered by <a href="#" style="color: var(--text-secondary); font-weight: 500;">DesbravaHub</a>
        </div>
    </footer>

    <div id="toast-container" class="toast-container"></div>
    <script>
        class ToastNotification {
            constructor() { this.container = document.getElementById('toast-container'); }
            show(title, message, type = 'success') {
                const icon = type === 'success' ? 'check_circle' : 'error';
                const toast = document.createElement('div');
                toast.className = `toast ${type}`;
                toast.innerHTML = `
                    <span class="material-icons-round">${icon}</span>
                    <div>
                        <strong style="display: block; font-size: 0.9rem;">${title}</strong>
                        <span style="font-size: 0.85rem; color: var(--text-secondary);">${message}</span>
                    </div>
                `;
                this.container.appendChild(toast);
                setTimeout(() => {
                    toast.style.opacity = '0';
                    toast.style.transform = 'translateY(20px)';
                    setTimeout(() => toast.remove(), 300);
                }, 4000);
            }
            success(t, m) { this.show(t, m, 'success'); }
            error(t, m) { this.show(t, m, 'error'); }
        }
        window.toast = new ToastNotification();

        function toggleTheme() {
            const html = document.documentElement;
            const btn = document.getElementById('themeToggle');
            const current = html.getAttribute('data-theme') || 'dark';
            const next = current === 'dark' ? 'light' : 'dark';
            
            btn.classList.add('flipping');
            
            setTimeout(() => {
                html.setAttribute('data-theme', next);
                localStorage.setItem('hub_theme', next);
                updateThemeIcon(next);
            }, 300);

            setTimeout(() => btn.classList.remove('flipping'), 600);
        }

        function updateThemeIcon(theme) {
            const icon = document.getElementById('themeIcon');
            if (icon) {
                icon.setAttribute('icon', theme === 'dark' ? 'solar:moon-bold-duotone' : 'solar:sun-bold-duotone');
            }
        }

        // Sync icon on load
        document.addEventListener('DOMContentLoaded', () => {
            const theme = document.documentElement.getAttribute('data-theme') || 'dark';
            updateThemeIcon(theme);
        });
    </script>
    <?= embed_media_sdk_scripts() ?>
</body>
</html>
