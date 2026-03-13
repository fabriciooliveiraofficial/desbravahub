<?php
require_once __DIR__ . '/../bootstrap/bootstrap.php';

$users = db_fetch_all("SELECT user_id, COUNT(*) as c FROM specialty_assignments WHERE status IN ('completed', 'approved') GROUP BY user_id LIMIT 10");
foreach ($users as $u) {
    echo "UID: {$u['user_id']}, Count: {$u['c']}\n";
}
