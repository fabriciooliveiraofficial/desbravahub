<?php
/**
 * Club Profile & Growth Settings View
 */
?>
<div class="dashboard-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <div>
        <h2 style="margin: 0; color: var(--text-primary); font-size: 1.5rem; display: flex; align-items: center; gap: 8px;">
            <span class="material-icons-round" style="color: #ec4899;">storefront</span>
            Perfil do Clube & Crescimento
        </h2>
        <p style="margin: 4px 0 0 0; color: var(--text-secondary); font-size: 0.95rem;">
            Gerencie a página pública do seu clube e ferramentas de atração.
        </p>
    </div>
    <div style="display: flex; gap: 12px;">
        <a href="<?= base_url('c/' . ($profile['slug'] ?? '')) ?>" target="_blank" class="btn btn-secondary" <?= empty($profile['slug']) ? 'style="display:none;"' : '' ?>>
            <span class="material-icons-round">open_in_new</span> Ver Página Pública
        </a>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
    <!-- Profile Form -->
    <div class="dashboard-card">
        <header class="dashboard-card-header">
            <span class="material-icons-round" style="color: #3b82f6;">edit_document</span>
            <h3>Dados Públicos</h3>
        </header>
        <div class="dashboard-card-body">
            <form id="profile-form">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label>Nome de Exibição *</label>
                        <input type="text" name="display_name" class="form-control" required value="<?= htmlspecialchars($profile['display_name'] ?? '') ?>" placeholder="Ex: Clube Leão do Norte">
                    </div>
                    <div class="form-group">
                        <label>Slug da URL *</label>
                        <input type="text" name="slug" class="form-control" required value="<?= htmlspecialchars($profile['slug'] ?? '') ?>" placeholder="Ex: leao-do-norte">
                        <small style="color: var(--text-secondary); font-size: 0.8rem;">https://desbravahub.app/c/<strong>slug</strong></small>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label>URL do Logo</label>
                        <input type="url" name="logo_url" class="form-control" value="<?= htmlspecialchars($profile['logo_url'] ?? '') ?>" placeholder="https://exemplo.com/logo.png">
                    </div>
                    <div class="form-group">
                        <label>URL da Capa (Banner)</label>
                        <input type="url" name="cover_image_url" class="form-control" value="<?= htmlspecialchars($profile['cover_image_url'] ?? '') ?>" placeholder="https://exemplo.com/capa.jpg">
                    </div>
                </div>

                <div class="form-group">
                    <label>Endereço das Reuniões</label>
                    <input type="text" name="meeting_address" class="form-control" value="<?= htmlspecialchars($profile['meeting_address'] ?? '') ?>" placeholder="Rua 1, Bairro Centro - Igreja Central">
                </div>

                <div class="form-group">
                    <label>Horário das Reuniões</label>
                    <input type="text" name="meeting_time" class="form-control" value="<?= htmlspecialchars($profile['meeting_time'] ?? '') ?>" placeholder="Domingos às 09:00">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label>Instagram do Clube (Ex: @clube)</label>
                        <input type="text" name="social_instagram" class="form-control" value="<?= htmlspecialchars($profile['social_instagram'] ?? '') ?>" placeholder="@seuclube">
                    </div>
                    <div class="form-group">
                        <label>Link Grupo WhatsApp (Dúvidas/Membros)</label>
                        <input type="url" name="social_whatsapp_group" class="form-control" value="<?= htmlspecialchars($profile['social_whatsapp_group'] ?? '') ?>" placeholder="https://chat.whatsapp.com/...">
                    </div>
                </div>

                <div class="form-group">
                    <label>Mensagem de Boas-Vindas (Sobre Nós)</label>
                    <textarea name="welcome_message" class="form-control" rows="4"><?= htmlspecialchars($profile['welcome_message'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label>Meta Description (SEO)</label>
                    <input type="text" name="seo_meta_description" class="form-control" value="<?= htmlspecialchars($profile['seo_meta_description'] ?? '') ?>" maxlength="160" placeholder="Descrição curta para o Google (máx 160 char)">
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 16px;">
                    <span class="material-icons-round">save</span> Salvar Perfil
                </button>
            </form>
        </div>
    </div>

    <!-- Growth & QR Code Area -->
    <div style="display: flex; flex-direction: column; gap: 24px;">
        <div class="dashboard-card">
            <header class="dashboard-card-header">
                <span class="material-icons-round" style="color: #10b981;">qr_code_2</span>
                <h3>QR Code (Offline Growth)</h3>
            </header>
            <div class="dashboard-card-body" style="text-align: center;">
                <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 16px;">
                    Use este QR Code em panfletos, cartazes e eventos da igreja para captação de membros.
                </p>
                
                <div id="qr-code-container" style="background: white; padding: 16px; border-radius: 8px; display: inline-block; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); margin-bottom: 16px; min-width: 200px; min-height: 200px; display: flex; align-items: center; justify-content: center;">
                    <?php if (!empty($growth['qr_code_path'])): ?>
                        <img src="<?= base_url($growth['qr_code_path']) ?>" alt="QR Code do Clube" style="max-width: 100%; height: auto; border-radius: 4px;">
                    <?php else: ?>
                        <span style="color: #888; font-size: 0.9rem;">QR Code não gerado</span>
                    <?php endif; ?>
                </div>

                <button type="button" class="btn btn-secondary" onclick="generateQr()" style="width: 100%;">
                    <span class="material-icons-round">autorenew</span> 
                    <?= empty($growth['qr_code_path']) ? 'Gerar QR Code' : 'Regerar QR Code' ?>
                </button>
            </div>
        </div>

        <div class="dashboard-card">
            <header class="dashboard-card-header">
                <span class="material-icons-round" style="color: #8b5cf6;">insights</span>
                <h3>Métricas de Crescimento</h3>
            </header>
            <div class="dashboard-card-body">
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid var(--border-light);">
                    <span>Acessos via QR Code:</span>
                    <strong style="color: #10b981; font-size: 1.2rem;"><?= number_format($growth['visits_count'] ?? 0) ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0;">
                    <span>Origem da Campanha:</span>
                    <strong style="color: var(--text-primary);"><?= htmlspecialchars($growth['campaign_source'] ?? 'N/A') ?></strong>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var toast;
    
    document.addEventListener('DOMContentLoaded', () => {
        toast = window.toast = window.toast || new (window.ToastNotification || ToastNotification)();

        document.getElementById('profile-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const btn = e.target.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="material-icons-round rotate">sync</span> Salvando...';
            btn.disabled = true;

            const formData = new FormData(e.target);

            try {
                const response = await fetch('<?= base_url($tenant['slug'] . '/admin/perfil-clube') ?>', {
                    method: 'POST',
                    body: formData,
                    headers: { 'Accept': 'application/json' }
                });

                const data = await response.json();

                if (data.success) {
                    toast.success('Sucesso', data.message);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    toast.error('Erro', data.error || 'Erro ao salvar perfil');
                }
            } catch (err) {
                console.error(err);
                if (toast) toast.error('Erro', 'Erro de conexão com o servidor');
                else alert('Erro de conexão com o servidor');
            } finally {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        });
    });

    async function generateQr() {
        if (!toast) toast = window.toast || new (window.ToastNotification || ToastNotification)();
        
        try {
            toast.info('Aguarde', 'Gerando QR Code...');
            
            const response = await fetch('<?= base_url($tenant['slug'] . '/admin/perfil-clube/qrcode') ?>', {
                method: 'POST',
                headers: { 'Accept': 'application/json' }
            });

            const data = await response.json();

            if (data.success) {
                toast.success('Sucesso', data.message);
                const container = document.getElementById('qr-code-container');
                container.innerHTML = `<img src="${window.location.origin}${data.path}" alt="QR Code do Clube" style="max-width: 100%; height: auto; border-radius: 4px;">`;
            } else {
                toast.error('Erro', data.error || 'Erro ao gerar QR Code');
            }
        } catch (err) {
            console.error(err);
            if (toast) toast.error('Erro', 'Erro de conexão.');
            else alert('Erro de conexão');
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
