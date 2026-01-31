<?php
/**
 * Finance History - Vibrant Light Edition v1.0
 * High Contrast, Clean Table Layout
 */
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

<style>
    :root {
        --font-primary: 'Inter', sans-serif;
        --bg-body: #f1f5f9;
        --bg-card: #ffffff;
        --text-dark: #0f172a;
        --text-medium: #334155;
        --text-light: #475569;
        
        --accent-finance: #6366f1;
        --accent-cyan: #06b6d4;
        --accent-emerald: #10b981;
        --border-color: #cbd5e1;
    }

    body {
        background-color: var(--bg-body) !important;
        font-family: var(--font-primary);
        color: var(--text-dark);
    }

    .history-wrapper {
        max-width: 1200px;
        margin: 0 auto;
        padding-bottom: 60px;
        animation: fadeIn 0.4s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Page Header */
    .page-header {
        margin-bottom: 32px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
    }

    .header-content h1 {
        font-size: 2rem;
        font-weight: 900;
        margin: 0;
        color: #000;
        letter-spacing: -0.025em;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        background: white;
        border: 2px solid var(--border-color);
        border-radius: 100px;
        color: var(--text-dark);
        font-weight: 700;
        text-decoration: none;
        transition: all 0.2s;
    }
    .btn-back:hover {
        background: #f8fafc;
        border-color: #94a3b8;
        transform: translateX(-4px);
    }

    /* Table Container */
    .table-card {
        background: white;
        border-radius: 24px;
        border: 2px solid var(--border-color);
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th {
        background: #f8fafc;
        padding: 16px 24px;
        text-align: left;
        font-size: 0.8rem;
        font-weight: 800;
        color: var(--text-light);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom: 2px solid var(--border-color);
    }

    td {
        padding: 20px 24px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.95rem;
        color: var(--text-dark);
        font-weight: 500;
    }

    tr:hover td {
        background: #fcfdfe;
    }

    /* Status Badges */
    .badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 100px;
        font-size: 0.8rem;
        font-weight: 800;
        text-transform: uppercase;
    }

    .status-completed { background: #dcfce7; color: #15803d; }
    .status-pending { background: #fff7ed; color: #c2410c; }
    .status-failed { background: #fee2e2; color: #b91c1c; }

    .type-pill {
        background: #f1f5f9;
        color: var(--text-light);
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 700;
        border: 1px solid #e2e8f0;
    }

    .amount {
        font-weight: 900;
        color: #000;
        font-family: inherit;
        font-size: 1.05rem;
    }

    /* Empty States */
    .empty-box {
        text-align: center;
        padding: 80px 40px;
    }

    .empty-icon {
        font-size: 4rem;
        margin-bottom: 24px;
        display: block;
    }

</style>

<div class="history-wrapper">
    
    <div class="page-header">
        <div class="header-content">
            <h1><span class="material-icons-round" style="font-size: 32px; color: var(--accent-finance);">receipt_long</span> Histórico de Transações</h1>
        </div>
        <a href="<?= base_url($tenant['slug'] . '/admin/financeiro') ?>" class="btn-back">
            <span class="material-icons-round" style="font-size: 20px;">arrow_back</span>
            Voltar
        </a>
    </div>

    <div class="table-card">
        <?php if (empty($payments)): ?>
            <div class="empty-box">
                <span class="empty-icon">💸</span>
                <h3 style="font-weight: 800; margin: 0 0 8px 0;">Sem movimentações</h3>
                <p style="color: var(--text-medium); margin: 0;">As transações aparecerão aqui assim que forem processadas.</p>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Data e Hora</th>
                        <th>Pagador</th>
                        <th>Categoria</th>
                        <th>Referência</th>
                        <th>Valor Liq.</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 800;"><?= date('d/m/Y', strtotime($payment['created_at'])) ?></div>
                                <div style="font-size: 0.8rem; color: var(--text-light);"><?= date('H:i', strtotime($payment['created_at'])) ?></div>
                            </td>
                            <td>
                                <div style="font-weight: 800;"><?= htmlspecialchars($payment['payer_name'] ?? $payment['payer_user_name'] ?? '-') ?></div>
                                <?php if ($payment['payer_email']): ?>
                                    <div style="font-size: 0.8rem; color: var(--text-light);"><?= htmlspecialchars($payment['payer_email']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $typeLabels = [
                                    'event' => 'Evento',
                                    'membership' => 'Mensalidade',
                                    'donation' => 'Doação',
                                    'material' => 'Material',
                                    'other' => 'Outro',
                                ];
                                ?>
                                <span class="type-pill"><?= $typeLabels[$payment['type']] ?? $payment['type'] ?></span>
                            </td>
                            <td>
                                <div style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= htmlspecialchars($payment['reference_name']) ?>">
                                    <?= htmlspecialchars($payment['reference_name'] ?? '-') ?>
                                </div>
                            </td>
                            <td class="amount">
                                <?= \App\Services\StripeConnect::formatAmount($payment['amount_cents']) ?>
                            </td>
                            <td>
                                <span class="badge status-<?= $payment['status'] ?>">
                                    <?= $payment['status'] === 'completed' ? 'Sucesso' : ($payment['status'] === 'pending' ? 'Processando' : 'Falhou') ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</div>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
<?php require BASE_PATH . '/views/admin/partials/footer.php'; ?>
