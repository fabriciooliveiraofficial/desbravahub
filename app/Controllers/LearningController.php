<?php
/**
 * Learning Controller
 * 
 * Handles the pathfinder learning experience:
 * - View assigned programs
 * - Complete steps via modals
 * - Track progress
 * - Submit answers/proofs
 */

namespace App\Controllers;

use App\Core\View;
use App\Core\App;

class LearningController
{
    /**
     * List assigned programs for pathfinder
     */
    public function index(): void
    {
        $tenant = App::tenant();
        $user = App::user();

        // Get user's assigned programs with progress
        // We calculate 'answered_count' to know if the user can submit the program (all steps have at least a draft)
        $programs = db_fetch_all("
            SELECT p.*, c.name as category_name, c.color as category_color, c.icon as category_icon, c.type as category_type,
                   up.id as progress_id, up.status as user_status, up.progress_percent, up.started_at, up.completed_at,
                   pv.id as version_id, pv.version_number,
                   (SELECT COUNT(*) FROM program_steps ps WHERE ps.version_id = pv.id) as total_steps,
                   (SELECT COUNT(id) FROM user_step_responses usr WHERE usr.progress_id = up.id) as answered_steps,
                   (SELECT COUNT(id) FROM user_step_responses usr WHERE usr.progress_id = up.id AND usr.status = 'approved') as approved_steps,
                   (SELECT COUNT(id) FROM user_step_responses usr WHERE usr.progress_id = up.id AND usr.status = 'rejected') as rejected_steps
            FROM user_program_progress up
            JOIN learning_programs p ON up.program_id = p.id
            JOIN program_versions pv ON up.version_id = pv.id
            LEFT JOIN learning_categories c ON p.category_id = c.id
            WHERE up.tenant_id = ? AND up.user_id = ?
            ORDER BY up.status, p.name
        ", [$tenant['id'], $user['id']]);

        // De-duplicate programs (keep latest progress_id for each program_id, prioritizing status)
        $uniquePrograms = [];
        $statusPriority = [
            'approved' => 3,
            'completed' => 3,
            'submitted' => 2,
            'rejected' => 1.5,
            'in_progress' => 1,
            'not_started' => 0
        ];

        foreach ($programs as $p) {
            $pid = $p['id'];
            $currentStatus = strtolower(trim($p['user_status'] ?? 'not_started'));
            
            if (!isset($uniquePrograms[$pid])) {
                $uniquePrograms[$pid] = $p;
                continue;
            }

            $existingStatus = strtolower(trim($uniquePrograms[$pid]['user_status'] ?? 'not_started'));
            $pCurr = $statusPriority[$currentStatus] ?? 0;
            $pExis = $statusPriority[$existingStatus] ?? 0;

            // Prioritize higher status, then higher ID
            if ($pCurr > $pExis || ($pCurr === $pExis && $p['progress_id'] > $uniquePrograms[$pid]['progress_id'])) {
                $uniquePrograms[$pid] = $p;
            }
        }
        $programs = array_values($uniquePrograms);

        // Group by status
        $grouped = [
            'in_progress' => [],
            'not_started' => [],
            'completed' => []
        ];

        foreach ($programs as $prog) {
            $status = $prog['user_status'] ?? 'not_started';
            if (isset($grouped[$status])) {
                $grouped[$status][] = $prog;
            }
        }

        View::render('pathfinder/programs/index', [
            'tenant' => $tenant,
            'user' => $user,
            'programs' => $programs,
            'grouped' => $grouped
        ], 'member');
    }

    /**
     * View a single program with steps
     */
    public function show(array $params): void
    {
        $tenant = App::tenant();
        $user = App::user();
        $programId = (int) ($params['id'] ?? 0);

        // Get program with user progress
        // Logic: ID can be Program ID (admin/direct) OR Assignment ID (dashboard link)
        
        // 1. Try to find by Program ID (Resource)
        $program = db_fetch_one("
            SELECT p.*, c.name as category_name, c.color as category_color, c.icon as category_icon,
                   up.id as progress_id, up.status as user_status, up.progress_percent, 
                   up.version_id, up.started_at
            FROM learning_programs p
            LEFT JOIN learning_categories c ON p.category_id = c.id
            LEFT JOIN user_program_progress up ON up.program_id = p.id AND up.user_id = ?
            WHERE p.id = ? AND p.tenant_id = ?
        ", [$user['id'], $programId, $tenant['id']]);

        // 2. If not found, try to find by Assignment ID (up.id)
        if (!$program) {
             $program = db_fetch_one("
                SELECT p.*, c.name as category_name, c.color as category_color, c.icon as category_icon,
                       up.id as progress_id, up.status as user_status, up.progress_percent, 
                       up.version_id, up.started_at
                FROM user_program_progress up
                JOIN learning_programs p ON up.program_id = p.id
                LEFT JOIN learning_categories c ON p.category_id = c.id
                WHERE up.id = ? AND up.user_id = ? AND up.tenant_id = ?
            ", [$programId, $user['id'], $tenant['id']]);
        }

        if (!$program) {
            header('HTTP/1.0 404 Not Found');
            echo 'Programa não encontrado';
            exit;
        }

        // Check if user has access (must be assigned)
        if (!$program['progress_id']) {
            error_log("LearningController::show - Access Denied: User " . $user['id'] . " not assigned to Program " . $programId);
            header('HTTP/1.0 403 Forbidden');
            echo 'Você não tem acesso a este programa';
            exit;
        }

        // Get version (user may be on older version)
        $versionId = $program['version_id'];

        // Get steps for this version
        $steps = db_fetch_all("
            SELECT s.*, 
                   (SELECT COUNT(*) FROM program_questions WHERE step_id = s.id) as question_count
            FROM program_steps s
            WHERE s.version_id = ?
            ORDER BY s.sort_order
        ", [$versionId]);

        // Get user responses for each step
        foreach ($steps as &$step) {
            $step['response'] = db_fetch_one("
                SELECT * FROM user_step_responses 
                WHERE progress_id = ? AND step_id = ?
            ", [$program['progress_id'], $step['id']]);

            // Get questions for this step
            $step['questions'] = db_fetch_all("
                SELECT * FROM program_questions 
                WHERE step_id = ? 
                ORDER BY sort_order
            ", [$step['id']]);
        }

        // Render within member layout (HUD Theme)
        View::render('pathfinder/programs/show', [
            'tenant' => $tenant,
            'user' => $user,
            'program' => $program,
            'steps' => $steps,
            'versionId' => $versionId
        ], 'member');
    }

    /**
     * Get step modal content (AJAX)
     */
    public function stepModal(array $params): void
    {
        $tenant = App::tenant();
        $user = App::user();
        $stepId = (int) ($params['step_id'] ?? 0);

        // Get step with questions
        $step = db_fetch_one("
            SELECT s.*, pv.program_id, p.is_outdoor
            FROM program_steps s
            JOIN program_versions pv ON s.version_id = pv.id
            JOIN learning_programs p ON pv.program_id = p.id
            WHERE s.id = ?
        ", [$stepId]);

        if (!$step) {
            $this->json(['error' => 'Requisito não encontrado'], 404);
            return;
        }

        // Verify user has access
        $progress = db_fetch_one("
            SELECT * FROM user_program_progress 
            WHERE program_id = ? AND user_id = ? AND tenant_id = ?
        ", [$step['program_id'], $user['id'], $tenant['id']]);

        if (!$progress) {
            $this->json(['error' => 'Acesso negado'], 403);
            return;
        }

        // Get questions
        $questions = db_fetch_all("
            SELECT * FROM program_questions WHERE step_id = ? ORDER BY sort_order
        ", [$stepId]);

        // Get existing response
        $response = db_fetch_one("
            SELECT * FROM user_step_responses 
            WHERE progress_id = ? AND step_id = ?
        ", [$progress['id'], $stepId]);

        // Render modal HTML
        ob_start();
        require BASE_PATH . '/views/pathfinder/programs/_step_modal.php';
        $html = ob_get_clean();

        $this->json([
            'success' => true,
            'html' => $html,
            'step' => $step,
            'status' => $response['status'] ?? 'not_started'
        ]);
    }

    /**
     * Submit step response (AJAX)
     */
    public function submitStep(array $params): void
    {
        // Buffer output to prevent PHP warnings from corrupting JSON response
        ob_start();

        try {
            $tenant = App::tenant();
            $user = App::user();
            $stepId = (int) ($params['step_id'] ?? 0);

            // Get step
            $step = db_fetch_one("
                SELECT s.*, pv.program_id
                FROM program_steps s
                JOIN program_versions pv ON s.version_id = pv.id
                WHERE s.id = ?
            ", [$stepId]);

            if (!$step) {
                ob_end_clean();
                $this->json(['error' => 'Requisito não encontrado'], 404);
                return;
            }

            // Get user progress
            $progress = db_fetch_one("
                SELECT * FROM user_program_progress 
                WHERE program_id = ? AND user_id = ? AND tenant_id = ?
            ", [$step['program_id'], $user['id'], $tenant['id']]);

            if (!$progress) {
                ob_end_clean();
                $this->json(['error' => 'Acesso negado'], 403);
                return;
            }

            // Collect response data
            $responseText = $_POST['response_text'] ?? null;

            // Handle multi-question responses (JSON)
            if (isset($_POST['answers']) && is_array($_POST['answers'])) {
                $responseText = json_encode($_POST['answers'], JSON_UNESCAPED_UNICODE);
                if ($responseText === false) {
                    ob_end_clean();
                    $this->json(['error' => 'Erro ao processar respostas (JSON Inválido)'], 400);
                    return;
                }
            }
            // Fallback: Handle legacy choice-based responses
            elseif (isset($_POST['response_choice'])) {
                $responseText = $_POST['response_choice'];
            } elseif (isset($_POST['response_choices']) && is_array($_POST['response_choices'])) {
                $responseText = json_encode($_POST['response_choices']);
            }

            $responseUrl = $_POST['response_url'] ?? null;
            $status = $_POST['status'] ?? 'submitted';
            
            if (!in_array($status, ['draft', 'submitted'])) {
                $status = 'submitted';
            }

            $responseFile = null;

            // Handle file upload
            if (isset($_FILES['response_file']) && $_FILES['response_file']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = BASE_PATH . '/public/uploads/responses/' . $tenant['id'] . '/' . $user['id'] . '/';
                if (!is_dir($uploadDir)) {
                    @mkdir($uploadDir, 0755, true);
                }
                $filename = time() . '_' . basename($_FILES['response_file']['name']);
                move_uploaded_file($_FILES['response_file']['tmp_name'], $uploadDir . $filename);
                $responseFile = '/uploads/responses/' . $tenant['id'] . '/' . $user['id'] . '/' . $filename;
            }

            // Check if response exists
            $existing = db_fetch_one("
                SELECT id FROM user_step_responses 
                WHERE progress_id = ? AND step_id = ?
            ", [$progress['id'], $stepId]);
            
            if ($existing) {
                // Update existing
                $oldFile = null;
                if (!$responseFile) {
                    $oldData = db_fetch_one("SELECT response_file FROM user_step_responses WHERE id = ?", [$existing['id']]);
                    if ($oldData) {
                        $oldFile = $oldData['response_file'] ?? null;
                    }
                }

                db_update('user_step_responses', [
                    'response_text' => $responseText,
                    'response_url' => $responseUrl,
                    'response_file' => $responseFile ?: $oldFile,
                    'status' => $status,
                    'submitted_at' => date('Y-m-d H:i:s')
                ], 'id = ?', [$existing['id']]);
            } else {
                // Create new
                db_insert('user_step_responses', [
                    'progress_id' => $progress['id'],
                    'step_id' => $stepId,
                    'response_text' => $responseText,
                    'response_url' => $responseUrl,
                    'response_file' => $responseFile,
                    'status' => $status,
                    'submitted_at' => date('Y-m-d H:i:s')
                ]);
            }

            // Update progress status if first submission
            if ($progress['status'] === 'not_started') {
                db_update('user_program_progress', [
                    'status' => 'in_progress',
                    'started_at' => date('Y-m-d H:i:s')
                ], 'id = ?', [$progress['id']]);
            }

            // Calculate and update progress percentage
            $this->updateProgressPercent($progress['id']);

            // Log to approval_logs (only if submitted)
            if ($status === 'submitted') {
                $responseId = $existing ? (int)$existing['id'] : (int)db()->lastInsertId();
                db_insert('approval_logs', [
                    'tenant_id' => $tenant['id'],
                    'response_id' => $responseId,
                    'action' => 'submitted',
                    'performed_by' => $user['id']
                ]);

                // Notify admins/instructors
                $program = db_fetch_one("SELECT * FROM learning_programs WHERE id = ?", [$step['program_id']]);
                if ($program) {
                    \App\Services\LearningNotificationService::stepSubmitted($tenant['id'], $user, $step, $program, $tenant['slug']);
                }
            }

            $message = $status === 'draft' ? 'Rascunho salvo com sucesso!' : 'Resposta enviada! Aguarde aprovação.';

            ob_end_clean();
            $this->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Throwable $e) {
            ob_end_clean();
            error_log("LearningController::submitStep - CRITICAL: " . $e->getMessage());
            error_log("Trace: " . $e->getTraceAsString());
            $this->json(['error' => 'Erro no servidor: ' . $e->getMessage()], 400);
        }
    }

    /**
     * Update progress percentage
     */
    private function updateProgressPercent(int $progressId): void
    {
        $progress = db_fetch_one("SELECT * FROM user_program_progress WHERE id = ?", [$progressId]);
        if (!$progress)
            return;

        // Count required steps
        $totalStepsRaw = db_fetch_one("
            SELECT COUNT(*) as count FROM program_steps 
            WHERE version_id = ? AND is_required = 1
        ", [$progress['version_id']]);
        $totalSteps = $totalStepsRaw['count'] ?? 0;

        // Count approved steps
        $approvedStepsRaw = db_fetch_one("
            SELECT COUNT(DISTINCT usr.step_id) as count
            FROM user_step_responses usr
            JOIN program_steps ps ON usr.step_id = ps.id
            WHERE usr.progress_id = ? AND usr.status = 'approved' AND ps.is_required = 1
        ", [$progressId]);
        $approvedSteps = $approvedStepsRaw['count'] ?? 0;

        $percent = $totalSteps > 0 ? round(($approvedSteps / $totalSteps) * 100) : 0;

        db_update('user_program_progress', ['progress_percent' => $percent], 'id = ?', [$progressId]);

        // Check completion
        if ($percent >= 100) {
            // Only finalize if not already completed
            if ($progress['status'] !== 'completed') {
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
            }
        }
    }

    private function json(array $data, int $code = 200): void
    {
        if (ob_get_length()) ob_clean();
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Submit all drafts for a program at once
     */
    public function submitProgram(array $params): void
    {
        // Prevent partial output
        if (ob_get_level()) ob_end_clean();
        ob_start();

        try {
            $tenant = App::tenant();
            $user = App::user();
            $progressId = (int) ($params['id'] ?? 0);

            // Get progress record specifically by progress_id
            $progress = db_fetch_one("
                SELECT id, program_id, status 
                FROM user_program_progress 
                WHERE id = ? AND user_id = ? AND tenant_id = ?
            ", [$progressId, $user['id'], $tenant['id']]);

            if (!$progress) {
                // Return clean JSON
                ob_clean();
                $this->json(['error' => 'Programa não encontrado'], 404);
                return;
            }

            db_begin();

            // 1. Update all drafts to submitted
            db_query("
                UPDATE user_step_responses 
                SET status = 'submitted', submitted_at = NOW() 
                WHERE progress_id = ? AND status = 'draft'
            ", [$progress['id']]);
            
            // 2. Ensure any that missed 'submitted_at' get it
            db_query("
                UPDATE user_step_responses 
                SET submitted_at = NOW() 
                WHERE progress_id = ? AND status = 'submitted' AND submitted_at IS NULL
            ", [$progress['id']]);

            // 3. Update program status
            db_update('user_program_progress', [
                'status' => 'submitted',
                'submitted_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$progress['id']]);

            // SELF-HEALING: Verify if 'submitted' was actually saved. 
            // If not, it means the column is an ENUM that doesn't support it.
            $verify = db_fetch_one("SELECT status FROM user_program_progress WHERE id = ?", [$progress['id']]);
            if ($verify && $verify['status'] !== 'submitted') {
                // Fix schema dynamically
                try {
                    // Force commit previous attempts
                    db_commit(); 
                    
                    // ALTER requires no active transaction in some DBs, or causes implicit commit
                    db_query("ALTER TABLE user_program_progress MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'not_started'");
                    
                    // Start new transaction for the retry
                    db_begin();
                    
                    // Retry update
                    db_update('user_program_progress', [
                        'status' => 'submitted',
                        'submitted_at' => date('Y-m-d H:i:s')
                    ], 'id = ?', [$progress['id']]);
                } catch (\Throwable $ex) {
                    error_log("Failed to auto-fix schema: " . $ex->getMessage());
                }
            }

            // 4. Log activity
            $anyResponse = db_fetch_one("SELECT id FROM user_step_responses WHERE progress_id = ? LIMIT 1", [$progress['id']]);
            if ($anyResponse) {
                db_insert('approval_logs', [
                    'tenant_id' => $tenant['id'],
                    'response_id' => (int) $anyResponse['id'],
                    'action' => 'submitted',
                    'performed_by' => $user['id'],
                    'notes' => "Enviou todas as respostas do programa #{$programId}"
                ]);
            }

            if (db_in_transaction()) db_commit();

            ob_clean(); // Clear buffer
            $this->json([
                'success' => true, 
                'message' => 'Respostas enviadas com sucesso! Aguarde a avaliação.'
            ]);

        } catch (\Throwable $e) {
            if (db_in_transaction()) db_rollback();
            
            error_log("LearningController::submitProgram - " . $e->getMessage());
            
            ob_clean(); // Ensure no HTML error leaks
            $this->json(['error' => 'Erro ao enviar respostas: ' . $e->getMessage()], 500);
        }
    }
}
