<?php
/**
 * Migration: Create Specialties Table
 * 
 * Run this script to create the table for custom specialties.
 * php database/create_specialties_table.php
 */

require_once __DIR__ . '/../bootstrap/bootstrap.php';

echo "Starting migration: Create Specialties Table\n";
echo "============================================\n\n";

try {
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
    echo "OK: Table 'specialties' created or already exists.\n";
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n============================================\n";
echo "Migration completed!\n";
