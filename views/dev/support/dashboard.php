<?php
/**
 * Developer Support Dashboard - List All Tickets
 */

session_start();
$devName = $_SESSION['dev_name'] ?? 'Dev';

$statusLabels = [
    'open' => ['label' => 'Aberto', 'class' => 'open', 'icon' => '🟢'],
    'in_progress' => ['label' => 'Em Andamento', 'class' => 'progress', 'icon' => '🟡'],
    'waiting' => ['label' => 'Aguardando', 'class' => 'waiting', 'icon' => '🟠'],
    'resolved' => ['label' => 'Resolvido', 'class' => 'resolved', 'icon' => '✅'],
    'closed' => ['label' => 'Fechado', 'class' => 'closed', 'icon' => '⚫'],
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
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suporte Dev | DesbravaHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #00d9ff;
            --secondary: #00ff88;
            --dark-bg: #0a0a1a;
            --card-bg: rgba(255, 255, 255, 0.03);
            --border: rgba(255, 255, 255, 0.08);
            --text: #e0e0e0;
            --text-muted: #888;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--dark-bg);
            color: var(--text);
            min-height: 100vh;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 32px;
            background: var(--card-bg);
            border-bottom: 1px solid var(--border);
        }

        .logo {
            font-size: 1.3rem;
            font-weight: 700;
            background: linear-gradient(90deg, var(--secondary), var(--primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .logout-btn {
            padding: 8px 16px;
            background: rgba(255, 107, 107, 0.2);
            color: #ff6b6b;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 32px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
        }

        .stat-value.open {
            color: var(--secondary);
        }

        .stat-value.progress {
            color: #ffc107;
        }

        .stat-value.waiting {
            color: #ff9800;
        }

        .stat-value.resolved {
            color: var(--primary);
        }

        .stat-label {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-top: 4px;
        }

        .filters {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .filters select {
            padding: 10px 16px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--card-bg);
            color: var(--text);
            font-size: 0.9rem;
        }

        .tickets-table {
            width: 100%;
            border-collapse: collapse;
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
        }

        .tickets-table th {
            text-align: left;
            padding: 14px 16px;
            background: rgba(0, 0, 0, 0.2);
            font-weight: 600;
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .tickets-table td {
            padding: 14px 16px;
            border-top: 1px solid var(--border);
        }

        .tickets-table tr:hover {
            background: rgba(0, 217, 255, 0.03);
        }

        .ticket-link {
            color: var(--text);
            text-decoration: none;
            font-weight: 500;
        }

        .ticket-link:hover {
            color: var(--primary);
        }

        .status-badge,
        .priority-badge,
        .category-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-badge.open {
            background: rgba(0, 255, 136, 0.2);
            color: var(--secondary);
        }

        .status-badge.progress {
            background: rgba(255, 193, 7, 0.2);
            color: #ffc107;
        }

        .status-badge.waiting {
            background: rgba(255, 152, 0, 0.2);
            color: #ff9800;
        }

        .status-badge.resolved {
            background: rgba(0, 217, 255, 0.2);
            color: var(--primary);
        }

        .status-badge.closed {
            background: rgba(136, 136, 136, 0.2);
            color: #888;
        }

        .priority-badge.high {
            background: rgba(255, 107, 107, 0.2);
            color: #ff6b6b;
        }

        .priority-badge.medium {
            background: rgba(255, 193, 7, 0.2);
            color: #ffc107;
        }

        .priority-badge.low {
            background: rgba(136, 136, 136, 0.2);
            color: #888;
        }

        .category-badge.bug {
            background: rgba(255, 107, 107, 0.2);
            color: #ff6b6b;
        }

        .category-badge.question {
            background: rgba(0, 217, 255, 0.2);
            color: var(--primary);
        }

        .category-badge.suggestion {
            background: rgba(255, 215, 0, 0.2);
            color: #ffd700;
        }

        .category-badge.improvement {
            background: rgba(0, 255, 136, 0.2);
            color: var(--secondary);
        }

        .tenant-name {
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .tickets-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>

<body>
    <header>
        <div class="logo">🛠️ Dev Portal - Suporte</div>
        <div class="user-info">
            <span>👤 <?= htmlspecialchars($devName) ?></span>
            <a href="<?= base_url('dev/logout') ?>" class="logout-btn">Sair</a>
        </div>
    </header>

    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value open"><?= $stats['open'] ?></div>
                <div class="stat-label">Abertos</div>
            </div>
            <div class="stat-card">
                <div class="stat-value progress"><?= $stats['in_progress'] ?></div>
                <div class="stat-label">Em Andamento</div>
            </div>
            <div class="stat-card">
                <div class="stat-value waiting"><?= $stats['waiting'] ?></div>
                <div class="stat-label">Aguardando</div>
            </div>
            <div class="stat-card">
                <div class="stat-value resolved"><?= $stats['resolved'] ?></div>
                <div class="stat-label">Resolvidos</div>
            </div>
        </div>

        <form class="filters" method="GET">
            <select name="status" onchange="this.form.submit()">
                <option value="">Todos Status</option>
                <?php foreach ($statusLabels as $key => $s): ?>
                    <option value="<?= $key ?>" <?= ($_GET['status'] ?? '') === $key ? 'selected' : '' ?>>
                        <?= $s['icon'] ?>     <?= $s['label'] ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="category" onchange="this.form.submit()">
                <option value="">Todas Categorias</option>
                <?php foreach ($categoryLabels as $key => $c): ?>
                    <option value="<?= $key ?>" <?= ($_GET['category'] ?? '') === $key ? 'selected' : '' ?>>
                        <?= $c['icon'] ?>     <?= $c['label'] ?>
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

        <?php if (empty($tickets)): ?>
            <div style="text-align: center; padding: 60px; color: var(--text-muted);">
                <div style="font-size: 48px; margin-bottom: 16px;">🎉</div>
                <p>Nenhum ticket encontrado com os filtros atuais</p>
            </div>
        <?php else: ?>
            <table class="tickets-table">
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
                                <a href="<?= base_url('dev/suporte/' . $t['id']) ?>" class="ticket-link">
                                    <?= htmlspecialchars($t['subject']) ?>
                                </a>
                                <div class="tenant-name">por <?= htmlspecialchars($t['user_name'] ?? 'Usuário') ?></div>
                            </td>
                            <td><?= htmlspecialchars($t['tenant_name'] ?? '-') ?></td>
                            <td><span class="category-badge <?= $category['class'] ?>"><?= $category['icon'] ?>
                                    <?= $category['label'] ?></span></td>
                            <td><span class="priority-badge <?= $priority['class'] ?>"><?= $priority['label'] ?></span></td>
                            <td><span class="status-badge <?= $status['class'] ?>"><?= $status['icon'] ?>
                                    <?= $status['label'] ?></span></td>
                            <td><?= $date->format('d/m H:i') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>

</html>