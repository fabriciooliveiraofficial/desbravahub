<?php
/**
 * Admin: Browse Classes (Learning Programs type 'class')
 */
$pageTitle = 'Catálogo de Classes';
$pageIcon = 'school';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<style>
    /* ============ Mirror Styles from Specialty Repository ============ */
    @media (max-width: 768px) {
        .page-hero { padding-top: 60px; }
    }

    .page-hero { margin-bottom: 24px; }
    .page-hero h1 {
        font-size: 2rem;
        font-weight: 700;
        margin: 0 0 8px 0;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 12px;
    }

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
        box-shadow: var(--shadow-sm);
    }

    .search-section { flex: 1; min-width: 240px; max-width: 400px; }
    .search-wrapper { position: relative; }
    .search-input {
        width: 100%;
        padding: 10px 16px 10px 44px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        background: var(--bg-dark);
        color: var(--text-main);
        font-size: 0.9rem;
        transition: all 0.2s;
    }
    .search-input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.1);
    }
    .search-icon {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        font-size: 1.2rem;
        pointer-events: none;
    }

    .actions-group { display: flex; gap: 12px; flex-wrap: wrap; }
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
    .btn-toolbar.primary {
        background: linear-gradient(135deg, var(--primary), var(--primary-hover));
        color: white;
        box-shadow: var(--shadow-cyan);
        border: none;
    }
    .btn-toolbar.primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(6, 182, 212, 0.3);
    }

    /* Tabs */
    .tabs {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 24px;
        padding: 4px 4px 16px 4px;
        overflow-x: auto;
        scrollbar-width: thin;
    }
    .tab-btn {
        flex-shrink: 0;
        padding: 8px 16px;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 50px;
        color: var(--text-muted);
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        white-space: nowrap;
        font-size: 0.85rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 8px;
        --cat-color: var(--primary);
    }
    .tab-btn::before {
        content: '';
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: var(--cat-color);
        box-shadow: 0 0 8px var(--cat-color);
        opacity: 0.7;
        transition: all 0.2s;
    }
    .tab-btn.active {
        background: rgba(255, 255, 255, 0.5);
        border-color: var(--cat-color);
        color: var(--cat-color);
        font-weight: 700;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    /* Sections */
    .category-section { margin-bottom: 32px; animation: fadeIn 0.4s ease-out forwards; }
    .category-header {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 20px;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-left: 4px solid var(--cat-color);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
        position: relative;
        overflow: hidden;
    }
    .category-icon { font-size: 2rem; z-index: 1; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1)); }
    .category-info { flex: 1; z-index: 1; }
    .category-info h2 { margin: 0; font-size: 1.25rem; color: var(--text-main); font-weight: 700; }
    .category-info p { margin: 4px 0 0; font-size: 0.9rem; color: var(--text-muted); }
    .category-count { padding: 6px 14px; background: var(--bg-dark); border-radius: 20px; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); border: 1px solid var(--border-color); margin-right: 8px; }

    .btn-delete-category {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        border: 1px solid transparent;
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
        cursor: pointer;
        transition: all 0.2s ease;
        flex-shrink: 0;
        z-index: 1;
    }

    .btn-delete-category:hover {
        background: #ef4444;
        color: white;
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    }

    /* Grid & Cards */
    .classes-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 16px;
    }
    .class-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 20px;
        display: flex;
        flex-direction: column;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        min-height: 200px;
        cursor: pointer;
    }
    .class-card:hover { border-color: var(--primary); transform: translateY(-4px); box-shadow: 0 12px 24px rgba(0, 0, 0, 0.04); }
    
    .class-header-row { display: flex; gap: 16px; margin-bottom: 16px; align-items: flex-start; }
    .class-badge { font-size: 2.5rem; line-height: 1; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1)); transition: transform 0.3s ease; }
    .class-card:hover .class-badge { transform: scale(1.1) rotate(5deg); }
    .class-title-block { flex: 1; }
    .class-title-block h3 { margin: 0 0 8px 0; font-size: 1.1rem; font-weight: 700; color: var(--text-main); }
    
    .class-meta { display: flex; flex-wrap: wrap; gap: 8px; }
    .meta-tag { display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; background: var(--bg-dark); border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); }
    .difficulty-stars { color: #fbbf24; letter-spacing: 1px; }

    .class-desc { flex: 1; color: var(--text-muted); font-size: 0.9rem; line-height: 1.6; margin-bottom: 20px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }

    .class-footer { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-top: auto; padding-top: 16px; border-top: 1px solid var(--border-color); }
    .xp-reward { display: flex; align-items: center; gap: 6px; color: var(--accent-emerald); background: var(--accent-emerald-bg); padding: 4px 10px; border-radius: 20px; font-weight: 700; font-size: 0.8rem; }
    
    .card-actions { display: flex; gap: 8px; }
    .btn-icon-action { width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 8px; border: 1px solid transparent; background: transparent; color: var(--text-muted); cursor: pointer; transition: all 0.2s; }
    .btn-icon-action:hover { background: var(--bg-dark); color: var(--text-main); }
    .btn-icon-action.danger:hover { background: #fee2e2; color: #ef4444; }
    
    .btn-card-assign { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; background: linear-gradient(135deg, var(--primary), var(--primary-hover)); color: white; border-radius: 8px; font-weight: 600; font-size: 0.85rem; text-decoration: none; box-shadow: 0 2px 4px rgba(6,182,212,0.2); transition: all 0.2s; }
    .btn-card-assign:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(6,182,212,0.3); }

    /* Modals (Unified) */
    .modal-overlay, .create-modal-overlay { 
        position: fixed; 
        inset: 0; 
        background: rgba(15, 23, 42, 0.6); 
        backdrop-filter: blur(4px); 
        z-index: 1000; 
        display: none; 
        align-items: center; 
        justify-content: center; 
        padding: 20px; 
        animation: fadeIn 0.2s ease-out;
    }
    .modal-overlay.active, .create-modal-overlay.active { display: flex; }

    .modal-content, .create-modal { 
        background: var(--bg-card); 
        border: 1px solid var(--border-color); 
        border-radius: 16px; 
        width: 100%; 
        max-width: 600px; 
        max-height: 85vh; 
        display: flex; 
        flex-direction: column; 
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); 
        animation: modalSlideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        overflow: hidden;
    }

    .modal-header { padding: 20px 24px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; }
    .modal-body { padding: 24px; overflow-y: auto; flex: 1; }
    .modal-footer { padding: 20px 24px; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end; gap: 12px; }

    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes modalSlideUp { from { opacity: 0; transform: translateY(20px) scale(0.96); } to { opacity: 1; transform: translateY(0) scale(1); } }
