<style>
        .settings-container {
            max-width: 700px;
            margin: 0 auto;
        }
        
        .page-toolbar {
            margin-bottom: 24px;
        }
        
        .page-toolbar h2 {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-dark);
            margin: 0;
        }
        
        .settings-card {
            background: var(--bg-card);
            border: 1px solid var(--border-light);
            border-radius: 16px;
            padding: 32px;
        }
        
        .section-title {
            font-size: 1.1rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border-light);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-primary);
        }
        
        .form-group .required {
            color: var(--accent-danger);
        }
        
        .form-control {
            width: 100%;
            padding: 14px 16px;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid var(--border-light);
            border-radius: 10px;
            color: var(--text-primary);
            font-size: 1rem;
            transition: all 0.2s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--accent-cyan);
            box-shadow: 0 0 0 3px rgba(0, 217, 255, 0.1);
        }
        
        .form-hint {
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin-top: 6px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 16px;
        }
        
        .verification-status {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px;
            border-radius: 10px;
            margin-bottom: 24px;
        }
        
        .verification-status.verified {
            background: rgba(0, 255, 136, 0.1);
            border: 1px solid rgba(0, 255, 136, 0.3);
            color: var(--accent-green);
        }
        
        .verification-status.unverified {
            background: rgba(247, 179, 43, 0.1);
            border: 1px solid rgba(247, 179, 43, 0.3);
            color: var(--accent-warning);
        }
        
        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 32px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 14px 28px;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--accent-cyan), var(--accent-green));
            color: #0a0a14;
        }
        
        .btn-secondary {
            background: var(--bg-card);
            border: 1px solid var(--border-light);
            color: var(--text-primary);
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .flash-message {
            padding: 16px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .flash-success {
            background: rgba(0, 255, 136, 0.1);
            border: 1px solid rgba(0, 255, 136, 0.3);
            color: var(--accent-green);
        }
        
        .flash-error {
            background: rgba(255, 107, 107, 0.1);
            border: 1px solid rgba(255, 107, 107, 0.3);
            color: var(--accent-danger);
        }
        
        .provider-hints {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            padding: 20px;
            margin-top: 24px;
        }
        
        .provider-hints h4 {
            margin-bottom: 12px;
            color: var(--text-secondary);
        }
        
        .provider-hint {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid var(--border-light);
            font-size: 0.9rem;
        }
        
        .provider-hint:last-child {
            border-bottom: none;
        }
        
        .provider-name {
            font-weight: 500;
        }
        
        .provider-settings {
            color: var(--text-secondary);
            font-family: monospace;
        }

        @media (max-width: 768px) {
            .settings-card {
                padding: 20px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>


        <div class="settings-container">
            <?php if (!empty($_SESSION['flash_success'])): ?>
                <div class="flash-message flash-success">
                    ✅ <?= htmlspecialchars($_SESSION['flash_success']) ?>
                </div>
                <?php unset($_SESSION['flash_success']); ?>
            <?php endif; ?>
            
            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="flash-message flash-error">
                    ❌ <?= htmlspecialchars($_SESSION['flash_error']) ?>
                </div>
                <?php unset($_SESSION['flash_error']); ?>
            <?php endif; ?>
            
            <div class="page-toolbar">
                <div class="page-info">
                    <h2 class="header-title">Configurações de Email</h2>
                    <p class="text-muted">Configure o servidor SMTP para enviar emails do clube</p>
                </div>
            </div>
            
            <?php if (!empty($migrationNeeded) && $migrationNeeded): ?>
                <div class="flash-message flash-error" style="flex-direction: column; align-items: flex-start;">
                    <strong>⚠️ Migration necessária</strong>
                    <p style="margin: 8px 0 0 0;">As tabelas do sistema de email ainda não foram criadas. Execute a migration acessando:</p>
                    <code style="background: rgba(0,0,0,0.3); padding: 8px 12px; border-radius: 6px; margin-top: 8px; display: block;">
                        <?= rtrim($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'], '/') ?>/migrate-email-system.php
                    </code>
                </div>
            <?php endif; ?>
            
            <form action="<?= base_url($tenant['slug'] . '/admin/email/settings') ?>" method="POST" class="settings-card">
                <!-- Verification Status -->
                <?php if ($settings): ?>
                    <div class="verification-status <?= $settings['is_verified'] ? 'verified' : 'unverified' ?>">
                        <?php if ($settings['is_verified']): ?>
                            ✅ Configuração verificada em <?= date('d/m/Y H:i', strtotime($settings['verified_at'])) ?>
                        <?php else: ?>
                            ⚠️ Configuração não verificada. Faça um teste para verificar.
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <h3 class="section-title">📧 Servidor SMTP</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Servidor SMTP <span class="required">*</span></label>
                        <input type="text" name="smtp_host" class="form-control" 
                               placeholder="smtp.gmail.com"
                               value="<?= htmlspecialchars($settings['smtp_host'] ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Porta <span class="required">*</span></label>
                        <input type="number" name="smtp_port" class="form-control" 
                               placeholder="587"
                               value="<?= htmlspecialchars($settings['smtp_port'] ?? '587') ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Usuário/Email <span class="required">*</span></label>
                    <input type="text" name="smtp_user" class="form-control" 
                           placeholder="seu-email@gmail.com"
                           value="<?= htmlspecialchars($settings['smtp_user'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Senha/App Password <?= $settings ? '' : '<span class="required">*</span>' ?></label>
                    <input type="password" name="smtp_pass" class="form-control" 
                           placeholder="<?= $settings ? '••••••••' : 'Senha do servidor SMTP' ?>"
                           <?= $settings ? '' : 'required' ?>>
                    <div class="form-hint">
                        Para Gmail, use uma "senha de app". <a href="https://myaccount.google.com/apppasswords" target="_blank" style="color: var(--accent-cyan);">Criar senha de app</a>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Criptografia</label>
                    <select name="encryption" class="form-control">
                        <option value="tls" <?= ($settings['encryption'] ?? 'tls') === 'tls' ? 'selected' : '' ?>>TLS (Recomendado)</option>
                        <option value="ssl" <?= ($settings['encryption'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                        <option value="none" <?= ($settings['encryption'] ?? '') === 'none' ? 'selected' : '' ?>>Nenhuma</option>
                    </select>
                </div>
                
                <h3 class="section-title" style="margin-top: 32px;">📝 Remetente</h3>
                
                <div class="form-group">
                    <label>Email do Remetente <span class="required">*</span></label>
                    <input type="email" name="from_email" class="form-control" 
                           placeholder="clube@exemplo.com"
                           value="<?= htmlspecialchars($settings['from_email'] ?? '') ?>" required>
                    <div class="form-hint">Este email aparecerá como remetente para os destinatários</div>
                </div>
                
                <div class="form-group">
                    <label>Nome do Remetente <span class="required">*</span></label>
                    <input type="text" name="from_name" class="form-control" 
                           placeholder="<?= htmlspecialchars($tenant['name']) ?>"
                           value="<?= htmlspecialchars($settings['from_name'] ?? $tenant['name']) ?>" required>
                </div>
                
                <div class="form-actions">
                    <a href="<?= base_url($tenant['slug'] . '/admin/email') ?>" class="btn-toolbar secondary">
                        <span class="material-icons-round">arrow_back</span> Voltar
                    </a>
                    <button type="submit" class="btn-toolbar primary">
                        <span class="material-icons-round">save</span> Salvar Configurações
                    </button>
                    <?php if ($settings): ?>
                        <button type="button" class="btn-toolbar secondary" onclick="testConnection()">
                            <span class="material-icons-round">power</span> Testar Conexão
                        </button>
                    <?php endif; ?>
                </div>
                
                <div class="provider-hints">
                    <h4>📋 Configurações comuns de provedores</h4>
                    <div class="provider-hint">
                        <span class="provider-name">Gmail</span>
                        <span class="provider-settings">smtp.gmail.com:587 (TLS)</span>
                    </div>
                    <div class="provider-hint">
                        <span class="provider-name">Outlook/Hotmail</span>
                        <span class="provider-settings">smtp-mail.outlook.com:587 (TLS)</span>
                    </div>
                    <div class="provider-hint">
                        <span class="provider-name">Yahoo</span>
                        <span class="provider-settings">smtp.mail.yahoo.com:587 (TLS)</span>
                    </div>
                    <div class="provider-hint">
                        <span class="provider-name">Hostinger</span>
                        <span class="provider-settings">smtp.hostinger.com:587 (TLS)</span>
                    </div>
                </div>
            </form>
        </div>

<!-- Toast Container -->
<div id="toast-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 10px;"></div>

<script>
(function() {
    var tenantSlug = '<?= $tenant['slug'] ?>';
    
    function showToast(message, type = 'info') {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        
        const bgColor = type === 'success' 
            ? 'rgba(0, 255, 136, 0.95)' 
            : type === 'error' 
                ? 'rgba(255, 107, 107, 0.95)' 
                : 'rgba(0, 217, 255, 0.95)';
        
        const textColor = type === 'success' ? '#0a0a14' : type === 'error' ? '#fff' : '#0a0a14';
        const icon = type === 'success' ? '✅' : type === 'error' ? '❌' : 'ℹ️';
        
        toast.style.cssText = `
            background: ${bgColor};
            color: ${textColor};
            padding: 16px 24px;
            border-radius: 12px;
            font-weight: 500;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.3s ease-out;
            max-width: 400px;
        `;
        
        toast.innerHTML = `<span style="font-size: 1.2rem;">${icon}</span><span>${message}</span>`;
        container.appendChild(toast);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease-in forwards';
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }
    
    // Expose testConnection globally for the onclick handler
    window.testConnection = async function() {
        const btn = event.target;
        btn.disabled = true;
        btn.textContent = '⏳ Testando...';
        
        try {
            const response = await fetch(`/${tenantSlug}/admin/email/test`, {
                method: 'POST'
            });
            
            const data = await response.json();
            
            if (data.success) {
                showToast(data.message, 'success');
                setTimeout(() => location.reload(), 2000);
            } else {
                showToast(data.error, 'error');
            }
        } catch (err) {
            showToast('Erro de conexão com o servidor', 'error');
        } finally {
            btn.disabled = false;
            btn.textContent = '🔌 Testar Conexão';
        }
    };
})();
</script>

<style>
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
</style>

