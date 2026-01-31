<?php
// Load helpers
require_once __DIR__ . '/../helpers/env.php';
require_once __DIR__ . '/../helpers/config.php';
require_once __DIR__ . '/../helpers/database.php';

// Force load env first
env('APP_ENV');
// Override DB_HOST in cache
$GLOBALS['__env_cache']['DB_HOST'] = '127.0.0.1';


$pdo = db();

echo "Migrating Email tables...\n";

// Table: tenant_smtp_settings
$sql = "CREATE TABLE IF NOT EXISTS tenant_smtp_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    smtp_host VARCHAR(255) NOT NULL,
    smtp_port INT DEFAULT 587,
    smtp_user VARCHAR(255),
    smtp_pass_encrypted TEXT,
    from_email VARCHAR(255) NOT NULL,
    from_name VARCHAR(255),
    encryption VARCHAR(10) DEFAULT 'tls',
    is_verified BOOLEAN DEFAULT 0,
    verified_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
)";

try {
    $pdo->exec($sql);
    echo "Created 'tenant_smtp_settings' table.\n";
} catch (PDOException $e) {
    echo "Error creating 'tenant_smtp_settings' table: " . $e->getMessage() . "\n";
}

// Table: composed_emails
$sql = "CREATE TABLE IF NOT EXISTS composed_emails (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    sender_id INT NOT NULL,
    recipient_type ENUM('individual', 'role', 'unit', 'all') DEFAULT 'individual',
    recipient_ids JSON,
    recipient_emails JSON,
    subject VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    status ENUM('draft', 'queued', 'sending', 'sent', 'failed') DEFAULT 'draft',
    sent_at TIMESTAMP NULL,
    sent_count INT DEFAULT 0,
    failed_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
)";

try {
    $pdo->exec($sql);
    echo "Created 'composed_emails' table.\n";
} catch (PDOException $e) {
    echo "Error creating 'composed_emails' table: " . $e->getMessage() . "\n";
}

echo "Migration completed.\n";
