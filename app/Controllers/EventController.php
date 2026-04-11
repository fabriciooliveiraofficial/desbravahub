<?php
/**
 * Event Controller
 * 
 * Handles events listing and enrollment for users.
 */

namespace App\Controllers;

use App\Core\View;
use App\Core\App;

class EventController
{
    /**
     * List events for user
     */
    public function index(): void
    {
        $user = App::user();
        $tenant = App::tenant();

        // Get upcoming events
        $events = db_fetch_all(
            "SELECT e.*, 
                (SELECT COUNT(*) FROM event_enrollments WHERE event_id = e.id) as enrolled_count,
                (SELECT id FROM event_enrollments WHERE event_id = e.id AND user_id = ?) as my_enrollment_id,
                (SELECT status FROM event_enrollments WHERE event_id = e.id AND user_id = ?) as my_status,
                (SELECT checkin_token FROM event_enrollments WHERE event_id = e.id AND user_id = ?) as checkin_token
             FROM events e 
             WHERE e.tenant_id = ? AND e.status IN ('upcoming', 'ongoing')
             ORDER BY e.start_datetime ASC",
            [$user['id'], $user['id'], $user['id'], $tenant['id']]
        );

        // Get past events
        $pastEvents = db_fetch_all(
            "SELECT e.*, 
                (SELECT id FROM event_enrollments WHERE event_id = e.id AND user_id = ?) as my_enrollment_id
             FROM events e 
             WHERE e.tenant_id = ? AND e.status = 'completed'
             ORDER BY e.start_datetime DESC
             LIMIT 10",
            [$user['id'], $tenant['id']]
        );

        // Fetch gallery for each event
        foreach ($events as &$event) {
            $event['gallery'] = db_fetch_all("SELECT * FROM event_gallery WHERE event_id = ? ORDER BY created_at DESC", [$event['id']]);
        }
        foreach ($pastEvents as &$event) {
            $event['gallery'] = db_fetch_all("SELECT * FROM event_gallery WHERE event_id = ? ORDER BY created_at DESC", [$event['id']]);
        }

        View::render('dashboard/events', [
            'tenant' => $tenant,
            'user' => $user,
            'events' => $events,
            'pastEvents' => $pastEvents
        ], 'member');
    }

    /**
     * Enroll in event
     */
    public function enroll(array $params): void
    {
        $user = App::user();
        $tenant = App::tenant();
        $eventId = (int) $params['id'];

        // Check if event exists and is open
        $event = db_fetch_one(
            "SELECT * FROM events WHERE id = ? AND tenant_id = ? AND status = 'upcoming'",
            [$eventId, $tenant['id']]
        );

        if (!$event) {
            $this->json(['error' => 'Evento não encontrado ou não disponível'], 404);
            return;
        }

        // Check if already enrolled
        $existing = db_fetch_one(
            "SELECT id FROM event_enrollments WHERE event_id = ? AND user_id = ?",
            [$eventId, $user['id']]
        );

        if ($existing) {
            $this->json(['error' => 'Você já está inscrito'], 400);
            return;
        }

        // Check capacity
        if ($event['max_participants']) {
            $count = db_fetch_column(
                "SELECT COUNT(*) FROM event_enrollments WHERE event_id = ?",
                [$eventId]
            );
            if ($count >= $event['max_participants']) {
                $this->json(['error' => 'Evento lotado'], 400);
                return;
            }
        }

        $token = bin2hex(random_bytes(16));

        // Enroll
        db_insert('event_enrollments', [
            'event_id' => $eventId,
            'user_id' => $user['id'],
            'tenant_id' => $tenant['id'],
            'status' => 'enrolled',
            'checkin_token' => $token
        ]);

        // Notifications
        $notificationService = new \App\Services\NotificationService();
        
        // 1. Send confirmation to Member
        $notificationService->send(
            $user['id'],
            'event_enrollment',
            "✅ Inscrição Confirmada!",
            "Sua vaga no evento \"{$event['title']}\" está garantida.",
            [
                'channels' => ['toast', 'email', 'push'],
                'data' => [
                    'event_id' => $eventId,
                    'link' => base_url($tenant['slug'] . '/eventos')
                ]
            ]
        );

        // 2. Alert Admins
        $admins = db_fetch_all("SELECT id FROM users WHERE tenant_id = ? AND role_id IN (SELECT id FROM roles WHERE slug = 'admin')", [$tenant['id']]);
        foreach ($admins as $admin) {
            $notificationService->send(
                (int) $admin['id'],
                'admin_alert',
                "👤 Nova Inscrição no Evento",
                "{$user['name']} acabou de se inscrever em \"{$event['title']}\".",
                [
                    'channels' => ['toast', 'push'],
                    'priority' => 'high',
                    'data' => [
                        'link' => base_url($tenant['slug'] . '/admin/eventos/' . $eventId . '/inscritos')
                    ]
                ]
            );
        }

        $this->json(['success' => true, 'message' => 'Inscrição confirmada!']);
    }

    /**
     * Cancel enrollment
     */
    public function cancel(array $params): void
    {
        $user = App::user();
        $eventId = (int) $params['id'];

        db_query(
            "DELETE FROM event_enrollments WHERE event_id = ? AND user_id = ?",
            [$eventId, $user['id']]
        );

        $this->json(['success' => true, 'message' => 'Inscrição cancelada']);
    }

    private function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}
