<?php
/**
 * Super Admin - Ticket Detail View
 * Shows a single ticket with messages and reply form
 */

$statusLabels = [
    'open' => ['label' => 'Aberto', 'class' => 'active'],
    'in_progress' => ['label' => 'Em Andamento', 'class' => 'pending'],
    'waiting' => ['label' => 'Aguardando', 'class' => 'pending'],
    'resolved' => ['label' => 'Resolvido', 'class' => 'active'],
    'closed' => ['label' => 'Fechado', 'class' => ''],
];

$categoryLabels = [
    'bug' => ['label' => 'Bug', 'icon' => '🐛'],
    'question' => ['label' => 'Dúvida', 'icon' => '❓'],
    'suggestion' => ['label' => 'Sugestão', 'icon' => '💡'],
    'improvement' => ['label' => 'Melhoria', 'icon' => '🚀'],
];

$ticketStatus = $statusLabels[$ticket['status']] ?? $statusLabels['open'];
$ticketCategory = $categoryLabels[$ticket['category']] ?? $categoryLabels['question'];
$createdAt = new DateTime($ticket['created_at']);
?>

<style>
    .ticket-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #94a3b8;
        text-decoration: none;
        font-size: 0.9rem;
        margin-bottom: 24px;
        transition: color 0.2s;
    }
    .ticket-back:hover { color: var(--sa-neon); }

    .ticket-header-card {
        background: var(--sa-surface);
        border: 1px solid rgba(255,255,255,0.05);
        border-radius: 16px;
        padding: 28px;
        margin-bottom: 28px;
    }
    .ticket-subject {
        font-family: 'Outfit', sans-serif;
        font-size: 1.4rem;
        font-weight: 600;
        color: white;
        margin-bottom: 20px;
    }
    .ticket-meta-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 16px;
    }
    .ticket-meta-item .meta-label {
        color: #64748b;
        font-size: 0.8rem;
        font-weight: 500;
        margin-bottom: 4px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .ticket-meta-item .meta-value {
        color: #cbd5e1;
        font-size: 0.95rem;
    }

    /* Messages */
    .messages-timeline {
        margin-bottom: 28px;
    }
    .msg {
        display: flex;
        gap: 14px;
        margin-bottom: 20px;
    }
    .msg.from-user { flex-direction: row; }
    .msg.from-dev { flex-direction: row-reverse; }

    .msg-avatar {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: grid;
        place-items: center;
        font-size: 18px;
        flex-shrink: 0;
    }
    .msg.from-user .msg-avatar { background: rgba(139, 92, 246, 0.3); }
    .msg.from-dev .msg-avatar { background: rgba(16, 185, 129, 0.3); }
    .msg.internal .msg-avatar { background: rgba(239, 68, 68, 0.3); }

    .msg-bubble {
        max-width: 70%;
        background: var(--sa-surface);
        border: 1px solid rgba(255,255,255,0.05);
        border-radius: 16px;
        padding: 16px 20px;
    }
    .msg.from-dev .msg-bubble {
        background: rgba(16, 185, 129, 0.06);
        border-color: rgba(16, 185, 129, 0.15);
    }
    .msg.internal .msg-bubble {
        background: rgba(239, 68, 68, 0.06);
        border-color: rgba(239, 68, 68, 0.15);
    }
    .msg-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
        font-size: 0.85rem;
    }
    .msg-sender {
        font-weight: 600;
        color: #e2e8f0;
    }
    .msg-time {
        color: #64748b;
        font-size: 0.8rem;
    }
    .msg-body {
        color: #cbd5e1;
        line-height: 1.7;
        white-space: pre-wrap;
    }
    .internal-tag {
        font-size: 0.65rem;
        background: rgba(239, 68, 68, 0.2);
        color: #f87171;
        padding: 2px 8px;
        border-radius: 4px;
        margin-left: 8px;
        font-weight: 600;
    }

    /* Reply Form */
    .reply-card {
        background: var(--sa-surface);
        border: 1px solid rgba(255,255,255,0.05);
        border-radius: 16px;
        padding: 28px;
    }
    .reply-card h3 {
        font-family: 'Outfit', sans-serif;
        font-size: 1.1rem;
        font-weight: 600;
        color: white;
        margin-bottom: 16px;
    }
    .reply-card textarea {
        width: 100%;
        min-height: 120px;
        padding: 14px;
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 12px;
        background: rgba(0,0,0,0.2);
        color: #e2e8f0;
        font-family: inherit;
        font-size: 0.95rem;
        resize: vertical;
        margin-bottom: 16px;
        transition: border-color 0.2s;
    }
    .reply-card textarea:focus {
        outline: none;
        border-color: var(--sa-primary);
    }
    .reply-options {
        display: flex;
        gap: 20px;
        align-items: center;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }
    .reply-options label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.9rem;
        color: #94a3b8;
        cursor: pointer;
    }
    .reply-options select {
        padding: 8px 14px;
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 8px;
        background: rgba(0,0,0,0.2);
        color: #cbd5e1;
        font-size: 0.9rem;
    }
    .btn-send {
        padding: 14px 32px;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.3s;
        background: var(--sa-primary);
        color: white;
    }
    .btn-send:hover {
        box-shadow: 0 4px 20px rgba(139, 92, 246, 0.4);
        transform: translateY(-1px);
    }
    .btn-send:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
    }
    .quick-replies {
        display: flex;
        gap: 8px;
        margin-top: 16px;
        flex-wrap: wrap;
    }
    .quick-btn {
        padding: 8px 14px;
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 8px;
        background: transparent;
        color: #94a3b8;
        cursor: pointer;
        font-size: 0.8rem;
        transition: all 0.2s;
    }
    .quick-btn:hover {
        border-color: var(--sa-primary);
        color: var(--sa-neon);
    }
