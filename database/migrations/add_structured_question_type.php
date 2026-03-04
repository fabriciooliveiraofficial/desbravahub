<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/helpers/env.php';
require_once BASE_PATH . '/helpers/config.php';
require_once BASE_PATH . '/helpers/database.php';

try {
    echo "Adding 'structured' question type...\n";
    db_query("ALTER TABLE program_questions MODIFY COLUMN type ENUM('text', 'single_choice', 'multiple_choice', 'true_false', 'file_upload', 'url', 'manual', 'structured') NOT NULL DEFAULT 'text'");
    echo "Migration complete.\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
