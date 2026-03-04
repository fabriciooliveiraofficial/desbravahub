<!-- Edit Program Details Modal Partial -->
<div id="editProgramDetailsModal" class="modal-overlay"
    style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div class="modal-content"
        style="background: var(--bg-card); padding: 24px; border-radius: 12px; width: 100%; max-width: 600px; max-height: 90vh; overflow-y: auto;">
        <div class="modal-header"
            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h3 style="margin: 0; display: flex; align-items: center; gap: 8px;">
                <span class="material-icons-round" style="color: var(--primary);">settings</span>
                Editar Detalhes do Programa
            </h3>
            <button onclick="closeEditProgramModal()"
                style="background: none; border: none; cursor: pointer; color: var(--text-secondary);">
                <span class="material-icons-round">close</span>
            </button>
        </div>

        <form id="editProgramDetailsForm" onsubmit="submitEditProgramDetails(event)">
            <input type="hidden" name="program_id" id="editProgramId">
            <div class="form-row">
                <div class="form-group" style="position: relative;">
                    <label>Nome *</label>
                    <input type="text" name="name" class="form-control" required="" placeholder="Ex: Primeiros Socorros"
                        id="editProgramName" autocomplete="off">
                </div>
                <div class="form-group">
                    <label>Categoria</label>
                    <select name="category_id" id="editProgramCategory" class="form-control">
                        <option value="">Sem categoria</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>">
                                <?php if (str_starts_with($cat['icon'] ?? '', 'fa-')): ?>
                                    (📂)
                                <?php elseif (str_contains($cat['icon'] ?? '', ':')): ?>
                                    (📂)
                                <?php else: ?>
                                    <?= $cat['icon'] ?> 
                                <?php endif; ?>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                 <div class="form-group">
                    <label>Ícone do Programa</label>
                    <input type="hidden" id="editProgramIcon" name="icon" value="noto:blue-book">
                    <div class="icon-picker-trigger" onclick="IconPicker.open('editProgramIcon', 'editProgramIconPreview', 'editProgramIconText')" style="display: flex; align-items: center; gap: 12px; padding: 10px; background: var(--bg-input); border: 1px solid var(--border-light); border-radius: 8px; cursor: pointer; height: 42px;">
                        <div id="editProgramIconPreview">
                            <iconify-icon icon="noto:blue-book" style="font-size: 1.5rem;"></iconify-icon>
                        </div>
                        <div class="icon-info" style="flex: 1;">
                            <span id="editProgramIconText" style="font-size: 0.85rem; color: var(--text-primary);">noto:blue-book</span>
                        </div>
                    </div>
                </div>
                 <div class="form-group">
                    <label>Duração (horas)</label>
                    <input type="number" name="duration_hours" id="editProgramDuration" class="form-control" value="4" min="1" max="200">
                </div>
            </div>

            <div class="form-group">
                <label>Descrição</label>
                <textarea name="description" id="editProgramDescription" class="form-control" rows="3"
                    placeholder="Descrição do programa..."></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Dificuldade (1-5)</label>
                    <select name="difficulty" id="editProgramDifficulty" class="form-control">
                        <option value="1">⭐ Iniciante</option>
                        <option value="2">⭐⭐ Básico</option>
                        <option value="3" selected="">⭐⭐⭐ Intermediário</option>
                        <option value="4">⭐⭐⭐⭐ Avançado</option>
                        <option value="5">⭐⭐⭐⭐⭐ Expert</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Recompensa XP</label>
                    <input type="number" name="xp_reward" id="editProgramXp" class="form-control" value="100" min="0" step="10">
                </div>
            </div>

            <div class="form-group">
                <label class="form-check" style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                    <input type="checkbox" name="is_outdoor" id="editProgramOutdoor" value="1" style="width: 20px; height: 20px;">
                    <span>🏕️ Programa Outdoor (prático, sem perguntas interativas)</span>
                </label>
                <small style="margin-left: 30px; display: block; color: var(--text-secondary);">Programas outdoor
                    exigem envio de provas para aprovação manual.</small>
            </div>

            <div class="form-footer"
                style="display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid var(--border-light); padding-top: 24px; margin-top: 24px;">
                <button type="button" class="btn-cancel" onclick="closeEditProgramModal()"
                    style="padding: 10px 20px; border-radius: 8px; border: 1px solid var(--border-light); background: transparent; color: var(--text-primary); cursor: pointer;">Cancelar</button>
                <button type="submit" class="btn-submit"
                    style="padding: 10px 20px; border-radius: 8px; border: none; background: linear-gradient(135deg, var(--primary), var(--primary-hover)); color: white; cursor: pointer; font-weight: 600;">💾
                    Salvar Alterações</button>
            </div>
        </form>
    </div>
