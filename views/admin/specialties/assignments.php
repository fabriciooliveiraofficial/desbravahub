<?php
/**
 * Admin: View All Assignments (Enhanced UI)
 */
$pageTitle = 'Atribuições de Especialidades';
?>
<!-- GSAP & Motion -->


<style>
    :root {
        --glass-bg: rgba(255, 255, 255, 0.7);
        --glass-border: rgba(255, 255, 255, 0.5);
        --glass-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
        --gradient-primary: linear-gradient(135deg, #06b6d4 0%, #3b82f6 100%);
        --gradient-success: linear-gradient(135deg, #10b981 0%, #059669 100%);
        --gradient-warning: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        --gradient-purple: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
    }

    /* Base Layout */
    .assignments-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 4px;
    }

    /* Hero Section */
    .gsap-hero {
        margin-bottom: 40px;
        padding: 40px 0 20px 0;
        position: relative;
    }

    .gsap-hero h1 {
        font-size: 2.5rem;
        font-weight: 800;
        letter-spacing: -1px;
        background: var(--gradient-primary);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 8px;
        display: inline-block;
    }

    .gsap-hero .subtitle {
        color: var(--text-secondary);
        font-size: 1.1rem;
        max-width: 600px;
    }

    /* Stats Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: var(--bg-card);
        border: 1px solid var(--border-light);
        border-radius: 10px;
        padding: 14px 12px;
        text-align: center;
    }

    .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
    }

    .stat-label {
        color: var(--text-secondary);
        font-size: 0.75rem;
        margin-top: 2px;
    }

    .stat-card.pending .stat-value {
        color: #ff9800;
    }

    .stat-card.in_progress .stat-value {
        color: var(--accent-cyan);
    }

    .stat-card.pending_review .stat-value {
        color: #9c27b0;
    }

    .stat-card.completed .stat-value {
        color: var(--accent-green);
    }

    .filters {
        display: flex;
        gap: 6px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .filter-btn {
        padding: 8px 14px;
        background: transparent;
        border: 1px solid var(--border-light);
        border-radius: 8px;
        color: var(--text-secondary);
        text-decoration: none;
        font-size: 0.85rem;
        white-space: nowrap;
    }

    .filter-btn.active {
        background: var(--accent-cyan);
        color: var(--bg-primary);
        border-color: var(--accent-cyan);
    }

    .assignments-table {
        background: var(--bg-card);
        border: 1px solid var(--border-light);
        border-radius: 10px;
        overflow-x: auto;
    }

    .assignments-table table {
        width: 100%;
        border-collapse: collapse;
        min-width: 600px;
    }

    .assignments-table th,
    .assignments-table td {
        padding: 12px 10px;
        text-align: left;
        border-bottom: 1px solid var(--border-light);
        font-size: 0.85rem;
    }

    .assignments-table th {
        background: rgba(0, 0, 0, 0.2);
        font-weight: 600;
        color: var(--text-secondary);
        white-space: nowrap;
    }

    .assignments-table tr:last-child td {
        border-bottom: none;
    }

    .specialty-cell {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .specialty-icon {
        font-size: 1.2rem;
    }

    .status-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 16px;
        font-size: 0.75rem;
        font-weight: 500;
        white-space: nowrap;
    }

    .status-pending {
        background: rgba(255, 152, 0, 0.2);
        color: #ff9800;
    }

    .status-in_progress {
        background: rgba(0, 217, 255, 0.2);
        color: var(--accent-cyan);
    }

    .status-pending_review {
        background: rgba(156, 39, 176, 0.2);
        color: #9c27b0;
    }

    .status-completed {
        background: rgba(0, 255, 136, 0.2);
        color: var(--accent-green);
    }

    .status-cancelled {
        background: rgba(244, 67, 54, 0.2);
        color: #f44336;
    }

    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: var(--text-secondary);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .admin-main {
            padding: 16px 10px;
        }

        .page-header {
            flex-direction: column;
            align-items: flex-start;
            padding-top: 70px;
        }

        .page-header h1 {
            font-size: 1.2rem;
        }

        .stats-row {
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
        }

        .stat-card {
            padding: 12px 8px;
        }

        .stat-value {
            font-size: 1.3rem;
        }

        .filters {
            overflow-x: auto;
            flex-wrap: nowrap;
            padding-bottom: 8px;
        }

        .filter-btn {
            padding: 6px 12px;
            font-size: 0.8rem;
        }
    }

    @media (max-width: 480px) {
        .stat-value {
            font-size: 1.2rem;
        }

        .stat-label {
            font-size: 0.7rem;
        }
    }
</style>

    <!-- Content -->
    <!-- Page Hero -->
    <section class="page-hero">
        <h1><span class="icon">📋</span> Atribuições de Especialidades</h1>
        <p class="subtitle">Acompanhe o progresso dos desbravadores</p>
    </section>

    <!-- Page Toolbar -->
    <div class="page-toolbar">
        <div class="actions-group">
            <a href="<?= base_url($tenant['slug'] . '/admin/especialidades') ?>" class="btn btn-primary">
                📚 Ver Catálogo
            </a>
        </div>
    </div>

    <div class="stats-row">
        <div class="stat-card pending">
            <div class="stat-value"><?= $counts['pending'] ?></div>
            <div class="stat-label">Pendentes</div>
        </div>
        <div class="stat-card in_progress">
            <div class="stat-value"><?= $counts['in_progress'] ?></div>
            <div class="stat-label">Em Andamento</div>
        </div>
        <div class="stat-card pending_review">
            <div class="stat-value"><?= $counts['pending_review'] ?></div>
            <div class="stat-label">Aguardando Avaliação</div>
        </div>
        <div class="stat-card completed">
            <div class="stat-value"><?= $counts['completed'] ?></div>
            <div class="stat-label">Concluídas</div>
        </div>
    </div>

    <div class="filters">
        <a href="<?= base_url($tenant['slug'] . '/admin/especialidades/atribuicoes') ?>"
            class="filter-btn <?= !$status ? 'active' : '' ?>">
            Todas
        </a>
        <a href="<?= base_url($tenant['slug'] . '/admin/especialidades/atribuicoes?status=pending') ?>"
            class="filter-btn <?= $status === 'pending' ? 'active' : '' ?>">
            Pendentes
        </a>
        <a href="<?= base_url($tenant['slug'] . '/admin/especialidades/atribuicoes?status=in_progress') ?>"
            class="filter-btn <?= $status === 'in_progress' ? 'active' : '' ?>">
            Em Andamento
        </a>
        <a href="<?= base_url($tenant['slug'] . '/admin/especialidades/atribuicoes?status=pending_review') ?>"
            class="filter-btn <?= $status === 'pending_review' ? 'active' : '' ?>">
            Aguardando Avaliação
        </a>
        <a href="<?= base_url($tenant['slug'] . '/admin/especialidades/atribuicoes?status=completed') ?>"
            class="filter-btn <?= $status === 'completed' ? 'active' : '' ?>">
            Concluídas
        </a>
    </div>

    <div class="assignments-table">
        <?php if (empty($assignments)): ?>
            <div class="empty-state">
                <p>Nenhuma atribuição encontrada</p>
                <a href="<?= base_url($tenant['slug'] . '/admin/especialidades') ?>" style="color: var(--accent-cyan);">
                    Ir para o catálogo →
                </a>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Especialidade</th>
                        <th>Desbravador</th>
                        <th>Atribuído por</th>
                        <th>Data Limite</th>
                        <th>Status</th>
                        <th>XP</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($assignments as $a): ?>
                        <tr>
                            <td>
                                <div class="specialty-cell">
                                    <span class="specialty-icon"><?= $a['specialty']['badge_icon'] ?? '📘' ?></span>
                                    <span><?= htmlspecialchars($a['specialty']['name'] ?? 'Desconhecida') ?></span>
                                </div>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($a['user_name']) ?></strong><br>
                                <small style="color: var(--text-secondary);"><?= htmlspecialchars($a['user_email']) ?></small>
                            </td>
                            <td><?= htmlspecialchars($a['assigned_by_name'] ?? '-') ?></td>
                            <td><?= $a['due_date'] ? date('d/m/Y', strtotime($a['due_date'])) : '-' ?></td>
                            <td>
                                <span class="status-badge status-<?= $a['status'] ?>">
                                    <?php
                                    $statusLabels = [
                                        'pending' => 'Pendente',
                                        'in_progress' => 'Em Andamento',
                                        'pending_review' => 'Aguardando Avaliação',
                                        'completed' => 'Concluída',
                                        'cancelled' => 'Cancelada'
                                    ];
                                    echo $statusLabels[$a['status']] ?? $a['status'];
                                    ?>
                                </span>
                            </td>
                            <td>
                                <?= $a['xp_earned'] > 0 ? '🌟 ' . $a['xp_earned'] : '-' ?>
                            </td>
                            <td>
                                <div class="actions" style="display: flex; gap: 8px;">
                                    <a href="<?= base_url($tenant['slug'] . '/admin/especialidades/atribuicao/' . $a['id']) ?>"
                                        class="btn-icon" title="Ver Detalhes">
                                        👁️
                                    </a>
                                    <button onclick="confirmDelete('<?= $a['assignment_id'] ?>')" 
                                            class="btn-icon delete" title="Remover Atribuição">
                                        🗑️
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Delete Confirmation -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmDelete(assignmentId) {
            Swal.fire({
                title: 'Remover Atribuição?',
                text: "Esta ação não pode ser desfeita. O progresso do desbravador será perdido.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sim, remover',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    deleteAssignment(assignmentId);
                }
            })
        }

        function deleteAssignment(assignmentId) {
            fetch('<?= base_url($tenant['slug'] . '/admin/especialidades/atribuicao/delete') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'assignment_id=' + encodeURIComponent(assignmentId)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire(
                        'Removido!',
                        'A atribuição foi removida com sucesso.',
                        'success'
                    ).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire(
                        'Erro!',
                        data.error || 'Ocorreu um erro ao remover.',
                        'error'
                    );
                }
            })
            .catch(error => {
                Swal.fire(
                    'Erro!',
                    'Erro de conexão.',
                    'error'
                );
            });
        }
    </script>

    <style>
        .btn-icon {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.1rem;
            padding: 4px;
            border-radius: 4px;
            transition: background 0.2s;
            text-decoration: none;
        }

        .btn-icon:hover {
            background: rgba(0,0,0,0.05);
        }

        .btn-icon.delete:hover {
            background: rgba(244, 67, 54, 0.1);
        }
    </style>