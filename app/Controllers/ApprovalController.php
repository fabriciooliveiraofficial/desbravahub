<?php
/**
 * Approval Controller
 * 
 * Admin interface for approving/rejecting step submissions.
 */

namespace App\Controllers;

use App\Core\App;
use App\Core\View;
use App\Services\LearningNotificationService;

class ApprovalController
{
    private function requireAdmin(): void
    {
        $user = App::user();
        $role = $user['role_name'] ?? '';

        if (!in_array($role, ['admin', 'director', 'counselor'])) {
            error_log("ApprovalController::requireAdmin - Access Denied: User " . ($user['id'] ?? 'unknown') . " with role $role tried to access approvals.");
            header('HTTP/1.0 403 Forbidden');
            echo 'Acesso negado';
            exit;
        }
    }

    /**
     * List pending evaluation queue (Grouped by Student + Program)
     */
    public function index(): void
    {
        $this->requireAdmin();

        $tenant = App::tenant();
        $user = App::user();

        // Get pending submission GROUPS
        // Each entry represents a student who has items to be reviewed in a specific program
        $pendingQueue = db_fetch_all("
            SELECT 
                upp.id as progress_id,
                u.id as user_id,
                u.name as user_name,
                u.avatar_url,
                ut.name as unit_name,
                p.id as program_id,
                p.name as program_name,
                p.icon as program_icon,
                p.type as program_type,
                COUNT(usr.id) as pending_count,
                MAX(usr.submitted_at) as last_submission,
                upp.progress_percent,
                (SELECT COUNT(*) FROM program_steps ps WHERE ps.version_id = upp.version_id) as total_steps
            FROM user_program_progress upp
            JOIN users u ON upp.user_id = u.id
            LEFT JOIN units ut ON u.unit_id = ut.id
            JOIN learning_programs p ON upp.program_id = p.id
            JOIN user_step_responses usr ON usr.progress_id = upp.id
            WHERE upp.tenant_id = ? AND usr.status = 'submitted'
            GROUP BY upp.id
            ORDER BY last_submission ASC
        ", [$tenant['id']]);

        // Get recent activity log (last 15 items)
        $recentApprovals = db_fetch_all("
            SELECT 
                al.*,
                u.name as user_name,
                reviewer.name as reviewer_name
            FROM approval_logs al
            LEFT JOIN user_step_responses usr ON al.response_id = usr.id
            LEFT JOIN user_program_progress upp ON usr.progress_id = upp.id
            LEFT JOIN users u ON upp.user_id = u.id
            LEFT JOIN users reviewer ON al.performed_by = reviewer.id
            WHERE al.tenant_id = ? AND al.action IN ('approved', 'rejected')
            ORDER BY al.created_at DESC
            LIMIT 15
        ", [$tenant['id']]);

        View::render('admin/approvals/index', [
            'tenant' => $tenant,
            'user' => $user,
            'pendingQueue' => $pendingQueue,
            'recentApprovals' => $recentApprovals,
            'pageTitle' => 'Centro de Avaliação',
            'pageIcon' => 'grading'
        ]);
    }

    /**
     * Focused review of a student's program submission
     */
    public function review(array $params): void
    {
        $this->requireAdmin();
        $tenant = App::tenant();
        $user = App::user();
        $progressId = (int) ($params['id'] ?? 0);

        // Get progress info with user and program details
        $progress = db_fetch_one("
            SELECT upp.*, u.name as user_name, u.avatar_url, ut.name as unit_name,
                   p.name as program_name, p.icon as program_icon, p.type as program_type
            FROM user_program_progress upp
            JOIN users u ON upp.user_id = u.id
            LEFT JOIN units ut ON u.unit_id = ut.id
            JOIN learning_programs p ON upp.program_id = p.id
            WHERE upp.id = ? AND upp.tenant_id = ?
        ", [$progressId, $tenant['id']]);

        if (!$progress) {
            header('Location: ' . base_url($tenant['slug'] . '/admin/aprovacoes'));
            exit;
        }

        // Get all steps with their responses
        $steps = db_fetch_all("
            SELECT ps.*, usr.id as response_id, usr.status as response_status, 
                   usr.response_text, usr.response_file, usr.response_url, 
                   usr.submitted_at, usr.feedback
            FROM program_steps ps
            LEFT JOIN user_step_responses usr ON ps.id = usr.step_id AND usr.progress_id = ?
            WHERE ps.version_id = ?
            ORDER BY ps.sort_order ASC
        ", [$progressId, $progress['version_id']]);

        // Enhance steps with question details to map numeric answers (e.g. "0" -> "First Option")
    foreach ($steps as &$step) {
        $questions = db_fetch_all("SELECT * FROM program_questions WHERE step_id = ? ORDER BY sort_order", [$step['id']]);
        $step['structured_content'] = []; // Always initialize for the view
        
        if (!empty($questions) && !empty($step['response_text'])) {
            $decoded = json_decode($step['response_text'], true);
            
            if (is_array($decoded)) {
                foreach ($questions as $q) {
                    $qId = $q['id'];
                    $answer = $decoded[$qId] ?? ($decoded[$questions[0]['id'] ?? -1] ?? null);
                    
                    if ($answer !== null) {
                        $label = $q['question_text'] ?: "Resposta";
                        $readableAnswer = $answer;
                        
                        // Map options if choice type
                        if (in_array($q['type'], ['single_choice', 'multiple_choice', 'select', 'true_false'])) {
                            $options = json_decode($q['options'] ?? '[]', true);
                            
                            if (is_numeric($answer) && isset($options[(int)$answer])) {
                                $opt = $options[(int)$answer];
                                $readableAnswer = is_string($opt) ? $opt : ($opt['text'] ?? $opt['label'] ?? $answer);
                            } elseif ($q['type'] === 'true_false') {
                                if ($answer === '0' || $answer === 0) $readableAnswer = 'Falso';
                                elseif ($answer === '1' || $answer === 1) $readableAnswer = 'Verdadeiro';
                            } elseif (is_array($answer)) {
                                $mappedArr = [];
                                foreach ($answer as $a) {
                                    if (is_numeric($a) && isset($options[(int)$a])) {
                                        $opt = $options[(int)$a];
                                        $mappedArr[] = is_string($opt) ? $opt : ($opt['text'] ?? $opt['label'] ?? $a);
                                    } else {
                                        $mappedArr[] = $a;
                                    }
                                }
                                $readableAnswer = implode(", ", $mappedArr);
                            }
                        }
                        
                        $step['structured_content'][] = [
                            'question' => $label,
                            'answer' => $readableAnswer,
                            'type' => $q['type']
                        ];
                    }
                }
            } else {
                // Fallback for non-JSON simple values (Legacy)
                foreach ($questions as $q) {
                    $readableAnswer = $step['response_text'];
                    if (in_array($q['type'], ['single_choice', 'multiple_choice', 'select', 'true_false'])) {
                        $options = json_decode($q['options'] ?? '[]', true);
                        if (is_numeric($step['response_text']) && isset($options[(int)$step['response_text']])) {
                            $opt = $options[(int)$step['response_text']];
                            $readableAnswer = is_string($opt) ? $opt : ($opt['text'] ?? $opt['label'] ?? $step['response_text']);
                        } elseif ($q['type'] === 'true_false') {
                            if ($step['response_text'] === '0') $readableAnswer = 'Falso';
                            if ($step['response_text'] === '1') $readableAnswer = 'Verdadeiro';
                        }
                    }
                    $step['structured_content'][] = [
                        'question' => $q['question_text'] ?: "Resposta",
                        'answer' => $readableAnswer,
                        'type' => $q['type']
                    ];
                    break; 
                }
            }
        }
    }

        require BASE_PATH . '/views/admin/approvals/review.php';
    }

    /**
     * Approve a submission (individual step)
     */
    public function approve(array $params): void
    {
        $this->requireAdmin();

        // Buffer output to prevent PHP warnings from corrupting JSON response
        ob_start();

        $tenant = App::tenant();
        $user = App::user();
        $responseId = (int) ($params['id'] ?? 0);

        $input = json_decode(file_get_contents('php://input'), true);
        $itemEvaluations = $input['item_evaluations'] ?? null;
        
        // If we have granular evaluations, we store them as JSON in the feedback field (prefixing with [GRANULAR])
        // or we could use another field, but for Phase 92 we'll keep it simple and efficient.
        $feedbackBlob = $itemEvaluations ? '[ITEM_EVAL]' . json_encode($itemEvaluations) : null;

        try {
            db_begin();

            // Update response status
            db_update('user_step_responses', [
                'status' => 'approved',
                'feedback' => $feedbackBlob,
                'reviewed_by' => $user['id'],
                'reviewed_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$responseId]);

            // Log approval
            db_insert('approval_logs', [
                'tenant_id' => $tenant['id'],
                'response_id' => $responseId,
                'action' => 'approved',
                'performed_by' => $user['id']
            ]);

            // Update progress & send notification (wrapped in try-catch)
            try {
                $response = db_fetch_one("SELECT usr.progress_id, usr.step_id, upp.user_id, upp.program_id 
                    FROM user_step_responses usr
                    JOIN user_program_progress upp ON usr.progress_id = upp.id
                    WHERE usr.id = ?", [$responseId]);

                if ($response) {
                    $this->updateProgressPercent($response['progress_id'], $tenant['slug']);

                    // Send notification
                    $step = db_fetch_one("SELECT * FROM program_steps WHERE id = ?", [$response['step_id']]);
                    $program = db_fetch_one("SELECT * FROM learning_programs WHERE id = ?", [$response['program_id']]);
                    if ($step && $program) {
                        LearningNotificationService::stepApproved($tenant['id'], $response['user_id'], $step, $program, $tenant['slug']);
                    }
                }
            } catch (\Exception $notifError) {
                error_log("Notification error during approval: " . $notifError->getMessage());
            }

            db_commit();

            ob_end_clean();
            $this->json(['success' => true, 'message' => 'Aprovado!']);

        } catch (\Exception $e) {
            db_rollback();
            ob_end_clean();
            $this->json(['error' => 'Erro: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Reject a submission with feedback
     */
    public function reject(array $params): void
    {
        $this->requireAdmin();

        // Buffer output to prevent PHP warnings from corrupting JSON response
        ob_start();

        $tenant = App::tenant();
        $user = App::user();
        $responseId = (int) ($params['id'] ?? 0);

        $input = json_decode(file_get_contents('php://input'), true);
        $feedback = $input['feedback'] ?? '';
        $itemEvaluations = $input['item_evaluations'] ?? null;

        // Combine overall feedback with granular evaluations
        $feedbackBlob = $itemEvaluations ? '[ITEM_EVAL]' . json_encode([
            'overall' => $feedback,
            'items' => $itemEvaluations
        ]) : $feedback;

        try {
            db_begin();

            // Update response status
            db_update('user_step_responses', [
                'status' => 'rejected',
                'feedback' => $feedbackBlob,
                'reviewed_by' => $user['id'],
                'reviewed_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$responseId]);

            // Log rejection
            db_insert('approval_logs', [
                'tenant_id' => $tenant['id'],
                'response_id' => $responseId,
                'action' => 'rejected',
                'performed_by' => $user['id'],
                'notes' => $feedback
            ]);

            // Recalculate progress & revert program status
            try {
                $response = db_fetch_one("SELECT usr.step_id, usr.progress_id, upp.user_id, upp.program_id, upp.status as program_status
                    FROM user_step_responses usr
                    JOIN user_program_progress upp ON usr.progress_id = upp.id
                    WHERE usr.id = ?", [$responseId]);

                if ($response) {
                    // Bug #1: Recalculate progress_percent after rejection
                    $this->updateProgressPercent($response['progress_id'], $tenant['slug']);

                    // Bug #2: Revert program status to in_progress if it was submitted/completed
                    if (in_array($response['program_status'], ['submitted', 'completed'])) {
                        db_update('user_program_progress', [
                            'status' => 'in_progress',
                            'completed_at' => null
                        ], 'id = ?', [$response['progress_id']]);
                    }

                    $step = db_fetch_one("SELECT * FROM program_steps WHERE id = ?", [$response['step_id']]);
                    $program = db_fetch_one("SELECT * FROM learning_programs WHERE id = ?", [$response['program_id']]);
                    
                    if ($step && $program) {
                        $rejectedCount = 0;
                        if ($itemEvaluations && is_array($itemEvaluations)) {
                            foreach ($itemEvaluations as $eval) {
                                if (($eval['status'] ?? '') === 'rejected') $rejectedCount++;
                            }
                        }

                        $notificationMsg = $feedback;
                        if ($rejectedCount > 0) {
                            $suffix = "({$rejectedCount} " . ($rejectedCount > 1 ? "itens precisam" : "item precisa") . " de correção)";
                            $notificationMsg = $notificationMsg ? $notificationMsg . " | " . $suffix : $suffix;
                        }

                        LearningNotificationService::stepRejected($tenant['id'], $response['user_id'], $step, $program, $notificationMsg, $tenant['slug']);
                    }
                }
            } catch (\Exception $notifError) {
                error_log("Notification error during rejection: " . $notifError->getMessage());
            }

            db_commit();

            // Discard any buffered output (PHP warnings, etc.)
            ob_end_clean();

            $this->json(['success' => true, 'message' => 'Rejeitado com feedback']);

        } catch (\Exception $e) {
            db_rollback();
            ob_end_clean();
            $this->json(['error' => 'Erro: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Bulk approve all pending items in a program submission
     */
    public function bulkApproveProgram(array $params): void
    {
        $this->requireAdmin();

        $tenant = App::tenant();
        $user = App::user();
        $progressId = (int) ($params['id'] ?? 0);

        try {
            db_begin();

            // Get all pending responses for this progress record
            $pending = db_fetch_all("SELECT id FROM user_step_responses WHERE progress_id = ? AND status = 'submitted'", [$progressId]);

            foreach ($pending as $item) {
                db_update('user_step_responses', [
                    'status' => 'approved',
                    'reviewed_by' => $user['id'],
                    'reviewed_at' => date('Y-m-d H:i:s')
                ], 'id = ?', [(int) $item['id']]);

                db_insert('approval_logs', [
                    'tenant_id' => $tenant['id'],
                    'response_id' => (int) $item['id'],
                    'action' => 'approved',
                    'performed_by' => $user['id']
                ]);
            }

            if (!empty($pending)) {
                $this->updateProgressPercent($progressId, $tenant['slug']);
            }

            db_commit();

            $this->json(['success' => true, 'message' => count($pending) . ' itens aprovados!']);

        } catch (\Exception $e) {
            db_rollback();
            $this->json(['error' => 'Erro: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update progress percentage
     */
    private function updateProgressPercent(int $progressId, string $tenantSlug = ''): void
    {
        $progress = db_fetch_one("SELECT * FROM user_program_progress WHERE id = ?", [$progressId]);
        if (!$progress)
            return;

        $totalSteps = db_fetch_column("
            SELECT COUNT(*) FROM program_steps 
            WHERE version_id = ? AND is_required = 1
        ", [$progress['version_id']]);

        $approvedSteps = db_fetch_column("
            SELECT COUNT(DISTINCT usr.step_id) 
            FROM user_step_responses usr
            JOIN program_steps ps ON usr.step_id = ps.id
            WHERE usr.progress_id = ? AND usr.status = 'approved' AND ps.is_required = 1
        ", [$progressId]);

        $percent = $totalSteps > 0 ? round(($approvedSteps / $totalSteps) * 100) : 0;

        db_update('user_program_progress', ['progress_percent' => $percent], 'id = ?', [$progressId]);

        if ($percent >= 100 && $progress['status'] !== 'completed') {
            db_update('user_program_progress', [
                'status' => 'completed',
                'completed_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$progressId]);

            // Award XP
            $program = db_fetch_one("SELECT * FROM learning_programs WHERE id = ?", [$progress['program_id']]);
            if ($program && $program['xp_reward'] > 0) {
                $progressionService = new \App\Services\ProgressionService();
                $progressionService->addXp($progress['user_id'], $program['xp_reward'], 'program', $program['id']);
            }

            // Send completion notification
            if ($tenantSlug) {
                if ($program) {
                    LearningNotificationService::programCompleted($progress['tenant_id'], $progress['user_id'], $program, $tenantSlug);
                }
            }
        }
    }

    private function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}
