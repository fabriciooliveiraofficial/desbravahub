<?php
/**
 * Communication Hub Controller
 *
 * Unified admin center for: leads, comment moderation, campaigns.
 */

namespace App\Controllers;

use App\Core\View;
use App\Core\App;
use App\Services\NotificationService;
use App\Services\WebPushService;
use App\Services\EmailService;

class CommunicationController
{
    /**
     * Main hub page — tabs: leads, comments, campaigns
     */
    public function index(): void
    {
        $this->requireAdmin();
        $tenant = App::tenant();
        $user   = App::user();

        \App\Services\CurationService::migrateLegacyData();


        $statusFilter = $_GET['status'] ?? 'new';
        $tab          = $_GET['tab'] ?? 'leads';

        // ── Leads ────────────────────────────────────────────────────────
        $leads = db_fetch_all(
            "SELECT * FROM public_leads WHERE tenant_id = ? ORDER BY created_at DESC LIMIT 100",
            [$tenant['id']]
        );
        $leadCounts = [
            'new'        => (int) db_fetch_column("SELECT COUNT(*) FROM public_leads WHERE tenant_id = ? AND status = 'new'",        [$tenant['id']]),
            'contacting' => (int) db_fetch_column("SELECT COUNT(*) FROM public_leads WHERE tenant_id = ? AND status = 'contacting'", [$tenant['id']]),
            'converted'  => (int) db_fetch_column("SELECT COUNT(*) FROM public_leads WHERE tenant_id = ? AND status = 'converted'",  [$tenant['id']]),
            'dismissed'  => (int) db_fetch_column("SELECT COUNT(*) FROM public_leads WHERE tenant_id = ? AND status = 'dismissed'",  [$tenant['id']]),
        ];

        // ── Comments ─────────────────────────────────────────────────────
        $commentStatusFilter = $_GET['comment_status'] ?? 'pending';
        $this->ensureCommentsTable();
        $comments = db_fetch_all(
            "SELECT * FROM public_comments WHERE tenant_id = ? AND status = ? ORDER BY created_at DESC LIMIT 100",
            [$tenant['id'], $commentStatusFilter]
        );
        $commentCounts = [
            'pending'  => (int) db_fetch_column("SELECT COUNT(*) FROM public_comments WHERE tenant_id = ? AND status = 'pending'",  [$tenant['id']]),
            'approved' => (int) db_fetch_column("SELECT COUNT(*) FROM public_comments WHERE tenant_id = ? AND status = 'approved'", [$tenant['id']]),
            'rejected' => (int) db_fetch_column("SELECT COUNT(*) FROM public_comments WHERE tenant_id = ? AND status = 'rejected'", [$tenant['id']]),
        ];

        // ── Campaigns ────────────────────────────────────────────────────
        $campaigns = db_fetch_all(
            "SELECT mc.*, u.name as creator_name FROM marketing_campaigns mc
             LEFT JOIN users u ON mc.created_by = u.id
             WHERE mc.tenant_id = ? ORDER BY mc.created_at DESC LIMIT 50",
            [$tenant['id']]
        );

        // ── Gallery (Highlights) ─────────────────────────────────────────
        $allCuratedMedia = db_fetch_all("
            SELECT cm.*,
                   u.name as user_name,
                   u.avatar_url as user_avatar,
                   COALESCE(ps.title, a.title, 'Destaque') as source_title
            FROM curated_media cm
            LEFT JOIN user_step_responses usr ON cm.source_type = 'step' AND cm.source_id = usr.id
            LEFT JOIN program_steps ps ON usr.step_id = ps.id
            LEFT JOIN user_program_progress upp ON usr.progress_id = upp.id

            LEFT JOIN activity_proofs ap ON cm.source_type = 'activity' AND cm.source_id = ap.id
            LEFT JOIN user_activities ua ON ap.user_activity_id = ua.id
            LEFT JOIN activities a ON ua.activity_id = a.id

            LEFT JOIN users u ON u.id = COALESCE(upp.user_id, ua.user_id)
            WHERE cm.tenant_id = ?
            ORDER BY cm.created_at DESC LIMIT 100
        ", [$tenant['id']]);

        // Purge records whose media_url cannot resolve to a valid URL —
        // these are invisible on the public page (sanitizeMediaItems filters them out)
        // and keeping them causes a count mismatch between gallery and public hub.
        $invalidIds    = [];
        $highlightedMedia = [];
        foreach ($allCuratedMedia as $item) {
            $url = trim($item['media_url'] ?? '');
            $valid = false;
            if ($url !== '') {
                if ($url[0] === '[' || $url[0] === '{') {
                    // Same JSON-extraction logic as PublicController::sanitizeMediaItems
                    $decoded = json_decode($url, true);
                    if (is_array($decoded)) {
                        foreach ($decoded as $val) {
                            if (is_string($val) && filter_var($val, FILTER_VALIDATE_URL)) { $valid = true; break; }
                            if (is_array($val)) {
                                foreach ($val as $sub) {
                                    if (is_string($sub) && filter_var($sub, FILTER_VALIDATE_URL)) { $valid = true; break 2; }
                                }
                            }
                        }
                    }
                } else {
                    $valid = (bool) filter_var($url, FILTER_VALIDATE_URL);
                }
            }
            if ($valid) {
                $highlightedMedia[] = $item;
            } else {
                $invalidIds[] = (int) $item['id'];
            }
        }

        // Delete orphaned records so they never reappear
        if (!empty($invalidIds)) {
            $placeholders = implode(',', array_fill(0, count($invalidIds), '?'));
            db_query("DELETE FROM curated_media WHERE id IN ($placeholders)", $invalidIds);
        }

        // ── Top stats ────────────────────────────────────────────────────
        $stats = [
            'new_leads'       => $leadCounts['new'],
            'pending_comments'=> $commentCounts['pending'],
            'total_campaigns' => count($campaigns),
            'converted_leads' => $leadCounts['converted'],
            'highlighted_media' => count($highlightedMedia),
        ];

        View::render('admin/communication/index', [
            'tenant'              => $tenant,
            'user'                => $user,
            'tab'                 => $tab,
            'leads'               => $leads,
            'leadCounts'          => $leadCounts,
            'comments'            => $comments,
            'commentCounts'       => $commentCounts,
            'commentStatusFilter' => $commentStatusFilter,
            'campaigns'           => $campaigns,
            'stats'               => $stats,
            'highlightedMedia'    => $highlightedMedia,
            'pageTitle'           => 'Hub de Comunicação',
            'pageIcon'            => 'hub',
        ]);
    }

    /**
     * Update a lead's status
     * POST /{tenant}/admin/comunicacao/leads/{id}/update
     */
    public function updateLead(array $params): void
    {
        $this->requireAdmin();
        $tenant  = App::tenant();
        $user    = App::user();
        $leadId  = (int)$params['id'];
        $status  = $_POST['status'] ?? '';

        $allowed = ['new', 'contacting', 'converted', 'dismissed'];
        if (!in_array($status, $allowed)) {
            $this->jsonError('Status inválido.');
            return;
        }

        $lead = db_fetch_one("SELECT * FROM public_leads WHERE id = ? AND tenant_id = ?", [$leadId, $tenant['id']]);
        if (!$lead) {
            $this->jsonError('Lead não encontrado.', 404);
            return;
        }

        db_update('public_leads', ['status' => $status], 'id = ?', [$leadId]);

        // Log the contact if status is changing to contacting
        if ($status === 'contacting') {
            db_insert('communication_logs', [
                'tenant_id'     => $tenant['id'],
                'lead_id'       => $leadId,
                'actor_user_id' => $user['id'],
                'channel'       => $_POST['channel'] ?? 'whatsapp',
                'note'          => $_POST['note'] ?? null,
            ]);
        }

        $this->json(['success' => true, 'status' => $status]);
    }

    /**
     * Update a public comment status
     * POST /{tenant}/admin/comunicacao/comentarios/{id}/update
     */
    public function updateComment(array $params): void
    {
        $this->requireAdmin();
        $tenant    = App::tenant();
        $commentId = (int)$params['id'];
        $action    = $_POST['action'] ?? '';

        if (!in_array($action, ['approved', 'rejected'])) {
            $this->jsonError('Ação inválida.');
            return;
        }

        $comment = db_fetch_one(
            "SELECT id FROM public_comments WHERE id = ? AND tenant_id = ?",
            [$commentId, $tenant['id']]
        );
        if (!$comment) {
            $this->jsonError('Comentário não encontrado.', 404);
            return;
        }

        db_update('public_comments', ['status' => $action], 'id = ?', [$commentId]);
        $this->json(['success' => true]);
    }

    /**
     * Send a campaign blast
     * POST /{tenant}/admin/comunicacao/campanhas/enviar
     */
    public function sendCampaign(): void
    {
        $this->requireAdmin();
        $tenant = App::tenant();
        $user   = App::user();

        $title       = trim($_POST['title'] ?? '');
        $content     = trim($_POST['content'] ?? '');
        $type        = $_POST['type'] ?? 'push';
        $targetGroup = $_POST['target_group'] ?? 'members';

        if (empty($title) || empty($content)) {
            $this->jsonError('Título e conteúdo são obrigatórios.');
            return;
        }

        if (!in_array($type, ['push', 'email', 'blast'])) {
            $this->jsonError('Tipo de campanha inválido.');
            return;
        }

        // Save campaign
        $campaignId = db_insert('marketing_campaigns', [
            'tenant_id'    => $tenant['id'],
            'title'        => $title,
            'content'      => $content,
            'type'         => $type,
            'target_group' => $targetGroup,
            'status'       => 'draft',
            'created_by'   => $user['id'],
        ]);

        $sentCount = 0;

        try {
            if ($type === 'push' || $type === 'blast') {
                // Push to members
                $notifService = new NotificationService();
                $notifService->broadcast('campaign', $title, $content, [
                    'priority' => 'normal',
                    'data'     => ['campaign_id' => $campaignId],
                ]);
                // Count active members
                $sentCount = (int) db_fetch_column(
                    "SELECT COUNT(*) FROM users WHERE tenant_id = ? AND status = 'active' AND deleted_at IS NULL",
                    [$tenant['id']]
                );
            }

            if ($type === 'email' || $type === 'blast') {
                // Email to members with email
                $members = db_fetch_all(
                    "SELECT id, name, email FROM users WHERE tenant_id = ? AND status = 'active' AND deleted_at IS NULL AND email IS NOT NULL AND email != ''",
                    [$tenant['id']]
                );
                $emailService = EmailService::getInstance();
                foreach ($members as $member) {
                    try {
                        $emailService->send(
                            $member['email'],
                            $title,
                            '<p>' . nl2br(htmlspecialchars($content)) . '</p>'
                        );
                        $sentCount++;
                    } catch (\Exception $e) {
                        error_log("Campaign email failed for user {$member['id']}: " . $e->getMessage());
                    }
                }
            }

            db_update('marketing_campaigns', [
                'status'     => 'sent',
                'sent_count' => $sentCount,
                'sent_at'    => date('Y-m-d H:i:s'),
            ], 'id = ?', [$campaignId]);

            $this->json([
                'success'   => true,
                'message'   => "Campanha enviada para {$sentCount} destinatários.",
                'sent_count'=> $sentCount,
            ]);

        } catch (\Exception $e) {
            db_update('marketing_campaigns', ['status' => 'failed'], 'id = ?', [$campaignId]);
            $this->jsonError('Erro ao enviar campanha: ' . $e->getMessage());
        }
    }

    /**
     * Remove media from public hub
     * POST /{tenant}/admin/comunicacao/galeria/{id}/unhighlight
     */
    public function unhighlightMedia(array $params): void
    {
        $this->requireAdmin();
        $tenant    = App::tenant();
        $curatedId = (int)$params['id'];

        $media = db_fetch_one(
            "SELECT * FROM curated_media WHERE id = ? AND tenant_id = ?",
            [$curatedId, $tenant['id']]
        );
        if (!$media) {
            $this->jsonError('Mídia não encontrada.', 404);
            return;
        }

        db_query("DELETE FROM curated_media WHERE id = ?", [$curatedId]);

        // Invalidate public landing page cache so the item disappears immediately
        $profile = db_fetch_one("SELECT slug FROM club_profiles WHERE tenant_id = ?", [$tenant['id']]);
        if ($profile) {
            $cacheFile = BASE_PATH . '/storage/framework/cache/pages/landing_' . md5($profile['slug']) . '.html';
            if (file_exists($cacheFile)) {
                @unlink($cacheFile);
            }
        }

        // Backward compatibility: update legacy show_public flag
        if ($media['source_type'] === 'step') {
            $remaining = (int)db_fetch_column("SELECT COUNT(*) FROM curated_media WHERE source_type = 'step' AND source_id = ?", [$media['source_id']]);
            if ($remaining === 0) {
                db_update('user_step_responses', ['show_public' => 0], 'id = ?', [$media['source_id']]);
            }
        } else {
             $remaining = (int)db_fetch_column("SELECT COUNT(*) FROM curated_media WHERE source_type = 'activity' AND source_id = ?", [$media['source_id']]);
            if ($remaining === 0) {
                db_update('activity_proofs', ['show_public' => 0], 'id = ?', [$media['source_id']]);
            }
        }

        $this->json(['success' => true]);
    }


    // ─── Private helpers ───────────────────────────────────────────────────────

    private function ensureTables(): void
    {
        $tables = [
            'public_leads' => "CREATE TABLE IF NOT EXISTS `public_leads` (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            'marketing_campaigns' => "CREATE TABLE IF NOT EXISTS `marketing_campaigns` (
                `id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
                `tenant_id` INT UNSIGNED NOT NULL,
                `title` VARCHAR(200) NOT NULL,
                `type` ENUM('push','email','blast') NOT NULL DEFAULT 'push',
                `content` TEXT NOT NULL,
                `target_group` ENUM('leads','members','all') NOT NULL DEFAULT 'members',
                `status` ENUM('draft','sent','failed') NOT NULL DEFAULT 'draft',
                `sent_count` INT NOT NULL DEFAULT 0,
                `created_by` INT UNSIGNED NULL,
                `sent_at` TIMESTAMP NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX (`tenant_id`),
                FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            'communication_logs' => "CREATE TABLE IF NOT EXISTS `communication_logs` (
                `id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
                `tenant_id` INT UNSIGNED NOT NULL,
                `lead_id` INT UNSIGNED NULL,
                `actor_user_id` INT UNSIGNED NULL,
                `channel` ENUM('whatsapp','email','phone','other') NOT NULL DEFAULT 'whatsapp',
                `note` TEXT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX (`tenant_id`), INDEX (`lead_id`),
                FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        ];


        foreach ($tables as $table => $sql) {
            try {
                db_query($sql);
            } catch (\Exception $e) {
                error_log("CommunicationController: Failed to create table {$table}: " . $e->getMessage());
            }
        }

        \App\Services\CurationService::ensureTable();
    }

    private function ensureCommentsTable(): void
    {
        try {
            db_query("CREATE TABLE IF NOT EXISTS `public_comments` (
                `id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
                `tenant_id` INT UNSIGNED NOT NULL,
                `source_type` VARCHAR(30) NOT NULL,
                `source_id` INT UNSIGNED NOT NULL,
                `author_name` VARCHAR(80) NOT NULL,
                `content` TEXT NOT NULL,
                `session_hash` VARCHAR(64) NULL,
                `status` ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX (`tenant_id`), INDEX (`status`),
                FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch (\Exception $e) {
            error_log("ensureCommentsTable failed: " . $e->getMessage());
        }
    }

    private function requireAdmin(): void
    {
        $user     = App::user();
        $roleName = $user['role_name'] ?? '';
        if (!in_array($roleName, ['admin', 'director', 'associate_director'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Acesso negado']);
            exit;
        }
    }

    private function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    private function jsonError(string $message, int $code = 400): void
    {
        $this->json(['error' => $message], $code);
    }

}

