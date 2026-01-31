<?php
ini_set('display_errors', 1);
require_once dirname(__DIR__) . '/bootstrap/bootstrap.php';
use App\Core\App;

$slug = 'demo-club';
$tenant = db_fetch_one("SELECT * FROM tenants WHERE slug = ?", [$slug]);

// Check if exists first
$exists = db_fetch_one("SELECT id FROM roles WHERE tenant_id = ? AND name = 'counselor'", [$tenant['id']]);

if ($exists) {
    echo "Role 'counselor' already exists (ID: {$exists['id']})\n";
} else {
    db_insert('roles', [
        'tenant_id' => $tenant['id'],
        'name' => 'counselor',
        'display_name' => 'Conselheiro',
        'is_system' => 1,
        'description' => 'Líder de unidade responsável pelos desbravadores'
    ]);
    echo "Role 'counselor' inserted successfully!\n";
}
