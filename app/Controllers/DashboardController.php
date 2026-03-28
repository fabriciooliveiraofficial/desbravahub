<?php
/**
 * Dashboard Controller
 * 
 * Painel principal do Desbravador com progresso, atividades e conquistas.
 */

namespace App\Controllers;

use App\Core\View;
use App\Core\App;
use App\Services\ActivityService;
use App\Services\ProgressionService;
use App\Services\NotificationService;
use App\Services\TenantService;
use App\Services\SpecialtyService;

class DashboardController
{
    private ActivityService $activityService;
    private ProgressionService $progressionService;
    private NotificationService $notificationService;

    public function __construct()
    {
        $this->activityService = new ActivityService();
        $this->progressionService = new ProgressionService();
        $this->notificationService = new NotificationService();
    }

    /**
     * Página principal do dashboard
     */
    public function index(): void
    {
        $user = App::user();
        $tenant = App::tenant();

        // Dados do progresso
        $progressData = $this->progressionService->getUserProgress($user['id']);
        
        // Update and get streak
        $streak = $this->progressionService->updateStreak($user['id']);
        $progressData['streak'] = $streak;

        // Count Insignias = Achievements + Completed Specialties + Completed Programs
        $achievementCount = (int) db_fetch_column(
            "SELECT COUNT(*) FROM user_achievements WHERE user_id = ? AND tenant_id = ?",
            [$user['id'], $tenant['id']]
        );

        $completedSpecialties = 0;
        try {
            $completedSpecialties = (int) db_fetch_column(
                "SELECT COUNT(*) FROM specialty_assignments WHERE user_id = ? AND tenant_id = ? AND status IN ('completed', 'approved')",
                [$user['id'], $tenant['id']]
            );
        } catch (\Exception $e) { /* table may not exist */ }

        $completedPrograms = 0;
        try {
            $completedPrograms = (int) db_fetch_column(
                "SELECT COUNT(*) FROM user_program_progress
                 WHERE user_id = ? AND tenant_id = ? AND status = 'completed'",
                [$user['id'], $tenant['id']]
            );
        } catch (\Exception $e) { /* table may not exist */ }

        $insigniaCount = $achievementCount + $completedSpecialties + $completedPrograms;

        // Get Next Event
        $nextEvent = db_fetch_one(
            "SELECT * FROM events 
             WHERE tenant_id = ? AND status = 'upcoming' AND start_datetime > NOW()
             ORDER BY start_datetime ASC LIMIT 1",
            [$tenant['id']]
        );

        // Atividades disponíveis
        $activities = $this->activityService->getAvailableForUser($user['id']);

        // Atividades em andamento
        $inProgress = array_filter($activities, fn($a) => ($a['user_status'] ?? null) === 'in_progress');

        // Atividades disponíveis para iniciar
        $available = array_filter($activities, fn($a) => !isset($a['user_status']) || $a['user_status'] === null);

        // Conquistas recentes — achievements + especialidades + programas concluídos
        $recentAchievements = $this->progressionService->getRecentAccomplishments($user['id'], 4);

        // Radar Data: cada eixo = uma especialidade/classe real do desbravador
        // Valor = progresso real (0–100%)
        $radarItems = [];

        try {
            // 1. Programas do novo sistema (especialidades e classes via user_program_progress)
            $progRows = db_fetch_all("
                SELECT lp.name,
                       COALESCE(up.progress_percent, 0) AS progress,
                       up.status,
                       COALESCE(lc.type, 'specialty') AS category_type
                FROM user_program_progress up
                JOIN learning_programs lp ON up.program_id = lp.id
                LEFT JOIN learning_categories lc ON lp.category_id = lc.id
                WHERE up.user_id = ? AND up.tenant_id = ? AND up.status != 'cancelled'
                ORDER BY up.updated_at DESC
                LIMIT 8
            ", [$user['id'], $tenant['id']]);

            foreach ($progRows as $row) {
                $radarItems[] = [
                    'name'     => $row['name'],
                    'progress' => (int) $row['progress'],
                    'type'     => $row['category_type'],
                    'status'   => $row['status'],
                ];
            }

            // 2. Especialidades legadas (specialty_assignments) — apenas se não cobertas pelo novo sistema
            $legacyRows = db_fetch_all("
                SELECT s.name, sa.status,
                       COUNT(sr.id) AS total_reqs,
                       COUNT(CASE WHEN urp.status IN ('completed','approved') THEN 1 END) AS done_reqs
                FROM specialty_assignments sa
                JOIN specialties s ON sa.specialty_id = s.id
                LEFT JOIN specialty_requirements sr ON sr.specialty_id = s.id
                LEFT JOIN user_requirement_progress urp
                    ON urp.requirement_id = sr.id AND urp.user_id = sa.user_id
                WHERE sa.user_id = ? AND sa.tenant_id = ? AND sa.status != 'cancelled'
                GROUP BY sa.id, s.name, sa.status
                ORDER BY sa.updated_at DESC
                LIMIT 5
            ", [$user['id'], $tenant['id']]);

            // Nomes já presentes no novo sistema (para deduplicar)
            $newNames = array_map(fn($i) => mb_strtolower($i['name'], 'UTF-8'), $radarItems);

            foreach ($legacyRows as $row) {
                if (in_array(mb_strtolower($row['name'], 'UTF-8'), $newNames)) continue;

                $total = (int) $row['total_reqs'];
                $done  = (int) $row['done_reqs'];
                if ($total > 0) {
                    $progress = (int) round(($done / $total) * 100);
                } else {
                    $progress = match($row['status']) {
                        'completed', 'approved' => 100,
                        'in_progress'            => 30,
                        default                  => 5,
                    };
                }

                $radarItems[] = [
                    'name'     => $row['name'],
                    'progress' => $progress,
                    'type'     => 'specialty',
                    'status'   => $row['status'],
                ];
            }

            // Limite final de 8 eixos
            $radarItems = array_slice($radarItems, 0, 8);

        } catch (\Exception $e) {
            // Silently fail to keep dashboard functional
        }

        // Activity Feed — unified feed via ActivityService
        $activityFeed = [];
        try {
            $activityFeed = $this->activityService->getGlobalFeed(
                (int) $tenant['id'],
                5
            );
        } catch (\Exception $e) {
            // Fallback: empty feed
        }

        // Ranking
        $leaderboard = $this->progressionService->getLeaderboard(5);

        // Notificações não lidas
        $unreadCount = $this->notificationService->getUnreadCount($user['id']);

        // Novas conquistas para notificar
        $newAchievements = $this->progressionService->getPendingAchievementNotifications($user['id']);

        // Unificar missões (Especialidades e Programas atribuídos)
        $missions = SpecialtyService::getUserAssignments($user['id'], $tenant['id']);

        // Mark as "Read/Viewed" for God Mode lifecycle tracking — batch to avoid N queries
        try {
            $specialtyIdsToMark = [];
            $programIdsToTouch  = [];

            foreach ($missions as $m) {
                if ($m['type_label'] === 'specialty' && empty($m['read_at'])) {
                    $specialtyIdsToMark[] = $m['id'];
                }
                if (($m['type_label'] === 'program' || ($m['is_program'] ?? false)) && $m['status'] === 'not_started') {
                    $programIdsToTouch[] = $m['id'];
                }
            }

            if (!empty($specialtyIdsToMark)) {
                $p = implode(',', array_fill(0, count($specialtyIdsToMark), '?'));
                db_query(
                    "UPDATE specialty_assignments SET read_at = ? WHERE id IN ($p)",
                    array_merge([date('Y-m-d H:i:s')], $specialtyIdsToMark)
                );
            }

            if (!empty($programIdsToTouch)) {
                $p = implode(',', array_fill(0, count($programIdsToTouch), '?'));
                db_query(
                    "UPDATE user_program_progress SET updated_at = NOW() WHERE id IN ($p) AND (updated_at IS NULL OR updated_at = created_at)",
                    $programIdsToTouch
                );
            }
        } catch (\Exception $e) {
            // Ignore errors (e.g. missing columns) to not break dashboard
        }

        // Mesclar missões em atividades em andamento ou disponíveis
        foreach ($missions as $m) {
            $specialty = $m['specialty'] ?? [];

            if (($specialty['name'] ?? null) === null) {
                continue; // Skip assignments with missing specialty/program data
            }

            $missionActivity = [
                'id' => $m['id'],
                'assignment_id' => $m['assignment_id'],
                'title' => $specialty['name'],
                'xp' => ($specialty['xp_reward'] ?? 0) ?: ($m['xp_earned'] ?? 0),
                'user_status' => $m['status'],
                'icon' => $specialty['badge_icon'] ?? '🎯',
                'is_mission' => true,
                'type_label' => $m['type_label'],
                'program_id' => str_replace('prog_', '', $m['specialty_id'] ?? ''),
                'total_steps' => $m['total_steps'] ?? 0,
                'answered_steps' => $m['answered_steps'] ?? 0,
                'progress_percent' => $m['progress_percent'] ?? 0
            ];

            if ($m['status'] === 'in_progress' || $m['status'] === 'pending' || $m['status'] === 'submitted' || $m['status'] === 'rejected') {
                $inProgress[] = $missionActivity;
            } else {
                $available[] = $missionActivity;
            }
        }

        View::render('dashboard/index', [
            'user' => $user,
            'tenant' => $tenant,
            'progress' => $progressData,
            'inProgress' => $inProgress,
            'available' => $available,
            'recentAchievements' => $recentAchievements,
            'leaderboard' => $leaderboard,
            'unreadCount' => $unreadCount,
            'newAchievements' => $newAchievements,
            'insigniaCount' => $insigniaCount,
            'nextEvent' => $nextEvent,
            'radarItems' => $radarItems,
            'activityFeed' => $activityFeed
        ], 'member');
    }

    /**
     * Página de todas as atividades
     */
    public function activities(): void
    {
        $user = App::user();
        $tenant = App::tenant();

        $activities = $this->activityService->getAvailableForUser($user['id']);

        // Agrupar por status
        $grouped = [
            'in_progress' => [],
            'available' => [],
            'completed' => [],
            'locked' => [],
        ];

        foreach ($activities as $activity) {
            $status = $activity['user_status'] ?? 'available';
            if (!isset($activity['user_status'])) {
                $status = $activity['is_locked'] ? 'locked' : 'available';
            }
            $grouped[$status][] = $activity;
        }

        // Notificações não lidas
        $unreadCount = $this->notificationService->getUnreadCount($user['id']);

        View::render('dashboard/activities', [
            'tenant' => $tenant,
            'user' => $user,
            'grouped' => $grouped,
            'unreadCount' => $unreadCount
        ], 'member');
    }

    /**
     * Detalhe de uma atividade
     */
    public function activityDetail(array $params): void
    {
        $user = App::user();
        $tenant = App::tenant();
        $activityId = (int) $params['id'];

        $activity = $this->activityService->findById($activityId);

        if (!$activity) {
            http_response_code(404);
            echo "Atividade não encontrada";
            return;
        }

        // Status do usuário
        $userActivity = $this->activityService->getUserActivity($user['id'], $activityId);
        $prerequisites = $this->activityService->getPrerequisites($activityId);

        // Notificações não lidas
        $unreadCount = $this->notificationService->getUnreadCount($user['id']);

        View::render('dashboard/activity-detail', [
            'tenant' => $tenant,
            'user' => $user,
            'activity' => $activity,
            'userActivity' => $userActivity,
            'prerequisites' => $prerequisites,
            'unreadCount' => $unreadCount
        ], 'member');
    }

    /**
     * Página de conquistas
     */
    public function achievements(): void
    {
        $user = App::user();
        $tenant = App::tenant();

        $progress          = $this->progressionService->getUserProgress($user['id']);
        $earned_items      = $this->progressionService->getAllEarnedItems($user['id']);
        $total_available   = $this->progressionService->countAvailableItems((int) $tenant['id']);

        // All possible items in this tenant
        $all_achievements = db_fetch_all("SELECT * FROM achievements WHERE tenant_id = ?", [$tenant['id']]);
        $all_specialties  = db_fetch_all("SELECT * FROM specialties WHERE tenant_id = ? AND status = 'active'", [$tenant['id']]);
        $all_programs     = db_fetch_all("SELECT * FROM learning_programs WHERE tenant_id = ? AND status = 'published'", [$tenant['id']]);

        // Identify IDs/Names already earned to exclude them from "Locked"
        $earned_names = array_column($earned_items, 'name');

        $locked_items = [];
        
        // Map Badges
        foreach ($all_achievements as $ach) {
            if (!in_array($ach['name'], $earned_names)) {
                $locked_items[] = ['name' => $ach['name'], 'type' => 'achievement', 'icon' => $ach['icon'] ?? '🏆'];
            }
        }
        // Map Specialties
        foreach ($all_specialties as $spec) {
            if (!in_array($spec['name'], $earned_names)) {
                $locked_items[] = ['name' => $spec['name'], 'type' => 'specialty', 'icon' => '🔍'];
            }
        }
        // Map Programs
        foreach ($all_programs as $prog) {
            if (!in_array($prog['name'], $earned_names)) {
                $locked_items[] = ['name' => $prog['name'], 'type' => 'program', 'icon' => $prog['icon'] ?? '📖'];
            }
        }

        // Sort locked items alphabetically
        usort($locked_items, fn($a, $b) => strcmp($a['name'], $b['name']));

        // Notificações não lidas
        $unreadCount = $this->notificationService->getUnreadCount($user['id']);

        View::render('dashboard/achievements', [
            'tenant'              => $tenant,
            'user'                => $user,
            'earned_items'        => $earned_items,
            'locked_items'        => $locked_items,
            'total_available'     => $total_available,
            'progress'            => $progress,
            'unreadCount'         => $unreadCount
        ], 'member');
    }

    /**
     * Página de ranking geral (Membros)
     */
    public function leaderboard(): void
    {
        $user = App::user();
        $tenant = App::tenant();

        // Get Top 100
        $leaderboard = $this->progressionService->getLeaderboard(100);
        
        // Calculate user position
        $userPosition = null;
        foreach ($leaderboard as $index => $member) {
            if ($member['id'] == $user['id']) {
                $userPosition = $index + 1;
                break;
            }
        }

        // If not in top 100, get actual position from DB
        if ($userPosition === null) {
            $posData = db_fetch_one("
                SELECT COUNT(*) + 1 as position 
                FROM users 
                WHERE tenant_id = ? AND status = 'active' AND deleted_at IS NULL
                AND xp_points > ?
            ", [$tenant['id'], $user['xp_points']]);
            $userPosition = $posData['position'] ?? null;
        }

        // Notificações não lidas
        $unreadCount = $this->notificationService->getUnreadCount($user['id']);

        View::render('dashboard/leaderboard', [
            'tenant' => $tenant,
            'user' => $user,
            'leaderboard' => $leaderboard,
            'userPosition' => $userPosition,
            'unreadCount' => $unreadCount
        ], 'member');
    }

    /**
     * Página de ranking de unidades
     */
    public function unitLeaderboard(): void
    {
        $user = App::user();
        $tenant = App::tenant();

        $unitLeaderboard = $this->progressionService->getUnitLeaderboard(100);
        
        // Unidade do usuário
        $userUnitId = $user['unit_id'] ?? null;
        $unitPosition = null;
        if ($userUnitId) {
            foreach ($unitLeaderboard as $index => $unit) {
                if ($unit['id'] == $userUnitId) {
                    $unitPosition = $index + 1;
                    break;
                }
            }
        }

        // Notificações não lidas
        $unreadCount = $this->notificationService->getUnreadCount($user['id']);

        View::render('dashboard/unit-leaderboard', [
            'tenant' => $tenant,
            'user' => $user,
            'unitLeaderboard' => $unitLeaderboard,
            'userUnitId' => $userUnitId,
            'unitPosition' => $unitPosition,
            'unreadCount' => $unreadCount
        ], 'member');
    }

    /**
     * Página de perfil
     */
    public function profile(): void
    {
        $user = App::user();
        $tenant = App::tenant();

        $progress = $this->progressionService->getUserProgress($user['id']);
        $achievements = $this->progressionService->getUserAchievements($user['id']);

        // Notificações não lidas
        $unreadCount = $this->notificationService->getUnreadCount($user['id']);

        // Referral Stats (with fallback if table missing)
        $referralStats = ['total' => 0, 'converted' => 0, 'xpEarned' => 0, 'pending' => 0];
        try {
            $referralStats = \App\Services\ReferralService::getUserStats($user['id'], $tenant['id']);
        } catch (\Exception $e) {
            error_log("Referral stats error (likely table missing): " . $e->getMessage());
        }

        // Fetch extended profile if exists
        $userProfile = [];
        try {
            $userProfile = db_fetch_one("SELECT * FROM user_profiles WHERE user_id = ?", [$user['id']]) ?: [];
        } catch (\Exception $e) {
            // Table might not exist yet
            $userProfile = [];
        }

        $certificateCount = 0;
        try {
            $certificateCount = (int) db_fetch_column(
                "SELECT COUNT(*) FROM issued_certificates WHERE user_id = ? AND tenant_id = ?",
                [$user['id'], $tenant['id']]
            );
        } catch (\Exception $e) { /* table may not exist yet */ }

        View::render('dashboard/profile', [
            'tenant'           => $tenant,
            'user'             => $user,
            'userProfile'      => $userProfile,
            'progress'         => $progress,
            'achievements'     => $achievements,
            'unreadCount'      => $unreadCount,
            'referralStats'    => $referralStats,
            'certificateCount' => $certificateCount,
        ], 'member');
    }

    /**
     * Faz o upload da foto de perfil (Avatar)
     */
    public function uploadAvatar(): void
    {
        header('Content-Type: application/json');

        $user = App::user();
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Não autenticado']);
            return;
        }

        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'Nenhum arquivo enviado ou erro no upload.']);
            return;
        }

