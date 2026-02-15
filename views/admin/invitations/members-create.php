<?php
/**
 * Create Member Invitation - Master Design v2.0
 * Standard Admin Layout
 */
?>

<!-- Header -->
<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem;">
    <div style="display: flex; align-items: center; gap: 1rem;">
        <a href="<?= base_url($tenant['slug'] . '/admin/convites/membros') ?>" 
           class="btn btn-sm btn-outline" 
           style="border: 1px solid var(--border-color); color: var(--text-muted); padding: 0.5rem; border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
            <span class="material-icons-round">arrow_back</span>
        </a>
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--text-dark); margin: 0;">Novo Convite</h1>
            <p style="color: var(--text-muted); font-size: 0.875rem; margin: 0;">Membros</p>
        </div>
    </div>
</div>

<div style="max-width: 800px; margin: 0 auto;">

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="dashboard-card" style="padding: 1rem; border-left: 4px solid #ef4444; margin-bottom: 2rem; flex-direction: row; align-items: center; gap: 0.75rem;">
            <span class="material-icons-round" style="color: #ef4444;">error</span>
            <span style="font-weight: 600; color: #b91c1c;"><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <form action="<?= base_url($tenant['slug'] . '/admin/convites/membros/enviar') ?>" method="POST" class="dashboard-card" style="padding: 2rem;">
        
        <div class="form-group" style="margin-bottom: 1.5rem;">
            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-dark);">
                E-mail do Membro <span style="color: var(--primary);">*</span>
            </label>
            <div style="position: relative;">
                <span class="material-icons-round" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);">email</span>
                <input type="email" name="email" class="form-control" placeholder="ex: desbravador@email.com" required 
                       style="padding-left: 3rem;">
            </div>
            <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.5rem; display: flex; align-items: center; gap: 0.25rem;">
                <span class="material-icons-round" style="font-size: 14px;">info</span>
                Enviaremos um link de ativação exclusivo para este endereço.
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 1.5rem;">
            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-dark);">
                Nome Completo (Opcional)
            </label>
            <div style="position: relative;">
                <span class="material-icons-round" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);">person</span>
                <input type="text" name="name" class="form-control" placeholder="ex: João Silva"
                       style="padding-left: 3rem;">
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 1.5rem;">
            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-dark);">
                Função Principal <span style="color: var(--primary);">*</span>
            </label>
            <div style="position: relative;">
                <span class="material-icons-round" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);">badge</span>
                <select name="role_name" class="form-control" required style="padding-left: 3rem; appearance: none; -webkit-appearance: none;">
                    <?php foreach ($roles as $key => $label): ?>
                        <option value="<?= $key ?>"><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
                <span class="material-icons-round" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); pointer-events: none;">expand_more</span>
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 2rem;">
            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-dark);">
                Mensagem de Boas-Vindas (Opcional)
            </label>
            <textarea name="custom_message" class="form-control" rows="4" placeholder="Escreva algo especial para o novo membro..." style="min-height: 120px;"></textarea>
        </div>

        <div style="display: flex; justify-content: flex-end; padding-top: 1rem; border-top: 1px solid var(--border-color);">
            <button type="submit" class="btn btn-primary btn-lg" style="display: flex; align-items: center; gap: 0.5rem;">
                Enviar Convite
                <span class="material-icons-round">send</span>
            </button>
        </div>
    </form>
</div>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
<?php require BASE_PATH . '/views/admin/partials/footer.php'; ?>
