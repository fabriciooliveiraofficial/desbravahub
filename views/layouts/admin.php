<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'DesbravaHub' ?></title>

    <!-- PWA -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#f3f4f6" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#0f172a" media="(prefers-color-scheme: dark)">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="DesbravaHub Admin">
    <link rel="apple-touch-icon" href="/assets/images/icon-192.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="<?= asset_url('css/admin.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= asset_url('css/evaluations.css') ?>?v=<?= time() ?>">

    <!-- HTMX -->
    <script src="https://unpkg.com/htmx.org@1.9.10"></script>

    <style>
        /* Helper for Iconify visibility */
        iconify-icon {
            display: inline-block !important;
            width: 1em;
            height: 1em;
            min-width: 1em;
            min-height: 1em;
            vertical-align: middle;
        }
    </style>

    <script>
        // Global Autocomplete Setup
        window.setupAutocomplete = function(inputId, dropdownId, warningId, searchUrl) {
            function init() {
                const input = document.getElementById(inputId);
                const dropdown = document.getElementById(dropdownId);
                const warning = document.getElementById(warningId);

                // If elements don't exist, stop.
                if (!input || !dropdown) return;
                
                // If input is not visible (e.g. hidden modal), we might still init, 
                // but let's check if we already did to avoid duplicates.
                if (input.dataset.autocompleteInitialized) return;
                input.dataset.autocompleteInitialized = 'true';

                let debounceTimer = null;

                input.addEventListener('input', () => {
                    clearTimeout(debounceTimer);
                    const query = input.value.trim();
                    
                    if (query.length < 2) {
                        dropdown.style.display = 'none';
                        if(warning) warning.style.display = 'none';
                        return;
                    }

                    debounceTimer = setTimeout(async () => {
                        try {
                            const response = await fetch(`${searchUrl}?q=${encodeURIComponent(query)}`);
                            const data = await response.json();

                            if (data.results && data.results.length > 0) {
                                dropdown.innerHTML = data.results.map(s => `
                                    <div class="autocomplete-item" style="padding: 10px 12px; cursor: pointer; display: flex; align-items: center; gap: 8px; border-bottom: 1px solid rgba(0,0,0,0.05);">
                                        <span style="font-size: 1.2rem;">${s.badge_icon || '📘'}</span>
                                        <span>${s.name}</span>
                                    </div>
                                `).join('');
                                dropdown.style.display = 'block';
                                if(warning) warning.style.display = 'block';

                                dropdown.querySelectorAll('.autocomplete-item').forEach(item => {
                                    item.addEventListener('click', () => {
                                        input.value = item.querySelector('span:last-child').textContent;
                                        dropdown.style.display = 'none';
                                        if(warning) warning.style.display = 'block'; // Keep warning if selected? Or hide? 
                                        // User wants to avoid duplication, so if they select an existing one, warning should act as "This exists".
                                        // The user message said: "campo retorna a mensagem... mas exibe os resultados".
                                        // If they select it, they ARE duplicating it?
                                        // Actually if they click, they explicitly chose the name. 
                                        // The warning "Já existe..." is valid.
                                    });
                                    item.addEventListener('mouseenter', () => item.style.background = 'rgba(0,0,0,0.05)');
                                    item.addEventListener('mouseleave', () => item.style.background = 'transparent');
                                });
                            } else {
                                dropdown.style.display = 'none';
                                if(warning) warning.style.display = 'none';
                            }
                        } catch (err) {
                            console.error('Autocomplete error:', err);
                        }
                    }, 300);
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', (e) => {
                    if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                        dropdown.style.display = 'none';
                    }
                });
            }

            // Run immediately if DOM is ready, otherwise wait
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', init);
            } else {
                init();
            }
        };
    </script>
    <script src="<?= asset_url('js/toast.js') ?>"></script>
    <script src="<?= asset_url('js/uas.js') ?>"></script>

    <style>
        /* Helper for Iconify visibility */
        iconify-icon {
            display: inline-block !important;
            width: 1em;
            height: 1em;
            min-width: 1em;
            min-height: 1em;
            vertical-align: middle;
        }
    </style>


    <style>
        /* Fade transition for HTMX swaps */
        .fade-me-out.htmx-swapping {
            opacity: 0;
            transition: opacity 200ms ease-out;
        }

        .fade-me-in {
            opacity: 1;
            transition: opacity 200ms ease-in;
        }
    </style>
</head>

<body class="<?= isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark' ? 'dark' : '' ?>" hx-boost="true">

    <?php
    // Sidebar
    require BASE_PATH . '/views/admin/partials/sidebar.php';
    ?>

    <main class="admin-main">
        <?php
        // Header
        require BASE_PATH . '/views/admin/partials/header.php';
        ?>

        <!-- Content Area -->
        <div id="main-content" class="fade-me-in">
            <?= $content ?>
        </div>
    </main>

    <script>
        // Re-initialize scripts after HTMX swap
        document.body.addEventListener('htmx:afterSwap', function (evt) {
            // Re-run theme toggle logic if needed or any other global init
            // For now, most legacy scripts might need a tweak to run on load AND after swap
            // but we'll test first.
        });
    </script>
    <script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"></script>
    <?php require BASE_PATH . '/views/admin/partials/footer.php'; ?>

    <!-- Toast Container for OOB Swaps -->
    <div id="toast-container" class="toast-container"></div>

    <!-- PWA Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(registration => {
                        console.log('[SW] Registered with scope:', registration.scope);
                    })
                    .catch(err => {
                        console.error('[SW] Registration failed:', err);
                    });
            });
        }
    </script>

    <!-- Diagnostic Script (User Requested) -->
    <script>
    document.body.addEventListener('htmx:afterRequest', function(evt) {
        console.log('HTMX Request Finished:', evt.detail);
        if (evt.detail.xhr.status >= 400) {
            console.error('HTMX Error:', evt.detail.xhr.status, evt.detail.xhr.responseText);
        }
    });
    </script>

    <style>
    .toast-container {
        position: fixed;
        bottom: 30px;
        right: 30px;
        z-index: 999999;
        display: flex;
        flex-direction: column;
        gap: 10px;
        pointer-events: none;
    }
    .toast-message {
        padding: 16px 24px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.95rem;
        box-shadow: 0 8px 30px rgba(0,0,0,0.2);
        opacity: 0;
        transform: translateY(20px);
        animation: slideIn 0.3s forwards, fadeOut 0.3s forwards 4s;
        background: white;
        display: flex;
        align-items: center;
        gap: 10px;
        pointer-events: auto;
    }
    @keyframes slideIn {
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes fadeOut {
        to { opacity: 0; transform: translateY(10px); }
    }
    .toast-success { background: linear-gradient(135deg, #22c55e, #16a34a); color: white; }
    .toast-error { background: linear-gradient(135deg, #ef4444, #dc2626); color: white; }
    .toast-warning { background: linear-gradient(135deg, #f59e0b, #d97706); color: white; }
    </style>
    <!-- Iconify Script (Local copy for reliability) -->
    <script src="<?= asset_url('js/iconify-icon.min.js') ?>"></script>
    <script>
        // Diagnostic for Iconify
        window.addEventListener('load', () => {
            const status = typeof IconifyIcon !== 'undefined' ? 'READY' : 'FAILED';
            console.log('Iconify check:', status);
            if (status === 'FAILED') {
                console.error('Iconify failed to load. Icons will not render.');
            }
        });
    </script>
</body>
</html>