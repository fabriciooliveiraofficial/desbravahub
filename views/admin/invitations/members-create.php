<?php
/**
 * Member Invitation - Vibrant Light Edition v1.0
 * High Contrast, Clean Layout
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
        
        --accent-primary: #6366f1; /* Indigo for Members */
        --accent-hover: #4f46e5;
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

    .page-header {
        margin-bottom: 32px;
    }

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
        margin-bottom: 24px;
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
        font-family: inherit;
    }

    .form-control:focus {
        border-color: var(--accent-primary);
        background: white;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
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
        box-shadow: 0 4px 10px rgba(99, 102, 241, 0.3);
    }
    
    .btn-submit:hover {
        background: var(--accent-hover);
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4);
    }

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
    <a href="<?= base_url($tenant['slug'] . '/admin/convites/membros') ?>" class="btn-back">
        <span class="material-icons-round" style="font-size: 18px;">arrow_back</span>
        Voltar para Membros
    </a>

    <div class="page-header">
        <h1><span>💌</span> Convidar Novo Membro</h1>
        <p style="color: var(--text-medium); font-weight: 500; margin-top: 8px;">Envie um convite amigável para novos membros do clube.</p>
    </div>

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="flash-box">
            <span class="material-icons-round">error</span>
            <?= htmlspecialchars($_SESSION['flash_error']) ?>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <form action="<?= base_url($tenant['slug'] . '/admin/convites/membros/enviar') ?>" method="POST" class="form-card">
        
        <div class="form-group">
            <label>E-mail do Membro <span class="required">*</span></label>
            <input type="email" name="email" class="form-control" placeholder="ex: desbravador@email.com" required>
        </div>

        <div class="form-group">
            <label>Nome Completo (Opcional)</label>
            <input type="text" name="name" class="form-control" placeholder="ex: João Silva">
        </div>

        <div class="form-group">
            <label>Função Principal <span class="required">*</span></label>
            <select name="role_name" class="form-control" required>
                <?php foreach ($roles as $key => $label): ?>
                    <option value="<?= $key ?>"><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Mensagem de Boas-Vindas (Opcional)</label>
            <textarea name="custom_message" class="form-control" rows="4" placeholder="Escreva algo especial para o novo membro..."></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-submit">
                🚀 Enviar Convite
            </button>
        </div>
    </form>
</div>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
<?php require BASE_PATH . '/views/admin/partials/footer.php'; ?>
