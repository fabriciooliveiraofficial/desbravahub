<?php
/**
 * Recruitment Invitations - Admin View
 * Shows all invitations sent by members.
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
           style="border-radius: 9999px; background: transparent; color: var(--text-muted);">
            <span class="material-icons-round" style="font-size: 18px;">stars</span>
            Liderança
        </a>
        <a href="<?= base_url($tenant['slug'] . '/admin/convites/membros') ?>" 
           class="btn btn-sm"
           style="border-radius: 9999px; background: transparent; color: var(--text-muted);">
            <span class="material-icons-round" style="font-size: 18px;">groups</span>
            Membros
        </a>
        <a href="<?= base_url($tenant['slug'] . '/admin/convites/recrutamento') ?>" 
           class="btn btn-sm"
           style="border-radius: 9999px; background: var(--primary); color: white;">
            <span class="material-icons-round" style="font-size: 18px;">campaign</span>
            Recrutamento
        </a>
    </div>
</div>

<div class="invites-wrapper">
    
    <div style="margin-bottom: 2rem;">
        <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--text-dark); margin-bottom: 0.5rem;">Gestão de Recrutamento</h2>
        <p style="color: var(--text-muted);">Convites enviados pelos desbravadores para trazer novos membros.</p>
    </div>

    <!-- Stats Summary -->
    <div class="dashboard-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 2rem;">
        <?php 
        $counts = [
            'total' => count($invitations),
            'converted' => count(array_filter($invitations, fn($i) => in_array($i['status'], ['registered', 'active']))),
            'active' => count(array_filter($invitations, fn($i) => $i['status'] === 'active')),
            'pending' => count(array_filter($invitations, fn($i) => in_array($i['status'], ['pending', 'clicked'])))
        ];
        ?>
        <div class="dashboard-card" style="padding: 1.5rem; align-items: center; text-align: center;">
            <div style="font-size: 2rem; font-weight: 800; color: var(--primary);"><?= $counts['total'] ?></div>
            <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Total Enviados</div>
        </div>
        <div class="dashboard-card" style="padding: 1.5rem; align-items: center; text-align: center;">
            <div style="font-size: 2rem; font-weight: 800; color: var(--accent-green);"><?= $counts['converted'] ?></div>
            <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Membros (Conversão)</div>
        </div>
        <div class="dashboard-card" style="padding: 1.5rem; align-items: center; text-align: center;">
            <div style="font-size: 2rem; font-weight: 800; color: var(--primary);"><?= $counts['active'] ?></div>
            <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Ativos (XP Pago)</div>
        </div>
        <div class="dashboard-card" style="padding: 1.5rem; align-items: center; text-align: center;">
            <div style="font-size: 2rem; font-weight: 800; color: var(--accent-warning);"><?= $counts['pending'] ?></div>
            <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Aguardando</div>
        </div>
    </div>

    <div class="dashboard-card" style="overflow: hidden;">
        <div class="table-container" style="border: none; box-shadow: none;">
            <table class="data-table">
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
                            <td colspan="5" style="text-align: center; padding: 4rem; color: var(--text-muted);">
                                <span class="material-icons-round" style="font-size: 3rem; display: block; margin-bottom: 1rem; opacity: 0.3;">campaign</span>
                                Nenhum convite de recrutamento encontrado.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($invitations as $invite): ?>
                            <tr>
                                <td data-label="Convidado">
                                    <div style="font-weight: 700; color: var(--text-dark);">
                                        <?= !empty($invite['converted_name']) ? htmlspecialchars($invite['converted_name']) : htmlspecialchars($invite['email']) ?>
                                    </div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);">
                                        <?= !empty($invite['converted_name']) ? htmlspecialchars($invite['email']) : 'Token: ' . substr($invite['token'], 0, 8) . '...' ?>
                                    </div>
                                </td>
                                <td data-label="Recrutador">
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--bg-hover); display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 800; color: var(--primary); border: 1px solid var(--border-color);">
                                            <?= strtoupper(substr($invite['referrer_name'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div style="font-weight: 600; color: var(--text-dark); font-size: 0.9rem;"><?= htmlspecialchars($invite['referrer_name']) ?></div>
                                            <div style="font-size: 0.75rem; color: var(--text-muted);"><?= htmlspecialchars($invite['referrer_email']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Data Envio">
                                    <div style="font-size: 0.9rem; color: var(--text-dark);"><?= date('d/m/Y', strtotime($invite['created_at'])) ?></div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);"><?= date('H:i', strtotime($invite['created_at'])) ?></div>
                                </td>
                                <td data-label="Status">
                                    <?php if ($invite['status'] === 'active'): ?>
                                        <span class="badge badge-success">Membro Ativo</span>
                                    <?php elseif ($invite['status'] === 'registered'): ?>
                                        <span class="badge badge-warning">Registrado (Inativo)</span>
                                    <?php elseif ($invite['status'] === 'clicked'): ?>
                                        <span class="badge" style="background: #e0f2fe; color: #0369a1;">Link Aberto</span>
                                    <?php else: ?>
                                        <span class="badge badge-muted">Pendente</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Bônus XP">
                                    <?php if ($invite['xp_rewarded']): ?>
                                        <div style="color: var(--accent-green); font-weight: 800; display: flex; align-items: center; gap: 0.25rem;">
                                            <span class="material-icons-round" style="font-size: 16px;">bolt</span>
                                            +<?= $invite['xp_rewarded'] ?> XP
                                        </div>
                                    <?php else: ?>
                                        <div style="color: var(--text-muted); font-size: 0.8rem;">Aguardando...</div>
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
