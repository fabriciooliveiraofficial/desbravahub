<?php
/**
 * Subscriptions (Mensalidades) Management Page
 */

$formatMoney = function($cents) {
    return 'R$ ' . number_format($cents / 100, 2, ',', '.');
};

$cycleLabelMap = [
    'WEEKLY' => 'Semanal',
    'BIWEEKLY' => 'Quinzenal',
    'MONTHLY' => 'Mensal',
    'QUARTERLY' => 'Trimestral',
    'SEMIANNUALLY' => 'Semestral',
    'YEARLY' => 'Anual',
];
?>

<!-- Header -->
<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
            <a href="<?= base_url($tenant['slug'] . '/admin/financeiro') ?>" style="color: var(--text-muted); text-decoration: none;">
                <span class="material-icons-round" style="font-size: 1.2rem;">arrow_back</span>
            </a>
            <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--text-dark); margin: 0;">Mensalidades</h1>
        </div>
        <p style="color: var(--text-muted); font-size: 0.875rem; margin: 0;">Crie planos de cobrança recorrente para seus membros</p>
    </div>
    <button onclick="document.getElementById('new-plan-modal').classList.remove('hidden')" class="btn btn-primary">
        <span class="material-icons-round">add</span>
        Novo Plano
    </button>
</div>

<!-- Plans Grid -->
<?php if (empty($plans)): ?>
    <div class="dashboard-card" style="align-items: center; text-align: center; padding: 3rem;">
        <div style="width: 80px; height: 80px; background: rgba(245, 158, 11, 0.1); border-radius: 20px; display: flex; align-items: center; justify-content: center; color: #d97706; margin-bottom: 1rem;">
            <span class="material-icons-round" style="font-size: 2.5rem;">autorenew</span>
        </div>
        <h3 style="margin: 0 0 0.5rem 0; font-weight: 700;">Nenhum plano criado</h3>
        <p style="color: var(--text-muted); margin: 0 0 1.5rem 0;">Crie seu primeiro plano de mensalidade para começar a cobrar seus membros automaticamente.</p>
        <button onclick="document.getElementById('new-plan-modal').classList.remove('hidden')" class="btn btn-primary">
            <span class="material-icons-round">add</span>
            Criar Primeiro Plano
        </button>
    </div>
<?php else: ?>
    <div class="dashboard-grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));">
        <?php foreach ($plans as $plan): ?>
        <div class="dashboard-card" style="position: relative;">
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                <div style="width: 44px; height: 44px; background: linear-gradient(135deg, #f59e0b, #d97706); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white;">
                    <span class="material-icons-round">savings</span>
                </div>
                <div>
                    <h3 style="margin: 0; font-weight: 700; font-size: 1rem;"><?= htmlspecialchars($plan['name']) ?></h3>
                    <span style="font-size: 0.7rem; background: <?= $plan['is_active'] ? '#dcfce7' : '#fef2f2' ?>; color: <?= $plan['is_active'] ? '#15803d' : '#dc2626' ?>; padding: 2px 8px; border-radius: 4px;">
                        <?= $plan['is_active'] ? 'Ativo' : 'Inativo' ?>
                    </span>
                </div>
            </div>

            <?php if ($plan['description']): ?>
                <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0 0 1rem 0;"><?= htmlspecialchars($plan['description']) ?></p>
            <?php endif; ?>

            <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 1rem;">
                <div>
                    <span style="font-size: 1.5rem; font-weight: 800; color: var(--text-dark);"><?= $formatMoney($plan['amount_cents']) ?></span>
                    <span style="font-size: 0.8rem; color: var(--text-muted);">/ <?= $cycleLabelMap[$plan['billing_cycle']] ?? $plan['billing_cycle'] ?></span>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 1.25rem; font-weight: 700; color: #10b981;"><?= $plan['active_subscribers'] ?? 0 ?></div>
                    <div style="font-size: 0.7rem; color: var(--text-muted);">assinantes</div>
                </div>
            </div>

            <button onclick="openAssignModal(<?= $plan['id'] ?>, '<?= htmlspecialchars($plan['name'], ENT_QUOTES) ?>')" class="btn btn-outline" style="width: 100%; justify-content: center;">
                <span class="material-icons-round">person_add</span>
                Atribuir a Membro
            </button>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- New Plan Modal -->
