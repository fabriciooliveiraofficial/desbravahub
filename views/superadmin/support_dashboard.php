<?php
/**
 * Super Admin - Support Dashboard View
 * Lists all support tickets with filters and stats
 */

$statusLabels = [
    'open' => ['label' => 'Aberto', 'class' => 'active', 'icon' => '🟢'],
    'in_progress' => ['label' => 'Em Andamento', 'class' => 'pending', 'icon' => '🟡'],
    'waiting' => ['label' => 'Aguardando', 'class' => 'pending', 'icon' => '🟠'],
    'resolved' => ['label' => 'Resolvido', 'class' => 'active', 'icon' => '✅'],
    'closed' => ['label' => 'Fechado', 'class' => '', 'icon' => '⚫'],
];

$categoryLabels = [
    'bug' => ['label' => 'Bug', 'icon' => '🐛', 'class' => 'bug'],
    'question' => ['label' => 'Dúvida', 'icon' => '❓', 'class' => 'question'],
    'suggestion' => ['label' => 'Sugestão', 'icon' => '💡', 'class' => 'suggestion'],
    'improvement' => ['label' => 'Melhoria', 'icon' => '🚀', 'class' => 'improvement'],
];

$priorityLabels = [
    'low' => ['label' => 'Baixa', 'class' => 'low'],
    'medium' => ['label' => 'Média', 'class' => 'medium'],
    'high' => ['label' => 'Alta', 'class' => 'high'],
];
?>

