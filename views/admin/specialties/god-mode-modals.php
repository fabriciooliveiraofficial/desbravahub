<?php
/**
 * Mission Control Modals - Creation Wizards
 * 
 * Contains modal HTML for:
 * - Specialty Creation Wizard (multi-step)
 * - Class Creation (simple form)
 */

use App\Services\SpecialtyService;

// Get categories for dropdown from JSON repository
$specialtyCategories = SpecialtyService::getCategories();
?>

<!-- Program Type Selector Modal -->
<div id="program-type-modal" class="god-modal" style="display: none;">
    <div class="god-modal-backdrop" onclick="closeModal('program-type-modal')"></div>
    <div class="god-modal-content god-modal-sm">
        <div class="god-modal-header">
            <h3><i class="fa-solid fa-plus-circle"></i> Novo Programa</h3>
            <button class="god-modal-close" onclick="closeModal('program-type-modal')">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
        <div class="god-modal-body">
            <p style="color: var(--text-muted); margin-bottom: 20px;">Selecione o tipo de programa que deseja criar:</p>
            
            <div class="program-type-options">
                <button class="program-type-btn" onclick="openSpecialtyWizard()">
                    <div class="program-type-icon" style="background: rgba(139, 92, 246, 0.1); color: var(--god-purple);">
                        <i class="fa-solid fa-award"></i>
                    </div>
                    <div class="program-type-info">
                        <strong>Especialidade</strong>
                        <span>Crie especialidades com requisitos e quizzes</span>
                    </div>
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
                
                <button class="program-type-btn" onclick="openClassModal()">
                    <div class="program-type-icon" style="background: rgba(14, 165, 233, 0.1); color: var(--god-blue);">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </div>
                    <div class="program-type-info">
                        <strong>Classe</strong>
                        <span>Crie classes para os desbravadores</span>
                    </div>
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Specialty Creation Wizard Modal -->
<div id="specialty-wizard-modal" class="god-modal" style="display: none;">
    <div class="god-modal-backdrop"></div>
    <div class="god-modal-content god-modal-lg">
        <div class="god-modal-header">
            <h3><i class="fa-solid fa-award"></i> Nova Especialidade</h3>
            <button class="god-modal-close" onclick="closeModal('specialty-wizard-modal')">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
        
        <!-- Wizard Steps Indicator -->
        <div class="wizard-steps">
            <div class="wizard-step active" data-step="1">
                <div class="step-number">1</div>
                <span>Dados Básicos</span>
            </div>
            <div class="wizard-step" data-step="2">
                <div class="step-number">2</div>
                <span>Requisitos</span>
            </div>
            <div class="wizard-step" data-step="3">
                <div class="step-number">3</div>
                <span>Revisão</span>
            </div>
        </div>
        
        <form id="specialty-wizard-form" onsubmit="return false;">
            <!-- Step 1: Basic Info -->
            <div class="wizard-panel active" data-panel="1">
                <div class="god-modal-body">
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label>Nome da Especialidade *</label>
                            <input type="text" name="name" id="spec-name" required placeholder="Ex: Primeiros Socorros" class="god-input">
                        </div>
                        
                        <div class="form-group">
                            <label>Categoria *</label>
                            <select name="category_id" id="spec-category" required class="god-input">
                                <option value="">Selecione...</option>
                                <?php foreach ($specialtyCategories as $cat): ?>
                                    <option value="<?= htmlspecialchars($cat['id']) ?>">
                                        <?= htmlspecialchars($cat['icon'] ?? '📁') ?> <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Ícone/Badge</label>
                            <div class="input-wrapper" style="display:flex; gap:8px;">
                                <input type="hidden" name="badge_icon" id="spec-icon" value="game-icons:rank-3">
                                <button type="button" class="god-input" onclick="openSpecIconPicker()" style="flex:1; display:flex; align-items:center; gap:10px; cursor:pointer;">
                                    <iconify-icon id="spec-icon-preview" icon="game-icons:rank-3" style="font-size:1.5rem;"></iconify-icon>
                                    <span id="spec-icon-text">game-icons:rank-3</span>
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-group full-width">
                            <label>Descrição</label>
                            <textarea name="description" id="spec-description" rows="3" class="god-input" placeholder="Descreva o objetivo desta especialidade..."></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Tipo</label>
                            <select name="type" id="spec-type" class="god-input">
                                <option value="indoor">🏠 Indoor</option>
                                <option value="outdoor">🏕️ Outdoor</option>
                                <option value="hybrid">🔄 Híbrido</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Dificuldade</label>
                            <select name="difficulty" id="spec-difficulty" class="god-input">
                                <option value="1">⭐ Muito Fácil</option>
                                <option value="2" selected>⭐⭐ Fácil</option>
                                <option value="3">⭐⭐⭐ Médio</option>
                                <option value="4">⭐⭐⭐⭐ Difícil</option>
                                <option value="5">⭐⭐⭐⭐⭐ Expert</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Duração (horas)</label>
                            <input type="number" name="duration_hours" id="spec-duration" value="4" min="1" max="100" class="god-input">
                        </div>
                        
                        <div class="form-group">
                            <label>XP de Recompensa</label>
                            <input type="number" name="xp_reward" id="spec-xp" value="100" min="10" max="1000" step="10" class="god-input">
                        </div>
                    </div>
                </div>
                <div class="god-modal-footer">
                    <button type="button" class="btn-god-cancel" onclick="closeModal('specialty-wizard-modal')">Cancelar</button>
                    <button type="button" class="btn-god-primary" onclick="wizardNext()">
                        Próximo <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </div>
            
            <!-- Step 2: Requirements -->
            <div class="wizard-panel" data-panel="2">
                <div class="god-modal-body">
                    <div class="requirements-header">
                        <h4>Requisitos da Especialidade</h4>
                        <button type="button" class="btn-add-requirement" onclick="addRequirement()">
                            <i class="fa-solid fa-plus"></i> Adicionar Requisito
                        </button>
                    </div>
                    
                    <div id="requirements-list" class="requirements-list">
                        <!-- Requirements will be added here dynamically -->
                        <div class="empty-requirements">
                            <i class="fa-solid fa-list-check"></i>
                            <p>Nenhum requisito adicionado ainda.</p>
                            <button type="button" class="btn-add-first" onclick="addRequirement()">
                                <i class="fa-solid fa-plus"></i> Adicionar Primeiro Requisito
                            </button>
                        </div>
                    </div>
                </div>
                <div class="god-modal-footer">
                    <button type="button" class="btn-god-cancel" onclick="wizardPrev()">
                        <i class="fa-solid fa-arrow-left"></i> Voltar
                    </button>
                    <button type="button" class="btn-god-primary" onclick="wizardNext()">
                        Próximo <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </div>
            
            <!-- Step 3: Review -->
            <div class="wizard-panel" data-panel="3">
                <div class="god-modal-body">
                    <h4 style="margin-bottom: 20px;">Revisão Final</h4>
                    
                    <div id="review-preview" class="review-preview">
                        <!-- Preview will be rendered here -->
                    </div>
                </div>
                <div class="god-modal-footer">
                    <button type="button" class="btn-god-cancel" onclick="wizardPrev()">
                        <i class="fa-solid fa-arrow-left"></i> Voltar
                    </button>
                    <button type="button" class="btn-god-secondary" onclick="saveSpecialty('draft')">
                        <i class="fa-solid fa-save"></i> Salvar Rascunho
                    </button>
                    <button type="button" class="btn-god-primary" onclick="saveSpecialty('publish')">
                        <i class="fa-solid fa-rocket"></i> Publicar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Class Creation Modal -->
