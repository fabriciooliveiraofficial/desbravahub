<?php
/**
 * E-Learning Specialty View
 * 
 * Duolingo-style question-by-question learning interface.
 */

$pageTitle = htmlspecialchars($assignment['specialty']['name'] ?? 'Especialidade');
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - Aprender</title>
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
            display: flex;
            flex-direction: column;
        }

        /* Header with progress */
        .learn-header {
            background: var(--bg-card);
            padding: 16px 24px;
            display: flex;
            align-items: center;
            gap: 16px;
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .back-btn {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 24px;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background 0.2s;
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .progress-container {
            flex: 1;
        }

        .progress-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .progress-title {
            font-weight: 600;
            color: var(--text);
        }

        .progress-count {
            color: var(--text-muted);
        }

        .progress-bar {
            height: 16px;
            background: var(--bg-input);
            border-radius: 8px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary), #7adc3e);
            border-radius: 8px;
            transition: width 0.5s ease;
        }

        /* Main content */
        .learn-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            max-width: 700px;
            margin: 0 auto;
            width: 100%;
        }

        /* Question indicator */
        .question-indicator {
            color: var(--text-muted);
            font-size: 14px;
            margin-bottom: 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Question card */
        .question-card {
            background: var(--bg-card);
            border-radius: var(--radius);
            padding: 32px;
            width: 100%;
            border: 2px solid var(--border);
            margin-bottom: 24px;
        }

        .question-title {
            font-size: 22px;
            font-weight: 600;
            line-height: 1.5;
            margin-bottom: 16px;
        }

        .question-description {
            color: var(--text-muted);
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 24px;
        }

        /* Answer input */
        .answer-area {
            margin-top: 16px;
        }

        .text-answer {
            width: 100%;
            min-height: 120px;
            padding: 16px;
            border: 2px solid var(--border);
            border-radius: 12px;
            background: var(--bg-input);
            color: var(--text);
            font-family: inherit;
            font-size: 16px;
            resize: vertical;
            transition: border-color 0.2s;
        }

        .text-answer:focus {
            outline: none;
            border-color: var(--primary);
        }

        /* Multiple choice */
        .choice-option {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px;
            background: var(--bg-input);
            border: 2px solid var(--border);
            border-radius: 12px;
            cursor: pointer;
            margin-bottom: 12px;
            transition: all 0.2s;
        }

        .choice-option:hover {
            border-color: var(--secondary);
            transform: translateX(4px);
        }

        .choice-option.selected {
            border-color: var(--primary);
            background: rgba(88, 204, 2, 0.1);
        }

        .choice-radio {
            width: 24px;
            height: 24px;
            border: 2px solid var(--border);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .choice-option.selected .choice-radio {
            border-color: var(--primary);
            background: var(--primary);
        }

        .choice-option.selected .choice-radio::after {
            content: '✓';
            color: white;
            font-size: 14px;
            font-weight: bold;
        }

        .choice-text {
            font-size: 16px;
        }

        /* File upload */
        .file-upload {
            border: 2px dashed var(--border);
            border-radius: 12px;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        .file-upload:hover {
            border-color: var(--secondary);
            background: rgba(28, 176, 246, 0.1);
        }

        .file-upload input {
            display: none;
        }

        .file-upload-icon {
            font-size: 48px;
            margin-bottom: 16px;
        }

        .file-upload-text {
            color: var(--text-muted);
            font-size: 14px;
        }

        .file-preview {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px;
            background: rgba(88, 204, 2, 0.1);
            border: 2px solid var(--primary);
            border-radius: 12px;
            margin-top: 12px;
        }

        /* Actions footer */
        .learn-footer {
            background: var(--bg-card);
            padding: 20px 24px;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
        }

        .btn {
            padding: 16px 32px;
            border: none;
            border-radius: 12px;
            font-family: inherit;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-secondary {
            background: var(--bg-input);
            color: var(--text-muted);
            border: 2px solid var(--border);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text);
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 0 #3d8c00;
        }

        .btn-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 6px 0 #3d8c00;
        }

        .btn-primary:active {
            transform: translateY(2px);
            box-shadow: 0 2px 0 #3d8c00;
        }

        .btn-primary:disabled {
            background: #555;
            box-shadow: 0 4px 0 #333;
            cursor: not-allowed;
        }

        /* Question navigator */
        .question-nav {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 24px;
        }

        .nav-dot {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            color: var(--text-muted);
            background: var(--bg-input);
            border: 2px solid var(--border);
            transition: all 0.2s;
        }

        .nav-dot:hover {
            border-color: var(--secondary);
        }

        .nav-dot.answered {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
        }

        .nav-dot.current {
            border-color: var(--secondary);
            background: rgba(28, 176, 246, 0.2);
            color: var(--secondary);
        }

        /* Status badge */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-badge.answered {
            background: rgba(88, 204, 2, 0.2);
            color: var(--success);
        }

        .status-badge.pending {
            background: rgba(255, 152, 0, 0.2);
            color: var(--warning);
        }

        /* Completion screen */
        .completion-screen {
            text-align: center;
            padding: 60px 20px;
        }

        .completion-icon {
            font-size: 80px;
            margin-bottom: 24px;
        }

        .completion-title {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 16px;
            background: linear-gradient(90deg, var(--primary), #7adc3e);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .completion-message {
            color: var(--text-muted);
            font-size: 18px;
            margin-bottom: 32px;
        }

        /* Loading spinner */
        .loading {
            display: none;
            align-items: center;
            gap: 8px;
        }

        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid transparent;
            border-top-color: currentColor;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Toast notification */
        .toast {
            position: fixed;
            bottom: 100px;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            background: var(--bg-card);
            color: var(--text);
            padding: 16px 24px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
            display: flex;
            align-items: center;
            gap: 12px;
            opacity: 0;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .toast.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }

        .toast.success {
            border-left: 4px solid var(--success);
        }

        .toast.error {
            border-left: 4px solid var(--danger);
        }

        /* Responsive */
        @media (max-width: 600px) {
            .learn-content {
                padding: 24px 16px;
            }

            .question-card {
                padding: 24px;
            }

            .question-title {
                font-size: 18px;
            }

            .learn-footer {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }

            .nav-dot {
                width: 36px;
                height: 36px;
                font-size: 12px;
            }
        }
    </style>
</head>

<body>
    <!-- Header with progress -->
    <header class="learn-header">
        <a href="<?= base_url($tenant['slug'] . '/especialidades') ?>" class="back-btn" title="Voltar">✕</a>
        <div class="progress-container">
            <div class="progress-info">
                <span class="progress-title"><?= $pageTitle ?></span>
                <span
                    class="progress-count"><?= count(array_filter($requirements, fn($r) => $r['status'] !== 'pending')) ?>
                    / <?= $totalReqs ?></span>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?= round($progress) ?>%"></div>
            </div>
        </div>
    </header>

    <!-- Main content -->
    <main class="learn-content">
        <?php if ($progress >= 100): ?>
            <!-- All requirements answered -->
            <div class="completion-screen">
                <div class="completion-icon">🎉</div>
                <h1 class="completion-title">Parabéns!</h1>
                <p class="completion-message">Você completou todos os requisitos desta especialidade!</p>
                <a href="<?= base_url($tenant['slug'] . '/especialidades/' . $assignment['id']) ?>" class="btn btn-primary">
                    Ver Detalhes
                </a>
            </div>
        <?php elseif ($currentRequirement): ?>
            <!-- Question navigator -->
            <div class="question-nav">
                <?php foreach ($requirements as $idx => $req): ?>
                    <a href="?q=<?= $idx + 1 ?>"
                        class="nav-dot <?= $req['status'] !== 'pending' ? 'answered' : '' ?> <?= $idx === $currentIndex ? 'current' : '' ?>"
                        title="Requisito <?= $idx + 1 ?>">
                        <?= $idx + 1 ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Question indicator -->
            <div class="question-indicator">
                Requisito <?= $currentIndex + 1 ?> de <?= $totalReqs ?>
                <?php if ($currentRequirement['status'] !== 'pending'): ?>
                    <span class="status-badge answered">✓ Respondido</span>
                <?php endif; ?>
            </div>

            <!-- Question card -->
            <div class="question-card">
                <h2 class="question-title"><?= htmlspecialchars($currentRequirement['title']) ?></h2>

                <?php if (!empty($currentRequirement['description'])): ?>
                    <p class="question-description"><?= nl2br(htmlspecialchars($currentRequirement['description'])) ?></p>
                <?php endif; ?>

                <form id="answerForm" class="answer-area">
                    <input type="hidden" name="requirement_id" value="<?= $currentRequirement['id'] ?>">
                    <input type="hidden" name="type" value="<?= htmlspecialchars($currentRequirement['type'] ?? 'text') ?>">

                    <?php if (($currentRequirement['type'] ?? 'text') === 'multiple_choice' && !empty($currentRequirement['options'])): ?>
                        <!-- Multiple choice -->
                        <?php foreach ($currentRequirement['options'] as $optIdx => $option): ?>
                            <label class="choice-option" data-value="<?= htmlspecialchars($option) ?>">
                                <span class="choice-radio"></span>
                                <span class="choice-text"><?= htmlspecialchars($option) ?></span>
                            </label>
                        <?php endforeach; ?>
                        <input type="hidden" name="answer" id="selectedChoice"
                            value="<?= htmlspecialchars($currentRequirement['answer'] ?? '') ?>">

                    <?php elseif (($currentRequirement['type'] ?? 'text') === 'file_upload'): ?>
                        <!-- File upload -->
                        <label class="file-upload" id="fileUploadZone">
                            <div class="file-upload-icon">📷</div>
                            <div class="file-upload-text">
                                Clique para enviar foto ou arquivo
                            </div>
                            <input type="file" name="file" id="fileInput" accept="image/*,.pdf,.doc,.docx">
                        </label>
                        <div id="filePreview" style="display: none;" class="file-preview">
                            <span>📎</span>
                            <span id="fileName"></span>
                        </div>

                    <?php else: ?>
                        <!-- Text answer -->
                        <textarea name="answer" class="text-answer" placeholder="Digite sua resposta aqui..."
                            rows="4"><?= htmlspecialchars($currentRequirement['answer'] ?? '') ?></textarea>
                    <?php endif; ?>
                </form>
            </div>
        <?php endif; ?>
    </main>

    <!-- Footer actions -->
    <?php if ($progress < 100 && $currentRequirement): ?>
        <footer class="learn-footer">
            <a href="<?= base_url($tenant['slug'] . '/especialidades') ?>" class="btn btn-secondary">
                Salvar e Sair
            </a>
            <button type="button" id="submitBtn" class="btn btn-primary" onclick="submitAnswer()">
                <span class="btn-text">
                    <?= ($currentIndex + 1 < $totalReqs) ? 'Responder e Avançar →' : 'Finalizar ✓' ?>
                </span>
                <span class="loading">
                    <span class="spinner"></span>
                    Salvando...
                </span>
            </button>
        </footer>
    <?php endif; ?>

    <!-- Toast notification -->
    <div id="toast" class="toast"></div>

    <script>
        const assignmentId = <?= $assignment['id'] ?>;
        const currentIndex = <?= $currentIndex ?>;
        const totalReqs = <?= $totalReqs ?>;
        const tenantSlug = '<?= $tenant['slug'] ?>';
        const requirementId = <?= $currentRequirement['id'] ?? 0 ?>;

        // LocalStorage backup key
        const backupKey = `specialty_${assignmentId}_${currentIndex}`;

        // Auto-save to LocalStorage on input change
        function saveToLocalStorage() {
            const form = document.getElementById('answerForm');
            if (!form) return;

            const formData = {};
            const textArea = form.querySelector('textarea[name="answer"]');
            const choiceInput = form.querySelector('input[name="answer"]');

            if (textArea) {
                formData.answer = textArea.value;
                formData.type = 'text';
            } else if (choiceInput) {
                formData.answer = choiceInput.value;
                formData.type = 'choice';
            }

            if (formData.answer) {
                localStorage.setItem(backupKey, JSON.stringify({
                    ...formData,
                    timestamp: Date.now(),
                    requirementId: requirementId
                }));
                console.log('Auto-saved to LocalStorage');
            }
        }

        // Restore from LocalStorage if available
        function restoreFromLocalStorage() {
            try {
                const saved = localStorage.getItem(backupKey);
                if (!saved) return;

                const data = JSON.parse(saved);

                // Only restore if saved within last 24 hours
                if (Date.now() - data.timestamp > 24 * 60 * 60 * 1000) {
                    localStorage.removeItem(backupKey);
                    return;
                }

                if (data.type === 'text') {
                    const textArea = document.querySelector('textarea[name="answer"]');
                    if (textArea && !textArea.value) {
                        textArea.value = data.answer;
                        console.log('Restored from LocalStorage');
                    }
                }
            } catch (e) {
                console.error('Error restoring from LocalStorage:', e);
            }
        }

        // Clear backup after successful save
        function clearLocalStorageBackup() {
            localStorage.removeItem(backupKey);
        }

        // Initialize: restore and setup auto-save
        document.addEventListener('DOMContentLoaded', () => {
            restoreFromLocalStorage();

            // Auto-save on text input
            const textArea = document.querySelector('textarea[name="answer"]');
            if (textArea) {
                textArea.addEventListener('input', debounce(saveToLocalStorage, 1000));
            }
        });

        // Debounce helper
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }


        // Multiple choice selection
        document.querySelectorAll('.choice-option').forEach(option => {
            option.addEventListener('click', function () {
                document.querySelectorAll('.choice-option').forEach(o => o.classList.remove('selected'));
                this.classList.add('selected');
                document.getElementById('selectedChoice').value = this.dataset.value;
            });
        });

        // Mark pre-selected choice
        const preSelected = document.getElementById('selectedChoice')?.value;
        if (preSelected) {
            document.querySelectorAll('.choice-option').forEach(opt => {
                if (opt.dataset.value === preSelected) {
                    opt.classList.add('selected');
                }
            });
        }

        // File upload preview
        const fileInput = document.getElementById('fileInput');
        if (fileInput) {
            fileInput.addEventListener('change', function () {
                const file = this.files[0];
                if (file) {
                    document.getElementById('fileName').textContent = file.name;
                    document.getElementById('filePreview').style.display = 'flex';
                    document.getElementById('fileUploadZone').style.display = 'none';
                }
            });
        }

        // Submit answer
        async function submitAnswer() {
            const btn = document.getElementById('submitBtn');
            const btnText = btn.querySelector('.btn-text');
            const loading = btn.querySelector('.loading');

            btn.disabled = true;
            btnText.style.display = 'none';
            loading.style.display = 'flex';

            const form = document.getElementById('answerForm');
            const formData = new FormData(form);

            try {
                const response = await fetch(`/${tenantSlug}/especialidades/${assignmentId}/responder`, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    clearLocalStorageBackup(); // Clear backup on success
                    showToast('Resposta salva!', 'success');

                    // Go to next question or completion
                    setTimeout(() => {
                        if (currentIndex + 1 < totalReqs) {
                            window.location.href = `?q=${currentIndex + 2}`;
                        } else {
                            window.location.reload();
                        }
                    }, 800);
                } else {
                    showToast(data.error || 'Erro ao salvar', 'error');
                    btn.disabled = false;
                    btnText.style.display = 'inline';
                    loading.style.display = 'none';
                }
            } catch (err) {
                showToast('Erro de conexão', 'error');
                btn.disabled = false;
                btnText.style.display = 'inline';
                loading.style.display = 'none';
            }
        }

        // Toast notification
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = 'toast ' + type;
            toast.classList.add('show');

            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }
    </script>
</body>

</html>