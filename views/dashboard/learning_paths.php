<?php
/**
 * Learning Paths - Mapa de Trilhas (Duolingo Style)
 * Deep Glass HUD - Animated Node Map
 */
$overallPercent = $totalNodes > 0 ? round(($completedNodes / $totalNodes) * 100) : 0;
?>

<div class="lp-wrapper">
    <!-- Header -->
    <header class="lp-header">
        <div>
            <h1 class="lp-title">MAPA DE TRILHAS</h1>
            <p class="lp-subtitle">Navegue pela sua jornada de aprendizado</p>
        </div>
        <div class="lp-header-stats">
            <div class="lp-stat-pill">
                <span class="material-icons-round" style="font-size:16px; color: var(--accent-green);">check_circle</span>
                <span><?= $completedNodes ?>/<?= $totalNodes ?></span>
            </div>
            <div class="lp-stat-pill gold">
                <span class="material-icons-round" style="font-size:16px; color: #fbbf24;">bolt</span>
                <span><?= number_format($totalXp) ?> XP</span>
            </div>
        </div>
    </header>

    <!-- Overall Progress Bar -->
    <div class="lp-overall-progress">
        <div class="lp-overall-bar">
            <div class="lp-overall-fill" style="width: <?= $overallPercent ?>%"></div>
        </div>
        <span class="lp-overall-label"><?= $overallPercent ?>% COMPLETO</span>
    </div>

    <?php if (empty($grouped)): ?>
        <!-- Empty State -->
        <div class="lp-empty">
            <div class="lp-empty-icon">
                <span class="material-icons-round">explore</span>
            </div>
            <h3>Nenhuma trilha disponível</h3>
            <p>Aguarde seu líder atribuir missões e especialidades para você!</p>
        </div>
    <?php else: ?>
        <!-- Category Paths -->
        <?php $catIdx = 0; foreach ($grouped as $catName => $category): $catIdx++; ?>
            <section class="lp-category stagger-<?= ($catIdx % 4) + 1 ?>">
                <!-- Category Header -->
                <div class="lp-cat-header" style="--cat-color: <?= htmlspecialchars($category['color']) ?>">
                    <div class="lp-cat-icon"><?= $category['icon'] ?></div>
                    <div class="lp-cat-info">
                        <h2 class="lp-cat-title"><?= htmlspecialchars($catName) ?></h2>
                        <?php
                            $catTotal = count($category['nodes']);
                            $catDone = count(array_filter($category['nodes'], fn($n) => $n['status'] === 'completed'));
                        ?>
                        <span class="lp-cat-count"><?= $catDone ?>/<?= $catTotal ?> concluídas</span>
                    </div>
                    <div class="lp-cat-progress-ring">
                        <?php $catPercent = $catTotal > 0 ? round(($catDone / $catTotal) * 100) : 0; ?>
                        <svg viewBox="0 0 36 36" class="lp-ring-svg">
                            <path class="lp-ring-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                            <path class="lp-ring-fill" stroke="<?= htmlspecialchars($category['color']) ?>"
                                  stroke-dasharray="<?= $catPercent ?>, 100"
                                  d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                        </svg>
                        <span class="lp-ring-text"><?= $catPercent ?>%</span>
                    </div>
                </div>

                <!-- Node Trail -->
                <div class="lp-trail">
                    <div class="lp-trail-line" style="--cat-color: <?= htmlspecialchars($category['color']) ?>"></div>
                    
                    <?php foreach ($category['nodes'] as $nodeIdx => $node): 
                        $isLeft = $nodeIdx % 2 === 0;
                        $statusClass = 'node-' . $node['status'];
                        $animDelay = $nodeIdx * 0.12;
                    ?>
                        <div class="lp-node-row <?= $isLeft ? 'left' : 'right' ?>" style="animation-delay: <?= $animDelay ?>s">
                            <!-- Connector dot on the line -->
                            <div class="lp-connector-dot <?= $statusClass ?>" style="--cat-color: <?= htmlspecialchars($category['color'] ?? '#6366f1') ?>"></div>
                            
                            <!-- Node Card -->
                            <a href="<?= $node['status'] !== 'locked' ? $node['link'] : '#' ?>" 
                               class="lp-node <?= $statusClass ?> <?= $node['status'] === 'locked' ? 'disabled' : '' ?>"
                               style="--cat-color: <?= htmlspecialchars($category['color'] ?? '#6366f1') ?>">
                                
                                <!-- Status Icon Overlay -->
                                <?php if ($node['status'] === 'completed'): ?>
                                    <div class="lp-node-badge completed">
                                        <span class="material-icons-round">check</span>
                                    </div>
                                <?php elseif ($node['status'] === 'locked'): ?>
                                    <div class="lp-node-badge locked">
                                        <span class="material-icons-round">lock</span>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="lp-node-icon"><?= $node['icon'] ?></div>
                                <div class="lp-node-content">
                                    <span class="lp-node-type"><?= $node['type'] === 'specialty' ? 'ESPECIALIDADE' : 'PROGRAMA' ?></span>
                                    <h3 class="lp-node-title"><?= htmlspecialchars($node['title']) ?></h3>
                                    
                                    <?php if ($node['status'] === 'in_progress'): ?>
                                        <div class="lp-node-progress-bar">
                                            <div class="lp-node-progress-fill" style="width: <?= $node['progress'] ?>%; background: var(--cat-color);"></div>
                                        </div>
                                        <span class="lp-node-progress-text"><?= $node['progress'] ?>%</span>
                                    <?php endif; ?>
                                </div>
                                <div class="lp-node-meta">
                                    <span class="lp-node-xp">+<?= $node['xp'] ?> XP</span>
                                    <div class="lp-node-difficulty">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <span class="lp-star <?= $i <= $node['difficulty'] ? 'filled' : '' ?>">★</span>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<style>
