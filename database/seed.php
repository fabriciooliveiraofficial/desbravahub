<?php
/**
 * DesbravaHub Database Seeder
 * 
 * Popula o banco de dados com dados iniciais para testes.
 * Execute: php database/seed.php
 */

require_once dirname(__DIR__) . '/bootstrap/bootstrap.php';

echo "🌱 DesbravaHub - Seeder\n";
echo "========================\n\n";

try {
    $pdo = db_connect();

    // Tenant de demonstração
    echo "📦 Criando tenant de demonstração...\n";

    $tenantId = db_insert('tenants', [
        'slug' => 'clube-demo',
        'name' => 'Clube Desbravadores Demo',
        'description' => 'Clube de demonstração para testes',
        'status' => 'active',
        'settings' => json_encode([
            'theme' => 'default',
            'language' => 'pt-BR',
        ]),
    ]);

    echo "   ✓ Tenant criado: clube-demo (ID: $tenantId)\n";

    // Roles
    echo "\n👥 Criando cargos...\n";

    $roles = [
        ['name' => 'admin', 'display_name' => 'Administrador', 'tenant_id' => $tenantId],
        ['name' => 'director', 'display_name' => 'Diretor', 'tenant_id' => $tenantId],
        ['name' => 'counselor', 'display_name' => 'Conselheiro', 'tenant_id' => $tenantId],
        ['name' => 'pathfinder', 'display_name' => 'Desbravador', 'tenant_id' => $tenantId],
    ];

    $roleIds = [];
    foreach ($roles as $role) {
        $roleIds[$role['name']] = db_insert('roles', $role);
        echo "   ✓ Cargo: {$role['display_name']}\n";
    }

    // Permissões
    echo "\n🔐 Criando permissões...\n";

    $permissions = [
        'activities.view',
        'activities.create',
        'activities.edit',
        'activities.delete',
        'activities.manage',
        'proofs.view',
        'proofs.submit',
        'proofs.review',
        'users.view',
        'users.create',
        'users.edit',
        'users.delete',
        'users.manage',
        'notifications.view',
        'notifications.broadcast',
        'versions.view',
        'versions.create',
        'versions.promote',
        'versions.rollout',
        'features.view',
        'features.manage',
        'admin.access',
    ];

    foreach ($permissions as $perm) {
        db_insert('permissions', ['name' => $perm, 'tenant_id' => $tenantId]);
    }
    echo "   ✓ " . count($permissions) . " permissões criadas\n";

    // Níveis
    echo "\n⭐ Criando níveis...\n";

    $levels = [
        ['number' => 1, 'name' => 'Iniciante', 'min_xp' => 0, 'tenant_id' => $tenantId],
        ['number' => 2, 'name' => 'Aprendiz', 'min_xp' => 100, 'tenant_id' => $tenantId],
        ['number' => 3, 'name' => 'Explorador', 'min_xp' => 300, 'tenant_id' => $tenantId],
        ['number' => 4, 'name' => 'Aventureiro', 'min_xp' => 600, 'tenant_id' => $tenantId],
        ['number' => 5, 'name' => 'Veterano', 'min_xp' => 1000, 'tenant_id' => $tenantId],
        ['number' => 6, 'name' => 'Elite', 'min_xp' => 1500, 'tenant_id' => $tenantId],
        ['number' => 7, 'name' => 'Mestre', 'min_xp' => 2500, 'tenant_id' => $tenantId],
        ['number' => 8, 'name' => 'Lenda', 'min_xp' => 4000, 'tenant_id' => $tenantId],
    ];

    foreach ($levels as $level) {
        db_insert('levels', $level);
        echo "   ✓ Nível {$level['number']}: {$level['name']} ({$level['min_xp']} XP)\n";
    }

    // Usuários
    echo "\n👤 Criando usuários...\n";

    $users = [
        [
            'email' => 'admin@demo.com',
            'name' => 'Administrador',
            'role_id' => $roleIds['admin'],
            'xp_points' => 500,
        ],
        [
            'email' => 'diretor@demo.com',
            'name' => 'Maria Diretora',
            'role_id' => $roleIds['director'],
            'xp_points' => 1200,
        ],
        [
            'email' => 'joao@demo.com',
            'name' => 'João Desbravador',
            'role_id' => $roleIds['pathfinder'],
            'xp_points' => 350,
        ],
        [
            'email' => 'ana@demo.com',
            'name' => 'Ana Exploradora',
            'role_id' => $roleIds['pathfinder'],
            'xp_points' => 200,
        ],
    ];

    foreach ($users as $user) {
        db_insert('users', [
            'tenant_id' => $tenantId,
            'email' => $user['email'],
            'name' => $user['name'],
            'password_hash' => password_hash('demo123', PASSWORD_DEFAULT),
            'role_id' => $user['role_id'],
            'xp_points' => $user['xp_points'],
            'level_id' => 1,
            'status' => 'active',
        ]);
        echo "   ✓ Usuário: {$user['name']} ({$user['email']})\n";
    }

    // Atividades
    echo "\n🎯 Criando atividades...\n";

    $activities = [
        [
            'title' => 'Bem-vindo ao Clube',
            'description' => 'Complete seu cadastro e conheça o clube.',
            'instructions' => 'Acesse seu perfil, complete todas as informações e tire uma foto.',
            'xp_reward' => 50,
            'min_level' => 1,
            'status' => 'active',
        ],
        [
            'title' => 'Primeira Reunião',
            'description' => 'Participe da sua primeira reunião semanal.',
            'instructions' => 'Compareça à reunião e tire uma foto com o grupo.',
            'xp_reward' => 100,
            'min_level' => 1,
            'status' => 'active',
        ],
        [
            'title' => 'Conhecendo a Natureza',
            'description' => 'Atividade ao ar livre para identificar plantas.',
            'instructions' => 'Fotografe 5 plantas diferentes e identifique cada uma.',
            'xp_reward' => 150,
            'min_level' => 2,
            'is_outdoor' => 1,
            'status' => 'active',
        ],
        [
            'title' => 'Primeiros Socorros',
            'description' => 'Aprenda técnicas básicas de primeiros socorros.',
            'instructions' => 'Complete o questionário sobre primeiros socorros.',
            'xp_reward' => 200,
            'min_level' => 2,
            'status' => 'active',
        ],
        [
            'title' => 'Acampamento Inaugural',
            'description' => 'Participe do seu primeiro acampamento.',
            'instructions' => 'Documente sua experiência no acampamento.',
            'xp_reward' => 300,
            'min_level' => 3,
            'is_outdoor' => 1,
            'status' => 'active',
        ],
    ];

    foreach ($activities as $activity) {
        $activity['tenant_id'] = $tenantId;
        $activity['is_outdoor'] = $activity['is_outdoor'] ?? 0;
        $activity['proof_types'] = json_encode(['upload', 'url']);
        db_insert('activities', $activity);
        echo "   ✓ Atividade: {$activity['title']}\n";
    }

    // Conquistas
    echo "\n🏆 Criando conquistas...\n";

    $achievements = [
        ['name' => 'Primeiro Passo', 'description' => 'Complete sua primeira atividade', 'xp_bonus' => 25],
        ['name' => 'Explorador', 'description' => 'Complete 5 atividades', 'xp_bonus' => 50],
        ['name' => 'Veterano', 'description' => 'Complete 10 atividades', 'xp_bonus' => 100],
        ['name' => 'Aventureiro', 'description' => 'Complete uma atividade ao ar livre', 'xp_bonus' => 25],
        ['name' => 'Mestre', 'description' => 'Alcance o nível 5', 'xp_bonus' => 150],
    ];

    foreach ($achievements as $ach) {
        db_insert('achievements', [
            'tenant_id' => $tenantId,
            'name' => $ach['name'],
            'description' => $ach['description'],
            'xp_bonus' => $ach['xp_bonus'],
            'criteria' => json_encode(['type' => 'manual']),
        ]);
        echo "   ✓ Conquista: {$ach['name']}\n";
    }

    // Versão inicial
    echo "\n📦 Criando versão inicial...\n";

    db_insert('app_versions', [
        'version' => '1.0.0',
        'changelog' => 'Versão inicial do DesbravaHub',
        'status' => 'stable',
        'released_at' => date('Y-m-d H:i:s'),
    ]);
    echo "   ✓ Versão 1.0.0 (stable)\n";

    echo "\n========================\n";
    echo "✅ Seeder concluído com sucesso!\n\n";
    echo "📧 Credenciais de acesso:\n";
    echo "   Admin: admin@demo.com / demo123\n";
    echo "   Diretor: diretor@demo.com / demo123\n";
    echo "   Usuário: joao@demo.com / demo123\n";
    echo "\n🔗 Acesse: http://localhost:8080/clube-demo/login\n\n";

} catch (\Exception $e) {
    echo "\n❌ Erro: " . $e->getMessage() . "\n";
    exit(1);
}
