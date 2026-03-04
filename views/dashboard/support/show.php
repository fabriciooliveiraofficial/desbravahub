<?php
/**
 * Detalhes do Chamado de Suporte
 */

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

<style>
    .ticket-wrapper {
        padding: 20px;
        max-width: 900px;
        margin: 0 auto;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--accent-cyan);
        text-decoration: none;
        margin-bottom: 24px;
        font-family: 'JetBrains Mono', monospace;
        font-weight: 700;
        font-size: 0.9rem;
        transition: all 0.3s;
    }

    .back-link:hover {
        transform: translateX(-4px);
        text-shadow: 0 0 10px var(--accent-cyan);
    }

    .ticket-header {
        background: var(--hud-glass-panel);
        border: 1px solid var(--hud-glass-border);
        border-radius: 20px;
        padding: 24px;
        margin-bottom: 24px;
        backdrop-filter: blur(15px);
    }

    .ticket-title {
        font-size: 1.5rem;
        font-weight: 800;
        margin-bottom: 12px;
        color: var(--text);
    }

    .ticket-meta {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.85rem;
        color: var(--text-muted);
    }

    .ticket-status {
        padding: 4px 12px;
        border-radius: 20px;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.7rem;
    }

    .ticket-status.open { background: rgba(0, 255, 136, 0.1); color: var(--accent-green); border: 1px solid rgba(0, 255, 136, 0.2); }
    .ticket-status.progress { background: rgba(255, 193, 7, 0.1); color: #ffc107; border: 1px solid rgba(255, 193, 7, 0.2); }
    .ticket-status.waiting { background: rgba(255, 152, 0, 0.1); color: #ff9800; border: 1px solid rgba(255, 152, 0, 0.2); }
    .ticket-status.resolved { background: rgba(0, 217, 255, 0.1); color: var(--accent-cyan); border: 1px solid rgba(0, 217, 255, 0.2); }
    .ticket-status.closed { background: rgba(136, 136, 136, 0.1); color: #888; border: 1px solid rgba(136, 136, 136, 0.2); }

    .messages-section {
        margin-bottom: 30px;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .message {
        display: flex;
        gap: 16px;
        max-width: 85%;
    }

    .message.user {
        align-self: flex-start;
    }

    .message.developer {
        align-self: flex-end;
        flex-direction: row-reverse;
    }

    .message-avatar {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: var(--hud-glass-panel);
        border: 1px solid var(--hud-glass-border);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    }

    .message.developer .message-avatar {
        background: rgba(0, 255, 136, 0.1);
        border-color: var(--accent-green);
    }

    .message-content {
        background: var(--hud-glass-panel);
        border: 1px solid var(--hud-glass-border);
        border-radius: 16px;
        padding: 16px 20px;
        backdrop-filter: blur(10px);
        position: relative;
    }

    .message.developer .message-content {
        background: rgba(0, 255, 136, 0.03);
        border-color: rgba(0, 255, 136, 0.2);
    }

    .message-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.75rem;
    }

    .message-sender {
        font-weight: 800;
        color: var(--accent-cyan);
    }

    .message.developer .message-sender {
        color: var(--accent-green);
    }

    .message-time {
        color: var(--text-muted);
        opacity: 0.6;
    }

    .message-text {
        line-height: 1.6;
        white-space: pre-wrap;
        color: var(--text);
        font-size: 0.95rem;
    }

    .reply-form {
        background: var(--hud-glass-panel);
        border: 1px solid var(--hud-glass-border);
        border-radius: 20px;
        padding: 24px;
        backdrop-filter: blur(15px);
    }

    .reply-form textarea {
        width: 100%;
        min-height: 120px;
        padding: 16px;
        border: 1px solid var(--hud-glass-border);
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.02);
        color: var(--text);
        font-family: inherit;
        font-size: 1rem;
        resize: vertical;
        margin-bottom: 16px;
        transition: all 0.3s;
    }

    .reply-form textarea:focus {
        outline: none;
        border-color: var(--accent-cyan);
        background: rgba(0, 217, 255, 0.05);
    }

    .btn-reply {
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }

    .attachments-section {
        margin-top: 20px;
        padding: 16px;
        background: rgba(255,255,255,0.02);
        border-radius: 12px;
        border: 1px solid var(--hud-glass-border);
    }

    .attachment {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        background: rgba(0, 217, 255, 0.1);
        border: 1px solid rgba(0, 217, 255, 0.2);
        border-radius: 10px;
        margin: 4px;
        text-decoration: none;
        color: var(--accent-cyan);
        font-size: 0.85rem;
        font-weight: 600;
        transition: all 0.3s;
    }

    .attachment:hover {
        background: rgba(0, 217, 255, 0.2);
        transform: translateY(-1px);
    }

    .resolved-badge {
        background: rgba(0, 255, 136, 0.05);
        border: 1px solid var(--accent-green);
        border-radius: 16px;
        padding: 20px;
        text-align: center;
        margin-bottom: 24px;
        color: var(--accent-green);
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }
</style>

<div class="ticket-wrapper">
    <a href="<?= base_url($tenant['slug'] . '/suporte') ?>" class="back-link">
        <span class="material-icons-round" style="font-size: 1.2rem; vertical-align: middle;">arrow_back</span>
        VOLTAR PARA CHAMADOS
    </a>

    <div class="ticket-header">
        <div class="ticket-title"><?= htmlspecialchars($ticket['subject']) ?></div>
        <div class="ticket-meta">
            <span class="ticket-status <?= $status['class'] ?>">
                <?= $status['label'] ?>
            </span>
            <span>
                <span class="material-icons-round" style="font-size: 14px; vertical-align: middle;">category</span>
                <?= $category['label'] ?>
            </span>
            <span>
                <span class="material-icons-round" style="font-size: 14px; vertical-align: middle;">tag</span>
                #<?= str_pad($ticket['id'], 5, '0', STR_PAD_LEFT) ?>
            </span>
            <span>
                <span class="material-icons-round" style="font-size: 14px; vertical-align: middle;">calendar_today</span>
                <?= $createdAt->format('d/m/Y H:i') ?>
            </span>
        </div>
    </div>

    <?php if ($ticket['status'] === 'resolved'): ?>
        <div class="resolved-badge">
            <span class="material-icons-round">check_circle</span>
            Este chamado foi resolvido. Obrigado pelo feedback!
        </div>
    <?php endif; ?>

    <div class="messages-section">
        <?php foreach ($messages as $msg):
            $msgDate = new DateTime($msg['created_at']);
            $isUser = $msg['sender_type'] === 'user';
            ?>
            <div class="message <?= $isUser ? 'user' : 'developer' ?>">
                <div class="message-avatar">
                    <span class="material-icons-round"><?= $isUser ? 'person' : 'engineering' ?></span>
                </div>
                <div class="message-content">
                    <div class="message-header">
                        <span class="message-sender">
                            <?= htmlspecialchars($msg['sender_name'] ?? ($isUser ? 'VOCÊ' : 'SUPORTE')) ?>
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
            <div style="font-size: 0.8rem; font-weight: 800; color: var(--text-muted); margin-bottom: 12px; text-transform: uppercase;">Anexos:</div>
            <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                <?php foreach ($attachments as $att): ?>
                    <a href="<?= base_url('storage/' . $att['path']) ?>" target="_blank" class="attachment">
                        <span class="material-icons-round" style="font-size: 16px;">attach_file</span>
                        <?= htmlspecialchars($att['filename']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <div style="margin-bottom: 24px;"></div>
    <?php endif; ?>

    <?php if ($ticket['status'] !== 'closed'): ?>
        <form class="reply-form" id="replyForm">
            <textarea name="message" class="hud-input" placeholder="Digite sua resposta..." required></textarea>
            <div class="reply-actions">
                <button type="submit" class="hud-btn primary btn-reply">
                    <span class="material-icons-round">send</span>
                    Enviar Resposta
                </button>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
    const form = document.getElementById('replyForm');
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(form);
            const btn = form.querySelector('button');
            const originalText = btn.innerHTML;
            
            btn.disabled = true;
            btn.innerHTML = '<span class="material-icons-round spin" style="font-size: 1.2rem; vertical-align: middle;">sync</span> Enviando...';

            try {
                const response = await fetch('<?= base_url($tenant['slug'] . '/suporte/' . $ticket['id'] . '/responder') ?>', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    location.reload();
                } else {
                    if (typeof toast !== 'undefined') toast.error('Erro', data.error || 'Erro ao enviar');
                    else alert(data.error || 'Erro ao enviar');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            } catch (err) {
                if (typeof toast !== 'undefined') toast.error('Erro', 'Erro de conexão');
                else alert('Erro de conexão');
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        });
    }
</script>