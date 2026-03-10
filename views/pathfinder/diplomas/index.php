<?php
/**
 * Meus Diplomas — Member certificate gallery
 * Uses the HUD / member layout (views/layouts/member.php)
 */
?>

<style>
/* ---- Page header ---- */
.diplomas-header {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 2rem;
}
.diplomas-icon {
    width: 52px; height: 52px;
    background: linear-gradient(135deg, #f59e0b22, #f59e0b11);
    border: 1px solid rgba(245,158,11,0.25);
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.75rem;
    flex-shrink: 0;
}
.diplomas-title { font-size: 1.5rem; font-weight: 900; color: var(--text-dark); }
.diplomas-sub   { font-size: 0.85rem; color: var(--text-muted); margin-top: 2px; }

/* ---- Empty state ---- */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
}
.empty-icon { font-size: 4rem; margin-bottom: 1rem; }
.empty-title { font-size: 1.25rem; font-weight: 800; color: var(--text-dark); margin-bottom: 0.5rem; }
.empty-sub { color: var(--text-muted); font-size: 0.9rem; line-height: 1.6; max-width: 340px; margin: 0 auto; }

/* ---- Diploma cards ---- */
.diplomas-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.25rem;
}

.diploma-card {
    background: var(--bg-card);
    border: 1px solid var(--border-light);
    border-radius: 16px;
    overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
    position: relative;
}
.diploma-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 32px rgba(0,0,0,0.3);
}

