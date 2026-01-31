<?php
/**
 * Finance Settings - Vibrant Light Edition v1.0
 * High Contrast, Modern Financial Dashboard
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
        
        --accent-finance: #6366f1; /* Indigo */
        --accent-cyan: #06b6d4;
        --accent-emerald: #10b981;
        --border-color: #cbd5e1;
    }

    body {
        background-color: var(--bg-body) !important;
        font-family: var(--font-primary);
        color: var(--text-dark);
    }

    .finance-wrapper {
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
    }

    .header-content p {
        margin: 6px 0 0;
        color: var(--text-medium);
        font-weight: 500;
        font-size: 1.05rem;
    }

    /* Custom Stats Horizontal */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin-bottom: 32px;
    }

    .stat-card {
        background: white;
        border-radius: 24px;
        border: 2px solid var(--border-color);
        padding: 24px;
        transition: all 0.2s;
        display: flex;
        flex-direction: column;
        gap: 8px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 20px -10px rgba(0,0,0,0.1);
        border-color: #94a3b8;
    }

    .stat-label {
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--text-light);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .stat-value {
        font-size: 1.75rem;
        font-weight: 800;
        color: #000;
    }

    /* Main Connection Section */
    .connection-panel {
        background: white;
        border-radius: 32px;
        border: 2px solid var(--border-color);
        padding: 40px;
        margin-bottom: 32px;
        position: relative;
        overflow: hidden;
    }

    .panel-header {
        display: flex;
        gap: 24px;
        align-items: center;
        margin-bottom: 32px;
    }

    .stripe-logo-box {
        width: 100px;
        height: 100px;
        background: #635bff;
        border-radius: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        box-shadow: 0 10px 20px rgba(99, 91, 255, 0.3);
    }

    .stripe-logo-box svg {
        width: 60px;
        height: auto;
    }

    .status-indicator {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 24px;
        border-radius: 100px;
        font-weight: 700;
        font-size: 0.95rem;
        width: fit-content;
        margin-bottom: 24px;
    }

    .status-ok { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
    .status-warn { background: #fff7ed; color: #c2410c; border: 1px solid #ffedd5; }
    .status-err { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }

    /* Action Buttons */
    .btn-finance {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        padding: 16px 36px;
        border-radius: 100px;
        font-weight: 800;
        font-size: 1.05rem;
        text-decoration: none;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(99, 91, 255, 0.3);
    }

    .btn-connect {
        background: linear-gradient(135deg, #635bff, #00d4ff);
        color: white;
    }

    .btn-connect:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(99, 91, 255, 0.4);
    }

    .btn-history {
        background: white;
        color: var(--text-dark);
        border: 2px solid var(--border-color);
        box-shadow: none;
        padding: 14px 30px;
    }
    .btn-history:hover {
        background: #f8fafc;
        border-color: #94a3b8;
    }

    /* Setup Steps Graphic */
    .steps-graphic {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
        margin-top: 40px;
        position: relative;
    }

    .graphic-step {
        display: flex;
        flex-direction: column;
        gap: 16px;
        padding: 24px;
        background: #f8fafc;
        border-radius: 24px;
        border: 2px solid #e2e8f0;
        transition: all 0.2s;
    }

    .graphic-step.active {
        background: white;
        border-color: var(--accent-emerald);
        box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.1);
    }

    .step-circle {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 900;
        background: #e2e8f0;
        color: var(--text-medium);
    }

    .active .step-circle {
        background: var(--accent-emerald);
        color: white;
    }

    /* Unique Feature Cards */
    .features-section {
        margin-top: 60px;
    }

    .features-header {
        text-align: center;
        margin-bottom: 40px;
    }

    .features-header h2 {
        font-size: 1.5rem;
        font-weight: 800;
        margin: 0;
    }

    .feature-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 24px;
    }

    .memorable-card {
        background: white;
        border-radius: 28px;
        border: 2px solid var(--border-color);
        padding: 32px;
        display: flex;
        flex-direction: column;
        gap: 20px;
        transition: all 0.3s;
    }

    .memorable-card:hover {
        border-color: var(--accent-finance);
        transform: translateY(-8px);
        box-shadow: 0 20px 25px -5px rgba(0,0,0,0.05);
    }

    .card-icon-box {
        width: 60px;
        height: 60px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        background: #f1f5f9;
        transition: all 0.3s;
    }

    .memorable-card:hover .card-icon-box {
        background: var(--accent-finance);
        color: white;
        transform: scale(1.1) rotate(5deg);
    }

    .card-title {
        font-size: 1.15rem;
        font-weight: 800;
        margin: 0;
    }

    .card-text {
        color: var(--text-medium);
        font-size: 0.95rem;
        line-height: 1.5;
        margin: 0;
    }

    /* Flash Messages */
    .flash-box {
        padding: 16px 24px;
        border-radius: 16px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 12px;
        font-weight: 700;
    }

    @media (max-width: 768px) {
        .steps-graphic { grid-template-columns: 1fr; }
        .page-header { flex-direction: column; align-items: flex-start; }
    }