<div id="class-modal" class="god-modal" style="display: none;">
    <div class="god-modal-backdrop" onclick="closeModal('class-modal')"></div>
    <div class="god-modal-content god-modal-sm">
        <div class="god-modal-header">
            <h3><i class="fa-solid fa-graduation-cap"></i> Nova Classe</h3>
            <button class="god-modal-close" onclick="closeModal('class-modal')">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
        <form id="class-form" onsubmit="return saveClass(event);">
            <div class="god-modal-body">
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label>Nome da Classe *</label>
                        <input type="text" name="name" id="class-name" required placeholder="Ex: Amigo" class="god-input">
                    </div>
                    
                    <div class="form-group full-width">
                        <label>Descrição</label>
                        <textarea name="description" id="class-description" rows="2" class="god-input" placeholder="Descrição opcional..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Ícone</label>
                        <div class="input-wrapper" style="display:flex; gap:8px;">
                            <input type="hidden" name="icon" id="god-class-icon" value="noto:seedling">
                            <button type="button" class="god-input" onclick="openGodClassIconPicker()" style="flex:1; display:flex; align-items:center; gap:10px; cursor:pointer;">
                                <iconify-icon id="god-class-icon-preview" icon="noto:seedling" style="font-size:1.5rem;"></iconify-icon>
                                <span id="god-class-icon-text">noto:seedling</span>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Cor</label>
                        <input type="color" name="color" id="class-color" value="#4CAF50" class="god-input god-color-input">
                    </div>
                </div>
            </div>
            <div class="god-modal-footer">
                <button type="button" class="btn-god-cancel" onclick="closeModal('class-modal')">Cancelar</button>
                <button type="submit" class="btn-god-primary">
                    <i class="fa-solid fa-check"></i> Criar Classe
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Requirement Template (hidden, cloned by JS) -->
<template id="requirement-template">
    <div class="requirement-item" data-index="0">
        <div class="requirement-header">
            <span class="requirement-number">#1</span>
            <select class="requirement-type god-input-sm" onchange="onRequirementTypeChange(this)">
                <option value="text">📝 Texto</option>
                <option value="proof">📷 Prova (Upload)</option>
                <option value="quiz">❓ Quiz</option>
            </select>
            <button type="button" class="btn-remove-req" onclick="removeRequirement(this)" title="Remover">
                <i class="fa-solid fa-trash"></i>
            </button>
        </div>
        <div class="requirement-body">
            <textarea class="requirement-description god-input" rows="2" placeholder="Descreva este requisito..." required></textarea>
            
            <!-- Quiz Questions Container (hidden by default) -->
            <div class="quiz-questions-container" style="display: none;">
                <div class="quiz-questions-header">
                    <span>Perguntas do Quiz</span>
                    <button type="button" class="btn-add-question" onclick="addQuestion(this)">
                        <i class="fa-solid fa-plus"></i> Pergunta
                    </button>
                </div>
                <div class="quiz-questions-list"></div>
            </div>
        </div>
    </div>
