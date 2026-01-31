<?php
/**
 * Criar tabelas do Sistema de Especialidades
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
    echo "<h1 style='color:#00d9ff;'>🔧 Criando tabelas do Sistema de Especialidades...</h1>\n\n";

    $tables = [
        'specialty_assignments' => "CREATE TABLE IF NOT EXISTS `specialty_assignments` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `tenant_id` INT UNSIGNED NOT NULL,
            `specialty_id` VARCHAR(50) NOT NULL,
            `user_id` INT UNSIGNED NOT NULL,
            `assigned_by` INT UNSIGNED NOT NULL,
            `due_date` DATE NULL,
            `instructions` TEXT NULL,
            `status` ENUM('pending','in_progress','pending_review','completed','cancelled') DEFAULT 'pending',
            `started_at` TIMESTAMP NULL,
            `completed_at` TIMESTAMP NULL,
            `xp_earned` INT UNSIGNED DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `uk_assignment` (`tenant_id`, `specialty_id`, `user_id`),
            KEY `idx_sa_user` (`user_id`),
            KEY `idx_sa_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'specialty_proofs' => "CREATE TABLE IF NOT EXISTS `specialty_proofs` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `assignment_id` INT UNSIGNED NOT NULL,
            `tenant_id` INT UNSIGNED NOT NULL,
            `type` ENUM('url','upload','quiz') NOT NULL,
            `content` TEXT NULL,
            `status` ENUM('pending','approved','rejected') DEFAULT 'pending',
            `reviewed_by` INT UNSIGNED NULL,
            `reviewed_at` TIMESTAMP NULL,
            `feedback` TEXT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            KEY `idx_sp_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'assignment_requirements' => "CREATE TABLE IF NOT EXISTS `assignment_requirements` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `assignment_id` INT UNSIGNED NOT NULL,
            `tenant_id` INT UNSIGNED NOT NULL,
            `requirement_id` VARCHAR(50) NOT NULL,
            `status` ENUM('pending','submitted','approved','rejected') DEFAULT 'pending',
            `proof_type` VARCHAR(20) NULL,
            `proof_content` TEXT NULL,
            `submitted_at` TIMESTAMP NULL,
            `reviewed_by` INT UNSIGNED NULL,
            `reviewed_at` TIMESTAMP NULL,
            `feedback` TEXT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `uk_requirement` (`assignment_id`, `requirement_id`),
            KEY `idx_ar_status` (`status`)
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

    echo "\n<h1 style='color:#00ff88'>✅ SETUP COMPLETO!</h1>\n";
    echo "</pre>";

    echo "<div style='background:#1a1a2e; padding:20px; font-family:system-ui;'>";
    echo "<a href='/cruzeiro-do-sul-juveve/admin/especialidades' style='padding:12px 24px; background:#00d9ff; color:#000; text-decoration:none; border-radius:8px;'>Ver Catálogo →</a>";
    echo "</div>";

} catch (Exception $e) {
    echo "<pre style='color:red'>ERRO: " . $e->getMessage() . "</pre>";
}
