<?php
/**
 * Setup Units System Tables
 * 
 * Execute via browser: /setup-units.php
 * 
 * ⚠️ DELETE THIS FILE AFTER USE FOR SECURITY
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

$config = [
    'db_host' => 'localhost',
    'db_name' => 'u714643564_db_desbravahub',
    'db_user' => 'u714643564_user_desbravah',
    'db_pass' => 'Fdm399788896528168172@#$%',
];

try {
    $pdo = new PDO(
        "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4",
        $config['db_user'],
        $config['db_pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}

$results = [];
$errors = [];

// Only run if requested
if (isset($_GET['run']) && $_GET['run'] === 'yes') {

    // 1. Create units table
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `units` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `tenant_id` INT UNSIGNED NOT NULL,
                `name` VARCHAR(100) NOT NULL,
                `description` TEXT NULL,
                `color` VARCHAR(7) NULL COMMENT 'Cor da unidade (#RRGGBB)',
                `mascot` VARCHAR(100) NULL COMMENT 'Mascote/símbolo da unidade',
                `motto` VARCHAR(255) NULL COMMENT 'Lema da unidade',
                `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_units_tenant` (`tenant_id`),
                KEY `idx_units_status` (`tenant_id`, `status`),
                CONSTRAINT `fk_units_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $results[] = "✅ Tabela units criada";
    } catch (PDOException $e) {
        $errors[] = "❌ units: " . $e->getMessage();
    }

    // 2. Create unit_counselors table
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `unit_counselors` (
                `unit_id` INT UNSIGNED NOT NULL,
                `user_id` INT UNSIGNED NOT NULL,
                `is_primary` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Conselheiro principal',
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`unit_id`, `user_id`),
                KEY `idx_unit_counselors_user` (`user_id`),
                CONSTRAINT `fk_unit_counselors_unit` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE,
                CONSTRAINT `fk_unit_counselors_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $results[] = "✅ Tabela unit_counselors criada";
    } catch (PDOException $e) {
        $errors[] = "❌ unit_counselors: " . $e->getMessage();
    }

    // 3. Add unit_id to users if not exists
    try {
        $cols = $pdo->query("SHOW COLUMNS FROM users LIKE 'unit_id'")->fetchAll();
        if (empty($cols)) {
            $pdo->exec("ALTER TABLE users ADD COLUMN `unit_id` INT UNSIGNED NULL AFTER `role_id`");
            $pdo->exec("ALTER TABLE users ADD KEY `idx_users_unit` (`unit_id`)");
            $results[] = "✅ Coluna unit_id adicionada em users";
        } else {
            $results[] = "ℹ️ Coluna unit_id já existe";
        }
    } catch (PDOException $e) {
        $errors[] = "❌ unit_id: " . $e->getMessage();
    }

    // 4. Add associate_director role if not exists
    try {
        // Get first tenant
        $tenant = $pdo->query("SELECT id FROM tenants LIMIT 1")->fetch();
        if ($tenant) {
            $exists = $pdo->prepare("SELECT id FROM roles WHERE tenant_id = ? AND name = 'associate_director'");
            $exists->execute([$tenant['id']]);
            if (!$exists->fetch()) {
                $stmt = $pdo->prepare("INSERT INTO roles (tenant_id, name, display_name, is_system) VALUES (?, 'associate_director', 'Diretor Associado', 1)");
                $stmt->execute([$tenant['id']]);
                $results[] = "✅ Role 'Diretor Associado' criada";
            } else {
                $results[] = "ℹ️ Role 'Diretor Associado' já existe";
            }
        }
    } catch (PDOException $e) {
        $errors[] = "❌ associate_director role: " . $e->getMessage();
    }

    // 5. Add units permissions
    $unitPermissions = [
        ['key' => 'units.view', 'name' => 'Ver Unidades', 'group' => 'Unidades'],
        ['key' => 'units.create', 'name' => 'Criar Unidades', 'group' => 'Unidades'],
        ['key' => 'units.edit', 'name' => 'Editar Unidades', 'group' => 'Unidades'],
        ['key' => 'units.delete', 'name' => 'Excluir Unidades', 'group' => 'Unidades'],
        ['key' => 'units.assign', 'name' => 'Atribuir Membros', 'group' => 'Unidades'],
        ['key' => 'permissions.manage', 'name' => 'Gerenciar Permissões', 'group' => 'Sistema'],
    ];

    foreach ($unitPermissions as $perm) {
        try {
            $exists = $pdo->prepare("SELECT id FROM permissions WHERE `key` = ?");
            $exists->execute([$perm['key']]);
            if (!$exists->fetch()) {
                $stmt = $pdo->prepare("INSERT INTO permissions (`key`, `name`, `group`) VALUES (?, ?, ?)");
                $stmt->execute([$perm['key'], $perm['name'], $perm['group']]);
                $results[] = "✅ Permissão '{$perm['key']}' criada";
            }
        } catch (PDOException $e) {
            // Ignore duplicates
        }
    }
}

$executed = isset($_GET['run']) && $_GET['run'] === 'yes';
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Sistema de Unidades</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #fff;
            min-height: 100vh;
            margin: 0;
            padding: 40px 20px;
        }

        .container {
            max-width: 700px;
            margin: 0 auto;
        }

        h1 {
            text-align: center;
            margin-bottom: 10px;
        }

        .subtitle {
            text-align: center;
            color: #888;
            margin-bottom: 40px;
        }

        .card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }

        .btn {
            display: inline-block;
            padding: 16px 32px;
            background: #58cc02;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            box-shadow: 0 4px 0 #3d8c00;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .result {
            padding: 12px 16px;
            margin: 8px 0;
            border-radius: 8px;
            background: rgba(88, 204, 2, 0.1);
            border: 1px solid rgba(88, 204, 2, 0.3);
        }

        .error {
            background: rgba(231, 76, 60, 0.1);
            border-color: rgba(231, 76, 60, 0.3);
        }

        .warning {
            background: #ff9800;
            color: #000;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
        }

        .center {
            text-align: center;
        }

        code {
            background: rgba(0, 0, 0, 0.3);
            padding: 2px 8px;
            border-radius: 4px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>👥 Setup Sistema de Unidades</h1>
        <p class="subtitle">Criar tabelas para gerenciamento de unidades do clube</p>

        <div class="card">
            <h2>📊 O que será criado</h2>
            <ul>
                <li><strong>units</strong> - Unidades do clube</li>
                <li><strong>unit_counselors</strong> - Conselheiros das unidades</li>
                <li><strong>unit_id</strong> - Coluna na tabela users</li>
                <li><strong>Diretor Associado</strong> - Novo cargo</li>
                <li><strong>Permissões</strong> - units.view, units.create, etc.</li>
            </ul>
        </div>

        <?php if ($executed): ?>
            <div class="card">
                <h2>📋 Resultados</h2>
                <?php foreach ($results as $r): ?>
                    <div class="result"><?= htmlspecialchars($r) ?></div>
                <?php endforeach; ?>
                <?php foreach ($errors as $e): ?>
                    <div class="result error"><?= htmlspecialchars($e) ?></div>
                <?php endforeach; ?>

                <?php if (empty($errors)): ?>
                    <p style="color: #58cc02; font-size: 18px; margin-top: 20px;">✅ Configuração concluída!</p>
                <?php else: ?>
                    <p style="color: #e74c3c; font-size: 18px; margin-top: 20px;">⚠️ Alguns erros ocorreram.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="card center">
                <p>Clique para criar as tabelas do sistema de unidades:</p>
                <br>
                <a href="?run=yes" class="btn">🚀 Executar Setup</a>
            </div>
        <?php endif; ?>

        <div class="warning">
            ⚠️ <strong>Segurança:</strong> Delete este arquivo (<code>setup-units.php</code>) após uso!
        </div>
    </div>
</body>

</html>