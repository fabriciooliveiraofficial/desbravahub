<?php
/**
 * Admin Notifications - Master Design v2.0
 * Standard Admin Layout
 */
?>

<!-- Header -->


<div style="max-width: 800px; margin: 0 auto;">

    <div class="dashboard-card">
        <div class="dashboard-card-header">
            <span class="material-icons-round" style="color: var(--primary);">campaign</span>
            <h3>Nova Transmissão</h3>
        </div>

        <form id="broadcast-form" class="dashboard-card-body">
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label for="title" style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-dark);">
                    Título da Notificação
                </label>
                <div style="position: relative;">
                     <span class="material-icons-round" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);">title</span>
                    <input type="text" id="title" name="title" class="form-control" required placeholder="Ex: Novo evento disponível!" style="padding-left: 3rem;">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 2rem;">
                <label for="message" style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-dark);">
                    Mensagem Principal
                </label>
                <textarea id="message" name="message" class="form-control" required rows="4" placeholder="Escreva sua mensagem aqui..." style="min-height: 120px;"></textarea>
            </div>

            <div class="form-group" style="margin-bottom: 2rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 1rem; color: var(--text-dark);">
                    Canais de Envio
                </label>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <label for="channel_toast" style="cursor: pointer; position: relative;">
                        <input type="checkbox" id="channel_toast" name="channels[]" value="toast" checked 
                               style="position: absolute; opacity: 0; width: 0; height: 0;"
                               onchange="this.nextElementSibling.style.borderColor = this.checked ? 'var(--primary)' : 'var(--border-color)';
                                         this.nextElementSibling.style.backgroundColor = this.checked ? 'rgba(var(--primary-rgb), 0.05)' : 'transparent';
                                         this.nextElementSibling.querySelector('.icon-box').style.color = this.checked ? 'var(--primary)' : 'var(--text-muted)';
                                         this.nextElementSibling.querySelector('.icon-box').style.backgroundColor = this.checked ? 'rgba(var(--primary-rgb), 0.1)' : 'var(--bg-hover)';">
                        <div style="
                            padding: 1.25rem; 
                            border: 2px solid var(--primary); 
                            border-radius: var(--radius-lg); 
                            display: flex; 
                            align-items: center; 
                            gap: 1rem;
                            transition: all 0.2s;
                            background-color: rgba(var(--primary-rgb), 0.05);
                        ">
                            <div class="icon-box" style="
                                width: 40px; height: 40px; 
                                border-radius: 50%; 
                                display: flex; align-items: center; justify-content: center;
                                background-color: rgba(var(--primary-rgb), 0.1);
                                color: var(--primary);
                                transition: all 0.2s;
                            ">
                                <span class="material-icons-round">notifications_active</span>
                            </div>
                            <div style="font-weight: 600; color: var(--text-dark);">Toast (No App)</div>
                        </div>
                    </label>

                    <label for="channel_email" style="cursor: pointer; position: relative;">
                        <input type="checkbox" id="channel_email" name="channels[]" value="email"
                               style="position: absolute; opacity: 0; width: 0; height: 0;"
                               onchange="this.nextElementSibling.style.borderColor = this.checked ? 'var(--primary)' : 'var(--border-color)';
                                         this.nextElementSibling.style.backgroundColor = this.checked ? 'rgba(var(--primary-rgb), 0.05)' : 'transparent';
                                         this.nextElementSibling.querySelector('.icon-box').style.color = this.checked ? 'var(--primary)' : 'var(--text-muted)';
                                         this.nextElementSibling.querySelector('.icon-box').style.backgroundColor = this.checked ? 'rgba(var(--primary-rgb), 0.1)' : 'var(--bg-hover)';">
                        <div style="
                            padding: 1.25rem; 
                            border: 2px solid var(--border-color); 
                            border-radius: var(--radius-lg); 
                            display: flex; 
                            align-items: center; 
                            gap: 1rem;
                            transition: all 0.2s;
                            background-color: transparent;
                        ">
                            <div class="icon-box" style="
                                width: 40px; height: 40px; 
                                border-radius: 50%; 
                                display: flex; align-items: center; justify-content: center;
                                background-color: var(--bg-hover);
                                color: var(--text-muted);
                                transition: all 0.2s;
                            ">
                                <span class="material-icons-round">alternate_email</span>
                            </div>
                            <div style="font-weight: 600; color: var(--text-dark);">E-mail Oficial</div>
                        </div>
                    </label>
                </div>
            </div>

            <div style="background: #fffbeb; border-left: 4px solid #f59e0b; padding: 1rem; margin-bottom: 2rem; border-radius: 0 var(--radius-md) var(--radius-md) 0; display: flex; gap: 1rem; align-items: center;">
                 <span class="material-icons-round" style="color: #d97706;">warning_amber</span>
                 <div style="color: #92400e; font-size: 0.9rem;">
                     Esta mensagem será enviada para <strong>todos</strong> os membros ativos. Use com responsabilidade.
                 </div>
            </div>

            <div style="display: flex; justify-content: flex-end;">
                <button type="submit" class="btn btn-primary btn-lg" id="submit-btn" style="display: flex; align-items: center; gap: 0.5rem; width: 100%; justify-content: center;">
                    <span class="material-icons-round">send</span>
                    Enviar Notificação agora
                </button>
            </div>
        </form>
    </div>
</div>

<div id="toast-container" style="position: fixed; top: 40px; right: 40px; z-index: 9999; display: flex; flex-direction: column; gap: 12px;"></div>

<script>
    var toast;

    document.addEventListener('DOMContentLoaded', () => {
        toast = window.toast = window.toast || new (window.ToastNotification || ToastNotification)();

        document.getElementById('broadcast-form').addEventListener('submit', async (e) => {
            e.preventDefault();

            const btn = document.getElementById('submit-btn');
            const originalContent = btn.innerHTML;
            
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
                    // Reset visual state of checkboxes if needed, though they default mostly to unchecked or checked based on HTML
                } else {
                    toast.error('Erro ao Enviar', data.error || 'Não foi possível completar o envio.');
                }
            } catch (err) {
                if (toast) toast.error('Erro Fatal', 'Erro de conexão ao servidor.');
                else alert('Erro de conexão ao servidor.');
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalContent;
            }
        });
    });
</script>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
<?php require BASE_PATH . '/views/admin/partials/footer.php'; ?>
