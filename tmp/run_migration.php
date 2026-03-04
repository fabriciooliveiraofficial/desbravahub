<?php
require __DIR__ . '/../bootstrap/bootstrap.php';
try {
    $db = \App\Core\App::get('db');
    // We add columns one by one in empty try-catches so if one already exists, the script continues
    try {
        $db->query("ALTER TABLE events ADD COLUMN slug VARCHAR(255) NULL AFTER title");
        echo "Added slug.\n";
    } catch(Exception $e) {}
    try {
        $db->query("ALTER TABLE events ADD COLUMN is_paid TINYINT(1) DEFAULT 0 AFTER status");
        echo "Added is_paid.\n";
    } catch(Exception $e) {}
    try {
        $db->query("ALTER TABLE events ADD COLUMN price DECIMAL(10,2) NULL AFTER is_paid");
        echo "Added price.\n";
    } catch(Exception $e) {}
    try {
        $db->query("ALTER TABLE events ADD COLUMN payment_link VARCHAR(500) NULL AFTER price");
        echo "Added payment_link.\n";
    } catch(Exception $e) {}
    echo "Done.\n";
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
