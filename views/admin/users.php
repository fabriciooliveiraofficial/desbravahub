        <div class="dashboard-card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Cargo</th>
                        <th>XP</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 36px; height: 36px; background: linear-gradient(135deg, #00d9ff, #00ff88); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #1a1a2e; font-weight: bold;">
                                    <?= strtoupper(substr($u['name'], 0, 1)) ?>
                                </div>
                                <span><?= htmlspecialchars($u['name']) ?></span>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td>
                            <select class="form-control" style="width: auto; padding: 6px 10px;" 
                                    onchange="updateRole(<?= $u['id'] ?>, this.value)"
                                    <?= $u['id'] === auth()['id'] ? 'disabled' : '' ?>>
                                <?php foreach ($roles as $role): ?>
                                <option value="<?= $role['id'] ?>" <?= $u['role_id'] == $role['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($role['display_name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><?= number_format($u['xp_points']) ?></td>
                        <td>
                            <?php
                            $statusClass = match($u['status']) {
                                'active' => 'badge-success',
                                'inactive' => 'badge-warning',
                                default => 'badge-danger'
                            };
                            ?>
                            <span class="badge <?= $statusClass ?>"><?= ucfirst($u['status']) ?></span>
                        </td>
                        <td>
                            <?php if ($u['id'] !== auth()['id']): ?>
                            <button class="btn btn-secondary btn-sm" onclick="toggleStatus(<?= $u['id'] ?>)">
                                🔄
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <script>

        async function updateRole(userId, roleId) {
            try {
                const formData = new FormData();
                formData.append('role_id', roleId);
                
                const response = await fetch(`<?= base_url($tenant['slug']) ?>/admin/usuarios/${userId}/role`, {
                    method: 'POST',
                    body: formData,
                    headers: { 'Accept': 'application/json' }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showToast('Cargo atualizado', 'success');
                } else {
                    showToast(data.error || 'Erro ao atualizar', 'error');
                }
            } catch (err) {
                showToast('Erro de conexão', 'error');
            }
        }
        
        function toggleStatus(userId) {
            showToast('Alteração de status será implementada em breve', 'info');
        }
    </script>
