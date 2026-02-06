<?php
/**
 * E-Learning: Outdoor Specialty View - HUD REDESIGN
 */
$pageTitle = htmlspecialchars($assignment['specialty']['name'] ?? 'Especialidade');
$specialty = $assignment['specialty'];
$progress = $progressPercent ?? 0;

use App\Services\SpecialtyService;
$requirements = SpecialtyService::getRequirementsWithProgress($assignment['id'], $specialty['id']);
$totalReqs = count($requirements);
$completedReqs = 0;
$pendingReview = 0;

foreach ($requirements as $req) {
    if ($req['status'] === 'approved') $completedReqs++;
    if ($req['status'] === 'submitted' || $req['status'] === 'answered') $pendingReview++;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - Comando de Campo</title>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700;800&family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/hud-theme.css') ?>">
    <style>
        body {
            background-color: #0a0a12;
            background-image: 
                radial-gradient(circle at 50% 0%, rgba(0, 217, 255, 0.05), transparent 50%),
                radial-gradient(circle at 0% 100%, rgba(139, 92, 246, 0.05), transparent 50%);
            color: #fff;
            font-family: 'Outfit', sans-serif;
            min-height: 100vh;
            padding: 20px;
        }

        .hud-container {
            max-width: 900px;
            margin: 0 auto;
            padding-bottom: 100px;
        }

        .hero-banner {
            position: relative;
            padding: 40px;
            border-radius: 32px;
            background: linear-gradient(135deg, rgba(30, 30, 50, 0.4), rgba(15, 15, 25, 0.6));
            border: 1px solid rgba(255, 255, 255, 0.1);
            overflow: hidden;
            margin-bottom: 32px;
        }

        .hero-banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--accent-cyan), var(--accent-purple));
        }

        .specialty-badge-lg {
            width: 80px;
            height: 80px;
            background: rgba(0, 217, 255, 0.1);
            border: 1px solid rgba(0, 217, 255, 0.3);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            box-shadow: 0 0 30px rgba(0, 217, 255, 0.1);
        }

        .back-nav {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--hud-text-dim);
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 24px;
            transition: color 0.2s;
        }

        .back-nav:hover {
            color: var(--accent-cyan);
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 40px;
        }

        .stat-plate {
            padding: 24px;
            text-align: center;
        }

        .stat-plate .value {
            font-family: 'JetBrains Mono', monospace;
            font-size: 2.2rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 4px;
        }

        .stat-plate .label {
            font-size: 0.7rem;
            font-weight: 800;
            color: var(--hud-text-dim);
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        /* Requirement Cards */
        .req-card {
            margin-bottom: 20px;
            padding: 0;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .req-card:hover {
            transform: translateX(8px);
        }

        .req-header {
            display: flex;
            padding: 24px;
            gap: 20px;
        }

        .req-index {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'JetBrains Mono', monospace;
            font-weight: 800;
            font-size: 1.1rem;
            color: var(--accent-cyan);
        }

        .req-title {
            flex: 1;
            font-size: 1.15rem;
            font-weight: 600;
            color: #f1f5f9;
            line-height: 1.5;
        }

        .status-pill {
            padding: 6px 14px;
            border-radius: 100px;
            font-size: 0.65rem;
            font-weight: 900;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            border: 1px solid currentColor;
        }

        /* Proof Center */
        .proof-center {
            background: rgba(0, 0, 0, 0.3);
            padding: 24px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }

        .type-selector {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
        }

        .type-btn {
            flex: 1;
            height: 44px;
            justify-content: center;
            font-size: 0.7rem;
        }

        .type-btn.active {
            background: var(--accent-cyan);
            color: #000;
            border-color: var(--accent-cyan);
            box-shadow: 0 0 15px rgba(0, 217, 255, 0.3);
        }

        .proof-input-group {
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .proof-input-group.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .file-dropzone {
            height: 120px;
            border: 2px dashed rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            background: rgba(255, 255, 255, 0.02);
        }

        .file-dropzone:hover {
            border-color: var(--accent-cyan);
            background: rgba(0, 217, 255, 0.05);
        }

        /* Animations */
        .stagger-1 { animation: slideUp 0.6s cubic-bezier(0.2, 0.8, 0.2, 1) forwards; }
        .stagger-2 { animation: slideUp 0.6s cubic-bezier(0.2, 0.8, 0.2, 1) 0.1s forwards; opacity: 0; }
        .stagger-3 { animation: slideUp 0.6s cubic-bezier(0.2, 0.8, 0.2, 1) 0.2s forwards; opacity: 0; }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 768px) {
            .stats-grid { grid-template-columns: 1fr; }
            .hero-banner { padding: 24px; text-align: center; }
            .specialty-header { display: flex; flex-direction: column; align-items: center; }
            .type-selector { flex-direction: column; }
        }
    </style>
</head>
<body>

<div class="hud-container">
    <a href="<?= base_url($tenant['slug'] . '/especialidades') ?>" class="back-nav stagger-1">
        <i class="material-icons-round">arrow_back</i> Voltar ao Centro de Treinamento
    </a>

    <!-- Hero Header -->
    <div class="hero-banner stagger-1">
        <div class="specialty-header" style="display: flex; align-items: center; gap: 24px;">
            <div class="specialty-badge-lg">
                <?= $specialty['badge_icon'] ?? '🎯' ?>
            </div>
            <div style="flex: 1;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                    <span class="hud-badge" style="color: var(--accent-green); background: rgba(0,255,136,0.05); border-color: rgba(0,255,136,0.2);">MISSÃO PRÁTICA</span>
                    <span class="hud-badge"><?= SpecialtyService::getXpReward($specialty['id']) ?> XP DISPONÍVEL</span>
                </div>
                <h1 style="font-size: 2rem; margin: 0; font-weight: 800; letter-spacing: -0.02em;"><?= htmlspecialchars($specialty['name']) ?></h1>
                <p style="color: var(--hud-text-dim); margin: 8px 0 0 0; font-weight: 500;">Complete os requisitos operacionais abaixo para sincronizar seus dados.</p>
            </div>
        </div>
    </div>

    <!-- Stats Panel -->
    <div class="stats-grid">
        <div class="tech-plate vibrant-cyan stat-plate stagger-2">
            <div class="value"><?= $progress ?>%</div>
            <div class="label">Sincronização</div>
        </div>
        <div class="tech-plate vibrant-purple stat-plate stagger-2">
            <div class="value"><?= $completedReqs ?>/<?= $totalReqs ?></div>
            <div class="label">Validados</div>
        </div>
        <div class="tech-plate vibrant-green stat-plate stagger-2">
            <div class="value"><?= $pendingReview ?></div>
            <div class="label">Em Análise</div>
        </div>
    </div>

    <!-- Operation Board -->
    <h2 class="hud-stat-label stagger-3" style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
        <i class="material-icons-round" style="font-size: 1.2rem; color: var(--accent-cyan);">receipt_long</i> QUADRO DE OPERAÇÕES
    </h2>

    <div class="req-list">
        <?php foreach ($requirements as $index => $req): 
            $status = $req['status'] ?? 'pending';
            $vibrant = match($status) {
                'approved' => 'vibrant-green',
                'submitted', 'answered' => 'vibrant-orange',
                'rejected' => 'vibrant-red',
                default => 'vibrant-cyan'
            };
            $label = match($status) {
                'approved' => 'Sincronizado',
                'submitted', 'answered' => 'Processando',
                'rejected' => 'Inconsistente',
                default => 'Disponível'
            };
            $icon = match($status) {
                'approved' => 'verified',
                'submitted', 'answered' => 'hourglass_top',
                'rejected' => 'gpp_maybe',
                default => 'radio_button_unchecked'
            };
        ?>
            <div class="tech-plate <?= $vibrant ?> req-card stagger-3" style="animation-delay: <?= 0.2 + ($index * 0.05) ?>s;">
                <div class="status-line"></div>
                <div class="req-header">
                    <div class="req-index"><?= str_pad($index + 1, 2, '0', STR_PAD_LEFT) ?></div>
                    <div class="req-title"><?= htmlspecialchars($req['title']) ?></div>
                    <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 8px;">
                        <span class="status-pill"><?= $label ?></span>
                        <i class="material-icons-round" style="font-size: 1.4rem; opacity: 0.5;"><?= $icon ?></i>
                    </div>
                </div>

                <?php if ($status === 'pending' || $status === 'rejected'): ?>
                    <div class="proof-center">
                        <div style="font-size: 0.7rem; font-weight: 800; color: var(--hud-text-dim); margin-bottom: 16px; text-transform: uppercase;">Portal de Comprovação</div>
                        
                        <div class="type-selector">
                            <button class="hud-btn secondary type-btn" onclick="activateProof(this, 'url', <?= $req['id'] ?>)">
                                <i class="material-icons-round" style="font-size: 1rem;">link</i> LINK/URL
                            </button>
                            <button class="hud-btn secondary type-btn" onclick="activateProof(this, 'file', <?= $req['id'] ?>)">
                                <i class="material-icons-round" style="font-size: 1rem;">photo_camera</i> ARQUIVO
                            </button>
                            <button class="hud-btn secondary type-btn" onclick="activateProof(this, 'text', <?= $req['id'] ?>)">
                                <i class="material-icons-round" style="font-size: 1rem;">edit_note</i> RELATÓRIO
                            </button>
                        </div>

                        <div id="input-url-<?= $req['id'] ?>" class="proof-input-group">
                            <input type="url" class="hud-input" id="val-url-<?= $req['id'] ?>" placeholder="Cole o link da evidência (YouTube, Drive, etc.)">
                        </div>

                        <div id="input-file-<?= $req['id'] ?>" class="proof-input-group">
                            <label for="file-<?= $req['id'] ?>" class="file-dropzone">
                                <i class="material-icons-round" style="font-size: 2rem; color: var(--accent-cyan); opacity: 0.5;">cloud_upload</i>
                                <div style="font-size: 0.85rem; margin-top: 8px;" id="filename-<?= $req['id'] ?>">Clique para selecionar foto ou vídeo</div>
                                <input type="file" id="file-<?= $req['id'] ?>" style="display: none;" onchange="updateFileName(this, <?= $req['id'] ?>)" accept="image/*,video/*">
                            </label>
                        </div>

                        <div id="input-text-<?= $req['id'] ?>" class="proof-input-group">
                            <textarea class="hud-input" id="val-text-<?= $req['id'] ?>" style="min-height: 100px; resize: none;" placeholder="Descreva brevemente a execução desta tarefa..."></textarea>
                        </div>

                        <button class="hud-btn primary" id="submit-<?= $req['id'] ?>" onclick="transmitData(<?= $req['id'] ?>)" style="width: 100%; margin-top: 20px; justify-content: center;" disabled>
                            <i class="material-icons-round">rocket_launch</i> TRANSMITIR DADOS
                        </button>
                    </div>
                <?php elseif ($status === 'submitted' || $status === 'answered'): ?>
                    <div class="proof-center" style="background: rgba(249, 115, 22, 0.05);">
                        <div style="display: flex; align-items: center; gap: 12px; color: #f97316;">
                            <i class="material-icons-round">hourglass_bottom</i>
                            <div style="font-size: 0.9rem; font-weight: 600;">Aguardando validação do comando superior.</div>
                        </div>
                    </div>
                <?php elseif ($status === 'approved'): ?>
                    <div class="proof-center" style="background: rgba(34, 197, 94, 0.05);">
                        <div style="display: flex; align-items: center; gap: 12px; color: #22c55e;">
                            <i class="material-icons-round">verified_user</i>
                            <div style="font-size: 0.9rem; font-weight: 600;">Tarefa validada e XP sincronizado com sucesso.</div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($status === 'rejected' && !empty($req['feedback'])): ?>
                    <div style="padding: 16px 24px; background: rgba(239, 68, 68, 0.1); border-top: 1px solid rgba(239, 68, 68, 0.2); color: #fecaca; font-size: 0.9rem;">
                        <strong style="color: #ef4444; font-size: 0.7rem; text-transform: uppercase; display: block; margin-bottom: 4px;">Comando de Revisão:</strong>
                        <?= htmlspecialchars($req['feedback']) ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    const assignmentId = <?= $assignment['id'] ?>;
    const tenantSlug = '<?= $tenant['slug'] ?>';
    const activeTypes = {};

    function activateProof(btn, type, reqId) {
        const container = btn.closest('.proof-center');
        container.querySelectorAll('.type-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        container.querySelectorAll('.proof-input-group').forEach(g => g.classList.remove('active'));
        document.getElementById(`input-${type}-${reqId}`).classList.add('active');

        activeTypes[reqId] = type;
        document.getElementById(`submit-${reqId}`).disabled = false;
    }

    function updateFileName(input, reqId) {
        if (input.files && input.files[0]) {
            document.getElementById(`filename-${reqId}`).innerText = input.files[0].name;
            document.getElementById(`filename-${reqId}`).style.color = "var(--accent-cyan)";
        }
    }

    async function transmitData(reqId) {
        const type = activeTypes[reqId];
        const btn = document.getElementById(`submit-${reqId}`);
        const originalHtml = btn.innerHTML;

        btn.disabled = true;
        btn.innerHTML = '<i class="material-icons-round spin">sync</i> ENVIANDO...';

        const fd = new FormData();
        fd.append('requirement_id', reqId);
        fd.append('proof_type', type);

        if (type === 'url') fd.append('content', document.getElementById(`val-url-${reqId}`).value);
        else if (type === 'text') fd.append('content', document.getElementById(`val-text-${reqId}`).value);
        else if (type === 'file') {
            const file = document.getElementById(`file-${reqId}`).files[0];
            if (file) fd.append('file', file);
        }

        try {
            const r = await fetch(`/${tenantSlug}/especialidades/${assignmentId}/prova`, {
                method: 'POST',
                body: fd
            });
            const d = await r.json();

            if (d.success) {
                location.reload();
            } else {
                alert(d.error || 'Erro ao transmitir dados');
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        } catch (e) {
            alert('Falha crítica na conexão');
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    }
</script>

</body>
</html>
