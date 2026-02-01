<?php
/**
 * Unidades Admin - Soft SaaS Design v6.0
 * Inspired by User Reference: Clean, Light, Functional
 */
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    .hud-wrapper {
        padding: 0;
        animation: fadeEnter 0.6s ease-out;
    }

    @keyframes fadeEnter {
        from { opacity: 0; transform: translateY(10px); }
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
    
    .animate-modal-in {
        animation: slideInUp 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    .animate-modal-out {
        animation: fadeScaleOut 0.2s ease-in forwards;
    }
    
    /* Toast Animations */
    .toast-enter {
        animation: slideInUp 0.3s ease-out forwards;
    }
    .toast-exit {
        animation: fadeScaleOut 0.3s ease-in forwards;
    }

    /* Page Header Alignments */
    .page-toolbar {
        margin-bottom: 24px;
    }

    /* Units Grid */
    /* Modern Unit Card - Glass & Glow */
    .units-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        gap: 32px;
        padding-bottom: 40px;
    }

    .unit-card-modern {
        position: relative;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.4);
        border-radius: 24px;
        padding: 0;
        transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        box-shadow: 
            0 4px 6px -1px rgba(0, 0, 0, 0.01),
            0 2px 4px -1px rgba(0, 0, 0, 0.01),
            0 0 0 1px rgba(0, 0, 0, 0.02);
    }
    
    .unit-card-modern::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, var(--card-color-bg) 0%, transparent 40%);
        opacity: 0.08;
        z-index: 0;
    }

    .unit-card-modern:hover {
        transform: translateY(-8px) scale(1.01);
        box-shadow: 
            0 20px 40px -8px var(--card-shadow-color),
            0 8px 16px -6px rgba(0, 0, 0, 0.03);
        z-index: 10;
        border-color: rgba(255,255,255, 0.8);
    }

    .unit-card-content {
        position: relative;
        z-index: 1;
        padding: 28px;
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .unit-header-modern {
        display: flex;
        align-items: center;
        gap: 20px;
        margin-bottom: 24px;
    }

    .unit-icon-glass {
        width: 72px;
        height: 72px;
        border-radius: 20px;
        background: linear-gradient(135deg, rgba(255,255,255,0.9), rgba(255,255,255,0.4));
        border: 1px solid rgba(255,255,255,0.6);
        box-shadow: 
            0 8px 16px -4px var(--card-shadow-color),
            inset 0 2px 4px rgba(255,255,255,1);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        color: var(--card-color);
        flex-shrink: 0;
        transition: transform 0.4s ease;
    }

    .unit-card-modern:hover .unit-icon-glass {
        transform: scale(1.1) rotate(-5deg);
    }

    .unit-info-modern {
        flex: 1;
        min-width: 0;
    }

    .unit-title-modern {
        font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
        font-size: 1.25rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0 0 6px 0;
        letter-spacing: -0.02em;
        line-height: 1.2;
    }

    .unit-motto-modern {
        font-size: 0.875rem;
        color: #64748b;
        font-style: italic;
        line-height: 1.5;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .unit-stats-glass {
        display: flex;
        background: rgba(255, 255, 255, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.4);
        border-radius: 16px;
        padding: 12px;
        gap: 12px;
        margin-top: auto;
    }

    .stat-pill {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 8px;
        border-radius: 12px;
        transition: background 0.2s;
    }

    .stat-pill:hover {
        background: rgba(255, 255, 255, 0.6);
    }

    .stat-pill-value {
        font-size: 1.25rem;
        font-weight: 700;
        color: #0f172a;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    .stat-pill-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        font-weight: 600;
        margin-top: 2px;
    }

    .unit-actions-overlay {
        position: absolute;
        top: 20px;
        right: 20px;
        display: flex;
        gap: 8px;
        opacity: 0;
        transform: translateX(10px);
        transition: all 0.3s ease;
        z-index: 20;
    }

    .unit-card-modern:hover .unit-actions-overlay {
        opacity: 1;
        transform: translateX(0);
    }

    .action-btn-glass {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.9);
        border: 1px solid rgba(0, 0, 0, 0.05);
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #475569;
        cursor: pointer;
        transition: all 0.2s;
    }

    .action-btn-glass:hover {
        transform: translateY(-2px);
        color: var(--card-color);
        box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
    }

    .action-btn-glass.delete:hover {
        color: #ef4444;
        background: #fef2f2;
    }
</style>

<div class="hud-wrapper">
    <!-- Toolbar -->
    <div class="page-toolbar">
        <div class="page-info">
            <p class="text-muted">Visão geral dos agrupamentos do clube.</p>
        </div>
        <div class="actions-group">
            <a href="<?= base_url($tenant['slug'] . '/admin/unidades/criar') ?>" class="btn-toolbar primary">
                <span class="material-icons-round">add</span> Nova Unidade
            </a>
        </div>
    </div>

    <!-- Summary Stats Row (Computed from PHP) -->
    <?php
        $totalUnits = count($units);
        $totalMembers = 0;
        $totalCounselors = 0;
        foreach($units as $u) {
            $totalMembers += count($u['members']);
            $totalCounselors += count($u['counselors']);
        }
    ?>
    <div class="stats-grid">
        <!-- Units -->
        <div class="stat-card purple">
            <div class="stat-card-bg-icon purple">
                <span class="material-icons-round">grid_view</span>
            </div>
            <div class="stat-icon">
                <span class="material-icons-round">grid_view</span>
            </div>
            <div class="stat-content">
                <span class="stat-value"><?= $totalUnits ?></span>
                <span class="stat-label">Unidades Ativas</span>
            </div>
        </div>
        <!-- Members -->
        <div class="stat-card blue">
            <div class="stat-card-bg-icon blue">
                <span class="material-icons-round">groups</span>
            </div>
            <div class="stat-icon">
                <span class="material-icons-round">groups</span>
            </div>
            <div class="stat-content">
                <span class="stat-value"><?= $totalMembers ?></span>
                <span class="stat-label">Total de Membros</span>
            </div>
        </div>
        <!-- Counselors -->
        <div class="stat-card amber">
            <div class="stat-card-bg-icon amber">
                <span class="material-icons-round">admin_panel_settings</span>
            </div>
            <div class="stat-icon">
                <span class="material-icons-round">admin_panel_settings</span>
            </div>
            <div class="stat-content">
                <span class="stat-value"><?= $totalCounselors ?></span>
                <span class="stat-label">Conselheiros</span>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <?php if (empty($units)): ?>
        <div class="saas-empty">
            <div style="font-size: 3rem; margin-bottom: 16px; opacity: 0.5;">📭</div>
            <h3 style="margin-bottom: 8px; color: var(--text-primary);">Nenhuma unidade encontrada</h3>
            <p style="color: var(--text-secondary); margin-bottom: 24px;">Comece a estruturar o clube criando a primeira unidade.</p>
            <a href="<?= base_url($tenant['slug'] . '/admin/unidades/criar') ?>" class="btn-saas-primary">Criar Unidade</a>
        </div>
    <?php else: ?>
        <div class="units-grid">
            <?php foreach ($units as $unit): ?>
                <?php 
                    $color = htmlspecialchars($unit['color'] ?? '#3b82f6');
                ?>
                <div class="unit-card-modern" style="--card-color: <?= $color ?>; --card-color-bg: <?= $color ?>; --card-shadow-color: <?= $color ?>20;">
                    <!-- Actions Overlay -->
                    <div class="unit-actions-overlay">
                        <a href="<?= base_url($tenant['slug'] . '/admin/unidades/' . $unit['id']) ?>" class="action-btn-glass" title="Editar">
                            <span class="material-icons-round" style="font-size: 20px;">edit_note</span>
                        </a>
                        <button onclick="deleteUnit(<?= $unit['id'] ?>)" class="action-btn-glass delete" title="Excluir">
                            <span class="material-icons-round" style="font-size: 20px;">delete_outline</span>
                        </button>
                    </div>

                    <div class="unit-card-content">
                        <div class="unit-header-modern">
                            <div class="unit-icon-glass">
                                <?php if($unit['mascot'] && strpos($unit['mascot'], ':') !== false): ?>
                                    <iconify-icon icon="<?= htmlspecialchars($unit['mascot']) ?>"></iconify-icon>
                                <?php elseif($unit['mascot'] && strpos($unit['mascot'], 'fa-') !== false): ?>
                                    <i class="<?= htmlspecialchars($unit['mascot']) ?>"></i>
                                <?php elseif($unit['mascot']): ?>
                                    <?= htmlspecialchars(substr($unit['mascot'], 0, 2)) ?>
                                <?php else: ?>
                                    <span class="material-icons-round">shield</span>
                                <?php endif; ?>
                            </div>
                            <div class="unit-info-modern">
                                <h3 class="unit-title-modern"><?= htmlspecialchars($unit['name']) ?></h3>
                                <div class="unit-motto-modern">
                                    <?= $unit['motto'] ? '"'.htmlspecialchars($unit['motto']).'"' : 'Avante!' ?>
                                </div>
                            </div>
                        </div>

                        <div class="unit-stats-glass">
                            <div class="stat-pill">
                                <span class="stat-pill-value"><?= count($unit['members']) ?></span>
                                <span class="stat-pill-label">Membros</span>
                            </div>
                            <div class="stat-pill">
                                <span class="stat-pill-value"><?= count($unit['counselors']) ?></span>
                                <span class="stat-pill-label">Líderes</span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal & Config -->
<div id="toast-container" style="position: fixed; bottom: 24px; right: 24px; z-index: 10000; display: flex; flex-direction: column; gap: 10px;"></div>
<div class="confirm-overlay" id="confirmModal" style="display: none;">
    <div class="confirm-box" style="background: #fff; border-radius: 16px; padding: 32px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); max-width: 400px; text-align: center;">
        <div style="width: 60px; height: 60px; background: #fee2e2; color: #ef4444; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px auto;">
            <span class="material-icons-round" style="font-size: 32px;">warning</span>
        </div>
        <h3 style="font-weight: 700; font-size: 1.25rem; color: #111827; margin-bottom: 8px;">Excluir Unidade?</h3>
        <p style="color: #6b7280; margin-bottom: 24px;">Esta ação é permanente e afetará todos os membros vinculados.</p>
        <div style="display: flex; gap: 12px; justify-content: center;">
             <button onclick="closeConfirmModal()" style="padding: 10px 20px; border-radius: 8px; border: 1px solid #e5e7eb; background: #fff; font-weight: 500; color: #374151; cursor: pointer;">Cancelar</button>
             <button id="confirmOkBtn" style="padding: 10px 20px; border-radius: 8px; border: none; background: #ef4444; color: #fff; font-weight: 500; cursor: pointer;">Sim, excluir</button>
        </div>
    </div>
</div>


<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">

<script>
    // Stats Counter Animation
    // Stats Counter Animation
    document.addEventListener('DOMContentLoaded', () => {
        const stats = document.querySelectorAll('.stat-value');
        stats.forEach(st => {
            const final = parseInt(st.textContent);
            let current = 0;
            const duration = 1000;
            const diff = final - current;
            const steps = 30;
            const stepDuration = duration / steps;
            const increment = diff / steps;
            
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

    // Toast
    // Toast
    window.showToast = function(msg, type = 'success') {
        const c = document.getElementById('toast-container');
        const t = document.createElement('div');
        t.textContent = msg;
        t.style.cssText = `background: ${type === 'error' ? '#ef4444' : '#10b981'}; color: #fff; padding: 12px 20px; border-radius: 8px; font-weight: 500; box-shadow: 0 4px 6px rgba(0,0,0,0.1); font-family: 'Inter'; font-size: 0.9rem; margin-top: 10px;`;
        c.appendChild(t);
        
        t.classList.add('toast-enter');
        
        setTimeout(() => { 
            t.classList.remove('toast-enter');
            t.classList.add('toast-exit');
            t.addEventListener('animationend', () => t.remove());
        }, 3000);
    }

    // Modal
    var resolveConfirm;
    var modal = document.getElementById('confirmModal');
    
    window.showConfirm = () => new Promise(res => {
        resolveConfirm = res;
        modal.style.display = 'flex';
        modal.style.position = 'fixed';
        modal.style.inset = '0';
        modal.style.background = 'rgba(0,0,0,0.5)';
        modal.style.zIndex = '9999';
        modal.style.alignItems = 'center';
        modal.style.justifyContent = 'center';
        modal.style.backdropFilter = 'blur(2px)';
        
        const box = modal.querySelector('.confirm-box');
        box.classList.remove('animate-modal-out');
        box.classList.add('animate-modal-in');
    });

    window.closeConfirmModal = () => {
        const box = modal.querySelector('.confirm-box');
        box.classList.remove('animate-modal-in');
        box.classList.add('animate-modal-out');
        
        setTimeout(() => {
            modal.style.display = 'none';
            if(resolveConfirm) resolveConfirm(false);
        }, 200);
    }

    document.getElementById('confirmOkBtn').onclick = () => { if(resolveConfirm) resolveConfirm(true); closeConfirmModal(); };

    window.deleteUnit = async (id) => {
        if(await showConfirm()) {
            try {
                const res = await fetch(`<?= base_url($tenant['slug']) ?>/admin/unidades/${id}/delete`, { method: 'POST' });
                if(res.ok) { showToast('Unidade excluída.'); setTimeout(() => location.reload(), 500); }
                else showToast('Erro ao excluir.', 'error');
            } catch { showToast('Erro de conexão.', 'error'); }
        }
    }
</script>
