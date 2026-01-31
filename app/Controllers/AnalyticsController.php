<?php
/**
 * Analytics Controller
 * 
 * Admin dashboard for learning analytics:
 * - Completion rates by category
 * - Drop-off analysis
 * - Most rejected questions
 * - Progress overview
 */

namespace App\Controllers;

use App\Core\App;

class AnalyticsController
{
    private function requireAdmin(): void
    {
        $user = App::user();
        $role = $user['role_name'] ?? '';

        if (!in_array($role, ['admin', 'director'])) {
            error_log("AnalyticsController::requireAdmin - Access Denied: User " . ($user['id'] ?? 'unknown') . " with role $role tried to access analytics.");
            header('HTTP/1.0 403 Forbidden');
            echo 'Acesso negado';
            exit;
        }
    }

    /**
     * Analytics dashboard
     */
    public function index(): void
    {
        $this->requireAdmin();

        $tenant = App::tenant();

        // Overview stats
        $stats = [
            'total_programs' => db_fetch_column(
                "SELECT COUNT(*) FROM learning_programs WHERE tenant_id = ? AND status = 'published'",
                [$tenant['id']]
            ),
            'total_assignments' => db_fetch_column(
                "SELECT COUNT(*) FROM user_program_progress WHERE tenant_id = ?",
                [$tenant['id']]
            ),
            'completed' => db_fetch_column(
                "SELECT COUNT(*) FROM user_program_progress WHERE tenant_id = ? AND status = 'completed'",
                [$tenant['id']]
            ),
            'in_progress' => db_fetch_column(
                "SELECT COUNT(*) FROM user_program_progress WHERE tenant_id = ? AND status = 'in_progress'",
                [$tenant['id']]
            ),
            'pending_approvals' => db_fetch_column(
                "SELECT COUNT(*) FROM user_step_responses usr
                 JOIN user_program_progress upp ON usr.progress_id = upp.id
                 WHERE upp.tenant_id = ? AND usr.status = 'submitted'",
                [$tenant['id']]
            )
        ];

        // Average completion rate
        $avgCompletion = db_fetch_column(
            "SELECT ROUND(AVG(progress_percent), 1) FROM user_program_progress WHERE tenant_id = ?",
            [$tenant['id']]
        ) ?? 0;

        // Completion rate by category
        $categoryStats = db_fetch_all("
            SELECT 
                c.id, c.name, c.color, c.icon,
                COUNT(DISTINCT p.id) as program_count,
                COUNT(DISTINCT upp.id) as assignment_count,
                SUM(CASE WHEN upp.status = 'completed' THEN 1 ELSE 0 END) as completed_count,
                ROUND(AVG(upp.progress_percent), 1) as avg_progress
            FROM learning_categories c
            LEFT JOIN learning_programs p ON p.category_id = c.id AND p.tenant_id = c.tenant_id
            LEFT JOIN user_program_progress upp ON upp.program_id = p.id
            WHERE c.tenant_id = ? AND c.status = 'active'
            GROUP BY c.id, c.name, c.color, c.icon
            ORDER BY assignment_count DESC
        ", [$tenant['id']]);

        // Top programs by completion
        $topPrograms = db_fetch_all("
            SELECT 
                p.id, p.name, p.icon, p.type,
                COUNT(upp.id) as total_assigns,
                SUM(CASE WHEN upp.status = 'completed' THEN 1 ELSE 0 END) as completions,
                ROUND(AVG(upp.progress_percent), 1) as avg_progress
            FROM learning_programs p
            LEFT JOIN user_program_progress upp ON upp.program_id = p.id
            WHERE p.tenant_id = ? AND p.status = 'published'
            GROUP BY p.id, p.name, p.icon, p.type
            HAVING total_assigns > 0
            ORDER BY completions DESC, avg_progress DESC
            LIMIT 10
        ", [$tenant['id']]);

        // Most rejected steps (problem areas)
        $problemSteps = db_fetch_all("
            SELECT 
                ps.id, ps.title,
                p.name as program_name, p.icon as program_icon,
                COUNT(usr.id) as rejection_count
            FROM user_step_responses usr
            JOIN program_steps ps ON usr.step_id = ps.id
            JOIN program_versions pv ON ps.version_id = pv.id
            JOIN learning_programs p ON pv.program_id = p.id
            JOIN user_program_progress upp ON usr.progress_id = upp.id
            WHERE upp.tenant_id = ? AND usr.status = 'rejected'
            GROUP BY ps.id, ps.title, p.name, p.icon
            ORDER BY rejection_count DESC
            LIMIT 5
        ", [$tenant['id']]);

        // Recent completions
        $recentCompletions = db_fetch_all("
            SELECT 
                upp.completed_at,
                u.name as user_name, u.profile_picture,
                p.name as program_name, p.icon as program_icon
            FROM user_program_progress upp
            JOIN users u ON upp.user_id = u.id
            JOIN learning_programs p ON upp.program_id = p.id
            WHERE upp.tenant_id = ? AND upp.status = 'completed'
            ORDER BY upp.completed_at DESC
            LIMIT 10
        ", [$tenant['id']]);

        require BASE_PATH . '/views/admin/analytics/index.php';
    }

    private function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}
