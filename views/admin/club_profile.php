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

                <div class="dashboard-card-header" style="margin-top: 32px; padding-left: 0; padding-right: 0;">
                    <span class="material-icons-round" style="color: #f59e0b;">local_fire_department</span>
                    <h3 style="margin:0;">Identidade e Event Hub</h3>
                </div>

                <div class="form-group" style="margin-top: 16px;">
                    <label>Lema do Clube</label>
                    <input type="text" name="club_motto" class="form-control" value="<?= htmlspecialchars($profile['club_motto'] ?? '') ?>" placeholder="Ex: O amor de Cristo nos motiva...">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label>Voto do Desbravador</label>
                        <textarea name="club_vow" class="form-control" rows="4"><?= htmlspecialchars($profile['club_vow'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Lei do Desbravador</label>
                        <textarea name="club_law" class="form-control" rows="4"><?= htmlspecialchars($profile['club_law'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="form-group">
                    <label>Estilo Visual da Página Pública (Hub)</label>
                    <select name="layout_vibe" class="form-control" style="background: var(--bg-dashboard); border: 1px solid var(--border-light); color: var(--text-primary); padding: 12px; border-radius: 8px; width: 100%;">
                        <option value="hybrid" <?= ($profile['layout_vibe'] ?? 'hybrid') === 'hybrid' ? 'selected' : '' ?>>Híbrido Premium (Feed Social + Galeria Instagram) - Recomendado</option>
                        <option value="feed" <?= ($profile['layout_vibe'] ?? '') === 'feed' ? 'selected' : '' ?>>Apenas Feed Linear (Estilo X/Twitter)</option>
                        <option value="grid" <?= ($profile['layout_vibe'] ?? '') === 'grid' ? 'selected' : '' ?>>Apenas Galeria Visual (Estilo Instagram)</option>
                    </select>
                    <small style="color: var(--text-secondary); font-size: 0.8rem; display: block; margin-top: 4px;">Escolha o Layout do Hub. Os temas Claro (Bússola) e Escuro (Fogueira) estarão sempre disponíveis ao visitante público.</small>
                </div>

                <!-- ── Landing Page Hero ──────────────────────────────── -->
                <div class="dashboard-card-header" style="margin-top: 32px; padding-left: 0; padding-right: 0;">
                    <span class="material-icons-round" style="color: #00ccff;">auto_awesome</span>
                    <h3 style="margin:0;">Hero de Conversão (Topo da Página Pública)</h3>
                </div>
                <p style="color: var(--text-secondary); font-size: 0.85rem; margin: 8px 0 16px;">
                    Seção imersiva no topo do Hub Público para atrair pais e jovens que não conhecem os Desbravadores.
                    Use um vídeo do YouTube ou uma imagem de fundo.
                </p>

                <div class="form-group">
                    <label>Título Principal (Headline)</label>
                    <input type="text" name="hero_headline" class="form-control" maxlength="255"
                           value="<?= htmlspecialchars($profile['hero_headline'] ?? '') ?>"
                           placeholder="Ex: Aventura com Propósito">
                    <small style="color: var(--text-secondary); font-size: 0.8rem;">Deixe em branco para ocultar a seção hero.</small>
                </div>

                <div class="form-group">
                    <label>Subtítulo (Breve explicação para quem não conhece os Desbravadores)</label>
                    <textarea name="hero_subheadline" class="form-control" rows="2" maxlength="500"
                              placeholder="Ex: Mais de 1 milhão de jovens pelo mundo descobrem amizade, fé e habilidades para a vida."><?= htmlspecialchars($profile['hero_subheadline'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label>Tipo de Fundo do Hero</label>
                    <div style="display: flex; gap: 12px; margin-top: 8px;" id="heroBannerTypePicker">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer; padding:10px 16px; border-radius:10px; border:1px solid var(--border-light); flex:1; transition:all .2s;"
                               id="typeImageLabel">
                            <input type="radio" name="hero_banner_type" value="image"
                                   <?= ($profile['hero_banner_type'] ?? 'image') === 'image' ? 'checked' : '' ?>
                                   onchange="switchHeroBannerType('image')" style="accent-color:#00ccff;">
                            <span class="material-icons-round" style="font-size:20px; color:#00ccff;">image</span>
                            Imagem
                        </label>
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer; padding:10px 16px; border-radius:10px; border:1px solid var(--border-light); flex:1; transition:all .2s;"
                               id="typeYoutubeLabel">
                            <input type="radio" name="hero_banner_type" value="youtube"
                                   <?= ($profile['hero_banner_type'] ?? '') === 'youtube' ? 'checked' : '' ?>
                                   onchange="switchHeroBannerType('youtube')" style="accent-color:#00ccff;">
                            <span class="material-icons-round" style="font-size:20px; color:#ef4444;">smart_display</span>
                            Vídeo YouTube
                        </label>
                    </div>
                </div>

                <div class="form-group" id="heroBannerUrlGroup">
                    <label id="heroBannerUrlLabel">URL da Imagem de Fundo</label>
                    <input type="url" name="hero_banner_url" id="heroBannerUrlInput" class="form-control"
                           value="<?= htmlspecialchars($profile['hero_banner_url'] ?? '') ?>"
                           placeholder="https://exemplo.com/foto.jpg">
                    <small id="heroBannerUrlHint" style="color: var(--text-secondary); font-size: 0.8rem;">
                        Use uma imagem de alta qualidade (mín. 1920×1080).
                    </small>
                </div>

                <!-- Live Preview strip -->
                <div id="heroBannerPreview" style="display:none; border-radius:12px; overflow:hidden; height:160px; position:relative; margin-bottom:16px; background:var(--bg-elevated); border:1px solid var(--border-light);">
                    <div id="heroBannerPreviewBg" style="position:absolute;inset:0;background-size:cover;background-position:center;"></div>
                    <div style="position:absolute;inset:0;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;">
                        <span id="heroBannerPreviewLabel" style="color:white;font-size:0.8rem;opacity:0.7;">Pré-visualização</span>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 16px;">
                    <span class="material-icons-round">save</span> Salvar Perfil
                </button>
            </form>

<script>
(function() {
    const currentType = '<?= htmlspecialchars($profile['hero_banner_type'] ?? 'image') ?>';
    switchHeroBannerType(currentType);
    // trigger preview on load if URL exists
    const urlInput = document.getElementById('heroBannerUrlInput');
    if (urlInput && urlInput.value) updateHeroPreview(urlInput.value, currentType);
    urlInput && urlInput.addEventListener('input', () => {
        const t = document.querySelector('input[name="hero_banner_type"]:checked')?.value || 'image';
        updateHeroPreview(urlInput.value, t);
    });
})();

function switchHeroBannerType(type) {
    const urlLabel   = document.getElementById('heroBannerUrlLabel');
    const urlHint    = document.getElementById('heroBannerUrlHint');
    const urlInput   = document.getElementById('heroBannerUrlInput');
    const imgLabel   = document.getElementById('typeImageLabel');
    const ytLabel    = document.getElementById('typeYoutubeLabel');
    const accentBorder = '1px solid rgba(0,204,255,0.5)';
    const defaultBorder = '1px solid var(--border-light)';

    if (type === 'youtube') {
        urlLabel.textContent = 'URL do Vídeo YouTube';
        urlInput.placeholder = 'https://www.youtube.com/watch?v=...';
        urlHint.textContent  = 'O vídeo tocará mudo, em loop, como fundo imersivo.';
        urlInput.type        = 'url';
        imgLabel.style.border   = defaultBorder;
        ytLabel.style.border    = accentBorder;
    } else {
        urlLabel.textContent = 'URL da Imagem de Fundo';
        urlInput.placeholder = 'https://exemplo.com/foto.jpg';
        urlHint.textContent  = 'Use uma imagem de alta qualidade (mín. 1920×1080).';
        urlInput.type        = 'url';
        imgLabel.style.border   = accentBorder;
        ytLabel.style.border    = defaultBorder;
    }
    updateHeroPreview(urlInput.value, type);
}

function updateHeroPreview(url, type) {
    const preview     = document.getElementById('heroBannerPreview');
    const previewBg   = document.getElementById('heroBannerPreviewBg');
    const previewLabel= document.getElementById('heroBannerPreviewLabel');
    if (!url) { preview.style.display = 'none'; return; }

    preview.style.display = 'block';
    if (type === 'youtube') {
        const vid = extractYtId(url);
        if (vid) {
            previewBg.style.backgroundImage = `url(https://img.youtube.com/vi/${vid}/hqdefault.jpg)`;
            previewLabel.textContent = '▶ Thumb do YouTube (o vídeo tocará em loop na página pública)';
        } else {
            previewBg.style.backgroundImage = '';
            previewLabel.textContent = 'URL de YouTube inválida';
        }
    } else {
        previewBg.style.backgroundImage = `url(${url})`;
        previewLabel.textContent = 'Pré-visualização da imagem';
    }
}

function extractYtId(url) {
    const m = url.match(/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i);
    return m ? m[1] : null;
}
</script>
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
        
        const btn = document.querySelector('button[onclick="generateQr()"]');
        const originalText = btn.innerHTML;
        
        try {
            btn.innerHTML = '<span class="material-icons-round rotate">sync</span> Gerando...';
            btn.disabled = true;
            toast.info('Aguarde', 'Gerando QR Code...');
            
            const response = await fetch('<?= base_url($tenant['slug'] . '/admin/perfil-clube/qrcode') ?>', {
                method: 'POST',
                headers: { 
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (data.success) {
                toast.success('Sucesso', data.message);
                const container = document.getElementById('qr-code-container');
                // Use the returned path directly (it's now a full URL from backend)
                container.innerHTML = `<img src="${data.path}" alt="QR Code do Clube" style="max-width: 100%; height: auto; border-radius: 4px;">`;
                
                // Update button text after first generation
                if (originalText.includes('Gerar')) {
                    btn.innerHTML = '<span class="material-icons-round">autorenew</span> Regerar QR Code';
                }
            } else {
                toast.error('Erro', data.error || 'Erro ao gerar QR Code');
            }
        } catch (err) {
            console.error(err);
            if (toast) toast.error('Erro', 'Erro de conexão ou resposta inválida do servidor.');
        } finally {
            if (!document.getElementById('qr-code-container').querySelector('img')) {
                btn.innerHTML = originalText;
            } else {
                btn.innerHTML = '<span class="material-icons-round">autorenew</span> Regerar QR Code';
            }
            btn.disabled = false;
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
