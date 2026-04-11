<?php
/**
 * Events - Agenda do Clube
 * DESIGN: Deep Glass HUD v3.0 (Content Only)
 */
?>
<style>
    /* Local Overrides/Specifics can go here if needed */
    .event-card-hud {
        display: flex;
        gap: 16px;
        align-items: center;
    }
    
    .date-box-hud {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 70px;
        height: 70px;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid var(--hud-glass-border);
        border-radius: var(--hud-radius);
        flex-shrink: 0;
    }
    
    .db-day { font-size: 1.8rem; font-weight: 800; line-height: 1; color: var(--hud-text-primary); }
    .db-month { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--accent-cyan); }
    
    @media (max-width: 600px) {
        .event-card-hud { flex-direction: column; align-items: flex-start; }
        .date-box-hud { width: 100%; height: 50px; flex-direction: row; gap: 10px; }
        .db-day { font-size: 1.4rem; }
    }
</style>

<div class="hud-wrapper">
    <header class="hud-header">
        <div>
            <h1 class="hud-title">Agenda Operacional</h1>
            <div class="hud-subtitle">Cronograma de Eventos e Missões</div>
        </div>
    </header>

    <?php if (empty($events)): ?>
        <div class="empty-state-hud">
            <span class="material-icons-round empty-icon-hud">event_busy</span>
            <h3 class="hud-section-title">Nenhum evento programado</h3>
            <p class="hud-subtitle">O cronograma está livre. Aguarde novas ordens.</p>
        </div>
    <?php else: ?>
        <div class="hud-grid" style="grid-template-columns: 1fr;"> <!-- List View -->
            <?php foreach ($events as $index => $event):
                $date = new DateTime($event['start_datetime']);
                $months = ['JAN', 'FEV', 'MAR', 'ABR', 'MAI', 'JUN', 'JUL', 'AGO', 'SET', 'OUT', 'NOV', 'DEZ'];
                ?>
                <div class="tech-plate type-pending" style="animation-delay: <?= $index * 0.1 ?>s">
                    <div class="status-line" style="background: var(--accent-cyan)"></div>
                    
                    <div class="event-card-hud">
                        <!-- Date -->
                        <div class="date-box-hud">
                            <span class="db-day"><?= $date->format('d') ?></span>
                            <span class="db-month"><?= $months[(int) $date->format('m') - 1] ?></span>
                        </div>
                        
                        <!-- Info -->
                        <div style="flex: 1; width: 100%;">
                            <div class="plate-header" style="margin-bottom: 8px;">
                                <div class="plate-content">
                                    <div class="plate-category">Evento Oficial</div>
                                    <h3 class="plate-title" style="font-size: 1.2rem;"><?= htmlspecialchars($event['title']) ?></h3>
                                </div>
                                <?php if ($event['xp_reward'] > 0): ?>
                                    <span class="hud-badge" style="color: var(--accent-green); border-color: var(--accent-green)">
                                        +<?= $event['xp_reward'] ?> XP
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="plate-data" style="margin-top: 8px;">
                                <div class="data-point">
                                    <span class="data-label">Horário</span>
                                    <span class="data-value"><?= $date->format('H:i') ?></span>
                                </div>
                                <?php if ($event['location']): ?>
                                    <div class="data-point">
                                        <span class="data-label">Local</span>
                                        <span class="data-value" style="font-size: 0.75rem;"><?= htmlspecialchars($event['location']) ?></span>
                                    </div>
                                <?php endif; ?>
                                <div class="data-point" style="align-items: flex-end;">
                                    <?php if ($event['my_enrollment_id']): ?>
                                        <div style="display: flex; gap: 8px; align-items: center;">
                                            <?php
                                            $gTitle = urlencode($event['title']);
                                            $gStart = $date->format('Ymd\THis');
                                            $gEnd   = $date->modify('+2 hours')->format('Ymd\THis'); // Default 2h duration
                                            $date->modify('-2 hours'); // Reset for view
                                            $gLoc   = urlencode($event['location'] ?: '');
                                            $gUrl   = "https://www.google.com/calendar/render?action=TEMPLATE&text={$gTitle}&dates={$gStart}/{$gEnd}&location={$gLoc}";
                                            ?>
                                            <a href="<?= $gUrl ?>" target="_blank" class="hud-badge" title="Adicionar ao Google Calendar"
                                               style="color: var(--accent-cyan); border-color: var(--accent-cyan); cursor: pointer; padding: 6px; display: flex; align-items: center; justify-content: center;">
                                                <span class="material-icons-round" style="font-size: 16px;">calendar_add_on</span>
                                            </a>
                                            
                                            <?php if ($event['my_status'] === 'attended'): ?>
                                                <button class="hud-badge" onclick="viewCertificate(<?= $event['id'] ?>)" 
                                                        style="color: #fbbf24; background: rgba(251,191,36,0.1); border-color: #fbbf24; cursor: pointer; padding: 6px 12px; font-weight: bold;">
                                                    <span class="material-icons-round" style="font-size: 14px; vertical-align: middle;">military_tech</span> CERTIFICADO
                                                </button>
                                            <?php else: ?>
                                                <button class="hud-badge" onclick="showTicket('<?= $event['checkin_token'] ?>', '<?= htmlspecialchars($event['title']) ?>')" 
                                                        style="color: var(--accent-green); background: rgba(0,255,136,0.1); cursor: pointer; padding: 6px 12px;">
                                                    MEU INGRESSO
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <button class="hud-badge" onclick="enroll(<?= $event['id'] ?>)" 
                                                style="color: var(--accent-cyan); cursor: pointer; padding: 6px 12px; transition: all 0.2s">
                                            INSCREVER-SE
                                        </button>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if (!empty($event['gallery'])): ?>
                                    <div class="event-gallery-preview" style="margin-top: 20px; padding-top: 15px; border-top: 1px solid rgba(255,255,255,0.05);">
                                        <div style="font-size: 0.65rem; color: var(--hud-text-dim); text-transform: uppercase; letter-spacing: 2px; margin-bottom: 10px;">Galeria de Atividades</div>
                                        <div style="display: flex; gap: 10px; overflow-x: auto; padding-bottom: 10px; scrollbar-width: none;">
                                            <?php foreach ($event['gallery'] as $img): ?>
                                                <img src="<?= htmlspecialchars($img['image_url']) ?>" 
                                                     onclick="openLightbox('<?= htmlspecialchars($img['image_url']) ?>', '<?= htmlspecialchars($img['caption'] ?: '') ?>')"
                                                     style="height: 60px; aspect-ratio: 1/1; object-fit: cover; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); cursor: pointer; transition: transform 0.2s;">
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Past Events (Optional, kept simpler) -->
    <?php if (!empty($pastEvents)): ?>
        <div class="hud-section" style="margin-top: 50px; opacity: 0.6;">
            <div class="hud-section-header" style="border-color: var(--hud-text-dim); background: none; padding-left: 0;">
                <h2 class="hud-section-title" style="color: var(--hud-text-dim)">Arquivo Morto</h2>
            </div>
            <div class="hud-grid" style="grid-template-columns: 1fr;">
                <?php foreach ($pastEvents as $event): 
                     $date = new DateTime($event['start_datetime']);
                ?>
                    <div class="tech-plate" style="padding: 16px;">
                        <div class="plate-header" style="margin-bottom: 0;">
                            <div class="plate-content">
                                <span style="font-family: monospace; color: var(--hud-text-dim); margin-right: 12px;">[<?= $date->format('d/m') ?>]</span>
                                <span style="color: var(--hud-text-dim); font-weight: 700;"><?= htmlspecialchars($event['title']) ?></span>
                            </div>
                            <?php if ($event['my_enrollment_id']): ?>
                                <i class="material-icons-round" style="color: var(--accent-green); font-size: 1rem;">check_circle</i>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    async function enroll(id) {
        if (!confirm('Deseja se inscrever neste evento?')) return;
        
        try {
            const response = await fetch('<?= base_url($tenant['slug'] . '/eventos/') ?>' + id + '/inscricao', {
                method: 'POST',
                headers: { 'Accept': 'application/json' }
            });
            const data = await response.json();
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.error || 'Erro ao se inscrever.');
            }
        } catch (err) {
            console.error(err);
            alert('Erro de conexão.');
        }
    }

    async function cancelEnrollment(id) {
        if (!confirm('Deseja cancelar sua inscrição?')) return;
        alert('Para cancelar, entre em contato com a diretoria do clube.');
    }

    function showTicket(token, title) {
        if (!token) {
            alert('Erro: Token de check-in não disponível. Tente recarregar a página.');
            return;
        }
        
        const tenantSlug = '<?= $tenant['slug'] ?>';
        const checkinUrl = window.location.origin + '/' + tenantSlug + '/admin/eventos/checkin/' + token;
        const qrUrl = `https://chart.googleapis.com/chart?cht=qr&chs=300x300&chl=${encodeURIComponent(checkinUrl)}`;
        
        document.getElementById('ticket-event-title').innerText = title;
        document.getElementById('ticket-qr').src = qrUrl;
        document.getElementById('ticket-modal').style.display = 'flex';
        setTimeout(() => document.getElementById('ticket-modal').classList.add('visible'), 10);
    }

    function closeTicket() {
        document.getElementById('ticket-modal').classList.remove('visible');
        setTimeout(() => document.getElementById('ticket-modal').style.display = 'none', 300);
    }

    function viewCertificate(eventId) {
        const tenantSlug = '<?= $tenant['slug'] ?>';
        window.open('/' + tenantSlug + '/certificados/evento/' + eventId, '_blank');
    }

    function openLightbox(url, caption) {
        document.getElementById('lightbox-img').src = url;
        document.getElementById('lightbox-caption').innerText = caption;
        document.getElementById('lightbox-modal').style.display = 'flex';
        setTimeout(() => document.getElementById('lightbox-modal').classList.add('visible'), 10);
    }

    function closeLightbox() {
        document.getElementById('lightbox-modal').classList.remove('visible');
        setTimeout(() => document.getElementById('lightbox-modal').style.display = 'none', 300);
    }
