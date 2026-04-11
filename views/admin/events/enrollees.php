<?php
/**
 * Admin Event Enrollees View
 */
?>

<div class="dashboard-header-actions" style="display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem;">
    <a href="<?= base_url($tenant['slug'] . '/admin/eventos') ?>" class="btn btn-secondary btn-sm" style="display: flex; align-items: center; gap: 0.5rem;">
        <span class="material-icons-round" style="font-size: 18px;">arrow_back</span> Voltar
    </a>
    <div style="flex: 1;">
        <h2 style="margin: 0; font-size: 1.5rem; color: var(--text-primary);"><?= htmlspecialchars($event['title']) ?></h2>
        <div style="font-size: 0.85rem; color: var(--text-secondary); display: flex; align-items: center; gap: 1rem; margin-top: 4px;">
            <span><span class="material-icons-round" style="font-size: 14px; vertical-align: middle;">calendar_today</span> <?= date('d/m/Y', strtotime($event['start_datetime'])) ?></span>
            <span><span class="material-icons-round" style="font-size: 14px; vertical-align: middle;">location_on</span> <?= htmlspecialchars($event['location'] ?: 'Presencial') ?></span>
        </div>
    </div>
    <div class="header-actions">
        <?php if (!empty($enrollees)): ?>
            <button type="button" class="btn btn-primary btn-sm" id="mark-all-btn" style="display: flex; align-items: center; gap: 0.5rem; background: var(--accent-gradient); border: none; padding: 10px 16px;">
                <span class="material-icons-round" style="font-size: 18px;">done_all</span> Marcar Presença em Massa
            </button>
        <?php endif; ?>
    </div>
</div>

<!-- Stats Row -->
<div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <div class="dashboard-card" style="padding: 1.5rem; text-align: center;">
        <div style="font-size: 0.85rem; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px;">Inscritos</div>
        <div style="font-size: 2rem; font-weight: 700; color: #6366f1; margin-top: 5px;"><?= count($enrollees) ?></div>
    </div>
    <div class="dashboard-card" style="padding: 1.5rem; text-align: center;">
        <div style="font-size: 0.85rem; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px;">Presentes</div>
        <div style="font-size: 2rem; font-weight: 700; color: #10b981; margin-top: 5px;">
            <?= count(array_filter($enrollees, fn($e) => $e['status'] === 'attended')) ?>
        </div>
    </div>
    <div class="dashboard-card" style="padding: 1.5rem; text-align: center;">
        <div style="font-size: 0.85rem; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px;">Faltas</div>
        <div style="font-size: 2rem; font-weight: 700; color: #ef4444; margin-top: 5px;">
            <?= count(array_filter($enrollees, fn($e) => $e['status'] === 'no_show')) ?>
        </div>
    </div>
    <div class="dashboard-card" style="padding: 1.5rem; text-align: center;">
        <div style="font-size: 0.85rem; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px;">Vagas</div>
        <div style="font-size: 2rem; font-weight: 700; color: var(--text-primary); margin-top: 5px;">
            <?= $event['max_participants'] ? $event['max_participants'] - count($enrollees) : '∞' ?>
        </div>
    </div>
</div>

