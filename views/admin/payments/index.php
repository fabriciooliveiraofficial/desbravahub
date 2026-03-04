<?php
/**
 * Asaas Payments Dashboard
 * Main financial management page for clubs
 */

$isConnected = $settings && $settings['is_connected'];
$formatMoney = function($cents) {
    return 'R$ ' . number_format($cents / 100, 2, ',', '.');
};
?>

<!-- Header -->
<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--text-dark); margin: 0;">Financeiro e Pagamentos</h1>
        <p style="color: var(--text-muted); font-size: 0.875rem; margin: 0;">Gerencie cobranças, mensalidades e loja do clube via Asaas</p>
    </div>
    <?php if ($isConnected): ?>
    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
        <a href="<?= base_url($tenant['slug'] . '/admin/pagamentos/mensalidades') ?>" class="btn btn-outline" style="background: white;">
            <span class="material-icons-round">autorenew</span>
            Mensalidades
        </a>
        <a href="<?= base_url($tenant['slug'] . '/admin/pagamentos/loja') ?>" class="btn btn-outline" style="background: white;">
            <span class="material-icons-round">storefront</span>
            Loja
        </a>
    </div>
    <?php endif; ?>
</div>

<!-- Stats Grid -->
<?php if ($isConnected): ?>
<div class="stats-grid">
    <!-- Total Revenue -->
    <div class="stat-card green" style="border-left: 4px solid #10b981;">
        <div class="stat-icon">
            <span class="material-icons-round">payments</span>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= $formatMoney($stats['total_revenue']) ?></span>
            <span class="stat-label">Total Arrecadado</span>
        </div>
    </div>

    <!-- This Month -->
    <div class="stat-card" style="border-left: 4px solid #3b82f6;">
        <div class="stat-icon" style="background-color: #eff6ff; color: #3b82f6;">
            <span class="material-icons-round">calendar_today</span>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= $formatMoney($stats['this_month']) ?></span>
            <span class="stat-label">Este Mês</span>
        </div>
    </div>

    <!-- Pending -->
    <div class="stat-card amber" style="border-left: 4px solid #f59e0b;">
        <div class="stat-icon">
            <span class="material-icons-round">pending</span>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= $formatMoney($stats['pending']) ?></span>
            <span class="stat-label">Pendentes</span>
        </div>
    </div>

    <!-- Transactions -->
    <div class="stat-card purple" style="border-left: 4px solid #a855f7;">
        <div class="stat-icon">
            <span class="material-icons-round">receipt_long</span>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= number_format($stats['transactions']) ?></span>
            <span class="stat-label">Total Transações</span>
        </div>
    </div>
</div>

<!-- Asaas Balance (if available) -->
<?php if ($balance && !isset($balance['error'])): ?>
<div class="dashboard-card" style="margin-bottom: 1.5rem;">
    <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem;">
        <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #10b981, #059669); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white;">
            <span class="material-icons-round">account_balance_wallet</span>
        </div>
        <div>
            <div style="font-weight: 700; font-size: 1.25rem; color: var(--text-dark);">
                R$ <?= number_format($balance['balance'] ?? 0, 2, ',', '.') ?>
            </div>
            <div style="font-size: 0.8rem; color: var(--text-muted);">Saldo Disponível no Asaas</div>
        </div>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>

