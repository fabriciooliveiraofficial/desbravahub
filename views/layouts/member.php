<?php
/**
 * Master Layout - Member Dashboard (Deep Glass HUD)
 * 
 * Provides the shell for the member application including:
 * - HTML5 Boilerplate
 * - Deep Glass HUD Theme
 * - Scripts (HTMX, Toast)
 * - Persistent Header & Bottom Navigation
 * - Main Content Area
 */
?>
<!DOCTYPE html>
<html lang="<?= $tenant['settings']['language'] ?? 'pt_BR' ?>" class="loading">


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#1a1a2e">
    <title>DesbravaHub | <?= $tenant['name'] ?></title>
    
    <!-- PWA -->
    <link rel="manifest" href="/manifest.json">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="DesbravaHub">
    <link rel="apple-touch-icon" href="/assets/images/icon-192.png">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Core Styles -->
    <link rel="stylesheet" href="<?= asset_url('css/app.css') ?>">
    <link rel="stylesheet" href="<?= asset_url('css/hud-theme.css') ?>">
    
    <!-- HTMX -->
    <script src="https://unpkg.com/htmx.org@1.9.10"></script>
    
    <!-- Toast System -->
    <script src="<?= asset_url('js/toast.js') ?>"></script>
    <script src="<?= asset_url('js/uas.js') ?>"></script>
</head>

<body class="hud-body" hx-boost="true" hx-target="#main-content" hx-select="#main-content" hx-indicator="#global-loader">
    
    <!-- Global Loader -->
    <div id="global-loader" class="htmx-indicator" style="background: linear-gradient(90deg, var(--accent-cyan), var(--accent-green)); height: 3px; position: fixed; top: 0; width: 100%; z-index: 9999; display: none;"></div>

    <!-- HUD Top Bar (Global) -->
    <?php require BASE_PATH . '/views/dashboard/partials/header.php'; ?>

    <div class="container">
        <!-- Main Content Area -->
        <main id="main-content" class="main-content">
            <?= $content ?>
        </main>
    </div>

    <!-- Navigation Partial -->
    <?php require BASE_PATH . '/views/dashboard/partials/nav.php'; ?>

    <!-- Push Notifications Banner & Logic -->
    <?php require BASE_PATH . '/views/dashboard/partials/push_banner.php'; ?>

    <!-- Push Notifications Core -->
    <script src="<?= asset_url('js/push-notifications.js') ?>"></script>
    <script src="<?= asset_url('js/pwa-install.js') ?>"></script>
    <script>
        // Init Push Notifications Core
        document.addEventListener('DOMContentLoaded', async () => {
            if (typeof pushNotifications !== 'undefined' && pushNotifications.isSupported()) {
                const publicKey = '<?= env('VAPID_PUBLIC_KEY', '') ?>';
                const apiEndpoint = '<?= base_url("{$tenant['slug']}/api/push/subscribe") ?>';
                if (publicKey) {
                    await pushNotifications.init(publicKey, apiEndpoint);
                }
            }
        });

        // Re-initialize Toast on page load/swap
        const toast = new ToastNotification();
        
        // Handle HTMX events
        document.body.addEventListener('htmx:afterSwap', function(event) {
            window.scrollTo(0, 0);
        });

        // =============================================
        // GLOBAL EVENT DELEGATION FOR PROGRAM CARDS
        // This persists across HTMX swaps
        // =============================================
        
        // Handle program card clicks (navigate to detail)
        document.body.addEventListener('click', function(e) {
            const card = e.target.closest('.program-card');
            if (card && !e.target.closest('.program-submit-btn')) {
                e.preventDefault();
                const href = card.dataset.href;
                if (href) {
                    window.location.href = href;
                }
            }
        });
        
        // Handle program submit button clicks
        document.body.addEventListener('click', async function(e) {
            const btn = e.target.closest('.program-submit-btn');
            if (!btn || btn.disabled || btn.classList.contains('disabled')) return;
            
            e.preventDefault();
            e.stopPropagation();
            
            const programId = btn.dataset.programId;
            if (!programId) return;
            
            const confirmed = await sconfirm('Deseja enviar todas as respostas para avaliação? Certifique-se de que revisou tudo.', 'Finalizar Missão');
            if (!confirmed) return;
            
            const originalContent = btn.innerHTML;
            btn.disabled = true;
            btn.classList.add('disabled');
            btn.innerHTML = '<span class="material-icons-round spin">sync</span> Enviando...';
            
            fetch(`/<?= $tenant['slug'] ?>/aprendizado/${programId}/submit-all`, {
                method: 'POST'
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    if (typeof toast !== 'undefined' && toast.success) {
                        toast.success('Sucesso', data.message);
                    } else {
                        alert(data.message);
                    }
                    setTimeout(() => location.reload(), 1500);
                } else {
                    if (typeof toast !== 'undefined' && toast.error) {
                        toast.error('Erro', data.error || 'Erro ao enviar');
                    } else {
                        alert(data.error || 'Erro ao enviar');
                    }
                    btn.disabled = false;
                    btn.classList.remove('disabled');
                    btn.innerHTML = originalContent;
                }
            })
            .catch(err => {
                console.error(err);
                alert('Erro de conexão');
                btn.disabled = false;
                btn.classList.remove('disabled');
                btn.innerHTML = originalContent;
            });
        });

        // Handle program filter tab clicks
        document.body.addEventListener('click', function(e) {
            const tab = e.target.closest('.program-filter-tab');
            if (!tab) return;
            
            e.preventDefault();
            
            const filterType = tab.dataset.filterType;
            if (!filterType) return;
            
            // Update tabs UI
            document.querySelectorAll('.program-filter-tab').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            
            // Filter cards
            let visibleCount = 0;
            const cards = document.querySelectorAll('.program-card');
            const emptyNotice = document.getElementById('empty-category-notice');
            
            cards.forEach(card => {
                if (card.dataset.categoryType === filterType) {
                    card.style.display = '';
                    card.style.opacity = '1';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Show/Hide empty notice
            if (emptyNotice) {
                emptyNotice.style.display = (visibleCount === 0) ? 'block' : 'none';
            }
        });
    </script>
</body>
</html>
