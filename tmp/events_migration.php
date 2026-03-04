<?php
require __DIR__ . '/../bootstrap/bootstrap.php';

try {
    $db = \App\Core\App::get('db');
    
    // Check if columns exist
    $columns = db_fetch_all("PRAGMA table_info(events)");
    $columnNames = array_column($columns, 'name');
    
    // Add slug if missing
    if (!in_array('slug', $columnNames)) {
        db_query("ALTER TABLE events ADD COLUMN slug VARCHAR(255) NULL AFTER title");
        echo "Added slug column.\n";
    }
    
    // Add is_paid if missing
    if (!in_array('is_paid', $columnNames)) {
        db_query("ALTER TABLE events ADD COLUMN is_paid TINYINT(1) DEFAULT 0");
        echo "Added is_paid column.\n";
    }
    
    // Add price if missing
    if (!in_array('price', $columnNames)) {
        db_query("ALTER TABLE events ADD COLUMN price DECIMAL(10,2) NULL");
        echo "Added price column.\n";
    }
    
    // Add payment_link if missing
    if (!in_array('payment_link', $columnNames)) {
        db_query("ALTER TABLE events ADD COLUMN payment_link VARCHAR(500) NULL");
        echo "Added payment_link column.\n";
    }
    
    echo "Migration complete.\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
