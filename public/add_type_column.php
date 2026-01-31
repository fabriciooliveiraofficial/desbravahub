<?php
// Mock server vars for CLI
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/';

require_once __DIR__ . '/../bootstrap/bootstrap.php';

try {
    echo "Adding 'type' column to learning_categories...\n";
    
    // Check if column exists
    $columns = db_fetch_all("SHOW COLUMNS FROM learning_categories LIKE 'type'");
    if (empty($columns)) {
        db_query("ALTER TABLE learning_categories ADD COLUMN type ENUM('specialty', 'class', 'both') DEFAULT 'specialty' AFTER icon");
        echo "Column 'type' added successfully.\n";
    } else {
        echo "Column 'type' already exists.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
