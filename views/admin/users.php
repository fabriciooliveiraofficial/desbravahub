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
                            <div style="display: flex; gap: 8px; justify-content: flex-end; align-items: center;">
                                <button class="btn btn-outline-primary btn-sm"
                                        onclick="viewUserCatalog(<?= $u['id'] ?>)"
                                        title="Ver Ficha Completa"
                                        style="padding: 4px; display: flex; align-items: center; justify-content: center;">
                                    <span class="material-icons-round" style="font-size: 20px;">medical_information</span>
                                </button>
                                <?php if ($u['id'] !== auth()['id']): ?>
                                <button class="btn btn-sm"
                                        onclick="toggleUserStatus(<?= $u['id'] ?>, '<?= $u['status'] ?>')"
                                        title="<?= $u['status'] === 'active' ? 'Bloquear Usuário' : 'Desbloquear Usuário' ?>"
                                        style="padding: 4px; display: flex; align-items: center; justify-content: center; color: <?= $u['status'] === 'active' ? 'var(--text-muted)' : 'var(--danger)' ?>;">
                                    <span class="material-icons-round" style="font-size: 20px;">
                                        <?= $u['status'] === 'active' ? 'block' : 'lock_open' ?>
                                    </span>
                                </button>
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
        .catalog-section { 
            background: #ffffff; 
            padding: 24px; 
            border-radius: 20px; 
            border: 1px solid rgba(0, 0, 0, 0.05); 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }
        .catalog-section:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08);
            border-color: rgba(6, 182, 212, 0.15);
        }
        .catalog-section h4 { 
            color: var(--accent-cyan); 
            margin: 0 0 20px 0; 
            border-bottom: 1px solid rgba(6, 182, 212, 0.1); 
            padding-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 1.1rem;
        }
        .catalog-item { margin-bottom: 16px; }
        .catalog-item:last-child { margin-bottom: 0; }
        .catalog-label { 
            font-size: 0.7rem; 
            color: #64748b; 
            text-transform: uppercase; 
            letter-spacing: 0.07em; 
            font-weight: 800; 
            margin-bottom: 6px; 
            display: block; 
        }
        .catalog-value { 
            font-size: 0.95rem; 
            color: #1e293b; 
            font-weight: 500;
            line-height: 1.5;
        }
        @media (max-width: 768px) { .catalog-grid { grid-template-columns: 1fr; } }

        /* Modal Styles - Overriding for Premium Light Theme */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            z-index: 1000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .modal-overlay.active {
            display: flex !important;
            opacity: 1 !important;
            visibility: visible !important;
        }
        .modal-content {
            background: #f8fafc;
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 28px;
            width: 100%;
            display: flex;
            flex-direction: column;
            box-shadow: 0 40px 80px -20px rgba(15, 23, 42, 0.2);
            animation: modalPop 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            overflow: hidden;
        }
        @keyframes modalPop {
            from { opacity: 0; transform: scale(0.9) translateY(20px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }
        .modal-header {
            padding: 24px 32px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #ffffff;
        }
        .modal-header h3 {
            color: #0f172a;
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 1.25rem;
        }
        .modal-body {
            padding: 32px;
            color: #334155;
            background: #f8fafc;
        }
        .modal-footer {
            padding: 20px 32px;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            background: #ffffff;
        }
        .btn-close {
            background: #f1f5f9;
            border: none;
            color: #64748b;
            cursor: pointer;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            transition: all 0.2s;
        }
        .btn-close:hover {
            color: #ef4444;
            background: #fee2e2;
            transform: rotate(90deg);
        }
        
        /* Print styles */
        @media print {
            body * { visibility: hidden; }
            #catalogModal, #catalogModal * { visibility: visible; }
            #catalogModal { position: absolute; left: 0; top: 0; width: 100%; background: white; }
            .modal-footer, .btn-close { display: none; }
            .modal-content { box-shadow: none; border: none; }
            .catalog-section { break-inside: avoid; border: 1px solid #eee; margin-bottom: 20px; }
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
                        <div class="catalog-section" style="grid-column: 1 / -1; background: linear-gradient(135deg, #ffffff, #f1f5f9); border: 1px solid rgba(6, 182, 212, 0.1); position: relative; overflow: hidden; box-shadow: 0 10px 30px -5px rgba(6, 182, 212, 0.1);">
                            <!-- Subtle Highlight Glow -->
                            <div style="position: absolute; left: -50px; top: -50px; width: 200px; height: 200px; background: radial-gradient(circle, rgba(6, 182, 212, 0.15) 0%, transparent 70%); pointer-events: none;"></div>
                            
                            <div style="display: flex; align-items: center; gap: 24px; position: relative; z-index: 1;">
                                <div style="width: 80px; height: 80px; border-radius: 24px; background: #ffffff; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(6, 182, 212, 0.2); box-shadow: 0 8px 16px -4px rgba(6, 182, 212, 0.2); position: relative;">
                                    <div style="position: absolute; inset: 0; border-radius: 24px; padding: 2px; background: linear-gradient(135deg, var(--accent-cyan), #3b82f6); -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0); -webkit-mask-composite: xor; mask-composite: exclude; opacity: 0.5;"></div>
                                    <span class="material-icons-round" style="font-size: 40px; background: linear-gradient(135deg, var(--accent-cyan), #2563eb); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">person</span>
                                </div>
                                <div>
                                    <h4 style="margin: 0; color: #0f172a; font-size: 1.75rem; display: block; border: none; padding: 0; font-family: 'Outfit'; font-weight: 900; letter-spacing: -0.02em;">${u.name}</h4>
                                    <div style="display: inline-flex; align-items: center; gap: 8px; margin-top: 8px; padding: 4px 12px; background: rgba(6, 182, 212, 0.08); border: 1px solid rgba(6, 182, 212, 0.1); border-radius: 10px; color: var(--accent-cyan); font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">
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
                            <div class="catalog-item"><span class="catalog-label">Email</span><span class="catalog-value" style="color: var(--accent-cyan); font-weight: 600;">${u.email}</span></div>
                            <div class="catalog-item"><span class="catalog-label">Telefone</span><span class="catalog-value">${u.phone || 'Não informado'}</span></div>
                            <div class="catalog-item"><span class="catalog-label">Emergência</span><span class="catalog-value" style="color: #ef4444; user-select: all; font-weight: 800;">${p.phone_emergency || 'Não informado'}</span></div>
                            <div class="catalog-item"><span class="catalog-label">Endereço</span><span class="catalog-value">${p.address || 'Não informado'}</span></div>
                        </div>

                        <div class="catalog-section">
                            <h4><span class="material-icons-round">medical_services</span> Informações Médicas</h4>
                            <div class="catalog-grid" style="margin-bottom: 12px; gap: 15px;">
                                <div class="catalog-item">
                                    <span class="catalog-label">Tipo Sanguíneo</span>
                                    <div style="display: flex; align-items: center; gap: 8px; margin-top: 5px;">
                                        <span class="catalog-value" style="color: white; font-weight: 800; font-size: 1.1rem; background: #ef4444; padding: 6px 12px; border-radius: 10px; box-shadow: 0 4px 10px rgba(239, 68, 68, 0.3); display: inline-flex; align-items: center; gap: 5px;">
                                            <span class="material-icons-round" style="font-size: 16px;">water_drop</span>
                                            ${p.blood_type || '?'}${p.rh_factor || ''}
                                        </span>
                                    </div>
                                </div>
                                <div class="catalog-item">
                                    <span class="catalog-label">Vacina Tétano</span>
                                    <span class="catalog-value" style="display: flex; align-items: center; gap: 6px; font-weight: 600;">
                                        <span class="material-icons-round" style="color: #10b981; font-size: 18px;">verified</span>
                                        ${p.tetanus_vaccine || 'Não informado'}
                                    </span>
                                </div>
                            </div>
                            
                            <div style="margin-top: 20px; padding: 16px; border-radius: 14px; background: #f1f5f9; border: 1px solid rgba(0,0,0,0.03);">
                                <h5 style="margin: 0 0 10px 0; color: #64748b; font-size: 0.75rem; text-transform: uppercase; font-weight: 800; letter-spacing: 0.05em; display: flex; align-items: center; gap: 6px;">
                                    <span class="material-icons-round" style="font-size: 16px; color: #ef4444;">error</span> Condições Diagnósticadas
                                </h5>
                                <div class="catalog-value" style="font-size: 0.9rem;">${htmlConditions}</div>
                            </div>

                            <div style="margin-top: 15px; padding: 16px; border-radius: 14px; background: #fef2f2; border: 1px solid rgba(239, 68, 68, 0.1);">
                                <h5 style="margin: 0 0 10px 0; color: #b91c1c; font-size: 0.75rem; text-transform: uppercase; font-weight: 800; letter-spacing: 0.05em; display: flex; align-items: center; gap: 6px;">
                                    <span class="material-icons-round" style="font-size: 16px; color: #ef4444;">no_food</span> Alergias Conhecidas
                                </h5>
                                <div class="catalog-value" style="font-size: 0.9rem; color: #b91c1c;">${htmlAllergies}</div>
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

                if (response.status === 403) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Sem permissão',
                        text: data.error || 'Você não tem permissão para alterar cargos.',
                    });
                    // Revert select to original value
                    event?.target?.dispatchEvent(new Event('cancel'));
                    location.reload();
                    return;
                }

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
                
                if (response.status === 403) {
                    Swal.fire({ icon: 'error', title: 'Sem permissão', text: data.error || 'Você não tem permissão para alterar o status deste usuário.' });
                    return;
                }

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

                if (response.status === 403) {
                    Swal.fire({ icon: 'error', title: 'Sem permissão', text: data.error || 'Você não tem permissão para excluir usuários.' });
                    return;
                }

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
