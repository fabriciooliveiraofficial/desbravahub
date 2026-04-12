<?php
/**
 * Unidades Admin - Padrão Plataforma v1.0
 * Design Limpo, Funcional e Localizado em PT-BR
 */
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    :root {
        --primary-blue: #3b82f6;
        --text-main: #0f172a;
        --text-sub: #64748b;
        --border-color: #e2e8f0;
        --card-radius: 24px;
        --card-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
    }

    .units-container {
        padding: 16px;
        width: 100%;
        min-height: 100vh;
        background: #f8fafc;
        font-family: 'Inter', sans-serif;
    }

    /* Stats Grid (Standard Dashboard Style) */
    .stats-ribbon {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin-bottom: 32px;
    }

    .unit-stat-card {
        background: white;
        border: 1px solid var(--border-color);
        border-radius: var(--card-radius);
        padding: 24px;
        display: flex;
        align-items: center;
        gap: 20px;
        position: relative;
        overflow: hidden;
        box-shadow: var(--card-shadow);
        transition: transform 0.3s ease;
    }

    .unit-stat-card:hover {
        transform: translateY(-2px);
    }

    .stat-icon-box {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f1f5f9;
        color: var(--accent-color, #64748b);
        flex-shrink: 0;
    }

    .stat-info {
        display: flex;
        flex-direction: column;
    }

    .stat-count {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 24px;
        font-weight: 800;
        color: var(--text-main);
        line-height: 1;
    }

    .stat-name {
        font-size: 0.875rem;
        color: var(--text-sub);
        font-weight: 500;
        margin-top: 4px;
    }

    /* Toolbar Actions */
    .units-toolbar {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 32px;
    }

    .btn-create-unit {
        background: var(--text-main);
        color: white;
        padding: 12px 28px;
        border-radius: 14px;
        font-weight: 600;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        gap: 10px;
        border: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.15);
    }

    .btn-create-unit:hover {
        background: #1e293b;
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.2);
    }

    /* Units Main Grid */
    .units-main-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        gap: 24px;
    }

    .unit-display-card {
        background: white;
        border: 1px solid var(--border-color);
        border-radius: var(--card-radius);
        padding: 0;
        display: flex;
        flex-direction: column;
        position: relative;
        overflow: hidden;
        box-shadow: var(--card-shadow);
        transition: all 0.3s ease;
    }

    .unit-display-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 12px 24px -4px rgba(0, 0, 0, 0.08);
    }

    .unit-card-body {
        padding: 28px;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .unit-identity {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .unit-mascot-box {
        width: 72px;
        height: 72px;
        border-radius: 20px;
        background: #f8fafc;
        border: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        color: var(--unit-accent-color);
        flex-shrink: 0;
        transition: transform 0.3s ease;
    }

    .unit-display-card:hover .unit-mascot-box {
        transform: scale(1.05) rotate(-3deg);
    }

    .unit-title-info {
        flex: 1;
        min-width: 0;
    }

    .unit-name-standard {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-main);
        margin: 0;
        letter-spacing: -0.01em;
    }

    .unit-motto-standard {
        font-size: 0.875rem;
        color: var(--text-sub);
        font-style: italic;
        margin-top: 4px;
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .unit-metrics-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        background: #f8fafc;
        padding: 16px;
        border-radius: 18px;
    }

    .metric-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .metric-value {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 1.25rem;
        font-weight: 800;
        color: var(--text-main);
    }

    .metric-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-sub);
        font-weight: 600;
        margin-top: 2px;
    }

    /* Actions */
    .unit-card-actions {
        position: absolute;
        top: 20px;
        right: 20px;
        display: flex;
        gap: 8px;
        opacity: 0;
        transition: all 0.2s ease;
    }

    .unit-display-card:hover .unit-card-actions {
        opacity: 1;
    }

    .btn-action-circle {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: white;
        border: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-sub);
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .btn-action-circle:hover {
        background: var(--text-main);
        color: white;
        border-color: var(--text-main);
        transform: translateY(-2px);
    }

    .btn-action-circle.delete:hover {
        background: #ef4444;
        border-color: #ef4444;
    }

    /* Empty Box */
    .empty-inventory {
        background: white;
        border: 1px solid var(--border-color);
        border-radius: var(--card-radius);
        padding: 80px 40px;
        text-align: center;
        box-shadow: var(--card-shadow);
    }
</style>

