<?php
/**
 * Admin: View All Assignments (Premium Redesign)
 */
$pageTitle = 'Atribuições';
$canForceDelete = (new \App\Services\PermissionService())->can('specialties.delete_active');
?>

<div class="assignments-container pb-5">
    <!-- Breadcrumbs & Header -->
    <nav aria-label="breadcrumb" class="mb-4 animate__animated animate__fadeIn">
        <ol class="breadcrumb mb-2">
            <li class="breadcrumb-item"><a href="<?= base_url($tenant['slug'] . '/admin/dashboard') ?>" class="text-secondary text-decoration-none small">Dashboard</a></li>
            <li class="breadcrumb-item active small text-white-50" aria-current="page text-white">Atribuições</li>
        </ol>
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h2 mb-0 fw-bold text-white">Gestão de Atribuições</h1>
            <a href="<?= base_url($tenant['slug'] . '/admin/especialidades') ?>" class="btn glass-btn d-flex align-items-center gap-2">
                <iconify-icon icon="lucide:plus-circle"></iconify-icon>
                Nova Atribuição
            </a>
        </div>
    </nav>

    <!-- Glassmorphism Stats Cards -->
    <div class="stats-grid mb-5">
        <!-- Pendentes -->
        <div class="glass-card stat-card p-4 animate__animated animate__fadeInUp">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon-wrapper" style="background: rgba(100, 116, 139, 0.1); color: #94a3b8;">
                    <iconify-icon icon="lucide:clock" data-width="24"></iconify-icon>
                </div>
                <div>
                    <div class="text-secondary small fw-medium">Pendentes</div>
                    <div class="h2 mb-0 fw-bold"><?= $counts['pending'] ?></div>
                </div>
            </div>
            <div class="stat-progress mt-3">
                <div class="progress-bar-bg"><div class="progress-bar-fill" style="width: 100%; background: #94a3b8;"></div></div>
            </div>
        </div>

        <!-- Em Andamento -->
        <div class="glass-card stat-card p-4 animate__animated animate__fadeInUp" style="animation-delay: 0.1s">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon-wrapper" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                    <iconify-icon icon="lucide:zap" data-width="24"></iconify-icon>
                </div>
                <div>
                    <div class="text-secondary small fw-medium">Em Andamento</div>
                    <div class="h2 mb-0 fw-bold"><?= $counts['in_progress'] ?></div>
                </div>
            </div>
            <div class="stat-progress mt-3">
                <div class="progress-bar-bg"><div class="progress-bar-fill" style="width: 100%; background: #f59e0b;"></div></div>
            </div>
        </div>

        <!-- Aguardando Revisão -->
        <div class="glass-card stat-card p-4 animate__animated animate__fadeInUp" style="animation-delay: 0.2s">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon-wrapper" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                    <iconify-icon icon="lucide:clipboard-list" data-width="24"></iconify-icon>
                </div>
                <div>
                    <div class="text-secondary small fw-medium">Revisão</div>
                    <div class="h2 mb-0 fw-bold"><?= $counts['pending_review'] ?></div>
                </div>
            </div>
            <div class="stat-progress mt-3">
                <div class="progress-bar-bg"><div class="progress-bar-fill" style="width: 100%; background: #10b981;"></div></div>
            </div>
        </div>

        <!-- Concluídas -->
        <div class="glass-card stat-card p-4 animate__animated animate__fadeInUp" style="animation-delay: 0.3s">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon-wrapper" style="background: rgba(0, 217, 255, 0.1); color: var(--accent-blue);">
                    <iconify-icon icon="lucide:check-circle" data-width="24"></iconify-icon>
                </div>
                <div>
                    <div class="text-secondary small fw-medium">Concluídas</div>
                    <div class="h2 mb-0 fw-bold"><?= $counts['completed'] ?></div>
                </div>
            </div>
            <div class="stat-progress mt-3">
                <div class="progress-bar-bg"><div class="progress-bar-fill" style="width: 100%; background: var(--accent-blue);"></div></div>
            </div>
        </div>
    </div>

    <!-- Main List Card -->
    <div class="glass-card p-0 overflow-hidden animate__animated animate__fadeIn">
        <!-- Control Bar -->
        <div class="control-bar p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="filter-group d-flex gap-2">
                <a href="<?= base_url($tenant['slug'] . '/admin/especialidades/atribuicoes') ?>" 
                   class="filter-pill <?= !$status ? 'active' : '' ?>">Todas</a>
                <a href="<?= base_url($tenant['slug'] . '/admin/especialidades/atribuicoes?status=pending') ?>" 
                   class="filter-pill <?= $status === 'pending' ? 'active' : '' ?>">Pendentes</a>
                <a href="<?= base_url($tenant['slug'] . '/admin/especialidades/atribuicoes?status=in_progress') ?>" 
                   class="filter-pill <?= $status === 'in_progress' ? 'active' : '' ?>">Em Andamento</a>
                <a href="<?= base_url($tenant['slug'] . '/admin/especialidades/atribuicoes?status=pending_review') ?>" 
                   class="filter-pill <?= $status === 'pending_review' ? 'active' : '' ?>">Revisão</a>
                <a href="<?= base_url($tenant['slug'] . '/admin/especialidades/atribuicoes?status=completed') ?>" 
                   class="filter-pill <?= $status === 'completed' ? 'active' : '' ?>">Concluídas</a>
            </div>
            
            <div class="search-box">
                <iconify-icon icon="lucide:search"></iconify-icon>
                <input type="text" placeholder="Filtrar atribuições..." id="tableSearch">
            </div>
        </div>

        <div class="table-container">
            <table class="modern-table" id="assignmentsTable">
                <thead>
                    <tr>
                        <th class="ps-4">Item / Especialidade</th>
                        <th>Desbravador</th>
                        <th>Atribuído por</th>
                        <th>Status</th>
                        <th>Prazo</th>
                        <th class="text-end pe-4">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($assignments)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="empty-icon-wrapper mb-3 opacity-25">
                                    <iconify-icon icon="lucide:inbox" data-width="64"></iconify-icon>
                                </div>
                                <div class="text-secondary">Nenhuma atribuição encontrada.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($assignments as $a): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="item-visual">
                                            <?php if ($a['type_label'] === 'program'): ?>
                                                <div class="program-visual-badge">
                                                    <iconify-icon icon="lucide:graduation-cap"></iconify-icon>
                                                </div>
                                            <?php else: ?>
                                                <img src="<?= base_url('assets/img/specialties/' . ($a['specialty']['badge_icon'] ?? 'default.png')) ?>" 
                                                     class="specialty-visual-badge" alt="Badge">
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-white"><?= htmlspecialchars($a['specialty']['name'] ?? 'Desconhecida') ?></div>
                                            <div class="text-secondary smaller"><?= htmlspecialchars($a['specialty']['category_name'] ?? 'Especialidade') ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-circle">
                                            <?= strtoupper(substr($a['user_name'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="fw-medium text-white-50"><?= htmlspecialchars($a['user_name']) ?></div>
                                            <div class="text-secondary smaller"><?= htmlspecialchars($a['user_email']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-secondary small">
                                        <div class="d-flex align-items-center gap-1">
                                            <iconify-icon icon="lucide:user-check" data-width="14"></iconify-icon>
                                            <?= htmlspecialchars($a['assigned_by_name'] ?? 'Sistema') ?>
                                        </div>
                                        <div class="opacity-50"><?= date('d/m/Y', strtotime($a['created_at'])) ?></div>
                                    </div>
                                </td>
                                <td>
                                    <span class="status-pill status-<?= $a['status'] ?>">
                                        <?php
                                        $labels = [
                                            'pending' => 'Pendente',
                                            'in_progress' => 'Em Progresso',
                                            'pending_review' => 'Para Revisar',
                                            'completed' => 'Concluída',
                                            'cancelled' => 'Cancelada'
                                        ];
                                        echo $labels[$a['status']] ?? $a['status'];
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($a['due_date']): ?>
                                        <div class="d-flex align-items-center gap-1 <?= (strtotime($a['due_date']) < time() && $a['status'] !== 'completed') ? 'text-danger fw-bold' : 'text-secondary' ?> small">
                                            <iconify-icon icon="lucide:calendar-clock"></iconify-icon>
                                            <?= date('d/m/Y', strtotime($a['due_date'])) ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-white-10">---</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="action-buttons">
                                        <a href="<?= base_url($tenant['slug'] . '/admin/especialidades/atendimento-direto?user_id=' . $a['user_id'] . '&specialty_id=' . ($a['type_label'] === 'program' ? 'prog_' . $a['id'] : $a['specialty_id'])) ?>" 
                                           class="btn-icon-modern" title="Ver Detalhes">
                                            <iconify-icon icon="lucide:pencil"></iconify-icon>
                                        </a>
                                        <button onclick="handleDelete('<?= $a['assignment_id'] ?>', '<?= addslashes($a['specialty']['name']) ?>', '<?= addslashes($a['user_name']) ?>')" 
                                                class="btn-icon-modern btn-icon-danger" title="Excluir">
                                            <iconify-icon icon="lucide:trash-2"></iconify-icon>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
:root {
    --glass-bg: rgba(255, 255, 255, 0.03);
    --glass-border: rgba(255, 255, 255, 0.08);
    --accent-blue: #00d9ff;
    --text-vibrant: #00ff88;
}

.assignments-container {
    max-width: 1400px;
    margin: 0 auto;
}

/* Glassmorphism Generic */
.glass-card {
    background: var(--glass-bg);
    backdrop-filter: blur(12px);
    border: 1px solid var(--glass-border);
    border-radius: 20px;
    box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.2);
}

.glass-btn {
    background: rgba(0, 217, 255, 0.1);
    border: 1px solid rgba(0, 217, 255, 0.2);
    color: var(--accent-blue);
    padding: 10px 20px;
    border-radius: 12px;
    font-weight: 600;
    transition: all 0.3s ease;
    text-decoration: none;
}

.glass-btn:hover {
    background: var(--accent-blue);
    color: #0b0e14;
    transform: translateY(-2px);
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
}

.stat-card {
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
    background: rgba(255, 255, 255, 0.06);
}

.stat-icon-wrapper {
    width: 54px;
    height: 54px;
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.progress-bar-bg {
    height: 4px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 10px;
    overflow: hidden;
}

.progress-bar-fill {
    height: 100%;
}

/* Control Bar */
.control-bar {
    background: rgba(255, 255, 255, 0.02);
    border-bottom: 1px solid var(--glass-border);
}

.filter-pill {
    padding: 8px 18px;
    border-radius: 50px;
    font-size: 0.85rem;
    font-weight: 500;
    color: rgba(255, 255, 255, 0.5);
    text-decoration: none !important;
    transition: all 0.2s ease;
    border: 1px solid transparent;
}

.filter-pill:hover {
    color: #fff;
    background: rgba(255, 255, 255, 0.05);
}

.filter-pill.active {
    background: var(--accent-blue);
    color: #0b0e14 !important;
    font-weight: 700;
    box-shadow: 0 4px 15px rgba(0, 217, 255, 0.3);
}

.search-box {
    position: relative;
    width: 100%;
    max-width: 300px;
}

.search-box span {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: rgba(255, 255, 255, 0.3);
}

.search-box input {
    width: 100%;
    background: rgba(0, 0, 0, 0.2);
    border: 1px solid var(--glass-border);
    padding: 10px 15px 10px 45px;
    border-radius: 12px;
    color: #fff;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.search-box input:focus {
    outline: none;
    border-color: var(--accent-blue);
    background: rgba(0, 0, 0, 0.3);
}

/* Table Design */
.modern-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.modern-table th {
    padding: 18px 15px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: rgba(255, 255, 255, 0.4);
    background: rgba(0,0,0,0.1);
}

.modern-table td {
    padding: 16px 15px;
    border-bottom: 1px solid rgba(255,255,255,0.03);
    vertical-align: middle;
}

.modern-table tr:hover td {
    background: rgba(255,255,255,0.02);
}

.item-visual {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.specialty-visual-badge {
    width: 44px;
    height: 44px;
    object-fit: contain;
    filter: drop-shadow(0 4px 10px rgba(0,0,0,0.5));
}

.program-visual-badge {
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--accent-blue);
    font-size: 1.5rem;
}

.avatar-circle {
    width: 32px;
    height: 32px;
    background: linear-gradient(135deg, #3b82f6, #06b6d4);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: 700;
    font-size: 0.75rem;
}

.status-pill {
    padding: 5px 12px;
    border-radius: 6px;
    font-size: 0.65rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: inline-block;
}

.status-pending { background: rgba(100, 116, 139, 0.15); color: #94a3b8; border: 1px solid rgba(100, 116, 139, 0.2); }
.status-in_progress { background: rgba(245, 158, 11, 0.15); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.2); }
.status-pending_review { background: rgba(156, 39, 176, 0.15); color: #d946ef; border: 1px solid rgba(156, 39, 176, 0.2); }
.status-completed { background: rgba(0, 255, 136, 0.1); color: var(--text-vibrant); border: 1px solid rgba(0, 255, 136, 0.2); }
.status-cancelled { background: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2); }

.btn-icon-modern {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,0.05);
    color: rgba(255,255,255,0.5);
    border: 1px solid rgba(255,255,255,0.08);
    transition: all 0.2s ease;
    text-decoration: none;
}

.btn-icon-modern:hover {
    background: rgba(255,255,255,0.1);
    color: #fff;
    transform: scale(1.1);
}

.btn-icon-danger:hover {
    background: rgba(239, 68, 68, 0.15);
    color: #f87171;
    border-color: rgba(239, 68, 68, 0.3);
}

.smaller { font-size: 0.75rem; }

@media (max-width: 768px) {
    .stats-grid { grid-template-columns: 1fr 1fr; }
    .filter-group { overflow-x: auto; padding-bottom: 5px; }
    .search-box { max-width: none; }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('tableSearch');
    const table = document.getElementById('assignmentsTable');
    
    if (searchInput && table) {
        searchInput.addEventListener('input', function() {
            const term = this.value.toLowerCase();
            const rows = table.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(term) ? '' : 'none';
            });
        });
    }
});

function handleDelete(id, name, user) {
    Swal.fire({
        title: 'Remover Atribuição?',
        text: `Deseja remover "${name}" de ${user}? O progresso não será apagado, mas a tarefa não aparecerá mais como ativa.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: 'rgba(255,255,255,0.1)',
        confirmButtonText: 'Sim, remover',
        cancelButtonText: 'Cancelar',
        background: '#1a1a2e',
        color: '#fff'
    }).then((result) => {
        if (result.isConfirmed) {
            const isProgram = id.startsWith('prog_');
            const numericId = id.split('_')[1];
            
            // Note: Ensure these endpoints exist or update accordingly
            const method = isProgram ? 'DELETE' : 'POST';
            const url = isProgram 
                ? `<?= base_url($tenant['slug'] . '/admin/programas/desatribuir/') ?>${numericId}`
                : `<?= base_url($tenant['slug'] . '/admin/especialidades/atribuicao/delete') ?>`;

            const body = isProgram ? null : 'assignment_id=' + encodeURIComponent(id);
            const headers = isProgram ? {} : { 'Content-Type': 'application/x-www-form-urlencoded' };

            fetch(url, { 
                method: method,
                headers: headers,
                body: body
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Removida!',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false,
                        background: '#1a1a2e',
                        color: '#fff'
                    }).then(() => location.reload());
                } else {
                    throw new Error(data.error || 'Erro ao remover');
                }
            })
            .catch(err => {
                Swal.fire('Erro!', err.message, 'error');
            });
        }
    });
}
</script>