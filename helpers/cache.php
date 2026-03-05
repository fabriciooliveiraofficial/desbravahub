<?php
/**
 * Cache Helper Functions
 */

/**
 * Clear the public landing page cache for a specific tenant
 * 
 * @param int $tenantId
 * @return bool
 */
function clear_club_landing_cache(int $tenantId): bool
{
    // Find the club profile slug
    $profile = db_fetch_one("SELECT slug FROM club_profiles WHERE tenant_id = ?", [$tenantId]);
    
    if (!$profile || empty($profile['slug'])) {
        return false;
    }

    $slug = $profile['slug'];
    $cacheDir = BASE_PATH . '/storage/framework/cache/pages';
    $cacheFile = $cacheDir . '/landing_' . md5($slug) . '.html';

    if (file_exists($cacheFile)) {
        return unlink($cacheFile);
    }

    return true;
}
