<?php
/**
 * Progression Service
 * 
 * Handles XP, levels, and progression logic.
 */

namespace App\Services;

use App\Core\App;

class ProgressionService
{
    /**
     * Add XP to a user
     */
    public function addXp(int $userId, int $amount, string $source, ?int $sourceId = null): array
    {
        $tenantId = App::tenantId();

        $user = db_fetch_one(
            "SELECT xp_points, level_id FROM users WHERE id = ? AND tenant_id = ?",
            [$userId, $tenantId]
        );

        if (!$user) {
            return ['success' => false, 'error' => 'User not found'];
        }

        $newXp = $user['xp_points'] + $amount;

        // Check for level up
        $newLevel = $this->calculateLevel($newXp);
        $leveledUp = $newLevel['id'] != $user['level_id'];

        // Update user
        db_update('users', [
            'xp_points' => $newXp,
            'level_id' => $newLevel['id'],
        ], 'id = ?', [$userId]);

        // Check for achievements
        $this->checkAchievements($userId, $newXp, $newLevel['level_number']);

        return [
            'success' => true,
            'xp_added' => $amount,
            'total_xp' => $newXp,
            'level' => $newLevel,
            'leveled_up' => $leveledUp,
        ];
    }

    /**
     * Calculate level based on XP
     */
    public function calculateLevel(int $xp): array
    {
        $level = db_fetch_one(
            "SELECT * FROM levels WHERE min_xp <= ? ORDER BY min_xp DESC LIMIT 1",
            [$xp]
        );

        return $level ?? ['id' => 1, 'level_number' => 1, 'name' => 'Iniciante', 'min_xp' => 0];
    }

    /**
     * Get user's current progress
     */
    public function getUserProgress(int $userId): array
    {
        $tenantId = App::tenantId();

        $user = db_fetch_one(
            "SELECT u.xp_points, u.level_id, l.level_number, l.name as level_name, l.min_xp, l.badge_url
             FROM users u
             LEFT JOIN levels l ON u.level_id = l.id
             WHERE u.id = ? AND u.tenant_id = ?",
            [$userId, $tenantId]
        );

        if (!$user) {
            return [];
        }

        // Get next level
        $nextLevel = db_fetch_one(
            "SELECT * FROM levels WHERE level_number = ?",
            [$user['level_number'] + 1]
        );

        // Calculate progress to next level
        $xpForNextLevel = $nextLevel ? $nextLevel['min_xp'] - $user['xp_points'] : 0;
        $progressPercent = 0;

        if ($nextLevel) {
            $levelRange = $nextLevel['min_xp'] - $user['min_xp'];
            $userProgress = $user['xp_points'] - $user['min_xp'];

            if ($levelRange > 0) {
                $progressPercent = min(100, round(($userProgress / $levelRange) * 100));
            } else {
                $progressPercent = 100;
            }
        }

        // Get activity stats
        $stats = db_fetch_one(
            "SELECT 
                (SELECT COUNT(*) FROM user_activities WHERE user_id = ? AND tenant_id = ?) + 
                (SELECT COUNT(*) FROM user_program_progress WHERE user_id = ? AND tenant_id = ?) as total_activities,

                (SELECT COUNT(*) FROM user_activities WHERE user_id = ? AND tenant_id = ? AND status = 'completed') + 
                (SELECT COUNT(*) FROM user_program_progress WHERE user_id = ? AND tenant_id = ? AND status = 'completed') as completed,

                (SELECT COUNT(*) FROM user_activities WHERE user_id = ? AND tenant_id = ? AND status = 'in_progress') + 
                (SELECT COUNT(*) FROM user_program_progress WHERE user_id = ? AND tenant_id = ? AND status = 'in_progress') as in_progress
            ",
            [
                $userId, $tenantId, $userId, $tenantId,
                $userId, $tenantId, $userId, $tenantId,
                $userId, $tenantId, $userId, $tenantId
            ]
        );

        return [
            'xp' => $user['xp_points'],
            'level' => [
                'number' => $user['level_number'],
                'name' => $user['level_name'],
                'badge_url' => $user['badge_url'],
            ],
            'next_level' => $nextLevel,
            'xp_for_next_level' => $xpForNextLevel,
            'progress_percent' => $progressPercent,
            'activities' => [
                'total' => $stats['total_activities'] ?? 0,
                'completed' => $stats['completed'] ?? 0,
                'in_progress' => $stats['in_progress'] ?? 0,
            ],
        ];
    }

