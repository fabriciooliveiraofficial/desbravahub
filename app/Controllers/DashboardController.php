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
        $progress = $this->progressionService->getUserProgress($user['id']);

        // Atividades disponíveis
        $activities = $this->activityService->getAvailableForUser($user['id']);

        // Atividades em andamento
        $inProgress = array_filter($activities, fn($a) => ($a['user_status'] ?? null) === 'in_progress');

        // Atividades disponíveis para iniciar
        $available = array_filter($activities, fn($a) => !isset($a['user_status']) || $a['user_status'] === null);

        // Conquistas recentes
        $achievements = $this->progressionService->getUserAchievements($user['id']);
        $recentAchievements = array_slice($achievements, 0, 3);

        // Ranking
        $leaderboard = $this->progressionService->getLeaderboard(5);

        // Notificações não lidas
        $unreadCount = $this->notificationService->getUnreadCount($user['id']);

        // Novas conquistas para notificar
        $newAchievements = $this->progressionService->getPendingAchievementNotifications($user['id']);

        // Unificar missões (Especialidades e Programas atribuídos)
        $missions = SpecialtyService::getUserAssignments($user['id'], $tenant['id']);

        // Mark as "Read/Viewed" for God Mode lifecycle tracking
        // We do this silently
        try {
            foreach ($missions as $m) {
                // If specialty is pending and not read, mark as read
                if ($m['type_label'] === 'specialty' && empty($m['read_at'])) {
                    db_update('specialty_assignments', ['read_at' => date('Y-m-d H:i:s')], 'id = ?', [$m['id']]);
                }
                
                // If program is not started and updated_at equals created_at (not touched), touch it
                // Logic: updated_at > created_at implies "Seen/Interacted"
                if (($m['type_label'] === 'program' || ($m['is_program'] ?? false)) && $m['status'] === 'not_started') {
                   // Only update if updated_at is null or same as created_at
                   // Note: checking SQL side is safer but this is a quick touch
                   db_query("UPDATE user_program_progress SET updated_at = NOW() WHERE id = ? AND (updated_at IS NULL OR updated_at = created_at)", [$m['id']]);
                }
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
                'xp_reward' => $specialty['xp_reward'] ?? 0,
                'user_status' => $m['status'],
                'icon' => $specialty['badge_icon'] ?? '🎯',
                'is_mission' => true,
                'type_label' => $m['type_label'],
                'program_id' => str_replace('prog_', '', $m['specialty_id'] ?? '')
            ];

            if ($m['status'] === 'in_progress' || $m['status'] === 'pending') {
                $inProgress[] = $missionActivity;
            } else {
                $available[] = $missionActivity;
            }
        }

        View::render('dashboard/index', [
            'user' => $user,
            'tenant' => $tenant,
            'progress' => $progress,
            'inProgress' => $inProgress,
            'available' => $available,
            'recentAchievements' => $recentAchievements,
            'leaderboard' => $leaderboard,
            'unreadCount' => $unreadCount,
            'newAchievements' => $newAchievements
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

        $achievements = $this->progressionService->getUserAchievements($user['id']);
        $progress = $this->progressionService->getUserProgress($user['id']);

        // Notificações não lidas
        $unreadCount = $this->notificationService->getUnreadCount($user['id']);

        View::render('dashboard/achievements', [
            'tenant' => $tenant,
            'user' => $user,
            'achievements' => $achievements,
            'progress' => $progress,
            'unreadCount' => $unreadCount
        ], 'member');
    }

    /**
     * Página de ranking
     */
    public function leaderboard(): void
    {
        $user = App::user();
        $tenant = App::tenant();

        $leaderboard = $this->progressionService->getLeaderboard(50);
        $progress = $this->progressionService->getUserProgress($user['id']);

        // Encontrar posição do usuário
        $userPosition = null;
        foreach ($leaderboard as $index => $member) {
            if ($member['id'] === $user['id']) {
                $userPosition = $index + 1;
                break;
            }
        }

        // Notificações não lidas
        $unreadCount = $this->notificationService->getUnreadCount($user['id']);

        View::render('dashboard/leaderboard', [
            'tenant' => $tenant,
            'user' => $user,
            'leaderboard' => $leaderboard,
            'progress' => $progress,
            'userPosition' => $userPosition,
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

        View::render('dashboard/profile', [
            'tenant' => $tenant,
            'user' => $user,
            'progress' => $progress,
            'achievements' => $achievements,
            'unreadCount' => $unreadCount
        ], 'member');
    }

    /**
     * Página de provas enviadas
     */
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
}
