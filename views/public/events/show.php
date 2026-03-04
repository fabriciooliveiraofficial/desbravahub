<?php
/**
 * Public Event Details View
 */

$isFree = !$event['is_paid'];
$isExternalPayment = $event['is_paid'] && !empty($event['payment_link']);
$isNativeAsaaS = $event['is_paid'] && empty($event['payment_link']); // To be handled later

$canEnroll = ($event['status'] === 'upcoming' || $event['status'] === 'ongoing');
if ($event['max_participants'] > 0 && $enrollmentCount >= $event['max_participants']) {
    $canEnroll = false;
}
if (!empty($event['registration_deadline']) && strtotime($event['registration_deadline']) < time()) {
    $canEnroll = false;
}
?>

<div class="container" style="max-width: 800px; margin: 0 auto; padding: 40px 20px;">
    
    <!-- Top Nav -->
    <a href="<?= base_url($tenant['slug'] . '/eventos') ?>" 
       style="display: inline-flex; align-items: center; gap: 8px; color: var(--text-secondary); text-decoration: none; margin-bottom: 24px; font-weight: 500;">
        <span class="material-icons-round" style="font-size: 20px;">arrow_back</span>
        Voltar para Eventos
    </a>

    <!-- Header Section -->
    <div style="background: white; border-radius: 24px; padding: 40px; box-shadow: 0 4px 24px rgba(0,0,0,0.06); border: 1px solid var(--border-light); margin-bottom: 32px;">
        <div style="display: flex; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
            <?php if ($event['is_paid']): ?>
                <span style="background: #fef3c7; color: #d97706; padding: 6px 16px; border-radius: 20px; font-weight: 600; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 6px;">
                    <span class="material-icons-round" style="font-size: 16px;">stars</span>
                    Evento Especial
                </span>
            <?php else: ?>
                <span style="background: #d1fae5; color: #059669; padding: 6px 16px; border-radius: 20px; font-weight: 600; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 6px;">
                    <span class="material-icons-round" style="font-size: 16px;">local_activity</span>
                    Evento Gratuito
                </span>
            <?php endif; ?>
            
            <span style="background: var(--bg-body); color: var(--text-secondary); padding: 6px 16px; border-radius: 20px; font-weight: 600; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 6px;">
                <span class="material-icons-round" style="font-size: 16px;">auto_awesome</span>
                <?= $event['xp_reward'] ?> XP
            </span>
        </div>

        <h1 style="font-size: 2.25rem; font-weight: 800; color: var(--text-primary); margin-bottom: 24px; line-height: 1.3;">
            <?= htmlspecialchars($event['title']) ?>
        </h1>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 24px; padding: 24px; background: var(--bg-body); border-radius: 16px; margin-bottom: 32px;">
            <div style="display: flex; gap: 12px; align-items: flex-start;">
                <div style="width: 40px; height: 40px; border-radius: 12px; background: white; color: #6366f1; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.05); flex-shrink: 0;">
                    <span class="material-icons-round">calendar_today</span>
                </div>
                <div>
                    <h4 style="margin: 0 0 4px; font-size: 0.875rem; color: var(--text-secondary);">Data e Hora</h4>
                    <p style="margin: 0; font-weight: 600; color: var(--text-primary);">
                        <?= date('d/m/Y', strtotime($event['start_datetime'])) ?><br>
                        <?= date('H:i', strtotime($event['start_datetime'])) ?>
                    </p>
                </div>
            </div>

            <?php if (!empty($event['location'])): ?>
                <div style="display: flex; gap: 12px; align-items: flex-start;">
                    <div style="width: 40px; height: 40px; border-radius: 12px; background: white; color: #ef4444; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.05); flex-shrink: 0;">
                        <span class="material-icons-round">location_on</span>
                    </div>
                    <div>
                        <h4 style="margin: 0 0 4px; font-size: 0.875rem; color: var(--text-secondary);">Local</h4>
                        <p style="margin: 0; font-weight: 600; color: var(--text-primary);">
                            <?= htmlspecialchars($event['location']) ?>
                        </p>
                    </div>
                </div>
            <?php endif; ?>

            <div style="display: flex; gap: 12px; align-items: flex-start;">
                <div style="width: 40px; height: 40px; border-radius: 12px; background: white; color: #f59e0b; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.05); flex-shrink: 0;">
                    <span class="material-icons-round">payments</span>
                </div>
                <div>
                    <h4 style="margin: 0 0 4px; font-size: 0.875rem; color: var(--text-secondary);">Investimento</h4>
                    <p style="margin: 0; font-weight: 600; color: var(--text-primary);">
                        <?php if ($event['is_paid'] && $event['price']): ?>
                            R$ <?= number_format($event['price'], 2, ',', '.') ?>
                        <?php else: ?>
                            Gratuito
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>

        <?php if (!empty($event['description'])): ?>
            <div style="margin-bottom: 32px;">
                <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 16px; color: var(--text-primary);">Sobre o Evento</h3>
                <div style="line-height: 1.6; color: var(--text-secondary); white-space: pre-wrap; font-size: 1.05rem;">
                    <?= htmlspecialchars($event['description']) ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Enrollment Section -->
        <div style="border-top: 1px solid var(--border-light); padding-top: 32px;">
            <?php if (!$user): ?>
                <div style="background: rgba(59, 130, 246, 0.1); border-radius: 16px; padding: 24px; text-align: center; border: 1px solid rgba(59, 130, 246, 0.2);">
                    <h4 style="color: #1e3a8a; margin: 0 0 12px; font-size: 1.125rem;">Faça login para participar</h4>
                    <p style="color: #3b82f6; margin: 0 0 20px;">Você precisa acessar sua conta de membro do clube para se inscrever neste evento.</p>
                    <a href="<?= base_url($tenant['slug'] . '/login') ?>?redirect=<?= urlencode(base_url($tenant['slug'] . '/eventos/' . $event['slug'])) ?>" 
                       style="display: inline-block; background: #2563eb; color: white; padding: 12px 24px; border-radius: 12px; font-weight: 600; text-decoration: none;">
                        Fazer Login
                    </a>
                </div>
            <?php elseif ($enrolled): ?>
                <div style="background: #f0fdf4; border-radius: 16px; padding: 24px; text-align: center; border: 1px solid #bbf7d0; display: flex; flex-direction: column; align-items: center; gap: 12px;">
                    <div style="width: 48px; height: 48px; background: #22c55e; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <span class="material-icons-round" style="font-size: 24px;">check</span>
                    </div>
                    <div>
                        <h4 style="color: #166534; margin: 0 0 4px; font-size: 1.25rem;">Inscrição Confirmada!</h4>
                        <p style="color: #15803d; margin: 0;">Você já está participando deste evento. Nos vemos lá!</p>
                    </div>
                </div>
            <?php elseif (!$canEnroll): ?>
                <div style="background: #fef2f2; border-radius: 16px; padding: 24px; text-align: center; border: 1px solid #fecaca;">
                    <span class="material-icons-round" style="color: #ef4444; font-size: 32px; margin-bottom: 12px;">event_busy</span>
                    <h4 style="color: #991b1b; margin: 0 0 8px; font-size: 1.125rem;">Inscrições Indisponíveis</h4>
                    <p style="color: #b91c1c; margin: 0;">Infelizmente as inscrições para este evento já foram encerradas ou as vagas esgotaram.</p>
                </div>
            <?php else: ?>
                <!-- Action Buttons -->
                <?php if ($isFree): ?>
                    <button id="enroll-btn" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 16px; font-size: 1.125rem;" onclick="enrollFreeEvent()">
                        <span class="material-icons-round">how_to_reg</span> Garanta Sua Vaga
                    </button>
                    <p style="text-align: center; margin: 12px 0 0; font-size: 0.875rem; color: var(--text-muted);">
                        Vagas limitadas. Inscrição 100% gratuita.
                    </p>
                <?php elseif ($isExternalPayment): ?>
                    <!-- External Link Payment -->
                    <a href="<?= htmlspecialchars($event['payment_link']) ?>" target="_blank" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 16px; font-size: 1.125rem; text-decoration: none;">
                        <span class="material-icons-round">open_in_new</span> Comprar Ingresso / Inscrição
                    </a>
                    <p style="text-align: center; margin: 12px 0 0; font-size: 0.875rem; color: var(--text-muted);">
                        Você será redirecionado para a página oficial de pagamento do acampamento/evento seguro.
                    </p>
                <?php elseif ($isNativeAsaaS): ?>
                    <button class="btn btn-primary" style="width: 100%; justify-content: center; padding: 16px; font-size: 1.125rem;" onclick="openAsaasCheckout()">
                        <span class="material-icons-round">shopping_cart_checkout</span> Pagar com Asaas
                    </button>
                    <p style="text-align: center; margin: 12px 0 0; font-size: 0.875rem; color: var(--text-muted);">
                        Checkout seguro via AsaaS integrado ao sistema.
                    </p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    var toast;
    document.addEventListener('DOMContentLoaded', () => {
        toast = window.toast = window.toast || new (window.ToastNotification || ToastNotification)();
    });

    async function enrollFreeEvent() {
        const btn = document.getElementById('enroll-btn');
        btn.disabled = true;
        btn.innerHTML = '<span class="material-icons-round rotate" style="animation: spin 1s linear infinite;">sync</span> Processando...';

        try {
            const formData = new FormData();
            formData.append('_token', '<?= $_SESSION['csrf_token'] ?? '' ?>');

            const response = await fetch('<?= base_url($tenant['slug'] . '/eventos/' . $event['slug'] . '/inscrever') ?>', {
                method: 'POST',
                body: formData,
                headers: { 'Accept': 'application/json' }
            });

            const data = await response.json();

            if (data.success) {
                if (toast) toast.success('Sucesso', data.message);
                setTimeout(() => window.location.reload(), 1500);
            } else {
                if (toast) toast.error('Erro', data.error || 'Falha ao realizar inscrição');
                btn.disabled = false;
                btn.innerHTML = '<span class="material-icons-round">how_to_reg</span> Garanta Sua Vaga';
            }
        } catch (err) {
            console.error(err);
            if (toast) toast.error('Erro', 'Erro de conexão com o servidor');
            btn.disabled = false;
            btn.innerHTML = '<span class="material-icons-round">how_to_reg</span> Garanta Sua Vaga';
        }
    }

    async function openAsaasCheckout() {
        const btn = document.querySelector('button[onclick="openAsaasCheckout()"]');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="material-icons-round rotate" style="animation: spin 1s linear infinite;">sync</span> Gerando Checkout...';

        try {
            const formData = new FormData();
            formData.append('_token', '<?= $_SESSION['csrf_token'] ?? '' ?>');

            const response = await fetch('<?= base_url($tenant['slug'] . '/eventos/' . $event['slug'] . '/checkout-asaas') ?>', {
                method: 'POST',
                body: formData,
                headers: { 'Accept': 'application/json' }
            });

            const data = await response.json();

            if (data.success && data.checkoutUrl) {
                window.location.href = data.checkoutUrl;
            } else {
                if (toast) toast.error('Erro', data.error || 'Falha ao gerar link de pagamento Asaas');
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        } catch (err) {
            console.error(err);
            if (toast) toast.error('Erro', 'Erro de conexão com o servidor');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }
</script>
