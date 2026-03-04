<?php
require_once __DIR__ . '/../bootstrap/bootstrap.php';

header('Content-Type: text/plain; charset=UTF-8');

$userId = 11;
$user = db_fetch_one("SELECT * FROM users WHERE id = ?", [$userId]);

if ($user) {
    echo "User $userId found:\n";
    print_r($user);
    
    $tenant = db_fetch_one("SELECT * FROM tenants WHERE id = ?", [$user['tenant_id']]);
    echo "\nBelongs to Tenant:\n";
    print_r($tenant);
} else {
    echo "User $userId NOT FOUND in 'users' table.\n";
    
    echo "\nSample users:\n";
    $samples = db_fetch_all("SELECT id, name, tenant_id FROM users LIMIT 10");
    print_r($samples);
}
