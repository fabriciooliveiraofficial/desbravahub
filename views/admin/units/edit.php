<?php
$isEdit = !empty($unit);
$title = $isEdit ? 'Editar Unidade' : 'Nova Unidade';
?>
<!-- Font Import -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<style>
    :root {
        --font-main: 'Inter', sans-serif;
        --color-bg: #f8fafc;
        --color-surface: #ffffff;
        --color-text-main: #0f172a;
        --color-text-sub: #475569;
        --color-border: #e2e8f0;
        
        --color-accent: #06b6d4; /* Cyan 500 */
        --color-accent-hover: #0891b2;
        --color-accent-light: #ecfeff;
        
        --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        --shadow-color: 0 10px 30px -5px rgba(6, 182, 212, 0.3);
        
        --radius-lg: 16px;
        --radius-xl: 24px;
    }

    body {
        background-color: var(--color-bg);
        font-family: var(--font-main);
        color: var(--color-text-main);
    }

    .page-container {
        max-width: 1200px;
        margin: 0 auto;
        padding-bottom: 120px;
    }

    /* Header Section */
    .hero-header {
        margin-bottom: 40px;
        text-align: left;
        padding: 20px 0;
        border-bottom: 2px solid var(--color-border);
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
    }

    .hero-title {
        font-size: 2.5rem;
        font-weight: 800;
        color: var(--color-text-main);
        margin: 0;
        letter-spacing: -0.03em;
        line-height: 1.1;
        background: linear-gradient(135deg, #0f172a 0%, #334155 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .hero-subtitle {
        font-size: 1.1rem;
        color: var(--color-text-sub);
        font-weight: 500;
        margin-top: 8px;
        max-width: 600px;
    }

    /* Grid Layout */
    .unique-grid {
        display: grid;
        grid-template-columns: 1.5fr 1fr;
        gap: 32px;
        align-items: start;
    }

    @media (max-width: 900px) {
        .unique-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Cards */
    .design-card {
        background: var(--color-surface);
        border-radius: var(--radius-xl);
        padding: 32px;
        box-shadow: var(--shadow-md);
        border: 1px solid var(--color-border);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .design-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
        border-color: #cbd5e1;
    }

    .card-decoration {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 6px;
        background: linear-gradient(90deg, var(--color-accent), #22d3ee);
    }

    .section-head {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 28px;
    }

    .section-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: var(--color-accent-light);
        color: var(--color-accent);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .section-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--color-text-main);
        margin: 0;
    }

    /* Form Elements */
    .form-group {
        margin-bottom: 24px;
    }

    .form-label {
        display: block;
        font-weight: 700;
        font-size: 0.95rem;
        color: var(--color-text-main);
        margin-bottom: 8px;
    }

    .input-wrapper {
        position: relative;
    }

    .form-input, .form-textarea {
        width: 100%;
        padding: 16px;
        border-radius: 12px;
        border: 2px solid var(--color-border);
        background: #fdfdfd;
        color: var(--color-text-main);
        font-size: 1rem;
        font-weight: 500;
        transition: all 0.2s;
        font-family: inherit;
    }

    .form-input:focus, .form-textarea:focus {
        outline: none;
        background: #fff;
        border-color: var(--color-accent);
        box-shadow: 0 0 0 4px var(--color-accent-light);
    }

    .form-input::placeholder {
        color: #94a3b8;
        font-weight: 400;
    }

    /* Color Picker Special */
    .color-picker-wrapper {
        display: flex;
        align-items: center;
        gap: 16px;
        background: #fff;
        border: 2px solid var(--color-border);
        padding: 8px;
        border-radius: 12px;
    }
    
    input[type="color"] {
        -webkit-appearance: none;
        border: none;
        width: 42px;
        height: 42px;
        border-radius: 8px;
        cursor: pointer;
        padding: 0;
        overflow: hidden;
    }
    input[type="color"]::-webkit-color-swatch-wrapper {
        padding: 0;
    }
    input[type="color"]::-webkit-color-swatch {
        border: none;
    }

    /* Chips & Selectors */
    .chips-container {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 16px;
        min-height: 40px;
    }

    .chip-item {
        background: var(--color-accent-light);
        color: #0e7490;
        padding: 8px 16px;
        border-radius: 100px;
        font-weight: 600;
        font-size: 0.9rem;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: 1px solid #cffafe;
        animation: popIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    
    @keyframes popIn {
        from { transform: scale(0.8); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }
    
    @keyframes fadeEnterUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes fadeScaleOut {
        from { opacity: 1; transform: scale(1); }
        to { opacity: 0; transform: scale(0.8); }
    }

    .animate-enter {
        animation: fadeEnterUp 0.6s ease-out forwards;
        opacity: 0; /* Start hidden */
    }

    .chip-item.animate-in {
        animation: popIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
    }

    .chip-item.animate-out {
        animation: fadeScaleOut 0.2s ease-in forwards;
    }

    .chip-remove {
        cursor: pointer;
        width: 20px;
        height: 20px;
        background: rgba(255,255,255,0.6);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        transition: all 0.2s;
    }
    .chip-remove:hover {
        background: #ef4444;
        color: white;
    }

    .add-row {
        display: flex;
        gap: 12px;
    }
    
    .btn-add {
        background: var(--color-text-main);
        color: white;
        border: none;
        width: 48px;
        border-radius: 12px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.2s;
    }
    .btn-add:hover { transform: scale(1.05); }

    /* Footer Actions */
    .floating-footer {
        position: fixed;
        bottom: 30px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(12px);
        padding: 12px;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        border: 1px solid rgba(255,255,255,0.5);
        display: flex;
        gap: 12px;
        z-index: 100;
        width: auto;
        max-width: 90%;
    }

    .btn-submit {
        background: linear-gradient(135deg, var(--color-accent), #0891b2);
        color: white;
        border: none;
        padding: 12px 32px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 1rem;
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(6, 182, 212, 0.4);
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(6, 182, 212, 0.5);
    }

    .btn-cancel {
        background: transparent;
        color: var(--color-text-sub);
        border: none;
        padding: 12px 24px;
        border-radius: 12px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: flex;
        align-items: center;
    }
    .btn-cancel:hover { background: rgba(0,0,0,0.05); color: var(--color-text-main); }

    .btn-cancel:hover { background: rgba(0,0,0,0.05); color: var(--color-text-main); }

    /* ============ Icon Picker (Standardized) ============ */
    .icon-picker-trigger {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        width: 100%;
        background: #fff;
        border: 2px solid var(--color-border);
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.2s;
        text-align: left;
    }

    .icon-picker-trigger:hover {
        border-color: var(--color-accent);
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .icon-picker-trigger:focus {
        outline: none;
        border-color: var(--color-accent);
        box-shadow: 0 0 0 4px var(--color-accent-light);
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
    }

    .icon-picker-text {
        flex: 1;
        color: var(--color-text-sub);
        font-size: 0.95rem;
        font-weight: 500;
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
        z-index: 3000;
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
    }
    .btn-modal-save {
        background: #8b5cf6;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 8px;
        cursor: pointer;
    }

</style>

<div class="page-container">
    
    <header class="hero-header">
        <div>
            <h1 class="hero-title"><?= $title ?></h1>
            <p class="hero-subtitle">Defina a identidade e a liderança da sua unidade.</p>
        </div>
        <div style="font-size: 3rem; opacity: 0.1;">🚩</div>
    </header>

    <form method="POST" action="<?= base_url($tenant['slug'] . '/admin/unidades' . ($isEdit ? '/' . $unit['id'] : '')) ?>">
        
        <div class="unique-grid">
            
            <!-- Left Column: Identity -->
            <div class="column-left" style="display: flex; flex-direction: column; gap: 32px;">
                
                <div class="design-card">
                    <div class="card-decoration"></div>
                    <div class="section-head">
                        <div class="section-icon">
                            <span class="material-icons-round">badge</span>
                        </div>
                        <h2 class="section-title">Identidade Visual</h2>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nome da Unidade</label>
                        <input type="text" name="name" class="form-input" required 
                               value="<?= htmlspecialchars($unit['name'] ?? '') ?>" 
                               placeholder="Ex: Leões de Judá">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Lema de Guerra</label>
                        <input type="text" name="motto" class="form-input" 
                               value="<?= htmlspecialchars($unit['motto'] ?? '') ?>" 
                               placeholder="Ex: Força e honra!">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Sobre</label>
                        <textarea name="description" class="form-textarea" rows="4" 
                                  placeholder="Uma breve descrição da história ou propósito desta unidade..."><?= htmlspecialchars($unit['description'] ?? '') ?></textarea>
                    </div>
                </div>

            </div>

            <!-- Right Column: Details & People -->
            <div class="column-right" style="display: flex; flex-direction: column; gap: 32px;">
                
                <div class="design-card">
                    <div class="card-decoration" style="background: linear-gradient(90deg, #f59e0b, #fbbf24);"></div>
                    
                    <div class="form-group">
                        <label class="form-label">Cor da Bandeira</label>
                        <div class="color-picker-wrapper">
                            <input type="color" id="colorPicker" value="<?= htmlspecialchars($unit['color'] ?? '#06b6d4') ?>">
                            <input type="text" name="color" id="colorText" class="form-input" style="border:none; padding: 0;" 
                                   value="<?= htmlspecialchars($unit['color'] ?? '#06b6d4') ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Ícone / Mascote</label>
                        <div class="input-wrapper">
                            <!-- Store full FA class if possible, or fallback. Defaulting to a star. -->
                            <input type="hidden" name="mascot" id="mascotInput" value="<?= htmlspecialchars($unit['mascot'] ?? 'fa-solid fa-star') ?>">
                            
                            <button type="button" class="icon-picker-trigger" onclick="openIconPicker()">
                                <div class="icon-picker-preview" id="iconPreview">
                                     <?php 
                                         $currentIcon = $unit['mascot'] ?? 'lucide:star';
                                         echo '<iconify-icon icon="'.htmlspecialchars($currentIcon).'" style="font-size: 1.5rem;"></iconify-icon>';
                                     ?>
                                 </div>
                                 <span class="icon-picker-text" id="iconText"><?= htmlspecialchars($unit['mascot'] ?? 'Selecionar ícone') ?></span>
                                <i class="fa-solid fa-chevron-down icon-picker-arrow"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="design-card">
                    <div class="section-head">
                        <div class="section-icon" style="background: #e0e7ff; color: #4338ca;">
                            <span class="material-icons-round">groups</span>
                        </div>
                        <div>
                            <h2 class="section-title">Equipe</h2>
                            <p style="margin:0; font-size: 0.85rem; color: var(--color-text-sub);">Quem faz parte desta unidade?</p>
                        </div>
                    </div>

                    <!-- Counselors -->
                    <div class="form-group">
                        <label class="form-label">Conselheiros</label>
                        <div class="chips-container" id="counselorsSelected">
                            <?php if ($isEdit && !empty($unit['counselors'])): ?>
                                <?php foreach ($unit['counselors'] as $c): ?>
                                    <span class="chip-item" data-id="<?= $c['id'] ?>">
                                        <?= htmlspecialchars($c['name']) ?>
                                        <div class="chip-remove" onclick="removeChip(this, 'counselors')">✕</div>
                                        <input type="hidden" name="counselors[]" value="<?= $c['id'] ?>">
                                    </span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <div class="add-row">
                            <select id="counselorSelect" class="form-input">
                                <option value="">Novo conselheiro...</option>
                                <?php foreach ($counselors as $c): ?>
                                    <option value="<?= $c['id'] ?>" data-name="<?= htmlspecialchars($c['name']) ?>">
                                        <?= htmlspecialchars($c['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="button" class="btn-add" onclick="addChip('counselor')">
                                <span class="material-icons-round">add</span>
                            </button>
                        </div>
                    </div>

                    <!-- Pathfinders -->
                    <div class="form-group" style="margin-top: 32px;">
                        <label class="form-label">Desbravadores</label>
                        <div class="chips-container" id="pathfindersSelected">
                            <?php if ($isEdit && !empty($unit['members'])): ?>
                                <?php foreach ($unit['members'] as $m): ?>
                                    <span class="chip-item" data-id="<?= $m['id'] ?>">
                                        <?= htmlspecialchars($m['name']) ?>
                                        <div class="chip-remove" onclick="removeChip(this, 'pathfinders')">✕</div>
                                        <input type="hidden" name="pathfinders[]" value="<?= $m['id'] ?>">
                                    </span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <div class="add-row">
                            <select id="pathfinderSelect" class="form-input">
                                <option value="">Novo desbravador...</option>
                                <?php foreach ($pathfinders as $p): ?>
                                    <option value="<?= $p['id'] ?>" data-name="<?= htmlspecialchars($p['name']) ?>">
                                        <?= htmlspecialchars($p['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="button" class="btn-add" onclick="addChip('pathfinder')">
                                <span class="material-icons-round">add</span>
                            </button>
                        </div>
                    </div>

                </div>

            </div>
        </div>

        <!-- Floating Footer -->
        <div class="floating-footer">
            <a href="<?= base_url($tenant['slug'] . '/admin/unidades') ?>" class="btn-cancel">Cancelar</a>
            <button type="submit" class="btn-submit">
                <span class="material-icons-round">check</span>
                <?= $isEdit ? 'Salvar Alterações' : 'Criar Unidade' ?>
            </button>
        </div>

    </form>
</div>

<!-- Standardized Icon Picker Modal -->
<div class="icon-picker-overlay" id="iconPickerModal" onclick="closeIconPicker()">
    <div class="icon-picker-modal" onclick="event.stopPropagation()">
        <div class="icon-picker-header">
            <h3><i class="fa-solid fa-icons"></i> Selecionar Ícone</h3>
            <div class="icon-search-wrapper">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" class="icon-search" id="iconSearch" placeholder="Buscar ícones..." oninput="handleIconSearch(event)">
            </div>
        </div>
        <div class="icon-picker-body" id="iconGridContainer">
            <!-- Icons rendered by JS -->
        </div>
        <div class="icon-picker-footer">
            <div class="icon-picker-selected">
                <i id="selectedIconPreviewModal" class="fa-solid fa-star"></i>
                <span id="selectedIconName">fa-solid fa-star</span>
            </div>
            <div class="icon-picker-actions">
                <button type="button" class="btn-modal-cancel" onclick="closeIconPicker()">Cancelar</button>
                <button type="button" class="btn-modal-save" onclick="confirmIconSelection()">
                    <i class="fa-solid fa-check"></i> Selecionar
                </button>
            </div>
        </div>
    </div>
</div>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">

<script>
    // Entrance Animations
    document.addEventListener('DOMContentLoaded', () => {
        // Helper to animate
        const animate = (selector, delay = 0) => {
            document.querySelectorAll(selector).forEach((el, i) => {
                el.classList.add('animate-enter');
                el.style.animationDelay = `${delay + (i * 0.1)}s`;
            });
        };

        animate(".hero-title", 0);
        animate(".hero-subtitle", 0.1);
        animate(".design-card", 0.2);
        animate(".floating-footer", 0.8);

        // Color Picker Sync
        const picker = document.getElementById('colorPicker');
        const text = document.getElementById('colorText');
        if(picker && text) {
            picker.addEventListener('input', () => text.value = picker.value);
            text.addEventListener('input', () => picker.value = text.value);
        }
    });

    // Chip Logic
    function addChip(type) {
        try {
            const select = document.getElementById(type + 'Select');
            const container = document.getElementById(type + 'sSelected');
            
            if (!select || !container) {
                console.error('Elements not found for type:', type);
                return;
            }
            
            if (!select.value) {
                console.warn('No value selected');
                return;
            }

            const id = select.value;
            const selectedOption = select.options[select.selectedIndex];
            const name = selectedOption.dataset.name || selectedOption.text;

            // Duplicate check
            if (container.querySelector(`[data-id="${id}"]`)) {
                console.log('Duplicate item prevented');
                return;
            }

            const chip = document.createElement('span');
            chip.className = 'chip-item';
            chip.dataset.id = id;
            chip.innerHTML = `${name} <div class="chip-remove" onclick="removeChip(this)">✕</div><input type="hidden" name="${type}s[]" value="${id}">`;
            
            container.appendChild(chip);
            select.value = '';
            
            // Animation
            chip.classList.add('animate-in');
        } catch (e) {
            console.error('Error adding chip:', e);
            alert('Erro ao adicionar item: ' + e.message);
        }
    }

    function removeChip(el) {
        const chip = el.parentElement;
        chip.classList.add('animate-out');
        setTimeout(() => chip.remove(), 200);
    }

    // ============ Modern Icon Picker Logic (Iconify) ============
    var iconCategories = {
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
        ],
        'Solar (Linear)': [
            'solar:tent-linear', 'solar:mountains-linear', 'solar:compass-linear', 'solar:map-linear',
            'solar:fire-linear', 'solar:leaf-linear', 'solar:tree-linear', 'solar:water-linear',
            'solar:cloud-sun-linear', 'solar:sun-linear', 'solar:moon-linear', 'solar:star-linear',
            'solar:heart-linear', 'solar:shield-linear', 'solar:cup-linear', 'solar:medal-ribbons-star-linear',
            'solar:diploma-linear', 'solar:book-linear', 'solar:pen-linear', 'solar:paint-roller-linear'
        ],
        'Tabler (Outline)': [
            'tabler:tent', 'tabler:mountain', 'tabler:compass', 'tabler:map-2',
            'tabler:flame', 'tabler:leaf', 'tabler:tree', 'tabler:waves-rect',
            'tabler:cloud-sun', 'tabler:sun', 'tabler:moon', 'tabler:star',
            'tabler:heart', 'tabler:shield', 'tabler:award', 'tabler:medal-2',
            'tabler:school', 'tabler:book-2', 'tabler:pencil', 'tabler:brush'
        ]
    };

    var selectedIcon = 'lucide:star';

    function openIconPicker() {
        renderIconGrid();
        document.getElementById('iconPickerModal').classList.add('active');
        document.getElementById('iconSearch').focus();
    }

    function closeIconPicker() {
        document.getElementById('iconPickerModal').classList.remove('active');
    }

    async function renderIconGrid(filter = '') {
        const container = document.getElementById('iconGridContainer');
        const filterLower = filter.toLowerCase();
        
        if (filter.length > 2) {
            container.innerHTML = '<div style="text-align:center;padding:20px;"><i class="fa-solid fa-circle-notch fa-spin"></i> Buscando na nuvem...</div>';
            try {
                // Search across multiple modern libraries via Iconify API
                const libraries = ['lucide', 'ph', 'tabler', 'solar', 'carbon'];
                const response = await fetch(`https://api.iconify.design/search?query=${filterLower}&limit=64&prefixes=${libraries.join(',')}`);
                const data = await response.json();
                
                if (data.icons && data.icons.length > 0) {
                    let html = '<div class="icon-category-title">Resultados da Busca</div><div class="icon-grid">';
                    data.icons.forEach(icon => {
                        const isSelected = icon === selectedIcon ? 'selected' : '';
                        html += `<div class="icon-item ${isSelected}" onclick="selectIcon('${icon}')" title="${icon}">
                            <iconify-icon icon="${icon}"></iconify-icon>
                        </div>`;
                    });
                    html += '</div>';
                    container.innerHTML = html;
                    return;
                }
            } catch (err) { console.error('Icon search error:', err); }
        }

        let html = '';
        for (const [category, icons] of Object.entries(iconCategories)) {
            const filteredIcons = icons.filter(icon => 
                filter === '' || icon.toLowerCase().includes(filterLower) || category.toLowerCase().includes(filterLower)
            );
            
            if (filteredIcons.length === 0) continue;

            html += `<div class="icon-category-title">${category}</div>`;
            html += '<div class="icon-grid">';
            
            filteredIcons.forEach(icon => {
                const isSelected = icon === selectedIcon ? 'selected' : '';
                html += `<div class="icon-item ${isSelected}" onclick="selectIcon('${icon}')" title="${icon}">
                    <iconify-icon icon="${icon}"></iconify-icon>
                </div>`;
            });
            
            html += '</div>';
        }

        container.innerHTML = html || '<p style="text-align:center;color:#94a3b8;padding:40px;">Nenhum ícone encontrado. Tente buscar algo diferente!</p>';
    }

    function selectIcon(iconClass) {
        selectedIcon = iconClass;
        document.querySelectorAll('.icon-item').forEach(el => el.classList.remove('selected'));
        // Use a more robust selector since title might have special characters
        const items = document.querySelectorAll('.icon-item');
        items.forEach(item => {
            if (item.getAttribute('title') === iconClass) {
                item.classList.add('selected');
                item.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        });
        
        document.getElementById('selectedIconPreviewModal').innerHTML = `<iconify-icon icon="${selectedIcon}"></iconify-icon>`;
        document.getElementById('selectedIconName').textContent = selectedIcon;
    }

    function confirmIconSelection() {
        document.getElementById('mascotInput').value = selectedIcon;
        document.getElementById('iconPreview').innerHTML = `<iconify-icon icon="${selectedIcon}" style="font-size: 1.5rem;"></iconify-icon>`;
        document.getElementById('iconText').textContent = selectedIcon;
        closeIconPicker();
    }

    let searchTimeout = null;
    function handleIconSearch(e) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            renderIconGrid(e.target.value);
        }, 400);
    }
</script>