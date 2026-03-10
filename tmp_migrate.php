<?php
require 'bootstrap/bootstrap.php';
try {
    db_query("ALTER TABLE learning_programs ADD COLUMN sort_order INT DEFAULT 0 AFTER status");
    echo "Migration successful\n";
} catch (Exception $e) {
    echo "Migration error: " . $e->getMessage() . "\n";
}