</style>

<div class="page-toolbar">
    <div class="search-section">
        <div class="search-wrapper">
            <span class="material-icons-round search-icon">search</span>
            <input type="text" id="searchInput" class="search-input" placeholder="Buscar classe..." oninput="filterClasses()">
        </div>
    </div>
    <div class="actions-group">
        <!-- Criação centralizada no Mission Control -->
    </div>
</div>

<div class="tabs">
    <button class="tab-btn active" data-category="all" onclick="filterByCategory('all')" style="--cat-color: var(--primary);">
        Todas
    </button>
    <?php foreach ($categories as $cat): ?>
        <button class="tab-btn" data-category="<?= $cat['id'] ?>" onclick="filterByCategory('<?= $cat['id'] ?>')" style="--cat-color: <?= $cat['color'] ?>;">
            <?php if (str_starts_with($cat['icon'] ?? '', 'fa-')): ?>
                <i class="<?= htmlspecialchars($cat['icon']) ?>"></i>
            <?php elseif (str_contains($cat['icon'] ?? '', ':')): ?>
                <iconify-icon icon="<?= htmlspecialchars($cat['icon']) ?>"></iconify-icon>
            <?php else: ?>
                <?= $cat['icon'] ?>
            <?php endif; ?>
            <?= htmlspecialchars($cat['name']) ?>
        </button>
    <?php endforeach; ?>
</div>

