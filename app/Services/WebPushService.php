<?php
/**
 * WebPush Service
 * 
 * Handles sending push notifications via Web Push Protocol (VAPID).
 */

namespace App\Services;

use App\Core\App;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class WebPushService
{
    private WebPush $webPush;
    private static ?WebPushService $instance = null;

    private function __construct()
    {
        $auth = [
            'VAPID' => [
                'subject' => 'mailto:admin@desbravahub.com.br',
                'publicKey' => config('vapid.public_key'),
                'privateKey' => config('vapid.private_key'),
            ],
        ];

        $this->webPush = new WebPush($auth);
    }

    public static function getInstance(): WebPushService
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Send push notification to a user
     */
    public function sendToUser(int $userId, string $title, string $message, ?array $data = []): void
    {
        // Get user subscriptions
        $subscriptions = db_fetch_all(
            "SELECT * FROM push_subscriptions WHERE user_id = ?",
            [$userId]
        );

        if (empty($subscriptions)) {
            return;
        }

        $tenant = App::tenant();
        
        // Use deep link URL from data if available, otherwise default to notifications page
        $defaultUrl = $tenant ? base_url($tenant['slug'] . '/notificacoes') : '/';
        $deepLinkUrl = $data['url'] ?? $data['link'] ?? $defaultUrl;

        $payload = json_encode([
            'title' => $title,
            'body' => $message,
            'url' => $deepLinkUrl,
            'data' => $data
        ]);

        foreach ($subscriptions as $sub) {
            $subscription = Subscription::create([
                'endpoint' => $sub['endpoint'],
                'publicKey' => $sub['p256dh'],
                'authToken' => $sub['auth'],
            ]);

            $this->webPush->queueNotification($subscription, $payload);
        }

        // Send all queued notifications
        foreach ($this->webPush->flush() as $report) {
            $endpoint = $report->getRequest()->getUri()->__toString();
            $logMsg = "[".date('Y-m-d H:i:s')."] Endpoint: " . substr($endpoint, 0, 50) . "... ";

            if ($report->isSuccess()) {
                file_put_contents(BASE_PATH . '/storage/logs/push.log', $logMsg . "SUCCESS\n", FILE_APPEND);
            } else {
                // If notification failed because subscription expired or is invalid
                if ($report->isSubscriptionExpired()) {
                    db_query("DELETE FROM push_subscriptions WHERE endpoint = ?", [$endpoint]);
                    file_put_contents(BASE_PATH . '/storage/logs/push.log', $logMsg . "EXPIRED (Deleted)\n", FILE_APPEND);
                } else {
                    $reason = $report->getReason();
                    file_put_contents(BASE_PATH . '/storage/logs/push.log', $logMsg . "FAILED: {$reason}\n", FILE_APPEND);
                }
                error_log("Push failure for {$endpoint}: {$report->getReason()}");
            }
        }
    }
}