</template>

<!-- Question Template (hidden, cloned by JS) -->
<template id="question-template">
    <div class="question-item" data-qindex="0">
        <div class="question-header">
            <span class="question-number">Q1</span>
            <button type="button" class="btn-remove-question" onclick="removeQuestion(this)" title="Remover">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
        <input type="text" class="question-text god-input-sm" placeholder="Digite a pergunta..." required>
        <div class="question-options">
            <div class="option-row">
                <input type="radio" name="correct_0_0" value="0" checked>
                <input type="text" class="option-text god-input-sm" placeholder="Opção A (correta)" required>
            </div>
            <div class="option-row">
                <input type="radio" name="correct_0_0" value="1">
                <input type="text" class="option-text god-input-sm" placeholder="Opção B">
            </div>
            <div class="option-row">
                <input type="radio" name="correct_0_0" value="2">
                <input type="text" class="option-text god-input-sm" placeholder="Opção C">
            </div>
            <div class="option-row">
                <input type="radio" name="correct_0_0" value="3">
                <input type="text" class="option-text god-input-sm" placeholder="Opção D">
            </div>
        </div>
    </div>
</template>

<style>
/* Modal Base Styles */
.god-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 10001;
    display: flex;
    align-items: center;
    justify-content: center;
}

.god-modal-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
}

.god-modal-content {
    position: relative;
    background: var(--god-card);
    border-radius: 16px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    max-height: 90vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    animation: modalSlideIn 0.3s ease;
}

