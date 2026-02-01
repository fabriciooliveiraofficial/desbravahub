<?php
/**
 * Shared Icon Picker Partial (Iconify)
 * Includes CSS, Modal HTML, and JavaScript.
 * 
 * Usage:
 * 1. Include this file in your view: <?php require BASE_PATH . '/views/admin/partials/icon_picker.php'; ?>
 * 2. Use the JS API: IconPicker.open(currentIcon, callbackFunction);
 */
?>

<!-- Icon Picker Styles -->
<style>
    /* ============ Icon Picker (Standardized) ============ */
    .icon-picker-trigger {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        width: 100%;
        background: #fff;
        border: 1px solid var(--border-color, #e2e8f0);
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.2s;
        text-align: left;
    }

    .icon-picker-trigger:hover {
        border-color: var(--primary, #06b6d4);
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    
    .icon-picker-trigger:focus {
        outline: none;
        border-color: var(--primary, #06b6d4);
        box-shadow: 0 0 0 4px rgba(6, 182, 212, 0.1);
    }

    .icon-picker-preview {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #8b5cf6, #06b6d4);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.25rem;
        flex-shrink: 0;
    }

    .icon-picker-text {
        flex: 1;
        color: var(--text-secondary, #64748b);
        font-size: 0.95rem;
        font-weight: 500;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .icon-picker-arrow {
        color: #94a3b8;
    }

    /* Icon Picker Modal */
    .icon-picker-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(8px);
        z-index: 99999; /* High z-index to be on top of other modals */
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s, visibility 0.3s;
    }

    .icon-picker-overlay.active {
        opacity: 1;
        visibility: visible;
        pointer-events: all;
    }

    .icon-picker-modal {
        background: linear-gradient(135deg, rgba(30, 41, 59, 0.98), rgba(15, 23, 42, 0.98));
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 24px;
        max-width: 600px;
        width: 100%;
        max-height: 80vh;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        box-shadow: 
            0 25px 50px -12px rgba(0, 0, 0, 0.5),
            0 0 60px -20px rgba(139, 92, 246, 0.15);
        transform: scale(0.9) translateY(20px);
        opacity: 0;
        transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.3s;
    }

    .icon-picker-overlay.active .icon-picker-modal {
        transform: scale(1) translateY(0);
        opacity: 1;
    }

    .icon-picker-modal::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, #8b5cf6, #06b6d4, #10b981);
    }

    .icon-picker-header {
        padding: 24px 24px 16px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }

    .icon-picker-header h3 {
        margin: 0 0 16px;
        font-size: 1.25rem;
        font-weight: 700;
        color: white;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .icon-search {
        width: 100%;
        padding: 14px 18px 14px 48px;
        background: rgba(0, 0, 0, 0.3);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 14px;
        color: white;
        font-size: 0.95rem;
        outline: none;
        transition: all 0.2s;
        font-family: inherit;
    }

    .icon-search:focus {
        border-color: rgba(139, 92, 246, 0.5);
        box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
    }

    .icon-search-wrapper {
        position: relative;
    }

    .icon-search-wrapper i {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
    }

    .icon-picker-body {
        padding: 16px 24px;
        overflow-y: auto;
        flex: 1;
    }

    .icon-category-title {
        font-size: 0.8rem;
        font-weight: 600;
        color: #8b5cf6;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin: 16px 0 12px;
        padding-left: 4px;
    }

    .icon-category-title:first-child {
        margin-top: 0;
    }

    .icon-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(56px, 1fr));
        gap: 8px;
    }

    .icon-item {
        width: 56px;
        height: 56px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.06);
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 1.25rem;
        color: #94a3b8;
    }

    .icon-item:hover {
        background: rgba(139, 92, 246, 0.15);
        border-color: rgba(139, 92, 246, 0.3);
        color: white;
        transform: scale(1.1);
    }

    .icon-item.selected {
        background: linear-gradient(135deg, #8b5cf6, #06b6d4);
        border-color: transparent;
        color: white;
        box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
    }

    .icon-picker-footer {
        padding: 16px 24px;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: rgba(0, 0, 0, 0.1);
    }

    .icon-picker-selected {
        display: flex;
        align-items: center;
        gap: 12px;
        color: #94a3b8;
        font-size: 0.9rem;
    }

    .icon-picker-selected i {
        font-size: 1.5rem;
        color: #8b5cf6;
    }

    .icon-picker-actions {
        display: flex;
        gap: 12px;
    }
    
    .btn-modal-cancel {
        background: rgba(255,255,255,0.1);
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 500;
        transition: background 0.2s;
    }
    .btn-modal-cancel:hover {
        background: rgba(255,255,255,0.2);
    }

    .btn-modal-save {
        background: #8b5cf6;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        transition: opacity 0.2s;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .btn-modal-save:hover {
        opacity: 0.9;
    }
</style>

<!-- Standardized Icon Picker Modal -->
<div class="icon-picker-overlay" id="globalIconPickerModal" onclick="IconPicker.close()">
    <div class="icon-picker-modal" onclick="event.stopPropagation()">
        <div class="icon-picker-header">
            <h3><i class="fa-solid fa-icons"></i> Selecionar Ícone</h3>
            <div class="icon-search-wrapper">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" class="icon-search" id="globalIconSearch" placeholder="Buscar ícones..." oninput="IconPicker.handleSearch(event)">
            </div>
        </div>
        <div class="icon-picker-body" id="globalIconGridContainer">
            <!-- Icons rendered by JS -->
        </div>
        <div class="icon-picker-footer">
            <div class="icon-picker-selected">
                <iconify-icon id="globalSelectedIconPreview" icon="lucide:star" style="font-size: 1.5rem; width: 24px; height: 24px; color: #8b5cf6;"></iconify-icon>
                <span id="globalSelectedIconName">lucide:star</span>
            </div>
            <div class="icon-picker-actions">
                <button type="button" class="btn-modal-cancel" onclick="IconPicker.close()">Cancelar</button>
                <button type="button" class="btn-modal-save" onclick="IconPicker.confirm()">
                    <i class="fa-solid fa-check"></i> Selecionar
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js"></script>

<script>
    window.IconPicker = {
        callback: null,
        selectedIcon: 'lucide:star',
        categories: {
            'Aventura & Insígnias (Game Icons)': [
                'game-icons:shield', 'game-icons:trophy', 'game-icons:swords', 'game-icons:crossed-swords',
                'game-icons:flame', 'game-icons:mountain-cave', 'game-icons:compass', 'game-icons:forest-camp',
                'game-icons:lion', 'game-icons:wolf-head', 'game-icons:eagle-emblem', 'game-icons:snake',
                'game-icons:gorilla', 'game-icons:bear-head', 'game-icons:shark-jaws', 'game-icons:dragon-head',
                'game-icons:fleur-de-lys', 'game-icons:rank-3', 'game-icons:walking-boot', 'game-icons:tent'
            ],
            'Animais & Objetos (HD Emojis)': [
                'noto:lion', 'noto:tiger', 'noto:eagle', 'noto:wolf',
                'noto:bear', 'noto:elephant', 'noto:fox', 'noto:panda',
                'noto:fire', 'noto:evergreen-tree', 'noto:mountain', 'noto:wrapped-gift',
                'noto:trophy', 'noto:sports-medal', 'noto:check-mark-button', 'noto:flag-for-brazil',
                'noto:automobile', 'noto:camping', 'noto:tent', 'noto:compass'
            ],
            'Lucide (Premium)': [
                'lucide:tent', 'lucide:mountain', 'lucide:compass', 'lucide:map',
                'lucide:flame', 'lucide:leaf', 'lucide:tree-pine', 'lucide:waves',
                'lucide:cloud', 'lucide:sun', 'lucide:moon', 'lucide:star',
                'lucide:heart', 'lucide:shield', 'lucide:award', 'lucide:medal',
                'lucide:graduation-cap', 'lucide:book-open', 'lucide:pencil', 'lucide:palette'
            ],
            'Phosphor (Thin)': [
                'ph:tent-thin', 'ph:mountain-thin', 'ph:compass-thin', 'ph:map-trifold-thin',
                'ph:fire-thin', 'ph:leaf-thin', 'ph:tree-evergreen-thin', 'ph:waves-thin',
                'ph:cloud-sun-thin', 'ph:sun-thin', 'ph:moon-thin', 'ph:star-thin',
                'ph:heart-thin', 'ph:shield-thin', 'ph:award-thin', 'ph:medal-thin',
                'ph:student-thin', 'ph:book-thin', 'ph:pencil-circle-thin', 'ph:paint-brush-thin'
            ]
        },
        searchTimeout: null,

        open: function(initialIcon, onSelectCallback) {
            this.callback = onSelectCallback;
            this.selectedIcon = initialIcon || 'lucide:star';
            this.renderGrid();
            
            // Update preview in footer
            const preview = document.getElementById('globalSelectedIconPreview');
            if(preview) preview.setAttribute('icon', this.selectedIcon);
            const name = document.getElementById('globalSelectedIconName');
            if(name) name.textContent = this.selectedIcon;

            document.getElementById('globalIconPickerModal').classList.add('active');
            // Slight delay to allow transition before focus
            setTimeout(() => {
                const search = document.getElementById('globalIconSearch');
                if(search) {
                    search.value = '';
                    search.focus();
                }
            }, 100);
        },

        close: function() {
            document.getElementById('globalIconPickerModal').classList.remove('active');
            this.callback = null;
        },

        confirm: function() {
            if (this.callback) {
                this.callback(this.selectedIcon);
            }
            this.close();
        },

        select: function(icon) {
            this.selectedIcon = icon;
            
            // Visual feedback
            const items = document.querySelectorAll('#globalIconGridContainer .icon-item');
            items.forEach(el => el.classList.remove('selected'));
            
            const selectedEl = document.querySelector(`#globalIconGridContainer .icon-item[title="${icon}"]`);
            if (selectedEl) selectedEl.classList.add('selected');

            // Update footer preview
            const preview = document.getElementById('globalSelectedIconPreview');
            if(preview) preview.setAttribute('icon', icon);
            
            const name = document.getElementById('globalSelectedIconName');
            if(name) name.textContent = icon;
        },

        handleSearch: function(e) {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.renderGrid(e.target.value);
            }, 400);
        },

        renderGrid: async function(filter = '') {
            const container = document.getElementById('globalIconGridContainer');
            if (!container) return;

            const filterLower = filter.toLowerCase();
            
            // API Search Mode
            if (filter.length > 2) {
                container.innerHTML = '<div style="text-align:center;padding:20px;color:#fff;"><i class="fa-solid fa-circle-notch fa-spin"></i> Vasculhando 150.000 ícones...</div>';
                try {
                    const response = await fetch(`https://api.iconify.design/search?query=${filterLower}&limit=150`);
                    const data = await response.json();
                    
                    if (data.icons && data.icons.length > 0) {
                        let html = '<div class="icon-category-title">Resultados (Múltiplas Bibliotecas)</div><div class="icon-grid">';
                        data.icons.forEach(icon => {
                            const isSelected = icon === this.selectedIcon ? 'selected' : '';
                            html += `<div class="icon-item ${isSelected}" onclick="IconPicker.select('${icon}')" title="${icon}">
                                <iconify-icon icon="${icon}"></iconify-icon>
                                <span style="font-size: 6px; position: absolute; bottom: 2px; opacity: 0.5;">${icon.split(':')[0]}</span>
                            </div>`;
                        });
                        html += '</div>';
                        container.innerHTML = html;
                        return;
                    }
                } catch (err) { console.error('Icon search error:', err); }
            }

            // Local Categories Mode
            let html = '';
            for (const [category, icons] of Object.entries(this.categories)) {
                // Check if any icon in category matches or category title matches
                const filteredIcons = icons.filter(icon => 
                    filter === '' || icon.toLowerCase().includes(filterLower) || category.toLowerCase().includes(filterLower)
                );
                
                if (filteredIcons.length === 0) continue;

                html += `<div class="icon-category-title">${category}</div>`;
                html += '<div class="icon-grid">';
                
                filteredIcons.forEach(icon => {
                    const isSelected = icon === this.selectedIcon ? 'selected' : '';
                    html += `<div class="icon-item ${isSelected}" onclick="IconPicker.select('${icon}')" title="${icon}">
                        <iconify-icon icon="${icon}"></iconify-icon>
                    </div>`;
                });
                
                html += '</div>';
            }

            container.innerHTML = html || '<p style="text-align:center;color:#94a3b8;padding:40px;">Nenhum ícone encontrado. Tente buscar em inglês (ex: House, Fire)!</p>';
        }
    };
</script>