/* Accent stripe at top based on type */
.diploma-card::before {
    content: '';
    display: block;
    height: 3px;
}
.diploma-card.type-specialty::before { background: linear-gradient(90deg, #06b6d4, #7c3aed); }
.diploma-card.type-program::before   { background: linear-gradient(90deg, #10b981, #06b6d4); }

.diploma-card-body {
    padding: 1.25rem;
}

/* Icon + title row */
.diploma-top {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 1rem;
}
.diploma-achievement-icon {
    width: 48px; height: 48px;
    font-size: 2rem;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.diploma-info { flex: 1; min-width: 0; }
.diploma-type-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 2px 8px;
    border-radius: 999px;
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 4px;
}
.badge-specialty { background: rgba(6,182,212,0.12);  color: #06b6d4; border: 1px solid rgba(6,182,212,0.2);  }
.badge-program   { background: rgba(16,185,129,0.12); color: #10b981; border: 1px solid rgba(16,185,129,0.2); }

.diploma-achievement-name {
    font-size: 1rem;
    font-weight: 800;
    color: var(--text-dark);
    line-height: 1.25;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Meta row */
.diploma-meta {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 1rem;
}
.diploma-meta-item {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 0.75rem;
    color: var(--text-muted);
}
.diploma-meta-item iconify-icon { font-size: 0.9rem; }

/* XP pill */
.xp-pill {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: rgba(245,158,11,0.1);
    border: 1px solid rgba(245,158,11,0.2);
    color: #f59e0b;
    padding: 3px 10px;
    border-radius: 999px;
    font-weight: 800;
    font-size: 0.75rem;
}

/* Download button */
.btn-download {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 10px;
    border-radius: 10px;
    border: none;
    background: linear-gradient(135deg, var(--primary), var(--primary-hover, #0891b2));
    color: white;
    font-weight: 700;
    font-size: 0.875rem;
    cursor: pointer;
    text-decoration: none;
    transition: opacity 0.2s, transform 0.15s;
}
.btn-download:hover { opacity: 0.9; transform: scale(1.01); }
.btn-download iconify-icon { font-size: 1.1rem; }

/* Verify link */
.verify-link {
    display: block;
    text-align: center;
    margin-top: 8px;
    font-size: 0.7rem;
    color: var(--text-muted);
    text-decoration: none;
}
.verify-link:hover { color: var(--primary); }

/* Stats bar */
.stats-bar {
    display: flex;
    gap: 1rem;
    padding: 1rem 1.5rem;
    background: var(--bg-card);
    border: 1px solid var(--border-light);
    border-radius: 12px;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}
.stat-item { display: flex; flex-direction: column; }
.stat-value { font-size: 1.25rem; font-weight: 900; color: var(--text-dark); }
.stat-label { font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-top: 1px; }
</style>

<!-- Page header -->
<div class="diplomas-header">
    <div class="diplomas-icon">
        <iconify-icon icon="solar:diploma-bold-duotone"></iconify-icon>
    </div>
    <div>
        <div class="diplomas-title">Certificados</div>
        <div class="diplomas-sub">Seus certificados de conclusão</div>
    </div>
</div>

<?php if (empty($certificates)): ?>
<!-- Empty state -->
<div class="dashboard-card">
    <div class="empty-state">
        <div class="empty-icon">
            <iconify-icon icon="solar:diploma-bold-duotone" style="color:var(--text-muted);font-size:4rem;"></iconify-icon>
        </div>
        <div class="empty-title">Nenhum certificado ainda</div>
        <div class="empty-sub">
            Complete especialidades e programas de aprendizagem para ganhar certificados oficiais do clube.
        </div>
    </div>
</div>

<?php else: ?>

<!-- Stats bar -->
<?php
$totalSpecialties = count(array_filter($certificates, fn($c) => $c['assignment_type'] === 'specialty'));
$totalPrograms    = count(array_filter($certificates, fn($c) => $c['assignment_type'] === 'program'));
$totalXp          = array_sum(array_column($certificates, 'xp_earned'));
?>
<div class="stats-bar">
    <div class="stat-item">
        <span class="stat-value"><?= count($certificates) ?></span>
        <span class="stat-label">Total</span>
    </div>
    <div class="stat-item">
        <span class="stat-value" style="color:#06b6d4;"><?= $totalSpecialties ?></span>
        <span class="stat-label">Especialidades</span>
    </div>
    <div class="stat-item">
        <span class="stat-value" style="color:#10b981;"><?= $totalPrograms ?></span>
        <span class="stat-label">Programas</span>
    </div>
    <div class="stat-item">
        <span class="stat-value" style="color:#f59e0b;">+<?= number_format($totalXp) ?></span>
        <span class="stat-label">XP Total</span>
    </div>
</div>

<!-- Certificates grid -->
<div class="diplomas-grid">
    <?php foreach ($certificates as $cert): ?>
    <?php
    $isSpecialty   = $cert['assignment_type'] === 'specialty';
    $typeClass     = $isSpecialty ? 'type-specialty' : 'type-program';
    $typeBadgeClass= $isSpecialty ? 'badge-specialty' : 'badge-program';
    $typeLabel     = $isSpecialty ? 'Especialidade' : 'Programa';
    $typeIcon      = $isSpecialty ? 'solar:star-bold-duotone' : 'solar:book-2-bold-duotone';
    $icon          = $cert['achievement_icon'] ?? 'noto:blue-book';
    $dateFormatted = $cert['completed_at']
        ? date('d/m/Y', strtotime($cert['completed_at']))
        : date('d/m/Y', strtotime($cert['issued_at']));
    $downloadUrl = '/' . htmlspecialchars($tenant['slug']) . '/certificados/' . $cert['id'] . '/download';
    $verifyUrl   = '/v/' . $cert['certificate_hash'];
    ?>
    <div class="diploma-card <?= $typeClass ?>">
        <div class="diploma-card-body">

            <div class="diploma-top">
                <div class="diploma-achievement-icon">
                    <?php if (str_starts_with($icon, 'fa-')): ?>
                        <i class="<?= htmlspecialchars($icon) ?>" style="color:var(--primary);font-size:2rem;"></i>
                    <?php elseif (str_contains($icon, ':')): ?>
                        <iconify-icon icon="<?= htmlspecialchars($icon) ?>"></iconify-icon>
                    <?php else: ?>
                        <span style="font-size:2rem;"><?= $icon ?></span>
                    <?php endif; ?>
                </div>
                <div class="diploma-info">
                    <span class="diploma-type-badge <?= $typeBadgeClass ?>">
                        <iconify-icon icon="<?= $typeIcon ?>"></iconify-icon>
                        <?= $typeLabel ?>
                    </span>
                    <div class="diploma-achievement-name" title="<?= htmlspecialchars($cert['achievement_name']) ?>">
                        <?= htmlspecialchars($cert['achievement_name']) ?>
                    </div>
                </div>
            </div>

            <div class="diploma-meta">
                <div class="diploma-meta-item">
                    <iconify-icon icon="solar:calendar-bold-duotone"></iconify-icon>
                    <?= $dateFormatted ?>
                </div>
                <?php if (!empty($cert['xp_earned'])): ?>
                <div class="diploma-meta-item">
                    <span class="xp-pill">
                        <iconify-icon icon="solar:medal-ribbons-star-bold-duotone"></iconify-icon>
                        +<?= (int) $cert['xp_earned'] ?> XP
                    </span>
                </div>
                <?php endif; ?>
            </div>

            <a href="<?= $downloadUrl ?>" class="btn-download">
                <iconify-icon icon="solar:file-download-bold-duotone"></iconify-icon>
                Baixar Certificado PDF
            </a>

            <a href="<?= $verifyUrl ?>" target="_blank" class="verify-link">
                <iconify-icon icon="solar:shield-check-bold-duotone" style="vertical-align:middle;margin-right:3px;"></iconify-icon>
                Verificar autenticidade
            </a>

        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php endif; ?>