@keyframes modalSlideIn {
    from { opacity: 0; transform: translateY(-20px) scale(0.95); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

.god-modal-sm { width: 90%; max-width: 500px; }
.god-modal-lg { width: 90%; max-width: 800px; }

.god-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 1px solid var(--god-border);
}

.god-modal-header h3 {
    margin: 0;
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--text-main);
    display: flex;
    align-items: center;
    gap: 10px;
}

.god-modal-close {
    width: 32px;
    height: 32px;
    border: none;
    background: transparent;
    color: var(--text-muted);
    cursor: pointer;
    border-radius: 8px;
    transition: all 0.2s;
}

.god-modal-close:hover {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

.god-modal-body {
    padding: 24px;
    overflow-y: auto;
    flex: 1;
}

.god-modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding: 16px 24px;
    border-top: 1px solid var(--god-border);
    background: rgba(0, 0, 0, 0.02);
}

/* Program Type Selector */
.program-type-options {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.program-type-btn {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px;
    background: var(--god-bg);
    border: 2px solid var(--god-border);
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s;
    text-align: left;
    width: 100%;
}

.program-type-btn:hover {
    border-color: var(--god-blue);
    background: rgba(14, 165, 233, 0.05);
}

.program-type-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

.program-type-info {
    flex: 1;
}

.program-type-info strong {
    display: block;
    color: var(--text-main);
    margin-bottom: 4px;
}

.program-type-info span {
    font-size: 0.85rem;
    color: var(--text-muted);
}

/* Wizard Steps */
.wizard-steps {
    display: flex;
    justify-content: center;
    padding: 20px;
    background: rgba(0, 0, 0, 0.02);
    border-bottom: 1px solid var(--god-border);
}

.wizard-step {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 0 24px;
    color: var(--text-muted);
    position: relative;
}

.wizard-step:not(:last-child)::after {
    content: '';
    position: absolute;
    right: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 40px;
    height: 2px;
    background: var(--god-border);
}

.wizard-step.active::after,
.wizard-step.completed::after {
    background: var(--god-blue);
}

.step-number {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: var(--god-border);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.85rem;
}

.wizard-step.active .step-number {
    background: var(--god-blue);
    color: white;
}

.wizard-step.completed .step-number {
    background: var(--success);
    color: white;
}

.wizard-step.active {
    color: var(--god-blue);
    font-weight: 600;
}

.wizard-panel {
    display: none;
}

.wizard-panel.active {
    display: block;
}

/* Form Styles */
.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-group label {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--text-main);
}

.god-input {
    padding: 12px 14px;
    border: 1px solid var(--god-border);
    border-radius: 8px;
    background: var(--god-bg);
    color: var(--text-main);
    font-size: 0.95rem;
    transition: all 0.2s;
}

.god-input:focus {
    outline: none;
    border-color: var(--god-blue);
    box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
}

.god-input-sm {
    padding: 8px 12px;
    font-size: 0.9rem;
    border: 1px solid var(--god-border);
    border-radius: 6px;
    background: var(--god-bg);
    color: var(--text-main);
}

.god-color-input {
    height: 46px;
    padding: 4px;
    cursor: pointer;
}

/* Buttons */
.btn-god-primary {
    padding: 10px 20px;
    background: linear-gradient(90deg, var(--god-blue), var(--god-purple));
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
}

.btn-god-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3);
}

.btn-god-secondary {
    padding: 10px 20px;
    background: var(--god-bg);
    color: var(--text-main);
    border: 1px solid var(--god-border);
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
}

.btn-god-secondary:hover {
    background: var(--god-border);
}

.btn-god-cancel {
    padding: 10px 20px;
    background: transparent;
    color: var(--text-muted);
    border: none;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-god-cancel:hover {
    color: var(--text-main);
}

/* Requirements */
.requirements-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.requirements-header h4 {
    margin: 0;
    color: var(--text-main);
}

.btn-add-requirement,
.btn-add-first {
    padding: 8px 16px;
    background: rgba(14, 165, 233, 0.1);
    color: var(--god-blue);
    border: 1px dashed var(--god-blue);
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s;
}

.btn-add-requirement:hover,
.btn-add-first:hover {
    background: rgba(14, 165, 233, 0.2);
}

.requirements-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
    max-height: 400px;
    overflow-y: auto;
}