<?php foreach ($grouped as $catId => $data): ?>
    <section class="category-section" data-category="<?= $catId ?>">
        <div class="category-header" style="--cat-color: <?= $data['category']['color'] ?>;">
            <span class="category-icon">
                <?php if (str_starts_with($data['category']['icon'] ?? '', 'fa-')): ?>
                    <i class="<?= htmlspecialchars($data['category']['icon']) ?>" style="color: <?= htmlspecialchars($data['category']['color']) ?>;"></i>
                <?php elseif (str_contains($data['category']['icon'] ?? '', ':')): ?>
                    <iconify-icon icon="<?= htmlspecialchars($data['category']['icon']) ?>" style="color: <?= htmlspecialchars($data['category']['color']) ?>;"></iconify-icon>
                <?php else: ?>
                    <?= $data['category']['icon'] ?>
                <?php endif; ?>
            </span>
            <div class="category-info">
                <h2><?= htmlspecialchars($data['category']['name']) ?></h2>
                <p><?= htmlspecialchars($data['category']['description']) ?></p>
            </div>
            <span class="category-count"><?= count($data['specialties']) ?> classes</span>
            <?php if (!empty($data['category']['is_learning_category'])): ?>
                <button type="button" class="btn-delete-category" title="Excluir categoria e todas as classes"
                    onclick="event.stopPropagation(); deleteCategoryWithPrograms('<?= $data['category']['db_id'] ?? '' ?>', '<?= htmlspecialchars(addslashes($data['category']['name']), ENT_QUOTES) ?>', <?= count($data['specialties']) ?>);">
                    <span class="material-icons-round">delete_forever</span>
                </button>
            <?php endif; ?>
        </div>

        <div class="classes-grid">
            <?php foreach ($data['specialties'] as $spec): ?>
                <?php
                $specJson = htmlspecialchars(json_encode($spec, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
                ?>
                <div class="class-card" data-name="<?= strtolower($spec['name']) ?>" data-specialty="<?= $specJson ?>" data-class="<?= $specJson ?>" onclick="openClassDetailsModal(this)">
                    <div class="class-header-row">
                        <div class="class-badge">
                            <?php if (str_starts_with($spec['badge_icon'] ?? '', 'fa-')): ?>
                                <i class="<?= htmlspecialchars($spec['badge_icon']) ?>"></i>
                            <?php elseif (str_contains($spec['badge_icon'] ?? '', ':')): ?>
                                <iconify-icon icon="<?= htmlspecialchars($spec['badge_icon']) ?>"></iconify-icon>
                            <?php else: ?>
                                <?= $spec['badge_icon'] ?>
                            <?php endif; ?>
                        </div>
                        <div class="class-title-block">
                            <h3><?= htmlspecialchars($spec['name']) ?></h3>
                            <div class="class-meta">
                                <span class="meta-tag" title="Tipo">
                                    <?= ($spec['type'] ?? 'indoor') === 'outdoor' ? '<span class="material-icons-round" style="font-size:12px">forest</span> Externo' : '<span class="material-icons-round" style="font-size:12px">home</span> Interno' ?>
                                </span>
                                <span class="meta-tag" title="Duração">
                                    <span class="material-icons-round" style="font-size:12px">schedule</span>
                                    <?= $spec['duration_hours'] ?? 4 ?>h
                                </span>
                                <span class="meta-tag difficulty-stars" title="Dificuldade">
                                    <?= str_repeat('★', $spec['difficulty'] ?? 1) . str_repeat('☆', 5 - ($spec['difficulty'] ?? 1)) ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <p class="class-desc">
                        <?= htmlspecialchars($spec['description'] ?? 'Sem descrição disponível.') ?>
                    </p>

                    <div class="class-footer">
                        <span class="xp-reward" title="XP Recompensa">
                            <span class="material-icons-round" style="font-size:14px">bolt</span>
                            <?= $spec['xp_reward'] ?? 100 ?>
                        </span>
                        <div class="card-actions">
                            <button type="button" class="btn-icon-action" title="Editar Detalhes"
                                onclick="event.stopPropagation(); openEditClassModal(this.closest('.class-card').getAttribute('data-class'));">
                                <span class="material-icons-round" style="font-size:18px">settings</span>
                            </button>
                            <a href="<?= base_url($tenant['slug'] . '/admin/especialidades/prog_' . $spec['id'] . '/atribuir') ?>"
                                class="btn-icon-action" title="Atribuir" onclick="event.stopPropagation();">
                                <span class="material-icons-round" style="font-size:18px">rocket_launch</span>
                            </a>
                            <button class="btn-icon-action" title="Editar" onclick="openEditModal(`<?= htmlspecialchars(json_encode($spec)) ?>`)">
                                <span class="material-icons-round" style="font-size:18px">edit</span>
                            </button>
                            <button type="button" class="btn-icon-action danger" title="Excluir"
                                onclick="event.stopPropagation(); deleteClass('<?= $spec['id'] ?>', '<?= htmlspecialchars(addslashes($spec['name'])) ?>');">
                                <span class="material-icons-round" style="font-size:18px">delete</span>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
<?php endforeach; ?>

<!-- Create Class Modal -->
<div id="createClassModal" class="create-modal-overlay" onclick="closeCreateClassModalOverlay(event)">
    <div class="create-modal" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h2><span class="material-icons-round" style="color:var(--primary)">add_circle</span> Nova Classe</h2>
            <button class="btn-icon-action" onclick="closeCreateClassModal()"><span class="material-icons-round">close</span></button>
        </div>
        <form id="createClassForm" onsubmit="submitNewClass(event)">
            <input type="hidden" name="type" value="class">
            <div class="modal-body">
                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div class="form-group">
                        <label style="display:block; margin-bottom:8px; font-weight:600;">Nível/Categoria *</label>
                        <select name="category_id" required style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--border-color); background:var(--bg-dark); color:var(--text-main);">
                            <option value="">Selecione...</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>">
                                    <?php
                                        $icon = $cat['icon'] ?? '📂';
                                        if(str_starts_with($icon, 'fa-')) echo '📂'; // Fallback for select option text
                                        else if(str_contains($icon, ':')) echo '📂';
                                        else echo $icon;
                                    ?> 
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="classIcon" style="display:block; margin-bottom:8px; font-weight:600;">Ícone</label>
                        <div class="input-wrapper">
                            <input type="hidden" id="classIcon" name="icon" value="noto:blue-book">
                            <button type="button" class="icon-picker-trigger" onclick="openClassIconPicker()">
                                <div class="icon-picker-preview" id="classIconPreview">
                                    <iconify-icon icon="noto:blue-book" style="font-size: 1.5rem;"></iconify-icon>
                                </div>
                                <span class="icon-picker-text" id="classIconText">noto:blue-book</span>
                                <i class="fa-solid fa-chevron-down icon-picker-arrow"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="display:block; margin-bottom:8px; font-weight:600;">Nome da Classe *</label>
                    <input type="text" name="name" required placeholder="Ex: Classe de Amigo - 2026" style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--border-color); background:var(--bg-dark); color:var(--text-main);">
                </div>

                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div class="form-group">
                        <label style="display:block; margin-bottom:8px; font-weight:600;">Dificuldade</label>
                        <select name="difficulty" style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--border-color); background:var(--bg-dark); color:var(--text-main);">
                            <option value="1">⭐ Muito Fácil</option>
                            <option value="2" selected>⭐⭐ Fácil</option>
                            <option value="3">⭐⭐⭐ Médio</option>
                            <option value="4">⭐⭐⭐⭐ Difícil</option>
                            <option value="5">⭐⭐⭐⭐⭐ Muito Difícil</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom:8px; font-weight:600;">XP Recompensa</label>
                        <input type="number" name="xp_reward" value="500" min="10" style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--border-color); background:var(--bg-dark); color:var(--text-main);">
                    </div>
                </div>

                <div class="form-group">
                    <label style="display:block; margin-bottom:8px; font-weight:600;">Descrição</label>
                    <textarea name="description" placeholder="Objetivos desta classe..." style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--border-color); background:var(--bg-dark); color:var(--text-main); min-height:80px;"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-toolbar secondary" onclick="closeCreateClassModal()">Cancelar</button>
                <button type="submit" class="btn-toolbar primary">
                    <span class="material-icons-round">rocket_launch</span> Criar Classe
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Details Modal -->
<div id="classDetailsModal" class="modal-overlay" onclick="closeClassDetailsModalOverlay(event)">
    <div class="modal-content" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h2 style="display:flex; align-items:center; gap:12px; margin:0;">
                <span id="modalBadge" style="font-size:1.5rem;"></span>
                <span id="modalTitle"></span>
            </h2>
            <button class="btn-icon-action" onclick="closeClassDetailsModal()"><span class="material-icons-round">close</span></button>
        </div>
        <div class="modal-body">
            <div id="modalTags" style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:24px;"></div>
            <div class="modal-section">
                <h4 style="margin:0 0 12px; font-size:0.95rem; color:var(--text-main); display:flex; align-items:center; gap:8px;">
                    <span class="material-icons-round">description</span> Descrição
                </h4>
                <p id="modalDescription" style="color:var(--text-secondary); line-height:1.6;"></p>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-toolbar secondary" onclick="closeClassDetailsModal()">Fechar</button>
            <a id="modalAssignBtn" href="#" class="btn-toolbar primary">
                <span class="material-icons-round">group_add</span> Atribuir Classe
            </a>
        </div>
    </div>
