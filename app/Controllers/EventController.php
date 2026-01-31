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
                (SELECT id FROM event_enrollments WHERE event_id = e.id AND user_id = ?) as my_enrollment_id
             FROM events e 
             WHERE e.tenant_id = ? AND e.status IN ('upcoming', 'ongoing')
             ORDER BY e.start_datetime ASC",
            [$user['id'], $tenant['id']]
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

        // Enroll
        db_insert('event_enrollments', [
            'event_id' => $eventId,
            'user_id' => $user['id'],
            'tenant_id' => $tenant['id'],
            'status' => 'enrolled',
        ]);

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
