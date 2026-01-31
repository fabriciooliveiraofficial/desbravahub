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
                <a href="<?= base_url($tenant['slug'] . '/admin/especialidades/nova') ?>"
                    class="action-item action-blue">
                    <div class="action-content">
                        <div class="action-icon-box">
                            <span class="material-icons-round">add</span>
                        </div>
                        <span class="action-label">Nova Especialidade</span>
                    </div>
                    <span class="material-icons-round action-arrow">chevron_right</span>
                </a>

                <a href="<?= base_url($tenant['slug'] . '/admin/provas') ?>" class="action-item action-orange">
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
</div>