    /**
     * Get leaderboard (Global or by Unit)
     */
    public function getLeaderboard(int $limit = 10, ?int $unitId = null): array
    {
        $tenantId = App::tenantId();
        $sql = "SELECT u.id, u.name, u.avatar_url, u.xp_points, l.level_number, l.name as level_name
                FROM users u
                LEFT JOIN levels l ON u.level_id = l.id
                WHERE u.tenant_id = ? AND u.status = 'active' AND u.deleted_at IS NULL";
        $params = [$tenantId];

        if ($unitId !== null) {
            $sql .= " AND u.unit_id = ?";
            $params[] = $unitId;
        }

        $sql .= " ORDER BY u.xp_points DESC LIMIT ?";
        $params[] = $limit;

        return db_fetch_all($sql, $params);
    }

    /**
     * Get Unit Leaderboard (Ranking of Units by total XP)
     */
    public function getUnitLeaderboard(int $limit = 5): array
    {
        $tenantId = App::tenantId();
        return db_fetch_all(
            "SELECT u.id, u.name, u.color, u.mascot, SUM(us.xp_points) as total_xp
             FROM units u
             JOIN users us ON us.unit_id = u.id
             WHERE u.tenant_id = ? AND u.status = 'active' AND us.deleted_at IS NULL
             GROUP BY u.id
             ORDER BY total_xp DESC
             LIMIT ?",
            [$tenantId, $limit]
        );
    }
    private function checkAchievements(int $userId, int $totalXp, int $levelNumber): void
    {
        $tenantId = App::tenantId();

        // Get unearned achievements
        $achievements = db_fetch_all(
            "SELECT a.* FROM achievements a
             LEFT JOIN user_achievements ua ON a.id = ua.achievement_id AND ua.user_id = ?
             WHERE a.tenant_id = ? AND ua.id IS NULL",
            [$userId, $tenantId]
        );

        // Get completed activity count
        $completedCount = db_fetch_column(
            "SELECT COUNT(*) FROM user_activities WHERE user_id = ? AND status = 'completed'",
            [$userId]
        );

        foreach ($achievements as $achievement) {
            $earned = false;

            switch ($achievement['criteria_type']) {
                case 'activities_completed':
                    $earned = $completedCount >= $achievement['criteria_value'];
                    break;
                case 'xp_earned':
                    $earned = $totalXp >= $achievement['criteria_value'];
                    break;
                case 'level_reached':
                    $earned = $levelNumber >= $achievement['criteria_value'];
                    break;
            }

            if ($earned) {
                $this->awardAchievement($userId, $achievement);
            }
        }
    }

    /**
     * Award an achievement to a user
     */
    private function awardAchievement(int $userId, array $achievement): void
    {
        $tenantId = App::tenantId();

        db_insert('user_achievements', [
            'user_id' => $userId,
            'achievement_id' => $achievement['id'],
            'tenant_id' => $tenantId,
            'notified' => 0,
        ]);

        // Award bonus XP if applicable
        if ($achievement['xp_reward'] > 0) {
            db_query(
                "UPDATE users SET xp_points = xp_points + ? WHERE id = ?",
                [$achievement['xp_reward'], $userId]
            );
        }
    }

    /**
     * Get user's achievements (legacy — kept for compatibility)
     */
    public function getUserAchievements(int $userId): array
    {
        $tenantId = App::tenantId();

        return db_fetch_all(
            "SELECT a.*, ua.earned_at
             FROM achievements a
             JOIN user_achievements ua ON a.id = ua.achievement_id
             WHERE ua.user_id = ? AND ua.tenant_id = ?
             ORDER BY ua.earned_at DESC",
            [$userId, $tenantId]
        );
    }

