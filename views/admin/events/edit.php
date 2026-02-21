<?php
/**
 * Admin Edit Event View
 */
?>
<div class="dashboard-header" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
    <div style="display: flex; align-items: center; gap: 12px;">
        <a href="<?= base_url($tenant['slug'] . '/admin/eventos') ?>" class="btn btn-secondary btn-sm" style="padding: 8px;">
            <span class="material-icons-round">arrow_back</span>
        </a>
        <h2 style="margin: 0; color: var(--text-primary); font-size: 1.5rem; display: flex; align-items: center; gap: 8px;">
            <span class="material-icons-round" style="color: #6366f1;">event_note</span>
            Editar Evento
        </h2>
    </div>
    <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete(<?= $event['id'] ?>)">
        <span class="material-icons-round">delete</span> Excluir
    </button>
</div>

<div class="dashboard-card">
    <div class="dashboard-card-body">
        <form id="edit-event-form">
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label>Título do Evento *</label>
                    <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($event['title']) ?>">
                </div>
                <div class="form-group">
                    <label>Slug (URL)</label>
                    <input type="text" name="slug" class="form-control" required value="<?= htmlspecialchars($event['slug']) ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Descrição</label>
                <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($event['description'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label>Localização</label>
                <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($event['location'] ?? '') ?>">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label>Data/Hora Início *</label>
                    <input type="datetime-local" name="start_datetime" class="form-control" required value="<?= date('Y-m-d\TH:i', strtotime($event['start_datetime'])) ?>">
                </div>
                <div class="form-group">
                    <label>Data/Hora Término</label>
                    <input type="datetime-local" name="end_datetime" class="form-control" value="<?= $event['end_datetime'] ? date('Y-m-d\TH:i', strtotime($event['end_datetime'])) : '' ?>">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label>Vagas (0 = Ilimitado)</label>
                    <input type="number" name="max_participants" class="form-control" min="0" value="<?= $event['max_participants'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label>Prazo de Inscrição</label>
                    <input type="datetime-local" name="registration_deadline" class="form-control" value="<?= $event['registration_deadline'] ? date('Y-m-d\TH:i', strtotime($event['registration_deadline'])) : '' ?>">
                </div>
                <div class="form-group">
                    <label>Recompensa (XP Fair Play)</label>
                    <input type="number" name="xp_reward" class="form-control" min="0" value="<?= $event['xp_reward'] ?>">
                </div>
            </div>

            <div style="border-top: 1px solid var(--border-light); margin: 24px 0; padding-top: 24px;">
                <h4 style="margin-top: 0; color: var(--text-primary); margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                    <span class="material-icons-round" style="color: #10b981; font-size: 20px;">payments</span>
                    Financeiro do Evento
                </h4>
                
                <div class="form-group" style="margin-bottom: 16px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="is_paid" id="is_paid" value="1" <?= $event['is_paid'] ? 'checked' : '' ?> style="width: 18px; height: 18px;" onchange="togglePaidFields()">
                        <strong>Evento Pago</strong> (Exige pagamento ou taxa de inscrição)
                    </label>
                </div>

                <div id="paid-fields" style="display: <?= $event['is_paid'] ? 'grid' : 'none' ?>; grid-template-columns: 1fr 2fr; gap: 16px;">
                    <div class="form-group">
                        <label>Valor (R$)</label>
                        <input type="number" name="price" class="form-control" step="0.01" min="0" value="<?= $event['price'] ?? '' ?>">
                    </div>
                    <div class="form-group">
                        <label>Link de Pagamento (Mercado Pago, Stripe, etc)</label>
                        <input type="url" name="payment_link" class="form-control" value="<?= htmlspecialchars($event['payment_link'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <div style="border-top: 1px solid var(--border-light); margin: 24px 0; padding-top: 24px;">
                <div class="form-group" style="max-width: 300px;">
                    <label>Status do Evento</label>
                    <select name="status" class="form-control">
                        <option value="upcoming" <?= $event['status'] === 'upcoming' ? 'selected' : '' ?>>Agendado (Inscrições Abertas)</option>
                        <option value="ongoing" <?= $event['status'] === 'ongoing' ? 'selected' : '' ?>>Em Andamento</option>
                        <option value="completed" <?= $event['status'] === 'completed' ? 'selected' : '' ?>>Concluído</option>
                        <option value="cancelled" <?= $event['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelado</option>
                    </select>
                </div>
            </div>

            <div style="margin-top: 24px; display: flex; justify-content: flex-end; gap: 12px;">
                <button type="submit" class="btn btn-primary" id="save-btn">
                    <span class="material-icons-round">save</span> Atualizar Evento
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    var toast = window.toast = window.toast || new (window.ToastNotification || ToastNotification)();

    function togglePaidFields() {
        const isPaid = document.getElementById('is_paid').checked;
        const fields = document.getElementById('paid-fields');
        fields.style.display = isPaid ? 'grid' : 'none';
        
        if (!isPaid) {
            document.querySelector('[name="price"]').value = '';
            document.querySelector('[name="payment_link"]').value = '';
        }
    }

    document.getElementById('edit-event-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const btn = document.getElementById('save-btn');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="material-icons-round rotate">sync</span> Salvando...';
        btn.disabled = true;

        const formData = new FormData(e.target);

        try {
            const response = await fetch('<?= base_url($tenant['slug'] . '/admin/eventos/' . $event['id'] . '/editar') ?>', {
                method: 'POST',
                body: formData,
                headers: { 'Accept': 'application/json' }
            });

            const data = await response.json();

            if (data.success) {
                toast.success('Sucesso', data.message);
                setTimeout(() => window.location.href = data.redirect, 1000);
            } else {
                toast.error('Erro', data.error || 'Erro ao atualizar evento');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        } catch (err) {
            console.error(err);
            toast.error('Erro', 'Erro de conexão.');
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    });

    async function confirmDelete(id) {
        if (!confirm('Tem certeza que deseja excluir este evento? Esta ação não pode ser desfeita e todas as inscrições serão perdidas.')) return;
        
        try {
            const response = await fetch(`<?= base_url($tenant['slug'] . '/admin/eventos/') ?>${id}/excluir`, {
                method: 'POST',
                headers: { 'Accept': 'application/json' }
            });

            const data = await response.json();

            if (data.success) {
                toast.success('Sucesso', data.message);
                setTimeout(() => window.location.href = '<?= base_url($tenant['slug'] . '/admin/eventos') ?>', 1000);
            } else {
                toast.error('Erro', data.error || 'Erro ao excluir');
            }
        } catch (err) {
            console.error(err);
            toast.error('Erro', 'Erro de conexão.');
        }
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
