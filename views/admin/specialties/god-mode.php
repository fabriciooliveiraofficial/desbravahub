<?php
/**
 * Admin: God Mode - Mission Control Dashboard
 * Panoptic view of all assignments and real-time progress.
 */
?>

<style>
    :root {
        --god-blue: #0ea5e9;
        --god-purple: #8b5cf6;
        --god-bg: #f3f4f6;
        --god-card: #ffffff;
        --god-border: rgba(0, 0, 0, 0.05);
        --text-main: #1e293b;
        --text-muted: #64748b;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
    }

    /* Dark Mode Overrides */
    .dark .god-section {
        --god-bg: #0f172a;
        --god-card: #1e293b;
        --god-border: rgba(255, 255, 255, 0.05);
        --text-main: #f8fafc;
        --text-muted: #94a3b8;
    }

    /* .god-section removed for standard layout */
    
    .god-container {
        /* max-width and margin removed to fill available space like Dashboard */
    }

    /* --- Header & Stats --- */
    .god-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }

    .god-title h1 {
        font-size: 1.8rem;
        font-weight: 800;
        background: linear-gradient(90deg, var(--god-blue), var(--god-purple));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .god-stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-widget {
        background: var(--god-card);
        border: 1px solid var(--god-border);
        border-radius: 16px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        backdrop-filter: blur(10px);
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .stat-data .value {
        font-size: 1.5rem;
        font-weight: 700;
        display: block;
        color: var(--text-main);
    }

    .stat-data .label {
        font-size: 0.8rem;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    /* --- Mission Matrix Table --- */
    .mission-matrix {
        background: var(--god-card);
        border: 1px solid var(--god-border);
        border-radius: 20px;
        overflow: hidden;
        backdrop-filter: blur(10px);
        box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
    }

    .matrix-table {
        width: 100%;
        border-collapse: collapse;
    }

    .matrix-table th {
        text-align: left;
        padding: 16px 20px;
        background: rgba(0, 0, 0, 0.02);
        color: var(--text-muted);
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom: 1px solid var(--god-border);
    }

    .matrix-table td {
        padding: 16px 20px;
        border-bottom: 1px solid var(--god-border);
        vertical-align: middle;
        color: var(--text-main);
    }

    .matrix-table tr:hover {
        background: rgba(0, 0, 0, 0.01);
    }

    /* --- Pathfinder Info --- */
    .member-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .member-avatar {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: var(--god-purple);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: white;
        border: 2px solid rgba(255,255,255,0.1);
    }

    .member-info .name {
        display: block;
        font-weight: 600;
        font-size: 0.95rem;
    }

    .member-info .role {
        font-size: 0.75rem;
        color: var(--text-muted);
    }

    /* --- Mission Info --- */
    .mission-cell {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .mission-icon {
        font-size: 1.5rem;
        width: 32px;
        text-align: center;
    }

    .mission-name {
        font-weight: 600;
        font-size: 0.9rem;
    }

    /* --- Lifecycle Tracker --- */
    .lifecycle-tracker {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .step {
        width: 36px;
        height: 6px;
        border-radius: 3px;
        background: rgba(0, 0, 0, 0.05);
        position: relative;
    }

    .dark .step {
        background: rgba(255, 255, 255, 0.1);
    }

    .step.active {
        background: var(--god-blue);
        box-shadow: 0 0 10px var(--god-blue);
    }

    .step.completed {
        background: var(--success);
    }

    .step.warning {
        background: var(--warning);
    }

    .step-label {
        position: absolute;
        top: -22px;
        left: 50%;
        transform: translateX(-50%);
        font-size: 0.65rem;
        white-space: nowrap;
        color: var(--text-muted);
        text-transform: uppercase;
        display: none; /* Hide all by default */
        z-index: 10;
        pointer-events: none;
    }

    .step.active .step-label {
        display: block; /* Only show active step label */
        color: var(--god-blue);
        font-weight: 700;
        animation: fadeInDown 0.3s ease-out;
    }

    @keyframes fadeInDown {
        from { opacity: 0; transform: translate(-50%, -5px); }
        to { opacity: 1; transform: translate(-50%, 0); }
    }

    /* --- Progress Bar --- */
    .progress-box {
        width: 120px;
    }

    .progress-container {
        height: 8px;
        background: rgba(0, 0, 0, 0.05);
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid rgba(0, 0, 0, 0.05);
    }

    .dark .progress-container {
        background: rgba(0, 0, 0, 0.3);
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--god-blue), var(--god-purple));
        border-radius: 10px;
        transition: width 0.5s ease;
    }

    .progress-text {
        display: flex;
        justify-content: space-between;
        font-size: 0.7rem;
        margin-top: 4px;
        color: var(--text-muted);
    }

    /* --- Status Badges --- */
    .status-pill {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .status-pill.unread { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); }
    .status-pill.received { background: rgba(59, 130, 246, 0.1); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.2); }
    .status-pill.active { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2); }

    .btn-god-action {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: rgba(0, 0, 0, 0.03);
        border: 1px solid var(--god-border);
        color: var(--text-muted);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }

    .dark .btn-god-action {
        background: rgba(255, 255, 255, 0.05);
    }

    .btn-god-action:hover {
        background: var(--god-blue);
        color: white;
        border-color: var(--god-blue);
        transform: scale(1.1);
    }

    /* Live Indicator */
    .live-dot {
        width: 8px;
        height: 8px;
        background: var(--danger);
        border-radius: 50%;
        display: inline-block;
        margin-right: 8px;
        box-shadow: 0 0 10px var(--danger);
        animation: blink 1.5s infinite;
    }

    @keyframes blink {
        0% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.3; transform: scale(1.5); }
        100% { opacity: 1; transform: scale(1); }
    }

    .empty-matrix {
        padding: 60px;
        text-align: center;
        color: var(--text-muted);
    }

    /* HTMX Animations */
    tr.htmx-swapping {
        opacity: 0;
        transform: translateX(20px);
        transition: all 400ms ease-out;
    }
    /* --- Page Toolbar (Migrated) --- */
    .page-toolbar {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
        padding: 16px 24px;
        background: var(--god-card);
        border: 1px solid var(--god-border);
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
    }
    
    .search-section {
        flex: 1;
        min-width: 240px;
        max-width: 400px;
    }

    .search-wrapper {
        position: relative;
    }

    .search-input {
        width: 100%;
        padding: 10px 16px 10px 44px;
        border: 1px solid var(--god-border);
        border-radius: 8px;
        background: rgba(0,0,0,0.03);
        color: var(--text-main);
        font-size: 0.9rem;
        transition: all 0.2s;
    }

    .dark .search-input {
        background: rgba(0,0,0,0.2);
    }

    .search-input:focus {
        outline: none;
        border-color: var(--god-blue);
        box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
        background: var(--god-card);
    }

    .search-icon {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        font-size: 1.2rem;
        pointer-events: none;
    }

    .actions-group {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .btn-toolbar {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s;
        border: 1px solid transparent;
    }

    .btn-toolbar.secondary {
        background: transparent;
        border-color: var(--god-border);
        color: var(--text-muted);
    }

    .btn-toolbar.secondary:hover {
        border-color: var(--god-blue);
        color: var(--god-blue);
        background: rgba(14, 165, 233, 0.05);
    }

    .btn-toolbar.primary {
        background: linear-gradient(135deg, var(--god-blue), var(--god-purple));
        color: white;
        border: none;
    }

    .btn-toolbar.primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
    }

    /* --- Responsive Adjustments --- */
    @media (max-width: 768px) {
        .god-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 16px;
        }

        .god-stats {
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .stat-widget {
            padding: 16px;
        }

        .page-toolbar {
            padding: 16px;
            gap: 12px;
        }

        .search-section {
            max-width: none;
        }

        .actions-group {
            width: 100%;
        }

        .btn-toolbar {
            flex: 1;
            justify-content: center;
            padding: 8px 12px;
            font-size: 0.8rem;
        }

        .matrix-table td::before {
            content: attr(data-label);
            position: absolute;
            left: 1rem;
            width: 40%; /* Reduced from 45% to give more room */
            padding-right: 10px;
            white-space: nowrap;
            text-align: left;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            font-size: 0.75rem; /* Slightly larger for readability */
        }

        .matrix-table td {
            min-height: 60px; /* Increased from 45px */
            padding: 16px 1rem !important; /* Forces vertical spacing */
            padding-left: 45% !important; /* Aligns content better */
        }

        .progress-box {
            width: 100%;
            margin: 4px 0; /* Add vertical breathing room */
        }

        .lifecycle-tracker {
            justify-content: flex-end;
            width: 100%;
            padding: 8px 0;
        }
        
        .step {
            width: 25px;
        }
    }
