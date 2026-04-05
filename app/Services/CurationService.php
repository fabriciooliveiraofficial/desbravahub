<?php
/**
 * Curation Service
 *
 * Centralizes the initialization and management of the curated media system.
 */

namespace App\Services;

use App\Core\App;

class CurationService
{
    /** Prevent SHOW COLUMNS from running more than once per request */
    private static bool $_ensured = false;

    public static function ensureTable(): void
    {
        if (self::$_ensured) return;
        self::$_ensured = true;

        try {
            db_query("CREATE TABLE IF NOT EXISTS `curated_media` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `tenant_id` INT UNSIGNED NOT NULL,
                `source_type` ENUM('step', 'activity') NOT NULL,
                `source_id` INT UNSIGNED NOT NULL,
                `media_url` TEXT NOT NULL,
                `thumbnail_url` TEXT NULL,
                `thumbnail_attempted` TINYINT(1) NOT NULL DEFAULT 0,
                `caption` VARCHAR(255) NULL,
                `display_name` VARCHAR(255) NULL,
                `display_avatar` TEXT NULL,
                `user_id` INT UNSIGNED NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_cm_tenant (tenant_id),
                INDEX idx_cm_source (source_type, source_id),
                FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            // Auto-repair: add columns introduced after initial table creation
            $cols = array_column(db_fetch_all("SHOW COLUMNS FROM curated_media"), 'Field');

            $needsBackfill = false;

            if (!in_array('user_id', $cols)) {
                db_query("ALTER TABLE curated_media ADD COLUMN `user_id` INT UNSIGNED NULL AFTER `caption`");
                $needsBackfill = true;
            }
            if (!in_array('display_name', $cols)) {
                db_query("ALTER TABLE curated_media ADD COLUMN `display_name` VARCHAR(255) NULL AFTER `caption`");
                $needsBackfill = true;
            }
            if (!in_array('display_avatar', $cols)) {
                db_query("ALTER TABLE curated_media ADD COLUMN `display_avatar` TEXT NULL AFTER `display_name`");
                $needsBackfill = true;
            }
            if (!in_array('thumbnail_attempted', $cols)) {
                db_query("ALTER TABLE curated_media ADD COLUMN `thumbnail_attempted` TINYINT(1) NOT NULL DEFAULT 0 AFTER `thumbnail_url`");
            }
            if (!in_array('thumbnail_retries', $cols)) {
                db_query("ALTER TABLE curated_media ADD COLUMN `thumbnail_retries` TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER `thumbnail_attempted`");
            }
            if (!in_array('thumbnail_retry_after', $cols)) {
                db_query("ALTER TABLE curated_media ADD COLUMN `thumbnail_retry_after` DATETIME NULL AFTER `thumbnail_retries`");
                // One-time migration: reset old failed thumbnails so the new retry system can attempt them
                db_query("UPDATE curated_media SET thumbnail_attempted = 0, thumbnail_retries = 0 WHERE thumbnail_url IS NULL AND thumbnail_attempted = 1 AND thumbnail_retry_after IS NULL");
            }

            if ($needsBackfill) {
                self::backfillDisplayData();
            }

        } catch (\Exception $e) {
            error_log("CurationService: Failed to ensure curated_media: " . $e->getMessage());
        }
    }

    /**
     * Backfill display_name, display_avatar, user_id for existing rows.
     * Uses two separate UPDATE paths (step / activity).
     * Rows whose source chains are broken are left with NULL — they'll show
     * their raw fallbacks on the public page.
     */
    public static function backfillDisplayData(): void
    {
        try {
            // Step type: response → progress → user
            db_query("
                UPDATE curated_media cm
                JOIN user_step_responses usr  ON cm.source_type = 'step' AND cm.source_id = usr.id
                JOIN user_program_progress upp ON usr.progress_id = upp.id
                JOIN users u                   ON u.id = upp.user_id
                SET cm.user_id       = u.id,
                    cm.display_name  = u.name,
                    cm.display_avatar = u.avatar_url
                WHERE cm.display_name IS NULL
            ");

            // Activity type: proof → user_activity → user
            db_query("
                UPDATE curated_media cm
                JOIN activity_proofs ap ON cm.source_type = 'activity' AND cm.source_id = ap.id
                JOIN user_activities ua ON ap.user_activity_id = ua.id
                JOIN users u            ON u.id = ua.user_id
                SET cm.user_id        = u.id,
                    cm.display_name   = u.name,
                    cm.display_avatar  = u.avatar_url
                WHERE cm.display_name IS NULL
            ");

            // Backfill caption from step title
            db_query("
                UPDATE curated_media cm
                JOIN user_step_responses usr ON cm.source_type = 'step' AND cm.source_id = usr.id
                JOIN program_steps ps        ON ps.id = usr.step_id
                SET cm.caption = ps.title
                WHERE cm.caption IS NULL AND ps.title IS NOT NULL
            ");

            // Backfill caption from activity title
            db_query("
                UPDATE curated_media cm
                JOIN activity_proofs ap ON cm.source_type = 'activity' AND cm.source_id = ap.id
                JOIN user_activities ua ON ap.user_activity_id = ua.id
                JOIN activities a       ON a.id = ua.activity_id
                SET cm.caption = a.title
                WHERE cm.caption IS NULL AND a.title IS NOT NULL
            ");

        } catch (\Exception $e) {
            error_log("CurationService: backfillDisplayData failed: " . $e->getMessage());
        }
    }

    /**
     * Resolve display data (name, avatar, title) for a given source at INSERT time.
     * Returns array with keys: display_name, display_avatar, user_id, caption.
     */
    public static function resolveDisplayData(string $sourceType, int $sourceId): array
    {
        $result = ['display_name' => null, 'display_avatar' => null, 'user_id' => null, 'caption' => null];

        try {
            if ($sourceType === 'step') {
                $row = db_fetch_one("
                    SELECT u.id as uid, u.name, u.avatar_url, ps.title
                    FROM user_step_responses usr
                    JOIN user_program_progress upp ON usr.progress_id = upp.id
                    JOIN users u                   ON u.id = upp.user_id
                    LEFT JOIN program_steps ps     ON ps.id = usr.step_id
                    WHERE usr.id = ?
                ", [$sourceId]);
            } else {
                $row = db_fetch_one("
                    SELECT u.id as uid, u.name, u.avatar_url, a.title
                    FROM activity_proofs ap
                    JOIN user_activities ua ON ap.user_activity_id = ua.id
                    JOIN users u            ON u.id = ua.user_id
                    LEFT JOIN activities a  ON a.id = ua.activity_id
                    WHERE ap.id = ?
                ", [$sourceId]);
            }

            if ($row) {
                $result['user_id']       = $row['uid']        ?: null;
                $result['display_name']  = $row['name']       ?: null;
                $result['display_avatar']= $row['avatar_url'] ?: null;
                $result['caption']       = $row['title']      ?: null;
            }
        } catch (\Exception $e) {
            error_log("CurationService: resolveDisplayData failed: " . $e->getMessage());
        }

        return $result;
    }

    /**
     * Migrate legacy data from show_public flags to curated_media table
     */
    public static function migrateLegacyData(): void
    {
        $tenantId = App::tenantId();
        if (!$tenantId) return;

        self::ensureTable();

        // Simple lock/check: only migrate if curated_media is empty for this tenant
        $exists = db_fetch_column("SELECT COUNT(*) FROM curated_media WHERE tenant_id = ?", [$tenantId]);
        if ($exists > 0) return;

        // Migrate Steps
        $stepHighlights = db_fetch_all("
            SELECT usr.id, usr.response_url, usr.thumbnail_url, usr.reviewed_at, usr.submitted_at,
                   u.name as uname, u.avatar_url as uavatar, u.id as uid,
                   ps.title as step_title
            FROM user_step_responses usr
            JOIN user_program_progress upp ON usr.progress_id = upp.id
            JOIN users u                   ON u.id = upp.user_id
            LEFT JOIN program_steps ps     ON ps.id = usr.step_id
            WHERE usr.tenant_id = ? AND usr.show_public = 1 AND usr.status = 'approved'
              AND (usr.response_url IS NOT NULL AND usr.response_url != '')
        ", [$tenantId]);

        foreach ($stepHighlights as $h) {
            db_insert('curated_media', [
                'tenant_id'      => $tenantId,
                'source_type'    => 'step',
                'source_id'      => $h['id'],
                'media_url'      => $h['response_url'],
                'thumbnail_url'  => $h['thumbnail_url'],
                'caption'        => $h['step_title'],
                'display_name'   => $h['uname'],
                'display_avatar' => $h['uavatar'],
                'user_id'        => $h['uid'],
                'created_at'     => $h['reviewed_at'] ?: $h['submitted_at'] ?: date('Y-m-d H:i:s')
            ]);
        }

        // Migrate Activities
        $activityHighlights = db_fetch_all("
            SELECT ap.id, ap.content, ap.thumbnail_url, ap.reviewed_at, ap.submitted_at,
                   u.name as uname, u.avatar_url as uavatar, u.id as uid,
                   a.title as act_title
            FROM activity_proofs ap
            JOIN user_activities ua ON ap.user_activity_id = ua.id
            JOIN users u            ON u.id = ua.user_id
            LEFT JOIN activities a  ON a.id = ua.activity_id
            WHERE ap.tenant_id = ? AND ap.show_public = 1 AND ap.status = 'approved'
              AND (ap.content IS NOT NULL AND ap.content != '')
        ", [$tenantId]);

        foreach ($activityHighlights as $h) {
            $url = trim((string) $h['content']);
            // Try JSON extraction
            if ($url !== '' && ($url[0] === '[' || $url[0] === '{')) {
                $decoded = json_decode($url, true);
                $extracted = null;
                if (is_array($decoded)) {
                    foreach ($decoded as $val) {
                        if (is_string($val) && filter_var($val, FILTER_VALIDATE_URL)) { $extracted = $val; break; }
                        if (is_array($val)) {
                            foreach ($val as $sub) {
                                if (is_string($sub) && filter_var($sub, FILTER_VALIDATE_URL)) { $extracted = $sub; break 2; }
                            }
                        }
                    }
                }
                $url = $extracted ?? '';
            }
            if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) continue;

            db_insert('curated_media', [
                'tenant_id'      => $tenantId,
                'source_type'    => 'activity',
                'source_id'      => $h['id'],
                'media_url'      => $url,
                'thumbnail_url'  => $h['thumbnail_url'],
                'caption'        => $h['act_title'],
                'display_name'   => $h['uname'],
                'display_avatar' => $h['uavatar'],
                'user_id'        => $h['uid'],
                'created_at'     => $h['reviewed_at'] ?: $h['submitted_at'] ?: date('Y-m-d H:i:s')
            ]);
        }
    }
}
