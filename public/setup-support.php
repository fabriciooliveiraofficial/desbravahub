<?php
/**
 * Criar tabelas do Sistema de Suporte
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

    echo "<pre style='font-family:monospace; color:#e0e0e0; background:#1a1a2e; padding:30px;'>";
    echo "<h1 style='color:#00d9ff;'>Criando tabelas do Sistema de Suporte...</h1>\n\n";

    $tables = [
        'support_tickets' => "CREATE TABLE IF NOT EXISTS `support_tickets` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `tenant_id` INT UNSIGNED NULL,
            `user_id` INT UNSIGNED NULL,
            `category` ENUM('bug', 'question', 'suggestion', 'improvement') NOT NULL DEFAULT 'question',
            `priority` ENUM('low', 'medium', 'high') NOT NULL DEFAULT 'medium',
            `subject` VARCHAR(255) NOT NULL,
            `description` TEXT NOT NULL,
            `related_module` VARCHAR(100) NULL,
            `related_url` VARCHAR(500) NULL,
            `status` ENUM('open', 'in_progress', 'waiting', 'resolved', 'closed') NOT NULL DEFAULT 'open',
            `linked_version_id` INT UNSIGNED NULL,
            `assigned_to` INT UNSIGNED NULL,
            `resolved_at` TIMESTAMP NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            INDEX `idx_tenant` (`tenant_id`),
            INDEX `idx_status` (`status`),
            INDEX `idx_category` (`category`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'support_messages' => "CREATE TABLE IF NOT EXISTS `support_messages` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `ticket_id` INT UNSIGNED NOT NULL,
            `sender_type` ENUM('user', 'developer') NOT NULL,
            `sender_id` INT UNSIGNED NULL,
            `sender_name` VARCHAR(255) NULL,
            `message` TEXT NOT NULL,
            `is_internal` TINYINT(1) NOT NULL DEFAULT 0,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            INDEX `idx_ticket` (`ticket_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'support_attachments' => "CREATE TABLE IF NOT EXISTS `support_attachments` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `ticket_id` INT UNSIGNED NOT NULL,
            `message_id` INT UNSIGNED NULL,
            `filename` VARCHAR(255) NOT NULL,
            `path` VARCHAR(500) NOT NULL,
            `size` INT UNSIGNED NOT NULL,
            `mime_type` VARCHAR(100) NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            INDEX `idx_ticket` (`ticket_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'developers' => "CREATE TABLE IF NOT EXISTS `developers` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `email` VARCHAR(255) NOT NULL,
            `password_hash` VARCHAR(255) NOT NULL,
            `name` VARCHAR(255) NOT NULL,
            `role` ENUM('admin', 'support') NOT NULL DEFAULT 'support',
            `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
            `last_login_at` TIMESTAMP NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    ];

    foreach ($tables as $name => $sql) {
        try {
            $pdo->exec($sql);
            echo "<span style='color:#00ff88'>✓</span> $name\n";
        } catch (Exception $e) {
            echo "<span style='color:#ff6b6b'>✗</span> $name: " . $e->getMessage() . "\n";
        }
    }

    // Criar desenvolvedor padrão
    echo "\n<h2 style='color:#00d9ff;'>Criando desenvolvedor padrão...</h2>\n";

    $devExists = $pdo->query("SELECT COUNT(*) FROM developers")->fetchColumn();
    if ($devExists == 0) {
        $stmt = $pdo->prepare("INSERT INTO developers (email, password_hash, name, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            'dev@desbravahub.com',
            password_hash('dev123456', PASSWORD_DEFAULT),
            'Desenvolvedor',
            'admin'
        ]);
        echo "<span style='color:#00ff88'>✓</span> Desenvolvedor criado: dev@desbravahub.com / dev123456\n";
    } else {
        echo "<span style='color:#ffc107'>!</span> Desenvolvedor já existe\n";
    }

    echo "\n<h1 style='color:#00ff88'>✅ SETUP COMPLETO!</h1>\n";
    echo "</pre>";

    echo "<div style='background:#1a1a2e; padding:20px; font-family:system-ui;'>";
    echo "<a href='/estrela-guia/suporte' style='padding:12px 24px; background:#00d9ff; color:#000; text-decoration:none; border-radius:8px; margin-right:10px;'>Suporte (Usuário)</a>";
    echo "<a href='/dev/suporte' style='padding:12px 24px; background:#00ff88; color:#000; text-decoration:none; border-radius:8px;'>Suporte (Dev)</a>";
    echo "</div>";

} catch (Exception $e) {
    echo "<pre style='color:red'>ERRO: " . $e->getMessage() . "</pre>";
}
