<?php
require_once __DIR__ . '/../bootstrap/bootstrap.php';

header('Content-Type: text/plain; charset=UTF-8');

$userId = 11;
$user = db_fetch_one("SELECT id, name, deleted_at FROM users WHERE id = ?", [$userId]);

if ($user) {
    echo "User 11 found:\n";
    var_dump($user['deleted_at']);
    echo "\nIs NULL? " . (is_null($user['deleted_at']) ? 'YES' : 'NO') . "\n";
    echo "Value: '" . $user['deleted_at'] . "'\n";
    echo "Type: " . gettype($user['deleted_at']) . "\n";
} else {
    echo "User 11 NOT FOUND.\n";
}
