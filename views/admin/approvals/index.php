<?php
/**
 * Admin: Evaluation Center
 * 
 * High-end interface for evaluating step submissions.
 */
$totalPendingItems = array_sum(array_column($pendingQueue, 'pending_count'));
$uniqueUnits = count(array_unique(array_column($pendingQueue, 'unit_name')));
?>

<style>
/* ============ LIGHT MINIMAL DESIGN SYSTEM ============ */
:root {
    --lm-bg: #ffffff;
    --lm-border: #f1f5f9;
    --lm-border-soft: #e2e8f0;
    --lm-text-main: #1e293b;
    --lm-text-sec: #64748b;
    --lm-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    --lm-accent-green: #10b981;
    --lm-accent-red: #ef4444;
}

/* History Tab Animation */
#history-tab {
    animation: lm-fade-in 0.4s ease-out;
}

/* Minimal Filter Bar */
.history-filter-bar {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    background: var(--lm-bg);
    padding: 16px 20px;
    border-radius: 12px;
    border: 1px solid var(--lm-border-soft);
    margin-bottom: 24px;
    align-items: center;
    box-shadow: var(--lm-shadow);
}

.filter-search-group {
    flex: 1;
    min-width: 280px;
    position: relative;
    display: flex;
    align-items: center;
}

.filter-search-icon {
    position: absolute;
    left: 14px;
    color: var(--lm-text-sec);
    font-size: 1.1rem;
    pointer-events: none;
}

.filter-search-input {
    width: 100%;
    background: #f8fafc;
    border: 1px solid var(--lm-border-soft);
    border-radius: 10px;
    padding: 10px 12px 10px 42px;
    color: var(--lm-text-main);
    font-size: 0.9rem;
    font-weight: 500;
    transition: all 0.2s;
}

.filter-search-input:focus {
    outline: none;
    border-color: var(--primary);
    background: #ffffff;
    box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.1);
}

.filter-controls {
    display: flex;
    gap: 8px;
    align-items: center;
}

.filter-select, .filter-date {
    background: #f8fafc;
    border: 1px solid var(--lm-border-soft);
    border-radius: 10px;
    padding: 8px 12px;
    color: var(--lm-text-main);
    font-size: 0.85rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.filter-select:hover, .filter-date:hover {
    border-color: var(--lm-text-sec);
}

.filter-clear-btn {
    width: 38px;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    background: #fff1f2;
    color: #e11d48;
    border: 1px solid #fecdd3;
    cursor: pointer;
    transition: all 0.2s;
}

.filter-clear-btn:hover {
    background: #e11d48;
    color: white;
}

/* Results Counter */
.history-result-count {
    font-size: 0.75rem;
    color: var(--lm-text-sec);
    margin: -12px 0 16px 4px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

/* Minimal Table Styles */
.history-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.history-table th {
    padding: 16px 20px;
    text-align: left;
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--lm-text-sec);
    text-transform: uppercase;
    letter-spacing: 0.1em;
    border-bottom: 2px solid var(--lm-border);
    background: #fcfdfe;
}

.history-table td {
    padding: 14px 20px;
    vertical-align: middle;
    border-bottom: 1px solid var(--lm-border);
    transition: background 0.2s;
}

.history-table tr:hover td {
    background: #f8fafc;
}

/* Status High Contrast */
.status-cell {
    display: flex;
    align-items: center;
    gap: 8px;
}

.status-text {
    font-weight: 700;
    font-size: 0.75rem;
    padding: 4px 10px;
    border-radius: 6px;
    display: inline-block;
}

.status-text.approved { 
    color: var(--lm-accent-green); 
    background: rgba(16, 185, 129, 0.08);
}

.status-text.rejected { 
    color: var(--lm-accent-red); 
    background: rgba(239, 68, 68, 0.08);
}

/* Identity Design */
.log-identity {
    display: flex;
    align-items: center;
    gap: 10px;
}

.log-avatar {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    background: #f1f5f9;
    border: 1px solid var(--lm-border-soft);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--lm-text-main);
    overflow: hidden;
    flex-shrink: 0;
}

.log-name {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--lm-text-main);
}

.log-timestamp {
    font-size: 0.85rem;
    font-weight: 500;
    color: var(--lm-text-sec);
}

