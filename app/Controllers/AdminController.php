<?php
/**
 * Admin Controller
 * 
 * Centralized admin panel controller with tenant isolation.
 */

namespace App\Controllers;

use App\Core\View;
use App\Core\App;
use App\Services\ActivityService;
use App\Services\VersionService;
use App\Services\FeatureFlagService;
use App\Services\NotificationService;

class AdminController
{
    private ActivityService $activityService;
    private VersionService $versionService;
    private FeatureFlagService $featureFlagService;
    private NotificationService $notificationService;

    public function __construct()
    {
        $this->activityService = new ActivityService();
        $this->versionService = new VersionService();
        $this->featureFlagService = new FeatureFlagService();
        $this->notificationService = new NotificationService();
    }

    /**
     * Admin dashboard
     */
    public function dashboard(): void
    {
        $this->requireAdmin();

        $tenant = App::tenant();
        $user = App::user();

        // Gather stats
        $stats = $this->getDashboardStats();

        View::render('admin/dashboard', [
            'tenant' => $tenant,
            'user' => $user,
            'stats' => $stats,
            'pageTitle' => 'Painel'
        ]);
    }

    /**
     * Activity management
     */
    public function activities(): void
    {
        $this->requirePermission('activities.manage');

        $tenant = App::tenant();
        $user = App::user();
        $activities = \App\Services\SpecialtyService::getSpecialties($tenant['id']);

        View::render('admin/activities', [
            'tenant' => $tenant,
            'user' => $user,
            'activities' => $activities,
            'pageTitle' => 'Gerenciar Especialidades',
            'pageIcon' => 'local_activity'
        ]);
    }

    /**
     * Create activity
     */
    public function createActivity(): void
    {
        $this->requirePermission('activities.create');

        $data = [
            'title' => $_POST['title'] ?? '',
            'description' => $_POST['description'] ?? '',
            'instructions' => $_POST['instructions'] ?? '',
            'min_level' => (int) ($_POST['min_level'] ?? 1),
            'xp_reward' => (int) ($_POST['xp_reward'] ?? 0),
            'proof_types' => $_POST['proof_types'] ?? ['upload'],
            'status' => $_POST['status'] ?? 'draft',
        ];

        if (empty($data['title'])) {
            $this->jsonError('Title required');
            return;
        }

        $id = $this->activityService->create($data);
        $this->json(['success' => true, 'id' => $id]);
    }

    /**
     * Update activity
     */
    public function updateActivity(array $params): void
    {
        $this->requirePermission('activities.edit');

        $id = (int) $params['id'];
        $data = $_POST;

        $result = $this->activityService->update($id, $data);
        $this->json(['success' => $result]);
    }

    /**
     * Users management
     */
    public function users(): void
    {
        $this->requirePermission('users.manage');

        $tenant = App::tenant();
        $users = db_fetch_all(
            "SELECT u.*, r.display_name as role_name 
             FROM users u 
             JOIN roles r ON u.role_id = r.id 
             WHERE u.tenant_id = ? AND u.deleted_at IS NULL 
             ORDER BY u.name",
            [$tenant['id']]
        );

        $roles = db_fetch_all(
            "SELECT * FROM roles WHERE tenant_id = ?",
            [$tenant['id']]
        );

        View::render('admin/users', [
            'tenant' => $tenant,
            'user' => auth(),
            'users' => $users,
            'roles' => $roles,
            'pageTitle' => 'Gerenciar Usuários',
            'pageIcon' => 'people'
        ]);
    }

    /**
     * Update user role
     */
    public function updateUserRole(array $params): void
    {
        $this->requirePermission('users.manage');

        $userId = (int) $params['id'];
        $roleId = (int) ($_POST['role_id'] ?? 0);
        $tenant = App::tenant();

        // Verify user belongs to tenant
        $user = db_fetch_one(
            "SELECT id FROM users WHERE id = ? AND tenant_id = ?",
            [$userId, $tenant['id']]
        );

        if (!$user) {
            $this->jsonError('User not found', 404);
            return;
        }

        db_update('users', ['role_id' => $roleId], 'id = ?', [$userId]);
        $this->json(['success' => true]);
    }

