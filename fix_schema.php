<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('BASE_PATH', __DIR__);

// Bootstrap application logic
require_once BASE_PATH . '/helpers/env.php';
env_load();
require_once BASE_PATH . '/helpers/config.php';
require_once BASE_PATH . '/helpers/database.php';

function column_exists($table, $column) {
    try {
        $cols = db_fetch_all("DESCRIBE $table");
        foreach ($cols as $col) {
            if ($col['Field'] === $column) return true;
        }
    } catch (Exception $e) {
        echo "Error checking $table: " . $e->getMessage() . "\n";
    }
    return false;
}

echo "--- DATABASE SCHEMA FIX START ---\n";

// Table 1: user_step_responses
if (!column_exists('user_step_responses', 'thumbnail_url')) {
    echo "Adding thumbnail_url to user_step_responses...\n";
    try {
        db_query("ALTER TABLE user_step_responses ADD COLUMN thumbnail_url TEXT NULL AFTER show_public");
        echo " [SUCCESS]\n";
    } catch (Exception $e) {
        echo " [FAILED] " . $e->getMessage() . "\n";
    }
} else {
    echo "thumbnail_url already exists in user_step_responses.\n";
}

// Table 2: activity_proofs
if (!column_exists('activity_proofs', 'thumbnail_url')) {
    echo "Adding thumbnail_url to activity_proofs...\n";
    try {
        db_query("ALTER TABLE activity_proofs ADD COLUMN thumbnail_url TEXT NULL AFTER show_public");
        echo " [SUCCESS]\n";
    } catch (Exception $e) {
        echo " [FAILED] " . $e->getMessage() . "\n";
    }
} else {
    echo "thumbnail_url already exists in activity_proofs.\n";
}

echo "--- DATABASE SCHEMA FIX END ---\n";
