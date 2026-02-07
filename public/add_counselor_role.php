<?php
/**
 * Utility to ensure 'counselor' and 'instructor' roles exist
 */
require_once dirname(__DIR__) . '/bootstrap/bootstrap.php';
use App\Core\App;

echo "<pre>";
echo "Updating roles and permissions...\n";

$tenants = db_fetch_all("SELECT * FROM tenants");

$rolesConfig = [
    'counselor' => [
        'display_name' => 'Conselheiro',
        'description' => 'Lidera unidade e acompanha desbravadores',
        'permissions' => [
            'dashboard.view',
            'users.view',
            'activities.view',
            'proofs.view',
            'proofs.review',
            'quizzes.view',
            'events.view'
        ]
    ],
    'instructor' => [
        'display_name' => 'Instrutor',
        'description' => 'Ministra classes e especialidades',
        'permissions' => [
            'dashboard.view',
            'activities.view',
            'activities.create',
            'activities.edit',
            'proofs.view',
            'proofs.review',
            'quizzes.view',
            'quizzes.create',
            'quizzes.edit',
            'events.view'
        ]
    ]
];

foreach ($tenants as $tenant) {
    echo "\nProcessing Tenant: {$tenant['name']} (ID: {$tenant['id']})\n";
    
    foreach ($rolesConfig as $roleName => $config) {
        $role = db_fetch_one("SELECT * FROM roles WHERE tenant_id = ? AND name = ?", [$tenant['id'], $roleName]);
        
        if (!$role) {
            $roleId = db_insert('roles', [
                'tenant_id' => $tenant['id'],
                'name' => $roleName,
                'display_name' => $config['display_name'],
                'description' => $config['description'],
                'is_system' => 1
            ]);
            echo "  - Created role: {$config['display_name']} (ID: $roleId)\n";
        } else {
            $roleId = $role['id'];
            echo "  - Role exists: {$config['display_name']} (ID: $roleId)\n";
        }
        
        // Assign Permissions
        $addedCount = 0;
        foreach ($config['permissions'] as $permKey) {
            $perm = db_fetch_one("SELECT id FROM permissions WHERE `key` = ?", [$permKey]);
            if ($perm) {
                $exists = db_fetch_one("SELECT 1 FROM role_permissions WHERE role_id = ? AND permission_id = ?", [$roleId, $perm['id']]);
                if (!$exists) {
                    db_insert('role_permissions', [
                        'role_id' => $roleId,
                        'permission_id' => $perm['id']
                    ]);
                    $addedCount++;
                }
            }
        }
        echo "  - Assigned $addedCount new permissions.\n";
    }
}

echo "\nDone!";
