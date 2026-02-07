<?php
/**
 * Admin: Learning Programs List
 * 
 * Lists programs (specialties/classes) with category filtering.
 */
$pageTitle = $currentCategory ? $currentCategory['name'] : 'Programas';
$pageIcon = $currentCategory ? 'category' : 'library_books';
$typeLabel = ($type ?? '') === 'class' ? 'Classes' : 'Especialidades';
?>
<style>
    /* ============ Page Toolbar ============ */
    .page-toolbar {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
        padding: 16px 24px;
        background: var(--bg-sidebar);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .actions-group {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .btn-toolbar {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s;
        border: 1px solid transparent;
    }

    .btn-toolbar.secondary {
        background: transparent;
        border-color: var(--border-color);
        color: var(--text-muted);
    }

    .btn-toolbar.secondary:hover {
        border-color: var(--primary);
        color: var(--primary);
    }

    .btn-toolbar.primary {
        background: linear-gradient(135deg, var(--primary), var(--primary-hover));
        color: white;
        box-shadow: 0 4px 6px rgba(6, 182, 212, 0.2);
        border: none;
    }

    .btn-toolbar.primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 12px rgba(6, 182, 212, 0.3);
    }

    /* Programs Grid */
    .programs-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 20px;
    }

    /* Filter Controls */
    .filters {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .filter-select {
        padding: 10px 16px;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        color: var(--text-main);
        font-size: 0.9rem;
        cursor: pointer;
        outline: none;
        min-width: 180px;
    }

    .filter-select:hover,
    .filter-select:focus {
        border-color: var(--primary);
    }

    /* Status Badges */
    .program-status {
        font-size: 0.75rem;
        padding: 4px 10px;
        border-radius: 20px;
        font-weight: 600;
    }

    .status-draft {
        background: rgba(255, 193, 7, 0.15);
        color: #ffc107;
    }

    .status-published {
        background: rgba(0, 255, 136, 0.15);
        color: #00ff88;
    }

    .status-archived {
        background: rgba(158, 158, 158, 0.15);
        color: #9e9e9e;
    }

    /* Action Buttons */
    .btn-action {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        border: 1px solid var(--border-color);
        background: transparent;
        color: var(--text-secondary);
        text-decoration: none;
    }

    .btn-action:hover {
        border-color: var(--primary);
        color: var(--primary);
        background: rgba(0, 217, 255, 0.05);
    }

    .btn-action-assign {
        background: linear-gradient(135deg, var(--accent-cyan), var(--accent-green));
        color: var(--bg-dark);
        border-color: transparent;
        font-weight: 600;
    }

    .btn-action-assign:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 217, 255, 0.3);
    }

    .btn-action-danger {
        background: rgba(244, 67, 54, 0.15);
        color: #f44336;
        border-color: rgba(244, 67, 54, 0.3);
    }

    .btn-action-danger:hover {
        background: rgba(244, 67, 54, 0.25);
        border-color: #f44336;
    }

    /* Empty state */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: var(--text-secondary);
    }

    .empty-state .icon {
        font-size: 4rem;
        margin-bottom: 16px;
    }

    .toast {
        position: fixed;
        bottom: 24px;
        right: 24px;
        padding: 16px 24px;
        background: var(--bg-card);
        border-left: 4px solid var(--accent-green);
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        transform: translateX(150%);
        transition: transform 0.3s ease;
        z-index: 1001;
    }

    .toast.show {
        transform: translateX(0);
    }

    .toast.error {
        border-left-color: #f44336;
    }

    /* Confirmation Modal */
    .confirm-modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.85);
        z-index: 2000;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .confirm-modal-overlay.active {
        display: flex;
    }

    .confirm-modal {
        background: #1e1e32;
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 16px;
        max-width: 400px;
        width: 100%;
        padding: 24px;
        text-align: center;
    }

    .confirm-modal-icon {
        font-size: 3rem;
        margin-bottom: 16px;
    }

    .confirm-modal h3 {
        margin: 0 0 8px;
        font-size: 1.2rem;
    }

    .confirm-modal p {
        color: var(--text-secondary);
        margin: 0 0 24px;
        font-size: 0.95rem;
    }

    .confirm-modal-actions {
        display: flex;
        gap: 12px;
        justify-content: center;
    }

    .confirm-modal-actions button {
        padding: 10px 24px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        border: none;
    }

    .btn-confirm-cancel {
        background: rgba(255, 255, 255, 0.1);
        color: var(--text-primary);
    }

    .btn-confirm-ok {
        background: linear-gradient(135deg, var(--accent-cyan), var(--accent-green));
        color: var(--bg-dark);
    }

    .btn-confirm-danger {
        background: rgba(244, 67, 54, 0.8);
        color: white;
    }

    /* ============ Premium Assignment Modal ============ */
    .assign-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(12px);
        z-index: 2000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s, visibility 0.3s;
    }

    .assign-modal-overlay.active {
        opacity: 1;
        visibility: visible;
    }

    .assign-modal {
        background: linear-gradient(135deg, rgba(30, 41, 59, 0.95), rgba(15, 23, 42, 0.95));
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 24px;
        max-width: 520px;
        width: 100%;
        max-height: 80vh;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        box-shadow: 
            0 25px 50px -12px rgba(0, 0, 0, 0.5),
            0 0 0 1px rgba(255, 255, 255, 0.05),
            0 0 60px -20px rgba(139, 92, 246, 0.15);
        transform: scale(0.9) translateY(20px);
        opacity: 0;
        transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.3s;
        position: relative;
    }

    .assign-modal-overlay.active .assign-modal {
        transform: scale(1) translateY(0);
        opacity: 1;
    }

    .assign-modal::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, #8b5cf6, #06b6d4, #10b981);
    }

    .assign-modal-header {
        padding: 28px 28px 20px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        background: linear-gradient(135deg, rgba(139, 92, 246, 0.05), transparent);
    }

    .assign-modal-header h3 {
        margin: 0 0 8px;
        font-size: 1.35rem;
        font-weight: 700;
        color: white;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .assign-modal-header p {
        margin: 0;
        color: #94a3b8;
        font-size: 0.95rem;
    }

    .assign-modal-body {
        padding: 20px 28px;
        overflow-y: auto;
        flex: 1;
    }

    .user-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .user-item {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 16px;
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.06);
        border-radius: 14px;
        cursor: pointer;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .user-item::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: transparent;
        border-radius: 0 4px 4px 0;
        transition: background 0.3s;
    }

    .user-item:hover {
        background: rgba(139, 92, 246, 0.08);
        border-color: rgba(139, 92, 246, 0.2);
        transform: translateX(4px);
    }

    .user-item:hover::before {
        background: linear-gradient(180deg, #8b5cf6, #06b6d4);
    }

    .user-item.selected {
        background: linear-gradient(135deg, rgba(139, 92, 246, 0.15), rgba(6, 182, 212, 0.1));
        border-color: rgba(139, 92, 246, 0.4);
        box-shadow: 0 0 20px -8px rgba(139, 92, 246, 0.3);
    }

    .user-item.selected::before {
        background: linear-gradient(180deg, #8b5cf6, #06b6d4);
    }

    .user-item.selected .user-avatar {
        box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.4);
    }

    .user-item.assigned {
        opacity: 0.5;
        cursor: not-allowed;
        filter: grayscale(0.5);
    }

    .user-avatar {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: linear-gradient(135deg, #8b5cf6, #06b6d4);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.1rem;
        color: white;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(139, 92, 246, 0.25);
        transition: all 0.3s;
    }

    .user-avatar img {
        width: 100%;
        height: 100%;
        border-radius: 12px;
        object-fit: cover;
    }

    .user-name {
        flex: 1;
        font-weight: 600;
        font-size: 0.95rem;
        color: #e2e8f0;
    }

    .user-badge {
        font-size: 0.75rem;
        padding: 4px 12px;
        border-radius: 20px;
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(16, 185, 129, 0.08));
        color: #10b981;
        font-weight: 600;
        border: 1px solid rgba(16, 185, 129, 0.2);
    }

    .assign-modal-footer {
        padding: 20px 28px;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        display: flex;
        gap: 14px;
        justify-content: flex-end;
        background: rgba(0, 0, 0, 0.1);
    }

    .btn-assign {
        padding: 12px 28px;
        border-radius: 14px;
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        border: none;
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        color: white;
        box-shadow: 0 4px 14px rgba(139, 92, 246, 0.35);
        transition: all 0.25s;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-assign:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(139, 92, 246, 0.45);
    }

    .btn-assign:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }

    .btn-cancel {
        padding: 12px 24px;
        border-radius: 14px;
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        border: none;
        background: rgba(255, 255, 255, 0.05);
        color: #94a3b8;
        transition: all 0.2s;
    }

    .btn-cancel:hover {
        background: rgba(255, 255, 255, 0.1);
        color: white;
    }

    .loading-users {
        text-align: center;
        padding: 40px;
        color: var(--text-secondary);
    }

    /* ============ GSAP Premium Modal ============ */
    .gsap-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(12px);
        z-index: 2000;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        visibility: hidden;
    }

    .gsap-modal-card {
        background: linear-gradient(135deg, rgba(30, 41, 59, 0.95), rgba(15, 23, 42, 0.95));
        border: 1px solid rgba(255, 255, 255, 0.1);
        width: 100%;
        max-width: 420px;
        border-radius: 28px;
        padding: 40px 32px;
        text-align: center;
        box-shadow: 
            0 25px 50px -12px rgba(0, 0, 0, 0.5),
            0 0 0 1px rgba(255, 255, 255, 0.05),
            0 0 60px -20px rgba(6, 182, 212, 0.15);
        transform: scale(0.8);
        opacity: 0;
        position: relative;
        overflow: hidden;
    }

    .gsap-modal-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 6px;
        background: linear-gradient(90deg, #06b6d4, #8b5cf6, #ec4899);
    }

    .gsap-icon-container {
        width: 80px;
        height: 80px;
        margin: 0 auto 24px;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .gsap-icon-glow {
        position: absolute;
        inset: 0;
        background: radial-gradient(circle, rgba(6, 182, 212, 0.2) 0%, transparent 70%);
        filter: blur(8px);
        border-radius: 50%;
        animation: pulse 3s infinite;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); opacity: 0.5; }
        50% { transform: scale(1.2); opacity: 0.8; }
    }

    .gsap-icon {
        font-size: 3.5rem;
        position: relative;
        z-index: 2;
        filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.3));
    }

    .gsap-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: white;
        margin: 0 0 12px;
        background: linear-gradient(to right, #fff, #cbd5e1);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .gsap-message {
        font-size: 1rem;
        color: #94a3b8;
        line-height: 1.6;
        margin: 0 0 32px;
    }

    .gsap-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    .gsap-btn {
        padding: 14px;
        border-radius: 16px;
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
        outline: none;
    }

    .gsap-btn.cancel {
        background: rgba(255, 255, 255, 0.05);
        color: #94a3b8;
    }

    .gsap-btn.cancel:hover {
        background: rgba(255, 255, 255, 0.1);
        color: white;
    }

    .gsap-btn.confirm {
        background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(6, 182, 212, 0.3);
    }

    .gsap-btn.confirm:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(6, 182, 212, 0.4);
    }

    .gsap-btn.danger {
        background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    }

    .gsap-btn.danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(239, 68, 68, 0.4);
    }

    /* CSS Animations for Modal */
    @keyframes modalPopIn {
        0% { opacity: 0; transform: scale(0.8); }
        60% { transform: scale(1.05); }
        100% { opacity: 1; transform: scale(1); }
    }
    
    @keyframes modalFadeOut {
        from { opacity: 1; transform: scale(1); }
        to { opacity: 0; transform: scale(0.95); }
    }

    .gsap-modal-overlay.active {
        visibility: visible;
        opacity: 1;
        transition: opacity 0.3s ease-out;
    }

    .gsap-modal-overlay.active .gsap-modal-card {
        opacity: 1;
        transform: scale(1);
        animation: modalPopIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
    }

    .gsap-modal-overlay.closing {
        opacity: 0;
        transition: opacity 0.3s ease-in;
    }

    .gsap-modal-overlay.closing .gsap-modal-card {
        animation: modalFadeOut 0.2s ease-in forwards;
    }
