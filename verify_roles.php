<?php
require_once 'bootstrap/bootstrap.php';
$tenant = db_fetch_one("SELECT id FROM tenants WHERE slug='clube-demo'");
$users = db_fetch_all("
    SELECT u.id, u.email, u.name, r.name as role_name 
    FROM users u 
    JOIN roles r ON u.role_id = r.id 
    WHERE u.tenant_id = ?
", [$tenant['id']]);

print_r($users);
