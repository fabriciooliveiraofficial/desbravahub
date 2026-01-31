<?php
// FORCE DEBUG
error_reporting(E_ALL);
ini_set('display_errors', 1);

$pageTitle = 'Atribuir Especialidade';
$pageIcon = 'assignment_ind';
?>
<style>
    /* ============ Clean & Modern UI Refinement ============ */
    :root {
        --bg-page: #f8fafc;
        --bg-card: #ffffff;
        --border-color: #e2e8f0;
        --text-primary: #0f172a;
        --text-secondary: #475569;
        --text-tertiary: #94a3b8;
        --accent-primary: #0ea5e9; /* Sky 500 */
        --accent-hover: #0284c7;   /* Sky 600 */
        --danger: #ef4444;
        --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        --radius-card: 16px;
        --radius-input: 10px;
    }

    /* Layout Structure */
    .content-container {
        max-width: 1280px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: 320px 1fr;
        gap: 24px;
        align-items: start;
        padding: 0 20px 40px;
    }

    /* Left Sidebar: Specialty Preview */
    .preview-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-card);
        padding: 24px;
        position: sticky;
        top: 24px;
        box-shadow: var(--shadow-sm);
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .specialty-header {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 12px;
        padding-bottom: 20px;
        border-bottom: 1px solid var(--border-color);
    }

    .specialty-icon-large {
        font-size: 3.5rem;
        line-height: 1;
        display: block;
        margin-bottom: 4px;
    }

    .specialty-title-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
        align-items: center;
        width: 100%;
    }

    .specialty-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-primary);
        line-height: 1.3;
        margin: 0;
        word-wrap: break-word;
    }

    .specialty-category {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        background: #f1f5f9;
        border-radius: 9999px;
        font-size: 0.75rem;
        color: var(--text-secondary);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        white-space: nowrap;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Meta Grid */
    .specialty-meta-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    .meta-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
    }

    .meta-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        font-weight: 700;
        color: var(--text-tertiary);
        letter-spacing: 0.05em;
    }

    .meta-value {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-primary);
    }

    .specialty-description {
        font-size: 0.9rem;
        color: var(--text-secondary);
        line-height: 1.6;
        text-align: center;
        padding: 0 8px;
    }

    .notification-brief {
        background: #f0f9ff;
        border: 1px solid #bae6fd;
        padding: 12px;
        border-radius: 8px;
        display: flex;
        gap: 10px;
        font-size: 0.8rem;
        color: #0369a1;
        line-height: 1.4;
        align-items: start;
    }

    /* Right Content: Main Form */
    .form-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-card);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }

    .form-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #fff;
    }

    .form-header h2 {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .selected-badge {
        background: #e0f2fe;
        color: #0284c7;
        padding: 4px 12px;
        border-radius: 999px;
        font-size: 0.85rem;
        font-weight: 700;
        border: 1px solid #bae6fd;
    }

    .form-body {
        padding: 24px;
        background: #fff;
    }

    /* Search & Filter */
    .search-wrapper {
        position: relative;
        margin-bottom: 20px;
    }

    .search-input {
        width: 100%;
        padding: 12px 16px 12px 44px;
        background: #fff;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-input);
        font-size: 0.95rem;
        color: var(--text-primary);
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .search-input:focus {
        border-color: var(--accent-primary);
        box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
        outline: none;
    }

    .search-wrapper i {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-tertiary);
        font-size: 1rem;
    }

    .selection-controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid var(--border-color);
    }

    .select-all-btn {
        background: transparent;
        border: 1px solid var(--border-color);
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 0.8rem;
        color: var(--text-secondary);
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .select-all-btn:hover {
        border-color: var(--accent-primary);
        color: var(--accent-primary);
        background: #f0f9ff;
    }

    /* Pathfinders Grid */
    .pathfinders-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 12px;
        max-height: 480px;
        overflow-y: auto;
        padding: 4px;
    }

    /* Card Styling */
    .pathfinder-card {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        background: #fff;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.2s;
        position: relative;
    }

    .pathfinder-card:hover:not(.disabled) {
        border-color: var(--accent-primary);
        background: #f8fafc;
        transform: translateY(-1px);
        box-shadow: var(--shadow-sm);
    }

    .pathfinder-card.selected {
        background: #f0f9ff;
        border-color: var(--accent-primary);
        box-shadow: 0 0 0 1px var(--accent-primary);
    }

    .pathfinder-card.disabled {
        background: #f1f5f9;
        opacity: 0.7;
        cursor: not-allowed;
        border-color: #cbd5e1;
    }

    /* Checkbox & Avatar */
    .checkbox-trigger {
        width: 20px;
        height: 20px;
        border: 2px solid #cbd5e1;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        flex-shrink: 0;
        background: #fff;
    }

    .pathfinder-card.selected .checkbox-trigger {
        background: var(--accent-primary);
        border-color: var(--accent-primary);
    }

    .pathfinder-card.selected .checkbox-trigger i {
        color: #fff;
        font-size: 12px;
        display: block; /* Ensure visibility */
    }
    
    .checkbox-trigger i {
        display: none;
    }

    .pf-avatar {
        width: 40px;
        height: 40px;
        background: #e2e8f0;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: #64748b;
        font-size: 0.9rem;
        flex-shrink: 0;
    }
    
    .pathfinder-card.selected .pf-avatar {
        background: var(--accent-primary);
        color: #fff;
    }

    .pf-info {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .pf-name {
        display: block;
        font-weight: 600;
        color: var(--text-primary);
        font-size: 0.9rem;
        line-height: 1.2;
        margin-bottom: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .pf-email {
        display: block;
        color: var(--text-tertiary);
        font-size: 0.75rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .assigned-label {
        position: absolute;
        top: 8px;
        right: 8px;
        background: #ffedd5;
        color: #c2410c;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    /* Delivery Config */
    .config-section {
        padding: 24px;
        background: #f8fafc;
        border-top: 1px solid var(--border-color);
    }

    .section-title {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 20px;
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .config-grid {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 24px;
    }

    .input-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .input-group label {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-secondary);
    }

    .form-control {
        background: #fff;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-input);
        padding: 10px 14px;
        color: var(--text-primary);
        font-size: 0.9rem;
        transition: border-color 0.2s;
        width: 100%;
    }

    .form-control:focus {
        border-color: var(--accent-primary);
        outline: none;
    }

    /* Footer Styles */
    .form-footer {
        margin-top: 24px;
        padding-top: 24px;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 16px;
    }

    .btn-secondary {
        padding: 10px 20px;
        border: 1px solid var(--border-color);
        background: #fff;
        color: var(--text-secondary);
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        font-size: 0.9rem;
        transition: all 0.2s;
    }

    .btn-secondary:hover {
        background: #f1f5f9;
        color: var(--text-primary);
        border-color: #cbd5e1;
    }

    .btn-primary {
        padding: 10px 24px;
        background: var(--accent-primary);
        color: #fff;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
        box-shadow: 0 2px 4px rgba(14, 165, 233, 0.2);
    }

    .btn-primary:hover:not(:disabled) {
        background: var(--accent-hover);
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(14, 165, 233, 0.3);
    }

    .btn-primary:disabled {
        background: #cbd5e1; /* Gray 300 */
        color: #64748b;    /* Gray 500 */
        cursor: not-allowed;
        box-shadow: none;
        transform: none;
    }

    /* Scrollbar */
    .pathfinders-grid::-webkit-scrollbar {
        width: 6px;
    }
    .pathfinders-grid::-webkit-scrollbar-track {
        background: transparent;
    }
    .pathfinders-grid::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }

    /* Responsive */
    @media (max-width: 900px) {
        .content-container {
            grid-template-columns: 1fr;
        }
        .preview-card {
            position: relative;
            top: 0;
            flex-direction: row;
            align-items: center;
            flex-wrap: wrap;
        }
        .specialty-header {
            flex-direction: row;
            border-bottom: none;
            padding-bottom: 0;
            flex: 1;
            text-align: left;
        }
        .specialty-title-group {
            align-items: flex-start;
        }
        .specialty-meta-grid {
            border-left: 1px solid var(--border-color);
            padding-left: 20px;
        }
    }

    @media (max-width: 600px) {
        .preview-card {
            flex-direction: column;
        }
        .specialty-header {
            flex-direction: column;
            text-align: center;
        }
        .specialty-title-group {
            align-items: center;
        }
        .specialty-meta-grid {
            border-left: none;
            padding-left: 0;
            width: 100%;
        }
        .config-grid {
            grid-template-columns: 1fr;
        }
        .form-footer {
            flex-direction: column-reverse;
        }
        .btn-primary, .btn-secondary {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<!-- Content -->
<div class="page-toolbar" style="margin-bottom: 24px; padding: 0 20px;">
    <a href="<?= base_url($tenant['slug'] . '/admin/especialidades') ?>" style="display: inline-flex; align-items: center; gap: 8px; color: var(--text-secondary); text-decoration: none; font-weight: 600; font-size: 0.9rem; transition: color 0.2s;">
        <i class="fa-solid fa-arrow-left"></i> Voltar para Especialidades
    </a>
</div>

<form id="assignForm" class="content-container">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
    
    <!-- Left Sidebar: Specialty Preview -->
    <div class="preview-card">
        <div class="specialty-header">
            <?php 
            $badgeIcon = trim($specialty['badge_icon'] ?? '🎓');
            if (str_contains($badgeIcon, 'fa-')): 
            ?>
                <i class="<?= htmlspecialchars($badgeIcon) ?> specialty-icon-large"></i>
            <?php else: ?>
                <span class="specialty-icon-large"><?= $badgeIcon ?></span>
            <?php endif; ?>
            
            <div class="specialty-title-group">
                <h2 class="specialty-title"><?= htmlspecialchars($specialty['name'] ?? 'Sem Nome') ?></h2>
                
                <div class="specialty-category">
                    <?php 
                    $catIcon = trim($specialty['category']['icon'] ?? '📂');
                    if (str_contains($catIcon, 'fa-')): 
                    ?>
                        <i class="<?= htmlspecialchars($catIcon) ?>"></i>
                    <?php else: ?>
                        <span><?= $catIcon ?></span>
                    <?php endif; ?>
                    <span><?= htmlspecialchars($specialty['category']['name'] ?? 'Geral') ?></span>
                </div>
            </div>
        </div>

        <div class="specialty-meta-grid">
            <div class="meta-item">
                <span class="meta-label">Dificuldade</span>
                <div class="meta-value">
                    <?php for($i=1; $i<=3; $i++): ?>
                        <i class="fa-solid fa-star" style="font-size: 0.8rem; color: <?= $i <= ($specialty['difficulty'] ?? 1) ? '#eab308' : '#e2e8f0' ?>;"></i>
                    <?php endfor; ?>
                </div>
            </div>
            <div class="meta-item">
                <span class="meta-label">XP Bônus</span>
                <div class="meta-value" style="color: var(--accent-hover);">+<?= $specialty['xp_reward'] ?? 0 ?></div>
            </div>
            <div class="meta-item">
                <span class="meta-label">Duração</span>
                <div class="meta-value"><?= $specialty['duration_hours'] ?? 0 ?>h</div>
            </div>
            <div class="meta-item">
                <span class="meta-label">Requisitos</span>
                <div class="meta-value"><?= count($specialty['requirements'] ?? []) ?></div>
            </div>
        </div>

        <?php if (!empty($specialty['requirements'])): ?>
        <div class="requirements-preview" style="margin-top: 10px;">
            <span class="meta-label" style="display: block; margin-bottom: 12px; text-align: center;">O QUE SERÁ CUMPRIDO:</span>
            <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 8px;">
                <?php foreach(array_slice($specialty['requirements'], 0, 5) as $req): ?>
                    <li style="display: flex; gap: 10px; font-size: 0.8rem; color: var(--text-secondary); line-height: 1.4; padding: 8px; background: #f8fafc; border-radius: 8px; border: 1px solid #f1f5f9;">
                        <i class="fa-solid fa-circle-check" style="color: #cbd5e1; margin-top: 2px;"></i>
                        <span style="overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                            <?= htmlspecialchars($req['title'] ?? 'Requisito') ?>
                        </span>
                    </li>
                <?php endforeach; ?>
                <?php if (count($specialty['requirements']) > 5): ?>
                    <li style="text-align: center; font-size: 0.75rem; color: var(--text-tertiary); font-weight: 600; padding-top: 4px;">
                        + <?= count($specialty['requirements']) - 5 ?> outros requisitos
                    </li>
                <?php endif; ?>
            </ul>
        </div>
        <?php endif; ?>

        <div class="notification-brief" style="margin-top: auto;">
            <i class="fa-solid fa-rocket" style="margin-top: 2px; color: var(--accent-primary);"></i>
            <span><strong>Pronto para decolar!</strong> Ao confirmar, a jornada será desbloqueada no app do desbravador.</span>
        </div>
    </div>

    <!-- Right Content: Association Form -->
    <div class="main-content" style="min-width: 0;">
        <div class="form-card">
            <div class="form-header">
                <h2> Selecionar Desbravadores</h2>
                <div class="selected-badge">
                    <span id="selectedCount">0</span> selecionado(s)
                </div>
            </div>
            
            <div class="form-body">
                <div class="search-wrapper">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="searchPathfinders" class="search-input" placeholder="Buscar por nome, email ou unidade..." oninput="filterPathfinders(this.value)">
                </div>

                <div class="selection-controls">
                    <span style="font-size: 0.8rem; color: var(--text-tertiary); font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Lista de Membros Ativos</span>
                    <button type="button" class="select-all-btn" onclick="toggleSelectAll()">
                        <div style="width: 16px; height: 16px; border: 2px solid currentColor; border-radius: 4px;"></div>
                        Selecionar Todos
                    </button>
                </div>

                <div class="pathfinders-grid" id="pathfindersList">
                    <?php if (empty($pathfinders)): ?>
                        <div style="grid-column: 1/-1; text-align: center; padding: 60px; color: var(--text-muted);">
                            <i class="fa-solid fa-user-slash" style="font-size: 3rem; opacity: 0.2; margin-bottom: 16px; display: block;"></i>
                            Nenhum desbravador ativo encontrado.
                        </div>
                    <?php else: ?>
                        <?php foreach ($pathfinders as $pf): ?>
                            <?php 
                                $isAssigned = in_array($pf['id'], $assignedUserIds ?? []); 
                                $nameParts = explode(' ', trim($pf['name']));
                                $initials = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
                            ?>
                            <label class="pathfinder-card <?= $isAssigned ? 'disabled' : '' ?>" 
                                   data-name="<?= strtolower(htmlspecialchars($pf['name'] ?? '')) ?>"
                                   data-email="<?= strtolower(htmlspecialchars($pf['email'] ?? '')) ?>">
                                
                                <input type="checkbox" 
                                       name="user_ids[]" 
                                       value="<?= $pf['id'] ?>"
                                       <?= $isAssigned ? 'disabled' : '' ?>
                                       onchange="updateCount(this)"
                                       style="position: absolute; opacity: 0; width: 0; height: 0; pointer-events: none;">
                                
                                <div class="checkbox-trigger">
                                    <i class="fa-solid fa-check"></i>
                                </div>

                                <div class="pf-avatar">
                                    <?= $initials ?>
                                </div>
                                
                                <div class="pf-info">
                                    <span class="pf-name"><?= htmlspecialchars($pf['name'] ?? '') ?></span>
                                    <span class="pf-email"><?= htmlspecialchars($pf['email'] ?? '') ?></span>
                                </div>

                                <?php if ($isAssigned): ?>
                                    <div class="assigned-label">Atribuído</div>
                                <?php endif; ?>
                            </label>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Configuration Section -->
            <div class="config-section">
                <div class="section-title">
                    <i class="fa-solid fa-sliders" style="color: var(--accent-primary);"></i>
                    Detalhes da Entrega
                </div>
                
                <div class="config-grid">
                    <div class="input-group">
                        <label for="due_date">Prazo de Conclusão (Opcional)</label>
                        <input type="date" id="due_date" name="due_date" class="form-control" min="<?= date('Y-m-d') ?>">
                    </div>
                    
                    <div class="input-group">
                        <label for="instructions">Mensagem para o Desbravador</label>
                        <textarea id="instructions" name="instructions" class="form-control" 
                                  placeholder="Escreva orientações ou um incentivo que o desbravador verá ao iniciar..."
                                  style="min-height: 80px; resize: vertical;"></textarea>
                    </div>
                </div>

                <div class="form-footer">
                    <a href="<?= base_url($tenant['slug'] . '/admin/especialidades') ?>" class="btn-secondary">
                        Cancelar
                    </a>
                    <button type="submit" class="btn-primary" id="submitBtn" disabled>
                        <i class="fa-solid fa-unlock-keyhole" style="font-size: 0.9rem;"></i> Liberar Missão
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
<!-- End Content -->

    <!-- Success/Error Toast -->
    <div id="toast" style="position: fixed; bottom: 24px; right: 24px; padding: 16px 24px; background: #fff; border-left: 4px solid var(--accent-green); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); border-radius: 8px; transform: translateY(150%); transition: transform 0.3s ease; z-index: 2000; font-weight: 600; color: #0f172a;">
        Mensagem aqui
    </div>

    <script>
        // Handle selection count and card states
        function updateCount(checkbox) {
            if(!checkbox) return;
            
            const card = checkbox.closest('.pathfinder-card');
            if(checkbox.checked) {
                card.classList.add('selected');
            } else {
                card.classList.remove('selected');
            }

            const checkedCount = document.querySelectorAll('input[name="user_ids[]"]:checked:not(:disabled)').length;
            document.getElementById('selectedCount').textContent = checkedCount;
            document.getElementById('submitBtn').disabled = checkedCount === 0;
        }


        
        // Premium Select All logic
        function toggleSelectAll() {
            const visibleCheckboxes = document.querySelectorAll('.pathfinder-card:not([style*="display: none"]) input[name="user_ids[]"]:not(:disabled)');
            if (visibleCheckboxes.length === 0) return;

            const allVisibleChecked = Array.from(visibleCheckboxes).every(cb => cb.checked);
            
            visibleCheckboxes.forEach(cb => {
                cb.checked = !allVisibleChecked;
                updateCount(cb);
            });
        }
        
        // Fast search with empty state support
        function filterPathfinders(query) {
            const q = query.toLowerCase().trim();
            const cards = document.querySelectorAll('.pathfinder-card');
            let visibleCount = 0;
            
            cards.forEach(card => {
                const name = card.dataset.name || '';
                const email = card.dataset.email || '';
                const matches = name.includes(q) || email.includes(q);
                card.style.display = matches ? 'flex' : 'none';
                if(matches) visibleCount++;
            });

            // Handle empty search results
            const list = document.getElementById('pathfindersList');
            const noResults = document.getElementById('noResultsMsg');
            
            if (visibleCount === 0) {
                if (!noResults) {
                    const msg = document.createElement('div');
                    msg.id = 'noResultsMsg';
                    msg.style.cssText = 'grid-column: 1/-1; text-align: center; padding: 40px; color: var(--text-muted); opacity: 0.8;';
                    msg.innerHTML = '<i class="fa-solid fa-face-frown" style="font-size: 2rem; margin-bottom: 12px; display: block;"></i> Nenhum membro encontrado para esta busca.';
                    list.appendChild(msg);
                }
            } else if (noResults) {
                noResults.remove();
            }
        }
        
        // Premium Toast Implementation
        function displayPageToast(message, isError = false) {
            const toast = document.getElementById('toast');
            toast.innerHTML = (isError ? '⚠️ ' : '✅ ') + message;
            toast.style.background = isError ? 'rgba(239, 68, 68, 0.95)' : 'rgba(16, 185, 129, 0.95)';
            toast.style.color = '#fff';
            toast.style.border = 'none';
            toast.style.backdropFilter = 'blur(10px)';
            toast.style.boxShadow = '0 10px 25px rgba(0,0,0,0.3)';
            toast.style.transform = 'translateY(0)';
            
            setTimeout(() => {
                toast.style.transform = 'translateY(150%)';
            }, 4000);
        }
        
        // Submit Flow with Loading State
        document.getElementById('assignForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitBtn');
            const originalHTML = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Processando...';
            
            try {
                const formData = new FormData(e.target);
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    displayPageToast(data.message || 'Missão liberada! Os desbravadores já podem começar.');
                    submitBtn.innerHTML = '<i class="fa-solid fa-rocket"></i> Missão Iniciada!';
                    setTimeout(() => {
                        window.location.href = '<?= base_url($tenant['slug'] . '/admin/especialidades') ?>';
                    }, 1200);
                } else {
                    displayPageToast(data.error || 'Falha na atribuição. Verifique e tente novamente.', true);
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalHTML;
                }
            } catch (err) {
                console.error('[Assign] Error:', err);
                displayPageToast('Erro de conexão com o servidor', true);
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalHTML;
            }
        });

        // Initialize tooltips if any or other micro-interactions
        document.querySelectorAll('.meta-item').forEach(item => {
            item.addEventListener('mouseenter', () => {
                item.style.transform = 'translateY(-2px)';
            });
            item.addEventListener('mouseleave', () => {
                item.style.transform = 'translateY(0)';
            });
        });
    </script>
    <script>
        console.log('[DEBUG] Assign.php execution reached end of view, about to load footer.');
    </script>

