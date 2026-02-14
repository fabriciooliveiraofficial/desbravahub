<?php
/**
 * Admin: Learning Programs List
 * 
 * Lists programs (specialties/classes) with category filtering.
 */
$pageTitle = $currentCategory ? $currentCategory['name'] : 'Programas';
$pageIcon = $currentCategory ? 'category' : 'library_books';
$typeLabel = ($type ?? '') === 'class' ? 'Classes' : 'Especialidades';
?>



        <div class="page-toolbar">
            <div class="filters" style="margin-bottom: 0;">
                <select class="filter-select" onchange="filterByCategory(this.value)">
                    <option value="">Todas as Categorias</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($categoryId ?? '') == $cat['id'] ? 'selected' : '' ?>>
                             <?php
                                $icon = $cat['icon'] ?? '📚';
                                if(str_starts_with($icon, 'fa-')) echo '📂'; 
                                else if(str_contains($icon, ':')) echo '📂';
                                else echo $icon;
                            ?> 
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <select class="filter-select" onchange="filterByType(this.value)">
                    <option value="">Todos os Tipos</option>
                    <option value="specialty" <?= ($type ?? '') === 'specialty' ? 'selected' : '' ?>>🎯 Especialidades
                    </option>
                    <option value="class" <?= ($type ?? '') === 'class' ? 'selected' : '' ?>>🎖️ Classes</option>
                </select>
                <select class="filter-select" onchange="filterByStatus(this.value)">
                    <option value="">Ativos & Rascunhos</option>
                    <option value="published" <?= ($status ?? '') === 'published' ? 'selected' : '' ?>>✅ Publicados</option>
                    <option value="draft" <?= ($status ?? '') === 'draft' ? 'selected' : '' ?>>📝 Rascunhos</option>
                    <option value="archived" <?= ($status ?? '') === 'archived' ? 'selected' : '' ?>>📦 Arquivados</option>
                </select>
            </div>

            <div class="actions-group">
                <button class="btn-toolbar primary" onclick="openCreateProgramModal()">
                    <span class="material-icons-round">add_circle</span> Novo Programa
                </button>
            </div>
        </div>



        <?php if (empty($programs)): ?>
            <div class="empty-state">
                <div class="icon">📚</div>
                <h3>Nenhum programa encontrado</h3>
                <p>Crie seu primeiro programa de aprendizagem.</p>
                <button onclick="openCreateProgramModal()" class="btn-add"
                    style="margin-top: 16px; display: inline-flex; align-items: center; gap: 8px; border: none; cursor: pointer;">
                    ➕ Criar Programa
                </button>
            </div>
        <?php else: ?>
            <div class="programs-grid">
                <?php foreach ($programs as $program): ?>
                    <div class="specialty-card">
                        <div class="specialty-header">
                            <div class="specialty-badge">
                                <?php if (str_starts_with($program['icon'] ?? '', 'fa-')): ?>
                                    <i class="<?= htmlspecialchars($program['icon']) ?>"></i>
                                <?php elseif (str_contains($program['icon'] ?? '', ':')): ?>
                                    <iconify-icon icon="<?= htmlspecialchars($program['icon']) ?>"></iconify-icon>
                                <?php else: ?>
                                    <?= $program['icon'] ?>
                                <?php endif; ?>
                            </div>
                            <div class="specialty-title">
                                <h3><?= htmlspecialchars($program['name']) ?></h3>
                                <div class="specialty-meta">
                                    <span class="meta-tag" title="Tipo">
                                        <?php if ($program['is_outdoor']): ?>
                                            <span class="material-icons-round" style="font-size:12px">forest</span> Externo
                                        <?php else: ?>
                                            <span class="material-icons-round" style="font-size:12px">home</span> Interno
                                        <?php endif; ?>
                                    </span>
                                    <span class="meta-tag" title="Duração">
                                        <span class="material-icons-round" style="font-size:12px">schedule</span>
                                        <?= $program['duration_hours'] ?>h
                                    </span>
                                    <span class="meta-tag difficulty-stars" title="Dificuldade">
                                        <?= str_repeat('★', $program['difficulty'] ?? 1) . str_repeat('☆', 5 - ($program['difficulty'] ?? 1)) ?>
                                    </span>
                                    <!-- Status Badge -->
                                    <span class="meta-tag status-<?= $program['status'] ?>" style="margin-left:auto; background: var(--bg-dark); padding: 2px 8px; border-radius: 4px;">
                                        <?= match ($program['status']) {
                                            'draft' => '📝 Rascunho',
                                            'published', 'active', '' => '✅ Publicado',
                                            'archived' => '📦 Arquivado',
                                            default => '❓ ' . ucfirst($program['status'] ?? 'Desconhecido')
                                        } ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <p class="specialty-desc">
                            <?= htmlspecialchars($program['description'] ?? 'Sem descrição disponível.') ?>
                        </p>

                        <div class="specialty-footer">
                            <span class="xp-reward" title="XP Recompensa">
                                <span class="material-icons-round" style="font-size:14px">bolt</span>
                                <?= number_format($program['xp_reward']) ?>
                            </span>
                            <div class="card-actions">
                                <a href="<?= base_url($programs_tenantSlug . '/admin/programas/' . $program['id'] . '/editar') ?>" 
                                   class="btn-icon-action" title="Editar">
                                    <span class="material-icons-round" style="font-size:18px">edit</span>
                                </a>

                                <?php if ($program['status'] === 'published' || $program['status'] === 'active' || empty($program['status'])): ?>
                                    <a href="<?= base_url($programs_tenantSlug . '/admin/especialidades/prog_' . $program['id'] . '/atribuir') ?>" 
                                       class="btn-card-assign">
                                        <span class="material-icons-round" style="font-size:16px">group_add</span> Atribuir
                                    </a>
                                <?php elseif ($program['status'] === 'draft'): ?>
                                    <button class="btn-icon-action" title="Publicar" onclick="publishProgram(<?= $program['id'] ?>)">
                                        <span class="material-icons-round" style="font-size:18px; color: var(--accent-cyan);">rocket_launch</span>
                                    </button>
                                <?php endif; ?>
                                
                                <button class="btn-icon-action danger" title="Excluir" 
                                        onclick="window.deleteProgram(<?= $program['id'] ?>, '<?= htmlspecialchars($program['name']) ?>')">
                                    <span class="material-icons-round" style="font-size:18px">delete</span>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <!-- </main> removed as it breaks layout -->

    <!-- GSAP Premium Confirmation Modal -->
    <div class="gsap-modal-overlay" id="gsapConfirmModal">
        <div class="gsap-modal-card">
            <div class="gsap-icon-container">
                <div class="gsap-icon-glow"></div>
                <div class="gsap-icon" id="gsapConfirmIcon">🚀</div>
            </div>
            <h3 class="gsap-title" id="gsapConfirmTitle">Confirmar Ação</h3>
            <p class="gsap-message" id="gsapConfirmMessage">Tem certeza que deseja prosseguir?</p>
            <div class="gsap-actions">
                <button type="button" class="gsap-btn cancel" onclick="closeGsapConfirm(false)">Cancelar</button>
                <button type="button" class="gsap-btn confirm" id="gsapConfirmOk" onclick="closeGsapConfirm(true)">Confirmar</button>
            </div>
        </div>
    </div>

    <!-- Assignment Modal -->
    <div class="assign-modal-overlay" id="assignModal" onclick="closeAssignModal()">
        <div class="assign-modal" onclick="event.stopPropagation()">
            <div class="assign-modal-header">
                <h3>👥 Atribuir Programa</h3>
                <p id="assignProgramName">Selecione os desbravadores</p>
            </div>
            <div class="assign-modal-body">
                <div class="user-list" id="userList">
                    <div class="loading-users">⏳ Carregando usuários...</div>
                </div>
            </div>
            <div class="assign-modal-footer">
                <button type="button" class="btn-cancel" onclick="closeAssignModal()">Cancelar</button>
                <button type="button" class="btn-assign" id="btnAssign" onclick="submitAssignment()">
                    ✅ Atribuir Selecionados
                </button>
            </div>
        </div>
    </div>

    <div class="toast" id="toast"></div>

    <!-- Create Program Modal -->
    <?php require BASE_PATH . '/views/admin/programs/partials/create_modal.php'; ?>


    <script>
        window.programs_tenantSlug = '<?= $tenant['slug'] ?>';
        window.currentAssignProgramId = null;
        window.selectedUserIds = [];

        // Program assignment functions
        async function openAssignModal(programId, programName) {
            currentAssignProgramId = programId;
            selectedUserIds = [];
            document.getElementById('assignProgramName').textContent = programName;
            document.getElementById('userList').innerHTML = '<div class="loading-users">⏳ Carregando usuários...</div>';
            document.getElementById('assignModal').classList.add('active');

            try {
                const resp = await fetch(`/${programs_tenantSlug}/admin/programas/${programId}/users`);
                const data = await resp.json();

                if (data.success && data.users) {
                    renderUserList(data.users);
                } else {
                    document.getElementById('userList').innerHTML = '<div class="loading-users">❌ Erro ao carregar usuários</div>';
                }
            } catch (err) {
                document.getElementById('userList').innerHTML = '<div class="loading-users">❌ Erro de conexão</div>';
            }
        }

        function renderUserList(users) {
            if (users.length === 0) {
                document.getElementById('userList').innerHTML = '<div class="loading-users">Nenhum desbravador cadastrado</div>';
                return;
            }

            const html = users.map(user => {
                const initials = user.name.charAt(0).toUpperCase();
                const isAssigned = user.already_assigned == 1;
                return `
                    <div class="user-item ${isAssigned ? 'assigned' : ''}" 
                         data-user-id="${user.id}" 
                         onclick="${isAssigned ? '' : 'toggleUserSelection(this)'}">
                        <div class="user-avatar">
                            ${user.profile_picture
                        ? `<img src="${user.profile_picture}" alt="${user.name}">`
                        : initials}
                        </div>
                        <span class="user-name">${user.name}</span>
                        ${isAssigned ? '<span class="user-badge">Já atribuído</span>' : ''}
                    </div>
                `;
            }).join('');

            document.getElementById('userList').innerHTML = html;
        }

        function toggleUserSelection(el) {
            const userId = parseInt(el.dataset.userId);
            el.classList.toggle('selected');

            if (el.classList.contains('selected')) {
                if (!selectedUserIds.includes(userId)) {
                    selectedUserIds.push(userId);
                }
            } else {
                selectedUserIds = selectedUserIds.filter(id => id !== userId);
            }
        }

        function closeAssignModal() {
            document.getElementById('assignModal').classList.remove('active');
            currentAssignProgramId = null;
            selectedUserIds = [];
        }

        async function submitAssignment() {
            if (selectedUserIds.length === 0) {
                showToast('Selecione pelo menos um usuário', 'error');
                return;
            }

            const btn = document.getElementById('btnAssign');
            btn.disabled = true;
            btn.textContent = '⏳ Atribuindo...';

            try {
                const resp = await fetch(`/${programs_tenantSlug}/admin/programas/${currentAssignProgramId}/assign`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ user_ids: selectedUserIds })
                });
                const data = await resp.json();

                if (data.success) {
                    showToast(data.message);
                    closeAssignModal();
                } else {
                    showToast(data.error || 'Erro ao atribuir', 'error');
                }
            } catch (err) {
                showToast('Erro de conexão', 'error');
            } finally {
                btn.disabled = false;
                btn.textContent = '✅ Atribuir Selecionados';
            }
        }

        function filterByCategory(categoryId) {
            const url = new URL(window.location);
            if (categoryId) {
                url.searchParams.set('category', categoryId);
            } else {
                url.searchParams.delete('category');
            }
            window.location = url;
        }

        function filterByType(type) {
            const url = new URL(window.location);
            if (type) {
                url.searchParams.set('type', type);
            } else {
                url.searchParams.delete('type');
            }
            window.location = url;
        }

        function filterByStatus(status) {
            const url = new URL(window.location);
            if (status) {
                url.searchParams.set('status', status);
            } else {
                url.searchParams.delete('status');
            }
            window.location = url;
        }

        async function publishProgram(id) {
            const confirmed = await showConfirm({
                title: 'Publicar Programa',
                message: 'Publicar este programa? Ele ficará disponível para atribuição.',
                icon: '🚀',
                okText: 'Publicar'
            });
            if (!confirmed) return;

            try {
                const resp = await fetch(`/${programs_tenantSlug}/admin/programas/${id}/publish`, { method: 'POST' });
                const data = await resp.json();
                if (data.success) {
                    showToast(data.message);
                    setTimeout(() => location.reload(), 500);
                } else {
                    showToast(data.error || 'Erro', 'error');
                }
            } catch (err) {
                showToast('Erro de conexão', 'error');
            }
        }

        async function deleteProgram(id, name, force = false) {
            if (!force) {
                const confirmed = await showConfirm({
                    title: 'Excluir Programa',
                    message: `Excluir "${name}"? Esta ação não pode ser desfeita.`,
                    icon: '🗑️',
                    danger: true,
                    okText: 'Excluir'
                });
                if (!confirmed) return;
            }

            try {
                console.log('Deletando programa:', id, force ? '(FORÇADO)' : '');
                const url = `/${programs_tenantSlug}/admin/programas/${id}/delete${force ? '?force=true' : ''}`;
                const resp = await fetch(url, { method: 'POST' });
                const data = await resp.json();
                
                if (data.success) {
                    if (data.has_progress && !force) {
                        // Special case: handled as archive but we offer the forced delete option directly
                        const forceDelete = await showConfirm({
                            title: '☢️ EXCLUSÃO CRÍTICA',
                            message: `Este programa possui progresso de desbravadores! Ele foi <b>ARQUIVADO</b> para segurança.<br><br>Deseja realmente <b>APAGAR TUDO</b> (incluindo o progresso de todos os usuários)? Esta ação é permanente e destrutiva!`,
                            icon: '⚠️',
                            danger: true,
                            okText: 'Sim, Apagar Tudo',
                            cancelText: 'Manter Arquivado'
                        });

                        if (forceDelete) {
                            return deleteProgram(id, name, true);
                        } else {
                            location.reload();
                        }
                        return;
                    }

                    showToast(data.message);
                    const delay = data.message.includes('arquivado') ? 2000 : 800;
                    setTimeout(() => location.reload(), delay);
                } else {
                    showToast(data.error || 'Erro ao excluir', 'error');
                }
            } catch (err) {
                console.error('Erro na deleção:', err);
                showToast('Erro de conexão ou erro interno', 'error');
            }
        }
        window.deleteProgram = deleteProgram;

        function showToast(msg, type = 'success') {
            const toast = document.getElementById('toast');
            toast.textContent = msg;
            toast.className = 'toast ' + type + ' show';
            setTimeout(() => toast.classList.remove('show'), 3000);
        }

        // CSS Confirmation Modal System
        var programsConfirmCallback = null;

        function showConfirm(options) {
            return new Promise((resolve) => {
                const { title, message, icon = '🚀', danger = false, okText = 'Confirmar' } = options;

                document.getElementById('gsapConfirmIcon').textContent = icon;
                document.getElementById('gsapConfirmTitle').textContent = title;
                document.getElementById('gsapConfirmMessage').textContent = message;

                const okBtn = document.getElementById('gsapConfirmOk');
                okBtn.textContent = okText;
                okBtn.className = danger ? 'gsap-btn danger' : 'gsap-btn confirm';

                programsConfirmCallback = resolve;
                
                const overlay = document.getElementById('gsapConfirmModal');
                overlay.classList.remove('closing');
                overlay.classList.add('active');
            });
        }

        function closeGsapConfirm(result) {
            if (programsConfirmCallback) {
                programsConfirmCallback(result);
                programsConfirmCallback = null;
            }

            const overlay = document.getElementById('gsapConfirmModal');
            overlay.classList.add('closing');
            
            setTimeout(() => {
                overlay.classList.remove('active', 'closing');
            }, 300);
        }

        // Check for auto-open param
        // Check for auto-open param
        {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('action') === 'create') {
                openCreateProgramModal();
            }
        }


    </script>