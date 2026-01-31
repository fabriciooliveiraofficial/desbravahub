<?php if (empty($assignments)): ?>
    <tr><td colspan="5" class="empty-matrix">Nenhuma missão ativa no momento.</td></tr>
<?php else: ?>
    <?php foreach ($assignments as $a): ?>
        <?php 
        $isStarted = ($a['status'] !== 'pending');
        $isRead = ($a['read_at'] !== null);
        $isPendingReview = ($a['status'] === 'pending_review');
        $specialty = $a['specialty'] ?? [];
        ?>
        <tr id="mission-row-<?= $a['assignment_id'] ?>">
            <td>
                <div class="member-cell">
                    <div class="member-avatar">
                        <?= strtoupper(substr($a['user_name'], 0, 1)) ?>
                    </div>
                    <div class="member-info">
                        <span class="name"><?= htmlspecialchars($a['user_name']) ?></span>
                        <span class="role">Unidade: <?= htmlspecialchars($a['unit_name'] ?? 'Geral') ?></span>
                    </div>
                </div>
            </td>
            <td>
                <div class="mission-cell">
                    <div class="mission-icon">
                        <?php 
                        $icon = $specialty['badge_icon'] ?? '🛡️';
                        if (str_contains($icon, 'fa-')): ?>
                            <i class="<?= htmlspecialchars($icon) ?>" style="color: var(--god-blue)"></i>
                        <?php else: ?>
                            <?= $icon ?>
                        <?php endif; ?>
                    </div>
                    <div class="mission-name"><?= htmlspecialchars($specialty['name'] ?? 'Missão não identificada') ?></div>
                </div>
            </td>
            <td>
                <?php
                // Determine the highest active step
                $stepActive = 1;
                if ($a['status'] === 'completed' || $isPendingReview) $stepActive = 5;
                elseif ($a['progress_percentage'] > 0) $stepActive = 4;
                elseif ($isStarted) $stepActive = 3;
                elseif ($isRead) $stepActive = 3; // If read, we are waiting for it to be started, so 3 is next
                else $stepActive = 2; // Not yet read, so active is "Notificando"
                ?>
                <div class="lifecycle-tracker">
                    <div class="step <?= $stepActive > 1 ? 'completed' : 'active' ?>">
                        <span class="step-label">Liberada</span>
                    </div>
                    <div class="step <?= $stepActive > 2 ? 'completed' : ($stepActive == 2 ? 'active' : '') ?>">
                        <span class="step-label"><?= $isRead ? 'Recebida' : 'Notificando' ?></span>
                    </div>
                    <div class="step <?= $stepActive > 3 ? 'completed' : ($stepActive == 3 ? 'active' : '') ?>">
                        <span class="step-label">Iniciada</span>
                    </div>
                    <div class="step <?= $stepActive > 4 ? 'completed' : ($stepActive == 4 ? 'active' : '') ?>">
                        <span class="step-label">No Campo</span>
                    </div>
                    <div class="step <?= $isPendingReview ? 'warning active' : ($a['status'] === 'completed' ? 'completed' : '') ?>">
                        <span class="step-label"><?= $isPendingReview ? 'Pend. Review' : 'Finalizada' ?></span>
                    </div>
                </div>
            </td>
            <td>
                <div class="progress-box">
                    <div class="progress-container">
                        <div class="progress-fill" style="width: <?= $a['progress_percentage'] ?>%;"></div>
                    </div>
                    <div class="progress-text">
                        <span><?= $a['progress_percentage'] ?>%</span>
                        <span><?= $a['completed_requirements'] ?>/<?= $a['total_requirements'] ?></span>
                    </div>
                </div>
            </td>
            <td>
                <div style="display: flex; gap: 8px;" id="actions-<?= $a['assignment_id'] ?>">
                    <?php if (($a['type_label'] ?? '') === 'specialty'): ?>
                        <a href="<?= base_url($tenant['slug'] . '/admin/especialidades/atribuicao/' . ($a['id'] ?? 0)) ?>" class="btn-god-action" title="Avaliar Provas">
                            <i class="fa-solid fa-magnifying-glass-chart"></i>
                        </a>
                    <?php endif; ?>
                    <a href="<?= base_url($tenant['slug'] . '/admin/especialidades/atribuicao/' . ($a['id'] ?? 0) . '/detalhes') ?>" class="btn-god-action" title="Ver Detalhes (Logs)">
                        <i class="fa-solid fa-list-ul"></i>
                    </a>

                    <!-- Remover Missão -->
                    <?php if (!$isStarted): ?>
                        <button 
                            class="btn-god-action btn-delete-mission" 
                            style="color: var(--danger);"
                            title="Remover Missão"
                            data-assignment-id="<?= $a['assignment_id'] ?? 0 ?>"
                        >
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    <?php else: ?>
                        <button 
                            class="btn-god-action" 
                            style="opacity: 0.3; cursor: not-allowed;" 
                            title="Não é possível remover (Missão em andamento)"
                            disabled
                        >
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
<?php endif; ?>
