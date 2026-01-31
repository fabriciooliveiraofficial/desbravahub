<?php
/**
 * Patch: Add missing columns to specialty_assignments
 * 
 * Run: php database/patch_add_assignment_columns.php
 */

require_once __DIR__ . '/../bootstrap/bootstrap.php';

echo "Patching specialty_assignments table...\n";

try {
    // Add due_date column if not exists
    db_query("ALTER TABLE specialty_assignments ADD COLUMN IF NOT EXISTS `due_date` DATE NULL AFTER `status`");
    echo "- due_date column: OK\n";

    // Add instructions column if not exists  
    db_query("ALTER TABLE specialty_assignments ADD COLUMN IF NOT EXISTS `instructions` TEXT NULL AFTER `due_date`");
    echo "- instructions column: OK\n";

    echo "\nPatch completed!\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";

    // Try alternative method for MySQL < 8.0
    try {
        echo "Trying alternative method...\n";

        // Check if column exists
        $cols = db_fetch_all("SHOW COLUMNS FROM specialty_assignments LIKE 'due_date'");
        if (empty($cols)) {
            db_query("ALTER TABLE specialty_assignments ADD COLUMN `due_date` DATE NULL AFTER `status`");
            echo "- due_date column added\n";
        } else {
            echo "- due_date column already exists\n";
        }

        $cols = db_fetch_all("SHOW COLUMNS FROM specialty_assignments LIKE 'instructions'");
        if (empty($cols)) {
            db_query("ALTER TABLE specialty_assignments ADD COLUMN `instructions` TEXT NULL AFTER `due_date`");
            echo "- instructions column added\n";
        } else {
            echo "- instructions column already exists\n";
        }

        echo "\nPatch completed!\n";
    } catch (PDOException $e2) {
        echo "Alternative method also failed: " . $e2->getMessage() . "\n";
    }
}
