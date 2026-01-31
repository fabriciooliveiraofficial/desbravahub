<!-- Create Class Modal Partial -->
<div id="classModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; justify-content: center; align-items: center;" onclick="closeClassModal(event)">
    <div class="modal-content" style="background: var(--bg-card); padding: 24px; border-radius: 12px; width: 100%; max-width: 500px; max-height: 90vh; overflow-y: auto;" onclick="event.stopPropagation()">
        <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h3 style="margin: 0; display: flex; align-items: center; gap: 8px;">
                <span class="material-icons-round" style="color: var(--primary);">school</span> 
                <span id="classModalTitle">Nova Classe</span>
            </h3>
            <button onclick="closeClassModal()" style="background: none; border: none; cursor: pointer; color: var(--text-secondary);">
                <span class="material-icons-round">close</span>
            </button>
        </div>

        <form id="classForm" onsubmit="submitClassForm(event)">
            <input type="hidden" id="classId" name="id">
            <div class="form-group" style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Nome da Classe *</label>
                <input type="text" id="className" name="name" class="form-control" required placeholder="Ex: Amigo" style="width: 100%; padding: 10px 14px; background: var(--bg-dark); border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-main);">
            </div>

            <div class="form-group" style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Descrição</label>
                <textarea id="classDescription" name="description" class="form-control" rows="3" placeholder="Descrição opcional..." style="width: 100%; padding: 10px 14px; background: var(--bg-dark); border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-main); resize: vertical;"></textarea>
            </div>

            <div class="form-row" style="display: flex; gap: 16px; margin-bottom: 16px;">
                <div class="form-group" style="flex: 1;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Ícone (emoji)</label>
                    <input type="text" id="classIcon" name="icon" class="form-control" value="🌱" maxlength="4" style="width: 100%; padding: 10px 14px; background: var(--bg-dark); border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-main); text-align: center; font-size: 1.2rem;">
                </div>
                <div class="form-group" style="flex: 1;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Cor</label>
                    <input type="color" id="classColor" name="color" value="#4CAF50" style="width: 100%; height: 42px; padding: 4px; background: var(--bg-dark); border: 1px solid var(--border-color); border-radius: 8px; cursor: pointer;">
                </div>
            </div>

            <div class="form-footer" style="display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid var(--border-light); padding-top: 24px; margin-top: 24px;">
                <button type="button" class="btn-cancel" onclick="closeClassModal()" style="padding: 10px 20px; border-radius: 8px; border: 1px solid var(--border-light); background: transparent; color: var(--text-primary); cursor: pointer;">Cancelar</button>
                <button type="submit" id="btnSaveClass" style="padding: 10px 20px; border-radius: 8px; border: none; background: linear-gradient(135deg, var(--primary), var(--primary-hover)); color: white; cursor: pointer; font-weight: 600;">
                    💾 Salvar Classe
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openClassModal(data = null) {
        document.getElementById('classForm').reset();
        const titleEl = document.getElementById('classModalTitle');
        const idEl = document.getElementById('classId');
        
        if (data) {
            titleEl.textContent = 'Editar Classe';
            idEl.value = data.db_id || data.id;
            document.getElementById('className').value = data.name || '';
            document.getElementById('classDescription').value = data.description || '';
            document.getElementById('classIcon').value = data.icon || '🌱';
            document.getElementById('classColor').value = data.color || '#4CAF50';
        } else {
            titleEl.textContent = 'Nova Classe';
            idEl.value = '';
            document.getElementById('classIcon').value = '🌱';
            document.getElementById('classColor').value = '#4CAF50';
        }

        document.getElementById('classModal').style.display = 'flex';
        document.getElementById('className').focus();
    }

    function closeClassModal(e) {
        if (e && e.target !== e.currentTarget) return;
        document.getElementById('classModal').style.display = 'none';
    }

    async function submitClassForm(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSaveClass');
        const originalText = btn.innerHTML;
        const id = document.getElementById('classId').value;
        
        const data = {
            name: document.getElementById('className').value,
            description: document.getElementById('classDescription').value,
            icon: document.getElementById('classIcon').value,
            color: document.getElementById('classColor').value
        };

        btn.disabled = true;
        btn.innerHTML = '⏳ Salvando...';

        const url = id 
            ? `/${window.tenantSlug}/admin/mission-control/class/${id}`
            : `/${window.tenantSlug}/admin/mission-control/class`;

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                if (typeof showToast === 'function') {
                    showToast(result.message || 'Salvo com sucesso!');
                } else {
                    alert(result.message || 'Salvo com sucesso!');
                }
                setTimeout(() => location.reload(), 500);
            } else {
                alert(result.error || 'Erro ao salvar classe');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Erro de conexão ao salvar classe');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }
</script>
