<?php
/**
 * Public Controller
 * 
 * Handles the public-facing pages for a Club, like the Landing Page 
 * and public Event details.
 */

namespace App\Controllers;

use App\Core\View;

class PublicController
{
    /**
     * Display the Club Landing Page (/c/[slug])
     */
    public function clubProfile(array $params): void
    {
        $slug = $params['slug'];

        // Caching Logic: cache public landing pages for 10 minutes to improve performance
        $cacheDir = BASE_PATH . '/storage/framework/cache/pages';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }
        
        $cacheFile = $cacheDir . '/landing_' . md5($slug) . '.html';
        $cacheTime = 600; // 10 minutes
        
        // Skip cache if logged in, or if it's a QR code visit (we need to track it)
        $isTrackingVisit = isset($_GET['utm_source']) && $_GET['utm_source'] === 'qr_offline';
        $useCache = !\App\Core\App::user() && !$isTrackingVisit;

        if ($useCache && file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
            echo file_get_contents($cacheFile);
            return;
        }

        // Find the club by slug
        $profile = db_fetch_one("SELECT * FROM club_profiles WHERE slug = ?", [$slug]);

        if (!$profile) {
            http_response_code(404);
            echo "Clube não encontrado.";
            return;
        }

        // Increment growth visits if it comes from a QR code
        if ($isTrackingVisit) {
            db_query(
                "UPDATE club_growth_tools SET visits_count = visits_count + 1 WHERE tenant_id = ?",
                [$profile['tenant_id']]
            );
        }

        $tenantId = $profile['tenant_id'];

        // Fetch upcoming public events for this club
        $events = db_fetch_all(
            "SELECT * FROM events 
             WHERE tenant_id = ? AND status IN ('upcoming', 'ongoing') 
             ORDER BY start_datetime ASC LIMIT 6",
            [$tenantId]
        );

        // Turn on output buffering to capture the rendered view
        ob_start();
        
        View::render('public/club_landing', [
            'profile' => $profile,
            'events' => $events,
            'pageTitle' => $profile['display_name'] . ' - Desbravadores',
            'metaDescription' => $profile['seo_meta_description'] ?: 'Conheça o Clube de Desbravadores ' . $profile['display_name']
        ], 'public');

        $html = ob_get_clean();

        // Save to Cache
        if ($useCache) {
            file_put_contents($cacheFile, $html);
        }

        // Output to screen
        echo $html;
    }

    /**
     * Display the Public Event Details and Registration Form
     */
    public function eventDetails(array $params): void
    {
        $clubSlug = $params['club_slug'];
        $eventSlug = $params['event_slug'];

        $profile = db_fetch_one("SELECT * FROM club_profiles WHERE slug = ?", [$clubSlug]);

        if (!$profile) {
            http_response_code(404);
            echo "Clube não encontrado.";
            return;
        }

        $event = db_fetch_one(
            "SELECT e.*, 
                (SELECT COUNT(*) FROM event_enrollments WHERE event_id = e.id) as enrolled_count
             FROM events e 
             WHERE e.tenant_id = ? AND e.slug = ?",
            [$profile['tenant_id'], $eventSlug]
        );

        if (!$event) {
            http_response_code(404);
            echo "Evento não encontrado.";
            return;
        }

        View::render('public/event_details', [
            'profile' => $profile,
            'event' => $event,
            'pageTitle' => $event['title'] . ' - ' . $profile['display_name']
        ], 'public');
    }

    /**
     * Handle Public Event Registration (Guest or Logged In)
     */
    public function registerEvent(array $params): void
    {
        $eventId = (int) $params['id'];
        
        // This is a simplified check, ideally we authenticate if trying to enroll as a member.
        $user = \App\Core\App::user();
        
        $event = db_fetch_one("SELECT * FROM events WHERE id = ? AND status = 'upcoming'", [$eventId]);

        if (!$event) {
            $this->jsonError('Evento não encontrado ou inscrições encerradas.');
            return;
        }

        // Check Capacity
        if ($event['max_participants']) {
            $count = db_fetch_column("SELECT COUNT(*) FROM event_enrollments WHERE event_id = ?", [$eventId]);
            if ($count >= $event['max_participants']) {
                $this->jsonError('Este evento já atingiu o limite máximo de vagas.');
                return;
            }
        }

        $data = [
            'event_id' => $eventId,
            'tenant_id' => $event['tenant_id'],
            'status' => 'enrolled'
        ];

        // Is it a logged-in member or a guest?
        if ($user) {
            // Check if already enrolled
            $existing = db_fetch_one("SELECT id FROM event_enrollments WHERE event_id = ? AND user_id = ?", [$eventId, $user['id']]);
            if ($existing) {
                $this->jsonError('Você já está inscrito neste evento.');
                return;
            }
            $data['user_id'] = $user['id'];
        } else {
            // Guest Registration
            $guestName = trim($_POST['guest_name'] ?? '');
            $guestPhone = trim($_POST['guest_phone'] ?? '');

            if (empty($guestName) || empty($guestPhone)) {
                $this->jsonError('Nome e telefone são obrigatórios para visitantes.');
                return;
            }
            $data['guest_name'] = $guestName;
            $data['guest_phone'] = $guestPhone;
        }

        try {
            db_insert('event_enrollments', $data);
            $this->json([
                'success' => true,
                'message' => 'Inscrição realizada com sucesso!',
                'payment_required' => (bool)$event['is_paid'],
                'payment_link' => $event['payment_link']
            ]);
        } catch (\Exception $e) {
            $this->jsonError('Erro ao realizar inscrição: ' . $e->getMessage());
        }
    }

    private function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    private function jsonError(string $message, int $code = 400): void
    {
        http_response_code($code);
        $this->json(['error' => $message]);
    }
}
