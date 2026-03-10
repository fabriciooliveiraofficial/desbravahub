<?php
/**
 * Migration: Certificate System
 * Creates issued_certificates table for tracking generated certificates.
 *
 * Run: php database/migrate_certificates.php
 */

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/bootstrap/bootstrap.php';

echo "Running certificate migration...\n";

$sql = "
CREATE TABLE IF NOT EXISTS issued_certificates (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id       INT NOT NULL,
    user_id         INT NOT NULL,
    assignment_type VARCHAR(20)  NOT NULL COMMENT 'specialty or program',
    assignment_id   INT          NOT NULL,
    certificate_hash VARCHAR(64) NOT NULL UNIQUE,
    file_path       VARCHAR(500) NULL     COMMENT 'Cached PDF path, relative to BASE_PATH',
    issued_at       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_tenant_user        (tenant_id, user_id),
    INDEX idx_tenant_assignment  (tenant_id, assignment_type, assignment_id),
    INDEX idx_hash               (certificate_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

try {
    db_query($sql);
    echo "✅ Table issued_certificates created (or already exists).\n";
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Migration complete.\n";
