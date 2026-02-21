<?php
/**
 * Specialty Controller
 * 
 * Handles specialty repository and assignments for admin and dashboard.
 */

namespace App\Controllers;

use App\Core\App;
use App\Services\SpecialtyService;
use App\Services\NotificationService;
use App\Core\View;

class SpecialtyController
{
    /**
     * Admin: Browse specialty repository
     */
    public function repository(): void
    {
        $this->requireLeadership();

        $tenant = App::tenant();
        $user = App::user();

        // All categories are now user-managed via learning_categories table
        $categories = [];
        $grouped = [];

        // Add Learning Engine categories and programs
        try {
            $learningCategories = db_fetch_all(
                "SELECT * FROM learning_categories WHERE tenant_id = ? AND status = 'active' AND type IN ('specialty', 'both') ORDER BY sort_order, name",
                [$tenant['id']]
            );

            foreach ($learningCategories as $lCat) {
                $catId = $lCat['id'];

                // Get programs in this category
                $programs = db_fetch_all(
                    "SELECT * FROM learning_programs WHERE tenant_id = ? AND category_id = ? AND status = 'published' ORDER BY name",
                    [$tenant['id'], $lCat['id']]
                );

                // Convert programs to specialty-like format
                $specs = [];
                foreach ($programs as $prog) {
                    $specs[] = [
                        'id' => 'prog_' . $prog['id'],
                        'name' => $prog['name'],
                        'badge_icon' => $prog['icon'] ?? '📘',
                        'type' => $prog['is_outdoor'] ? 'outdoor' : 'indoor',
                        'duration_hours' => $prog['estimated_hours'] ?? 4,
                        'difficulty' => $prog['difficulty'] ?? 2,
                        'xp_reward' => $prog['xp_reward'] ?? 100,
                        'description' => $prog['description'] ?? '',
                        'requirements' => [],
                        'category_id' => $catId,
                        'is_learning_program' => true,
                        'program_id' => $prog['id']
                    ];
                }

                // Build category data
                $categories[] = [
                    'id' => $catId,
                    'name' => $lCat['name'],
                    'icon' => $lCat['icon'] ?? '📂',
                    'color' => $lCat['color'] ?? '#00d9ff',
                    'description' => $lCat['description'] ?? '',
                    'is_learning_category' => true
                ];

                $grouped[$catId] = [
                    'category' => [
                        'id' => $catId,
                        'db_id' => $lCat['id'],
                        'name' => $lCat['name'],
                        'icon' => $lCat['icon'] ?? '📂',
                        'color' => $lCat['color'] ?? '#00d9ff',
                        'description' => $lCat['description'] ?? '',
                        'is_learning_category' => true
                    ],
                    'specialties' => $specs
                ];
            }
        } catch (\Exception $e) {
            // Learning Engine tables may not exist yet, silently continue
        }

        // Sort categories alphabetically by name (A-Z)
        usort($categories, fn($a, $b) => strcasecmp($a['name'], $b['name']));

        View::render('admin/specialties/repository', [
            'tenant' => $tenant,
            'user' => $user,
            'categories' => $categories,
            'grouped' => $grouped
        ]);
    }

    /**
     * Admin: Browse specialties by category
     */
    public function repositoryByCategory(array $params): void
    {
        $this->requireLeadership();

        $tenant = App::tenant();
        $user = App::user();
        $categoryId = $params['id'] ?? '';

        // Get category using service (in-memory data)
        $category = SpecialtyService::getCategory($categoryId);

        if (!$category) {
            header('Location: ' . base_url($tenant['slug'] . '/admin/especialidades'));
            return;
        }

        // Get all categories for navigation
        $categories = SpecialtyService::getCategories();

        // Get specialties for this category only using service
        $specialties = array_values(SpecialtyService::getByCategory($categoryId, $tenant['id']));

        View::render('admin/specialties/category', [
            'tenant' => $tenant,
            'user' => $user,
            'category' => $category,
            'categories' => $categories,
            'specialties' => $specialties
        ]);
    }

    /**
     * Admin: Show assignment form
     */
    public function showAssign(array $params): void
    {
        $this->requireLeadership();

        $tenant = App::tenant();
        $user = App::user();
        $specialtyId = $params['id'] ?? '';

        $specialty = SpecialtyService::getSpecialty($specialtyId);
        if (!$specialty) {
            header('Location: ' . base_url($tenant['slug'] . '/admin/especialidades'));
            return;
        }

        // Ensure requirements are loaded for the UI preview
        if (empty($specialty['requirements'])) {
            $specialty['requirements'] = SpecialtyService::getRequirementsFromDB($specialtyId);
        }

        // Get users to assign (Pathfinders + Leadership roles)
        $pathfinders = db_fetch_all(
            "SELECT u.id, u.name, u.email, r.name as role_name, r.display_name as role_display
             FROM users u
             JOIN roles r ON u.role_id = r.id
             WHERE u.tenant_id = ? AND u.status = 'active' 
             AND r.name IN ('admin', 'director', 'associate_director', 'chaplain', 'instructor', 'counselor', 'leader', 'pathfinder')
             ORDER BY r.name = 'pathfinder' DESC, u.name ASC",
            [$tenant['id']]
        );

        // Check who already has this specialty assigned
        if (str_starts_with($specialtyId, 'prog_')) {
            $progId = (int) substr($specialtyId, 5);
            $existingAssignments = db_fetch_all(
                "SELECT user_id, id FROM user_program_progress 
                 WHERE tenant_id = ? AND program_id = ?",
                [$tenant['id'], $progId]
            );
            $prefix = 'prog_';
        } else {
            $existingAssignments = db_fetch_all(
                "SELECT user_id, id FROM specialty_assignments 
                 WHERE tenant_id = ? AND specialty_id = ? AND status != 'cancelled'",
                [$tenant['id'], $specialtyId]
            );
            $prefix = 'spec_';
        }
        
        $assignmentMap = [];
        foreach ($existingAssignments as $ea) {
            $assignmentMap[$ea['user_id']] = $prefix . $ea['id'];
        }

        View::render('admin/specialties/assign', [
            'tenant' => $tenant,
            'user' => $user,
            'specialty' => $specialty,
            'pathfinders' => $pathfinders,
            'assignmentMap' => $assignmentMap,
            'assignedUserIds' => array_keys($assignmentMap)
        ]);
    }

