<?php
require 'bootstrap/bootstrap.php';

$migrations = [
    'learning_programs.sort_order' => "ALTER TABLE learning_programs ADD COLUMN sort_order INT DEFAULT 0 AFTER status",
    'learning_categories.type'     => "ALTER TABLE learning_categories ADD COLUMN type ENUM('specialty', 'class', 'both') NOT NULL DEFAULT 'specialty' AFTER icon",
    'learning_categories.sort_order' => "ALTER TABLE learning_categories ADD COLUMN sort_order INT NOT NULL DEFAULT 0 AFTER status",
];

foreach ($migrations as $label => $sql) {
    try {
        db_query($sql);
        echo "OK: $label\n";
    } catch (Exception $e) {
        // "Duplicate column" means already exists — that's fine
        echo "SKIP ($label): " . $e->getMessage() . "\n";
    }
}
