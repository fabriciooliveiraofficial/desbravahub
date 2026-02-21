<?php
/**
 * Super Scraper UI View
 */
?>

<div class="sa-card" style="margin-bottom: 24px;">
    <h3 style="color: white; font-family: 'Outfit'; margin-bottom: 16px; display:flex; align-items:center; gap:8px;">
        <span class="material-symbols-rounded" style="color: var(--sa-neon)">smart_toy</span>
        Configuração de Inteligência Artificial
    </h3>
    <p style="color: #94a3b8; margin-bottom: 24px;">Para o Scraper atingir 100% de precisão em qualquer estrutura de site ou PDF complexo, insira sua chave da OpenAI. Sem a chave, o sistema tentará usar expressões regulares (Regex) com resultados limitados.</p>
    
    <div style="background: rgba(0,0,0,0.2); padding: 16px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);">
        <label style="display:block; color:white; font-size:0.875rem; margin-bottom:8px; font-weight:500;">Chave de API (OpenAI gpt-4o / gpt-4o-mini)</label>
        <div style="display:flex; gap:12px;">
            <input type="password" id="apiKey" value="<?= htmlspecialchars($_SESSION['super_scraper_key'] ?? '') ?>" placeholder="sk-proj-..." style="flex:1; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); color:white; padding:12px 16px; border-radius:8px; font-family:monospace; outline:none;">
            <button onclick="saveApiKey()" style="background:var(--sa-primary); color:white; border:none; padding:0 24px; border-radius:8px; font-weight:600; cursor:pointer; transition:background 0.2s;">
                Salvar Chave
            </button>
        </div>
    </div>
</div>

<div style="display:grid; grid-template-columns: 1fr 1fr; gap:24px;">
    
    <!-- Input Column -->
    <div class="sa-card" style="display:flex; flex-direction:column;">
        <h3 style="color: white; font-family: 'Outfit'; margin-bottom: 16px;">Fonte de Dados</h3>
        
        <div style="display:flex; gap:8px; margin-bottom:24px;">
            <button class="tab-btn active" onclick="switchTab('url')">URL / Link</button>
            <button class="tab-btn" onclick="switchTab('text')">Texto Bruto / HTML</button>
        </div>

        <div id="tab-url" class="tab-content active" style="flex:1;">
            <label style="display:block; color:#cbd5e1; font-size:0.875rem; margin-bottom:8px;">URL do Site ou PDF Público</label>
            <input type="url" id="targetUrl" placeholder="https://..." style="width:100%; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); color:white; padding:12px 16px; border-radius:8px; outline:none; margin-bottom:24px;">
        </div>

        <div id="tab-text" class="tab-content" style="flex:1; display:none;">
            <label style="display:block; color:#cbd5e1; font-size:0.875rem; margin-bottom:8px;">Cole o texto ou código HTML aqui</label>
            <textarea id="rawText" placeholder="<div class='requisitos'>..." style="width:100%; height:300px; resize:none; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); color:white; padding:12px 16px; border-radius:8px; outline:none; font-family:monospace; margin-bottom:24px;"></textarea>
        </div>

        <button id="btnScrape" onclick="startScrape()" style="width:100%; background:white; color:var(--sa-dark); border:none; padding:16px; border-radius:12px; font-weight:700; font-size:1rem; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; transition:all 0.2s;">
            <span class="material-symbols-rounded">bolt</span>
            Extrair & Normalizar (JSON)
        </button>
    </div>

    <!-- Output Column -->
    <div class="sa-card" style="display:flex; flex-direction:column;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 16px;">
            <h3 style="color: white; font-family: 'Outfit'; margin:0;">Resultado JSON</h3>
            <button onclick="copyJson()" style="background:rgba(255,255,255,0.1); color:white; border:none; padding:6px 12px; border-radius:6px; font-size:0.8rem; cursor:pointer; display:flex; align-items:center; gap:4px;">
                <span class="material-symbols-rounded" style="font-size:16px;">content_copy</span> Copiar
            </button>
        </div>
        
        <div id="outputContainer" style="flex:1; background:#0f172a; border-radius:12px; border:1px solid rgba(255,255,255,0.1); padding:16px; overflow:auto; max-height: 500px; position:relative;">
            <div id="loadingOverlay" style="display:none; position:absolute; top:0; left:0; right:0; bottom:0; background:rgba(15,23,42,0.8); backdrop-filter:blur(4px); flex-direction:column; align-items:center; justify-content:center; z-index:10;">
                <div class="spinner" style="width:40px; height:40px; border:4px solid rgba(255,255,255,0.1); border-top-color:var(--sa-primary); border-radius:50%; animation:spin 1s linear infinite;"></div>
                <div id="loadingText" style="color:white; margin-top:16px; font-weight:500;">Analisando com IA...</div>
            </div>
            <pre><code id="jsonOutput" style="color:#a78bfa; font-family:monospace; font-size:0.875rem;">// O JSON formatado aparecerá aqui</code></pre>
        </div>
    </div>
