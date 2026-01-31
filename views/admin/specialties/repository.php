<?php
/**
 * Admin: Browse Specialty Repository
 */
$pageTitle = 'Catálogo de Especialidades';
$pageIcon = 'school';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<style>
    /* ============ Mobile Header Spacing ============ */
    @media (max-width: 768px) {
        .page-hero {
            padding-top: 60px;
        }
    }

    /* ============ Page Hero (Title Section) ============ */
    .page-hero {
        margin-bottom: 24px;
    }

    .page-hero h1 {
        font-size: 2rem;
        font-weight: 700;
        margin: 0 0 8px 0;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .page-hero h1 .icon {
        font-size: 1.8rem;
    }

    .page-hero .subtitle {
        color: var(--text-secondary);
        font-size: 1rem;
        margin: 0;
        line-height: 1.5;
    }

    /* ============ Page Toolbar (Redesigned) ============ */
    .page-toolbar {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
        padding: 16px 24px;
        background: var(--bg-sidebar);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        box-shadow: var(--shadow-sm);
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
        border: 1px solid var(--border-color);
        border-radius: 8px;
        background: var(--bg-dark);
        color: var(--text-main);
        font-size: 0.9rem;
        transition: all 0.2s;
    }

    .search-input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.1);
    }

    .search-input::placeholder {
        color: var(--text-muted);
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
        border-color: var(--border-color);
        color: var(--text-muted);
    }

    .btn-toolbar.secondary:hover {
        border-color: var(--primary);
        color: var(--primary);
        background: rgba(6, 182, 212, 0.05);
    }

    .btn-toolbar.primary {
        background: linear-gradient(135deg, var(--primary), var(--primary-hover));
        color: white;
        box-shadow: var(--shadow-cyan);
        border: none;
    }

    .btn-toolbar.primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(6, 182, 212, 0.3);
    }

    /* ============ Category Tabs ============ */
    /* ============ Category Tabs (Redesigned) ============ */
    .tabs {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 24px;
        padding: 4px 4px 16px 4px;
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
    }

    .tabs::-webkit-scrollbar {
        height: 4px;
    }

    .tabs::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.02);
        border-radius: 2px;
    }

    .tabs::-webkit-scrollbar-thumb {
        background: var(--border-color);
        border-radius: 2px;
    }

    .tab-btn {
        position: relative;
        flex-shrink: 0;
        padding: 8px 16px;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 50px;
        /* Pill shape */
        color: var(--text-muted);
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        white-space: nowrap;
        font-size: 0.85rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 8px;
        --cat-color: var(--primary);
        /* Default fallback */
    }

    .tab-btn::before {
        content: '';
        display: block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: var(--cat-color);
        box-shadow: 0 0 8px var(--cat-color);
        opacity: 0.7;
        transition: all 0.2s;
    }

    .tab-btn:hover {
        background: var(--bg-hover);
        border-color: var(--cat-color);
        color: var(--text-main);
        transform: translateY(-1px);
    }

    .tab-btn:hover::before {
        opacity: 1;
        transform: scale(1.2);
    }

    .tab-btn.active {
        background: rgba(255, 255, 255, 0.5);
        border-color: var(--cat-color);
        color: var(--cat-color);
        font-weight: 700;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .tab-btn.active::before {
        opacity: 1;
        box-shadow: 0 0 8px var(--cat-color);
    }

    /* ============ Category Sections (Redesigned) ============ */
    .category-section {
        margin-bottom: 32px;
        animation: fadeIn 0.4s ease-out forwards;
    }

    .category-header {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 20px;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-left: 4px solid var(--cat-color);
        /* Color accent */
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
        position: relative;
        overflow: hidden;
    }

    .category-header::after {
        /* Subtle background tint */
        content: '';
        position: absolute;
        inset: 0;
        background: var(--cat-color);
        opacity: 0.04;
        pointer-events: none;
        z-index: 0;
    }

    .category-icon {
        font-size: 2rem;
        flex-shrink: 0;
        z-index: 1;
        filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
    }

    .category-info {
        flex: 1;
        min-width: 0;
        z-index: 1;
    }

    .category-info h2 {
        margin: 0 0 4px 0;
        font-size: 1.25rem;
        color: var(--text-main);
        font-weight: 700;
        letter-spacing: -0.01em;
    }

    .category-info p {
        margin: 0;
        font-size: 0.9rem;
        color: var(--text-muted);
        line-height: 1.4;
    }

    .category-count {
        flex-shrink: 0;
        padding: 6px 14px;
        background: var(--bg-dark);
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--text-secondary);
        z-index: 1;
        border: 1px solid var(--border-color);
    }

    .btn-delete-category {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        border: 1px solid transparent;
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
        cursor: pointer;
        transition: all 0.2s ease;
        flex-shrink: 0;
        z-index: 1;
    }

    .btn-delete-category:hover {
        background: #ef4444;
        color: white;
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    }

    .btn-delete-category .material-icons-round {
        font-size: 20px;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* ============ Specialty Grid ============ */
    .specialties-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 16px;
    }

    /* ============ Specialty Card (Redesigned) ============ */
    .specialty-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 20px;
        display: flex;
        flex-direction: column;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        min-height: 200px;
        position: relative;
        overflow: hidden;
    }

    .specialty-card:hover {
        border-color: var(--primary);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.04);
        transform: translateY(-4px);
    }

    .specialty-header {
        display: flex;
        gap: 16px;
        margin-bottom: 16px;
        align-items: flex-start;
    }

    .specialty-badge {
        font-size: 2.5rem;
        line-height: 1;
        filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
        transition: transform 0.3s ease;
    }

    .specialty-card:hover .specialty-badge {
        transform: scale(1.1) rotate(5deg);
    }

    .specialty-title {
        flex: 1;
        min-width: 0;
    }

    .specialty-title h3 {
        margin: 0 0 8px 0;
        font-size: 1.1rem;
        font-weight: 700;
        line-height: 1.4;
        color: var(--text-main);
        letter-spacing: -0.01em;
    }

    .specialty-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .meta-tag {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        background: var(--bg-dark);
        border: 1px solid var(--border-color);
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-muted);
        transition: all 0.2s;
    }

    .meta-tag:hover {
        border-color: var(--primary);
        color: var(--primary);
    }

    .difficulty-stars {
        color: #fbbf24;
        /* Amber 400 */
        letter-spacing: 1px;
    }

    .specialty-desc {
        flex: 1;
        color: var(--text-muted);
        font-size: 0.9rem;
        line-height: 1.6;
        margin-bottom: 20px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .specialty-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-top: auto;
        padding-top: 20px;
        border-top: 1px solid var(--border-color);
    }

    .xp-reward {
        display: flex;
        align-items: center;
        gap: 6px;
        color: var(--accent-emerald);
        background: var(--accent-emerald-bg);
        padding: 4px 10px;
        border-radius: 20px;
        font-weight: 700;
        font-size: 0.8rem;
    }

    .card-actions {
        display: flex;
        gap: 8px;
    }

    .btn-icon-action {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        border: 1px solid transparent;
        background: transparent;
        color: var(--text-muted);
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-icon-action:hover {
        background: var(--bg-dark);
        color: var(--text-main);
    }

    .btn-icon-action.danger:hover {
        background: #fee2e2;
        color: #ef4444;
    }

    .btn-card-assign {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        background: linear-gradient(135deg, var(--primary), var(--primary-hover));
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.85rem;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: 0 2px 4px rgba(6, 182, 212, 0.2);
    }

    .btn-card-assign:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(6, 182, 212, 0.3);
    }

    /* ============ Responsive - Tablet ============ */
    @media (max-width: 992px) {
        .specialties-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    /* ============ Responsive - Mobile ============ */
    @media (max-width: 768px) {
        .admin-main {
            padding: 16px;
        }

        .page-hero h1 {
            font-size: 1.5rem;
        }

        .page-toolbar {
            flex-direction: column;
            align-items: stretch;
        }

        .search-section {
            max-width: 100%;
        }

        .actions-group {
            justify-content: center;
        }

        .tabs {
            margin-bottom: 20px;
            padding-bottom: 10px;
            gap: 6px;
        }

        .tab-btn {
            padding: 6px 12px;
            font-size: 0.8rem;
        }

        .specialties-grid {
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .category-header {
            padding: 10px 14px;
        }

        .category-icon {
            font-size: 1.25rem;
        }

        .category-info h2 {
            font-size: 1rem;
        }

        .specialty-card {
            min-height: auto;
            padding: 14px;
        }

        .specialty-badge {
            font-size: 1.75rem;
        }

        .specialty-footer {
            flex-wrap: wrap;
        }
    }

    /* ============ Responsive - Small Mobile ============ */
    @media (max-width: 480px) {
        .admin-main {
            padding: 12px;
        }

        .repository-header h1 {
            font-size: 1.1rem;
        }

        .header-subtitle {
            font-size: 0.8rem;
        }

        .tab-btn {
            padding: 5px 10px;
            font-size: 0.75rem;
        }

        .specialty-card {
            padding: 12px;
        }

        .specialty-title h3 {
            font-size: 0.95rem;
        }

        .specialty-footer {
            gap: 8px;
        }

        .btn-card-assign {
            padding: 8px 14px;
            font-size: 0.8rem;
            flex: 1;
            justify-content: center;
        }

        .xp-reward {
            font-size: 0.85rem;
        }
    }

    /* ============ Specialty Card - Clickable ============ */
    .specialty-card {
        cursor: pointer;
    }

    /* ============ Modal Styles ============ */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.8);
        backdrop-filter: blur(4px);
        z-index: 1000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .modal-overlay.active {
        display: flex;
    }

    .modal-content {
        background: #1e1e32;
        border: 1px solid var(--border-light);
        border-radius: 16px;
        max-width: 700px;
        width: 100%;
        max-height: 90vh;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        animation: modalSlideIn 0.3s ease;
    }

    @keyframes modalSlideIn {
        from {
            transform: translateY(-20px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .modal-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border-light);
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .modal-badge {
        font-size: 3rem;
    }

    .modal-title-section {
        flex: 1;
    }

    .modal-title {
        font-size: 1.4rem;
        font-weight: 700;
        margin: 0 0 8px 0;
    }

    .modal-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .modal-tag {
        padding: 4px 10px;
        background: rgba(0, 0, 0, 0.3);
        border-radius: 20px;
        font-size: 0.75rem;
        color: var(--text-secondary);
    }

    .modal-tag.xp {
        background: rgba(0, 255, 136, 0.2);
        color: var(--accent-green);
        font-weight: 600;
    }

    .modal-close {
        background: none;
        border: none;
        color: var(--text-secondary);
        font-size: 1.5rem;
        cursor: pointer;
        padding: 8px;
        border-radius: 8px;
        transition: all 0.2s;
    }

    .modal-close:hover {
        background: rgba(255, 255, 255, 0.1);
        color: var(--text-primary);
    }

    .modal-body {
        padding: 24px;
        overflow-y: auto;
        flex: 1;
    }

    .modal-section {
        margin-bottom: 20px;
    }

    .modal-section:last-child {
        margin-bottom: 0;
    }

    /* ============ Modals (Redesigned) ============ */
    .modal-overlay,
    .create-modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(4px);
        z-index: 1000;
        align-items: center;
        justify-content: center;
        padding: 20px;
        animation: fadeIn 0.2s ease-out;
    }

    .modal-overlay.active,
    .create-modal-overlay.active {
        display: flex;
    }

    .modal-content,
    .create-modal {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        width: 100%;
        max-width: 600px;
        max-height: 85vh;
        display: flex;
        flex-direction: column;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        animation: slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px) scale(0.96);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    /* Modal Header */
    .modal-header,
    .create-modal-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: var(--bg-card);
        border-radius: 16px 16px 0 0;
    }

    .modal-header h2,
    .create-modal-header h2 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-main);
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .modal-close {
        background: transparent;
        border: none;
        color: var(--text-muted);
        cursor: pointer;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        font-size: 1.5rem;
    }

    .modal-close:hover {
        background: var(--bg-dark);
        color: var(--text-main);
    }

    /* Modal Body */
    .modal-body,
    .create-modal-body {
        padding: 24px;
        overflow-y: auto;
        flex: 1;
    }

    /* Forms */
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: var(--text-main);
        font-size: 0.9rem;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 10px 14px;
        background: var(--bg-dark);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        color: var(--text-main);
        font-family: inherit;
        font-size: 0.95rem;
        transition: all 0.2s;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.1);
        background: var(--bg-card);
    }

    .form-group textarea {
        min-height: 120px;
        resize: vertical;
        line-height: 1.5;
    }

    .form-hint {
        font-size: 0.8rem;
        color: var(--text-muted);
        margin-top: 6px;
    }

    .xp-suggestion {
        background: var(--accent-emerald-bg);
        color: var(--accent-emerald);
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 0.85rem;
        margin-top: 8px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-weight: 500;
    }

    /* Modal Footer */
    .modal-footer,
    .create-modal-footer {
        padding: 20px 24px;
        border-top: 1px solid var(--border-color);
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        background: var(--bg-card);
        border-radius: 0 0 16px 16px;
    }

    .btn-cancel {
        padding: 10px 20px;
        background: transparent;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        color: var(--text-muted);
        cursor: pointer;
        font-size: 0.9rem;
        font-weight: 500;
        transition: all 0.2s;
    }

    .btn-cancel:hover {
        background: var(--bg-dark);
        color: var(--text-main);
        border-color: var(--text-muted);
    }

    .btn-create,
    .btn-modal-assign {
        padding: 10px 20px;
        background: linear-gradient(135deg, var(--primary), var(--primary-hover));
        border: none;
        border-radius: 8px;
        color: white;
        font-weight: 600;
        cursor: pointer;
        font-size: 0.9rem;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
        text-decoration: none;
        box-shadow: 0 2px 4px rgba(6, 182, 212, 0.2);
    }

    .btn-create:hover,
    .btn-modal-assign:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(6, 182, 212, 0.3);
    }

    .btn-create:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    /* Detail Modal Specifics */
    .modal-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 8px;
    }

    .modal-tag {
        background: var(--bg-dark);
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.8rem;
        color: var(--text-secondary);
        border: 1px solid var(--border-color);
    }

    .modal-tag.xp {
        background: var(--accent-emerald-bg);
        color: var(--accent-emerald);
        border-color: transparent;
        font-weight: 600;
    }

    .modal-section {
        margin-bottom: 24px;
    }

    .modal-section-title {
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--text-main);
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .modal-description {
        color: var(--text-secondary);
        line-height: 1.6;
    }

    .requirements-list li {
        background: var(--bg-dark);
        border: 1px solid var(--border-color);
        margin-bottom: 8px;
        border-radius: 8px;
    }

    .req-number {
        background: var(--primary);
        color: white;
    }

    @media (max-width: 600px) {
        .form-row {
            grid-template-columns: 1fr;
            gap: 0;
        }

        .modal-content,
        .create-modal {
            height: 100vh;
            max-height: 100vh;
            border-radius: 0;
            border: none;
        }

        .modal-header,
        .create-modal-header {
            border-radius: 0;
        }

        .modal-footer,
        .create-modal-footer {
            border-radius: 0;
            padding-bottom: max(20px, env(safe-area-inset-bottom));
        }
    }