<style>
    .support-stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 32px;
    }
    .support-stat {
        background: linear-gradient(145deg, rgba(30,41,59,1) 0%, rgba(15,23,42,1) 100%);
        border: 1px solid rgba(255,255,255,0.05);
        border-radius: 16px;
        padding: 24px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    .support-stat::after {
        content: '';
        position: absolute;
        top: 0; right: 0;
        width: 80px; height: 80px;
        background: radial-gradient(circle, var(--sa-primary) 0%, transparent 70%);
        opacity: 0.08;
        transform: translate(20%, -20%);
        border-radius: 50%;
    }
    .support-stat .value {
        font-family: 'Outfit', sans-serif;
        font-size: 2.5rem;
        font-weight: 700;
        line-height: 1;
    }
    .support-stat .value.open { color: #34d399; }
    .support-stat .value.progress { color: #fbbf24; }
    .support-stat .value.waiting { color: #fb923c; }
    .support-stat .value.resolved { color: #a78bfa; }
    .support-stat .label {
        font-size: 0.85rem;
        color: #94a3b8;
        margin-top: 8px;
    }

    .support-filters {
        display: flex;
        gap: 12px;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }
    .support-filters select {
        padding: 10px 16px;
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 10px;
        background: var(--sa-surface);
        color: #cbd5e1;
        font-size: 0.9rem;
        cursor: pointer;
        transition: border-color 0.2s;
    }
    .support-filters select:focus {
        outline: none;
        border-color: var(--sa-primary);
    }

    .priority-badge, .category-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 99px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .priority-badge.high { background: rgba(239, 68, 68, 0.2); color: #f87171; }
    .priority-badge.medium { background: rgba(245, 158, 11, 0.2); color: #fbbf24; }
    .priority-badge.low { background: rgba(148, 163, 184, 0.2); color: #94a3b8; }
    
    .category-badge.bug { background: rgba(239, 68, 68, 0.15); color: #f87171; }
    .category-badge.question { background: rgba(59, 130, 246, 0.15); color: #60a5fa; }
    .category-badge.suggestion { background: rgba(234, 179, 8, 0.15); color: #facc15; }
    .category-badge.improvement { background: rgba(16, 185, 129, 0.15); color: #34d399; }

    .ticket-link {
        color: #e2e8f0;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.2s;
    }
    .ticket-link:hover { color: var(--sa-neon); }
    .ticket-submitter { font-size: 0.8rem; color: #64748b; margin-top: 2px; }
    
    .empty-state {
        text-align: center;
        padding: 80px 24px;
        color: #64748b;
    }
    .empty-state .icon { font-size: 64px; margin-bottom: 16px; }
    
    @media (max-width: 768px) {
        .support-stats { grid-template-columns: repeat(2, 1fr); }
    }
</style>

<!-- Stats -->
<div class="support-stats">
    <div class="support-stat">
        <div class="value open"><?= $stats['open'] ?? 0 ?></div>
        <div class="label">Abertos</div>
    </div>
    <div class="support-stat">
        <div class="value progress"><?= $stats['in_progress'] ?? 0 ?></div>
        <div class="label">Em Andamento</div>
    </div>
    <div class="support-stat">
        <div class="value waiting"><?= $stats['waiting'] ?? 0 ?></div>
        <div class="label">Aguardando</div>
    </div>
    <div class="support-stat">
        <div class="value resolved"><?= $stats['resolved'] ?? 0 ?></div>
        <div class="label">Resolvidos</div>
    </div>
</div>

<!-- Filters -->
<form class="support-filters" method="GET">
    <select name="status" onchange="this.form.submit()">
        <option value="">Todos Status</option>
        <?php foreach ($statusLabels as $key => $s): ?>
            <option value="<?= $key ?>" <?= ($_GET['status'] ?? '') === $key ? 'selected' : '' ?>>
                <?= $s['icon'] ?> <?= $s['label'] ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select name="category" onchange="this.form.submit()">
        <option value="">Todas Categorias</option>
        <?php foreach ($categoryLabels as $key => $c): ?>
            <option value="<?= $key ?>" <?= ($_GET['category'] ?? '') === $key ? 'selected' : '' ?>>
                <?= $c['icon'] ?> <?= $c['label'] ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select name="priority" onchange="this.form.submit()">
        <option value="">Todas Prioridades</option>
        <?php foreach ($priorityLabels as $key => $p): ?>
            <option value="<?= $key ?>" <?= ($_GET['priority'] ?? '') === $key ? 'selected' : '' ?>>
                <?= $p['label'] ?>
            </option>
        <?php endforeach; ?>
    </select>
</form>

<!-- Tickets Table -->
<?php if (empty($tickets)): ?>
    <div class="empty-state">
        <div class="icon">🎉</div>
        <p>Nenhum ticket encontrado com os filtros atuais</p>
    </div>
<?php else: ?>
    <div class="sa-card" style="padding: 0; overflow: hidden;">
        <table class="sa-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Assunto</th>
                    <th>Clube</th>
                    <th>Categoria</th>
                    <th>Prioridade</th>
                    <th>Status</th>
                    <th>Data</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tickets as $t):
                    $status = $statusLabels[$t['status']] ?? $statusLabels['open'];
                    $category = $categoryLabels[$t['category']] ?? $categoryLabels['question'];
                    $priority = $priorityLabels[$t['priority']] ?? $priorityLabels['medium'];
                    $date = new DateTime($t['created_at']);
                    ?>
                    <tr>
                        <td><?= $t['id'] ?></td>
                        <td>
                            <a href="/super-admin/suporte/<?= $t['id'] ?>" class="ticket-link">
                                <?= htmlspecialchars($t['subject']) ?>
                            </a>
                            <div class="ticket-submitter">por <?= htmlspecialchars($t['user_name'] ?? 'Usuário') ?></div>
                        </td>
                        <td><?= htmlspecialchars($t['tenant_name'] ?? '-') ?></td>
                        <td><span class="category-badge <?= $category['class'] ?>"><?= $category['icon'] ?> <?= $category['label'] ?></span></td>
                        <td><span class="priority-badge <?= $priority['class'] ?>"><?= $priority['label'] ?></span></td>
                        <td><span class="sa-badge <?= $status['class'] ?>"><?= $status['icon'] ?> <?= $status['label'] ?></span></td>
                        <td style="white-space: nowrap;"><?= $date->format('d/m H:i') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
