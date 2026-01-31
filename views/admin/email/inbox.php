<?php
/**
 * Email Inbox - Maximum Visibility Edition v4.0
 * NO ANIMATIONS - FORCED CONTRAST
 */
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    :root {
        --font-primary: 'Inter', sans-serif;
    }

    body {
        background-color: #f1f5f9 !important;
    }

    .email-wrapper {
        font-family: 'Inter', sans-serif;
        max-width: 1400px;
        margin: 0 auto;
        padding-bottom: 60px;
        color: #0f172a;
    }

    /* Header */
    /* Header Alignments */
    .page-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }
    .page-toolbar h2 {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-dark);
        margin: 0;
    }

    .btn-action {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        border-radius: 100px;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
    }

    .btn-cyan {
        background: #06b6d4 !important;
        color: white !important;
        box-shadow: 0 4px 6px rgba(6, 182, 212, 0.2);
    }
    .btn-cyan:hover {
        background: #0891b2 !important;
    }

    .btn-white {
        background: white !important;
        color: #0f172a !important;
        border: 2px solid #cbd5e1 !important;
    }
    .btn-white:hover {
        background: #f8fafc !important;
        border-color: #94a3b8 !important;
    }

    /* Stats Cards - FORCED VISIBILITY */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 24px;
        margin-bottom: 40px;
    }

    .stat-card {
        background: #ffffff !important;
        padding: 24px;
        border-radius: 16px;
        border: 2px solid #94a3b8 !important; /* DARK BORDER */
        display: flex;
        align-items: center;
        gap: 20px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08) !important;
        opacity: 1 !important; /* Force Opacity */
        transform: none !important; /* No transforms */
    }
    
    .stat-card:hover {
        border-color: #64748b !important;
        box-shadow: 0 8px 16px rgba(0,0,0,0.12) !important;
    }

    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        font-weight: 700;
        opacity: 1 !important;
    }

    .stat-content {
        display: flex;
        flex-direction: column;
    }

    /* Email List */
    .email-container {
        background: #ffffff !important;
        border-radius: 20px;
        border: 2px solid #cbd5e1 !important;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }

    .list-header {
        padding: 24px 32px;
        background: #fff;
        border-bottom: 2px solid #e2e8f0;
        font-weight: 800;
        color: #0f172a !important;
        font-size: 1.2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .msg-row {
        display: flex;
        align-items: center;
        padding: 24px 32px;
        border-bottom: 1px solid #e2e8f0;
        gap: 24px;
        cursor: pointer;
        text-decoration: none;
        color: inherit;
        background: #fff;
    }

    .msg-row:hover {
        background: #f8fafc !important;
    }

    .msg-row:last-child {
        border-bottom: none;
    }

    .msg-icon-box {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        flex-shrink: 0;
    }

    .msg-body {
        flex: 1;
        min-width: 0;
    }

    .msg-subject {
        font-weight: 700;
        font-size: 1.1rem;
        color: #0f172a !important;
        margin-bottom: 6px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .msg-meta {
        font-size: 0.9rem;
        color: #475569 !important;
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    
    .meta-tag {
        background: #f1f5f9;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 600;
        color: #334155;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: 1px solid #cbd5e1;
    }

    .msg-right {
        text-align: right;
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 8px;
    }
    
    .msg-date {
        font-size: 0.9rem;
        color: #475569 !important;
        font-weight: 600;
    }

    .status-badge {
        padding: 6px 14px;
        border-radius: 100px;
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    /* Empty State */
    .empty-inbox {
        text-align: center;
        padding: 80px 20px;
    }
    .empty-illust {
        font-size: 5rem;
        margin-bottom: 24px;
    }

</style>

<div class="email-wrapper">
    
    <!-- Toolbar -->
    <div class="page-toolbar">
        <div class="page-info">
            <h2 class="header-title"><span style="color: #06b6d4;">📬</span> Centro de Mensagens</h2>
            <p class="text-muted">Gerencie a comunicação com o clube</p>
        </div>
        <div class="actions-group">
            <a href="<?= base_url($tenant['slug'] . '/admin/email/settings') ?>" class="btn-toolbar secondary">
                <span class="material-icons-round">settings</span> Configurações
            </a>
            <a href="<?= base_url($tenant['slug'] . '/admin/email/compose') ?>" class="btn-toolbar primary">
                <span class="material-icons-round">add</span> Novo Email
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
        <!-- Total -->
        <div class="stat-card">
            <div class="stat-icon" style="background: #e0f2fe !important; color: #0284c7 !important;">
                <span class="material-icons-round">all_inbox</span>
            </div>
            <div class="stat-content">
                <span class="stat-value"><?= $stats['total'] ?? 0 ?></span>
                <span class="stat-label">Total Criados</span>
            </div>
        </div>
        <!-- Enviados -->
        <div class="stat-card">
            <div class="stat-icon" style="background: #dcfce7 !important; color: #15803d !important;">
                <span class="material-icons-round">check_circle</span>
            </div>
            <div class="stat-content">
                <span class="stat-value"><?= $stats['sent'] ?? 0 ?></span>
                <span class="stat-label">Enviados</span>
            </div>
        </div>
        <!-- Rascunhos -->
        <div class="stat-card">
            <div class="stat-icon" style="background: #ffedd5 !important; color: #c2410c !important;">
                <span class="material-icons-round">edit_note</span>
            </div>
            <div class="stat-content">
                <span class="stat-value"><?= $stats['draft'] ?? 0 ?></span>
                <span class="stat-label">Rascunhos</span>
            </div>
        </div>
        <!-- Falhas -->
        <div class="stat-card">
            <div class="stat-icon" style="background: #fee2e2 !important; color: #b91c1c !important;">
                <span class="material-icons-round">error_outline</span>
            </div>
            <div class="stat-content">
                <span class="stat-value"><?= $stats['failed'] ?? 0 ?></span>
                <span class="stat-label">Falhas/Erros</span>
            </div>
        </div>
    </div>

    <!-- Inbox List -->
    <div class="email-container">
        <div class="list-header">
            Mensagens Recentes
            <div style="font-size: 0.95rem; font-weight: 700; color: #475569; background: #f1f5f9; padding: 4px 12px; border-radius: 100px;">
                <?= count($emails) ?> registros
            </div>
        </div>

        <?php if (empty($emails)): ?>
            <div class="empty-inbox">
                <div class="empty-illust">🪁</div>
                <h3 style="font-size: 1.8rem; color: #0f172a !important; font-weight: 800; margin-bottom: 8px;">Caixa de entrada vazia</h3>
                <p style="color: #475569 !important; max-width: 400px; margin: 0 auto; font-size: 1.1rem; font-weight: 500;">Assim que você enviar emails para o clube, eles aparecerão listados aqui.</p>
            </div>
        <?php else: ?>
            <?php foreach ($emails as $email): ?>
                <?php 
                    $statusColor = match($email['status']) {
                        'sent' => '#15803d',
                        'draft' => '#c2410c',
                        'failed' => '#b91c1c',
                        default => '#0369a1'
                    };
                    $statusBg = match($email['status']) {
                        'sent' => '#dcfce7',
                        'draft' => '#ffedd5',
                        'failed' => '#fee2e2',
                        default => '#e0f2fe'
                    };
                ?>
                <div class="msg-row" onclick="window.location='<?= base_url($tenant['slug'] . '/admin/email/compose?id=' . $email['id']) ?>'">
                    <div class="msg-icon-box" style="background: <?= $statusBg ?> !important; color: <?= $statusColor ?> !important;">
                        <span class="material-icons-round">
                            <?= match($email['status']) {
                                'sent' => 'done_all',
                                'draft' => 'edit',
                                'failed' => 'priority_high',
                                default => 'mail'
                            } ?>
                        </span>
                    </div>

                    <div class="msg-body">
                        <div class="msg-subject"><?= htmlspecialchars($email['subject']) ?></div>
                        <div class="msg-meta">
                            <span class="meta-tag">
                                <span class="material-icons-round" style="font-size: 16px;">person</span>
                                <?= htmlspecialchars($email['sender_name'] ?? 'Admin') ?>
                            </span>
                            <span class="meta-tag">
                                <span class="material-icons-round" style="font-size: 16px;">group</span>
                                <?= match ($email['recipient_type']) {
                                    'individual' => 'Individual',
                                    'role' => 'Cargo',
                                    'unit' => 'Unidade',
                                    'all' => 'Todos',
                                    default => 'Outros'
                                } ?>
                            </span>
                        </div>
                    </div>

                    <div class="msg-right">
                        <span class="status-badge" style="background: <?= $statusBg ?> !important; color: <?= $statusColor ?> !important;">
                            <?= strtoupper($email['status'] == 'sent' ? 'Enviado' : ($email['status'] == 'draft' ? 'Rascunho' : $email['status'])) ?>
                        </span>
                        <div class="msg-date"><?= date('d M, H:i', strtotime($email['created_at'])) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
<!-- No GSAP Animations -->
<script>
    // Just a clean load
    console.log('Email Layout Loaded - High Contrast Mode');
</script>
