<?php
/**
 * E-Learning: Outdoor Specialty View
 * 
 * For practical/outdoor specialties that require proof submission
 * instead of quiz questions.
 */

$pageTitle = htmlspecialchars($assignment['specialty']['name'] ?? 'Especialidade');
$specialty = $assignment['specialty'];
$progress = $progressPercent ?? 0;

// Get requirements with their progress status
use App\Services\SpecialtyService;
$requirements = SpecialtyService::getRequirementsWithProgress($assignment['id'], $specialty['id']);
$totalReqs = count($requirements);
$completedReqs = 0;
$pendingReview = 0;

foreach ($requirements as $req) {
    if ($req['status'] === 'approved')
        $completedReqs++;
    if ($req['status'] === 'submitted' || $req['status'] === 'answered')
        $pendingReview++;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - Prática</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #58cc02;
            --primary-hover: #4caf00;
            --secondary: #1cb0f6;
            --bg-dark: #1b1b2f;
            --bg-card: #262640;
            --bg-input: #1e1e35;
            --text: #ffffff;
            --text-muted: #a3a3a3;
            --border: rgba(255, 255, 255, 0.1);
            --success: #58cc02;
            --warning: #ff9800;
            --danger: #ff4b4b;
            --purple: #9c27b0;
            --radius: 16px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-dark);
            color: var(--text);
            min-height: 100vh;
            padding-bottom: 100px;
        }

        /* Header */
        .header {
            background: var(--bg-card);
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-content {
            max-width: 800px;
            margin: 0 auto;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 14px;
            margin-bottom: 16px;
        }

        .back-link:hover {
            color: var(--secondary);
        }

        .specialty-header {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .specialty-icon {
            font-size: 3rem;
        }

        .specialty-info h1 {
            font-size: 1.5rem;
            margin-bottom: 8px;
        }

        .specialty-tags {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .tag {
            padding: 4px 12px;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 16px;
            font-size: 12px;
        }

        .tag.outdoor {
            background: rgba(76, 175, 80, 0.2);
            color: #4caf50;
        }

        .tag.xp {
            background: rgba(88, 204, 2, 0.2);
            color: var(--success);
        }

        /* Progress section */
        .progress-section {
            max-width: 800px;
            margin: 24px auto;
            padding: 0 16px;
        }

        .progress-card {
            background: var(--bg-card);
            border-radius: var(--radius);
            padding: 20px;
        }

        .progress-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .progress-bar {
            height: 10px;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 5px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--secondary), var(--primary));
            border-radius: 5px;
            transition: width 0.5s ease;
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-top: 16px;
        }

        .stat-box {
            padding: 12px;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            text-align: center;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .stat-value.approved {
            color: var(--success);
        }

        .stat-value.pending {
            color: var(--purple);
        }

        .stat-label {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 4px;
        }

        /* Requirements list */
        .requirements-section {
            max-width: 800px;
            margin: 24px auto;
            padding: 0 16px;
        }

        .section-title {
            font-size: 1.1rem;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .requirement-card {
            background: var(--bg-card);
            border-radius: var(--radius);
            padding: 20px;
            margin-bottom: 16px;
            border-left: 4px solid var(--border);
            transition: all 0.2s;
        }

        .requirement-card.pending {
            border-left-color: var(--warning);
        }

        .requirement-card.submitted,
        .requirement-card.answered {
            border-left-color: var(--purple);
        }

        .requirement-card.approved {
            border-left-color: var(--success);
        }

        .requirement-card.rejected {
            border-left-color: var(--danger);
        }

        .requirement-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 12px;
        }

        .requirement-number {
            font-weight: 700;
            color: var(--secondary);
            margin-right: 8px;
        }

        .requirement-title {
            flex: 1;
            line-height: 1.5;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 16px;
            font-size: 12px;
            font-weight: 500;
            white-space: nowrap;
        }

        .status-badge.pending {
            background: rgba(255, 152, 0, 0.2);
            color: var(--warning);
        }

        .status-badge.submitted,
        .status-badge.answered {
            background: rgba(156, 39, 176, 0.2);
            color: var(--purple);
        }

        .status-badge.approved {
            background: rgba(88, 204, 2, 0.2);
            color: var(--success);
        }

        .status-badge.rejected {
            background: rgba(255, 75, 75, 0.2);
            color: var(--danger);
        }

        /* Proof submission */
        .proof-section {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 12px;
            padding: 16px;
            margin-top: 12px;
        }

        .proof-label {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 8px;
        }

        .proof-types {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }

        .proof-type-btn {
            padding: 8px 16px;
            background: transparent;
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text);
            cursor: pointer;
            font-size: 13px;
            transition: all 0.2s;
        }

        .proof-type-btn:hover {
            border-color: var(--secondary);
        }

        .proof-type-btn.active {
            background: var(--secondary);
            border-color: var(--secondary);
            color: var(--bg-dark);
        }

        .proof-input {
            display: none;
        }

        .proof-input.active {
            display: block;
        }

        .proof-input input,
        .proof-input textarea {
            width: 100%;
            padding: 12px;
            background: var(--bg-input);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text);
            font-family: inherit;
            font-size: 14px;
        }

        .proof-input textarea {
            min-height: 100px;
            resize: vertical;
        }

        .file-upload-zone {
            border: 2px dashed var(--border);
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        .file-upload-zone:hover {
            border-color: var(--secondary);
            background: rgba(28, 176, 246, 0.1);
        }

        .file-upload-zone input {
            display: none;
        }

        .file-upload-icon {
            font-size: 2rem;
            margin-bottom: 8px;
        }

        .btn-submit-proof {
            padding: 12px 24px;
            background: linear-gradient(135deg, var(--secondary), var(--primary));
            border: none;
            border-radius: 8px;
            color: var(--bg-dark);
            font-weight: 600;
            cursor: pointer;
            margin-top: 12px;
            transition: all 0.2s;
        }

        .btn-submit-proof:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Proof preview */
        .proof-preview {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: rgba(88, 204, 2, 0.1);
            border: 1px solid var(--success);
            border-radius: 8px;
            margin-top: 8px;
        }

        .proof-preview a {
            color: var(--secondary);
            word-break: break-all;
        }

        /* Feedback section */
        .feedback-section {
            background: rgba(255, 75, 75, 0.1);
            border: 1px solid var(--danger);
            border-radius: 8px;
            padding: 12px;
            margin-top: 12px;
        }

        .feedback-section.success {
            background: rgba(88, 204, 2, 0.1);
            border-color: var(--success);
        }

        /* Toast */
        .toast {
            position: fixed;
            bottom: 80px;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            padding: 14px 24px;
            background: var(--success);
            color: var(--bg-dark);
            border-radius: 8px;
            font-weight: 600;
            opacity: 0;
            transition: all 0.3s;
            z-index: 1000;
        }

        .toast.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }

        .toast.error {
            background: var(--danger);
            color: white;
        }

        /* Responsive */
        @media (max-width: 600px) {
            .specialty-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .specialty-icon {
                font-size: 2.5rem;
            }

            .specialty-info h1 {
                font-size: 1.3rem;
            }

            .stats-row {
                grid-template-columns: repeat(3, 1fr);
                gap: 8px;
            }

            .stat-value {
                font-size: 1.2rem;
            }

            .requirement-header {
                flex-direction: column;
            }

            .proof-types {
                flex-direction: column;
            }

            .proof-type-btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>

<body>
    <header class="header">
        <div class="header-content">
            <a href="<?= base_url($tenant['slug'] . '/especialidades') ?>" class="back-link">
                ← Voltar às Especialidades
            </a>

            <div class="specialty-header">
                <span class="specialty-icon"><?= $specialty['badge_icon'] ?? '🎯' ?></span>
                <div class="specialty-info">
                    <h1><?= htmlspecialchars($specialty['name']) ?></h1>
                    <div class="specialty-tags">
                        <span class="tag outdoor">🏕️ Prática</span>
                        <span class="tag"><?= $specialty['category']['icon'] ?? '' ?>
                            <?= htmlspecialchars($specialty['category']['name'] ?? '') ?></span>
                        <span class="tag xp">🌟 <?= SpecialtyService::getXpReward($specialty['id']) ?> XP</span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <section class="progress-section">
        <div class="progress-card">
            <div class="progress-header">
                <strong>Progresso Geral</strong>
                <span><?= $progress ?>%</span>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?= $progress ?>%"></div>
            </div>
            <div class="stats-row">
                <div class="stat-box">
                    <div class="stat-value approved"><?= $completedReqs ?></div>
                    <div class="stat-label">Aprovados</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value pending"><?= $pendingReview ?></div>
                    <div class="stat-label">Aguardando</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value"><?= $totalReqs ?></div>
                    <div class="stat-label">Total</div>
                </div>
            </div>
        </div>
    </section>

    <section class="requirements-section">
        <h2 class="section-title">📋 Requisitos Práticos</h2>

        <?php foreach ($requirements as $index => $req): ?>
            <?php
            $status = $req['status'] ?? 'pending';
            $statusLabel = match ($status) {
                'pending' => 'Pendente',
                'answered', 'submitted' => 'Enviado',
                'approved' => 'Aprovado ✓',
                'rejected' => 'Correção Necessária',
                default => $status
            };
            ?>
            <div class="requirement-card <?= $status ?>" data-req-id="<?= $req['id'] ?>">
                <div class="requirement-header">
                    <div class="requirement-title">
                        <span class="requirement-number"><?= $index + 1 ?>.</span>
                        <?= htmlspecialchars($req['title']) ?>
                    </div>
                    <span class="status-badge <?= $status ?>"><?= $statusLabel ?></span>
                </div>

                <?php if ($status === 'pending' || $status === 'rejected'): ?>
                    <!-- Proof submission form -->
                    <div class="proof-section">
                        <div class="proof-label">Enviar comprovação:</div>
                        <div class="proof-types">
                            <button type="button" class="proof-type-btn" data-type="url"
                                onclick="selectProofType(this, <?= $req['id'] ?>)">🔗 Link/URL</button>
                            <button type="button" class="proof-type-btn" data-type="file"
                                onclick="selectProofType(this, <?= $req['id'] ?>)">📁 Arquivo</button>
                            <button type="button" class="proof-type-btn" data-type="text"
                                onclick="selectProofType(this, <?= $req['id'] ?>)">📝 Descrição</button>
                        </div>

                        <div class="proof-input" id="proof-url-<?= $req['id'] ?>">
                            <input type="url" placeholder="Cole o link aqui (YouTube, Instagram, etc.)"
                                id="url-input-<?= $req['id'] ?>">
                        </div>

                        <div class="proof-input" id="proof-file-<?= $req['id'] ?>">
                            <div class="file-upload-zone"
                                onclick="document.getElementById('file-input-<?= $req['id'] ?>').click()">
                                <div class="file-upload-icon">📤</div>
                                <div>Clique para enviar foto ou vídeo</div>
                                <input type="file" id="file-input-<?= $req['id'] ?>" accept="image/*,video/*"
                                    onchange="handleFileSelect(this, <?= $req['id'] ?>)">
                            </div>
                            <div id="file-preview-<?= $req['id'] ?>" style="display: none;" class="proof-preview">
                                <span>📎</span>
                                <span id="file-name-<?= $req['id'] ?>"></span>
                            </div>
                        </div>

                        <div class="proof-input" id="proof-text-<?= $req['id'] ?>">
                            <textarea placeholder="Descreva como você completou este requisito..."
                                id="text-input-<?= $req['id'] ?>"></textarea>
                        </div>

                        <button class="btn-submit-proof" id="submit-btn-<?= $req['id'] ?>"
                            onclick="submitProof(<?= $req['id'] ?>)" disabled>
                            ✅ Enviar Comprovação
                        </button>
                    </div>
                <?php elseif ($status === 'answered' || $status === 'submitted'): ?>
                    <!-- Waiting for review -->
                    <div class="proof-section">
                        <div class="proof-label">⏳ Aguardando aprovação do instrutor</div>
                        <?php if (!empty($req['answer']) || !empty($req['file_path'])): ?>
                            <div class="proof-preview">
                                <?php if (!empty($req['file_path'])): ?>
                                    <span>📎</span>
                                    <a href="<?= htmlspecialchars($req['file_path']) ?>" target="_blank">Ver arquivo enviado</a>
                                <?php else: ?>
                                    <span>📝</span>
                                    <span><?= htmlspecialchars(substr($req['answer'] ?? '', 0, 100)) ?>...</span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php elseif ($status === 'approved'): ?>
                    <!-- Approved -->
                    <div class="feedback-section success">
                        <strong>✅ Aprovado!</strong>
                        <?php if (!empty($req['feedback'])): ?>
                            <p style="margin-top: 8px;"><?= htmlspecialchars($req['feedback']) ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if ($status === 'rejected' && !empty($req['feedback'])): ?>
                    <div class="feedback-section">
                        <strong>❌ Correção necessária:</strong>
                        <p style="margin-top: 8px;"><?= htmlspecialchars($req['feedback']) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </section>

    <div id="toast" class="toast"></div>

    <script>
        const assignmentId = <?= $assignment['id'] ?>;
        const tenantSlug = '<?= $tenant['slug'] ?>';
        const proofTypes = {}; // Store selected proof type per requirement

        function selectProofType(btn, reqId) {
            // Update button states
            btn.closest('.proof-types').querySelectorAll('.proof-type-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            // Show corresponding input
            document.querySelectorAll(`[id^="proof-"][id$="-${reqId}"]`).forEach(el => el.classList.remove('active'));
            document.getElementById(`proof-${btn.dataset.type}-${reqId}`).classList.add('active');

            proofTypes[reqId] = btn.dataset.type;
            document.getElementById(`submit-btn-${reqId}`).disabled = false;
        }

        function handleFileSelect(input, reqId) {
            const file = input.files[0];
            if (file) {
                document.getElementById(`file-name-${reqId}`).textContent = file.name;
                document.getElementById(`file-preview-${reqId}`).style.display = 'flex';
            }
        }

        async function submitProof(reqId) {
            const type = proofTypes[reqId];
            if (!type) return;

            const btn = document.getElementById(`submit-btn-${reqId}`);
            btn.disabled = true;
            btn.textContent = 'Enviando...';

            const formData = new FormData();
            formData.append('requirement_id', reqId);
            formData.append('proof_type', type);

            if (type === 'url') {
                formData.append('content', document.getElementById(`url-input-${reqId}`).value);
            } else if (type === 'file') {
                const file = document.getElementById(`file-input-${reqId}`).files[0];
                if (file) formData.append('file', file);
            } else if (type === 'text') {
                formData.append('content', document.getElementById(`text-input-${reqId}`).value);
            }

            try {
                const response = await fetch(`/${tenantSlug}/especialidades/${assignmentId}/prova`, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    showToast('Comprovação enviada!', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(data.error || 'Erro ao enviar', 'error');
                    btn.disabled = false;
                    btn.textContent = '✅ Enviar Comprovação';
                }
            } catch (err) {
                showToast('Erro de conexão', 'error');
                btn.disabled = false;
                btn.textContent = '✅ Enviar Comprovação';
            }
        }

        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = 'toast ' + type;
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 3000);
        }
    </script>
</body>

</html>