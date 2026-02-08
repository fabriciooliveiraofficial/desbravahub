<?php
/**
 * Notifications Page - Deep Glass HUD v3.0
 */
?>
<div class="hud-wrapper">
    
    <!-- HUD Header -->
    <header class="hud-header">
        <div>
            <h1 class="hud-title">NOTIFICAÇÕES</h1>
            <div class="hud-subtitle">Centro de Mensagens e Alertas</div>
        </div>
        
        <?php if (!empty($notifications)): ?>
            <form action="<?= base_url($tenant['slug'] . '/notificacoes/limpar') ?>" method="POST" hx-boost="false" onsubmit="return confirm('Tem certeza que deseja limpar todas as notificações?');">
                <button type="submit" class="hud-btn secondary" style="padding: 10px 20px; border-radius: 100px; font-size: 0.75rem;">
                    <span class="material-icons-round" style="font-size: 18px;">delete_sweep</span>
                    LIMPAR TODAS
                </button>
            </form>
        <?php endif; ?>
    </header>

    <?php if (empty($notifications)): ?>
        <div class="empty-state-hud stagger-1">
            <span class="material-icons-round empty-icon-hud" style="font-size: 5rem;">notifications_off</span>
            <h3 class="hud-section-title">BOX DE ENTRADA VAZIO</h3>
            <div id="push-status-default" style="display: none;">
                <p class="hud-subtitle" style="color: var(--hud-text-dim);">Você está em dia com todas as atualizações.</p>
                <button onclick="enablePushNotifications()" class="hud-btn primary btn-enable-push" style="margin-top: 32px;">
                    <span class="material-icons-round">sensors</span>
                    ATIVAR NOTIFICAÇÕES PUSH
                </button>
            </div>

            <div id="push-status-denied" style="display: none; margin-top: 24px; padding: 20px; border-radius: 16px; background: rgba(239, 68, 68, 0.05); border: 1px solid rgba(239, 68, 68, 0.2);">
                <div style="display: flex; align-items: center; gap: 12px; color: #f87171; justify-content: center; margin-bottom: 12px;">
                    <span class="material-icons-round">block</span>
                    <strong class="tech-font" style="font-size: 0.8rem; letter-spacing: 1px;">ACESSO BLOQUEADO</strong>
                </div>
                <p class="hud-subtitle" style="color: var(--hud-text-dim); font-size: 0.85rem; max-width: 400px; margin: 0 auto;">
                    As notificações estão bloqueadas no seu navegador. Para receber alertas, clique no <strong>ícone de cadeado</strong> na barra de endereços e altere para <strong>Permitir</strong>.
                </p>
            </div>
            
            <script>
                document.addEventListener('DOMContentLoaded', async () => {
                    if (typeof pushNotifications !== 'undefined' && pushNotifications.isSupported()) {
                        const permission = Notification.permission;
                        if (permission === 'default') {
                            document.getElementById('push-status-default').style.display = 'block';
                        } else if (permission === 'denied') {
                            document.getElementById('push-status-denied').style.display = 'block';
                        }
                    }
                });

                async function enablePushNotifications() {
                    const btn = document.querySelector('.btn-enable-push');
                    const originalContent = btn.innerHTML;
                    btn.innerHTML = '<span class="material-icons-round spin">sync</span> Ativando...';
                    btn.disabled = true;

                    try {
                        const sub = await pushNotifications.subscribe('<?= base_url($tenant['slug'] . '/api/push/subscribe') ?>');
                        if (typeof toast !== 'undefined') toast.success('Sucesso', 'Notificações ativadas!');
                        document.getElementById('push-status-default').style.display = 'none';
                    } catch (err) {
                        console.error(err);
                        btn.innerHTML = originalContent;
                        btn.disabled = false;
                        
                        if (err.message === 'Permission denied' || Notification.permission === 'denied') {
                            document.getElementById('push-status-default').style.display = 'none';
                            document.getElementById('push-status-denied').style.display = 'block';
                            if (typeof toast !== 'undefined') toast.warning('Ação Necessária', 'Você precisa permitir as notificações nas configurações do navegador.');
                        } else {
                            if (typeof toast !== 'undefined') toast.error('Erro', 'Não foi possível ativar as notificações no momento.');
                        }
                    }
                }
            </script>
        </div>
    <?php else: ?>
        <div class="hud-grid" style="grid-template-columns: 1fr; max-width: 800px; margin: 0 auto;">
            <?php foreach ($notifications as $idx => $n): ?>
                <?php 
                    $data = json_decode($n['data'] ?? '{}', true);
                    $link = $data['link'] ?? '#';
                    $isUnread = empty($n['read_at']);
                    
                    // Icon logic
                    $icon = 'notifications';
                    $accentClass = 'vibrant-cyan';
                    if (str_contains($n['type'], 'specialty')) {
                        $icon = 'military_tech';
                        $accentClass = 'vibrant-orange';
                    } elseif (str_contains($n['type'], 'complete')) {
                        $icon = 'workspace_premium';
                        $accentClass = 'vibrant-green';
                    } elseif (str_contains($n['type'], 'activity')) {
                        $icon = 'assignment';
                        $accentClass = 'vibrant-cyan';
                    } elseif (str_contains($n['type'], 'event')) {
                        $icon = 'event_available';
                        $accentClass = 'vibrant-pink';
                    }
                ?>
                <a href="<?= $link ?>" class="tech-plate <?= $accentClass ?> stagger-<?= ($idx % 4) + 1 ?>" style="padding: 20px; text-decoration: none;">
                    <?php if ($isUnread): ?>
                        <div class="status-line"></div>
                    <?php endif; ?>
                    
                    <div style="display: flex; gap: 20px; align-items: flex-start;">
                        <div class="hud-logo-icon" style="flex-shrink: 0; width: 48px; height: 48px; border-radius: 12px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1);">
                            <span class="material-icons-round" style="font-size: 24px; opacity: 0.9;"><?= $icon ?></span>
                        </div>
                        
                        <div style="flex: 1;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                <h3 class="plate-title" style="font-size: 1.1rem; margin: 0; color: <?= $isUnread ? '#fff' : 'var(--hud-text-dim)' ?>;">
                                    <?= htmlspecialchars($n['title']) ?>
                                </h3>
                                <div class="tech-font" style="font-size: 0.65rem; color: var(--hud-text-dim); opacity: 0.7; font-weight: 700;">
                                    <?php
                                    $date = new DateTime($n['created_at']);
                                    $now = new DateTime();
                                    $diff = $now->diff($date);
                                    if ($diff->days == 0) {
                                        echo ($diff->h == 0) ? ($diff->i . 'M ATRÁS') : ($diff->h . 'H ATRÁS');
                                    } elseif ($diff->days == 1) {
                                        echo 'ONTEM';
                                    } else {
                                        echo $date->format('d/m/Y');
                                    }
                                    ?>
                                </div>
                            </div>
                            
                            <p style="margin: 8px 0 0; font-size: 0.9rem; line-height: 1.5; color: <?= $isUnread ? 'var(--hud-text-primary)' : 'var(--hud-text-dim)' ?>;">
                                <?= htmlspecialchars($n['message']) ?>
                            </p>
                        </div>
                        
                        <?php if ($isUnread): ?>
                            <div style="width: 8px; height: 8px; background: var(--accent-cyan); border-radius: 50%; margin-top: 8px; box-shadow: 0 0 10px var(--accent-cyan);"></div>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
    /* Small adjustments to ensure HUD feel */
    .hud-wrapper {
        animation: hud-reveal 0.4s ease-out;
    }
    
    .tech-plate {
        border-radius: 20px;
        transition: all 0.3s cubic-bezier(0.2, 0.8, 0.2, 1);
    }
    
    .tech-plate:hover {
        background: rgba(255, 255, 255, 0.05);
        border-color: rgba(255, 255, 255, 0.2);
    }

    .spin {
        animation: fa-spin 2s infinite linear;
    }
    
    @keyframes fa-spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(359deg); }
    }
</style>