<?php
/**
 * Standalone Migration: Member Invitations Table
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Migration</title>";
echo "<style>body{font-family:monospace;background:#1a1a2e;color:#e0e0e0;padding:30px;}pre{background:#16213e;padding:20px;border-radius:10px;}h1{color:#00d9ff;}.success{color:#00ff88;}.error{color:#ff6b6b;}</style></head><body>";
echo "<h1>🔧 Member Invitations Migration</h1>";
echo "<pre>";

// Load .env
$envFile = dirname(__DIR__) . '/.env';
$envContent = file_get_contents($envFile);
$lines = explode("\n", $envContent);
$env = [];
foreach ($lines as $line) {
    $line = trim($line);
    if (empty($line) || $line[0] === '#')
        continue;
    if (strpos($line, '=') !== false) {
        list($key, $value) = explode('=', $line, 2);
        $env[trim($key)] = trim($value);
    }
}

$dbHost = $env['DB_HOST'] ?? 'localhost';
$dbName = $env['DB_DATABASE'] ?? '';
$dbUser = $env['DB_USERNAME'] ?? '';
$dbPass = $env['DB_PASSWORD'] ?? '';

try {
    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "<span class='success'>✅ Connected!</span>\n\n";

    // Create member_invitations table
    echo "Creating member_invitations table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `member_invitations` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `tenant_id` INT UNSIGNED NOT NULL,
            `invited_by` INT UNSIGNED NOT NULL,
            `email` VARCHAR(255) NOT NULL,
            `name` VARCHAR(255) NULL,
            `role_name` VARCHAR(50) NOT NULL DEFAULT 'pathfinder',
            `custom_message` TEXT NULL,
            `token` VARCHAR(64) NOT NULL,
            `expires_at` TIMESTAMP NOT NULL,
            `accepted_at` TIMESTAMP NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_member_token` (`token`),
            KEY `idx_member_email` (`email`),
            KEY `idx_member_tenant` (`tenant_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<span class='success'>✅ member_invitations created</span>\n\n";

    echo "<span class='success'>🎉 Migration completed!</span>\n";
    echo "\n⚠️ Delete this file after use!";

} catch (Exception $e) {
    echo "<span class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</span>\n";
}

echo "</pre></body></html>";
