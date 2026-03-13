<?php
/**
 * Communication Hub Migration
 * Creates: public_leads, marketing_campaigns, communication_logs
 * Run once via: php database/migrate_communication_hub.php
 */

require_once __DIR__ . '/../bootstrap/bootstrap.php';

$pdo = db();
echo "=== Communication Hub Migration ===\n\n";

// public_leads
echo "Creating public_leads...\n";
$pdo->exec("
    CREATE TABLE IF NOT EXISTS `public_leads` (
        `id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        `tenant_id` INT UNSIGNED NOT NULL,
        `name` VARCHAR(120) NOT NULL,
        `phone` VARCHAR(30) NULL,
        `email` VARCHAR(160) NULL,
        `message` TEXT NULL,
        `status` ENUM('new','contacting','converted','dismissed') NOT NULL DEFAULT 'new',
        `source` VARCHAR(50) NOT NULL DEFAULT 'hub_cta',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX (`tenant_id`),
        INDEX (`status`),
        FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");
echo "  ✓ public_leads\n";

// marketing_campaigns
echo "Creating marketing_campaigns...\n";
$pdo->exec("
    CREATE TABLE IF NOT EXISTS `marketing_campaigns` (
        `id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        `tenant_id` INT UNSIGNED NOT NULL,
        `title` VARCHAR(200) NOT NULL,
        `type` ENUM('push','email','blast') NOT NULL DEFAULT 'push',
        `content` TEXT NOT NULL,
        `target_group` ENUM('leads','members','all') NOT NULL DEFAULT 'members',
        `status` ENUM('draft','sent','failed') NOT NULL DEFAULT 'draft',
        `sent_count` INT NOT NULL DEFAULT 0,
        `created_by` INT UNSIGNED NULL,
        `sent_at` TIMESTAMP NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (`tenant_id`),
        FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");
echo "  ✓ marketing_campaigns\n";

// communication_logs
echo "Creating communication_logs...\n";
$pdo->exec("
    CREATE TABLE IF NOT EXISTS `communication_logs` (
        `id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        `tenant_id` INT UNSIGNED NOT NULL,
        `lead_id` INT UNSIGNED NULL,
        `actor_user_id` INT UNSIGNED NULL,
        `channel` ENUM('whatsapp','email','phone','other') NOT NULL DEFAULT 'whatsapp',
        `note` TEXT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (`tenant_id`),
        INDEX (`lead_id`),
        FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");
echo "  ✓ communication_logs\n";

echo "\n✅ Migration complete.\n";