</div>

<!-- Edit Class Details Modal [Tactical Pathfinder Redesign] -->
<style>
    #editClassDetailsModal .modal-content {
        max-width: 650px;
        max-height: 94vh;
        display: flex;
        flex-direction: column;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 20px;
        box-shadow: var(--shadow-2xl), 0 0 40px rgba(6, 182, 212, 0.05);
        overflow: hidden;
    }

    #editClassDetailsModal .modal-header {
        flex-shrink: 0;
        background: linear-gradient(to bottom, rgba(6, 182, 212, 0.05), transparent);
        border-bottom: 1px solid var(--border-color);
        padding: 24px 32px;
    }

    #editClassDetailsModal form {
        overflow-y: auto;
        flex: 1;
        padding: 32px;
        scrollbar-width: thin;
        scrollbar-color: var(--primary) transparent;
    }

    #editClassDetailsModal form::-webkit-scrollbar {
        width: 6px;
    }

    #editClassDetailsModal form::-webkit-scrollbar-track {
        background: transparent;
    }

    #editClassDetailsModal form::-webkit-scrollbar-thumb {
        background: var(--border-color);
        border-radius: 10px;
    }

    #editClassDetailsModal form::-webkit-scrollbar-thumb:hover {
        background: var(--primary);
    }

    #editClassDetailsModal .modal-header h3 {
        font-size: 1.25rem;
        letter-spacing: -0.02em;
        font-weight: 800;
        text-transform: uppercase;
        color: var(--text-dark);
    }

    #editClassDetailsModal .tactical-label {
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: var(--text-muted);
        margin-bottom: 10px;
        display: block;
    }

    #editClassDetailsModal .form-control-tactical {
        width: 100%;
        background: var(--bg-dark);
        border: 1.5px solid var(--border-color);
        border-radius: 12px;
        padding: 12px 16px;
        color: var(--text-main);
        font-weight: 500;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }

    #editClassDetailsModal .form-control-tactical:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(6, 182, 212, 0.1);
        outline: none;
        background: var(--bg-card);
    }

    /* Fair Play HUD */
    .fair-play-hud {
        display: grid;
        grid-template-columns: 2.2fr 0.8fr 1fr;
        gap: 0;
        padding: 24px 20px;
        background: rgba(6, 182, 212, 0.03);
        border-radius: 16px;
        border: 1px dashed var(--border-color);
        margin: 32px 0;
        position: relative;
    }

    .fair-play-hud::before {
        content: 'FAIR PLAY ENGINE / AUTO-CALC';
        position: absolute;
        top: 0;
        left: 24px;
        transform: translateY(-50%);
        background: var(--bg-card);
        padding: 0 12px;
        font-size: 8px;
        font-weight: 900;
        color: var(--primary);
        letter-spacing: 0.15em;
        white-space: nowrap;
        z-index: 1;
    }

    .hud-metric {
        display: flex;
        flex-direction: column;
        gap: 6px;
        padding: 0 20px;
        position: relative;
    }

    .hud-metric:not(:first-child)::before {
        content: '';
        position: absolute;
        left: 0;
        top: 10%;
        bottom: 10%;
        width: 1px;
        background: var(--border-color);
        opacity: 0.8;
    }

    .hud-metric .tactical-label {
        margin-bottom: 2px !important;
        opacity: 0.7;
    }

    .hud-metric select.form-control-tactical {
        border: none !important;
        background: transparent !important;
        padding: 0 !important;
        font-size: 1rem !important;
        font-weight: 800 !important;
        height: auto !important;
        cursor: pointer;
        width: 100%;
        color: var(--text-dark);
        appearance: none;
    }

    .hud-metric .value-container {
        font-family: 'JetBrains Mono', monospace;
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--text-dark);
        display: flex;
        align-items: center;
        gap: 8px;
        height: 24px; /* Align with select height */
    }

    .hud-metric input[readonly] {
        background: transparent !important;
        border: none !important;
        padding: 0 !important;
        width: 60px !important;
        cursor: default !important;
        color: var(--text-dark) !important;
        font-family: inherit;
        font-weight: inherit;
        font-size: inherit;
        outline: none;
    }

    /* Icon Picker Premium */
    .icon-trigger-premium {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 14px 20px;
        background: var(--bg-dark);
        border: 1.5px solid var(--border-color);
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .icon-trigger-premium:hover {
        border-color: var(--primary);
        background: rgba(6, 182, 212, 0.05);
    }

    .icon-preview-box {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--bg-card);
        border-radius: 10px;
        box-shadow: var(--shadow-sm);
        font-size: 1.5rem;
        color: var(--primary);
    }

    /* Outdoor Toggle Premium */
    .outdoor-feature-card {
        padding: 20px;
        border-radius: 16px;
        border: 1.5px solid var(--border-color);
        background: var(--bg-card);
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        gap: 16px;
    }

    #editClassOutdoor:checked + .outdoor-feature-card {
        border-color: var(--accent-emerald);
        background: var(--accent-emerald-bg);
    }

    .feature-icon-box {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: var(--bg-dark);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        transition: all 0.3s;
    }

    #editClassOutdoor:checked + .outdoor-feature-card .feature-icon-box {
        background: var(--accent-emerald);
        color: white;
    }
