<?php
/**
 * Admin: Edit Specialty Requirements
 * 
 * Interface for adding/editing requirements for a specialty.
 */
$pageTitle = 'Editar Requisitos - ' . htmlspecialchars($specialty['name']);
?>
<style>
    .page-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        padding: 16px;
        background: var(--bg-card);
        border-radius: 12px;
        border: 1px solid var(--border-light);
    }

    .header-actions {
        display: flex;
        gap: 12px;
        margin-left: auto;
    }

    /* Requirements List */
    .requirements-container {
        /* Removed container border/bg as items will be cards */
        background: transparent;
        border: none;
    }

    .requirements-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .requirements-header h2 {
        margin: 0;
        font-size: 1.2rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .btn-add {
        padding: 10px 20px;
        background: rgba(0, 217, 255, 0.1);
        color: var(--accent-cyan);
        border: 1px solid rgba(0, 217, 255, 0.3);
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        font-size: 0.9rem;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-add:hover {
        background: rgba(0, 217, 255, 0.2);
        transform: translateY(-2px);
    }

    .requirements-list {
        padding: 0;
        list-style: none;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .requirement-item {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 0;
        /* Remove padding to handle inner sections */
        display: flex;
        flex-direction: column;
        transition: all 0.2s ease;
        position: relative;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        /* Ensure headers don't overflow corners */
    }

    .requirement-item:hover {
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.08);
        border-color: #cbd5e1;
        transform: translateY(-2px);
    }

    /* CARD HEADER: Handle + Number + Title + Type */
    .req-header-row {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px 20px;
        background: #f8fafc;
        /* Distinct header background */
        border-bottom: 1px solid #e2e8f0;
    }

    .req-handle {
        color: #94a3b8;
        cursor: grab;
        padding: 6px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        transition: all 0.2s;
    }

    .req-handle:hover {
        color: var(--accent-cyan);
        border-color: var(--accent-cyan);
        background: #f0f9ff;
    }

    .req-number {
        width: 38px;
        height: 38px;
        background: linear-gradient(135deg, var(--accent-cyan), var(--accent-green));
        color: #0f172a;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 1.1rem;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        flex-shrink: 0;
    }

    .req-title-input {
        flex: 1;
        padding: 10px 14px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        color: var(--text-main);
        font-size: 1rem;
        font-weight: 600;
        transition: all 0.2s;
    }

    .req-title-input:focus {
        border-color: var(--accent-cyan);
        box-shadow: 0 0 0 3px rgba(0, 217, 255, 0.1);
        outline: none;
    }

    .req-type-select {
        padding: 10px 32px 10px 14px;
        /* Space for arrow */
        background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%2364748b' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E") no-repeat right 10px center;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        color: var(--text-secondary);
        font-size: 0.9rem;
        font-weight: 500;
        cursor: pointer;
        appearance: none;
        /* Remove default arrow */
    }

    .req-type-select:focus {
        border-color: var(--accent-cyan);
        outline: none;
    }

    /* CARD BODY: Description */
    .req-body {
        padding: 16px 20px;
    }

    .req-description {
        width: 100%;
        padding: 12px 14px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        color: var(--text-secondary);
        resize: vertical;
        min-height: 80px;
        font-family: inherit;
        font-size: 0.95rem;
        line-height: 1.6;
    }

    .req-description:focus {
        border-color: var(--accent-cyan);
        outline: none;
        color: var(--text-main);
    }

    /* CARD FOOTER: Points + Actions */
    .req-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 20px;
        background: #fff;
        border-top: 1px solid #f1f5f9;
    }

    .req-points {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 6px 12px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
    }

    .req-points label {
        font-size: 0.8rem;
        text-transform: uppercase;
        font-weight: 700;
        color: #64748b;
    }

    .req-points input {
        width: 40px;
        background: transparent;
        border: none;
        color: #0f172a;
        font-weight: 700;
        text-align: center;
        font-size: 1rem;
    }

    .req-points input:focus {
        outline: none;
        color: var(--accent-cyan);
    }

    .btn-delete {
        padding: 8px 16px;
        background: #fee2e2;
        /* Red-100 */
        color: #dc2626;
        /* Red-600 */
        border: 1px solid #fecaca;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
    }

    .btn-delete:hover {
        background: #fecaca;
        transform: translateY(-1px);
    }

    .btn-delete::after {
        content: "Excluir";
    }

    /* Questions Editor */
    .questions-section {
        margin: 0 20px 20px;
        padding: 16px;
        background: #f8fafc;
        border: 1px dashed #e2e8f0;
        border-radius: 8px;
    }

    .questions-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }

    .questions-header h4 {
        margin: 0;
        font-size: 0.9rem;
        color: var(--text-secondary);
    }

    .question-item {
        background: rgba(0, 0, 0, 0.2);
        border-radius: 6px;
        padding: 12px;
        margin-bottom: 12px;
    }

    .question-text {
        width: 100%;
        padding: 10px;
        background: rgba(0, 0, 0, 0.3);
        border: 1px solid var(--border-light);
        border-radius: 6px;
        color: var(--text-primary);
        margin-bottom: 10px;
    }

    .options-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .option-item {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .option-item input[type="text"] {
        flex: 1;
        padding: 8px 12px;
        background: rgba(0, 0, 0, 0.3);
        border: 1px solid var(--border-light);
        border-radius: 6px;
        color: var(--text-primary);
    }

    .option-item input[type="radio"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }

    /* Empty state */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: var(--text-secondary);
    }

    .empty-state .icon {
        font-size: 4rem;
        margin-bottom: 16px;
    }

    .empty-state p {
        margin-bottom: 20px;
    }

    /* Toast */
    .toast {
        position: fixed;
        bottom: 24px;
        right: 24px;
        padding: 16px 24px;
        background: var(--bg-card);
        border-left: 4px solid var(--accent-green);
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        transform: translateX(150%);
        transition: transform 0.3s ease;
        z-index: 1000;
    }

    .toast.show {
        transform: translateX(0);
    }

    .toast.error {
        border-left-color: #f44336;
    }

    .confirm-modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.85);
        z-index: 2000;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .confirm-modal-overlay.active {
        display: flex;
    }

    .confirm-modal {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        max-width: 380px;
        width: 100%;
        padding: 32px 24px;
        text-align: center;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        transform: scale(0.95);
        animation: modalPop 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
    }

    @keyframes modalPop {
        to {
            transform: scale(1);
        }
    }

    .confirm-modal-icon {
        font-size: 3.5rem;
        margin-bottom: 20px;
        display: inline-block;
        filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.1));
    }

    .confirm-modal h3 {
        margin: 0 0 12px;
        font-size: 1.25rem;
        color: #0f172a;
        font-weight: 700;
    }

    .confirm-modal p {
        color: #64748b;
        margin: 0 0 28px;
        line-height: 1.5;
        font-size: 1rem;
    }

    .confirm-modal-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .confirm-modal-actions button {
        padding: 12px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        border: 1px solid transparent;
        font-size: 0.95rem;
        transition: all 0.2s;
    }

    .btn-confirm-cancel {
        background: #fff;
        border-color: #e2e8f0;
        color: #64748b;
    }

    .btn-confirm-cancel:hover {
        background: #f8fafc;
        color: #0f172a;
        border-color: #cbd5e1;
    }

    .btn-confirm-danger {
        background: #ef4444;
        color: white;
        box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.3);
    }

    .btn-confirm-danger:hover {
        background: #dc2626;
        box-shadow: 0 6px 12px -2px rgba(239, 68, 68, 0.4);
        transform: translateY(-1px);
    }

    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            padding-top: 70px;
        }

        .req-title-row {
            flex-direction: column;
        }

        .req-type-select {
            width: 100%;
        }
    }
