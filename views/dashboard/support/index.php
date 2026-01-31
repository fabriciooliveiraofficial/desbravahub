<?php
/**
 * Lista de Chamados de Suporte
 */

use App\Core\App;

$tenant = App::tenant();
$user = App::user();

$statusLabels = [
    'open' => ['label' => 'Aberto', 'class' => 'open', 'icon' => '🟢'],
    'in_progress' => ['label' => 'Em Andamento', 'class' => 'progress', 'icon' => '🟡'],
    'waiting' => ['label' => 'Aguardando', 'class' => 'waiting', 'icon' => '🟠'],
    'resolved' => ['label' => 'Resolvido', 'class' => 'resolved', 'icon' => '🟢'],
    'closed' => ['label' => 'Fechado', 'class' => 'closed', 'icon' => '⚫'],
];

$categoryLabels = [
    'bug' => ['label' => 'Bug', 'icon' => '🐛'],
    'question' => ['label' => 'Dúvida', 'icon' => '❓'],
    'suggestion' => ['label' => 'Sugestão', 'icon' => '💡'],
    'improvement' => ['label' => 'Melhoria', 'icon' => '🚀'],
];
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suporte | <?= htmlspecialchars($tenant['name']) ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
    <style>
        .support-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .btn-new-ticket {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: var(--dark-bg);
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-new-ticket:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0, 217, 255, 0.4);
        }

        .tickets-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .ticket-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px 20px;
            text-decoration: none;
            color: var(--text);
            transition: all 0.3s ease;
        }

        .ticket-card:hover {
            border-color: var(--primary);
            transform: translateX(4px);
        }

        .ticket-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 8px;
        }

        .ticket-subject {
            font-weight: 600;
            font-size: 1.05rem;
        }

        .ticket-meta {
            display: flex;
            gap: 12px;
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .ticket-status {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .ticket-status.open {
            background: rgba(0, 255, 136, 0.2);
            color: var(--secondary);
        }

        .ticket-status.progress {
            background: rgba(255, 193, 7, 0.2);
            color: #ffc107;
        }

        .ticket-status.waiting {
            background: rgba(255, 152, 0, 0.2);
            color: #ff9800;
        }

        .ticket-status.resolved {
            background: rgba(0, 217, 255, 0.2);
            color: var(--primary);
        }

        .ticket-status.closed {
            background: rgba(136, 136, 136, 0.2);
            color: #888;
        }

        .ticket-category {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-muted);
        }

        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 16px;
        }
    </style>
</head>

<body>
    <?php require BASE_PATH . '/views/dashboard/partials/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="support-header">
                <h1 class="page-title">🎧 Suporte</h1>
                <a href="<?= base_url($tenant['slug'] . '/suporte/novo') ?>" class="btn-new-ticket">
                    ➕ Novo Chamado
                </a>
            </div>

            <?php if (empty($tickets)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📭</div>
                    <p>Você ainda não abriu nenhum chamado</p>
                    <p style="margin-top: 8px;">Precisa de ajuda? Abra um chamado!</p>
                </div>
            <?php else: ?>
                <div class="tickets-list">
                    <?php foreach ($tickets as $ticket):
                        $status = $statusLabels[$ticket['status']] ?? $statusLabels['open'];
                        $category = $categoryLabels[$ticket['category']] ?? $categoryLabels['question'];
                        $date = new DateTime($ticket['created_at']);
                        ?>
                        <a href="<?= base_url($tenant['slug'] . '/suporte/' . $ticket['id']) ?>" class="ticket-card">
                            <div class="ticket-header">
                                <span class="ticket-subject"><?= htmlspecialchars($ticket['subject']) ?></span>
                                <span class="ticket-status <?= $status['class'] ?>">
                                    <?= $status['icon'] ?>         <?= $status['label'] ?>
                                </span>
                            </div>
                            <div class="ticket-meta">
                                <span class="ticket-category"><?= $category['icon'] ?>         <?= $category['label'] ?></span>
                                <span>#<?= $ticket['id'] ?></span>
                                <span><?= $date->format('d/m/Y H:i') ?></span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php require BASE_PATH . '/views/dashboard/partials/nav.php'; ?>
</body>

</html>