</script>

<!-- Lightbox Modal -->
<div id="lightbox-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.9); z-index: 10001; align-items: center; justify-content: center; backdrop-filter: blur(15px); transition: all 0.3s ease; opacity: 0;">
    <button onclick="closeLightbox()" style="position: absolute; top: 20px; right: 20px; background: none; border: none; color: white; cursor: pointer; z-index: 10002;">
        <span class="material-icons-round" style="font-size: 32px;">close</span>
    </button>
    
    <div style="max-width: 90vw; max-height: 80vh; position: relative; text-align: center;">
        <img id="lightbox-img" src="" style="max-width: 100%; max-height: 80vh; border-radius: 12px; box-shadow: 0 0 40px rgba(0,0,0,0.5);">
        <p id="lightbox-caption" style="color: white; margin-top: 20px; font-size: 1.1rem; text-shadow: 0 2px 4px rgba(0,0,0,0.5);"></p>
    </div>
</div>

<!-- Ticket Modal Overlay -->
<div id="ticket-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.85); z-index: 10000; align-items: center; justify-content: center; backdrop-filter: blur(10px); transition: all 0.3s ease; opacity: 0;">
    <div class="tech-plate" style="width: 90%; max-width: 360px; padding: 30px; border: 1px solid rgba(0,255,255,0.3); text-align: center; background: rgba(13,27,62,0.95); position: relative; box-shadow: 0 0 50px rgba(0,255,255,0.15);">
        <button onclick="closeTicket()" style="position: absolute; top: 15px; right: 15px; background: none; border: none; color: var(--hud-text-dim); cursor: pointer;">
            <span class="material-icons-round">close</span>
        </button>

        <div style="font-family: monospace; font-size: 0.7rem; color: var(--accent-cyan); letter-spacing: 2px; margin-bottom: 15px; text-transform: uppercase;">Acesso Autorizado</div>
        <h3 id="ticket-event-title" style="margin: 0 0 25px; color: white; font-size: 1.2rem;">Nome do Evento</h3>
        
        <div style="background: white; padding: 15px; border-radius: 12px; display: inline-block; margin-bottom: 25px; box-shadow: 0 0 20px rgba(255,255,255,0.1);">
            <img id="ticket-qr" src="" style="display: block; width: 200px; height: 200px;">
        </div>

        <p style="font-size: 0.8rem; color: var(--hud-text-dim); line-height: 1.5; margin-bottom: 0;">Apresente este QR Code no local do evento para validar sua presença e receber suas recompensas.</p>
    </div>
</div>

<style>
#ticket-modal.visible { opacity: 1; }
#ticket-modal.visible .tech-plate { transform: scale(1); }
.tech-plate { transform: scale(0.9); transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }

.hud-badge {
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 6px;
    font-family: 'Inter', sans-serif;
    font-size: 0.7rem;
    letter-spacing: 1px;
    font-weight: 700;
    text-transform: uppercase;
    transition: all 0.2s;
    background: rgba(255,255,255,0.05);
}
.hud-badge:hover {
    background: rgba(255,255,255,0.15);
    transform: translateY(-1px);
}
</style>