</div>

<script>
    window.openEditProgramModal = function(programJson) {
        console.log("Opening edit modal with data:", programJson);
        let program;
        try {
            // Decodifica a string JSON. Se vier do HTML enconded, usamos a forma direta
            program = typeof programJson === 'string' ? JSON.parse(programJson) : programJson;
        } catch(e) {
            console.error("Failed to parse program data", e, programJson);
            return;
        }

        const modal = document.getElementById('editProgramDetailsModal');
        if (!modal) {
            console.error("Modal element #editProgramDetailsModal not found!");
            return;
        }
        modal.classList.add('active');
        
        // Populate fields
        document.getElementById('editProgramId').value = program.id;
        document.getElementById('editProgramName').value = program.name;
        document.getElementById('editProgramCategory').value = program.category_id || '';
        document.getElementById('editProgramDuration').value = program.duration_hours || 4;
        document.getElementById('editProgramDescription').value = program.description || '';
        document.getElementById('editProgramDifficulty').value = program.difficulty || 3;
        document.getElementById('editProgramXp').value = program.xp_reward || 100;
        document.getElementById('editProgramOutdoor').checked = parseInt(program.is_outdoor) === 1;

        // Set Icon
        const iconValue = program.icon || 'noto:blue-book';
        document.getElementById('editProgramIcon').value = iconValue;
        document.getElementById('editProgramIconText').textContent = iconValue;
        
        const preview = document.getElementById('editProgramIconPreview');
        if (iconValue.startsWith('fa-')) {
            preview.innerHTML = `<i class="${iconValue}" style="font-size: 1.5rem; color: var(--primary);"></i>`;
        } else if (iconValue.includes(':')) {
            preview.innerHTML = `<iconify-icon icon="${iconValue}" style="font-size: 1.5rem;"></iconify-icon>`;
        } else {
             preview.innerHTML = `<span style="font-size: 1.5rem;">${iconValue}</span>`;
        }
    }

    window.closeEditProgramModal = function() {
        document.getElementById('editProgramDetailsModal').classList.remove('active');
        document.getElementById('editProgramDetailsForm').reset();
    }

    // Close on click outside
    document.getElementById('editProgramDetailsModal').addEventListener('click', function (e) {
        if (e.target === this) {
            closeEditProgramModal();
        }
    });

    async function submitEditProgramDetails(e) {
        e.preventDefault();

        const form = document.getElementById('editProgramDetailsForm');
        const formData = new FormData(form);
        const programId = document.getElementById('editProgramId').value;
        const btn = form.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;

        btn.disabled = true;
        btn.innerHTML = 'Salvando...';

        try {
            const tSlug = window.programs_tenantSlug || '<?= $tenant['slug'] ?? '' ?>';
            const submitUrl = `/${tSlug}/admin/programas/${programId}`;

            const response = await fetch(submitUrl, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                showToast(data.message || 'Programa atualizado com sucesso!');
                closeEditProgramModal();
                setTimeout(() => location.reload(), 800);
            } else {
                showToast(data.error || 'Erro ao atualizar programa', 'error');
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        } catch (error) {
            console.error('Erro:', error);
            showToast('Erro de conexão ao atualizar programa', 'error');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }
</script>
