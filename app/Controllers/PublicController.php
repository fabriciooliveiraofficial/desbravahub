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
             ORDER BY start_datetime ASC LIMIT 10",
            [$tenantId]
        );

        // Fetch curated media for the Instagram-style grid feed
        $curatedMedia = db_fetch_all("
            SELECT 
                'activity' as source_type,
                ap.id as source_id,
                a.title as title,
                ap.type as media_type,
                ap.content as media_content,
                NULL as raw_response_text,
                ap.thumbnail_url as thumbnail_url,
                u.name as user_name,
                u.avatar_url,
                ap.submitted_at as date
            FROM activity_proofs ap
            JOIN user_activities ua ON ap.user_activity_id = ua.id
            JOIN activities a ON ua.activity_id = a.id
            JOIN users u ON ua.user_id = u.id
            WHERE a.tenant_id = ? AND ap.show_public = 1 AND ap.status = 'approved'
            
            UNION ALL
            
            SELECT 
                'program_step' as source_type,
                usr.id as source_id,
                ps.title as title,
                CASE 
                    WHEN usr.response_url IS NOT NULL AND usr.response_url != '' THEN 'url'
                    WHEN usr.response_file IS NOT NULL AND usr.response_file != '' THEN 'upload'
                    ELSE 'text'
                END as media_type,
                COALESCE(usr.response_url, usr.response_file, usr.response_text) as media_content,
                usr.response_text as raw_response_text,
                usr.thumbnail_url as thumbnail_url,
                u.name as user_name,
                u.avatar_url,
                usr.submitted_at as date
            FROM user_step_responses usr
            JOIN program_steps ps ON usr.step_id = ps.id
            JOIN user_program_progress upp ON usr.progress_id = upp.id
            JOIN users u ON upp.user_id = u.id
            WHERE upp.tenant_id = ? AND usr.show_public = 1 AND usr.status = 'approved'

            ORDER BY date DESC
            LIMIT 60
        ", [$tenantId, $tenantId]);

        // Transform and sanitize curatedMedia (Handle JSON arrays from response_text)
        $sanitizedMedia = [];
        foreach ($curatedMedia as $media) {
            $content = trim((string)($media['media_content'] ?? ''));
            $isJson = (str_starts_with($content, '[') || str_starts_with($content, '{'));
            
            if ($isJson) {
                $decoded = json_decode($content, true);
                if (is_array($decoded)) {
                    $extractedUrl = null;
                    $foundQuestionId = null;

                    foreach ($decoded as $qId => $val) {
                        if (is_string($val) && filter_var($val, FILTER_VALIDATE_URL)) { 
                            $extractedUrl = $val; 
                            $foundQuestionId = $qId;
                            break; 
                        }
                        if (is_array($val)) {
                            foreach ($val as $subVal) {
                                if (is_string($subVal) && filter_var($subVal, FILTER_VALIDATE_URL)) { 
                                    $extractedUrl = $subVal; 
                                    $foundQuestionId = $qId;
                                    break 2; 
                                }
                            }
                        }
                    }

                    if ($extractedUrl) {
                        $media['media_content'] = $extractedUrl;
                        
                        // Try to resolve a better title from the question
                        if ($foundQuestionId && is_numeric($foundQuestionId)) {
                            $qTitle = db_fetch_column("SELECT question_text FROM program_questions WHERE id = ?", [$foundQuestionId]);
                            if ($qTitle) {
                                // Clean up the title (often has HTML or is too long)
                                $qTitle = strip_tags($qTitle);
                                if (strlen($qTitle) > 100) $qTitle = mb_substr($qTitle, 0, 97) . '...';
                                $media['title'] = $qTitle;
                            }
                        }

                        if (preg_match('/youtube|youtu\.be|tiktok|instagram|reels|shorts/i', $extractedUrl)) {
                           $media['media_type'] = 'url';
                        }
                        $sanitizedMedia[] = $media;
                    }
                    continue; 
                }
            }
            
            // For plain links that might have been structured originally, try to find the title mapping
            if (!empty($content) && filter_var($content, FILTER_VALIDATE_URL) && !empty($media['raw_response_text'])) {
                $decodedRaw = json_decode($media['raw_response_text'], true);
                if (is_array($decodedRaw)) {
                    foreach ($decodedRaw as $qId => $val) {
                        $match = false;
                        if (is_string($val) && $val === $content) $match = true;
                        if (is_array($val) && in_array($content, $val)) $match = true;
                        
                        if ($match && is_numeric($qId)) {
                            $qTitle = db_fetch_column("SELECT question_text FROM program_questions WHERE id = ?", [$qId]);
                            if ($qTitle) {
                                $qTitle = strip_tags($qTitle);
                                if (strlen($qTitle) > 100) $qTitle = mb_substr($qTitle, 0, 97) . '...';
                                $media['title'] = $qTitle;
                            }
                            break;
                        }
                    }
                }
            }

            // Allow normal URLs or upload paths
            if (!empty($content)) {
                $media['media_content'] = $content;
                $sanitizedMedia[] = $media;
            }
        }
        $curatedMedia = $sanitizedMedia;

        // Turn on output buffering to capture the rendered view
        ob_start();
        
        View::render('public/club_landing', [
            'profile' => $profile,
            'events' => $events,
            'curatedMedia' => $curatedMedia,
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

    /**
     * Handle Anonymous Like Interaction
     */
    public function toggleLike(array $params): void
    {
        $clubSlug = $params['club_slug'];
        $profile = db_fetch_one("SELECT tenant_id FROM club_profiles WHERE slug = ?", [$clubSlug]);
        if (!$profile) {
            $this->jsonError('Clube não encontrado', 404);
            return;
        }

        $tenantId = $profile['tenant_id'];
        $sourceType = $_POST['source_type'] ?? '';
        $sourceId = (int)($_POST['source_id'] ?? 0);
        
        $sessionId = $_COOKIE['hub_session_id'] ?? '';
        if (!$sessionId) {
            $sessionId = bin2hex(random_bytes(16));
            setcookie('hub_session_id', $sessionId, time() + 60*60*24*365, '/');
        }

        if (!$sourceType || !$sourceId) {
            $this->jsonError('Parâmetros inválidos');
            return;
        }

        try {
            $existing = db_fetch_one(
                "SELECT id FROM public_interactions WHERE tenant_id = ? AND source_type = ? AND source_id = ? AND session_hash = ? AND interaction_type = 'like'", 
                [$tenantId, $sourceType, $sourceId, $sessionId]
            );

            if ($existing) {
                db_query("DELETE FROM public_interactions WHERE id = ?", [$existing['id']]);
                $action = 'unliked';
            } else {
                db_insert('public_interactions', [
                    'tenant_id' => $tenantId,
                    'source_type' => $sourceType,
                    'source_id' => $sourceId,
                    'session_hash' => $sessionId,
                    'interaction_type' => 'like'
                ]);
                $action = 'liked';
            }

            $count = db_fetch_column(
                "SELECT COUNT(*) FROM public_interactions WHERE tenant_id = ? AND source_type = ? AND source_id = ? AND interaction_type = 'like'", 
                [$tenantId, $sourceType, $sourceId]
            );

            $this->json(['success' => true, 'action' => $action, 'count' => $count]);
        } catch (\Exception $e) {
            $this->jsonError('Database Error: ' . $e->getMessage(), 500);
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
