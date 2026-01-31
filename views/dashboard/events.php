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
                                        <button class="hud-badge" onclick="cancelEnrollment(<?= $event['id'] ?>)" 
                                                style="color: var(--accent-green); background: rgba(0,255,136,0.1); cursor: pointer; padding: 6px 12px;">
                                            CONFIRMADO
                                        </button>
                                    <?php else: ?>
                                        <button class="hud-badge" onclick="enroll(<?= $event['id'] ?>)" 
                                                style="color: var(--accent-cyan); cursor: pointer; padding: 6px 12px; transition: all 0.2s">
                                            INSCREVER-SE
                                        </button>
                                    <?php endif; ?>
                                </div>
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
    async function enroll(eventId) {
        try {
            const btn = event.target;
            const originalText = btn.innerText;
            btn.innerText = 'PROCESSANDO...';
            btn.disabled = true;

            const response = await fetch(`/${<?= json_encode($tenant['slug']) ?>}/eventos/${eventId}/inscrever`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            });
            const data = await response.json();
            if (data.success) {
                location.reload();
            } else {
                swal(data.error || 'Erro ao inscrever', 'Ops!');
                btn.innerText = originalText;
                btn.disabled = false;
            }
        } catch (e) {
            swal('Falha na comunicação', 'Erro');
        }
    }

    async function cancelEnrollment(eventId) {
        const confirmed = await sconfirm('Abortar participação nesta missão?', 'Cancelar Inscrição');
        if (!confirmed) return;
        try {
            const response = await fetch(`/${<?= json_encode($tenant['slug']) ?>}/eventos/${eventId}/cancelar`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            });
            const data = await response.json();
            if (data.success) {
                location.reload();
            } else {
                swal(data.error || 'Erro ao cancelar', 'Erro');
            }
        } catch (e) {
            swal('Falha na comunicação', 'Erro');
        }
    }
</script>