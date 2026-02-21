<?php
/**
 * Admin Events List View
 */
?>
<div class="dashboard-card">
    <header class="dashboard-card-header">
        <span class="material-icons-round" style="color: #6366f1;">event</span>
        <h3>Gerenciar Eventos</h3>
        <a href="<?= base_url($tenant['slug'] . '/admin/eventos/novo') ?>" class="btn btn-primary" style="margin-left: auto;">
            <span class="material-icons-round">add</span> Novo Evento
        </a>
    </header>
    <div class="dashboard-card-body">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Evento</th>
                    <th>Data</th>
                    <th>Inscritos</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($events as $event): ?>
                    <tr>
                        <td data-label="Evento">
                            <strong><?= htmlspecialchars($event['title']) ?></strong>
                            <div style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 4px;">
                                <span class="material-icons-round" style="font-size: 14px; vertical-align: middle;">location_on</span>
                                <?= htmlspecialchars($event['location'] ?: 'Local não definido') ?>
                            </div>
                            <?php if ($event['is_paid']): ?>
                                <span class="badge badge-warning" style="margin-top: 4px; display: inline-block;">
                                    💰 Pago (R$ <?= number_format($event['price'], 2, ',', '.') ?>)
                                </span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Data">
                            <?= date('d/m/Y H:i', strtotime($event['start_datetime'])) ?>
                        </td>
                        <td data-label="Inscritos">
                            <span class="badge badge-info">
                                <?= $event['enrolled_count'] ?> 
                                <?= $event['max_participants'] ? '/ ' . $event['max_participants'] : '' ?>
                            </span>
                        </td>
                        <td data-label="Status">
                            <?php
                            echo match ($event['status']) {
                                'upcoming' => '<span class="badge badge-primary">Agendado</span>',
                                'ongoing' => '<span class="badge badge-success">Em Andamento</span>',
                                'completed' => '<span class="badge badge-secondary">Concluído</span>',
                                'cancelled' => '<span class="badge badge-danger">Cancelado</span>',
                                default => '<span class="badge badge-secondary">Desconhecido</span>'
                            };
                            ?>
                        </td>
                        <td data-label="Ações">
                            <div style="display: flex; gap: 8px;">
                                <a href="<?= base_url($tenant['slug'] . '/admin/eventos/' . $event['id'] . '/editar') ?>" class="btn btn-sm btn-secondary" title="Editar">
                                    <span class="material-icons-round" style="font-size: 18px;">edit</span>
                                </a>
                                <a href="<?= base_url('c/' . $tenant['slug'] . '/evento/' . $event['slug']) ?>" target="_blank" class="btn btn-sm btn-secondary" title="Página Pública">
                                    <span class="material-icons-round" style="font-size: 18px;">open_in_new</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($events)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 40px; color: var(--text-secondary);">
                            Nenhum evento criado ainda.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