/* ============ LEARNING PATHS - NODE MAP ============ */
:root {
    --lp-bg: rgba(15, 15, 30, 0.6);
    --lp-card-bg: rgba(30, 30, 55, 0.7);
    --lp-border: rgba(255, 255, 255, 0.06);
    --lp-text: #e2e8f0;
    --lp-text-dim: #94a3b8;
    --lp-green: #22c55e;
    --lp-yellow: #fbbf24;
    --lp-gray: #475569;
}

.lp-wrapper {
    padding: 20px 16px 40px;
    max-width: 600px;
    margin: 0 auto;
}

/* Header */
.lp-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 20px;
}

.lp-title {
    font-family: 'JetBrains Mono', monospace;
    font-size: 1.4rem;
    font-weight: 800;
    color: #fff;
    letter-spacing: 0.15em;
    margin: 0;
}

.lp-subtitle {
    font-size: 0.75rem;
    color: var(--lp-text-dim);
    margin: 4px 0 0;
}

.lp-header-stats {
    display: flex;
    gap: 8px;
}

.lp-stat-pill {
    display: flex;
    align-items: center;
    gap: 4px;
    background: rgba(34, 197, 94, 0.1);
    border: 1px solid rgba(34, 197, 94, 0.2);
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 800;
    color: var(--lp-green);
}

.lp-stat-pill.gold {
    background: rgba(251, 191, 36, 0.1);
    border-color: rgba(251, 191, 36, 0.2);
    color: #fbbf24;
}

/* Overall Progress */
.lp-overall-progress {
    margin-bottom: 32px;
}

.lp-overall-bar {
    height: 8px;
    background: rgba(255, 255, 255, 0.06);
    border-radius: 100px;
    overflow: hidden;
    border: 1px solid var(--lp-border);
}

.lp-overall-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--accent-cyan), var(--lp-green));
    border-radius: 100px;
    transition: width 1s ease;
    box-shadow: 0 0 12px rgba(34, 197, 94, 0.4);
}

.lp-overall-label {
    display: block;
    text-align: right;
    font-size: 0.65rem;
    font-weight: 800;
    color: var(--lp-text-dim);
    margin-top: 6px;
    letter-spacing: 0.1em;
}