<div class="dashboard-card">
    <header class="dashboard-card-header">
        <span class="material-icons-round" style="color: #6366f1;">people</span>
        <h3>Lista de Participantes</h3>
    </header>
    <div class="dashboard-card-body">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Participante</th>
                    <th>Email</th>
                    <th>Inscrição</th>
                    <th>Status</th>
                    <th>Presença</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($enrollees as $enrollee): ?>
                    <tr id="enrollee-<?= $enrollee['id'] ?>">
                        <td data-label="Participante">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div class="avatar-container" style="width: 32px; height: 32px; border-radius: 50%; overflow: hidden; background: var(--bg-secondary); flex-shrink: 0;">
                                    <?php if ($enrollee['avatar_url']): ?>
                                        <img src="<?= htmlspecialchars($enrollee['avatar_url']) ?>" alt="<?= htmlspecialchars($enrollee['name']) ?>" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.onerror=null; this.parentElement.innerHTML='<div style=\'width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:var(--accent-gradient);color:white;font-weight:bold;font-size:12px;\'>' + '<?= substr($enrollee['name'], 0, 1) ?>' + '</div>';">
                                    <?php else: ?>
                                        <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: var(--accent-gradient); color: white; font-weight: bold; font-size: 12px;">
                                            <?= substr($enrollee['name'], 0, 1) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <div style="font-weight: 600; color: var(--text-primary);"><?= htmlspecialchars($enrollee['name']) ?></div>
                                    <div style="font-size: 0.75rem; color: var(--text-secondary);"><?= htmlspecialchars($enrollee['role_name'] ?: 'Membro') ?></div>
                                </div>
                            </div>
                        </td>
                        <td data-label="Email" style="color: var(--text-secondary); font-size: 0.85rem;">
                            <?= htmlspecialchars($enrollee['email']) ?>
                        </td>
                        <td data-label="Inscrição" style="color: var(--text-secondary); font-size: 0.85rem;">
                            <?= date('d/m/Y H:i', strtotime($enrollee['enrolled_at'])) ?>
                        </td>
                        <td data-label="Status" class="status-cell">
                            <?php
                            $statusLabel = match ($enrollee['status']) {
                                'enrolled' => '<span class="badge badge-info status-badge">Inscrito</span>',
                                'attended' => '<span class="badge badge-success status-badge">Presente</span>',
                                'no_show' => '<span class="badge badge-danger status-badge">Faltou</span>',
                                'cancelled' => '<span class="badge badge-secondary status-badge">Cancelado</span>',
                                default => '<span class="badge badge-secondary status-badge">' . $enrollee['status'] . '</span>'
                            };
                            echo $statusLabel;
                            ?>
                        </td>
                        <td data-label="Presença">
                            <div class="attendance-controls" style="display: flex; gap: 8px;">
                                <button type="button" 
                                        class="btn btn-sm btn-outline-success mark-status-btn <?= $enrollee['status'] === 'attended' ? 'active' : '' ?>" 
                                        data-id="<?= $enrollee['id'] ?>" 
                                        data-status="attended" 
                                        title="Marcar como Presente">
                                    <span class="material-icons-round" style="font-size: 18px;">check_circle</span>
                                </button>
                                <button type="button" 
                                        class="btn btn-sm btn-outline-danger mark-status-btn <?= $enrollee['status'] === 'no_show' ? 'active' : '' ?>" 
                                        data-id="<?= $enrollee['id'] ?>" 
                                        data-status="no_show" 
                                        title="Marcar como Falta">
                                    <span class="material-icons-round" style="font-size: 18px;">cancel</span>
                                </button>
                                <button type="button" 
                                        class="btn btn-sm btn-outline-secondary mark-status-btn <?= $enrollee['status'] === 'enrolled' ? 'active' : '' ?>" 
                                        data-id="<?= $enrollee['id'] ?>" 
                                        data-status="enrolled" 
                                        title="Resetar para Inscrito">
                                    <span class="material-icons-round" style="font-size: 18px;">restart_alt</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($enrollees)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 40px; color: var(--text-secondary);">
                            Nenhum inscrito para este evento.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.mark-status-btn {
    padding: 6px;
    border-radius: 8px;
    transition: all 0.2s ease;
    background: transparent;
    cursor: pointer;
}
.mark-status-btn.active.btn-outline-success { background: #10b981; color: white; border-color: #10b981; }
.mark-status-btn.active.btn-outline-danger { background: #ef4444; color: white; border-color: #ef4444; }
.mark-status-btn.active.btn-outline-secondary { background: #64748b; color: white; border-color: #64748b; }

.status-badge {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

tr.updating { opacity: 0.6; pointer-events: none; }
</style>

<script>
document.querySelectorAll('.mark-status-btn').forEach(btn => {
    btn.addEventListener('click', async function() {
        const id = this.dataset.id;
        const status = this.dataset.status;
        const row = document.getElementById('enrollee-' + id);
        
        if (this.classList.contains('active')) return;

        row.classList.add('updating');

        try {
            const formData = new FormData();
            formData.append('enrollment_id', id);
            formData.append('status', status);

            const response = await fetch('<?= base_url($tenant['slug'] . '/admin/eventos/inscritos/presenca') ?>', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                // Update buttons UI
                row.querySelectorAll('.mark-status-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                // Update status badge
                const statusCell = row.querySelector('.status-cell');
                let badgeClass = 'secondary';
                let label = status;

                switch(status) {
                    case 'attended': badgeClass = 'success'; label = 'Presente'; break;
                    case 'no_show': badgeClass = 'danger'; label = 'Faltou'; break;
                    case 'enrolled': badgeClass = 'info'; label = 'Inscrito'; break;
                }
                
                statusCell.innerHTML = `<span class="badge badge-${badgeClass} status-badge">${label}</span>`;
                
                // Optional: Recalculate stats or show toast
                if (window.showToast) {
                    showToast('Status atualizado com sucesso!', 'success');
                }
            } else {
                alert(result.error || 'Erro ao atualizar status.');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Erro de conexão ao atualizar status.');
        } finally {
            row.classList.remove('updating');
        }
    });
});

// Mass Attendance logic
const markAllBtn = document.getElementById('mark-all-btn');
if (markAllBtn) {
    markAllBtn.addEventListener('click', async function() {
        if (!confirm('Deseja marcar TODOS os inscritos com presença? Isso irá premiar todos com XP, Badges (se houver) e gerar Certificados.')) return;

        const originalText = this.innerHTML;
        this.innerHTML = '<span class="material-icons-round rotate">sync</span> Processando...';
        this.disabled = true;

        try {
            const response = await fetch('<?= base_url($tenant['slug'] . '/admin/eventos/' . $event['id'] . '/inscritos/presenca-massa') ?>', {
                method: 'POST',
                headers: { 'Accept': 'application/json' }
            });

            const result = await response.json();

            if (result.success) {
                if (window.toast) toast.success('Sucesso', result.message);
                else alert(result.message);
                setTimeout(() => window.location.reload(), 1500);
            } else {
                alert(result.error || 'Erro ao processar presença em massa.');
                this.innerHTML = originalText;
                this.disabled = false;
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Erro de conexão ao processar massa.');
            this.innerHTML = originalText;
            this.disabled = false;
        }
    });
}
</script>

<style>
.rotate {
    animation: spin 1s linear infinite;
}
@keyframes spin {
    100% { transform: rotate(360deg); }
}
</style>