</style>

<div id="editClassDetailsModal" class="modal-overlay" onclick="if(event.target === this) closeEditClassModal();">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Redefinir Classe</h3>
            <button onclick="closeEditClassModal()" style="background: var(--bg-dark); border: none; cursor: pointer; color: var(--text-muted); width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; transition: all 0.2s;">
                <span class="material-icons-round" style="font-size: 20px;">close</span>
            </button>
        </div>

        <form id="editClassDetailsForm" onsubmit="submitEditClassDetails(event)">
            <input type="hidden" name="program_id" id="editClassId">

            <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 24px;">
                <!-- Main Fields -->
                <div style="display: flex; flex-direction: column; gap: 24px;">
                    <div class="form-group">
                        <label class="tactical-label">ID / Nome da Classe</label>
                        <input type="text" name="name" id="editClassName" class="form-control-tactical" required placeholder="Ex: Pioneiro do Céu">
                    </div>

                    <div class="form-group">
                        <label class="tactical-label">Objetivos e Missão</label>
                        <textarea name="description" id="editClassDescription" class="form-control-tactical" rows="4" style="resize: none;" placeholder="Descreva os requisitos e o propósito desta classe..."></textarea>
                    </div>
                </div>

                <!-- Side Panel -->
                <div style="display: flex; flex-direction: column; gap: 24px;">
                    <div class="form-group">
                        <label class="tactical-label">Nível Hierárquico</label>
                        <select name="category_id" id="editClassCategory" class="form-control-tactical" required>
                            <option value="" disabled selected>Selecionar...</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="tactical-label">Identidade Visual</label>
                        <input type="hidden" id="editClassIcon" name="icon">
                        <div class="icon-trigger-premium" onclick="IconPicker.open(document.getElementById('editClassIcon').value, (sel) => {
                                document.getElementById('editClassIcon').value = sel;
                                document.getElementById('editClassIconPreview').innerHTML = `<iconify-icon icon='${sel}'></iconify-icon>`;
                                document.getElementById('editClassIconText').textContent = sel;
                            })">
                            <div class="icon-preview-box" id="editClassIconPreview">
                                <iconify-icon icon="noto:blue-book"></iconify-icon>
                            </div>
                            <div style="flex: 1; overflow: hidden;">
                                <span id="editClassIconText" style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">noto:blue-book</span>
                                <span style="font-size: 0.65rem; color: var(--primary); font-weight: 800; text-transform: uppercase;">Mudar Ícone</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fair Play HUD Section -->
            <div class="fair-play-hud">
                <div class="hud-metric">
                    <label class="tactical-label">Dificuldade</label>
                    <select name="difficulty" id="editClassDifficulty" class="form-control-tactical" onchange="applyEditFairPlayClass()">
                        <option value="1">⭐ Muito Fácil</option>
                        <option value="2">⭐⭐ Fácil</option>
                        <option value="3">⭐⭐⭐ Médio</option>
                        <option value="4">⭐⭐⭐⭐ Difícil</option>
                        <option value="5">⭐⭐⭐⭐⭐ Expert</option>
                    </select>
                </div>

                <div class="hud-metric">
                    <label class="tactical-label">XP Yield</label>
                    <div class="value-container">
                        <span class="material-icons-round" style="font-size: 18px; color: var(--accent-emerald);">bolt</span>
                        <input type="number" name="xp_reward" id="editClassXp" readonly value="280">
                    </div>
                </div>

                <div class="hud-metric">
                    <label class="tactical-label">Est. Time</label>
                    <div class="value-container">
                        <span class="material-icons-round" style="font-size: 18px; color: var(--accent-amber);">schedule</span>
                        <input type="number" name="estimated_hours" id="editClassDuration" readonly value="8">
                        <span style="font-size: 0.7rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Hrs</span>
                    </div>
                </div>
            </div>

            <!-- Outdoor Switch Section -->
            <div style="margin-top: 32px;">
                <input type="checkbox" name="is_outdoor" id="editClassOutdoor" value="1" style="display: none;">
                <label for="editClassOutdoor" class="outdoor-feature-card">
                    <div class="feature-icon-box">
                        <span class="material-icons-round">forest</span>
                    </div>
                    <div>
                        <span style="display: block; font-weight: 700; color: var(--text-dark); margin-bottom: 2px;">Experiência Outdoor</span>
                        <span style="display: block; font-size: 0.8rem; color: var(--text-muted); line-height: 1.4;">Atividades práticas em ambiente externo. Exige aprovação manual de evidências.</span>
                    </div>
                </label>
            </div>

            <!-- Footer Actions -->
            <div style="display: flex; justify-content: flex-end; gap: 16px; margin-top: 40px; padding-top: 24px; border-top: 1px solid var(--border-color);">
                <button type="button" class="btn-toolbar" onclick="closeEditClassModal()" style="background: transparent; border: 1.5px solid var(--border-color); color: var(--text-muted);">Descartar</button>
                <button type="submit" class="btn-toolbar primary" style="padding: 12px 32px; height: auto;">
                    <span class="material-icons-round">save</span>
                    Finalizar Alterações
                </button>
            </div>
        </form>
    </div>
