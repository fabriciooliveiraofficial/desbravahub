<?php
/**
 * Finance Settings - Master Design v2.0
 * Standard Admin Layout
 */
?>

<!-- Header -->
<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--text-dark); margin: 0;">Financeiro e Pagamentos</h1>
        <p style="color: var(--text-muted); font-size: 0.875rem; margin: 0;">Gerencie recebimentos de eventos, mensalidades e doações via Stripe</p>
    </div>
    <div style="display: flex; gap: 1rem;">
        <a href="<?= base_url($tenant['slug'] . '/admin/pagamentos/historico') ?>" class="btn btn-outline" style="background: white;">
            <span class="material-icons-round">history</span>
            Ver Histórico
        </a>
    </div>
</div>

<!-- Flash Notifications -->
<?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="dashboard-card" style="padding: 1rem; border-left: 4px solid #10b981; margin-bottom: 2rem; flex-direction: row; align-items: center; gap: 0.75rem;">
        <span class="material-icons-round" style="color: #10b981;">check_circle</span>
        <span style="font-weight: 600; color: #15803d;"><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="dashboard-card" style="padding: 1rem; border-left: 4px solid #ef4444; margin-bottom: 2rem; flex-direction: row; align-items: center; gap: 0.75rem;">
        <span class="material-icons-round" style="color: #ef4444;">error</span>
        <span style="font-weight: 600; color: #b91c1c;"><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<!-- Stats Grid -->
<?php if ($settings && $settings['is_connected']): ?>
    <div class="stats-grid">
        <!-- Total Arrecadado -->
        <div class="stat-card green" style="border-left: 4px solid #10b981;">
            <div class="stat-icon">
                <span class="material-icons-round">payments</span>
            </div>
            <div class="stat-content">
                <span class="stat-value"><?= \App\Services\StripeService::formatAmount($stats['total_revenue']) ?></span>
                <span class="stat-label">Total Arrecadado</span>
            </div>
        </div>

        <!-- Este Mês -->
        <div class="stat-card" style="border-left: 4px solid #3b82f6;">
            <div class="stat-icon" style="background-color: #eff6ff; color: #3b82f6;">
                <span class="material-icons-round">calendar_today</span>
            </div>
            <div class="stat-content">
                <span class="stat-value"><?= \App\Services\StripeService::formatAmount($stats['this_month']) ?></span>
                <span class="stat-label">Este Mês</span>
            </div>
        </div>

        <!-- Pendentes -->
        <div class="stat-card amber" style="border-left: 4px solid #f59e0b;">
            <div class="stat-icon">
                <span class="material-icons-round">pending</span>
            </div>
            <div class="stat-content">
                <span class="stat-value"><?= \App\Services\StripeService::formatAmount($stats['pending']) ?></span>
                <span class="stat-label">Pagamentos Pendentes</span>
            </div>
        </div>

        <!-- Transações -->
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
<?php endif; ?>

