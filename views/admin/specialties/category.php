<?php
/**
 * Admin: Browse Specialties by Category
 */
$pageTitle = $category['name'] . ' - Especialidades';
?>
<style>
    /* ============ Mobile Top Padding ============ */
    @media (max-width: 768px) {
        .admin-main {
            padding-top: 70px;
        }
    }

    /* ============ Breadcrumb ============ */
    .breadcrumb {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 20px;
        font-size: 0.9rem;
    }

    .breadcrumb a {
        color: var(--text-secondary);
        text-decoration: none;
    }

    .breadcrumb a:hover {
        color: var(--accent-cyan);
    }

    .breadcrumb-separator {
        color: var(--text-secondary);
        opacity: 0.5;
    }

    .breadcrumb-current {
        color: var(--text-primary);
        font-weight: 500;
    }

    /* ============ Category Header ============ */
    .category-header {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 20px 24px;
        border-radius: 12px;
        margin-bottom: 24px;
    }

    .category-icon {
        font-size: 2.5rem;
    }

    .category-info h1 {
        margin: 0 0 4px 0;
        font-size: 1.5rem;
        color: #fff;
    }

    .category-info p {
        margin: 0;
        font-size: 0.9rem;
        opacity: 0.9;
        color: rgba(255, 255, 255, 0.8);
    }

    .category-count {
        margin-left: auto;
        padding: 8px 16px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 20px;
        font-weight: 600;
        color: #fff;
    }

    /* ============ Specialty Grid ============ */
    .specialties-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
    }

    /* ============ Specialty Card ============ */
    .specialty-card {
        background: var(--bg-card);
        border: 1px solid var(--border-light);
        border-radius: 12px;
        padding: 20px;
        display: flex;
        flex-direction: column;
        transition: all 0.2s ease;
        cursor: pointer;
        position: relative;
        z-index: 1;
    }

    .specialty-card:hover {
        border-color: var(--accent-cyan);
        transform: translateY(-2px);
        box-shadow: 0 4px 20px rgba(0, 217, 255, 0.1);
    }

    .specialty-header {
        display: flex;
        gap: 14px;
        margin-bottom: 12px;
    }

    .specialty-badge {
        font-size: 2.5rem;
        flex-shrink: 0;
        line-height: 1;
    }

    .specialty-title {
        flex: 1;
        min-width: 0;
    }

    .specialty-title h3 {
        margin: 0 0 8px 0;
        font-size: 1.1rem;
        font-weight: 600;
        line-height: 1.3;
        color: var(--text-primary);
    }

    .specialty-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .meta-tag {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        background: rgba(0, 0, 0, 0.3);
        border-radius: 6px;
        font-size: 0.75rem;
        color: var(--text-secondary);
    }

    .difficulty-stars {
        color: #ffc107;
    }

    .specialty-desc {
        flex: 1;
        color: var(--text-secondary);
        font-size: 0.9rem;
        line-height: 1.5;
        margin-bottom: 16px;
    }

    .specialty-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding-top: 16px;
        border-top: 1px solid var(--border-light);
        flex-wrap: wrap;
    }

    .xp-reward {
        display: flex;
        align-items: center;
        gap: 6px;
        color: var(--accent-green);
        font-weight: 700;
        font-size: 1rem;
    }

    .card-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .btn-card {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        border: 1px solid var(--border-light);
        border-radius: 8px;
        font-weight: 500;
        font-size: 0.85rem;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s;
        background: transparent;
        color: var(--text-primary);
    }

    .btn-card:hover {
        background: rgba(255, 255, 255, 0.05);
    }

    .btn-card-assign {
        background: linear-gradient(135deg, var(--accent-cyan), var(--accent-green));
        border-color: transparent;
        color: #0a0a14;
        font-weight: 600;
    }

    .btn-card-assign:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 217, 255, 0.3);
    }

    .btn-card-danger {
        background: rgba(244, 67, 54, 0.15);
        color: #f44336;
        border-color: rgba(244, 67, 54, 0.3);
    }

    .btn-card-danger:hover {
        background: rgba(244, 67, 54, 0.25);
        border-color: #f44336;
    }

    .btn-assign {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: linear-gradient(135deg, var(--accent-cyan), var(--accent-green));
        border: none;
        border-radius: 8px;
        color: #0a0a14;
        font-weight: 600;
        font-size: 0.9rem;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-assign:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 217, 255, 0.3);
    }

    /* Empty state */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: var(--text-secondary);
    }

    .empty-state h3 {
        margin: 16px 0 8px;
        color: var(--text-primary);
    }

    /* ============ Responsive ============ */
    @media (max-width: 768px) {
        .admin-main {
            padding: 16px;
        }

        .category-header {
            flex-wrap: wrap;
            padding: 16px;
        }

        .category-icon {
            font-size: 2rem;
        }

        .category-info h1 {
            font-size: 1.25rem;
        }

        .category-count {
            margin-left: 0;
            margin-top: 8px;
        }

        .specialties-grid {
            grid-template-columns: 1fr;
            gap: 16px;
        }

        .specialty-card {
            padding: 16px;
        }

        .specialty-badge {
            font-size: 2rem;
        }
    }

    /* ============ Specialty Modal ============ */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.7);
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .modal-overlay.open {
        display: flex !important;
    }

    .specialty-modal {
        background: #1e1e32;
        border: 1px solid var(--border-light);
        border-radius: 16px;
        max-width: 700px;
        width: 100%;
        max-height: 90vh;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .modal-header-specialty {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 24px;
        border-bottom: 1px solid var(--border-light);
    }

    .modal-header-specialty .badge {
        font-size: 3rem;
    }

    .modal-header-specialty .info h2 {
        margin: 0 0 8px 0;
        font-size: 1.4rem;
        color: var(--text-primary);
    }

    .modal-header-specialty .info .level {
        display: inline-block;
        padding: 4px 12px;
        background: var(--accent-cyan);
        color: var(--bg-dark);
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .modal-close-btn {
        position: absolute;
        top: 16px;
        right: 16px;
        background: none;
        border: none;
        color: var(--text-secondary);
        font-size: 1.5rem;
        cursor: pointer;
    }

    .modal-body-specialty {
        flex: 1;
        overflow-y: auto;
        padding: 24px;
    }

    .requirements-section h3 {
        margin: 0 0 16px 0;
        font-size: 1.1rem;
        color: var(--text-primary);
    }

    .requirements-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .requirement-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 12px;
        background: rgba(0, 0, 0, 0.2);
        border-radius: 8px;
        margin-bottom: 10px;
    }

    .requirement-item .order {
        width: 28px;
        height: 28px;
        background: var(--accent-cyan);
        color: var(--bg-dark);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.85rem;
        flex-shrink: 0;
    }

    .requirement-item .text {
        flex: 1;
        color: var(--text-primary);
        font-size: 0.95rem;
    }

    .empty-requirements {
        text-align: center;
        padding: 40px;
        color: var(--text-secondary);
    }

    .empty-requirements .icon {
        font-size: 2.5rem;
        margin-bottom: 12px;
    }

    .modal-footer-specialty {
        display: flex;
        gap: 12px;
        justify-content: flex-end;
        padding: 16px 24px;
        border-top: 1px solid var(--border-light);
    }

    .btn-edit-specialty {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        background: linear-gradient(135deg, var(--accent-cyan), var(--accent-green));
        border: none;
        border-radius: 8px;
        color: var(--bg-dark);
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
    }

    .btn-close-modal {
        padding: 12px 24px;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid var(--border-light);
        border-radius: 8px;
        color: var(--text-primary);
        cursor: pointer;
    }
</style>

    <!-- Content -->
    <nav class="breadcrumb">
        <a href="<?= base_url($tenant['slug'] . '/admin/especialidades') ?>">📚 Catálogo</a>
        <span class="breadcrumb-separator">›</span>
        <span class="breadcrumb-current"><?= $category['icon'] ?> <?= htmlspecialchars($category['name']) ?></span>
    </nav>

    <div class="category-header" style="background: <?= htmlspecialchars($category['color']) ?>;">
        <span class="category-icon"><?= $category['icon'] ?></span>
        <div class="category-info">
            <h1><?= htmlspecialchars($category['name']) ?></h1>
            <p><?= htmlspecialchars($category['description'] ?? '') ?></p>
        </div>
        <span class="category-count"><?= count($specialties) ?> especialidades</span>
    </div>

    <!-- Search Filter -->
    <div class="filter-section">
        <div class="search-box">
            <span class="search-icon">🔍</span>
            <input type="text" id="specialtySearch" placeholder="Buscar especialidade..."
                oninput="filterSpecialties(this.value)">
            <span class="search-count" id="searchCount"><?= count($specialties) ?> resultados</span>
        </div>
    </div>

    <style>
        .filter-section {
            margin-bottom: 24px;
        }

        .search-box {
            display: flex;
            align-items: center;
            gap: 12px;
            background: var(--bg-card);
            border: 1px solid var(--border-light);
            border-radius: 12px;
            padding: 12px 20px;
            transition: border-color 0.2s;
        }

        .search-box:focus-within {
            border-color: var(--accent-cyan);
        }

        .search-icon {
            font-size: 1.2rem;
        }

        .search-box input {
            flex: 1;
            background: transparent;
            border: none;
            outline: none;
            color: var(--text-primary);
            font-size: 1rem;
        }

        .search-box input::placeholder {
            color: var(--text-secondary);
        }

        .search-count {
            color: var(--text-secondary);
            font-size: 0.85rem;
            white-space: nowrap;
        }

        .specialty-card.hidden {
            display: none;
        }

        .no-results {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-secondary);
            display: none;
        }

        .no-results.visible {
            display: block;
        }
    </style>

    <?php if (empty($specialties)): ?>
        <div class="empty-state">
            <div style="font-size: 3rem;">📭</div>
            <h3>Nenhuma especialidade nesta categoria</h3>
            <p>Adicione especialidades a esta categoria para vê-las aqui.</p>
        </div>
    <?php else: ?>
        <div class="specialties-grid">
            <?php foreach ($specialties as $spec): ?>
                <div class="specialty-card" data-name="<?= strtolower(htmlspecialchars($spec['name'])) ?>"
                    data-spec='<?= htmlspecialchars(json_encode($spec, JSON_UNESCAPED_UNICODE), ENT_QUOTES, "UTF-8") ?>'
                    onclick="openSpecialtyModal(this)">
                    <div class="specialty-header">
                        <span class="specialty-badge"><?= $spec['badge_icon'] ?? '📚' ?></span>
                        <div class="specialty-title">
                            <h3><?= htmlspecialchars($spec['name']) ?></h3>
                            <div class="specialty-meta">
                                <span class="meta-tag">
                                    <?= ($spec['type'] ?? 'indoor') === 'outdoor' ? '🏕️ Externo' : '🏠 Interno' ?>
                                </span>
                                <span class="meta-tag">
                                    ⏱️ <?= $spec['duration_hours'] ?? 4 ?>h
                                </span>
                                <span class="meta-tag difficulty-stars">
                                    <?= str_repeat('⭐', $spec['difficulty'] ?? 1) ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <p class="specialty-desc"><?= htmlspecialchars($spec['description'] ?? 'Sem descrição disponível.') ?>
                    </p>

                    <div class="specialty-footer">
                        <span class="xp-reward">🌟 <?= $spec['xp_reward'] ?? 100 ?> XP</span>
                        <div class="card-actions">
                            <a href="<?= base_url($tenant['slug'] . '/admin/especialidades/' . $spec['id'] . '/requisitos') ?>"
                                class="btn-card" onclick="event.stopPropagation();">
                                ✏️ Editar
                            </a>
                            <a href="<?= base_url($tenant['slug'] . '/admin/especialidades/' . $spec['id'] . '/atribuir') ?>"
                                class="btn-card btn-card-assign" onclick="event.stopPropagation();">
                                👥 Atribuir
                            </a>
                            <button type="button" class="btn-card btn-card-danger"
                                onclick="event.stopPropagation(); deleteSpecialty('<?= $spec['id'] ?>', '<?= htmlspecialchars(addslashes($spec['name'])) ?>');">
                                🗑️
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="no-results" id="noResults">
            <div style="font-size: 2rem;">🔍</div>
            <p>Nenhuma especialidade encontrada com esse termo.</p>
        </div>
    <?php endif; ?>
    <!-- End Content -->

    <!-- Specialty Detail Modal -->
    <div class="modal-overlay" id="specialtyModal" onclick="closeModalIfOverlay(event)">
        <div class="specialty-modal" style="position: relative;">
            <button class="modal-close-btn" onclick="closeSpecialtyModal()">✕</button>
            <div class="modal-header-specialty">
                <span class="badge" id="modalBadge">📚</span>
                <div class="info">
                    <h2 id="modalName">Nome da Especialidade</h2>
                    <span class="level" id="modalLevel">Básico</span>
                </div>
            </div>
            <div class="modal-body-specialty">
                <p id="modalDesc" style="color: var(--text-secondary); margin-bottom: 20px;">Descrição da especialidade.
                </p>
                <div class="requirements-section">
                    <h3>📋 Requisitos / Tarefas</h3>
                    <ul class="requirements-list" id="modalRequirements">
                        <!-- Filled by JS -->
                    </ul>
                    <div class="empty-requirements" id="emptyRequirements" style="display: none;">
                        <div class="icon">📝</div>
                        <p>Nenhum requisito cadastrado ainda.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer-specialty">
                <button class="btn-close-modal" onclick="closeSpecialtyModal()">Fechar</button>
                <a href="#" id="modalAssignBtn" class="btn-edit-specialty">👥 Atribuir Especialidade</a>
            </div>
        </div>
    </div>

    <script>
        window.tenantSlug = '<?= $tenant['slug'] ?>';
        window.currentSpecId = null;

        function openSpecialtyModal(cardElement) {
            console.log('openSpecialtyModal called', cardElement);
            try {
                const specData = JSON.parse(cardElement.getAttribute('data-spec'));
                console.log('specData:', specData);
                currentSpecId = specData.id;

                // Populate modal
                document.getElementById('modalBadge').textContent = specData.badge_icon || '📚';
                document.getElementById('modalName').textContent = specData.name;
                document.getElementById('modalLevel').textContent = specData.level || 'basic';
                document.getElementById('modalDesc').textContent = specData.description || 'Sem descrição';

                // Build requirements list
                const reqList = document.getElementById('modalRequirements');
                const emptyReq = document.getElementById('emptyRequirements');
                reqList.innerHTML = '';

                if (specData.requirements && specData.requirements.length > 0) {
                    emptyReq.style.display = 'none';
                    reqList.style.display = 'block';
                    specData.requirements.forEach((req, idx) => {
                        const li = document.createElement('li');
                        li.className = 'requirement-item';
                        li.innerHTML = `
                        <span class="order">${req.order || idx + 1}</span>
                        <span class="text">${req.description}</span>
                    `;
                        reqList.appendChild(li);
                    });
                } else {
                    emptyReq.style.display = 'block';
                    reqList.style.display = 'none';
                }

                // Set assign link
                document.getElementById('modalAssignBtn').href = '<?= base_url($tenant["slug"] . "/admin/especialidades/") ?>' + specData.id + '/atribuir';

                // Open modal - Force inline styles for visibility
                const modalEl = document.getElementById('specialtyModal');
                console.log('Modal element found:', modalEl);
                console.log('Adding open class and forcing styles...');
                modalEl.classList.add('open');

                // Force inline styles to guarantee visibility
                modalEl.style.display = 'flex';
                modalEl.style.position = 'fixed';
                modalEl.style.top = '0';
                modalEl.style.left = '0';
                modalEl.style.right = '0';
                modalEl.style.bottom = '0';
                modalEl.style.width = '100vw';
                modalEl.style.height = '100vh';
                modalEl.style.zIndex = '99999';
                modalEl.style.backgroundColor = 'rgba(0, 0, 0, 0.85)';
                modalEl.style.alignItems = 'center';
                modalEl.style.justifyContent = 'center';
                modalEl.style.opacity = '1';
                modalEl.style.visibility = 'visible';

                // Style the inner modal with proper dark theme
                const innerModal = modalEl.querySelector('.specialty-modal');
                if (innerModal) {
                    innerModal.style.backgroundColor = '#1e1e32';
                    innerModal.style.color = '#ffffff';
                    innerModal.style.borderRadius = '16px';
                    innerModal.style.maxWidth = '700px';
                    innerModal.style.width = '90%';
                    innerModal.style.maxHeight = '90vh';
                    innerModal.style.overflow = 'auto';
                }

                console.log('Modal classes after add:', modalEl.className);
                console.log('Modal display style:', window.getComputedStyle(modalEl).display);
                console.log('Modal z-index:', window.getComputedStyle(modalEl).zIndex);
            } catch (error) {
                console.error('Error opening modal:', error);
                showToast('Erro ao abrir modal: ' + error.message, 'error');
            }
        }

        function closeSpecialtyModal() {
            const modalEl = document.getElementById('specialtyModal');
            modalEl.classList.remove('open');
            // Reset inline styles
            modalEl.style.display = '';
            modalEl.style.position = '';
            modalEl.style.top = '';
            modalEl.style.left = '';
            modalEl.style.right = '';
            modalEl.style.bottom = '';
            modalEl.style.width = '';
            modalEl.style.height = '';
            modalEl.style.zIndex = '';
            modalEl.style.backgroundColor = '';
            modalEl.style.alignItems = '';
            modalEl.style.justifyContent = '';
            modalEl.style.opacity = '';
            modalEl.style.visibility = '';
            currentSpecId = null;
        }

        function closeModalIfOverlay(event) {
            if (event.target.id === 'specialtyModal') {
                closeSpecialtyModal();
            }
        }

        // Filter specialties
        function filterSpecialties(query) {
            const searchTerm = query.toLowerCase().trim();
            const cards = document.querySelectorAll('.specialty-card');
            const noResults = document.getElementById('noResults');
            const searchCount = document.getElementById('searchCount');
            let visibleCount = 0;

            cards.forEach(card => {
                const name = card.getAttribute('data-name') || '';
                const matches = name.includes(searchTerm);

                if (matches) {
                    card.classList.remove('hidden');
                    visibleCount++;
                } else {
                    card.classList.add('hidden');
                }
            });

            if (searchCount) searchCount.textContent = visibleCount + ' resultado' + (visibleCount !== 1 ? 's' : '');
            if (noResults) noResults.classList.toggle('visible', visibleCount === 0 && searchTerm !== '');
        }

        // Delete specialty function
        async function deleteSpecialty(specId, specName) {
            const confirmed = await showConfirm({
                title: 'Excluir Especialidade',
                message: `Excluir "${specName}"? Esta ação não pode ser desfeita.`,
                icon: '🗑️',
                danger: true,
                okText: 'Excluir'
            });
            if (!confirmed) return;

            try {
                const resp = await fetch(`/${window.tenantSlug}/api/specialties/${specId}/delete`, { method: 'POST' });
                const data = await resp.json();
                if (data.success) {
                    showToast(data.message || 'Especialidade excluída!');
                    setTimeout(() => location.reload(), 500);
                } else {
                    showToast(data.error || 'Erro ao excluir', 'error');
                }
            } catch (err) {
                showToast('Erro de conexão', 'error');
            }
        }

        // Confirmation Modal System
        window.confirmCallback = null;

        function showConfirm(options) {
            return new Promise((resolve) => {
                const { title, message, icon = '⚠️', danger = false, okText = 'Confirmar' } = options;

                let modal = document.getElementById('confirmModalOverlay');
                if (!modal) {
                    modal = document.createElement('div');
                    modal.id = 'confirmModalOverlay';
                    modal.style.cssText = 'display:none;position:fixed;inset:0;background:rgba(0,0,0,0.85);z-index:3000;align-items:center;justify-content:center;';
                    modal.innerHTML = `
                        <div style="background:#1e1e32;border:1px solid rgba(255,255,255,0.1);border-radius:16px;max-width:400px;width:90%;padding:24px;text-align:center;">
                            <div id="cModalIcon" style="font-size:3rem;margin-bottom:16px;"></div>
                            <h3 id="cModalTitle" style="margin:0 0 8px;font-size:1.2rem;color:#fff;"></h3>
                            <p id="cModalMsg" style="color:#9ca3af;margin:0 0 24px;"></p>
                            <div style="display:flex;gap:12px;justify-content:center;">
                                <button onclick="closeConfirmModal(false)" style="padding:10px 24px;border-radius:8px;font-weight:600;cursor:pointer;border:none;background:rgba(255,255,255,0.1);color:#fff;">Cancelar</button>
                                <button id="cModalOk" onclick="closeConfirmModal(true)" style="padding:10px 24px;border-radius:8px;font-weight:600;cursor:pointer;border:none;"></button>
                            </div>
                        </div>
                    `;
                    document.body.appendChild(modal);
                }

                document.getElementById('cModalIcon').textContent = icon;
                document.getElementById('cModalTitle').textContent = title;
                document.getElementById('cModalMsg').textContent = message;
                const okBtn = document.getElementById('cModalOk');
                okBtn.textContent = okText;
                okBtn.style.background = danger ? '#f44336' : 'linear-gradient(135deg,#00D9FF,#00ff88)';
                okBtn.style.color = danger ? '#fff' : '#000';

                window.confirmCallback = resolve;
                modal.style.display = 'flex';
            });
        }

        function closeConfirmModal(result) {
            document.getElementById('confirmModalOverlay').style.display = 'none';
            if (window.confirmCallback) {
                window.confirmCallback(result);
                window.confirmCallback = null;
            }
        }
    </script>