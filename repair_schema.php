<?php
/**
 * Schema Repair Script — learning_categories
 *
 * Run this once on the hosting server to ensure the required columns
 * (type, sort_order, status) exist in the learning_categories table.
 *
 * Safe to run multiple times — each ALTER is skipped if the column already exists.
 *
 * Usage: php repair_schema.php  (CLI)
 *        OR visit https://yoursite.com/repair_schema.php  (browser, then DELETE the file)
 */

define('REPAIR_MODE', true);
require_once __DIR__ . '/bootstrap/bootstrap.php';

header('Content-Type: text/plain; charset=UTF-8');

echo "=== DesbravaHub Schema Repair: learning_categories ===\n\n";

// -----------------------------------------------------------------------
// Helper: check if a column exists and add it if not
// -----------------------------------------------------------------------
function ensure_column(string $table, string $column, string $definition): void
{
    try {
        $cols = array_column(db_fetch_all("SHOW COLUMNS FROM `{$table}` LIKE '{$column}'"), 'Field');
        if (empty($cols)) {
            db_query("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}");
            echo "[ADDED]   {$table}.{$column}\n";
        } else {
            echo "[EXISTS]  {$table}.{$column}\n";
        }
    } catch (\Exception $e) {
        echo "[ERROR]   {$table}.{$column} — " . $e->getMessage() . "\n";
    }
}

// -----------------------------------------------------------------------
// 1. learning_categories columns
// -----------------------------------------------------------------------
echo "[1] Checking learning_categories columns:\n";
ensure_column('learning_categories', 'type',       "ENUM('specialty','class','both') NOT NULL DEFAULT 'specialty'");
ensure_column('learning_categories', 'sort_order', "INT NOT NULL DEFAULT 0");
ensure_column('learning_categories', 'status',     "VARCHAR(20) NOT NULL DEFAULT 'active'");

// -----------------------------------------------------------------------
// 2. learning_programs columns
// -----------------------------------------------------------------------
echo "\n[2] Checking learning_programs columns:\n";
ensure_column('learning_programs', 'sort_order', "INT NOT NULL DEFAULT 0");

// -----------------------------------------------------------------------
// 3. Verify that existing rows have a valid type value
// -----------------------------------------------------------------------
echo "\n[3] Back-filling NULL / empty type values in learning_categories:\n";
try {
    $fixed = db_query(
        "UPDATE learning_categories SET type = 'specialty' WHERE type IS NULL OR type = ''"
    );
    echo "[OK]  Rows patched: " . (is_object($fixed) ? $fixed->rowCount() : '?') . "\n";
} catch (\Exception $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
}

// -----------------------------------------------------------------------
// 4. Quick sanity count
// -----------------------------------------------------------------------
echo "\n[4] Category counts by type:\n";
try {
    $rows = db_fetch_all(
        "SELECT type, COUNT(*) as total FROM learning_categories GROUP BY type"
    );
    if (empty($rows)) {
        echo "  (no rows in learning_categories)\n";
    } else {
        foreach ($rows as $r) {
            echo "  type={$r['type']}  count={$r['total']}\n";
        }
    }
} catch (\Exception $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
}

echo "\n=== Repair complete. Delete this file from the server! ===\n";