</style>

<!-- Removed god-section wrapper -->
    <div class="god-container">
        <!-- Header -->


        <!-- Intelligent Toolbar -->
        <div class="page-toolbar">
            <div class="search-section">
                <div class="search-wrapper">
                    <span class="material-icons-round search-icon">search</span>
                    <input type="text" id="searchInput" class="search-input" placeholder="Filtrar missões, desbravadores..." oninput="filterMatrix()">
                </div>
            </div>
            <div class="actions-group">
                <a href="<?= base_url($tenant['slug'] . '/admin/especialidades/atribuicoes') ?>" class="btn-toolbar secondary">
                    <span class="material-icons-round">checklist</span> Atribuições
                </a>
                <button onclick="openCategoryModal()" class="btn-toolbar secondary">
                    <span class="material-icons-round">category</span> Nova Categoria
                </button>
                <button onclick="openCreateProgramModal()" class="btn-toolbar secondary">
                    <span class="material-icons-round">playlist_add</span> Criar Programa
                </button>
            </div>
        </div>

        <!-- Quick Stats -->
        <?php
        $counts = ['pending' => 0, 'received' => 0, 'active' => 0, 'completed' => 0];
        foreach ($assignments as $a) {
            if ($a['status'] === 'completed') $counts['completed']++;
            elseif ($a['status'] === 'in_progress' || $a['status'] === 'pending_review') $counts['active']++;
            elseif ($a['read_at']) $counts['received']++;
            else $counts['pending']++;
        }
        ?>
        <div class="god-stats">
            <div class="stat-widget">
                <div class="stat-icon" style="background: rgba(239, 68, 68, 0.1); color: var(--danger);"><i class="fa-solid fa-paper-plane"></i></div>
                <div class="stat-data">
                    <span class="value"><?= $counts['pending'] ?></span>
                    <span class="label">Liberadas (Invisíveis)</span>
                </div>
            </div>
            <div class="stat-widget">
                <div class="stat-icon" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;"><i class="fa-solid fa-check-double"></i></div>
                <div class="stat-data">
                    <span class="value"><?= $counts['received'] ?></span>
                    <span class="label">Desbravador Notou</span>
                </div>
            </div>
            <div class="stat-widget">
                <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: var(--success);"><i class="fa-solid fa-rocket"></i></div>
                <div class="stat-data">
                    <span class="value"><?= $counts['active'] ?></span>
                    <span class="label">Em Execução</span>
                </div>
            </div>
            <div class="stat-widget">
                <div class="stat-icon" style="background: rgba(139, 92, 246, 0.1); color: var(--god-purple);"><i class="fa-solid fa-award"></i></div>
                <div class="stat-data">
                    <span class="value"><?= $counts['completed'] ?></span>
                    <span class="label">Concluídas</span>
                </div>
            </div>
        </div>

        <!-- Main Tracking Matrix -->
        <div class="mission-matrix">
            <table class="matrix-table">
                <thead>
                    <tr>
                        <th width="250">Desbravador</th>
                        <th width="250">Missão / Especialidade</th>
                        <th width="300">Lifecycle Live</th>
                        <th width="150">Progresso</th>
                        <th width="120">Ações</th>
                    </tr>
                </thead>
                <tbody id="mission-matrix-body" 
                       hx-get="<?= base_url($tenant['slug'] . '/admin/especialidades/god-mode/matrix') ?>" 
                       hx-trigger="every 5s" 
                       hx-swap="innerHTML">
                    <?php require BASE_PATH . '/views/admin/specialties/partials/matrix-rows.php'; ?>
                </tbody>
            </table>
        </div>
        
        <div style="margin-top: 20px; text-align: right; color: var(--text-muted); font-size: 0.8rem;">
            <span class="live-dot"></span> Monitoramento em Tempo Real Ativo
        </div>
    </div>

