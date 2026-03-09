<?php
/**
 * Shared Universal Icon Picker Partial (Iconify)
 * Robust version with Curated Categories and Discovery Dashboard.
 * 
 * Usage:
 * 1. Include: <?php require BASE_PATH . '/views/admin/partials/icon_picker.php'; ?>
 * 2. Trigger: IconPicker.open(currentValue, (selected) => { ... });
 */
?>

<style>
    /* ============ Icon Picker (Standardized & Premium) ============ */
    .icon-picker-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.7);
        backdrop-filter: blur(12px);
        z-index: 99999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .icon-picker-overlay.active {
        opacity: 1;
        visibility: visible;
    }

    .icon-picker-modal {
        background: linear-gradient(135deg, #1e293b, #0f172a);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 28px;
        max-width: 650px;
        width: 100%;
        max-height: 85vh;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 80px -20px rgba(139, 92, 246, 0.3);
        transform: scale(0.95) translateY(20px);
        transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .icon-picker-overlay.active .icon-picker-modal {
        transform: scale(1) translateY(0);
    }

    /* Header & Search */
    .icon-picker-header {
        padding: 28px 28px 20px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        background: rgba(255, 255, 255, 0.02);
    }

    .icon-picker-header h3 {
        margin: 0 0 20px;
        font-size: 1.4rem;
        font-weight: 800;
        color: white;
        display: flex;
        align-items: center;
        gap: 12px;
        letter-spacing: -0.02em;
    }

    .icon-search-wrapper {
        position: relative;
    }

    .icon-search {
        width: 100%;
        padding: 16px 20px 16px 52px;
        background: rgba(0, 0, 0, 0.4);
        border: 2px solid rgba(255, 255, 255, 0.1);
        border-radius: 18px;
        color: white;
        font-size: 1rem;
        outline: none;
        transition: all 0.2s;
        font-family: inherit;
    }

    .icon-search:focus {
        border-color: #8b5cf6;
        background: rgba(0, 0, 0, 0.6);
        box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.2);
    }

    .icon-search-wrapper i {
        position: absolute;
        left: 20px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 1.2rem;
    }

    /* Body & Navigation */
    .icon-picker-body {
        padding: 24px;
        overflow-y: auto;
        flex: 1;
        scrollbar-width: thin;
        scrollbar-color: rgba(255,255,255,0.1) transparent;
    }

    /* Dashboard Categories (Netflix Style) */
    .discovery-section {
        margin-bottom: 32px;
    }

    .discovery-title {
        font-size: 0.9rem;
        font-weight: 700;
        color: #8b5cf6;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .icon-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(64px, 1fr));
        gap: 12px;
    }

    .icon-item {
        aspect-ratio: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 16px;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }

    .icon-item iconify-icon {
        font-size: 1.75rem;
        color: #94a3b8;
        transition: color 0.2s;
    }

    .icon-item:hover {
        background: rgba(139, 92, 246, 0.2);
        border-color: rgba(139, 92, 246, 0.4);
        transform: translateY(-4px) scale(1.05);
    }

    .icon-item:hover iconify-icon {
        color: white;
    }

    .icon-item.selected {
        background: linear-gradient(135deg, #8b5cf6, #6366f1);
        border-color: transparent;
        box-shadow: 0 8px 20px -4px rgba(139, 92, 246, 0.5);
    }

    .icon-item.selected iconify-icon {
        color: white;
    }

    .icon-lib-tag {
        font-size: 8px;
        position: absolute;
        bottom: 6px;
        opacity: 0.4;
        text-transform: uppercase;
        font-weight: 700;
        color: white;
    }

    /* Footer */
    .icon-picker-footer {
        padding: 20px 28px;
        border-top: 1px solid rgba(255, 255, 255, 0.06);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: rgba(0, 0, 0, 0.2);
    }

    .selected-info {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .selected-preview {
        width: 48px;
        height: 48px;
        background: rgba(255,255,255,0.05);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: #8b5cf6;
        border: 1px solid rgba(139, 92, 246, 0.2);
    }

    .selected-name {
        color: #94a3b8;
        font-size: 0.9rem;
        font-weight: 600;
        font-family: monospace;
    }

    .btn-picker {
        padding: 12px 24px;
        border-radius: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-picker-cancel {
        background: rgba(255,255,255,0.05);
        color: white;
    }

    .btn-picker-cancel:hover { background: rgba(255,255,255,0.1); }

    .btn-picker-save {
        background: #8b5cf6;
        color: white;
        box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
    }

    .btn-picker-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(139, 92, 246, 0.4);
    }

    /* Loading State */
    .picker-loader {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 60px;
        color: #94a3b8;
        gap: 16px;
    }

    .spin { animation: picker-spin 1s linear infinite; }
    @keyframes picker-spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

    /* Mobile Responsive */
    @media (max-width: 640px) {
        .icon-picker-modal { height: 100vh; max-height: 100vh; border-radius: 0; }
        .icon-grid { grid-template-columns: repeat(4, 1fr); }
        .icon-picker-header { padding: 20px; }
        .icon-picker-body { padding: 15px; }
        .selected-name { display: none; }
    }
</style>

<div class="icon-picker-overlay" id="universalIconPickerOverlay">
    <div class="icon-picker-modal">
        <div class="icon-picker-header">
            <h3><i class="fa-solid fa-wand-magic-sparkles"></i> Biblioteca de Ícones</h3>
            <div class="icon-search-wrapper">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" class="icon-search" id="iconPickerSearch" placeholder="Busque entre 150.000 ícones (ex: 'campfire', 'gold')..." autocomplete="off">
            </div>
        </div>

        <div class="icon-picker-body" id="iconPickerBody">
            <!-- Content injected by JS -->
        </div>

        <div class="icon-picker-footer">
            <div class="selected-info">
                <div class="selected-preview" id="pickerSelectedPreview">
                    <iconify-icon icon="lucide:star"></iconify-icon>
                </div>
                <span class="selected-name" id="pickerSelectedName">lucide:star</span>
            </div>
            <div style="display: flex; gap: 12px;">
                <button class="btn-picker btn-picker-cancel" id="pickerCancelBtn">Cancelar</button>
                <button class="btn-picker btn-picker-save" id="pickerConfirmBtn">
                    <i class="fa-solid fa-check"></i> Próximo
                </button>
            </div>
        </div>
    </div>
</div>

<script>
window.IconPicker = {
    currentCallback: null,
    selectedValue: 'lucide:star',
    searchTimeout: null,
    
    // Curated Dashboard Config
    categories: {
        'Aventura & Exterior': [
            'game-icons:camp-fire', 'game-icons:forest-camp', 'game-icons:mountain-cave', 'game-icons:walking-boot',
            'game-icons:compass', 'game-icons:tent', 'game-icons:back-pack', 'game-icons:torch',
            'lucide:mountain', 'lucide:tent', 'lucide:trees', 'lucide:flame',
            'ph:fire-duotone', 'ph:compass-duotone', 'ph:map-trifold-duotone', 'ph:flashlight-duotone'
        ],
        'Animais & Natureza': [
            'noto:lion', 'noto:tiger', 'noto:eagle', 'noto:wolf', 'noto:bear', 'noto:fox',
            'game-icons:lion', 'game-icons:wolf-head', 'game-icons:eagle-emblem', 'game-icons:bear-head',
            'lucide:bird', 'lucide:dog', 'lucide:cat', 'lucide:fish',
            'ri:leaf-fill', 'ri:sun-cloudy-fill', 'ri:water-flash-fill', 'ri:moon-clear-fill'
        ],
        'Conquistas & Medalhas': [
            'game-icons:trophy', 'game-icons:medal-skull', 'game-icons:diamond-hard', 'game-icons:rank-3',
            'lucide:award', 'lucide:medal', 'lucide:crown', 'lucide:star',
            'ph:seal-check-duotone', 'ph:certificate-duotone', 'ph:trophy-duotone', 'ph:flag-banner-duotone',
            'fluent-emoji:gold-medal', 'fluent-emoji:military-medal', 'fluent-emoji:trophy'
        ],
        'Geral & Interface': [
            'lucide:user', 'lucide:settings', 'lucide:info', 'lucide:bell',
            'lucide:calendar', 'lucide:clock', 'lucide:map-pin', 'lucide:shield',
            'ri:home-4-fill', 'ri:layout-grid-fill', 'ri:heart-3-fill', 'ri:mail-send-fill'
        ]
    },

    open: function(initialValue, callbackOrInputId, previewId = null, textId = null) {
        this.selectedValue = initialValue || 'lucide:star';
        
        // Handle flexible callback API
        if (typeof callbackOrInputId === 'function') {
            this.currentCallback = callbackOrInputId;
        } else {
            // Robust legacy support: Auto-create callback from IDs
            this.currentCallback = (selected) => {
                const input = document.getElementById(callbackOrInputId);
                if (input) {
                    input.value = selected;
                    // Trigger change event for reactive listeners
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                }
                const preview = document.getElementById(previewId);
                if (preview) preview.innerHTML = `<iconify-icon icon="${selected}" style="font-size: 1.5rem;"></iconify-icon>`;
                const text = document.getElementById(textId);
                if (text) text.textContent = selected;
            };
        }
        
        // Reset and Update UI
        const searchInput = document.getElementById('iconPickerSearch');
        if (searchInput) searchInput.value = '';
        
        this.updatePreview();
        this.renderDashboard();
        
        const overlay = document.getElementById('universalIconPickerOverlay');
        if (overlay) overlay.classList.add('active');
        
        setTimeout(() => {
            if (searchInput) searchInput.focus();
        }, 100);
    },

    close: function() {
        const overlay = document.getElementById('universalIconPickerOverlay');
        if (overlay) overlay.classList.remove('active');
    },

    confirm: function() {
        if (this.currentCallback) this.currentCallback(this.selectedValue);
        this.close();
    },

    select: function(icon) {
        this.selectedValue = icon;
        this.updatePreview();
        
        // Highlight in grid
        document.querySelectorAll('.icon-item').forEach(el => {
            el.classList.toggle('selected', el.dataset.icon === icon);
        });
    },

    updatePreview: function() {
        const previewEl = document.getElementById('pickerSelectedPreview');
        const nameEl = document.getElementById('pickerSelectedName');
        if (previewEl) previewEl.innerHTML = `<iconify-icon icon="${this.selectedValue}"></iconify-icon>`;
        if (nameEl) nameEl.textContent = this.selectedValue;
    },

    renderDashboard: function() {
        const container = document.getElementById('iconPickerBody');
        if (!container) return;
        
        let html = '';
        for (const [title, icons] of Object.entries(this.categories)) {
            html += `
                <div class="discovery-section">
                    <div class="discovery-title">${title}</div>
                    <div class="icon-grid">
                        ${icons.map(icon => this.createIconItem(icon)).join('')}
                    </div>
                </div>
            `;
        }
        container.innerHTML = html;
    },

    createIconItem: function(icon) {
        const isSelected = icon === this.selectedValue ? 'selected' : '';
        const lib = icon.split(':')[1] ? icon.split(':')[0] : 'ext';
        return `
            <div class="icon-item ${isSelected}" data-icon="${icon}" onclick="IconPicker.select('${icon}')" title="${icon}">
                <iconify-icon icon="${icon}"></iconify-icon>
                <div class="icon-lib-tag">${lib}</div>
            </div>
        `;
    },

    init: function() {
        const overlay = document.getElementById('universalIconPickerOverlay');
        if (!overlay || overlay.dataset.pickerInitialized === 'true') return;

        const searchInput = document.getElementById('iconPickerSearch');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                clearTimeout(IconPicker.searchTimeout);
                IconPicker.searchTimeout = setTimeout(() => IconPicker.handleSearch(e.target.value), 400);
            });
        }

        const cancelBtn = document.getElementById('pickerCancelBtn');
        if (cancelBtn) cancelBtn.addEventListener('click', () => IconPicker.close());

        const confirmBtn = document.getElementById('pickerConfirmBtn');
        if (confirmBtn) confirmBtn.addEventListener('click', () => IconPicker.confirm());

        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) IconPicker.close();
        });

        overlay.dataset.pickerInitialized = 'true';
        console.log('IconPicker: Initialized.');
    },

    handleSearch: async function(query) {
        const container = document.getElementById('iconPickerBody');
        if (!container) return;
        
        if (query.length < 2) {
            this.renderDashboard();
            return;
        }

        container.innerHTML = `
            <div class="picker-loader">
                <i class="fa-solid fa-circle-notch fa-spin spin" style="font-size: 2rem;"></i>
                <span>Pesquisando em 102 bibliotecas de ícones...</span>
            </div>
        `;

        try {
            const response = await fetch(`https://api.iconify.design/search?query=${encodeURIComponent(query)}&limit=120`);
            const data = await response.json();
            
            if (data.icons && data.icons.length > 0) {
                container.innerHTML = `
                    <div class="discovery-section">
                        <div class="discovery-title">Resultados para "${query}"</div>
                        <div class="icon-grid">
                            ${data.icons.map(icon => this.createIconItem(icon)).join('')}
                        </div>
                    </div>
                `;
            } else {
                container.innerHTML = `
                    <div class="picker-loader">
                        <i class="fa-solid fa-magnifying-glass-chart" style="font-size: 2.5rem; opacity: 0.3;"></i>
                        <span>Nenhum ícone encontrado. Tente termos em inglês (ex: fire, flag).</span>
                    </div>
                `;
            }
        } catch (err) {
            console.error('Iconify Search Error:', err);
            container.innerHTML = `<p style="color:#ef4444;text-align:center;padding:20px;">Erro ao buscar ícones. Verifique sua conexão.</p>`;
        }
    }
};

// Event Listeners initialization
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => IconPicker.init());
} else {
    IconPicker.init();
}

// HTMX Support
document.body.addEventListener('htmx:afterSwap', function(evt) {
    // If the swapped content contains the picker, re-init
    if (document.getElementById('universalIconPickerOverlay')) {
        IconPicker.init();
    }
});
</script>