</div>ointer; font-weight: 600;">💾
                    Salvar Alterações</button>
            </div>
        </form>
    </div>
</div>



<script>
    var tenantSlug = '<?= $tenant['slug'] ?>';
    
    function filterByCategory(catId) {
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.toggle('active', btn.dataset.category === catId));
        document.querySelectorAll('.category-section').forEach(sec => {
            sec.style.display = (catId === 'all' || sec.dataset.category === catId) ? '' : 'none';
        });
    }

    function filterClasses() {
        const query = document.getElementById('searchInput').value.toLowerCase();
        document.querySelectorAll('.class-card').forEach(card => {
            card.style.display = card.dataset.name.includes(query) ? '' : 'none';
        });
    }

    function openClassDetailsModal(cardElement) {
        const data = JSON.parse(cardElement.dataset.specialty);
        const modalBadge = document.getElementById('modalBadge');
        const icon = data.badge_icon || '📘';
        
        if (icon.startsWith('fa-')) {
            modalBadge.innerHTML = `<i class="${icon}"></i>`;
        } else if (icon.includes(':')) {
            modalBadge.innerHTML = `<iconify-icon icon="${icon.split(' ')[0]}"></iconify-icon>`;
        } else {
            modalBadge.textContent = icon;
        }
        
        document.getElementById('modalTitle').textContent = data.name;
        document.getElementById('modalDescription').textContent = data.description || 'Sem descrição.';
        document.getElementById('modalAssignBtn').href = `/${tenantSlug}/admin/especialidades/prog_${data.id}/atribuir`;
        
        const tags = `
            <span class="class-tag">⏱️ ${data.duration_hours}h</span>
            <span class="class-tag">${'⭐'.repeat(data.difficulty)}</span>
            <span class="class-tag" style="background:rgba(16,185,129,0.1); color:#10b981; border:none;">🌟 ${data.xp_reward} XP</span>
        `;
        document.getElementById('modalTags').innerHTML = tags;
        document.getElementById('classDetailsModal').classList.add('active');
    }

    function closeClassDetailsModal() { document.getElementById('classDetailsModal').classList.remove('active'); }
    function closeClassDetailsModalOverlay(e) { if(e.target.id === 'classDetailsModal') closeClassDetailsModal(); }

    function openCreateClassModal() { document.getElementById('createClassModal').classList.add('active'); }
    function closeCreateClassModal() { document.getElementById('createClassModal').classList.remove('active'); }
    function closeCreateClassModalOverlay(e) { if(e.target.id === 'createClassModal') closeCreateClassModal(); }

    function openEditModal(data) {
        if (typeof data === 'string') data = JSON.parse(data);
        const id = String(data.program_id || data.id).replace('prog_', '');
        window.location.href = `/${tenantSlug}/admin/programas/${id}/editar`;
    }

    // Fair Play Engine — mirrors create_modal.php
    var EDIT_FAIR_PLAY_CLASS = {
        1: { xp: 100,  hours: 4  },
        2: { xp: 180,  hours: 8  },
        3: { xp: 280,  hours: 16 },
        4: { xp: 400,  hours: 32 },
        5: { xp: 500,  hours: 60 },
    };

    function applyEditFairPlayClass() {
        const diff = parseInt(document.getElementById('editClassDifficulty').value, 10);
        const rule = EDIT_FAIR_PLAY_CLASS[diff];
        if (!rule) return;
        document.getElementById('editClassXp').value       = rule.xp;
        document.getElementById('editClassDuration').value = rule.hours;
    }

    window.openEditClassModal = function(classJson) {
        let classData;
        try {
            classData = typeof classJson === 'string' ? JSON.parse(classJson) : classJson;
        } catch(e) {
            console.error('Failed to parse class data', e, classJson);
            return;
        }

        const modal = document.getElementById('editClassDetailsModal');
        if (!modal) { console.error('Modal #editClassDetailsModal not found!'); return; }
        modal.classList.add('active');

        // Populate base fields
        document.getElementById('editClassId').value          = classData.id;
        document.getElementById('editClassName').value        = classData.name;
        document.getElementById('editClassCategory').value    = classData.category_id || '';
        document.getElementById('editClassDescription').value = classData.description || '';
        document.getElementById('editClassOutdoor').checked   = classData.type === 'outdoor';

        // Set difficulty then sync XP + Duration via Fair Play Engine
        document.getElementById('editClassDifficulty').value = classData.difficulty || 2;
        applyEditFairPlayClass();

        // Set Icon
        const iconValue = classData.badge_icon || 'noto:blue-book';
        document.getElementById('editClassIcon').value        = iconValue;
        document.getElementById('editClassIconText').textContent = iconValue;
        const preview = document.getElementById('editClassIconPreview');
        if (iconValue.startsWith('fa-')) {
            preview.innerHTML = `<i class="${iconValue}" style="font-size:1.5rem;color:var(--primary);"></i>`;
        } else if (iconValue.includes(':')) {
            const cleanIcon = iconValue.split(' ')[0];
            preview.innerHTML = `<iconify-icon icon="${cleanIcon}" style="font-size:1.5rem;"></iconify-icon>`;
        } else {
            preview.innerHTML = `<span style="font-size:1.5rem;">${iconValue}</span>`;
        }
    }

    window.closeEditClassModal = function() {
        document.getElementById('editClassDetailsModal').classList.remove('active');
        document.getElementById('editClassDetailsForm').reset();
    }

    // Close on click outside
    document.getElementById('editClassDetailsModal').addEventListener('click', function(e) {
        if (e.target === this) closeEditClassModal();
    });

    async function submitEditClassDetails(e) {
        e.preventDefault();

        const form      = document.getElementById('editClassDetailsForm');
        const formData  = new FormData(form);
        const classId = document.getElementById('editClassId').value;
        const btn       = form.querySelector('button[type="submit"]');
        const original  = btn.innerHTML;

        btn.disabled = true;
        btn.innerHTML = 'Salvando...';

        try {
            const response = await fetch(`/${tenantSlug}/admin/api/classes/${classId}/update`, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                alert(data.message || 'Classe atualizada com sucesso!');
                closeEditClassModal();
                setTimeout(() => location.reload(), 800);
            } else {
                alert(data.error || 'Erro ao atualizar classe');
                btn.disabled = false;
                btn.innerHTML = original;
            }
        } catch (error) {
            console.error('Erro:', error);
            alert('Erro de conexão ao atualizar classe');
            btn.disabled = false;
            btn.innerHTML = original;
        }
    }

    async function submitNewClass(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        const btn = e.target.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '⏳ Criando...';

        try {
            const resp = await fetch(`/${tenantSlug}/admin/programas`, {
                method: 'POST',
                body: formData
            });
            const data = await resp.json();
            if (data.success) {
                location.href = data.redirect || location.href;
            } else {
                alert(data.error || 'Erro ao criar');
                btn.disabled = false;
                btn.innerHTML = 'Criar Classe';
            }
        } catch (err) {
            console.error(err);
            btn.disabled = false;
            btn.innerHTML = 'Criar Classe';
        }
    }

    async function deleteClass(id, name) {
        if (!confirm(`Excluir "${name}"?`)) return;
        try {
            const resp = await fetch(`/${tenantSlug}/admin/programas/${id}/delete`, { method: 'POST' });
            const data = await resp.json();
            if (data.success) location.reload();
            else alert(data.error);
        } catch (err) { console.error(err); }
    }

    async function deleteCategoryWithPrograms(categoryId, categoryName, programCount) {
        const warningMsg = programCount > 0 
            ? `Excluir a categoria "${categoryName}" e suas ${programCount} classe(s)? Esta ação é permanente e removerá todo o progresso dos desbravadores nestas classes.`
            : `Excluir a categoria "${categoryName}"?`;
        
        const confirmed = await showConfirm({
            title: 'Excluir Categoria',
            message: warningMsg,
            icon: '⚠️',
            danger: true,
            okText: 'Excluir Tudo'
        });
        if (!confirmed) return;

        try {
            const resp = await fetch(`/${tenantSlug}/admin/categorias/${categoryId}/delete-cascade`, { method: 'POST' });
            const data = await resp.json();
            if (data.success) {
                location.reload();
            } else {
                alert(data.error || 'Erro ao excluir');
            }
        } catch (err) {
            console.error(err);
            alert('Erro de conexão');
        }
    }

    // Confirmation Modal System
    var confirmCallback = null;
    function showConfirm(options) {
        return new Promise((resolve) => {
            const { title, message, icon = '⚠️', danger = false, okText = 'Confirmar' } = options;
            let modal = document.getElementById('confirmModalOverlay');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'confirmModalOverlay';
                modal.style.cssText = 'display:none;position:fixed;inset:0;background:rgba(15, 23, 42, 0.4);backdrop-filter:blur(4px);z-index:3000;align-items:center;justify-content:center;transition:opacity 0.2s;';
                modal.innerHTML = `
                     <div style="background:#1e1e32;border:1px solid var(--border-color);border-radius:16px;max-width:400px;width:90%;padding:32px;text-align:center;box-shadow:0 20px 25px -5px rgba(0, 0, 0, 0.1);transform:scale(0.95);transition:transform 0.2s;">
                        <div id="cModalIcon" style="font-size:3.5rem;margin-bottom:20px;display:inline-block;filter:drop-shadow(0 4px 6px rgba(0,0,0,0.1));"></div>
                        <h3 id="cModalTitle" style="margin:0 0 12px;font-size:1.25rem;font-weight:700;color:var(--text-main);"></h3>
                        <p id="cModalMsg" style="color:var(--text-muted);margin:0 0 32px;font-size:0.95rem;line-height:1.6;"></p>
                        <div style="display:flex;gap:12px;justify-content:center;">
                            <button onclick="closeConfirmModal(false)" class="btn-toolbar secondary">Cancelar</button>
                            <button id="cModalOk" onclick="closeConfirmModal(true)" class="btn-toolbar" style="border:none;"></button>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);
            }
            setTimeout(() => { modal.style.opacity = '1'; modal.querySelector('div').style.transform = 'scale(1)'; }, 10);
            document.getElementById('cModalIcon').textContent = icon;
            document.getElementById('cModalTitle').textContent = title;
            document.getElementById('cModalMsg').textContent = message;
            const okBtn = document.getElementById('cModalOk');
            okBtn.textContent = okText;
            okBtn.className = danger ? 'btn-toolbar danger' : 'btn-toolbar primary';
            okBtn.style.background = danger ? '#ef4444' : '';
            okBtn.style.color = '#fff';
            confirmCallback = resolve;
            modal.style.display = 'flex';
        });
    }
    function closeConfirmModal(result) {
        document.getElementById('confirmModalOverlay').style.display = 'none';
        if (confirmCallback) { confirmCallback(result); confirmCallback = null; }
    }
</script>
<script>
    function openClassIconPicker() {
        const currentIcon = document.getElementById('classIcon').value;
        IconPicker.open(currentIcon, (selectedIcon) => {
            document.getElementById('classIcon').value = selectedIcon;
            document.getElementById('classIconPreview').innerHTML = `<iconify-icon icon="${selectedIcon}" style="font-size: 1.5rem;"></iconify-icon>`;
            document.getElementById('classIconText').textContent = selectedIcon;
        });
    }
</script>

<?php require_once BASE_PATH . '/views/admin/partials/icon_picker.php'; ?>