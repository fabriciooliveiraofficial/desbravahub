<?php
define('BASE_PATH', __DIR__);
require 'helpers/env.php';
require 'helpers/config.php';
require 'helpers/database.php';

// Manually load env just in case
env_load();

echo "--- DB CONFIG ---\n";
print_r(config('database.connections.mysql'));

try {
    $db = db();
    echo "✓ Database connected.\n";
} catch (Exception $e) {
    echo "✗ Connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "--- CLUB PROFILES ---\n";
try {
    $profiles = db_fetch_all('SELECT slug, tenant_id, display_name FROM club_profiles');
    foreach ($profiles as $p) {
        echo "Slug: [{$p['slug']}], Tenant ID: {$p['tenant_id']}, Name: {$p['display_name']}\n";
    }
} catch (Exception $e) {
    echo "Error fetching profiles: " . $e->getMessage() . "\n";
}

echo "\n--- ALL RECENT EVENTS ---\n";
try {
    $events = db_fetch_all('SELECT id, title, tenant_id, status, start_datetime FROM events ORDER BY id DESC LIMIT 10');
    foreach ($events as $e) {
        echo "ID: {$e['id']}, Title: {$e['title']}, Tenant: {$e['tenant_id']}, Status: {$e['status']}, Start: {$e['start_datetime']}\n";
    }
} catch (Exception $e) {
    echo "Error fetching events: " . $e->getMessage() . "\n";
}

echo "\n--- CACHE FILES ---\n";
$cacheDir = BASE_PATH . '/storage/framework/cache/pages';
if (is_dir($cacheDir)) {
    $files = scandir($cacheDir);
    foreach ($files as $f) {
        if ($f !== '.' && $f !== '..') {
            $path = $cacheDir . '/' . $f;
            echo "File: $f, MTime: " . date('Y-m-d H:i:s', filemtime($path)) . ", Size: " . filesize($path) . " bytes\n";
        }
    }
} else {
    echo "Cache directory not found.\n";
}
