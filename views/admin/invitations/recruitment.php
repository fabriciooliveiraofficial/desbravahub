<?php
/**
 * Gestão de Recrutamento - Elegance & Precision v1.0
 * Monitoramento de crescimento orgânico através de convites de membros.
 */
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    :root {
        --inv-primary: #334155;
        --inv-accent: <?= $tenant['accent_color'] ?? '#3b82f6' ?>;
        --inv-bg: transparent;
        --inv-card-bg: #ffffff;
        --inv-border: #f1f5f9;
        --inv-text-main: #334155;
        --inv-text-muted: #64748b;
        --inv-radius-lg: 32px;
        --inv-radius-md: 20px;
        --inv-shadow: 0 10px 30px -10px rgba(51, 65, 85, 0.06);
    }

    .inv-container {
        padding: 16px;
        width: 100%;
        min-height: 100vh;
        background: var(--inv-bg);
        font-family: 'Inter', sans-serif;
    }

    /* Unified Sticky Header & Tabs */
    .inv-header-sticky {
        position: sticky;
        top: 0;
        z-index: 100;
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(16px);
        margin: -16px -16px 32px -16px;
        padding: 20px 32px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid rgba(226, 232, 240, 0.8);
    }

    .inv-tabs {
        display: flex;
        gap: 8px;
        background: #f1f5f9;
        padding: 6px;
        border-radius: 100px;
    }

    .inv-tab-link {
        padding: 10px 24px;
        border-radius: 100px;
        font-size: 0.875rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--inv-text-muted);
    }

    .inv-tab-link.active {
        background: white;
        color: var(--inv-accent);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
    }

    /* Stats Dashboard */
    .inv-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin-bottom: 32px;
    }

    .inv-stat-card {
        background: var(--inv-card-bg);
        border: 1px solid var(--inv-border);
        border-radius: var(--inv-radius-md);
        padding: 24px;
        box-shadow: var(--inv-shadow);
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .inv-stat-value {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 2rem;
        font-weight: 800;
        color: var(--inv-text-main);
        line-height: 1;
    }

    .inv-stat-label {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--inv-text-muted);
    }

    /* Table Registry */
    .inv-registry-card {
        background: var(--inv-card-bg);
        border: 1px solid var(--inv-border);
        border-radius: var(--inv-radius-lg);
        overflow: hidden;
        box-shadow: var(--inv-shadow);
    }

    .inner-header {
        padding: 32px;
        border-bottom: 1px solid var(--inv-border);
    }

    .inner-header h2 {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-weight: 800;
        font-size: 1.25rem;
        color: var(--inv-text-main);
        margin: 0;
    }

    .inner-header p {
        margin: 4px 0 0 0;
        font-size: 0.875rem;
        color: var(--inv-text-muted);
    }

    .inv-table {
        width: 100%;
        border-collapse: collapse;
    }

    .inv-table th {
        text-align: left;
        padding: 20px 32px;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: var(--inv-text-muted);
        font-weight: 700;
        background: #fcfcfc;
        border-bottom: 1px solid var(--inv-border);
    }

    .inv-table td {
        padding: 24px 32px;
        border-bottom: 1px solid var(--inv-border);
        vertical-align: middle;
    }

    .inv-table tr:last-child td { border-bottom: none; }

    .inv-table tr:hover { background: #fafafa; }

    .inv-recruiter {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .inv-avatar-mini {
        width: 32px;
        height: 32px;
        border-radius: 10px;
        background: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        font-weight: 700;
        color: var(--inv-text-main);
    }

    .inv-status-pill {
        display: inline-flex;
        padding: 4px 12px;
        border-radius: 8px;
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .status-active { background: #f0fdf4; color: #166534; }
    .status-registered { background: #fffbeb; color: #92400e; }
    .status-clicked { background: #f0f9ff; color: #075985; }
    .status-pending { background: #f8fafc; color: #64748b; }

    .inv-xp-reward {
        display: flex;
        align-items: center;
        gap: 6px;
        color: #10b981;
        font-weight: 800;
        font-size: 0.9rem;
    }
</style>

<div class="inv-container">
    <!-- Header Fixo & Navigation -->
    <header class="inv-header-sticky">
        <nav class="inv-tabs">
            <a href="<?= base_url($tenant['slug'] . '/admin/convites') ?>" class="inv-tab-link">
                <span class="material-icons-round">stars</span>
                Liderança
            </a>
            <a href="<?= base_url($tenant['slug'] . '/admin/convites/membros') ?>" class="inv-tab-link">
                <span class="material-icons-round">groups</span>
                Membros
            </a>
            <a href="<?= base_url($tenant['slug'] . '/admin/convites/recrutamento') ?>" class="inv-tab-link active">
                <span class="material-icons-round">campaign</span>
                Recrutamento
            </a>
        </nav>
    </header>

    <div class="inv-content-wrapper">
        
        <!-- Dashboard de Métricas -->
        <?php 
        $counts = [
            'total' => count($invitations),
            'converted' => count(array_filter($invitations, fn($i) => in_array($i['status'], ['registered', 'active']))),
            'active' => count(array_filter($invitations, fn($i) => $i['status'] === 'active')),
            'pending' => count(array_filter($invitations, fn($i) => in_array($i['status'], ['pending', 'clicked'])))
        ];
        ?>
        <div class="inv-stats-grid">
            <div class="inv-stat-card">
                <span class="inv-stat-label">Total Enviados</span>
                <span class="inv-stat-value"><?= $counts['total'] ?></span>
            </div>
            <div class="inv-stat-card" style="border-bottom: 4px solid #10b981;">
                <span class="inv-stat-label">Membros (Conversão)</span>
                <span class="inv-stat-value" style="color: #10b981;"><?= $counts['converted'] ?></span>
            </div>
            <div class="inv-stat-card">
                <span class="inv-stat-label">Ativos (XP Pago)</span>
                <span class="inv-stat-value"><?= $counts['active'] ?></span>
            </div>
            <div class="inv-stat-card">
                <span class="inv-stat-label">Aguardando</span>
                <span class="inv-stat-value" style="color: #f59e0b;"><?= $counts['pending'] ?></span>
            </div>
        </div>

        <div class="inv-registry-card">
            <div class="inner-header">
                <h2>Gestão de Recrutamento</h2>
                <p>Monitoramento de convites enviados pelos membros do clube.</p>
            </div>

            <table class="inv-table">
                <thead>
                    <tr>
                        <th>Convidado</th>
                        <th>Recrutador</th>
                        <th>Data Envio</th>
                        <th>Status</th>
                        <th>Bônus XP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($invitations)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 80px 40px; color: var(--inv-text-muted);">
                                <span class="material-icons-round" style="font-size: 48px; display: block; margin-bottom: 16px; opacity: 0.3;">campaign</span>
                                <h4 style="margin: 0; font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 700;">Nenhum convite encontrado</h4>
                                <p style="margin-top: 4px; font-size: 0.85rem;">O recrutamento orgânico ainda não iniciou registros.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($invitations as $invite): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <?php if (!empty($invite['converted_avatar_url'])): ?>
                                            <img src="<?= htmlspecialchars($invite['converted_avatar_url']) ?>" class="inv-avatar-mini" style="object-fit: cover; border: 1px solid var(--inv-border);">
                                        <?php else: ?>
                                            <div class="inv-avatar-mini">
                                                <?= strtoupper(substr($invite['converted_name'] ?? $invite['email'], 0, 1)) ?>
                                            </div>
                                        <?php endif; ?>
                                        <div style="min-width: 0;">
                                            <div style="font-weight: 700; color: var(--inv-text-main); font-size: 0.95rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                <?= !empty($invite['converted_name']) ? htmlspecialchars($invite['converted_name']) : htmlspecialchars($invite['email']) ?>
                                            </div>
                                            <div style="font-size: 0.75rem; color: var(--inv-text-muted); margin-top: 2px;">
                                                <?= !empty($invite['converted_name']) ? htmlspecialchars($invite['email']) : 'Token: ' . substr($invite['token'], 0, 8) . '...' ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="inv-recruiter">
                                        <?php if (!empty($invite['referrer_avatar_url'])): ?>
                                            <img src="<?= htmlspecialchars($invite['referrer_avatar_url']) ?>" class="inv-avatar-mini" style="object-fit: cover; border: 1px solid var(--inv-border);">
                                        <?php else: ?>
                                            <div class="inv-avatar-mini">
                                                <?= strtoupper(substr($invite['referrer_name'], 0, 1)) ?>
                                            </div>
                                        <?php endif; ?>
                                        <div style="min-width: 0;">
                                            <div style="font-weight: 600; color: var(--inv-text-main); font-size: 0.85rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= htmlspecialchars($invite['referrer_name']) ?></div>
                                            <div style="font-size: 0.75rem; color: var(--inv-text-muted);"><?= htmlspecialchars($invite['referrer_email']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 600; color: var(--inv-text-main); font-size: 0.85rem;">
                                        <?= date('d/m/Y', strtotime($invite['created_at'])) ?>
                                    </div>
                                    <div style="font-size: 0.75rem; color: var(--inv-text-muted);">
                                        às <?= date('H:i', strtotime($invite['created_at'])) ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($invite['status'] === 'active'): ?>
                                        <span class="inv-status-pill status-active">Membro Ativo</span>
                                    <?php elseif ($invite['status'] === 'registered'): ?>
                                        <span class="inv-status-pill status-registered">Registrado</span>
                                    <?php elseif ($invite['status'] === 'clicked'): ?>
                                        <span class="inv-status-pill status-clicked">Link Aberto</span>
                                    <?php else: ?>
                                        <span class="inv-status-pill status-pending">Pendente</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($invite['xp_rewarded']): ?>
                                        <div class="inv-xp-reward">
                                            <span class="material-icons-round" style="font-size: 16px;">bolt</span>
                                            +<?= $invite['xp_rewarded'] ?> XP
                                        </div>
                                    <?php else: ?>
                                        <div style="color: var(--inv-text-muted); font-size: 0.75rem; font-weight: 600;">Aguardando...</div>
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

<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
<?php require BASE_PATH . '/views/admin/partials/footer.php'; ?>
