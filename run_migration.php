<?php
/**
 * Referral System Migration Executor (v2)
 */

// Basic bootstrap
define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/helpers/env.php';
require_once BASE_PATH . '/helpers/config.php';
require_once BASE_PATH . '/helpers/database.php';

// Try to connect and execute migration
try {
    $sqlFile = BASE_PATH . '/database/migrations/create_referral_invites.sql';
    if (!file_exists($sqlFile)) {
        die("ERROR: Migration file not found at $sqlFile\n");
    }

    $sql = file_get_contents($sqlFile);
    
    // Split by semicolon
    $queries = preg_split('/;\s*$/m', $sql);
    
    echo "Running migration...\n";
    foreach ($queries as $query) {
        $query = trim($query);
        if (empty($query)) continue;
        
        db_query($query);
        echo "Executed query.\n";
    }
    
    echo "SUCCESS: Migration completed.\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
