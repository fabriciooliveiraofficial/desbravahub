<?php
/**
 * Create Invitation - Vibrant Light Edition v1.0
 * High Contrast, Modern Form
 */
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    :root {
        --font-primary: 'Inter', sans-serif;
        --bg-body: #f1f5f9;
        --bg-card: #ffffff;
        --text-dark: #0f172a;
        --text-medium: #334155;
        --text-light: #475569;
        
        --accent-primary: #06b6d4;
        --accent-hover: #0891b2;
        --border-color: #cbd5e1;
    }

    body {
        background-color: var(--bg-body) !important;
        font-family: var(--font-primary);
        color: var(--text-dark);
    }

    .form-wrapper {
        max-width: 650px;
        margin: 0 auto;
        padding-bottom: 60px;
        animation: fadeIn 0.4s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Header */
    .page-header {
        margin-bottom: 32px;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--text-medium);
        text-decoration: none;
        font-weight: 700;
        margin-bottom: 16px;
        width: fit-content;
        transition: color 0.2s;
    }
    .btn-back:hover { color: var(--text-dark); }

    .page-header h1 {
        font-size: 1.8rem;
        font-weight: 800;
        margin: 0;
        color: #000;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    /* Card */
    .form-card {
        background: white;
        border-radius: 24px;
        border: 2px solid var(--border-color);
        padding: 40px;
        box-shadow: 0 10px 30px -10px rgba(0,0,0,0.1);
    }

    .form-group {
        margin-bottom: 28px;
    }

    .form-group label {
        display: block;
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 10px;
    }

    .required { color: #ef4444; margin-left: 2px; }

    .form-control {
        width: 100%;
        padding: 14px 18px;
        background: #f8fafc;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        font-size: 1rem;
        color: var(--text-dark);
        transition: all 0.2s;
        outline: none;
    }

    .form-control:focus {
        border-color: var(--accent-primary);
        background: white;
        box-shadow: 0 0 0 4px rgba(6, 182, 212, 0.1);
    }

    .form-hint {
        font-size: 0.85rem;
        color: var(--text-light);
        margin-top: 8px;
        font-weight: 500;
    }

    /* Role Cards */
    .role-grid {
        display: grid;
        gap: 12px;
    }

    .role-card {
        cursor: pointer;
        position: relative;
    }
    
    .role-card input { display: none; }

    .role-content {
        padding: 16px 20px;
        background: white;
        border: 2px solid #e2e8f0;
        border-radius: 16px;
        display: flex;
        align-items: center;
        gap: 16px;
        transition: all 0.2s;
    }

    .role-card:hover .role-content {
        border-color: #cbd5e1;
        background: #f8fafc;
    }

    .role-card input:checked + .role-content {
        border-color: var(--accent-primary);
        background: #ecfeff;
        box-shadow: 0 4px 12px rgba(6, 182, 212, 0.1);
    }

    .role-icon {
        width: 48px;
        height: 48px;
        background: #f1f5f9;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        transition: all 0.2s;
    }

    .role-card input:checked + .role-content .role-icon {
        background: var(--accent-primary);
        color: white;
    }

    .role-text {
        display: flex;
        flex-direction: column;
    }

    .role-name {
        font-weight: 700;
        color: var(--text-dark);
        font-size: 1rem;
    }

    .role-desc {
        font-size: 0.85rem;
        color: var(--text-medium);
        line-height: 1.4;
    }

    /* Actions */
    .form-actions {
        margin-top: 40px;
        display: flex;
        justify-content: flex-end;
    }

    .btn-submit {
        background: var(--accent-primary);
        color: white;
        padding: 14px 40px;
        border-radius: 100px;
        font-weight: 700;
        font-size: 1rem;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.2s;
        box-shadow: 0 4px 10px rgba(6, 182, 212, 0.3);
    }
    
    .btn-submit:hover {
        background: var(--accent-hover);
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(6, 182, 212, 0.4);
    }

    /* Flash Box */
    .flash-box {
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 24px;
        background: #fee2e2;
        color: #b91c1c;
        border: 1px solid #fecaca;
        display: flex;
        align-items: center;
        gap: 12px;
        font-weight: 600;
    }

</style>

<div class="form-wrapper">
    <a href="<?= base_url($tenant['slug'] . '/admin/convites') ?>" class="btn-back">
        <span class="material-icons-round" style="font-size: 18px;">arrow_back</span>
        Voltar para a lista
    </a>

    <div class="page-header">
        <h1><span>✉️</span> Novo Convite de Liderança</h1>
        <p style="color: var(--text-medium); font-weight: 500;">O acesso de administração será concedido após o aceite.</p>
    </div>

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="flash-box">
            <span class="material-icons-round">error</span>
            <?= htmlspecialchars($_SESSION['flash_error']) ?>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <form action="<?= base_url($tenant['slug'] . '/admin/convites/enviar') ?>" method="POST" class="form-card">
        
        <div class="form-group">
            <label>E-mail do convidado <span class="required">*</span></label>
            <input type="email" name="email" class="form-control" placeholder="ex: diretor@clube.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            <div class="form-hint">Enviaremos um link de ativação exclusivo para este endereço.</div>
        </div>

        <div class="form-group">
            <label>Nome (opcional)</label>
            <input type="text" name="name" class="form-control" placeholder="Como devemos chamá-lo?" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
            <div class="form-hint">Se preenchido, o e-mail será mais amigável.</div>
        </div>

        <div class="form-group">
            <label>Selecione o Cargo <span class="required">*</span></label>
            <div class="role-grid">
                <?php foreach ($roles as $key => $label): ?>
                    <label class="role-card">
                        <input type="radio" name="role_name" value="<?= $key ?>" required <?= $key === 'associate_director' ? 'checked' : '' ?>>
                        <div class="role-content">
                            <div class="role-icon">
                                <span class="material-icons-round">
                                    <?= $key === 'associate_director' ? 'admin_panel_settings' : ($key === 'counselor' ? 'school' : 'auto_stories') ?>
                                </span>
                            </div>
                            <div class="role-text">
                                <span class="role-name"><?= htmlspecialchars($label) ?></span>
                                <span class="role-desc">
                                    <?= $key === 'associate_director' ? 'Acesso total à administração do sistema.' : ($key === 'counselor' ? 'Gestão de unidades e acompanhamento de membros.' : 'Focado em classes, especialidades e ensino.') ?>
                                </span>
                            </div>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-submit">
                Enviar Convite Agora
                <span class="material-icons-round">send</span>
            </button>
        </div>
    </form>
</div>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
<?php require BASE_PATH . '/views/admin/partials/footer.php'; ?>


<?php require BASE_PATH . '/views/admin/partials/footer.php'; ?>