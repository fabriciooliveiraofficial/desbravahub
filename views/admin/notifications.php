<?php
/**
 * Admin Notifications - Vibrant Light Edition v1.0
 * High Contrast, Modern Broadcast Interface
 */
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

<style>
    :root {
        --font-primary: 'Inter', sans-serif;
        --bg-body: #f1f5f9;
        --bg-card: #ffffff;
        --text-dark: #0f172a;
        --text-medium: #334155;
        --text-light: #475569;
        
        --accent-primary: #6366f1; /* Indigo */
        --accent-hover: #4f46e5;
        --border-color: #cbd5e1;
        --accent-warn: #f59e0b;
    }

    body {
        background-color: var(--bg-body) !important;
        font-family: var(--font-primary);
        color: var(--text-dark);
    }

    .broadcast-container {
        max-width: 700px;
        margin: 0 auto;
        padding-bottom: 60px;
        animation: fadeIn 0.4s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Page Header */
    .page-header {
        text-align: center;
        margin-bottom: 40px;
    }

    .page-header h1 {
        font-size: 2.25rem;
        font-weight: 900;
        margin: 0;
        color: #000;
        letter-spacing: -0.025em;
    }

    .page-header p {
        margin: 10px 0 0;
        color: var(--text-medium);
        font-weight: 500;
        font-size: 1.1rem;
    }

    /* Form Card */
    .broadcast-card {
        background: white;
        border-radius: 32px;
        border: 2px solid var(--border-color);
        padding: 48px;
        box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05);
    }

    .form-group {
        margin-bottom: 32px;
    }

    .form-group label {
        display: block;
        font-size: 0.95rem;
        font-weight: 800;
        color: var(--text-dark);
        margin-bottom: 12px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .form-control {
        width: 100%;
        padding: 16px 20px;
        background: #f8fafc;
        border: 2px solid #e2e8f0;
        border-radius: 16px;
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

    /* Channel Selection Cards */
    .channels-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .channel-card {
        position: relative;
        cursor: pointer;
    }

    .channel-card input {
        position: absolute;
        opacity: 0;
    }

    .channel-ui {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px 20px;
        background: #f8fafc;
        border: 2px solid #e2e8f0;
        border-radius: 16px;
        font-weight: 700;
        transition: all 0.2s;
        color: var(--text-medium);
    }

    .channel-card input:checked + .channel-ui {
        border-color: var(--accent-primary);
        background: white;
        color: var(--accent-primary);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.1);
    }

    .channel-ui .material-icons-round { 
        font-size: 20px; 
        color: var(--text-light);
        transition: color 0.2s;
    }
    
    .channel-card input:checked + .channel-ui .material-icons-round {
        color: var(--accent-primary);
    }

    /* Warning Box */
    .warning-box {
        background: #fffbeb;
        border: 2px solid #fde68a;
        border-radius: 20px;
        padding: 24px;
        margin-bottom: 32px;
        display: flex;
        gap: 16px;
        align-items: center;
    }

    .warning-icon {
        width: 48px;
        height: 48px;
        background: #fef3c7;
        color: #d97706;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
    }

    .warning-text {
        font-size: 0.95rem;
        color: #92400e;
        font-weight: 600;
        line-height: 1.5;
    }

    /* Submit Button */
    .btn-broadcast {
        width: 100%;
        background: linear-gradient(135deg, var(--accent-primary), var(--accent-hover));
        color: white;
        padding: 18px 32px;
        border-radius: 100px;
        font-weight: 800;
        font-size: 1.1rem;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        transition: all 0.2s;
        box-shadow: 0 10px 20px -5px rgba(99, 102, 241, 0.4);
    }

    .btn-broadcast:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 30px -5px rgba(99, 102, 241, 0.5);
    }

    .btn-broadcast:disabled {
        opacity: 0.7;
        cursor: not-allowed;
        transform: none;
    }

</style>

<div class="broadcast-container">
    
    <div class="page-header">
        <h1>📣 Mural de Avisos</h1>
        <p>Envie notificações urgentes para todos os desbravadores do seu clube</p>
    </div>

    <div class="broadcast-card">
        <form id="broadcast-form">
            <div class="form-group">
                <label>Título da Notificação</label>
                <input type="text" name="title" class="form-control" required placeholder="Ex: Novo evento disponível!">
            </div>

            <div class="form-group">
                <label>Mensagem Principal</label>
                <textarea name="message" class="form-control" required rows="4"
                    placeholder="Escreva sua mensagem aqui..."></textarea>
            </div>

            <div class="form-group">
                <label>Canais de Envio</label>
                <div class="channels-grid">
                    <label class="channel-card">
                        <input type="checkbox" name="channels[]" value="toast" checked>
                        <div class="channel-ui">
                            <span class="material-icons-round">notifications_active</span>
                            Toast (No App)
                        </div>
                    </label>
                    <label class="channel-card">
                        <input type="checkbox" name="channels[]" value="email">
                        <div class="channel-ui">
                            <span class="material-icons-round">alternate_email</span>
                            E-mail Oficial
                        </div>
                    </label>
                </div>
            </div>

            <div class="warning-box">
                <div class="warning-icon">
                    <span class="material-icons-round">emergency</span>
                </div>
                <div class="warning-text">
                    Esta mensagem será enviada para <strong>todos</strong> os membros ativos. Use com responsabilidade.
                </div>
            </div>

            <button type="submit" class="btn-broadcast" id="submit-btn">
                <span class="material-icons-round">send</span>
                Enviar Notificação agora
            </button>
        </form>
    </div>
</div>

<div id="toast-container" style="position: fixed; top: 40px; right: 40px; z-index: 9999; display: flex; flex-direction: column; gap: 12px;"></div>

<script>
    var toast = window.toast = window.toast || new (window.ToastNotification || ToastNotification)();

    document.getElementById('broadcast-form').addEventListener('submit', async (e) => {
        e.preventDefault();

        const btn = document.getElementById('submit-btn');
        const icon = btn.querySelector('.material-icons-round');
        const originalText = btn.textContent;
        
        btn.disabled = true;
        btn.innerHTML = '<span class="material-icons-round animate-spin">sync</span> Enviando...';

        try {
            const formData = new FormData(e.target);
            const response = await fetch('<?= base_url($tenant['slug'] . '/admin/notifications/broadcast') ?>', {
                method: 'POST',
                body: formData,
                headers: { 'Accept': 'application/json' }
            });

            const data = await response.json();

            if (data.success) {
                toast.success('Sucesso', 'Notificação enviada com sucesso para o clube!');
                e.target.reset();
            } else {
                toast.error('Erro ao Enviar', data.error || 'Não foi possível completar o envio.');
            }
        } catch (err) {
            toast.error('Erro Fatal', 'Erro de conexão ao servidor.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<span class="material-icons-round">send</span> Enviar Notificação agora';
        }
    });
</script>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
<?php require BASE_PATH . '/views/admin/partials/footer.php'; ?>
