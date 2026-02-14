<?php
/**
 * Admin: Evaluation Center
 * 
 * High-end interface for evaluating step submissions.
 */
$totalPendingItems = array_sum(array_column($pendingQueue, 'pending_count'));
$uniqueUnits = count(array_unique(array_column($pendingQueue, 'unit_name')));
?>

<div class="evaluation-layout">
    <!-- Page Toolbar (Filters & Tabs) -->
    <div class="page-toolbar" style="margin-bottom: 32px; display: flex; justify-content: space-between; align-items: center; background: var(--bg-sidebar); border: 1px solid var(--border-color); border-radius: 12px; padding: 16px 24px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <p style="color: var(--text-secondary); font-size: 0.95rem; font-weight: 500; margin: 0;">
                <span class="material-icons-round" style="vertical-align: middle; font-size: 1.1rem; color: var(--primary);">info</span>
                Gestão tática de requisitos e validação de progresso.
            </p>
        </div>
        
        <div style="display: flex; gap: 8px;">
            <button class="btn-toolbar primary" onclick="showEvalTab('pending')" id="btn-tab-pending">
                <span class="material-icons-round">analytics</span>
                Fila Ativa
            </button>
            <button class="btn-toolbar secondary" onclick="showEvalTab('history')" id="btn-tab-history">
                <span class="material-icons-round">history</span>
                Histórico
            </button>
        </div>
    </div>

    <!-- Dynamic Stats Grid -->
    <section class="hero-section">
        <div class="stat-glass-card">
            <div class="stat-icon-box" style="color: #fbbf24;">
                <span class="material-icons-round">pending_actions</span>
            </div>
            <div>
                <div class="stat-value"><?= count($pendingQueue) ?></div>
                <div class="stat-label">Sessões Pendentes</div>
            </div>
        </div>
        
        <div class="stat-glass-card">
            <div class="stat-icon-box" style="color: var(--eval-cyan);">
                <span class="material-icons-round">rule</span>
            </div>
            <div>
                <div class="stat-value"><?= $totalPendingItems ?></div>
                <div class="stat-label">Itens Aguardando</div>
            </div>
        </div>

        <div class="stat-glass-card">
            <div class="stat-icon-box" style="color: var(--eval-purple);">
                <span class="material-icons-round">tour</span>
            </div>
            <div>
                <div class="stat-value"><?= $uniqueUnits ?></div>
                <div class="stat-label">Unidades em Fila</div>
            </div>
        </div>
    </section>

    <!-- Pending Queue Display -->
    <div id="pending-tab">
        <?php if (empty($pendingQueue)): ?>
            <div class="stat-glass-card" style="justify-content: center; padding: 100px 40px; flex-direction: column; text-align: center;">
                <span class="material-icons-round" style="font-size: 4rem; color: #10b981; opacity: 0.3; margin-bottom: 24px;">verified_user</span>
                <h3 style="margin: 0; font-size: 1.5rem;">Criptografia Silenciosa...</h3>
                <p style="color: var(--text-secondary); margin-top: 10px;">Todos os requisitos foram validados. Nenhum dado pendente.</p>
            </div>
        <?php else: ?>
            <div class="queue-table-container">
                <table class="queue-table">
                    <thead>
                        <tr>
                            <th>Candidato</th>
                            <th>Programa</th>
                            <th>Pendente</th>
                            <th>Último Sinal</th>
                            <th>Progresso</th>
                            <th style="text-align: right;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingQueue as $idx => $q): ?>
                            <tr style="animation: fadeInUp 0.4s ease-out forwards; animation-delay: <?= $idx * 0.05 ?>s; opacity: 0;">
                                <td>
                                    <div class="table-identity">
                                        <div class="table-avatar">
                                            <?php if ($q['avatar_url']): ?>
                                                <img src="<?= $q['avatar_url'] ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                            <?php else: ?>
                                                <?= strtoupper(substr($q['user_name'] ?? 'U', 0, 1)) ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="table-candidate-info">
                                            <h4><?= htmlspecialchars($q['user_name']) ?></h4>
                                            <p>
                                                <span class="material-icons-round" style="font-size: 0.8rem;">hub</span>
                                                <?= htmlspecialchars($q['unit_name'] ?? 'Equipe Principal') ?>
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="table-program">
                                        <div class="table-program-icon">
                                            <span class="material-icons-round" style="font-size: 1.1rem;"><?= $q['program_icon'] ?? 'bookmark' ?></span>
                                        </div>
                                        <span class="table-program-name"><?= htmlspecialchars($q['program_name']) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="tag-pending"><?= $q['pending_count'] ?> pendentes</span>
                                </td>
                                <td>
                                    <div class="table-timestamp">
                                        <?= date('d/m H:i', strtotime($q['last_submission'])) ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="table-progress-box">
                                        <div class="table-progress-bar">
                                            <div class="table-progress-inner" style="width: <?= $q['progress_percent'] ?>%;"></div>
                                        </div>
                                        <div class="table-progress-label"><?= $q['progress_percent'] ?>%</div>
                                    </div>
                                </td>
                                <td style="text-align: right;">
                                    <div class="action-cluster">
                                        <a href="<?= base_url($tenant['slug'] . '/admin/aprovacoes/' . $q['progress_id'] . '/review') ?>" 
                                           class="action-btn primary" 
                                           title="Avaliar Requisitos"
                                           hx-boost="false">
                                            <span class="material-icons-round">assignment_turned_in</span>
                                        </a>
                                        <button class="action-btn secondary" 
                                                onclick="bulkApproveProgram(<?= $q['progress_id'] ?>)" 
                                                title="Aprovar tudo de uma vez">
                                            <span class="material-icons-round">done_all</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- History Display -->
    <div id="history-tab" style="display: none;">
        <!-- Smart Filter Bar -->
        <div class="history-filter-bar">
            <div class="filter-search-group">
                <span class="material-icons-round filter-search-icon">search</span>
                <input type="text" id="historySearch" class="filter-search-input" 
                       placeholder="Pesquisar por nome, validador..." 
                       oninput="filterHistory()">
            </div>
            <div class="filter-controls">
                <select id="historyStatusFilter" class="filter-select" onchange="filterHistory()">
                    <option value="all">Todos os Status</option>
                    <option value="approved">✅ Aprovados</option>
                    <option value="rejected">❌ Rejeitados</option>
                </select>
                <input type="date" id="historyDateFrom" class="filter-date" onchange="filterHistory()" title="Data inicial">
                <input type="date" id="historyDateTo" class="filter-date" onchange="filterHistory()" title="Data final">
                <button class="filter-clear-btn" onclick="clearHistoryFilters()" title="Limpar filtros">
                    <span class="material-icons-round">filter_list_off</span>
                </button>
            </div>
        </div>

        <!-- Results Counter -->
        <div id="historyResultCount" class="history-result-count"></div>

        <div class="stat-glass-card" style="display: block; padding: 0; overflow: hidden;">
            <?php if (empty($recentApprovals)): ?>
                <div style="padding: 60px; text-align: center; color: var(--text-secondary);">Nenhum log de atividade detectado.</div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th>Evento</th>
                                <th>Sujeito</th>
                                <th>Validador</th>
                                <th>Timestamp</th>
                            </tr>
                        </thead>
                        <tbody id="historyTableBody">
                            <?php foreach ($recentApprovals as $log): ?>
                                <tr data-status="<?= $log['action'] ?>" 
                                    data-name="<?= htmlspecialchars(strtolower($log['user_name'] ?? '')) ?>" 
                                    data-reviewer="<?= htmlspecialchars(strtolower($log['reviewer_name'] ?? '')) ?>"
                                    data-date="<?= date('Y-m-d', strtotime($log['created_at'])) ?>">
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 12px;">
                                            <div style="width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; background: <?= $log['action'] === 'approved' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)' ?>; color: <?= $log['action'] === 'approved' ? '#10b981' : '#f87171' ?>;">
                                                <span class="material-icons-round" style="font-size: 1.1rem;"><?= $log['action'] === 'approved' ? 'check_circle' : 'cancel' ?></span>
                                            </div>
                                            <span style="font-weight: 600; font-size: 0.9rem; color: var(--text-main);"><?= $log['action'] === 'approved' ? 'APROVADO' : 'REJEITADO' ?></span>
                                        </div>
                                    </td>
                                    <td style="font-weight: 500; color: var(--text-main);"><?= htmlspecialchars($log['user_name'] ?? 'N/A') ?></td>
                                    <td style="color: var(--text-secondary);"><?= htmlspecialchars($log['reviewer_name'] ?? 'Auto-System') ?></td>
                                    <td style="font-family: 'JetBrains Mono'; font-size: 0.85rem; color: var(--eval-cyan);">
                                        <?= date('d/m/Y H:i', strtotime($log['created_at'])) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <!-- No results state -->
                <div id="historyNoResults" style="display: none; padding: 60px; text-align: center; color: var(--text-secondary);">
                    <span class="material-icons-round" style="font-size: 3rem; opacity: 0.3; margin-bottom: 12px; display: block;">search_off</span>
                    Nenhum resultado encontrado com os filtros atuais.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>



