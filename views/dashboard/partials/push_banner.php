<?php
/**
 * Push Notification Opt-in Banner
 */
?>
<div id="push-banner" style="display: none; position: fixed; bottom: 120px; left: 16px; right: 16px; background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.5); border-radius: 20px; padding: 20px; z-index: 10000; box-shadow: 0 15px 35px rgba(0,0,0,0.1); animation: slideUpBanner 0.6s cubic-bezier(0.16, 1, 0.3, 1);">
    <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 16px;">
        <div style="width: 44px; height: 44px; background: linear-gradient(135deg, #6366f1, #a855f7); border-radius: 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);">
            <span class="material-icons-round" style="color: #fff; font-size: 22px;">notifications_active</span>
        </div>
        <div>
            <h4 style="color: #1e293b; font-size: 16px; font-weight: 700; margin-bottom: 2px;">Ativar Notificações?</h4>
            <p style="color: #64748b; font-size: 13px; line-height: 1.4;">Receba alertas de missões e conquistas em tempo real.</p>
        </div>
    </div>
    <div style="display: flex; gap: 10px;">
        <button id="enable-push-btn" style="flex: 1; background: #6366f1; color: #fff; border: none; padding: 12px; border-radius: 12px; font-weight: 600; font-size: 14px; cursor: pointer; transition: transform 0.2s; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);">
            Ativar Agora
        </button>
        <button id="close-push-banner" style="background: #f1f5f9; color: #64748b; border: none; padding: 12px 18px; border-radius: 12px; font-weight: 600; font-size: 14px; cursor: pointer;">
            Depois
        </button>
    </div>
</div>

<style>
@keyframes slideUpBanner {
    from { transform: translateY(100px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    const banner = document.getElementById('push-banner');
    const enableBtn = document.getElementById('enable-push-btn');
    const closeBtn = document.getElementById('close-push-banner');

    if (!pushNotifications.isSupported()) return;

    // Check if user has already dismissed the banner in this session
    if (sessionStorage.getItem('push_banner_dismissed')) return;

    // Wait a bit to show the banner
    setTimeout(async () => {
        if (typeof pushNotifications === 'undefined' || !pushNotifications.getPermissionStatus) return;
        
        const status = pushNotifications.getPermissionStatus();
        if (status === 'default') {
            banner.style.display = 'block';
        }
    }, 3000);

    closeBtn.addEventListener('click', () => {
        banner.style.display = 'none';
        sessionStorage.setItem('push_banner_dismissed', 'true');
    });

    enableBtn.addEventListener('click', async () => {
        enableBtn.disabled = true;
        enableBtn.innerText = 'Ativando...';
        
        try {
            const apiEndpoint = '<?= base_url("{$tenant['slug']}/api/push/subscribe") ?>';
            await pushNotifications.subscribe(apiEndpoint);
            
            banner.style.display = 'none';
            if (window.toast) {
                 toast.show('Sucesso!', 'Você receberá notificações agora.', 'success');
            }
        } catch (err) {
            console.error(err);
            enableBtn.disabled = false;
            enableBtn.innerText = 'Ativar Agora';
            if (window.toast) {
                toast.show('Erro', 'Não foi possível ativar as notificações.', 'error');
            }
        }
    });
});
</script>
