<?php
/**
 * Dashboard - Painel do Desbravador
 * DESIGN: Pixel-Perfect Reference HUD v3.1
 */
?>
<style>
/* =========================================================
   HUD v3.1 - PIXEL PERFECT DESIGN SYSTEM
   ========================================================= */

/* Global Colors & Mixins */
:root {
    --neon-green: #10b981; /* #34d399 */
    --neon-cyan: #06b6d4;  /* #22d3ee */
    --dark-bg: #0B1121;
    --panel-bg: rgba(20, 30, 48, 0.4);
    --panel-border: rgba(255, 255, 255, 0.08);
    --text-dim: #94a3b8;
    --text-bright: #f8fafc;
}

.hud-wrapper-v3 {
    display: flex;
    flex-direction: column;
    gap: 24px;
    padding: 0 40px 40px;
    max-width: 1400px;
    margin: 0 auto;
}

/* Glass Panel Base */
.glass-panel {
    background: var(--panel-bg);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid var(--panel-border);
    border-radius: 20px;
    padding: 24px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(255, 255, 255, 0.05);
}

/* Panel Header Title */
.panel-title {
    color: var(--text-bright);
    font-size: 0.9rem;
    font-weight: 800;
    letter-spacing: 1px;
    text-transform: uppercase;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* =========================================================
   TOP SECTION: HERO & RADAR
   ========================================================= */
.top-grid {
    display: grid;
    grid-template-columns: 350px 1fr;
    gap: 24px;
}

/* HERO AREA (Profile & Level) */
.hero-panel {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.achiever-xp-label {
    text-transform: uppercase;
    letter-spacing: 2px;
    font-size: 0.8rem;
    font-weight: 800;
    color: var(--text-bright);
}
.achiever-xp-value {
    color: var(--text-dim);
    font-size: 0.75rem;
    margin-top: 4px;
    margin-bottom: 24px;
}

/* Concentric Rings Avatar */
.avatar-rings-container {
    position: relative;
    width: 200px;
    height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
}
.ring-outer {
    position: absolute;
    width: 200px;
    height: 200px;
    border-radius: 50%;
    background: conic-gradient(from 270deg, #06b6d4, #34d399, #06b6d4);
    -webkit-mask: radial-gradient(farthest-side, transparent calc(100% - 5px), #fff calc(100% - 4px));
    mask: radial-gradient(farthest-side, transparent calc(100% - 5px), #fff calc(100% - 4px));
    filter: drop-shadow(0 0 10px rgba(52, 211, 153, 0.55)) drop-shadow(0 0 22px rgba(6, 182, 212, 0.4));
    border: none;
}
.ring-inner {
    position: absolute;
    width: 170px;
    height: 170px;
    border-radius: 50%;
    background: conic-gradient(from 270deg, #34d399, #06b6d4, #34d399);
    -webkit-mask: radial-gradient(farthest-side, transparent calc(100% - 5px), #fff calc(100% - 4px));
    mask: radial-gradient(farthest-side, transparent calc(100% - 5px), #fff calc(100% - 4px));
    filter: drop-shadow(0 0 8px rgba(6, 182, 212, 0.5)) drop-shadow(0 0 18px rgba(52, 211, 153, 0.35));
    border: none;
}
.avatar-image-wrapper {
    width: 140px;
    height: 140px;
    border-radius: 50%;
    overflow: hidden;
    position: relative;
    z-index: 2;
    border: 2px solid var(--panel-border);
}
.avatar-image-wrapper img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.level-badge-float {
    position: absolute;
    top: 10px;
    background: rgba(11, 17, 33, 0.8);
    backdrop-filter: blur(4px);
    border: 1px solid var(--panel-border);
    color: var(--text-bright);
    font-size: 0.65rem;
    padding: 2px 10px;
    border-radius: 10px;
    z-index: 3;
}
.pathfinder-shield {
    position: absolute;
    bottom: -18px;
    width: 56px;
    height: 56px;
    z-index: 10;
    display: flex;
    justify-content: center;
    align-items: center;
    filter: drop-shadow(0 0 12px rgba(0,0,0,0.8));
}
.pathfinder-shield img {
    width: 100%;
    height: auto;
    object-fit: contain;
}
.hero-name {
    font-size: 1.6rem;
    font-weight: 800;
    color: var(--text-bright);
    margin-bottom: 4px;
}
.hero-role {
    font-size: 0.75rem;
    color: var(--neon-cyan);
    letter-spacing: 1px;
    text-transform: uppercase;
    font-weight: 700;
    background: rgba(6, 182, 212, 0.1);
    padding: 4px 12px;
    border-radius: 10px;
}

/* RADAR AREA (Skill Spectrum) */
.radar-wrapper {
    position: relative;
    height: 350px;
    width: 100%;
}


/* =========================================================
   BOTTOM SECTION: 3 COLUMNS (Feed, Achievements, Events)
   ========================================================= */
.bottom-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 24px;
}

/* Activity Feed */
.feed-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.feed-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 10px 12px;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.06);
    border-left: 3px solid #10b981;
    border-radius: 6px;
    font-size: 0.83rem;
    transition: background 0.15s;
}
.feed-item:hover {
    background: rgba(16, 185, 129, 0.06);
}
.feed-item-body { line-height: 1.4; }
.feed-user { font-weight: 800; color: var(--text-bright); }
.feed-action { color: var(--text-dim); }
.feed-time {
    font-size: 0.72rem;
    color: #475569;
    font-weight: 500;
}
.feed-empty {
    padding: 10px 12px;
    font-size: 0.83rem;
    color: var(--text-muted, #64748b);
}

/* Achievements 2x2 Grid */
.achievements-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    padding: 10px 0;
}
.ach-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 12px;
}
.ach-icon-glow {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    /* Base neon aesthetic */
    background: rgba(6, 182, 212, 0.05);
    border: 2px solid var(--neon-cyan);
    color: var(--neon-cyan);
    box-shadow: 0 0 20px rgba(6, 182, 212, 0.3), inset 0 0 10px rgba(6, 182, 212, 0.2);
}
.ach-card.green .ach-icon-glow {
    border-color: var(--neon-green);
    color: var(--neon-green);
    box-shadow: 0 0 20px rgba(16, 185, 129, 0.3), inset 0 0 10px rgba(16, 185, 129, 0.2);
    background: rgba(16, 185, 129, 0.05);
}
.ach-card.purple .ach-icon-glow {
    border-color: #8b5cf6;
    color: #8b5cf6;
    box-shadow: 0 0 20px rgba(139, 92, 246, 0.3), inset 0 0 10px rgba(139, 92, 246, 0.2);
    background: rgba(139, 92, 246, 0.05);
}
.ach-name {
    font-size: 0.75rem;
    font-weight: 800;
    color: var(--neon-cyan);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    line-height: 1.2;
}
.ach-card.green .ach-name  { color: var(--neon-green); }
.ach-card.purple .ach-name { color: #8b5cf6; }

/* Upcoming Events List */
.event-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.event-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.06);
    border-left: 3px solid #10b981;
    border-radius: 6px;
    transition: background 0.15s;
}
.event-item:hover {
    background: rgba(16, 185, 129, 0.06);
}
.event-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 3px;
}
.event-title {
    font-size: 0.83rem;
    font-weight: 800;
    color: var(--text-bright);
    letter-spacing: 0.5px;
    text-transform: uppercase;
    line-height: 1.2;
}
.event-date {
    font-size: 0.72rem;
    color: var(--text-dim);
}
.event-icon {
    flex-shrink: 0;
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: rgba(16, 185, 129, 0.1);
    color: var(--neon-green);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    border: 1px solid rgba(16, 185, 129, 0.2);
}
.event-item:nth-child(3n+2) .event-icon {
    background: rgba(6, 182, 212, 0.1);
    color: var(--neon-cyan);
    border-color: rgba(6, 182, 212, 0.2);
}
.event-item:nth-child(3n) .event-icon {
    background: rgba(139, 92, 246, 0.1);
    color: #8b5cf6;
    border-color: rgba(139, 92, 246, 0.2);
}

/* Responsive */
@media (max-width: 1024px) {
    .top-grid { grid-template-columns: 1fr; }
    .bottom-grid { grid-template-columns: 1fr 1fr; }
    .hud-wrapper-v3 { padding: 0 24px 24px; }
}
@media (max-width: 768px) {
    .bottom-grid { grid-template-columns: 1fr; }
    .hud-wrapper-v3 { padding: 0 16px 16px; }
}
</style>

<div class="hud-wrapper-v3">

    <!-- HUD Celebration Interceptor -->
    <?php require BASE_PATH . '/views/dashboard/partials/hero_modal.php'; ?>

    <!-- Top Row: Hero & Radar -->
    <div class="top-grid">

        <!-- 1. HERO PROFILE -->
        <div class="glass-panel hero-panel">
            <div class="achiever-xp-label">XP DO DESBRAVADOR</div>
            <div class="achiever-xp-value">
                <?= number_format($progress['xp'] ?? 0) ?> / <?= number_format($progress['next_level_xp'] ?? 1000) ?> XP
            </div>
            
            <div class="avatar-rings-container">
                <div class="level-badge-float">Level <?= is_array($progress['level'] ?? 1) ? ($progress['level']['number'] ?? 1) : ($progress['level'] ?? 1) ?></div>
                <div class="ring-outer"></div>
                <div class="ring-inner"></div>
                <div class="avatar-image-wrapper">
                    <?php if (!empty($user['avatar_url'])): ?>
                        <img src="<?= $user['avatar_url'] ?>" alt="<?= htmlspecialchars($user['name']) ?>" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div style="display:none; width: 100%; height: 100%; background: #1e293b; align-items: center; justify-content: center; font-size: 3rem; color: #cbd5e1; font-weight: 800;">
                            <?= strtoupper(substr($user['name'], 0, 1)) ?>
                        </div>
                    <?php else: ?>
                        <!-- Fallback style if no avatar -->
                        <div style="width: 100%; height: 100%; background: #1e293b; display: flex; align-items: center; justify-content: center; font-size: 3rem; color: #cbd5e1; font-weight: 800;">
                            <?= strtoupper(substr($user['name'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Pathfinder Shield -->
                <div class="pathfinder-shield">
                    <img src="<?= base_url('assets/images/logo_desbravador.png') ?>" alt="Escudo Desbravador">
                </div>
            </div>

            <div class="hero-name"><?= htmlspecialchars(explode(' ', trim($user['name']))[0]) ?> <?= htmlspecialchars(isset(explode(' ', trim($user['name']))[1]) ? explode(' ', trim($user['name']))[1] : '') ?></div>
            <div class="hero-role">DESBRAVADOR / LVL <?= is_array($progress['level'] ?? 1) ? ($progress['level']['number'] ?? 1) : ($progress['level'] ?? 1) ?></div>
        </div>

        <!-- 2. SKILL SPECTRUM RADAR -->
        <div class="glass-panel">
            <div class="panel-title">ESPECTRO DE HABILIDADES</div>

            <div class="radar-wrapper">
                <canvas id="skillRadar"></canvas>
            </div>
        </div>

    </div>

    <!-- Bottom Row: 3 Columns -->
    <div class="bottom-grid">

        <!-- 3. ACTIVITY FEED -->
        <div class="glass-panel">
            <div class="panel-title">FEED DE ATIVIDADES</div>
            <div class="feed-list">
                <?php if (!empty($activityFeed)): ?>
                    <?php foreach ($activityFeed as $feed): ?>
                        <div class="feed-item">
                            <div class="feed-item-body">
                                <span class="feed-user"><?= htmlspecialchars($feed['user']) ?></span><span class="feed-action"><?= $feed['action'] ?></span>
                            </div>
                            <span class="feed-time"><?= htmlspecialchars($feed['time']) ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="feed-empty">Nenhuma atividade recente detectada.</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- 4. RECENT ACHIEVEMENTS -->
        <div class="glass-panel" style="border-color: rgba(6, 182, 212, 0.3); box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5), inset 0 0 20px rgba(6, 182, 212, 0.05);">
            <div class="panel-title">CONQUISTAS RECENTES</div>
            <div class="achievements-grid">
                <?php if (!empty($recentAchievements)): ?>
                    <?php foreach ($recentAchievements as $ach): ?>
                        <div class="ach-card <?= htmlspecialchars($ach['style']) ?>">
                            <div class="ach-icon-glow">
                                <?php 
                                $icon = $ach['icon'];
                                if (str_starts_with($icon, 'fa-')): ?>
                                    <i class="<?= htmlspecialchars($icon) ?>"></i>
                                <?php elseif (str_contains($icon, ':')): ?>
                                    <?php $iconName = explode(' ', $icon)[0]; ?>
                                    <iconify-icon icon="<?= htmlspecialchars($iconName) ?>"></iconify-icon>
                                <?php elseif (strlen($icon) > 4 && !str_contains($icon, ' ')): ?>
                                    <span class="material-icons-round"><?= htmlspecialchars($icon) ?></span>
                                <?php else: ?>
                                    <?= $icon ?>
                                <?php endif; ?>
                            </div>
                            <span class="ach-name"><?= htmlspecialchars(mb_strtoupper(mb_strimwidth($ach['name'], 0, 22, '…', 'UTF-8'), 'UTF-8')) ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="grid-column:1/-1;text-align:center;color:var(--text-dim);font-size:0.75rem;font-weight:700;text-transform:uppercase;padding:1.5rem 0;">
                        Nenhuma conquista ainda
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- 5. UPCOMING EVENTS -->
        <div class="glass-panel">
            <div class="panel-title">PRÓXIMOS EVENTOS</div>
            <div class="event-list">
                <?php if ($nextEvent): ?>
                    <?php
                    $mesesPt = ['','Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
                    $ts = strtotime($nextEvent['start_datetime']);
                    $dateStr = $mesesPt[(int)date('n', $ts)] . ' ' . date('j', $ts) . ' | ' . date('H:i', $ts);
                    ?>
                    <div class="event-item">
                        <div class="event-info">
                            <span class="event-title"><?= htmlspecialchars($nextEvent['title']) ?></span>
                            <span class="event-date"><?= $dateStr ?></span>
                        </div>
                        <div class="event-icon"><span class="material-icons-round">event</span></div>
                    </div>
                <?php else: ?>
                    <div class="event-item">
                        <div class="event-info">
                            <span class="event-title">Fogueira do Clube</span>
                            <span class="event-date">Out 26 | 19:00</span>
                        </div>
                        <div class="event-icon"><span class="material-icons-round">local_fire_department</span></div>
                    </div>
                    <div class="event-item">
                        <div class="event-info">
                            <span class="event-title">Oficina de Tecnologia</span>
                            <span class="event-date">Nov 2 | 10:00</span>
                        </div>
                        <div class="event-icon"><span class="material-icons-round">settings</span></div>
                    </div>
                    <div class="event-item">
                        <div class="event-info">
                            <span class="event-title">Dia de Serviço</span>
                            <span class="event-date">Nov 15 | 09:00</span>
                        </div>
                        <div class="event-icon"><span class="material-icons-round">forest</span></div>
                    </div>
                    <div class="event-item">
                        <div class="event-info">
                            <span class="event-title">Trilha em Grupo</span>
                            <span class="event-date">Nov 23 | 08:00</span>
                        </div>
                        <div class="event-icon"><span class="material-icons-round">hiking</span></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

</div>

<script>
(function initRadarChart() {
    requestAnimationFrame(function() {
    const ctx = document.getElementById('skillRadar');
    if (!ctx) return;
    if (ctx._chartInstance) { ctx._chartInstance.destroy(); ctx._chartInstance = null; }

    try {
        // Array de itens reais: [{name, progress, type, status}, ...]
        let items = <?= json_encode($radarItems ?? []) ?>;

        // Estado vazio: sem nenhum programa/especialidade
        if (items.length === 0) {
            const wrapper = ctx.closest('.radar-wrapper');
            if (wrapper) {
                ctx.style.display = 'none';
                wrapper.insertAdjacentHTML('beforeend',
                    '<div style="text-align:center;color:var(--text-muted,#94a3b8);padding:2rem 1rem;font-size:.85rem;">' +
                    '<span class="material-icons-round" style="font-size:2rem;display:block;margin-bottom:.5rem;opacity:.4">radar</span>' +
                    'Nenhuma especialidade ou classe em andamento</div>'
                );
            }
            return;
        }

        // Mínimo de 3 eixos para o radar ter forma visível
        while (items.length < 3) {
            items.push({ name: '—', progress: 0, type: 'ghost', status: 'pending' });
        }

        // Quebra nome longo em até 2 linhas de ~13 chars para caber nos eixos
        function wrapLabel(name) {
            if (!name || name === '—') return ['—'];
            const words = name.split(' ');
            const lines = [];
            let cur = '';
            for (const w of words) {
                const candidate = cur ? cur + ' ' + w : w;
                if (candidate.length <= 13) {
                    cur = candidate;
                } else {
                    if (cur) lines.push(cur);
                    cur = w;
                    if (lines.length >= 1 && cur.length > 13) {
                        cur = cur.substring(0, 12) + '…';
                        break;
                    }
                }
            }
            if (cur) lines.push(cur);
            return lines.length ? lines : [name.substring(0, 13)];
        }

        // Status label legível para o tooltip
        const statusLabel = { not_started:'Não iniciado', in_progress:'Em andamento',
            submitted:'Aguardando avaliação', rejected:'Revisão necessária',
            completed:'Concluído', approved:'Aprovado', pending:'Pendente' };

        const labels        = [];
        const rawPoints     = []; // progresso real 0–100
        const displayPoints = []; // polygon visual (não-zero mesmo se 0%)

        const visualBase = 5; // mínimo visual para eixos em 0%

        items.forEach(item => {
            const prog = Math.min(100, Math.max(0, item.progress || 0));
            labels.push(wrapLabel(item.name));
            rawPoints.push(prog);
            displayPoints.push(prog > 0 ? prog : visualBase);
        });

        ctx._chartInstance = new Chart(ctx, {
            type: 'radar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Espectro',
                    data: displayPoints,
                    backgroundColor: 'rgba(16, 185, 129, 0.15)',
                    borderColor: '#10b981',
                    borderWidth: 2,
                    pointBackgroundColor: items.map(i => i.type === 'class' ? '#8b5cf6' : '#10b981'),
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: '#10b981',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: { top: 40, bottom: 40, left: 55, right: 55 }
                },
                scales: {
                    r: {
                        angleLines: { color: 'rgba(255,255,255,0.1)' },
                        grid: { color: 'rgba(255,255,255,0.1)', circular: false },
                        pointLabels: {
                            color: '#e2e8f0',
                            font: { family: 'Nunito, sans-serif', size: 11, weight: '700' },
                            padding: 12
                        },
                        suggestedMin: 0,
                        suggestedMax: 100,
                        ticks: { display: false }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(11,17,33,0.95)',
                        titleColor: '#10b981',
                        bodyColor: '#e2e8f0',
                        borderColor: 'rgba(16,185,129,0.3)',
                        borderWidth: 1,
                        padding: 12,
                        displayColors: false,
                        callbacks: {
                            title: function(context) {
                                const item = items[context[0].dataIndex];
                                return item ? item.name : '';
                            },
                            label: function(context) {
                                const item = items[context.dataIndex];
                                if (!item || item.type === 'ghost') return '';
                                const pct = rawPoints[context.dataIndex];
                                const st  = statusLabel[item.status] || item.status;
                                const typeStr = item.type === 'class' ? 'Classe' : 'Especialidade';
                                return [typeStr + ' · ' + st, 'Progresso: ' + pct + '%'];
                            }
                        }
                    }
                }
            }
        });
    } catch (e) {
        console.error("Dashboard Radar Chart Init Error:", e);
    }
    });
})();
</script>
