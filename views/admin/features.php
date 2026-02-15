<div class="dashboard-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Flag</th>
                <th>Descrição</th>
                <th>Global</th>
                <th>Rollout</th>
                <th>Override</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($flags as $flag): ?>
                <tr>
                    <td data-label="Flag">
                        <code style="background: rgba(0,0,0,0.3); padding: 4px 8px; border-radius: 4px;">
                            <?= htmlspecialchars($flag['key']) ?>
                        </code>
                    </td>
                    <td data-label="Descrição">
                        <strong><?= htmlspecialchars($flag['name']) ?></strong>
                        <?php if ($flag['description']): ?>
                            <p style="color: #888; font-size: 0.85rem; margin-top: 4px;">
                                <?= htmlspecialchars($flag['description']) ?>
                            </p>
                        <?php endif; ?>
                    </td>
                    <td data-label="Global">
                        <span class="badge <?= $flag['is_enabled'] ? 'badge-success' : 'badge-danger' ?>">
                            <?= $flag['is_enabled'] ? 'ON' : 'OFF' ?>
                        </span>
                    </td>
                    <td data-label="Rollout">
                        <?= $flag['rollout_percentage'] ?>%
                    </td>
                    <td data-label="Override">
                        <?php if ($flag['override_id']): ?>
                            <span class="badge <?= $flag['tenant_enabled'] ? 'badge-success' : 'badge-danger' ?>">
                                <?= $flag['tenant_enabled'] ? 'Ativo' : 'Inativo' ?>
                            </span>
                        <?php else: ?>
                            <span style="color: #666;">Herdado</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (empty($flags)): ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 40px; color: #888;">
                        Nenhuma feature flag configurada
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>