    /**
     * Get recent accomplishments unified: achievements + specialties + programs.
     * Returns [{name, date, type, icon, style}] ordered by date DESC.
     */
    public function getRecentAccomplishments(int $userId, int $limit = 4): array
    {
        $tenantId = App::tenantId();

        $rows = db_fetch_all("
            SELECT name, date, type FROM (

                SELECT a.name        AS name,
                       ua.earned_at  AS date,
                       'achievement' AS type
                FROM user_achievements ua
                JOIN achievements a ON ua.achievement_id = a.id
                WHERE ua.user_id = ? AND ua.tenant_id = ?

                UNION ALL

                SELECT s.name AS name,
                       COALESCE(sa.completed_at, sa.updated_at, sa.created_at) AS date,
                       'specialty' AS type
                FROM specialty_assignments sa
                JOIN specialties s ON sa.specialty_id = s.id
                WHERE sa.user_id = ? AND sa.tenant_id = ?
                  AND sa.status IN ('completed', 'approved')

                UNION ALL

                SELECT lp.name AS name,
                       COALESCE(upp.completed_at, upp.updated_at, upp.started_at) AS date,
                       CASE WHEN lc.type = 'class' THEN 'class' ELSE 'program' END AS type
                FROM user_program_progress upp
                JOIN learning_programs lp ON upp.program_id = lp.id
                LEFT JOIN learning_categories lc ON lp.category_id = lc.id
                WHERE upp.user_id = ? AND upp.tenant_id = ?
                  AND upp.status = 'completed'

            ) AS all_accomplishments
            ORDER BY date DESC
            LIMIT ?
        ", [$userId, $tenantId,
            $userId, $tenantId,
            $userId, $tenantId,
            $limit]);

        $iconMap  = [
            'achievement' => 'military_tech',
            'specialty'   => 'explore',
            'program'     => 'auto_stories',
            'class'       => 'school',
        ];
        $styleMap = [
            'achievement' => 'cyan',
            'specialty'   => 'green',
            'program'     => 'green',
            'class'       => 'purple',
        ];

