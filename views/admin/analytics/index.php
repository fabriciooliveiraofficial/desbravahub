<?php
/**
 * Admin: Analytics Dashboard
 * 
 * Learning analytics with completion rates, category stats, and insights.
 */
$pageTitle = 'Analytics';
$completionRate = $stats['total_assignments'] > 0
    ? round(($stats['completed'] / $stats['total_assignments']) * 100)
    : 0;
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - <?= htmlspecialchars($tenant['name']) ?></title>
    <link rel="stylesheet" href="<?= asset_url('css/admin.css') ?>">
    <style>
        .admin-main {
            padding: 24px;
        }

        .page-header {
            margin-bottom: 24px;
        }

        .page-header h1 {
            margin: 0;
            font-size: 1.5rem;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border-light);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
        }

        .stat-icon {
            font-size: 2rem;
            margin-bottom: 8px;
        }

        /* Sections */
        .section {
            background: var(--bg-card);
            border: 1px solid var(--border-light);
            border-radius: 12px;
            margin-bottom: 24px;
            overflow: hidden;
        }

        .section-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-light);
            font-weight: 600;
        }

        .section-body {
            padding: 20px;
        }

        /* Charts Grid */
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 24px;
            margin-bottom: 24px;
        }

        /* Category Stats */
        .category-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid var(--border-light);
        }

        .category-row:last-child {
            border-bottom: none;
        }

        .category-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .category-info {
            flex: 1;
        }

        .category-name {
            font-weight: 600;
        }

        .category-meta {
            font-size: 0.85rem;
            color: var(--text-secondary);
        }

        .category-progress {
            width: 100px;
            text-align: right;
        }

        .progress-bar {
            height: 8px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            overflow: hidden;
            margin-top: 4px;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--accent-cyan), var(--accent-green));
            border-radius: 4px;
        }

        /* Program Stats */
        .program-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid var(--border-light);
        }

        .program-row:last-child {
            border-bottom: none;
        }

        .program-icon {
            font-size: 1.5rem;
        }

        .program-info {
            flex: 1;
        }

        .program-name {
            font-weight: 600;
        }

        .program-meta {
            font-size: 0.85rem;
            color: var(--text-secondary);
        }

        .program-stats {
            display: flex;
            gap: 16px;
            text-align: center;
        }

        .program-stat-value {
            font-weight: 700;
            color: var(--accent-cyan);
        }

        .program-stat-label {
            font-size: 0.75rem;
            color: var(--text-secondary);
        }

        /* Problem Steps */
        .problem-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid var(--border-light);
        }

        .problem-row:last-child {
            border-bottom: none;
        }

        .problem-icon {
            font-size: 1.5rem;
        }

        .rejection-count {
            background: rgba(244, 67, 54, 0.2);
            color: #f44336;
            padding: 4px 10px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.85rem;
        }

        /* Recent Completions */
        .completion-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid var(--border-light);
        }

        .completion-row:last-child {
            border-bottom: none;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--accent-cyan);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: var(--bg-dark);
        }

        .completion-info {
            flex: 1;
        }

        .user-name {
            font-weight: 600;
        }

        .completion-program {
            font-size: 0.85rem;
            color: var(--text-secondary);
        }

        .completion-time {
            font-size: 0.8rem;
            color: var(--text-secondary);
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-secondary);
        }
    </style>
</head>

