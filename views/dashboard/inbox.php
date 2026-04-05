<?php
/**
 * Inbox View - Central de Avisos
 */
?>
<style>
.inbox-wrapper {
    display: flex;
    flex-direction: column;
    gap: 24px;
    padding: 0 40px 40px;
    max-width: 1000px;
    margin: 0 auto;
}

.inbox-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 20px;
}

.inbox-title {
    font-size: 1.8rem;
    font-weight: 800;
    color: var(--text-bright);
    letter-spacing: 1px;
}

.inbox-tabs {
    display: flex;
    gap: 12px;
    background: rgba(255, 255, 255, 0.05);
    padding: 6px;
    border-radius: 14px;
    border: 1px solid rgba(255, 255, 255, 0.08);
}

.inbox-tab {
    padding: 8px 20px;
    border-radius: 10px;
    font-size: 0.85rem;
    font-weight: 700;
    color: var(--text-dim);
    cursor: pointer;
    transition: all 0.2s;
    border: none;
    background: transparent;
}

.inbox-tab.active {
    background: var(--accent-cyan);
    color: #0B1121;
}

.inbox-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.inbox-item {
    background: rgba(20, 30, 48, 0.4);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 20px;
    padding: 24px;
    display: flex;
    gap: 20px;
    transition: all 0.3s;
    position: relative;
    overflow: hidden;
}

.inbox-item:hover {
    background: rgba(255, 255, 255, 0.05);
    border-color: rgba(6, 182, 212, 0.3);
    transform: translateY(-2px);
}

.inbox-item.unread {
    border-left: 4px solid var(--accent-cyan);
    background: rgba(6, 182, 212, 0.05);
}

.inbox-item-icon {
    width: 60px;
    height: 60px;
    border-radius: 16px;
    background: rgba(255, 255, 255, 0.05);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    color: var(--text-dim);
    flex-shrink: 0;
}

.unread .inbox-item-icon {
    background: rgba(6, 182, 212, 0.1);
    color: var(--accent-cyan);
}

.inbox-item-content {
    flex: 1;
}

.inbox-item-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 8px;
}

.inbox-item-title {
    font-size: 1.1rem;
    font-weight: 800;
    color: var(--text-bright);
}

.inbox-item-time {
    font-size: 0.75rem;
    color: var(--text-dim);
}

.inbox-item-msg {
    color: var(--text-dim);
    font-size: 0.95rem;
    line-height: 1.5;
    margin-bottom: 16px;
}

.inbox-item-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: var(--accent-cyan);
    text-decoration: none;
    font-size: 0.85rem;
    font-weight: 700;
}

.inbox-item-link:hover {
    text-decoration: underline;
}

.inbox-empty {
    text-align: center;
    padding: 60px;
    color: var(--text-dim);
}

.inbox-empty-icon {
    font-size: 64px !important;
    opacity: 0.2;
    margin-bottom: 20px;
}

@media (max-width: 768px) {
    .inbox-wrapper { padding: 0 16px 16px; }
    .inbox-header { flex-direction: column; align-items: flex-start; gap: 16px; }
    .inbox-item { padding: 16px; gap: 12px; }
    .inbox-item-icon { width: 44px; height: 44px; font-size: 20px; }
}
</style>

<div class="inbox-wrapper">
    <div class="inbox-header">
        <h1 class="inbox-title">CENTRAL DE AVISOS</h1>
        <div class="inbox-tabs">
            <button class="inbox-tab active" data-target="todos">TODOS</button>
            <button class="inbox-tab" data-target="conquistas">CONQUISTAS</button>
            <button class="inbox-tab" data-target="clube">CLUBE</button>
        </div>
    </div>

    <div class="inbox-list" id="inbox-content">
        <?php foreach (['todos', 'conquistas', 'clube'] as $cat): ?>
            <div class="tab-pane <?= $cat === 'todos' ? '' : 'hidden' ?>" id="pane-<?= $cat ?>">
                <?php if (empty($categories[$cat])): ?>
                    <div class="inbox-empty">
                        <span class="material-icons-round inbox-empty-icon">notifications_none</span>
                        <p>Nenhuma notificação encontrada nesta categoria.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($categories[$cat] as $n): ?>
                        <?php 
                        $isUnread = empty($n['read_at']);
                        $data = json_decode($n['data'] ?? '{}', true);
                        $link = $data['link'] ?? $data['url'] ?? '';
                        
                        $icon = 'notifications';
                        if (in_array($n['type'], ['achievement', 'level_up'])) $icon = 'emoji_events';
                        if ($n['type'] === 'mission_assigned') $icon = 'military_tech';
                        if ($n['type'] === 'event') $icon = 'event';
                        if ($n['type'] === 'sos_alert') $icon = 'report_problem';
                        ?>
                        <div class="inbox-item <?= $isUnread ? 'unread' : '' ?>">
                            <div class="inbox-item-icon">
                                <span class="material-icons-round"><?= $icon ?></span>
                            </div>
                            <div class="inbox-item-content">
                                <div class="inbox-item-header">
                                    <h3 class="inbox-item-title"><?= htmlspecialchars($n['title']) ?></h3>
                                    <span class="inbox-item-time"><?= date('d/m/Y H:i', strtotime($n['created_at'])) ?></span>
                                </div>
                                <p class="inbox-item-msg"><?= htmlspecialchars($n['message']) ?></p>
                                <?php if ($link): ?>
                                    <a href="<?= $link ?>" class="inbox-item-link">
                                        VER DETALHES
                                        <span class="material-icons-round">arrow_forward</span>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
.hidden { display: none !important; }
</style>

<script>
(function() {
    const tabs = document.querySelectorAll('.inbox-tab');
    const panes = document.querySelectorAll('.tab-pane');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const target = tab.dataset.target;
            
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');

            panes.forEach(p => {
                if (p.id === 'pane-' + target) {
                    p.classList.remove('hidden');
                } else {
                    p.classList.add('hidden');
                }
            });
        });
    });
})();
</script>
