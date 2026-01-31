<?php
/**
 * Admin: Create Learning Program
 */
$typeLabel = ($type ?? 'specialty') === 'class' ? 'Classe' : 'Especialidade';
$pageTitle = 'Nova ' . $typeLabel;
$pageIcon = 'add_circle';
?>

    <style>
        .form-card {
            background: var(--bg-card);
            border: 1px solid var(--border-light);
            border-radius: 12px;
            padding: 32px;
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            overflow: hidden;
        }

        .form-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #00d9ff, #00ff88);
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            background: var(--bg-input);
            border: 1px solid var(--border-light);
            border-radius: 8px;
            color: var(--text-primary);
            font-size: 1rem;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 3px rgba(0, 217, 255, 0.1);
        }

        textarea.form-control {
            resize: vertical;
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            padding: 12px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 8px;
            border: 1px solid var(--border-light);
            transition: all 0.2s ease;
        }

        .form-check:hover {
            border-color: var(--accent-blue);
            background: rgba(0, 217, 255, 0.05);
        }

        .form-check input[type="checkbox"] {
            width: 20px;
            height: 20px;
            accent-color: var(--accent-blue);
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--accent-blue), var(--accent-green));
            color: #1a1a2e;
            border: none;
            padding: 12px 32px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 217, 255, 0.3);
        }

        .btn-submit:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .btn-cancel {
            padding: 12px 24px;
            background: transparent;
            border: 1px solid var(--border-light);
            border-radius: 8px;
            color: var(--text-primary);
            cursor: pointer;
            margin-right: 12px;
            text-decoration: none;
        }

        .form-footer {
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid var(--border-light);
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        .toast {
            position: fixed;
            bottom: 24px;
            right: 24px;
            padding: 16px 24px;
            background: var(--bg-card);
            border-left: 4px solid var(--accent-green);
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            transform: translateX(150%);
            transition: transform 0.3s ease;
            z-index: 1001;
        }

        .toast.show {
            transform: translateX(0);
        }

        .toast.error {
            border-left-color: #f44336;
        }
    </style>

    <div class="content-container">
            <a href="<?= base_url($tenant['slug'] . '/admin/programas') ?>" class="back-link">
                <span class="material-icons-round" style="font-size: 18px;">arrow_back</span> Voltar para Programas
            </a>

            <div class="form-card">
                <form id="createForm" onsubmit="submitForm(event)">
                    <input type="hidden" name="type" value="<?= htmlspecialchars($type ?? 'specialty') ?>">

                    <div class="form-group" style="position: relative;">
                        <label>Nome *</label>
                        <input type="text" name="name" id="programName" class="form-control" required
                            placeholder="Ex: Primeiros Socorros" autocomplete="off">
                        <div id="programNameAutocomplete" style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: var(--bg-card, #fff); border: 1px solid var(--border-color, #ddd); border-radius: 8px; max-height: 200px; overflow-y: auto; z-index: 100; box-shadow: 0 4px 12px rgba(0,0,0,0.1);"></div>
                        <div id="programNameWarning" style="display: none; color: #f7b32b; font-size: 0.85rem; margin-top: 4px;">
                            ⚠️ Já existe uma especialidade/programa com nome similar
                        </div>
                    </div>

                    <script type="module">
                        (function() {
                            function initProgramAutocomplete() {
                                const input = document.getElementById('programName');
                                const dropdown = document.getElementById('programNameAutocomplete');
                                const warning = document.getElementById('programNameWarning');

                                if (!input || !dropdown || input.dataset.autocompleteInitialized) return;
                                input.dataset.autocompleteInitialized = 'true';

                                let debounceTimer = null;

                                input.addEventListener('input', () => {
                                    clearTimeout(debounceTimer);
                                    const query = input.value.trim();
                                    
                                    if (query.length < 2) {
                                        dropdown.style.display = 'none';
                                        warning.style.display = 'none';
                                        return;
                                    }

                                    debounceTimer = setTimeout(async () => {
                                        try {
                                            const response = await fetch(`<?= base_url($tenant['slug'] . '/api/specialties/search') ?>?q=${encodeURIComponent(query)}`);
                                            const data = await response.json();

                                            if (data.results && data.results.length > 0) {
                                                dropdown.innerHTML = data.results.map(s => `
                                                    <div class="autocomplete-item" style="padding: 10px 12px; cursor: pointer; display: flex; align-items: center; gap: 8px; border-bottom: 1px solid rgba(0,0,0,0.05);">
                                                        <span style="font-size: 1.2rem;">${s.badge_icon || '📘'}</span>
                                                        <span>${s.name}</span>
                                                    </div>
                                                `).join('');
                                                dropdown.style.display = 'block';
                                                warning.style.display = 'block';

                                                dropdown.querySelectorAll('.autocomplete-item').forEach(item => {
                                                    item.addEventListener('click', () => {
                                                        input.value = item.querySelector('span:last-child').textContent;
                                                        dropdown.style.display = 'none';
                                                    });
                                                    item.addEventListener('mouseenter', () => item.style.background = 'rgba(0,0,0,0.05)');
                                                    item.addEventListener('mouseleave', () => item.style.background = 'transparent');
                                                });
                                            } else {
                                                dropdown.style.display = 'none';
                                                warning.style.display = 'none';
                                            }
                                        } catch (err) {
                                            console.error('Autocomplete error:', err);
                                        }
                                    }, 300);
                                });

                                document.addEventListener('click', (e) => {
                                    if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                                        dropdown.style.display = 'none';
                                    }
                                });
                            }

                            if (typeof htmx !== 'undefined') {
                                htmx.onLoad(() => initProgramAutocomplete());
                            }
                            if (document.readyState === 'loading') {
                                document.addEventListener('DOMContentLoaded', initProgramAutocomplete);
                            } else {
                                initProgramAutocomplete();
                            }
                        })();
                    </script>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Categoria</label>
                            <select name="category_id" class="form-control">
                                <option value="">Sem categoria</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" style="color: <?= $cat['color'] ?>;">
                                        <?php if (!str_starts_with($cat['icon'] ?? '', 'fa-')): ?>
                                            <?= $cat['icon'] ?> 
                                        <?php endif; ?>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Ícone (emoji)</label>
                            <input type="text" name="icon" class="form-control" value="📘" maxlength="4">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Descrição</label>
                        <textarea name="description" class="form-control" rows="3"
                            placeholder="Descrição do programa..."></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Duração (horas)</label>
                            <input type="number" name="duration_hours" class="form-control" value="4" min="1" max="200">
                        </div>

                        <div class="form-group">
                            <label>Dificuldade (1-5)</label>
                            <select name="difficulty" class="form-control">
                                <option value="1">⭐ Iniciante</option>
                                <option value="2">⭐⭐ Básico</option>
                                <option value="3" selected>⭐⭐⭐ Intermediário</option>
                                <option value="4">⭐⭐⭐⭐ Avançado</option>
                                <option value="5">⭐⭐⭐⭐⭐ Expert</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Recompensa XP</label>
                            <input type="number" name="xp_reward" class="form-control" value="100" min="0" step="10">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-check">
                            <input type="checkbox" name="is_outdoor" value="1">
                            <span>🏕️ Programa Outdoor (prático, sem perguntas interativas)</span>
                        </label>
                        <small>Programas outdoor exigem envio de provas para aprovação manual.</small>
                    </div>

                    <div class="form-footer">
                        <a href="<?= base_url($tenant['slug'] . '/admin/programas') ?>" class="btn-cancel">Cancelar</a>
                        <button type="submit" class="btn-submit" id="btnSubmit">🚀 Criar e Editar Requisitos</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <div class="toast" id="toast"></div>

    <script>
        var tenantSlug = '<?= $tenant['slug'] ?>';

        async function submitForm(e) {
            e.preventDefault();
            const form = new FormData(document.getElementById('createForm'));
            const btn = document.getElementById('btnSubmit');
            btn.disabled = true;
            btn.textContent = '⏳ Criando...';

            try {
                const resp = await fetch(`/${tenantSlug}/admin/programas`, { method: 'POST', body: form });
                const data = await resp.json();

                if (data.success && data.redirect) {
                    showToast('Programa criado com sucesso!');
                    setTimeout(() => window.location = data.redirect, 500);
                } else {
                    showToast(data.error || 'Erro ao criar', 'error');
                    btn.disabled = false;
                    btn.textContent = '🚀 Criar e Editar Requisitos';
                }
            } catch (err) {
                showToast('Erro de conexão', 'error');
                btn.disabled = false;
                btn.textContent = '🚀 Criar e Editar Requisitos';
            }
        }

        function showToast(msg, type = 'success') {
            const toast = document.getElementById('toast');
            toast.textContent = msg;
            toast.className = 'toast ' + type + ' show';
            setTimeout(() => toast.classList.remove('show'), 3000);
        }
    </script>
</body>

</html>