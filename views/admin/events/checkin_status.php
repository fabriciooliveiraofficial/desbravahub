<?php
/**
 * Admin QR Check-in Status View
 */
?>

<div style="max-width: 500px; margin: 40px auto; padding: 20px;">
    <?php if (isset($error)): ?>
        <div class="dashboard-card" style="border-top: 4px solid #ef4444; text-align: center; padding: 40px 20px;">
            <span class="material-icons-round" style="font-size: 64px; color: #ef4444; margin-bottom: 20px;">error_outline</span>
            <h2 style="color: var(--text-primary); margin-bottom: 10px;">Ops! Algo deu errado</h2>
            <p style="color: var(--text-secondary); margin-bottom: 30px;"><?= $error ?></p>
            <a href="<?= base_url($tenant['slug'] . '/admin/eventos') ?>" class="btn btn-primary" style="width: 100%;">Voltar para Eventos</a>
        </div>
    <?php else: ?>
        <div class="dashboard-card" style="border-top: 4px solid #6366f1; overflow: hidden;">
            <div style="background: var(--bg-surface); padding: 30px 20px; text-align: center; border-bottom: 1px solid var(--border-light);">
                <div class="avatar-container" style="width: 80px; height: 80px; border-radius: 50%; border: 3px solid #6366f1; padding: 2px; margin: 0 auto 16px;">
                    <?php if ($enrollment['avatar_url']): ?>
                        <img src="<?= htmlspecialchars($enrollment['avatar_url']) ?>" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                    <?php else: ?>
                        <div style="width: 100%; height: 100%; border-radius: 50%; background: var(--accent-gradient); color: white; display: flex; align-items: center; justify-content: center; font-size: 28px; font-weight: bold;">
                            <?= substr($enrollment['user_name'], 0, 1) ?>
                        </div>
                    <?php endif; ?>
                </div>
                <h2 style="margin: 0; color: var(--text-primary);"><?= htmlspecialchars($enrollment['user_name']) ?></h2>
                <p style="color: var(--text-secondary); margin: 4px 0 0;">Participante Confirmado</p>
            </div>

            <div style="padding: 24px;">
                <div style="margin-bottom: 24px;">
                    <div style="font-size: 0.75rem; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;">Evento</div>
                    <div style="display: flex; align-items: center; gap: 12px; background: var(--bg-secondary); padding: 12px; border-radius: 12px;">
                        <span class="material-icons-round" style="color: #6366f1;">event</span>
                        <div>
                            <div style="font-weight: 600; color: var(--text-primary);"><?= htmlspecialchars($enrollment['event_title']) ?></div>
                            <div style="font-size: 0.85rem; color: var(--text-secondary);"><?= date('d/m/Y H:i', strtotime($enrollment['start_datetime'])) ?></div>
                        </div>
                    </div>
                </div>

                <div id="checkin-actions">
                    <?php if ($enrollment['status'] === 'attended'): ?>
                        <div style="background: #ecfdf5; border: 1px solid #d1fae5; color: #065f46; padding: 16px; border-radius: 8px; text-align: center; display: flex; align-items: center; justify-content: center; gap: 8px;">
                            <span class="material-icons-round">check_circle</span>
                            <strong>Check-in já realizado</strong>
                        </div>
                        <a href="<?= base_url($tenant['slug'] . '/admin/eventos') ?>" class="btn btn-secondary" style="width: 100%; margin-top: 16px;">Voltar</a>
                    <?php else: ?>
                        <button type="button" id="confirm-checkin-btn" class="btn btn-primary" style="width: 100%; height: 50px; font-size: 1rem; background: var(--accent-gradient); border: none;">
                            <span class="material-icons-round">qr_code_scanner</span> Confirmar Presença
                        </button>
                        <a href="<?= base_url($tenant['slug'] . '/admin/eventos') ?>" class="btn btn-secondary" style="width: 100%; margin-top: 12px;">Ignorar</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
const confirmBtn = document.getElementById('confirm-checkin-btn');
if (confirmBtn) {
    confirmBtn.addEventListener('click', async function() {
        this.disabled = true;
        this.innerHTML = '<span class="material-icons-round rotate">sync</span> Processando...';

        try {
            const response = await fetch('<?= base_url($tenant['slug'] . '/admin/eventos/checkin/' . ($enrollment['checkin_token'] ?? '')) ?>', {
                method: 'POST',
                headers: { 'Accept': 'application/json' }
            });

            const result = await response.json();

            if (result.success) {
                document.getElementById('checkin-actions').innerHTML = `
                    <div style="background: #ecfdf5; border: 1px solid #d1fae5; color: #065f46; padding: 20px; border-radius: 12px; text-align: center; animation: slideUp 0.3s ease;">
                        <span class="material-icons-round" style="font-size: 48px; display: block; margin-bottom: 12px;">verified</span>
                        <div style="font-weight: 600; font-size: 1.1rem; margin-bottom: 4px;">Sucesso!</div>
                        <div style="font-size: 0.9rem;">${result.message}</div>
                        <a href="<?= base_url($tenant['slug'] . '/admin/eventos') ?>" class="btn btn-success" style="width: 100%; margin-top: 20px;">Continuar Scaneando</a>
                    </div>
                `;
            } else {
                alert(result.error || 'Erro ao realizar check-in');
                this.disabled = false;
                this.innerHTML = '<span class="material-icons-round">qr_code_scanner</span> Confirmar Presença';
            }
        } catch (error) {
            console.error(error);
            alert('Erro de conexão ao processar check-in.');
            this.disabled = false;
            this.innerHTML = '<span class="material-icons-round">qr_code_scanner</span> Confirmar Presença';
        }
    });
}
</script>

<style>
.rotate { animation: spin 1s linear infinite; }
@keyframes spin { 100% { transform: rotate(360deg); } }
@keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
</style>
