<?php
define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/helpers/env.php';
require_once BASE_PATH . '/helpers/config.php';
require_once BASE_PATH . '/helpers/database.php';

// Mock environment for CLI
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/';

try {
    // 1. Check a specific program ID (e.g. 20)
    $programId = 20;
    $program = db_fetch_one("SELECT * FROM learning_programs WHERE id = ?", [$programId]);
    
    echo "Program ID {$programId} exists? " . ($program ? "YES" : "NO") . "\n";
    if ($program) {
        echo "Program Tenant ID: " . $program['tenant_id'] . "\n";
        echo "Program Status: " . $program['status'] . "\n";
    }

    // 2. Check assignments for a user (if we knew user ID)
    // Let's just list recent assignments
    $recent = db_fetch_all("SELECT * FROM user_program_progress ORDER BY id DESC LIMIT 5");
    echo "\nRecent Assignments:\n";
    foreach ($recent as $r) {
        $pid = $r['program_id'];
        $pname = db_fetch_column("SELECT name FROM learning_programs WHERE id = ?", [$pid]);
        echo "ID: {$r['id']} | TID: {$r['tenant_id']} | UID: {$r['user_id']} | PID: {$pid} ({$pname}) | Status: {$r['status']}\n";
    }

    // 3. Check tenants
    $tenants = db_fetch_all("SELECT id, slug FROM tenants");
    echo "\nTenants:\n";
    foreach ($tenants as $t) {
        echo "ID: {$t['id']} | Slug: {$t['slug']}\n";
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