/* Empty State */
.lp-empty {
    text-align: center;
    padding: 60px 20px;
}

.lp-empty-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 20px;
    background: rgba(99, 102, 241, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid rgba(99, 102, 241, 0.2);
}

.lp-empty-icon .material-icons-round {
    font-size: 36px;
    color: #6366f1;
}

.lp-empty h3 {
    font-size: 1.1rem;
    color: #fff;
    margin: 0 0 8px;
}

.lp-empty p {
    font-size: 0.8rem;
    color: var(--lp-text-dim);
    margin: 0;
}

/* Category Section */
.lp-category {
    margin-bottom: 48px;
    opacity: 0;
    animation: lpFadeIn 0.5s ease forwards;
}

@keyframes lpFadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.stagger-1 { animation-delay: 0.1s; }
.stagger-2 { animation-delay: 0.2s; }
.stagger-3 { animation-delay: 0.3s; }
.stagger-4 { animation-delay: 0.4s; }

/* Category Header */
.lp-cat-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px;
    background: var(--lp-card-bg);
    border: 1px solid var(--lp-border);
    border-radius: 16px;
    margin-bottom: 8px;
    backdrop-filter: blur(12px);
}

.lp-cat-icon {
    font-size: 1.8rem;
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 14px;
    border: 1px solid var(--lp-border);
    flex-shrink: 0;
}

.lp-cat-info {
    flex: 1;
}

.lp-cat-title {
    font-size: 0.95rem;
    font-weight: 800;
    color: #fff;
    margin: 0 0 2px;
}

.lp-cat-count {
    font-size: 0.65rem;
    color: var(--lp-text-dim);
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

/* Category Progress Ring */
.lp-cat-progress-ring {
    position: relative;
    width: 44px;
    height: 44px;
    flex-shrink: 0;
}

.lp-ring-svg {
    width: 100%;
    height: 100%;
    transform: rotate(-90deg);
}

.lp-ring-bg {
    fill: none;
    stroke: rgba(255, 255, 255, 0.06);
    stroke-width: 3;
}

.lp-ring-fill {
    fill: none;
    stroke-width: 3;
    stroke-linecap: round;
    transition: stroke-dasharray 1s ease;
}

.lp-ring-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 0.55rem;
    font-weight: 900;
    color: #fff;
}

/* ========== NODE TRAIL ========== */
.lp-trail {
    position: relative;
    padding: 12px 0;
}

