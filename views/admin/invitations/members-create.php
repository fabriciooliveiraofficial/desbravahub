<?php
/**
 * Create Member Invitation - Elegance & Precision v1.0
 * Refined form for member invitations and recruitment.
 */
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    :root {
        --inv-primary: #334155;
        --inv-accent: <?= $tenant['accent_color'] ?? '#3b82f6' ?>;
        --inv-bg: transparent;
        --inv-card-bg: #ffffff;
        --inv-border: #f1f5f9;
        --inv-text-main: #334155;
        --inv-text-muted: #64748b;
        --inv-radius-lg: 32px;
        --inv-radius-md: 20px;
        --inv-shadow: 0 10px 30px -10px rgba(51, 65, 85, 0.06);
    }

    .inv-container {
        padding: 16px;
        width: 100%;
        min-height: 100vh;
        background: var(--inv-bg);
        font-family: 'Inter', sans-serif;
    }

    .inv-header-sticky {
        position: sticky;
        top: 0;
        z-index: 100;
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(16px);
        margin: -16px -16px 32px -16px;
        padding: 20px 32px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid rgba(226, 232, 240, 0.8);
    }

    .inv-back-btn {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        border: 1px solid var(--inv-border);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--inv-text-muted);
        text-decoration: none;
        transition: all 0.3s;
        background: white;
    }

    .inv-back-btn:hover {
        background: #f1f5f9;
        color: var(--inv-accent);
        transform: translateX(-4px);
    }

    .inv-form-card {
        background: var(--inv-card-bg);
        border: 1px solid var(--inv-border);
        border-radius: var(--inv-radius-lg);
        padding: 48px;
        box-shadow: var(--inv-shadow);
        max-width: 800px;
        margin: 0 auto;
    }

    .inv-form-header {
        margin-bottom: 40px;
        text-align: center;
    }

    .inv-form-header h2 {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-weight: 800;
        font-size: 1.75rem;
        color: var(--inv-text-main);
        margin: 0;
    }

    .inv-form-header p {
        color: var(--inv-text-muted);
        margin-top: 8px;
    }

    .inv-field-group {
        margin-bottom: 24px;
    }

    .inv-label {
        display: block;
        font-weight: 700;
        font-size: 0.875rem;
        color: var(--inv-text-main);
        margin-bottom: 10px;
    }

    .inv-input-wrapper {
        position: relative;
    }

    .inv-input-icon {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--inv-text-muted);
        font-size: 20px;
    }

    .inv-input {
        width: 100%;
        padding: 16px 16px 16px 52px;
        border-radius: 16px;
        border: 1px solid var(--inv-border);
        background: #f8fafc;
        font-family: 'Inter', sans-serif;
        font-size: 0.95rem;
        transition: all 0.3s;
        color: var(--inv-text-main);
    }

    .inv-input:focus {
        outline: none;
        border-color: var(--inv-accent);
        background: white;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    }

    .btn-inv-submit {
        background: var(--inv-accent);
        color: white;
        padding: 18px 32px;
        border-radius: 18px;
        font-weight: 700;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: all 0.3s;
        width: 100%;
        margin-top: 32px;
        box-shadow: 0 8px 20px -6px rgba(59, 130, 246, 0.35);
    }

    .btn-inv-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 24px -6px rgba(59, 130, 246, 0.45);
        filter: brightness(1.1);
    }

    textarea.inv-input {
        min-height: 120px;
        resize: vertical;
        padding-left: 18px;
    }
</style>

<div class="inv-container">
    <header class="inv-header-sticky">
        <a href="<?= base_url($tenant['slug'] . '/admin/convites/membros') ?>" class="inv-back-btn">
            <span class="material-icons-round">arrow_back</span>
        </a>
        <div style="text-align: right;">
            <div style="font-weight: 800; font-family: 'Plus Jakarta Sans', sans-serif; color: var(--inv-text-main);">Novo Convite</div>
            <div style="font-size: 0.75rem; color: var(--inv-text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Corpo de Membros</div>
        </div>
    </header>

    <div class="inv-form-card">
        <div class="inv-form-header">
            <h2>Integrar Novo Membro</h2>
            <p>Inicie o processo de admissão para um novo desbravador no clube.</p>
        </div>

        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div style="background: #fff1f2; border: 1px solid #ef444440; padding: 16px 24px; border-radius: 20px; margin-bottom: 32px; display: flex; align-items: center; gap: 12px;">
                <span class="material-icons-round" style="color: #ef4444;">error</span>
                <span style="font-weight: 600; color: #991b1b;"><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <form action="<?= base_url($tenant['slug'] . '/admin/convites/membros/enviar') ?>" method="POST">
            
            <div class="inv-field-group">
                <label class="inv-label">E-mail de Cadastro</label>
                <div class="inv-input-wrapper">
                    <span class="material-icons-round inv-input-icon">alternate_email</span>
                    <input type="email" name="email" class="inv-input" placeholder="ex: desbravador@email.com" required>
                </div>
            </div>

            <div class="inv-field-group">
                <label class="inv-label">Nome Completo (Opcional)</label>
                <div class="inv-input-wrapper">
                    <span class="material-icons-round inv-input-icon">person_outline</span>
                    <input type="text" name="name" class="inv-input" placeholder="Ex: João da Silva">
                </div>
            </div>

            <div class="inv-field-group">
                <label class="inv-label">Unidade Principal</label>
                <div class="inv-input-wrapper">
                    <span class="material-icons-round inv-input-icon">badge</span>
                    <select name="role_name" class="inv-input" required style="appearance: none; -webkit-appearance: none; cursor: pointer;">
                        <?php foreach ($roles as $key => $label): ?>
                            <option value="<?= $key ?>"><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span class="material-icons-round" style="position: absolute; right: 18px; top: 50%; transform: translateY(-50%); color: var(--inv-text-muted); pointer-events: none;">expand_more</span>
                </div>
            </div>

            <div class="inv-field-group">
                <label class="inv-label">Mensagem de Boas-Vindas</label>
                <textarea name="custom_message" class="inv-input" placeholder="Escreva algo especial para o novo membro..."></textarea>
            </div>

            <button type="submit" class="btn-inv-submit">
                <span>Enviar Convite de Admissão</span>
                <span class="material-icons-round">send</span>
            </button>
        </form>
    </div>
</div>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
<?php require BASE_PATH . '/views/admin/partials/footer.php'; ?>
