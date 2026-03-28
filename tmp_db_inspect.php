<?php
require_once __DIR__ . '/bootstrap/bootstrap.php';

$tenantSlug = 'cruzeiro-do-sul'; // Default or common slug to test
$tenant = db_fetch_one("SELECT * FROM tenants WHERE slug = ?", [$tenantSlug]);

if (!$tenant) {
    echo "Tenant not found: $tenantSlug\n";
    $tenant = db_fetch_one("SELECT * FROM tenants LIMIT 1");
    if (!$tenant) {
        echo "No tenants found in DB.\n";
        exit;
    }
    echo "Using first available tenant: " . $tenant['slug'] . "\n";
}

echo "Tenant: " . $tenant['name'] . " (ID: " . $tenant['id'] . ")\n";

echo "\n--- Learning Programs with NULL or Inexistent Categories ---\n";
$programs = db_fetch_all("
    SELECT p.id, p.name, p.category_id, p.type, c.name as category_name
    FROM learning_programs p
    LEFT JOIN learning_categories c ON p.category_id = c.id
    WHERE p.tenant_id = ?
", [$tenant['id']]);

foreach ($programs as $p) {
    if ($p['category_name'] === null) {
        echo "ID: {$p['id']} | Name: {$p['name']} | Type: {$p['type']} | Cat ID: " . ($p['category_id'] ?? 'NULL') . " | Status: CATEGORY MISSING\n";
    }
}

echo "\n--- All Learning Categories for this Tenant ---\n";
$categories = db_fetch_all("SELECT id, name, type FROM learning_categories WHERE tenant_id = ?", [$tenant['id']]);
foreach ($categories as $c) {
    echo "ID: {$c['id']} | Name: {$c['name']} | Type: {$c['type']}\n";
}
