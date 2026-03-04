<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/helpers/env.php';
require_once BASE_PATH . '/helpers/config.php';
require_once BASE_PATH . '/helpers/database.php';

try {
    // Get a valid tenant ID first
    $tenant = db_fetch_one("SELECT id, slug FROM tenants WHERE status = 'active' LIMIT 1");
    if (!$tenant) {
        die("No active tenant found for test.\n");
    }

    echo "Testing SOS Leadership Query for Tenant: {$tenant['slug']} (ID: {$tenant['id']})\n";

    $query = "
        SELECT u.id, u.name, r.name as role 
        FROM users u 
        JOIN roles r ON u.role_id = r.id
        WHERE u.tenant_id = ?
        AND r.name IN ('admin', 'director', 'associate_director', 'chaplain', 'instructor', 'counselor', 'leader', 'secretary')
        AND u.status = 'active'
    ";

    $leaders = db_fetch_all($query, [$tenant['id']]);

    if (empty($leaders)) {
        echo "WARNING: No active leaders found for this tenant using the fixed query.\n";
        echo "Check if roles exist: " . count(db_fetch_all("SELECT * FROM roles WHERE tenant_id = ?", [$tenant['id']])) . "\n";
    } else {
        echo "FOUND " . count($leaders) . " leaders:\n";
        foreach ($leaders as $l) {
            echo "- {$l['name']} ({$l['role']})\n";
        }
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