</div>

<style>
    .tab-btn {
        background: transparent;
        border: 1px solid rgba(255,255,255,0.1);
        color: #94a3b8;
        padding: 8px 16px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 500;
        transition: all 0.2s;
    }
    .tab-btn.active {
        background: rgba(139, 92, 246, 0.2);
        color: var(--sa-neon);
        border-color: var(--sa-primary);
    }
    @keyframes spin { 100% { transform: rotate(360deg); } }
</style>

<script>
    function switchTab(tab) {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.style.display = 'none');
        
        event.target.classList.add('active');
        document.getElementById('tab-' + tab).style.display = 'block';
    }

    async function saveApiKey() {
        const key = document.getElementById('apiKey').value;
        const btn = event.target;
        btn.innerHTML = 'Salvando...';
        
        try {
            const res = await fetch('/super-admin/api/save-key', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ key })
            });
            if (res.ok) {
                btn.innerHTML = 'Salvo! ✓';
                btn.style.background = '#10b981';
                setTimeout(() => {
                    btn.innerHTML = 'Salvar Chave';
                    btn.style.background = 'var(--sa-primary)';
                }, 2000);
            }
        } catch(e) {
            btn.innerHTML = 'Erro';
        }
    }

    async function startScrape() {
        const activeTab = document.querySelector('.tab-content.active').id;
        let payload = { type: activeTab === 'tab-url' ? 'url' : 'text' };
        
        if (payload.type === 'url') {
            payload.content = document.getElementById('targetUrl').value;
            if (!payload.content) return alert('Insira uma URL válida.');
        } else {
            payload.content = document.getElementById('rawText').value;
            if (!payload.content) return alert('Insira algum texto ou HTML.');
        }

        const btn = document.getElementById('btnScrape');
        const overlay = document.getElementById('loadingOverlay');
        const output = document.getElementById('jsonOutput');
        
        btn.disabled = true;
        btn.style.opacity = '0.7';
        overlay.style.display = 'flex';
        output.innerHTML = '';

        try {
            const response = await fetch('/super-admin/scraper/process', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(payload)
            });
            
            const data = await response.json();
            
            if (response.ok) {
                output.innerHTML = JSON.stringify(data.result, null, 2);
            } else {
                output.innerHTML = `// ERRO: ${data.error}`;
                output.style.color = '#ef4444';
            }
        } catch (error) {
            output.innerHTML = `// ERRO FATAL DE REDE\n${error.message}`;
            output.style.color = '#ef4444';
        } finally {
            btn.disabled = false;
            btn.style.opacity = '1';
            overlay.style.display = 'none';
        }
    }
    
    function copyJson() {
        const text = document.getElementById('jsonOutput').innerText;
        navigator.clipboard.writeText(text);
        
        const btn = event.target;
        const originalContent = btn.innerHTML;
        btn.innerHTML = '<span class="material-symbols-rounded" style="font-size:16px;">check</span> Copiado!';
        setTimeout(() => btn.innerHTML = originalContent, 2000);
    }
</script>
