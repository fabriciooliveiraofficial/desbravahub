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

    /**
     * When true, sendToUser() queues but does NOT flush.
     * Call flushAll() explicitly after all users have been queued.
     */
    private bool $deferred = false;

    /** Tracks how many notifications are queued (for batch flushing). */
    private int $queuedCount = 0;

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

    // ── Deferred mode ────────────────────────────────────────

    /**
     * Enable/disable deferred mode.
     * In deferred mode, sendToUser() only queues — call flushAll() to send.
     */
    public function setDeferred(bool $deferred): void
    {
        $this->deferred = $deferred;
        if (!$deferred) {
            $this->queuedCount = 0;
        }
    }

    public function isDeferred(): bool
    {
        return $this->deferred;
    }

    // ── Core methods ────────────────────────────────────────

    /**
     * Send push notification to a user.
     *
     * In normal mode: queues + flushes immediately.
     * In deferred mode: queues only — caller must call flushAll().
     */
    public function sendToUser(int $userId, string $title, string $message, ?array $data = []): void
    {
        $this->queueForUser($userId, $title, $message, $data);

        if (!$this->deferred) {
            $this->flushAll();
        }
    }

    /**
     * Queue push notifications for a user WITHOUT flushing.
     */
    public function queueForUser(int $userId, string $title, string $message, ?array $data = []): void
    {
        $subscriptions = db_fetch_all(
            "SELECT * FROM push_subscriptions WHERE user_id = ?",
            [$userId]
        );

        if (empty($subscriptions)) {
            return;
        }

        $tenant = App::tenant();

        $defaultUrl = $tenant ? base_url($tenant['slug'] . '/notificacoes') : '/';
        $deepLinkUrl = $data['url'] ?? $data['link'] ?? $defaultUrl;

        $payload = json_encode([
            'title'           => $title,
            'body'            => $message,
            'url'             => $deepLinkUrl,
            'type'            => $data['type'] ?? null,
            'priority'        => $data['priority'] ?? 'normal',
            'notification_id' => $data['notification_id'] ?? null, // hoisted for client-side deduplication
            'user_id'         => $userId, // used by client to discard cross-user broadcasts
            'data'            => $data,
        ]);

        foreach ($subscriptions as $sub) {
            $subscription = Subscription::create([
                'endpoint' => $sub['endpoint'],
                'publicKey' => $sub['p256dh'],
                'authToken' => $sub['auth'],
            ]);

            $this->webPush->queueNotification($subscription, $payload);
            $this->queuedCount++;
        }
    }

    /**
     * Flush all queued notifications and process delivery reports.
     *
     * Safe to call when the queue is empty — returns immediately.
     *
     * @return array{success: int, failed: int, expired: int}
     */
    public function flushAll(): array
    {
        $stats = ['success' => 0, 'failed' => 0, 'expired' => 0];

        if ($this->queuedCount === 0) {
            return $stats;
        }

        foreach ($this->webPush->flush() as $report) {
            $endpoint = $report->getRequest()->getUri()->__toString();
            $logMsg = "[".date('Y-m-d H:i:s')."] Endpoint: " . substr($endpoint, 0, 50) . "... ";

            if ($report->isSuccess()) {
                file_put_contents(BASE_PATH . '/storage/logs/push.log', $logMsg . "SUCCESS\n", FILE_APPEND);
                $stats['success']++;
            } else {
                if ($report->isSubscriptionExpired()) {
                    db_query("DELETE FROM push_subscriptions WHERE endpoint = ?", [$endpoint]);
                    file_put_contents(BASE_PATH . '/storage/logs/push.log', $logMsg . "EXPIRED (Deleted)\n", FILE_APPEND);
                    $stats['expired']++;
                } else {
                    $reason = $report->getReason();
                    file_put_contents(BASE_PATH . '/storage/logs/push.log', $logMsg . "FAILED: {$reason}\n", FILE_APPEND);
                    $stats['failed']++;
                }
                error_log("Push failure for {$endpoint}: {$report->getReason()}");
            }
        }

        $this->queuedCount = 0;

        file_put_contents(
            BASE_PATH . '/storage/logs/push.log',
            "[".date('Y-m-d H:i:s')."] FLUSH COMPLETE — ok:{$stats['success']} fail:{$stats['failed']} expired:{$stats['expired']}\n",
            FILE_APPEND
        );

        return $stats;
    }
}
