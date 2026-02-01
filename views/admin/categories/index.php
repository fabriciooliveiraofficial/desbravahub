<?php
/**
 * Admin: Learning Categories Management
 * 
 * CRUD interface for tenant-scoped categories.
 */
$pageTitle = 'Categorias';
$pageIcon = '📂';
?>
<style>
    /* ============ Premium Categories Page Styles ============ */

    /* ============ Page Header - Premium ============ */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 32px;
        padding: 24px 28px;
        background: linear-gradient(135deg, var(--bg-card) 0%, rgba(6, 182, 212, 0.03) 100%);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-card);
        flex-wrap: wrap;
        gap: 20px;
        animation: slideUp 0.4s ease-out;
    }

    .page-toolbar h2 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 12px;
        color: var(--text-dark);
    }

    .btn-add {
        padding: 14px 24px;
        background: var(--gradient-primary);
        border: none;
        border-radius: var(--radius-lg);
        color: white;
        font-weight: 600;
        cursor: pointer;
        font-size: 0.95rem;
        transition: var(--transition-bounce);
        box-shadow: 0 4px 14px rgba(6, 182, 212, 0.25);
        position: relative;
        overflow: hidden;
    }

    .btn-add::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(45deg, transparent, rgba(255,255,255,0.2), transparent);
        transform: translateX(-100%);
        transition: transform 0.5s;
    }

    .btn-add:hover::before {
        transform: translateX(100%);
    }

    .btn-add:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(6, 182, 212, 0.35);
    }

    /* ============ Categories Grid ============ */
    .categories-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 20px;
    }

    /* ============ Category Card - Premium ============ */
    .category-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-xl);
        padding: 24px;
        transition: var(--transition-bounce);
        position: relative;
        box-shadow: var(--shadow-card);
        animation: slideUp 0.5s ease-out backwards;
        overflow: hidden;
    }

    .category-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--gradient-primary);
        transform: scaleX(0);
        transition: transform 0.3s ease;
        transform-origin: left;
    }

    .category-card:nth-child(1) { animation-delay: 0.05s; }
    .category-card:nth-child(2) { animation-delay: 0.1s; }
    .category-card:nth-child(3) { animation-delay: 0.15s; }
    .category-card:nth-child(4) { animation-delay: 0.2s; }
    .category-card:nth-child(5) { animation-delay: 0.25s; }
    .category-card:nth-child(6) { animation-delay: 0.3s; }

    .category-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--shadow-card-hover);
        border-color: rgba(6, 182, 212, 0.3);
    }

    .category-card:hover::before {
        transform: scaleX(1);
    }

    .category-header {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 16px;
    }

    .category-icon {
        font-size: 2rem;
        width: 56px;
        height: 56px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: var(--radius-lg);
        transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.1));
    }

    .category-card:hover .category-icon {
        transform: scale(1.15) rotate(8deg);
    }

    .category-info {
        flex: 1;
    }

    .category-name {
        font-size: 1.125rem;
        font-weight: 700;
        margin: 0 0 6px;
        transition: color 0.2s;
        color: var(--text-dark);
    }

    .category-type {
        font-size: 0.85rem;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .category-desc {
        font-size: 0.9rem;
        color: var(--text-muted);
        margin: 0;
        line-height: 1.6;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .category-actions {
        display: flex;
        gap: 10px;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid var(--border-color);
    }

    .btn-edit,
    .btn-delete {
        flex: 1;
        padding: 10px 16px;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        background: var(--bg-dark);
        color: var(--text-main);
        cursor: pointer;
        font-size: 0.875rem;
        font-weight: 600;
        transition: var(--transition-bounce);
    }

    .btn-edit:hover {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }

    .btn-delete {
        color: var(--accent-red);
        border-color: rgba(239, 68, 68, 0.3);
    }

    .btn-delete:hover {
        background: var(--accent-red);
        color: white;
        border-color: var(--accent-red);
    }

    /* ============ Empty State - Premium ============ */
    .empty-state {
        text-align: center;
        padding: 80px 24px;
        background: var(--bg-card);
        border: 2px dashed var(--border-color);
        border-radius: var(--radius-xl);
        animation: fadeIn 0.5s ease-out;
    }

    .empty-state .icon {
        font-size: 5rem;
        margin-bottom: 24px;
        display: block;
    }

    .empty-state h3 {
        margin: 0 0 12px;
        font-size: 1.35rem;
        font-weight: 700;
        color: var(--text-main);
    }

    .empty-state p {
        color: var(--text-muted);
        margin-bottom: 28px;
        font-size: 1rem;
    }

    /* ============ Modal - Glassmorphism ============ */
    .modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.8);
        backdrop-filter: blur(8px);
        z-index: 1000;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .modal-overlay.active {
        display: flex;
        animation: fadeIn 0.2s ease-out;
    }

    .modal {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-2xl);
        max-width: 520px;
        width: 100%;
        overflow: hidden;
        box-shadow: var(--shadow-2xl);
        animation: scaleIn 0.3s ease-out;
    }

    .modal-header {
        padding: 24px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: linear-gradient(135deg, transparent, rgba(6, 182, 212, 0.03));
    }

    .modal-header h2 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-main);
    }

    .modal-close {
        background: var(--bg-dark);
        border: 1px solid var(--border-color);
        width: 36px;
        height: 36px;
        border-radius: var(--radius-md);
        font-size: 1.25rem;
        cursor: pointer;
        color: var(--text-muted);
        transition: var(--transition-bounce);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-close:hover {
        background: var(--accent-red);
        border-color: var(--accent-red);
        color: white;
        transform: rotate(90deg);
    }

    .modal-body {
        padding: 24px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        font-size: 0.9rem;
        color: var(--text-main);
    }

    .form-control {
        width: 100%;
        padding: 12px 16px;
        background: var(--bg-dark);
        border: 2px solid var(--border-color);
        border-radius: var(--radius-lg);
        color: var(--text-main);
        font-size: 0.95rem;
        transition: var(--transition-bounce);
    }

    .form-control:hover {
        border-color: rgba(6, 182, 212, 0.3);
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(6, 182, 212, 0.1);
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    .color-input {
        width: 60px;
        height: 46px;
        padding: 4px;
        border: 2px solid var(--border-color);
        border-radius: var(--radius-lg);
        cursor: pointer;
        transition: var(--transition-bounce);
    }

    .color-input:hover {
        border-color: var(--primary);
    }

    .modal-footer {
        padding: 20px 24px;
        border-top: 1px solid var(--border-color);
        display: flex;
        gap: 12px;
        justify-content: flex-end;
        background: linear-gradient(135deg, rgba(6, 182, 212, 0.02), transparent);
    }

    .btn-cancel {
        background: var(--bg-dark);
        border: 1px solid var(--border-color);
        color: var(--text-main);
        padding: 12px 24px;
        border-radius: var(--radius-lg);
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition-bounce);
    }

    .btn-cancel:hover {
        background: var(--item-hover);
    }

    .btn-save {
        background: var(--gradient-primary);
        color: white;
        padding: 12px 24px;
        border-radius: var(--radius-lg);
        border: none;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition-bounce);
        box-shadow: 0 4px 14px rgba(6, 182, 212, 0.25);
    }

    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(6, 182, 212, 0.35);
    }

    /* ============ Toast - Premium ============ */
    .toast {
        position: fixed;
        bottom: 24px;
        right: 24px;
        padding: 18px 28px;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-left: 4px solid var(--accent-emerald);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-xl);
        transform: translateX(150%);
        transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 1001;
        font-weight: 500;
        color: var(--text-main);
    }

    .toast.show {
        transform: translateX(0);
    }

    .toast.error {
        border-left-color: var(--accent-red);
    }

    /* ============ Confirm Modal - Premium ============ */
    .confirm-modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.8);
        backdrop-filter: blur(8px);
        z-index: 2000;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .confirm-modal-overlay.active {
        display: flex;
        animation: fadeIn 0.2s ease-out;
    }

    .confirm-modal {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-2xl);
        max-width: 420px;
        width: 100%;
        padding: 32px;
        text-align: center;
        box-shadow: var(--shadow-2xl);
        animation: scaleIn 0.3s ease-out;
    }

    .confirm-modal-icon {
        font-size: 4rem;
        margin-bottom: 20px;
        display: block;
    }

    .confirm-modal h3 {
        margin: 0 0 12px;
        font-size: 1.35rem;
        font-weight: 700;
        color: var(--text-main);
    }

    .confirm-modal p {
        color: var(--text-muted);
        margin: 0 0 28px;
        font-size: 1rem;
        line-height: 1.6;
    }

    .confirm-modal-actions {
        display: flex;
        gap: 12px;
        justify-content: center;
    }

    .confirm-modal-actions button {
        padding: 12px 28px;
        border-radius: var(--radius-lg);
        font-weight: 600;
        cursor: pointer;
        border: none;
        transition: var(--transition-bounce);
    }

    .btn-confirm-cancel {
        background: var(--bg-dark);
        border: 1px solid var(--border-color);
        color: var(--text-main);
    }

    .btn-confirm-cancel:hover {
        background: var(--item-hover);
    }

    .btn-confirm-ok {
        background: var(--gradient-primary);
        color: white;
        box-shadow: 0 4px 14px rgba(6, 182, 212, 0.25);
    }

    .btn-confirm-ok:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(6, 182, 212, 0.35);
    }

    .btn-confirm-danger {
        background: var(--gradient-danger);
        color: white;
        box-shadow: 0 4px 14px rgba(239, 68, 68, 0.25);
    }

    .btn-confirm-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(239, 68, 68, 0.35);
    }

    /* ============ Responsive ============ */
    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            padding: 20px;
        }

        .categories-grid {
            grid-template-columns: 1fr;
        }

        .form-row {
            grid-template-columns: 1fr;
        }

        .confirm-modal-actions {
            flex-direction: column;
        }
    }
    /* ============ Icon Picker ============ */