<!-- Custom Confirmation Modal -->
<div id="god-confirm-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 25px; border-radius: 12px; width: 90%; max-width: 400px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); text-align: center;">
        <div style="font-size: 3rem; color: #f59e0b; margin-bottom: 15px;">
            <i class="fa-solid fa-circle-exclamation"></i>
        </div>
        <h3 style="margin: 0 0 10px; color: #1f2937; font-size: 1.25rem;">Remover Missão?</h3>
        <p style="color: #6b7280; margin-bottom: 25px; line-height: 1.5;">Esta ação removerá o progresso do desbravador nesta especialidade. Tem certeza?</p>
        <div style="display: flex; gap: 10px; justify-content: center;">
            <button onclick="closeGodModal(false)" style="padding: 10px 20px; border: 1px solid #d1d5db; background: white; color: #374151; border-radius: 6px; cursor: pointer; font-weight: 500;">Cancelar</button>
            <button onclick="closeGodModal(true)" style="padding: 10px 20px; border: none; background: #ef4444; color: white; border-radius: 6px; cursor: pointer; font-weight: 500;">Sim, Remover</button>
        </div>
    </div>
</div>

<!-- Vanilla JS Toast and Delete Logic (Event Delegation) -->
<script>
console.log('God Mode Script Loaded'); // Force log to see if module loads

