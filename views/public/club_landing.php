<?php
/**
 * Public Landing Page - /c/[slug]
 */

use App\Core\App;

$stats = [];
try {
    $stats = [
        'members' => db_fetch_column("SELECT COUNT(*) FROM users WHERE tenant_id = ? AND status = 'active'", [$profile['tenant_id']]) ?? 0,
        'activities' => db_fetch_column("SELECT COUNT(*) FROM activities WHERE tenant_id = ? AND status = 'active'", [$profile['tenant_id']]) ?? 0,
    ];
} catch (Exception $e) {
    $stats = ['members' => 0, 'activities' => 0];
}
?>

<style>
    /* Hero Section */
    .hero {
        padding: 60px 24px;
        text-align: center;
        background: radial-gradient(ellipse at 50% -20%, rgba(139, 92, 246, 0.15), transparent 60%);
        border-bottom: 1px solid var(--border);
    }
    .hero-content {
        max-width: 800px;
        margin: 0 auto;
    }
    .club-logo-lg {
        width: 140px; height: 140px;
        border-radius: 36px;
        object-fit: cover;
        margin: 0 auto 32px;
        border: 4px solid var(--surface);
        box-shadow: 0 12px 40px rgba(0,0,0,0.4), 0 0 0 1px rgba(255,255,255,0.1);
    }
    .club-logo-placeholder {
        width: 140px; height: 140px;
        border-radius: 36px;
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        margin: 0 auto 32px;
        display: flex; align-items: center; justify-content: center;
        font-size: 56px;
        border: 4px solid var(--surface);
        box-shadow: 0 12px 40px rgba(0,0,0,0.4), 0 0 0 1px rgba(255,255,255,0.1);
    }
    .hero h1 {
        font-size: clamp(2.5rem, 6vw, 4rem);
        font-weight: 800;
        letter-spacing: -0.04em;
        line-height: 1.1;
        margin-bottom: 24px;
        background: linear-gradient(135deg, #fff 40%, rgba(255,255,255,0.5));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .hero p.lead {
        font-size: 1.25rem;
        color: var(--text-secondary);
        max-width: 600px;
        margin: 0 auto 40px;
        line-height: 1.6;
    }

    /* Stats Cards */
    .stats-container {
        display: flex; gap: 24px; justify-content: center;
        max-width: 600px; margin: 0 auto;
    }
    .stat-box {
        background: var(--surface);
        padding: 24px;
        border-radius: 20px;
        border: 1px solid var(--border);
        min-width: 160px;
    }
    .stat-number {
        font-size: 2.5rem; font-weight: 800;
        background: linear-gradient(to right, var(--primary), var(--secondary));
        -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        margin-bottom: 4px; line-height: 1;
    }

    /* Social Links */
    .social-links {
        display: flex; justify-content: center; gap: 16px; margin: 40px 0 0;
    }
    .social-btn {
        width: 48px; height: 48px; border-radius: 50%;
        background: var(--surface); border: 1px solid var(--border);
        display: flex; align-items: center; justify-content: center;
        color: var(--text-primary); transition: all 0.2s;
    }
    .social-btn:hover { background: var(--surface-hover); transform: translateY(-4px); border-color: var(--primary); color: var(--primary); }

    /* Events Section */
    .section-title {
        font-size: 2rem; font-weight: 800; margin-bottom: 32px;
        display: flex; align-items: center; gap: 12px;
    }
    .events-grid {
        display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px;
    }
    .event-card {
        display: flex; flex-direction: column; height: 100%;
        padding: 24px;
    }
    .event-date-badge {
        display: inline-flex; flex-direction: column; align-items: center; justify-content: center;
        background: rgba(139, 92, 246, 0.1); border: 1px solid rgba(139, 92, 246, 0.2);
        color: var(--primary); border-radius: 12px;
        width: 64px; height: 64px; margin-bottom: 20px;
    }
    .event-date-badge .day { font-size: 1.5rem; font-weight: 800; line-height: 1; }
    .event-date-badge .month { font-size: 0.75rem; text-transform: uppercase; font-weight: 700; }
    
    .event-card h3 { font-size: 1.25rem; margin-bottom: 8px; font-weight: 700; line-height: 1.3; }
    .event-meta {
        display: flex; flex-direction: column; gap: 8px; margin-bottom: 24px;
        font-size: 0.9rem; color: var(--text-secondary);
    }
    .event-meta div { display: flex; align-items: center; gap: 8px; }

    .event-footer {
        margin-top: auto; padding-top: 20px; border-top: 1px solid var(--border);
        display: flex; justify-content: space-between; align-items: center;
    }
     
    @media (max-width: 640px) {
        .stats-container { flex-direction: column; }
        .stat-box { width: 100%; }
        .hero { padding: 40px 16px; }
    }
</style>

<div class="hero">
    <div class="hero-content">
        <?php if (!empty($profile['logo_url'])): ?>
            <img src="<?= htmlspecialchars($profile['logo_url']) ?>" alt="Logo do Clube" class="club-logo-lg">
        <?php else: ?>
            <div class="club-logo-placeholder">⛺</div>
        <?php endif; ?>

        <h1><?= htmlspecialchars($profile['display_name']) ?></h1>
        <p class="lead"><?= nl2br(htmlspecialchars($profile['welcome_message'])) ?></p>

        <div class="stats-container">
            <div class="stat-box">
                <div class="stat-number"><?= number_format($stats['members']) ?></div>
                <div style="color: var(--text-secondary); font-weight: 500;">Desbravadores</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?= number_format($stats['activities']) ?></div>
                <div style="color: var(--text-secondary); font-weight: 500;">Especialidades Ativas</div>
            </div>
        </div>

        <div class="social-links">
            <?php if (!empty($profile['instagram_url'])): ?>
                <a href="<?= htmlspecialchars($profile['instagram_url']) ?>" target="_blank" class="social-btn" title="Instagram">
                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>
                </a>
            <?php endif; ?>
            <?php if (!empty($profile['whatsapp_number'])): ?>
                <?php $waPhone = preg_replace('/[^0-9]/', '', $profile['whatsapp_number']); ?>
                <a href="https://wa.me/55<?= $waPhone ?>" target="_blank" class="social-btn" title="WhatsApp">
                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="container" style="padding-top: 64px; padding-bottom: 64px;">
    <?php if (!empty($events)): ?>
        <h2 class="section-title">
            <span class="material-icons-round" style="color: var(--primary);">event</span> 
            Próximos Eventos
        </h2>
        
        <div class="events-grid">
            <?php foreach ($events as $event): ?>
                <?php 
                    $date = new DateTime($event['start_datetime']);
                    $months = ['1'=>'Jan', '2'=>'Fev', '3'=>'Mar', '4'=>'Abr', '5'=>'Mai', '6'=>'Jun', '7'=>'Jul', '8'=>'Ago', '9'=>'Set', '10'=>'Out', '11'=>'Nov', '12'=>'Dez'];
                    $isFull = $event['max_participants'] > 0 && ($event['enrolled_count'] ?? 0) >= $event['max_participants'];
                ?>
                <a href="<?= base_url('c/' . $profile['slug'] . '/evento/' . $event['slug']) ?>" class="card event-card">
                    <div class="event-date-badge">
                        <span class="day"><?= $date->format('d') ?></span>
                        <span class="month"><?= $months[$date->format('n')] ?></span>
                    </div>
                    
                    <h3><?= htmlspecialchars($event['title']) ?></h3>
                    
                    <div class="event-meta">
                        <div>
                            <span class="material-icons-round" style="font-size: 18px;">schedule</span>
                            <?= $date->format('H:i') ?>
                        </div>
                        <?php if ($event['location']): ?>
                            <div>
                                <span class="material-icons-round" style="font-size: 18px;">location_on</span>
                                <?= htmlspecialchars($event['location']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="event-footer">
                        <div style="font-weight: 600; color: <?= $event['is_paid'] ? '#eab308' : 'var(--secondary)' ?>;">
                            <?= $event['is_paid'] ? 'R$ ' . number_format($event['price'], 2, ',', '.') : 'Gratuito' ?>
                        </div>
                        
                        <?php if ($isFull): ?>
                            <span class="badge" style="background: rgba(239, 68, 68, 0.15); color: #fca5a5;">Lotado</span>
                        <?php else: ?>
                            <span style="color: var(--primary); font-weight: 500; font-size: 0.9rem;">Ver detalhes &rarr;</span>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 64px 20px; background: var(--surface); border-radius: 24px; border: 1px dashed var(--border);">
            <div style="width: 64px; height: 64px; margin: 0 auto 24px; border-radius: 50%; background: rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center;">
                <span class="material-icons-round" style="font-size: 32px; color: var(--text-muted);">event_busy</span>
            </div>
            <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 8px;">Nenhum evento agendado</h3>
            <p style="color: var(--text-secondary);">Fique ligado, em breve teremos novidades!</p>
        </div>
    <?php endif; ?>
</div>
