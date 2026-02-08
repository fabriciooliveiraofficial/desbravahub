<?php
/**
 * Program Controller
 * 
 * Manages learning programs (specialties & classes) with versioning.
 */

namespace App\Controllers;

use App\Core\App;
use App\Core\View;
use App\Services\LearningNotificationService;

class ProgramController
{
    /**
     * Require admin/director role
     */
    protected function requireAdmin(): void
    {
        $user = App::user();
        $role = $user['role_name'] ?? '';

        if (!in_array($role, ['admin', 'director', 'counselor'])) {
            error_log("ProgramController::requireAdmin - Access Denied: User " . ($user['id'] ?? 'unknown') . " with role $role tried to access programs.");
            header('HTTP/1.0 403 Forbidden');
            echo 'Acesso negado';
            exit;
        }
    }

    /**
     * Get categories (with auto-seeding)
     */
    private function getCategories(int $tenantId): array
    {
        $sql = "SELECT * FROM learning_categories WHERE tenant_id = ? AND status = 'active' ORDER BY name";
        $categories = db_fetch_all($sql, [$tenantId]);

        if (empty($categories)) {
            $this->seedCategories($tenantId);
            $categories = db_fetch_all($sql, [$tenantId]);
        }

        return $categories;
    }

    /**
     * Seed categories from legacy service
     */
    private function seedCategories(int $tenantId): void
    {
        // 1. Ensure schema is correct (Add 'type' column if missing)
        try {
            $columns = db_fetch_all("SHOW COLUMNS FROM learning_categories LIKE 'type'");
            if (empty($columns)) {
                // Determine previous column to place 'type' after
                // Assuming 'icon' exists. If not, just ADD.
                db_query("ALTER TABLE learning_categories ADD COLUMN type ENUM('specialty', 'class', 'both') DEFAULT 'specialty'");
            }
        } catch (\Exception $e) {
            // Check if error is "Duplicate column" just in case, otherwise log?
            // If it fails, insertion might fail next, but we try.
        }

        // Import Legacy
        $legacyCats = \App\Services\SpecialtyService::getCategories();
        foreach ($legacyCats as $c) {
            $exists = db_fetch_one("SELECT id FROM learning_categories WHERE tenant_id = ? AND name = ?", [$tenantId, $c['name']]);
            if (!$exists) {
                db_insert('learning_categories', [
                    'tenant_id' => $tenantId,
                    'name' => $c['name'],
                    'color' => $c['color'],
                    'icon' => $c['icon'] ?? '📁',
                    'type' => 'specialty',
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        }

        // Import Classes
        $classes = [
            ['name' => 'Amigo', 'color' => '#4CAF50', 'icon' => '🌱'],
            ['name' => 'Companheiro', 'color' => '#2196F3', 'icon' => '🌿'],
            ['name' => 'Pesquisador', 'color' => '#9C27B0', 'icon' => '🔍'],
            ['name' => 'Pioneiro', 'color' => '#FF9800', 'icon' => '🏕️'],
            ['name' => 'Excursionista', 'color' => '#F44336', 'icon' => '🥾'],
            ['name' => 'Guia', 'color' => '#00BCD4', 'icon' => '🧭'],
        ];

        foreach ($classes as $c) {
            $exists = db_fetch_one("SELECT id FROM learning_categories WHERE tenant_id = ? AND name = ?", [$tenantId, $c['name']]);
            if (!$exists) {
                db_insert('learning_categories', [
                    'tenant_id' => $tenantId,
                    'name' => $c['name'],
                    'color' => $c['color'],
                    'icon' => $c['icon'],
                    'type' => 'class',
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        }
    }

    /**
     * List all programs (with optional category/type filters)
     */
    public function index(): void
    {
        $this->requireAdmin();

        $tenant = App::tenant();
        $user = App::user();

        $categoryId = $_GET['category'] ?? null;
        $type = $_GET['type'] ?? null;
        $status = $_GET['status'] ?? null;

        // Build query
        $sql = "SELECT p.*, c.name as category_name, c.color as category_color, c.icon as category_icon
                FROM learning_programs p
                LEFT JOIN learning_categories c ON p.category_id = c.id
                WHERE p.tenant_id = ?";
        $params = [$tenant['id']];

        if ($categoryId) {
            $sql .= " AND p.category_id = ?";
            $params[] = (int) $categoryId;
        }

        if ($type) {
            $sql .= " AND p.type = ?";
            $params[] = $type;
        }

        if ($status) {
            $sql .= " AND p.status = ?";
            $params[] = $status;
        } else {
            // Default: don't show archived
            $sql .= " AND (p.status IS NULL OR p.status != 'archived')";
        }

        $sql .= " ORDER BY p.name";

        $programs = db_fetch_all($sql, $params);

        // Get categories for filter dropdown
        // Get categories for filter dropdown
        $categories = $this->getCategories($tenant['id']);

        // Current category info
        $currentCategory = null;
        if ($categoryId) {
            $currentCategory = db_fetch_one(
                "SELECT * FROM learning_categories WHERE id = ? AND tenant_id = ?",
                [$categoryId, $tenant['id']]
            );
        }

        View::render('admin/programs/index', [
            'tenant' => $tenant,
            'user' => $user,
            'programs' => $programs,
            'categories' => $categories,
            'currentCategory' => $currentCategory,
            'categoryId' => $categoryId,
            'type' => $type,
            'status' => $status,
            'programs_tenantSlug' => $tenant['slug']
        ]);
    }

    /**
     * Create new program form
     */
    public function create(): void
    {
        $this->requireAdmin();

        $tenant = App::tenant();
        $user = App::user();

        $categories = $this->getCategories($tenant['id']);

        $type = $_GET['type'] ?? 'specialty';

        require BASE_PATH . '/views/admin/programs/create.php';
    }

    /**
     * Store new program
     */
    public function store(): void
    {
        $this->requireAdmin();

        $tenant = App::tenant();
        $user = App::user();

        $name = trim($_POST['name'] ?? '');
        $categoryId = $_POST['category_id'] ?? null;
    
        // Handle prefixed IDs from UI (e.g. lc_123, cat_missionary)
        if ($categoryId) {
            if (str_starts_with($categoryId, 'lc_')) {
                $categoryId = substr($categoryId, 3);
            } elseif (!is_numeric($categoryId)) {
                // Legacy string ID - not supported by learning_programs table, set to null
                $categoryId = null;
            }
        }

        $type = $_POST['type'] ?? 'specialty';
        $icon = $_POST['icon'] ?? '📘';
        $description = trim($_POST['description'] ?? '');
        $isOutdoor = isset($_POST['is_outdoor']);
        $durationHours = (int) ($_POST['duration_hours'] ?? 4);
        $difficulty = (int) ($_POST['difficulty'] ?? 1);
        $xpReward = (int) ($_POST['xp_reward'] ?? 100);

        if (empty($name)) {
            $this->json(['error' => 'Nome é obrigatório'], 400);
            return;
        }

        // Generate slug
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));

        try {
            db_begin();

            // Create program
            $programId = db_insert('learning_programs', [
                'tenant_id' => $tenant['id'],
                'category_id' => $categoryId ?: null,
                'type' => $type,
                'name' => $name,
                'slug' => $slug,
                'icon' => $icon,
                'description' => $description,
                'is_outdoor' => $isOutdoor ? 1 : 0,
                'duration_hours' => $durationHours,
                'difficulty' => $difficulty,
                'xp_reward' => $xpReward,
                'status' => 'draft',
                'created_by' => $user['id']
            ]);

            // Create initial version (v1 draft)
            db_insert('program_versions', [
                'program_id' => $programId,
                'version_number' => 1,
                'status' => 'draft'
            ]);

            db_commit();

            $this->json([
                'success' => true,
                'message' => 'Programa criado!',
                'redirect' => base_url($tenant['slug'] . '/admin/programas/' . $programId . '/editar')
            ]);

        } catch (\Exception $e) {
            db_rollback();
            $this->json(['error' => 'Erro ao criar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Edit program (steps and questions)
     */
    public function edit(array $params): void
    {
        $this->requireAdmin();

        $tenant = App::tenant();
        $user = App::user();
        $programId = (int) ($params['id'] ?? 0);

        $program = db_fetch_one(
            "SELECT p.*, c.name as category_name FROM learning_programs p 
             LEFT JOIN learning_categories c ON p.category_id = c.id
             WHERE p.id = ? AND p.tenant_id = ?",
            [$programId, $tenant['id']]
        );

        if (!$program) {
            header('HTTP/1.0 404 Not Found');
            echo 'Programa não encontrado';
            exit;
        }

        // Get current version (draft or latest)
        $version = db_fetch_one(
            "SELECT * FROM program_versions WHERE program_id = ? ORDER BY version_number DESC LIMIT 1",
            [$programId]
        );

        // Get steps for this version
        $steps = [];
        if ($version) {
            $steps = db_fetch_all(
                "SELECT * FROM program_steps WHERE version_id = ? ORDER BY sort_order",
                [$version['id']]
            );

            // Get questions for each step
            foreach ($steps as &$step) {
                $step['questions'] = db_fetch_all(
                    "SELECT * FROM program_questions WHERE step_id = ? ORDER BY sort_order",
                    [$step['id']]
                );
            }
        }

        $categories = db_fetch_all(
            "SELECT * FROM learning_categories WHERE tenant_id = ? AND status = 'active' ORDER BY name",
            [$tenant['id']]
        );

        View::render('admin/programs/edit', [
        'tenant' => $tenant,
        'user' => $user,
        'program' => $program,
        'version' => $version,
        'steps' => $steps,
        'categories' => $categories
    ]);
}

    /**
     * Update program metadata
     */
    public function update(array $params): void
    {
        $this->requireAdmin();

        $tenant = App::tenant();
        $programId = (int) ($params['id'] ?? 0);

        $name = trim($_POST['name'] ?? '');
        $categoryId = $_POST['category_id'] ?? null;
        $icon = $_POST['icon'] ?? '📘';
        $description = trim($_POST['description'] ?? '');
        $isOutdoor = isset($_POST['is_outdoor']);
        $durationHours = (int) ($_POST['duration_hours'] ?? 4);
        $difficulty = (int) ($_POST['difficulty'] ?? 1);
        $xpReward = (int) ($_POST['xp_reward'] ?? 100);

        if (empty($name)) {
            $this->json(['error' => 'Nome é obrigatório'], 400);
            return;
        }

        try {
            db_update('learning_programs', [
                'name' => $name,
                'category_id' => $categoryId ?: null,
                'icon' => $icon,
                'description' => $description,
                'is_outdoor' => $isOutdoor ? 1 : 0,
                'duration_hours' => $durationHours,
                'difficulty' => $difficulty,
                'xp_reward' => $xpReward
            ], 'id = ? AND tenant_id = ?', [$programId, $tenant['id']]);

            $this->json(['success' => true, 'message' => 'Programa atualizado!']);

        } catch (\Exception $e) {
            $this->json(['error' => 'Erro: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Save steps and questions
     */
    public function saveSteps(array $params): void
    {
        $this->requireAdmin();

        $tenant = App::tenant();
        $programId = (int) ($params['id'] ?? 0);

        $input = json_decode(file_get_contents('php://input'), true);
        $steps = $input['steps'] ?? [];

        // Get current version
        $version = db_fetch_one(
            "SELECT * FROM program_versions WHERE program_id = ? ORDER BY version_number DESC LIMIT 1",
            [$programId]
        );

        if (!$version) {
            $this->json(['error' => 'Versão não encontrada'], 404);
            return;
        }

        try {
            db_begin();

            // Delete existing steps and questions for this version
            $existingSteps = db_fetch_all("SELECT id FROM program_steps WHERE version_id = ?", [$version['id']]);
            foreach ($existingSteps as $existingStep) {
                db_delete('program_questions', 'step_id = ?', [$existingStep['id']]);
            }
            db_delete('program_steps', 'version_id = ?', [$version['id']]);

            // Insert new steps and questions
            foreach ($steps as $stepIndex => $step) {
                $stepId = db_insert('program_steps', [
                    'version_id' => $version['id'],
                    'title' => $step['title'] ?? 'Requisito',
                    'description' => $step['description'] ?? '',
                    'instructions' => $step['instructions'] ?? '',
                    'sort_order' => $stepIndex,
                    'is_required' => ($step['is_required'] ?? true) ? 1 : 0,
                    'points' => (int) ($step['points'] ?? 10)
                ]);

                // Insert questions for this step
                $questions = $step['questions'] ?? [];
                foreach ($questions as $qIndex => $question) {
                    // Handle correct_answers array for multiple choice
                    $correctAnswer = $question['correct_answer'] ?? null;
                    if (isset($question['correct_answers']) && is_array($question['correct_answers'])) {
                        $correctAnswer = json_encode($question['correct_answers']);
                    }

                    db_insert('program_questions', [
                        'step_id' => $stepId,
                        'type' => $question['type'] ?? 'text',
                        'question_text' => $question['question_text'] ?? '',
                        'options' => isset($question['options']) ? json_encode($question['options']) : null,
                        'correct_answer' => $correctAnswer,
                        'points' => (int) ($question['points'] ?? 10),
                        'is_required' => ($question['is_required'] ?? true) ? 1 : 0,
                        'sort_order' => $qIndex
                    ]);
                }
            }

            db_commit();

            $this->json(['success' => true, 'message' => 'Requisitos salvos!']);

        } catch (\Exception $e) {
            db_rollback();
            $this->json(['error' => 'Erro: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Publish program
     */
    public function publish(array $params): void
    {
        $this->requireAdmin();

        $tenant = App::tenant();
        $programId = (int) ($params['id'] ?? 0);

        try {
            // Update program status
            db_update('learning_programs', ['status' => 'published'], 'id = ? AND tenant_id = ?', [$programId, $tenant['id']]);

            // Update current version to published
            db_query(
                "UPDATE program_versions SET status = 'published', published_at = NOW() 
                 WHERE program_id = ? AND status = 'draft' ORDER BY version_number DESC LIMIT 1",
                [$programId]
            );

            $this->json(['success' => true, 'message' => 'Programa publicado!']);

        } catch (\Exception $e) {
            $this->json(['error' => 'Erro: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete program
     */
    public function delete(array $params): void
    {
        $this->requireAdmin();

        $tenant = App::tenant();
        $programId = (int) ($params['id'] ?? 0);

        $force = ($_REQUEST['force'] ?? '') === 'true';

        // Check if has progress
        $hasProgress = (int) db_fetch_column(
            "SELECT COUNT(*) FROM user_program_progress WHERE program_id = ?",
            [$programId]
        );

        if ($hasProgress > 0) {
            if ($force) {
                // If forced, we delete the progress first
                try {
                    db_delete('user_program_progress', 'program_id = ?', [$programId]);
                } catch (\Exception $e) {
                    $this->json(['error' => 'Falha ao remover progresso dos usuários: ' . $e->getMessage()], 500);
                    return;
                }
            } else {
                // Get current status
                $currentStatus = db_fetch_column("SELECT status FROM learning_programs WHERE id = ?", [$programId]);
                
                if ($currentStatus === 'archived') {
                    $this->json([
                        'success' => true, 
                        'is_archived' => true,
                        'has_progress' => true,
                        'message' => 'O programa já está arquivado e possui progresso de usuários, não podendo ser removido permanentemente sem exclusão forçada.'
                    ]);
                } else {
                    // Archive instead (Standard behavior)
                    db_update('learning_programs', ['status' => 'archived'], 'id = ? AND tenant_id = ?', [$programId, $tenant['id']]);
                    $this->json([
                        'success' => true, 
                        'is_archived' => true,
                        'has_progress' => true,
                        'message' => 'Programa arquivado! (Ocultado da lista por possuir progresso de usuários)'
                    ]);
                }
                return;
            }
        }

        try {
            db_begin();

            // Get versions
            $versions = db_fetch_all("SELECT id FROM program_versions WHERE program_id = ?", [$programId]);

            foreach ($versions as $version) {
                // Delete questions
                $steps = db_fetch_all("SELECT id FROM program_steps WHERE version_id = ?", [$version['id']]);
                foreach ($steps as $step) {
                    db_delete('program_questions', 'step_id = ?', [$step['id']]);
                }
                // Delete steps
                db_delete('program_steps', 'version_id = ?', [$version['id']]);
            }

            // Delete versions
            db_delete('program_versions', 'program_id = ?', [$programId]);

            // Delete program
            db_delete('learning_programs', 'id = ? AND tenant_id = ?', [$programId, $tenant['id']]);

            db_commit();

            $this->json(['success' => true, 'message' => 'Programa excluído!']);

        } catch (\Exception $e) {
            db_rollback();
            $this->json(['error' => 'Erro: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Assign program to users
     */
    public function assign(array $params): void
    {
        $this->requireAdmin();

        $tenant = App::tenant();
        $programId = (int) ($params['id'] ?? 0);

        $input = json_decode(file_get_contents('php://input'), true);
        $userIds = $input['user_ids'] ?? [];

        if (empty($userIds)) {
            $this->json(['error' => 'Selecione ao menos um usuário'], 400);
            return;
        }

        // Get program
        $program = db_fetch_one("SELECT * FROM learning_programs WHERE id = ? AND tenant_id = ?", [$programId, $tenant['id']]);
        if (!$program || $program['status'] !== 'published') {
            $this->json(['error' => 'Programa não publicado'], 400);
            return;
        }

        // Get published version
        $version = db_fetch_one(
            "SELECT * FROM program_versions WHERE program_id = ? AND status = 'published' ORDER BY version_number DESC LIMIT 1",
            [$programId]
        );

        if (!$version) {
            $this->json(['error' => 'Programa sem versão publicada'], 400);
            return;
        }

        $assignedCount = 0;
        $alreadyAssigned = 0;

        foreach ($userIds as $userId) {
            $userId = (int) $userId;

            // Check if already assigned
            $existing = db_fetch_one(
                "SELECT id FROM user_program_progress WHERE program_id = ? AND user_id = ?",
                [$programId, $userId]
            );

            if ($existing) {
                $alreadyAssigned++;
                continue;
            }

            // Create progress entry
            db_insert('user_program_progress', [
                'tenant_id' => $tenant['id'],
                'user_id' => $userId,
                'program_id' => $programId,
                'version_id' => $version['id'],
                'status' => 'not_started',
                'progress_percent' => 0
            ]);

            // Send notification
            LearningNotificationService::programAssigned($tenant['id'], $userId, $program, $tenant['slug']);

            $assignedCount++;
        }

        $message = "$assignedCount usuário(s) atribuído(s)";
        if ($alreadyAssigned > 0) {
            $message .= " ($alreadyAssigned já possuíam)";
        }

        $this->json(['success' => true, 'message' => $message, 'assigned' => $assignedCount]);
    }

    /**
     * Get users for assignment modal
     */
    public function getUsers(array $params): void
    {
        $this->requireAdmin();

        $tenant = App::tenant();
        $programId = (int) ($params['id'] ?? 0);

        // Get users to assign (Pathfinders + Leadership roles)
        $users = db_fetch_all("
            SELECT u.id, u.name, u.avatar_url as profile_picture,
                   CASE WHEN upp.id IS NOT NULL THEN 1 ELSE 0 END as already_assigned,
                   r.display_name as role_display
            FROM users u
            JOIN roles r ON u.role_id = r.id
            LEFT JOIN user_program_progress upp ON upp.user_id = u.id AND upp.program_id = ?
            WHERE u.tenant_id = ? AND r.name IN (
                'admin', 'director', 'associate_director', 'chaplain', 'instructor', 'counselor', 'leader', 'pathfinder'
            )
            ORDER BY r.name = 'pathfinder' DESC, u.name ASC
        ", [$programId, $tenant['id']]);

        $this->json(['success' => true, 'users' => $users]);
    }

    private function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}
