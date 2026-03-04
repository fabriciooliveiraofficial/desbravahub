<?php
/**
 * Migration: Fix Status ENUMs
 * Ensures 'user_step_responses' and 'user_requirement_progress' have the correct ENUM values.
 */

require_once __DIR__ . '/../bootstrap/bootstrap.php';

try {
    $pdo = db();
    echo "Database connection successful.\n";
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Updating user_step_responses.status ENUM...\n";
    $pdo->exec("
        ALTER TABLE `user_step_responses` 
        MODIFY COLUMN `status` ENUM('not_started', 'in_progress', 'draft', 'submitted', 'approved', 'rejected') 
        DEFAULT 'not_started'
    ");
    echo "✅ user_step_responses updated.\n";

    echo "Updating user_requirement_progress.status ENUM...\n";
    // First, check if the table exists (Legacy specialty system)
    $stmt = $pdo->query("SHOW TABLES LIKE 'user_requirement_progress'");
    if ($stmt->rowCount() > 0) {
        $pdo->exec("
            ALTER TABLE `user_requirement_progress` 
            MODIFY COLUMN `status` ENUM('pending', 'draft', 'answered', 'submitted', 'approved', 'rejected') 
            DEFAULT 'pending'
        ");
        echo "✅ user_requirement_progress updated.\n";
    } else {
        echo "ℹ️ user_requirement_progress table not found (ignoring).\n";
    }

    echo "\n✅ ENUM Fix completed successfully!\n";

} catch (PDOException $e) {
    echo "\n❌ Database Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "\n❌ General Error: " . $e->getMessage() . "\n";
}
