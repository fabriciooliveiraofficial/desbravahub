<?php
/**
 * Quiz - Responder Quiz de Atividade
 */

use App\Core\App;

$tenant = App::tenant();
$user = App::user();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz | <?= htmlspecialchars($tenant['name']) ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
    <style>
        .quiz-container {
            max-width: 700px;
            margin: 0 auto;
        }

        .quiz-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .quiz-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .quiz-activity {
            color: var(--text-muted);
        }

        .question-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 20px;
            transition: all 0.3s;
        }

        .question-card.correct {
            border-color: var(--secondary);
            background: rgba(0, 255, 136, 0.05);
        }

        .question-card.incorrect {
            border-color: #ff6b6b;
            background: rgba(255, 107, 107, 0.05);
        }

        .question-number {
            display: inline-block;
            width: 32px;
            height: 32px;
            background: var(--primary);
            color: var(--dark-bg);
            border-radius: 50%;
            text-align: center;
            line-height: 32px;
            font-weight: 600;
            margin-right: 12px;
        }

        .question-text {
            font-size: 1.1rem;
            margin-bottom: 16px;
        }

        .options-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .option-label {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            border: 1px solid var(--border);
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .option-label:hover {
            border-color: var(--primary);
            background: rgba(0, 217, 255, 0.05);
        }

        .option-label input {
            display: none;
        }

        .option-label input:checked+.option-radio {
            background: var(--primary);
            border-color: var(--primary);
        }

        .option-label input:checked+.option-radio::after {
            content: '✓';
            color: var(--dark-bg);
        }

        .option-radio {
            width: 24px;
            height: 24px;
            border: 2px solid var(--border);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: all 0.3s;
        }

        .btn-submit {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: var(--dark-bg);
            margin-top: 20px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0, 217, 255, 0.4);
        }

        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .result-card {
            text-align: center;
            padding: 40px;
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 20px;
            margin-bottom: 24px;
        }

        .result-card.passed {
            border-color: var(--secondary);
            background: rgba(0, 255, 136, 0.05);
        }

        .result-card.failed {
            border-color: #ff6b6b;
            background: rgba(255, 107, 107, 0.05);
        }

        .result-icon {
            font-size: 64px;
            margin-bottom: 16px;
        }

        .result-title {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .result-score {
            font-size: 1.2rem;
            color: var(--text-muted);
        }

        .completed-message {
            text-align: center;
            padding: 60px 20px;
        }

        .completed-message .icon {
            font-size: 64px;
            margin-bottom: 16px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--text-muted);
            text-decoration: none;
            margin-bottom: 20px;
        }

        .back-link:hover {
            color: var(--primary);
        }
    </style>
</head>

<body>
    <?php require BASE_PATH . '/views/dashboard/partials/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <a href="<?= base_url($tenant['slug'] . '/especialidades') ?>" class="back-link">← Voltar</a>

            <div class="quiz-container">
                <div class="quiz-header">
                    <div class="quiz-title"><?= htmlspecialchars($quiz['title']) ?></div>
                    <div class="quiz-activity">📋 <?= htmlspecialchars($quiz['activity_title']) ?></div>
                </div>

                <?php if ($completed): ?>
                    <div class="completed-message">
                        <div class="icon">✅</div>
                        <h2>Quiz já concluído!</h2>
                        <p style="color: var(--text-muted); margin-top: 8px;">
                            Você já completou este quiz com sucesso.
                        </p>
                    </div>
                <?php else: ?>
                    <form id="quizForm">
                        <?php foreach ($questions as $i => $q): ?>
                            <div class="question-card" data-question="<?= $q['id'] ?>">
                                <div class="question-text">
                                    <span class="question-number"><?= $i + 1 ?></span>
                                    <?= htmlspecialchars($q['question']) ?>
                                </div>

                                <div class="options-list">
                                    <?php foreach ($q['options'] as $opt): ?>
                                        <label class="option-label">
                                            <input type="radio" name="answers[<?= $q['id'] ?>]" value="<?= $opt['id'] ?>" required>
                                            <span class="option-radio"></span>
                                            <span><?= htmlspecialchars($opt['text']) ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <button type="submit" class="btn-submit" id="submitBtn">
                            ✓ Enviar Respostas
                        </button>
                    </form>

                    <div id="resultContainer" style="display: none;">
                        <div class="result-card" id="resultCard">
                            <div class="result-icon" id="resultIcon"></div>
                            <div class="result-title" id="resultTitle"></div>
                            <div class="result-score" id="resultScore"></div>
                        </div>
                        <a href="<?= base_url($tenant['slug'] . '/especialidades') ?>" class="btn-submit"
                            style="text-decoration: none; display: block; text-align: center;">
                            Voltar às Especialidades
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php require BASE_PATH . '/views/dashboard/partials/nav.php'; ?>

    <script>
        const form = document.getElementById('quizForm');
        const resultContainer = document.getElementById('resultContainer');

        if (form) {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();

                const btn = document.getElementById('submitBtn');
                btn.disabled = true;
                btn.textContent = 'Enviando...';

                const formData = new FormData(form);

                try {
                    const response = await fetch('<?= base_url($tenant['slug'] . '/quiz/' . $quiz['id'] . '/enviar') ?>', {
                        method: 'POST',
                        body: formData
                    });

                    const data = await response.json();

                    if (data.success) {
                        // Show results on questions
                        Object.entries(data.results).forEach(([qId, correct]) => {
                            const card = document.querySelector(`[data-question="${qId}"]`);
                            if (card) {
                                card.classList.add(correct ? 'correct' : 'incorrect');
                            }
                        });

                        // Show result
                        form.style.display = 'none';
                        resultContainer.style.display = 'block';

                        const resultCard = document.getElementById('resultCard');
                        const resultIcon = document.getElementById('resultIcon');
                        const resultTitle = document.getElementById('resultTitle');
                        const resultScore = document.getElementById('resultScore');

                        if (data.passed) {
                            resultCard.classList.add('passed');
                            resultIcon.textContent = '🎉';
                            resultTitle.textContent = 'Parabéns!';
                            resultScore.textContent = `Você acertou ${data.percentage}% - Aprovado!`;
                        } else {
                            resultCard.classList.add('failed');
                            resultIcon.textContent = '😕';
                            resultTitle.textContent = 'Tente novamente';
                            resultScore.textContent = `Você acertou ${data.percentage}% - Mínimo: ${<?= $quiz['passing_score'] ?>}%`;
                        }
                    } else {
                        swal(data.error || 'Erro ao enviar', 'Houve um problema');
                        btn.disabled = false;
                        btn.textContent = '✓ Enviar Respostas';
                    }
                } catch (err) {
                    swal('Erro de conexão', 'Erro');
                    btn.disabled = false;
                    btn.textContent = '✓ Enviar Respostas';
                }
            });
        }
    </script>
</body>

</html>