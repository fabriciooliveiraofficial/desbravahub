<?php
/**
 * Achievements Page - Galeria de Conquistas
 * Exibe: insígnias + especialidades concluídas + missões concluídas
 */

$earnedCount = count($earned_items ?? []);
$remaining   = max(0, ($total_available ?? 0) - $earnedCount);

$typeLabel = [
    'achievement' => 'Insígnia',
    'specialty'   => 'Especialidade',
    'program'     => 'Missão',
    'class'       => 'Classe',
];
$styleColor = [
    'achievement' => 'var(--accent-warning, #f59e0b)',
    'specialty'   => 'var(--neon-green, #10b981)',
    'program'     => 'var(--neon-green, #10b981)',
    'class'       => '#8b5cf6',
];

/**
 * Render an icon value:
 *  - "prefix:name"  → Iconify web component
 *  - starts with http/https or /  → <img>
 *  - anything else  → plain text/emoji
 */
function renderIcon(string $icon, string $color = '#fff', int $sizePx = 48): string {
    $icon = trim($icon);
    if ($icon === '') return '<span style="font-size:' . $sizePx . 'px">🏆</span>';

    // Iconify string: "collection:icon-name"
    if (preg_match('/^[\w-]+:[\w-]+$/', $icon)) {
        return '<iconify-icon icon="' . htmlspecialchars($icon) . '" style="font-size:' . $sizePx . 'px;color:' . $color . '"></iconify-icon>';
    }

    // URL (badge image)
    if (str_starts_with($icon, 'http') || str_starts_with($icon, '/')) {
        return '<img src="' . htmlspecialchars($icon) . '" alt="" style="width:' . $sizePx . 'px;height:' . $sizePx . 'px;object-fit:contain;border-radius:8px;">';
    }

    // Emoji or text
    return '<span style="font-size:' . $sizePx . 'px;line-height:1;">' . htmlspecialchars($icon) . '</span>';
}
?>
<div class="hud-wrapper">
    <!-- HUD Header -->
    <header class="hud-header">
        <div>
            <h1 class="hud-title">Galeria de Conquistas</h1>
            <div class="hud-subtitle">Insígnias, especialidades e missões concluídas</div>
        </div>
    </header>

    <!-- Summary Stats -->
    <div class="hud-stats">
        <div class="hud-stat-card primary">
            <div>
                <div class="hud-stat-value" style="color: var(--accent-warning)"><?= $earnedCount ?></div>
                <div class="hud-stat-label">Conquistadas</div>
            </div>
            <i class="material-icons-round hud-stat-icon" style="color: var(--accent-warning)">emoji_events</i>
        </div>
        <div class="hud-stat-card">
            <div>
                <div class="hud-stat-value" style="color: var(--hud-text-dim)"><?= $remaining ?></div>
                <div class="hud-stat-label">Restantes</div>
            </div>
            <i class="material-icons-round hud-stat-icon" style="color: var(--hud-text-dim)">lock</i>
        </div>
    </div>

    <!-- Earned Items -->
    <section class="hud-section">
        <?php if (!empty($earned_items)): ?>
            <div class="hud-grid" style="grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));">
                <?php foreach ($earned_items as $item):
                    $color   = $styleColor[$item['type']] ?? 'var(--accent-warning)';
                    $label   = $typeLabel[$item['type']] ?? 'Conquista';
                    $dateStr = !empty($item['date']) ? date('d/m/Y', strtotime($item['date'])) : '';
                ?>
                    <div class="tech-plate" style="text-align:center;padding:20px;border-color:<?= $color ?>33;box-shadow:0 0 12px <?= $color ?>18;">
                        <div style="margin-bottom:10px;display:flex;justify-content:center;align-items:center;height:52px;filter:drop-shadow(0 0 10px <?= $color ?>66);">
                            <?= renderIcon($item['icon'] ?? '', $color, 46) ?>
                        </div>
                        <h3 class="plate-title" style="font-size:0.82rem;margin-bottom:6px;color:#fff;">
                            <?= htmlspecialchars($item['name']) ?>
                        </h3>
                        <span class="hud-badge" style="color:<?= $color ?>;border-color:<?= $color ?>55;font-size:0.65rem;display:block;margin-bottom:4px;">
                            <?= $label ?>
                        </span>
                        <?php if ($dateStr): ?>
                            <span style="font-size:0.65rem;color:var(--hud-text-dim);"><?= $dateStr ?></span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="text-align:center;padding:3rem 1rem;color:var(--hud-text-dim);">
                <span class="material-icons-round" style="font-size:3rem;display:block;margin-bottom:1rem;opacity:.35;">emoji_events</span>
                <p style="font-size:0.9rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;">Nenhuma conquista ainda</p>
                <p style="font-size:0.8rem;margin-top:.5rem;opacity:.7;">Complete missões e especialidades para acumular conquistas aqui.</p>
            </div>
        <?php endif; ?>
    </section>

    <!-- Locked Achievements (system badges not yet earned) -->
    <?php if (!empty($locked_achievements)): ?>
    <section class="hud-section">
        <h2 style="font-size:0.75rem;font-weight:800;text-transform:uppercase;letter-spacing:.1em;color:var(--hud-text-dim);margin-bottom:16px;display:flex;align-items:center;gap:8px;">
            <span class="material-icons-round" style="font-size:1rem;">lock</span>
            Insígnias Bloqueadas
        </h2>
        <div class="hud-grid" style="grid-template-columns:repeat(auto-fill,minmax(130px,1fr));">
            <?php foreach ($locked_achievements as $ach):
                $achIcon = $ach['badge_url'] ?? '🏆';
            ?>
                <div class="tech-plate" style="text-align:center;padding:16px;opacity:0.4;filter:grayscale(1);">
                    <div style="margin-bottom:8px;display:flex;justify-content:center;align-items:center;height:44px;">
                        <?= renderIcon($achIcon, '#fff', 40) ?>
                    </div>
                    <h3 class="plate-title" style="font-size:0.78rem;margin-bottom:4px;">
                        <?= htmlspecialchars($ach['name']) ?>
                    </h3>
                    <span class="hud-badge" style="color:var(--hud-text-dim);font-size:0.62rem;">BLOQUEADO</span>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</div>
