<!-- Admin Sidebar (Redesigned - Light/Dark Theme) -->
<aside class="admin-sidebar" role="navigation" id="admin-sidebar">
    <!-- Header -->
    <div class="sidebar-header">
        <div class="brand-wrapper">
            <div class="brand-row">
                <div class="brand-logo" style="background: linear-gradient(to top right, #06b6d4, #2563eb);">
                    <span class="material-icons-round text-white text-[20px]"
                        style="color: white; font-size: 20px;">bolt</span>
                </div>
                <h1 class="brand-title">
                    <?= htmlspecialchars($tenant['name']) ?>
                </h1>
            </div>

            <?php
            // Role Badge Logic
            $roleLabel = 'Membro';
            if (isset($user['role_name'])) {
                $roleLabels = [
                    'admin' => 'Admin',
                    'director' => 'Diretor',
                    'associate_director' => 'Diretor Assoc.',
                    'counselor' => 'Conselheiro',
                    'instructor' => 'Instrutor',
                    'pathfinder' => 'Desbravador',
                    'parent' => 'Responsável'
                ];
                $roleLabel = $roleLabels[$user['role_name']] ?? ucfirst($user['role_name']);
            }
            $permissionService = new \App\Services\PermissionService();
            ?>
            <div style="margin-top: 8px;">
                <span class="role-badge" style="background-color: #d1fae5; color: #065f46; border: 1px solid #6ee7b7;">
                    <?= htmlspecialchars($roleLabel) ?>
                </span>
            </div>
        </div>

        <!-- Toggle Button (Mobile) -->
        <button class="d-md-none mobile-sidebar-close" style="background: none; border: none; color: var(--text-muted); cursor: pointer;"
            id="mobile-sidebar-close">
            <span class="material-icons-round">close</span>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav custom-scrollbar">
        <!-- Dashboard (Fixed Link) -->
        <a href="<?= base_url($tenant['slug'] . '/admin') ?>"
            class="nav-item <?= str_ends_with($_SERVER['REQUEST_URI'], '/admin') || str_contains($_SERVER['REQUEST_URI'], '/admin?t=') ? 'active' : '' ?>">
            <span class="material-icons-round">dashboard</span>
            Dashboard
        </a>

        <!-- Mission Control / God Mode -->
        <!-- Mission Control / God Mode -->
        <a href="<?= base_url($tenant['slug'] . '/admin/especialidades/god-mode') ?>"
            class="nav-item <?= str_contains($_SERVER['REQUEST_URI'], '/admin/especialidades/god-mode') ? 'active' : '' ?>">
            <span class="material-icons-round">visibility</span>
            Mission Control 
            <span style="font-size: 0.6rem; background: var(--primary-color, #2563eb); color: white; padding: 2px 4px; border-radius: 4px; margin-left: auto; text-transform: uppercase;">Beta</span>
        </a>

        <div style="height: 0.5rem;"></div>

        <!-- Activities Group -->
        <div class="nav-group">
            <button class="nav-item justify-between" onclick="toggleSubmenu('activities-submenu')"
                style="justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <div class="nav-icon-container" style="color: #6366f1;"> <!-- Indigo-500 -->
                        <span class="material-icons-round">local_activity</span>
                    </div>
                    <span>Atividades</span>
                </div>
                <span class="material-icons-round transform transition-transform" id="arrow-activities-submenu"
                    style="transition: transform 0.2s;">expand_more</span>
            </button>

            <div id="activities-submenu" class="submenu-container">
                <div class="submenu-line"></div>


                <a href="<?= base_url($tenant['slug'] . '/admin/programas') ?>"
                    class="submenu-item <?= str_contains($_SERVER['REQUEST_URI'], '/admin/programas') ? 'active' : '' ?>">
                    <span class="submenu-dot" style="border-color: #06b6d4;"></span> <!-- Cyan-500 -->
                    Programas
                </a>

                <a href="<?= base_url($tenant['slug'] . '/admin/especialidades') ?>"
                    class="submenu-item <?= str_contains($_SERVER['REQUEST_URI'], '/admin/especialidades') ? 'active' : '' ?>">
                    <span class="submenu-dot" style="border-color: #f97316;"></span> <!-- Orange-500 -->
                    Especialidades
                </a>

                <a href="<?= base_url($tenant['slug'] . '/admin/classes') ?>"
                    class="submenu-item <?= str_contains($_SERVER['REQUEST_URI'], '/admin/classes') ? 'active' : '' ?>">
                    <span class="submenu-dot" style="border-color: #eab308;"></span> <!-- Yellow-500 -->
                    Classes
                </a>

                <a href="<?= base_url($tenant['slug'] . '/admin/categorias') ?>"
                    class="submenu-item <?= str_contains($_SERVER['REQUEST_URI'], '/admin/categorias') ? 'active' : '' ?>">
                    <span class="submenu-dot" style="border-color: #10b981;"></span> <!-- Emerald-500 -->
                    Categorias
                </a>
            </div>
        </div>

        <div style="margin: 0.5rem 0.5rem; height: 1px; background-color: var(--border-color);"></div>

        <!-- Users -->
        <a href="<?= base_url($tenant['slug'] . '/admin/usuarios') ?>"
            class="nav-item group <?= str_contains($_SERVER['REQUEST_URI'], '/admin/usuarios') ? 'active' : '' ?>">
            <span class="material-icons-round" style="color: #a855f7;">group</span> <!-- Purple-500 -->
            Usuários
        </a>

        <!-- Units -->
        <a href="<?= base_url($tenant['slug'] . '/admin/unidades') ?>"
            class="nav-item group <?= str_contains($_SERVER['REQUEST_URI'], '/admin/unidades') ? 'active' : '' ?>">
            <span class="material-icons-round" style="color: #eab308;">cottage</span> <!-- Yellow-500 -->
            Unidades
        </a>

        <!-- Permissions (Admin Only) -->
        <?php if ($permissionService->can('admin.access')): ?>
            <a href="<?= base_url($tenant['slug'] . '/admin/permissoes') ?>"
                class="nav-item group <?= str_contains($_SERVER['REQUEST_URI'], '/admin/permissoes') ? 'active' : '' ?>">
                <span class="material-icons-round" style="color: #d97706;">lock</span> <!-- Amber-600 -->
                Permissões
            </a>
        <?php endif; ?>

        <!-- Email Group -->
        <?php if ($permissionService->canAny(['notifications.broadcast', 'admin.access'])): ?>
            <a href="<?= base_url($tenant['slug'] . '/admin/email/inbox') ?>"
                class="nav-item group <?= str_contains($_SERVER['REQUEST_URI'], '/admin/email') ? 'active' : '' ?>">
                <span class="material-icons-round" style="color: #3b82f6;">mail</span> <!-- Blue-500 -->
                Email
            </a>
        <?php endif; ?>

        <!-- Invitations -->
        <?php if ($permissionService->canAny(['users.create', 'admin.access'])): ?>
            <a href="<?= base_url($tenant['slug'] . '/admin/convites') ?>"
                class="nav-item group <?= str_contains($_SERVER['REQUEST_URI'], '/admin/convites') ? 'active' : '' ?>">
                <span class="material-icons-round" style="color: #94a3b8;">confirmation_number</span> <!-- Slate-400 -->
                Convites
            </a>
        <?php endif; ?>

        <!-- Payments -->
        <a href="<?= base_url($tenant['slug'] . '/admin/financeiro') ?>"
            class="nav-item group <?= str_contains($_SERVER['REQUEST_URI'], '/admin/financeiro') ? 'active' : '' ?>">
            <span class="material-icons-round" style="color: #0ea5e9;">credit_card</span> <!-- Sky-500 -->
            Financeiro
        </a>

        <!-- Evaluation Center -->
        <a href="<?= base_url($tenant['slug'] . '/admin/aprovacoes') ?>"
            class="nav-item group <?= str_contains($_SERVER['REQUEST_URI'], '/admin/aprovacoes') ? 'active' : '' ?>">
            <span class="material-icons-round" style="color: var(--accent-cyan);">fact_check</span>
            Avaliações
        </a>

        <!-- Versions -->
        <?php if ($permissionService->can('admin.versions')): ?>
            <a href="<?= base_url($tenant['slug'] . '/admin/versoes') ?>"
                class="nav-item group <?= str_contains($_SERVER['REQUEST_URI'], '/admin/versoes') ? 'active' : '' ?>">
                <span class="material-icons-round" style="color: #60a5fa;">system_update_alt</span> <!-- Blue-400 -->
                Versões
            </a>
        <?php endif; ?>

        <!-- Feature Flags -->
        <?php if ($permissionService->can('admin.features')): ?>
            <a href="<?= base_url($tenant['slug'] . '/admin/features') ?>"
                class="nav-item group <?= str_contains($_SERVER['REQUEST_URI'], '/admin/features') ? 'active' : '' ?>">
                <span class="material-icons-round" style="color: #f43f5e;">flag</span> <!-- Rose-500 -->
                Feature Flags
            </a>
        <?php endif; ?>

        <!-- Notifications -->
        <a href="<?= base_url($tenant['slug'] . '/admin/notificacoes') ?>"
            class="nav-item group <?= str_contains($_SERVER['REQUEST_URI'], '/admin/notificacoes') ? 'active' : '' ?>">
            <span class="material-icons-round" style="color: #ec4899;">campaign</span> <!-- Pink-500 -->
            Notificações
        </a>

    </nav>
    <!-- App Switcher -->
    <div class="sidebar-footer" style="margin-top: auto; padding: 16px; border-top: 1px solid var(--border-color);">
        <a href="<?= base_url($tenant['slug'] . '/dashboard') ?>" 
           class="nav-item" 
           style="color: var(--accent-primary); font-weight: 800; background: #ecfeff; border-radius: 12px; border: 1px solid #cffafe;"
           hx-boost="false">
            <span class="material-icons-round" style="font-size: 20px;">rocket_launch</span>
            Painel Desbravador
        </a>
    </div>


</aside>




<script>
    function toggleSubmenu(id) {
        const submenu = document.getElementById(id);
        const arrow = document.getElementById('arrow-' + id);

        if (submenu.classList.contains('open')) {
            submenu.classList.remove('open');
            arrow.style.transform = 'rotate(0deg)';
        } else {
            submenu.classList.add('open');
            arrow.style.transform = 'rotate(180deg)';
        }
    }

    // Auto-open submenu if active child present on load or after swap
    function expandActiveSubmenus() {
        const activeLinks = document.querySelectorAll('.submenu-item.active');
        activeLinks.forEach(link => {
            const parent = link.closest('.submenu-container');
            if (parent && !parent.classList.contains('open')) {
                parent.classList.add('open');
                const arrowId = 'arrow-' + parent.id;
                const arrow = document.getElementById(arrowId);
                if (arrow) arrow.style.transform = 'rotate(180deg)';
            }
        });
    }

    document.addEventListener('DOMContentLoaded', expandActiveSubmenus);
    document.addEventListener('htmx:afterSwap', expandActiveSubmenus);
</script>