        $file = $_FILES['avatar'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        
        if (!in_array($file['type'], $allowedTypes)) {
            echo json_encode(['success' => false, 'message' => 'Formato de imagem inválido. Use JPG, PNG ou WEBP.']);
            return;
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'avatar_' . $user['id'] . '_' . time() . '.' . $ext;
        
        // Ensure directory exists
        $uploadDir = BASE_PATH . '/public/uploads/avatars';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $destPath = $uploadDir . '/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $destPath)) {
            // Build the public URL to save in database
            // Note: adapt path depending on your routing, usually /uploads/...
            $avatarUrl = base_url('uploads/avatars/' . $filename);
            
            // Delete old avatar if it exists and isn't a default fallback
            if (!empty($user['avatar_url'])) {
                $oldPath = str_replace(base_url(), BASE_PATH . '/public/', $user['avatar_url']);
                if (file_exists($oldPath) && is_file($oldPath)) {
                    @unlink($oldPath);
                }
            }

            db_update('users', ['avatar_url' => $avatarUrl], 'id = ?', [$user['id']]);

            echo json_encode(['success' => true, 'avatar_url' => $avatarUrl]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Falha ao processar o arquivo.']);
        }
    }

    /**
     * Salva as informações do Livro de Registro (Perfil)
     */
    public function saveProfile(): void
    {
        header('Content-Type: application/json');

        $user = App::user();
        $tenant = App::tenant();

        if (!$user || !$tenant) {
            echo json_encode(['success' => false, 'message' => 'Não autenticado']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
            return;
        }

        // Ensure user_profiles table exists (outside transaction to avoid implicit commit)
        try {
            db_fetch_one("SELECT 1 FROM user_profiles LIMIT 1");
        } catch (\Exception $e) {
            db_query("
                CREATE TABLE IF NOT EXISTS user_profiles (
                    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
                    user_id INT UNSIGNED NOT NULL,
                    tenant_id INT UNSIGNED NOT NULL,
                    address VARCHAR(500) NULL,
                    phone_emergency VARCHAR(50) NULL,
                    school_grade VARCHAR(100) NULL,
                    blood_type VARCHAR(5) NULL,
                    rh_factor VARCHAR(5) NULL,
                    tetanus_vaccine VARCHAR(100) NULL,
                    medical_conditions JSON NULL,
                    allergies JSON NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY uk_user_profiles_user (user_id),
                    KEY idx_user_profiles_tenant (tenant_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        // Strict validation for Pathfinders
        if (is_pathfinder()) {
            $requiredFields = [
                'name' => 'Nome Completo',
                'birth_date' => 'Data de Nascimento',
                'phone' => 'Telefone',
                'address' => 'Endereço Completo',
                'phone_emergency' => 'Telefone de Emergência',
                'school_grade' => 'Série Escolar',
                'blood_type' => 'Tipo Sanguíneo',
                'rh_factor' => 'Fator RH',
                'tetanus_vaccine' => 'Vacina contra Tétano'
            ];

            $missing = [];
            foreach ($requiredFields as $field => $label) {
                if (empty(trim($input[$field] ?? ''))) {
                    $missing[] = $label;
                }
            }

            if (!empty($missing)) {
                echo json_encode(['success' => false, 'message' => 'Campos obrigatórios ausentes: ' . implode(', ', $missing)]);
                return;
            }
        }

        try {
            db_begin();

            // Update base user info
            if (!empty($input['name']) || !empty($input['phone']) || !empty($input['birth_date'])) {
                $userData = [];
                if (!empty($input['name'])) $userData['name'] = trim($input['name']);
                if (isset($input['phone'])) $userData['phone'] = trim($input['phone']);
                if (!empty($input['birth_date'])) $userData['birth_date'] = $input['birth_date'];
                
                if (!empty($userData)) {
                    db_update('users', $userData, 'id = ?', [$user['id']]);
                }
            }

            $medicalConds = json_encode([
                'diabetes' => !empty($input['cond_diabetes']),
                'epilepsia' => !empty($input['cond_epilepsy']),
                'coracao' => !empty($input['cond_heart']),
                'hemofilia' => !empty($input['cond_hemophilia']),
                'bronquite' => !empty($input['cond_bronchitis']),
                'asma' => !empty($input['cond_asthma']),
                'outros' => trim($input['cond_others'] ?? '')
            ], JSON_UNESCAPED_UNICODE);

            $allergies = json_encode([
                'penicilina' => !empty($input['allergy_penicillin']),
                'soro' => !empty($input['allergy_serum']),
                'outros' => trim($input['allergy_others'] ?? '')
            ], JSON_UNESCAPED_UNICODE);

            $profileData = [
                'user_id' => $user['id'],
                'tenant_id' => $tenant['id'],
                'address' => trim($input['address'] ?? ''),
                'phone_emergency' => trim($input['phone_emergency'] ?? ''),
                'school_grade' => trim($input['school_grade'] ?? ''),
                'blood_type' => trim($input['blood_type'] ?? ''),
                'rh_factor' => trim($input['rh_factor'] ?? ''),
                'tetanus_vaccine' => trim($input['tetanus_vaccine'] ?? ''),
                'medical_conditions' => $medicalConds,
                'allergies' => $allergies
            ];

            $exists = db_fetch_one("SELECT id FROM user_profiles WHERE user_id = ?", [$user['id']]);

            if ($exists) {
                db_update('user_profiles', $profileData, 'user_id = ?', [$user['id']]);
            } else {
                db_insert('user_profiles', $profileData);
            }

            db_commit();

            // Update the current user runtime object if base info was updated
            if (!empty($userData)) {
                $freshUser = db_fetch_one("SELECT * FROM users WHERE id = ?", [$user['id']]);
                if ($freshUser) {
                    \App\Core\App::setUser($freshUser);
                }
            }

            // Referral System: Check if this user was referred and activate the reward
            try {
                \App\Services\ReferralService::checkAndActivate($user['id']);
            } catch (\Exception $e) {
                error_log("Referral activation check error: " . $e->getMessage());
            }

            echo json_encode(['success' => true, 'message' => 'Ficha de Registro atualizada com sucesso!']);
        } catch (\Exception $e) {
            db_rollback();
            error_log('[Profile Save Error] ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao salvar ficha: ' . $e->getMessage()]);
        }
    }

    /**
     * Página de provas enviadas
     */
    public function proofs(): void
    {
        $user = App::user();
        $tenant = App::tenant();

        // Buscar provas do usuário
        $proofs = db_fetch_all(
            "SELECT p.*, a.title as activity_title, ua.status as activity_status
            FROM activity_proofs p
            JOIN user_activities ua ON p.user_activity_id = ua.id
            JOIN activities a ON ua.activity_id = a.id
            WHERE ua.user_id = ? AND ua.tenant_id = ?
            ORDER BY p.submitted_at DESC",
            [$user['id'], $tenant['id']]
        );

        // Buscar respostas de programas (Especialidades/Classes)
        // Excluindo rascunhos 'draft'
        $programResponses = db_fetch_all("
            SELECT usr.id, usr.status, usr.submitted_at, usr.feedback,
                   usr.response_text, usr.response_file, usr.response_url,
                   ps.title as step_title, 
                   prog.name as program_name, prog.type as program_type
            FROM user_step_responses usr
            JOIN program_steps ps ON usr.step_id = ps.id
            JOIN user_program_progress upp ON usr.progress_id = upp.id
            JOIN learning_programs prog ON upp.program_id = prog.id
            WHERE upp.user_id = ? AND upp.tenant_id = ? AND usr.status != 'draft'
            ORDER BY usr.submitted_at DESC
        ", [$user['id'], $tenant['id']]);

        // Merge and Normalize
        foreach ($programResponses as $r) {
            $type = 'text';
            $content = $r['response_text'];

            if (!empty($r['response_url'])) {
                $type = 'url';
                $content = $r['response_url'];
            } elseif (!empty($r['response_file'])) {
                $type = 'upload';
                $content = $r['response_file'];
            }

            $proofs[] = [
                'id' => 'prog_' . $r['id'],
                'activity_title' => $r['program_name'] . ': ' . $r['step_title'],
                'status' => $r['status'],
                'type' => $type,
                'content' => $content,
                'feedback' => $r['feedback'],
                'submitted_at' => $r['submitted_at'],
                'activity_status' => $r['status'] // Map status to legacy field if needed
            ];
        }

        // Sort merged array by date DESC
        usort($proofs, function($a, $b) {
            return strtotime($b['submitted_at']) - strtotime($a['submitted_at']);
        });

        // --- Calculate Stats (Count PROGRAMS, not questions) ---
        $stats = ['pending' => 0, 'approved' => 0, 'rejected' => 0];

        // 1. Legacy Stats
        $legacyStats = db_fetch_all("
            SELECT p.status, COUNT(*) as count
            FROM activity_proofs p
            JOIN user_activities ua ON p.user_activity_id = ua.id
            WHERE ua.user_id = ? AND ua.tenant_id = ?
            GROUP BY p.status
        ", [$user['id'], $tenant['id']]);
        
        foreach ($legacyStats as $s) {
            if (isset($stats[$s['status']])) $stats[$s['status']] += $s['count'];
        }

        // 2. Program Stats (New System)
        // Pending = submitted
        $progPending = db_fetch_one("
            SELECT COUNT(*) as count FROM user_program_progress 
            WHERE user_id = ? AND tenant_id = ? AND status = 'submitted'
        ", [$user['id'], $tenant['id']]);
        $stats['pending'] += ($progPending['count'] ?? 0);

        // Approved = completed
        $progApproved = db_fetch_one("
            SELECT COUNT(*) as count FROM user_program_progress 
            WHERE user_id = ? AND tenant_id = ? AND status = 'completed'
        ", [$user['id'], $tenant['id']]);
        $stats['approved'] += ($progApproved['count'] ?? 0);

        // Rejected = In Progress but has rejected steps (and not yet resubmitted/completed)
        // Logic: Distinct programs that have at least one 'rejected' step
        $progRejected = db_fetch_one("
            SELECT COUNT(DISTINCT upp.id) as count
            FROM user_program_progress upp
            JOIN user_step_responses usr ON upp.id = usr.progress_id
            WHERE upp.user_id = ? AND upp.tenant_id = ? 
            AND upp.status != 'completed' 
            AND usr.status = 'rejected'
        ", [$user['id'], $tenant['id']]);
        $stats['rejected'] += ($progRejected['count'] ?? 0);


        // Notificações não lidas
        $unreadCount = $this->notificationService->getUnreadCount($user['id']);

        View::render('dashboard/proofs', [
            'tenant' => $tenant,
            'user' => $user,
            'proofs' => $proofs,
            'stats' => $stats,
            'unreadCount' => $unreadCount
        ], 'member');
    }

    /**
     * Página de notificações
     */
    public function notifications(): void
    {
        $user = App::user();
        $tenant = App::tenant();

        $notifications = $this->notificationService->getAll($user['id'], 50);
        $unreadCount = $this->notificationService->getUnreadCount($user['id']);

        // Mark all as read when visiting the page
        $this->notificationService->markAllAsRead($user['id']);

        View::render('dashboard/notifications', [
            'tenant' => $tenant,
            'user' => $user,
            'notifications' => $notifications,
            'unreadCount' => $unreadCount
        ], 'member');
    }

    /**
     * Clear all notifications
     */
    public function clearNotifications(): void
    {
        $user = App::user();
        $tenant = App::tenant();

        $this->notificationService->clearAll($user['id']);

        // Redirect back with success
        header('Location: ' . base_url($tenant['slug'] . '/notificacoes'));
        exit;
    }

    /**
     * Página de classes dos desbravadores
     */
    public function classes(): void
    {
        $user = App::user();
        $tenant = App::tenant();

        // Define pathfinder classes
        $classes = [
            ['id' => 'amigo', 'name' => 'Amigo', 'color' => '#4CAF50', 'icon' => '🌱', 'min_age' => 10, 'level' => 1],
            ['id' => 'companheiro', 'name' => 'Companheiro', 'color' => '#2196F3', 'icon' => '🌿', 'min_age' => 11, 'level' => 2],
            ['id' => 'pesquisador', 'name' => 'Pesquisador', 'color' => '#9C27B0', 'icon' => '🔍', 'min_age' => 12, 'level' => 3],
            ['id' => 'pioneiro', 'name' => 'Pioneiro', 'color' => '#FF9800', 'icon' => '🏕️', 'min_age' => 13, 'level' => 4],
            ['id' => 'excursionista', 'name' => 'Excursionista', 'color' => '#F44336', 'icon' => '🥾', 'min_age' => 14, 'level' => 5],
            ['id' => 'guia', 'name' => 'Guia', 'color' => '#00BCD4', 'icon' => '🧭', 'min_age' => 15, 'level' => 6],
        ];

        // Get user's current class
        $userClass = $user['pathfinder_class'] ?? null;

        // Notificações não lidas
        $unreadCount = $this->notificationService->getUnreadCount($user['id']);

        View::render('dashboard/classes', [
            'tenant' => $tenant,
            'classes' => $classes,
            'userClass' => $userClass,
            'unreadCount' => $unreadCount
        ], 'member');
    }

    /**
     * Mapa de Trilhas (Learning Paths) - Visualização estilo Duolingo
     */
    public function learningPaths(): void
    {
        $user = App::user();
        $tenant = App::tenant();

        // 1. Get all assigned specialties
        $specialtyNodes = [];
        try {
            $assignments = db_fetch_all("
                SELECT sa.id, sa.specialty_id, sa.status, sa.created_at, sa.completed_at,
                       lp.name, lp.icon, lp.difficulty, lp.xp_reward, lp.description,
                       lc.name as category_name, lc.color as category_color, lc.icon as category_icon,
                       (SELECT COUNT(*) FROM user_requirement_progress WHERE assignment_id = sa.id) as total_reqs,
                       (SELECT COUNT(*) FROM user_requirement_progress WHERE assignment_id = sa.id AND status IN ('draft', 'submitted', 'approved', 'rejected')) as answered_reqs
                FROM specialty_assignments sa
                JOIN learning_programs lp ON sa.specialty_id = lp.id
                LEFT JOIN learning_categories lc ON lp.category_id = lc.id
                WHERE sa.user_id = ? AND sa.tenant_id = ?
                ORDER BY lc.sort_order, lc.name, sa.created_at
            ", [$user['id'], $tenant['id']]);

            foreach ($assignments as $a) {
                $total = (int)($a['total_reqs'] ?? 0);
                $answered = (int)($a['answered_reqs'] ?? 0);
                $progress = $total > 0 ? round(($answered / $total) * 100) : 0;
                
                // If status is completed/approved, force 100%
                $status = $a['status'] ?? 'not_started';
                if ($status === 'completed' || $status === 'approved') {
                    $progress = 100;
                }
                $nodeStatus = 'locked';
                if ($status === 'completed' || $status === 'approved') {
                    $nodeStatus = 'completed';
                } elseif ($status === 'in_progress' || $status === 'pending' || $status === 'submitted') {
                    $nodeStatus = 'in_progress';
                } elseif ($status === 'not_started') {
                    $nodeStatus = 'available';
                }

                $specialtyNodes[] = [
                    'id' => 'spec_' . $a['id'],
                    'title' => $a['name'],
                    'icon' => $a['icon'] ?? '🎯',
                    'xp' => $a['xp_reward'] ?? 100,
                    'difficulty' => $a['difficulty'] ?? 2,
                    'status' => $nodeStatus,
                    'progress' => $progress,
                    'category' => $a['category_name'] ?? 'Sem Categoria',
                    'category_color' => $a['category_color'] ?? '#6366f1',
                    'category_icon' => $a['category_icon'] ?? '📂',
                    'type' => 'specialty',
                    'link' => base_url($tenant['slug'] . '/especialidades/' . $a['id'])
                ];
            }
        } catch (\Exception $e) {
            // Silently continue if specialty_assignments table doesn't exist
        }

        // 2. Get all assigned programs
        $programNodes = [];
        try {
            $programs = db_fetch_all("
                SELECT up.id, up.program_id, up.status, up.progress_percent, up.started_at, up.completed_at,
                       p.name, p.icon, p.difficulty, p.xp_reward, p.description, p.type as program_type,
                       c.name as category_name, c.color as category_color, c.icon as category_icon,
                       (SELECT COUNT(*) FROM program_steps ps JOIN program_versions pv ON ps.version_id = pv.id WHERE pv.program_id = p.id) as total_steps,
                       (SELECT COUNT(*) FROM user_step_responses usr WHERE usr.progress_id = up.id) as answered_steps
                FROM user_program_progress up
                JOIN learning_programs p ON up.program_id = p.id
                LEFT JOIN learning_categories c ON p.category_id = c.id
                WHERE up.user_id = ? AND up.tenant_id = ?
                ORDER BY c.sort_order, c.name, up.created_at
            ", [$user['id'], $tenant['id']]);

            foreach ($programs as $p) {
                $status = $p['status'] ?? 'not_started';
                $nodeStatus = 'locked';
                if ($status === 'completed' || $status === 'approved') {
                    $nodeStatus = 'completed';
                } elseif ($status === 'in_progress' || $status === 'submitted' || $status === 'rejected') {
                    $nodeStatus = 'in_progress';
                } elseif ($status === 'not_started') {
                    $nodeStatus = 'available';
                }

                $total = (int)($p['total_steps'] ?? 0);
                $answered = (int)($p['answered_steps'] ?? 0);
                $progress = $total > 0 ? round(($answered / $total) * 100) : 0;

                // Force 100% if status is completed/approved
                if ($status === 'completed' || $status === 'approved') {
                    $progress = 100;
                }

                $programNodes[] = [
                    'id' => 'prog_' . $p['id'],
                    'title' => $p['name'],
                    'icon' => $p['icon'] ?? '📘',
                    'xp' => $p['xp_reward'] ?? 100,
                    'difficulty' => $p['difficulty'] ?? 2,
                    'status' => $nodeStatus,
                    'progress' => $progress,
                    'total_steps' => $total,
                    'completed_steps' => $answered,
                    'category' => $p['category_name'] ?? 'Geral',
                    'category_color' => $p['category_color'] ?? '#64748b',
                    'category_icon' => $p['category_icon'] ?? '📁',
                    'type' => ($p['program_type'] === 'class') ? 'class' : 'specialty',
                    'link' => base_url($tenant['slug'] . '/aprendizado/' . $p['program_id'])
                ];
            }
        } catch (\Exception $e) {
            // Silently continue if tables don't exist
        }

        // 3. Merge all nodes and group by category
        $allNodes = array_merge($specialtyNodes, $programNodes);
        $grouped = [];
        foreach ($allNodes as $node) {
            $cat = $node['category'];
            if (!isset($grouped[$cat])) {
                $grouped[$cat] = [
                    'name' => $cat,
                    'color' => $node['category_color'],
                    'icon' => $node['category_icon'],
                    'nodes' => []
                ];
            }
            $grouped[$cat]['nodes'][] = $node;
        }

        // 4. Stats
        $totalNodes = count($allNodes);
        $sumProgress = array_sum(array_column($allNodes, 'progress'));
        $overallPercent = $totalNodes > 0 ? round($sumProgress / $totalNodes) : 0;

        $completedNodes = count(array_filter($allNodes, fn($n) => $n['status'] === 'completed'));
        $inProgressNodes = count(array_filter($allNodes, fn($n) => $n['progress'] > 0 && $n['status'] !== 'completed'));
        $totalXp = array_sum(array_column(array_filter($allNodes, fn($n) => $n['status'] === 'completed'), 'xp'));

        $unreadCount = $this->notificationService->getUnreadCount($user['id']);

        View::render('dashboard/learning_paths', [
            'tenant' => $tenant,
            'user' => $user,
            'grouped' => $grouped,
            'overallPercent' => $overallPercent,
            'totalNodes' => $totalNodes,
            'completedNodes' => $completedNodes,
            'inProgressNodes' => $inProgressNodes,
            'totalXp' => $totalXp,
            'unreadCount' => $unreadCount
        ], 'member');
    }

    /**
     * Recruitment Ranking (Referral Leaderboard)
     */
    public function recruitmentLeaderboard(): void
    {
        $user = App::user();
        $tenant = App::tenant();

        $recruitLeaderboard = [];
        $userStats = ['total' => 0, 'converted' => 0, 'xpEarned' => 0, 'pending' => 0];
        $userInvites = [];
        $userRecruitPosition = null;

        try {
            $recruitLeaderboard = \App\Services\ReferralService::getRecruitmentLeaderboard($tenant['id'], 100);
            $userStats = \App\Services\ReferralService::getUserStats($user['id'], $tenant['id']);
            $userInvites = \App\Services\ReferralService::getUserInvites($user['id'], $tenant['id']);

            // User position in recruitment ranking
            foreach ($recruitLeaderboard as $index => $recruiter) {
                if ($recruiter['id'] == $user['id']) {
                    $userRecruitPosition = $index + 1;
                    break;
                }
            }
        } catch (\Exception $e) {
            error_log("Recruitment Leaderboard error (likely table missing): " . $e->getMessage());
            $_SESSION['flash_error'] = "O sistema de recrutamento está sendo inicializado. Se o erro persistir, contate o administrador.";
        }

        $unreadCount = $this->notificationService->getUnreadCount($user['id']);

        View::render('dashboard/recruitment-leaderboard', [
            'tenant' => $tenant,
            'user' => $user,
            'recruitLeaderboard' => $recruitLeaderboard,
            'userStats' => $userStats,
            'userInvites' => $userInvites,
            'userRecruitPosition' => $userRecruitPosition,
            'unreadCount' => $unreadCount,
            'referralXpReward' => \App\Services\ReferralService::REFERRAL_XP_REWARD
        ], 'member');
    }

    /**
     * Send referral invite (API)
     */
    public function sendReferralInvite(): void
    {
        header('Content-Type: application/json');

        $user = App::user();
        $input = json_decode(file_get_contents('php://input'), true);
        $email = trim($input['email'] ?? '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Email inválido.']);
            return;
        }

        $result = \App\Services\ReferralService::sendInvite($user['id'], $email);
        echo json_encode($result);
    }

    /**
     * Handle invite link click (public, no auth required)
     */
    public function handleInviteClick(array $params): void
    {
        $token = $params['token'] ?? '';
        $tenant = App::tenant();

        $invite = \App\Services\ReferralService::trackClick($token);

        if (!$invite) {
            header('Location: ' . base_url($tenant['slug'] . '/login'));
            return;
        }

        // Redirect to registration page with referral context
        header('Location: ' . base_url($tenant['slug'] . '/login'));
    }
}
