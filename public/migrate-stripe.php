<?php
/**
 * Standalone Migration: Stripe Payment System
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Migration</title>";
echo "<style>body{font-family:monospace;background:#1a1a2e;color:#e0e0e0;padding:30px;}pre{background:#16213e;padding:20px;border-radius:10px;}h1{color:#00d9ff;}.success{color:#00ff88;}.error{color:#ff6b6b;}</style></head><body>";
echo "<h1>💳 Stripe Payment System Migration</h1>";
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

    // 1. Create tenant_stripe_settings table
    echo "Creating tenant_stripe_settings table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `tenant_stripe_settings` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `tenant_id` INT UNSIGNED NOT NULL,
            `stripe_account_id` VARCHAR(255) NULL,
            `is_connected` TINYINT(1) NOT NULL DEFAULT 0,
            `details_submitted` TINYINT(1) NOT NULL DEFAULT 0,
            `charges_enabled` TINYINT(1) NOT NULL DEFAULT 0,
            `payouts_enabled` TINYINT(1) NOT NULL DEFAULT 0,
            `default_currency` VARCHAR(3) NOT NULL DEFAULT 'BRL',
            `platform_fee_percent` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
            `connected_at` TIMESTAMP NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_tenant_stripe` (`tenant_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<span class='success'>✅ tenant_stripe_settings created</span>\n\n";

    // 2. Create payments table
    echo "Creating payments table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `payments` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `tenant_id` INT UNSIGNED NOT NULL,
            `user_id` INT UNSIGNED NULL,
            `stripe_payment_id` VARCHAR(255) NULL,
            `stripe_checkout_id` VARCHAR(255) NULL,
            `type` ENUM('event', 'membership', 'donation', 'material', 'other') NOT NULL DEFAULT 'other',
            `reference_id` INT UNSIGNED NULL COMMENT 'ID of event, product, etc',
            `reference_name` VARCHAR(255) NULL COMMENT 'Name of what was paid for',
            `amount_cents` INT NOT NULL,
            `currency` VARCHAR(3) NOT NULL DEFAULT 'BRL',
            `platform_fee_cents` INT NOT NULL DEFAULT 0,
            `status` ENUM('pending', 'processing', 'completed', 'failed', 'refunded', 'cancelled') NOT NULL DEFAULT 'pending',
            `payer_name` VARCHAR(255) NULL,
            `payer_email` VARCHAR(255) NULL,
            `payer_phone` VARCHAR(50) NULL,
            `metadata` JSON NULL,
            `paid_at` TIMESTAMP NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_payment_tenant` (`tenant_id`),
            KEY `idx_payment_user` (`user_id`),
            KEY `idx_payment_status` (`status`),
            KEY `idx_payment_type` (`type`),
            KEY `idx_stripe_checkout` (`stripe_checkout_id`),
            KEY `idx_stripe_payment` (`stripe_payment_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<span class='success'>✅ payments created</span>\n\n";

    // 3. Create subscriptions table (for recurring memberships)
    echo "Creating subscriptions table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `subscriptions` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `tenant_id` INT UNSIGNED NOT NULL,
            `user_id` INT UNSIGNED NOT NULL,
            `stripe_subscription_id` VARCHAR(255) NULL,
            `stripe_customer_id` VARCHAR(255) NULL,
            `name` VARCHAR(255) NOT NULL COMMENT 'e.g. Mensalidade Desbravador',
            `amount_cents` INT NOT NULL,
            `currency` VARCHAR(3) NOT NULL DEFAULT 'BRL',
            `interval_type` ENUM('monthly', 'quarterly', 'yearly') NOT NULL DEFAULT 'monthly',
            `status` ENUM('active', 'past_due', 'cancelled', 'paused') NOT NULL DEFAULT 'active',
            `current_period_start` DATE NULL,
            `current_period_end` DATE NULL,
            `cancelled_at` TIMESTAMP NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_sub_tenant` (`tenant_id`),
            KEY `idx_sub_user` (`user_id`),
            KEY `idx_sub_status` (`status`),
            KEY `idx_stripe_sub` (`stripe_subscription_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<span class='success'>✅ subscriptions created</span>\n\n";

    // 4. Create payment_items table (for itemized payments)
    echo "Creating payment_items table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `payment_items` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `payment_id` INT UNSIGNED NOT NULL,
            `name` VARCHAR(255) NOT NULL,
            `description` TEXT NULL,
            `quantity` INT NOT NULL DEFAULT 1,
            `unit_price_cents` INT NOT NULL,
            `total_cents` INT NOT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_item_payment` (`payment_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<span class='success'>✅ payment_items created</span>\n\n";

    echo "<span class='success' style='font-size:1.2em;'>🎉 Migration completed!</span>\n";
    echo "\nYou can now configure Stripe in the admin panel.\n";
    echo "\n<strong>⚠️ IMPORTANTE:</strong> Delete this file after use!";
    echo "\n\n<strong>📝 Próximos passos:</strong>";
    echo "\n1. Adicione as variáveis ao .env:";
    echo "\n   STRIPE_SECRET_KEY=sk_test_xxx";
    echo "\n   STRIPE_PUBLISHABLE_KEY=pk_test_xxx";
    echo "\n   STRIPE_WEBHOOK_SECRET=whsec_xxx";

} catch (Exception $e) {
    echo "<span class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</span>\n";
}

echo "</pre></body></html>";
