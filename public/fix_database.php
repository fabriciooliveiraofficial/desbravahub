<?php
/**
 * ONE-TIME FIX: Increase icon column size and fix truncated data
 */

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/helpers/env.php';
env_load();
require_once BASE_PATH . '/helpers/config.php';
require_once BASE_PATH . '/helpers/database.php';

header('Content-Type: text/plain; charset=utf-8');

try {
    $pdo = db();
    echo "✅ Database connection OK\n\n";

    echo "--- Phase 1: Altering Tables ---\n";
    
    // Increase size in learning_categories
    $pdo->exec("ALTER TABLE learning_categories MODIFY COLUMN icon VARCHAR(255) DEFAULT '📚'");
    echo "✅ learning_categories.icon increased to VARCHAR(255)\n";

    // Increase size in learning_programs
    $pdo->exec("ALTER TABLE learning_programs MODIFY COLUMN icon VARCHAR(255) DEFAULT '📘'");
    echo "✅ learning_programs.icon increased to VARCHAR(255)\n";

    echo "\n--- Phase 2: Fixing Truncated Data ---\n";

    // Fix ADRA icon (common humanitarian icon)
    $stmt = $pdo->prepare("UPDATE learning_categories SET icon = 'mdi:hand-heart' WHERE icon = 'mdi:donati'");
    $stmt->execute();
    echo "✅ Fixed " . $stmt->rowCount() . " rows with 'mdi:donati' -> 'mdi:hand-heart'\n";

    // Fix other potential truncations if we find patterns (not known yet, but we'll stick to the reported one)
    
    echo "\n--- Phase 3: Verification ---\n";
    $stmt = $pdo->query("DESCRIBE learning_categories");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($row['Field'] === 'icon') {
            echo "Category Icon Column: " . $row['Type'] . "\n";
        }
    }

    echo "\n✅ FIX COMPLETED SUCCESSFULLY\n";

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
