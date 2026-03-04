<?php
require_once __DIR__ . '/../bootstrap/bootstrap.php';

header('Content-Type: text/plain; charset=UTF-8');

$userId = 11;
$u = db_fetch_one("SELECT * FROM users WHERE id = ?", [$userId]);

if ($u) {
    echo "User found. Role ID: {$u['role_id']}\n";
    $role = db_fetch_one("SELECT * FROM roles WHERE id = ?", [$u['role_id']]);
    if ($role) {
        echo "Role found:\n";
        print_r($role);
    } else {
        echo "Role NOT FOUND for ID {$u['role_id']}!\n";
    }
} else {
    echo "User 11 NOT FOUND.\n";
}

echo "\nAll roles for tenant 2:\n";
$roles = db_fetch_all("SELECT * FROM roles WHERE tenant_id = 2");
print_r($roles);
