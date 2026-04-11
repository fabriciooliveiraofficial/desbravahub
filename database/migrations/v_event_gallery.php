<?php
/**
 * Migration: v_event_gallery
 * 
 * Adds support for event activity galleries.
 */

require_once dirname(dirname(__DIR__)) . '/bootstrap/bootstrap.php';

echo "🚀 Criando suporte a Galeria de Atividades...\n";

try {
    db_query("CREATE TABLE IF NOT EXISTS `event_gallery` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `event_id` INT UNSIGNED NOT NULL,
        `tenant_id` INT UNSIGNED NOT NULL,
        `image_url` VARCHAR(500) NOT NULL,
        `caption` VARCHAR(255) NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    echo "✅ Tabela 'event_gallery' criada.\n";
    echo "\n🎉 Migração concluída com sucesso!\n";
} catch (\Exception $e) {
    echo "\n❌ ERRO na migração: " . $e->getMessage() . "\n";
    exit(1);
}
