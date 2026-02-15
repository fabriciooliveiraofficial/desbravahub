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
                        <td data-label="Nome">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 36px; height: 36px; background: linear-gradient(135deg, #00d9ff, #00ff88); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #1a1a2e; font-weight: bold;">
                                    <?= strtoupper(substr($u['name'], 0, 1)) ?>
                                </div>
                                <span><?= htmlspecialchars($u['name']) ?></span>
                            </div>
                        </td>
                        <td data-label="Email"><?= htmlspecialchars($u['email']) ?></td>
                        <td data-label="Cargo">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <select class="form-control" style="width: auto; padding: 6px 10px;" 
                                        onchange="updateRole(<?= $u['id'] ?>, this.value)"
                                        <?= $u['id'] === auth()['id'] ? 'disabled' : '' ?>>
                                    <?php foreach ($roles as $role): ?>
                                    <option value="<?= $role['id'] ?>" <?= $u['role_id'] == $role['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($role['display_name']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if ($u['id'] !== auth()['id']): ?>
                                <button class="btn btn-sm" 
                                        onclick="toggleUserStatus(<?= $u['id'] ?>, '<?= $u['status'] ?>')" 
                                        title="<?= $u['status'] === 'active' ? 'Bloquear Usuário' : 'Desbloquear Usuário' ?>"
                                        style="padding: 4px; display: flex; align-items: center; justify-content: center; color: <?= $u['status'] === 'active' ? 'var(--text-muted)' : 'var(--danger)' ?>;">
                                    <span class="material-icons-round" style="font-size: 20px;">
                                        <?= $u['status'] === 'active' ? 'block' : 'lock_open' ?>
                                    </span>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td data-label="XP"><?= number_format($u['xp_points']) ?></td>
                        <td data-label="Status">
                            <?php
                            $statusClass = match($u['status']) {
                                'active' => 'badge-success',
                                'inactive' => 'badge-danger',
                                default => 'badge-danger'
                            };
                            $statusLabel = $u['status'] === 'active' ? 'Ativo' : 'Bloqueado';
                            ?>
                            <span class="badge <?= $statusClass ?>"><?= $statusLabel ?></span>
                        </td>
                        <td data-label="Ações">
                            <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                <?php if ($u['id'] !== auth()['id']): ?>
                                <button class="btn btn-outline-danger btn-sm" 
                                        onclick="deleteUser(<?= $u['id'] ?>)" 
                                        title="Excluir Usuário"
                                        style="padding: 4px; display: flex; align-items: center; justify-content: center;">
                                    <span class="material-icons-round" style="font-size: 20px;">delete</span>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <!-- SweetAlert2 for Interactions -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
        
        async function toggleUserStatus(userId, currentStatus) {
            const action = currentStatus === 'active' ? 'bloquear' : 'desbloquear';
            const result = await Swal.fire({
                title: 'Tem certeza?',
                text: `Deseja ${action} este usuário?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: currentStatus === 'active' ? '#ef4444' : '#10b981',
                cancelButtonColor: '#6B7280',
                confirmButtonText: `Sim, ${action}!`,
                cancelButtonText: 'Cancelar'
            });

            if (!result.isConfirmed) return;

            try {
                const response = await fetch(`<?= base_url($tenant['slug']) ?>/admin/usuarios/${userId}/status`, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        text: `Usuário ${data.new_status === 'active' ? 'desbloqueado' : 'bloqueado'} com sucesso.`,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire('Erro', data.error || 'Erro ao alterar status', 'error');
                }
            } catch (err) {
                Swal.fire('Erro', 'Erro de conexão', 'error');
            }
        }

        async function deleteUser(userId) {
            const result = await Swal.fire({
                title: 'Excluir Usuário?',
                text: "Esta ação não pode ser desfeita e o usuário perderá acesso imediatamente.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Sim, excluir!',
                cancelButtonText: 'Cancelar'
            });

            if (!result.isConfirmed) return;

            try {
                const response = await fetch(`<?= base_url($tenant['slug']) ?>/admin/usuarios/${userId}/delete`, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Excluído!',
                        text: 'O usuário foi removido com sucesso.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire('Erro', data.error || 'Erro ao excluir usuário', 'error');
                }
            } catch (err) {
                Swal.fire('Erro', 'Erro de conexão', 'error');
            }
        }

        function showToast(message, type) {
            if (window.toast) {
                window.toast[type](message);
            } else {
                console.log(`${type}: ${message}`);
            }
        }
    </script>
