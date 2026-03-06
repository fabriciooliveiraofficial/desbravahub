<?php
require_once __DIR__ . '/bootstrap/bootstrap.php';
try {
    db_query("ALTER TABLE `public_interactions` ADD COLUMN `session_id` VARCHAR(255) NULL AFTER `interaction_type`");
    echo "Column added";
} catch (\Exception $e) {
    echo $e->getMessage();
}
