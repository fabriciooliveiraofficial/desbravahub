<!-- Edit Program Details Modal Partial -->
<style>
    #editProgramDetailsModal .modal-content {
        background: var(--bg-card);
        padding: 0; 
        border: 1px solid var(--border-color);
        border-radius: 20px;
        width: 100%;
        max-width: 600px;
        max-height: 94vh;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    }

    #editProgramDetailsModal .modal-header {
        padding: 24px 32px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: var(--bg-card);
    }

    #editProgramDetailsModal form {
        padding: 32px;
        overflow-y: auto;
        flex: 1;
    }

    /* Custom scrollbar for form */
    #editProgramDetailsModal form::-webkit-scrollbar {
        width: 6px;
    }

    #editProgramDetailsModal form::-webkit-scrollbar-track {
        background: transparent;
    }

    #editProgramDetailsModal form::-webkit-scrollbar-thumb {
        background: var(--border-color);
        border-radius: 10px;
    }

    #editProgramDetailsModal form::-webkit-scrollbar-thumb:hover {
        background: var(--primary);
    }

    #editProgramDetailsModal .modal-header h3 {
        font-size: 1.25rem;
        letter-spacing: -0.02em;
        font-weight: 800;
        text-transform: uppercase;
        color: var(--text-dark);
    }

    #editProgramDetailsModal .tactical-label {
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: var(--text-muted);
        margin-bottom: 10px;
        display: block;
    }

    #editProgramDetailsModal .form-control-tactical {
        width: 100%;
        background: var(--bg-dark);
        border: 1.5px solid var(--border-color);
        border-radius: 12px;
        padding: 12px 16px;
        color: var(--text-main);
        font-weight: 500;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }

    #editProgramDetailsModal .form-control-tactical:focus {
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
        height: 24px;
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

    #editProgramOutdoor:checked + .outdoor-feature-card {
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

    #editProgramOutdoor:checked + .outdoor-feature-card .feature-icon-box {
        background: var(--accent-emerald);
        color: white;
    }
</style>

