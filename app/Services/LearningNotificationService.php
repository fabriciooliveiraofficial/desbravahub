<?php
/**
 * Learning Notification Service
 * 
 * Triggers notifications for learning engine events.
 * Unified to use NotificationService for all channels (Toast, Push, Email).
 */

namespace App\Services;

class LearningNotificationService
{
    /**
     * Notify user about program assignment
     */
    public static function programAssigned(int $tenantId, int $userId, array $program, string $tenantSlug): void
    {
        $service = new NotificationService();
        $service->send(
            $userId,
            'program_assigned',
            '📚 Novo programa atribuído!',
            "Você foi atribuído a: {$program['icon']} {$program['name']}",
            [
                'data' => [
                    'link' => base_url("{$tenantSlug}/aprendizado/{$program['id']}"),
                    'icon' => $program['icon'] ?? '📚'
                ],
                'channels' => ['toast', 'push']
            ]
        );
    }

    /**
     * Notify admins about step submission
     */
    public static function stepSubmitted(int $tenantId, array $user, array $step, array $program, string $tenantSlug): void
    {
        $admins = db_fetch_all("
            SELECT u.id FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE u.tenant_id = ? AND r.name IN ('admin', 'director', 'counselor', 'instructor')
        ", [$tenantId]);

        $service = new NotificationService();
        foreach ($admins as $admin) {
            $service->send(
                $admin['id'],
                'step_submitted',
                '📋 Nova submissão para aprovar',
                "{$user['name']} completou: {$step['title']} ({$program['name']})",
                [
                    'data' => [
                        'link' => base_url("{$tenantSlug}/admin/aprovacoes"),
                        'icon' => '📋'
                    ],
                    'channels' => ['toast', 'push']
                ]
            );
        }
    }

    /**
     * Notify user about step approval
     */
    public static function stepApproved(int $tenantId, int $userId, array $step, array $program, string $tenantSlug): void
    {
        $programIcon = $program['icon'] ?? '📘';
        $title = '✅ Requisito aprovado!';
        $message = "Seu requisito \"{$step['title']}\" no programa {$programIcon} {$program['name']} foi aprovado! Continue avançando.";

        $service = new NotificationService();
        $service->send(
            $userId,
            'step_approved',
            $title,
            $message,
            [
                'data' => [
                    'link' => base_url("{$tenantSlug}/aprendizado/{$program['id']}"),
                    'url' => base_url("{$tenantSlug}/aprendizado/{$program['id']}"),
                    'icon' => '✅',
                    'program_name' => $program['name'],
                    'step_name' => $step['title']
                ],
                'channels' => ['toast', 'push']
            ]
        );
    }

    /**
     * Notify user about step rejection
     */
    public static function stepRejected(int $tenantId, int $userId, array $step, array $program, string $feedback, string $tenantSlug): void
    {
        $programIcon = $program['icon'] ?? '📘';
        $title = '❌ Revisão necessária';
        $message = "Seu requisito \"{$step['title']}\" no programa {$programIcon} {$program['name']} precisa de ajustes.";
        
        if ($feedback) {
            // Truncate feedback for push notification readability
            $shortFeedback = mb_strlen($feedback) > 100 ? mb_substr($feedback, 0, 100) . '...' : $feedback;
            $message .= " Feedback: {$shortFeedback}";
        }

        $service = new NotificationService();
        $service->send(
            $userId,
            'step_rejected',
            $title,
            $message,
            [
                'data' => [
                    'link' => base_url("{$tenantSlug}/aprendizado/{$program['id']}"),
                    'url' => base_url("{$tenantSlug}/aprendizado/{$program['id']}"),
                    'icon' => '❌',
                    'program_name' => $program['name'],
                    'step_name' => $step['title']
                ],
                'channels' => ['toast', 'push']
            ]
        );
    }

    /**
     * Notify user about program completion
     */
    public static function programCompleted(int $tenantId, int $userId, array $program, string $tenantSlug): void
    {
        $service = new NotificationService();
        $service->send(
            $userId,
            'program_completed',
            '🎉 Programa concluído!',
            "Parabéns! Você completou {$program['icon']} {$program['name']}!",
            [
                'data' => [
                    'link' => base_url("{$tenantSlug}/aprendizado/{$program['id']}"),
                    'icon' => '🎉'
                ],
                'channels' => ['toast', 'push']
            ]
        );
    }
}
