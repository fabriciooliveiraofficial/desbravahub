<?php
/**
 * Notification Controller
 * 
 * Handles notification API endpoints.
 */

namespace App\Controllers;

use App\Core\App;
use App\Services\NotificationService;

class NotificationController
{
    private NotificationService $service;

    public function __construct()
    {
        $this->service = new NotificationService();
    }

    /**
     * Get user's notifications
     */
    public function index(): void
    {
        $user = App::user();
        $limit = (int) ($_GET['limit'] ?? 20);

        $notifications = $this->service->getAll($user['id'], min($limit, 100));
        $unreadCount = $this->service->getUnreadCount($user['id']);

        $this->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Get unread notifications (for polling/toast)
     */
    public function unread(): void
    {
        $user = App::user();

        $notifications = $this->service->getUnread($user['id']);
        $count = count($notifications);

        $this->json([
            'notifications' => $notifications,
            'count' => $count,
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markRead(array $params): void
    {
        $notificationId = (int) $params['id'];
        $this->service->markAsRead($notificationId);

        $this->json(['success' => true]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllRead(): void
    {
        $user = App::user();
        $this->service->markAllAsRead($user['id']);

        $this->json(['success' => true]);
    }

    /**
     * Update notification preferences
     */
    public function updatePreferences(): void
    {
        $user = App::user();
        $type = $_POST['type'] ?? '';
        $channels = $_POST['channels'] ?? [];

        if (is_string($channels)) {
            $channels = json_decode($channels, true) ?? [];
        }

        if (empty($type)) {
            $this->jsonError('Notification type required', 400);
            return;
        }

        $this->service->updatePreferences($user['id'], $type, $channels);

        $this->json(['success' => true]);
    }

    /**
     * Admin: Send broadcast notification
     */
    public function broadcast(): void
    {
        if (!can('notifications.broadcast')) {
            $this->jsonError('Permissão negada', 403);
            return;
        }

        $title = $_POST['title'] ?? '';
        $message = $_POST['message'] ?? '';
        $channels = $_POST['channels'] ?? ['toast'];

        if (empty($title) || empty($message)) {
            $this->jsonError('Title and message required', 400);
            return;
        }

        $ids = $this->service->broadcast('broadcast', $title, $message, [
            'channels' => $channels,
            'priority' => $_POST['priority'] ?? 'normal',
        ]);

        $this->json([
            'success' => true,
            'sent_count' => count($ids),
        ]);
    }

    /**
     * Subscribe to push notifications
     */
    public function subscribe(): void
    {
        $user = App::user();
        $raw = file_get_contents('php://input');
        $input = json_decode($raw, true);

        // Debug log
        file_put_contents(BASE_PATH . '/storage/logs/push.log', "[".date('Y-m-d H:i:s')."] User: {$user['id']}, IP: {$_SERVER['REMOTE_ADDR']}, Body: {$raw}\n", FILE_APPEND);

        if (!$input || empty($input['endpoint'])) {
            $this->jsonError('Subscription data required', 400);
            return;
        }

        $tenantId = App::tenantId();

        try {
            // Check if subscription already exists
            $existing = db_fetch_one(
                "SELECT id FROM push_subscriptions WHERE endpoint = ? AND user_id = ?",
                [$input['endpoint'], $user['id']]
            );

            $data = [
                'tenant_id' => $tenantId,
                'user_id' => $user['id'],
                'endpoint' => $input['endpoint'],
                'p256dh' => $input['keys']['p256dh'] ?? '',
                'auth' => $input['keys']['auth'] ?? '',
                'browser' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                'device_type' => $this->detectDeviceType(),
            ];

            if ($existing) {
                db_update('push_subscriptions', $data, 'id = ?', [$existing['id']]);
            } else {
                db_insert('push_subscriptions', $data);
            }

            // Auto-enable push in preferences if subscribing
            $this->service->updatePreferences($user['id'], 'all', ['push']);

            $this->json(['success' => true]);

        } catch (\Exception $e) {
            file_put_contents(BASE_PATH . '/storage/logs/push.log', "[".date('Y-m-d H:i:s')."] ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
            $this->jsonError('Erro ao processar assinatura', 500);
        }
    }

    /**
     * Unsubscribe from push notifications
     */
    public function unsubscribe(): void
    {
        $user = App::user();
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input || empty($input['endpoint'])) {
            $this->jsonError('Endpoint required', 400);
            return;
        }

        db_query(
            "DELETE FROM push_subscriptions WHERE endpoint = ? AND user_id = ?",
            [$input['endpoint'], $user['id']]
        );

        $this->json(['success' => true]);
    }

    /**
     * Test push delivery to current user
     */
    public function testPush(): void
    {
        $user = App::user();
        
        // Count subscriptions
        $count = (int) db_fetch_column("SELECT COUNT(*) FROM push_subscriptions WHERE user_id = ?", [$user['id']]);

        $this->service->send($user['id'], 'test_push', 'Teste DesbravaHub', 'As notificações push estão funcionando com sucesso! 🎯', [
            'channels' => ['push']
        ]);

        $this->json([
            'success' => true, 
            'message' => 'Notificação enviada',
            'subscriptions' => $count
        ]);
    }

    private function detectDeviceType(): string
    {
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        if (preg_match('/(tablet|ipad|playbook|silk)|(android(?!.*mobi))/i', $ua)) {
            return 'tablet';
        }
        if (preg_match('/(Mobile|Android|iPhone|IEMobile|BlackBerry|Kindle|Opera Mini)/i', $ua)) {
            return 'mobile';
        }
        return 'desktop';
    }

    private function json(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    private function jsonError(string $message, int $code = 400): void
    {
        http_response_code($code);
        $this->json(['error' => $message]);
    }
}