// Robust Toast Implementation
window.Toast = {
    show: function(message, type) {
        let container = document.getElementById('god-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'god-toast-container';
            container.style.cssText = 'position: fixed; bottom: 30px; right: 30px; z-index: 999999; display: flex; flex-direction: column; gap: 10px; pointer-events: none;';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        
        let icon = '';
        let color = '#3b82f6';
        if (type === 'success') { icon = '✅'; color = '#22c55e'; }
        if (type === 'warning') { icon = '⚠️'; color = '#f59e0b'; }
        if (type === 'error') { icon = '❌'; color = '#ef4444'; }

        toast.innerHTML = `<span style="font-size: 1.2rem; margin-right: 10px;">${icon}</span><span style="font-weight: 600;">${message}</span>`;
        
        toast.style.cssText = `
            padding: 16px 24px;
            background: white;
            border-left: 5px solid ${color};
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.3s ease;
            pointer-events: auto;
            min-width: 300px;
        `;

        container.appendChild(toast);
        requestAnimationFrame(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translateY(0)';
        });

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(10px)';
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    }
};

// Modal Logic
let pendingServerDelete = null;

window.closeGodModal = function(confirmed) {
    const modal = document.getElementById('god-confirm-modal');
    modal.style.display = 'none';
    
    if (confirmed && pendingServerDelete) {
        pendingServerDelete();
    }
    pendingServerDelete = null;
};

function showGodModal(callback) {
    pendingServerDelete = callback;
    const modal = document.getElementById('god-confirm-modal');
    modal.style.display = 'flex';
}

function handleGlobalDelete(evt) {
    const btn = evt.target.closest('.btn-delete-mission');
    if (!btn) return;

    evt.preventDefault();
    evt.stopPropagation();

    const assignmentId = btn.dataset.assignmentId;
    console.log('Delete Clicked for:', assignmentId);

    if (!assignmentId) return;

    // Trigger Modal
    showGodModal(() => executeDelete(assignmentId, btn));
}