</style>

<a href="/super-admin/suporte" class="ticket-back">
    <span class="material-symbols-rounded" style="font-size: 18px;">arrow_back</span>
    Voltar à Central de Suporte
</a>

<!-- Ticket Header -->
<div class="ticket-header-card">
    <div class="ticket-subject"><?= htmlspecialchars($ticket['subject']) ?></div>
    <div class="ticket-meta-grid">
        <div class="ticket-meta-item">
            <div class="meta-label">Status</div>
            <div class="meta-value"><span class="sa-badge <?= $ticketStatus['class'] ?>"><?= $ticketStatus['label'] ?></span></div>
        </div>
        <div class="ticket-meta-item">
            <div class="meta-label">Categoria</div>
            <div class="meta-value"><?= $ticketCategory['icon'] ?> <?= $ticketCategory['label'] ?></div>
        </div>
        <div class="ticket-meta-item">
            <div class="meta-label">Prioridade</div>
            <div class="meta-value"><?= ucfirst($ticket['priority']) ?></div>
        </div>
        <div class="ticket-meta-item">
            <div class="meta-label">Clube</div>
            <div class="meta-value"><?= htmlspecialchars($ticket['tenant_name'] ?? '-') ?></div>
        </div>
        <div class="ticket-meta-item">
            <div class="meta-label">Usuário</div>
            <div class="meta-value"><?= htmlspecialchars($ticket['user_name'] ?? '-') ?></div>
        </div>
        <div class="ticket-meta-item">
            <div class="meta-label">Email</div>
            <div class="meta-value"><?= htmlspecialchars($ticket['user_email'] ?? '-') ?></div>
        </div>
        <div class="ticket-meta-item">
            <div class="meta-label">Aberto em</div>
            <div class="meta-value"><?= $createdAt->format('d/m/Y H:i') ?></div>
        </div>
        <?php if (!empty($ticket['related_module'])): ?>
        <div class="ticket-meta-item">
            <div class="meta-label">Módulo</div>
            <div class="meta-value"><?= htmlspecialchars($ticket['related_module']) ?></div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Messages Timeline -->
<div class="messages-timeline">
    <?php foreach ($messages as $msg):
        $msgDate = new DateTime($msg['created_at']);
        $isUser = $msg['sender_type'] === 'user';
        $isInternal = !empty($msg['is_internal']);
    ?>
        <div class="msg <?= $isUser ? 'from-user' : 'from-dev' ?> <?= $isInternal ? 'internal' : '' ?>">
            <div class="msg-avatar">
                <?= $isUser ? '👤' : '🛡️' ?>
            </div>
            <div class="msg-bubble">
                <div class="msg-header">
                    <span class="msg-sender">
                        <?= htmlspecialchars($msg['sender_name'] ?? ($isUser ? 'Usuário' : 'Admin')) ?>
                        <?php if ($isInternal): ?>
                            <span class="internal-tag">Interna</span>
                        <?php endif; ?>
                    </span>
                    <span class="msg-time"><?= $msgDate->format('d/m H:i') ?></span>
                </div>
                <div class="msg-body"><?= nl2br(htmlspecialchars($msg['message'])) ?></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Reply Form -->
<div class="reply-card">
    <h3>💬 Responder ao Ticket</h3>
    <form id="replyForm">
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

        <button type="submit" class="btn-send">💬 Enviar Resposta</button>

        <div class="quick-replies">
            <button type="button" class="quick-btn" onclick="quickReply('Recebemos seu chamado e já estamos analisando.')">📩 Confirmar Recebimento</button>
            <button type="button" class="quick-btn" onclick="quickReply('Precisamos de mais informações para prosseguir. Poderia detalhar melhor?')">❓ Pedir Info</button>
            <button type="button" class="quick-btn" onclick="quickReply('O problema foi corrigido na última atualização. Por favor, teste novamente.')">✅ Corrigido</button>
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
        const btn = form.querySelector('.btn-send');
        btn.disabled = true;
        btn.textContent = 'Enviando...';

        try {
            const response = await fetch('/super-admin/suporte/<?= $ticket['id'] ?>/responder', {
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