<!-- Connection Panel -->
<div class="dashboard-card" style="margin-bottom: 1.5rem;">
    <div class="dashboard-card-header">
        <div style="display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; background: linear-gradient(135deg, #0ea5e9, #2563eb); border-radius: 8px; color: white;">
            <span class="material-icons-round">bolt</span>
        </div>
        <div>
            <h3>Conexão Asaas</h3>
            <div style="font-size: 0.8rem; color: var(--text-muted);">Gateway de pagamentos integrado</div>
        </div>
    </div>

    <div class="dashboard-card-body">
        <?php if ($isConnected): ?>
            <!-- Connected State -->
            <div style="display: flex; align-items: center; gap: 0.75rem; color: #15803d; background: #dcfce7; padding: 1rem; border-radius: var(--radius-lg); border: 1px solid #bbf7d0; margin-bottom: 1.5rem; width: fit-content;">
                <span class="material-icons-round">verified_user</span>
                <span style="font-weight: 600;">Conta Asaas conectada e ativa</span>
                <span style="font-size: 0.7rem; background: #15803d; color: white; padding: 2px 8px; border-radius: 4px; text-transform: uppercase;">
                    <?= $settings['environment'] === 'production' ? 'Produção' : 'Sandbox' ?>
                </span>
            </div>

            <!-- Payment Methods Status -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                <div style="background: <?= $settings['credit_card_enabled'] ? '#f0fdf4' : '#fef2f2' ?>; border: 1px solid <?= $settings['credit_card_enabled'] ? '#bbf7d0' : '#fecaca' ?>; border-radius: 12px; padding: 1rem; text-align: center;">
                    <span class="material-icons-round" style="color: <?= $settings['credit_card_enabled'] ? '#10b981' : '#ef4444' ?>;">credit_card</span>
                    <div style="font-size: 0.8rem; font-weight: 600; margin-top: 4px;">Cartão</div>
                    <div style="font-size: 0.65rem; color: var(--text-muted);">
                        Até <?= $settings['max_installments'] ?>x
                    </div>
                </div>
                <div style="background: <?= $settings['pix_enabled'] ? '#f0fdf4' : '#fef2f2' ?>; border: 1px solid <?= $settings['pix_enabled'] ? '#bbf7d0' : '#fecaca' ?>; border-radius: 12px; padding: 1rem; text-align: center;">
                    <span class="material-icons-round" style="color: <?= $settings['pix_enabled'] ? '#10b981' : '#ef4444' ?>;">qr_code</span>
                    <div style="font-size: 0.8rem; font-weight: 600; margin-top: 4px;">PIX</div>
                    <div style="font-size: 0.65rem; color: var(--text-muted);">Instantâneo</div>
                </div>
                <div style="background: <?= $settings['boleto_enabled'] ? '#f0fdf4' : '#fef2f2' ?>; border: 1px solid <?= $settings['boleto_enabled'] ? '#bbf7d0' : '#fecaca' ?>; border-radius: 12px; padding: 1rem; text-align: center;">
                    <span class="material-icons-round" style="color: <?= $settings['boleto_enabled'] ? '#10b981' : '#ef4444' ?>;">description</span>
                    <div style="font-size: 0.8rem; font-weight: 600; margin-top: 4px;">Boleto</div>
                    <div style="font-size: 0.65rem; color: var(--text-muted);">2-3 dias úteis</div>
                </div>
            </div>

            <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                <button onclick="document.getElementById('settings-panel').classList.toggle('hidden')" class="btn btn-outline" style="background: white;">
                    <span class="material-icons-round">settings</span>
                    Configurações
                </button>
                <button onclick="if(confirm('Desconectar sua conta Asaas?')) disconnectAsaas()" class="btn btn-outline" style="color: #dc2626; border-color: #fecaca; background: #fef2f2;">
                    <span class="material-icons-round">link_off</span>
                    Desconectar
                </button>
            </div>

            <!-- Settings Panel (Hidden by default) -->
            <div id="settings-panel" class="hidden" style="margin-top: 1.5rem; padding: 1.5rem; background: var(--bg-hover); border-radius: var(--radius-lg);">
                <h4 style="margin: 0 0 1rem 0; font-weight: 700;">Configurações de Pagamento</h4>
                <form id="settings-form" style="display: grid; gap: 1rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="credit_card_enabled" <?= $settings['credit_card_enabled'] ? 'checked' : '' ?>>
                        <span>Cartão de Crédito</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="pix_enabled" <?= $settings['pix_enabled'] ? 'checked' : '' ?>>
                        <span>PIX</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="boleto_enabled" <?= $settings['boleto_enabled'] ? 'checked' : '' ?>>
                        <span>Boleto Bancário</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="installment_enabled" <?= $settings['installment_enabled'] ? 'checked' : '' ?>>
                        <span>Parcelamento</span>
                    </label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div>
                            <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 4px;">Máximo de Parcelas</label>
                            <select name="max_installments" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 8px; background: white;">
                                <?php for ($i = 2; $i <= 21; $i++): ?>
                                    <option value="<?= $i ?>" <?= ($settings['max_installments'] ?? 12) == $i ? 'selected' : '' ?>><?= $i ?>x</option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 4px;">Taxa Juros Mensal (%)</label>
                            <input type="number" name="installment_interest_rate" step="0.01" min="0" max="10"
                                value="<?= $settings['installment_interest_rate'] ?? 0 ?>"
                                style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 8px;">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: fit-content;">
                        <span class="material-icons-round">save</span>
                        Salvar Configurações
                    </button>
                </form>
            </div>

        <?php else: ?>
            <!-- Not Connected State -->
            <div style="display: flex; align-items: center; gap: 0.75rem; color: #d97706; background: #fffbeb; padding: 1rem; border-radius: var(--radius-lg); border: 1px solid #fcd34d; margin-bottom: 1.5rem; width: fit-content;">
                <span class="material-icons-round">warning_amber</span>
                <span style="font-weight: 600;">Nenhuma conta de pagamento conectada</span>
            </div>

            <!-- Connect Form -->
            <div style="background: var(--bg-hover); border-radius: var(--radius-lg); padding: 1.5rem; margin-bottom: 2rem;">
                <h4 style="margin: 0 0 1rem 0; font-weight: 700;">Conectar conta Asaas</h4>
                <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 1rem;">
                    Crie uma conta gratuita em <a href="https://www.asaas.com" target="_blank" style="color: #0ea5e9; font-weight: 600;">asaas.com</a> 
                    e insira sua API Key abaixo para começar a receber pagamentos.
                </p>
                <form id="connect-form" style="display: grid; gap: 1rem; max-width: 500px;">
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 4px;">Ambiente</label>
                        <select name="environment" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 8px; background: white;">
                            <option value="sandbox">Sandbox (Teste)</option>
                            <option value="production">Produção</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 4px;">API Key Asaas</label>
                        <input type="password" name="asaas_api_key" placeholder="$aact_..." required
                            style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 8px;">
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg" style="background: linear-gradient(135deg, #0ea5e9, #2563eb); border: none; width: fit-content;">
                        <span class="material-icons-round">bolt</span>
                        Conectar ao Asaas
                    </button>
                </form>
            </div>

            <!-- Features Preview -->
            <h3 style="text-align: center; margin-bottom: 1.5rem; font-weight: 800; color: var(--text-dark);">Libere recursos poderosos</h3>
            <div class="dashboard-grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));">
                <div class="dashboard-card" style="align-items: center; text-align: center; padding: 2rem;">
                    <div style="width: 60px; height: 60px; background: rgba(6, 182, 212, 0.1); border-radius: 16px; display: flex; align-items: center; justify-content: center; color: #0ea5e9; margin-bottom: 1rem;">
                        <span class="material-icons-round" style="font-size: 2rem;">credit_card</span>
                    </div>
                    <h4 style="margin: 0 0 0.5rem 0; font-weight: 700;">Cartão até 21x</h4>
                    <p style="color: var(--text-muted); margin: 0; font-size: 0.85rem;">Parcelamento no cartão com juros por conta do comprador.</p>
                </div>

                <div class="dashboard-card" style="align-items: center; text-align: center; padding: 2rem;">
                    <div style="width: 60px; height: 60px; background: rgba(245, 158, 11, 0.1); border-radius: 16px; display: flex; align-items: center; justify-content: center; color: #d97706; margin-bottom: 1rem;">
                        <span class="material-icons-round" style="font-size: 2rem;">autorenew</span>
                    </div>
                    <h4 style="margin: 0 0 0.5rem 0; font-weight: 700;">Mensalidades</h4>
                    <p style="color: var(--text-muted); margin: 0; font-size: 0.85rem;">Cobranças recorrentes automáticas para seus membros.</p>
                </div>

                <div class="dashboard-card" style="align-items: center; text-align: center; padding: 2rem;">
                    <div style="width: 60px; height: 60px; background: rgba(236, 72, 153, 0.1); border-radius: 16px; display: flex; align-items: center; justify-content: center; color: #db2777; margin-bottom: 1rem;">
                        <span class="material-icons-round" style="font-size: 2rem;">storefront</span>
                    </div>
                    <h4 style="margin: 0 0 0.5rem 0; font-weight: 700;">Loja do Clube</h4>
                    <p style="color: var(--text-muted); margin: 0; font-size: 0.85rem;">Venda uniformes, insígnias e lenços online.</p>
                </div>

                <div class="dashboard-card" style="align-items: center; text-align: center; padding: 2rem;">
                    <div style="width: 60px; height: 60px; background: rgba(99, 102, 241, 0.1); border-radius: 16px; display: flex; align-items: center; justify-content: center; color: #6366f1; margin-bottom: 1rem;">
                        <span class="material-icons-round" style="font-size: 2rem;">local_activity</span>
                    </div>
                    <h4 style="margin: 0 0 0.5rem 0; font-weight: 700;">Eventos Pagos</h4>
                    <p style="color: var(--text-muted); margin: 0; font-size: 0.85rem;">Taxa de inscrição automática para acampamentos e eventos.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Recent Payments Table -->
<?php if ($isConnected && !empty($recentPayments)): ?>
<div class="dashboard-card">
    <div class="dashboard-card-header">
        <span class="material-icons-round" style="color: #3b82f6;">receipt_long</span>
        <h3>Pagamentos Recentes</h3>
    </div>
    <div class="dashboard-card-body" style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border-color); text-align: left;">
                    <th style="padding: 0.75rem 0.5rem; color: var(--text-muted); font-weight: 600;">Pagador</th>
                    <th style="padding: 0.75rem 0.5rem; color: var(--text-muted); font-weight: 600;">Descrição</th>
                    <th style="padding: 0.75rem 0.5rem; color: var(--text-muted); font-weight: 600;">Valor</th>
                    <th style="padding: 0.75rem 0.5rem; color: var(--text-muted); font-weight: 600;">Método</th>
                    <th style="padding: 0.75rem 0.5rem; color: var(--text-muted); font-weight: 600;">Status</th>
                    <th style="padding: 0.75rem 0.5rem; color: var(--text-muted); font-weight: 600;">Data</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentPayments as $payment): ?>
                <?php
                    $statusColors = [
                        'PENDING' => ['#f59e0b', '#fffbeb', 'Pendente'],
                        'CONFIRMED' => ['#10b981', '#f0fdf4', 'Confirmado'],
                        'RECEIVED' => ['#10b981', '#f0fdf4', 'Recebido'],
                        'OVERDUE' => ['#ef4444', '#fef2f2', 'Vencido'],
                        'REFUNDED' => ['#6366f1', '#eef2ff', 'Reembolsado'],
                    ];
                    $sc = $statusColors[$payment['status']] ?? ['#94a3b8', '#f8fafc', $payment['status']];
                ?>
                <tr style="border-bottom: 1px solid var(--border-color);">
                    <td style="padding: 0.75rem 0.5rem; font-weight: 600;"><?= htmlspecialchars($payment['payer_name'] ?? 'Desconhecido') ?></td>
                    <td style="padding: 0.75rem 0.5rem; color: var(--text-muted);"><?= htmlspecialchars($payment['description'] ?? '-') ?></td>
                    <td style="padding: 0.75rem 0.5rem; font-weight: 700;"><?= $formatMoney($payment['amount_cents']) ?></td>
                    <td style="padding: 0.75rem 0.5rem;">
                        <?php
                        $methodIcons = ['CREDIT_CARD' => 'credit_card', 'PIX' => 'qr_code', 'BOLETO' => 'description'];
                        $icon = $methodIcons[$payment['billing_type']] ?? 'payments';
                        ?>
                        <span class="material-icons-round" style="font-size: 1rem; vertical-align: middle;"><?= $icon ?></span>
                        <?= $payment['billing_type'] ?? '-' ?>
                    </td>
                    <td style="padding: 0.75rem 0.5rem;">
                        <span style="background: <?= $sc[1] ?>; color: <?= $sc[0] ?>; padding: 2px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 600;">
                            <?= $sc[2] ?>
                        </span>
                    </td>
                    <td style="padding: 0.75rem 0.5rem; color: var(--text-muted);">
                        <?= date('d/m/Y', strtotime($payment['created_at'])) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<script>
