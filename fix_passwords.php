<?php
require __DIR__ . '/bootstrap/bootstrap.php';

$newHash = password_hash('password123', PASSWORD_BCRYPT);

echo "New hash: $newHash\n";

db_query("UPDATE users SET password_hash = ?", [$newHash]);

echo "All users updated!\n";

// Verify
$user = db_fetch_one("SELECT password_hash FROM users WHERE email = 'admin@demo.com'");
$verified = password_verify('password123', $user['password_hash']);
echo "Verification: " . ($verified ? 'SUCCESS' : 'FAILED') . "\n";