@keyframes lm-fade-in {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>


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
                                                <img src="<?= $q['avatar_url'] ?>" 
                                                     style="width: 100%; height: 100%; object-fit: cover;"
                                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                <span class="avatar-fallback-initials" style="display:none;"><?= strtoupper(substr($q['user_name'] ?? 'U', 0, 1)) ?></span>
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
                       placeholder="Filtrar por nome do membro ou validador..." 
                       oninput="filterHistory()">
            </div>
            <div class="filter-controls">
                <select id="historyStatusFilter" class="filter-select" onchange="filterHistory()">
                    <option value="all">Filtro: Status</option>
                    <option value="approved">Aprovados</option>
                    <option value="rejected">Rejeitados</option>
                </select>
                <input type="date" id="historyDateFrom" class="filter-date" onchange="filterHistory()" title="Data inicial">
                <input type="date" id="historyDateTo" class="filter-date" onchange="filterHistory()" title="Data final">
                <button class="filter-clear-btn" onclick="clearHistoryFilters()" title="Redefinir Filtros">
                    <span class="material-icons-round">refresh</span>
                </button>
            </div>
        </div>

        <!-- Results Counter -->
        <div id="historyResultCount" class="history-result-count"></div>

        <div class="stat-glass-card" style="display: block; padding: 0; overflow: hidden; background: #ffffff; border: 1px solid var(--lm-border-soft); box-shadow: var(--lm-shadow); border-radius: 12px;">
            <?php if (empty($recentApprovals)): ?>
                <div style="padding: 100px; text-align: center;">
                    <span class="material-icons-round" style="font-size: 4rem; color: #cbd5e1; margin-bottom: 16px; display: block;">history_toggle_off</span>
                    <div style="color: var(--lm-text-main); font-weight: 700; font-size: 1.25rem;">Nenhuma atividade recente</div>
                    <div style="color: var(--lm-text-sec); margin-top: 8px;">O registro de auditoria está pronto para as próximas ações.</div>
                </div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Candidato</th>
                                <th>Validador</th>
                                <th>Data & Hora</th>
                            </tr>
                        </thead>
                        <tbody id="historyTableBody">
                            <?php foreach ($recentApprovals as $log): ?>
                                <tr data-status="<?= $log['action'] ?>" 
                                    data-name="<?= htmlspecialchars(strtolower($log['user_name'] ?? '')) ?>" 
                                    data-reviewer="<?= htmlspecialchars(strtolower($log['reviewer_name'] ?? '')) ?>"
                                    data-date="<?= date('Y-m-d', strtotime($log['created_at'])) ?>">
                                    <td>
                                        <div class="status-cell">
                                            <span class="status-text <?= $log['action'] ?>">
                                                <span class="material-icons-round" style="vertical-align: middle; font-size: 0.9rem; margin-right: 4px;"><?= $log['action'] === 'approved' ? 'check' : 'close' ?></span>
                                                <?= $log['action'] === 'approved' ? 'APROVADO' : 'REJEITADO' ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="log-identity">
                                            <div class="log-avatar">
                                                <?php if (!empty($log['user_avatar'])): ?>
                                                    <img src="<?= $log['user_avatar'] ?>" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                    <span style="display:none;"><?= strtoupper(substr($log['user_name'] ?? 'U', 0, 1)) ?></span>
                                                <?php else: ?>
                                                    <?= strtoupper(substr($log['user_name'] ?? 'U', 0, 1)) ?>
                                                <?php endif; ?>
                                            </div>
                                            <span class="log-name"><?= htmlspecialchars($log['user_name'] ?? 'N/A') ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="log-identity">
                                            <div class="log-avatar" style="border-radius: 50%; background: #f8fafc;">
                                                <?php if (!empty($log['reviewer_avatar'])): ?>
                                                    <img src="<?= $log['reviewer_avatar'] ?>" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                    <span style="display:none;"><?= strtoupper(substr($log['reviewer_name'] ?? 'A', 0, 1)) ?></span>
                                                <?php else: ?>
                                                    <?= strtoupper(substr($log['reviewer_name'] ?? 'A', 0, 1)) ?>
                                                <?php endif; ?>
                                            </div>
                                            <span class="log-name" style="color: var(--lm-text-sec); font-weight: 500; font-size: 0.85rem;"><?= htmlspecialchars($log['reviewer_name'] ?? 'Auto-System') ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="log-timestamp">
                                            <?= date('d/m/Y', strtotime($log['created_at'])) ?>
                                            <span style="opacity: 0.7; margin-left: 6px; font-weight: 400;"><?= date('H:i', strtotime($log['created_at'])) ?></span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <!-- No results state -->
                <div id="historyNoResults" style="display: none; padding: 100px; text-align: center;">
                    <span class="material-icons-round" style="font-size: 4rem; color: #cbd5e1; margin-bottom: 20px; display: block;">search_off</span>
                    <div style="color: var(--lm-text-main); font-weight: 700; font-size: 1.2rem;">Ops! Nada encontrado</div>
                    <div style="color: var(--lm-text-sec); margin-top: 10px;">Refine seus filtros para encontrar outros registros.</div>
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