<?php
/**
 * Admin: Edit Learning Program (Steps & Questions)
 */
$pageTitle = 'Editar: ' . $program['name'];
$pageIcon = 'edit_note';
$typeLabel = $program['type'] === 'class' ? 'Classe' : 'Especialidade';
?>
<style>
    /* ============ Premium Design System ============ */
    
    /* Keyframe Animations */
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    @keyframes pulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(6, 182, 212, 0.4); }
        50% { box-shadow: 0 0 0 8px rgba(6, 182, 212, 0); }
    }
    
    @keyframes shimmer {
        0% { background-position: -200% 0; }
        100% { background-position: 200% 0; }
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes scaleIn {
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }

    /* ============ Page Header Hero ============ */
    .page-toolbar {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        margin-bottom: 32px;
        padding: 20px 28px;
        background: linear-gradient(135deg, var(--bg-card) 0%, rgba(6, 182, 212, 0.03) 100%);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        box-shadow: 
            0 4px 6px -1px rgba(0, 0, 0, 0.1),
            0 2px 4px -1px rgba(0, 0, 0, 0.06);
        animation: slideUp 0.4s ease-out;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        color: var(--text-muted) !important;
        text-decoration: none !important;
        font-size: 0.9rem;
        font-weight: 500;
        border-radius: 8px;
        transition: all 0.2s ease;
        background: transparent;
    }

    .back-link:hover {
        color: var(--primary) !important;
        background: rgba(6, 182, 212, 0.08);
        transform: translateX(-4px);
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
        padding: 12px 24px;
        border-radius: 10px;
        font-size: 0.9rem;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        border: none;
        position: relative;
        overflow: hidden;
    }

    .btn-toolbar::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
        transform: translateX(-100%);
        transition: transform 0.5s;
    }

    .btn-toolbar:hover::before {
        transform: translateX(100%);
    }

    .btn-toolbar.primary {
        background: linear-gradient(135deg, #06b6d4 0%, #0891b2 50%, #0e7490 100%);
        color: white;
        box-shadow: 
            0 4px 14px rgba(6, 182, 212, 0.35),
            inset 0 1px 0 rgba(255,255,255,0.15);
    }

    .btn-toolbar.primary:hover {
        transform: translateY(-2px);
        box-shadow: 
            0 8px 20px rgba(6, 182, 212, 0.45),
            inset 0 1px 0 rgba(255,255,255,0.15);
    }

    .btn-toolbar.primary:active {
        transform: translateY(0);
    }

    .btn-toolbar.success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        box-shadow: 
            0 4px 14px rgba(16, 185, 129, 0.35),
            inset 0 1px 0 rgba(255,255,255,0.15);
    }

    .btn-toolbar.success:hover {
        transform: translateY(-2px);
        box-shadow: 
            0 8px 20px rgba(16, 185, 129, 0.45),
            inset 0 1px 0 rgba(255,255,255,0.15);
    }

    .status-badge {
        font-size: 0.85rem;
        padding: 8px 16px;
        border-radius: 24px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        letter-spacing: 0.3px;
    }

    /* ============ Steps Container - Glass Card ============ */
    .steps-container {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 
            0 10px 40px -10px rgba(0, 0, 0, 0.1),
            0 0 0 1px rgba(255, 255, 255, 0.05);
        animation: slideUp 0.5s ease-out 0.1s both;
    }

    .steps-header {
        padding: 24px 28px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: linear-gradient(135deg, transparent 0%, rgba(6, 182, 212, 0.02) 100%);
    }

    .steps-header h2 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-main);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* ============ Step Cards - Premium Design ============ */
    .step-card {
        border-bottom: 1px solid var(--border-color);
        background: var(--bg-card);
        transition: all 0.3s ease;
        animation: fadeIn 0.4s ease-out;
    }

    .step-card:last-child {
        border-bottom: none;
    }

    .step-card:hover {
        background: rgba(6, 182, 212, 0.02);
    }

    .step-header {
        padding: 20px 28px;
        display: flex;
        align-items: center;
        gap: 20px;
        cursor: pointer;
        transition: all 0.25s ease;
        position: relative;
    }

    .step-header::after {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: transparent;
        transition: background 0.3s ease;
        border-radius: 0 4px 4px 0;
    }

    .step-header:hover {
        padding-left: 32px;
    }

    .step-header:hover::after {
        background: linear-gradient(180deg, #06b6d4, #10b981);
    }

    .step-number {
        width: 44px;
        height: 44px;
        background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        color: white;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.1rem;
        flex-shrink: 0;
        box-shadow: 
            0 4px 12px rgba(6, 182, 212, 0.35),
            inset 0 1px 0 rgba(255,255,255,0.2);
        transition: all 0.3s ease;
    }

    .step-card:hover .step-number {
        transform: scale(1.05);
        box-shadow: 
            0 6px 16px rgba(6, 182, 212, 0.45),
            inset 0 1px 0 rgba(255,255,255,0.2);
    }

    .step-info {
        flex: 1;
    }

    .step-title {
        font-weight: 600;
        font-size: 1.05rem;
        margin: 0 0 6px;
        color: var(--text-main);
        transition: color 0.2s;
    }

    .step-card:hover .step-title {
        color: var(--primary);
    }

    .step-meta {
        font-size: 0.875rem;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .step-meta span {
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .step-actions {
        display: flex;
        gap: 8px;
        opacity: 0;
        transform: translateX(10px);
        transition: all 0.25s ease;
    }

    .step-header:hover .step-actions {
        opacity: 1;
        transform: translateX(0);
    }

    .step-btn {
        padding: 8px 14px;
        background: var(--bg-dark);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        color: var(--text-muted);
        cursor: pointer;
        font-size: 0.9rem;
        transition: all 0.2s ease;
    }

    .step-btn:hover {
        background: var(--bg-hover);
        color: var(--text-main);
        transform: translateY(-1px);
    }

    .step-btn.delete:hover {
        color: #ef4444;
        border-color: #ef4444;
        background: rgba(239, 68, 68, 0.08);
    }

    .step-body {
        padding: 0 28px 28px;
        display: none;
        animation: slideUp 0.3s ease-out;
    }

    .step-card.expanded .step-body {
        display: block;
    }

    .step-card.expanded .step-header {
        background: linear-gradient(90deg, rgba(6, 182, 212, 0.08) 0%, transparent 100%);
    }

    .step-card.expanded .step-header::after {
        background: linear-gradient(180deg, #06b6d4, #0891b2);
    }

    .step-card.expanded .step-number {
        animation: pulse 2s infinite;
    }

    /* ============ Form Elements - Modern Style ============ */
    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        font-size: 0.875rem;
        color: var(--text-main);
        letter-spacing: 0.3px;
    }

    .form-control {
        width: 100%;
        padding: 14px 18px;
        background: var(--bg-dark);
        border: 2px solid var(--border-color);
        border-radius: 12px;
        color: var(--text-main);
        font-size: 0.95rem;
        transition: all 0.25s ease;
    }

    .form-control:hover {
        border-color: rgba(6, 182, 212, 0.3);
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(6, 182, 212, 0.1);
        background: var(--bg-card);
    }

    .form-control::placeholder {
        color: var(--text-muted);
        opacity: 0.7;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    /* ============ Questions Section - Premium Cards ============ */
    .questions-section {
        margin-top: 28px;
        padding-top: 28px;
        border-top: 1px solid var(--border-color);
    }

    .questions-section h4 {
        color: var(--text-main);
        margin: 0 0 16px;
        font-size: 1rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .question-card {
        background: linear-gradient(135deg, var(--bg-dark) 0%, rgba(6, 182, 212, 0.02) 100%);
        border: 1px solid var(--border-color);
        border-radius: 14px;
        padding: 20px;
        margin-bottom: 14px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .question-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #06b6d4, #10b981);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .question-card:hover {
        border-color: rgba(6, 182, 212, 0.3);
        transform: translateY(-2px);
        box-shadow: 0 8px 24px -8px rgba(0, 0, 0, 0.12);
    }

    .question-card:hover::before {
        opacity: 1;
    }

    .question-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 14px;
    }

    .question-type-badge {
        font-size: 0.75rem;
        padding: 6px 14px;
        border-radius: 20px;
        background: linear-gradient(135deg, rgba(6, 182, 212, 0.15), rgba(6, 182, 212, 0.08));
        color: var(--primary);
        font-weight: 600;
        border: 1px solid rgba(6, 182, 212, 0.2);
        letter-spacing: 0.3px;
    }

    .add-question-btn {
        width: 100%;
        padding: 16px;
        background: linear-gradient(135deg, rgba(6, 182, 212, 0.04), rgba(16, 185, 129, 0.04));
        border: 2px dashed var(--border-color);
        border-radius: 14px;
        color: var(--text-muted);
        cursor: pointer;
        margin-top: 12px;
        font-size: 0.95rem;
        font-weight: 500;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .add-question-btn:hover {
        border-color: var(--primary);
        color: var(--primary);
        background: rgba(6, 182, 212, 0.08);
        transform: translateY(-2px);
    }

    /* ============ Add Step Button - Prominent CTA ============ */
    .add-step-btn {
        width: 100%;
        padding: 24px;
        background: linear-gradient(135deg, rgba(6, 182, 212, 0.05), rgba(16, 185, 129, 0.05));
        border: 2px dashed var(--border-color);
        border-radius: 16px;
        color: var(--text-muted);
        cursor: pointer;
        margin-top: 20px;
        font-size: 1rem;
        font-weight: 600;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .add-step-btn:hover:not(.disabled) {
        border-color: var(--primary);
        color: var(--primary);
        background: linear-gradient(135deg, rgba(6, 182, 212, 0.1), rgba(16, 185, 129, 0.1));
        transform: translateY(-3px);
        box-shadow: 0 8px 24px -8px rgba(6, 182, 212, 0.25);
    }

    .add-step-btn.disabled {
        opacity: 0.5;
        cursor: not-allowed;
        filter: grayscale(1);
        transform: none !important;
        box-shadow: none !important;
        border-style: dashed;
    }

    /* ============ Question Modal - Glass Premium ============ */
    .question-modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(8px);
        z-index: 1000;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .question-modal-overlay.active {
        display: flex;
        animation: fadeIn 0.25s ease-out;
    }

    .question-modal {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 24px;
        max-width: 680px;
        width: 100%;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 
            0 25px 80px -20px rgba(0, 0, 0, 0.4),
            0 0 0 1px rgba(255, 255, 255, 0.05);
        animation: scaleIn 0.3s ease-out;
    }

    .question-modal-header {
        padding: 28px 32px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: linear-gradient(135deg, transparent, rgba(6, 182, 212, 0.03));
    }

    .question-modal-header h2 {
        margin: 0;
        font-size: 1.35rem;
        font-weight: 700;
        color: var(--text-main);
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .modal-close-btn {
        width: 40px;
        height: 40px;
        background: var(--bg-dark);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        color: var(--text-muted);
        font-size: 1.25rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .modal-close-btn:hover {
        background: rgba(239, 68, 68, 0.1);
        border-color: #ef4444;
        color: #ef4444;
        transform: rotate(90deg);
    }

    .question-modal-body {
        padding: 32px;
    }

    .question-modal-body h4 {
        margin: 0 0 20px;
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-main);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* ============ Question Type Grid - Premium Cards ============ */
    .question-types-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 14px;
        margin-bottom: 28px;
    }

    .question-type-option {
        padding: 24px 16px;
        background: var(--bg-dark);
        border: 2px solid var(--border-color);
        border-radius: 16px;
        cursor: pointer;
        text-align: center;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .question-type-option::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(6, 182, 212, 0.1), rgba(16, 185, 129, 0.1));
        opacity: 0;
        transition: opacity 0.3s;
    }

    .question-type-option:hover {
        border-color: rgba(6, 182, 212, 0.5);
        transform: translateY(-4px);
        box-shadow: 0 12px 28px -8px rgba(6, 182, 212, 0.25);
    }

    .question-type-option:hover::before {
        opacity: 1;
    }

    .question-type-option.selected {
        border-color: var(--primary);
        background: linear-gradient(135deg, rgba(6, 182, 212, 0.12), rgba(6, 182, 212, 0.06));
        transform: translateY(-2px);
        box-shadow: 
            0 8px 20px -8px rgba(6, 182, 212, 0.35),
            inset 0 0 0 1px rgba(6, 182, 212, 0.1);
    }

    .question-type-option.selected::after {
        content: '✓';
        position: absolute;
        top: 10px;
        right: 10px;
        width: 22px;
        height: 22px;
        background: linear-gradient(135deg, #06b6d4, #0891b2);
        color: white;
        border-radius: 50%;
        font-size: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        animation: scaleIn 0.2s ease-out;
    }

    .question-type-option .type-icon {
        font-size: 2.25rem;
        display: block;
        margin-bottom: 12px;
        position: relative;
        z-index: 1;
    }

    .question-type-option .type-label {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text-main);
        position: relative;
        z-index: 1;
    }

    /* ============ Form Section Animation ============ */
    .question-form-section {
        display: none;
        animation: slideUp 0.4s ease-out;
    }

    .question-form-section.active {
        display: block;
    }

    /* ============ Options Editor - Premium Style ============ */
    .options-list {
        margin: 16px 0;
    }

    .option-row {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 10px;
        padding: 14px 16px;
        background: var(--bg-dark);
        border: 2px solid var(--border-color);
        border-radius: 12px;
        transition: all 0.25s ease;
    }

    .option-row:hover {
        border-color: rgba(6, 182, 212, 0.3);
    }

    .option-row.correct {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(16, 185, 129, 0.05));
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
    }

    .option-correct-marker {
        cursor: pointer;
        font-size: 1.25rem;
        opacity: 0.4;
        transition: all 0.2s ease;
        padding: 4px;
    }

    .option-correct-marker:hover {
        opacity: 0.7;
        transform: scale(1.1);
    }

    .option-row.correct .option-correct-marker {
        opacity: 1;
        color: #10b981;
    }

    .option-text-input {
        flex: 1;
        padding: 12px 16px;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        color: var(--text-main);
        font-size: 0.95rem;
        transition: all 0.2s ease;
    }

    .option-text-input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.1);
    }

    .option-delete-btn {
        background: transparent;
        border: none;
        color: var(--text-muted);
        cursor: pointer;
        padding: 8px;
        font-size: 1rem;
        border-radius: 8px;
        transition: all 0.2s ease;
    }

    .option-delete-btn:hover {
        color: #ef4444;
        background: rgba(239, 68, 68, 0.1);
    }

    .add-option-btn {
        width: 100%;
        padding: 14px;
        background: transparent;
        border: 2px dashed var(--border-color);
        border-radius: 12px;
        color: var(--text-muted);
        cursor: pointer;
        font-size: 0.9rem;
        font-weight: 500;
        transition: all 0.25s ease;
    }

    .add-option-btn:hover {
        border-color: var(--primary);
        color: var(--primary);
        background: rgba(6, 182, 212, 0.05);
    }

    /* ============ Modal Footer - Premium Buttons ============ */
    .question-modal-footer {
        padding: 24px 32px;
        border-top: 1px solid var(--border-color);
        display: flex;
        justify-content: flex-end;
        gap: 14px;
        background: linear-gradient(135deg, rgba(6, 182, 212, 0.02), transparent);
    }

    .btn-cancel {
        padding: 12px 24px;
        background: var(--bg-dark);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        color: var(--text-muted);
        cursor: pointer;
        font-weight: 600;
        font-size: 0.95rem;
        transition: all 0.2s ease;
    }

    .btn-cancel:hover {
        background: var(--bg-hover);
        color: var(--text-main);
        border-color: var(--text-muted);
    }

    .btn-add-question {
        padding: 12px 28px;
        background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        border: none;
        border-radius: 10px;
        color: white;
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.25s ease;
        box-shadow: 0 4px 14px rgba(6, 182, 212, 0.35);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-add-question:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(6, 182, 212, 0.45);
    }

    /* ============ Toast Notifications - Premium ============ */
    .toast {
        position: fixed;
        bottom: 24px;
        right: 24px;
        padding: 18px 28px;
        background: var(--bg-card);
        border-left: 4px solid #10b981;
        border-radius: 14px;
        box-shadow: 
            0 20px 40px -10px rgba(0, 0, 0, 0.2),
            0 0 0 1px rgba(255, 255, 255, 0.05);
        transform: translateX(150%);
        transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 1001;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .toast.show {
        transform: translateX(0);
    }

    .toast.error {
        border-left-color: #ef4444;
    }

    /* ============ Confirmation Modal - Premium ============ */
    .confirm-modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(8px);
        z-index: 2000;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .confirm-modal-overlay.active {
        display: flex;
        animation: fadeIn 0.25s ease-out;
    }

    .confirm-modal {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 24px;
        max-width: 420px;
        width: 100%;
        padding: 32px;
        text-align: center;
        box-shadow: 
            0 25px 80px -20px rgba(0, 0, 0, 0.4),
            0 0 0 1px rgba(255, 255, 255, 0.05);
        animation: scaleIn 0.3s ease-out;
    }

    .confirm-modal-icon {
        font-size: 4rem;
        margin-bottom: 20px;
        display: block;
    }

    .confirm-modal h3 {
        margin: 0 0 12px;
        font-size: 1.35rem;
        font-weight: 700;
        color: var(--text-main);
    }

    .confirm-modal p {
        color: var(--text-muted);
        margin: 0 0 28px;
        font-size: 1rem;
        line-height: 1.6;
    }

    .confirm-modal-actions {
        display: flex;
        gap: 14px;
        justify-content: center;
    }

    .confirm-modal-actions button {
        padding: 12px 28px;
        border-radius: 12px;
        font-weight: 600;
        cursor: pointer;
        border: none;
        font-size: 0.95rem;
        transition: all 0.25s ease;
    }

    .btn-confirm-cancel {
        background: var(--bg-dark);
        color: var(--text-main);
        border: 1px solid var(--border-color);
    }

    .btn-confirm-cancel:hover {
        background: var(--bg-hover);
    }

    .btn-confirm-ok {
        background: linear-gradient(135deg, #06b6d4, #0891b2);
        color: white;
        box-shadow: 0 4px 14px rgba(6, 182, 212, 0.35);
    }

    .btn-confirm-ok:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(6, 182, 212, 0.45);
    }

    .btn-confirm-danger {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: white;
        box-shadow: 0 4px 14px rgba(239, 68, 68, 0.35);
    }

    .btn-confirm-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(239, 68, 68, 0.45);
    }

    /* ============ Responsive Adjustments ============ */
    @media (max-width: 768px) {
        .page-toolbar {
            padding: 16px 20px;
        }
        
        .step-header {
            padding: 16px 20px;
        }
        
        .step-body {
            padding: 0 20px 20px;
        }
        
        .form-row {
            grid-template-columns: 1fr;
        }
        
        .question-types-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .question-modal {
            border-radius: 20px;
            margin: 10px;
        }
        
        .question-modal-header,
        .question-modal-body,
        .question-modal-footer {
            padding: 20px;
        }
    }
    /* ============ GSAP Premium Modal ============ */
    .gsap-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(12px);
        z-index: 2000;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        visibility: hidden;
    }

    .gsap-modal-card {
        background: linear-gradient(135deg, rgba(30, 41, 59, 0.95), rgba(15, 23, 42, 0.95));
        border: 1px solid rgba(255, 255, 255, 0.1);
        width: 100%;
        max-width: 420px;
        border-radius: 28px;
        padding: 40px 32px;
        text-align: center;
        box-shadow: 
            0 25px 50px -12px rgba(0, 0, 0, 0.5),
            0 0 0 1px rgba(255, 255, 255, 0.05),
            0 0 60px -20px rgba(6, 182, 212, 0.15);
        transform: scale(0.8);
        opacity: 0;
        position: relative;
        overflow: hidden;
    }

    .gsap-modal-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 6px;
        background: linear-gradient(90deg, #06b6d4, #8b5cf6, #ec4899);
    }

    .gsap-icon-container {
        width: 80px;
        height: 80px;
        margin: 0 auto 24px;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .gsap-icon-glow {
        position: absolute;
        inset: 0;
        background: radial-gradient(circle, rgba(6, 182, 212, 0.2) 0%, transparent 70%);
        filter: blur(8px);
        border-radius: 50%;
        animation: pulse 3s infinite;
    }

    .gsap-icon {
        font-size: 3.5rem;
        position: relative;
        z-index: 2;
        filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.3));
    }

    .gsap-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: white;
        margin: 0 0 12px;
        background: linear-gradient(to right, #fff, #cbd5e1);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .gsap-message {
        font-size: 1rem;
        color: #94a3b8;
        line-height: 1.6;
        margin: 0 0 32px;
    }

    .gsap-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    .gsap-btn {
        padding: 14px;
        border-radius: 16px;
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
        outline: none;
    }

    .gsap-btn.cancel {
        background: rgba(255, 255, 255, 0.05);
        color: #94a3b8;
    }

    .gsap-btn.cancel:hover {
        background: rgba(255, 255, 255, 0.1);
        color: white;
    }

    .gsap-btn.confirm {
        background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(6, 182, 212, 0.3);
    }

    .gsap-btn.confirm:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(6, 182, 212, 0.4);
    }

    .gsap-btn.danger {
        background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    }

    .gsap-btn.danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(239, 68, 68, 0.4);
    }
    /* CSS Animations for Modal */
    @keyframes modalPopIn {
        0% { opacity: 0; transform: scale(0.8); }
        60% { transform: scale(1.05); }
        100% { opacity: 1; transform: scale(1); }
    }
    
    @keyframes modalFadeOut {
        from { opacity: 1; transform: scale(1); }
        to { opacity: 0; transform: scale(0.95); }
    }

    .gsap-modal-overlay.active {
        visibility: visible;
        opacity: 1;
        transition: opacity 0.3s ease-out;
    }

    .gsap-modal-overlay.active .gsap-modal-card {
        opacity: 1;
        transform: scale(1);
        animation: modalPopIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
    }

    .gsap-modal-overlay.closing {
        opacity: 0;
        transition: opacity 0.3s ease-in;
    }

    .gsap-modal-overlay.closing .gsap-modal-card {
        animation: modalFadeOut 0.2s ease-in forwards;
    }
