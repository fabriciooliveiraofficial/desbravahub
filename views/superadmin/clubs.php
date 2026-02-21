<?php
/**
 * Super Admin Clubs List View
 */
?>

<div class="sa-card">
    <table class="sa-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Slug</th>
                <th>Membros</th>
                <th>Data de Criação</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($clubs as $club): ?>
                <tr>
                    <td>#<?= $club['id'] ?></td>
                    <td style="color:white; font-weight:500;"><?= htmlspecialchars($club['name']) ?></td>
                    <td style="font-family: monospace;">/<?= htmlspecialchars($club['slug']) ?></td>
                    <td><?= $club['member_count'] ?> usuários</td>
                    <td><?= date('d/m/Y H:i', strtotime($club['created_at'])) ?></td>
                    <td>
                        <span class="sa-badge <?= $club['status'] == 'active' ? 'active' : 'pending' ?>">
                            <?= strtoupper($club['status']) ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
