<?php
/**
 * Invitations - Vibrant Light Edition v1.0
 * High Contrast, Clean Layout
 */
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    :root {
        --font-primary: 'Inter', sans-serif;
        --bg-body: #f1f5f9;
        --bg-card: #ffffff;
        --text-dark: #0f172a;
        --text-medium: #334155;
        --text-light: #475569;
        
        --accent-primary: #06b6d4;
        --accent-hover: #0891b2;
        --border-color: #cbd5e1;
    }

    body {
        background-color: var(--bg-body) !important;
        font-family: var(--font-primary);
        color: var(--text-dark);
    }

    .invites-wrapper {
        max-width: 1200px;
        margin: 0 auto;
        padding-bottom: 60px;
        animation: fadeIn 0.4s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Tabs Navigation */
    .tab-nav {
        display: flex;
        gap: 12px;
        margin-bottom: 32px;
        background: white;
        padding: 8px;
        border-radius: 100px;
        width: fit-content;
        border: 2px solid #e2e8f0;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
    }

    .tab-link {
        padding: 10px 24px;
        border-radius: 100px;
        text-decoration: none;
        font-weight: 700;
        font-size: 0.95rem;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--text-light);
    }

    .tab-link.active {
        background: var(--accent-primary);
        color: white;
        box-shadow: 0 4px 12px rgba(6, 182, 212, 0.3);
    }

    .tab-link:not(.active):hover {
        background: #f1f5f9;
        color: var(--text-dark);
    }

    /* Top Header */
    .header-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 32px;
        flex-wrap: wrap;
        gap: 20px;
    }

    .header-row h2 {
        font-size: 1.25rem;
        font-weight: 700;
        margin: 0;
        color: var(--text-dark);
    }
    
    .header-info p {
        margin: 4px 0 0;
        color: var(--text-medium);
        font-weight: 500;
    }

    .btn-action {
        background: var(--accent-primary);
        color: white;
        padding: 12px 28px;
        border-radius: 100px;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
        box-shadow: 0 4px 10px rgba(6, 182, 212, 0.3);
        border: none;
    }
    .btn-action:hover {
        background: var(--accent-hover);
        transform: translateY(-2px);
        box-shadow: 0 8px 15px rgba(6, 182, 212, 0.4);
    }

    /* Sections */
    .section-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 40px 0 20px;
        color: var(--text-dark);
    }

    .section-header h2 {
        font-size: 1.25rem;
        font-weight: 800;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .count-badge {
        background: #e2e8f0;
        color: var(--text-medium);
        font-size: 0.8rem;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 100px;
    }

    /* Cards Grid */
    .invites-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 20px;
    }

    .invite-card {
        background: white;
        border-radius: 20px;
        border: 2px solid var(--border-color);
        padding: 24px;
        transition: all 0.2s;
        display: flex;
        flex-direction: column;
        gap: 16px;
        position: relative;
        overflow: hidden;
    }

    .invite-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px -10px rgba(0,0,0,0.1);
        border-color: #94a3b8;
    }

    .card-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .user-avatar {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        background: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        color: var(--accent-primary);
        font-weight: 800;
        border: 2px solid #e2e8f0;
    }

    .user-details {
        display: flex;
        flex-direction: column;
    }

    .user-email {
        font-weight: 700;
        font-size: 1rem;
        color: var(--text-dark);
        margin: 0;
    }

    .user-name {
        font-size: 0.85rem;
        color: var(--text-medium);
        font-weight: 500;
    }

    .role-tag {
        padding: 4px 12px;
        border-radius: 100px;
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        background: #f1f5f9;
        color: var(--text-medium);
        border: 1px solid #e2e8f0;
    }

    .card-meta {
        background: #f8fafc;
        padding: 12px 16px;
        border-radius: 12px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        font-size: 0.85rem;
        color: var(--text-light);
        font-weight: 600;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .card-actions {
        display: flex;
        gap: 10px;
        margin-top:auto;
    }

    .btn-sm {
        flex: 1;
        padding: 10px;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background: white;
        font-weight: 700;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        text-decoration: none;
        color: var(--text-medium);
    }

    .btn-sm:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: var(--text-dark);
    }

    .btn-revoke:hover {
        background: #fef2f2;
        border-color: #fecaca;
        color: #dc2626;
    }

    /* Status Styles */
    .status-ribbon {
        position: absolute;
        top: 0;
        right: 0;
        padding: 4px 16px;
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        border-bottom-left-radius: 12px;
    }

    .status-pending { background: #fff7ed; color: #c2410c; }
    .status-accepted { background: #dcfce7; color: #15803d; }
    .status-expired { background: #fee2e2; color: #b91c1c; opacity: 0.7; }

    /* Empty State */
    .empty-box {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 20px;
        border: 2px dashed #cbd5e1;
        color: var(--text-medium);
    }

    .empty-illust {
        font-size: 4rem;
        margin-bottom: 16px;
    }

    /* Flash Messages */
    .flash-box {
        padding: 16px 24px;
        border-radius: 16px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 12px;
        font-weight: 600;
    }
    .flash-success { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
    .flash-error { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }

</style>

<div class="invites-wrapper">
    
    <!-- Flash Notifications -->
    <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="flash-box flash-success">
            <span class="material-icons-round">check_circle</span>
            <?= htmlspecialchars($_SESSION['flash_success']) ?>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="flash-box flash-error">
            <span class="material-icons-round">error</span>
            <?= htmlspecialchars($_SESSION['flash_error']) ?>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <!-- Navigation Tabs -->
    <div class="tab-nav">
        <a href="<?= base_url($tenant['slug'] . '/admin/convites') ?>" class="tab-link active">
            <span class="material-icons-round">stars</span>
            Liderança
        </a>
        <a href="<?= base_url($tenant['slug'] . '/admin/convites/membros') ?>" class="tab-link">
            <span class="material-icons-round">groups</span>
            Membros
        </a>
    </div>

    <!-- Header -->
    <div class="page-toolbar">
        <div class="page-info">
            <h2 class="header-title">Convites de Liderança</h2>
            <p class="text-muted">Gerencie o acesso de diretores, conselheiros e instrutores</p>
        </div>
        <div class="actions-group">
            <a href="<?= base_url($tenant['slug'] . '/admin/convites/novo') ?>" class="btn-toolbar primary">
                <span class="material-icons-round">add</span> Novo Convite
            </a>
        </div>
    </div>

    <!-- Migration Alert (Internal only) -->
    <?php if (!empty($migrationNeeded) && $migrationNeeded): ?>
        <div class="flash-box flash-error" style="flex-direction: column; align-items: flex-start;">
            <div style="display:flex; align-items:center; gap:8px;">
                <span class="material-icons-round">warning</span>
                <strong>Migration necessária</strong>
            </div>
            <code style="background: rgba(0,0,0,0.05); padding: 8px 12px; border-radius: 8px; margin-top: 10px; display: block; font-size: 0.9rem; width: 100%;">
                <?= rtrim($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'], '/') ?>/migrate-email-system.php
            </code>
        </div>
    <?php endif; ?>

    <!-- Pending Section -->
    <div class="section-header">
        <h2><span style="color: #f59e0b;">⏳</span> Convites Pendentes</h2>
        <span class="count-badge"><?= count($pending) ?></span>
    </div>

    <?php if (empty($pending)): ?>
        <div class="empty-box">
            <div class="empty-illust">✉️</div>
            <p style="font-size: 1.1rem; font-weight: 700;">Nenhum convite pendente</p>
            <p>Envie novos convites para expandir sua liderança</p>
        </div>
    <?php else: ?>
        <div class="invites-grid">
            <?php foreach ($pending as $invite): ?>
                <div class="invite-card">
                    <div class="status-ribbon status-pending">Pendente</div>
                    <div class="card-top">
                        <div class="user-info">
                            <div class="user-avatar"><?= strtoupper(substr($invite['email'], 0, 1)) ?></div>
                            <div class="user-details">
                                <span class="user-email"><?= htmlspecialchars($invite['email']) ?></span>
                                <span class="user-name"><?= htmlspecialchars($invite['name'] ?? 'Pessoa convidada') ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div style="display:flex; gap:8px;">
                        <span class="role-tag">
                            <?= $roles[$invite['role_name']] ?? $invite['role_name'] ?>
                        </span>
                    </div>

                    <div class="card-meta">
                        <div class="meta-item">
                            <span class="material-icons-round" style="font-size: 16px;">event</span>
                            Enviado em <?= date('d/m/Y', strtotime($invite['created_at'])) ?>
                        </div>
                        <div class="meta-item" style="color: #c2410c;">
                            <span class="material-icons-round" style="font-size: 16px;">timer</span>
                            Expira em <?= date('d/m/Y', strtotime($invite['expires_at'])) ?>
                        </div>
                    </div>

                    <div class="card-actions">
                        <form action="<?= base_url($tenant['slug'] . '/admin/convites/' . $invite['id'] . '/reenviar') ?>" method="POST" style="flex:1;">
                            <button type="submit" class="btn-sm">
                                <span class="material-icons-round" style="font-size: 18px; color: var(--accent-primary);">refresh</span>
                                Reenviar
                            </button>
                        </form>
                        <form action="<?= base_url($tenant['slug'] . '/admin/convites/' . $invite['id'] . '/revogar') ?>" method="POST" style="flex:1;" onsubmit="return confirm('Revogar este convite?')">
                            <button type="submit" class="btn-sm btn-revoke">
                                <span class="material-icons-round" style="font-size: 18px;">delete_outline</span>
                                Revogar
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Accepted Section -->
    <?php if (!empty($accepted)): ?>
        <div class="section-header">
            <h2><span style="color: #10b981;">✅</span> Convites Aceitos</h2>
            <span class="count-badge"><?= count($accepted) ?></span>
        </div>
        <div class="invites-grid">
            <?php foreach ($accepted as $invite): ?>
                <div class="invite-card">
                    <div class="status-ribbon status-accepted">Ativado</div>
                    <div class="card-top">
                        <div class="user-info">
                            <div class="user-avatar" style="color: #10b981;"><?= strtoupper(substr($invite['email'], 0, 1)) ?></div>
                            <div class="user-details">
                                <span class="user-email"><?= htmlspecialchars($invite['email']) ?></span>
                                <span class="user-name"><?= htmlspecialchars($invite['name'] ?? 'Membro ativo') ?></span>
                            </div>
                        </div>
                    </div>

                    <div style="display:flex; gap:8px;">
                        <span class="role-tag">
                            <?= $roles[$invite['role_name']] ?? $invite['role_name'] ?>
                        </span>
                    </div>

                    <div class="card-meta">
                        <div class="meta-item">
                            <span class="material-icons-round" style="font-size: 16px;">verified</span>
                            Aceito em <?= date('d/m/Y H:i', strtotime($invite['accepted_at'])) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Expired Section -->
    <?php if (!empty($expired)): ?>
        <div class="section-header">
            <h2><span style="color: #ef4444;">❌</span> Convites Expirados</h2>
            <span class="count-badge"><?= count($expired) ?></span>
        </div>
        <div class="invites-grid">
            <?php foreach ($expired as $invite): ?>
                <div class="invite-card" style="opacity: 0.8;">
                    <div class="status-ribbon status-expired">Expirado</div>
                    <div class="card-top">
                        <div class="user-info">
                            <div class="user-avatar" style="color: #ef4444;"><?= strtoupper(substr($invite['email'], 0, 1)) ?></div>
                            <div class="user-details">
                                <span class="user-email"><?= htmlspecialchars($invite['email']) ?></span>
                                <span class="user-name"><?= htmlspecialchars($invite['name'] ?? 'Não aceito') ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="card-meta">
                        <div class="meta-item">
                            <span class="material-icons-round" style="font-size: 16px;">history</span>
                            Expirou em <?= date('d/m/Y', strtotime($invite['expires_at'] ?? $invite['created_at'])) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
<?php require BASE_PATH . '/views/admin/partials/footer.php'; ?>
