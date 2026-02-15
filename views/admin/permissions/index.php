<?php
/**
 * Permissions Admin - Vibrant Light Edition v6.0
 * No Dark Buttons, High Visibility Cyan Theme
 */
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">

<style>
    :root {
        --font-primary: 'Inter', sans-serif;
        --font-mono: 'JetBrains Mono', monospace;
        --bg-body: #f1f5f9;
        --bg-card: #ffffff;
        --text-dark: #1e293b; /* Slate 800 (Not Black) */
        --text-medium: #475569; /* Slate 600 */
        --text-light: #64748b; /* Slate 500 */
        
        /* Vibrant Theme Colors */
        --accent-primary: #06b6d4; /* Cyan 500 */
        --accent-hover: #0891b2; /* Cyan 600 */
        --accent-on: #06b6d4; 
        --accent-off: #cbd5e1;
        --border-color: #cbd5e1;
    }
    
    @keyframes fadeEnterUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    @keyframes slideInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes fadeScaleOut {
        from { opacity: 1; transform: scale(1); }
        to { opacity: 0; transform: scale(0.95); }
    }

    .animate-enter {
        animation: fadeEnterUp 0.5s ease-out forwards;
        opacity: 0;
    }
    
    .toast-enter {
        animation: slideInUp 0.3s ease-out forwards;
    }
    
    .toast-exit {
        animation: fadeScaleOut 0.3s ease-in forwards;
    }

    .perm-wrapper {
        font-family: var(--font-primary);
        max-width: 1400px;
        margin: 0 auto;
        padding-bottom: 120px;
        color: var(--text-dark);
    }

    /* Header */
    /* Header Alignments */
    .page-toolbar {
        margin-bottom: 24px;
        text-align: left;
    }
    .page-toolbar h2 {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-dark);
        margin: 0;
    }

    /* Role Switcher */
    .role-switcher-container {
        display: flex;
        justify-content: center;
        margin-bottom: 40px;
        position: sticky;
        top: 20px;
        z-index: 100;
    }

    .role-switcher {
        background: #fff;
        padding: 6px;
        border-radius: 100px;
        display: inline-flex;
        gap: 8px;
        box-shadow: 0 4px 20px rgba(6, 182, 212, 0.15); /* Cyan shadow hint */
        border: 2px solid #e2e8f0;
        overflow-x: auto;
        max-width: 90vw;
    }

    .role-pill {
        padding: 10px 24px;
        border-radius: 100px;
        border: 2px solid transparent;
        background: transparent;
        color: var(--text-medium);
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
        white-space: nowrap;
        font-size: 0.95rem;
    }

    .role-pill:hover {
        color: var(--accent-primary);
        background: #ecfeff; /* Light Cyan */
        border-color: #cffafe;
    }

    .role-pill.active {
        background: var(--accent-primary);
        color: #fff;
        border-color: var(--accent-primary);
        box-shadow: 0 4px 12px rgba(6, 182, 212, 0.4);
    }

    /* Permission Groups Grid */
    .perm-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        gap: 24px;
    }

    /* Group Card */
    .group-card {
        background: var(--bg-card);
        border-radius: 20px;
        overflow: hidden;
        border: 1px solid var(--border-color);
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        display: flex;
        flex-direction: column;
    }
    
    .group-header-strip {
        background: #fff;
        padding: 24px;
        border-bottom: 2px solid #f1f5f9;
        font-weight: 800;
        color: var(--text-dark);
        display: flex;
        align-items: center;
        gap: 16px;
        font-size: 1.1rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .group-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: #ecfeff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        color: var(--accent-primary);
    }

    .toggle-list {
        padding: 24px;
        display: flex;
        flex-direction: column;
        gap: 16px;
        background: #fcfcfc;
    }

    /* Toggle Row */
    .toggle-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px;
        border-radius: 16px;
        background: #fff;
        border: 2px solid #e2e8f0;
        transition: all 0.2s;
        cursor: pointer;
    }

    .toggle-row:hover {
        border-color: var(--accent-primary);
        box-shadow: 0 4px 12px rgba(6, 182, 212, 0.1);
    }

    .toggle-info {
        flex: 1;
        padding-right: 16px;
    }
    .toggle-name {
        font-weight: 700;
        color: var(--text-dark);
        font-size: 1rem;
        margin-bottom: 6px;
        display: block;
    }
    .toggle-key {
        font-family: var(--font-mono);
        color: var(--text-medium);
        font-size: 0.8rem;
        background: #f1f5f9;
        padding: 4px 8px;
        border-radius: 6px;
        display: inline-block;
        font-weight: 600;
        border: 1px solid #e2e8f0;
    }

    /* The Switch */
    .switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 34px;
        flex-shrink: 0;
    }
    
    .switch input { 
        opacity: 0;
        width: 0;
        height: 0;
    }
    
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: var(--accent-off); /* Gray */
        transition: .2s;
        border-radius: 34px;
    }
    
    .slider:before {
        position: absolute;
        content: "";
        height: 26px;
        width: 26px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: .2s;
        border-radius: 50%;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    
    input:checked + .slider {
        background-color: var(--accent-primary); /* Cyan */
    }
    
    input:checked + .slider:before {
        transform: translateX(26px);
    }

    /* Floating Command Bar - LIGHT VERSION */
    .command-bar {
        position: fixed;
        bottom: 30px;
        left: 50%;
        transform: translateX(-50%);
        background: #ffffff; /* White bg */
        padding: 12px 16px 12px 32px;
        border-radius: 100px;
        display: flex;
        align-items: center;
        gap: 32px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.15), 0 0 0 1px rgba(0,0,0,0.05); /* Soft shadow + Border */
        z-index: 1000;
        color: var(--text-dark);
        max-width: 90%;
        width: auto;
    }

    .cmd-info {
        font-size: 1rem;
        color: var(--text-medium);
        white-space: nowrap;
        font-weight: 500;
    }
    .cmd-info strong {
        color: var(--accent-primary);
        font-weight: 800;
    }

    .btn-save-fab {
        background: var(--accent-primary);
        color: #fff;
        border: none;
        padding: 16px 32px;
        border-radius: 100px;
        font-weight: 800;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 10px 20px -5px rgba(6, 182, 212, 0.4);
    }

    .btn-save-fab:hover {
        background: var(--accent-hover);
        transform: translateY(-2px);
        box-shadow: 0 15px 30px -5px rgba(6, 182, 212, 0.5);
    }