    /**
     * Proofs pending review
     */
    public function proofs(): void
    {
        $this->requirePermission('proofs.review');

        $tenant = App::tenant();
        
        // 1. Get Activity Proofs (Standard Activities)
        $activityProofs = db_fetch_all(
            "SELECT 
                'activity' as kind,
                p.id, 
                p.type,
                p.content,
                p.submitted_at,
                u.name as user_name, 
                a.title as item_title
             FROM activity_proofs p
             JOIN user_activities ua ON p.user_activity_id = ua.id
             JOIN users u ON ua.user_id = u.id
             JOIN activities a ON ua.activity_id = a.id
             WHERE p.tenant_id = ? AND p.status = 'pending'
             ORDER BY p.submitted_at ASC",
            [$tenant['id']]
        );

        // 2. Get Program/Step Submissions (E-Learning)
    $programSubmissions = db_fetch_all("
        SELECT 
            'program' as kind,
            usr.id,
            CASE 
                WHEN usr.response_file IS NOT NULL THEN 'upload'
                WHEN usr.response_url IS NOT NULL THEN 'url'
                ELSE 'text'
            END as type,
            usr.response_text,
            usr.response_file,
            usr.response_url,
            usr.submitted_at,
            u.name as user_name,
            CONCAT(p.name, ' - ', ps.title) as item_title,
            usr.step_id
        FROM user_step_responses usr
        JOIN program_steps ps ON usr.step_id = ps.id
        JOIN program_versions pv ON ps.version_id = pv.id
        JOIN learning_programs p ON pv.program_id = p.id
        JOIN user_program_progress upp ON usr.progress_id = upp.id
        JOIN users u ON upp.user_id = u.id
        WHERE upp.tenant_id = ? AND usr.status = 'submitted'
        ORDER BY usr.submitted_at ASC
    ", [$tenant['id']]);

    // Format program responses if they are JSON
    foreach ($programSubmissions as &$psub) {
        $content = $psub['response_file'] ?? $psub['response_url'] ?? $psub['response_text'];
        
        if ($psub['type'] === 'text' && !empty($psub['response_text'])) {
            $decoded = json_decode($psub['response_text'], true);
            if (is_array($decoded)) {
                $questions = db_fetch_all("SELECT * FROM program_questions WHERE step_id = ? ORDER BY sort_order", [$psub['step_id']]);
                if (!empty($questions)) {
                    $formatted = "";
                    foreach ($questions as $q) {
                        $ans = $decoded[$q['id']] ?? ($decoded[$questions[0]['id'] ?? -1] ?? null);
                        if ($ans !== null) {
                            $qTitle = $q['question_text'] ?: "Pergunta";
                            
                            // Simple choice mapping for the summary
                            if (in_array($q['type'], ['single_choice', 'select', 'true_false'])) {
                                $options = json_decode($q['options'] ?? '[]', true);
                                if (is_numeric($ans) && isset($options[(int)$ans])) {
                                    $opt = $options[(int)$ans];
                                    $ans = is_string($opt) ? $opt : ($opt['text'] ?? $opt['label'] ?? $ans);
                                } elseif ($q['type'] === 'true_false') {
                                    $ans = ($ans == '1') ? 'Verdadeiro' : 'Falso';
                                }
                            }
                            
                            $formatted .= ($formatted ? " | " : "") . "$qTitle: $ans";
                        }
                    }
                    $content = $formatted ?: $content;
                }
            }
        }
        $psub['content'] = $content;
    }

    // Merge both
    $proofs = array_merge($activityProofs, $programSubmissions);
        
        // Sort merged proofs by date (newest first for inbox efficiency)
        usort($proofs, function($a, $b) {
            return strtotime($b['submitted_at']) - strtotime($a['submitted_at']);
        });

        View::render('admin/proofs', [
            'tenant' => $tenant,
            'user' => auth(),
            'proofs' => $proofs,
            'pageTitle' => 'Revisar Provas',
            'pageIcon' => 'assignment_turned_in'
        ]);
    }

    /**
     * Version management
     */
    public function versions(): void
    {
        $this->requirePermission('versions.view');

        $tenant = App::tenant();
        $versions = $this->versionService->getAllVersions();
        $currentVersion = $this->versionService->getTenantVersion($tenant['id']);

        View::render('admin/versions', [
            'tenant' => $tenant,
            'user' => App::user(),
            'versions' => $versions,
            'currentVersion' => $currentVersion,
            'pageTitle' => 'Gerenciar Versões',
            'pageIcon' => 'history'
        ]);
    }

    /**
     * Feature flags management
     */
    public function features(): void
    {
        $this->requirePermission('features.manage');

        $tenant = App::tenant();
        $flags = $this->featureFlagService->getTenantFlags($tenant['id']);

        View::render('admin/features', [
            'tenant' => $tenant,
            'user' => App::user(),
            'flags' => $flags,
            'pageTitle' => 'Feature Flags',
            'pageIcon' => 'toggle_on'
        ]);
    }

    /**
     * Notification broadcast
     */
    public function notifications(): void
    {
        $this->requirePermission('notifications.broadcast');

        $tenant = App::tenant();

        View::render('admin/notifications', [
            'tenant' => $tenant,
            'user' => App::user(),
            'pageTitle' => 'Notificações',
            'pageIcon' => 'campaign'
        ]);
    }

    /**
     * Send broadcast notification
     */
    public function sendBroadcast(): void
    {
        $this->requirePermission('notifications.broadcast');

        $title = $_POST['title'] ?? '';
        $message = $_POST['message'] ?? '';
        $channels = $_POST['channels'] ?? ['toast'];

        if (empty($title) || empty($message)) {
            $this->jsonError('Title and message required');
            return;
        }

        $ids = $this->notificationService->broadcast('broadcast', $title, $message, [
            'channels' => $channels,
        ]);

        $this->json(['success' => true, 'sent_count' => count($ids)]);
    }

    /**
     * Get dashboard stats
     */
    private function getDashboardStats(): array
    {
        $tenantId = App::tenantId();

        return [
            'users' => db_fetch_column(
                "SELECT COUNT(*) FROM users WHERE tenant_id = ? AND deleted_at IS NULL",
                [$tenantId]
            ),
            'active_users' => db_fetch_column(
                "SELECT COUNT(*) FROM users WHERE tenant_id = ? AND status = 'active' AND deleted_at IS NULL",
                [$tenantId]
            ),
            'activities' => db_fetch_column(
                "SELECT COUNT(*) FROM activities WHERE tenant_id = ?",
                [$tenantId]
            ),
            'pending_proofs' => db_fetch_column(
                "SELECT COUNT(*) FROM activity_proofs WHERE tenant_id = ? AND status = 'pending'",
                [$tenantId]
            ),
            'completed_activities' => db_fetch_column(
                "SELECT COUNT(*) FROM user_activities WHERE tenant_id = ? AND status = 'completed'",
                [$tenantId]
            ),
        ];
    }

    /**
     * Require admin or director role
     */
    private function requireAdmin(): void
    {
        $user = App::user();
        $roleName = $user['role_name'] ?? '';

        if (!in_array($roleName, ['admin', 'director', 'associate_director', 'counselor', 'instructor'])) {
            http_response_code(403);
            echo "Acesso negado";
            exit;
        }
    }

    /**
     * Require specific permission
     */
    private function requirePermission(string $permission): void
    {
        $user = App::user();
        $roleName = $user['role_name'] ?? '';

        // DEBUG: Show role info
        // echo "<pre>User role_name: " . var_export($roleName, true) . " | User data: " . var_export(array_keys($user ?? []), true) . "</pre>";

        // Admins and Directors have all permissions
        if (in_array($roleName, ['admin', 'director', 'associate_director'])) {
            return;
        }

        // Also check counselor and instructor for some basic admin access
        if (in_array($roleName, ['counselor', 'instructor'])) {
            return; // Leadership can access admin pages
        }

        if (!can($permission)) {
            http_response_code(403);
            echo "Permissão negada: $permission (role: $roleName)";
            exit;
        }
    }

    /**
     * Quiz management
     */
    public function quizzes(): void
    {
        $this->requireAdmin();
        require BASE_PATH . '/views/admin/quizzes.php';
    }

    /**
     * Create quiz
     */
    public function createQuiz(): void
    {
        $this->requireAdmin();
        $tenant = App::tenant();

        $title = trim($_POST['title'] ?? '');
        $activityId = (int) ($_POST['activity_id'] ?? 0);
        $passingScore = (int) ($_POST['passing_score'] ?? 70);

        if (empty($title) || !$activityId) {
            $this->jsonError('Título e atividade são obrigatórios');
            return;
        }

        db_insert('quizzes', [
            'tenant_id' => $tenant['id'],
            'activity_id' => $activityId,
            'title' => $title,
            'passing_score' => $passingScore,
        ]);

        header('Location: ' . base_url($tenant['slug'] . '/admin/quizzes'));
        exit;
    }

    /**
     * Pathfinder Classes management
     */
    public function classes(): void
    {
        $tenant = App::tenant();
        $user = App::user();

        $categories = [];
        $grouped = [];

        try {
            // Get categories that are classes
            $learningCategories = db_fetch_all(
                "SELECT * FROM learning_categories 
                 WHERE tenant_id = ? AND status = 'active' AND type IN ('class', 'both') 
                 ORDER BY sort_order, name",
                [$tenant['id']]
            );

            foreach ($learningCategories as $lCat) {
                $catId = $lCat['id'];

                // Get programs (the actual classes) in this category
                $programs = db_fetch_all(
                    "SELECT * FROM learning_programs 
                     WHERE tenant_id = ? AND category_id = ? AND status = 'published' 
                     ORDER BY name",
                    [$tenant['id'], $catId]
                );

                $specs = [];
                foreach ($programs as $prog) {
                    // Get assigned users count for this specific program
                    $assignedCount = db_fetch_column(
                        "SELECT COUNT(*) FROM user_program_progress 
                         WHERE program_id = ? AND tenant_id = ?",
                        [$prog['id'], $tenant['id']]
                    ) ?: 0;

                    $specs[] = [
                        'id' => $prog['id'],
                        'db_id' => $prog['id'],
                        'name' => $prog['name'],
                        'badge_icon' => $prog['icon'] ?? '📘',
                        'type' => $prog['is_outdoor'] ? 'outdoor' : 'indoor',
                        'duration_hours' => $prog['estimated_hours'] ?? 4,
                        'difficulty' => $prog['difficulty'] ?? 2,
                        'xp_reward' => $prog['xp_reward'] ?? 100,
                        'description' => $prog['description'] ?? '',
                        'member_count' => $assignedCount,
                        'is_learning_program' => true,
                        'program_id' => $prog['id'],
                        'category_id' => $catId
                    ];
                }

                // Header data for the section
                $categories[] = [
                    'id' => $catId,
                    'db_id' => $lCat['id'],
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
                    'specialties' => $specs // Using key 'specialties' to match repository.php pattern if needed
                ];
            }
        } catch (\Exception $e) {
            // Silently fail if tables missing
        }

        // Sort tabs A-Z
        usort($categories, fn($a, $b) => strcasecmp($a['name'], $b['name']));

        View::render('admin/classes/index', [
            'tenant' => $tenant,
            'user' => $user,
            'categories' => $categories,
            'grouped' => $grouped,
            'pageTitle' => 'Classes',
            'pageIcon' => 'school'
        ]);
    }

    /**
     * Store new class (Mission Control)
     */
    public function storeClass(): void
    {
        $this->requireAdmin();

        $tenant = App::tenant();
        $user = App::user();

        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['name'])) {
            $this->jsonError('Nome da classe é obrigatório');
            return;
        }

