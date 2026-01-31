<?php
/**
 * E-Learning Tables Setup Script
 * 
 * Execute this script via browser to create/update the e-learning tables.
 * URL: /setup-elearning.php
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

    // 1. Create specialty_assignments table
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `specialty_assignments` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `tenant_id` INT UNSIGNED NOT NULL,
                `specialty_id` VARCHAR(50) NOT NULL COMMENT 'References specialties_repository.json',
                `user_id` INT UNSIGNED NOT NULL,
                `assigned_by` INT UNSIGNED NOT NULL,
                `status` ENUM('pending', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
                `due_date` DATE NULL,
                `instructions` TEXT NULL,
                `xp_earned` INT UNSIGNED NOT NULL DEFAULT 0,
                `started_at` TIMESTAMP NULL,
                `completed_at` TIMESTAMP NULL,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uk_specialty_assignment` (`tenant_id`, `specialty_id`, `user_id`),
                KEY `idx_specialty_assignments_user` (`user_id`),
                KEY `idx_specialty_assignments_status` (`tenant_id`, `status`),
                CONSTRAINT `fk_specialty_assignments_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
                CONSTRAINT `fk_specialty_assignments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
                CONSTRAINT `fk_specialty_assignments_assigned_by` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $results[] = "✅ specialty_assignments - OK";
    } catch (PDOException $e) {
        $errors[] = "❌ specialty_assignments - " . $e->getMessage();
    }

    // 2. Add missing columns if table exists but columns don't
    try {
        $cols = $pdo->query("SHOW COLUMNS FROM specialty_assignments LIKE 'due_date'")->fetchAll();
        if (empty($cols)) {
            $pdo->exec("ALTER TABLE specialty_assignments ADD COLUMN `due_date` DATE NULL AFTER `status`");
            $results[] = "✅ Added due_date column";
        }
    } catch (PDOException $e) {
        // Ignore if table doesn't exist yet
    }

    try {
        $cols = $pdo->query("SHOW COLUMNS FROM specialty_assignments LIKE 'instructions'")->fetchAll();
        if (empty($cols)) {
            $pdo->exec("ALTER TABLE specialty_assignments ADD COLUMN `instructions` TEXT NULL AFTER `due_date`");
            $results[] = "✅ Added instructions column";
        }
    } catch (PDOException $e) {
        // Ignore
    }

    // 3. Create specialty_requirements table
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `specialty_requirements` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `specialty_id` VARCHAR(50) NOT NULL COMMENT 'References specialties_repository.json',
                `order_num` INT UNSIGNED NOT NULL DEFAULT 1,
                `type` ENUM('text', 'multiple_choice', 'checkbox', 'file_upload', 'practical') NOT NULL DEFAULT 'text',
                `title` VARCHAR(500) NOT NULL,
                `description` TEXT NULL,
                `options` JSON NULL COMMENT 'For multiple_choice/checkbox types',
                `correct_answer` JSON NULL COMMENT 'Expected answer(s)',
                `points` INT UNSIGNED NOT NULL DEFAULT 10,
                `is_required` TINYINT(1) NOT NULL DEFAULT 1,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_specialty_requirements_specialty` (`specialty_id`),
                KEY `idx_specialty_requirements_order` (`specialty_id`, `order_num`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $results[] = "✅ specialty_requirements - OK";
    } catch (PDOException $e) {
        $errors[] = "❌ specialty_requirements - " . $e->getMessage();
    }

    // 4. Create user_requirement_progress table
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `user_requirement_progress` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `assignment_id` INT UNSIGNED NOT NULL,
                `requirement_id` INT UNSIGNED NOT NULL,
                `status` ENUM('pending', 'answered', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
                `answer` TEXT NULL COMMENT 'User response (text, JSON for choices)',
                `file_path` VARCHAR(500) NULL COMMENT 'For file_upload type',
                `answered_at` TIMESTAMP NULL,
                `reviewed_by` INT UNSIGNED NULL,
                `reviewed_at` TIMESTAMP NULL,
                `feedback` TEXT NULL COMMENT 'Instructor feedback',
                `points_earned` INT UNSIGNED NOT NULL DEFAULT 0,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uk_user_requirement_progress` (`assignment_id`, `requirement_id`),
                KEY `idx_user_requirement_progress_status` (`status`),
                CONSTRAINT `fk_user_requirement_progress_assignment` FOREIGN KEY (`assignment_id`) REFERENCES `specialty_assignments` (`id`) ON DELETE CASCADE,
                CONSTRAINT `fk_user_requirement_progress_requirement` FOREIGN KEY (`requirement_id`) REFERENCES `specialty_requirements` (`id`) ON DELETE CASCADE,
                CONSTRAINT `fk_user_requirement_progress_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $results[] = "✅ user_requirement_progress - OK";
    } catch (PDOException $e) {
        $errors[] = "❌ user_requirement_progress - " . $e->getMessage();
    }
}

$executed = isset($_GET['run']) && $_GET['run'] === 'yes';
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup E-Learning Tables</title>
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
            transition: all 0.2s;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 0 #3d8c00;
        }

        .btn:active {
            transform: translateY(2px);
            box-shadow: 0 2px 0 #3d8c00;
        }

        .btn-danger {
            background: #e74c3c;
            box-shadow: 0 4px 0 #c0392b;
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
        <h1>🎓 Setup E-Learning Tables</h1>
        <p class="subtitle">Create/update tables for the specialty e-learning system</p>

        <div class="card">
            <h2>📊 Tables to Create</h2>
            <ul>
                <li><strong>specialty_assignments</strong> - Assignments of specialties to pathfinders</li>
                <li><strong>specialty_requirements</strong> - Questions/tasks for each specialty</li>
                <li><strong>user_requirement_progress</strong> - User progress on requirements</li>
            </ul>
        </div>

        <?php if ($executed): ?>
            <div class="card">
                <h2>📋 Results</h2>
                <?php foreach ($results as $r): ?>
                    <div class="result"><?= htmlspecialchars($r) ?></div>
                <?php endforeach; ?>
                <?php foreach ($errors as $e): ?>
                    <div class="result error"><?= htmlspecialchars($e) ?></div>
                <?php endforeach; ?>

                <?php if (empty($errors)): ?>
                    <p style="color: #58cc02; font-size: 18px; margin-top: 20px;">✅ All done! Tables are ready.</p>
                <?php else: ?>
                    <p style="color: #e74c3c; font-size: 18px; margin-top: 20px;">⚠️ Some errors occurred.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="card center">
                <p>Click the button below to create/update the e-learning tables:</p>
                <br>
                <a href="?run=yes" class="btn">🚀 Execute Setup</a>
            </div>
        <?php endif; ?>

        <div class="warning">
            ⚠️ <strong>Security Warning:</strong> Delete this file (<code>setup-elearning.php</code>) after use!
        </div>
    </div>
</body>

</html>