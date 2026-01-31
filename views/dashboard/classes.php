<?php
/**
 * Classes Page - Minha Trilha
 * DESIGN: Deep Glass HUD v3.0 (Content Only)
 */
?>
<div class="hud-wrapper">
    <!-- HUD Header -->
    <header class="hud-header">
        <div>
            <h1 class="hud-title">Minha Trilha</h1>
            <div class="hud-subtitle">Rumo à próxima patente!</div>
        </div>
    </header>

    <div class="hud-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
        <?php foreach ($classes as $index => $class): ?>
            <?php
            $isCurrent = ($userClass === $class['id']);
            
            // Calculate status based on levels
            $currentUserLevel = 1;
            foreach($classes as $c) {
                if ($c['id'] === $userClass) {
                    $currentUserLevel = $c['level'];
                    break;
                }
            }

            $isCompleted = $class['level'] < $currentUserLevel;
            $isLocked = $class['level'] > $currentUserLevel;
            
            $cardType = 'type-locked';
            $statusLabel = 'BLOQUEADO';
            $statusColor = 'var(--hud-text-dim)';
            $progress = 0;

            if ($isCurrent) {
                $cardType = 'type-in_progress';
                $statusLabel = 'EM PROGRESSO';
                $statusColor = 'var(--accent-warning)';
                $progress = 50; 
            } elseif ($isCompleted) {
                $cardType = 'type-completed';
                $statusLabel = 'CONCLUÍDA';
                $statusColor = 'var(--accent-green)';
                $progress = 100;
            }
            ?>

            <div class="tech-plate <?= $cardType ?>" style="animation-delay: <?= $index * 0.1 ?>s; <?= $isLocked ? 'opacity: 0.6; filter: grayscale(0.8);' : '' ?>">
                <div class="status-line" style="background: <?= $statusColor ?>"></div>
                
                <div class="plate-header" style="flex-direction: column; align-items: center; text-align: center; gap: 16px;">
                    <div style="width: 80px; height: 80px; border-radius: 50%; background: rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center; font-size: 2.5rem; border: 1px solid var(--hud-glass-border); box-shadow: 0 0 20px <?= $class['color'] ?>40;">
                        <span style="filter: drop-shadow(0 0 10px <?= $class['color'] ?>);"><?= $class['icon'] ?></span>
                    </div>

                    <div class="plate-content">
                        <div class="plate-category" style="margin-bottom: 4px;"><?= $class['min_age'] ?> ANOS +</div>
                        <h3 class="plate-title" style="font-size: 1.4rem; color: <?= $class['color'] ?>"><?= $class['name'] ?></h3>
                    </div>
                </div>

                <div class="hud-progress" style="margin-top: 16px; background: rgba(0,0,0,0.3);">
                    <div class="hud-progress-bar" style="width: <?= $progress ?>%; background: <?= $class['color'] ?>; box-shadow: 0 0 10px <?= $class['color'] ?>;"></div>
                </div>

                <div class="plate-data" style="justify-content: center; margin-top: 16px;">
                    <span class="hud-badge" style="color: <?= $statusColor ?>; border-color: <?= $statusColor ?>; width: 100%; justify-content: center;">
                        <?php if ($isCompleted): ?><i class="material-icons-round" style="font-size: 1rem; margin-right: 6px;">check_circle</i><?php endif; ?>
                        <?php if ($isCurrent): ?><i class="material-icons-round" style="font-size: 1rem; margin-right: 6px;">play_arrow</i><?php endif; ?>
                        <?php if ($isLocked): ?><i class="material-icons-round" style="font-size: 1rem; margin-right: 6px;">lock</i><?php endif; ?>
                        <?= $statusLabel ?>
                    </span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>