    /**
     * Admin: Process assignment
     */
    public function assign(array $params): void
    {
        $this->requireLeadership();

        $tenant = App::tenant();
        $user = App::user();
        $specialtyId = $params['id'] ?? '';

        $specialty = SpecialtyService::getSpecialty($specialtyId);
        if (!$specialty) {
            $this->json(['error' => 'Especialidade não encontrada'], 404);
            return;
        }

        $userIds = $_POST['user_ids'] ?? [];
        $dueDate = $_POST['due_date'] ?? null;
        $instructions = $_POST['instructions'] ?? null;

        if (empty($userIds)) {
            $this->json(['error' => 'Selecione pelo menos um desbravador'], 400);
            return;
        }

        $assigned = 0;
        $errors = [];

        // Buffer output to prevent PHP notices from breaking JSON
        ob_start();

        foreach ($userIds as $userId) {
            try {
                SpecialtyService::assign(
                    $tenant['id'],
                    $specialtyId,
                    (int) $userId,
                    $user['id'],
                    $dueDate ?: null,
                    $instructions ?: null
                );

                // Build deep link URL for notification
                $isProgram = str_starts_with($specialtyId, 'prog_');
                $assignmentId = db_fetch_column(
                    "SELECT id FROM specialty_assignments WHERE specialty_id = ? AND user_id = ? ORDER BY id DESC LIMIT 1",
                    [$specialtyId, $userId]
                );
                $deepLinkUrl = $assignmentId 
                    ? base_url($tenant['slug'] . '/especialidades/' . $assignmentId)
                    : base_url($tenant['slug'] . '/especialidades');

                // Send notification with deep link
                $notificationService = new NotificationService();
                if ($isProgram) {
                    $notificationService->send(
                        (int) $userId,
                        'program_assigned',
                        '📚 Novo Programa',
                        "Você recebeu o programa '{$specialty['name']}' para completar.",
                        ['data' => ['program_id' => str_replace('prog_', '', $specialtyId), 'url' => $deepLinkUrl], 'channels' => ['toast', 'push']]
                    );
                } else {
                    $notificationService->send(
                        (int) $userId,
                        'specialty_assigned',
                        '🎯 Nova Especialidade',
                        "Você recebeu a especialidade '{$specialty['name']}' para completar.",
                        ['data' => ['specialty_id' => $specialtyId, 'url' => $deepLinkUrl], 'channels' => ['toast', 'push']]
                    );
                }

                $assigned++;
            } catch (\Exception $e) {
                $errors[] = "Desbravador {$userId}: " . $e->getMessage();
            }
        }

        // Discard any buffered output (PHP notices, etc.)
        ob_end_clean();

        $this->json([
            'success' => true,
            'message' => "{$assigned} atribuição(ões) realizada(s)!",
            'assigned' => $assigned
        ]);
    }

