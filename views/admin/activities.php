<div class="dashboard-card">
    <header class="dashboard-card-header">
        <span class="material-icons-round" style="color: #ec4899;">local_activity</span>
        <h3>Especialidades</h3>
        <button class="btn btn-primary" onclick="openModal('create-modal')" style="margin-left: auto;">
            <span class="material-icons-round">add</span> Nova Especialidade
        </button>
    </header>
    <div class="dashboard-card-body">
        <table class="data-table">
            <thead>
                <tr>
                    <th width="60">Ícone</th>
                    <th>Nome</th>
                    <th>Dificuldade</th>
                    <th>XP</th>
                    <th>Tipo</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($activities as $activity): ?>
                    <tr>
                        <td data-label="Ícone" style="text-align: center;">
                            <span style="font-size: 1.5rem;">
                                <?php if (str_starts_with($activity['badge_icon'] ?? '', 'fa-')): ?>
                                    <i class="<?= htmlspecialchars($activity['badge_icon']) ?>"></i>
                                <?php elseif (str_contains($activity['badge_icon'] ?? '', ':')): ?>
                                    <?php $iconName = explode(' ', $activity['badge_icon'])[0]; ?>
                                    <iconify-icon icon="<?= htmlspecialchars($iconName) ?>"></iconify-icon>
                                <?php else: ?>
                                    <?= $activity['badge_icon'] ?? '📘' ?>
                                <?php endif; ?>
                            </span>
                        </td>
                        <td data-label="Nome">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <strong><?= htmlspecialchars($activity['name']) ?></strong>
                                <?php if ($activity['is_custom'] ?? false): ?>
                                    <span class="badge badge-success" style="font-size: 0.7rem;">Custom</span>
                                <?php endif; ?>
                            </div>
                            <small
                                style="color: #888;"><?= htmlspecialchars(substr($activity['description'] ?? '', 0, 50)) ?>...</small>
                        </td>
                        <td data-label="Dificuldade">
                            <span title="Nível <?= $activity['difficulty'] ?? 1 ?>">
                                <?= str_repeat('⭐', $activity['difficulty'] ?? 1) ?>
                            </span>
                        </td>
                        <td data-label="XP">
                            <span style="color: #00ff88; font-weight: bold;">
                                +<?= number_format($activity['xp_reward'] ?? 0) ?> XP
                            </span>
                        </td>
                        <td data-label="Tipo">
                            <?php
                            $type = $activity['type'] ?? 'indoor';
                            echo match ($type) {
                                'outdoor' => '<span class="badge badge-warning">🏕️ Outdoor</span>',
                                'mixed' => '<span class="badge badge-info">🔄 Misto</span>',
                                default => '<span class="badge badge-secondary">🏠 Indoor</span>'
                            };
                            ?>
                        </td>
                        <td data-label="Ações">
                            <?php if ($activity['is_custom'] ?? false): ?>
                                <a href="<?= base_url($tenant['slug'] . '/admin/especialidades/' . $activity['id'] . '/requisitos') ?>"
                                    class="btn btn-secondary btn-sm" title="Editar Requisitos">
                                    ✏️
                                </a>
                            <?php else: ?>
                                <span class="badge badge-secondary" style="opacity: 0.5;">Padrão</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($activities)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 40px; color: #888;">
                            Nenhuma especialidade criada ainda
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Create Modal -->
<div class="modal-overlay" id="create-modal">
    <div class="modal">
        <div class="modal-header">
            <h3>➕ Nova Especialidade</h3>
            <button class="modal-close" onclick="closeModal('create-modal')">×</button>
        </div>

        <form id="create-form">
            <div class="form-group">
                <label>Nome da Especialidade *</label>
                <input type="text" name="name" class="form-control" required placeholder="Ex: Arte de Contar Histórias">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label>Categoria *</label>
                    <select name="category_id" class="form-control" required>
                        <option value="">Selecione...</option>
                        <?php
                        $categories = \App\Services\SpecialtyService::getCategories();
                        foreach ($categories as $cat):
                            ?>
                            <option value="<?= $cat['id'] ?>">
                                <?php if (str_starts_with($cat['icon'] ?? '', 'fa-')): ?>
                                    <i class="<?= htmlspecialchars($cat['icon']) ?>"></i>
                                <?php elseif (str_contains($cat['icon'] ?? '', ':')): ?>
                                    <iconify-icon icon="<?= htmlspecialchars($cat['icon']) ?>"></iconify-icon>
                                <?php else: ?>
                                    <?= $cat['icon'] ?>
                                <?php endif; ?>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Ícone da Especialidade</label>
                    <input type="hidden" id="actIcon" name="badge_icon" value="noto:blue-book">
                    <div class="icon-picker-trigger" 
                         onclick="IconPicker.open(document.getElementById('actIcon').value, (sel) => {
                             document.getElementById('actIcon').value = sel;
                             document.getElementById('actIconPreview').innerHTML = `<iconify-icon icon='${sel}' style='font-size: 1.5rem;'></iconify-icon>`;
                             document.getElementById('actIconText').textContent = sel;
                         })"
                         style="display: flex; align-items: center; gap: 12px; padding: 10px; background: var(--bg-input); border: 1px solid var(--border-light); border-radius: 8px; cursor: pointer;">
                        <div id="actIconPreview">
                            <iconify-icon icon="noto:blue-book" style="font-size: 1.5rem;"></iconify-icon>
                        </div>
                        <div class="icon-info">
                            <span id="actIconText" style="font-size: 0.9rem; color: var(--text-primary);">noto:blue-book</span>
                        </div>
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label>Tipo</label>
                    <select name="type" class="form-control">
                        <option value="indoor">🏠 Indoor (Teórico)</option>
                        <option value="outdoor">🏕️ Outdoor (Prático)</option>
                        <option value="mixed">🔄 Misto</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Dificuldade</label>
                    <select name="difficulty" class="form-control">
                        <option value="1">⭐ Muito Fácil</option>
                        <option value="2" selected>⭐⭐ Fácil</option>
                        <option value="3">⭐⭐⭐ Médio</option>
                        <option value="4">⭐⭐⭐⭐ Difícil</option>
                        <option value="5">⭐⭐⭐⭐⭐ Muito Difícil</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Descrição</label>
                <textarea name="description" class="form-control" rows="4"></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label>Tempo Estimado (horas)</label>
                    <input type="number" name="duration_hours" class="form-control" value="4" min="1" max="100">
                </div>

                <div class="form-group">
                    <label>Recompensa XP</label>
                    <input type="number" name="xp_reward" class="form-control" value="100" min="0">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('create-modal')">Cancelar</button>
                <button type="submit" class="btn btn-primary">Criar Especialidade</button>
            </div>
        </form>
    </div>
