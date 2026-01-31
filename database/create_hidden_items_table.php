<?php
/**
 * Migration: Create Tenant Hidden Items Table
 * 
 * Stores IDs of standard items (specialties/classes) that a tenant wants to hide.
 */

require_once __DIR__ . '/../bootstrap/bootstrap.php';

echo "Starting migration: Create Tenant Hidden Items Table\n";
echo "================================================\n\n";

try {
    db_query("
        CREATE TABLE IF NOT EXISTS `tenant_hidden_items` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `tenant_id` INT UNSIGNED NOT NULL,
            `item_id` VARCHAR(100) NOT NULL COMMENT 'ID of the specialty/class',
            `type` ENUM('specialty', 'class', 'program') NOT NULL DEFAULT 'specialty',
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_hidden_items` (`tenant_id`, `item_id`),
            KEY `idx_hidden_items_tenant` (`tenant_id`),
            CONSTRAINT `fk_hidden_items_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "OK: Table 'tenant_hidden_items' created or already exists.\n";
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n================================================\n";
echo "Migration completed!\n";
