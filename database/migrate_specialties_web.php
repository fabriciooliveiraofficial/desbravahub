<?php
/**
 * Web Migration: Create Specialties Table
 * 
 * Access this file via browser to run the migration.
 * Example: http://localhost/database/migrate_specialties_web.php
 */

require_once __DIR__ . '/../bootstrap/bootstrap.php';

// Simple security check (optional, but good practice)
// if (!is_debug() && !is_dev()) {
//     die('Migrations can only be run in debug/dev mode.');
// }

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Migration: Specialties</title>
    <style>
        body {
            font-family: sans-serif;
            padding: 20px;
            line-height: 1.6;
            background: #f0f2f5;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .success {
            color: green;
            background: #e8f5e9;
            padding: 10px;
            border-radius: 4px;
        }

        .error {
            color: red;
            background: #ffebee;
            padding: 10px;
            border-radius: 4px;
        }

        h1 {
            margin-top: 0;
        }

        pre {
            background: #eee;
            padding: 10px;
            overflow-x: auto;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Migration Status</h1>

        <?php
        try {
            // Check connection first
            $pdo = db();
            echo "<div class='success'>Database connection successful.</div>";

            echo "<p>Creating table `specialties`...</p>";

            db_query("
                CREATE TABLE IF NOT EXISTS `specialties` (
                    `id` VARCHAR(50) NOT NULL,
                    `tenant_id` INT UNSIGNED NOT NULL,
                    `category_id` VARCHAR(50) NOT NULL,
                    `name` VARCHAR(255) NOT NULL,
                    `badge_icon` VARCHAR(255) DEFAULT '📘',
                    `type` ENUM('indoor', 'outdoor', 'mixed') DEFAULT 'indoor',
                    `duration_hours` INT UNSIGNED DEFAULT 4,
                    `difficulty` INT UNSIGNED DEFAULT 2,
                    `xp_reward` INT UNSIGNED DEFAULT 100,
                    `description` TEXT,
                    `status` ENUM('active', 'inactive', 'archived') DEFAULT 'active',
                    `created_by` INT UNSIGNED NOT NULL,
                    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    KEY `idx_specialties_tenant` (`tenant_id`),
                    KEY `idx_specialties_category` (`category_id`),
                    CONSTRAINT `fk_specialties_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
                    CONSTRAINT `fk_specialties_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            echo "<div class='success'>✅ Migration completed successfully! Table `specialties` is ready.</div>";

        } catch (PDOException $e) {
            echo "<div class='error'>❌ Database Error: " . htmlspecialchars($e->getMessage()) . "</div>";
            echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        } catch (Exception $e) {
            echo "<div class='error'>❌ General Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
        ?>

        <p><a href="../">Return to Home</a></p>
    </div>
</body>

</html>