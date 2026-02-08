<?php
require_once __DIR__ . '/../bootstrap/bootstrap.php';
$email = 'fdm060881@gmail.com';
$result = db_update('users', ['role_id' => 1], 'email = ?', [$email]);
echo "Rows updated: " . $result;
