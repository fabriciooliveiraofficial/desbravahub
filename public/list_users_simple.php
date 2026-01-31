<?php
require_once __DIR__ . '/../bootstrap/bootstrap.php';

$users = db_fetch_all("SELECT id, name, email, role_id FROM users WHERE tenant_id = 1");
foreach ($users as $u) {
    echo "ID: {$u['id']} | Name: {$u['name']} | Role: {$u['role_id']} | Email: {$u['email']}\n";
}