<script>
    function showEvalTab(tab) {
        document.getElementById('pending-tab').style.display = tab === 'pending' ? 'block' : 'none';
        document.getElementById('history-tab').style.display = tab === 'history' ? 'block' : 'none';
        
        const btnPending = document.getElementById('btn-tab-pending');
        const btnHistory = document.getElementById('btn-tab-history');

        if (tab === 'pending') {
            btnPending.classList.replace('secondary', 'primary');
            btnHistory.classList.replace('primary', 'secondary');
        } else {
            btnHistory.classList.replace('secondary', 'primary');
            btnPending.classList.replace('primary', 'secondary');
        }
    }

    async function bulkApproveProgram(id) {
        const confirmed = await sconfirm('Deseja validar todos os requisitos pendentes nesta sessão?', 'Aprovação em Massa');
        if (!confirmed) return;
        try {
            const resp = await fetch(`/<?= $tenant['slug'] ?>/admin/aprovacoes/${id}/bulk-approve-program`, { method: 'POST' });
            const data = await resp.json();
            if (data.success) { 
                await swal(data.message, 'Sucesso');
                location.reload(); 
            }
            else { 
                swal(data.error || 'Erro operacional', 'Houve um problema');
            }
        } catch (err) { 
            console.error(err);
            swal('Falha na conexão', 'Erro de Conexão'); 
        }
    }

    // Smart History Filter
    function filterHistory() {
        const search = document.getElementById('historySearch')?.value?.toLowerCase() || '';
        const status = document.getElementById('historyStatusFilter')?.value || 'all';
        const dateFrom = document.getElementById('historyDateFrom')?.value || '';
        const dateTo = document.getElementById('historyDateTo')?.value || '';

        const rows = document.querySelectorAll('#historyTableBody tr');
        let visible = 0;

        rows.forEach(row => {
            const rowStatus = row.dataset.status;
            const rowName = row.dataset.name || '';
            const rowReviewer = row.dataset.reviewer || '';
            const rowDate = row.dataset.date || '';

            let show = true;

            // Text search (name or reviewer)
            if (search && !rowName.includes(search) && !rowReviewer.includes(search)) {
                show = false;
            }

            // Status filter
            if (status !== 'all') {
                const statusMap = { 'approved': 'approved', 'rejected': 'rejected' };
                if (rowStatus !== statusMap[status]) show = false;
            }

            // Date range
            if (dateFrom && rowDate < dateFrom) show = false;
            if (dateTo && rowDate > dateTo) show = false;

            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        // Show/hide no-results state
        const noResults = document.getElementById('historyNoResults');
        if (noResults) noResults.style.display = visible === 0 ? 'block' : 'none';

        // Update counter
        const counter = document.getElementById('historyResultCount');
        const hasFilters = search || status !== 'all' || dateFrom || dateTo;
        if (counter) {
            counter.style.display = hasFilters ? 'block' : 'none';
            counter.textContent = `${visible} de ${rows.length} registro${rows.length !== 1 ? 's' : ''}`;
        }
    }

    function clearHistoryFilters() {
        document.getElementById('historySearch').value = '';
        document.getElementById('historyStatusFilter').value = 'all';
        document.getElementById('historyDateFrom').value = '';
        document.getElementById('historyDateTo').value = '';
        filterHistory();
    }
</script>