</style>


        <div class="page-toolbar">
            <div class="filters" style="margin-bottom: 0;">
                <select class="filter-select" onchange="filterByCategory(this.value)">
                    <option value="">Todas as Categorias</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($categoryId ?? '') == $cat['id'] ? 'selected' : '' ?>>
                             <?php
                                $icon = $cat['icon'] ?? '📚';
                                if(str_starts_with($icon, 'fa-')) echo '📂'; 
                                else if(str_contains($icon, ':')) echo '📂';
                                else echo $icon;
                            ?> 
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <select class="filter-select" onchange="filterByType(this.value)">
                    <option value="">Todos os Tipos</option>
                    <option value="specialty" <?= ($type ?? '') === 'specialty' ? 'selected' : '' ?>>🎯 Especialidades
                    </option>
                    <option value="class" <?= ($type ?? '') === 'class' ? 'selected' : '' ?>>🎖️ Classes</option>
                </select>
            </div>

            <div class="actions-group">
                <button class="btn-toolbar primary" onclick="openCreateProgramModal()">
                    <span class="material-icons-round">add_circle</span> Novo Programa
                </button>
            </div>
        </div>



        <?php if (empty($programs)): ?>
            <div class="empty-state">
                <div class="icon">📚</div>
                <h3>Nenhum programa encontrado</h3>
                <p>Crie seu primeiro programa de aprendizagem.</p>
                <button onclick="openCreateProgramModal()" class="btn-add"
                    style="margin-top: 16px; display: inline-flex; align-items: center; gap: 8px; border: none; cursor: pointer;">
                    ➕ Criar Programa
                </button>
            </div>
        <?php else: ?>
            <div class="programs-grid">
                <?php foreach ($programs as $program): ?>
                    <div class="specialty-card">
                        <div class="specialty-header">
                            <div class="specialty-badge">
                                <?php if (str_starts_with($program['icon'] ?? '', 'fa-')): ?>
                                    <i class="<?= htmlspecialchars($program['icon']) ?>"></i>
                                <?php elseif (str_contains($program['icon'] ?? '', ':')): ?>
                                    <iconify-icon icon="<?= htmlspecialchars($program['icon']) ?>"></iconify-icon>
                                <?php else: ?>
                                    <?= $program['icon'] ?>
                                <?php endif; ?>
                            </div>
                            <div class="specialty-title">
                                <h3><?= htmlspecialchars($program['name']) ?></h3>
                                <div class="specialty-meta">
                                    <span class="meta-tag" title="Tipo">
                                        <?php if ($program['is_outdoor']): ?>
                                            <span class="material-icons-round" style="font-size:12px">forest</span> Externo
                                        <?php else: ?>
                                            <span class="material-icons-round" style="font-size:12px">home</span> Interno
                                        <?php endif; ?>
                                    </span>
                                    <span class="meta-tag" title="Duração">
                                        <span class="material-icons-round" style="font-size:12px">schedule</span>
                                        <?= $program['duration_hours'] ?>h
                                    </span>
                                    <span class="meta-tag difficulty-stars" title="Dificuldade">
                                        <?= str_repeat('★', $program['difficulty'] ?? 1) . str_repeat('☆', 5 - ($program['difficulty'] ?? 1)) ?>
                                    </span>
                                    <!-- Status Badge -->
                                    <span class="meta-tag status-<?= $program['status'] ?>" style="margin-left:auto; background: var(--bg-dark); padding: 2px 8px; border-radius: 4px;">
                                        <?= match ($program['status']) {
                                            'draft' => '📝 Rascunho',
                                            'published', 'active' => '✅ Publicado',
                                            'archived' => '📦 Arquivado',
                                            default => '❓ ' . ucfirst($program['status'] ?? 'Desconhecido')
                                        } ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <p class="specialty-desc">
                            <?= htmlspecialchars($program['description'] ?? 'Sem descrição disponível.') ?>
                        </p>

                        <div class="specialty-footer">
                            <span class="xp-reward" title="XP Recompensa">
                                <span class="material-icons-round" style="font-size:14px">bolt</span>
                                <?= number_format($program['xp_reward']) ?>
                            </span>
                            <div class="card-actions">
                                <a href="<?= base_url($programs_tenantSlug . '/admin/programas/' . $program['id'] . '/editar') ?>" 
                                   class="btn-icon-action" title="Editar">
                                    <span class="material-icons-round" style="font-size:18px">edit</span>
                                </a>

                                <?php if ($program['status'] === 'published' || $program['status'] === 'active'): ?>
                                    <a href="<?= base_url($programs_tenantSlug . '/admin/especialidades/prog_' . $program['id'] . '/atribuir') ?>" 
                                       class="btn-card-assign">
                                        <span class="material-icons-round" style="font-size:16px">group_add</span> Atribuir
                                    </a>
                                <?php elseif ($program['status'] === 'draft'): ?>
                                    <button class="btn-icon-action" title="Publicar" onclick="publishProgram(<?= $program['id'] ?>)">
                                        <span class="material-icons-round" style="font-size:18px; color: var(--accent-cyan);">rocket_launch</span>
                                    </button>
                                <?php endif; ?>
                                
                                <button class="btn-icon-action danger" title="Excluir" 
                                        onclick="window.deleteProgram(<?= $program['id'] ?>, '<?= htmlspecialchars($program['name']) ?>')">
                                    <span class="material-icons-round" style="font-size:18px">delete</span>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <!-- </main> removed as it breaks layout -->

    <!-- GSAP Premium Confirmation Modal -->
    <div class="gsap-modal-overlay" id="gsapConfirmModal">
        <div class="gsap-modal-card">
            <div class="gsap-icon-container">
                <div class="gsap-icon-glow"></div>
                <div class="gsap-icon" id="gsapConfirmIcon">🚀</div>
            </div>
            <h3 class="gsap-title" id="gsapConfirmTitle">Confirmar Ação</h3>
            <p class="gsap-message" id="gsapConfirmMessage">Tem certeza que deseja prosseguir?</p>
            <div class="gsap-actions">
                <button type="button" class="gsap-btn cancel" onclick="closeGsapConfirm(false)">Cancelar</button>
                <button type="button" class="gsap-btn confirm" id="gsapConfirmOk" onclick="closeGsapConfirm(true)">Confirmar</button>
            </div>
        </div>
    </div>

    <!-- Assignment Modal -->
    <div class="assign-modal-overlay" id="assignModal" onclick="closeAssignModal()">
        <div class="assign-modal" onclick="event.stopPropagation()">
            <div class="assign-modal-header">
                <h3>👥 Atribuir Programa</h3>
                <p id="assignProgramName">Selecione os desbravadores</p>
            </div>
            <div class="assign-modal-body">
                <div class="user-list" id="userList">
                    <div class="loading-users">⏳ Carregando usuários...</div>
                </div>
            </div>
            <div class="assign-modal-footer">
                <button type="button" class="btn-cancel" onclick="closeAssignModal()">Cancelar</button>
                <button type="button" class="btn-assign" id="btnAssign" onclick="submitAssignment()">
                    ✅ Atribuir Selecionados
                </button>
            </div>
        </div>
    </div>

    <div class="toast" id="toast"></div>

    <!-- Create Program Modal -->
    <?php require BASE_PATH . '/views/admin/programs/partials/create_modal.php'; ?>


    <script>
        window.programs_tenantSlug = '<?= $tenant['slug'] ?>';
        window.currentAssignProgramId = null;
        window.selectedUserIds = [];

        // Program assignment functions
        async function openAssignModal(programId, programName) {
            currentAssignProgramId = programId;
            selectedUserIds = [];
            document.getElementById('assignProgramName').textContent = programName;
            document.getElementById('userList').innerHTML = '<div class="loading-users">⏳ Carregando usuários...</div>';
            document.getElementById('assignModal').classList.add('active');

            try {
                const resp = await fetch(`/${programs_tenantSlug}/admin/programas/${programId}/users`);
                const data = await resp.json();

                if (data.success && data.users) {
                    renderUserList(data.users);
                } else {
                    document.getElementById('userList').innerHTML = '<div class="loading-users">❌ Erro ao carregar usuários</div>';
                }
            } catch (err) {
                document.getElementById('userList').innerHTML = '<div class="loading-users">❌ Erro de conexão</div>';
            }
        }

        function renderUserList(users) {
            if (users.length === 0) {
                document.getElementById('userList').innerHTML = '<div class="loading-users">Nenhum desbravador cadastrado</div>';
                return;
            }

            const html = users.map(user => {
                const initials = user.name.charAt(0).toUpperCase();
                const isAssigned = user.already_assigned == 1;
                return `
                    <div class="user-item ${isAssigned ? 'assigned' : ''}" 
                         data-user-id="${user.id}" 
                         onclick="${isAssigned ? '' : 'toggleUserSelection(this)'}">
                        <div class="user-avatar">
                            ${user.profile_picture
                        ? `<img src="${user.profile_picture}" alt="${user.name}">`
                        : initials}
                        </div>
                        <span class="user-name">${user.name}</span>
                        ${isAssigned ? '<span class="user-badge">Já atribuído</span>' : ''}
                    </div>
                `;
            }).join('');

            document.getElementById('userList').innerHTML = html;
        }

        function toggleUserSelection(el) {
            const userId = parseInt(el.dataset.userId);
            el.classList.toggle('selected');

            if (el.classList.contains('selected')) {
                if (!selectedUserIds.includes(userId)) {
                    selectedUserIds.push(userId);
                }
            } else {
                selectedUserIds = selectedUserIds.filter(id => id !== userId);
            }
        }

        function closeAssignModal() {
            document.getElementById('assignModal').classList.remove('active');
            currentAssignProgramId = null;
            selectedUserIds = [];
        }

        async function submitAssignment() {
            if (selectedUserIds.length === 0) {
                showToast('Selecione pelo menos um usuário', 'error');
                return;
            }

            const btn = document.getElementById('btnAssign');
            btn.disabled = true;
            btn.textContent = '⏳ Atribuindo...';

            try {
                const resp = await fetch(`/${programs_tenantSlug}/admin/programas/${currentAssignProgramId}/assign`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ user_ids: selectedUserIds })
                });
                const data = await resp.json();

                if (data.success) {
                    showToast(data.message);
                    closeAssignModal();
                } else {
                    showToast(data.error || 'Erro ao atribuir', 'error');
                }
            } catch (err) {
                showToast('Erro de conexão', 'error');
            } finally {
                btn.disabled = false;
                btn.textContent = '✅ Atribuir Selecionados';
            }
        }

        function filterByCategory(categoryId) {
            const url = new URL(window.location);
            if (categoryId) {
                url.searchParams.set('category', categoryId);
            } else {
                url.searchParams.delete('category');
            }
            window.location = url;
        }

        function filterByType(type) {
            const url = new URL(window.location);
            if (type) {
                url.searchParams.set('type', type);
            } else {
                url.searchParams.delete('type');
            }
            window.location = url;
        }

        async function publishProgram(id) {
            const confirmed = await showConfirm({
                title: 'Publicar Programa',
                message: 'Publicar este programa? Ele ficará disponível para atribuição.',
                icon: '🚀',
                okText: 'Publicar'
            });
            if (!confirmed) return;

            try {
                const resp = await fetch(`/${programs_tenantSlug}/admin/programas/${id}/publish`, { method: 'POST' });
                const data = await resp.json();
                if (data.success) {
                    showToast(data.message);
                    setTimeout(() => location.reload(), 500);
                } else {
                    showToast(data.error || 'Erro', 'error');
                }
            } catch (err) {
                showToast('Erro de conexão', 'error');
            }
        }

        async function deleteProgram(id, name) {
            const confirmed = await showConfirm({
                title: 'Excluir Programa',
                message: `Excluir "${name}"? Esta ação não pode ser desfeita.`,
                icon: '🗑️',
                danger: true,
                okText: 'Excluir'
            });
            if (!confirmed) return;

            try {
                const resp = await fetch(`/${programs_tenantSlug}/admin/programas/${id}/delete`, { method: 'POST' });
                const data = await resp.json();
                if (data.success) {
                    showToast(data.message);
                    setTimeout(() => location.reload(), 500);
                } else {
                    showToast(data.error || 'Erro', 'error');
                }
            } catch (err) {
                showToast('Erro de conexão', 'error');
            }
        }
        window.deleteProgram = deleteProgram;

        function showToast(msg, type = 'success') {
            const toast = document.getElementById('toast');
            toast.textContent = msg;
            toast.className = 'toast ' + type + ' show';
            setTimeout(() => toast.classList.remove('show'), 3000);
        }

        // CSS Confirmation Modal System
        var programsConfirmCallback = null;

        function showConfirm(options) {
            return new Promise((resolve) => {
                const { title, message, icon = '🚀', danger = false, okText = 'Confirmar' } = options;

                document.getElementById('gsapConfirmIcon').textContent = icon;
                document.getElementById('gsapConfirmTitle').textContent = title;
                document.getElementById('gsapConfirmMessage').textContent = message;

                const okBtn = document.getElementById('gsapConfirmOk');
                okBtn.textContent = okText;
                okBtn.className = danger ? 'gsap-btn danger' : 'gsap-btn confirm';

                programsConfirmCallback = resolve;
                
                const overlay = document.getElementById('gsapConfirmModal');
                overlay.classList.remove('closing');
                overlay.classList.add('active');
            });
        }

        function closeGsapConfirm(result) {
            if (programsConfirmCallback) {
                programsConfirmCallback(result);
                programsConfirmCallback = null;
            }

            const overlay = document.getElementById('gsapConfirmModal');
            overlay.classList.add('closing');
            
            setTimeout(() => {
                overlay.classList.remove('active', 'closing');
            }, 300);
        }

        // Check for auto-open param
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('action') === 'create') {
            openCreateProgramModal();
        }


    </script>