</style>


    <!-- Page Toolbar -->
    <div class="page-toolbar">
        <div class="search-section">
            <div class="search-wrapper">
                <span class="material-icons-round search-icon">search</span>
                <input type="text" id="searchInput" class="search-input" placeholder="Buscar especialidade..."
                    oninput="filterSpecialties()">
            </div>
        </div>
        <div class="actions-group">
            <button class="btn-toolbar primary" onclick="openCreateSpecialtyModal()">
                <span class="material-icons-round">add_circle</span> Nova Especialidade
            </button>
        </div>
    </div>

    <div class="tabs">
        <button class="tab-btn active" data-category="all" onclick="filterByCategory('all')"
            style="--cat-color: var(--primary);">
            Todas
        </button>
        <?php foreach ($categories as $cat): ?>
            <button class="tab-btn" data-category="<?= $cat['id'] ?>" onclick="filterByCategory('<?= $cat['id'] ?>')"
                style="--cat-color: <?= $cat['color'] ?>;">
                <?php if (str_starts_with($cat['icon'] ?? '', 'fa-')): ?>
                    <i class="<?= htmlspecialchars($cat['icon']) ?>"></i>
                <?php else: ?>
                    <?= $cat['icon'] ?>
                <?php endif; ?>
                <?= htmlspecialchars($cat['name']) ?>
            </button>
        <?php endforeach; ?>
    </div>

    <?php foreach ($grouped as $catId => $data): ?>
            <section class="category-section" data-category="<?= $catId ?>">
                <div class="category-header" style="--cat-color: <?= $data['category']['color'] ?>;">
                    <span class="category-icon">
                        <?php if (str_starts_with($data['category']['icon'] ?? '', 'fa-')): ?>
                            <i class="<?= htmlspecialchars($data['category']['icon']) ?>" style="color: <?= htmlspecialchars($data['category']['color']) ?>;"></i>
                        <?php else: ?>
                            <?= $data['category']['icon'] ?>
                        <?php endif; ?>
                    </span>
                    <div class="category-info">
                        <h2><?= htmlspecialchars($data['category']['name']) ?></h2>
                        <p><?= htmlspecialchars($data['category']['description']) ?></p>
                    </div>
                    <span class="category-count"><?= count($data['specialties']) ?> especialidades</span>
                    <?php if (!empty($data['category']['is_learning_category'])): ?>
                        <button type="button" class="btn-delete-category" title="Excluir categoria e todas as especialidades"
                            onclick="event.stopPropagation(); deleteCategoryWithSpecialties('<?= $data['category']['db_id'] ?? '' ?>', '<?= htmlspecialchars(addslashes($data['category']['name']), ENT_QUOTES) ?>', <?= count($data['specialties']) ?>);">
                            <span class="material-icons-round">delete_forever</span>
                        </button>
                    <?php endif; ?>
                </div>

                <div class="specialties-grid">
                    <?php foreach ($data['specialties'] as $spec): ?>
                        <?php
                        // Prepare specialty data for modal (including requirements from DB if available)
                        $specData = [
                            'id' => $spec['id'],
                            'name' => $spec['name'],
                            'badge_icon' => $spec['badge_icon'],
                            'type' => $spec['type'] ?? 'indoor',
                            'duration_hours' => $spec['duration_hours'] ?? 4,
                            'difficulty' => $spec['difficulty'] ?? 1,
                            'xp_reward' => $spec['xp_reward'] ?? 100,
                            'description' => $spec['description'] ?? '',
                            'requirements' => $spec['requirements'] ?? [],
                            'category_name' => $data['category']['name'] ?? '',
                            'category_icon' => $data['category']['icon'] ?? ''
                        ];
                        $specJson = htmlspecialchars(json_encode($specData, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
                        ?>
                        <div class="specialty-card" data-name="<?= strtolower($spec['name']) ?>" data-specialty="<?= $specJson ?>"
                            onclick="openSpecialtyModal(this)">
                            <div class="specialty-header">
                                <div class="specialty-badge">
                                    <?php if (str_starts_with($spec['badge_icon'] ?? '', 'fa-')): ?>
                                        <i class="<?= htmlspecialchars($spec['badge_icon']) ?>"></i>
                                    <?php else: ?>
                                        <?= $spec['badge_icon'] ?>
                                    <?php endif; ?>
                                </div>
                                <div class="specialty-title">
                                    <h3><?= htmlspecialchars($spec['name']) ?></h3>
                                    <div class="specialty-meta">
                                        <span class="meta-tag" title="Tipo">
                                            <?= ($spec['type'] ?? 'indoor') === 'outdoor' ? '<span class="material-icons-round" style="font-size:12px">forest</span> Externo' : '<span class="material-icons-round" style="font-size:12px">home</span> Interno' ?>
                                        </span>
                                        <span class="meta-tag" title="Duração">
                                            <span class="material-icons-round" style="font-size:12px">schedule</span>
                                            <?= $spec['duration_hours'] ?? 4 ?>h
                                        </span>
                                        <span class="meta-tag difficulty-stars" title="Dificuldade">
                                            <?= str_repeat('★', $spec['difficulty'] ?? 1) . str_repeat('☆', 5 - ($spec['difficulty'] ?? 1)) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <p class="specialty-desc">
                                <?= htmlspecialchars($spec['description'] ?? 'Sem descrição disponível.') ?>
                            </p>

                            <div class="specialty-footer">
                                <span class="xp-reward" title="XP Recompensa">
                                    <span class="material-icons-round" style="font-size:14px">bolt</span>
                                    <?= $spec['xp_reward'] ?? 100 ?>
                                </span>
                                <div class="card-actions">
                                    <a href="<?= base_url($tenant['slug'] . '/admin/especialidades/' . $spec['id'] . '/atribuir') ?>"
                                        class="btn-card-assign" onclick="event.stopPropagation();">
                                        <span class="material-icons-round" style="font-size:16px">group_add</span>
                                        Atribuir
                                    </a>
                                    <button class="btn-icon-action" title="Editar" onclick="openEditModal(`<?= htmlspecialchars(json_encode($spec)) ?>`)">
                                        <span class="material-icons-round" style="font-size:18px">edit</span>
                                    </button>
                                    <button type="button" class="btn-icon-action danger" title="Excluir"
                                        onclick="event.stopPropagation(); deleteSpecialty('<?= $spec['id'] ?>', '<?= htmlspecialchars(addslashes($spec['name'])) ?>');">
                                        <span class="material-icons-round" style="font-size:18px">delete</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
    <?php endforeach; ?>
    <!-- End Content -->

    <script>var tenantSlug = '<?= $tenant['slug'] ?>';</script>



    <!-- Create Specialty Modal -->
    <div id="createSpecialtyModal" class="create-modal-overlay" onclick="closeCreateModal(event)">
        <div class="create-modal" onclick="event.stopPropagation()">
            <div class="create-modal-header">
                <h2><span class="material-icons-round" style="color:var(--primary)">add_circle</span> Nova Especialidade
                </h2>
                <button class="modal-close" onclick="closeCreateSpecialtyModal()"><span
                        class="material-icons-round">close</span></button>
            </div>
            <form id="createSpecialtyForm" onsubmit="submitNewSpecialty(event)">
                <div class="create-modal-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="specCategory">Categoria *</label>
                            <select id="specCategory" name="category_id" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= $cat['icon'] ?>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="specIcon">Emoji/Ícone</label>
                            <div style="position: relative; display: flex; gap: 8px;">
                                <input type="text" id="specIcon" name="badge_icon" value="📘" maxlength="5" style="flex: 1;">
                                <button type="button" id="emojiTrigger" class="btn-secondary" style="padding: 0 12px; font-size: 1.2rem;">😊</button>
                            </div>
                            <div id="emojiPickerContainer"></div>


                            <script type="module">
                                function initEmojiPicker() {
                                    const trigger = document.getElementById('emojiTrigger');
                                    const input = document.getElementById('specIcon');
                                    const container = document.getElementById('emojiPickerContainer');
                                    
                                    if (!trigger || !input || !container || trigger.dataset.pickerInitialized) return;
                                    
                                    trigger.dataset.pickerInitialized = 'true';
                                    let pickerElement = null;

                                    trigger.addEventListener('click', (e) => {
                                        e.stopPropagation();
                                        
                                        // Toggle: if picker exists, remove it
                                        if (pickerElement) {
                                            pickerElement.remove();
                                            pickerElement = null;
                                            return;
                                        }

                                        // Create picker
                                        pickerElement = document.createElement('emoji-picker');
                                        pickerElement.style.position = 'absolute';
                                        pickerElement.style.zIndex = '1000';
                                        pickerElement.style.right = '0';
                                        pickerElement.style.top = '40px';
                                        container.appendChild(pickerElement);

                                        // Listen for emoji selection
                                        pickerElement.addEventListener('emoji-click', event => {
                                            input.value = event.detail.unicode;
                                            pickerElement.remove();
                                            pickerElement = null;
                                        });

                                        // Close when clicking outside
                                        function closePicker(event) {
                                            if (pickerElement && !pickerElement.contains(event.target) && event.target !== trigger) {
                                                pickerElement.remove();
                                                pickerElement = null;
                                                document.removeEventListener('click', closePicker);
                                            }
                                        }
                                        setTimeout(() => document.addEventListener('click', closePicker), 0);
                                    });
                                }

                                // Run on HTMX load (handles both initial and swaps)
                                if (typeof htmx !== 'undefined') {
                                    htmx.onLoad(() => initEmojiPicker());
                                }
                                // Also run immediately if DOM is already loaded
                                if (document.readyState === 'loading') {
                                    document.addEventListener('DOMContentLoaded', initEmojiPicker);
                                } else {
                                    initEmojiPicker();
                                }
                            </script>
                            <div class="form-hint">Digite um emoji para representar</div>
                        </div>
                    </div>

                    <div class="form-group" style="position: relative;">
                        <label for="specName">Nome da Especialidade *</label>
                        <input type="text" id="specName" name="name" required
                            placeholder="Ex: Arte de Contar Histórias" autocomplete="off">
                        <div id="specNameAutocomplete" style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: var(--card-bg, #fff); border: 1px solid var(--border-color, #ddd); border-radius: 8px; max-height: 200px; overflow-y: auto; z-index: 100; box-shadow: 0 4px 12px rgba(0,0,0,0.1);"></div>
                        <div id="specNameWarning" style="display: none; color: #f7b32b; font-size: 0.85rem; margin-top: 4px;">
                            ⚠️ Já existe uma especialidade com nome similar
                        </div>
                    </div>

                    <script>
                        // Initialize for Specialty Modal
                        // Use a check to ensure function exists (it should from layout)
                        document.addEventListener('DOMContentLoaded', () => {
                             if (typeof setupAutocomplete === 'function') {
                                setupAutocomplete(
                                    'specName', 
                                    'specNameAutocomplete', 
                                    'specNameWarning', 
                                    '<?= base_url($tenant['slug'] . '/api/specialties/search') ?>'
                                );
                             }
                        });
                    </script>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="specType">Tipo</label>
                            <select id="specType" name="type" onchange="updateXpSuggestion()">
                                <option value="indoor">🏠 Indoor (Teórico)</option>
                                <option value="outdoor">🏕️ Outdoor (Prático)</option>
                                <option value="mixed">🔄 Misto</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="specDifficulty">Dificuldade</label>
                            <select id="specDifficulty" name="difficulty" onchange="updateXpSuggestion()">
                                <option value="1">⭐ Muito Fácil</option>
                                <option value="2" selected>⭐⭐ Fácil</option>
                                <option value="3">⭐⭐⭐ Médio</option>
                                <option value="4">⭐⭐⭐⭐ Difícil</option>
                                <option value="5">⭐⭐⭐⭐⭐ Muito Difícil</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="specDuration">Duração Estimada (horas)</label>
                            <input type="number" id="specDuration" name="duration_hours" value="4" min="1" max="100">
                        </div>
                        <div class="form-group">
                            <label for="specXp">XP Recompensa</label>
                            <input type="number" id="specXp" name="xp_reward" value="100" min="10" max="1000">
                            <div id="xpSuggestion" class="xp-suggestion">
                                <span class="material-icons-round" style="font-size:16px">lightbulb</span>
                                Sugestão: 100 XP
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="specDescription">Descrição</label>
                        <textarea id="specDescription" name="description"
                            placeholder="Descreva brevemente o objetivo desta especialidade..."></textarea>
                    </div>
                </div>
                <div class="create-modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeCreateSpecialtyModal()">Cancelar</button>
                    <button type="submit" class="btn-create" id="btnCreate">
                        <span class="material-icons-round">rocket_launch</span> Criar Especialidade
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Specialty Detail Modal -->
    <div id="specialtyModal" class="modal-overlay" onclick="closeModal(event)">
        <div class="modal-content" onclick="event.stopPropagation()">
            <div class="modal-header">
                <h2>
                    <span id="modalBadge" style="font-size:1.5rem; margin-right:8px;"></span>
                    <span id="modalTitle"></span>
                </h2>
                <button class="modal-close" onclick="closeModal()"><span
                        class="material-icons-round">close</span></button>
            </div>

            <div class="modal-body">
                <div class="modal-tags" id="modalTags" style="margin-bottom: 24px;"></div>

                <div class="modal-section">
                    <div class="modal-section-title">
                        <span class="material-icons-round">description</span> Descrição
                    </div>
                    <p class="modal-description" id="modalDescription"></p>
                </div>

                <div class="modal-section">
                    <div class="modal-section-title">
                        <span class="material-icons-round">checklist</span>
                        Requisitos (<span id="reqCount">0</span>)
                    </div>
                    <ul class="requirements-list" id="modalRequirements"></ul>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-cancel" onclick="closeModal()">Fechar</button>
                <a id="modalAssignBtn" href="#" class="btn-modal-assign">
                    <span class="material-icons-round">group_add</span> Atribuir Especialidade
                </a>
            </div>
        </div>
    </div>

    <script>
        window.tenantSlug = '<?= $tenant['slug'] ?>';
        window.currentSpecialty = null;

        /**
         * Redirects to the full Program Editor (Requirements, Quizzes, etc.)
         */
        function openEditModal(data) {
            if (typeof data === 'string') {
                try {
                    data = JSON.parse(data);
                } catch (e) {
                    console.error('Error parsing specialty data', e);
                    return;
                }
            }

            const programId = data.program_id || data.id;
            if (!programId) {
                console.error('No Program ID found for editing');
                return;
            }

            // Extract numeric ID if prefixed (e.g., "prog_6" -> "6")
            const cleanId = String(programId).replace('prog_', '');
            
            // Redirect to the Program Editor
            window.location.href = `/${window.tenantSlug}/admin/programas/${cleanId}/editar`;
        }

        function openSpecialtyModal(cardElement) {
            const data = JSON.parse(cardElement.dataset.specialty);
            currentSpecialty = data;

            // Populate modal
            const modalBadge = document.getElementById('modalBadge');
            if (data.badge_icon && data.badge_icon.startsWith('fa-')) {
                modalBadge.innerHTML = `<i class="${data.badge_icon}"></i>`;
            } else {
                modalBadge.textContent = data.badge_icon || '📘';
            }
            document.getElementById('modalTitle').textContent = data.name;
            document.getElementById('modalDescription').textContent = data.description || 'Sem descrição disponível.';

            // Tags
            const categoryIconHtml = (data.category_icon && data.category_icon.startsWith('fa-')) 
                ? `<i class="${data.category_icon}"></i>` 
                : (data.category_icon || '📂');
                
            const tagsHtml = `
                <span class="modal-tag">${categoryIconHtml} ${data.category_name}</span>
                <span class="modal-tag">${data.type === 'outdoor' ? '🏕️ Externo' : '🏠 Interno'}</span>
                <span class="modal-tag">⏱️ ${data.duration_hours}h</span>
                <span class="modal-tag">${'⭐'.repeat(data.difficulty)}</span>
                <span class="modal-tag xp">🌟 ${data.xp_reward} XP</span>
            `;
            document.getElementById('modalTags').innerHTML = tagsHtml;

            // Requirements
            const reqList = document.getElementById('modalRequirements');
            const requirements = data.requirements || [];
            document.getElementById('reqCount').textContent = requirements.length;

            if (requirements.length === 0) {
                reqList.innerHTML = '<div class="no-requirements">Os requisitos serão carregados quando a especialidade for atribuída.</div>';
            } else {
                reqList.innerHTML = requirements.map((req, i) => {
                    const typeLabel = getTypeLabel(req.type || 'text');
                    const typeBadgeClass = (req.type === 'practical' || req.type === 'file_upload') ? 'practical' : '';
                    return `
                        <li>
                            <span class="req-number">${i + 1}</span>
                            <div>
                                ${req.title || req.description || req}
                                <span class="req-type-badge ${typeBadgeClass}">${typeLabel}</span>
                            </div>
                        </li>
                    `;
                }).join('');
            }

            // Set assign button URL
            document.getElementById('modalAssignBtn').href = `/${tenantSlug}/admin/especialidades/${data.id}/atribuir`;

            // Show modal
            document.getElementById('specialtyModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function getTypeLabel(type) {
            const labels = {
                'text': '📝 Texto',
                'practical': '🛠️ Prático',
                'file_upload': '📎 Arquivo',
                'multiple_choice': '🔘 Múltipla Escolha',
                'checkbox': '☑️ Checkbox'
            };
            return labels[type] || '📝 Texto';
        }

        function closeModal(event) {
            if (event && event.target !== event.currentTarget) return;
            document.getElementById('specialtyModal').classList.remove('active');
            document.body.style.overflow = '';
            currentSpecialty = null;
        }

        // Close on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeModal();
        });

        function filterByCategory(catId) {
            // Update tabs
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.toggle('active', btn.dataset.category === catId);
            });

            // Filter sections
            document.querySelectorAll('.category-section').forEach(section => {
                if (catId === 'all') {
                    section.style.display = 'block';
                } else {
                    section.style.display = section.dataset.category === catId ? 'block' : 'none';
                }
            });
        }

        function filterSpecialties() {
            const query = document.getElementById('searchInput').value.toLowerCase();

            document.querySelectorAll('.specialty-card').forEach(card => {
                const name = card.dataset.name;
                card.style.display = name.includes(query) ? 'block' : 'none';
            });

            // Show all categories when searching
            document.querySelectorAll('.category-section').forEach(section => {
                const hasVisibleCards = section.querySelectorAll('.specialty-card[style*="display: block"]').length > 0 ||
                    section.querySelectorAll('.specialty-card:not([style])').length > 0;
                section.style.display = hasVisibleCards ? 'block' : 'none';
            });
        }

        // ============ Create Specialty Modal ============
        function openCreateSpecialtyModal() {
            document.getElementById('createSpecialtyModal').classList.add('active');
            document.getElementById('createSpecialtyForm').reset();
            document.getElementById('specIcon').value = '📘';
            updateXpSuggestion();
        }

        function closeCreateSpecialtyModal() {
            document.getElementById('createSpecialtyModal').classList.remove('active');
        }

        function closeCreateModal(event) {
            if (event.target.id === 'createSpecialtyModal') {
                closeCreateSpecialtyModal();
            }
        }


        function updateXpSuggestion() {
            const difficulty = parseInt(document.getElementById('specDifficulty').value);
            const type = document.getElementById('specType').value;

            // XP calculation based on difficulty and type
            const baseXp = [50, 100, 150, 200, 300][difficulty - 1];
            const typeMultiplier = type === 'outdoor' ? 1.2 : type === 'mixed' ? 1.1 : 1.0;
            const suggestedXp = Math.round(baseXp * typeMultiplier);

            document.getElementById('xpSuggestion').textContent = `💡 Sugestão: ${suggestedXp} XP`;
            document.getElementById('specXp').value = suggestedXp;
        }

        async function submitNewSpecialty(event) {
            event.preventDefault();

            const form = document.getElementById('createSpecialtyForm');
            const formData = new FormData(form);
            const btn = document.getElementById('btnCreate');

            btn.disabled = true;
            btn.textContent = '⏳ Criando...';

            try {
                const response = await fetch('<?= base_url($tenant["slug"] . "/admin/especialidades/criar") ?>', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    showToast('✅ Especialidade criada com sucesso!');
                    // Redirect to requirements editor
                    if (data.redirect) {
                        setTimeout(() => window.location.href = data.redirect, 500);
                    } else {
                        setTimeout(() => location.reload(), 500);
                    }
                } else {
                    showToast(data.error || 'Erro ao criar especialidade', 'error');
                }
            } catch (err) {
                console.error(err);
                showToast('Erro de conexão', 'error');
            } finally {
                btn.disabled = false;
                btn.textContent = '🚀 Criar Especialidade';
            }
        }

        // Filter specialties by search text
        function filterSpecialties() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase().trim();
            const cards = document.querySelectorAll('.specialty-card');
            const categorySections = document.querySelectorAll('.category-section');
            
            cards.forEach(card => {
                const name = card.querySelector('.specialty-name')?.textContent?.toLowerCase() || '';
                const description = card.querySelector('.specialty-description')?.textContent?.toLowerCase() || '';
                const matches = name.includes(searchTerm) || description.includes(searchTerm);
                card.style.display = matches ? '' : 'none';
            });

            // Hide empty category sections
            categorySections.forEach(section => {
                const visibleCards = section.querySelectorAll('.specialty-card[style=""], .specialty-card:not([style*="display: none"])');
                const hasVisibleCards = Array.from(section.querySelectorAll('.specialty-card')).some(c => c.style.display !== 'none');
                section.style.display = hasVisibleCards || !searchTerm ? '' : 'none';
            });
        }

        // Filter by category tab
        function filterByCategory(categoryId) {
            const tabs = document.querySelectorAll('.tab-btn');
            const sections = document.querySelectorAll('.category-section');
            
            // Update active tab
            tabs.forEach(tab => {
                if (tab.dataset.category === categoryId || (categoryId === 'all' && tab.dataset.category === 'all')) {
                    tab.classList.add('active');
                } else {
                    tab.classList.remove('active');
                }
            });

            // Show/hide sections
            sections.forEach(section => {
                if (categoryId === 'all' || section.dataset.category == categoryId) {
                    section.style.display = '';
                } else {
                    section.style.display = 'none';
                }
            });

            // Clear search when changing categories
            document.getElementById('searchInput').value = '';
        }

        function showToast(msg, type = 'success') {
            let toast = document.getElementById('toastNotification');
            if (!toast) {
                toast = document.createElement('div');
                toast.id = 'toastNotification';
                toast.style.cssText = 'position:fixed;bottom:24px;right:24px;padding:16px 24px;background:#1e1e32;border-left:4px solid #00ff88;border-radius:8px;box-shadow:0 4px 20px rgba(0,0,0,0.3);transform:translateX(150%);transition:transform 0.3s ease;z-index:2000;color:#fff;';
                document.body.appendChild(toast);
            }
            toast.textContent = msg;
            toast.style.borderLeftColor = type === 'error' ? '#f44336' : '#00ff88';
            toast.style.transform = 'translateX(0)';
            setTimeout(() => toast.style.transform = 'translateX(150%)', 3000);
        }

        // Delete specialty function
        async function deleteSpecialty(specId, specName) {
            const confirmed = await showConfirm({
                title: 'Excluir Especialidade',
                message: `Excluir "${specName}"? Esta ação não pode ser desfeita.`,
                icon: '🗑️',
                danger: true,
                okText: 'Excluir'
            });
            if (!confirmed) return;

            try {
                const resp = await fetch(`/${tenantSlug}/api/specialties/${specId}/delete`, { method: 'POST' });
                const data = await resp.json();
                if (data.success) {
                    showToast(data.message || 'Especialidade excluída!');
                    setTimeout(() => location.reload(), 500);
                } else {
                    showToast(data.error || 'Erro ao excluir', 'error');
                }
            } catch (err) {
                showToast('Erro de conexão', 'error');
            }
        }

        // Delete category with all specialties (cascade delete)
        async function deleteCategoryWithSpecialties(categoryId, categoryName, specialtyCount) {
            const warningMsg = specialtyCount > 0 
                ? `Excluir a categoria "${categoryName}" e suas ${specialtyCount} especialidade(s)?<br><br><strong style="color:#ef4444;">⚠️ ATENÇÃO:</strong> Esta ação irá excluir permanentemente:<br>• A categoria<br>• Todas as especialidades desta categoria<br>• Todas as atribuições de todos os usuários`
                : `Excluir a categoria "${categoryName}"?<br><br>Esta categoria está vazia.`;
            
            const confirmed = await showConfirm({
                title: '🗑️ Excluir Categoria',
                message: warningMsg,
                icon: '⚠️',
                danger: true,
                okText: 'Excluir Tudo'
            });
            if (!confirmed) return;

            try {
                showToast('Excluindo categoria...', 'info');
                const resp = await fetch(`/${tenantSlug}/admin/categorias/${categoryId}/delete-cascade`, { method: 'POST' });
                const data = await resp.json();
                if (data.success) {
                    showToast(data.message || 'Categoria e especialidades excluídas!');
                    setTimeout(() => location.reload(), 800);
                } else {
                    showToast(data.error || 'Erro ao excluir categoria', 'error');
                }
            } catch (err) {
                console.error(err);
                showToast('Erro de conexão', 'error');
            }
        }

        // Confirmation Modal System
        var confirmCallback = null;

        function showConfirm(options) {
            return new Promise((resolve) => {
                const { title, message, icon = '⚠️', danger = false, okText = 'Confirmar' } = options;

                let modal = document.getElementById('confirmModalOverlay');
                if (!modal) {
                    modal = document.createElement('div');
                    modal.id = 'confirmModalOverlay';
                    modal.style.cssText = 'display:none;position:fixed;inset:0;background:rgba(15, 23, 42, 0.4);backdrop-filter:blur(4px);z-index:3000;align-items:center;justify-content:center;transition:opacity 0.2s;';
                    modal.innerHTML = `
                         <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:16px;max-width:400px;width:90%;padding:32px;text-align:center;box-shadow:0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);transform:scale(0.95);transition:transform 0.2s;">
                            <div id="cModalIcon" style="font-size:3.5rem;margin-bottom:20px;display:inline-block;filter:drop-shadow(0 4px 6px rgba(0,0,0,0.1));"></div>
                            <h3 id="cModalTitle" style="margin:0 0 12px;font-size:1.25rem;font-weight:700;color:#0f172a;"></h3>
                            <p id="cModalMsg" style="color:#64748b;margin:0 0 32px;font-size:0.95rem;line-height:1.6;"></p>
                            <div style="display:flex;gap:12px;justify-content:center;">
                                <button onclick="closeConfirmModal(false)" style="padding:12px 24px;border-radius:8px;font-weight:600;cursor:pointer;border:1px solid #cbd5e1;background:#fff;color:#64748b;transition:all 0.2s;">Cancelar</button>
                                <button id="cModalOk" onclick="closeConfirmModal(true)" style="padding:12px 24px;border-radius:8px;font-weight:600;cursor:pointer;border:none;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);transition:all 0.2s;"></button>
                            </div>
                        </div>
                    `;
                    document.body.appendChild(modal);
                }

                // Animate In
                setTimeout(() => {
                    modal.style.opacity = '1';
                    modal.querySelector('div').style.transform = 'scale(1)';
                }, 10);

                document.getElementById('cModalIcon').textContent = icon;
                document.getElementById('cModalTitle').textContent = title;
                document.getElementById('cModalMsg').textContent = message;
                const okBtn = document.getElementById('cModalOk');
                okBtn.textContent = okText;
                // Button Styles
                if (danger) {
                    okBtn.style.background = '#ef4444'; // Red-500
                    okBtn.style.color = '#ffffff';
                    okBtn.onmouseover = () => okBtn.style.background = '#dc2626';
                    okBtn.onmouseout = () => okBtn.style.background = '#ef4444';
                } else {
                    okBtn.style.background = 'linear-gradient(135deg, #06b6d4, #10b981)'; // Cyan to Emerald
                    okBtn.style.color = '#ffffff';
                    okBtn.onmouseover = () => { okBtn.style.transform = 'translateY(-1px)'; okBtn.style.boxShadow = '0 6px 12px -2px rgba(6, 182, 212, 0.3)'; };
                    okBtn.onmouseout = () => { okBtn.style.transform = 'translateY(0)'; okBtn.style.boxShadow = '0 4px 6px -1px rgba(0,0,0,0.1)'; };
                }

                confirmCallback = resolve;
                modal.style.display = 'flex';
            });
        }

        function closeConfirmModal(result) {
            document.getElementById('confirmModalOverlay').style.display = 'none';
            if (confirmCallback) {
                confirmCallback(result);
                confirmCallback = null;
            }
        }
    </script>