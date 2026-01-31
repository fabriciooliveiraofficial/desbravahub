<?php
/**
 * Detalhes do Chamado de Suporte
 */

use App\Core\App;

$tenant = App::tenant();
$user = App::user();

$statusLabels = [
    'open' => ['label' => 'Aberto', 'class' => 'open', 'icon' => '🟢'],
    'in_progress' => ['label' => 'Em Andamento', 'class' => 'progress', 'icon' => '🟡'],
    'waiting' => ['label' => 'Aguardando Resposta', 'class' => 'waiting', 'icon' => '🟠'],
    'resolved' => ['label' => 'Resolvido', 'class' => 'resolved', 'icon' => '✅'],
    'closed' => ['label' => 'Fechado', 'class' => 'closed', 'icon' => '⚫'],
];

$categoryLabels = [
    'bug' => ['label' => 'Bug', 'icon' => '🐛'],
    'question' => ['label' => 'Dúvida', 'icon' => '❓'],
    'suggestion' => ['label' => 'Sugestão', 'icon' => '💡'],
    'improvement' => ['label' => 'Melhoria', 'icon' => '🚀'],
];

$status = $statusLabels[$ticket['status']] ?? $statusLabels['open'];
$category = $categoryLabels[$ticket['category']] ?? $categoryLabels['question'];
$createdAt = new DateTime($ticket['created_at']);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chamado #<?= $ticket['id'] ?> | <?= htmlspecialchars($tenant['name']) ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
    <style>
        .ticket-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--text-muted);
            text-decoration: none;
            margin-bottom: 20px;
        }

        .back-link:hover {
            color: var(--primary);
        }

        .ticket-header {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
        }

        .ticket-title {
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 12px;
        }

        .ticket-meta {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .ticket-status {
            padding: 6px 14px;
            border-radius: 20px;
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

        .messages-section {
            margin-bottom: 24px;
        }

        .message {
            display: flex;
            gap: 12px;
            margin-bottom: 16px;
        }

        .message.user {
            flex-direction: row;
        }

        .message.developer {
            flex-direction: row-reverse;
        }

        .message-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .message.developer .message-avatar {
            background: var(--secondary);
        }

        .message-content {
            max-width: 70%;
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 14px 18px;
        }

        .message.developer .message-content {
            background: rgba(0, 255, 136, 0.05);
            border-color: rgba(0, 255, 136, 0.2);
        }

        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            font-size: 0.85rem;
        }

        .message-sender {
            font-weight: 600;
            color: var(--text);
        }

        .message-time {
            color: var(--text-muted);
        }

        .message-text {
            line-height: 1.6;
            white-space: pre-wrap;
        }

        .reply-form {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 20px;
        }

        .reply-form textarea {
            width: 100%;
            min-height: 100px;
            padding: 14px;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.02);
            color: var(--text);
            font-family: inherit;
            font-size: 1rem;
            resize: vertical;
            margin-bottom: 12px;
        }

        .reply-form textarea:focus {
            outline: none;
            border-color: var(--primary);
        }

        .reply-actions {
            display: flex;
            gap: 12px;
        }

        .btn-reply {
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            background: var(--primary);
            color: var(--dark-bg);
        }

        .btn-reply:hover {
            box-shadow: 0 4px 16px rgba(0, 217, 255, 0.4);
        }

        .attachments-section {
            margin-top: 16px;
        }

        .attachment {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            background: rgba(0, 217, 255, 0.1);
            border-radius: 8px;
            margin: 4px;
            text-decoration: none;
            color: var(--primary);
            font-size: 0.9rem;
        }

        .attachment:hover {
            background: rgba(0, 217, 255, 0.2);
        }

        .resolved-badge {
            background: rgba(0, 255, 136, 0.1);
            border: 1px solid var(--secondary);
            border-radius: 12px;
            padding: 16px;
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <?php require BASE_PATH . '/views/dashboard/partials/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <a href="<?= base_url($tenant['slug'] . '/suporte') ?>" class="back-link">← Voltar</a>

            <div class="ticket-container">
                <div class="ticket-header">
                    <div class="ticket-title"><?= htmlspecialchars($ticket['subject']) ?></div>
                    <div class="ticket-meta">
                        <span class="ticket-status <?= $status['class'] ?>">
                            <?= $status['icon'] ?> <?= $status['label'] ?>
                        </span>
                        <span><?= $category['icon'] ?> <?= $category['label'] ?></span>
                        <span>#<?= $ticket['id'] ?></span>
                        <span>Aberto em <?= $createdAt->format('d/m/Y H:i') ?></span>
                    </div>
                </div>

                <?php if ($ticket['status'] === 'resolved'): ?>
                    <div class="resolved-badge">
                        ✅ Este chamado foi resolvido. Obrigado pelo feedback!
                    </div>
                <?php endif; ?>

                <div class="messages-section">
                    <?php foreach ($messages as $msg):
                        $msgDate = new DateTime($msg['created_at']);
                        $isUser = $msg['sender_type'] === 'user';
                        ?>
                        <div class="message <?= $isUser ? 'user' : 'developer' ?>">
                            <div class="message-avatar">
                                <?= $isUser ? '👤' : '🛠️' ?>
                            </div>
                            <div class="message-content">
                                <div class="message-header">
                                    <span class="message-sender">
                                        <?= htmlspecialchars($msg['sender_name'] ?? ($isUser ? 'Você' : 'Suporte')) ?>
                                    </span>
                                    <span class="message-time"><?= $msgDate->format('d/m H:i') ?></span>
                                </div>
                                <div class="message-text"><?= nl2br(htmlspecialchars($msg['message'])) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if (!empty($attachments)): ?>
                    <div class="attachments-section">
                        <strong>Anexos:</strong><br>
                        <?php foreach ($attachments as $att): ?>
                            <a href="<?= base_url('storage/' . $att['path']) ?>" target="_blank" class="attachment">
                                📎 <?= htmlspecialchars($att['filename']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($ticket['status'] !== 'closed'): ?>
                    <form class="reply-form" id="replyForm">
                        <textarea name="message" placeholder="Digite sua resposta..." required></textarea>
                        <div class="reply-actions">
                            <button type="submit" class="btn-reply">💬 Enviar Resposta</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php require BASE_PATH . '/views/dashboard/partials/nav.php'; ?>

    <script>
        const form = document.getElementById('replyForm');
        if (form) {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();

                const formData = new FormData(form);
                const btn = form.querySelector('button');
                btn.disabled = true;
                btn.textContent = 'Enviando...';

                try {
                    const response = await fetch('<?= base_url($tenant['slug'] . '/suporte/' . $ticket['id'] . '/responder') ?>', {
                        method: 'POST',
                        body: formData
                    });

                    const data = await response.json();

                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.error || 'Erro ao enviar');
                        btn.disabled = false;
                        btn.textContent = '💬 Enviar Resposta';
                    }
                } catch (err) {
                    alert('Erro de conexão');
                    btn.disabled = false;
                    btn.textContent = '💬 Enviar Resposta';
                }
            });
        }
    </script>
</body>

</html>