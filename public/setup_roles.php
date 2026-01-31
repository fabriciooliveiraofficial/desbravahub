<?php
require __DIR__ . '/../bootstrap/bootstrap.php';

// Autoloader for App namespace (copied from index.php)
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = BASE_PATH . '/app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

use App\Core\App;

echo "<pre>";
echo "Setting up Roles and Permissions...\n";

$tenantId = App::tenant()['id'] ?? 1; // Default to 1 if no tenant context (should be run in tenant context usually, but for seed we can force)
// Actually better to iterate all tenants or just use the current one if accessed via URL
$tenant = App::tenant();
if (!$tenant) {
    // If not in tenant context, try to fetch the first one or specific one
    $tenant = db_fetch_one("SELECT * FROM tenants LIMIT 1");
    echo "No tenant context, using tenant: {$tenant['name']}\n";
}
$tenantId = $tenant['id'];

// Define roles and their permission keys
$rolesConfig = [
    'parent' => [
        'display_name' => 'Pai/Responsável',
        'description' => 'Acesso para pais e responsáveis acompanharem atividades',
        'permissions' => [
            'dashboard.view',
            'events.view',
            'activities.view',
            'quizzes.view' // Maybe to see what quizzes exist?
        ]
    ],
    'associate_director' => [
        'display_name' => 'Diretor Associado',
        'description' => 'Auxilia na direção do clube',
        'permissions' => [
            'dashboard.view',
            'users.view',
            'users.create',
            'users.edit',
            'activities.view',
            'activities.create',
            'activities.edit',
            'proofs.view',
            'proofs.review',
            'quizzes.view',
            'quizzes.create',
            'quizzes.edit',
            'events.view',
            'events.create',
            'events.edit',
            'notifications.send'
        ]
    ],
    'counselor' => [
        'display_name' => 'Conselheiro',
        'description' => 'Lidera unidade e acompanha desbravadores',
        'permissions' => [
            'dashboard.view',
            'users.view',
            'activities.view',
            'activities.create',
            'activities.edit', // Can create activities for unit? Maybe.
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

foreach ($rolesConfig as $roleName => $config) {
    echo "Processing Role: {$roleName}...\n";

    // 1. Create or Get Role
    $role = db_fetch_one("SELECT * FROM roles WHERE tenant_id = ? AND name = ?", [$tenantId, $roleName]);

    if (!$role) {
        $roleId = db_insert('roles', [
            'tenant_id' => $tenantId,
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

    // 2. Assign Permissions
    $addedCount = 0;
    foreach ($config['permissions'] as $permKey) {
        $perm = db_fetch_one("SELECT id FROM permissions WHERE `key` = ?", [$permKey]);

        if ($perm) {
            // Check if already assigned
            $exists = db_fetch_one("SELECT 1 FROM role_permissions WHERE role_id = ? AND permission_id = ?", [$roleId, $perm['id']]);

            if (!$exists) {
                db_insert('role_permissions', [
                    'role_id' => $roleId,
                    'permission_id' => $perm['id']
                ]);
                $addedCount++;
            }
        } else {
            echo "  - WARNING: Permission key '$permKey' not found in permissions table.\n";
        }
    }
    echo "  - Assigned $addedCount new permissions.\n";
}

echo "\nDone!";