<!-- Main Connection Panel -->
<div class="dashboard-card">
    <div class="dashboard-card-header">
        <div style="display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; background: #635bff; border-radius: 8px; color: white;">
             <svg viewBox="0 0 60 25" xmlns="http://www.w3.org/2000/svg" style="width: 24px; height: auto;">
                <path fill="currentColor" d="M59.64 14.28h-8.06c.19 1.93 1.6 2.55 3.2 2.55 1.64 0 2.96-.37 4.05-.95v3.32a8.89 8.89 0 0 1-4.56 1.1c-4.01 0-6.83-2.5-6.83-7.48 0-4.19 2.39-7.52 6.3-7.52 3.92 0 5.96 3.28 5.96 7.5 0 .4-.02 1.04-.06 1.48zm-3.67-3.14c0-1.25-.63-2.47-1.97-2.47-1.38 0-2.08 1.22-2.29 2.47h4.26zM40.95 20.3c-1.44 0-2.32-.6-2.9-1.04l-.02 4.63-4.12.87V5.57h3.76l.08 1.02a4.7 4.7 0 0 1 3.23-1.29c2.9 0 5.62 2.6 5.62 7.4 0 5.23-2.7 7.6-5.65 7.6zM40 8.95c-.95 0-1.54.34-1.97.81l.02 6.12c.4.44.98.78 1.95.78 1.52 0 2.54-1.65 2.54-3.87 0-2.15-1.04-3.84-2.54-3.84zM28.24 5.57h4.13v14.44h-4.13V5.57zm0-4.7L32.37 0v3.36l-4.13.88V.87zm-4.32 9.35v9.79H19.8V5.57h3.7l.12 1.22c1-1.77 3.07-1.41 3.62-1.22v3.79c-.52-.17-2.29-.43-3.32.86zm-8.55 4.72c0 2.43 2.6 1.68 3.12 1.46v3.36c-.55.3-1.54.54-2.89.54-3.15 0-4.3-1.93-4.3-4.75V0l4.07-.87v5.89h3.12V8.3h-3.12v6.64zm-8.93-4.22v9.79H2.32V10.3c0-1.41-.78-2-2.02-2-1.33 0-2.26.74-2.83 1.66l.02 10.05H-6.6V5.57h3.62l.13 1.18c1.07-1.42 2.62-1.45 3.02-1.45 1.53 0 2.71.52 3.52 1.55.94-1.15 2.35-1.55 3.56-1.55 2.77 0 4.23 1.72 4.23 4.8z" />
            </svg>
        </div>
        <div>
            <h3>Conexão Direct Stripe</h3>
            <div style="font-size: 0.8rem; color: var(--text-muted);">Status do Checkout e Repasses</div>
        </div>
    </div>

    <div class="dashboard-card-body">
        <?php if (!$isConfigured): ?>
            <div style="display: flex; align-items: center; gap: 0.75rem; color: #dc2626; background: #fef2f2; padding: 1rem; border-radius: var(--radius-lg); border: 1px solid #fecaca; margin-bottom: 2rem;">
                <span class="material-icons-round">cloud_off</span>
                <span style="font-weight: 600;">Plataforma não configurada</span>
            </div>
            
            <div style="background: var(--bg-hover); border-radius: var(--radius-lg); padding: 1.5rem; margin-bottom: 2rem;">
                <h4 style="margin: 0 0 0.5rem 0; font-weight: 700; color: var(--text-dark);">Ação do Desenvolvedor Necessária</h4>
                <p style="color: var(--text-muted); margin: 0; line-height: 1.5;">As chaves de API globais (sk_live, pk_live) precisam ser configuradas no servidor para habilitar pagamentos para qualquer clube.</p>
            </div>

        <?php elseif ($settings && $settings['is_connected']): ?>
            <div style="display: flex; align-items: center; gap: 0.75rem; color: #15803d; background: #dcfce7; padding: 1rem; border-radius: var(--radius-lg); border: 1px solid #bbf7d0; margin-bottom: 2rem; width: fit-content;">
                <span class="material-icons-round">verified_user</span>
                <span style="font-weight: 600;">Sua conta Stripe está ativa e configurada</span>
            </div>

            <!-- Steps Graphic -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                <!-- Step 1 -->
                <div style="background: white; border: 1px solid #10b981; border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.1);">
                    <div style="width: 32px; height: 32px; background: #10b981; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; margin-bottom: 1rem;">✓</div>
                    <div style="font-weight: 700; color: var(--text-dark); margin-bottom: 0.25rem;">Conta Criada</div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);">ID: <?= htmlspecialchars($settings['stripe_account_id']) ?></div>
                </div>

                <!-- Step 2 -->
                <div style="background: <?= $settings['charges_enabled'] ? 'white' : 'var(--bg-hover)' ?>; border: 1px solid <?= $settings['charges_enabled'] ? '#10b981' : 'var(--border-color)' ?>; border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: <?= $settings['charges_enabled'] ? '0 4px 6px -1px rgba(16, 185, 129, 0.1)' : 'none' ?>;">
                    <div style="width: 32px; height: 32px; background: <?= $settings['charges_enabled'] ? '#10b981' : 'var(--border-color)' ?>; color: <?= $settings['charges_enabled'] ? 'white' : 'var(--text-muted)' ?>; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; margin-bottom: 1rem;">
                        <?= $settings['charges_enabled'] ? '✓' : '2' ?>
                    </div>
                    <div style="font-weight: 700; color: var(--text-dark); margin-bottom: 0.25rem;">Pagamentos</div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);"><?= $settings['charges_enabled'] ? 'Você pode receber valores agora.' : 'Verificação pendente no Stripe.' ?></div>
                </div>

                <!-- Step 3 -->
                <div style="background: <?= $settings['payouts_enabled'] ? 'white' : 'var(--bg-hover)' ?>; border: 1px solid <?= $settings['payouts_enabled'] ? '#10b981' : 'var(--border-color)' ?>; border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: <?= $settings['payouts_enabled'] ? '0 4px 6px -1px rgba(16, 185, 129, 0.1)' : 'none' ?>;">
                    <div style="width: 32px; height: 32px; background: <?= $settings['payouts_enabled'] ? '#10b981' : 'var(--border-color)' ?>; color: <?= $settings['payouts_enabled'] ? 'white' : 'var(--text-muted)' ?>; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; margin-bottom: 1rem;">
                        <?= $settings['payouts_enabled'] ? '✓' : '3' ?>
                    </div>
                    <div style="font-weight: 700; color: var(--text-dark); margin-bottom: 0.25rem;">Repasses</div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);"><?= $settings['payouts_enabled'] ? 'Saques liberados para sua conta.' : 'Configure seu banco no Stripe.' ?></div>
                </div>
            </div>

            <div style="display: flex; gap: 1rem; align-items: center;">
                <?php if (!$settings['charges_enabled'] || !$settings['payouts_enabled']): ?>
                    <a href="<?= base_url($tenant['slug'] . '/admin/pagamentos/conectar') ?>" class="btn btn-primary btn-lg">
                        <span class="material-icons-round">settings</span>
                        Completar Configuração
                    </a>
                <?php endif; ?>
                
                <form action="<?= base_url($tenant['slug'] . '/admin/pagamentos/desconectar') ?>" method="POST" onsubmit="return confirm('Desconectar sua conta Stripe?')">
                    <button type="submit" class="btn btn-outline" style="color: #dc2626; border-color: #fecaca; background: #fef2f2;">
                        <span class="material-icons-round">link_off</span>
                        Desconectar Conta
                    </button>
                </form>
            </div>

        <?php else: ?>
            <div style="display: flex; align-items: center; gap: 0.75rem; color: #d97706; background: #fffbeb; padding: 1rem; border-radius: var(--radius-lg); border: 1px solid #fcd34d; margin-bottom: 2rem; width: fit-content;">
                <span class="material-icons-round">warning_amber</span>
                <span style="font-weight: 600;">Nenhuma conta conectada</span>
            </div>
            
            <a href="<?= base_url($tenant['slug'] . '/admin/pagamentos/conectar') ?>" class="btn btn-primary btn-lg" style="margin-bottom: 3rem; background: linear-gradient(135deg, #635bff, #00d4ff); border: none;">
                <span class="material-icons-round">bolt</span>
                Conectar ao Stripe agora
            </a>

            <div>
                <h3 style="text-align: center; margin-bottom: 2rem; font-weight: 800; color: var(--text-dark);">Libere novos recursos poderosos</h3>
                
                <div class="dashboard-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
                    <div class="dashboard-card" style="align-items: center; text-align: center; padding: 2rem;">
                        <div style="width: 60px; height: 60px; background: rgba(6, 182, 212, 0.1); border-radius: 16px; display: flex; align-items: center; justify-content: center; color: var(--primary); margin-bottom: 1rem;">
                            <span class="material-icons-round" style="font-size: 2rem;">local_activity</span>
                        </div>
                        <h4 style="margin: 0 0 0.5rem 0; font-weight: 700; color: var(--text-dark);">Venda de Eventos</h4>
                        <p style="color: var(--text-muted); margin: 0;">Inscrições automáticas com pagamento via Cartão, PIX ou Boleto.</p>
                    </div>

                    <div class="dashboard-card" style="align-items: center; text-align: center; padding: 2rem;">
                         <div style="width: 60px; height: 60px; background: rgba(245, 158, 11, 0.1); border-radius: 16px; display: flex; align-items: center; justify-content: center; color: #d97706; margin-bottom: 1rem;">
                            <span class="material-icons-round" style="font-size: 2rem;">savings</span>
                        </div>
                        <h4 style="margin: 0 0 0.5rem 0; font-weight: 700; color: var(--text-dark);">Mensalidades</h4>
                        <p style="color: var(--text-muted); margin: 0;">Cobranças recorrentes automáticas com régua de cobrança.</p>
                    </div>

                    <div class="dashboard-card" style="align-items: center; text-align: center; padding: 2rem;">
                         <div style="width: 60px; height: 60px; background: rgba(236, 72, 153, 0.1); border-radius: 16px; display: flex; align-items: center; justify-content: center; color: #db2777; margin-bottom: 1rem;">
                            <span class="material-icons-round" style="font-size: 2rem;">volunteer_activism</span>
                        </div>
                        <h4 style="margin: 0 0 0.5rem 0; font-weight: 700; color: var(--text-dark);">Doações Externas</h4>
                        <p style="color: var(--text-muted); margin: 0;">Receba apoio da comunidade através de links públicos simples.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>


