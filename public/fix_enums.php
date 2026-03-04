<?php
/**
 * Browser-based Migration: Fix Status ENUMs
 * 
 * Access: http://localhost:8080/fix_enums.php
 */

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/helpers/env.php';
env_load();
require_once BASE_PATH . '/helpers/config.php';
require_once BASE_PATH . '/helpers/database.php';

header('Content-Type: text/plain; charset=utf-8');

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
    echo "\nPOR FAVOR, EXCLUA ESTE ARQUIVO APÓS A EXECUÇÃO.";

} catch (PDOException $e) {
    echo "\n❌ Database Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "\n❌ General Error: " . $e->getMessage() . "\n";
}
