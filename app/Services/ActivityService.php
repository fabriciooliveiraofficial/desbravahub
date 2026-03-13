<?php
/**
 * Activity Service
 * 
 * Manages activities, prerequisites, and activity-related operations.
 */

namespace App\Services;

use App\Core\App;

class ActivityService
{
    /**
     * Get all activities for current tenant
     */
    public function getAll(array $filters = []): array
    {
        $tenantId = App::tenantId();
        $sql = "SELECT a.*, 
                       (SELECT COUNT(*) FROM activity_prerequisites WHERE activity_id = a.id) as prerequisite_count
                FROM activities a 
                WHERE a.tenant_id = ?";
        $params = [$tenantId];

        if (!empty($filters['status'])) {
            $sql .= " AND a.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['min_level'])) {
            $sql .= " AND a.min_level <= ?";
            $params[] = $filters['min_level'];
        }

        $sql .= " ORDER BY a.order_position, a.id";

        return db_fetch_all($sql, $params);
    }

    /**
     * Get activity by ID
     */
    public function findById(int $id): ?array
    {
        $tenantId = App::tenantId();
        return db_fetch_one(
            "SELECT * FROM activities WHERE id = ? AND tenant_id = ?",
            [$id, $tenantId]
        );
    }

    /**
     * Get user activity status
     */
    public function getUserActivity(int $userId, int $activityId): ?array
    {
        return db_fetch_one(
            "SELECT * FROM user_activities WHERE user_id = ? AND activity_id = ?",
            [$userId, $activityId]
        );
    }

    /**
     * Get activities available for a user (based on level and prerequisites)
     */
    public function getAvailableForUser(int $userId): array
    {
        $tenantId = App::tenantId();
        $user = db_fetch_one("SELECT level_id, xp_points FROM users WHERE id = ? AND tenant_id = ?", [$userId, $tenantId]);

        if (!$user) {
            return [];
        }

        // Get user's current level
        $level = db_fetch_one("SELECT level_number FROM levels WHERE id = ?", [$user['level_id'] ?? 1]);
        $userLevel = $level['level_number'] ?? 1;

        // Get completed activity IDs
        $completed = db_fetch_all(
            "SELECT activity_id FROM user_activities WHERE user_id = ? AND status = 'completed'",
            [$userId]
        );
        $completedIds = array_column($completed, 'activity_id');

        // Get all active activities
    $activities = db_fetch_all(
        "SELECT a.*, 
                CASE WHEN ua.id IS NOT NULL THEN ua.status ELSE 'not_started' END as user_status,
                ua.started_at, ua.completed_at
         FROM activities a
         LEFT JOIN user_activities ua ON a.id = ua.activity_id AND ua.user_id = ?
         WHERE a.tenant_id = ? AND a.status = 'active' AND a.min_level <= ?
         ORDER BY a.order_position",
        [$userId, $tenantId, $userLevel]
    );

    if (empty($activities)) {
        return [];
    }

    // 1. Fetch all prerequisites for these activities in one go
    $activityIds = array_column($activities, 'id');
    $placeholders = implode(',', array_fill(0, count($activityIds), '?'));
    $allPrereqs = db_fetch_all(
        "SELECT activity_id, prerequisite_activity_id 
         FROM activity_prerequisites 
         WHERE activity_id IN ($placeholders)",
        $activityIds
    );

    // Group prerequisites by activity_id
    $groupedPrereqs = [];
    foreach ($allPrereqs as $p) {
        $groupedPrereqs[$p['activity_id']][] = $p['prerequisite_activity_id'];
    }

    // Filter by prerequisites
    return array_filter($activities, function ($activity) use ($completedIds, $groupedPrereqs) {
        $prereqs = $groupedPrereqs[$activity['id']] ?? [];
        foreach ($prereqs as $prereqId) {
            if (!in_array($prereqId, $completedIds)) {
                return false;
            }
        }
        return true;
    });
}

