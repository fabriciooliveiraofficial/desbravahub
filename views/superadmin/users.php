<?php
/**
 * Super Admin Users List View
 */
?>

<div class="sa-card">
    <table class="sa-table">
        <thead>
            <tr>
                <th>Nome / Email</th>
                <th>Clube</th>
                <th>Função</th>
                <th>XP Nível</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td>
                        <div style="font-weight: 500; color: white;"><?= htmlspecialchars($u['name']) ?></div>
                        <div style="font-size: 0.8rem; color: #64748b;"><?= htmlspecialchars($u['email']) ?></div>
                    </td>
                    <td><?= htmlspecialchars($u['tenant_name']) ?></td>
                    <td>
                        <span class="sa-badge" style="background: rgba(255,255,255,0.05); color:#cbd5e1;">
                            <?= htmlspecialchars($u['role_name']) ?>
                        </span>
                        <?php if ($u['is_superadmin']): ?>
                            <span class="sa-badge" style="background: rgba(139, 92, 246, 0.2); color:#a78bfa; margin-left:4px;">Super</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-family: monospace;"><?= $u['xp_points'] ?> XP</td>
                    <td>
                        <span class="sa-badge <?= $u['status'] == 'active' ? 'active' : 'pending' ?>">
                            <?= strtoupper($u['status']) ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