</style>

<!-- Page Header -->
<header class="page-toolbar">
    <div class="page-info">
        <h2 class="header-title">📂 Categorias</h2>
        <span style="font-size: 0.8rem; margin-left: 10px; opacity: 0.5;">
            Script Test: <iconify-icon icon="mdi:check-decagram" style="color: #00ff88; vertical-align: middle;"></iconify-icon>
        </span>
    </div>
    <div class="actions-group">
        <button class="btn-toolbar primary" onclick="openModal()">
            <span class="material-icons-round">add</span> Nova Categoria
        </button>
    </div>
</header>

<?php if (empty($categories)): ?>
    <div class="empty-state">
        <span class="icon">📂</span>
        <h3>Nenhuma categoria criada</h3>
        <p>Crie sua primeira categoria para organizar especialidades e classes.</p>
        <button class="btn-add" onclick="openModal()">
            ➕ Criar Categoria
        </button>
    </div>
<?php else: ?>
    <div class="categories-grid">
        <?php foreach ($categories as $cat): ?>
            <div class="category-card" data-id="<?= $cat['id'] ?>">
                <div class="category-header">
                    <div class="category-icon" style="background: <?= htmlspecialchars($cat['color']) ?>20;">
                        <?php if (str_contains($cat['icon'] ?? '', ':')): ?>
                            <iconify-icon icon="<?= htmlspecialchars($cat['icon']) ?>" style="color: <?= htmlspecialchars($cat['color']) ?>; font-size: 1.5rem;"></iconify-icon>
                        <?php elseif (str_starts_with($cat['icon'] ?? '', 'fa-')): ?>
                            <i class="<?= htmlspecialchars($cat['icon']) ?>" style="color: <?= htmlspecialchars($cat['color']) ?>;"></i>
                        <?php else: ?>
                            <span style="font-size: 1.5rem;"><?= $cat['icon'] ?: '📁' ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="category-info">
                        <h3 class="category-name" style="color: <?= htmlspecialchars($cat['color']) ?>;">
                            <?= htmlspecialchars($cat['name']) ?>
                        </h3>
                        <div class="category-type">
                            <?= match ($cat['type']) {
                                'specialty' => '🎯 Especialidades',
                                'class' => '🎖️ Classes',
                                'both' => '📚 Ambos'
                            } ?>
                        </div>
                    </div>
                </div>
                <?php if (!empty($cat['description'])): ?>
                    <p class="category-desc">
                        <?= htmlspecialchars(substr($cat['description'], 0, 100)) ?>...
                    </p>
                <?php endif; ?>
                <div class="category-actions">
                    <button class="btn-edit" onclick="editCategory(<?= htmlspecialchars(json_encode($cat)) ?>)">
                        ✏️ Editar
                    </button>
                    <button class="btn-delete"
                        onclick="deleteCategory(<?= $cat['id'] ?>, '<?= htmlspecialchars(addslashes($cat['name']), ENT_QUOTES) ?>')">
                        🗑️ Excluir
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

    <!-- Category Modal -->
    <div class="modal-overlay" id="categoryModal" onclick="closeModal(event)">
        <div class="modal" onclick="event.stopPropagation()">
            <div class="modal-header">
                <h2 id="modalTitle">➕ Nova Categoria</h2>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <form id="categoryForm" onsubmit="saveCategory(event)">
                <input type="hidden" id="categoryId" name="id" value="">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nome *</label>
                        <input type="text" id="catName" name="name" class="form-control" required
                            placeholder="Ex: Atividades Missionárias">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Tipo</label>
                            <select id="catType" name="type" class="form-control">
                                <option value="specialty">🎯 Especialidades</option>
                                <option value="class">🎖️ Classes</option>
                                <option value="both">📚 Ambos</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Ícone</label>
                            <input type="hidden" id="catIcon" name="icon" value="fa-solid fa-folder">
                            <button type="button" class="icon-picker-trigger" onclick="openCategoryIconPicker()">
                                <div class="icon-picker-preview" id="iconPreview">
                                    <i class="fa-solid fa-folder"></i>
                                </div>
                                <span class="icon-picker-text" id="iconText">fa-solid fa-folder</span>
                                <i class="fa-solid fa-chevron-down icon-picker-arrow"></i>
                            </button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Cor</label>
                        <input type="color" id="catColor" name="color" class="color-input" value="#00D9FF">
                    </div>
                    <div class="form-group">
                        <label>Descrição</label>
                        <textarea id="catDescription" name="description" class="form-control" rows="3"
                            placeholder="Descrição opcional..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-edit btn-cancel" onclick="closeModal()">Cancelar</button>
                    <button type="submit" class="btn-edit btn-save" id="btnSave">💾 Salvar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div class="confirm-modal-overlay" id="confirmModal" onclick="closeConfirmModal()">
        <div class="confirm-modal" onclick="event.stopPropagation()">
            <div class="confirm-modal-icon" id="confirmIcon">⚠️</div>
            <h3 id="confirmTitle">Confirmar</h3>
            <p id="confirmMessage">Tem certeza?</p>
            <div class="confirm-modal-actions">
                <button type="button" class="btn-confirm-cancel" onclick="closeConfirmModal()">Cancelar</button>
                <button type="button" class="btn-confirm-ok" id="confirmOkBtn"
                    onclick="confirmAction()">Confirmar</button>
            </div>
        </div>
    </div>

    <div class="toast" id="toast"></div>

    <script>
        var tenantSlug = tenantSlug || '<?= $tenant['slug'] ?>';
        let editingId = null;

        function openModal(data = null) {
            editingId = null;
            document.getElementById('modalTitle').textContent = '➕ Nova Categoria';
            document.getElementById('categoryId').value = '';
            document.getElementById('catName').value = '';
            document.getElementById('catType').value = 'specialty';
            document.getElementById('catIcon').value = '📚';
            document.getElementById('catColor').value = '#00D9FF';
            document.getElementById('catDescription').value = '';
            document.getElementById('categoryModal').classList.add('active');
        }

        function editCategory(cat) {
            editingId = cat.id;
            document.getElementById('modalTitle').textContent = '✏️ Editar Categoria';
            document.getElementById('categoryId').value = cat.id;
            document.getElementById('catName').value = cat.name;
            document.getElementById('catType').value = cat.type;
            document.getElementById('catIcon').value = cat.icon;
            document.getElementById('catColor').value = cat.color;
            document.getElementById('catDescription').value = cat.description || '';
            document.getElementById('categoryModal').classList.add('active');
        }

        function closeModal(e) {
            if (e && e.target !== e.currentTarget) return;
            document.getElementById('categoryModal').classList.remove('active');
        }

        async function saveCategory(e) {
            e.preventDefault();
            const form = new FormData(document.getElementById('categoryForm'));
            const btn = document.getElementById('btnSave');
            btn.disabled = true;
            btn.textContent = '⏳ Salvando...';

            const url = editingId
                ? `/${tenantSlug}/admin/categorias/${editingId}`
                : `/${tenantSlug}/admin/categorias`;

            try {
                const resp = await fetch(url, { method: 'POST', body: form });
                const data = await resp.json();

                if (data.success) {
                    showToast(data.message);
                    setTimeout(() => location.reload(), 500);
                } else {
                    showToast(data.error || 'Erro ao salvar', 'error');
                }
            } catch (err) {
                showToast('Erro de conexão', 'error');
            } finally {
                btn.disabled = false;
                btn.textContent = '💾 Salvar';
            }
        }

        async function deleteCategory(id, name) {
            const confirmed = await showConfirm({
                title: 'Excluir Categoria',
                message: `Excluir a categoria "${name}"? Esta ação não pode ser desfeita.`,
                icon: '🗑️',
                danger: true,
                okText: 'Excluir'
            });
            if (!confirmed) return;

            try {
                const resp = await fetch(`/${tenantSlug}/admin/categorias/${id}/delete`, { method: 'POST' });
                const data = await resp.json();

                if (data.success) {
                    showToast(data.message);
                    setTimeout(() => location.reload(), 500);
                } else {
                    showToast(data.error || 'Erro ao excluir', 'error');
                }
            } catch (err) {
                showToast('Erro de conexão', 'error');
            }
        }

        function showToast(msg, type = 'success') {
            const toast = document.getElementById('toast');
            toast.textContent = msg;
            toast.className = 'toast ' + type + ' show';
            setTimeout(() => toast.classList.remove('show'), 3000);
        }

        // Confirmation Modal System
        window.confirmCallback = null;

        function showConfirm(options) {
            return new Promise((resolve) => {
                const { title, message, icon = '⚠️', danger = false, okText = 'Confirmar' } = options;

                document.getElementById('confirmIcon').textContent = icon;
                document.getElementById('confirmTitle').textContent = title;
                document.getElementById('confirmMessage').textContent = message;

                const okBtn = document.getElementById('confirmOkBtn');
                okBtn.textContent = okText;
                okBtn.className = danger ? 'btn-confirm-danger' : 'btn-confirm-ok';

                confirmCallback = resolve;
                document.getElementById('confirmModal').classList.add('active');
            });
        }

        function closeConfirmModal() {
            document.getElementById('confirmModal').classList.remove('active');
            if (confirmCallback) {
                confirmCallback(false);
                confirmCallback = null;
            }
        }

        function confirmAction() {
            document.getElementById('confirmModal').classList.remove('active');
            if (confirmCallback) {
                confirmCallback(true);
                confirmCallback = null;
            }
        }

        function openCategoryIconPicker() {
            const currentIcon = document.getElementById('catIcon').value;
            IconPicker.open(currentIcon, (selectedIcon) => {
                document.getElementById('catIcon').value = selectedIcon;
                
                // Handle preview based on icon type
                const previewEl = document.getElementById('iconPreview');
                const textEl = document.getElementById('iconText');
                
                if(selectedIcon.startsWith('fa-')) {
                   previewEl.innerHTML = `<i class="${selectedIcon}"></i>`;
                } else if(selectedIcon.includes(':')) {
                   previewEl.innerHTML = `<iconify-icon icon="${selectedIcon}" style="font-size:1.5rem"></iconify-icon>`;
                } else {
                   previewEl.textContent = selectedIcon;
                }
                textEl.textContent = selectedIcon;
            });
        }
        
        // Overwrite editCategory to handle preview
        const originalEditCategory = editCategory;
        editCategory = function(cat) {
            originalEditCategory(cat);
            if(cat.icon && cat.icon.includes(':') && !cat.icon.startsWith('fa-')) {
                 document.getElementById('iconPreview').innerHTML = `<iconify-icon icon="${cat.icon}" style="font-size:1.5rem"></iconify-icon>`;
            }
        };
    </script>
<?php require_once BASE_PATH . '/views/admin/partials/icon_picker.php'; ?>