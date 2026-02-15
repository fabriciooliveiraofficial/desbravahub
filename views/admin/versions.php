<div class="stats-grid" style="margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-icon">📦</div>
        <div class="stat-content">
            <span class="stat-value"><?= htmlspecialchars($currentVersion['version'] ?? '1.0.0') ?></span>
            <span class="stat-label">Versão Atual</span>
        </div>
    </div>
</div>

<div class="dashboard-card">
    <h3 style="margin-bottom: 20px;">Histórico de Versões</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>Versão</th>
                <th>Status</th>
                <th>Lançamento</th>
                <th>Changelog</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($versions as $version): ?>
                <tr>
                    <td data-label="Versão">
                        <strong><?= htmlspecialchars($version['version']) ?></strong>
                    </td>
                    <td data-label="Status">
                        <?php
                        $statusClass = match ($version['status']) {
                            'stable' => 'badge-success',
                            'beta' => 'badge-info',
                            'canary' => 'badge-warning',
                            default => 'badge-danger'
                        };
                        ?>
                        <span class="badge <?= $statusClass ?>"><?= ucfirst($version['status']) ?></span>
                    </td>
                    <td data-label="Lançamento">
                        <?= $version['released_at'] ? date('d/m/Y', strtotime($version['released_at'])) : '-' ?>
                    </td>
                    <td data-label="Changelog" style="max-width: 300px; overflow: hidden; text-overflow: ellipsis;">
                        <?= htmlspecialchars($version['changelog'] ?? '-') ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>