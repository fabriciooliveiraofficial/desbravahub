<?php
require_once __DIR__ . '/../bootstrap/bootstrap.php';

try {
    $tenant = db_fetch_one("SELECT id FROM tenants WHERE slug='clube-demo'");
    if (!$tenant) {
        echo "Tenant not found.\n";
        exit;
    }
    
    $user = db_fetch_one("
        SELECT u.id, u.email, u.name, r.name as role_name, r.permissions as role_permissions
        FROM users u 
        JOIN roles r ON u.role_id = r.id 
        WHERE u.email = 'fabriciooliveiraofficial@gmail.com' AND u.tenant_id = ?
    ", [$tenant['id']]);

    echo "USER CHECK:\n";
    if ($user) {
        echo "ID: {$user['id']}\n";
        echo "Email: {$user['email']}\n";
        echo "Role: {$user['role_name']}\n";
        echo "Permissions JSON: {$user['role_permissions']}\n";
        
        // Mocking the 'can' check
        $permissions = json_decode($user['role_permissions'] ?? '[]', true);
        $hasManage = in_array('users.manage', $permissions) || in_array('*', $permissions);
        echo "Manual check for 'users.manage': " . ($hasManage ? "YES" : "NO") . "\n";
    } else {
        echo "User 'fabriciooliveiraofficial@gmail.com' not found in tenant 'clube-demo'.\n";
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
