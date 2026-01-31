<?php
/**
 * Quiz Service
 * 
 * Manages quizzes, questions, and quiz attempts.
 */

namespace App\Services;

use App\Core\App;

class QuizService
{
    /**
     * Get quiz for an activity
     */
    public function getQuizForActivity(int $activityId): ?array
    {
        $tenantId = App::tenantId();

        $quiz = db_fetch_one(
            "SELECT * FROM quizzes WHERE activity_id = ? AND tenant_id = ?",
            [$activityId, $tenantId]
        );

        if ($quiz) {
            $quiz['questions'] = $this->getQuestions($quiz['id']);
        }

        return $quiz;
    }

    /**
     * Get questions for a quiz
     */
    public function getQuestions(int $quizId, bool $includeCorrectAnswers = false): array
    {
        $questions = db_fetch_all(
            "SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY order_position",
            [$quizId]
        );

        foreach ($questions as &$question) {
            $question['options'] = db_fetch_all(
                "SELECT id, option_text, order_position" . ($includeCorrectAnswers ? ", is_correct" : "") .
                " FROM quiz_options WHERE question_id = ? ORDER BY order_position",
                [$question['id']]
            );
        }

        return $questions;
    }

    /**
     * Start a quiz attempt
     */
    public function startAttempt(int $quizId, int $userId): array
    {
        $tenantId = App::tenantId();

        $quiz = db_fetch_one(
            "SELECT * FROM quizzes WHERE id = ? AND tenant_id = ?",
            [$quizId, $tenantId]
        );

        if (!$quiz) {
            return ['success' => false, 'error' => 'Quiz not found'];
        }

        // Check max attempts
        if ($quiz['max_attempts']) {
            $attemptCount = db_fetch_column(
                "SELECT COUNT(*) FROM user_quiz_attempts WHERE quiz_id = ? AND user_id = ?",
                [$quizId, $userId]
            );

            if ($attemptCount >= $quiz['max_attempts']) {
                return ['success' => false, 'error' => 'Maximum attempts reached'];
            }
        }

        $attemptId = db_insert('user_quiz_attempts', [
            'quiz_id' => $quizId,
            'user_id' => $userId,
            'tenant_id' => $tenantId,
            'answers' => json_encode([]),
        ]);

        return [
            'success' => true,
            'attempt_id' => $attemptId,
            'quiz' => $quiz,
            'questions' => $this->getQuestions($quizId, false),
        ];
    }

    /**
     * Submit quiz answers
     */
    public function submitAttempt(int $attemptId, array $answers): array
    {
        $tenantId = App::tenantId();
        $userId = App::user()['id'];

        $attempt = db_fetch_one(
            "SELECT * FROM user_quiz_attempts WHERE id = ? AND user_id = ? AND tenant_id = ? AND completed_at IS NULL",
            [$attemptId, $userId, $tenantId]
        );

        if (!$attempt) {
            return ['success' => false, 'error' => 'Invalid or completed attempt'];
        }

        $quiz = db_fetch_one("SELECT * FROM quizzes WHERE id = ?", [$attempt['quiz_id']]);
        $questions = $this->getQuestions($quiz['id'], true);

        // Calculate score
        $totalPoints = 0;
        $earnedPoints = 0;
        $results = [];

        foreach ($questions as $question) {
            $totalPoints += $question['points'];
            $userAnswer = $answers[$question['id']] ?? null;
            $isCorrect = false;

            // Check answer based on question type
            switch ($question['question_type']) {
                case 'single_choice':
                case 'true_false':
                    $correctOption = array_filter($question['options'], fn($o) => $o['is_correct']);
                    $correctId = !empty($correctOption) ? reset($correctOption)['id'] : null;
                    $isCorrect = $userAnswer == $correctId;
                    break;

                case 'multiple_choice':
                    $correctIds = array_column(array_filter($question['options'], fn($o) => $o['is_correct']), 'id');
                    $userAnswerIds = is_array($userAnswer) ? $userAnswer : [];
                    sort($correctIds);
                    sort($userAnswerIds);
                    $isCorrect = $correctIds === $userAnswerIds;
                    break;
            }

            if ($isCorrect) {
                $earnedPoints += $question['points'];
            }

            $results[$question['id']] = [
                'correct' => $isCorrect,
                'user_answer' => $userAnswer,
            ];
        }

        $score = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100, 2) : 0;
        $passed = $score >= $quiz['passing_score'];

        // Calculate time spent
        $startTime = strtotime($attempt['started_at']);
        $timeSpent = time() - $startTime;

        // Update attempt
        db_update('user_quiz_attempts', [
            'score' => $score,
            'passed' => $passed ? 1 : 0,
            'answers' => json_encode($answers),
            'completed_at' => date('Y-m-d H:i:s'),
            'time_spent_seconds' => $timeSpent,
        ], 'id = ?', [$attemptId]);

        return [
            'success' => true,
            'score' => $score,
            'passed' => $passed,
            'passing_score' => $quiz['passing_score'],
            'earned_points' => $earnedPoints,
            'total_points' => $totalPoints,
            'results' => $quiz['show_correct_answers'] ? $results : null,
            'time_spent' => $timeSpent,
        ];
    }

    /**
     * Get user's attempts for a quiz
     */
    public function getUserAttempts(int $quizId, int $userId): array
    {
        return db_fetch_all(
            "SELECT * FROM user_quiz_attempts WHERE quiz_id = ? AND user_id = ? ORDER BY started_at DESC",
            [$quizId, $userId]
        );
    }

    /**
     * Create a quiz
     */
    public function createQuiz(int $activityId, array $data): int
    {
        $tenantId = App::tenantId();
        $userId = App::user()['id'];

        return db_insert('quizzes', [
            'activity_id' => $activityId,
            'tenant_id' => $tenantId,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'passing_score' => $data['passing_score'] ?? 70,
            'time_limit_minutes' => $data['time_limit_minutes'] ?? null,
            'shuffle_questions' => $data['shuffle_questions'] ?? 0,
            'shuffle_options' => $data['shuffle_options'] ?? 0,
            'show_correct_answers' => $data['show_correct_answers'] ?? 1,
            'max_attempts' => $data['max_attempts'] ?? null,
            'created_by' => $userId,
        ]);
    }

    /**
     * Add a question to a quiz
     */
    public function addQuestion(int $quizId, array $data): int
    {
        $questionId = db_insert('quiz_questions', [
            'quiz_id' => $quizId,
            'question_text' => $data['question_text'],
            'question_type' => $data['question_type'] ?? 'single_choice',
            'points' => $data['points'] ?? 1,
            'order_position' => $data['order_position'] ?? 0,
            'explanation' => $data['explanation'] ?? null,
        ]);

        // Add options
        if (!empty($data['options'])) {
            foreach ($data['options'] as $index => $option) {
                db_insert('quiz_options', [
                    'question_id' => $questionId,
                    'option_text' => $option['text'],
                    'is_correct' => $option['is_correct'] ?? 0,
                    'order_position' => $index,
                ]);
            }
        }

        return $questionId;
    }
}
