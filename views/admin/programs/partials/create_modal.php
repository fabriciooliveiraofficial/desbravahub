<!-- Create Program Modal Partial -->
<div id="createModal" class="modal-overlay"
    style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div class="modal-content"
        style="background: var(--bg-card); padding: 24px; border-radius: 12px; width: 100%; max-width: 600px; max-height: 90vh; overflow-y: auto;">
        <div class="modal-header"
            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h3 style="margin: 0; display: flex; align-items: center; gap: 8px;">
                <span class="material-icons-round" style="color: var(--primary);">add_circle</span>
                Novo Programa
            </h3>
            <button onclick="closeCreateProgramModal()"
                style="background: none; border: none; cursor: pointer; color: var(--text-secondary);">
                <span class="material-icons-round">close</span>
            </button>
        </div>

        <form id="createProgramFormV2" onsubmit="submitCreateProgramActionV2(event)">
            <div class="form-row">
                <div class="form-group">
                    <label>Tipo de Programa</label>
                    <select name="type" id="programType" class="form-control" onchange="filterCategories()">
                        <option value="specialty" <?= ($type ?? '') == 'specialty' ? 'selected' : '' ?>>🎯 Especialidade</option>
                        <option value="class" <?= ($type ?? '') == 'class' ? 'selected' : '' ?>>🎖️ Classe</option>
                    </select>
                </div>
                <div class="form-group" style="position: relative;">
                    <label>Nome *</label>
                    <input type="text" name="name" class="form-control" required="" placeholder="Ex: Primeiros Socorros"
                        id="programName" autocomplete="off">
                    <div id="programNameAutocomplete" style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: var(--bg-card); border: 1px solid var(--border-light); border-radius: 8px; max-height: 200px; overflow-y: auto; z-index: 100; box-shadow: 0 4px 12px rgba(0,0,0,0.1);"></div>
                    <div id="programNameWarning" style="display: none; color: #f7b32b; font-size: 0.85rem; margin-top: 4px;">
                        ⚠️ Já existe um programa com nome similar
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Categoria</label>
                    <select name="category_id" id="programCategory" class="form-control">
                        <option value="">Sem categoria</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" data-type="<?= $cat['type'] ?? 'both' ?>">
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

                <div class="form-group">
                    <label>Ícone do Programa</label>
                    <input type="hidden" id="programIcon" name="icon" value="noto:blue-book">
                    <div class="icon-picker-trigger" onclick="IconPicker.open('programIcon', 'programIconPreview', 'programIconText')" style="display: flex; align-items: center; gap: 12px; padding: 10px; background: var(--bg-input); border: 1px solid var(--border-light); border-radius: 8px; cursor: pointer; height: 42px;">
                        <div id="programIconPreview">
                            <iconify-icon icon="noto:blue-book" style="font-size: 1.5rem;"></iconify-icon>
                        </div>
                        <div class="icon-info" style="flex: 1;">
                            <span id="programIconText" style="font-size: 0.85rem; color: var(--text-primary);">noto:blue-book</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Descrição</label>
                <textarea name="description" class="form-control" rows="3"
                    placeholder="Descrição do programa..."></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Duração (horas)</label>
                    <input type="number" name="duration_hours" class="form-control" value="4" min="1" max="200">
                </div>

                <div class="form-group">
                    <label>Dificuldade (1-5)</label>
                    <select name="difficulty" class="form-control">
                        <option value="1">⭐ Iniciante</option>
                        <option value="2">⭐⭐ Básico</option>
                        <option value="3" selected="">⭐⭐⭐ Intermediário</option>
                        <option value="4">⭐⭐⭐⭐ Avançado</option>
                        <option value="5">⭐⭐⭐⭐⭐ Expert</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Recompensa XP</label>
                    <input type="number" name="xp_reward" class="form-control" value="100" min="0" step="10">
                </div>
            </div>

            <div class="form-group">
                <label class="form-check" style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                    <input type="checkbox" name="is_outdoor" value="1" style="width: 20px; height: 20px;">
                    <span>🏕️ Programa Outdoor (prático, sem perguntas interativas)</span>
                </label>
                <small style="margin-left: 30px; display: block; color: var(--text-secondary);">Programas outdoor
                    exigem envio de provas para aprovação manual.</small>
            </div>

            <div class="form-footer"
                style="display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid var(--border-light); padding-top: 24px; margin-top: 24px;">
                <button type="button" class="btn-cancel" onclick="closeCreateProgramModal()"
                    style="padding: 10px 20px; border-radius: 8px; border: 1px solid var(--border-light); background: transparent; color: var(--text-primary); cursor: pointer;">Cancelar</button>
                <button type="submit" class="btn-submit"
                    style="padding: 10px 20px; border-radius: 8px; border: none; background: linear-gradient(135deg, var(--primary), var(--primary-hover)); color: white; cursor: pointer; font-weight: 600;">🚀
                    Criar e Editar Requisitos</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Tenant slug expected from parent view
    
    function openCreateProgramModal() {
        const modal = document.getElementById('createModal');
        modal.style.display = 'flex';
        
        // Initialize autocomplete if available
        if (typeof setupAutocomplete === 'function') {
            setupAutocomplete(
                'programName',
                'programNameAutocomplete',
                'programNameWarning',
                '<?= base_url($tenant['slug'] . '/api/specialties/search') ?>'
            );
        }

        const nameInput = document.getElementById('programName');
        if (nameInput) nameInput.focus();
    }

    function closeCreateProgramModal() {
        document.getElementById('createModal').style.display = 'none';
        // Reset form
        document.getElementById('createProgramFormV2').reset();
        document.getElementById('programNameWarning').style.display = 'none';
        document.getElementById('programNameAutocomplete').style.display = 'none';
    }

    function filterCategories() {
        const typeSelect = document.getElementById('programType');
        const catSelect = document.getElementById('programCategory');
        
        if (!typeSelect || !catSelect) return;

        const selectedType = typeSelect.value;
        const options = catSelect.options;
        
        // Always show first option (No Category)
        
        for (let i = 1; i < options.length; i++) {
            const opt = options[i];
            const catType = opt.getAttribute('data-type') || 'both';
            
            // Logic:
            // 'both' -> show always
            // 'specialty' -> show if selectedType is specialty
            // 'class' -> show if selectedType is class
            
            let show = false;
            if (catType === 'both') show = true;
            else if (catType === selectedType) show = true;
            
            if (show) {
                opt.style.display = '';
                opt.disabled = false;
            } else {
                opt.style.display = 'none';
                opt.disabled = true;
            }
        }
        
        // If current selection is now hidden/disabled, reset to empty
        const currentOpt = options[catSelect.selectedIndex];
        if (currentOpt && (currentOpt.disabled || currentOpt.style.display === 'none')) {
            catSelect.value = "";
        }
    }

    // Initialize filtering on load
    if(document.readyState === 'loading') {
       document.addEventListener('DOMContentLoaded', filterCategories);
    } else {
       filterCategories();
    }
    
    // Iconify Picker is handled via onclick="IconPicker.open"
    // No need for initProgramIconPicker anymore
    
    // Close on click outside
    document.getElementById('createModal').addEventListener('click', function (e) {
        if (e.target === this) {
            closeCreateProgramModal();
        }
    });

    async function submitCreateProgramActionV2(e) {
        e.preventDefault();

        const form = document.getElementById('createProgramFormV2');
        const formData = new FormData(form);
        const btn = form.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;

        btn.disabled = true;
        btn.innerHTML = 'Criando...';

        try {
            // Use JS construction with safe PHP injection
            const tSlug = '<?= $tenant['slug'] ?? '' ?>';
            const submitUrl = '/' + tSlug + '/admin/programas';
            console.log('Submitting to:', submitUrl);

            const response = await fetch(submitUrl, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                window.location.href = data.redirect;
            } else {
                alert(data.error || 'Erro ao criar programa');
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        } catch (error) {
            console.error('Erro:', error);
            alert('Erro de conexão ao criar programa');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }
</script>
<?php require_once BASE_PATH . '/views/admin/partials/icon_picker.php'; ?>