        $name = trim($input['name']);
        $description = $input['description'] ?? '';
        $icon = $input['icon'] ?? '🌱';
        $color = $input['color'] ?? '#4CAF50';

        // Get next sort order
        $maxOrder = db_fetch_column(
            "SELECT MAX(sort_order) FROM learning_categories WHERE tenant_id = ?",
            [$tenant['id']]
        ) ?: 0;

        try {
            db_insert('learning_categories', [
                'tenant_id' => $tenant['id'],
                'name' => $name,
                'description' => $description,
                'icon' => $icon,
                'color' => $color,
                'type' => 'class',
                'status' => 'active',
                'sort_order' => $maxOrder + 1
            ]);

            $classId = db_last_insert_id();

            $this->json([
                'success' => true,
                'message' => 'Classe criada com sucesso!',
                'class_id' => $classId
            ]);

        } catch (\Exception $e) {
            $this->jsonError('Erro ao criar classe: ' . $e->getMessage());
        }
    }

    /**
     * Update existing class/category
     */
    public function updateClass(array $params): void
    {
        $this->requireAdmin();

        $id = (int) $params['id'];
        $tenant = App::tenant();
        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['name'])) {
            $this->jsonError('Nome da classe é obrigatório');
            return;
        }

        try {
            db_update('learning_categories', [
                'name' => trim($input['name']),
                'description' => $input['description'] ?? '',
                'icon' => $input['icon'] ?? '🌱',
                'color' => $input['color'] ?? '#4CAF50'
            ], 'id = ? AND tenant_id = ?', [$id, $tenant['id']]);

            $this->json([
                'success' => true,
                'message' => 'Classe atualizada com sucesso!'
            ]);

        } catch (\Exception $e) {
            $this->jsonError('Erro ao atualizar classe: ' . $e->getMessage());
        }
    }

    /**
     * Delete class/category
     */
    public function deleteClass(array $params): void
    {
        $this->requireAdmin();

        $id = (int) $params['id'];
        $tenant = App::tenant();

        try {
            // Soft delete or hard delete? Let's check status.
            // For now, hard delete based on typical app pattern here, but usually categories are 'deleted' status.
            db_update('learning_categories', ['status' => 'deleted'], 'id = ? AND tenant_id = ?', [$id, $tenant['id']]);

            $this->json([
                'success' => true,
                'message' => 'Classe removida com sucesso!'
            ]);

        } catch (\Exception $e) {
            $this->jsonError('Erro ao remover classe: ' . $e->getMessage());
        }
    }

    private function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    private function jsonError(string $message, int $code = 400): void
    {
        http_response_code($code);
        $this->json(['error' => $message]);
    }

    /**
     * Permission matrix management
     */
    public function permissions(): void
    {
        $this->requireAdmin();

        $tenant = App::tenant();
        $user = App::user();

        // Get all roles for this tenant
        $roles = db_fetch_all(
            "SELECT * FROM roles WHERE tenant_id = ? ORDER BY
             CASE name 
                WHEN 'admin' THEN 1 
                WHEN 'director' THEN 2 
                WHEN 'associate_director' THEN 3 
                WHEN 'counselor' THEN 4 
                WHEN 'pathfinder' THEN 5 
                ELSE 6 
             END",
            [$tenant['id']]
        );

        // Get all permissions grouped
        $permissions = db_fetch_all(
            "SELECT * FROM permissions ORDER BY `group`, `key`"
        );

        // Get role_permissions matrix
        $rolePermissions = [];
        $rp = db_fetch_all(
            "SELECT rp.role_id, rp.permission_id 
             FROM role_permissions rp
             JOIN roles r ON rp.role_id = r.id
             WHERE r.tenant_id = ?",
            [$tenant['id']]
        );

        foreach ($rp as $row) {
            $rolePermissions[$row['role_id']][$row['permission_id']] = true;
        }

        // Group permissions by group
        $groupedPermissions = [];
        foreach ($permissions as $perm) {
            $group = $perm['group'] ?? 'Outros';
            $groupedPermissions[$group][] = $perm;
        }

        View::render('admin/permissions/index', [
            'tenant' => $tenant,
            'user' => $user,
            'roles' => $roles,
            'rolePermissions' => $rolePermissions,
            'groupedPermissions' => $groupedPermissions,
            'pageTitle' => 'Permissões',
            'pageIcon' => 'lock'
        ]);
    }

    /**
     * Save permission changes
     */
    public function savePermissions(): void
    {
        $this->requireAdmin();

        $tenant = App::tenant();
        $roleId = (int) ($_POST['role_id'] ?? 0);
        $permissionIds = $_POST['permissions'] ?? [];

        // Verify role belongs to tenant
        $role = db_fetch_one(
            "SELECT id FROM roles WHERE id = ? AND tenant_id = ?",
            [$roleId, $tenant['id']]
        );

        if (!$role) {
            $this->jsonError('Role not found', 404);
            return;
        }

        // Remove all current permissions for this role
        db_query("DELETE FROM role_permissions WHERE role_id = ?", [$roleId]);

        // Add new permissions
        foreach ($permissionIds as $permId) {
            db_insert('role_permissions', [
                'role_id' => $roleId,
                'permission_id' => (int) $permId,
            ]);
        }

        $this->json(['success' => true]);
    }

    /**
     * Review a unified proof (Activity or Program Response)
     */
    public function reviewUnifiedProof(array $params): void
    {
        $this->requirePermission('proofs.review');

        $tenant = App::tenant();
        $user = App::user();
        $id = (int) ($params['id'] ?? 0);
        $kind = $_POST['kind'] ?? 'activity';
        $action = $_POST['action'] ?? '';
        $comment = $_POST['comment'] ?? null;

        if ($kind === 'program') {
            // Handle Program Step Response (Forward to ApprovalController logic)
            $approvalCtrl = new \App\Controllers\ApprovalController();
            
            // Re-route based on action
            $routeParams = ['id' => $id];
            
            if ($action === 'approved') {
                $approvalCtrl->approve($routeParams);
            } else if ($action === 'rejected') {
                $approvalCtrl->reject($routeParams);
            } else if ($action === 'requested_changes') {
                // requested_changes maps to rejected with feedback in program system
                $approvalCtrl->reject($routeParams); 
            }
            return;
        }

        // Handle Activity Proof (Standard logic)
        $proofService = new \App\Services\ProofService();
        $result = $proofService->reviewProof($id, $action, $comment);

        if ($result['success']) {
            $this->json(['message' => 'Proof reviewed successfully']);
        } else {
            $this->json(['error' => $result['error']], 400);
        }
    }
}
