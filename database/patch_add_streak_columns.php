<?php
/**
 * Patch: Add Streak Columns to Users Table
 */

require __DIR__ . '/../bootstrap/bootstrap.php';

echo "Adding streak columns to users table...\n";

try {
    db_query("ALTER TABLE users ADD COLUMN current_streak INT UNSIGNED NOT NULL DEFAULT 0 AFTER level_id");
    db_query("ALTER TABLE users ADD COLUMN last_streak_date DATE NULL AFTER current_streak");
    echo "Columns added successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    // Check if columns already exist
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Columns already exist. Skipping.\n";
    } else {
        exit(1);
    }
}