</style>

<div class="perm-wrapper">


    <div class="role-switcher-container">
        <div class="role-switcher">
            <?php foreach ($roles as $i => $role): ?>
                <button class="role-pill <?= $i === 0 ? 'active' : '' ?>" 
                        data-role-id="<?= $role['id'] ?>"
                        onclick="selectRole(<?= $role['id'] ?>)">
                    <?= htmlspecialchars($role['display_name']) ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>

    <form id="permissionsForm">
        <input type="hidden" name="role_id" id="roleIdInput" value="<?= $roles[0]['id'] ?? 0 ?>">

        <div class="perm-grid">
            <?php foreach ($groupedPermissions as $group => $perms): ?>
                <div class="group-card">
                    <div class="group-header-strip">
                        <div class="group-icon">
                            <?= match(strtolower($group)) {
                                'usuarios', 'users' => '👥',
                                'financeiro', 'finances' => '💰',
                                'unidades', 'units' => '⛺',
                                'events' => '📅',
                                default => '🔒'
                            } ?>
                        </div>
                        <?= htmlspecialchars($group) ?>
                    </div>
                    <div class="toggle-list">
                        <?php foreach ($perms as $perm): ?>
                            <label class="toggle-row">
                                <div class="toggle-info">
                                    <span class="toggle-name"><?= htmlspecialchars($perm['name']) ?></span>
                                    <span class="toggle-key"><?= htmlspecialchars($perm['key']) ?></span>
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

        <div class="command-bar">
            <div class="cmd-info">
                Editando: <strong id="currentRoleName"><?= htmlspecialchars($roles[0]['display_name'] ?? '') ?></strong>
                <span style="margin: 0 12px; opacity: 0.3">|</span>
                <span id="changeCount">0 ativos</span>
            </div>
            <button type="submit" class="btn-save-fab" id="saveBtn">
                <span class="material-icons-round">check_circle</span> Salvar Alterações
            </button>
        </div>
    </form>
</div>

<!-- Dependencies -->

<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
<div id="toast-container" style="position: fixed; bottom: 100px; right: 24px; z-index: 10000; display: flex; flex-direction: column; gap: 10px;"></div>

<script>
    // System Toast
    window.showToast = function(message, type = 'success') {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.textContent = message;
        toast.style.cssText = `background: ${type === 'error' ? '#ef4444' : '#06b6d4'}; color: #fff; padding: 12px 24px; border-radius: 100px; font-weight: 700; font-family: 'Inter'; box-shadow: 0 10px 30px rgba(0,0,0,0.2); opacity: 0; transform: translateY(20px);`;
        if(type === 'error') toast.style.background = '#dc2626';
        
        container.appendChild(toast);
        
        container.appendChild(toast);
        toast.classList.add('toast-enter');
        
        setTimeout(() => {
            toast.classList.remove('toast-enter');
            toast.classList.add('toast-exit');
            toast.addEventListener('animationend', () => toast.remove());
        }, 3000);
    }

    // Constants
    window.rolePermissions = <?= json_encode($rolePermissions) ?>;
    
    function updateChangeCount() {
        const active = document.querySelectorAll('input[name="permissions[]"]:checked').length;
        document.getElementById('changeCount').textContent = `${active} ativos`;
    }

    window.selectRole = function(roleId) {
        document.getElementById('roleIdInput').value = roleId;
        
        document.querySelectorAll('.role-pill').forEach(t => t.classList.remove('active'));
        const tab = document.querySelector(`.role-pill[data-role-id="${roleId}"]`);
        if (tab) tab.classList.add('active');
        
        const roleName = tab ? tab.innerText.trim() : '';
        document.getElementById('currentRoleName').textContent = roleName;
        
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
        btn.innerHTML = `<span class="material-icons-round margin-right:8px; animation:spin 1s linear infinite;">refresh</span> ...`;
        
        try {
            const formData = new FormData(e.target);
            const response = await fetch('<?= base_url($tenant['slug'] . '/admin/permissoes') ?>', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            
            if (data.success) {
                showToast('Salvo com sucesso!');
                const roleId = formData.get('role_id');
                if (!rolePermissions[roleId]) rolePermissions[roleId] = {};
                rolePermissions[roleId] = {}; 
                formData.getAll('permissions[]').forEach(pid => {
                    rolePermissions[roleId][pid] = true;
                });
            } else {
                showToast(data.error || 'Erro', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Erro de conexão', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    });

    // Init
    <?php if (!empty($roles)): ?>
        selectRole(<?= $roles[0]['id'] ?>);
    <?php endif; ?>
    
    // Entrance
    // Entrance - CSS Class Trigger
    document.addEventListener('DOMContentLoaded', () => {
        const animate = (selector, delayStr = 0) => {
            document.querySelectorAll(selector).forEach((el, i) => {
                el.classList.add('animate-enter');
                el.style.animationDelay = (delayStr + (i * 0.05)) + 's';
            });
        };
        
        animate(".group-card", 0);
        animate(".command-bar", 0.5);
    });
</script>