.empty-requirements {
    text-align: center;
    padding: 40px;
    color: var(--text-muted);
}

.empty-requirements i {
    font-size: 3rem;
    margin-bottom: 12px;
    opacity: 0.3;
}

.requirement-item {
    background: var(--god-bg);
    border: 1px solid var(--god-border);
    border-radius: 12px;
    overflow: hidden;
}

.requirement-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    background: rgba(0, 0, 0, 0.02);
    border-bottom: 1px solid var(--god-border);
}

.requirement-number {
    font-weight: 700;
    color: var(--god-blue);
    min-width: 30px;
}

.requirement-type {
    flex: 1;
    max-width: 180px;
}

.btn-remove-req {
    width: 32px;
    height: 32px;
    border: none;
    background: transparent;
    color: var(--text-muted);
    cursor: pointer;
    border-radius: 6px;
    transition: all 0.2s;
}

.btn-remove-req:hover {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

.requirement-body {
    padding: 16px;
}

.requirement-description {
    width: 100%;
    resize: vertical;
}

/* Quiz Questions */
.quiz-questions-container {
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px dashed var(--god-border);
}

.quiz-questions-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--text-main);
}

.btn-add-question {
    padding: 6px 12px;
    background: transparent;
    color: var(--god-purple);
    border: 1px dashed var(--god-purple);
    border-radius: 6px;
    font-size: 0.8rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.quiz-questions-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.question-item {
    background: white;
    border: 1px solid var(--god-border);
    border-radius: 8px;
    padding: 12px;
}

.question-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.question-number {
    font-weight: 700;
    font-size: 0.85rem;
    color: var(--god-purple);
}

.btn-remove-question {
    width: 24px;
    height: 24px;
    border: none;
    background: transparent;
    color: var(--text-muted);
    cursor: pointer;
    border-radius: 4px;
}

.btn-remove-question:hover {
    color: #ef4444;
}

.question-text {
    width: 100%;
    margin-bottom: 10px;
}

.question-options {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.option-row {
    display: flex;
    align-items: center;
    gap: 8px;
}

.option-row input[type="radio"] {
    accent-color: var(--god-blue);
}

.option-row .option-text {
    flex: 1;
}

/* Review Preview */
.review-preview {
    background: var(--god-bg);
    border: 1px solid var(--god-border);
    border-radius: 12px;
    padding: 20px;
}

.review-section {
    margin-bottom: 20px;
}

.review-section:last-child {
    margin-bottom: 0;
}

.review-section h5 {
    margin: 0 0 12px;
    color: var(--text-muted);
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.review-specialty-card {
    display: flex;
    gap: 16px;
    align-items: flex-start;
}

.review-icon {
    font-size: 3rem;
    width: 80px;
    height: 80px;
    background: rgba(139, 92, 246, 0.1);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.review-info h4 {
    margin: 0 0 8px;
    color: var(--text-main);
}

.review-meta {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
}

.review-meta span {
    font-size: 0.85rem;
    color: var(--text-muted);
}

.review-requirements-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.review-requirement {
    padding: 10px 14px;
    background: white;
    border: 1px solid var(--god-border);
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 10px;
}

    color: var(--god-blue);
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
}
</style>


<?php require_once BASE_PATH . '/views/admin/partials/icon_picker.php'; ?>

<script>
function openSpecIconPicker() {
    IconPicker.open(document.getElementById('spec-icon').value, (icon) => {
        document.getElementById('spec-icon').value = icon;
        document.getElementById('spec-icon-preview').setAttribute('icon', icon);
        document.getElementById('spec-icon-text').textContent = icon;
    });
}

function openGodClassIconPicker() {
    IconPicker.open(document.getElementById('god-class-icon').value, (icon) => {
        document.getElementById('god-class-icon').value = icon;
        document.getElementById('god-class-icon-preview').setAttribute('icon', icon);
        document.getElementById('god-class-icon-text').textContent = icon;
    });
}
</script>
