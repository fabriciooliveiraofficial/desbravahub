<?php
/**
 * Developer - Ticket Detail View
 */

session_start();
$devName = $_SESSION['dev_name'] ?? 'Dev';

$statusLabels = [
    'open' => ['label' => 'Aberto', 'class' => 'open'],
    'in_progress' => ['label' => 'Em Andamento', 'class' => 'progress'],
    'waiting' => ['label' => 'Aguardando', 'class' => 'waiting'],
    'resolved' => ['label' => 'Resolvido', 'class' => 'resolved'],
    'closed' => ['label' => 'Fechado', 'class' => 'closed'],
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
    <title>Ticket #<?= $ticket['id'] ?> | Dev Portal</title>
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

        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 32px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--text-muted);
            text-decoration: none;
            margin-bottom: 24px;
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
            margin-bottom: 16px;
        }

        .ticket-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 16px;
        }

        .meta-item {
            font-size: 0.9rem;
        }

        .meta-label {
            color: var(--text-muted);
            margin-bottom: 4px;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 0.8rem;
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

        .message.internal .message-avatar {
            background: rgba(255, 107, 107, 0.5);
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

        .message.internal .message-content {
            background: rgba(255, 107, 107, 0.05);
            border-color: rgba(255, 107, 107, 0.2);
        }

        .internal-badge {
            font-size: 0.7rem;
            background: rgba(255, 107, 107, 0.2);
            color: #ff6b6b;
            padding: 2px 8px;
            border-radius: 4px;
            margin-left: 8px;
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
            padding: 24px;
        }

        .reply-form textarea {
            width: 100%;
            min-height: 120px;
            padding: 14px;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.02);
            color: var(--text);
            font-family: inherit;
            font-size: 1rem;
            resize: vertical;
            margin-bottom: 16px;
        }

        .reply-form textarea:focus {
            outline: none;
            border-color: var(--secondary);
        }

        .reply-options {
            display: flex;
            gap: 16px;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }

        .reply-options label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            cursor: pointer;
        }

        .reply-options select {
            padding: 10px 14px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--card-bg);
            color: var(--text);
        }

        .btn-reply {
            padding: 14px 28px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            background: linear-gradient(135deg, var(--secondary), var(--primary));
            color: var(--dark-bg);
        }

        .btn-reply:hover {
            box-shadow: 0 4px 20px rgba(0, 255, 136, 0.4);
        }

        .quick-actions {
            display: flex;
            gap: 8px;
            margin-top: 16px;
        }

        .quick-btn {
            padding: 8px 16px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: transparent;
            color: var(--text);
            cursor: pointer;
            font-size: 0.85rem;
        }

        .quick-btn:hover {
            border-color: var(--primary);
        }
    </style>
</head>

<body>
    <header>
        <div class="logo">🛠️ Dev Portal - Ticket #<?= $ticket['id'] ?></div>
        <span>👤 <?= htmlspecialchars($devName) ?></span>
    </header>

    <div class="container">
        <a href="<?= base_url('dev/suporte') ?>" class="back-link">← Voltar</a>

        <div class="ticket-header">
            <div class="ticket-title"><?= htmlspecialchars($ticket['subject']) ?></div>
            <div class="ticket-meta">
                <div class="meta-item">
                    <div class="meta-label">Status</div>
                    <span class="status-badge <?= $status['class'] ?>"><?= $status['label'] ?></span>
                </div>
                <div class="meta-item">
                    <div class="meta-label">Categoria</div>
                    <span><?= $category['icon'] ?> <?= $category['label'] ?></span>
                </div>
                <div class="meta-item">
                    <div class="meta-label">Prioridade</div>
                    <span><?= ucfirst($ticket['priority']) ?></span>
                </div>
                <div class="meta-item">
                    <div class="meta-label">Clube</div>
                    <span><?= htmlspecialchars($ticket['tenant_name'] ?? '-') ?></span>
                </div>
                <div class="meta-item">
                    <div class="meta-label">Usuário</div>
                    <span><?= htmlspecialchars($ticket['user_name'] ?? '-') ?></span>
                </div>
                <div class="meta-item">
                    <div class="meta-label">Email</div>
                    <span><?= htmlspecialchars($ticket['user_email'] ?? '-') ?></span>
                </div>
                <div class="meta-item">
                    <div class="meta-label">Criado em</div>
                    <span><?= $createdAt->format('d/m/Y H:i') ?></span>
                </div>
                <?php if ($ticket['related_module']): ?>
                    <div class="meta-item">
                        <div class="meta-label">Módulo</div>
                        <span><?= htmlspecialchars($ticket['related_module']) ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="messages-section">
            <?php foreach ($messages as $msg):
                $msgDate = new DateTime($msg['created_at']);
                $isUser = $msg['sender_type'] === 'user';
                $isInternal = $msg['is_internal'];
                ?>
                <div class="message <?= $isUser ? 'user' : 'developer' ?> <?= $isInternal ? 'internal' : '' ?>">
                    <div class="message-avatar">
                        <?= $isUser ? '👤' : '🛠️' ?>
                    </div>
                    <div class="message-content">
                        <div class="message-header">
                            <span class="message-sender">
                                <?= htmlspecialchars($msg['sender_name'] ?? ($isUser ? 'Usuário' : 'Dev')) ?>
                                <?php if ($isInternal): ?>
                                    <span class="internal-badge">Interna</span>
                                <?php endif; ?>
                            </span>
                            <span class="message-time"><?= $msgDate->format('d/m H:i') ?></span>
                        </div>
                        <div class="message-text"><?= nl2br(htmlspecialchars($msg['message'])) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <form class="reply-form" id="replyForm">
            <textarea name="message" placeholder="Escreva sua resposta..." required></textarea>

            <div class="reply-options">
                <label>
                    <input type="checkbox" name="is_internal" value="1">
                    Nota interna (não visível ao usuário)
                </label>

                <label>
                    Alterar status:
                    <select name="status">
                        <option value="">Manter atual</option>
                        <option value="in_progress">Em Andamento</option>
                        <option value="waiting">Aguardando Usuário</option>
                        <option value="resolved">Resolvido</option>
                        <option value="closed">Fechado</option>
                    </select>
                </label>
            </div>

            <button type="submit" class="btn-reply">💬 Enviar Resposta</button>

            <div class="quick-actions">
                <button type="button" class="quick-btn"
                    onclick="quickReply('Recebemos seu chamado e já estamos analisando.')">📩 Confirmar
                    Recebimento</button>
                <button type="button" class="quick-btn"
                    onclick="quickReply('Precisamos de mais informações para prosseguir. Poderia detalhar melhor?')">❓
                    Pedir Info</button>
                <button type="button" class="quick-btn"
                    onclick="quickReply('O problema foi corrigido na última atualização. Por favor, teste novamente.')">✅
                    Corrigido</button>
            </div>
        </form>
    </div>

    <script>
        const form = document.getElementById('replyForm');
        const textarea = form.querySelector('textarea');

        function quickReply(text) {
            textarea.value = text;
            textarea.focus();
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(form);
            const btn = form.querySelector('.btn-reply');
            btn.disabled = true;
            btn.textContent = 'Enviando...';

            try {
                const response = await fetch('<?= base_url('dev/suporte/' . $ticket['id'] . '/responder') ?>', {
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
    </script>
</body>

</html>