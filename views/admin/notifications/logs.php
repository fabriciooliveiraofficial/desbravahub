<?php include __DIR__ . '/../../layouts/admin_header.php'; ?>

<div class="content-wrapper">
    <div class="page-header">
        <div class="header-content">
            <h1 class="page-title">Relatório de Entregas</h1>
            <p class="page-subtitle">Histórico de e-mails e notificações disparadas pelo sistema.</p>
        </div>
        <div class="header-actions">
            <a href="<?= base_url($tenant['slug'] . '/admin/notificacoes') ?>" class="btn-secondary">
                <span class="material-icons-round">arrow_back</span>
                Voltar
            </a>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card glass">
            <div class="stat-icon email">
                <span class="material-icons-round">email</span>
            </div>
            <div class="stat-info">
                <h3 class="stat-value"><?= count(array_filter($logs, fn($l) => $l['status'] === 'sent')) ?></h3>
                <p class="stat-label">Enviados com Sucesso</p>
            </div>
        </div>

        <div class="stat-card glass">
            <div class="stat-icon error">
                <span class="material-icons-round">error_outline</span>
            </div>
            <div class="stat-info">
                <h3 class="stat-value"><?= count(array_filter($logs, fn($l) => $l['status'] === 'failed')) ?></h3>
                <p class="stat-label">Falhas Detectadas</p>
            </div>
        </div>
    </div>

    <div class="card glass">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Data/Hora</th>
                        <th>Destinatário</th>
                        <th>Assunto</th>
                        <th>Canal</th>
                        <th>Status</th>
                        <th>Detalhes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <span class="material-icons-round opacity-20" style="font-size: 48px;">history</span>
                                <p class="mt-2">Nenhum log encontrado.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td class="whitespace-nowrap">
                                    <div class="date-time">
                                        <span class="date"><?= date('d/m/Y', strtotime($log['created_at'])) ?></span>
                                        <span class="time"><?= date('H:i', strtotime($log['created_at'])) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="user-info">
                                        <span class="user-name"><?= htmlspecialchars($log['user_name'] ?? 'Membro') ?></span>
                                        <span class="user-email"><?= htmlspecialchars($log['to_email']) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="subject-text"><?= htmlspecialchars($log['subject']) ?></span>
                                </td>
                                <td>
                                    <span class="badge channel">E-mail</span>
                                </td>
                                <td>
                                    <?php if ($log['status'] === 'sent'): ?>
                                        <span class="badge success">
                                            <span class="dot"></span>
                                            Enviado
                                        </span>
                                    <?php elseif ($log['status'] === 'failed'): ?>
                                        <span class="badge danger">
                                            <span class="dot"></span>
                                            Falhou
                                        </span>
                                    <?php else: ?>
                                        <span class="badge warning">
                                            <span class="dot"></span>
                                            <?= htmlspecialchars($log['status']) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($log['error_message']): ?>
                                        <span class="error-trigger" title="<?= htmlspecialchars($log['error_message']) ?>">
                                            <span class="material-icons-round text-danger">info</span>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small">OK</span>
                                    <?php endif; ?>
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
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        display: flex;
        align-items: center;
        padding: 24px;
        border-radius: 16px;
        gap: 20px;
    }

    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .stat-icon.email { background: rgba(0, 217, 255, 0.1); color: #00d9ff; }
    .stat-icon.error { background: rgba(255, 78, 80, 0.1); color: #ff4e50; }

    .stat-value { font-size: 28px; font-weight: 700; margin: 0; color: #fff; }
    .stat-label { font-size: 14px; color: rgba(255,255,255,0.6); margin: 4px 0 0; }

    .date-time { display: flex; flex-direction: column; }
    .date { font-weight: 600; color: #fff; font-size: 14px; }
    .time { font-size: 12px; color: rgba(255,255,255,0.5); }

    .user-info { display: flex; flex-direction: column; }
    .user-name { font-weight: 600; color: #fff; }
    .user-email { font-size: 12px; color: rgba(255,255,255,0.5); }

    .subject-text { color: rgba(255,255,255,0.8); font-size: 14px; }

    .badge {
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .badge.success { background: rgba(0, 255, 136, 0.1); color: #00ff88; }
    .badge.danger { background: rgba(255, 78, 80, 0.1); color: #ff4e50; }
    .badge.warning { background: rgba(255, 193, 7, 0.1); color: #ffc107; }
    .badge.channel { background: rgba(255,255,255,0.05); color: rgba(255,255,255,0.7); }

    .dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }

    .error-trigger { cursor: help; }
    
    .data-table thead th {
        background: rgba(255,255,255,0.02);
        color: rgba(255,255,255,0.5);
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }
</style>

<?php include __DIR__ . '/../../layouts/admin_footer.php'; ?>
