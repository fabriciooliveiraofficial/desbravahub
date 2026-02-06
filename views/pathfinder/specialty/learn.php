<?php
/**
 * E-Learning Specialty View - HUD REDESIGN
 */
$pageTitle = htmlspecialchars($assignment['specialty']['name'] ?? 'Especialidade');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - Treinamento Neural</title>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700;800&family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/hud-theme.css') ?>">
    <style>
        body {
            background-color: #0d0d1a;
            color: #fff;
            font-family: 'Outfit', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-image: 
                radial-gradient(circle at 50% 0%, rgba(0, 217, 255, 0.08), transparent 50%),
                linear-gradient(rgba(20, 20, 40, 0.4) 1px, transparent 1px),
                linear-gradient(90deg, rgba(20, 20, 40, 0.4) 1px, transparent 1px);
            background-size: 100% 100%, 40px 40px, 40px 40px;
        }

        /* Header Progress */
        .learn-header {
            background: rgba(10, 10, 20, 0.8);
            backdrop-filter: blur(20px);
            padding: 16px 32px;
            display: flex;
            align-items: center;
            gap: 24px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .back-btn {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.2s;
        }

        .back-btn:hover {
            background: rgba(239, 68, 68, 0.1);
            border-color: rgba(239, 68, 68, 0.3);
            color: #ef4444;
        }

        .hud-progress-container {
            flex: 1;
        }

        .progress-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--hud-text-dim);
        }

        .neural-progress-bar {
            height: 6px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 100px;
            position: relative;
            overflow: hidden;
        }

        .neural-progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--accent-cyan), var(--accent-purple));
            box-shadow: 0 0 15px rgba(0, 217, 255, 0.5);
            transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Main Simulation Area */
        .simulation-content {
            flex: 1;
            max-width: 800px;
            margin: 0 auto;
            width: 100%;
            padding: 40px 20px;
            display: flex;
            flex-direction: column;
        }

        .node-navigator {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-bottom: 32px;
            flex-wrap: wrap;
        }

        .node-dot {
            width: 12px;
            height: 12px;
            border-radius: 3px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s;
            text-decoration: none;
        }

        .node-dot.answered {
            background: var(--accent-cyan);
            box-shadow: 0 0 10px rgba(0, 217, 255, 0.4);
        }

        .node-dot.current {
            background: var(--accent-purple);
            transform: scale(1.4);
            box-shadow: 0 0 15px rgba(139, 92, 246, 0.5);
            border-color: #fff;
        }

        /* Training Card */
        .training-plate {
            padding: 40px;
            margin-bottom: 32px;
            min-height: 400px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .training-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .training-title {
            font-size: 1.8rem;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 16px;
            letter-spacing: -0.02em;
        }

        /* Question Options */
        .option-grid {
            display: grid;
            gap: 16px;
        }

        .option-card {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 20px 24px;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .option-card:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: var(--accent-cyan);
            transform: scale(1.02);
        }

        .option-card.selected {
            background: rgba(0, 217, 255, 0.1);
            border-color: var(--accent-cyan);
            box-shadow: 0 0 20px rgba(0, 217, 255, 0.1);
        }

        .option-bullet {
            width: 28px;
            height: 28px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.8rem;
            color: var(--hud-text-dim);
            transition: all 0.2s;
        }

        .option-card.selected .option-bullet {
            background: var(--accent-cyan);
            border-color: var(--accent-cyan);
            color: #000;
        }

        .option-text {
            font-size: 1.1rem;
            font-weight: 500;
        }

        /* Footer Console */
        .training-footer {
            background: rgba(10, 10, 20, 0.9);
            backdrop-filter: blur(20px);
            padding: 24px 32px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            bottom: 0;
        }

        /* Completion State */
        .completion-hero {
            text-align: center;
            padding: 60px 0;
            animation: zoomFade 0.8s cubic-bezier(0.2, 0.8, 0.2, 1);
        }

        @keyframes zoomFade {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }

        @media (max-width: 768px) {
            .training-plate { padding: 24px; }
            .training-title { font-size: 1.4rem; }
            .training-footer { flex-direction: column; gap: 16px; }
            .hud-btn { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>

    <header class="learn-header">
        <a href="<?= base_url($tenant['slug'] . '/especialidades') ?>" class="back-btn">
            <i class="material-icons-round">close</i>
        </a>
        <div class="hud-progress-container">
            <div class="progress-meta">
                <span>SIMULAÇÃO DE NEURAL: <?= $pageTitle ?></span>
                <span><?= count(array_filter($requirements, fn($r) => $r['status'] !== 'pending')) ?> / <?= $totalReqs ?> NODES</span>
            </div>
            <div class="neural-progress-bar">
                <div class="neural-progress-fill" style="width: <?= round($progress) ?>%"></div>
            </div>
        </div>
    </header>

    <main class="simulation-content">
        <?php if ($progress >= 100): ?>
            <div class="completion-hero">
                <div style="font-size: 5rem; margin-bottom: 24px; filter: drop-shadow(0 0 20px var(--accent-green));">🏆</div>
                <h1 style="font-size: 3rem; font-weight: 800; background: linear-gradient(90deg, #fff, var(--accent-green)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">SINCRO CONCLUÍDA</h1>
                <p style="color: var(--hud-text-dim); font-size: 1.2rem; margin-bottom: 40px;">Todos os dados neuronais foram carregados no QG com sucesso.</p>
                <div class="tech-plate vibrant-green" style="padding: 20px; display: inline-block;">
                    <a href="<?= base_url($tenant['slug'] . '/especialidades/' . $assignment['id']) ?>" class="hud-btn primary">
                        EFETIVAR RECOMPENSA XP <i class="material-icons-round">auto_fix_high</i>
                    </a>
                </div>
            </div>
        <?php elseif ($currentRequirement): ?>
            
            <div class="node-navigator">
                <?php foreach ($requirements as $idx => $req): ?>
                    <a href="?q=<?= $idx + 1 ?>" 
                       class="node-dot <?= $req['status'] !== 'pending' ? 'answered' : '' ?> <?= $idx === $currentIndex ? 'current' : '' ?>"
                       title="Node <?= $idx + 1 ?>"></a>
                <?php endforeach; ?>
            </div>

            <div class="tech-plate vibrant-cyan training-plate">
                <div class="status-line"></div>
                
                <div class="training-header">
                    <span class="hud-badge" style="margin-bottom: 16px;">NODE DE CONHECIMENTO <?= $currentIndex + 1 ?></span>
                    <h2 class="training-title"><?= htmlspecialchars($currentRequirement['title']) ?></h2>
                    <?php if (!empty($currentRequirement['description'])): ?>
                        <p style="color: var(--hud-text-dim); line-height: 1.6; max-width: 600px; margin: 0 auto;"><?= nl2br(htmlspecialchars($currentRequirement['description'])) ?></p>
                    <?php endif; ?>
                </div>

                <form id="answerForm" style="width: 100%;">
                    <input type="hidden" name="requirement_id" value="<?= $currentRequirement['id'] ?>">
                    <input type="hidden" name="type" value="<?= htmlspecialchars($currentRequirement['type'] ?? 'text') ?>">

                    <?php if (($currentRequirement['type'] ?? 'text') === 'multiple_choice' && !empty($currentRequirement['options'])): ?>
                        <div class="option-grid">
                            <?php foreach ($currentRequirement['options'] as $optIdx => $option): ?>
                                <div class="option-card" onclick="selectChoice(this, '<?= htmlspecialchars($option) ?>')">
                                    <div class="option-bullet"><?= chr(65 + $optIdx) ?></div>
                                    <div class="option-text"><?= htmlspecialchars($option) ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" name="answer" id="selectedChoice" value="<?= htmlspecialchars($currentRequirement['answer'] ?? '') ?>">

                    <?php elseif (($currentRequirement['type'] ?? 'text') === 'file_upload'): ?>
                        <div style="text-align: center;">
                            <label for="fileInput" class="hud-btn secondary" style="padding: 40px; border-style: dashed; border-width: 2px; flex-direction: column; gap: 15px; cursor: pointer;">
                                <i class="material-icons-round" style="font-size: 3rem; color: var(--accent-cyan);">cloud_upload</i>
                                <div style="font-weight: 700; font-size: 1rem;" id="fileName">ANEXAR EVIDÊNCIA OPERACIONAL</div>
                                <div style="font-size: 0.7rem; opacity: 0.5;">FOTOS OU DOCUMENTOS (PDF, DOC)</div>
                                <input type="file" name="file" id="fileInput" style="display: none;" onchange="handleFile(this)" accept="image/*,.pdf,.doc,.docx">
                            </label>
                        </div>

                    <?php else: ?>
                        <div class="tech-plate" style="padding: 4px; background: rgba(0,0,0,0.2);">
                            <textarea name="answer" class="hud-input" style="min-height: 180px; resize: none; border: none; background: transparent; padding: 20px; font-size: 1.1rem;" placeholder="Insira seu relatório de treinamento aqui..."><?= htmlspecialchars($currentRequirement['answer'] ?? '') ?></textarea>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        <?php endif; ?>
    </main>

    <?php if ($progress < 100 && $currentRequirement): ?>
    <footer class="training-footer">
        <div style="display: flex; align-items: center; gap: 12px;">
            <i class="material-icons-round" style="color: var(--accent-cyan); font-size: 1.5rem;">settings_input_component</i>
            <div style="font-size: 0.75rem; color: var(--hud-text-dim); line-height: 1.2;">
                OPERADOR: <?= strtoupper($_SESSION['user']['username'] ?? 'USER-117') ?><br>
                STATUS: TRANSMITINDO...
            </div>
        </div>

        <div style="display: flex; gap: 16px;">
            <a href="<?= base_url($tenant['slug'] . '/especialidades') ?>" class="hud-btn secondary">SALVAR E SAIR</a>
            <button type="button" class="hud-btn primary" id="submitBtn" onclick="submitAnswer()">
                <span class="btn-text"><?= ($currentIndex + 1 < $totalReqs) ? 'PRÓXIMO NODE →' : 'FINALIZAR SINCRONIA ✓' ?></span>
                <span id="loadingState" style="display: none; align-items: center; gap: 8px;">
                    <i class="material-icons-round spin">sync</i> PROCESSANDO
                </span>
            </button>
        </div>
    </footer>
    <?php endif; ?>

    <script>
        const assignmentId = <?= $assignment['id'] ?>;
        const tenantSlug = '<?= $tenant['slug'] ?>';
        const currentIndex = <?= $currentIndex ?>;
        const totalReqs = <?= $totalReqs ?>;

        function selectChoice(card, val) {
            document.querySelectorAll('.option-card').forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');
            document.getElementById('selectedChoice').value = val;
        }

        // Pre-select if choice exists
        window.addEventListener('load', () => {
            const currentVal = document.getElementById('selectedChoice')?.value;
            if (currentVal) {
                document.querySelectorAll('.option-card').forEach(c => {
                    if (c.querySelector('.option-text').innerText === currentVal) c.classList.add('selected');
                });
            }
        });

        function handleFile(input) {
            if (input.files && input.files[0]) {
                document.getElementById('fileName').innerText = input.files[0].name;
                document.getElementById('fileName').parentElement.style.borderColor = "var(--accent-cyan)";
            }
        }

        async function submitAnswer() {
            const btn = document.getElementById('submitBtn');
            const txt = btn.querySelector('.btn-text');
            const loader = document.getElementById('loadingState');

            btn.disabled = true;
            txt.style.display = 'none';
            loader.style.display = 'flex';

            const form = document.getElementById('answerForm');
            const fd = new FormData(form);

            try {
                const r = await fetch(`/${tenantSlug}/especialidades/${assignmentId}/responder`, {
                    method: 'POST', body: fd
                });
                const d = await r.json();

                if (d.success) {
                    if (currentIndex + 1 < totalReqs) {
                        window.location.href = `?q=${currentIndex + 2}`;
                    } else {
                        window.location.reload();
                    }
                } else {
                    alert(d.error || 'Erro ao sincronizar node');
                    btn.disabled = false;
                    txt.style.display = 'inline';
                    loader.style.display = 'none';
                }
            } catch (e) {
                alert('Falha crítica na rede neural');
                btn.disabled = false;
                txt.style.display = 'inline';
                loader.style.display = 'none';
            }
        }
    </script>
</body>
</html>