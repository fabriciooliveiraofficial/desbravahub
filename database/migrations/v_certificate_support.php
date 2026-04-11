<?php
/**
 * Migration: v_certificate_support
 * 
 * Creates the issued_certificates table if it doesn't exist.
 * This table is required for the CertificateService.
 */

require_once dirname(dirname(__DIR__)) . '/bootstrap/bootstrap.php';

echo "🚀 Iniciando migração de suporte a certificados...\n";

try {
    db_query("CREATE TABLE IF NOT EXISTS `issued_certificates` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `tenant_id` INT UNSIGNED NOT NULL,
        `user_id` INT UNSIGNED NOT NULL,
        `assignment_type` ENUM('specialty', 'program', 'event') NOT NULL,
        `assignment_id` INT UNSIGNED NOT NULL,
        `certificate_hash` VARCHAR(64) NOT NULL,
        `file_path` VARCHAR(500) NULL,
        `issued_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uk_certificate_hash` (`certificate_hash`),
        KEY `idx_certificates_user` (`user_id`),
        KEY `idx_certificates_tenant` (`tenant_id`),
        CONSTRAINT `fk_certificates_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
        CONSTRAINT `fk_certificates_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    echo "✅ Tabela 'issued_certificates' criada/verificada.\n";
    echo "\n🎉 Migração concluída com sucesso!\n";
} catch (\Exception $e) {
    echo "\n❌ ERRO na migração: " . $e->getMessage() . "\n";
    exit(1);
}