<div id="editProgramDetailsModal" class="modal-overlay" onclick="if(event.target === this) closeEditProgramModal();">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Redefinir Programa</h3>
            <button onclick="closeEditProgramModal()" style="background: var(--bg-dark); border: none; cursor: pointer; color: var(--text-muted); width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; transition: all 0.2s;">
                <span class="material-icons-round" style="font-size: 20px;">close</span>
            </button>
        </div>

        <form id="editProgramDetailsForm" onsubmit="submitEditProgramDetails(event)">
            <input type="hidden" name="program_id" id="editProgramId">

            <!-- Row 1: Nome + Categoria -->
            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
                <div class="form-group">
                    <label class="tactical-label">Nome do Programa</label>
                    <input type="text" name="name" id="editProgramName" class="form-control-tactical" required placeholder="Ex: Primeiros Socorros">
                </div>
                <div class="form-group">
                    <label class="tactical-label">Categoria</label>
                    <select name="category_id" id="editProgramCategory" class="form-control-tactical" required>
                        <option value="" disabled>Selecione...</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Row 2: Ícone Premium -->
            <div class="form-group" style="margin-bottom: 24px;">
                <label class="tactical-label">Identificação Visual</label>
                <input type="hidden" id="editProgramIcon" name="icon" value="noto:blue-book">
                <div class="icon-trigger-premium" 
                     onclick="IconPicker.open(document.getElementById('editProgramIcon').value, (sel) => {
                         document.getElementById('editProgramIcon').value = sel;
                         updateProgramIconPreview(sel);
                     })">
                    <div id="editProgramIconPreview" class="icon-preview-box">
                        <iconify-icon icon="noto:blue-book"></iconify-icon>
                    </div>
                    <div class="icon-details">
                        <div id="editProgramIconText" style="font-weight: 700; color: var(--text-dark); font-family: 'JetBrains Mono', monospace; font-size: 0.9rem;">noto:blue-book</div>
                        <div style="font-size: 11px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 2px;">Clique para alterar o ícone</div>
                    </div>
                </div>
            </div>

            <!-- Row 3: Descrição -->
            <div class="form-group" style="margin-bottom: 24px;">
                <label class="tactical-label">Descrição do Programa</label>
                <textarea name="description" id="editProgramDescription" class="form-control-tactical" rows="4" style="resize: none;" placeholder="Descreva os objetivos e requisitos..."></textarea>
            </div>

            <!-- Row 4: Fair Play HUD -->
            <div class="fair-play-hud">
                <div class="hud-metric">
                    <label class="tactical-label">Dificuldade</label>
                    <select name="difficulty" id="editProgramDifficulty" class="form-control-tactical" onchange="applyEditFairPlayProg()">
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
                        <span class="material-icons-round" style="color: var(--primary); font-size: 18px;">Bolt</span>
                        <input type="number" name="xp_reward" id="editProgramXp" readonly value="280">
                    </div>
                </div>

                <div class="hud-metric">
                    <label class="tactical-label">Est. Time</label>
                    <div class="value-container">
                        <span class="material-icons-round" style="color: var(--primary); font-size: 18px;">schedule</span>
                        <input type="number" name="duration_hours" id="editProgramDuration" readonly value="16">
                        <span style="font-size: 10px; color: var(--text-muted); font-weight: 800; margin-left: -4px;">HRS</span>
                    </div>
                </div>
            </div>

            <!-- Row 5: Outdoor Feature Card -->
            <div class="form-group" style="margin-bottom: 24px;">
                <label class="tactical-label">Configurações de Local</label>
                <input type="checkbox" name="is_outdoor" id="editProgramOutdoor" value="1" style="display: none;">
                <label for="editProgramOutdoor" class="outdoor-feature-card">
                    <div class="feature-icon-box">
                        <span class="material-icons-round">terrain</span>
                    </div>
                    <div class="feature-info">
                        <div style="font-weight: 800; color: var(--text-dark); font-size: 0.95rem; display: flex; align-items: center; gap: 8px;">
                            Mission Outdoor
                            <span class="outdoor-badge" style="font-size: 8px; padding: 2px 6px; background: var(--bg-dark); border-radius: 4px; color: var(--text-muted);">FIELD WORK</span>
                        </div>
                        <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 4px; line-height: 1.4;">
                            Atividades práticas que exigem evidências fotográficas ou vídeos para conclusão.
                        </div>
                    </div>
                </label>
            </div>

            <div class="form-footer" style="display: flex; justify-content: flex-end; gap: 16px; margin-top: 40px; padding-top: 24px; border-top: 1px solid var(--border-color);">
                <button type="button" class="btn-tactical-cancel" onclick="closeEditProgramModal()" style="padding: 12px 24px; background: transparent; border: 1.5px solid var(--border-color); color: var(--text-main); font-weight: 800; font-size: 11px; text-transform: uppercase; letter-spacing: 0.1em; border-radius: 10px; cursor: pointer; transition: all 0.2s;">
                    Devolver Missão
                </button>
                <button type="submit" class="btn-tactical-submit" style="padding: 12px 32px; background: var(--primary); border: none; color: white; font-weight: 800; font-size: 11px; text-transform: uppercase; letter-spacing: 0.1em; border-radius: 10px; cursor: pointer; box-shadow: 0 4px 12px rgba(6, 182, 212, 0.3); transition: all 0.2s; display: flex; align-items: center; gap: 8px;">
                    <span class="material-icons-round" style="font-size: 18px;">save</span>
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    var EDIT_PROG_FAIR_PLAY = {
        1: { xp: 100,  hours: 4  },
        2: { xp: 180,  hours: 8  },
        3: { xp: 280,  hours: 16 },
        4: { xp: 400,  hours: 32 },
        5: { xp: 500,  hours: 60 },
    };

    function applyEditFairPlayProg() {
        const diff = parseInt(document.getElementById('editProgramDifficulty').value, 10);
        const rule = EDIT_PROG_FAIR_PLAY[diff];
        if (!rule) return;
        document.getElementById('editProgramXp').value       = rule.xp;
        document.getElementById('editProgramDuration').value = rule.hours;
    }

    function updateProgramIconPreview(sel) {
        const preview = document.getElementById('editProgramIconPreview');
        const textLabel = document.getElementById('editProgramIconText');
        textLabel.textContent = sel;
        
        if (sel.startsWith('fa-')) {
            preview.innerHTML = `<i class="${sel}" style="font-size:1.5rem;color:var(--primary);"></i>`;
        } else if (sel.includes(':')) {
            const cleanIcon = sel.split(' ')[0];
            preview.innerHTML = `<iconify-icon icon="${cleanIcon}" style="font-size:1.5rem;"></iconify-icon>`;
        } else {
            preview.innerHTML = `<span style="font-size:1.5rem;">${sel}</span>`;
        }
    }

    window.openEditProgramModal = function(programJson) {
        let program;
        try {
            program = typeof programJson === 'string' ? JSON.parse(programJson) : programJson;
        } catch(e) {
            console.error('Failed to parse program data', e, programJson);
            return;
        }

        const modal = document.getElementById('editProgramDetailsModal');
        if (!modal) return;
        modal.classList.add('active');

        // Populate base fields
        document.getElementById('editProgramId').value          = program.id;
        document.getElementById('editProgramName').value        = program.name;
        document.getElementById('editProgramCategory').value    = program.category_id || '';
        document.getElementById('editProgramDescription').value = program.description || '';
        document.getElementById('editProgramOutdoor').checked   = parseInt(program.is_outdoor) === 1;

        // Set difficulty then sync XP + Duration
        document.getElementById('editProgramDifficulty').value = program.difficulty || 3;
        applyEditFairPlayProg();

        // Set Icon
        const iconValue = program.icon || 'noto:blue-book';
        document.getElementById('editProgramIcon').value = iconValue;
        updateProgramIconPreview(iconValue);
    }

    window.closeEditProgramModal = function() {
        document.getElementById('editProgramDetailsModal').classList.remove('active');
        document.getElementById('editProgramDetailsForm').reset();
    }

    async function submitEditProgramDetails(e) {
        e.preventDefault();
        const form      = document.getElementById('editProgramDetailsForm');
        const formData  = new FormData(form);
        const programId = document.getElementById('editProgramId').value;
        const btn       = form.querySelector('button[type="submit"]');
        const original  = btn.innerHTML;

        btn.disabled = true;
        btn.innerHTML = 'Processando...';

        try {
            const tSlug = window.programs_tenantSlug || '<?= $tenant['slug'] ?? '' ?>';
            const response = await fetch(`/${tSlug}/admin/programas/${programId}`, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            if (data.success) {
                showToast(data.message || 'Programa atualizado!', 'success');
                closeEditProgramModal();
                setTimeout(() => location.reload(), 800);
            } else {
                showToast(data.error || 'Erro ao atualizar', 'error');
                btn.disabled = false;
                btn.innerHTML = original;
            }
        } catch (error) {
            console.error('Erro:', error);
            showToast('Erro de conexão', 'error');
            btn.disabled = false;
            btn.innerHTML = original;
        }
    }
</script>