/* Central Vertical Line */
.lp-trail-line {
    position: absolute;
    left: 50%;
    top: 0;
    bottom: 0;
    width: 3px;
    background: linear-gradient(180deg, var(--cat-color, #6366f1), transparent);
    opacity: 0.2;
    transform: translateX(-50%);
    border-radius: 2px;
}

/* Node Row */
.lp-node-row {
    display: flex;
    align-items: center;
    margin-bottom: 16px;
    position: relative;
    opacity: 0;
    animation: lpNodeSlide 0.4s ease forwards;
}

.lp-node-row.left {
    justify-content: flex-start;
    padding-right: 52%;
}

.lp-node-row.right {
    justify-content: flex-end;
    padding-left: 52%;
}

@keyframes lpNodeSlide {
    from { opacity: 0; transform: translateY(16px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Connector Dot */
.lp-connector-dot {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: var(--lp-gray);
    border: 3px solid rgba(30, 30, 55, 1);
    z-index: 5;
    transition: all 0.3s ease;
}

.lp-connector-dot.node-completed {
    background: var(--lp-green);
    box-shadow: 0 0 10px rgba(34, 197, 94, 0.5);
}

.lp-connector-dot.node-in_progress {
    background: var(--lp-yellow);
    box-shadow: 0 0 10px rgba(251, 191, 36, 0.5);
    animation: dotPulseLP 2s ease-in-out infinite;
}

.lp-connector-dot.node-available {
    background: var(--cat-color, #6366f1);
    opacity: 0.6;
}

@keyframes dotPulseLP {
    0%, 100% { transform: translateX(-50%) scale(1); }
    50% { transform: translateX(-50%) scale(1.3); }
}

/* ========== NODE CARD ========== */
.lp-node {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 16px;
    background: var(--lp-card-bg);
    border: 1px solid var(--lp-border);
    border-radius: 16px;
    text-decoration: none;
    color: var(--lp-text);
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
    width: 100%;
    backdrop-filter: blur(8px);
}

.lp-node:hover:not(.disabled) {
    transform: translateY(-2px);
    border-color: var(--cat-color, #6366f1);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3), 0 0 0 1px var(--cat-color, #6366f1);
}

/* Completed shimmer */
.lp-node.node-completed {
    border-color: rgba(34, 197, 94, 0.3);
    background: linear-gradient(135deg, rgba(34, 197, 94, 0.08), var(--lp-card-bg));
}

/* In Progress glow */
.lp-node.node-in_progress {
    border-color: rgba(251, 191, 36, 0.3);
    background: linear-gradient(135deg, rgba(251, 191, 36, 0.05), var(--lp-card-bg));
}

/* Locked */
.lp-node.node-locked {
    opacity: 0.4;
    filter: grayscale(0.6);
    cursor: not-allowed;
}

/* Available */
.lp-node.node-available {
    border-color: rgba(99, 102, 241, 0.2);
}

/* Node Badge */
.lp-node-badge {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.lp-node-badge.completed {
    background: var(--lp-green);
    box-shadow: 0 0 8px rgba(34, 197, 94, 0.5);
}

.lp-node-badge.locked {
    background: var(--lp-gray);
}

.lp-node-badge .material-icons-round {
    font-size: 14px;
    color: #fff;
}

/* Icon */
.lp-node-icon {
    font-size: 1.6rem;
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.04);
    border-radius: 12px;
    flex-shrink: 0;
    border: 1px solid var(--lp-border);
}

/* Content */
.lp-node-content {
    flex: 1;
    min-width: 0;
}

.lp-node-type {
    font-size: 0.55rem;
    font-weight: 800;
    color: var(--cat-color, #6366f1);
    letter-spacing: 0.1em;
    text-transform: uppercase;
}

.lp-node-title {
    font-size: 0.8rem;
    font-weight: 700;
    color: #fff;
    margin: 2px 0 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Node Progress Bar */
.lp-node-progress-bar {
    height: 4px;
    background: rgba(255, 255, 255, 0.06);
    border-radius: 100px;
    overflow: hidden;
    margin-top: 6px;
}

.lp-node-progress-fill {
    height: 100%;
    border-radius: 100px;
    transition: width 0.8s ease;
}

.lp-node-progress-text {
    font-size: 0.55rem;
    font-weight: 800;
    color: var(--lp-yellow);
    margin-top: 2px;
    display: block;
}

/* Meta */
.lp-node-meta {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 4px;
    flex-shrink: 0;
}

.lp-node-xp {
    font-size: 0.65rem;
    font-weight: 800;
    color: var(--lp-green);
    background: rgba(34, 197, 94, 0.1);
    padding: 2px 8px;
    border-radius: 8px;
}

.lp-node-difficulty {
    display: flex;
    gap: 1px;
}

.lp-star {
    font-size: 0.5rem;
    color: var(--lp-gray);
}

.lp-star.filled {
    color: var(--lp-yellow);
}

/* ========== MOBILE RESPONSIVE ========== */
@media (max-width: 480px) {
    .lp-wrapper {
        padding: 16px 12px 40px;
    }

    .lp-header {
        flex-direction: column;
        gap: 12px;
    }

    .lp-title {
        font-size: 1.2rem;
    }

    /* On very small screens, stack nodes vertically instead of alternating */
    .lp-node-row.left,
    .lp-node-row.right {
        padding-left: 32px;
        padding-right: 0;
        justify-content: flex-start;
    }

    .lp-trail-line {
        left: 16px;
    }

    .lp-connector-dot {
        left: 16px;
    }

    .lp-node-title {
        font-size: 0.75rem;
    }
}
</style>