</style>
</head>

<body>
    <!-- Content -->

    <div class="page-toolbar"
        style="background: var(--bg-card); border: 1px solid var(--border-light); border-radius: 12px; padding: 16px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
        <div style="display: flex; align-items: center; gap: 16px;">
            <div
                style="font-size: 2.2rem; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,0.2); border-radius: 12px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);">
                <?= $specialty['badge_icon'] ?? '📘' ?>
            </div>
            <div>
                <h3
                    style="margin:0; font-size:1.1rem; color:var(--text-main); font-weight: 600; letter-spacing: -0.01em;">
                    <?= htmlspecialchars($specialty['name']) ?>
                </h3>
                <p style="margin:4px 0 0 0; font-size:0.9rem; color:var(--text-secondary)">Gerencie os requisitos e
                    perguntas</p>
            </div>
        </div>
        <div class="header-actions" style="display: flex; gap: 10px;">
            <div style="display: flex; align-items: center; margin-right: 15px;">
                <span style="padding: 6px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; 
                    background: <?= ($specialty['status'] ?? 'active') === 'active' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(251, 191, 36, 0.1)' ?>; 
                    color: <?= ($specialty['status'] ?? 'active') === 'active' ? '#10b981' : '#f59e0b' ?>; 
                    border: 1px solid <?= ($specialty['status'] ?? 'active') === 'active' ? 'rgba(16, 185, 129, 0.2)' : 'rgba(251, 191, 36, 0.2)' ?>;">
                    <?= ($specialty['status'] ?? 'active') === 'active' ? '✅ Publicado' : '📝 Rascunho' ?>
                </span>
            </div>
            
            <a href="<?= base_url($tenant['slug'] . '/admin/especialidades') ?>" class="btn-toolbar secondary"
                style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: 8px; text-decoration: none; font-weight: 500; font-size: 0.9rem; transition: all 0.2s;">
                <span class="material-icons-round" style="font-size: 1.2rem;">arrow_back</span> Voltar
            </a>
            
            <?php if (($specialty['status'] ?? 'active') !== 'active'): ?>
            <button class="btn-toolbar success" onclick="publishSpecialty()"
                style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 24px; border-radius: 10px; border: none; font-weight: 700; font-size: 0.95rem; cursor: pointer; transition: all 0.3s ease; background: linear-gradient(135deg, #10b981, #059669); color: white; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);">
                <span class="material-icons-round" style="font-size: 1.2rem;">rocket_launch</span> Publicar
            </button>
            <?php endif; ?>

            <button class="btn-toolbar primary" onclick="saveAllRequirements()"
                style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 24px; border-radius: 10px; border: none; font-weight: 700; font-size: 0.95rem; cursor: pointer; transition: all 0.3s ease; background: linear-gradient(135deg, #00D9FF, #00FFA3); color: #0f172a !important; box-shadow: 0 4px 15px rgba(0, 217, 255, 0.3);">
                <span class="material-icons-round" style="font-size: 1.2rem;">save</span> Salvar Requisitos
            </button>
        </div>
    </div>

    <div class="requirements-container">
        <div class="requirements-header">
            <h2>📋 Requisitos</h2>
            <button class="btn-add" onclick="addRequirement()">
                ➕ Adicionar Requisito
            </button>
        </div>

        <ul class="requirements-list" id="requirementsList">
            <?php if (empty($requirements)): ?>
                <li id="emptyState" style="text-align:center; padding: 40px; color: var(--text-secondary);">
                    Nenhum requisito adicionado ainda via JSON.
                </li>
            <?php else: ?>
                <?php foreach ($requirements as $idx => $req): ?>
                    <li class="requirement-item" data-id="<?= $req['id'] ?? '' ?>">
                        <div class="req-header-row">
                            <span class="req-handle">⋮⋮</span>
                            <span class="req-number"><?= $idx + 1 ?></span>
                            <input type="text" class="req-title-input" placeholder="Título do requisito"
                                value="<?= htmlspecialchars($req['title'] ?? '') ?>">
                            <select class="req-type-select" onchange="toggleQuestions(this)">
                                <option value="text" <?= ($req['type'] ?? '') === 'text' ? 'selected' : '' ?>>📝 Texto
                                </option>
                                <option value="multiple_choice" <?= ($req['type'] ?? '') === 'multiple_choice' ? 'selected' : '' ?>>🔘 Múltipla Escolha</option>
                                <option value="file_upload" <?= ($req['type'] ?? '') === 'file_upload' ? 'selected' : '' ?>>📁
                                    Upload</option>
                                <option value="practical" <?= ($req['type'] ?? '') === 'practical' ? 'selected' : '' ?>>🏕️
                                    Prático</option>
                            </select>
                        </div>

                        <div class="req-body">
                            <textarea class="req-description"
                                placeholder="Descrição detalhada..."><?= htmlspecialchars($req['description'] ?? '') ?></textarea>
                            
                            <!-- Existing Questions -->
                            <div class="questions-section" style="margin-top: 20px;">
                                <div class="questions-header">
                                    <h4>Perguntas de Validação</h4>
                                    <button type="button" class="btn-add-question" onclick="addQuestion(this)" 
                                        style="background:transparent; border:1px dashed var(--accent-cyan); color:var(--accent-cyan); padding:4px 12px; border-radius:6px; cursor:pointer; font-size:0.8rem;">
                                        + Adicionar Pergunta
                                    </button>
                                </div>
                                <div class="questions-list">
                                    <?php if (!empty($req['questions'])): ?>
                                        <?php foreach ($req['questions'] as $qIdx => $q): ?>
                                            <div class="question-item">
                                                <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                                                    <span style="font-weight:600; font-size:0.85rem; color:var(--text-secondary);">Pergunta <?= $qIdx + 1 ?></span>
                                                    <button type="button" onclick="this.closest('.question-item').remove()" style="color:#ef4444; background:none; border:none; cursor:pointer;">&times;</button>
                                                </div>
                                                <input type="text" class="question-text" placeholder="Digite a pergunta..." value="<?= htmlspecialchars(is_array($q) ? ($q['text'] ?? '') : $q) ?>">
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="req-footer">
                            <div class="req-points">
                                <label>Pontos</label>
                                <input type="number" value="<?= $req['points'] ?? 10 ?>" min="1" max="100">
                            </div>
                            <button class="btn-delete" onclick="removeRequirement(this)" title="Remover">
                                🗑️
                            </button>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>

    <script>
        // Requirements Management Script
        
        function addRequirement() {
            const list = document.getElementById('requirementsList');
            const emptyState = document.getElementById('emptyState');
            if (emptyState) emptyState.remove();

            const count = list.querySelectorAll('.requirement-item').length + 1;
            const li = document.createElement('li');
            li.className = 'requirement-item';
            
            li.innerHTML = `
                <div class="req-header-row">
                    <span class="req-handle">⋮⋮</span>
                    <span class="req-number">${count}</span>
                    <input type="text" class="req-title-input" placeholder="Título do requisito" value="">
                    <select class="req-type-select" onchange="toggleQuestions(this)">
                        <option value="text">📝 Texto</option>
                        <option value="multiple_choice">🔘 Múltipla Escolha</option>
                        <option value="file_upload">📁 Upload</option>
                        <option value="practical">🏕️ Prático</option>
                    </select>
                </div>

                <div class="req-body">
                    <textarea class="req-description" placeholder="Descrição detalhada..."></textarea>
                    
                    <!-- Questions Container (Hidden by default unless needed) -->
                    <div class="questions-section" style="display:none; margin-top: 20px;">
                        <div class="questions-header">
                            <h4>Perguntas de Validação</h4>
                            <button type="button" class="btn-add-question" onclick="addQuestion(this)" 
                                style="background:transparent; border:1px dashed var(--accent-cyan); color:var(--accent-cyan); padding:4px 12px; border-radius:6px; cursor:pointer; font-size:0.8rem;">
                                + Adicionar Pergunta
                            </button>
                        </div>
                        <div class="questions-list"></div>
                    </div>
                </div>

                <div class="req-footer">
                    <div class="req-points">
                        <label>Pontos</label>
                        <input type="number" value="10" min="1" max="100">
                    </div>
                    <button class="btn-delete" onclick="removeRequirement(this)" title="Remover">
                        🗑️
                    </button>
                </div>
            `;
            
            list.appendChild(li);
        }

        function removeRequirement(btn) {
            if (!confirm('Remover este requisito?')) return;
            const li = btn.closest('li');
            li.remove();
            reindexRequirements();
        }

        function reindexRequirements() {
            const list = document.getElementById('requirementsList');
            const items = list.querySelectorAll('.requirement-item');
            
            if (items.length === 0) {
                list.innerHTML = `<li id="emptyState" style="text-align:center; padding: 40px; color: var(--text-secondary);">
                    Nenhum requisito adicionado ainda.
                </li>`;
            } else {
                items.forEach((item, index) => {
                    item.querySelector('.req-number').textContent = index + 1;
                });
            }
        }

        function toggleQuestions(select) {
            const reqItem = select.closest('.requirement-item');
            const questionsSection = reqItem.querySelector('.questions-section');
            
            // Show questions for all types except maybe 'practical' if desired, 
            // but usually we want questions for all validation types.
            // For now, let's show it for all types to give flexibility.
            questionsSection.style.display = 'block';
        }

        function addQuestion(btn) {
            const questionsList = btn.closest('.questions-section').querySelector('.questions-list');
            const count = questionsList.children.length + 1;
            
            const div = document.createElement('div');
            div.className = 'question-item';
            div.innerHTML = `
                <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                    <span style="font-weight:600; font-size:0.85rem; color:var(--text-secondary);">Pergunta ${count}</span>
                    <button type="button" onclick="this.closest('.question-item').remove()" style="color:#ef4444; background:none; border:none; cursor:pointer;">&times;</button>
                </div>
                <input type="text" class="question-text" placeholder="Digite a pergunta...">
                
                <!-- Options for Multiple Choice (could be dynamic based on requirement type) -->
                <!-- For simplicity, we just allow text input for question now. 
                     If the requirement type is Multiple Choice, we might expect options here.
                     Let's keep it simple: Just the question text. -->
            `;
            questionsList.appendChild(div);
        }

        async function saveAllRequirements() {
            const items = document.querySelectorAll('.requirement-item');
            const requirements = [];
            
            items.forEach(item => {
                const questions = [];
                const qList = item.querySelectorAll('.question-item');
                qList.forEach(q => {
                    const text = q.querySelector('.question-text').value;
                    if(text.trim()) questions.push({ text });
                });

                requirements.push({
                    id: item.dataset.id || null, // Keep ID if editing existing
                    title: item.querySelector('.req-title-input').value,
                    type: item.querySelector('.req-type-select').value,
                    description: item.querySelector('.req-description').value,
                    points: item.querySelector('.req-points input').value,
                    questions: questions
                });
            });

            const btn = document.querySelector('button[onclick="saveAllRequirements()"]');
            const originalText = btn.innerHTML;
            
            btn.disabled = true;
            btn.textContent = 'Salvando...';

            try {
                const url = '<?= base_url($tenant['slug'] . '/admin/especialidades/' . ($specialty['id'] ?? '') . '/requisitos') ?>';
                
                const resp = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ requirements })
                });

                const data = await resp.json();

                if (data.success) {
                    alert('Requisitos salvos com sucesso!');
                    window.location.reload(); // Reload to sync IDs
                } else {
                    alert(data.error || 'Erro ao salvar requisitos');
                }
            } catch (err) {
                console.error(err);
                alert('Erro de conexão ao salvar.');
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }

        function publishSpecialty() {
            if (!confirm('Deseja realmente publicar esta especialidade?\nEla ficará visível para todos os desbravadores.')) {
                return;
            }

            const btn = document.querySelector('button[onclick="publishSpecialty()"]');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.textContent = 'Publicando...';

            try {
                const url = '<?= base_url($tenant['slug'] . '/admin/especialidades/' . ($specialty['id'] ?? '') . '/publicar') ?>';
                
                const resp = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' }
                });

                const data = await resp.json();

                if (data.success) {
                    alert(data.message || 'Publicado com sucesso!');
                    window.location.reload();
                } else {
                    alert(data.error || 'Erro ao publicar.');
                }
            } catch (err) {
                console.error(err);
                alert('Erro de conexão.');
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }

        // Expose functions globally
        window.addRequirement = addRequirement;
        window.removeRequirement = removeRequirement;
        window.toggleQuestions = toggleQuestions;
        window.addQuestion = addQuestion;
        window.saveAllRequirements = saveAllRequirements;
        window.publishSpecialty = publishSpecialty;
    </script>