    /**
     * Check if prerequisites are met
     */
    public function checkPrerequisites(int $activityId, array $completedIds): bool
    {
        $prerequisites = db_fetch_all(
            "SELECT prerequisite_activity_id FROM activity_prerequisites WHERE activity_id = ?",
            [$activityId]
        );

        foreach ($prerequisites as $prereq) {
            if (!in_array($prereq['prerequisite_activity_id'], $completedIds)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get prerequisites for an activity
     */
    public function getPrerequisites(int $activityId): array
    {
        return db_fetch_all(
            "SELECT a.* FROM activities a
             JOIN activity_prerequisites ap ON a.id = ap.prerequisite_activity_id
             WHERE ap.activity_id = ?",
            [$activityId]
        );
    }

    /**
     * Start an activity for a user
     */
    public function startActivity(int $userId, int $activityId): array
    {
        $tenantId = App::tenantId();
        $activity = $this->findById($activityId);

        if (!$activity) {
            return ['success' => false, 'error' => 'Activity not found'];
        }

        // Check if already started
        $existing = db_fetch_one(
            "SELECT * FROM user_activities WHERE user_id = ? AND activity_id = ?",
            [$userId, $activityId]
        );

        if ($existing) {
            return ['success' => false, 'error' => 'Activity already started'];
        }

        // Check prerequisites
        $completed = db_fetch_all(
            "SELECT activity_id FROM user_activities WHERE user_id = ? AND status = 'completed'",
            [$userId]
        );

        if (!$this->checkPrerequisites($activityId, array_column($completed, 'activity_id'))) {
            return ['success' => false, 'error' => 'Prerequisites not met'];
        }

        // Calculate deadline if applicable
        $deadlineAt = null;
        if ($activity['deadline_days']) {
            $deadlineAt = date('Y-m-d H:i:s', strtotime("+{$activity['deadline_days']} days"));
        }

        // Create user activity record
        $id = db_insert('user_activities', [
            'user_id' => $userId,
            'activity_id' => $activityId,
            'tenant_id' => $tenantId,
            'status' => 'in_progress',
            'attempts' => 1,
            'deadline_at' => $deadlineAt,
        ]);

        return ['success' => true, 'user_activity_id' => $id];
    }

    /**
     * Create a new activity
     */
    public function create(array $data): int
    {
        $tenantId = App::tenantId();
        $user = App::user();

        return db_insert('activities', [
            'tenant_id' => $tenantId,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'instructions' => $data['instructions'] ?? null,
            'min_level' => $data['min_level'] ?? 1,
            'xp_reward' => $data['xp_reward'] ?? 0,
            'is_outdoor' => $data['is_outdoor'] ?? 0,
            'proof_types' => json_encode($data['proof_types'] ?? ['upload']),
            'max_attempts' => $data['max_attempts'] ?? null,
            'deadline_days' => $data['deadline_days'] ?? null,
            'status' => $data['status'] ?? 'draft',
            'order_position' => $data['order_position'] ?? 0,
            'created_by' => $user['id'] ?? null,
        ]);
    }

    /**
     * Get unified global activity feed for the tenant dashboard.
     *
     * Aggregates recent events from three sources via UNION:
     *   - Achievements earned (user_achievements)
     *   - Specialties completed/approved (specialty_assignments)
     *   - Programs completed (user_program_progress)
     *
     * Returns array of [user, action, time] items ready for the view.
     */
    public function getGlobalFeed(int $tenantId, int $limit = 5): array
    {
        $rows = db_fetch_all("
            SELECT user_name, action_type, item_name, event_at FROM (

                SELECT u.name          AS user_name,
                       'achievement'   AS action_type,
                       a.name          AS item_name,
                       ua.earned_at    AS event_at
                FROM user_achievements ua
                JOIN users u        ON ua.user_id        = u.id
                JOIN achievements a ON ua.achievement_id = a.id
                WHERE ua.tenant_id = ?

                UNION ALL

                SELECT u.name                                              AS user_name,
                       CASE sa.status
                           WHEN 'completed' THEN 'specialty_done'
                           WHEN 'approved'  THEN 'specialty_done'
                           ELSE 'specialty_started'
                       END                                                 AS action_type,
                       s.name                                              AS item_name,
                       COALESCE(sa.completed_at, sa.updated_at, sa.created_at) AS event_at
                FROM specialty_assignments sa
                JOIN users u       ON sa.user_id      = u.id
                JOIN specialties s ON sa.specialty_id = s.id
                WHERE sa.tenant_id = ?
                  AND sa.status IN ('in_progress', 'completed', 'approved')

                UNION ALL

                SELECT u.name   AS user_name,
                       CASE upp.status
                           WHEN 'completed'  THEN 'program_done'
                           WHEN 'submitted'  THEN 'program_submitted'
                           ELSE 'program_started'
                       END      AS action_type,
                       lp.name  AS item_name,
                       COALESCE(upp.completed_at, upp.updated_at, upp.started_at) AS event_at
                FROM user_program_progress upp
                JOIN users u             ON upp.user_id    = u.id
                JOIN learning_programs lp ON upp.program_id = lp.id
                WHERE upp.tenant_id = ?
                  AND upp.status IN ('in_progress', 'submitted', 'completed')

            ) AS feed
            ORDER BY event_at DESC
            LIMIT ?
        ", [$tenantId, $tenantId, $tenantId, $limit]);

        $actions = [
            'achievement'      => ' conquistou a insígnia <strong>%s</strong>',
            'specialty_done'   => ' concluiu a especialidade <strong>%s</strong>',
            'specialty_started'=> ' iniciou a especialidade <strong>%s</strong>',
            'program_done'     => ' completou a missão <strong>%s</strong>',
            'program_submitted'=> ' enviou a missão <strong>%s</strong> para avaliação',
            'program_started'  => ' iniciou a missão <strong>%s</strong>',
        ];

        $feed = [];
        foreach ($rows as $r) {
            $nameParts = explode(' ', trim($r['user_name']));
            $shortName = $nameParts[0] . (isset($nameParts[1]) ? ' ' . substr($nameParts[1], 0, 1) . '.' : '');
            $item      = htmlspecialchars($r['item_name'] ?? '');
            $tpl       = $actions[$r['action_type']] ?? ' realizou uma ação';

            $feed[] = [
                'user'   => $shortName,
                'action' => sprintf($tpl, $item),
                'time'   => self::humanTime($r['event_at']),
            ];
        }

        return $feed;
    }

    /**
     * Convert a datetime string to a short human-readable PT-BR string.
     */
    private static function humanTime(?string $datetime): string
    {
        if (!$datetime) return '—';
        $diff = time() - strtotime($datetime);
        if ($diff < 60)        return 'agora';
        if ($diff < 3600)      return 'há ' . floor($diff / 60) . ' min';
        if ($diff < 86400)     return 'há ' . floor($diff / 3600) . ' h';
        if ($diff < 604800)    return 'há ' . floor($diff / 86400) . ' dias';
        return date('d/m', strtotime($datetime));
    }

    /**
     * Update an activity
     */
    public function update(int $id, array $data): bool
    {
        $tenantId = App::tenantId();
        $allowedFields = [
            'title',
            'description',
            'instructions',
            'min_level',
            'xp_reward',
            'is_outdoor',
            'proof_types',
            'max_attempts',
            'deadline_days',
            'status',
            'order_position'
        ];

        $updateData = array_intersect_key($data, array_flip($allowedFields));

        if (isset($updateData['proof_types']) && is_array($updateData['proof_types'])) {
            $updateData['proof_types'] = json_encode($updateData['proof_types']);
        }

        if (empty($updateData)) {
            return false;
        }

        return db_update('activities', $updateData, 'id = ? AND tenant_id = ?', [$id, $tenantId]) > 0;
    }
}
