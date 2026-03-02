<?php
/**
 * Super Admin Standalone Login View
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Super Admin Access') ?> - DesbravaHub</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Google Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    
    <!-- Tailwind CSS via CDN -->
    <script>/* suppress tw cdn warn */const _cw=console.warn;console.warn=(...a)=>{if(a[0]&&typeof a[0]==='string'&&a[0].includes('cdn.tailwindcss.com'))return;_cw.apply(console,a)};</script>
    <script src="https://cdn.tailwindcss.com"></script>
    
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                        jakarta: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                    colors: {
                        sa: {
                            purple: '#6d28d9',      /* Violet 700 */
                            purple_hover: '#5b21b6',/* Violet 800 */
                            purple_light: '#ede9fe',/* Violet 100 */
                            purple_border: '#8b5cf6',/* Violet 500 */
                            dark: '#0f172a',        /* Slate 900 */
                            card: '#1e293b',        /* Slate 800 */
                            border: '#334155',      /* Slate 700 */
                            text: '#f8fafc',        /* Slate 50 */
                            text_muted: '#94a3b8',  /* Slate 400 */
                        }
                    }
                }
            }
        }
    </script>
    
    <style>
        body {
            background-color: #0f172a;
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-image: 
                radial-gradient(at 0% 0%, hsla(253,16%,7%,1) 0, transparent 50%), 
                radial-gradient(at 50% 0%, hsla(225,39%,30%,0.1) 0, transparent 50%), 
                radial-gradient(at 100% 0%, hsla(339,49%,30%,0.1) 0, transparent 50%);
        }

        .sa-input {
            background: #1e293b;
            border: 1px solid #334155;
            color: #f8fafc;
            transition: all 0.2s ease;
        }

        .sa-input:focus {
            border-color: #8b5cf6;
            box-shadow: 0 0 0 2px rgba(139, 92, 246, 0.2);
            outline: none;
        }

        .sa-btn {
            background: linear-gradient(135deg, #6d28d9, #4c1d95);
            color: white;
            transition: all 0.3s ease;
        }

        .sa-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(109, 40, 217, 0.4);
            background: linear-gradient(135deg, #7c3aed, #5b21b6);
        }

        .sa-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
    </style>
</head>
<body class="antialiased selection:bg-sa-purple selection:text-white">

    <main class="w-full max-w-md p-6">
        
        <!-- Logo / Branding -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-sa-purple to-purple-400 mb-4 shadow-lg shadow-sa-purple/30">
                <span class="material-symbols-rounded text-white text-3xl">admin_panel_settings</span>
            </div>
            <h1 class="text-3xl font-bold font-jakarta bg-clip-text text-transparent bg-gradient-to-r from-white to-sa-text_muted">
                Super Admin
            </h1>
            <p class="text-sa-text_muted mt-2">Acesso restrito à equipe técnica.</p>
        </div>

        <!-- Alert (Error Message) -->
        <?php if(isset($_SESSION['flash_error'])): ?>
            <div class="bg-red-900/40 border border-red-500/50 text-red-200 px-4 py-3 rounded-xl mb-6 flex items-start">
                <span class="material-symbols-rounded mr-2 text-red-400">error</span>
                <span><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <!-- Login Card -->
        <div class="bg-sa-card border border-sa-border rounded-2xl p-6 md:p-8 shadow-2xl relative overflow-hidden">
            <!-- Subtle accent top line -->
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-sa-purple to-transparent opacity-50"></div>

            <form id="saLoginForm" class="space-y-6">
                
                <div>
                    <label for="email" class="block text-sm font-medium text-sa-text_muted mb-1.5">E-mail Corporativo</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-symbols-rounded text-sa-text_muted">mail</span>
                        </div>
                        <input type="email" id="email" name="email" required autocomplete="username"
                               class="sa-input w-full rounded-xl pl-10 pr-4 py-3" 
                               placeholder="nome@desbravahub.com">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-sm font-medium text-sa-text_muted">Senha Mestra</label>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-symbols-rounded text-sa-text_muted">lock</span>
                        </div>
                        <input type="password" id="password" name="password" required autocomplete="current-password"
                               class="sa-input w-full rounded-xl pl-10 pr-4 py-3" 
                               placeholder="••••••••">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" id="submitBtn" class="sa-btn w-full flex items-center justify-center font-bold rounded-xl py-3 px-4">
                        <span id="btnText">Acessar Sistema</span>
                        <span id="btnIcon" class="material-symbols-rounded ml-2 text-xl">login</span>
                    </button>
                    <div id="btnSpinner" class="hidden text-center text-sm text-sa-text_muted mt-4">
                        <span class="material-symbols-rounded animate-spin">refresh</span> Autenticando...
                    </div>
                </div>
                
                <div id="errorMessage" class="hidden bg-red-900/30 border border-red-500/30 text-red-300 rounded-lg p-3 text-sm flex items-start">
                    <span class="material-symbols-rounded text-red-400 mr-2 text-lg">warning</span>
                    <span id="errorText">Erro ao autenticar.</span>
                </div>

            </form>
        </div>
        
        <!-- Footer -->
        <div class="mt-8 text-center">
            <p class="text-xs text-sa-text_muted/60">
                &copy; <?= date('Y') ?> DesbravaHub. Conexões encriptadas (TLS 1.3).
            </p>
        </div>

    </main>

    <script>
        document.getElementById('saLoginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const btn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');
            const btnIcon = document.getElementById('btnIcon');
            const spinner = document.getElementById('btnSpinner');
            const errorDiv = document.getElementById('errorMessage');
            const errorText = document.getElementById('errorText');
            
            // Reset state
            errorDiv.classList.add('hidden');
            btn.disabled = true;
            btnText.classList.add('opacity-0');
            btnIcon.classList.add('opacity-0');
            spinner.classList.remove('hidden');
            
            const formData = new FormData();
            formData.append('email', document.getElementById('email').value);
            formData.append('password', document.getElementById('password').value);
            
            try {
                const response = await fetch('/super-admin/login', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (response.ok && data.success) {
                    spinner.innerHTML = '<span class="material-symbols-rounded text-green-400">check_circle</span> Acesso Liberado!';
                    setTimeout(() => {
                        window.location.href = data.redirect || '/super-admin/dashboard';
                    }, 500);
                } else {
                    throw new Error(data.error || 'Falha na autenticação.');
                }
            } catch (error) {
                // Restore button state
                btn.disabled = false;
                btnText.classList.remove('opacity-0');
                btnIcon.classList.remove('opacity-0');
                spinner.classList.add('hidden');
                
                // Show error
                errorText.textContent = error.message;
                errorDiv.classList.remove('hidden');
            }
        });
    </script>
</body>
</html>
