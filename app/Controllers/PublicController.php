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
    private const ITEMS_PER_PAGE = 12;

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

        // Ensure comments table exists
        $this->ensureCommentsTable();

        // Fetch upcoming and recent events for grouping
        $events = db_fetch_all(
            "SELECT * FROM events
             WHERE tenant_id = ? AND status IN ('upcoming', 'ongoing', 'completed')
               AND start_datetime >= DATE_SUB(NOW(), INTERVAL 90 DAY)
             ORDER BY start_datetime DESC LIMIT 20",
            [$tenantId]
        );

        // Fetch first page of curated media
        $rawMedia = $this->fetchCuratedMedia($tenantId, 0, self::ITEMS_PER_PAGE + 1);
        $hasMore  = count($rawMedia) > self::ITEMS_PER_PAGE;
        if ($hasMore) array_pop($rawMedia);

        $curatedMedia = $this->sanitizeMediaItems($rawMedia, $tenantId);

        // Group curated media by nearest event (14 days before → 7 days after)
        $eventGroups = [];
        foreach ($events as $ev) {
            $evTs   = strtotime($ev['start_datetime']);
            $before = $evTs - (14 * 86400);
            $after  = $evTs + (7  * 86400);
            $count  = 0;
            $cover  = null;
            foreach ($curatedMedia as $med) {
                $medTs = strtotime($med['date'] ?? '');
                if ($medTs >= $before && $medTs <= $after) {
                    $count++;
                    if (!$cover) {
                        $u = trim($med['media_content'] ?? '');
                        if (!empty($med['thumbnail_url'])) {
                            $cover = $med['thumbnail_url'];
                        } elseif (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=|shorts\/)|youtu\.be\/)([^"&?\/\s]{11})/i', $u, $_ytm)) {
                            $cover = 'https://img.youtube.com/vi/' . $_ytm[1] . '/hqdefault.jpg';
                        }
                    }
                }
            }
            if ($count > 0) {
                $eventGroups[] = [
                    'id'    => $ev['id'],
                    'title' => $ev['title'],
                    'date'  => $ev['start_datetime'],
                    'count' => $count,
                    'cover' => $cover,
                    'from'  => date('c', $before),
                    'to'    => date('c', $after),
                ];
            }
        }

        // Turn on output buffering to capture the rendered view
        ob_start();

        // Pick best OG image: first media thumbnail → club logo → nothing
        $ogImage = null;
        foreach ($curatedMedia as $_ogM) {
            $u = trim($_ogM['media_content'] ?? '');
            if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=|shorts\/)|youtu\.be\/)([^"&?\/\s]{11})/i', $u, $_ytm)) {
                $ogImage = 'https://img.youtube.com/vi/' . $_ytm[1] . '/hqdefault.jpg';
                break;
            }
            if (!empty($_ogM['thumbnail_url'])) {
                $ogImage = $_ogM['thumbnail_url'];
                break;
            }
            if (preg_match('/\.(jpg|jpeg|png|webp|gif)/i', $u)) {
                $ogImage = strpos($u, 'storage/') === 0 ? base_url('/' . $u) : $u;
                break;
            }
        }
        if (!$ogImage && !empty($profile['logo_url'])) {
            $ogImage = $profile['logo_url'];
        }

        View::render('public/club_landing', [
            'profile'      => $profile,
            'events'       => $events,
            'eventGroups'  => $eventGroups,
            'curatedMedia' => $curatedMedia,
            'hasMore'      => $hasMore,
            'pageTitle'    => $profile['display_name'] . ' - Desbravadores',
            'metaDescription' => $profile['seo_meta_description'] ?: 'Conheça o Clube de Desbravadores ' . $profile['display_name'],
            'ogImage'      => $ogImage,
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
     * Paginated media feed for infinite scroll (JSON)
     * GET /c/{club_slug}/media?page=N
     */
    public function getMediaPage(array $params): void
    {
        $clubSlug = $params['club_slug'];
        $profile  = db_fetch_one("SELECT tenant_id FROM club_profiles WHERE slug = ?", [$clubSlug]);
        if (!$profile) { $this->jsonError('Clube não encontrado', 404); return; }

        $page   = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * self::ITEMS_PER_PAGE;

        $rawMedia = $this->fetchCuratedMedia($profile['tenant_id'], $offset, self::ITEMS_PER_PAGE + 1);
        $hasMore  = count($rawMedia) > self::ITEMS_PER_PAGE;
        if ($hasMore) array_pop($rawMedia);

        $items = $this->sanitizeMediaItems($rawMedia, $profile['tenant_id']);

        $this->json(['success' => true, 'items' => $items, 'hasMore' => $hasMore, 'page' => $page]);
    }

    /**
     * Get comments for a media item (JSON)
     * GET /c/{club_slug}/media/{source_type}/{source_id}/comments
     */
    public function getComments(array $params): void
    {
        $clubSlug   = $params['club_slug'];
        $sourceType = $params['source_type'];
        $sourceId   = (int)$params['source_id'];

        $profile = db_fetch_one("SELECT tenant_id FROM club_profiles WHERE slug = ?", [$clubSlug]);
        if (!$profile) { $this->jsonError('Clube não encontrado', 404); return; }

        $this->ensureCommentsTable();

        $comments = db_fetch_all(
            "SELECT id, author_name, content, created_at
             FROM public_comments
             WHERE tenant_id = ? AND source_type = ? AND source_id = ? AND status = 'approved'
             ORDER BY created_at ASC LIMIT 50",
            [$profile['tenant_id'], $sourceType, $sourceId]
        );

        $this->json(['success' => true, 'comments' => $comments]);
    }

    /**
     * Post a new comment on a media item (JSON)
     * POST /c/{club_slug}/media/{source_type}/{source_id}/comment
     */
    public function postComment(array $params): void
    {
        $clubSlug   = $params['club_slug'];
        $sourceType = $params['source_type'];
        $sourceId   = (int)$params['source_id'];

        $profile = db_fetch_one("SELECT tenant_id FROM club_profiles WHERE slug = ?", [$clubSlug]);
        if (!$profile) { $this->jsonError('Clube não encontrado', 404); return; }

        $this->ensureCommentsTable();

        // Honeypot: bots fill this hidden field
        if (!empty($_POST['website'])) {
            $this->json(['success' => true, 'message' => 'Comentário enviado para moderação!']);
            return;
        }

        $authorName = trim($_POST['author_name'] ?? '');
        $content    = trim($_POST['content'] ?? '');

        if (empty($authorName) || empty($content)) {
            $this->jsonError('Nome e comentário são obrigatórios.');
            return;
        }
        if (mb_strlen($authorName) > 80 || mb_strlen($content) > 500) {
            $this->jsonError('Dados inválidos.');
            return;
        }

        // Rate limit: 3 comments per session per hour
        $sessionId = $_COOKIE['hub_session_id'] ?? '';
        if ($sessionId) {
            $recentCount = db_fetch_column(
                "SELECT COUNT(*) FROM public_comments
                 WHERE tenant_id = ? AND session_hash = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)",
                [$profile['tenant_id'], $sessionId]
            );
            if ($recentCount >= 3) {
                $this->jsonError('Limite de comentários atingido. Tente novamente em 1 hora.');
                return;
            }
        }

        if (!$sessionId) {
            $sessionId = bin2hex(random_bytes(16));
            setcookie('hub_session_id', $sessionId, time() + 60 * 60 * 24 * 365, '/');
        }

        db_insert('public_comments', [
            'tenant_id'   => $profile['tenant_id'],
            'source_type' => $sourceType,
            'source_id'   => $sourceId,
            'author_name' => htmlspecialchars($authorName, ENT_QUOTES, 'UTF-8'),
            'content'     => htmlspecialchars($content, ENT_QUOTES, 'UTF-8'),
            'session_hash'=> $sessionId,
            'status'      => 'pending',
        ]);

        $this->json(['success' => true, 'message' => 'Comentário enviado para moderação!']);
    }

    /**
     * Track a media view (JSON) — deduplicated per session
     * POST /c/{club_slug}/media/{source_type}/{source_id}/view
     */
    public function trackView(array $params): void
    {
        $clubSlug   = $params['club_slug'];
        $sourceType = $params['source_type'];
        $sourceId   = (int)$params['source_id'];

        $profile = db_fetch_one("SELECT tenant_id FROM club_profiles WHERE slug = ?", [$clubSlug]);
        if (!$profile) { $this->jsonError('Clube não encontrado', 404); return; }

        $sessionId = $_COOKIE['hub_session_id'] ?? '';
        if (!$sessionId) {
            $sessionId = bin2hex(random_bytes(16));
            setcookie('hub_session_id', $sessionId, time() + 60 * 60 * 24 * 365, '/');
        }

        $existing = db_fetch_one(
            "SELECT id FROM public_interactions
             WHERE tenant_id = ? AND source_type = ? AND source_id = ? AND session_hash = ? AND interaction_type = 'view'",
            [$profile['tenant_id'], $sourceType, $sourceId, $sessionId]
        );

        if (!$existing) {
            try {
                db_insert('public_interactions', [
                    'tenant_id'        => $profile['tenant_id'],
                    'source_type'      => $sourceType,
                    'source_id'        => $sourceId,
                    'session_hash'     => $sessionId,
                    'interaction_type' => 'view',
                ]);
            } catch (\Exception $e) {
                // Ignore duplicate key errors silently
            }
        }

        $count = db_fetch_column(
            "SELECT COUNT(*) FROM public_interactions
             WHERE tenant_id = ? AND source_type = ? AND source_id = ? AND interaction_type = 'view'",
            [$profile['tenant_id'], $sourceType, $sourceId]
        );

        $this->json(['success' => true, 'views' => (int)$count]);
    }

    // ─── Display the Public Event Details and Registration Form ───────────────

    public function eventDetails(array $params): void
    {
        $clubSlug  = $params['club_slug'];
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
            'profile'      => $profile,
            'event'        => $event,
            'pageTitle'    => $event['title'] . ' - ' . $profile['display_name']
        ], 'public');
    }

    // ─── Handle Public Event Registration (Guest or Logged In) ───────────────

    public function registerEvent(array $params): void
    {
        $eventId = (int) $params['id'];

        $user  = \App\Core\App::user();
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
            'event_id'  => $eventId,
            'tenant_id' => $event['tenant_id'],
            'status'    => 'enrolled'
        ];

        if ($user) {
            $existing = db_fetch_one("SELECT id FROM event_enrollments WHERE event_id = ? AND user_id = ?", [$eventId, $user['id']]);
            if ($existing) {
                $this->jsonError('Você já está inscrito neste evento.');
                return;
            }
            $data['user_id'] = $user['id'];
        } else {
            $guestName  = trim($_POST['guest_name']  ?? '');
            $guestPhone = trim($_POST['guest_phone'] ?? '');

            if (empty($guestName) || empty($guestPhone)) {
                $this->jsonError('Nome e telefone são obrigatórios para visitantes.');
                return;
            }
            $data['guest_name']  = $guestName;
            $data['guest_phone'] = $guestPhone;
        }

        try {
            db_insert('event_enrollments', $data);
            $this->json([
                'success'          => true,
                'message'          => 'Inscrição realizada com sucesso!',
                'payment_required' => (bool)$event['is_paid'],
                'payment_link'     => $event['payment_link']
            ]);
        } catch (\Exception $e) {
            $this->jsonError('Erro ao realizar inscrição: ' . $e->getMessage());
        }
    }

    // ─── Handle Anonymous Like Interaction ───────────────────────────────────

    public function toggleLike(array $params): void
    {
        $clubSlug = $params['club_slug'];
        $profile  = db_fetch_one("SELECT tenant_id FROM club_profiles WHERE slug = ?", [$clubSlug]);
        if (!$profile) {
            $this->jsonError('Clube não encontrado', 404);
            return;
        }

        $tenantId  = $profile['tenant_id'];
        $sourceType = $_POST['source_type'] ?? '';
        $sourceId   = (int)($_POST['source_id'] ?? 0);

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
                    'tenant_id'        => $tenantId,
                    'source_type'      => $sourceType,
                    'source_id'        => $sourceId,
                    'session_hash'     => $sessionId,
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

    // ─── Private Helpers ─────────────────────────────────────────────────────

    /**
     * Fetch raw curated media rows from DB
     */
    private function fetchCuratedMedia(int $tenantId, int $offset, int $limit): array
    {
        \App\Services\CurationService::ensureTable();
        return db_fetch_all("
            SELECT
                cm.id,
                cm.source_type,
                cm.source_id,
                cm.thumbnail_attempted,
                cm.thumbnail_retries,
                cm.thumbnail_retry_after,
                -- Title: prefer stored caption, fallback to JOIN, fallback to 'Destaque'
                COALESCE(cm.caption, ps.title, a.title, 'Destaque') as title,
                'url' as media_type,
                cm.media_url as media_content,
                NULL as raw_response_text,
                cm.thumbnail_url,
                -- User info: prefer stored columns (survive source deletions), fallback to JOIN
                COALESCE(cm.display_name,   u_s.name,       u_a.name)       as user_name,
                COALESCE(cm.display_avatar, u_s.avatar_url, u_a.avatar_url) as avatar_url,
                cm.created_at as date
            FROM curated_media cm
            -- Step fallback (only when display_name is not stored yet)
            LEFT JOIN user_step_responses usr ON cm.display_name IS NULL AND cm.source_type = 'step' AND cm.source_id = usr.id
            LEFT JOIN program_steps ps        ON usr.step_id = ps.id
            LEFT JOIN user_program_progress upp ON usr.progress_id = upp.id
            LEFT JOIN users u_s               ON u_s.id = upp.user_id
            -- Activity fallback (only when display_name is not stored yet)
            LEFT JOIN activity_proofs ap      ON cm.display_name IS NULL AND cm.source_type = 'activity' AND cm.source_id = ap.id
            LEFT JOIN user_activities ua      ON ap.user_activity_id = ua.id
            LEFT JOIN activities a            ON ua.activity_id = a.id
            LEFT JOIN users u_a               ON u_a.id = ua.user_id
            WHERE cm.tenant_id = ?
            ORDER BY date DESC
            LIMIT ? OFFSET ?
        ", [$tenantId, $limit, $offset]);
    }

    /**
     * Sanitize and enrich media items (JSON extraction, comment/like/view counts)
     */
    private function sanitizeMediaItems(array $rawMedia, int $tenantId): array
    {
        $sessionId = $_COOKIE['hub_session_id'] ?? '';
        $sanitized = [];

        foreach ($rawMedia as $media) {
            $content = trim((string)($media['media_content'] ?? ''));
            $isJson  = (str_starts_with($content, '[') || str_starts_with($content, '{'));

            if ($isJson) {
                $decoded = json_decode($content, true);
                if (is_array($decoded)) {
                    $extractedUrl = null;
                    foreach ($decoded as $val) {
                        if (is_string($val) && filter_var($val, FILTER_VALIDATE_URL)) { $extractedUrl = $val; break; }
                        if (is_array($val)) {
                            foreach ($val as $subVal) {
                                if (is_string($subVal) && filter_var($subVal, FILTER_VALIDATE_URL)) { $extractedUrl = $subVal; break 2; }
                            }
                        }
                    }
                    if ($extractedUrl) $content = $extractedUrl;
                }
            }

            if (!$content || !filter_var($content, FILTER_VALIDATE_URL)) {
                continue;
            }

            // ── Thumbnail fetch with smart retry & local caching ──────────
            $needsFetch = false;
            $retries    = (int)($media['thumbnail_retries'] ?? 0);
            $retryAfter = $media['thumbnail_retry_after'] ?? null;
            $maxRetries = 3;

            if (empty($media['thumbnail_url'])) {
                // Never attempted, or retry window has passed
                if (empty($media['thumbnail_attempted'])) {
                    $needsFetch = true;
                } elseif ($retries < $maxRetries && $retryAfter && strtotime($retryAfter) <= time()) {
                    $needsFetch = true;
                }
            } elseif (!empty($media['thumbnail_url']) && strpos($media['thumbnail_url'], 'uploads/thumbnails/') === false) {
                // Has a remote URL (not cached locally) — try to cache it now
                $localPath = cache_thumbnail_locally($media['thumbnail_url'], (int)$media['id']);
                if ($localPath) {
                    $media['thumbnail_url'] = $localPath;
                    db_query(
                        "UPDATE curated_media SET thumbnail_url = ? WHERE id = ?",
                        [$localPath, $media['id']]
                    );
                }
            }

            if ($needsFetch) {
                $fetched = fetch_media_thumbnail($content);
                if ($fetched) {
                    // Try to cache locally (prevents CDN expiration)
                    $localPath = cache_thumbnail_locally($fetched, (int)$media['id']);
                    $finalThumb = $localPath ?: $fetched;

                    $media['thumbnail_url'] = $finalThumb;
                    db_query(
                        "UPDATE curated_media SET thumbnail_url = ?, thumbnail_attempted = 1, thumbnail_retries = 0, thumbnail_retry_after = NULL WHERE id = ?",
                        [$finalThumb, $media['id']]
                    );
                } else {
                    // Failed — schedule retry with exponential backoff (6h, 24h, 72h)
                    $newRetries = $retries + 1;
                    $backoffHours = min(6 * pow(4, $retries), 72);
                    $nextRetry = date('Y-m-d H:i:s', strtotime("+{$backoffHours} hours"));
                    db_query(
                        "UPDATE curated_media SET thumbnail_attempted = 1, thumbnail_retries = ?, thumbnail_retry_after = ? WHERE id = ?",
                        [$newRetries, $nextRetry, $media['id']]
                    );
                }
            }

            $media['media_content'] = $content;
            $media = $this->enrichMediaItem($media, $tenantId, $sessionId);
            $sanitized[] = $media;
        }

        return $sanitized;
    }

    /**
     * Attach like_count, view_count, comment_count, has_liked to a media item
     */
    private function enrichMediaItem(array $media, int $tenantId, string $sessionId): array
    {
        $st = $media['source_type'];
        $si = $media['source_id'];

        $media['like_count'] = (int)db_fetch_column(
            "SELECT COUNT(*) FROM public_interactions WHERE tenant_id=? AND source_type=? AND source_id=? AND interaction_type='like'",
            [$tenantId, $st, $si]
        );
        $media['view_count'] = (int)db_fetch_column(
            "SELECT COUNT(*) FROM public_interactions WHERE tenant_id=? AND source_type=? AND source_id=? AND interaction_type='view'",
            [$tenantId, $st, $si]
        );
        $media['comment_count'] = (int)db_fetch_column(
            "SELECT COUNT(*) FROM public_comments WHERE tenant_id=? AND source_type=? AND source_id=? AND status='approved'",
            [$tenantId, $st, $si]
        );
        $media['has_liked'] = $sessionId ? (bool)db_fetch_one(
            "SELECT id FROM public_interactions WHERE tenant_id=? AND source_type=? AND source_id=? AND session_hash=? AND interaction_type='like'",
            [$tenantId, $st, $si, $sessionId]
        ) : false;

        // Reactions (top 3 by count)
        try {
            $this->ensureReactionsTable();
            $rxRows = db_fetch_all(
                "SELECT reaction_type, COUNT(*) as cnt FROM public_reactions
                 WHERE tenant_id=? AND source_type=? AND source_id=?
                 GROUP BY reaction_type ORDER BY cnt DESC LIMIT 3",
                [$tenantId, $st, $si]
            );
            $media['reactions']   = $rxRows;
            $media['my_reaction'] = $sessionId ? (db_fetch_column(
                "SELECT reaction_type FROM public_reactions WHERE tenant_id=? AND source_type=? AND source_id=? AND session_hash=?",
                [$tenantId, $st, $si, $sessionId]
            ) ?: null) : null;
        } catch (\Exception $e) {
            $media['reactions']   = [];
            $media['my_reaction'] = null;
        }

        return $media;
    }

    /**
     * Auto-create public_comments table if it doesn't exist
     */
    private function ensureCommentsTable(): void
    {
        try {
            db_query("
                CREATE TABLE IF NOT EXISTS public_comments (
                    id           INT AUTO_INCREMENT PRIMARY KEY,
                    tenant_id    INT NOT NULL,
                    source_type  VARCHAR(50) NOT NULL,
                    source_id    INT NOT NULL,
                    author_name  VARCHAR(100) NOT NULL,
                    content      TEXT NOT NULL,
                    session_hash VARCHAR(64) DEFAULT NULL,
                    status       ENUM('pending','approved','rejected') DEFAULT 'pending',
                    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_pc_tenant_source (tenant_id, source_type, source_id),
                    INDEX idx_pc_status (status)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");

            // Add session_hash column if missing (upgrade path)
            $cols = db_fetch_all("SHOW COLUMNS FROM public_comments LIKE 'session_hash'");
            if (empty($cols)) {
                db_query("ALTER TABLE public_comments ADD COLUMN session_hash VARCHAR(64) DEFAULT NULL AFTER content");
            }
        } catch (\Exception $e) {
            // Silently ignore — table already exists or DB doesn't support DDL here
        }
    }

    /**
     * Capture a lead from the "Quero Participar" CTA
     * POST /c/{club_slug}/lead
     */
    public function postLead(array $params): void
    {
        $clubSlug = $params['club_slug'];

        $profile = db_fetch_one("SELECT tenant_id FROM club_profiles WHERE slug = ?", [$clubSlug]);
        if (!$profile) {
            $this->jsonError('Clube não encontrado', 404);
            return;
        }

        // Honeypot
        if (!empty($_POST['website'])) {
            $this->json(['success' => true, 'message' => 'Obrigado pelo interesse!']);
            return;
        }

        $name    = trim($_POST['name'] ?? '');
        $phone   = trim($_POST['phone'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if (empty($name)) {
            $this->jsonError('Nome é obrigatório.');
            return;
        }
        if (empty($phone) && empty($email)) {
            $this->jsonError('Informe WhatsApp ou e-mail para contato.');
            return;
        }
        if (mb_strlen($name) > 120 || mb_strlen($phone) > 30 || mb_strlen($email) > 160) {
            $this->jsonError('Dados inválidos.');
            return;
        }

        // Rate limit: 1 lead per session per 24h
        $sessionId = $_COOKIE['hub_session_id'] ?? '';
        if ($sessionId) {
            try {
                $recent = db_fetch_column(
                    "SELECT COUNT(*) FROM public_leads WHERE tenant_id = ? AND source = ? AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)",
                    [$profile['tenant_id'], 'session_' . substr($sessionId, 0, 16)]
                );
                if ($recent > 0) {
                    $this->json(['success' => true, 'message' => 'Obrigado pelo interesse! Entraremos em contato em breve.']);
                    return;
                }
            } catch (\Exception $e) { /* table may not exist yet */ }
        }

        if (!$sessionId) {
            $sessionId = bin2hex(random_bytes(16));
            setcookie('hub_session_id', $sessionId, time() + 60 * 60 * 24 * 365, '/');
        }

        // Auto-create table if needed
        try {
            db_query("CREATE TABLE IF NOT EXISTS `public_leads` (
                `id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
                `tenant_id` INT UNSIGNED NOT NULL,
                `name` VARCHAR(120) NOT NULL,
                `phone` VARCHAR(30) NULL,
                `email` VARCHAR(160) NULL,
                `message` TEXT NULL,
                `status` ENUM('new','contacting','converted','dismissed') NOT NULL DEFAULT 'new',
                `source` VARCHAR(50) NOT NULL DEFAULT 'hub_cta',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX (`tenant_id`), INDEX (`status`),
                FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch (\Exception $e) { /* already exists */ }

        try {
            db_insert('public_leads', [
                'tenant_id' => $profile['tenant_id'],
                'name'      => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
                'phone'     => $phone ? htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') : null,
                'email'     => $email ?: null,
                'message'   => $message ? htmlspecialchars($message, ENT_QUOTES, 'UTF-8') : null,
                'source'    => 'session_' . substr($sessionId, 0, 16),
            ]);
        } catch (\Exception $e) {
            $this->jsonError('Erro ao salvar interesse. Tente novamente.');
            return;
        }

        // Notify club director in-app
        try {
            $director = db_fetch_one(
                "SELECT id FROM users WHERE tenant_id = ? AND role_name IN ('admin','director') AND status = 'active' ORDER BY id LIMIT 1",
                [$profile['tenant_id']]
            );
            if ($director) {
                $notifService = new \App\Services\NotificationService();
                $notifService->send(
                    $director['id'],
                    'lead',
                    '🌟 Novo Interesse no Clube!',
                    "{$name} quer saber mais sobre os Desbravadores.",
                    ['data' => ['lead_name' => $name, 'phone' => $phone]]
                );
            }
        } catch (\Exception $e) {
            error_log("Lead notification failed: " . $e->getMessage());
        }

        $this->json(['success' => true, 'message' => 'Obrigado pelo interesse! O clube entrará em contato em breve. 🙏']);
    }

    /**
     * Toggle reaction emoji on a media item
     * POST /c/{club_slug}/react
     */
    public function react(array $params): void
    {
        $clubSlug   = $params['club_slug'];
        $profile    = db_fetch_one("SELECT tenant_id FROM club_profiles WHERE slug = ?", [$clubSlug]);
        if (!$profile) { $this->jsonError('Clube não encontrado', 404); return; }

        $tenantId     = $profile['tenant_id'];
        $sourceType   = trim($_POST['source_type'] ?? '');
        $sourceId     = (int)($_POST['source_id']   ?? 0);
        $reactionType = trim($_POST['reaction_type'] ?? 'like');

        $allowed = ['like','love','haha','wow','clap','fire'];
        if (!in_array($reactionType, $allowed) || !$sourceType || !$sourceId) {
            $this->jsonError('Parâmetros inválidos'); return;
        }

        $sessionId = $_COOKIE['hub_session_id'] ?? '';
        if (!$sessionId) {
            $sessionId = bin2hex(random_bytes(16));
            setcookie('hub_session_id', $sessionId, time() + 60*60*24*365, '/');
        }

        $this->ensureReactionsTable();

        try {
            $existing = db_fetch_one(
                "SELECT id, reaction_type FROM public_reactions WHERE tenant_id=? AND source_type=? AND source_id=? AND session_hash=?",
                [$tenantId, $sourceType, $sourceId, $sessionId]
            );

            if ($existing) {
                if ($existing['reaction_type'] === $reactionType) {
                    // Same reaction — toggle off
                    db_query("DELETE FROM public_reactions WHERE id=?", [$existing['id']]);
                    $myReaction = null;
                } else {
                    // Different reaction — update
                    db_query("UPDATE public_reactions SET reaction_type=? WHERE id=?", [$reactionType, $existing['id']]);
                    $myReaction = $reactionType;
                }
            } else {
                db_insert('public_reactions', [
                    'tenant_id'     => $tenantId,
                    'source_type'   => $sourceType,
                    'source_id'     => $sourceId,
                    'session_hash'  => $sessionId,
                    'reaction_type' => $reactionType,
                ]);
                $myReaction = $reactionType;
            }

            $counts = db_fetch_all(
                "SELECT reaction_type, COUNT(*) as cnt FROM public_reactions
                 WHERE tenant_id=? AND source_type=? AND source_id=?
                 GROUP BY reaction_type ORDER BY cnt DESC",
                [$tenantId, $sourceType, $sourceId]
            );

            $this->json(['success' => true, 'my_reaction' => $myReaction, 'counts' => $counts]);
        } catch (\Exception $e) {
            $this->jsonError('Erro: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Auto-create public_reactions table if it doesn't exist
     */
    private function ensureReactionsTable(): void
    {
        static $_done = false;
        if ($_done) return;
        try {
            db_query("
                CREATE TABLE IF NOT EXISTS public_reactions (
                    id            INT AUTO_INCREMENT PRIMARY KEY,
                    tenant_id     INT NOT NULL,
                    source_type   VARCHAR(50) NOT NULL,
                    source_id     INT NOT NULL,
                    session_hash  VARCHAR(64) NOT NULL,
                    reaction_type ENUM('like','love','haha','wow','clap','fire') NOT NULL DEFAULT 'like',
                    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE KEY uniq_reaction (tenant_id, source_type, source_id, session_hash),
                    INDEX idx_pr_source (tenant_id, source_type, source_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        } catch (\Exception $e) { /* already exists */ }
        $_done = true;
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
