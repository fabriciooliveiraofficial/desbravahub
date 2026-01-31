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

        // Filter by prerequisites
        return array_filter($activities, function ($activity) use ($completedIds) {
            return $this->checkPrerequisites($activity['id'], $completedIds);
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