// Attach listener globally (once)
document.body.removeEventListener('click', handleGlobalDelete); // Safety removal
document.body.addEventListener('click', handleGlobalDelete); 

async function executeDelete(assignmentId, button) {
    console.log('LOG: executeDelete started for', assignmentId); // DEBUG

    // Define URL inside
    const DELETE_URL = '<?= base_url($tenant['slug'] . '/admin/especialidades/atribuicao/delete') ?>';
    console.log('LOG: Target URL', DELETE_URL); // DEBUG
    
    // UI Feedback
    const originalContent = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i>';
    const row = button.closest('tr');

    try {
        console.log('LOG: Starting fetch...'); // DEBUG
        const response = await fetch(DELETE_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'assignment_id=' + encodeURIComponent(assignmentId)
        });
        console.log('LOG: Response received', response.status, response.statusText); // DEBUG

        let data;
        try {
            const textHTML = await response.text(); // Get raw text first
            console.log('LOG: Raw response body:', textHTML.substring(0, 100) + '...'); // DEBUG
            
            try {
                data = JSON.parse(textHTML);
                console.log('LOG: JSON Parsed successfully', data); // DEBUG
            } catch(e) {
                // If not JSON, maybe it's the 200 OK HTML we were sending before?
                // Or maybe an error page.
                console.error('LOG: Failed to parse JSON', e);
                throw new Error('Resposta não é JSON válido');
            }

        } catch (e) {
            console.error('Error processing response:', e);
            if (response.ok) throw new Error('Erro de parsing mas resposta OK');
            throw new Error('Erro na resposta do servidor');
        }

        if (response.ok && data.success) {
            console.log('LOG: Success condition met'); // DEBUG
            window.Toast.show(data.message, 'success');
            if (row) {
                row.style.transition = 'all 0.5s ease';
                row.style.opacity = '0';
                row.style.transform = 'translateX(50px)';
                setTimeout(() => row.remove(), 500);
            }
        } else {
            console.log('LOG: Error condition met', data.message); // DEBUG
            window.Toast.show(data.message || 'Erro ao remover missão', 'warning');
            button.disabled = false;
            button.innerHTML = originalContent;
        }
    } catch (error) {
        console.error('Delete Error Log:', error);
        window.Toast.show('Erro de conexão: ' + error.message, 'error');
        button.disabled = false;
        button.innerHTML = originalContent;
    }
};
</script>

<?php require BASE_PATH . '/views/admin/specialties/god-mode-modals.php'; ?>

<script>
// ============================================
// MISSION CONTROL - CREATION WIZARD
// ============================================

const SPECIALTY_CREATE_URL = '<?= base_url($tenant['slug'] . '/admin/mission-control/specialty') ?>';
const CLASS_CREATE_URL = '<?= base_url($tenant['slug'] . '/admin/mission-control/class') ?>';

let currentWizardStep = 1;
let requirementCounter = 0;

// --- Modal Management ---
function openModal(modalId) {
    document.getElementById(modalId).style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
    document.body.style.overflow = '';
}

function openSpecialtyWizard() {
    closeModal('program-type-modal');
    resetSpecialtyWizard();
    openModal('specialty-wizard-modal');
}

function openClassModal() {
    closeModal('program-type-modal');
    document.getElementById('class-form').reset();
    openModal('class-modal');
}

// --- Wizard Navigation ---
function resetSpecialtyWizard() {
    currentWizardStep = 1;
    requirementCounter = 0;
    document.getElementById('specialty-wizard-form').reset();
    document.getElementById('requirements-list').innerHTML = `
        <div class="empty-requirements">
            <i class="fa-solid fa-list-check"></i>
            <p>Nenhum requisito adicionado ainda.</p>
            <button type="button" class="btn-add-first" onclick="addRequirement()">
                <i class="fa-solid fa-plus"></i> Adicionar Primeiro Requisito
            </button>
        </div>
    `;
    updateWizardUI();
}

