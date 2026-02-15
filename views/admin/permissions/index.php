<?php
/**
 * Permissions Admin - Master Design Compliance
 * Strictly follows docs/MASTER_DESIGN.md & public/assets/css/admin.css
 */
?>

<!-- View Specific Styles for Switch Component (Not in admin.css yet) -->
<style>
    /* Toggle Switch - Using Design Token Variables */
    .switch {
        position: relative;
        display: inline-block;
        width: 48px;
        height: 26px;
        flex-shrink: 0;
    }
    
    .switch input { opacity: 0; width: 0; height: 0; }
    
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: var(--border-color); /* Neutral gray */
        transition: var(--transition-base); /* Standard transition */
        border-radius: var(--radius-full);
    }
    
    .slider:before {
        position: absolute;
        content: "";
        height: 20px;
        width: 20px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: var(--transition-bounce); /* Global bounce */
        border-radius: 50%;
        box-shadow: var(--shadow-sm);
    }
    
    input:checked + .slider {
        background-color: var(--primary); /* Cyan */
    }
    
    input:checked + .slider:before {
        transform: translateX(22px);
    }

    /* Permission Item */
    .perm-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem;
        border-radius: var(--radius-lg);
        transition: var(--transition-base);
        cursor: pointer;
        border: 1px solid transparent;
        margin-bottom: 0.5rem;
    }

    .perm-item:hover {
        background-color: var(--bg-hover);
        border-color: var(--border-color);
    }

    .perm-key {
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.75rem;
        color: var(--text-muted);
        background: var(--bg-dark);
        padding: 2px 6px;
        border-radius: var(--radius-sm);
        margin-top: 4px;
        display: inline-flex;
    }
    
    /* Role Pills - Using Dashboard Card Style for Container */
    .role-pills-container {
        display: flex;
        gap: 0.5rem;
        background: var(--bg-card);
        padding: 0.25rem;
        border-radius: var(--radius-full);
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-sm);
    }
    
    .role-pill {
        padding: 0.5rem 1rem;
        border-radius: var(--radius-full);
        font-weight: 600;
        font-size: 0.875rem;
        color: var(--text-muted);
        background: transparent;
        border: none;
        cursor: pointer;
        transition: var(--transition-base);
    }
    
    .role-pill:hover {
        color: var(--text-dark);
        background: var(--bg-hover);
    }
    
    .role-pill.active {
        background: var(--primary);
        color: white;
        box-shadow: var(--shadow-cyan);
    }

    /* Mobile Responsive Toolbar */
    @media (max-width: 768px) {
        .permissions-toolbar {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 1rem;
            margin: 0 0 1rem 0 !important;
            padding: 1rem !important;
            position: relative !important;
        }

        .role-pills-wrapper {
            width: 100%;
        }

        .permissions-toolbar .header-actions {
            width: 100%;
            justify-content: space-between;
        }
    }
</style>

<form id="permissionsForm">
    <input type="hidden" name="role_id" id="roleIdInput" value="<?= $roles[0]['id'] ?? 0 ?>">

    <!-- Permissions Toolbar (Sticky, Separate from Header) -->
    <div class="permissions-toolbar" style="
        position: sticky; 
        top: 0; 
        z-index: 50; 
        margin: -2rem -2rem 2rem -2rem; 
        padding: 1rem 2rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    ">
        <!-- Role Switcher -->
        <div class="role-pills-wrapper" style="overflow-x: auto; -webkit-overflow-scrolling: touch; scrollbar-width: none;">
            <div class="role-pills-container" style="display: flex; gap: 0.5rem; width: max-content; padding-bottom: 4px;">
                <?php foreach ($roles as $i => $role): ?>
                    <button type="button" class="role-pill <?= $i === 0 ? 'active' : '' ?>" 
                            data-role-id="<?= $role['id'] ?>"
                            onclick="selectRole(<?= $role['id'] ?>)"
                            style="white-space: nowrap;">
                        <?= htmlspecialchars($role['display_name']) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Actions -->
        <div class="header-actions" style="display: flex; align-items: center;">
            <div style="font-size: 0.875rem; color: var(--text-muted); margin-right: 1rem;">
                <span id="changeCount" style="font-weight: 700; color: var(--primary);">0</span> ativos
            </div>
            <button type="submit" class="btn-save-fab" id="saveBtn" style="
                background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
                color: white;
                border: none;
                padding: 0.625rem 1.25rem;
                border-radius: var(--radius-lg);
                font-weight: 600;
                cursor: pointer;
                display: flex;
                align-items: center;
                gap: 0.5rem;
                box-shadow: var(--shadow-cyan);
                transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            ">
                <span class="material-icons-round">save</span>
                Salvar
            </button>
        </div>
    </div>

    <!-- Dashboard Grid -->
    <div class="dashboard-grid" style="grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));">
        <?php foreach ($groupedPermissions as $group => $perms): ?>
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <!-- Dynamic Icons based on Group Name -->
                    <?php 
                        $icon = match(strtolower($group)) {
                            'usuarios', 'users' => 'group',
                            'financeiro', 'finances' => 'payments',
                            'unidades', 'units' => 'camping',
                            'events' => 'event',
                            'admin' => 'admin_panel_settings',
                            'activities' => 'local_activity',
                            'dashboard' => 'dashboard',
                            'notifications' => 'notifications',
                            'proofs', 'provas' => 'assignment_turned_in',
                            'quizzes' => 'quiz',
                            default => 'lock'
                        };
                        
                        // Assign colors based on consistency with dashboard
                        $color = match($icon) {
                            'group' => '#8b5cf6', // Purple
                            'payments' => '#10b981', // Emerald
                            'camping' => '#f59e0b', // Amber
                            'event' => '#ec4899', // Pink
                            'dashboard' => '#3b82f6', // Blue
                            default => 'var(--primary)'
                        };
                    ?>
                    <span class="material-icons-round" style="color: <?= $color ?>;"><?= $icon ?></span>
                    <h3><?= htmlspecialchars($group) ?></h3>
                </div>
                
                <div class="dashboard-card-body">
                    <?php foreach ($perms as $perm): ?>
                        <label class="perm-item">
                            <div style="display: flex; flex-direction: column;">
                                <span style="font-weight: 600; color: var(--text-dark); font-size: 0.9375rem;">
                                    <?= htmlspecialchars($perm['name']) ?>
                                </span>
                                <span class="perm-key"><?= htmlspecialchars($perm['key']) ?></span>
                            </div>
                            <div class="switch">
                                <input type="checkbox" name="permissions[]" value="<?= $perm['id'] ?>" data-permission-id="<?= $perm['id'] ?>">
                                <span class="slider"></span>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</form>

