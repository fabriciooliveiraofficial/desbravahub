<?php
/**
 * Create Invitation - Master Design v2.0
 * Standard Admin Layout
 */
?>

<!-- Header -->
<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem;">
    <div style="display: flex; align-items: center; gap: 1rem;">
        <a href="<?= base_url($tenant['slug'] . '/admin/convites') ?>" 
           class="btn btn-sm btn-outline" 
           style="border: 1px solid var(--border-color); color: var(--text-muted); padding: 0.5rem; border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
            <span class="material-icons-round">arrow_back</span>
        </a>
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--text-dark); margin: 0;">Novo Convite</h1>
            <p style="color: var(--text-muted); font-size: 0.875rem; margin: 0;">Liderança</p>
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

    <form action="<?= base_url($tenant['slug'] . '/admin/convites/enviar') ?>" method="POST" class="dashboard-card" style="padding: 2rem;">
        
        <div class="form-group" style="margin-bottom: 1.5rem;">
            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-dark);">
                E-mail do convidado <span style="color: var(--primary);">*</span>
            </label>
            <div style="position: relative;">
                <span class="material-icons-round" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);">email</span>
                <input type="email" name="email" class="form-control" placeholder="ex: diretor@clube.com" required 
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       style="padding-left: 3rem;">
            </div>
            <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.5rem; display: flex; align-items: center; gap: 0.25rem;">
                <span class="material-icons-round" style="font-size: 14px;">info</span>
                Enviaremos um link de ativação exclusivo para este endereço.
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 2rem;">
            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-dark);">
                Nome (opcional)
            </label>
            <div style="position: relative;">
                <span class="material-icons-round" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);">person</span>
                <input type="text" name="name" class="form-control" placeholder="Como devemos chamá-lo?" 
                       value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                       style="padding-left: 3rem;">
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 2rem;">
            <label style="display: block; font-weight: 600; margin-bottom: 1rem; color: var(--text-dark);">
                Selecione o Cargo <span style="color: var(--primary);">*</span>
            </label>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem;">
                <?php foreach ($roles as $key => $label): ?>
                    <label style="cursor: pointer; position: relative;">
                        <input type="radio" name="role_name" value="<?= $key ?>" required <?= $key === 'associate_director' ? 'checked' : '' ?> 
                               style="position: absolute; opacity: 0; width: 0; height: 0;"
                               onchange="document.querySelectorAll('.role-card-inner').forEach(el => {
                                   el.style.borderColor = 'var(--border-color)';
                                   el.style.backgroundColor = 'transparent';
                                   el.querySelector('.role-icon').style.backgroundColor = 'var(--bg-hover)';
                                   el.querySelector('.role-icon').style.color = 'var(--text-muted)';
                               });
                               const inner = this.nextElementSibling;
                               if(this.checked) {
                                   inner.style.borderColor = 'var(--primary)';
                                   inner.style.backgroundColor = 'rgba(var(--primary-rgb), 0.05)';
                                   inner.querySelector('.role-icon').style.backgroundColor = 'var(--primary)';
                                   inner.querySelector('.role-icon').style.color = 'white';
                               }">
                        <div class="role-card-inner" style="
                            padding: 1.25rem; 
                            border: 2px solid <?= $key === 'associate_director' ? 'var(--primary)' : 'var(--border-color)' ?>; 
                            border-radius: var(--radius-lg); 
                            display: flex; 
                            align-items: center; 
                            gap: 1rem;
                            transition: all 0.2s;
                            background-color: <?= $key === 'associate_director' ? 'rgba(var(--primary-rgb), 0.05)' : 'transparent' ?>;
                        ">
                            <div class="role-icon" style="
                                width: 40px; height: 40px; 
                                border-radius: 50%; 
                                display: flex; align-items: center; justify-content: center;
                                background-color: <?= $key === 'associate_director' ? 'var(--primary)' : 'var(--bg-hover)' ?>;
                                color: <?= $key === 'associate_director' ? 'white' : 'var(--text-muted)' ?>;
                                transition: all 0.2s;
                            ">
                                <span class="material-icons-round">
                                    <?= $key === 'associate_director' ? 'admin_panel_settings' : ($key === 'counselor' ? 'school' : 'auto_stories') ?>
                                </span>
                            </div>
                            <div>
                                <div style="font-weight: 600; color: var(--text-dark); margin-bottom: 0.25rem;"><?= htmlspecialchars($label) ?></div>
                                <div style="font-size: 0.75rem; color: var(--text-muted); line-height: 1.4;">
                                    <?= $key === 'associate_director' ? 'Acesso total à administração.' : ($key === 'counselor' ? 'Gestão de unidades e membros.' : 'Focado em ensino e especialidades.') ?>
                                </div>
                            </div>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>
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