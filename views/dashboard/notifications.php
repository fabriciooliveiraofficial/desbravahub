<?php
/**
 * Notifications Page (Content Only - Uses member layout)
 */
?>
<style>
    .page-header {
        margin-bottom: 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .page-header h1 {
        margin: 0;
        color: #1f2937;
    }

    .btn-clear-all {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        border-radius: 8px;
        border: 1px solid #fca5a5;
        background: #fee2e2;
        color: #ef4444;
        font-weight: 600;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-clear-all:hover {
        background: #fecaca;
        border-color: #f87171;
    }

    .notifications-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .notification-item {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 16px 20px;
        display: flex;
        gap: 16px;
        align-items: flex-start;
        transition: all 0.2s;
    }

    .notification-item:hover {
        border-color: #6366f1;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.1);
    }

    .notification-item.unread {
        border-left: 4px solid #6366f1;
    }

    .notification-icon {
        font-size: 1.5rem;
        padding: 10px;
        background: rgba(99, 102, 241, 0.1);
        border-radius: 10px;
    }

    .notification-content {
        flex: 1;
    }

    .notification-title {
        font-weight: 600;
        margin-bottom: 4px;
        color: #1f2937;
    }

    .notification-message {
        color: #6b7280;
        font-size: 0.95rem;
    }

    .notification-time {
        font-size: 0.85rem;
        color: #9ca3af;
        margin-top: 8px;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
    }

    .empty-state-icon {
        font-size: 4rem;
        margin-bottom: 16px;
    }

    .empty-state h3 {
        color: #1f2937;
    }

    .empty-state p {
        color: #6b7280;
    }
</style>

<div class="container">
    <header class="page-header">
        <h1>🔔 Notificações</h1>
        <div style="display: flex; gap: 10px;">
            <button onclick="testPush()" class="btn-clear-all" style="background: #e0e7ff; border-color: #c7d2fe; color: #4338ca;">
                <span class="material-icons-round" style="font-size: 18px;">sensors</span>
                Testar Push
            </button>
            <?php if (!empty($notifications)): ?>
                <form action="<?= base_url($tenant['slug'] . '/notificacoes/limpar') ?>" method="POST" hx-boost="false" onsubmit="return confirm('Tem certeza que deseja limpar todas as notificações?');">
                    <button type="submit" class="btn-clear-all">
                        <span class="material-icons-round" style="font-size: 18px;">delete_sweep</span>
                        Limpar Todas
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </header>

    <script>
        async function testPush() {
            const btn = event.currentTarget;
            const originalContent = btn.innerHTML;
            btn.innerHTML = '<span class="material-icons-round fa-spin" style="font-size: 18px;">sync</span> Enviando...';
            btn.disabled = true;

            try {
                const response = await fetch('<?= base_url($tenant['slug'] . '/api/push/test') ?>', { method: 'POST' });
                const data = await response.json();
                if (data.success) {
                    if (data.subscriptions === 0) {
                        toast.show('Atenção', 'Seu computador não está recebendo push. Clique aqui para sincronizar agora.', 'warning', {
                            action: {
                                text: 'Sincronizar',
                                callback: () => repairPush()
                            }
                        });
                    } else {
                        toast.show('Comando enviado!', `Enviado para ${data.subscriptions} dispositivo(s).`, 'success');
                    }
                } else {
                    toast.show('Erro', data.error || 'Falha ao enviar teste', 'error');
                }
            } catch (err) {
                toast.show('Erro', 'Falha na conexão', 'error');
            } finally {
                btn.innerHTML = originalContent;
                btn.disabled = false;
            }
        }

        async function repairPush() {
            toast.show('Sincronizando...', 'Tentando registrar este navegador.', 'info');
            try {
                await pushNotifications.subscribe('<?= base_url($tenant['slug'] . '/api/push/subscribe') ?>');
                toast.show('Sucesso!', 'Seu navegador foi registrado. Tente o teste de push agora.', 'success');
            } catch (err) {
                console.error(err);
                toast.show('Erro na Sincronização', err.message || 'Falha ao registrar navegador', 'error');
            }
        }
    </script>

    <?php if (empty($notifications)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">📭</div>
            <h3>Nenhuma notificação</h3>
            <p>Você não tem notificações no momento.</p>
            
            <button onclick="enablePushNotifications()" class="btn-enable-push" style="margin-top: 20px; padding: 10px 20px; border-radius: 8px; border: none; background: #6366f1; color: white; cursor: pointer; font-weight: 600; display: none;">
                Ativar Notificações
            </button>
            
            <script>
                // Check permission and show button if needed
                document.addEventListener('DOMContentLoaded', async () => {
                    if (typeof pushNotifications !== 'undefined' && pushNotifications.isSupported() && Notification.permission === 'default') {
                        document.querySelector('.btn-enable-push').style.display = 'inline-block';
                    }
                });

                async function enablePushNotifications() {
                    const btn = document.querySelector('.btn-enable-push');
                    const originalText = btn.innerText;
                    btn.innerText = 'Ativando...';
                    btn.disabled = true;

                    try {
                        await pushNotifications.subscribe('<?= base_url($tenant['slug'] . '/api/push/subscribe') ?>');
                        toast.show('Notificações ativadas!', 'success');
                        btn.style.display = 'none';
                    } catch (err) {
                        console.error(err);
                        toast.show('Erro ao ativar notificações', 'error');
                        btn.innerText = originalText;
                        btn.disabled = false;
                    }
                }
            </script>
        </div>
    <?php else: ?>
        <div class="notifications-list">
            <?php foreach ($notifications as $n): ?>
                <?php 
                    $data = json_decode($n['data'] ?? '{}', true);
                    $link = $data['link'] ?? '#';
                ?>
                <a href="<?= $link ?>" class="notification-item <?= empty($n['read_at']) ? 'unread' : '' ?>" style="text-decoration: none; color: inherit;">
                    <div class="notification-icon">
                        <?php
                        $icon = '🔔';
                        if (str_contains($n['type'], 'specialty'))
                            $icon = '🎯';
                        if (str_contains($n['type'], 'complete'))
                            $icon = '🎉';
                        if (str_contains($n['type'], 'activity'))
                            $icon = '📋';
                        if (str_contains($n['type'], 'event'))
                            $icon = '📅';
                        echo $icon;
                        ?>
                    </div>
                    <div class="notification-content">
                        <div class="notification-title"><?= htmlspecialchars($n['title']) ?></div>
                        <div class="notification-message"><?= htmlspecialchars($n['message']) ?></div>
                        <div class="notification-time">
                            <?php
                            $date = new DateTime($n['created_at']);
                            $now = new DateTime();
                            $diff = $now->diff($date);

                            if ($diff->days == 0) {
                                if ($diff->h == 0) {
                                    echo $diff->i . ' min atrás';
                                } else {
                                    echo $diff->h . 'h atrás';
                                }
                            } elseif ($diff->days == 1) {
                                echo 'Ontem';
                            } else {
                                echo $date->format('d/m/Y');
                            }
                            ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>