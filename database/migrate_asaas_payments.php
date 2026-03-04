<?php
/**
 * Asaas Payment Integration - Database Migration
 * 
 * Creates all tables required for the Asaas payment gateway integration.
 * Run this script once to set up the schema.
 * 
 * Usage: php database/migrate_asaas_payments.php
 */

require_once __DIR__ . '/../bootstrap.php';

echo "=== Asaas Payment Integration Migration ===\n\n";

$queries = [

    // 1. Asaas account settings per tenant (replaces/extends Stripe settings)
    "CREATE TABLE IF NOT EXISTS tenant_asaas_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tenant_id INT NOT NULL,
        asaas_api_key VARCHAR(255) DEFAULT NULL COMMENT 'API Key da subconta Asaas',
        asaas_wallet_id VARCHAR(100) DEFAULT NULL COMMENT 'walletId da subconta',
        asaas_account_id VARCHAR(100) DEFAULT NULL COMMENT 'ID da subconta no Asaas',
        environment ENUM('sandbox', 'production') DEFAULT 'sandbox',
        is_connected TINYINT(1) DEFAULT 0,
        installment_enabled TINYINT(1) DEFAULT 1,
        max_installments INT DEFAULT 12 COMMENT 'Máximo de parcelas permitido (até 21)',
        installment_interest_rate DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Taxa de juros mensal repasse ao comprador (%)',
        pix_enabled TINYINT(1) DEFAULT 1,
        boleto_enabled TINYINT(1) DEFAULT 1,
        credit_card_enabled TINYINT(1) DEFAULT 1,
        connected_at DATETIME DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uk_tenant (tenant_id),
        INDEX idx_tenant (tenant_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // 2. Map platform users to Asaas customers
    "CREATE TABLE IF NOT EXISTS asaas_customers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tenant_id INT NOT NULL,
        user_id INT NOT NULL,
        asaas_customer_id VARCHAR(100) NOT NULL COMMENT 'ID do cliente no Asaas',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uk_tenant_user (tenant_id, user_id),
        INDEX idx_asaas_customer (asaas_customer_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // 3. Payment transactions (central log for all payments)
    "CREATE TABLE IF NOT EXISTS asaas_payments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tenant_id INT NOT NULL,
        user_id INT DEFAULT NULL COMMENT 'Comprador',
        asaas_payment_id VARCHAR(100) DEFAULT NULL COMMENT 'ID da cobrança no Asaas',
        asaas_subscription_id VARCHAR(100) DEFAULT NULL,
        type ENUM('event', 'subscription', 'product', 'custom') NOT NULL DEFAULT 'custom',
        reference_id INT DEFAULT NULL COMMENT 'ID do evento/plano/produto',
        description VARCHAR(500) DEFAULT NULL,
        amount_cents INT NOT NULL COMMENT 'Valor total em centavos',
        net_amount_cents INT DEFAULT NULL COMMENT 'Valor líquido recebido',
        installment_count INT DEFAULT 1,
        billing_type ENUM('BOLETO', 'CREDIT_CARD', 'PIX', 'UNDEFINED') DEFAULT 'UNDEFINED',
        status VARCHAR(50) DEFAULT 'PENDING' COMMENT 'PENDING, CONFIRMED, RECEIVED, OVERDUE, REFUNDED, etc.',
        payment_link VARCHAR(500) DEFAULT NULL COMMENT 'Link de pagamento ou invoice',
        paid_at DATETIME DEFAULT NULL,
        due_date DATE DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_tenant (tenant_id),
        INDEX idx_user (user_id),
        INDEX idx_asaas_id (asaas_payment_id),
        INDEX idx_type_ref (type, reference_id),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // 4. Subscription plans (mensalidades)
    "CREATE TABLE IF NOT EXISTS payment_plans (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tenant_id INT NOT NULL,
        name VARCHAR(255) NOT NULL COMMENT 'Ex: Mensalidade Desbravador 2025',
        description TEXT DEFAULT NULL,
        amount_cents INT NOT NULL COMMENT 'Valor em centavos',
        billing_cycle ENUM('WEEKLY', 'BIWEEKLY', 'MONTHLY', 'QUARTERLY', 'SEMIANNUALLY', 'YEARLY') DEFAULT 'MONTHLY',
        max_installments INT DEFAULT NULL,
        is_active TINYINT(1) DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_tenant (tenant_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // 5. User subscriptions (link members to plans)
    "CREATE TABLE IF NOT EXISTS user_subscriptions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tenant_id INT NOT NULL,
        user_id INT NOT NULL,
        plan_id INT NOT NULL,
        asaas_subscription_id VARCHAR(100) DEFAULT NULL COMMENT 'ID da assinatura no Asaas',
        status VARCHAR(50) DEFAULT 'ACTIVE' COMMENT 'ACTIVE, INACTIVE, EXPIRED',
        next_due_date DATE DEFAULT NULL,
        started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        cancelled_at DATETIME DEFAULT NULL,
        INDEX idx_tenant_user (tenant_id, user_id),
        INDEX idx_plan (plan_id),
        INDEX idx_asaas_sub (asaas_subscription_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // 6. Store products (merchandise)
    "CREATE TABLE IF NOT EXISTS store_products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tenant_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        description TEXT DEFAULT NULL,
        price_cents INT NOT NULL,
        image_url VARCHAR(500) DEFAULT NULL,
        category VARCHAR(100) DEFAULT NULL COMMENT 'uniforme, insignia, lenço, etc.',
        stock INT DEFAULT NULL COMMENT 'NULL = sem controle de estoque',
        is_active TINYINT(1) DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_tenant (tenant_id),
        INDEX idx_active (is_active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // 7. Store orders
    "CREATE TABLE IF NOT EXISTS store_orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tenant_id INT NOT NULL,
        user_id INT NOT NULL,
        total_cents INT NOT NULL,
        status VARCHAR(50) DEFAULT 'pending' COMMENT 'pending, paid, shipped, delivered, cancelled',
        asaas_payment_id VARCHAR(100) DEFAULT NULL,
        notes TEXT DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_tenant (tenant_id),
        INDEX idx_user (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // 8. Store order items
    "CREATE TABLE IF NOT EXISTS store_order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL DEFAULT 1,
        unit_price_cents INT NOT NULL,
        INDEX idx_order (order_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // 9. Add payment fields to events (if events table exists)
    "ALTER TABLE events ADD COLUMN IF NOT EXISTS price_cents INT DEFAULT 0 COMMENT 'Taxa de inscrição em centavos'",
    "ALTER TABLE events ADD COLUMN IF NOT EXISTS payment_required TINYINT(1) DEFAULT 0",
];

$success = 0;
$errors = 0;

foreach ($queries as $sql) {
    try {
        db_query($sql);
        // Extract table name for display
        if (preg_match('/(?:CREATE TABLE IF NOT EXISTS|ALTER TABLE)\s+(\w+)/i', $sql, $m)) {
            echo "  ✅ {$m[1]}\n";
        } else {
            echo "  ✅ Query executed\n";
        }
        $success++;
    } catch (\Exception $e) {
        $msg = $e->getMessage();
        // Ignore "column already exists" or "table already exists"
        if (str_contains($msg, 'Duplicate column') || str_contains($msg, 'already exists')) {
            echo "  ⏭️  Skipped (already exists)\n";
            $success++;
        } else {
            echo "  ❌ Error: {$msg}\n";
            $errors++;
        }
    }
}

echo "\n--- Migration Complete ---\n";
echo "Success: {$success} | Errors: {$errors}\n";