        return array_map(fn($r) => [
            'name'  => $r['name'],
            'date'  => $r['date'],
            'type'  => $r['type'],
            'icon'  => $iconMap[$r['type']]  ?? 'star',
            'style' => $styleMap[$r['type']] ?? 'cyan',
        ], $rows);
    }

    /**
     * Get ALL earned items for the achievements gallery (no limit).
     * Combines: system achievements + completed specialties + completed programs/classes.
     * Returns [{name, date, type, icon, style}] ordered by date DESC.
     */
    public function getAllEarnedItems(int $userId): array
    {
        $tenantId = App::tenantId();

        // Fallback emoji per type when DB has no icon stored
        $fallbackIcon = ['achievement' => '🏆', 'specialty' => '🔍', 'program' => '📖', 'class' => '🎓'];
        $styleMap     = ['achievement' => 'cyan', 'specialty' => 'green', 'program' => 'green', 'class' => 'purple'];

        $rows = db_fetch_all("
            SELECT name, date, type, db_icon FROM (

                SELECT a.name                        AS name,
                       ua.earned_at                  AS date,
                       'achievement'                 AS type,
                       COALESCE(a.badge_url, '')     AS db_icon
                FROM user_achievements ua
                JOIN achievements a ON ua.achievement_id = a.id
                WHERE ua.user_id = ? AND ua.tenant_id = ?

                UNION ALL

                SELECT s.name                                                   AS name,
                       COALESCE(sa.completed_at, sa.updated_at, sa.created_at) AS date,
                       'specialty'                                              AS type,
                       COALESCE(s.badge_icon, '')                              AS db_icon
                FROM specialty_assignments sa
                JOIN specialties s ON sa.specialty_id = s.id
                WHERE sa.user_id = ? AND sa.tenant_id = ?
                  AND sa.status IN ('completed', 'approved')

                UNION ALL

                SELECT lp.name                                                      AS name,
                       COALESCE(upp.completed_at, upp.updated_at, upp.started_at)  AS date,
                       CASE WHEN lc.type = 'class' THEN 'class' ELSE 'program' END AS type,
                       COALESCE(lp.icon, '')                                        AS db_icon
                FROM user_program_progress upp
                JOIN learning_programs lp ON upp.program_id = lp.id
                LEFT JOIN learning_categories lc ON lp.category_id = lc.id
                WHERE upp.user_id = ? AND upp.tenant_id = ?
                  AND upp.status = 'completed'

            ) AS all_earned
            ORDER BY date DESC
        ", [$userId, $tenantId,
            $userId, $tenantId,
            $userId, $tenantId]);

        return array_map(function ($r) use ($fallbackIcon, $styleMap) {
            $type = $r['type'];
            $icon = ($r['db_icon'] !== '') ? $r['db_icon'] : ($fallbackIcon[$type] ?? '🏆');
            return [
                'name'  => $r['name'],
                'date'  => $r['date'],
                'type'  => $type,
                'icon'  => $icon,
                'style' => $styleMap[$type] ?? 'cyan',
            ];
        }, $rows);
    }

    /**
     * Count total goals available across achievements + specialties + programs for this tenant.
     */
    public function countAvailableItems(int $tenantId): int
    {
        $counts = db_fetch_one("
            SELECT
                (SELECT COUNT(*) FROM achievements        WHERE tenant_id = ?) +
                (SELECT COUNT(*) FROM specialties         WHERE tenant_id = ? AND status = 'active') +
                (SELECT COUNT(*) FROM learning_programs   WHERE tenant_id = ? AND status IN ('active', 'published'))
            AS total
        ", [$tenantId, $tenantId, $tenantId]);

        return (int) ($counts['total'] ?? 0);
    }

    /**
     * Get pending achievement notifications
     */
    public function getPendingAchievementNotifications(int $userId): array
    {
        $tenantId = App::tenantId();

        $achievements = db_fetch_all(
            "SELECT a.* FROM achievements a
             JOIN user_achievements ua ON a.id = ua.achievement_id
             WHERE ua.user_id = ? AND ua.tenant_id = ? AND ua.notified = 0",
            [$userId, $tenantId]
        );

        // Mark as notified
        if (!empty($achievements)) {
            db_query(
                "UPDATE user_achievements SET notified = 1 WHERE user_id = ? AND tenant_id = ? AND notified = 0",
                [$userId, $tenantId]
            );
        }

        return $achievements;
    }

    /**
     * Update user's daily login streak
     */
    public function updateStreak(int $userId): int
    {
        $tenantId = App::tenantId();

        try {
            $user = db_fetch_one(
                "SELECT current_streak, last_streak_date FROM users WHERE id = ? AND tenant_id = ?",
                [$userId, $tenantId]
            );
        } catch (\Exception $e) {
            // Check if columns exist, if not, create them (Auto-migration)
            if (strpos($e->getMessage(), 'Unknown column') !== false) {
                db_query("ALTER TABLE users ADD COLUMN current_streak INT UNSIGNED NOT NULL DEFAULT 0 AFTER level_id");
                db_query("ALTER TABLE users ADD COLUMN last_streak_date DATE NULL AFTER current_streak");
                
                $user = db_fetch_one(
                    "SELECT current_streak, last_streak_date FROM users WHERE id = ? AND tenant_id = ?",
                    [$userId, $tenantId]
                );
            } else {
                throw $e;
            }
        }

        if (!$user) {
            return 0;
        }

        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $lastDate = $user['last_streak_date'];
        $streak = (int)$user['current_streak'];

        if ($lastDate === $today) {
            return $streak; // Already updated today
        }

        if ($lastDate === $yesterday) {
            $streak++; // Continued streak
        } else {
            $streak = 1; // Streak broken or new
        }

        db_update('users', [
            'current_streak' => $streak,
            'last_streak_date' => $today
        ], 'id = ?', [$userId]);

        return $streak;
    }
}
