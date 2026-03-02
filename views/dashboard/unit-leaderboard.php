<?php
/**
 * Página de Ranking de Unidades
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ranking de Unidades - <?= htmlspecialchars($tenant['name']) ?></title>
    <link rel="stylesheet" href="<?= asset_url('css/app.css') ?>">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <style>
        .ranking-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            background: rgba(255, 255, 255, 0.05);
            padding: 5px;
            border-radius: 12px;
            width: fit-content;
        }

        .ranking-tab {
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            color: var(--text-secondary);
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        .ranking-tab.active {
            background: var(--gradient-primary);
            color: var(--bg-dark);
        }

        .your-position {
            background: var(--gradient-secondary);
            border-radius: 16px;
            padding: 24px;
            text-align: center;
            margin-bottom: 30px;
            color: #fff;
            box-shadow: 0 10px 30px rgba(139, 92, 246, 0.3);
        }

        .your-position h2 {
            font-size: 3rem;
            margin-bottom: 4px;
            font-weight: 800;
        }

        .leaderboard-full {
            background: var(--bg-card);
            border: 1px solid var(--border-light);
            border-radius: 16px;
            overflow: hidden;
            backdrop-filter: blur(10px);
        }

        .leaderboard-row {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-light);
            transition: background 0.2s;
        }

        .leaderboard-row:hover {
            background: rgba(255, 255, 255, 0.02);
        }

        .leaderboard-row:last-child {
            border-bottom: none;
        }

        .leaderboard-row.is-yours {
            background: rgba(139, 92, 246, 0.1);
            border-left: 4px solid var(--accent-purple);
        }

        .leaderboard-row.top-3 {
            background: rgba(247, 179, 43, 0.03);
        }

        .rank {
            font-size: 1.3rem;
            font-weight: 700;
            min-width: 45px;
            text-align: center;
        }

        .rank.gold { color: #FFD700; transform: scale(1.2); }
        .rank.silver { color: #C0C0C0; transform: scale(1.1); }
        .rank.bronze { color: #CD7F32; }

        .unit-info {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .unit-mascot-circle {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .unit-details h3 {
            font-size: 1.1rem;
            margin-bottom: 4px;
            font-weight: 700;
        }

        .unit-details .unit-meta {
            font-size: 0.8rem;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .dot-separator {
            width: 4px;
            height: 4px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
        }

        .unit-stats {
            text-align: right;
        }

        .unit-xp {
            font-size: 1.2rem;
            font-weight: 800;
            color: var(--accent-cyan);
            letter-spacing: -0.02em;
        }

        .xp-label {
            font-size: 0.7rem;
            color: var(--text-muted);
            text-transform: uppercase;
            font-weight: 700;
        }
    </style>
</head>

<body>
    <?php require BASE_PATH . '/views/dashboard/partials/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <header class="page-header" style="margin-bottom: 25px;">
                <h1 style="font-size: 2rem; font-weight: 800; margin-bottom: 10px;">🏆 Ranking das Unidades</h1>
                <p style="color: var(--text-secondary);">A disputa pelo topo do Quartel General!</p>
            </header>

            <div class="ranking-tabs">
                <a href="<?= base_url($tenant['slug'] . '/ranking') ?>" class="ranking-tab">Geral (Membros)</a>
                <a href="<?= base_url($tenant['slug'] . '/ranking-unidades') ?>" class="ranking-tab active">Unidades</a>
            </div>

            <?php if ($unitPosition): ?>
                <div class="your-position">
                    <div style="font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.1em; opacity: 0.8; margin-bottom: 5px;">Sua Unidade</div>
                    <h2>#<?= $unitPosition ?></h2>
                    <p>Posição geral no ranking de unidades</p>
                </div>
            <?php endif; ?>

            <div class="leaderboard-full">
                <?php if (empty($unitLeaderboard)): ?>
                    <div style="padding: 40px; text-align: center; color: var(--text-secondary);">
                        <i class="material-icons-round" style="font-size: 3rem; opacity: 0.3; margin-bottom: 15px;">group_off</i>
                        <p>Ainda não há dados de unidades suficientes para o ranking.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($unitLeaderboard as $index => $unit): ?>
                        <?php
                        $position = $index + 1;
                        $isYours = $unit['id'] == ($userUnitId ?? 0);
                        $isTop3 = $position <= 3;
                        ?>
                        <div class="leaderboard-row <?= $isYours ? 'is-yours' : '' ?> <?= $isTop3 ? 'top-3' : '' ?>">
                            <div class="rank <?= $position === 1 ? 'gold' : ($position === 2 ? 'silver' : ($position === 3 ? 'bronze' : '')) ?>">
                                <?php if ($position === 1): ?>🥇<?php elseif ($position === 2): ?>🥈<?php elseif ($position === 3): ?>🥉<?php else: ?>#<?= $position ?><?php endif; ?>
                            </div>

                            <div class="unit-info">
                                <div class="unit-mascot-circle" style="border-bottom: 3px solid <?= $unit['color'] ?? 'var(--accent-cyan)' ?>">
                                    <?= $unit['mascot'] ?? '🛡️' ?>
                                </div>
                                <div class="unit-details">
                                    <h3>
                                        <?= htmlspecialchars($unit['name']) ?>
                                        <?php if ($isYours): ?>
                                            <span style="font-size: 0.7rem; background: var(--accent-purple); color: #fff; padding: 2px 8px; border-radius: 20px; vertical-align: middle; margin-left: 5px;">SUA UNIDADE</span>
                                        <?php endif; ?>
                                    </h3>
                                    <div class="unit-meta">
                                        <span>Total XP Acumulado</span>
                                    </div>
                                </div>
                            </div>

                            <div class="unit-stats">
                                <div class="unit-xp"><?= number_format($unit['total_xp']) ?></div>
                                <div class="xp-label">PONTOS</div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div style="margin-top: 30px; background: rgba(0, 217, 255, 0.05); border: 1px solid rgba(0, 217, 255, 0.1); border-radius: 12px; padding: 20px; display: flex; align-items: flex-start; gap: 15px;">
                <i class="material-icons-round" style="color: var(--accent-cyan);">info</i>
                <div style="font-size: 0.85rem; color: var(--text-secondary); line-height: 1.5;">
                    <strong style="color: #fff; display: block; margin-bottom: 5px;">Como funciona o ranking?</strong>
                    O ranking das unidades é baseado na soma total de XP de todos os membros ativos da unidade. 
                    Quanto mais atividades sua unidade completar, mais alto vocês subirão no ranking geral do clube!
                </div>
            </div>
        </div>
    </main>

    <?php require BASE_PATH . '/views/dashboard/partials/nav.php'; ?>
</body>

</html>