</style>

<div class="page-toolbar">
    <div class="toolbar-info">
        <a href="<?= base_url($tenant['slug'] . '/admin/programas') ?>" class="back-link">
            <span class="material-icons-round">arrow_back</span> Voltar
        </a>
        <span class="status-badge"
            style="background: <?= $program['status'] === 'published' ? 'linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(16, 185, 129, 0.08))' : 'linear-gradient(135deg, rgba(251, 191, 36, 0.15), rgba(251, 191, 36, 0.08))' ?>; color: <?= $program['status'] === 'published' ? '#10b981' : '#f59e0b' ?>; border: 1px solid <?= $program['status'] === 'published' ? 'rgba(16, 185, 129, 0.3)' : 'rgba(251, 191, 36, 0.3)' ?>;">
            <?= $program['status'] === 'published' ? '✅ Publicado' : '📝 Rascunho' ?>
        </span>
    </div>

    <div class="actions-group">
        <button class="btn-toolbar primary" onclick="saveAllSteps()">
            <span class="material-icons-round">save</span> Salvar
        </button>
        <?php if ($program['status'] === 'draft'): ?>
            <button class="btn-toolbar success" onclick="publishProgram()">
                <span class="material-icons-round">rocket_launch</span> Publicar
            </button>
        <?php endif; ?>
            </div>
        </div>

        <div class="steps-container">
            <div class="steps-header">
                <h2>📋 Requisitos (<?= count($steps) ?>)</h2>
            </div>

            <div id="stepsList">
                <?php foreach ($steps as $index => $step): ?>
                    <div class="step-card" data-step-index="<?= $index ?>">
                        <div class="step-header" onclick="toggleStep(this)">
                            <span class="step-number"><?= $index + 1 ?></span>
                            <div class="step-info">
                                <h3 class="step-title"><?= htmlspecialchars($step['title']) ?></h3>
                                <div class="step-meta"><?= count($step['questions'] ?? []) ?> perguntas •
                                    <?= $step['points'] ?> pts
                                </div>
                            </div>
                            <div class="step-actions">
                                <button class="step-btn delete"
                                    onclick="event.stopPropagation(); removeStep(<?= $index ?>)">🗑️</button>
                            </div>
                        </div>
                        <div class="step-body">
                            <div class="form-group">
                                <label>Título do Requisito</label>
                                <input type="text" class="form-control step-title-input"
                                    value="<?= htmlspecialchars($step['title']) ?>">
                            </div>
                            <div class="form-group">
                                <label>Descrição</label>
                                <textarea class="form-control step-description-input"
                                    rows="2"><?= htmlspecialchars($step['description'] ?? '') ?></textarea>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Pontos</label>
                                    <input type="number" class="form-control step-points-input"
                                        value="<?= $step['points'] ?>" min="0">
                                </div>
                                <div class="form-group">
                                    <label>Obrigatório</label>
                                    <select class="form-control step-required-input">
                                        <option value="1" <?= $step['is_required'] ? 'selected' : '' ?>>Sim</option>
                                        <option value="0" <?= !$step['is_required'] ? 'selected' : '' ?>>Não</option>
                                    </select>
                                </div>
                            </div>

                            <div class="questions-section">
                                <h4>📝 Perguntas</h4>
                                <div class="questions-list">
                                    <?php foreach ($step['questions'] ?? [] as $qIndex => $question): ?>
                                        <div class="question-card" data-question-index="<?= $qIndex ?>">
                                            <div class="question-header">
                                                <span class="question-type-badge"><?= match ($question['type']) {
                                                    'text' => '📝 Texto',
                                                    'single_choice' => '🔘 Única Escolha',
                                                    'multiple_choice' => '☑️ Múltipla Escolha',
                                                    'true_false' => '✅ Verdadeiro/Falso',
                                                    'file_upload' => '📎 Upload',
                                                    'url' => '🔗 URL',
                                                    'manual' => '✋ Aprovação Manual',
                                                    default => '❓ ' . $question['type']
                                                } ?></span>
                                                <button class="step-btn delete" onclick="removeQuestion(this)">🗑️</button>
                                            </div>
                                            <div class="form-group">
                                                <input type="text" class="form-control question-text-input"
                                                    value="<?= htmlspecialchars($question['question_text']) ?>"
                                                    placeholder="Texto da pergunta...">
                                                <input type="hidden" class="question-type-input"
                                                    value="<?= $question['type'] ?>">
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <button class="add-question-btn" onclick="addQuestion(this)">➕ Adicionar Pergunta</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <button id="addStepBtn" class="add-step-btn" onclick="addStep()">➕ Adicionar Requisito</button>
        </div>
    </main>

    <!-- Question Builder Modal -->
    <div class="question-modal-overlay" id="questionModal" onclick="closeQuestionModal(event)">
        <div class="question-modal" onclick="event.stopPropagation()">
            <div class="question-modal-header">
                <h2>➕ Nova Pergunta</h2>
                <button class="modal-close-btn" onclick="closeQuestionModal()">&times;</button>
            </div>
            <div class="question-modal-body">
                <!-- Step 1: Choose Type -->
                <h4 style="margin-bottom: 16px;">📋 Tipo de Pergunta</h4>
                <div class="question-types-grid">
                    <div class="question-type-option" data-type="single_choice"
                        onclick="selectQuestionType('single_choice')">
                        <span class="type-icon">🔘</span>
                        <span class="type-label">Única Escolha</span>
                    </div>
                    <div class="question-type-option" data-type="multiple_choice"
                        onclick="selectQuestionType('multiple_choice')">
                        <span class="type-icon">☑️</span>
                        <span class="type-label">Múltipla Escolha</span>
                    </div>
                    <div class="question-type-option" data-type="true_false" onclick="selectQuestionType('true_false')">
                        <span class="type-icon">✅</span>
                        <span class="type-label">Verdadeiro/Falso</span>
                    </div>
                    <div class="question-type-option" data-type="text" onclick="selectQuestionType('text')">
                        <span class="type-icon">📝</span>
                        <span class="type-label">Texto</span>
                    </div>
                    <div class="question-type-option" data-type="file_upload"
                        onclick="selectQuestionType('file_upload')">
                        <span class="type-icon">📎</span>
                        <span class="type-label">Upload</span>
                    </div>
                    <div class="question-type-option" data-type="url" onclick="selectQuestionType('url')">
                        <span class="type-icon">🔗</span>
                        <span class="type-label">URL</span>
                    </div>
                    <div class="question-type-option" data-type="manual" onclick="selectQuestionType('manual')">
                        <span class="type-icon">✋</span>
                        <span class="type-label">Manual</span>
                    </div>
                </div>

                <!-- Step 2: Question Form (shown after type selected) -->
                <div class="question-form-section" id="questionFormSection">
                    <div class="form-group">
                        <label>Texto da Pergunta *</label>
                        <textarea class="form-control" id="newQuestionText" rows="2"
                            placeholder="Digite a pergunta..."></textarea>
                    </div>

                    <!-- Options for choice types -->
                    <div id="optionsEditor" style="display: none;">
                        <label>Opções de Resposta</label>
                        <p style="font-size: 0.8rem; color: var(--text-secondary); margin-bottom: 12px;">
                            Clique no ícone para marcar a(s) resposta(s) correta(s)
                        </p>
                        <div class="options-list" id="optionsList"></div>
                        <button type="button" class="add-option-btn" onclick="addOption()">➕ Adicionar Opção</button>
                    </div>

                    <!-- True/False Options -->
                    <div id="trueFalseEditor" style="display: none;">
                        <label>Resposta Correta</label>
                        <div style="display: flex; gap: 16px; margin-top: 8px;">
                            <label class="option-row" style="flex: 1; cursor: pointer;" id="trueFalseTrue"
                                onclick="setTrueFalseAnswer(true)">
                                <span class="option-correct-marker">○</span>
                                <span style="flex: 1;">✅ Verdadeiro</span>
                            </label>
                            <label class="option-row" style="flex: 1; cursor: pointer;" id="trueFalseFalse"
                                onclick="setTrueFalseAnswer(false)">
                                <span class="option-correct-marker">○</span>
                                <span style="flex: 1;">❌ Falso</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-row" style="margin-top: 16px;">
                        <div class="form-group">
                            <label>Pontos</label>
                            <input type="number" class="form-control" id="newQuestionPoints" value="10" min="0">
                        </div>
                        <div class="form-group">
                            <label>Obrigatória</label>
                            <select class="form-control" id="newQuestionRequired">
                                <option value="1" selected>Sim</option>
                                <option value="0">Não</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="question-modal-footer">
                <button type="button" class="btn-cancel" onclick="closeQuestionModal()">Cancelar</button>
                <button type="button" class="btn-add-question" onclick="submitNewQuestion()">✅ Adicionar
                    Pergunta</button>
            </div>
        </div>
    </div>

    <!-- GSAP Premium Confirmation Modal -->
    <div class="gsap-modal-overlay" id="gsapConfirmModal">
        <div class="gsap-modal-card">
            <div class="gsap-icon-container">
                <div class="gsap-icon-glow"></div>
                <div class="gsap-icon" id="gsapConfirmIcon">🚀</div>
            </div>
            <h3 class="gsap-title" id="gsapConfirmTitle">Confirmar Action</h3>
            <p class="gsap-message" id="gsapConfirmMessage">Tem certeza que deseja prosseguir?</p>
            <div class="gsap-actions">
                <button type="button" class="gsap-btn cancel" onclick="closeGsapConfirm(false)">Cancelar</button>
                <button type="button" class="gsap-btn confirm" id="gsapConfirmOk" onclick="closeGsapConfirm(true)">Confirmar</button>
            </div>
        </div>
    </div>

    <div class="toast" id="toast"></div>

    <script>
        // Initialize button state
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(updateAddStepButtonState, 500); // Give small delay for list to render
        });

        window.tenantSlug = '<?= $tenant['slug'] ?>';
        window.programId = <?= $program['id'] ?>;

        function toggleStep(header) {
            header.closest('.step-card').classList.toggle('expanded');
        }

        function updateAddStepButtonState() {
            const list = document.getElementById('stepsList');
            const btn = document.getElementById('addStepBtn');
            if (!list || !btn) return;

            const steps = list.querySelectorAll('.step-card');
            if (steps.length === 0) {
                btn.classList.remove('disabled');
                return;
            }

            const lastStep = steps[steps.length - 1];
            const questions = lastStep.querySelectorAll('.question-card');
            
            if (questions.length === 0) {
                btn.classList.add('disabled');
            } else {
                btn.classList.remove('disabled');
            }
        }

        function addStep() {
            const btn = document.getElementById('addStepBtn');
            if (btn.classList.contains('disabled')) {
                showToast('⚠️ Adicione pelo menos 1 pergunta ao requisito atual antes de criar um novo.', 'error');
                return;
            }

            const list = document.getElementById('stepsList');
            const index = list.children.length;
            const stepHtml = `
                <div class="step-card expanded" data-step-index="${index}">
                    <div class="step-header" onclick="toggleStep(this)">
                        <span class="step-number">${index + 1}</span>
                        <div class="step-info">
                            <h3 class="step-title">Novo Requisito</h3>
                            <div class="step-meta">0 perguntas • 10 pts</div>
                        </div>
                        <div class="step-actions">
                            <button class="step-btn delete" onclick="event.stopPropagation(); removeStep(${index})">🗑️</button>
                        </div>
                    </div>
                    <div class="step-body" style="display:block;">
                        <div class="form-group">
                            <label>Título do Requisito</label>
                            <input type="text" class="form-control step-title-input" value="Novo Requisito" placeholder="Título...">
                        </div>
                        <div class="form-group">
                            <label>Descrição</label>
                            <textarea class="form-control step-description-input" rows="2" placeholder="Descrição..."></textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Pontos</label>
                                <input type="number" class="form-control step-points-input" value="10" min="0">
                            </div>
                            <div class="form-group">
                                <label>Obrigatório</label>
                                <select class="form-control step-required-input">
                                    <option value="1" selected>Sim</option>
                                    <option value="0">Não</option>
                                </select>
                            </div>
                        </div>
                        <div class="questions-section">
                            <h4>📝 Perguntas</h4>
                            <div class="questions-list"></div>
                            <button class="add-question-btn" onclick="addQuestion(this)">➕ Adicionar Pergunta</button>
                        </div>
                    </div>
                </div>
            `;
            list.insertAdjacentHTML('beforeend', stepHtml);
            updateStepNumbers();
            updateAddStepButtonState();
        }

        async function removeStep(index) {
            const confirmed = await showConfirm({
                title: 'Remover Requisito',
                message: 'Tem certeza que deseja remover este requisito?',
                icon: '🗑️',
                danger: true,
                okText: 'Remover'
            });
            if (!confirmed) return;
            const list = document.getElementById('stepsList');
            list.children[index]?.remove();
            updateStepNumbers();
            updateAddStepButtonState();
            showToast('Requisito removido');
        }

        // ============ Question Modal System ============
        let currentQuestionBtn = null;
        let selectedQuestionType = null;
        let trueFalseAnswer = null;

        const typeLabels = {
            'single_choice': '🔘 Única Escolha',
            'multiple_choice': '☑️ Múltipla Escolha',
            'true_false': '✅ Verdadeiro/Falso',
            'text': '📝 Texto',
            'file_upload': '📎 Upload',
            'url': '🔗 URL',
            'manual': '✋ Aprovação Manual'
        };

        function addQuestion(btn) {
            currentQuestionBtn = btn;
            selectedQuestionType = null;
            trueFalseAnswer = null;

            // Reset modal state
            document.querySelectorAll('.question-type-option').forEach(el => el.classList.remove('selected'));
            document.getElementById('questionFormSection').classList.remove('active');
            document.getElementById('optionsEditor').style.display = 'none';
            document.getElementById('trueFalseEditor').style.display = 'none';
            document.getElementById('optionsList').innerHTML = '';
            document.getElementById('newQuestionText').value = '';
            document.getElementById('newQuestionPoints').value = '10';
            document.getElementById('newQuestionRequired').value = '1';
            document.getElementById('trueFalseTrue').classList.remove('correct');
            document.getElementById('trueFalseFalse').classList.remove('correct');

            // Show modal
            document.getElementById('questionModal').classList.add('active');
        }

        function closeQuestionModal(e) {
            if (e && e.target !== e.currentTarget) return;
            document.getElementById('questionModal').classList.remove('active');
            currentQuestionBtn = null;
        }

        function selectQuestionType(type) {
            selectedQuestionType = type;

            // Update UI
            document.querySelectorAll('.question-type-option').forEach(el => {
                el.classList.toggle('selected', el.dataset.type === type);
            });

            // Show form section
            document.getElementById('questionFormSection').classList.add('active');

            // Show/hide options editor based on type
            const optionsEditor = document.getElementById('optionsEditor');
            const trueFalseEditor = document.getElementById('trueFalseEditor');

            if (type === 'single_choice' || type === 'multiple_choice') {
                optionsEditor.style.display = 'block';
                trueFalseEditor.style.display = 'none';

                // Add default options if empty
                const optionsList = document.getElementById('optionsList');
                if (optionsList.children.length === 0) {
                    addOption();
                    addOption();
                }
            } else if (type === 'true_false') {
                optionsEditor.style.display = 'none';
                trueFalseEditor.style.display = 'block';
            } else {
                optionsEditor.style.display = 'none';
                trueFalseEditor.style.display = 'none';
            }
        }

        function addOption() {
            const optionsList = document.getElementById('optionsList');
            const index = optionsList.children.length;
            const isMultiple = selectedQuestionType === 'multiple_choice';
            const marker = isMultiple ? '☐' : '○';

            const html = `
                <div class="option-row" data-index="${index}">
                    <span class="option-correct-marker" onclick="toggleOptionCorrect(this)">${marker}</span>
                    <input type="text" class="option-text-input" placeholder="Opção ${index + 1}..." value="">
                    <button type="button" class="option-delete-btn" onclick="removeOption(this)">🗑️</button>
                </div>
            `;
            optionsList.insertAdjacentHTML('beforeend', html);
        }

        function removeOption(btn) {
            btn.closest('.option-row').remove();
            // Reindex options
            document.querySelectorAll('.option-row').forEach((row, i) => {
                row.dataset.index = i;
            });
        }

        function toggleOptionCorrect(marker) {
            const row = marker.closest('.option-row');
            const isMultiple = selectedQuestionType === 'multiple_choice';

            if (isMultiple) {
                // Multiple choice - toggle this option
                row.classList.toggle('correct');
                marker.textContent = row.classList.contains('correct') ? '☑' : '☐';
            } else {
                // Single choice - only one can be correct
                document.querySelectorAll('.option-row').forEach(r => {
                    r.classList.remove('correct');
                    r.querySelector('.option-correct-marker').textContent = '○';
                });
                row.classList.add('correct');
                marker.textContent = '●';
            }
        }

        function setTrueFalseAnswer(value) {
            trueFalseAnswer = value;
            document.getElementById('trueFalseTrue').classList.toggle('correct', value === true);
            document.getElementById('trueFalseFalse').classList.toggle('correct', value === false);

            const trueMarker = document.querySelector('#trueFalseTrue .option-correct-marker');
            const falseMarker = document.querySelector('#trueFalseFalse .option-correct-marker');
            trueMarker.textContent = value === true ? '●' : '○';
            falseMarker.textContent = value === false ? '●' : '○';
        }

        function submitNewQuestion() {
            if (!selectedQuestionType) {
                showToast('Selecione um tipo de pergunta', 'error');
                return;
            }

            const questionText = document.getElementById('newQuestionText').value.trim();
            if (!questionText) {
                showToast('Digite o texto da pergunta', 'error');
                return;
            }

            const points = parseInt(document.getElementById('newQuestionPoints').value) || 10;
            const isRequired = document.getElementById('newQuestionRequired').value === '1';

            // Collect options and correct answers for choice types
            let options = [];
            let correctAnswer = null;
            let correctAnswers = [];

            if (selectedQuestionType === 'single_choice' || selectedQuestionType === 'multiple_choice') {
                const optionRows = document.querySelectorAll('.option-row');
                optionRows.forEach((row, index) => {
                    const inputEl = row.querySelector('.option-text-input');
                    if (!inputEl) return; // Skip if element not found
                    const text = inputEl.value.trim();
                    if (text) {
                        options.push(text);
                        if (row.classList.contains('correct')) {
                            if (selectedQuestionType === 'single_choice') {
                                correctAnswer = options.length - 1;
                            } else {
                                correctAnswers.push(options.length - 1);
                            }
                        }
                    }
                });

                if (options.length < 2) {
                    showToast('Adicione pelo menos 2 opções', 'error');
                    return;
                }

                if (selectedQuestionType === 'single_choice' && correctAnswer === null) {
                    showToast('Marque a resposta correta', 'error');
                    return;
                }

                if (selectedQuestionType === 'multiple_choice' && correctAnswers.length === 0) {
                    showToast('Marque pelo menos uma resposta correta', 'error');
                    return;
                }
            } else if (selectedQuestionType === 'true_false') {
                options = ['Verdadeiro', 'Falso'];
                if (trueFalseAnswer === null) {
                    showToast('Selecione a resposta correta', 'error');
                    return;
                }
                correctAnswer = trueFalseAnswer ? 0 : 1;
            }

            // Build question card HTML
            let optionsHtml = '';
            if (options.length > 0) {
                optionsHtml = `<input type="hidden" class="question-options-input" value='${JSON.stringify(options)}'>`;
            }
            if (correctAnswer !== null) {
                optionsHtml += `<input type="hidden" class="question-correct-input" value="${correctAnswer}">`;
            }
            if (correctAnswers.length > 0) {
                optionsHtml += `<input type="hidden" class="question-correct-answers-input" value='${JSON.stringify(correctAnswers)}'>`;
            }

            const html = `
                <div class="question-card">
                    <div class="question-header">
                        <span class="question-type-badge">${typeLabels[selectedQuestionType]}</span>
                        <button class="step-btn delete" onclick="removeQuestion(this)">🗑️</button>
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control question-text-input" value="${escapeHtml(questionText)}" placeholder="Texto da pergunta...">
                        <input type="hidden" class="question-type-input" value="${selectedQuestionType}">
                        <input type="hidden" class="question-points-input" value="${points}">
                        <input type="hidden" class="question-required-input" value="${isRequired ? '1' : '0'}">
                        ${optionsHtml}
                    </div>
                    ${options.length > 0 ? `<div class="options-preview" style="font-size: 0.85rem; color: var(--text-secondary);">📋 ${options.length} opções</div>` : ''}
                </div>
            `;

            currentQuestionBtn.previousElementSibling.insertAdjacentHTML('beforeend', html);
            closeQuestionModal();
            showToast('Pergunta adicionada!');
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function removeQuestion(btn) {
            btn.closest('.question-card').remove();
            updateAddStepButtonState();
        }

        function updateStepNumbers() {
            document.querySelectorAll('.step-card').forEach((card, i) => {
                card.querySelector('.step-number').textContent = i + 1;
            });
        }

        function collectSteps() {
            const steps = [];
            document.querySelectorAll('.step-card').forEach(card => {
                const questions = [];
                card.querySelectorAll('.question-card').forEach(qCard => {
                    const questionData = {
                        type: qCard.querySelector('.question-type-input')?.value || 'text',
                        question_text: qCard.querySelector('.question-text-input')?.value || '',
                        is_required: qCard.querySelector('.question-required-input')?.value !== '0',
                        points: parseInt(qCard.querySelector('.question-points-input')?.value) || 10
                    };

                    // Get options if present
                    const optionsInput = qCard.querySelector('.question-options-input');
                    if (optionsInput) {
                        try {
                            questionData.options = JSON.parse(optionsInput.value);
                        } catch (e) { }
                    }

                    // Get correct answer(s)
                    const correctInput = qCard.querySelector('.question-correct-input');
                    if (correctInput) {
                        questionData.correct_answer = parseInt(correctInput.value);
                    }

                    const correctAnswersInput = qCard.querySelector('.question-correct-answers-input');
                    if (correctAnswersInput) {
                        try {
                            questionData.correct_answers = JSON.parse(correctAnswersInput.value);
                        } catch (e) { }
                    }

                    questions.push(questionData);
                });

                steps.push({
                    title: card.querySelector('.step-title-input')?.value || 'Requisito',
                    description: card.querySelector('.step-description-input')?.value || '',
                    points: parseInt(card.querySelector('.step-points-input')?.value || 10),
                    is_required: card.querySelector('.step-required-input')?.value === '1',
                    questions
                });
            });
            return steps;
        }

        async function saveAllSteps() {
            const steps = collectSteps();

            try {
                const resp = await fetch(`/${tenantSlug}/admin/programas/${programId}/steps`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ steps })
                });
                const data = await resp.json();

                if (data.success) {
                    showToast(data.message);
                } else {
                    showToast(data.error || 'Erro ao salvar', 'error');
                }
            } catch (err) {
                showToast('Erro de conexão', 'error');
            }
        }

        async function publishProgram() {
            const confirmed = await showConfirm({
                title: 'Publicar Programa',
                message: 'Publicar este programa? Ele ficará disponível para atribuição.',
                icon: '🚀',
                okText: 'Publicar'
            });
            if (!confirmed) return;

            try {
                const resp = await fetch(`/${tenantSlug}/admin/programas/${programId}/publish`, { method: 'POST' });
                const data = await resp.json();

                if (data.success) {
                    showToast(data.message);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(data.error || 'Erro', 'error');
                }
            } catch (err) {
                showToast('Erro de conexão', 'error');
            }
        }

        function showToast(msg, type = 'success') {
            const toast = document.getElementById('toast');
            toast.textContent = msg;
            toast.className = 'toast ' + type + ' show';
            setTimeout(() => toast.classList.remove('show'), 3000);
        }

        // ============ CSS Confirmation Modal System ============
        var confirmCallback = null;

        function showConfirm(options) {
            return new Promise((resolve) => {
                const { title, message, icon = '🚀', danger = false, okText = 'Confirmar' } = options;

                document.getElementById('gsapConfirmIcon').textContent = icon;
                document.getElementById('gsapConfirmTitle').textContent = title;
                document.getElementById('gsapConfirmMessage').textContent = message;

                const okBtn = document.getElementById('gsapConfirmOk');
                okBtn.textContent = okText;
                okBtn.className = danger ? 'gsap-btn danger' : 'gsap-btn confirm';

                confirmCallback = resolve;
                
                const overlay = document.getElementById('gsapConfirmModal');
                overlay.classList.remove('closing');
                overlay.classList.add('active');
            });
        }

        function closeGsapConfirm(result) {
            if (confirmCallback) {
                confirmCallback(result);
                confirmCallback = null;
            }

            const overlay = document.getElementById('gsapConfirmModal');
            overlay.classList.add('closing');
            
            setTimeout(() => {
                overlay.classList.remove('active', 'closing');
            }, 300);
        }
    </script>