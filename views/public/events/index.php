<?php
/**
 * Public Events List View
 */
?>

<div class="container" style="max-width: 1200px; margin: 0 auto; padding: 40px 20px;">
    <!-- Header -->
    <div style="text-align: center; margin-bottom: 48px;">
        <h1 style="font-size: 2.5rem; font-weight: 800; color: var(--text-primary); margin-bottom: 16px;">
            Trilhas e Eventos
        </h1>
        <p style="font-size: 1.125rem; color: var(--text-secondary); max-width: 600px; margin: 0 auto;">
            Confira as próximas aventuras, acampamentos e atividades do clube <strong><?= htmlspecialchars($tenant['name']) ?></strong>.
        </p>
    </div>

    <?php if (empty($events)): ?>
        <div style="text-align: center; padding: 60px 20px; background: white; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: 1px solid var(--border-light);">
            <span class="material-icons-round" style="font-size: 64px; color: var(--text-muted); margin-bottom: 24px;">event_busy</span>
            <h3 style="font-size: 1.5rem; color: var(--text-primary); margin-bottom: 12px;">Nenhum evento agendado</h3>
            <p style="color: var(--text-secondary);">Fique de olho! Novas atividades serão publicadas em breve.</p>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px;">
            <?php foreach ($events as $event): ?>
                <div style="background: white; border-radius: 20px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: 1px solid var(--border-light); transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 12px 30px rgba(0,0,0,0.1)'" onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 20px rgba(0,0,0,0.05)'">
                    
                    <!-- Card Image Area (Gradient Placeholder) -->
                    <div style="height: 160px; background: linear-gradient(135deg, #06b6d4, #2563eb); position: relative;">
                        <?php if ($event['is_paid']): ?>
                            <div style="position: absolute; top: 16px; right: 16px; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); color: white; padding: 6px 12px; border-radius: 20px; font-weight: 600; font-size: 0.875rem; display: flex; align-items: center; gap: 4px;">
                                <span class="material-icons-round" style="font-size: 16px; color: #fbbf24;">stars</span>
                                Evento Especial
                            </div>
                        <?php endif; ?>
                        
                        <div style="position: absolute; bottom: -24px; left: 24px; width: 64px; height: 64px; background: white; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); display: flex; flex-direction: column; align-items: center; justify-content: center; line-height: 1;">
                            <span style="font-size: 0.875rem; font-weight: 600; color: #ef4444; text-transform: uppercase;"><?= date('M', strtotime($event['start_datetime'])) ?></span>
                            <span style="font-size: 1.5rem; font-weight: 800; color: var(--text-primary);"><?= date('d', strtotime($event['start_datetime'])) ?></span>
                        </div>
                    </div>

                    <div style="padding: 40px 24px 24px;">
                        <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--text-primary); margin: 0 0 12px; line-height: 1.4;">
                            <?= htmlspecialchars($event['title']) ?>
                        </h3>

                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 24px;">
                            <?php if (!empty($event['location'])): ?>
                                <div style="display: flex; align-items: flex-start; gap: 8px; color: var(--text-secondary); font-size: 0.95rem;">
                                    <span class="material-icons-round" style="font-size: 18px; color: #6366f1; flex-shrink: 0; margin-top: 2px;">location_on</span>
                                    <span><?= htmlspecialchars($event['location']) ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <div style="display: flex; align-items: center; gap: 8px; color: var(--text-secondary); font-size: 0.95rem;">
                                <span class="material-icons-round" style="font-size: 18px; color: #10b981;">schedule</span>
                                <span><?= date('H:i', strtotime($event['start_datetime'])) ?></span>
                            </div>

                            <?php if ($event['is_paid'] && $event['price']): ?>
                                <div style="display: flex; align-items: center; gap: 8px; color: var(--text-secondary); font-size: 0.95rem;">
                                    <span class="material-icons-round" style="font-size: 18px; color: #f59e0b;">payments</span>
                                    <strong style="color: var(--text-primary);">R$ <?= number_format($event['price'], 2, ',', '.') ?></strong>
                                </div>
                            <?php elseif (!$event['is_paid']): ?>
                                <div style="display: flex; align-items: center; gap: 8px; color: var(--text-secondary); font-size: 0.95rem;">
                                    <span class="material-icons-round" style="font-size: 18px; color: #10b981;">local_activity</span>
                                    <strong style="color: #10b981;">Gratuito</strong>
                                </div>
                            <?php endif; ?>
                        </div>

                        <a href="<?= base_url($tenant['slug'] . '/eventos/' . $event['slug']) ?>" 
                           style="display: block; width: 100%; text-align: center; padding: 12px; border-radius: 12px; background: #f1f5f9; color: var(--text-primary); font-weight: 600; text-decoration: none; transition: background 0.2s;"
                           onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
                            Ver Detalhes
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