<div class="units-container">
    <!-- Ribbon de Resumo -->
    <?php
        $totalUnits = count($units);
        $totalMembers = 0;
        $totalCounselors = 0;
        foreach($units as $u) {
            $totalMembers += count($u['members'] ?? []);
            $totalCounselors += count($u['counselors'] ?? []);
        }
    ?>
    <div class="stats-ribbon">
        <div class="unit-stat-card" style="--accent-color: #8b5cf6;">
            <div class="stat-icon-box" style="background: rgba(139, 92, 246, 0.1);">
                <span class="material-icons-round">grid_view</span>
            </div>
            <div class="stat-info">
                <span class="stat-count counter"><?= $totalUnits ?></span>
                <span class="stat-name">Unidades Criadas</span>
            </div>
        </div>
        <div class="unit-stat-card" style="--accent-color: #3b82f6;">
            <div class="stat-icon-box" style="background: rgba(59, 130, 246, 0.1);">
                <span class="material-icons-round">groups</span>
            </div>
            <div class="stat-info">
                <span class="stat-count counter"><?= $totalMembers ?></span>
                <span class="stat-name">Membros Ativos</span>
            </div>
        </div>
        <div class="unit-stat-card" style="--accent-color: #f59e0b;">
            <div class="stat-icon-box" style="background: rgba(245, 158, 11, 0.1);">
                <span class="material-icons-round">military_tech</span>
            </div>
            <div class="stat-info">
                <span class="stat-count counter"><?= $totalCounselors ?></span>
                <span class="stat-name">Conselheiros</span>
            </div>
        </div>
    </div>

    <!-- Barra de Ferramentas -->
    <div class="units-toolbar">
        <a href="<?= base_url($tenant['slug'] . '/admin/unidades/criar') ?>" class="btn-create-unit">
            <span class="material-icons-round">add</span> Nova Unidade
        </a>
    </div>

    <!-- Grade de Unidades -->
    <?php if (empty($units)): ?>
        <div class="empty-inventory">
            <span class="material-icons-round" style="font-size: 64px; color: #e2e8f0; margin-bottom: 24px;">category</span>
            <h3 style="font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 700; margin-bottom: 8px;">Nenhuma unidade ativa</h3>
            <p style="color: var(--text-sub); margin-bottom: 32px;">Organize o seu clube criando as primeiras unidades de gestão.</p>
            <a href="<?= base_url($tenant['slug'] . '/admin/unidades/criar') ?>" class="btn-create-unit" style="display: inline-flex;">Adicionar Unidade</a>
        </div>
    <?php else: ?>
        <div class="units-main-grid">
            <?php foreach ($units as $unit): ?>
                <?php $unitAccent = $unit['color'] ?: '#3b82f6'; ?>
                <div class="unit-display-card">
                    <!-- Ações de Gestão -->
                    <div class="unit-card-actions">
                        <a href="<?= base_url($tenant['slug'] . '/admin/unidades/' . $unit['id']) ?>" class="btn-action-circle" title="Editar">
                            <span class="material-icons-round" style="font-size: 18px;">edit</span>
                        </a>
                        <button onclick="deleteUnit(<?= $unit['id'] ?>)" class="btn-action-circle delete" title="Excluir">
                            <span class="material-icons-round" style="font-size: 18px;">delete</span>
                        </button>
                    </div>

                    <div class="unit-card-body">
                        <div class="unit-identity">
                            <div class="unit-mascot-box" style="--unit-accent-color: <?= $unitAccent ?>; border-color: <?= $unitAccent ?>30;">
                                <?php if($unit['mascot'] && strpos($unit['mascot'], ':') !== false): ?>
                                    <iconify-icon icon="<?= htmlspecialchars($unit['mascot']) ?>"></iconify-icon>
                                <?php elseif($unit['mascot'] && strpos($unit['mascot'], 'fa-') !== false): ?>
                                    <i class="<?= htmlspecialchars($unit['mascot']) ?>"></i>
                                <?php elseif($unit['mascot']): ?>
                                    <span style="font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800;">
                                        <?= htmlspecialchars(substr($unit['mascot'], 0, 2)) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="material-icons-round">shield</span>
                                <?php endif; ?>
                            </div>
                            <div class="unit-title-info">
                                <h3 class="unit-name-standard"><?= htmlspecialchars($unit['name']) ?></h3>
                                <div class="unit-motto-standard">
                                    <?= $unit['motto'] ? '"'.htmlspecialchars($unit['motto']).'"' : 'Avante e avante!' ?>
                                </div>
                            </div>
                        </div>

                        <div class="unit-metrics-row">
                            <div class="metric-item">
                                <span class="metric-value counter"><?= count($unit['members'] ?? []) ?></span>
                                <span class="metric-label">Membros</span>
                            </div>
                            <div class="metric-item" style="border-left: 1px solid #e2e8f0;">
                                <span class="metric-value counter"><?= count($unit['counselors'] ?? []) ?></span>
                                <span class="metric-label">Líderes</span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modais e Notificações -->
