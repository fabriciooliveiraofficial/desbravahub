<?php
/**
 * Push Subscription Controller
 * 
 * Handles push notification subscriptions.
 */

namespace App\Controllers;

use App\Core\App;

class PushController
{
    /**
     * Get VAPID public key
     */
    public function publicKey(): void
    {
        $key = env('VAPID_PUBLIC_KEY', '');

        $this->json(['publicKey' => $key]);
    }

    /**
     * Subscribe to push notifications
     */
    public function subscribe(): void
    {
        $user = App::user();
        $tenant = App::tenant();

        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input || !isset($input['endpoint'])) {
            $this->json(['error' => 'Invalid subscription'], 400);
            return;
        }

        // Store subscription
        $existing = db_fetch_one(
            "SELECT id FROM push_subscriptions WHERE user_id = ? AND endpoint = ?",
            [$user['id'], $input['endpoint']]
        );

        if (!$existing) {
            db_insert('push_subscriptions', [
                'user_id' => $user['id'],
                'tenant_id' => $tenant['id'],
                'endpoint' => $input['endpoint'],
                'p256dh' => $input['keys']['p256dh'] ?? '',
                'auth' => $input['keys']['auth'] ?? '',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            ]);
        }

        $this->json(['success' => true]);
    }

    /**
     * Unsubscribe from push notifications
     */
    public function unsubscribe(): void
    {
        $user = App::user();

        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input || !isset($input['endpoint'])) {
            $this->json(['error' => 'Invalid subscription'], 400);
            return;
        }

        db_query(
            "DELETE FROM push_subscriptions WHERE user_id = ? AND endpoint = ?",
            [$user['id'], $input['endpoint']]
        );

        $this->json(['success' => true]);
    }

    /**
     * Get user's subscription status
     */
    public function status(): void
    {
        $user = App::user();

        $count = db_fetch_column(
            "SELECT COUNT(*) FROM push_subscriptions WHERE user_id = ?",
            [$user['id']]
        );

        $this->json([
            'subscribed' => $count > 0,
            'devices' => (int) $count
        ]);
    }

    private function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}
