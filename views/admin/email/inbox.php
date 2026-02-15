<?php
/**
 * Email Inbox - Master Design v5.0
 * Standard Admin Layout
 */
?>
<!-- Toolbar (Sticky, Transparent) -->
<div class="permissions-toolbar" style="
    position: sticky; 
    top: 0; 
    z-index: 50; 
    margin: -2rem -2rem 2rem -2rem; 
    padding: 1rem 2rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
">
    <div style="display: flex; gap: 1rem;">
        <!-- Placeholder for potential future filters -->
    </div>

    <!-- Actions -->
    <div class="header-actions" style="display: flex; gap: 1rem;">
        <a href="<?= base_url($tenant['slug'] . '/admin/email/settings') ?>" class="btn btn-secondary">
            <span class="material-icons-round">settings</span>
            Configurações
        </a>
        <a href="<?= base_url($tenant['slug'] . '/admin/email/compose') ?>" class="btn btn-primary">
            <span class="material-icons-round">add</span>
            Novo Email
        </a>
    </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid">
    <!-- Total -->
    <div class="stat-card" style="border-left: 4px solid #3b82f6;">
        <div class="stat-icon" style="background-color: #eff6ff; color: #3b82f6;">
            <span class="material-icons-round">all_inbox</span>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= number_format($stats['total'] ?? 0) ?></span>
            <span class="stat-label">Total Criados</span>
        </div>
    </div>

    <!-- Enviados -->
    <div class="stat-card green" style="border-left: 4px solid #10b981;">
        <div class="stat-icon">
            <span class="material-icons-round">check_circle</span>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= number_format($stats['sent'] ?? 0) ?></span>
            <span class="stat-label">Enviados</span>
        </div>
    </div>

    <!-- Rascunhos -->
    <div class="stat-card amber" style="border-left: 4px solid #f59e0b;">
        <div class="stat-icon">
            <span class="material-icons-round">edit_note</span>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= number_format($stats['draft'] ?? 0) ?></span>
            <span class="stat-label">Rascunhos</span>
        </div>
    </div>

    <!-- Falhas -->
    <div class="stat-card" style="border-left: 4px solid #ef4444;">
        <div class="stat-icon" style="background-color: #fef2f2; color: #ef4444;">
            <span class="material-icons-round">error_outline</span>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= number_format($stats['failed'] ?? 0) ?></span>
            <span class="stat-label">Falhas/Erros</span>
        </div>
    </div>
</div>

<!-- Data Table Card -->
<div class="dashboard-card">
    <div class="dashboard-card-header">
        <span class="material-icons-round" style="color: var(--primary);">list_alt</span>
        <h3>Mensagens Recentes</h3>
        <div style="margin-left: auto; font-size: 0.875rem; color: var(--text-muted); font-weight: 500;">
            <?= count($emails) ?> registros
        </div>
    </div>

    <div class="table-container" style="border: none; box-shadow: none;">
        <?php if (empty($emails)): ?>
            <div style="text-align: center; padding: 4rem 2rem;">
                <span class="material-icons-round" style="font-size: 4rem; color: var(--text-muted); opacity: 0.5; margin-bottom: 1rem;">inbox</span>
                <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--text-dark); margin-bottom: 0.5rem;">Caixa de entrada vazia</h3>
                <p style="color: var(--text-muted);">Nenhum email encontrado.</p>
            </div>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 60px;">Status</th>
                        <th>Assunto</th>
                        <th>Para</th>
                        <th>Data</th>
                        <th style="width: 100px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($emails as $email): ?>
                        <tr style="cursor: pointer;" onclick="window.location='<?= base_url($tenant['slug'] . '/admin/email/compose?id=' . $email['id']) ?>'">
                            <td data-label="Status">
                                <?php 
                                    $icon = match($email['status']) {
                                        'sent' => 'check_circle',
                                        'draft' => 'edit',
                                        'failed' => 'error',
                                        default => 'mail'
                                    };
                                    $color = match($email['status']) {
                                        'sent' => 'text-emerald-600',
                                        'draft' => 'text-amber-600',
                                        'failed' => 'text-red-600',
                                        default => 'text-blue-600'
                                    };
                                    // Inline colors for simplicity since specific text classes might vary
                                    $styleColor = match($email['status']) {
                                        'sent' => '#10b981',
                                        'draft' => '#f59e0b',
                                        'failed' => '#ef4444',
                                        default => '#3b82f6'
                                    };
                                ?>
                                <span class="material-icons-round" style="color: <?= $styleColor ?>; font-size: 1.25rem;">
                                    <?= $icon ?>
                                </span>
                            </td>
                            <td data-label="Assunto">
                                <div style="font-weight: 600; color: var(--text-dark); margin-bottom: 0.25rem;">
                                    <?= htmlspecialchars($email['subject']) ?>
                                </div>
                                <div style="font-size: 0.8rem; color: var(--text-muted); display: flex; align-items: center; gap: 0.5rem;">
                                    <span class="material-icons-round" style="font-size: 14px;">person</span>
                                    <?= htmlspecialchars($email['sender_name'] ?? 'Admin') ?>
                                </div>
                            </td>
                            <td data-label="Para">
                                <span class="badge" style="background-color: var(--bg-hover); color: var(--text-muted); border: 1px solid var(--border-color);">
                                    <?= match ($email['recipient_type']) {
                                        'individual' => 'Individual',
                                        'role' => 'Cargo',
                                        'unit' => 'Unidade',
                                        'all' => 'Todos',
                                        default => 'Outros'
                                    } ?>
                                </span>
                            </td>
                            <td data-label="Data">
                                <div style="font-size: 0.875rem; font-weight: 500;">
                                    <?= date('d M, Y', strtotime($email['created_at'])) ?>
                                </div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">
                                    <?= date('H:i', strtotime($email['created_at'])) ?>
                                </div>
                            </td>
                            <td data-label="Ação">
                                <?php
                                $badgeClass = match($email['status']) {
                                    'sent' => 'badge-success',
                                    'draft' => 'badge-warning',
                                    'failed' => 'badge-danger',
                                    default => 'badge-info'
                                };
                                $statusLabel = match($email['status']) {
                                    'sent' => 'Enviado',
                                    'draft' => 'Rascunho',
                                    'failed' => 'Falha',
                                    default => ucfirst($email['status'])
                                };
                                ?>
                                <span class="badge <?= $badgeClass ?>"><?= $statusLabel ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
