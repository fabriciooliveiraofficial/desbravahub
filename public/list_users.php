<?php
require_once __DIR__ . '/../bootstrap/bootstrap.php';

$users = db_fetch_all("
    SELECT u.id, u.name, u.email, r.name as role 
    FROM users u 
    JOIN user_roles ur ON u.id = ur.user_id 
    JOIN roles r ON ur.role_id = r.id 
    WHERE u.tenant_id = 1
");

foreach ($users as $u) {
    echo "[{$u['id']}] {$u['name']} ({$u['email']}) - {$u['role']}\n";
}
