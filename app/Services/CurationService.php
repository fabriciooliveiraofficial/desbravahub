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
    /**
     * Ensure the curated_media table exists
     */
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
                `caption` VARCHAR(255) NULL,
                `user_id` INT UNSIGNED NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_cm_tenant (tenant_id),
                INDEX idx_cm_source (source_type, source_id),
                FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            // Auto-repair: add user_id if table existed before this column was introduced
            $cols = array_column(db_fetch_all("SHOW COLUMNS FROM curated_media"), 'Field');
            if (!in_array('user_id', $cols)) {
                db_query("ALTER TABLE curated_media ADD COLUMN `user_id` INT UNSIGNED NULL AFTER `caption`");
                // Backfill immediately after adding the column (one-time operation)
                self::backfillUserIds();
            }
        } catch (\Exception $e) {
            error_log("CurationService: Failed to ensure curated_media: " . $e->getMessage());
        }
    }

    /**
     * Backfill user_id for existing curated_media rows that have no user_id yet.
     * Called automatically once after the column is added; safe to call again (WHERE IS NULL).
     */
    public static function backfillUserIds(): void
    {
        try {
            // Step type: user_step_responses → user_program_progress → user_id
            db_query("
                UPDATE curated_media cm
                JOIN user_step_responses usr ON cm.source_type = 'step' AND cm.source_id = usr.id
                JOIN user_program_progress upp ON usr.progress_id = upp.id
                SET cm.user_id = upp.user_id
                WHERE cm.user_id IS NULL
            ");
            // Activity type: activity_proofs → user_activities → user_id
            db_query("
                UPDATE curated_media cm
                JOIN activity_proofs ap ON cm.source_type = 'activity' AND cm.source_id = ap.id
                JOIN user_activities ua ON ap.user_activity_id = ua.id
                SET cm.user_id = ua.user_id
                WHERE cm.user_id IS NULL
            ");
        } catch (\Exception $e) {
            error_log("CurationService: backfillUserIds failed: " . $e->getMessage());
        }
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
            SELECT id, response_url, thumbnail_url, reviewed_at, submitted_at
            FROM user_step_responses 
            WHERE tenant_id = ? AND show_public = 1 AND status = 'approved' AND (response_url IS NOT NULL AND response_url != '')
        ", [$tenantId]);

        foreach ($stepHighlights as $h) {
            db_insert('curated_media', [
                'tenant_id' => $tenantId,
                'source_type' => 'step',
                'source_id' => $h['id'],
                'media_url' => $h['response_url'],
                'thumbnail_url' => $h['thumbnail_url'],
                'created_at' => $h['reviewed_at'] ?: $h['submitted_at'] ?: date('Y-m-d H:i:s')
            ]);
        }

        // Migrate Activities — only insert records with a resolvable URL
        $activityHighlights = db_fetch_all("
            SELECT id, content, thumbnail_url, reviewed_at, submitted_at
            FROM activity_proofs
            WHERE tenant_id = ? AND show_public = 1 AND status = 'approved' AND (content IS NOT NULL AND content != '')
        ", [$tenantId]);

        foreach ($activityHighlights as $h) {
            $url = trim((string) $h['content']);
            // Try JSON extraction (same logic as PublicController::sanitizeMediaItems)
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
            // Skip records that cannot resolve to a valid URL
            if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) continue;

            db_insert('curated_media', [
                'tenant_id'    => $tenantId,
                'source_type'  => 'activity',
                'source_id'    => $h['id'],
                'media_url'    => $url,
                'thumbnail_url'=> $h['thumbnail_url'],
                'created_at'   => $h['reviewed_at'] ?: $h['submitted_at'] ?: date('Y-m-d H:i:s')
            ]);
        }
    }
}
