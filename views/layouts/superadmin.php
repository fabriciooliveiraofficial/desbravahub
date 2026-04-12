<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DesbravaHub | Super Admin</title>
    
    <!-- Modern Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Icons & Utilities -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Design System -->
    <link rel="stylesheet" href="/assets/css/superadmin.css?v=5.0">
</head>
<body class="bg-sa-bg text-sa-text font-['Plus_Jakarta_Sans'] antialiased min-h-screen">

    <div class="flex min-h-screen relative">
        
        <!-- Sidebar: Titanium Clarity Design -->
        <aside class="w-72 bg-white border-r border-sa-border flex flex-col sticky top-0 h-screen z-50 shadow-[4px_0_24px_rgba(0,0,0,0.02)]">
            <!-- Brand Core -->
            <div class="p-8 border-b border-sa-border bg-slate-50/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-sa-primary flex items-center justify-center shadow-lg shadow-blue-500/20">
                        <span class="material-symbols-rounded text-white text-2xl">shield_person</span>
                    </div>
                    <div>
                        <span class="text-lg font-extrabold text-slate-900 tracking-tight block leading-none">DesbravaHub</span>
                        <span class="text-[10px] text-sa-primary font-bold uppercase tracking-widest">Painel Master</span>
                    </div>
                </div>
            </div>

            <!-- Navigation Network -->
            <nav class="flex-1 p-6 space-y-1 overflow-y-auto">
                <p class="text-[10px] font-bold text-sa-text-dim uppercase tracking-[.15em] px-4 mb-4">Gerenciamento</p>
                
                <a href="/super-admin/dashboard" class="flex items-center gap-3 px-4 py-3 rounded-lg text-sa-text-muted hover:text-sa-primary hover:bg-sa-primary/5 transition-all no-underline group <?= strpos($_SERVER['REQUEST_URI'], '/super-admin/dashboard') !== false || $_SERVER['REQUEST_URI'] == '/super-admin' ? 'bg-sa-primary/5 text-sa-primary' : '' ?>">
                    <span class="material-symbols-rounded text-xl transition-colors">dashboard</span>
                    <span class="font-semibold text-sm">Dashboard</span>
                </a>

                <a href="/super-admin/clubs" class="flex items-center gap-3 px-4 py-3 rounded-lg text-sa-text-muted hover:text-sa-primary hover:bg-sa-primary/5 transition-all no-underline group <?= strpos($_SERVER['REQUEST_URI'], '/super-admin/clubs') !== false ? 'bg-sa-primary/5 text-sa-primary' : '' ?>">
                    <span class="material-symbols-rounded text-xl transition-colors">lan</span>
                    <span class="font-semibold text-sm">Franquias</span>
                </a>

                <a href="/super-admin/users" class="flex items-center gap-3 px-4 py-3 rounded-lg text-sa-text-muted hover:text-sa-primary hover:bg-sa-primary/5 transition-all no-underline group <?= strpos($_SERVER['REQUEST_URI'], '/super-admin/users') !== false ? 'bg-sa-primary/5 text-sa-primary' : '' ?>">
                    <span class="material-symbols-rounded text-xl transition-colors">group</span>
                    <span class="font-semibold text-sm">Usuários Master</span>
                </a>

                <div class="pt-6 mb-4 px-4 text-[10px] font-bold text-sa-text-dim uppercase tracking-[.15em]">Sistemas IA</div>

                <a href="/super-admin/scraper" class="flex items-center gap-3 px-4 py-3 rounded-lg text-sa-text-muted hover:text-sa-secondary hover:bg-sa-secondary/5 transition-all no-underline group <?= strpos($_SERVER['REQUEST_URI'], '/super-admin/scraper') !== false ? 'bg-sa-secondary/5 text-sa-secondary' : '' ?>">
                    <span class="material-symbols-rounded text-xl transition-colors">precision_manufacturing</span>
                    <span class="font-semibold text-sm">Super Scraper</span>
                </a>

                <a href="/super-admin/migracao" class="flex items-center gap-3 px-4 py-3 rounded-lg text-sa-text-muted hover:text-sa-secondary hover:bg-sa-secondary/5 transition-all no-underline group <?= strpos($_SERVER['REQUEST_URI'], '/super-admin/migracao') !== false ? 'bg-sa-secondary/5 text-sa-secondary' : '' ?>">
                    <span class="material-symbols-rounded text-xl transition-colors">database</span>
                    <span class="font-semibold text-sm">Migração IA</span>
                </a>

                <div class="pt-6 mb-4 px-4 text-[10px] font-bold text-sa-text-dim uppercase tracking-[.15em]">Suporte</div>

                <a href="/super-admin/suporte" class="flex items-center gap-3 px-4 py-3 rounded-lg text-sa-text-muted hover:text-sa-accent hover:bg-sa-accent/5 transition-all no-underline group <?= strpos($_SERVER['REQUEST_URI'], '/super-admin/suporte') !== false ? 'bg-sa-accent/5 text-sa-accent' : '' ?>">
                    <span class="material-symbols-rounded text-xl transition-colors">support_agent</span>
                    <span class="font-semibold text-sm">Canal de Chamados</span>
                </a>
            </nav>

            <!-- Identity Module -->
            <div class="p-6 border-t border-sa-border bg-slate-50/30">
                <div class="flex items-center gap-3 p-3 rounded-xl bg-white border border-sa-border shadow-sm">
                    <div class="w-9 h-9 rounded-lg bg-sa-primary/10 text-sa-primary flex items-center justify-center font-bold text-xs">
                        <?= substr($user['name'] ?? 'A', 0, 1) ?>
                    </div>
                    <div class="flex-1 overflow-hidden">
                        <p class="text-[11px] font-bold text-slate-900 truncate"><?= htmlspecialchars($user['name'] ?? 'Administrador') ?></p>
                        <p class="text-[9px] text-sa-primary font-black uppercase tracking-widest">Nível Master</p>
                    </div>
                    <a href="/super-admin/logout" class="text-sa-text-dim hover:text-sa-error transition-colors no-underline" title="Encerrar Sessão">
                        <span class="material-symbols-rounded text-lg">logout</span>
                    </a>
                </div>
            </div>
        </aside>

        <!-- Main Workspace -->
        <main class="flex-1 flex flex-col relative h-screen overflow-y-auto">
            
            <!-- Modern Topbar -->
            <header class="sticky top-0 z-40 h-20 bg-white/80 backdrop-blur-md border-b border-sa-border px-10 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <span class="text-[10px] font-bold text-sa-primary bg-sa-primary/5 px-3 py-1 rounded-full border border-sa-primary/10 uppercase tracking-widest">Sistema: Online</span>
                    <div class="h-4 w-[1px] bg-sa-border"></div>
                    <span class="text-[10px] font-semibold text-sa-text-dim uppercase tracking-widest"><?= date('l, d F Y') ?></span>
                </div>

                <div class="flex items-center gap-4">
                    <div class="flex items-center bg-sa-surface-muted rounded-full px-4 py-1.5 border border-sa-border group focus-within:border-sa-primary transition-all">
                        <span class="material-symbols-rounded text-sa-text-dim text-sm mr-2">search</span>
                        <input type="text" placeholder="Pesquisar..." class="bg-transparent border-none outline-none text-xs text-sa-text w-48 font-medium">
                    </div>
                    <button class="w-9 h-9 flex items-center justify-center rounded-full text-sa-text-dim hover:text-sa-primary hover:bg-sa-primary/5 transition-all">
                        <span class="material-symbols-rounded text-xl">notifications</span>
                    </button>
                </div>
            </header>

            <!-- Content Area -->
            <div class="flex-1">
                <?= $content ?>
            </div>

            <!-- Modern Footer -->
            <footer class="p-10 border-t border-sa-border bg-white">
                <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4">
                    <p class="text-[11px] font-bold text-sa-text-dim uppercase tracking-widest">
                        &copy; <?= date('Y') ?> <span class="text-slate-900">DesbravaHub</span> Platform Core
                    </p>
                    <div class="flex items-center gap-6 text-[11px] font-bold text-sa-text-dim uppercase tracking-widest">
                        <a href="#" class="no-underline hover:text-sa-primary transition-colors">Segurança</a>
                        <a href="#" class="no-underline hover:text-sa-primary transition-colors">Suporte</a>
                        <a href="#" class="no-underline hover:text-sa-primary transition-colors">v5.0.0</a>
                    </div>
                </div>
            </footer>
        </main>
    </div>

</body>
</html>
