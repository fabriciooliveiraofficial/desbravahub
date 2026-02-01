<!-- Create Category Modal Partial -->
<div id="categoryModal" class="modal-overlay"
    style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;"
    onclick="closeCategoryModal(event)">
    <div class="modal-content"
        style="background: var(--bg-card); padding: 24px; border-radius: 12px; width: 100%; max-width: 600px; max-height: 90vh; overflow-y: auto;"
        onclick="event.stopPropagation()">
        <div class="modal-header"
            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h3 id="catModalTitle" style="margin: 0; display: flex; align-items: center; gap: 8px;">
                <span class="material-icons-round" style="color: var(--primary);">category</span>
                Nova Categoria
            </h3>
            <button onclick="closeCategoryModal()"
                style="background: none; border: none; cursor: pointer; color: var(--text-secondary);">
                <span class="material-icons-round">close</span>
            </button>
        </div>

        <form id="categoryForm" onsubmit="submitCategoryForm(event)">
            <input type="hidden" id="categoryId" name="id" value="">

            <div class="form-group">
                <label>Nome *</label>
                <input type="text" id="catName" name="name" class="form-control" required=""
                    placeholder="Ex: Atividades Missionárias"
                    style="width: 100%; padding: 10px 14px; background: var(--bg-dark); border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-main); font-size: 0.95rem;">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Tipo</label>
                    <select id="catType" name="type" class="form-control"
                        style="width: 100%; padding: 10px 14px; background: var(--bg-dark); border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-main); font-size: 0.95rem;">
                        <option value="specialty">🎯 Especialidades</option>
                        <option value="class">🎖️ Classes</option>
                        <option value="both">📚 Ambos</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Ícone</label>
                    <div class="input-wrapper">
                        <input type="hidden" id="catIcon" name="icon" value="📚">
                        <button type="button" class="icon-picker-trigger" onclick="openCategoryIconPicker()">
                            <div class="icon-picker-preview" id="catIconPreview">
                                <iconify-icon icon="noto:books" style="font-size: 1.5rem;"></iconify-icon>
                            </div>
                            <span class="icon-picker-text" id="catIconText">Selecionar ícone</span>
                            <i class="fa-solid fa-chevron-down icon-picker-arrow"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Cor</label>
                <input type="color" id="catColor" name="color" class="color-input" value="#00D9FF"
                    style="width: 100%; height: 40px; padding: 0; border: none; border-radius: 8px; cursor: pointer; background: var(--bg-dark);">
            </div>

            <div class="form-group">
                <label>Descrição</label>
                <textarea id="catDescription" name="description" class="form-control" rows="3"
                    placeholder="Descrição opcional..."
                    style="width: 100%; padding: 10px 14px; background: var(--bg-dark); border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-main); font-size: 0.95rem; resize: vertical; min-height: 100px;"></textarea>
            </div>

            <div class="form-footer"
                style="display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid var(--border-light); padding-top: 24px; margin-top: 24px;">
                <button type="button" class="btn-cancel" onclick="closeCategoryModal()"
                    style="padding: 10px 20px; border-radius: 8px; border: 1px solid var(--border-light); background: transparent; color: var(--text-primary); cursor: pointer;">Cancelar</button>
                <button type="submit" class="btn-submit" id="btnSaveCategory"
                    style="padding: 10px 20px; border-radius: 8px; border: none; background: linear-gradient(135deg, var(--primary), var(--primary-hover)); color: white; cursor: pointer; font-weight: 600;">💾
                    Salvar</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCategoryModal() {
        document.getElementById('catModalTitle').innerHTML = '<span class="material-icons-round" style="color: var(--primary);">category</span> Nova Categoria';
        document.getElementById('categoryId').value = '';
        document.getElementById('catName').value = '';
        document.getElementById('catType').value = 'specialty';
        
        // Default Icon
        document.getElementById('catIcon').value = 'noto:books';
        document.getElementById('catIconPreview').innerHTML = '<iconify-icon icon="noto:books" style="font-size: 1.5rem;"></iconify-icon>';
        document.getElementById('catIconText').textContent = 'noto:books';

        document.getElementById('catColor').value = '#00D9FF';
        document.getElementById('catDescription').value = '';

        const modal = document.getElementById('categoryModal');
        modal.style.display = 'flex';
        document.getElementById('catName').focus();
    }
    
    function openCategoryIconPicker() {
        const currentIcon = document.getElementById('catIcon').value;
        IconPicker.open(currentIcon, (selectedIcon) => {
            document.getElementById('catIcon').value = selectedIcon;
            document.getElementById('catIconPreview').innerHTML = `<iconify-icon icon="${selectedIcon}" style="font-size: 1.5rem;"></iconify-icon>`;
            document.getElementById('catIconText').textContent = selectedIcon;
        });
    }

    function closeCategoryModal(e) {
        if (e && e.target !== e.currentTarget) return;
        document.getElementById('categoryModal').style.display = 'none';
    }

    async function submitCategoryForm(e) {
        e.preventDefault();
        const form = new FormData(document.getElementById('categoryForm'));
        const btn = document.getElementById('btnSaveCategory');
        const originalText = btn.textContent;

        btn.disabled = true;
        btn.textContent = '⏳ Salvando...';

        const url = `/${tenantSlug}/admin/categorias`;

        try {
            const resp = await fetch(url, { method: 'POST', body: form });
            const data = await resp.json();

            if (data.success) {
                if (typeof showToast === 'function') {
                    showToast(data.message || 'Categoria criada com sucesso!');
                } else {
                    alert(data.message || 'Categoria criada com sucesso!');
                }

                setTimeout(() => location.reload(), 500);
            } else {
                if (typeof showToast === 'function') {
                    showToast(data.error || 'Erro ao salvar', 'error');
                } else {
                    alert(data.error || 'Erro ao salvar');
                }
            }
        } catch (err) {
            console.error(err);
            if (typeof showToast === 'function') {
                showToast('Erro de conexão', 'error');
            } else {
                alert('Erro de conexão');
            }
        } finally {
            btn.disabled = false;
            btn.textContent = originalText;
        }
    }
</script>

<?php require_once BASE_PATH . '/views/admin/partials/icon_picker.php'; ?>