<div id="toast-container" style="position: fixed; bottom: 24px; right: 24px; z-index: 10000; display: flex; flex-direction: column; gap: 10px;"></div>
<div class="confirm-overlay" id="confirmModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.4); backdrop-filter: blur(8px); z-index: 9999; align-items: center; justify-content: center;">
    <div class="confirm-box" style="background: white; border-radius: 28px; padding: 40px; max-width: 420px; text-align: center; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.15);">
        <div style="width: 72px; height: 72px; background: #fff1f2; color: #e11d48; border-radius: 22px; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px auto;">
            <span class="material-icons-round" style="font-size: 36px;">warning</span>
        </div>
        <h3 style="font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800; color: #1e293b; font-size: 1.5rem; margin-bottom: 12px;">Excluir Unidade?</h3>
        <p style="color: #64748b; margin-bottom: 32px; font-size: 0.95rem; line-height: 1.5;">Esta ação é permanente. Todos os membros vinculados a esta unidade serão desvinculados.</p>
        <div style="display: flex; gap: 12px; justify-content: center;">
             <button onclick="closeConfirmModal()" style="flex: 1; padding: 14px; border-radius: 14px; font-weight: 600; font-size: 0.9rem; border: 1px solid #e2e8f0; background: white; color: #475569; cursor: pointer; transition: all 0.2s;">Cancelar</button>
             <button id="confirmOkBtn" style="flex: 1; padding: 14px; border-radius: 14px; font-weight: 600; font-size: 0.9rem; border: none; background: #e11d48; color: white; cursor: pointer; transition: all 0.2s;">Sim, Excluir</button>
        </div>
    </div>
</div>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">

<script>
    // Animação de Contagem
    document.addEventListener('DOMContentLoaded', () => {
        const stats = document.querySelectorAll('.counter');
        stats.forEach(st => {
            const final = parseInt(st.textContent);
            let current = 0;
            const duration = 1200;
            const stepDuration = 20;
            const increment = final / (duration / stepDuration);
            
            const timer = setInterval(() => {
                current += increment;
                if (current >= final) {
                    st.textContent = final;
                    clearInterval(timer);
                } else {
                    st.textContent = Math.ceil(current);
                }
            }, stepDuration);
        });
    });

    // Motor de Toast (Padrão)
    window.showToast = function(msg, type = 'success') {
        const c = document.getElementById('toast-container');
        const t = document.createElement('div');
        t.textContent = msg;
        t.style.cssText = `background: ${type === 'error' ? '#e11d48' : '#0f172a'}; color: #fff; padding: 16px 28px; border-radius: 16px; font-family: 'Inter'; font-size: 0.9rem; font-weight: 600; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); border-left: 6px solid ${type === 'error' ? '#fff' : '#3b82f6'}; transform: translateY(20px); opacity: 0; transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);`;
        c.appendChild(t);
        
        requestAnimationFrame(() => {
            t.style.transform = 'translateY(0)';
            t.style.opacity = '1';
        });

        setTimeout(() => {
            t.style.transform = 'translateY(20px)';
            t.style.opacity = '0';
            setTimeout(() => t.remove(), 3000);
        }, 3000);
    }

    var resolveConfirm;
    var modal = document.getElementById('confirmModal');
    
    window.showConfirm = () => new Promise(res => {
        resolveConfirm = res;
        modal.style.display = 'flex';
    });

    window.closeConfirmModal = () => {
        modal.style.display = 'none';
        if(resolveConfirm) resolveConfirm(false);
    }

    document.getElementById('confirmOkBtn').onclick = () => { if(resolveConfirm) resolveConfirm(true); closeConfirmModal(); };

    window.deleteUnit = async (id) => {
        if(await showConfirm()) {
            try {
                const res = await fetch(`<?= base_url($tenant['slug']) ?>/admin/unidades/${id}/delete`, { method: 'POST' });
                if(res.ok) { showToast('Unidade removida com sucesso.'); setTimeout(() => location.reload(), 500); }
                else showToast('Erro ao sincronizar dados.', 'error');
            } catch { showToast('Falha na conexão com o servidor.', 'error'); }
        }
    }
</script>