function updateWizardUI() {
    // Update step indicators
    document.querySelectorAll('.wizard-step').forEach(step => {
        const stepNum = parseInt(step.dataset.step);
        step.classList.remove('active', 'completed');
        if (stepNum < currentWizardStep) step.classList.add('completed');
        if (stepNum === currentWizardStep) step.classList.add('active');
    });
    
    // Update panels
    document.querySelectorAll('.wizard-panel').forEach(panel => {
        const panelNum = parseInt(panel.dataset.panel);
        panel.classList.toggle('active', panelNum === currentWizardStep);
    });
    
    // If on review step, generate preview
    if (currentWizardStep === 3) {
        generateReviewPreview();
    }
}

function wizardNext() {
    // Validate current step
    if (currentWizardStep === 1) {
        const name = document.getElementById('spec-name').value.trim();
        const category = document.getElementById('spec-category').value;
        if (!name || !category) {
            window.Toast.show('Preencha nome e categoria', 'warning');
            return;
        }
    }
    
    if (currentWizardStep < 3) {
        currentWizardStep++;
        updateWizardUI();
    }
}

function wizardPrev() {
    if (currentWizardStep > 1) {
        currentWizardStep--;
        updateWizardUI();
    }
}

// --- Requirements Builder ---
function addRequirement() {
    // Remove empty state if exists
    const emptyState = document.querySelector('.empty-requirements');
    if (emptyState) emptyState.remove();
    
    const template = document.getElementById('requirement-template');
    const clone = template.content.cloneNode(true);
    const item = clone.querySelector('.requirement-item');
    
    requirementCounter++;
    item.dataset.index = requirementCounter;
    item.querySelector('.requirement-number').textContent = '#' + requirementCounter;
    
    document.getElementById('requirements-list').appendChild(clone);
}

function removeRequirement(btn) {
    const item = btn.closest('.requirement-item');
    item.style.opacity = '0';
    item.style.transform = 'translateX(50px)';
    setTimeout(() => {
        item.remove();
        renumberRequirements();
        
        // Show empty state if no requirements left
        const list = document.getElementById('requirements-list');
        if (list.children.length === 0) {
            list.innerHTML = `
                <div class="empty-requirements">
                    <i class="fa-solid fa-list-check"></i>
                    <p>Nenhum requisito adicionado ainda.</p>
                    <button type="button" class="btn-add-first" onclick="addRequirement()">
                        <i class="fa-solid fa-plus"></i> Adicionar Primeiro Requisito
                    </button>
                </div>
            `;
        }
    }, 300);
}

function renumberRequirements() {
    document.querySelectorAll('.requirement-item').forEach((item, idx) => {
        item.dataset.index = idx + 1;
        item.querySelector('.requirement-number').textContent = '#' + (idx + 1);
    });
    requirementCounter = document.querySelectorAll('.requirement-item').length;
}

function onRequirementTypeChange(select) {
    const container = select.closest('.requirement-item').querySelector('.quiz-questions-container');
    if (select.value === 'quiz') {
        container.style.display = 'block';
        // Add first question if none
        if (container.querySelector('.quiz-questions-list').children.length === 0) {
            addQuestion(container.querySelector('.btn-add-question'));
        }
    } else {
        container.style.display = 'none';
    }
}

// --- Quiz Question Builder ---
function addQuestion(btn) {
    const list = btn.closest('.quiz-questions-container').querySelector('.quiz-questions-list');
    const reqItem = btn.closest('.requirement-item');
    const reqIdx = reqItem.dataset.index;
    
    const template = document.getElementById('question-template');
    const clone = template.content.cloneNode(true);
    const qItem = clone.querySelector('.question-item');
    
    const qIdx = list.children.length;
    qItem.dataset.qindex = qIdx;
    qItem.querySelector('.question-number').textContent = 'Q' + (qIdx + 1);
    
    // Update radio names to be unique
    qItem.querySelectorAll('input[type="radio"]').forEach(radio => {
        radio.name = `correct_${reqIdx}_${qIdx}`;
    });
    
    list.appendChild(clone);
}