<body>
    <?php require BASE_PATH . '/views/admin/partials/sidebar.php'; ?>

    <main class="admin-main">
        <header class="page-toolbar">
            <div class="page-info">
                <h2 class="header-title">📊 Analytics de Aprendizagem</h2>
            </div>
        </header>

        <!-- Overview Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-content">
                    <div class="stat-icon">📚</div>
                    <span class="stat-value"><?= $stats['total_programs'] ?></span>
                    <span class="stat-label">Programas Publicados</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-content">
                    <div class="stat-icon">👥</div>
                    <span class="stat-value"><?= $stats['total_assignments'] ?></span>
                    <span class="stat-label">Atribuições</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-content">
                    <div class="stat-icon">✅</div>
                    <span class="stat-value"><?= $stats['completed'] ?></span>
                    <span class="stat-label">Concluídos</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-content">
                    <div class="stat-icon">🔄</div>
                    <span class="stat-value"><?= $stats['in_progress'] ?></span>
                    <span class="stat-label">Em Andamento</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-content">
                    <div class="stat-icon">⏳</div>
                    <span class="stat-value"><?= $stats['pending_approvals'] ?></span>
                    <span class="stat-label">Pendentes</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-content">
                    <div class="stat-icon">📈</div>
                    <span class="stat-value"><?= $avgCompletion ?>%</span>
                    <span class="stat-label">Progresso Médio</span>
                </div>
            </div>
        </div>

        <div class="charts-grid">
            <!-- Category Performance -->
            <div class="section">
                <div class="section-header">📂 Desempenho por Categoria</div>
                <div class="section-body">
                    <?php if (empty($categoryStats)): ?>
                        <div class="empty-state">Nenhuma categoria encontrada</div>
                    <?php else: ?>
                        <?php foreach ($categoryStats as $cat): ?>
                            <div class="category-row">
                                <div class="category-icon" style="background: <?= htmlspecialchars($cat['color']) ?>20;">
                                    <?= $cat['icon'] ?>
                                </div>
                                <div class="category-info">
                                    <div class="category-name"><?= htmlspecialchars($cat['name']) ?></div>
                                    <div class="category-meta">
                                        <?= $cat['program_count'] ?> programas •
                                        <?= $cat['assignment_count'] ?> atribuições
                                    </div>
                                </div>
                                <div class="category-progress">
                                    <span><?= $cat['avg_progress'] ?? 0 ?>%</span>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?= $cat['avg_progress'] ?? 0 ?>%;"></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Problem Areas -->
            <div class="section">
                <div class="section-header">⚠️ Áreas que Precisam de Atenção</div>
                <div class="section-body">
                    <?php if (empty($problemSteps)): ?>
                        <div class="empty-state">✨ Nenhuma rejeição frequente</div>
                    <?php else: ?>
                        <?php foreach ($problemSteps as $step): ?>
                            <div class="problem-row">
                                <span class="problem-icon"><?= $step['program_icon'] ?></span>
                                <div class="program-info">
                                    <div class="program-name"><?= htmlspecialchars($step['title']) ?></div>
                                    <div class="program-meta"><?= htmlspecialchars($step['program_name']) ?></div>
                                </div>
                                <span class="rejection-count"><?= $step['rejection_count'] ?> rejeições</span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="charts-grid">
            <!-- Top Programs -->
            <div class="section">
                <div class="section-header">🏆 Top Programas</div>
                <div class="section-body">
                    <?php if (empty($topPrograms)): ?>
                        <div class="empty-state">Nenhum programa com atribuições</div>
                    <?php else: ?>
                        <?php foreach ($topPrograms as $prog): ?>
                            <div class="program-row">
                                <span class="program-icon"><?= $prog['icon'] ?></span>
                                <div class="program-info">
                                    <div class="program-name"><?= htmlspecialchars($prog['name']) ?></div>
                                    <div class="program-meta">
                                        <?= $prog['type'] === 'class' ? 'Classe' : 'Especialidade' ?>
                                    </div>
                                </div>
                                <div class="program-stats">
                                    <div>
                                        <div class="program-stat-value"><?= $prog['total_assigns'] ?></div>
                                        <div class="program-stat-label">Atribuídos</div>
                                    </div>
                                    <div>
                                        <div class="program-stat-value"><?= $prog['completions'] ?></div>
                                        <div class="program-stat-label">Concluídos</div>
                                    </div>
                                    <div>
                                        <div class="program-stat-value"><?= $prog['avg_progress'] ?>%</div>
                                        <div class="program-stat-label">Progresso</div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Completions -->
            <div class="section">
                <div class="section-header">🎉 Conclusões Recentes</div>
                <div class="section-body">
                    <?php if (empty($recentCompletions)): ?>
                        <div class="empty-state">Nenhuma conclusão ainda</div>
                    <?php else: ?>
                        <?php foreach ($recentCompletions as $completion): ?>
                            <div class="completion-row">
                                <div class="user-avatar">
                                    <?= strtoupper(substr($completion['user_name'], 0, 1)) ?>
                                </div>
                                <div class="completion-info">
                                    <div class="user-name"><?= htmlspecialchars($completion['user_name']) ?></div>
                                    <div class="completion-program">
                                        <?= $completion['program_icon'] ?>         <?= htmlspecialchars($completion['program_name']) ?>
                                    </div>
                                </div>
                                <span class="completion-time">
                                    <?= date('d/m H:i', strtotime($completion['completed_at'])) ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </main>
</body>

</html>