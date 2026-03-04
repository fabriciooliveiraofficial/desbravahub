<?php
/**
 * Lista de Chamados de Suporte
 */

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

<style>
    .support-wrapper {
        padding: 20px;
        max-width: 900px;
        margin: 0 auto;
    }

    .support-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        margin-bottom: 30px;
        background: var(--hud-glass-panel);
        border: 1px solid var(--hud-glass-border);
        padding: 20px 24px;
        border-radius: 20px;
        backdrop-filter: blur(10px);
        flex-wrap: wrap;
    }

    .support-title {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--text);
        text-shadow: 0 0 10px rgba(0, 217, 255, 0.3);
    }

    .btn-minimalist {
        width: 48px;
        height: 48px;
        padding: 0 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px !important;
    }

    @media (max-width: 480px) {
        .support-header {
            padding: 16px;
            gap: 12px;
        }

        .support-title {
            font-size: 1.25rem;
        }

        .support-wrapper {
            padding: 12px;
        }
    }

    .tickets-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .ticket-card {
        background: var(--hud-glass-panel);
        border: 1px solid var(--hud-glass-border);
        border-radius: 16px;
        padding: 20px;
        text-decoration: none;
        color: var(--text);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        backdrop-filter: blur(10px);
    }

    .ticket-card:hover {
        border-color: var(--accent-cyan);
        transform: scale(1.02);
        background: rgba(0, 217, 255, 0.05);
    }

    .ticket-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }

    .ticket-subject {
        font-weight: 700;
        font-size: 1.1rem;
        color: var(--text);
    }

    .ticket-meta {
        display: flex;
        gap: 16px;
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.8rem;
        color: var(--text-muted);
    }

    .ticket-status {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .ticket-status.open { background: rgba(0, 255, 136, 0.1); color: var(--accent-green); border: 1px solid rgba(0, 255, 136, 0.2); }
    .ticket-status.progress { background: rgba(255, 193, 7, 0.1); color: #ffc107; border: 1px solid rgba(255, 193, 7, 0.2); }
    .ticket-status.waiting { background: rgba(255, 152, 0, 0.1); color: #ff9800; border: 1px solid rgba(255, 152, 0, 0.2); }
    .ticket-status.resolved { background: rgba(0, 217, 255, 0.1); color: var(--accent-cyan); border: 1px solid rgba(0, 217, 255, 0.2); }
    .ticket-status.closed { background: rgba(136, 136, 136, 0.1); color: #888; border: 1px solid rgba(136, 136, 136, 0.2); }

    .empty-state {
        text-align: center;
        padding: 80px 40px;
        background: var(--hud-glass-panel);
        border: 1px solid var(--hud-glass-border);
        border-radius: 20px;
        color: var(--text-muted);
    }

    .empty-state-icon {
        font-size: 64px;
        margin-bottom: 20px;
        opacity: 0.5;
    }
</style>

<div class="support-wrapper">
    <div class="support-header">
        <div class="support-title">
            <span class="material-icons-round">headset_mic</span>
            Suporte
        </div>
        <a href="<?= base_url($tenant['slug'] . '/suporte/novo') ?>" class="hud-btn primary btn-minimalist" title="Novo Chamado">
            <span class="material-icons-round">add</span>
        </a>
    </div>

    <?php if (empty($tickets)): ?>
        <div class="empty-state">
            <span class="material-icons-round empty-state-icon">mark_chat_read</span>
            <p style="font-size: 1.1rem; font-weight: 600; color: var(--text);">Você ainda não possui chamados.</p>
            <p style="margin-top: 8px;">Precisa de ajuda com alguma especialidade ou erro? Nossa equipe está pronta para ajudar!</p>
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
                            <?= $status['label'] ?>
                        </span>
                    </div>
                    <div class="ticket-meta">
                        <span class="ticket-category"><?= $category['icon'] ?> <?= $category['label'] ?></span>
                        <span>#<?= str_pad($ticket['id'], 5, '0', STR_PAD_LEFT) ?></span>
                        <span>
                            <span class="material-icons-round" style="font-size: 14px; vertical-align: middle;">calendar_today</span>
                            <?= $date->format('d/m/Y H:i') ?>
                        </span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>