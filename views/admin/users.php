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
                                <button class="btn btn-outline-primary btn-sm" 
                                        onclick="viewUserCatalog(<?= $u['id'] ?>)" 
                                        title="Ver Ficha Completa"
                                        style="padding: 4px; display: flex; align-items: center; justify-content: center;">
                                    <span class="material-icons-round" style="font-size: 20px;">medical_information</span>
                                </button>
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

    <!-- User Catalog Modal -->
    <div id="catalogModal" class="modal-overlay" style="display: none;">
        <div class="modal-content" style="max-width: 800px; width: 95%; max-height: 90vh; overflow-y: auto;">
            <div class="modal-header">
                <h3 id="catalogModalTitle" style="margin: 0; display: flex; align-items: center; gap: 10px;">
                    <span class="material-icons-round">badge</span> Ficha de Registro
                </h3>
                <button type="button" class="btn-close" onclick="closeCatalogModal()">
                    <span class="material-icons-round">close</span>
                </button>
            </div>
            <div class="modal-body" id="catalogModalBody">
                <div style="text-align: center; padding: 40px;">
                    <span class="material-icons-round spin" style="font-size: 40px; color: var(--accent-cyan);">loop</span>
                    <p style="margin-top: 15px; color: var(--text-muted);">Buscando ficha de registro...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeCatalogModal()">Fechar</button>
                <button type="button" class="btn btn-primary" onclick="window.print()">
                    <span class="material-icons-round" style="font-size: 16px; margin-right: 5px;">print</span> 
                    Imprimir Ficha
                </button>
            </div>
        </div>
    </div>

    <!-- SweetAlert2 for Interactions -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .catalog-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .catalog-section { background: rgba(0,0,0,0.2); padding: 20px; border-radius: 12px; border: 1px solid var(--border-color); }
        .catalog-section h4 { 
            color: var(--accent-cyan); 
            margin: 0 0 15px 0; 
            border-bottom: 1px solid rgba(0, 217, 255, 0.2); 
            padding-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .catalog-item { margin-bottom: 12px; }
        .catalog-label { font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; font-weight: bold; margin-bottom: 4px; display: block; }
        .catalog-value { font-size: 1rem; color: var(--text-light); }
        @media (max-width: 768px) { .catalog-grid { grid-template-columns: 1fr; } }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            z-index: 1000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }
        .modal-overlay.active {
            display: flex !important;
            opacity: 1 !important;
            visibility: visible !important;
        }
        .modal-content {
            background: linear-gradient(145deg, rgba(30, 41, 59, 0.95), rgba(15, 23, 42, 0.95));
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            width: 100%;
            display: flex;
            flex-direction: column;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: slideUp 0.3s ease;
        }
        .modal-header {
            padding: 20px 24px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-header h3 {
            color: #fff;
        }
        .modal-body {
            padding: 24px;
            color: var(--text-main);
        }
        .modal-footer {
            padding: 16px 24px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }
        .btn-close {
            background: transparent;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: all 0.2s;
        }
        .btn-close:hover {
            color: #ef4444;
            background: rgba(239, 68, 68, 0.1);
        }
        
        /* Print styles */
        @media print {
            body * { visibility: hidden; }
            #catalogModal, #catalogModal * { visibility: visible; }
            #catalogModal { position: absolute; left: 0; top: 0; width: 100%; }
            .modal-footer { display: none; }
            .btn-close { display: none; }
            .catalog-section { break-inside: avoid; border-color: #ccc; }
        }
    </style>

    <script>
        function closeCatalogModal() {
            const modal = document.getElementById('catalogModal');
            modal.classList.remove('active');
            modal.style.display = 'none';
            modal.style.opacity = '0';
            modal.style.visibility = 'hidden';
        }

        window.viewUserCatalog = async function(userId) {
            const modal = document.getElementById('catalogModal');
            const body = document.getElementById('catalogModalBody');
            const title = document.getElementById('catalogModalTitle');
            
            // Show modal with loading state - force visibility
            modal.style.display = 'flex';
            modal.style.opacity = '1';
            modal.style.visibility = 'visible';
            modal.classList.add('active');
            title.innerHTML = `<span class="material-icons-round">badge</span> Ficha de Registro`;
            body.innerHTML = `
                <div style="text-align: center; padding: 40px;">
                    <span class="material-icons-round spin" style="font-size: 40px; color: var(--accent-cyan);">loop</span>
                    <p style="margin-top: 15px; color: var(--text-muted);">Buscando ficha de registro...</p>
                </div>
            `;

            try {
                // Get tenant slug from URL
                const pathSegments = window.location.pathname.split('/').filter(s => s);
                const tenantSlug = pathSegments[0] || 'clube-demo';
                
                const url = `/${tenantSlug}/admin/usuarios/${userId}/ficha`;

                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();

                if (!response.ok || data.error) {
                    throw new Error(data.error || data.message || `Erro do servidor (${response.status})`);
                }

                const u = data.user;
                const p = data.profile || {};
                
                const medical = typeof p.medical_conditions === 'string' ? JSON.parse(p.medical_conditions) : (p.medical_conditions || {});
                const allergies = typeof p.allergies === 'string' ? JSON.parse(p.allergies) : (p.allergies || {});

                // Formatter helpers
                const dt = u.birth_date ? new Date(u.birth_date).toLocaleDateString('pt-BR', {timeZone: 'UTC'}) : 'Não informado';
                const created = u.created_at ? new Date(u.created_at).toLocaleDateString('pt-BR') : 'Não informado';

                // Conditions list
                const activeConditions = Object.keys(medical).filter(k => k !== 'outros' && medical[k]).map(k => k.charAt(0).toUpperCase() + k.slice(1));
                if (medical.outros) activeConditions.push(medical.outros);
                let htmlConditions = activeConditions.length > 0 ? activeConditions.join(', ') : 'Nenhuma';

                // Allergies list
                const activeAllergies = Object.keys(allergies).filter(k => k !== 'outros' && allergies[k]).map(k => k.charAt(0).toUpperCase() + k.slice(1));
                if (allergies.outros) activeAllergies.push(allergies.outros);
                let htmlAllergies = activeAllergies.length > 0 ? activeAllergies.join(', ') : 'Nenhuma';

                title.innerHTML = `<span class="material-icons-round">badge</span> Ficha: ${u.name}`;

                body.innerHTML = `
                    <div class="catalog-grid">
                        <div class="catalog-section" style="grid-column: 1 / -1; background: linear-gradient(135deg, rgba(30,41,59,0.5), rgba(15,23,42,0.8)); border: 1px solid rgba(255,255,255,0.1);">
                            <div style="display: flex; align-items: center; gap: 20px;">
                                <div style="width: 60px; height: 60px; border-radius: 15px; background: rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center; border: 2px solid var(--accent-cyan);">
                                    <span class="material-icons-round" style="font-size: 30px; color: white;">person</span>
                                </div>
                                <div>
                                    <h4 style="margin: 0; color: white; display: block; border: none; padding: 0;">${u.name}</h4>
                                    <div style="color: var(--accent-cyan); font-size: 0.9rem; font-weight: 500; display: flex; align-items: center; gap: 6px; margin-top: 5px;">
                                        <span class="material-icons-round" style="font-size: 16px;">workspace_premium</span>
                                        ${u.role_name}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="catalog-section" style="grid-column: 1 / -1;">
                            <h4><span class="material-icons-round">info</span> Dados Pessoais</h4>
                            <div class="catalog-grid" style="margin-bottom: 0;">
                                <div>
                                    <div class="catalog-item"><span class="catalog-label">Nome</span><span class="catalog-value">${u.name}</span></div>
                                    <div class="catalog-item"><span class="catalog-label">Data de Nascimento</span><span class="catalog-value">${dt}</span></div>
                                    <div class="catalog-item"><span class="catalog-label">Série Escolar</span><span class="catalog-value">${p.school_grade || 'Não informado'}</span></div>
                                </div>
                                <div>
                                    <div class="catalog-item"><span class="catalog-label">Data do Alistamento</span><span class="catalog-value">${created}</span></div>
                                    <div class="catalog-item"><span class="catalog-label">Cargo Principal</span><span class="catalog-value">${u.role_name}</span></div>
                                </div>
                            </div>
                        </div>

                        <div class="catalog-section">
                            <h4><span class="material-icons-round">contact_phone</span> Contatos</h4>
                            <div class="catalog-item"><span class="catalog-label">Email</span><span class="catalog-value">${u.email}</span></div>
                            <div class="catalog-item"><span class="catalog-label">Telefone</span><span class="catalog-value">${u.phone || 'Não informado'}</span></div>
                            <div class="catalog-item"><span class="catalog-label">Emergência</span><span class="catalog-value" style="color: var(--accent-warning); user-select: all; font-weight: bold;">${p.phone_emergency || 'Não informado'}</span></div>
                            <div class="catalog-item"><span class="catalog-label">Endereço</span><span class="catalog-value">${p.address || 'Não informado'}</span></div>
                        </div>

                        <div class="catalog-section">
                            <h4><span class="material-icons-round">medical_services</span> Informações Médicas</h4>
                            <div class="catalog-grid" style="margin-bottom: 12px; gap: 10px;">
                                <div class="catalog-item"><span class="catalog-label">Tipo Sanguíneo</span><span class="catalog-value" style="color: #ef4444; font-weight: 800; font-size: 1.2rem; background: rgba(239, 68, 68, 0.1); padding: 5px 10px; border-radius: 8px; border: 1px solid rgba(239, 68, 68, 0.2); display: inline-block;">${p.blood_type || '?'}${p.rh_factor || ''}</span></div>
                                <div class="catalog-item"><span class="catalog-label">Vacina Tétano</span><span class="catalog-value">${p.tetanus_vaccine || 'Não informado'}</span></div>
                            </div>
                            
                            <div style="margin-top: 15px; padding: 12px; border-radius: 8px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05);">
                                <h5 style="margin: 0 0 8px 0; color: #cbd5e1; font-size: 0.85rem; display: flex; align-items: center; gap: 6px;">
                                    <span class="material-icons-round" style="font-size: 16px; color: #ef4444;">warning</span> Condições Diagnósticadas
                                </h5>
                                <div class="catalog-value" style="line-height: 1.5;">${htmlConditions}</div>
                            </div>

                            <div style="margin-top: 15px; padding: 12px; border-radius: 8px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05);">
                                <h5 style="margin: 0 0 8px 0; color: #cbd5e1; font-size: 0.85rem; display: flex; align-items: center; gap: 6px;">
                                    <span class="material-icons-round" style="font-size: 16px; color: #f59e0b;">coronavirus</span> Alergias Conhecidas
                                </h5>
                                <div class="catalog-value" style="line-height: 1.5; color: #fda4af;">${htmlAllergies}</div>
                            </div>
                        </div>
                    </div>
                `;

            } catch (err) {
                console.error('Error fetching user catalog:', err);
                body.innerHTML = `
                    <div style="padding: 40px; text-align: center;">
                        <span class="material-icons-round" style="font-size: 48px; color: #ef4444; margin-bottom: 16px;">error_outline</span>
                        <h3 style="color: var(--text-light); margin-bottom: 8px;">Erro ao carregar dados</h3>
                        <p style="color: var(--text-muted); margin-bottom: 24px;">${err.message}</p>
                    </div>
                `;
            }
        }

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
