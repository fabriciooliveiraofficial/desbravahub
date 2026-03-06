<!-- Stats Grid -->
<div class="stats-grid">
    <!-- Active Users (Purple) -->
    <div class="stat-card purple">
        <div class="stat-card-bg-icon purple">
            <span class="material-icons-round">people</span>
        </div>
        <div class="stat-icon">
            <span class="material-icons-round">people</span>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= $stats['active_users'] ?></span>
            <span class="stat-label">Usuários Ativos</span>
        </div>
    </div>

    <!-- Specialties (Pink) -->
    <div class="stat-card pink">
        <div class="stat-card-bg-icon pink">
            <span class="material-icons-round">track_changes</span>
        </div>
        <div class="stat-icon">
            <span class="material-icons-round">track_changes</span>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= $stats['activities'] ?></span>
            <span class="stat-label">Especialidades</span>
        </div>
    </div>

    <!-- Pending Proofs (Amber) -->
    <div class="stat-card amber">
        <div class="stat-card-bg-icon amber">
            <span class="material-icons-round">hourglass_empty</span>
        </div>
        <div class="stat-icon">
            <span class="material-icons-round">hourglass_empty</span>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= $stats['pending_proofs'] ?></span>
            <span class="stat-label">Provas Pendentes</span>
        </div>
    </div>

    <!-- Completions (Green) -->
    <div class="stat-card green">
        <div class="stat-card-bg-icon green">
            <span class="material-icons-round">check_box</span>
        </div>
        <div class="stat-icon">
            <span class="material-icons-round">check_box</span>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= $stats['completed_activities'] ?></span>
            <span class="stat-label">Conclusões</span>
        </div>
    </div>
</div>

<div class="dashboard-grid">
    <!-- Quick Actions -->
    <section class="dashboard-card">
        <div class="dashboard-card-header">
            <span class="material-icons-round" style="color: #f97316;">bolt</span>
            <h3>Ações Rápidas</h3>
        </div>
        <div class="dashboard-card-body">
            <div class="quick-actions">
                <a href="<?= base_url($tenant['slug'] . '/admin/especialidades') ?>"
                    class="action-item action-blue">
                    <div class="action-content">
                        <div class="action-icon-box">
                            <span class="material-icons-round">add</span>
                        </div>
                        <span class="action-label">Nova Especialidade</span>
                    </div>
                    <span class="material-icons-round action-arrow">chevron_right</span>
                </a>

                <a href="<?= base_url($tenant['slug'] . '/admin/aprovacoes') ?>" class="action-item action-orange">
                    <div class="action-content">
                        <div class="action-icon-box">
                            <span class="material-icons-round">assignment</span>
                        </div>
                        <span class="action-label">Revisar Provas</span>
                    </div>
                    <span class="material-icons-round action-arrow">chevron_right</span>
                </a>

                <a href="<?= base_url($tenant['slug'] . '/admin/notificacoes') ?>" class="action-item action-pink">
                    <div class="action-content">
                        <div class="action-icon-box">
                            <span class="material-icons-round">campaign</span>
                            <h3 style="display:none">Actions</h3>
                        </div>
                        <span class="action-label">Enviar Notificação</span>
                    </div>
                    <span class="material-icons-round action-arrow">chevron_right</span>
                </a>
            </div>
        </div>
    </section>

    <!-- Club Summary -->
    <section class="dashboard-card">
        <div class="dashboard-card-header">
            <span class="material-icons-round" style="color: #3b82f6;">poll</span>
            <h3>Resumo do Clube</h3>
        </div>
        <div class="dashboard-card-body" style="display: flex; flex-direction: column; justify-content: center;">
            <div class="summary-list">
                <div class="summary-item">
                    <div class="summary-label">
                        <span class="summary-dot dot-purple"></span>
                        <span>Total de Usuários</span>
                    </div>
                    <span class="summary-value"><?= $stats['users'] ?></span>
                </div>

                <div class="summary-item">
                    <div class="summary-label">
                        <span class="summary-dot dot-pink"></span>
                        <span>Especialidades Criadas</span>
                    </div>
                    <span class="summary-value"><?= $stats['activities'] ?></span>
                </div>

                <div class="summary-item">
                    <div class="summary-label">
                        <span class="summary-dot dot-green"></span>
                        <span>Especialidades Concluídas</span>
                    </div>
                    <span class="summary-value"><?= $stats['completed_activities'] ?></span>
                </div>
            </div>
        </div>
    </section>
    <!-- Recent Notifications Board -->
    <section class="dashboard-card" id="notification-board-container">
        <div class="dashboard-card-header">
            <span class="material-icons-round" style="color: #ef4444;">notifications</span>
            <h3>Avisos e Alertas</h3>
            <a href="<?= base_url($tenant['slug'] . '/notificacoes') ?>" class="link-more" style="margin-left: auto;">Ver Todas</a>
        </div>
        <div class="dashboard-card-body">
            <?php if (empty($notifications)): ?>
                <div class="empty-state small">
                    <span class="material-icons-round empty-icon" style="font-size: 2rem;">notifications_none</span>
                    <p>Nenhuma notificação recente.</p>
                </div>
            <?php else: ?>
                <div class="summary-list">
                    <?php foreach ($notifications as $n): 
                        $icon = 'notifications';
                        $color = '#64748b';
                        if (str_contains(strtolower($n['title']), 'sos')) { $icon = 'emergency'; $color = '#ef4444'; }
                        elseif (str_contains(strtolower($n['title']), 'missão')) { $icon = 'rocket_launch'; $color = '#00d9ff'; }
                        elseif (str_contains(strtolower($n['title']), 'conquista')) { $icon = 'military_tech'; $color = '#00ff88'; }
                    ?>
                        <a href="<?= base_url($tenant['slug'] . '/notificacoes') ?>" class="summary-item" style="text-decoration: none; color: inherit; padding: 10px; border-radius: 8px; transition: background 0.2s;">
                            <div class="summary-label" style="gap: 12px;">
                                <span class="material-icons-round" style="color: <?= $color ?>; font-size: 20px;"><?= $icon ?></span>
                                <div style="display: flex; flex-direction: column;">
                                    <span style="font-weight: 600; font-size: 0.9rem;"><?= htmlspecialchars($n['title']) ?></span>
                                    <span style="font-size: 0.75rem; opacity: 0.6; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px;">
                                        <?= htmlspecialchars($n['message']) ?>
                                    </span>
                                </div>
                            </div>
                            <span class="summary-value" style="font-size: 0.7rem; font-weight: normal; opacity: 0.5;">
                                <?= date('d/m H:i', strtotime($n['created_at'])) ?>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>