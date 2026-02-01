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
     * Get leaderboard
     */
    public function getLeaderboard(int $limit = 10): array
    {
        $tenantId = App::tenantId();

        return db_fetch_all(
            "SELECT u.id, u.name, u.avatar_url, u.xp_points, l.level_number, l.name as level_name
             FROM users u
             LEFT JOIN levels l ON u.level_id = l.id
             WHERE u.tenant_id = ? AND u.status = 'active' AND u.deleted_at IS NULL
             ORDER BY u.xp_points DESC
             LIMIT ?",
            [$tenantId, $limit]
        );
    }

    /**
     * Check and award achievements
     */
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
     * Get user's achievements
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
