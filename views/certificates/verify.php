<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificação de Certificado · DesbravaHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;900&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <style>
        :root {
            --bg:        #0d0d1a;
            --bg-card:   #12121f;
            --bg-glass:  rgba(255,255,255,0.04);
            --border:    rgba(255,255,255,0.08);
            --primary:   #06b6d4;
            --purple:    #7c3aed;
            --green:     #10b981;
            --amber:     #f59e0b;
            --red:       #ef4444;
            --text:      #e2e8f0;
            --muted:     #64748b;
            --radius:    16px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Nunito', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        /* ---- Animated background ---- */
        body::before {
            content: '';
            position: fixed; inset: 0;
            background:
                radial-gradient(ellipse 600px 400px at 20% 30%, rgba(6,182,212,0.06) 0%, transparent 70%),
                radial-gradient(ellipse 400px 400px at 80% 70%, rgba(124,58,237,0.06) 0%, transparent 70%);
            pointer-events: none;
        }

        .container {
            width: 100%;
            max-width: 680px;
            position: relative;
            z-index: 1;
        }

        /* ---- Logo header ---- */
        .brand {
            text-align: center;
            margin-bottom: 2rem;
        }
        .brand-logo {
            font-size: 1.5rem;
            font-weight: 900;
            background: linear-gradient(135deg, var(--primary), var(--purple));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.5px;
        }
        .brand-sub {
            font-size: 0.75rem;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-top: 4px;
        }

        /* ---- Card ---- */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 2.5rem 2rem;
            position: relative;
            overflow: hidden;
        }
        .card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
        }
        .card.valid::before   { background: linear-gradient(90deg, var(--green), var(--primary)); }
        .card.invalid::before { background: linear-gradient(90deg, var(--red), var(--amber)); }

        /* ---- Status badge ---- */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 999px;
            font-weight: 700;
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
        }
        .status-badge.valid   { background: rgba(16,185,129,0.12); color: var(--green); border: 1px solid rgba(16,185,129,0.25); }
        .status-badge.invalid { background: rgba(239,68,68,0.12);  color: var(--red);   border: 1px solid rgba(239,68,68,0.25);  }

        /* ---- Achievement block ---- */
        .achievement-row {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            margin-bottom: 2rem;
            padding: 1.25rem;
            background: var(--bg-glass);
            border: 1px solid var(--border);
            border-radius: 12px;
        }
        .achievement-icon {
            width: 64px; height: 64px;
            display: flex; align-items: center; justify-content: center;
            font-size: 2.5rem;
            flex-shrink: 0;
        }
        .achievement-info { flex: 1; min-width: 0; }
        .achievement-type {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--primary);
            font-weight: 700;
            margin-bottom: 4px;
        }
        .achievement-name {
            font-size: 1.25rem;
            font-weight: 900;
            color: var(--text);
            line-height: 1.2;
        }

        /* ---- Details grid ---- */
        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        @media (max-width: 480px) { .details-grid { grid-template-columns: 1fr; } }

        .detail-item {
            padding: 1rem;
            background: var(--bg-glass);
            border: 1px solid var(--border);
            border-radius: 10px;
        }
        .detail-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--muted);
            font-weight: 600;
            margin-bottom: 6px;
        }
        .detail-value {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text);
        }
        .detail-value.highlight { color: var(--primary); }

        /* ---- Hash block ---- */
        .hash-block {
            padding: 1rem 1.25rem;
            background: rgba(124,58,237,0.06);
            border: 1px solid rgba(124,58,237,0.15);
            border-radius: 10px;
            margin-bottom: 1.5rem;
        }
        .hash-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--purple);
            font-weight: 700;
            margin-bottom: 6px;
        }
        .hash-value {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.75rem;
            color: var(--text);
            word-break: break-all;
            line-height: 1.6;
        }

        /* ---- XP badge ---- */
        .xp-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(245,158,11,0.12);
            border: 1px solid rgba(245,158,11,0.25);
            color: var(--amber);
            padding: 4px 12px;
            border-radius: 999px;
            font-weight: 800;
            font-size: 0.875rem;
        }

        /* ---- Invalid state ---- */
        .invalid-msg {
            text-align: center;
            padding: 2rem 0;
        }
        .invalid-icon { font-size: 4rem; margin-bottom: 1rem; }
        .invalid-title {
            font-size: 1.5rem;
            font-weight: 900;
            color: var(--red);
            margin-bottom: 0.5rem;
        }
        .invalid-sub {
            color: var(--muted);
            font-size: 0.9rem;
            line-height: 1.6;
        }

        /* ---- Footer note ---- */
        .footer-note {
            text-align: center;
            font-size: 0.75rem;
            color: var(--muted);
            margin-top: 1.5rem;
            line-height: 1.6;
        }
        .footer-note a { color: var(--primary); text-decoration: none; }
    </style>
