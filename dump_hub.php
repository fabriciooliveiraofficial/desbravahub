<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/helpers/env.php';
env_load();
require_once BASE_PATH . '/helpers/config.php';
require_once BASE_PATH . '/helpers/database.php';
require_once BASE_PATH . '/helpers/url.php';
require_once BASE_PATH . '/helpers/media.php';
require_once BASE_PATH . '/app/Core/View.php';

// Mock Router classes/globals if needed
$slug = 'clube-demo';
$profile = db_fetch_one("SELECT * FROM club_profiles WHERE slug = ?", [$slug]);
$tenantId = $profile['tenant_id'];

// Minimal PublicController logic simulation
$events = db_fetch_all("SELECT * FROM events WHERE tenant_id = ? AND status = 'published' AND start_datetime >= CURDATE() ORDER BY start_datetime ASC", [$tenantId]);

$curatedMedia = db_fetch_all("
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
                usr.thumbnail_url as thumbnail_url,
                u.name as user_name,
                u.avatar_url,
                usr.submitted_at as date
            FROM user_step_responses usr
            JOIN program_steps ps ON usr.step_id = ps.id
            JOIN user_program_progress upp ON usr.progress_id = upp.id
            JOIN users u ON upp.user_id = u.id
            WHERE upp.tenant_id = ? AND usr.show_public = 1 AND usr.status = 'approved'
", [$tenantId]);

// Sanitize
$sanitizedMedia = [];
foreach ($curatedMedia as $media) {
    if (!empty($media['media_content'])) {
        $sanitizedMedia[] = $media;
    }
}
$curatedMedia = $sanitizedMedia;

echo "DUMPING HUB MEDIA SECTION:\n";
ob_start();
include BASE_PATH . '/views/public/club_landing.php';
$html = ob_get_clean();

// Extract the insta-grid section
if (preg_match('/<div class="insta-grid">(.*?)<\/div>/s', $html, $matches)) {
    echo $matches[1] . "\n";
} else {
    echo "insta-grid section NOT FOUND in HTML output!\n";
    // Check if "Nenhuma mídia" is there
    if (strpos($html, 'Nenhuma mídia') !== false) {
        echo "HUB SHOWS 'NO MEDIA' MESSAGE.\n";
    }
}
