<?php
/**
 * Admin - Gerenciar Quizzes
 */

use App\Core\App;

$tenant = App::tenant();
$user = App::user();

// Get all quizzes for this tenant with activity info
$quizzes = db_fetch_all(
    "SELECT q.*, a.title as activity_title,
        (SELECT COUNT(*) FROM quiz_questions WHERE quiz_id = q.id) as question_count,
        (SELECT COUNT(*) FROM user_quiz_attempts WHERE quiz_id = q.id) as attempt_count
     FROM quizzes q
     JOIN activities a ON q.activity_id = a.id
     WHERE q.tenant_id = ?
     ORDER BY q.created_at DESC",
    [$tenant['id']]
);

// Get activities for dropdown
$activities = db_fetch_all(
    "SELECT id, title FROM activities WHERE tenant_id = ? ORDER BY title",
    [$tenant['id']]
);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quizzes | Admin</title>
    <link rel="stylesheet" href="<?= asset_url('css/admin.css') ?>">
    <style>
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .btn-add {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: var(--dark-bg);
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
        }

        .quiz-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
        }

        .quiz-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .quiz-title {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .quiz-activity {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-top: 4px;
        }

        .quiz-stats {
            display: flex;
            gap: 16px;
            margin-top: 12px;
            font-size: 0.9rem;
        }

        .stat {
            display: flex;
            align-items: center;
            gap: 6px;
            color: var(--text-muted);
        }

        .actions {
            display: flex;
            gap: 8px;
        }

        .btn-small {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.85rem;
            cursor: pointer;
            border: 1px solid var(--border);
            background: transparent;
            color: var(--text);
            text-decoration: none;
        }

        .btn-small:hover {
            border-color: var(--primary);
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: var(--dark-bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 32px;
            width: 100%;
            max-width: 500px;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-title {
            font-size: 1.3rem;
            margin-bottom: 24px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.02);
            color: var(--text);
            font-size: 1rem;
        }

        .modal-actions {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }

        .btn-cancel {
            flex: 1;
            padding: 12px;
            border: 1px solid var(--border);
            background: transparent;
            color: var(--text);
            border-radius: 8px;
            cursor: pointer;
        }

        .btn-save {
            flex: 1;
            padding: 12px;
            border: none;
            background: var(--primary);
            color: var(--dark-bg);
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-muted);
        }
    </style>
</head>

<body>
    <?php require BASE_PATH . '/views/admin/partials/sidebar.php'; ?>

    <main class="admin-main">
        <div class="container">
            <div class="admin-header">
                <h1 class="page-title">📝 Quizzes</h1>
                <button class="btn-add" onclick="openModal()">➕ Novo Quiz</button>
            </div>

            <?php if (empty($quizzes)): ?>
                <div class="empty-state">
                    <p>Nenhum quiz criado ainda</p>
                    <p style="margin-top: 8px;">Crie quizzes para validar especialidades automaticamente!</p>
                </div>
            <?php else: ?>
                <?php foreach ($quizzes as $quiz): ?>
                    <div class="quiz-card">
                        <div class="quiz-header">
                            <div>
                                <div class="quiz-title"><?= htmlspecialchars($quiz['title']) ?></div>
                                <div class="quiz-activity">📋 <?= htmlspecialchars($quiz['activity_title']) ?></div>
                            </div>
                            <div class="actions">
                                <a href="<?= base_url($tenant['slug'] . '/admin/quizzes/' . $quiz['id'] . '/perguntas') ?>"
                                    class="btn-small">✏️ Perguntas</a>
                            </div>
                        </div>
                        <div class="quiz-stats">
                            <span class="stat">❓ <?= $quiz['question_count'] ?> perguntas</span>
                            <span class="stat">📊 <?= $quiz['attempt_count'] ?> tentativas</span>
                            <span class="stat">🎯 Mínimo: <?= $quiz['passing_score'] ?>%</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <!-- Modal Novo Quiz -->
    <div class="modal" id="modal">
        <div class="modal-content">
            <div class="modal-title">Novo Quiz</div>
            <form id="quizForm" action="<?= base_url($tenant['slug'] . '/admin/quizzes') ?>" method="POST">
                <div class="form-group">
                    <label>Título</label>
                    <input type="text" name="title" required placeholder="Ex: Quiz de Nós e Amarras">
                </div>

                <div class="form-group">
                    <label>Atividade</label>
                    <select name="activity_id" required>
                        <option value="">Selecione...</option>
                        <?php foreach ($activities as $a): ?>
                            <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Pontuação mínima (%)</label>
                    <input type="number" name="passing_score" value="70" min="1" max="100" required>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeModal()">Cancelar</button>
                    <button type="submit" class="btn-save">Criar Quiz</button>
                </div>
            </form>
        </div>
    </div>



    <script>
        function openModal() {
            document.getElementById('modal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('modal').classList.remove('active');
        }

        document.getElementById('modal').addEventListener('click', (e) => {
            if (e.target.id === 'modal') closeModal();
        });
    </script>
</body>

</html>