<?php
/**
 * Quiz Controller
 * 
 * Handles quizzes for activity proofs.
 */

namespace App\Controllers;

use App\Core\App;

class QuizController
{
    /**
     * Show quiz for activity
     */
    public function show(array $params): void
    {
        $user = App::user();
        $tenant = App::tenant();
        $quizId = (int) $params['id'];

        $quiz = db_fetch_one(
            "SELECT q.*, a.title as activity_title
             FROM quizzes q
             JOIN activities a ON q.activity_id = a.id
             WHERE q.id = ? AND q.tenant_id = ?",
            [$quizId, $tenant['id']]
        );

        if (!$quiz) {
            http_response_code(404);
            echo "Quiz não encontrado";
            return;
        }

        // Check if user already completed
        $attempt = db_fetch_one(
            "SELECT * FROM user_quiz_attempts 
             WHERE quiz_id = ? AND user_id = ? AND passed = 1",
            [$quizId, $user['id']]
        );

        if ($attempt) {
            $completed = true;
            $questions = [];
        } else {
            $completed = false;
            $questions = db_fetch_all(
                "SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY `order` ASC",
                [$quizId]
            );

            // Get options for each question
            foreach ($questions as &$q) {
                $q['options'] = db_fetch_all(
                    "SELECT id, text FROM quiz_options WHERE question_id = ?",
                    [$q['id']]
                );
            }
        }

        require BASE_PATH . '/views/dashboard/quiz.php';
    }

    /**
     * Submit quiz answers
     */
    public function submit(array $params): void
    {
        $user = App::user();
        $tenant = App::tenant();
        $quizId = (int) $params['id'];

        $quiz = db_fetch_one(
            "SELECT * FROM quizzes WHERE id = ? AND tenant_id = ?",
            [$quizId, $tenant['id']]
        );

        if (!$quiz) {
            $this->json(['error' => 'Quiz não encontrado'], 404);
            return;
        }

        $answers = $_POST['answers'] ?? [];

        // Get all questions and correct answers
        $questions = db_fetch_all(
            "SELECT q.id, q.points, 
                (SELECT GROUP_CONCAT(id) FROM quiz_options WHERE question_id = q.id AND is_correct = 1) as correct_ids
             FROM quiz_questions q
             WHERE q.quiz_id = ?",
            [$quizId]
        );

        $totalPoints = 0;
        $earnedPoints = 0;
        $results = [];

        foreach ($questions as $q) {
            $totalPoints += $q['points'];
            $correctIds = explode(',', $q['correct_ids']);
            $userAnswer = $answers[$q['id']] ?? null;

            $isCorrect = in_array($userAnswer, $correctIds);

            if ($isCorrect) {
                $earnedPoints += $q['points'];
            }

            $results[$q['id']] = $isCorrect;
        }

        $percentage = $totalPoints > 0 ? ($earnedPoints / $totalPoints) * 100 : 0;
        $passed = $percentage >= $quiz['passing_score'];

        // Record attempt
        db_insert('user_quiz_attempts', [
            'quiz_id' => $quizId,
            'user_id' => $user['id'],
            'tenant_id' => $tenant['id'],
            'score' => $earnedPoints,
            'max_score' => $totalPoints,
            'passed' => $passed ? 1 : 0,
            'answers_json' => json_encode($answers),
        ]);

        // If passed, create proof
        if ($passed) {
            // Get user activity
            $userActivity = db_fetch_one(
                "SELECT id FROM user_activities WHERE user_id = ? AND activity_id = ?",
                [$user['id'], $quiz['activity_id']]
            );

            if (!$userActivity) {
                $userActivityId = db_insert('user_activities', [
                    'user_id' => $user['id'],
                    'activity_id' => $quiz['activity_id'],
                    'tenant_id' => $tenant['id'],
                    'status' => 'in_progress',
                ]);
            } else {
                $userActivityId = $userActivity['id'];
            }

            // Create proof with approved status (quiz auto-validates)
            db_insert('activity_proofs', [
                'user_activity_id' => $userActivityId,
                'type' => 'quiz',
                'content' => "Quiz #$quizId - Score: $earnedPoints/$totalPoints",
                'status' => 'approved',
            ]);

            // Update activity status
            db_update('user_activities', ['status' => 'completed'], 'id = ?', [$userActivityId]);

            // Award XP
            $activity = db_fetch_one("SELECT xp_reward FROM activities WHERE id = ?", [$quiz['activity_id']]);
            if ($activity) {
                db_query(
                    "UPDATE users SET xp_points = xp_points + ? WHERE id = ?",
                    [$activity['xp_reward'], $user['id']]
                );
            }
        }

        $this->json([
            'success' => true,
            'passed' => $passed,
            'score' => $earnedPoints,
            'maxScore' => $totalPoints,
            'percentage' => round($percentage, 1),
            'results' => $results,
        ]);
    }

    private function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}