<div id="new-plan-modal" class="hidden" style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: white; border-radius: 16px; padding: 2rem; max-width: 500px; width: 100%; max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3 style="margin: 0; font-weight: 700;">Novo Plano de Mensalidade</h3>
            <button onclick="document.getElementById('new-plan-modal').classList.add('hidden')" style="background: none; border: none; cursor: pointer; color: var(--text-muted);">
                <span class="material-icons-round">close</span>
            </button>
        </div>
        <form id="create-plan-form" style="display: grid; gap: 1rem;">
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 4px;">Nome do Plano *</label>
                <input type="text" name="name" placeholder="Ex: Mensalidade Desbravador 2025" required
                    style="width: 100%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 8px;">
            </div>
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 4px;">Descrição</label>
                <textarea name="description" rows="2" placeholder="Descrição opcional..."
                    style="width: 100%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 8px; resize: vertical;"></textarea>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 4px;">Valor (R$) *</label>
                    <input type="number" name="amount" step="0.01" min="1" placeholder="50.00" required
                        style="width: 100%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 8px;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 4px;">Periodicidade</label>
                    <select name="cycle" style="width: 100%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 8px; background: white;">
                        <option value="MONTHLY">Mensal</option>
                        <option value="WEEKLY">Semanal</option>
                        <option value="BIWEEKLY">Quinzenal</option>
                        <option value="QUARTERLY">Trimestral</option>
                        <option value="SEMIANNUALLY">Semestral</option>
                        <option value="YEARLY">Anual</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary" style="margin-top: 0.5rem;">
                <span class="material-icons-round">check</span>
                Criar Plano
            </button>
        </form>
    </div>
</div>

<!-- Assign Member Modal -->
<div id="assign-modal" class="hidden" style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: white; border-radius: 16px; padding: 2rem; max-width: 450px; width: 100%;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3 style="margin: 0; font-weight: 700;">Atribuir Mensalidade</h3>
            <button onclick="document.getElementById('assign-modal').classList.add('hidden')" style="background: none; border: none; cursor: pointer; color: var(--text-muted);">
                <span class="material-icons-round">close</span>
            </button>
        </div>
        <p id="assign-plan-name" style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 1rem;"></p>
        <form id="assign-form" style="display: grid; gap: 1rem;">
            <input type="hidden" name="plan_id" id="assign-plan-id">
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 4px;">Selecione o Membro</label>
                <select name="user_id" required style="width: 100%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 8px; background: white;">
                    <option value="">Escolha...</option>
                    <!-- Populated dynamically or with all members -->
                </select>
            </div>
            <button type="submit" class="btn btn-primary">
                <span class="material-icons-round">person_add</span>
                Atribuir Mensalidade
            </button>
        </form>
    </div>
</div>

<script>
function openAssignModal(planId, planName) {
    document.getElementById('assign-plan-id').value = planId;
    document.getElementById('assign-plan-name').textContent = 'Plano: ' + planName;
    document.getElementById('assign-modal').classList.remove('hidden');
}

document.getElementById('create-plan-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true;
    try {
        const resp = await fetch('<?= base_url($tenant['slug'] . '/admin/pagamentos/planos') ?>', {
            method: 'POST', body: new FormData(this)
        });
        const data = await resp.json();
        if (data.success) {
            if (typeof showToast !== 'undefined') showToast(data.message, 'success');
            setTimeout(() => location.reload(), 800);
        } else {
            if (typeof showToast !== 'undefined') showToast(data.error, 'error');
            else alert(data.error);
            btn.disabled = false;
        }
    } catch (err) { alert('Erro'); btn.disabled = false; }
});

document.getElementById('assign-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true;
    try {
        const resp = await fetch('<?= base_url($tenant['slug'] . '/admin/pagamentos/assinar') ?>', {
            method: 'POST', body: new FormData(this)
        });
        const data = await resp.json();
        if (data.success) {
            if (typeof showToast !== 'undefined') showToast(data.message, 'success');
            setTimeout(() => location.reload(), 800);
        } else {
            if (typeof showToast !== 'undefined') showToast(data.error, 'error');
            else alert(data.error);
            btn.disabled = false;
        }
    } catch (err) { alert('Erro'); btn.disabled = false; }
});
</script>

<style>
.hidden { display: none !important; }
</style>
