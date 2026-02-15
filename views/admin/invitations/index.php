<?php
/**
 * Invitations - Master Design v2.0
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
    <!-- Tabs -->
    <div style="display: flex; gap: 0.5rem; background: var(--bg-card); padding: 0.25rem; border-radius: 9999px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);">
        <a href="<?= base_url($tenant['slug'] . '/admin/convites') ?>" 
           class="btn btn-sm" 
           style="border-radius: 9999px; <?= !str_contains($_SERVER['REQUEST_URI'], '/membros') ? 'background: var(--primary); color: white;' : 'background: transparent; color: var(--text-muted);' ?>">
            <span class="material-icons-round" style="font-size: 18px;">stars</span>
            Liderança
        </a>
        <a href="<?= base_url($tenant['slug'] . '/admin/convites/membros') ?>" 
           class="btn btn-sm"
           style="border-radius: 9999px; <?= str_contains($_SERVER['REQUEST_URI'], '/membros') ? 'background: var(--primary); color: white;' : 'background: transparent; color: var(--text-muted);' ?>">
            <span class="material-icons-round" style="font-size: 18px;">groups</span>
            Membros
        </a>
    </div>

    <!-- Actions -->
    <div class="header-actions">
        <a href="<?= base_url($tenant['slug'] . '/admin/convites/novo') ?>" class="btn btn-primary">
            <span class="material-icons-round">add</span>
            Novo Convite
        </a>
    </div>
</div>

<div class="invites-wrapper">
    
    <!-- Flash Notifications -->
    <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="dashboard-card" style="padding: 1rem; border-left: 4px solid #10b981; margin-bottom: 2rem; flex-direction: row; align-items: center; gap: 0.75rem;">
            <span class="material-icons-round" style="color: #10b981;">check_circle</span>
            <span style="font-weight: 600; color: #15803d;"><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="dashboard-card" style="padding: 1rem; border-left: 4px solid #ef4444; margin-bottom: 2rem; flex-direction: row; align-items: center; gap: 0.75rem;">
            <span class="material-icons-round" style="color: #ef4444;">error</span>
            <span style="font-weight: 600; color: #b91c1c;"><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <!-- Pending Section -->
    <div style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
        <span class="material-icons-round" style="color: #f59e0b; font-size: 1.5rem;">pending_actions</span>
        <h3 style="margin: 0; font-size: 1.125rem; font-weight: 700; color: var(--text-dark);">Convites Pendentes</h3>
        <span class="badge badge-warning"><?= count($pending) ?></span>
    </div>

    <?php if (empty($pending)): ?>
        <div class="dashboard-card" style="text-align: center; padding: 4rem 2rem; align-items: center;">
            <span class="material-icons-round" style="font-size: 4rem; color: var(--text-muted); opacity: 0.5; margin-bottom: 1rem;">mark_email_unread</span>
            <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--text-dark); margin-bottom: 0.5rem;">Nenhum convite pendente</h3>
            <p style="color: var(--text-muted);">Envie novos convites para expandir sua liderança</p>
        </div>
    <?php else: ?>
        <div class="dashboard-grid" style="grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));">
            <?php foreach ($pending as $invite): ?>
                <div class="dashboard-card">
                    <div class="dashboard-card-header" style="justify-content: space-between; padding: 1rem;">
                        <span class="badge badge-warning">Pendente</span>
                        <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">
                           Expira em <?= date('d/m', strtotime($invite['expires_at'])) ?>
                        </div>
                    </div>
                    
                    <div class="dashboard-card-body" style="padding: 1rem; display: flex; flex-direction: column; gap: 1rem;">
                        <div style="display: flex; gap: 1rem; align-items: center;">
                            <div style="width: 40px; height: 40px; border-radius: 9999px; background: linear-gradient(135deg, #f59e0b, #d97706); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                                <?= strtoupper(substr($invite['email'], 0, 1)) ?>
                            </div>
                            <div style="min-width: 0;">
                                <div style="font-weight: 600; color: var(--text-dark); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <?= htmlspecialchars($invite['email']) ?>
                                </div>
                                <div style="font-size: 0.8rem; color: var(--text-muted);">
                                    <?= htmlspecialchars($invite['name'] ?? 'Sem nome') ?>
                                </div>
                            </div>
                        </div>

                        <div style="background: var(--bg-hover); padding: 0.75rem; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                            <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; margin-bottom: 0.25rem;">Função</div>
                            <div style="font-weight: 600; color: var(--text-dark); display: flex; align-items: center; gap: 0.5rem;">
                                <span class="material-icons-round" style="font-size: 16px; color: var(--primary);">badge</span>
                                <?= $roles[$invite['role_name']] ?? ucfirst($invite['role_name']) ?>
                            </div>
                        </div>

                        <div style="display: flex; gap: 0.5rem; margin-top: auto;">
                            <form action="<?= base_url($tenant['slug'] . '/admin/convites/' . $invite['id'] . '/reenviar') ?>" method="POST" style="flex: 1;">
                                <button type="submit" class="btn btn-outline btn-sm" style="width: 100%; justify-content: center;">
                                    <span class="material-icons-round" style="font-size: 16px;">refresh</span>
                                    Reenviar
                                </button>
                            </form>
                            <form action="<?= base_url($tenant['slug'] . '/admin/convites/' . $invite['id'] . '/revogar') ?>" method="POST" style="flex: 1;" onsubmit="return confirm('Revogar este convite?')">
                                <button type="submit" class="btn btn-secondary btn-sm" style="width: 100%; color: #dc2626; border-color: #fecaca; background: #fef2f2; justify-content: center;">
                                    <span class="material-icons-round" style="font-size: 16px;">close</span>
                                    Revogar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Accepted Section -->
    <?php if (!empty($accepted)): ?>
        <div style="margin: 2rem 0 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
            <span class="material-icons-round" style="color: #10b981; font-size: 1.5rem;">check_circle</span>
            <h3 style="margin: 0; font-size: 1.125rem; font-weight: 700; color: var(--text-dark);">Convites Aceitos</h3>
            <span class="badge badge-success"><?= count($accepted) ?></span>
        </div>
        <div class="dashboard-card">
            <div class="table-container" style="border: none; box-shadow: none;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Pessoa</th>
                            <th>Função</th>
                            <th>Aceito em</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($accepted as $invite): ?>
                            <tr>
                                <td data-label="Pessoa">
                                    <div style="display: flex; align-items: center; gap: 1rem;">
                                        <div style="width: 40px; height: 40px; border-radius: 9999px; background: linear-gradient(135deg, #10b981, #059669); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                                            <?= strtoupper(substr($invite['email'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div style="font-weight: 600; color: var(--text-dark);"><?= htmlspecialchars($invite['email']) ?></div>
                                            <div style="font-size: 0.8rem; color: var(--text-muted);"><?= htmlspecialchars($invite['name'] ?? 'Membro') ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Função">
                                    <div style="display: flex; align-items: center; gap: 0.5rem; color: var(--text-dark); font-weight: 500;">
                                        <span class="material-icons-round" style="font-size: 18px; color: var(--primary);">badge</span>
                                        <?= $roles[$invite['role_name']] ?? ucfirst($invite['role_name']) ?>
                                    </div>
                                </td>
                                <td data-label="Aceito em">
                                    <div style="font-size: 0.875rem; color: var(--text-dark); font-weight: 500;">
                                        <?= date('d/m/Y', strtotime($invite['accepted_at'])) ?>
                                    </div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);">
                                        <?= date('H:i', strtotime($invite['accepted_at'])) ?>
                                    </div>
                                </td>
                                <td data-label="Status">
                                    <span class="badge badge-success">Ativo</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <!-- Expired Section -->
    <?php if (!empty($expired)): ?>
        <div style="margin: 2rem 0 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
            <span class="material-icons-round" style="color: #ef4444; font-size: 1.5rem;">history</span>
            <h3 style="margin: 0; font-size: 1.125rem; font-weight: 700; color: var(--text-dark);">Convites Expirados</h3>
            <span class="badge badge-danger"><?= count($expired) ?></span>
        </div>
        <div class="dashboard-grid" style="grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));">
            <?php foreach ($expired as $invite): ?>
                <div class="dashboard-card" style="opacity: 0.6;">
                    <div class="dashboard-card-body" style="padding: 1rem; display: flex; align-items: center; gap: 1rem;">
                        <div style="width: 40px; height: 40px; border-radius: 9999px; background: #e2e8f0; color: #64748b; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                            <?= strtoupper(substr($invite['email'], 0, 1)) ?>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-weight: 600; color: var(--text-muted); text-decoration: line-through;"><?= htmlspecialchars($invite['email']) ?></div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);">Expirou em <?= date('d/m/Y', strtotime($invite['expires_at'] ?? $invite['created_at'])) ?></div>
                        </div>
                        <div class="badge badge-danger">Expirado</div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
<?php require BASE_PATH . '/views/admin/partials/footer.php'; ?>
