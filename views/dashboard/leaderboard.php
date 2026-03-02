<?php
/**
 * Página de Ranking
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ranking - <?= htmlspecialchars($tenant['name']) ?></title>
    <link rel="stylesheet" href="<?= asset_url('css/app.css') ?>">
    <style>
        .your-position {
            background: var(--gradient-primary);
            border-radius: 16px;
            padding: 24px;
            text-align: center;
            margin-bottom: 30px;
            color: var(--bg-dark);
        }

        .your-position h2 {
            font-size: 3rem;
            margin-bottom: 4px;
        }

        .leaderboard-full {
            background: var(--bg-card);
            border: 1px solid var(--border-light);
            border-radius: 16px;
            overflow: hidden;
        }

        .leaderboard-row {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-light);
        }

        .leaderboard-row:last-child {
            border-bottom: none;
        }

        .leaderboard-row.is-you {
            background: rgba(0, 217, 255, 0.1);
        }

        .leaderboard-row.top-3 {
            background: rgba(247, 179, 43, 0.05);
        }

        .rank {
            font-size: 1.3rem;
            font-weight: 700;
            min-width: 40px;
            text-align: center;
        }

        .rank.gold {
            color: #FFD700;
        }

        .rank.silver {
            color: #C0C0C0;
        }

        .rank.bronze {
            color: #CD7F32;
        }

        .member-info {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .member-avatar-lg {
            width: 48px;
            height: 48px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--bg-dark);
            font-weight: 700;
            font-size: 1.2rem;
        }

        .member-details h3 {
            font-size: 1rem;
            margin-bottom: 2px;
        }

        .member-details span {
            font-size: 0.85rem;
            color: var(--text-secondary);
        }

        .member-stats {
            text-align: right;
        }

        .member-xp {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--accent-green);
        }

        .member-level {
            font-size: 0.8rem;
            color: var(--text-muted);
        }
    </style>
</head>

<body>
    <?php
    $unreadCount = 0;
    require BASE_PATH . '/views/dashboard/partials/header.php';
    ?>

    <main class="main-content">
        <div class="container">
            <header class="page-header" style="margin-bottom: 25px;">
                <h1 style="font-size: 2rem; font-weight: 800; margin-bottom: 10px;">🏆 Ranking Geral</h1>
                <p style="color: var(--text-secondary);">Os desbravadores lendários do clube!</p>
            </header>

            <div class="ranking-tabs" style="display: flex; gap: 10px; margin-bottom: 25px; background: rgba(255, 255, 255, 0.05); padding: 5px; border-radius: 12px; width: fit-content;">
                <a href="<?= base_url($tenant['slug'] . '/ranking') ?>" class="ranking-tab active" style="padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.9rem; transition: all 0.2s; background: var(--gradient-primary); color: var(--bg-dark);">Geral (Membros)</a>
                <a href="<?= base_url($tenant['slug'] . '/ranking-unidades') ?>" class="ranking-tab" style="padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.9rem; transition: all 0.2s; color: var(--text-secondary);">Unidades</a>
            </div>

            <?php if ($userPosition): ?>
                <div class="your-position">
                    <h2>#<?= $userPosition ?></h2>
                    <p>Sua posição no ranking</p>
                </div>
            <?php endif; ?>

            <div class="leaderboard-full">
                <?php foreach ($leaderboard as $index => $member): ?>
                    <?php
                    $position = $index + 1;
                    $isYou = $member['id'] === $user['id'];
                    $isTop3 = $position <= 3;
                    ?>
                    <div class="leaderboard-row <?= $isYou ? 'is-you' : '' ?> <?= $isTop3 ? 'top-3' : '' ?>">
                        <div
                            class="rank <?= $position === 1 ? 'gold' : ($position === 2 ? 'silver' : ($position === 3 ? 'bronze' : '')) ?>">
                            <?php if ($position === 1): ?>🥇<?php elseif ($position === 2): ?>🥈<?php elseif ($position === 3): ?>🥉<?php else: ?><?= $position ?><?php endif; ?>
                        </div>

                        <div class="member-info">
                            <div class="member-avatar-lg">
                                <?= strtoupper(substr($member['name'], 0, 1)) ?>
                            </div>
                            <div class="member-details">
                                <h3>
                                    <?= htmlspecialchars($member['name']) ?>
                                    <?= $isYou ? '(você)' : '' ?>
                                </h3>
                                <span>Nível <?= $member['level_number'] ?? 1 ?></span>
                            </div>
                        </div>

                        <div class="member-stats">
                            <div class="member-xp"><?= number_format($member['xp_points']) ?> XP</div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <?php require BASE_PATH . '/views/dashboard/partials/nav.php'; ?>
</body>

</html>