</style>

<div class="finance-wrapper">
    
    <!-- Flash Notifications -->
    <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="flash-box status-ok">
            <span class="material-icons-round">check_circle</span>
            <?= htmlspecialchars($_SESSION['flash_success']) ?>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="flash-box status-err">
            <span class="material-icons-round">error</span>
            <?= htmlspecialchars($_SESSION['flash_error']) ?>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <!-- Header -->
    <div class="page-header">
        <div class="header-content">
            <h1>Financeiro e Pagamentos</h1>
            <p>Gerencie recebimentos de eventos, mensalidades e doações via Stripe</p>
        </div>
        <div class="header-actions">
            <a href="<?= base_url($tenant['slug'] . '/admin/pagamentos/historico') ?>" class="btn-finance btn-history">
                <span class="material-icons-round" style="font-size: 20px;">history</span>
                Ver Histórico
            </a>
        </div>
    </div>

    <!-- Stats Section -->
    <?php if ($settings && $settings['is_connected']): ?>
        <div class="stats-row">
            <div class="stat-card">
                <span class="stat-label">Total Arrecadado</span>
                <span class="stat-value"><?= \App\Services\StripeService::formatAmount($stats['total_revenue']) ?></span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Este Mês</span>
                <span class="stat-value"><?= \App\Services\StripeService::formatAmount($stats['this_month']) ?></span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Pagamentos Pendentes</span>
                <span class="stat-value" style="color: #c2410c;"><?= \App\Services\StripeService::formatAmount($stats['pending']) ?></span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Total Transações</span>
                <span class="stat-value"><?= number_format($stats['transactions']) ?></span>
            </div>
        </div>
    <?php endif; ?>

    <!-- Connection Panel -->
    <div class="connection-panel">
        <div class="panel-header">
            <div class="stripe-logo-box">
                <svg viewBox="0 0 60 25" xmlns="http://www.w3.org/2000/svg">
                    <path fill="currentColor" d="M59.64 14.28h-8.06c.19 1.93 1.6 2.55 3.2 2.55 1.64 0 2.96-.37 4.05-.95v3.32a8.89 8.89 0 0 1-4.56 1.1c-4.01 0-6.83-2.5-6.83-7.48 0-4.19 2.39-7.52 6.3-7.52 3.92 0 5.96 3.28 5.96 7.5 0 .4-.02 1.04-.06 1.48zm-3.67-3.14c0-1.25-.63-2.47-1.97-2.47-1.38 0-2.08 1.22-2.29 2.47h4.26zM40.95 20.3c-1.44 0-2.32-.6-2.9-1.04l-.02 4.63-4.12.87V5.57h3.76l.08 1.02a4.7 4.7 0 0 1 3.23-1.29c2.9 0 5.62 2.6 5.62 7.4 0 5.23-2.7 7.6-5.65 7.6zM40 8.95c-.95 0-1.54.34-1.97.81l.02 6.12c.4.44.98.78 1.95.78 1.52 0 2.54-1.65 2.54-3.87 0-2.15-1.04-3.84-2.54-3.84zM28.24 5.57h4.13v14.44h-4.13V5.57zm0-4.7L32.37 0v3.36l-4.13.88V.87zm-4.32 9.35v9.79H19.8V5.57h3.7l.12 1.22c1-1.77 3.07-1.41 3.62-1.22v3.79c-.52-.17-2.29-.43-3.32.86zm-8.55 4.72c0 2.43 2.6 1.68 3.12 1.46v3.36c-.55.3-1.54.54-2.89.54-3.15 0-4.3-1.93-4.3-4.75V0l4.07-.87v5.89h3.12V8.3h-3.12v6.64zm-8.93-4.22v9.79H2.32V10.3c0-1.41-.78-2-2.02-2-1.33 0-2.26.74-2.83 1.66l.02 10.05H-6.6V5.57h3.62l.13 1.18c1.07-1.42 2.62-1.45 3.02-1.45 1.53 0 2.71.52 3.52 1.55.94-1.15 2.35-1.55 3.56-1.55 2.77 0 4.23 1.72 4.23 4.8z" />
                </svg>
            </div>
            <div>
                <h2 style="font-size: 1.5rem; font-weight: 800; margin: 0;">Conexão Direct Stripe</h2>
                <p style="color: var(--text-medium); margin-top: 4px; font-weight: 600;">Status do Checkout e Repasses</p>
            </div>
        </div>

        <?php if (!$isConfigured): ?>
            <div class="status-indicator status-err">
                <span class="material-icons-round">cloud_off</span>
                ⚠️ Plataforma não configurada
            </div>
            <div style="background: #f8fafc; border: 2px solid #e2e8f0; border-radius: 20px; padding: 32px;">
                <h4 style="margin: 0 0 12px 0; font-weight: 800;">Ação do Desenvolvedor Necessária</h4>
                <p style="color: var(--text-medium); line-height: 1.6;">As chaves de API globais (sk_live, pk_live) precisam ser configuradas no servidor para habilitar pagamentos para qualquer clube.</p>
            </div>
        <?php elseif ($settings && $settings['is_connected']): ?>
            <div class="status-indicator status-ok">
                <span class="material-icons-round">verified_user</span>
                Sua conta Stripe está ativa e configurada
            </div>

            <div class="steps-graphic">
                <div class="graphic-step active">
                    <div class="step-circle">✓</div>
                    <span class="card-title">Conta Criada</span>
                    <span class="card-text">ID: <?= htmlspecialchars($settings['stripe_account_id']) ?></span>
                </div>
                <div class="graphic-step <?= $settings['charges_enabled'] ? 'active' : '' ?>">
                    <div class="step-circle"><?= $settings['charges_enabled'] ? '✓' : '2' ?></div>
                    <span class="card-title">Pagamentos Habilitados</span>
                    <span class="card-text"><?= $settings['charges_enabled'] ? 'Você pode receber valores agora.' : 'Verificação pendente no Stripe.' ?></span>
                </div>
                <div class="graphic-step <?= $settings['payouts_enabled'] ? 'active' : '' ?>">
                    <div class="step-circle"><?= $settings['payouts_enabled'] ? '✓' : '3' ?></div>
                    <span class="card-title">Repasses Automáticos</span>
                    <span class="card-text"><?= $settings['payouts_enabled'] ? 'Saques liberados para sua conta.' : 'Configure seu banco no Stripe.' ?></span>
                </div>
            </div>

            <div style="margin-top: 40px; display: flex; gap: 16px;">
                <?php if (!$settings['charges_enabled'] || !$settings['payouts_enabled']): ?>
                    <a href="<?= base_url($tenant['slug'] . '/admin/pagamentos/conectar') ?>" class="btn-finance btn-connect">
                        <span class="material-icons-round">settings</span>
                        Completar Configuração
                    </a>
                <?php endif; ?>
                <form action="<?= base_url($tenant['slug'] . '/admin/pagamentos/desconectar') ?>" method="POST" onsubmit="return confirm('Desconectar sua conta Stripe?')">
                    <button type="submit" class="btn-finance btn-history" style="color: #dc2626; border-color: #fecaca;">
                        <span class="material-icons-round">link_off</span>
                        Desconectar Conta
                    </button>
                </form>
            </div>

        <?php else: ?>
            <div class="status-indicator status-warn">
                <span class="material-icons-round">warning_amber</span>
                Nenhuma conta conectada
            </div>
            
            <a href="<?= base_url($tenant['slug'] . '/admin/pagamentos/conectar') ?>" class="btn-finance btn-connect" style="margin-bottom: 20px;">
                <span class="material-icons-round">bolt</span>
                Conectar ao Stripe agora
            </a>

            <div class="features-section">
                <div class="features-header">
                    <h2>Libere novos recursos poderosos</h2>
                </div>
                <div class="feature-grid">
                    <div class="memorable-card">
                        <div class="card-icon-box">🎫</div>
                        <h4 class="card-title">Venda de Eventos</h4>
                        <p class="card-text">Inscrições automáticas com pagamento via Cartão, PIX ou Boleto.</p>
                    </div>
                    <div class="memorable-card">
                        <div class="card-icon-box">💰</div>
                        <h4 class="card-title">Mensalidades</h4>
                        <p class="card-text">Cobranças recorrentes automáticas com régua de cobrança.</p>
                    </div>
                    <div class="memorable-card">
                        <div class="card-icon-box">❤️</div>
                        <h4 class="card-title">Doações Externas</h4>
                        <p class="card-text">Receba apoio da comunidade através de links públicos simples.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

</div>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
<?php require BASE_PATH . '/views/admin/partials/footer.php'; ?>
