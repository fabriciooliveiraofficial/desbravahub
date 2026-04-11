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
    ): array {
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

        // Dispatch to channels and track success
        $results = $this->dispatchToChannels($notificationId, $userId, $channels, $title, $message, $data, $priority);

        return [
            'id' => $notificationId,
            'results' => $results
        ];
    }

    /**
     * Broadcast to all users in tenant.
     *
     * Uses deferred push flushing so all notifications are sent in a single
     * batch instead of one HTTP round-trip per user.
     */
    public function broadcast(string $type, string $title, string $message, array $options = []): array
    {
        $tenantId = App::tenantId();
        $ids = [];
        $stats = [
            'total_users' => 0,
            'email' => ['sent' => 0, 'failed' => 0],
            'push' => ['sent' => 0, 'failed' => 0],
            'toast' => ['sent' => 0]
        ];

        // Get all active users
        $users = db_fetch_all(
            "SELECT id FROM users WHERE tenant_id = ? AND status = 'active' AND deleted_at IS NULL",
            [$tenantId]
        );

        $stats['total_users'] = count($users);

        // Enable deferred push mode so flush() happens once at the end
        $webPush = WebPushService::getInstance();
        $webPush->setDeferred(true);

        try {
            foreach ($users as $user) {
                $result = $this->send($user['id'], $type, $title, $message, $options);
                $ids[] = $result['id'];
                
                // Aggregate stats
                foreach ($result['results'] as $channel => $success) {
                    if ($success) {
                        $stats[$channel]['sent']++;
                    } else {
                        $stats[$channel]['failed']++;
                    }
                }
            }

            // Flush all queued push notifications in one batch
            $pushStats = $webPush->flushAll();
            $stats['push']['sent']    = $pushStats['success'];
            $stats['push']['failed']  = $pushStats['failed'];
            $stats['push']['expired'] = $pushStats['expired'] ?? 0;
        } finally {
            $webPush->setDeferred(false);
        }

        return [
            'notification_ids' => $ids,
            'stats' => $stats
        ];
    }

    /**
     * Get user's unread notifications.
     *
     * For user-specific rows (user_id IS NOT NULL): checks notifications.read_at.
     * For broadcast rows (user_id IS NULL): checks notification_reads so each user
     * gets their own independent read state.
     */
    public function getUnread(int $userId, int $limit = 20): array
    {
        $tenantId = App::tenantId();

        return db_fetch_all(
            "SELECT n.* FROM notifications n
             LEFT JOIN notification_reads nr ON nr.notification_id = n.id AND nr.user_id = ?
             WHERE n.tenant_id = ?
               AND (n.user_id = ? OR n.user_id IS NULL)
               AND n.read_at IS NULL
               AND nr.id IS NULL
               AND n.channels LIKE '%\"toast\"%'
             ORDER BY n.created_at DESC
             LIMIT ?",
            [$userId, $tenantId, $userId, $limit]
        );
    }

    /**
     * Get all notifications for user
     */
    public function getAll(int $userId, int $limit = 50): array
    {
        $tenantId = App::tenantId();

        // Strictly return only UNREAD notifications (specific or broadcast)
        // This ensures the "Bell" center works as a persistent task/alert list.
        return db_fetch_all(
            "SELECT n.*, 1 as is_unread
             FROM notifications n
             LEFT JOIN notification_reads nr ON nr.notification_id = n.id AND nr.user_id = ?
             WHERE n.tenant_id = ? 
               AND (n.user_id = ? OR n.user_id IS NULL)
               AND n.channels LIKE '%\"toast\"%'
               AND (
                   (n.user_id IS NOT NULL AND n.read_at IS NULL) OR
                   (n.user_id IS NULL AND nr.id IS NULL)
               )
             ORDER BY n.created_at DESC
             LIMIT ?",
            [$userId, $tenantId, $userId, $limit]
        );
    }

    /**
     * Mark notification as read.
     *
     * Broadcasts (user_id IS NULL) are tracked per-user via notification_reads.
     * User-specific notifications update notifications.read_at directly.
     */
    public function markAsRead(int $notificationId, int $userId): void
    {
        $notif = db_fetch_one("SELECT user_id FROM notifications WHERE id = ?", [$notificationId]);
        if (!$notif) return;

        if ($notif['user_id'] === null) {
            db_query(
                "INSERT IGNORE INTO notification_reads (notification_id, user_id) VALUES (?, ?)",
                [$notificationId, $userId]
            );
        } else {
            db_update('notifications', ['read_at' => date('Y-m-d H:i:s')], 'id = ?', [$notificationId]);
        }
    }

    /**
     * Mark all as read for user.
     *
     * User-specific notifications: update read_at on the row.
     * Broadcast notifications: insert into notification_reads for this user.
     */
    /**
     * Mark multiple notifications as read for a specific user
     */
    public function markMultipleAsRead(array $ids, int $userId): void
    {
        if (empty($ids)) return;
        
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        
        // 1. Process individual notifications directly
        db_query(
            "UPDATE notifications SET read_at = NOW() 
             WHERE id IN ($placeholders) AND user_id = ? AND read_at IS NULL",
            [...$ids, $userId]
        );
        
        // 2. Process broadcast notifications (shared)
        $broadcastIds = db_fetch_all(
            "SELECT id FROM notifications WHERE id IN ($placeholders) AND user_id IS NULL",
            $ids
        );

        foreach ($broadcastIds as $row) {
            $id = $row['id'];
            $exists = db_fetch_column(
                "SELECT id FROM notification_reads WHERE notification_id = ? AND user_id = ?",
                [$id, $userId]
            );

            if (!$exists) {
                db_insert('notification_reads', [
                    'notification_id' => $id,
                    'user_id' => $userId,
                    'read_at' => date('Y-m-d H:i:s')
                ]);
            }
        }
    }

    public function markAllAsRead(int $userId): void
    {
        $tenantId = App::tenantId();

        // User-specific notifications
        db_query(
            "UPDATE notifications SET read_at = NOW()
             WHERE tenant_id = ? AND user_id = ? AND read_at IS NULL",
            [$tenantId, $userId]
        );

        // Broadcast notifications not yet read by this user
        db_query(
            "INSERT IGNORE INTO notification_reads (notification_id, user_id)
             SELECT n.id, ? FROM notifications n
             LEFT JOIN notification_reads nr ON nr.notification_id = n.id AND nr.user_id = ?
             WHERE n.tenant_id = ? AND n.user_id IS NULL AND n.read_at IS NULL AND nr.id IS NULL",
            [$userId, $userId, $tenantId]
        );
    }

    /**
     * Get unread count.
     * Uses the same broadcast-aware logic as getUnread().
     */
    public function getUnreadCount(int $userId): int
    {
        $tenantId = App::tenantId();

        return (int) db_fetch_column(
            "SELECT COUNT(*) FROM notifications n
             LEFT JOIN notification_reads nr ON nr.notification_id = n.id AND nr.user_id = ?
             WHERE n.tenant_id = ?
               AND (n.user_id = ? OR n.user_id IS NULL)
               AND n.read_at IS NULL
               AND nr.id IS NULL
               AND n.channels LIKE '%\"toast\"%'",
            [$userId, $tenantId, $userId]
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

        // 1. Try specific preference
        $prefs = db_fetch_one(
            "SELECT * FROM user_notification_preferences WHERE user_id = ? AND notification_type = ?",
            [$userId, $type]
        );

        // 2. Fallback to 'all' preference if specific not found
        if (!$prefs && $type !== 'all') {
            $prefs = db_fetch_one(
                "SELECT * FROM user_notification_preferences WHERE user_id = ? AND notification_type = 'all'",
                [$userId]
            );
        }

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
    ): array {
        $results = [];

        // Mark as sent in DB
        db_update('notifications', ['sent_at' => date('Y-m-d H:i:s')], 'id = ?', [$notificationId]);

        // Email channel
        if (in_array('email', $channels) && $userId) {
            $results['email'] = $this->sendEmail($userId, $title, $message, $data);
        }

        // Push channel
        if (in_array('push', $channels) && $userId) {
            // Include notification_id so client can mark as read on receipt
            $pushData = array_merge($data ?? [], ['notification_id' => $notificationId, 'priority' => $priority]);
            $results['push'] = $this->queuePush($userId, $title, $message, $pushData);
        }

        // Toast: always considered "sent" to DB for retrieval
        if (in_array('toast', $channels)) {
            $results['toast'] = true;
        }

        return $results;
    }

    /**
     * Send email notification return status
     */
    private function sendEmail(int $userId, string $title, string $message, ?array $data): bool
    {
        $user = db_fetch_one("SELECT email, name FROM users WHERE id = ?", [$userId]);
        if (!$user)
            return false;

        return $this->emailService->send(
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
    private function queuePush(int $userId, string $title, string $message, ?array $data): bool
    {
        try {
            $webPush = WebPushService::getInstance();
            return $webPush->sendToUser($userId, $title, $message, $data);
        } catch (\Exception $e) {
            error_log("Push queue error: " . $e->getMessage());
            return false;
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
