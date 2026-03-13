<?php
require 'bootstrap/bootstrap.php';

echo "Starting migration: curated_media\n";

try {
    // 1. Create the table
    $sql = "CREATE TABLE IF NOT EXISTS curated_media (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        tenant_id INT UNSIGNED NOT NULL,
        source_type ENUM('step', 'activity') NOT NULL,
        source_id INT UNSIGNED NOT NULL,
        media_url TEXT NOT NULL,
        thumbnail_url TEXT NULL,
        caption VARCHAR(255) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_tenant (tenant_id),
        INDEX idx_source (source_type, source_id),
        FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    db_query($sql);
    echo "✓ Table curated_media created or already exists.\n";

    // 2. Migrate existing highlights from user_step_responses
    echo "Migrating user_step_responses highlights...\n";
    $stepHighlights = db_fetch_all("
        SELECT id, tenant_id, response_url, thumbnail_url, reviewed_at, submitted_at
        FROM user_step_responses 
        WHERE show_public = 1 AND status = 'approved' AND (response_url IS NOT NULL AND response_url != '')
    ");

    foreach ($stepHighlights as $h) {
        $exists = db_fetch_one("SELECT id FROM curated_media WHERE source_type = 'step' AND source_id = ? AND media_url = ?", [$h['id'], $h['response_url']]);
        if (!$exists) {
            db_insert('curated_media', [
                'tenant_id' => $h['tenant_id'],
                'source_type' => 'step',
                'source_id' => $h['id'],
                'media_url' => $h['response_url'],
                'thumbnail_url' => $h['thumbnail_url'],
                'created_at' => $h['reviewed_at'] ?: $h['submitted_at'] ?: date('Y-m-d H:i:s')
            ]);
            echo "  + Migrated step response #{$h['id']}\n";
        }
    }

    // 3. Migrate existing highlights from activity_proofs
    echo "Migrating activity_proofs highlights...\n";
    $activityHighlights = db_fetch_all("
        SELECT id, tenant_id, content, thumbnail_url, reviewed_at, submitted_at
        FROM activity_proofs
        WHERE show_public = 1 AND status = 'approved' AND (content IS NOT NULL AND content != '')
    ");

    foreach ($activityHighlights as $h) {
        $exists = db_fetch_one("SELECT id FROM curated_media WHERE source_type = 'activity' AND source_id = ? AND media_url = ?", [$h['id'], $h['content']]);
        if (!$exists) {
            db_insert('curated_media', [
                'tenant_id' => $h['tenant_id'],
                'source_type' => 'activity',
                'source_id' => $h['id'],
                'media_url' => $h['content'],
                'thumbnail_url' => $h['thumbnail_url'],
                'created_at' => $h['reviewed_at'] ?: $h['submitted_at'] ?: date('Y-m-d H:i:s')
            ]);
            echo "  + Migrated activity proof #{$h['id']}\n";
        }
    }

    echo "Migration completed successfully!\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
