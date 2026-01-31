<?php
/**
 * Notification Service
 * 
 * Handles hybrid notifications: toast, push, and email.
 * One notification → multiple delivery channels.
 */

namespace App\Services;

use App\Core\App;

class NotificationService
{
    private EmailService $emailService;

    public function __construct()
    {
        $this->emailService = EmailService::getInstance();
    }

    /**
     * Send a notification
     * 
     * @param int|null $userId Null for broadcast to all tenant users
     * @param string $type Notification type
     * @param string $title Notification title
     * @param string $message Notification message
     * @param array $options Additional options (channels, priority, data)
     */
    public function send(
        ?int $userId,
        string $type,
        string $title,
        string $message,
        array $options = []
    ): int {
        $tenantId = App::tenantId();

        // Default channels based on user preferences
        $channels = $options['channels'] ?? $this->getDefaultChannels($userId, $type);
        $priority = $options['priority'] ?? 'normal';
        $data = $options['data'] ?? null;

        // Create notification record
        $notificationId = db_insert('notifications', [
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data ? json_encode($data) : null,
            'channels' => json_encode($channels),
            'priority' => $priority,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // Dispatch to channels
        $this->dispatchToChannels($notificationId, $userId, $channels, $title, $message, $data, $priority);

        return $notificationId;
    }

    /**
     * Broadcast to all users in tenant
     */
    public function broadcast(string $type, string $title, string $message, array $options = []): array
    {
        $tenantId = App::tenantId();
        $ids = [];

        // Get all active users
        $users = db_fetch_all(
            "SELECT id FROM users WHERE tenant_id = ? AND status = 'active' AND deleted_at IS NULL",
            [$tenantId]
        );

        foreach ($users as $user) {
            $ids[] = $this->send($user['id'], $type, $title, $message, $options);
        }

        return $ids;
    }

    /**
     * Get user's unread notifications
     */
    public function getUnread(int $userId, int $limit = 20): array
    {
        $tenantId = App::tenantId();

        return db_fetch_all(
            "SELECT * FROM notifications 
             WHERE tenant_id = ? AND (user_id = ? OR user_id IS NULL) AND read_at IS NULL
             ORDER BY created_at DESC
             LIMIT ?",
            [$tenantId, $userId, $limit]
        );
    }

    /**
     * Get all notifications for user
     */
    public function getAll(int $userId, int $limit = 50): array
    {
        $tenantId = App::tenantId();

        return db_fetch_all(
            "SELECT * FROM notifications 
             WHERE tenant_id = ? AND (user_id = ? OR user_id IS NULL)
             ORDER BY created_at DESC
             LIMIT ?",
            [$tenantId, $userId, $limit]
        );
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(int $notificationId): void
    {
        db_update('notifications', [
            'read_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$notificationId]);
    }

    /**
     * Mark all as read for user
     */
    public function markAllAsRead(int $userId): void
    {
        $tenantId = App::tenantId();

        db_query(
            "UPDATE notifications SET read_at = NOW() 
             WHERE tenant_id = ? AND (user_id = ? OR user_id IS NULL) AND read_at IS NULL",
            [$tenantId, $userId]
        );
    }

    /**
     * Get unread count
     */
    public function getUnreadCount(int $userId): int
    {
        $tenantId = App::tenantId();

        return (int) db_fetch_column(
            "SELECT COUNT(*) FROM notifications 
             WHERE tenant_id = ? AND (user_id = ? OR user_id IS NULL) AND read_at IS NULL",
            [$tenantId, $userId]
        );
    }

    /**
     * Clear all notifications for user
     */
    public function clearAll(int $userId): void
    {
        $tenantId = App::tenantId();

        db_query(
            "DELETE FROM notifications 
             WHERE tenant_id = ? AND (user_id = ? OR user_id IS NULL)",
            [$tenantId, $userId]
        );
    }

    /**
     * Get default channels based on user preferences
     */
    private function getDefaultChannels(?int $userId, string $type): array
    {
        if (!$userId) {
            return ['toast', 'email'];
        }

        $prefs = db_fetch_one(
            "SELECT * FROM user_notification_preferences WHERE user_id = ? AND notification_type = ?",
            [$userId, $type]
        );

        if (!$prefs) {
            // No explicit preferences - check if user has push subscription
            $hasSubscription = db_fetch_column(
                "SELECT COUNT(*) FROM push_subscriptions WHERE user_id = ?",
                [$userId]
            );
            
            // Default: toast always + push if subscribed
            return $hasSubscription ? ['toast', 'push'] : ['toast'];
        }

        $channels = [];
        if ($prefs['channel_toast'])
            $channels[] = 'toast';
        if ($prefs['channel_push'])
            $channels[] = 'push';
        if ($prefs['channel_email'])
            $channels[] = 'email';

        return $channels ?: ['toast'];
    }

    /**
     * Dispatch notification to channels
     */
    private function dispatchToChannels(
        int $notificationId,
        ?int $userId,
        array $channels,
        string $title,
        string $message,
        ?array $data,
        string $priority
    ): void {
        // Mark as sent
        db_update('notifications', ['sent_at' => date('Y-m-d H:i:s')], 'id = ?', [$notificationId]);

        // Email channel
        if (in_array('email', $channels) && $userId) {
            $this->sendEmail($userId, $title, $message, $data);
        }

        // Push channel (structure ready, implementation in future)
        if (in_array('push', $channels) && $userId) {
            $this->queuePush($userId, $title, $message, $data);
        }

        // Toast is handled client-side via polling or websocket
    }

    /**
     * Send email notification
     */
    private function sendEmail(int $userId, string $title, string $message, ?array $data): void
    {
        $user = db_fetch_one("SELECT email, name FROM users WHERE id = ?", [$userId]);
        if (!$user)
            return;

        $this->emailService->send(
            $user['email'],
            $title,
            $this->buildEmailContent($user['name'], $title, $message, $data)
        );
    }

    /**
     * Build email HTML content
     */
    private function buildEmailContent(string $userName, string $title, string $message, ?array $data): string
    {
        $appName = config('app.name');
        $link = !empty($data['link']) ? $data['link'] : base_url();

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(90deg, #00d9ff, #00ff88); padding: 20px; border-radius: 8px 8px 0 0; }
        .header h1 { color: #1a1a2e; margin: 0; font-size: 24px; }
        .content { background: #f9f9f9; padding: 20px; border-radius: 0 0 8px 8px; }
        .btn { display: inline-block; background: #00d9ff; color: #1a1a2e; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold; }
        .footer { text-align: center; margin-top: 20px; color: #888; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>$appName</h1>
        </div>
        <div class="content">
            <p>Olá, <strong>$userName</strong>!</p>
            <h2>$title</h2>
            <p>$message</p>
            <p><a href="$link" class="btn">Ver Detalhes</a></p>
        </div>
        <div class="footer">
            <p>Este email foi enviado por $appName</p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Queue push notification
     */
    private function queuePush(int $userId, string $title, string $message, ?array $data): void
    {
        try {
            $webPush = WebPushService::getInstance();
            $webPush->sendToUser($userId, $title, $message, $data);
        } catch (\Exception $e) {
            error_log("Push queue error: " . $e->getMessage());
        }
    }

    /**
     * Update user notification preferences
     */
    public function updatePreferences(int $userId, string $type, array $channels): void
    {
        $tenantId = App::tenantId();

        $existing = db_fetch_one(
            "SELECT id FROM user_notification_preferences WHERE user_id = ? AND notification_type = ?",
            [$userId, $type]
        );

        $data = [
            'channel_toast' => in_array('toast', $channels) ? 1 : 0,
            'channel_push' => in_array('push', $channels) ? 1 : 0,
            'channel_email' => in_array('email', $channels) ? 1 : 0,
        ];

        if ($existing) {
            db_update('user_notification_preferences', $data, 'id = ?', [$existing['id']]);
        } else {
            db_insert('user_notification_preferences', array_merge($data, [
                'user_id' => $userId,
                'tenant_id' => $tenantId,
                'notification_type' => $type,
            ]));
        }
    }
}
