<?php
/**
 * Club Profile & Growth Settings View
 * Tactical Pathfinder Redesign - Unified Operational Hub
 */
?>
<style>
    :root {
        --tactical-primary: var(--primary, #06b6d4);
        --tactical-primary-rgb: var(--primary-rgb, 6, 182, 212);
        --tactical-bg: var(--bg-main, #0f172a);
        --tactical-card: var(--bg-card, #1e293b);
        --tactical-border: var(--border-color, #334155);
        --tactical-text: var(--text-main, #f8fafc);
        --tactical-muted: var(--text-muted, #94a3b8);
        --tactical-dark: var(--bg-dark, #020617);
    }

    .tactical-hub-container {
        font-family: 'Inter', system-ui, sans-serif;
        color: var(--tactical-text);
        width: 100%;
        padding: 16px;
        box-sizing: border-box;
    }

    /* Spacing for sync button */
    .form-footer {
        margin-top: 32px;
    }

    /* Operation Modules */
    .operation-module {
        background: var(--tactical-card);
        border: 1px solid var(--tactical-border);
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        margin-bottom: 24px;
        transition: transform 0.2s ease;
    }

    .module-header {
        padding: 24px 32px;
        border-bottom: 1px solid var(--tactical-border);
        display: flex;
        align-items: center;
        gap: 12px;
        background: rgba(var(--tactical-primary-rgb), 0.03);
    }

    .module-header h3 {
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.15em;
        color: var(--tactical-muted);
        margin: 0;
    }

    .module-header .material-icons-round {
        font-size: 20px;
        color: var(--tactical-primary);
    }

    .module-body {
        padding: 32px;
    }

    /* Form Controls */
    .tactical-label {
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: var(--tactical-muted);
        margin-bottom: 10px;
        display: block;
    }

    .form-control-tactical {
        width: 100%;
        background: var(--tactical-dark);
        border: 1.5px solid var(--tactical-border);
        border-radius: 12px;
        padding: 12px 16px;
        color: var(--tactical-text);
        font-weight: 500;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .form-control-tactical:focus {
        border-color: var(--tactical-primary);
        box-shadow: 0 0 0 4px rgba(var(--tactical-primary-rgb), 0.1);
        outline: none;
        background: var(--tactical-card);
    }

    .slug-preview {
        font-family: 'JetBrains Mono', monospace;
        font-size: 11px;
        color: var(--tactical-muted);
        margin-top: 8px;
        display: block;
        padding-left: 14px;
        border-left: 2px solid var(--tactical-border);
    }

    .slug-preview strong {
        color: var(--tactical-primary);
    }

    /* Equipment Selector (Hero Type) */
    .equipment-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-top: 12px;
    }

    .equipment-card {
        padding: 16px 20px;
        border-radius: 14px;
        border: 1.5px solid var(--tactical-border);
        background: var(--tactical-dark);
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .equipment-radio:checked + .equipment-card {
        border-color: var(--tactical-primary);
        background: rgba(var(--tactical-primary-rgb), 0.08);
        box-shadow: 0 8px 24px -12px rgba(var(--tactical-primary-rgb), 0.3);
    }

    .equipment-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: var(--tactical-card);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        color: var(--tactical-muted);
        transition: all 0.3s;
    }

    .equipment-radio:checked + .equipment-card .equipment-icon {
        background: var(--tactical-primary);
        color: white;
    }

    .equipment-text {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--tactical-muted);
    }

    .equipment-radio:checked + .equipment-card .equipment-text {
        color: var(--tactical-text);
    }

    /* Growth Module (Sidebar) */
    .growth-diagnostic {
        background: var(--tactical-card);
        border: 1px solid var(--tactical-border);
        border-radius: 24px;
    }

    .qr-technical-frame {
        background: white;
        padding: 24px;
        border-radius: 16px;
        display: inline-block;
        border: 2px solid var(--tactical-border);
        box-shadow: 0 20px 40px -20px rgba(0,0,0,0.1);
        margin-bottom: 24px;
        position: relative;
    }

    .qr-technical-frame::after {
        content: 'SCAN_READY';
        position: absolute;
        bottom: -8px;
        left: 50%;
        transform: translateX(-50%);
        background: var(--tactical-primary);
        color: white;
        font-family: 'JetBrains Mono', monospace;
        font-size: 8px;
        font-weight: 900;
        padding: 2px 8px;
        border-radius: 4px;
    }

    .metric-readout {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 20px;
        background: var(--tactical-dark);
        border-radius: 12px;
        margin-bottom: 12px;
        border: 1px solid var(--tactical-border);
    }

    .metric-value {
        font-family: 'JetBrains Mono', monospace;
        font-size: 1.25rem;
        font-weight: 800;
        color: var(--tactical-primary);
    }

    /* Animations & Utility */
    .rotate { animation: spin 1s linear infinite; }
    @keyframes spin { 100% { transform: rotate(360deg); } }

    @media (max-width: 1024px) {
        .tactical-grid { grid-template-columns: 1fr !important; }
    }
</style>

<div class="tactical-hub-container">
    <div style="display: flex; justify-content: flex-end; margin-bottom: 24px;">
        <?php if (!empty($profile['slug'])): ?>
            <a href="<?= base_url('c/' . $profile['slug']) ?>" target="_blank" 
               class="btn-tactical-view"
               style="background: var(--tactical-card); border: 1.5px solid var(--tactical-border); padding: 12px 24px; border-radius: 12px; color: var(--tactical-text); font-weight: 800; font-size: 11px; text-transform: uppercase; letter-spacing: 0.1em; text-decoration: none; display: flex; align-items: center; gap: 8px; transition: all 0.3s; width: fit-content;">
                <span class="material-icons-round" style="font-size: 18px;">visibility</span>
                Ver Página Pública
            </a>
        <?php endif; ?>
    </div>

    <div class="tactical-grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 32px;">
        <!-- Left Column: Operations -->
        <div class="operation-flow">
            <form id="profile-form">
                <!-- MODULE: BIO -->
                <div class="operation-module">
                    <header class="module-header">
                        <span class="material-icons-round">badge</span>
                        <h3>Modulo Operacional: Bio & Identidade</h3>
                    </header>
                    <div class="module-body">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
                            <div class="form-group">
                                <label class="tactical-label">Nome de Exibição</label>
                                <input type="text" name="display_name" class="form-control-tactical" required value="<?= htmlspecialchars($profile['display_name'] ?? '') ?>" placeholder="Ex: Clube Leão do Norte">
                            </div>
                            <div class="form-group">
                                <label class="tactical-label">Slug da URL (Identificador)</label>
                                <input type="text" name="slug" class="form-control-tactical" required value="<?= htmlspecialchars($profile['slug'] ?? '') ?>" placeholder="Ex: leao-do-norte">
                                <span class="slug-preview">desbravahub.app/c/<strong><?= htmlspecialchars($profile['slug'] ?? 'slug') ?></strong></span>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
                            <div class="form-group">
                                <label class="tactical-label">URL do Logo (SVG/PNG)</label>
                                <input type="url" name="logo_url" class="form-control-tactical" value="<?= htmlspecialchars($profile['logo_url'] ?? '') ?>" placeholder="https://exemplo.com/logo.png">
                            </div>
                            <div class="form-group">
                                <label class="tactical-label">URL da Capa (Banner)</label>
                                <input type="url" name="cover_image_url" class="form-control-tactical" value="<?= htmlspecialchars($profile['cover_image_url'] ?? '') ?>" placeholder="https://exemplo.com/capa.jpg">
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
                            <div class="form-group">
                                <label class="tactical-label">Local de Reunião</label>
                                <input type="text" name="meeting_address" class="form-control-tactical" value="<?= htmlspecialchars($profile['meeting_address'] ?? '') ?>" placeholder="Rua 1, Bairro Centro - Igreja Central">
                            </div>
                            <div class="form-group">
                                <label class="tactical-label">Cronograma Semanal</label>
                                <input type="text" name="meeting_time" class="form-control-tactical" value="<?= htmlspecialchars($profile['meeting_time'] ?? '') ?>" placeholder="Domingos às 09:00">
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
                            <div class="form-group">
                                <label class="tactical-label">Instagram (@)</label>
                                <input type="text" name="social_instagram" class="form-control-tactical" value="<?= htmlspecialchars($profile['social_instagram'] ?? '') ?>" placeholder="@seuclube">
                            </div>
                            <div class="form-group">
                                <label class="tactical-label">Link WhatsApp (Grupo)</label>
                                <input type="url" name="social_whatsapp_group" class="form-control-tactical" value="<?= htmlspecialchars($profile['social_whatsapp_group'] ?? '') ?>" placeholder="https://chat.whatsapp.com/...">
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 24px;">
                            <label class="tactical-label">Mensagem de Boas-Vindas</label>
                            <textarea name="welcome_message" class="form-control-tactical" rows="4" style="resize:none;"><?= htmlspecialchars($profile['welcome_message'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label class="tactical-label">Meta Description (SEO)</label>
                            <input type="text" name="seo_meta_description" class="form-control-tactical" value="<?= htmlspecialchars($profile['seo_meta_description'] ?? '') ?>" maxlength="160" placeholder="Descrição para motores de busca...">
                        </div>
                    </div>
                </div>

                <!-- MODULE: CULTURE -->
                <div class="operation-module">
                    <header class="module-header">
                        <span class="material-icons-round">auto_fix_high</span>
                        <h3>Modulo Operacional: Cultura & Estilo</h3>
                    </header>
                    <div class="module-body">
                        <div class="form-group" style="margin-bottom: 24px;">
                            <label class="tactical-label">Lema da Unidade/Clube</label>
                            <input type="text" name="club_motto" class="form-control-tactical" value="<?= htmlspecialchars($profile['club_motto'] ?? '') ?>" placeholder="Ex: O amor de Cristo nos motiva...">
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
                            <div class="form-group">
                                <label class="tactical-label">Voto</label>
                                <textarea name="club_vow" class="form-control-tactical" rows="3" style="resize:none;"><?= htmlspecialchars($profile['club_vow'] ?? '') ?></textarea>
                            </div>
                            <div class="form-group">
                                <label class="tactical-label">Lei</label>
                                <textarea name="club_law" class="form-control-tactical" rows="3" style="resize:none;"><?= htmlspecialchars($profile['club_law'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="tactical-label">Arquitetura de Layout (Hub Público)</label>
                            <select name="layout_vibe" class="form-control-tactical">
                                <option value="hybrid" <?= ($profile['layout_vibe'] ?? 'hybrid') === 'hybrid' ? 'selected' : '' ?>>Híbrido Premium (Social + Galeria)</option>
                                <option value="feed" <?= ($profile['layout_vibe'] ?? '') === 'feed' ? 'selected' : '' ?>>Feed Linear (Estilo X)</option>
                                <option value="grid" <?= ($profile['layout_vibe'] ?? '') === 'grid' ? 'selected' : '' ?>>Galeria Visual (Estilo Instagram)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- MODULE: HERO ENGINE -->
                <div class="operation-module">
                    <header class="module-header">
                        <span class="material-icons-round">bolt</span>
                        <h3>Modulo Operacional: Hero Conversion Engine</h3>
                    </header>
                    <div class="module-body">
                        <div class="form-group" style="margin-bottom: 24px;">
                            <label class="tactical-label">Headline (Título de Impacto)</label>
                            <input type="text" name="hero_headline" class="form-control-tactical" maxlength="255" value="<?= htmlspecialchars($profile['hero_headline'] ?? '') ?>" placeholder="Ex: Aventura com Propósito">
                        </div>

                        <div class="form-group" style="margin-bottom: 24px;">
                            <label class="tactical-label">Subheadline (Contextualização)</label>
                            <textarea name="hero_subheadline" class="form-control-tactical" rows="2" maxlength="500" placeholder="Ex: Descubra amizade, fé e novas habilidades."><?= htmlspecialchars($profile['hero_subheadline'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group" style="margin-bottom: 24px;">
                            <label class="tactical-label">Seletor de Equipamento (Fundo do Hero)</label>
                            <div class="equipment-grid">
                                <label style="display:block; cursor:pointer;">
                                    <input type="radio" name="hero_banner_type" value="image" class="equipment-radio" style="display:none;" <?= ($profile['hero_banner_type'] ?? 'image') === 'image' ? 'checked' : '' ?> onchange="switchHeroBannerType('image')">
                                    <div class="equipment-card">
                                        <div class="equipment-icon">
                                            <span class="material-icons-round">image</span>
                                        </div>
                                        <span class="equipment-text">Static Image</span>
                                    </div>
                                </label>

                                <label style="display:block; cursor:pointer;">
                                    <input type="radio" name="hero_banner_type" value="youtube" class="equipment-radio" style="display:none;" <?= ($profile['hero_banner_type'] ?? '') === 'youtube' ? 'checked' : '' ?> onchange="switchHeroBannerType('youtube')">
                                    <div class="equipment-card">
                                        <div class="equipment-icon">
                                            <span class="material-icons-round" style="color: #ef4444;">smart_display</span>
                                        </div>
                                        <span class="equipment-text">Cinematic Video</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="form-group" id="heroBannerUrlGroup" style="margin-bottom: 24px;">
                            <label class="tactical-label" id="heroBannerUrlLabel">Source URL (Asset)</label>
                            <input type="url" name="hero_banner_url" id="heroBannerUrlInput" class="form-control-tactical" value="<?= htmlspecialchars($profile['hero_banner_url'] ?? '') ?>" placeholder="https://...">
                            <small id="heroBannerUrlHint" style="display:block; margin-top:8px; font-size:11px; color:var(--tactical-muted); font-style:italic; opacity:0.8;"></small>
                        </div>

                        <div id="heroBannerPreview" style="display:none; border-radius:20px; overflow:hidden; height:200px; position:relative; margin-bottom:24px; border:2px dashed var(--tactical-border); background:var(--tactical-dark);">
                            <div id="heroBannerPreviewBg" style="position:absolute; inset:0; background-size:cover; background-position:center; opacity:0.5;"></div>
                            <div style="position:absolute; inset:0; display:flex; align-items:center; justify-content:center; flex-direction:column; gap:12px; background: radial-gradient(circle at center, rgba(0,0,0,0.5), transparent);">
                                <span class="material-icons-round" style="color:var(--tactical-primary); font-size:40px;">biotech</span>
                                <span id="heroBannerPreviewLabel" style="color:white; font-size:10px; font-weight:900; text-transform:uppercase; letter-spacing:0.15em; background:var(--tactical-card); border:1px solid var(--tactical-border); padding:6px 16px; border-radius:6px;">PRÉ-VISUALIZAÇÃO ANALÍTICA</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-footer">
                    <button type="submit" class="btn-tactical-submit" style="width: 100%; padding: 20px; background: var(--tactical-primary); border: none; color: white; font-weight: 900; font-size: 13px; text-transform: uppercase; letter-spacing: 0.15em; border-radius: 16px; cursor: pointer; box-shadow: 0 10px 25px -5px rgba(var(--tactical-primary-rgb), 0.4); transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 12px;">
                        <span class="material-icons-round">save</span>
                        Sincronizar Operações do Clube
                    </button>
                </div>
            </form>
        </div>

        <!-- Right Column: Growth & Telemetry -->
        <div class="diagnostic-flow">
            <div class="operation-module growth-diagnostic">
                <header class="module-header" style="background: rgba(16, 185, 129, 0.05);">
                    <span class="material-icons-round" style="color:#10b981;">qr_code_2</span>
                    <h3>Growth Engine: Offline Discovery</h3>
                </header>
                <div class="module-body" style="text-align: center;">
                    <p style="color: var(--tactical-muted); font-size: 0.85rem; margin-bottom: 24px; line-height: 1.5;">
                        Identificador único para materiais impressos e eventos. Rastreie a conversão física em digital.
                    </p>
                    
                    <div class="qr-technical-frame" id="qr-technical-frame">
                        <div id="qr-code-container" style="min-width: 180px; min-height: 180px; display: flex; align-items: center; justify-content: center;">
                            <?php if (!empty($growth['qr_code_path'])): ?>
                                <img src="<?= base_url($growth['qr_code_path']) ?>" alt="QR Code" style="max-width: 100%; height: auto; border-radius: 4px;">
                            <?php else: ?>
                                <div style="color: var(--tactical-muted); font-size: 0.75rem; font-weight: 700; text-transform: uppercase; opacity: 0.5;">Frequência não gerada</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <button type="button" class="btn-tactical-qr" onclick="generateQr()" style="width: 100%; padding: 14px; background: var(--tactical-dark); border: 1.5px solid var(--tactical-border); color: var(--tactical-text); font-weight: 800; font-size: 11px; text-transform: uppercase; letter-spacing: 0.1em; border-radius: 12px; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px;">
                        <span class="material-icons-round">autorenew</span> 
                        <?= empty($growth['qr_code_path']) ? 'Gerar Acesso QR' : 'Sincronizar QR' ?>
                    </button>
                </div>
            </div>

            <div class="operation-module" style="margin-top: 24px;">
                <header class="module-header" style="background: rgba(139, 92, 246, 0.05);">
                    <span class="material-icons-round" style="color:#8b5cf6;">insights</span>
                    <h3>Telemetria de Engajamento</h3>
                </header>
                <div class="module-body" style="padding: 24px;">
                    <div class="metric-readout">
                        <span class="tactical-label" style="margin:0;">Vistas via QR</span>
                        <span class="metric-value"><?= number_format($growth['visits_count'] ?? 0) ?></span>
                    </div>
                    <div class="metric-readout" style="margin-bottom:0;">
                        <span class="tactical-label" style="margin:0;">Vetor de Campanha</span>
                        <span style="font-family:'JetBrains Mono'; font-weight:700; font-size:11px; color:var(--tactical-text); letter-spacing:0.05em;"><?= htmlspecialchars($growth['campaign_source'] ?? 'ACTIVE_HUB') ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const toast = window.toast = window.toast || new (window.ToastNotification || ToastNotification)();
        const urlInput = document.getElementById('heroBannerUrlInput');
        const initialType = document.querySelector('input[name="hero_banner_type"]:checked')?.value || 'image';

        // Initialize Hero Engine
        switchHeroBannerType(initialType, false);
        if (urlInput.value) updateHeroPreview(urlInput.value, initialType);

        // Live URL updates
        urlInput.addEventListener('input', () => {
            const type = document.querySelector('input[name="hero_banner_type"]:checked')?.value || 'image';
            updateHeroPreview(urlInput.value, type);
        });

        // Form Submission
        document.getElementById('profile-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = e.target.querySelector('button[type="submit"]');
            const originalHTML = btn.innerHTML;
            
            btn.innerHTML = '<span class="material-icons-round rotate">sync</span> Sincronizando...';
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
                    toast.success('Operação Completa', data.message);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    toast.error('Falha na Operação', data.error || 'Erro inesperado no servidor');
                }
            } catch (err) {
                console.error(err);
                toast.error('Erro de Rede', 'Não foi possível conectar ao Hub Central.');
            } finally {
                btn.innerHTML = originalHTML;
                btn.disabled = false;
            }
        });
    });

    function switchHeroBannerType(type, triggerPreview = true) {
        const urlLabel = document.getElementById('heroBannerUrlLabel');
        const urlHint  = document.getElementById('heroBannerUrlHint');
        const urlInput = document.getElementById('heroBannerUrlInput');

        if (type === 'youtube') {
            urlLabel.textContent = 'Identificador Cinematico (YouTube URL)';
            urlInput.placeholder = 'https://www.youtube.com/watch?v=...';
            urlHint.textContent  = 'O algorítmo extrairá o ID automaticamente para renderização em loop.';
        } else {
            urlLabel.textContent = 'Matriz de Imagem (Background URL)';
            urlInput.placeholder = 'https://exemplo.com/foto.jpg';
            urlHint.textContent  = 'Recomendado: 1920x1080px para máxima fidelidade visual.';
        }

        if (triggerPreview) updateHeroPreview(urlInput.value, type);
    }

    function updateHeroPreview(url, type) {
        const preview      = document.getElementById('heroBannerPreview');
        const previewBg    = document.getElementById('heroBannerPreviewBg');
        const previewLabel = document.getElementById('heroBannerPreviewLabel');

        if (!url) {
            preview.style.display = 'none';
            return;
        }

        preview.style.display = 'block';
        if (type === 'youtube') {
            const vid = extractYtId(url);
            if (vid) {
                previewBg.style.backgroundImage = `url(https://img.youtube.com/vi/${vid}/hqdefault.jpg)`;
                previewLabel.textContent = 'PRÉ-VISUALIZAÇÃO: CINEMATIC ENGINE READY';
            } else {
                previewBg.style.backgroundImage = '';
                previewLabel.textContent = 'ERRO: URL YOUTUBE INVÁLIDA';
            }
        } else {
            previewBg.style.backgroundImage = `url(${url})`;
            previewLabel.textContent = 'PRÉ-VISUALIZAÇÃO: STATIC BUFFER READY';
        }
    }

    function extractYtId(url) {
        const m = url.match(/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i);
        return m ? m[1] : null;
    }

    async function generateQr() {
        const toast = window.toast || new (window.ToastNotification || ToastNotification)();
        const btn = document.querySelector('button[onclick="generateQr()"]');
        const originalHTML = btn.innerHTML;
        
        try {
            btn.innerHTML = '<span class="material-icons-round rotate">sync</span> Sincronizando...';
            btn.disabled = true;
            
            const response = await fetch('<?= base_url($tenant['slug'] . '/admin/perfil-clube/qrcode') ?>', {
                method: 'POST',
                headers: { 
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (data.success) {
                toast.success('Criptografia Concluída', 'Novo QR Code gerado com sucesso.');
                const container = document.getElementById('qr-code-container');
                container.innerHTML = `<img src="${data.path}" alt="QR Code" style="max-width: 100%; height: auto; border-radius: 4px; animation: fadeIn 0.5s ease;">`;
                btn.innerHTML = '<span class="material-icons-round">autorenew</span> Sincronizar QR';
            } else {
                toast.error('Erro de Protocolo', data.error || 'Falha ao processar QR Code');
                btn.innerHTML = originalHTML;
            }
        } catch (err) {
            console.error(err);
            toast.error('Erro Crítico', 'Falha na comunicação com o Growth Engine.');
            btn.innerHTML = originalHTML;
        } finally {
            btn.disabled = false;
        }
    }
</script>