</head>
<body>
<div class="container">

    <!-- Brand -->
    <div class="brand">
        <div class="brand-logo">DesbravaHub</div>
        <div class="brand-sub">Verificação de Autenticidade</div>
    </div>

    <?php if ($valid && $cert): ?>
    <!-- ============================================================ -->
    <!-- VALID CERTIFICATE                                            -->
    <!-- ============================================================ -->
    <div class="card valid">

        <div class="status-badge valid">
            <iconify-icon icon="solar:verified-check-bold-duotone"></iconify-icon>
            Certificado Autêntico
        </div>

        <!-- Achievement -->
        <div class="achievement-row">
            <div class="achievement-icon">
                <?php
                $icon = $cert['achievement_icon'] ?? 'noto:blue-book';
                if (str_starts_with($icon, 'fa-')): ?>
                    <i class="<?= htmlspecialchars($icon) ?>" style="color:var(--primary);"></i>
                <?php else: ?>
                    <iconify-icon icon="<?= htmlspecialchars($icon) ?>"></iconify-icon>
                <?php endif; ?>
            </div>
            <div class="achievement-info">
                <div class="achievement-type">
                    <?= $cert['assignment_type'] === 'specialty' ? 'Especialidade' : 'Programa de Aprendizagem' ?>
                </div>
                <div class="achievement-name"><?= htmlspecialchars($cert['achievement_name'] ?? '') ?></div>
                <?php if (!empty($cert['xp_earned'])): ?>
                <div style="margin-top:8px;">
                    <span class="xp-badge">
                        <iconify-icon icon="solar:medal-ribbons-star-bold-duotone"></iconify-icon>
                        +<?= (int) $cert['xp_earned'] ?> XP
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Details -->
        <div class="details-grid">
            <div class="detail-item">
                <div class="detail-label">Desbravador(a)</div>
                <div class="detail-value"><?= htmlspecialchars($cert['user_name'] ?? '') ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Clube</div>
                <div class="detail-value"><?= htmlspecialchars($cert['club_name'] ?? '') ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Data de Conclusão</div>
                <div class="detail-value highlight">
                    <?php
                    $dateRaw = $cert['completed_at'] ?? $cert['issued_at'];
                    $ts = strtotime($dateRaw);
                    $months = ['January'=>'janeiro','February'=>'fevereiro','March'=>'março',
                               'April'=>'abril','May'=>'maio','June'=>'junho',
                               'July'=>'julho','August'=>'agosto','September'=>'setembro',
                               'October'=>'outubro','November'=>'novembro','December'=>'dezembro'];
                    $formatted = date('d \d\e F \d\e Y', $ts);
                    foreach ($months as $en => $pt) $formatted = str_ireplace($en, $pt, $formatted);
                    echo $formatted;
                    ?>
                </div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Certificado Emitido em</div>
                <div class="detail-value">
                    <?php
                    $ts2 = strtotime($cert['issued_at']);
                    $f2 = date('d/m/Y', $ts2);
                    echo $f2;
                    ?>
                </div>
            </div>
        </div>

        <!-- Hash -->
        <div class="hash-block">
            <div class="hash-label">
                <iconify-icon icon="solar:shield-keyhole-bold-duotone" style="vertical-align:middle;margin-right:4px;"></iconify-icon>
                Código de Autenticidade
            </div>
            <div class="hash-value"><?= htmlspecialchars($cert['certificate_hash'] ?? '') ?></div>
        </div>

    </div>

    <?php else: ?>
    <!-- ============================================================ -->
    <!-- INVALID / NOT FOUND                                          -->
    <!-- ============================================================ -->
    <div class="card invalid">
        <div class="invalid-msg">
            <div class="invalid-icon">
                <iconify-icon icon="solar:shield-warning-bold-duotone" style="color:var(--red);"></iconify-icon>
            </div>
            <div class="invalid-title">Certificado Inválido</div>
            <div class="invalid-sub">
                <?php if ($hash): ?>
                    O código <strong style="color:var(--text);font-family:'JetBrains Mono',monospace;font-size:0.8em;"><?= htmlspecialchars(substr($hash, 0, 32)) ?>...</strong>
                    não corresponde a nenhum certificado emitido pela plataforma.<br><br>
                    Verifique se o link está correto e completo.
                <?php else: ?>
                    Nenhum código de verificação foi fornecido.
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="footer-note">
        Emitido pela plataforma <strong>DesbravaHub</strong> &mdash; Sistema de Gestão para Clubes de Desbravadores.<br>
        Esta página confirma a autenticidade de certificados digitais emitidos pela plataforma.
    </div>

</div>
</body>
</html>
