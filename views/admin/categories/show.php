<?php
/**
 * Admin: View Category Programs
 * 
 * Shows all programs in a specific learning category.
 */
$pageTitle = $category['name'];
$pageIcon = $category['icon'];
?>
<style>
    /* Category Header */
    .category-header {
        display: flex;
        align-items: center;
        gap: 20px;
        padding: 24px 28px;
        border-radius: var(--radius-xl);
        margin-bottom: 28px;
        animation: slideUp 0.4s ease-out;
        box-shadow: var(--shadow-lg);
    }

    .category-icon {
        font-size: 3.5rem;
        filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.2));
    }

    .category-info {
        flex: 1;
    }

    .category-info h1 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 700;
        color: #fff;
    }

    .category-info p {
        margin: 8px 0 0;
        color: rgba(255, 255, 255, 0.85);
        font-size: 0.95rem;
    }

    .category-stats {
        display: flex;
        gap: 12px;
    }

    .stat-badge {
        padding: 10px 18px;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(8px);
        border-radius: 24px;
        font-size: 0.875rem;
        font-weight: 600;
        color: #fff;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .back-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: var(--radius-lg);
        color: #fff;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 600;
        transition: var(--transition-bounce);
    }

    .back-btn:hover {
        background: rgba(255, 255, 255, 0.25);
        transform: translateX(-4px);
    }

    /* Programs Grid */
    .programs-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 20px;
    }

    /* Program Card - Premium */
    .program-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-xl);
        padding: 20px;
        display: flex;
        flex-direction: column;
        transition: var(--transition-bounce);
        min-height: 200px;
        box-shadow: var(--shadow-card);
        animation: slideUp 0.5s ease-out backwards;
        position: relative;
        overflow: hidden;
    }

    .program-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--gradient-primary);
        transform: scaleX(0);
        transition: transform 0.3s ease;
        transform-origin: left;
    }

    .program-card:hover {
        border-color: rgba(6, 182, 212, 0.3);
        transform: translateY(-6px);
        box-shadow: var(--shadow-card-hover);
    }

    .program-card:hover::before {
        transform: scaleX(1);
    }

    .program-card:nth-child(1) { animation-delay: 0.05s; }
    .program-card:nth-child(2) { animation-delay: 0.1s; }
    .program-card:nth-child(3) { animation-delay: 0.15s; }
    .program-card:nth-child(4) { animation-delay: 0.2s; }
    .program-card:nth-child(5) { animation-delay: 0.25s; }
    .program-card:nth-child(6) { animation-delay: 0.3s; }

    .program-header {
        display: flex;
        gap: 14px;
        margin-bottom: 12px;
    }

    .program-badge {
        font-size: 2.5rem;
        flex-shrink: 0;
        filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
        transition: transform 0.3s ease;
    }

    .program-card:hover .program-badge {
        transform: scale(1.1) rotate(5deg);
    }

    .program-title {
        flex: 1;
        min-width: 0;
    }

    .program-title h3 {
        margin: 0 0 8px 0;
        font-size: 1.05rem;
        font-weight: 600;
        line-height: 1.4;
        color: var(--text-main);
    }

    .program-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .meta-tag {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        background: var(--bg-dark);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-muted);
    }

    .meta-tag.status-published {
        background: var(--accent-emerald-bg);
        border-color: var(--accent-emerald-border);
        color: var(--accent-emerald);
    }

    .meta-tag.status-draft {
        background: var(--accent-amber-bg);
        border-color: rgba(245, 158, 11, 0.2);
        color: var(--accent-amber);
    }

    .program-desc {
        flex: 1;
        color: var(--text-muted);
        font-size: 0.9rem;
        line-height: 1.6;
        margin-bottom: 16px;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    .program-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-top: auto;
        padding-top: 16px;
        border-top: 1px solid var(--border-color);
    }

    .xp-reward {
        display: flex;
        align-items: center;
        gap: 6px;
        color: var(--accent-emerald);
        font-weight: 700;
        font-size: 0.95rem;
    }

    .btn-edit {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: var(--gradient-primary);
        border: none;
        border-radius: var(--radius-lg);
        color: white;
        font-weight: 600;
        font-size: 0.875rem;
        text-decoration: none;
        cursor: pointer;
        transition: var(--transition-bounce);
        box-shadow: 0 4px 14px rgba(6, 182, 212, 0.25);
    }

    .btn-edit:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(6, 182, 212, 0.35);
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 80px 24px;
        background: var(--bg-card);
        border: 2px dashed var(--border-color);
        border-radius: var(--radius-xl);
        animation: fadeIn 0.5s ease-out;
    }

    .empty-state-icon {
        font-size: 5rem;
        margin-bottom: 20px;
        display: block;
    }

    .empty-state h3 {
        margin: 0 0 12px;
        font-size: 1.35rem;
        font-weight: 700;
        color: var(--text-main);
    }

    .empty-state p {
        color: var(--text-muted);
        margin-bottom: 28px;
        font-size: 1rem;
    }

    .btn-create {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 14px 28px;
        background: var(--gradient-primary);
        border: none;
        border-radius: var(--radius-lg);
        color: white;
        font-weight: 600;
        font-size: 1rem;
        text-decoration: none;
        transition: var(--transition-bounce);
        box-shadow: 0 4px 14px rgba(6, 182, 212, 0.25);
    }

    .btn-create:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 24px rgba(6, 182, 212, 0.35);
    }

    @media (max-width: 768px) {
        .category-header {
            flex-direction: column;
            text-align: center;
        }

        .category-stats {
            flex-wrap: wrap;
            justify-content: center;
        }

        .programs-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- Category Header -->
<div class="category-header"
    style="background: linear-gradient(135deg, <?= $category['color'] ?>, <?= $category['color'] ?>dd);">
    <span class="category-icon"><?= $category['icon'] ?></span>
    <div class="category-info">
        <h1><?= htmlspecialchars($category['name']) ?></h1>
        <?php if (!empty($category['description'])): ?>
            <p><?= htmlspecialchars($category['description']) ?></p>
        <?php endif; ?>
    </div>
    <div class="category-stats">
        <span class="stat-badge">📘 <?= count($programs) ?> programas</span>
        <span class="stat-badge">👥 <?= array_sum(array_column($programs, 'assigned_count')) ?> atribuições</span>
    </div>
    <a href="<?= base_url($tenant['slug'] . '/admin/categorias') ?>" class="back-btn">
        ← Voltar
    </a>
</div>

<!-- Programs Grid -->
<?php if (empty($programs)): ?>
    <div class="empty-state">
        <span class="empty-state-icon">📭</span>
        <h3>Nenhum programa nesta categoria</h3>
        <p>Crie um programa de aprendizado para começar.</p>
        <a href="<?= base_url($tenant['slug'] . '/admin/programas/criar') ?>" class="btn-create">
            ➕ Criar Programa
        </a>
    </div>
<?php else: ?>
    <div class="programs-grid"><?php foreach ($programs as $program): ?>
                    <div class="program-card">
                        <div class="program-header">
                            <span class="program-badge"><?= $program['icon'] ?? '📘' ?></span>
                            <div class="program-title">
                                <h3><?= htmlspecialchars($program['name']) ?></h3>
                                <div class="program-meta">
                                    <span class="meta-tag status-<?= $program['status'] ?>">
                                        <?= $program['status'] === 'published' ? '✅ Publicado' : '📝 Rascunho' ?>
                                    </span>
                                    <span class="meta-tag">
                                        👥 <?= $program['assigned_count'] ?> atribuídos
                                    </span>
                                    <span class="meta-tag">
                                        ✅ <?= $program['completed_count'] ?> concluídos
                                    </span>
                                </div>
                            </div>
                        </div>

                        <p class="program-desc">
                            <?= htmlspecialchars($program['description'] ?: 'Sem descrição disponível.') ?>
                        </p>

                        <div class="program-footer">
                            <span class="xp-reward">🌟 <?= $program['xp_reward'] ?? 100 ?> XP</span>
                            <a href="<?= base_url($tenant['slug'] . '/admin/programas/' . $program['id'] . '/editar') ?>"
                                class="btn-edit">
                                ✏️ Editar
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>