// Connect Form
document.getElementById('connect-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('button[type="submit"]');
    const original = btn.innerHTML;
    btn.innerHTML = '<span class="material-icons-round spin">sync</span> Conectando...';
    btn.disabled = true;

    try {
        const formData = new FormData(this);
        const resp = await fetch('<?= base_url($tenant['slug'] . '/admin/pagamentos/conectar') ?>', {
            method: 'POST',
            body: formData
        });
        const data = await resp.json();

        if (data.success) {
            if (typeof showToast !== 'undefined') showToast(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            if (typeof showToast !== 'undefined') showToast(data.error, 'error');
            else alert(data.error);
            btn.innerHTML = original;
            btn.disabled = false;
        }
    } catch (err) {
        alert('Erro de conexão');
        btn.innerHTML = original;
        btn.disabled = false;
    }
});

// Settings Form
document.getElementById('settings-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    try {
        const resp = await fetch('<?= base_url($tenant['slug'] . '/admin/pagamentos/configuracoes') ?>', {
            method: 'POST',
            body: formData
        });
        const data = await resp.json();
        if (data.success) {
            if (typeof showToast !== 'undefined') showToast(data.message, 'success');
            else alert(data.message);
        } else {
            if (typeof showToast !== 'undefined') showToast(data.error, 'error');
        }
    } catch (err) {
        alert('Erro ao salvar');
    }
});

// Disconnect
async function disconnectAsaas() {
    try {
        const resp = await fetch('<?= base_url($tenant['slug'] . '/admin/pagamentos/desconectar') ?>', { method: 'POST' });
        const data = await resp.json();
        if (data.success) {
            if (typeof showToast !== 'undefined') showToast(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        }
    } catch (err) {
        alert('Erro ao desconectar');
    }
}
</script>

<style>
.hidden { display: none !important; }
.spin { animation: spin 1s linear infinite; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>