</div>



<?php require BASE_PATH . '/views/admin/partials/icon_picker.php'; ?>
<script>
    var toast;

    function openModal(id) {
        document.getElementById(id).classList.add('active');
    }

    function closeModal(id) {
        document.getElementById(id).classList.remove('active');
    }

    document.addEventListener('DOMContentLoaded', () => {
        toast = window.toast = window.toast || new (window.ToastNotification || ToastNotification)();

        const createForm = document.getElementById('create-form');
        if (createForm) {
            createForm.addEventListener('submit', async (e) => {
                e.preventDefault();

                const formData = new FormData(e.target);

                try {
                    const response = await fetch('<?= base_url($tenant['slug'] . '/admin/especialidades/criar') ?>', {
                        method: 'POST',
                        body: formData,
                        headers: { 'Accept': 'application/json' }
                    });

                    const data = await response.json();

                    if (data.success) {
                        toast.success('Sucesso', 'Especialidade criada com sucesso');
                        if (data.redirect) {
                            setTimeout(() => window.location.href = data.redirect, 500);
                        } else {
                            setTimeout(() => location.reload(), 1000);
                        }
                    } else {
                        toast.error('Erro', data.error || 'Erro ao criar especialidade');
                    }
                } catch (err) {
                    console.error(err);
                    if (toast) toast.error('Erro', 'Erro de conexão');
                    else alert('Erro de conexão');
                }
            });
        }
    });

    function editActivity(id) {
        // TODO: Implement edit modal
        if (!toast) toast = window.toast || new (window.ToastNotification || ToastNotification)();
        toast.info('Em breve', 'Edição será implementada em breve');
    }

</script>