<div id="toast-container" style="position: fixed; bottom: 2rem; right: 2rem; z-index: 10000; display: flex; flex-direction: column; gap: 0.5rem;"></div>

<script>
    // Constants
    window.rolePermissions = <?= json_encode($rolePermissions) ?>;
    
    // Toast Functionality (Global Standard)
    window.showToast = function(message, type = 'success') {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.textContent = message;
        
        const bg = type === 'error' ? 'var(--gradient-danger)' : 'var(--gradient-success)';
        
        toast.style.cssText = `
            background: ${bg};
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius-full);
            font-weight: 600;
            box-shadow: var(--shadow-lg);
            opacity: 0;
            transform: translateY(20px);
            transition: var(--transition-bounce);
            display: flex; align-items: center; gap: 8px;
        `;
        
        container.appendChild(toast);
        
        requestAnimationFrame(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translateY(0)';
        });
        
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(20px)';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    function updateChangeCount() {
        const active = document.querySelectorAll('input[name="permissions[]"]:checked').length;
        document.getElementById('changeCount').textContent = active;
    }

    window.selectRole = function(roleId) {
        document.getElementById('roleIdInput').value = roleId;
        
        document.querySelectorAll('.role-pill').forEach(t => t.classList.remove('active'));
        const tab = document.querySelector(`.role-pill[data-role-id="${roleId}"]`);
        if (tab) tab.classList.add('active');
        
        const currentPerms = rolePermissions[roleId] || {};
        const checkboxes = document.querySelectorAll('input[name="permissions[]"]');
        
        checkboxes.forEach((cb) => {
            cb.checked = !!currentPerms[cb.dataset.permissionId];
        });
        
        updateChangeCount();
    }

    document.querySelectorAll('input[name="permissions[]"]').forEach(cb => {
        cb.addEventListener('change', updateChangeCount);
    });

    document.getElementById('permissionsForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('saveBtn');
        const originalText = btn.innerHTML;
        
        btn.disabled = true;
        btn.innerHTML = `<span class="material-icons-round" style="animation:spin 1s linear infinite; margin-right: 8px;">refresh</span> Salvando...`;
        
        try {
            const formData = new FormData(e.target);
            const response = await fetch('<?= base_url($tenant['slug'] . '/admin/permissoes') ?>', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            
            if (data.success) {
                showToast('Permissões atualizadas com sucesso!');
                const roleId = formData.get('role_id');
                rolePermissions[roleId] = {}; 
                formData.getAll('permissions[]').forEach(pid => {
                    rolePermissions[roleId][pid] = true;
                });
            } else {
                showToast(data.error || 'Erro ao salvar', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Erro de conexão com o servidor', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    });

    // Init
    <?php if (!empty($roles)): ?>
        selectRole(<?= $roles[0]['id'] ?>);
    <?php endif; ?>

    // Spin Animation
    const style = document.createElement('style');
    style.innerHTML = `@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }`;
    document.head.appendChild(style);
</script>