function removeQuestion(btn) {
    const qItem = btn.closest('.question-item');
    qItem.remove();
    
    // Renumber questions
    const list = btn.closest('.quiz-questions-list');
    list.querySelectorAll('.question-item').forEach((item, idx) => {
        item.dataset.qindex = idx;
        item.querySelector('.question-number').textContent = 'Q' + (idx + 1);
    });
}

// --- Review Preview ---
function generateReviewPreview() {
    const name = document.getElementById('spec-name').value;
    const icon = document.getElementById('spec-icon').value || '🏅';
    const category = document.getElementById('spec-category').selectedOptions[0]?.text || 'N/A';
    const difficulty = document.getElementById('spec-difficulty').selectedOptions[0]?.text || 'N/A';
    const xp = document.getElementById('spec-xp').value;
    const duration = document.getElementById('spec-duration').value;
    
    const requirements = [];
    document.querySelectorAll('.requirement-item').forEach(item => {
        const type = item.querySelector('.requirement-type').value;
        const desc = item.querySelector('.requirement-description').value;
        const typeLabels = { text: '📝 Texto', proof: '📷 Prova', quiz: '❓ Quiz' };
        requirements.push({ type, typeLabel: typeLabels[type], description: desc });
    });
    
    let reqsHtml = requirements.length > 0 
        ? requirements.map((r, i) => `
            <div class="review-requirement">
                <span class="req-type">${r.typeLabel}</span>
                <span>${r.description.substring(0, 60)}${r.description.length > 60 ? '...' : ''}</span>
            </div>
        `).join('') 
        : '<p style="color: var(--text-muted);">Nenhum requisito adicionado.</p>';
    
    document.getElementById('review-preview').innerHTML = `
        <div class="review-section">
            <h5>Especialidade</h5>
            <div class="review-specialty-card">
                <div class="review-icon">${icon}</div>
                <div class="review-info">
                    <h4>${name}</h4>
                    <div class="review-meta">
                        <span><i class="fa-solid fa-folder"></i> ${category}</span>
                        <span><i class="fa-solid fa-star"></i> ${difficulty}</span>
                        <span><i class="fa-solid fa-bolt"></i> ${xp} XP</span>
                        <span><i class="fa-solid fa-clock"></i> ${duration}h</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="review-section">
            <h5>Requisitos (${requirements.length})</h5>
            <div class="review-requirements-list">
                ${reqsHtml}
            </div>
        </div>
    `;
}

// --- Save Specialty ---
async function saveSpecialty(action) {
    const form = document.getElementById('specialty-wizard-form');
    
    // Collect data
    const specialty = {
        name: document.getElementById('spec-name').value.trim(),
        category_id: document.getElementById('spec-category').value,
        badge_icon: document.getElementById('spec-icon').value || '🏅',
        description: document.getElementById('spec-description').value,
        type: document.getElementById('spec-type').value,
        difficulty: parseInt(document.getElementById('spec-difficulty').value),
        duration_hours: parseInt(document.getElementById('spec-duration').value),
        xp_reward: parseInt(document.getElementById('spec-xp').value),
        publish: action === 'publish'
    };
    
    // Collect requirements
    const requirements = [];
    document.querySelectorAll('.requirement-item').forEach(item => {
        const req = {
            type: item.querySelector('.requirement-type').value,
            description: item.querySelector('.requirement-description').value.trim(),
            questions: []
        };
        
        if (req.type === 'quiz') {
            item.querySelectorAll('.question-item').forEach(qItem => {
                const options = [];
                let correctIndex = 0;
                qItem.querySelectorAll('.option-row').forEach((row, optIdx) => {
                    const optText = row.querySelector('.option-text').value.trim();
                    if (optText) options.push(optText);
                    if (row.querySelector('input[type="radio"]').checked) {
                        correctIndex = optIdx;
                    }
                });
                
                req.questions.push({
                    text: qItem.querySelector('.question-text').value.trim(),
                    options: options,
                    correct_index: correctIndex
                });
            });
        }
        
        if (req.description) {
            requirements.push(req);
        }
    });
    
    specialty.requirements = requirements;
    
    // Find submit buttons and disable them
    const btns = document.querySelectorAll('.wizard-panel[data-panel="3"] button');
    btns.forEach(b => b.disabled = true);
    
    try {
        const response = await fetch(SPECIALTY_CREATE_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(specialty)
        });
        
        const data = await response.json();
        
        if (response.ok && data.success) {
            window.Toast.show(data.message || 'Especialidade criada!', 'success');
            closeModal('specialty-wizard-modal');
            
            // Optionally redirect to edit requirements
            if (data.redirect) {
                setTimeout(() => window.location.href = data.redirect, 1500);
            }
        } else {
            window.Toast.show(data.error || 'Erro ao criar especialidade', 'error');
        }
    } catch (err) {
        console.error(err);
        window.Toast.show('Erro de conexão', 'error');
    } finally {
        btns.forEach(b => b.disabled = false);
    }
}

