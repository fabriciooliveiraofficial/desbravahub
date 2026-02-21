<?php
/**
 * Public Event Details - /c/[club_slug]/evento/[event_slug]
 */

$date = new DateTime($event['start_datetime']);
$months = ['1'=>'Janeiro', '2'=>'Fevereiro', '3'=>'Março', '4'=>'Abril', '5'=>'Maio', '6'=>'Junho', '7'=>'Julho', '8'=>'Agosto', '9'=>'Setembro', '10'=>'Outubro', '11'=>'Novembro', '12'=>'Dezembro'];

$isFull = $event['max_participants'] > 0 && ($event['enrolled_count'] ?? 0) >= $event['max_participants'];
$isClosed = $event['status'] !== 'upcoming';
$isDeadlinePassed = $event['registration_deadline'] && new DateTime() > new DateTime($event['registration_deadline']);

$canRegister = !$isFull && !$isClosed && !$isDeadlinePassed;
?>

<style>
    .event-header {
        position: relative;
        padding: 60px 24px;
        background: radial-gradient(ellipse at center top, rgba(16, 185, 129, 0.15), transparent 70%);
        border-bottom: 1px solid var(--border);
        margin-bottom: 40px;
    }

    .back-link {
        display: inline-flex; align-items: center; gap: 8px;
        color: var(--text-secondary); margin-bottom: 32px;
        font-weight: 500; transition: color 0.2s;
    }
    .back-link:hover { color: var(--primary); }

    .event-title {
        font-size: clamp(2rem, 5vw, 3.5rem);
        font-weight: 800; line-height: 1.1; margin-bottom: 24px;
        letter-spacing: -0.02em;
    }

    .event-meta-banner {
        display: flex; flex-wrap: wrap; gap: 16px; align-items: center;
        margin-bottom: 24px;
    }
    .meta-item {
        display: flex; align-items: center; gap: 8px;
        background: var(--surface); padding: 8px 16px;
        border-radius: 12px; border: 1px solid var(--border);
        font-weight: 600; font-size: 0.95rem;
    }

    .two-col-layout {
        display: grid; grid-template-columns: 2fr 1fr; gap: 40px;
        padding-bottom: 80px;
    }

    .main-content {
        background: var(--surface);
        padding: 40px; border-radius: 24px;
        border: 1px solid var(--border);
    }

    .main-content h3 { font-size: 1.5rem; margin-bottom: 20px; color: var(--text-primary); }
    .main-content p { color: var(--text-secondary); line-height: 1.8; margin-bottom: 24px; font-size: 1.05rem; }

    .registration-panel {
        background: var(--surface); padding: 32px;
        border-radius: 24px; border: 1px solid var(--border);
        position: sticky; top: 100px;
        box-shadow: 0 12px 40px rgba(0,0,0,0.3);
    }
    .price-tag {
        font-size: 2.5rem; font-weight: 800; color: var(--text-primary);
        margin-bottom: 8px; display: block;
    }
    .spots-left { font-size: 0.9rem; color: #fca5a5; font-weight: 600; margin-top: 12px; text-align: center; }

    /* Tabs */
    .auth-tabs { display: flex; gap: 8px; margin-bottom: 24px; background: rgba(0,0,0,0.2); padding: 4px; border-radius: 12px; }
    .tab-btn {
        flex: 1; text-align: center; padding: 10px; border-radius: 8px;
        font-weight: 600; font-size: 0.9rem; color: var(--text-secondary);
        cursor: pointer; transition: all 0.2s;
    }
    .tab-btn.active { background: var(--surface); color: var(--text-primary); box-shadow: 0 2px 8px rgba(0,0,0,0.2); }
    .tab-pane { display: none; }
    .tab-pane.active { display: block; animation: fadeIn 0.3s; }

    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

    @media (max-width: 900px) {
        .two-col-layout { grid-template-columns: 1fr; }
        .main-content { padding: 24px; }
    }
</style>

<div class="event-header">
    <div class="container">
        <a href="<?= base_url('c/' . $profile['slug']) ?>" class="back-link">
            <span class="material-icons-round">arrow_back</span>
            Voltar para o Clube
        </a>

        <div class="event-meta-banner">
            <?php if ($event['is_paid']): ?>
                <div class="meta-item" style="color: #eab308; border-color: rgba(234, 179, 8, 0.3); background: rgba(234, 179, 8, 0.1);">
                    <span class="material-icons-round" style="font-size: 20px;">payments</span>
                    Evento Pago
                </div>
            <?php else: ?>
                <div class="meta-item" style="color: var(--secondary); border-color: rgba(16, 185, 129, 0.3); background: rgba(16, 185, 129, 0.1);">
                    <span class="material-icons-round" style="font-size: 20px;">local_activity</span>
                    Evento Gratuito
                </div>
            <?php endif; ?>
            
            <div class="meta-item">
                <span class="material-icons-round" style="font-size: 20px; color: var(--text-muted);">calendar_today</span>
                <?= $date->format('d') ?> de <?= $months[$date->format('n')] ?> de <?= $date->format('Y') ?>
            </div>
        </div>

        <h1 class="event-title"><?= htmlspecialchars($event['title']) ?></h1>
    </div>
</div>

<div class="container two-col-layout">
    <div class="main-content">
        <h3>Sobre o Evento</h3>
        <p><?= nl2br(htmlspecialchars($event['description'] ?? 'Sem descrição fornecida.')) ?></p>

        <h3 style="margin-top: 40px;">Informações Importantes</h3>
        <div style="display: grid; gap: 16px;">
            <div style="display: flex; gap: 16px; align-items: flex-start;">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center; color: var(--primary);">
                    <span class="material-icons-round">schedule</span>
                </div>
                <div>
                    <h4 style="margin-bottom: 4px; font-size: 1rem;">Horário</h4>
                    <div style="color: var(--text-secondary);"><?= $date->format('H:i') ?> <?= $event['end_datetime'] ? 'até ' . date('H:i', strtotime($event['end_datetime'])) : '' ?></div>
                </div>
            </div>

            <?php if ($event['location']): ?>
            <div style="display: flex; gap: 16px; align-items: flex-start;">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center; color: var(--secondary);">
                    <span class="material-icons-round">location_on</span>
                </div>
                <div>
                    <h4 style="margin-bottom: 4px; font-size: 1rem;">Localização</h4>
                    <div style="color: var(--text-secondary);"><?= htmlspecialchars($event['location']) ?></div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Registration Sidebar -->
    <div>
        <div class="registration-panel">
            <span class="price-tag">
                <?= $event['is_paid'] ? 'R$ ' . number_format($event['price'], 2, ',', '.') : 'Gratuito' ?>
            </span>
            <div style="color: var(--text-muted); margin-bottom: 24px; font-size: 0.95rem;">
                <?= $event['is_paid'] ? 'Taxa de inscrição requerida.' : 'Inscrição isenta de taxas.' ?>
            </div>

            <?php if (!$canRegister): ?>
                <div style="padding: 16px; border-radius: 12px; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); text-align: center; color: #fca5a5; font-weight: 600;">
                    <?php 
                        if ($isClosed) echo "Inscrições Encerradas";
                        elseif ($isFull) echo "Vagas Esgotadas";
                        elseif ($isDeadlinePassed) echo "Prazo de Inscrição Encerrado";
                    ?>
                </div>
            <?php else: ?>
                
                <div class="auth-tabs" id="authTabs">
                    <div class="tab-btn active" data-target="member-form">Já sou Membro</div>
                    <div class="tab-btn" data-target="guest-form">Sou Visitante</div>
                </div>

                <form id="event-reg-form">
                    <input type="hidden" name="is_guest" id="is_guest_input" value="0">
                    
                    <div class="tab-pane active" id="member-form">
                        <div style="padding: 16px; background: rgba(255,255,255,0.05); border-radius: 12px; margin-bottom: 24px; text-align: center;">
                            <span class="material-icons-round" style="font-size: 32px; color: var(--primary); margin-bottom: 8px;">verified_user</span>
                            <p style="font-size: 0.9rem; color: var(--text-secondary);">
                                Sua inscrição será vinculada diretamente ao seu perfil no <strong><?= htmlspecialchars($profile['display_name']) ?></strong>.
                            </p>
                        </div>
                    </div>

                    <div class="tab-pane" id="guest-form">
                        <div class="form-group">
                            <label>Seu Nome Completo</label>
                            <input type="text" name="guest_name" id="guest_name" class="form-control" placeholder="João da Silva">
                        </div>
                        <div class="form-group" style="margin-bottom: 24px;">
                            <label>WhatsApp / Telefone</label>
                            <input type="tel" name="guest_phone" id="guest_phone" class="form-control" placeholder="(11) 99999-9999">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 16px; font-size: 1.1rem; border-radius: 16px;" id="reg-btn">
                        Confirmar Inscrição
                    </button>
                    
                    <?php if ($event['max_participants']): ?>
                        <div class="spots-left">
                            Restam apenas <?= $event['max_participants'] - ($event['enrolled_count'] ?? 0) ?> vagas!
                        </div>
                    <?php endif; ?>
                </form>

            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // Tab Switching Logic
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
            
            btn.classList.add('active');
            document.getElementById(btn.dataset.target).classList.add('active');
            document.getElementById('is_guest_input').value = btn.dataset.target === 'guest-form' ? '1' : '0';
        });
    });

    // Registration Form Submit
    document.getElementById('event-reg-form')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const isGuest = document.getElementById('is_guest_input').value === '1';
        if (isGuest) {
            if (!document.getElementById('guest_name').value || !document.getElementById('guest_phone').value) {
                toast.error('Erro', 'Por favor, preencha todos os campos do visitante.');
                return;
            }
        } else {
            // Simplified check: if member tab is active, we just submit. 
            // In a real flow, if they are NOT logged in, we might prompt them to login first.
            // For this implementation, the controller handles generic user check or fails if strict.
            <?php if (!\App\Core\App::user()): ?>
                window.location.href = '<?= base_url($profile['slug'] . '/login?redirect=' . urlencode($_SERVER['REQUEST_URI'])) ?>';
                return;
            <?php endif; ?>
        }

        const btn = document.getElementById('reg-btn');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="material-icons-round rotate">sync</span> Processando...';
        btn.disabled = true;

        const formData = new FormData(e.target);

        try {
            const response = await fetch('<?= base_url('c/' . $profile['slug'] . '/evento/' . $event['id'] . '/inscrever') ?>', {
                method: 'POST',
                body: formData,
                headers: { 'Accept': 'application/json' }
            });

            const data = await response.json();

            if (data.success) {
                if (data.payment_required && data.payment_link) {
                    toast.success('Inscrito!', 'Redirecionando para o pagamento...');
                    setTimeout(() => window.location.href = data.payment_link, 1500);
                } else {
                    toast.success('Sucesso!', data.message);
                    setTimeout(() => location.reload(), 2000);
                }
            } else {
                toast.error('Atenção', data.error);
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        } catch (err) {
            console.error(err);
            toast.error('Erro', 'Falha na conexão com o servidor.');
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    });
</script>

<style>
.rotate { animation: spin 1s linear infinite; }
@keyframes spin { 100% { transform: rotate(360deg); } }
</style>
