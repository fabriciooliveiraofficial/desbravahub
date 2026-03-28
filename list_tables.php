<?php
require_once __DIR__ . '/helpers/env.php';
env_load();
require_once __DIR__ . '/helpers/config.php';
require_once __DIR__ . '/helpers/database.php';

try {
    $tables = db_fetch_all("SHOW TABLES");
    foreach ($tables as $t) {
        $name = array_values($t)[0];
        echo $name . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