// --- Save Class ---
async function saveClass(evt) {
    evt.preventDefault();
    
    const classData = {
        name: document.getElementById('class-name').value.trim(),
        description: document.getElementById('class-description').value,
        icon: document.getElementById('class-icon').value || '🌱',
        color: document.getElementById('class-color').value || '#4CAF50'
    };
    
    if (!classData.name) {
        window.Toast.show('Nome da classe é obrigatório', 'warning');
        return false;
    }
    
    const btn = document.querySelector('#class-form button[type="submit"]');
    btn.disabled = true;
    
    try {
        const response = await fetch(CLASS_CREATE_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(classData)
        });
        
        const data = await response.json();
        
        if (response.ok && data.success) {
            window.Toast.show(data.message || 'Classe criada!', 'success');
            closeModal('class-modal');
            
            // Redirect to edit requirements (V3.1 Engine)
            if (data.redirect) {
                setTimeout(() => window.location.href = data.redirect, 1500);
            }
        } else {
            window.Toast.show(data.error || 'Erro ao criar classe', 'error');
        }
    } catch (err) {
        console.error(err);
        window.Toast.show('Erro de conexão', 'error');
    } finally {
        btn.disabled = false;
    }
    
    return false;
}
</script>

<script>
    /* --- Intelligent Filter Logic (Migrated) --- */
    function filterMatrix() {
        const query = document.getElementById('searchInput').value.toLowerCase().trim();
        const rows = document.querySelectorAll('#mission-matrix-body tr');
        
        rows.forEach(row => {
            const pathfinder = row.querySelector('.member-info .name')?.textContent?.toLowerCase() || '';
            const mission = row.querySelector('.mission-name')?.textContent?.toLowerCase() || '';
            
            // Allow searching by status as well? Maybe later.
            const match = pathfinder.includes(query) || mission.includes(query);
            row.style.display = match ? '' : 'none';
        });
    }

    // Re-apply filter after HTMX updates (Polling persistence)
    document.body.addEventListener('htmx:afterSwap', function(evt) {
        if (evt.target.id === 'mission-matrix-body') {
            const query = document.getElementById('searchInput').value;
            if (query && query.length > 0) {
                filterMatrix();
            }
        }
    });

    // Make tenantSlug available for Modals (if not already)
    if (typeof window.tenantSlug === 'undefined') {
        window.tenantSlug = '<?= $tenant['slug'] ?>';
    }
</script>

<!-- Create Program Modal (Reused) -->
<?php require BASE_PATH . '/views/admin/programs/partials/create_modal.php'; ?>

<!-- Create Category Modal (Reused) -->
<?php require BASE_PATH . '/views/admin/categories/partials/create_category_modal.php'; ?>
