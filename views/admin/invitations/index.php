<?php
/**
 * Gestão de Convites (Liderança) - Elegance & Precision v1.0
 * Um design refinado para processos de convite formais.
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

    .btn-inv-primary {
        background: var(--inv-accent);
        color: white;
        padding: 12px 24px;
        border-radius: 16px;
        font-weight: 600;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s;
        box-shadow: 0 8px 20px -6px rgba(59, 130, 246, 0.35);
    }

    .btn-inv-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 24px -6px rgba(59, 130, 246, 0.45);
        filter: brightness(1.1);
    }

    /* Section Headers */
    .inv-section-title {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 24px;
        margin-top: 48px;
    }

    .inv-section-title:first-of-type { margin-top: 0; }

    .inv-section-title h3 {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-weight: 800;
        font-size: 1.25rem;
        color: var(--inv-text-main);
        margin: 0;
    }

    .inv-badge-count {
        background: white;
        padding: 4px 12px;
        border-radius: 100px;
        font-size: 0.75rem;
        font-weight: 800;
        color: var(--inv-text-muted);
        border: 1px solid var(--inv-border);
    }

    /* Envelope Cards (Pending) */
    .inv-grid-pending {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
        gap: 24px;
    }

    .inv-envelope-card {
        background: var(--inv-card-bg);
        border: 1px solid var(--inv-border);
        border-radius: var(--inv-radius-md);
        padding: 32px;
        position: relative;
        overflow: hidden;
        box-shadow: var(--inv-shadow);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .inv-envelope-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 6px;
        background: linear-gradient(90deg, #f59e0b, #fbbf24);
    }

    .inv-envelope-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.08);
        border-color: #fbbf2440;
    }

    .inv-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 24px;
    }

    .inv-expiry-tag {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--inv-text-muted);
        font-weight: 700;
        background: #fef3c7;
        padding: 4px 10px;
        border-radius: 8px;
        color: #92400e;
    }

    .inv-user-profile {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
    }

    .inv-avatar {
        width: 52px;
        height: 52px;
        border-radius: 18px;
        background: #fffbeb;
        border: 1px solid #fef3c7;
        color: #d97706;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 1.25rem;
    }

    .inv-user-info h4 {
        margin: 0;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-weight: 700;
        font-size: 1.05rem;
        color: var(--inv-text-main);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 220px;
    }

    .inv-user-info span {
        font-size: 0.85rem;
        color: var(--inv-text-muted);
    }

    .inv-role-strip {
        background: #f8fafc;
        border: 1px solid var(--inv-border);
        border-radius: 14px;
        padding: 12px 16px;
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 24px;
    }

    .inv-role-strip .material-icons-round {
        font-size: 18px;
        color: var(--inv-accent);
    }

    .inv-role-name {
        font-size: 0.875rem;
        font-weight: 700;
        color: var(--inv-text-main);
    }

    .inv-card-actions {
        display: flex;
        gap: 12px;
    }

    .btn-inv-ghost {
        flex: 1;
        padding: 10px;
        border-radius: 12px;
        border: 1px solid var(--inv-border);
        background: white;
        color: var(--inv-text-main);
        font-size: 0.8rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }

    .btn-inv-ghost:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }

    .btn-inv-ghost.revoke:hover {
        color: #e11d48;
        background: #fff1f2;
        border-color: #fecdd3;
    }

    /* Table Registry (Accepted) */
    .inv-registry-card {
        background: var(--inv-card-bg);
        border: 1px solid var(--inv-border);
        border-radius: var(--inv-radius-lg);
        overflow: hidden;
        box-shadow: var(--inv-shadow);
    }

    .inv-table {
        width: 100%;
        border-collapse: collapse;
    }

    .inv-table th {
        text-align: left;
        padding: 20px 24px;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: var(--inv-text-muted);
        font-weight: 700;
        border-bottom: 1px solid var(--inv-border);
    }

    .inv-table td {
        padding: 24px;
        border-bottom: 1px solid var(--inv-border);
        vertical-align: middle;
    }

    .inv-table tr:last-child td { border-bottom: none; }

    .inv-table tr:hover { background: #fafafa; }

    /* Expired Cards */
    .inv-grid-expired {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
    }

    .inv-ghost-card {
        padding: 20px;
        border-radius: 16px;
        background: rgba(241, 245, 249, 0.5);
        border: 1px dashed #e2e8f0;
        display: flex;
        align-items: center;
        gap: 16px;
        opacity: 0.7;
    }

    .inv-ghost-card .inv-avatar {
        background: #e2e8f0;
        color: #94a3b8;
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }
</style>

<div class="inv-container">
    <!-- Header Fixo & Navigation -->
    <header class="inv-header-sticky">
        <nav class="inv-tabs">
            <a href="<?= base_url($tenant['slug'] . '/admin/convites') ?>" class="inv-tab-link active">
                <span class="material-icons-round">stars</span>
                Liderança
            </a>
            <a href="<?= base_url($tenant['slug'] . '/admin/convites/membros') ?>" class="inv-tab-link">
                <span class="material-icons-round">groups</span>
                Membros
            </a>
            <a href="<?= base_url($tenant['slug'] . '/admin/convites/recrutamento') ?>" class="inv-tab-link">
                <span class="material-icons-round">campaign</span>
                Recrutamento
            </a>
        </nav>

        <a href="<?= base_url($tenant['slug'] . '/admin/convites/novo') ?>" class="btn-inv-primary">
            <span class="material-icons-round">add</span>
            Novo Convite
        </a>
    </header>

    <div class="inv-content-wrapper">
        <!-- Notificações -->
        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div style="background: #ecfdf5; border: 1px solid #10b98140; padding: 16px 24px; border-radius: 20px; margin-bottom: 32px; display: flex; align-items: center; gap: 12px;">
                <span class="material-icons-round" style="color: #10b981;">check_circle</span>
                <span style="font-weight: 600; color: #065f46;"><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div style="background: #fff1f2; border: 1px solid #ef444440; padding: 16px 24px; border-radius: 20px; margin-bottom: 32px; display: flex; align-items: center; gap: 12px;">
                <span class="material-icons-round" style="color: #ef4444;">error</span>
                <span style="font-weight: 600; color: #991b1b;"><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <!-- Seção: Pendentes -->
        <div class="inv-section-title">
            <span class="material-icons-round" style="color: #f59e0b;">mail</span>
            <h3>Convites Pendentes</h3>
            <span class="inv-badge-count"><?= count($pending) ?></span>
        </div>

        <?php if (empty($pending)): ?>
            <div class="inv-registry-card" style="padding: 80px 40px; text-align: center;">
                <span class="material-icons-round" style="font-size: 48px; color: #e2e8f0; margin-bottom: 16px;">mark_email_unread</span>
                <h4 style="font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 700; color: var(--inv-text-main);">Caixa de Saída Vazia</h4>
                <p style="color: var(--inv-text-muted); max-width: 320px; margin: 8px auto 24px auto;">Não há convites aguardando resposta neste momento.</p>
                <a href="<?= base_url($tenant['slug'] . '/admin/convites/novo') ?>" class="btn-inv-primary" style="display: inline-flex;">Enviar Convite</a>
            </div>
        <?php else: ?>
            <div class="inv-grid-pending">
                <?php foreach ($pending as $invite): ?>
                    <div class="inv-envelope-card">
                        <div class="inv-card-header">
                            <span style="font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800; font-size: 0.75rem; color: #fbbf24;">DH-INV-<?= str_pad($invite['id'], 3, '0', STR_PAD_LEFT) ?></span>
                            <div class="inv-expiry-tag">
                                Expira <?= date('d/m', strtotime($invite['expires_at'])) ?>
                            </div>
                        </div>

                        <div class="inv-user-profile">
                            <?php if (!empty($invite['target_avatar_url'])): ?>
                                <img src="<?= htmlspecialchars($invite['target_avatar_url']) ?>" class="inv-avatar" style="object-fit: cover;">
                            <?php else: ?>
                                <div class="inv-avatar">
                                    <?= strtoupper(substr($invite['target_real_name'] ?? $invite['name'] ?? $invite['email'], 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                            <div class="inv-user-info">
                                <h4><?= htmlspecialchars($invite['email']) ?></h4>
                                <span><?= htmlspecialchars($invite['target_real_name'] ?? $invite['name'] ?? 'Pessoa convidada') ?></span>
                            </div>
                        </div>

                        <div class="inv-role-strip">
                            <span class="material-icons-round">verified_user</span>
                            <span class="inv-role-name"><?= $roles[$invite['role_name']] ?? ucfirst($invite['role_name']) ?></span>
                        </div>

                        <div class="inv-card-actions">
                            <form action="<?= base_url($tenant['slug'] . '/admin/convites/' . $invite['id'] . '/reenviar') ?>" method="POST" style="flex: 1;">
                                <button type="submit" class="btn-inv-ghost">
                                    <span class="material-icons-round" style="font-size: 16px;">refresh</span>
                                    Reenviar
                                </button>
                            </form>
                            <form action="<?= base_url($tenant['slug'] . '/admin/convites/' . $invite['id'] . '/revogar') ?>" method="POST" style="flex: 1;" onsubmit="return confirm('Revogar este convite?')">
                                <button type="submit" class="btn-inv-ghost revoke">
                                    <span class="material-icons-round" style="font-size: 16px;">close</span>
                                    Revogar
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Seção: Aceitos -->
        <?php if (!empty($accepted)): ?>
            <div class="inv-section-title">
                <span class="material-icons-round" style="color: #10b981;">how_to_reg</span>
                <h3>Histórico de Aceite</h3>
                <span class="inv-badge-count"><?= count($accepted) ?></span>
            </div>
            
            <div class="inv-registry-card">
                <table class="inv-table">
                    <thead>
                        <tr>
                            <th>Identidade</th>
                            <th>Nível de Acesso</th>
                            <th>Data de Entrada</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($accepted as $invite): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 16px;">
                                        <?php if (!empty($invite['target_avatar_url'])): ?>
                                            <img src="<?= htmlspecialchars($invite['target_avatar_url']) ?>" class="inv-avatar" style="width: 40px; height: 40px; object-fit: cover; border-radius: 12px; border: 1px solid var(--inv-border);">
                                        <?php else: ?>
                                            <div class="inv-avatar" style="width: 40px; height: 40px; font-size: 1rem; background: #ecfdf5; border-color: #d1fae5; color: #059669;">
                                                <?= strtoupper(substr($invite['target_real_name'] ?? $invite['name'] ?? $invite['email'], 0, 1)) ?>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div style="font-weight: 700; color: var(--inv-text-main); font-size: 0.95rem;"><?= htmlspecialchars($invite['email']) ?></div>
                                            <div style="font-size: 0.8rem; color: var(--inv-text-muted);"><?= htmlspecialchars($invite['target_real_name'] ?? $invite['name'] ?? 'Membro Ativo') ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 8px; font-weight: 600; color: var(--inv-text-main); font-size: 0.9rem;">
                                        <span class="material-icons-round" style="font-size: 18px; color: var(--inv-accent);">security</span>
                                        <?= $roles[$invite['role_name']] ?? ucfirst($invite['role_name']) ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 600; color: var(--inv-text-main); font-size: 0.9rem;">
                                        <?= date('d/m/Y', strtotime($invite['accepted_at'])) ?>
                                    </div>
                                    <div style="font-size: 0.75rem; color: var(--inv-text-muted);">
                                        às <?= date('H:i', strtotime($invite['accepted_at'])) ?>
                                    </div>
                                </td>
                                <td>
                                    <span style="padding: 4px 12px; border-radius: 8px; background: #f0fdf4; color: #166534; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em;">Integrado</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- Seção: Expirados -->
        <?php if (!empty($expired)): ?>
            <div class="inv-section-title">
                <span class="material-icons-round" style="color: #94a3b8;">history</span>
                <h3>Registros Expirados</h3>
                <span class="inv-badge-count"><?= count($expired) ?></span>
            </div>
            
            <div class="inv-grid-expired">
                <?php foreach ($expired as $invite): ?>
                    <div class="inv-ghost-card">
                        <div class="inv-avatar">
                            <?= strtoupper(substr($invite['email'], 0, 1)) ?>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-weight: 600; color: var(--inv-text-muted); font-size: 0.85rem; text-decoration: line-through; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= htmlspecialchars($invite['email']) ?></div>
                            <div style="font-size: 0.75rem; color: var(--inv-text-muted);">Esgotado em <?= date('d/m/Y', strtotime($invite['expires_at'] ?? $invite['created_at'])) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
<?php require BASE_PATH . '/views/admin/partials/footer.php'; ?>
