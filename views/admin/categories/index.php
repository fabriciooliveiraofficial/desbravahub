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
    /* Category Card Actions */
    .category-actions {
        display: flex;
        gap: 8px;
        margin-top: 16px;
        width: 100%;
    }

    .btn-edit,
    .btn-delete {
        flex: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
    }

    /* Edit Button - Blue/Cyan */
    .btn-edit {
        background: rgba(6, 182, 212, 0.1);
        color: #0891b2;
        border: 1px solid rgba(6, 182, 212, 0.2);
    }

    .btn-edit:hover {
        background: rgba(6, 182, 212, 0.2);
        transform: translateY(-2px);
    }

    /* Delete Button - Red/Danger */
    .btn-delete {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
        border: 1px solid rgba(239, 68, 68, 0.2);
    }

    .btn-delete:hover {
        background: rgba(239, 68, 68, 0.2);
        transform: translateY(-2px);
    }
</style>

<!-- Page Header -->
<header class="page-toolbar">
    <div class="page-info">
        <h2 class="header-title">📂 Categorias</h2>
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
        <div class="modal" onclick="event.stopPropagation()" style="background: var(--bg-card);">
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



    <script>
        var tenantSlug = tenantSlug || '<?= $tenant['slug'] ?>';
        var editingId = editingId || null;

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
                    showToast(data.message, 'success');
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
                    showToast(data.message, 'success');
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