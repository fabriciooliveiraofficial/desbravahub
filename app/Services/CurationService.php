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
    public static function ensureTable(): void
    {
        try {
            db_query("CREATE TABLE IF NOT EXISTS `curated_media` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `tenant_id` INT UNSIGNED NOT NULL,
                `source_type` ENUM('step', 'activity') NOT NULL,
                `source_id` INT UNSIGNED NOT NULL,
                `media_url` TEXT NOT NULL,
                `thumbnail_url` TEXT NULL,
                `caption` VARCHAR(255) NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_cm_tenant (tenant_id),
                INDEX idx_cm_source (source_type, source_id),
                FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch (\Exception $e) {
            error_log("CurationService: Failed to create table curated_media: " . $e->getMessage());
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

        // Migrate Activities
        $activityHighlights = db_fetch_all("
            SELECT id, content, thumbnail_url, reviewed_at, submitted_at
            FROM activity_proofs
            WHERE tenant_id = ? AND show_public = 1 AND status = 'approved' AND (content IS NOT NULL AND content != '')
        ", [$tenantId]);

        foreach ($activityHighlights as $h) {
            db_insert('curated_media', [
                'tenant_id' => $tenantId,
                'source_type' => 'activity',
                'source_id' => $h['id'],
                'media_url' => $h['content'],
                'thumbnail_url' => $h['thumbnail_url'],
                'created_at' => $h['reviewed_at'] ?: $h['submitted_at'] ?: date('Y-m-d H:i:s')
            ]);
        }
    }
}