    /**
     * Admin: Delete assignment
     */
    public function deleteAssignment(array $params): void
    {
        $this->requireLeadership();

        $tenant = App::tenant();
        $assignmentId = $_POST['assignment_id'] ?? '';

        header('Content-Type: application/json');

        if (empty($assignmentId)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            return;
        }

        $success = SpecialtyService::deleteAssignment($assignmentId, $tenant['id']);

        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Missão removida com sucesso!']);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Não é possível remover: missão em andamento']);
        }
    }

    /**
     * Admin: List all assignments
     */
    public function assignments(): void
    {
        $this->requireLeadership();

        $tenant = App::tenant();
        $user = App::user();

        $status = $_GET['status'] ?? null;
        $assignments = SpecialtyService::getTenantAssignments($tenant['id'], $status);

        // Count by status
        $counts = [
            'pending' => 0,
            'in_progress' => 0,
            'pending_review' => 0,
            'completed' => 0
        ];

        $all = SpecialtyService::getTenantAssignments($tenant['id']);
        foreach ($all as $a) {
            if (isset($counts[$a['status']])) {
                $counts[$a['status']]++;
            }
        }

        View::render('admin/specialties/assignments', [
            'tenant' => $tenant,
            'user' => $user,
            'assignments' => $assignments,
            'counts' => $counts,
            'status' => $status
        ]);
    }

    /**
     * Admin: API Endpoint to search master repository specialties for autocomplete
     */
    public function searchMaster(): void
    {
        $this->requireLeadership();

        $term = trim($_GET['q'] ?? '');
        if (strlen($term) < 2) {
            $this->json([]);
            return;
        }

        // Get all specialties from the JSON repository
        $allSpecialties = SpecialtyService::getSpecialties();
        
        $results = [];
        $termLower = mb_strtolower($term, 'UTF-8');

        foreach ($allSpecialties as $spec) {
            if (str_contains(mb_strtolower($spec['name'], 'UTF-8'), $termLower)) {
                // Ensure requirements exists, but default to empty array if not present.
                // This allows the frontend to have a structure to work with, even if data is missing.
                $results[] = [
                    'id' => $spec['id'],
                    'name' => $spec['name'],
                    'category_id' => $spec['category_id'] ?? '',
                    'badge_icon' => $spec['badge_icon'] ?? '',
                    'difficulty' => $spec['difficulty'] ?? 2,
                    'xp_reward' => $spec['xp_reward'] ?? 100,
                    'duration_hours' => $spec['duration_hours'] ?? 4,
                    'requirements' => $spec['requirements'] ?? []
                ];
            }

            if (count($results) >= 15) {
                break;
            }
        }

        $this->json($results);
    }

    /**
     * Admin: "God Mode" Mission Control Dashboard
     */
    public function godMode(): void
    {
        $this->requireLeadership();

        $tenant = App::tenant();
        $user = App::user();

        $assignments = SpecialtyService::getGodModeData($tenant['id']);
        
        // Fetch categories for "Nova Categoria" and "Criar Programa" modals
        $categories = db_fetch_all(
            "SELECT * FROM learning_categories WHERE tenant_id = ? AND status = 'active' ORDER BY sort_order, name",
            [$tenant['id']]
        );

        View::render('admin/specialties/god-mode', [
            'tenant' => $tenant,
            'user' => $user,
            'assignments' => $assignments,
            'categories' => $categories
        ]);
    }

    /**
     * Admin: "God Mode" Matrix Partial (HTMX Poll)
     */
    public function godModeMatrix(): void
    {
        $this->requireLeadership();

        $tenant = App::tenant();
        $assignments = SpecialtyService::getGodModeData($tenant['id']);

        // Return just the rows
        require BASE_PATH . '/views/admin/specialties/partials/matrix-rows.php';
    }

    /**
     * Admin: View assignment details and logs
     */
    public function assignmentDetails(array $params): void
    {
        $this->requireLeadership();

        $tenant = App::tenant();
        $assignmentId = $params['id'] ?? 0;

        // Get assignment with detailed info
        $assignment = SpecialtyService::getAssignment((int) $assignmentId, $tenant['id']);
        if (!$assignment) {
            header('Location: ' . base_url($tenant['slug'] . '/admin/especialidades/god-mode'));
            return;
        }

        // Get requirements with progress
        $requirementsWithProgress = SpecialtyService::getRequirementsWithProgress(
            (int) $assignmentId,
            $assignment['specialty_id']
        );

        // Get activity logs (if available)
        $logs = db_fetch_all(
            "SELECT urp.*, sr.title as requirement_title 
             FROM user_requirement_progress urp
             LEFT JOIN specialty_requirements sr ON urp.requirement_id = sr.id
             WHERE urp.assignment_id = ?
             ORDER BY urp.updated_at DESC",
            [$assignmentId]
        );

        View::render('admin/specialties/assignment-details', [
            'tenant' => $tenant,
            'assignment' => $assignment,
            'requirements' => $requirementsWithProgress,
            'logs' => $logs
        ]);
    }

    /**
     * Admin: Review assignment with requirements
     */
    public function reviewAssignment(array $params): void
    {
        $this->requireLeadership();

        $tenant = App::tenant();
        $assignmentId = $params['id'] ?? 0;

        $assignment = SpecialtyService::getAssignment((int) $assignmentId, $tenant['id']);
        if (!$assignment) {
            header('Location: ' . base_url($tenant['slug'] . '/admin/especialidades/atribuicoes'));
            return;
        }

        // Get pathfinder info
        $pathfinder = db_fetch_one(
            "SELECT id, name, email FROM users WHERE id = ?",
            [$assignment['user_id']]
        );

        // Get requirements progress
        $requirementsProgress = SpecialtyService::getRequirementsProgress((int) $assignmentId);

        View::render('admin/specialties/review', [
            'tenant' => $tenant,
            'user' => App::user(),
            'assignment' => $assignment,
            'pathfinder' => $pathfinder,
            'requirementsProgress' => $requirementsProgress
        ]);
    }

    /**
     * Admin: Approve a requirement
     */
    public function adminApproveRequirement(array $params): void
    {
        $this->requireLeadership();

        $tenant = App::tenant();
        $user = App::user();
        $requirementRowId = $params['id'] ?? 0;

        $success = SpecialtyService::approveRequirement((int) $requirementRowId, $user['id']);

        if ($success) {
            $this->json(['success' => true, 'message' => 'Requisito aprovado!']);
        } else {
            $this->json(['error' => 'Erro ao aprovar'], 500);
        }
    }

    /**
     * Admin: Reject a requirement
     */
    public function adminRejectRequirement(array $params): void
    {
        $this->requireLeadership();

        $tenant = App::tenant();
        $user = App::user();
        $requirementRowId = $params['id'] ?? 0;

        $data = json_decode(file_get_contents('php://input'), true);
        $feedback = $data['feedback'] ?? 'Requisito não atende aos critérios.';

        $success = SpecialtyService::rejectRequirement((int) $requirementRowId, $user['id'], $feedback);

        if ($success) {
            $this->json(['success' => true, 'message' => 'Requisito rejeitado']);
        } else {
            $this->json(['error' => 'Erro ao rejeitar'], 500);
        }
    }

    /**
     * Admin: Complete assignment
     */
    public function adminCompleteAssignment(array $params): void
    {
        $this->requireLeadership();

        $tenant = App::tenant();
        $assignmentId = $params['id'] ?? 0;

        $assignment = SpecialtyService::getAssignment((int) $assignmentId, $tenant['id']);
        if (!$assignment) {
            $this->json(['error' => 'Atribuição não encontrada'], 404);
            return;
        }

        $specialty = $assignment['specialty'];
        $xpReward = $specialty['xp_reward'] ?? 0;

        // Mark as completed
        SpecialtyService::completeAssignment((int) $assignmentId, $xpReward);

        // Award XP to user
        db_query(
            "UPDATE users SET xp = xp + ? WHERE id = ?",
            [$xpReward, $assignment['user_id']]
        );

        // Notify user
        $notificationService = new NotificationService();
        $notificationService->send(
            (int) $assignment['user_id'],
            'specialty_completed',
            '🎉 Especialidade Concluída!',
            "Parabéns! Você completou '{$specialty['name']}' e ganhou {$xpReward} XP!",
            ['channels' => ['toast', 'push'], 'data' => ['specialty_id' => $assignment['specialty_id']]]
        );

        $this->json(['success' => true, 'message' => 'Especialidade concluída!', 'xp' => $xpReward]);
    }

    /**
     * Pathfinder: My specialties
     */
    public function mySpecialties(): void
    {
        $tenant = App::tenant();
        $user = App::user();

        $assignments = SpecialtyService::getUserAssignments($user['id'], $tenant['id']);

        // Group by status
        $grouped = [
            'pending' => [],
            'in_progress' => [],
            'pending_review' => [],
            'completed' => []
        ];

        foreach ($assignments as $a) {
            $grouped[$a['status']][] = $a;
        }

        View::render('dashboard/specialties', [
            'tenant' => $tenant,
            'user' => $user,
            'assignments' => $assignments,
            'grouped' => $grouped
        ], 'member');
    }

    /**
     * Show specialty detail
     */
    public function show(array $params): void
    {
        $tenant = App::tenant();
        $user = App::user();
        $assignmentId = $params['id'] ?? 0;

        $assignment = SpecialtyService::getAssignment((int) $assignmentId, $tenant['id']);

        if (!$assignment || $assignment['user_id'] != $user['id']) {
            header('Location: ' . base_url($tenant['slug'] . '/especialidades'));
            return;
        }

        // Get proofs
        $proofs = [];

        // Get requirements progress
        $requirementsProgress = SpecialtyService::getRequirementsProgress((int) $assignmentId);

        // Ensure assignment has specialty data
        if (empty($assignment['specialty'])) {
            $assignment['specialty'] = SpecialtyService::getSpecialty($assignment['specialty_id']) ?? [];
        }
        
        // Add fallback empty specialty if still null
        if (empty($assignment['specialty'])) {
            $assignment['specialty'] = [
                'name' => 'Especialidade',
                'description' => '',
                'requirements' => []
            ];
        }

        // Extract variables for the view
        $specialty = $assignment['specialty'];
        
        // Render with member layout
        View::render('dashboard/specialty-detail', [
            'tenant' => $tenant,
            'user' => $user,
            'assignment' => $assignment,
            'specialty' => $specialty,
            'proofs' => $proofs,
            'requirementsProgress' => $requirementsProgress
        ], 'member');
    }

    /**
     * Submit proof for a specific requirement
     */
    public function submitRequirementProof(array $params): void
    {
        $tenant = App::tenant();
        $user = App::user();
        $assignmentId = $params['id'] ?? 0;

        $assignment = SpecialtyService::getAssignment((int) $assignmentId, $tenant['id']);

        if (!$assignment || $assignment['user_id'] != $user['id']) {
            $this->json(['error' => 'Não autorizado'], 403);
            return;
        }

        if ($assignment['status'] !== 'in_progress') {
            $this->json(['error' => 'Especialidade não está em andamento'], 400);
            return;
        }

        $requirementId = $_POST['requirement_id'] ?? '';
        $proofType = $_POST['proof_type'] ?? 'url';
        $content = '';

        if ($proofType === 'url') {
            $content = $_POST['url'] ?? '';
        } elseif ($proofType === 'upload' && isset($_FILES['file'])) {
            $file = $_FILES['file'];
            $uploadDir = BASE_PATH . '/public/uploads/proofs/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $filename = uniqid() . '_' . basename($file['name']);
            move_uploaded_file($file['tmp_name'], $uploadDir . $filename);
            $content = '/uploads/proofs/' . $filename;
        }

        if (empty($content) || empty($requirementId)) {
            $this->json(['error' => 'Dados incompletos'], 400);
            return;
        }

        // Submit proof for this requirement
        $success = SpecialtyService::submitRequirementProof(
            (int) $assignmentId,
            $requirementId,
            $proofType,
            $content
        );

        if ($success) {
            $this->json(['success' => true, 'message' => 'Prova enviada!']);
        } else {
            $this->json(['error' => 'Erro ao enviar prova'], 500);
        }
    }

    /**
     * Start specialty
     */
    public function start(array $params): void
    {
        $tenant = App::tenant();
        $user = App::user();
        $assignmentId = $params['id'] ?? 0;

        $assignment = SpecialtyService::getAssignment((int) $assignmentId, $tenant['id']);

        if (!$assignment || $assignment['user_id'] != $user['id']) {
            $this->json(['error' => 'Não autorizado'], 403);
            return;
        }

        if ($assignment['status'] !== 'pending') {
            $this->json(['error' => 'Esta especialidade já foi iniciada'], 400);
            return;
        }

        SpecialtyService::startAssignment((int) $assignmentId);

        // Initialize requirements for tracking
        SpecialtyService::initializeRequirements(
            (int) $assignmentId,
            $tenant['id'],
            $assignment['specialty_id']
        );

        $this->json(['success' => true, 'message' => 'Especialidade iniciada!']);
    }

    /**
     * Submit proof
     */
    public function submitProof(array $params): void
    {
        $tenant = App::tenant();
        $user = App::user();
        $assignmentId = $params['id'] ?? 0;

        $assignment = SpecialtyService::getAssignment((int) $assignmentId, $tenant['id']);

        if (!$assignment || $assignment['user_id'] != $user['id']) {
            $this->json(['error' => 'Não autorizado'], 403);
            return;
        }

        $type = $_POST['type'] ?? 'url';
        $content = '';

        if ($type === 'url') {
            $content = $_POST['url'] ?? '';
        } elseif ($type === 'upload' && isset($_FILES['file'])) {
            $file = $_FILES['file'];
            $uploadDir = BASE_PATH . '/public/uploads/proofs/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $filename = uniqid() . '_' . basename($file['name']);
            move_uploaded_file($file['tmp_name'], $uploadDir . $filename);
            $content = '/uploads/proofs/' . $filename;
        }

        if (empty($content)) {
            $this->json(['error' => 'Conteúdo da prova é obrigatório'], 400);
            return;
        }

        db_insert('specialty_proofs', [
            'assignment_id' => $assignmentId,
            'tenant_id' => $tenant['id'],
            'type' => $type,
            'content' => $content,
            'status' => 'pending'
        ]);

        // Update assignment status
        db_query(
            "UPDATE specialty_assignments SET status = 'pending_review' WHERE id = ?",
            [$assignmentId]
        );

        $this->json(['success' => true, 'message' => 'Prova enviada para avaliação!']);
    }

    /**
     * E-Learning: Show learning interface with question-by-question flow
     */
    public function learn(array $params): void
    {
        $tenant = App::tenant();
        $user = App::user();
        $assignmentId = $params['id'] ?? 0;

        $assignment = SpecialtyService::getAssignment((int) $assignmentId, $tenant['id']);

        if (!$assignment || $assignment['user_id'] != $user['id']) {
            header('Location: ' . base_url($tenant['slug'] . '/especialidades'));
            return;
        }

        // Auto-start if pending
        if ($assignment['status'] === 'pending') {
            SpecialtyService::startAssignment((int) $assignmentId);
            SpecialtyService::initializeRequirements((int) $assignmentId, $tenant['id'], $assignment['specialty_id']);
            $assignment['status'] = 'in_progress';
        }

        // Get all requirements with progress
        $requirements = SpecialtyService::getRequirementsWithProgress((int) $assignmentId, $assignment['specialty_id']);

        // Calculate progress
        $totalReqs = count($requirements);
        $answeredReqs = array_filter($requirements, fn($r) => $r['status'] !== 'pending');
        $approvedReqs = array_filter($requirements, fn($r) => $r['status'] === 'approved');
        $progress = $totalReqs > 0 ? (count($answeredReqs) / $totalReqs) * 100 : 0;
        $progressPercent = $totalReqs > 0 ? round((count($approvedReqs) / $totalReqs) * 100) : 0;

        // Check specialty type - route to outdoor view for practical specialties
        $specialtyType = SpecialtyService::getSpecialtyType($assignment['specialty_id']);

        if ($specialtyType === 'outdoor' || $specialtyType === 'mixed') {
            // Outdoor/practical specialty - use proof submission view
            require BASE_PATH . '/views/pathfinder/specialty/outdoor.php';
            return;
        }

        // Get current requirement (first unanswered, or last answered)
        $currentIndex = 0;
        foreach ($requirements as $idx => $req) {
            if ($req['status'] === 'pending') {
                $currentIndex = $idx;
                break;
            }
            $currentIndex = $idx;
        }

        // Navigation: if ?q=N is provided, go to that question
        if (isset($_GET['q']) && is_numeric($_GET['q'])) {
            $requestedIndex = (int) $_GET['q'] - 1;
            if ($requestedIndex >= 0 && $requestedIndex < $totalReqs) {
                $currentIndex = $requestedIndex;
            }
        }

        $currentRequirement = $requirements[$currentIndex] ?? null;

        require BASE_PATH . '/views/pathfinder/specialty/learn.php';
    }

    /**
     * E-Learning: Submit answer for a requirement
     */
    public function submitAnswer(array $params): void
    {
        $tenant = App::tenant();
        $user = App::user();
        $assignmentId = $params['id'] ?? 0;

        $assignment = SpecialtyService::getAssignment((int) $assignmentId, $tenant['id']);

        if (!$assignment || $assignment['user_id'] != $user['id']) {
            $this->json(['error' => 'Não autorizado'], 403);
            return;
        }

        if ($assignment['status'] !== 'in_progress') {
            $this->json(['error' => 'Especialidade não está em andamento'], 400);
            return;
        }

        $requirementId = $_POST['requirement_id'] ?? 0;
        $answerType = $_POST['type'] ?? 'text';
        $answer = '';
        $filePath = null;

        // Handle different answer types
        if ($answerType === 'file_upload' && isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['file'];
            $uploadDir = BASE_PATH . '/public/uploads/specialty_proofs/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $filename = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file['name']);
            move_uploaded_file($file['tmp_name'], $uploadDir . $filename);
            $filePath = '/uploads/specialty_proofs/' . $filename;
            $answer = 'file_uploaded';
        } else {
            $answer = $_POST['answer'] ?? '';
        }

        if (empty($answer) && empty($filePath)) {
            $this->json(['error' => 'Resposta é obrigatória'], 400);
            return;
        }

        // Save answer
        $success = SpecialtyService::saveRequirementAnswer(
            (int) $assignmentId,
            (int) $requirementId,
            $answer,
            $filePath
        );

        if ($success) {
            // Check if all requirements are answered
            $allAnswered = SpecialtyService::checkAllRequirementsAnswered((int) $assignmentId);

            $this->json([
                'success' => true,
                'message' => 'Resposta salva!',
                'all_answered' => $allAnswered
            ]);
        } else {
            $this->json(['error' => 'Erro ao salvar resposta'], 500);
        }
    }

    /**
     * E-Learning: Get next unanswered requirement
     */
    public function nextRequirement(array $params): void
    {
        $tenant = App::tenant();
        $user = App::user();
        $assignmentId = $params['id'] ?? 0;

        $assignment = SpecialtyService::getAssignment((int) $assignmentId, $tenant['id']);

        if (!$assignment || $assignment['user_id'] != $user['id']) {
            $this->json(['error' => 'Não autorizado'], 403);
            return;
        }

        $next = SpecialtyService::getNextUnansweredRequirement((int) $assignmentId, $assignment['specialty_id']);

        if ($next) {
            $this->json([
                'success' => true,
                'requirement' => $next,
                'redirect' => base_url($tenant['slug'] . '/especialidades/' . $assignmentId . '/aprender?q=' . $next['order_num'])
            ]);
        } else {
            // All requirements answered - specialty complete
            $this->json([
                'success' => true,
                'completed' => true,
                'redirect' => base_url($tenant['slug'] . '/especialidades/' . $assignmentId)
            ]);
        }
    }

    /**
     * Store new specialty (Admin)
     */
    public function storeSpecialty(array $params): void
    {
        $this->requireLeadership();

        $tenant = App::tenant();
        $user = App::user();

        $categoryId = $_POST['category_id'] ?? '';
        $name = trim($_POST['name'] ?? '');
        $badgeIcon = $_POST['badge_icon'] ?? '📘';
        $type = $_POST['type'] ?? 'indoor';
        $durationHours = (int) ($_POST['duration_hours'] ?? 4);
        $difficulty = (int) ($_POST['difficulty'] ?? 2);
        $xpReward = (int) ($_POST['xp_reward'] ?? 100);
        $description = trim($_POST['description'] ?? '');

        if (empty($categoryId) || empty($name)) {
            $this->json(['error' => 'Categoria e nome são obrigatórios'], 400);
            return;
        }

        // Clean category ID (handle prefixes like lc_123 or strings)
        $cleanCategoryId = $categoryId;
        if (str_starts_with($categoryId, 'lc_')) {
            $cleanCategoryId = substr($categoryId, 3);
        } elseif (!is_numeric($categoryId)) {
            // If it's a string ID (legacy), we might need to look it up or set null if migrating
            // For now, assuming new architecture uses integer IDs for categories
            // If it fails, we default to null or try to find by name? 
            // Better to fail gracefully if category ID format is wrong
             $cleanCategoryId = null; // Let it fail validation or insert as null?
        }

        // Generate slug for learning_programs
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
        $specialtyId = null; // Will be the auto-increment ID from learning_programs

        try {
            // Start transaction
            db_begin();

            // Insert into learning_programs (The Source of Truth)
            $programId = db_insert('learning_programs', [
                'tenant_id' => $tenant['id'],
                'category_id' => $cleanCategoryId,
                'type' => 'specialty', // Explicitly set type to specialty
                'name' => $name,
                'slug' => $slug,
                'icon' => $badgeIcon,
                'description' => $description,
                'is_outdoor' => ($type === 'outdoor' || $type === 'mixed') ? 1 : 0,
                'duration_hours' => $durationHours,
                'difficulty' => $difficulty,
                'xp_reward' => $xpReward,
                'status' => 'draft', // Draft by default (Active is not a valid enum value)
                'created_by' => $user['id']
            ]);

            // Also create initial version for it (since ProgramController expects it)
            db_insert('program_versions', [
                'program_id' => $programId,
                'version_number' => 1,
                'status' => 'draft', // Content is draft until published
                'created_at' => date('Y-m-d H:i:s') 
            ]);

            db_commit();

            $this->json([
                'success' => true,
                'message' => 'Especialidade criada com sucesso!',
                'specialty_id' => $programId,
                // Redirect to the requirements editor which should now support integer IDs
                'redirect' => base_url($tenant['slug'] . '/admin/programas/' . $programId . '/editar') 
            ]);

        } catch (\Exception $e) {
            db_rollback();
            // Log full error for internal debugging
            error_log("SpecialtyCreation Error: " . $e->getMessage());
            $this->json(['error' => 'Erro ao criar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Store complete specialty with requirements (Mission Control)
     * Creates specialty, requirements, and quiz questions in one transaction
     */
    public function storeSpecialtyComplete(): void
    {
        $this->requireLeadership();

        $tenant = App::tenant();
        $user = App::user();

        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['name']) || empty($input['category_id'])) {
            $this->json(['error' => 'Nome e categoria são obrigatórios'], 400);
            return;
        }

        $name = trim($input['name']);
        $categoryId = $input['category_id'];
        $badgeIcon = $input['badge_icon'] ?? '🏅';
        $type = $input['type'] ?? 'indoor';
        $durationHours = (int) ($input['duration_hours'] ?? 4);
        $difficulty = (int) ($input['difficulty'] ?? 2);
        $xpReward = (int) ($input['xp_reward'] ?? 100);
        $description = trim($input['description'] ?? '');
        $publish = !empty($input['publish']);
        $requirements = $input['requirements'] ?? [];

        // Generate unique slug
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name)) . '-' . substr(uniqid(), -5);

        try {
            db_begin();

            // Create program instead of legacy specialty
            $programId = db_insert('learning_programs', [
                'tenant_id' => $tenant['id'],
                'category_id' => $categoryId ?: null,
                'type' => 'specialty',
                'name' => $name,
                'slug' => $slug,
                'icon' => $badgeIcon,
                'description' => $description,
                'is_outdoor' => $type === 'outdoor' ? 1 : 0,
                'duration_hours' => $durationHours,
                'difficulty' => $difficulty,
                'xp_reward' => $xpReward,
                'status' => $publish ? 'published' : 'draft',
                'created_by' => $user['id']
            ]);

            // Create initial version for the new structure
            $versionId = db_insert('program_versions', [
                'program_id' => $programId,
                'version_number' => 1,
                'status' => $publish ? 'published' : 'draft',
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Create steps and questions
            foreach ($requirements as $idx => $req) {
                $stepId = db_insert('program_steps', [
                    'version_id' => $versionId,
                    'title' => 'Requisito', // Legacy wizard didn't have title
                    'description' => $req['description'] ?? '',
                    'instructions' => '',
                    'sort_order' => $idx,
                    'is_required' => 1,
                    'points' => (int) ($req['points'] ?? 10)
                ]);

                if (!empty($req['questions'])) {
                    foreach ($req['questions'] as $qIndex => $q) {
                        db_insert('program_questions', [
                            'step_id' => $stepId,
                            'type' => isset($q['options']) && !empty($q['options']) ? 'multiple_choice' : 'text',
                            'question_text' => $q['text'] ?? '',
                            'options' => isset($q['options']) ? json_encode($q['options'], JSON_UNESCAPED_UNICODE) : null,
                            'correct_answer' => $q['correct_index'] ?? null, // Keep correct_index for logic matching
                            'points' => 10,
                            'is_required' => 1,
                            'sort_order' => $qIndex
                        ]);
                    }
                }
            }

            db_commit();

            $this->json([
                'success' => true,
                'message' => $publish ? 'Especialidade publicada!' : 'Especialidade salva como rascunho!',
                'specialty_id' => $programId,
                'redirect' => $publish ? null : base_url($tenant['slug'] . '/admin/programas/' . $programId . '/editar')
            ]);

        } catch (\Exception $e) {
            $this->json(['error' => 'Erro ao criar especialidade: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Edit requirements for a specialty (Admin)
     */
    public function editRequirements(array $params): void
    {
        $this->requireLeadership();

        $tenant = App::tenant();
        $user = App::user();
        $specialtyId = $params['id'] ?? '';

        // Redirect Learning Programs to the new Program Editor
        if (str_starts_with($specialtyId, 'prog_')) {
            $programId = substr($specialtyId, 5);
            header("Location: " . base_url("{$tenant['slug']}/admin/programas/{$programId}/editar"));
            exit;
        }

        // Get specialty from JSON repository (no DB table for specialties)
        $specialty = SpecialtyService::getSpecialty($specialtyId);

        if (!$specialty) {
            header('Location: ' . base_url($tenant['slug'] . '/admin/especialidades'));
            return;
        }

        // Get requirements from database
        $requirements = SpecialtyService::getRequirementsFromDB($specialtyId);

        // Get categories for context
        $categories = SpecialtyService::getCategories();

        View::render('admin/specialties/edit-requirements', [
            'tenant' => $tenant,
            'user' => $user,
            'specialty' => $specialty,
            'requirements' => $requirements,
            'categories' => $categories
        ]);
    }

    /**
     * Save requirements for a specialty (Admin)
     */
    public function saveRequirements(array $params): void
    {
        $this->requireLeadership();

        $tenant = App::tenant();
        $specialtyId = $params['id'] ?? '';

        $input = json_decode(file_get_contents('php://input'), true);
        $requirements = $input['requirements'] ?? [];

        if (empty($requirements)) {
            $this->json(['error' => 'Nenhum requisito fornecido'], 400);
            return;
        }

        try {
            // Delete existing requirements
            db_query(
                "DELETE FROM specialty_requirements WHERE specialty_id = ?",
                [$specialtyId]
            );

            // Insert new requirements
            foreach ($requirements as $idx => $req) {
                $questionsJson = null;
                if (!empty($req['questions'])) {
                    $questionsJson = json_encode($req['questions'], JSON_UNESCAPED_UNICODE);
                }

                db_insert('specialty_requirements', [
                    'specialty_id' => $specialtyId,
                    'order_num' => $idx + 1,
                    'type' => $req['type'] ?? 'text',
                    'title' => $req['title'] ?? '',
                    'description' => $req['description'] ?? '',
                    'options' => $questionsJson,
                    'points' => (int) ($req['points'] ?? 10),
                    'is_required' => 1
                ]);
            }

            $this->json([
                'success' => true,
                'message' => 'Requisitos salvos com sucesso!',
                'count' => count($requirements)
            ]);

        } catch (\Exception $e) {
            $this->json(['error' => 'Erro ao salvar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Publish specialty (Admin)
     */
    public function publish(array $params): void
    {
        $this->requireLeadership();

        $tenant = App::tenant();
        $specialtyId = $params['id'] ?? '';

        try {
            // Integrity Lock: Check if specialty has requirements
            $reqCount = db_fetch_column(
                "SELECT COUNT(*) FROM specialty_requirements WHERE specialty_id = ?",
                [$specialtyId]
            );

            if ($reqCount == 0) {
                $this->json(['error' => 'A especialidade precisa de pelo menos 1 requisito para ser publicada (Integrity Lock).'], 400);
                return;
            }

            db_query(
                "UPDATE specialties SET status = 'active' WHERE id = ? AND tenant_id = ?",
                [$specialtyId, $tenant['id']]
            );

            $this->json(['success' => true, 'message' => 'Especialidade publicada com sucesso!']);
        } catch (\Exception $e) {
            $this->json(['error' => 'Erro ao publicar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete specialty (Admin)
     */
    public function delete(array $params): void
    {
        $this->requireLeadership();
        $id = $params['id'];
        $tenant = App::tenant();

        try {
            if (str_starts_with($id, 'prog_')) {
                // Delete Learning Program
                $progId = (int) substr($id, 5);
                
                // Check for progress
                $hasProgress = db_fetch_column(
                    "SELECT COUNT(*) FROM user_program_progress WHERE program_id = ? AND tenant_id = ?",
                    [$progId, $tenant['id']]
                );

                if ($hasProgress > 0) {
                     db_update('learning_programs', ['status' => 'archived'], 'id = ? AND tenant_id = ?', [$progId, $tenant['id']]);
                     $this->json(['success' => true, 'message' => 'Programa arquivado (possui progresso).']);
                     return;
                }

                $count = db_delete('learning_programs', 'id = ? AND tenant_id = ?', [$progId, $tenant['id']]);
                
                if ($count > 0) {
                    $this->json(['success' => true, 'message' => 'Programa excluído com sucesso!']);
                    return;
                }
            }

            // Delete Custom Specialty
            $count = db_delete('specialties', "id = ? AND tenant_id = ?", [$id, $tenant['id']]);

            if ($count > 0) {
                $this->json(['success' => true, 'message' => 'Especialidade excluída com sucesso!']);
            } else {
                // If not found in DB, it might be a standard specialty/program we want to "hide"
                // Check if it's already hidden to avoid duplicates (though DB has unique index)
                try {
                     // Lazy Create Table if migration failed
                     db_query("
                        CREATE TABLE IF NOT EXISTS `tenant_hidden_items` (
                            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                            `tenant_id` INT UNSIGNED NOT NULL,
                            `item_id` VARCHAR(100) NOT NULL COMMENT 'ID of the specialty/class',
                            `type` ENUM('specialty', 'class', 'program') NOT NULL DEFAULT 'specialty',
                            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                            PRIMARY KEY (`id`),
                            UNIQUE KEY `uk_hidden_items` (`tenant_id`, `item_id`),
                            KEY `idx_hidden_items_tenant` (`tenant_id`),
                            CONSTRAINT `fk_hidden_items_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                     ");

                     db_insert('tenant_hidden_items', [
                        'tenant_id' => $tenant['id'],
                        'item_id' => $id,
                        'type' => 'specialty' // Default or derived
                     ]);
                     
                     $this->json(['success' => true, 'message' => 'Especialidade removida da sua lista!']);
                } catch (\Exception $e) {
                     // Could be already hidden or table issue
                     $this->json(['success' => false, 'error' => 'Item não encontrado ou já excluído.'], 404);
                }
            }
        } catch (\Exception $e) {
            $this->json(['error' => 'Erro ao excluir: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Require leadership role
     */
    private function requireLeadership(): void
    {
        $user = App::user();
        $role = $user['role_name'] ?? '';

        if (!in_array($role, ['admin', 'director', 'counselor'])) {
            error_log("SpecialtyController::requireLeadership - Access Denied: User " . ($user['id'] ?? 'unknown') . " with role $role tried to access leadership specialty features.");
            header('HTTP/1.0 403 Forbidden');
            echo 'Acesso negado';
            exit;
        }
    }

    /**
     * Search specialties by name (API for autocomplete)
     */
    public function search(): void
    {
        $this->requireLeadership();

        $tenant = App::tenant();
        $query = trim($_GET['q'] ?? '');

        if (strlen($query) < 2) {
            $this->json(['results' => []]);
            return;
        }

        // Use SpecialtyService to search all specialties (repository + custom)
        $allResults = SpecialtyService::search($query, $tenant['id']);
        
        // Limit to 10 results and format for autocomplete
        $results = array_slice(array_values($allResults), 0, 10);
        $formatted = array_map(fn($s) => [
            'id' => $s['id'],
            'name' => $s['name'],
            'badge_icon' => $s['badge_icon'] ?? '📘'
        ], $results);

        $this->json(['results' => $formatted]);
    }

    private function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}
