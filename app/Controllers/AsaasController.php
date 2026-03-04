<?php
/**
 * Asaas Controller
 * 
 * Handles all payment-related routes for the Asaas integration.
 * Manages: Connection, Payments Dashboard, Subscriptions, Store, Webhooks.
 */

namespace App\Controllers;

use App\Core\App;
use App\Core\View;
use App\Services\AsaasService;

class AsaasController
{
    /**
     * Payments Dashboard - Main page
     */
    public function index(): void
    {
        $user = App::user();
        $tenant = App::tenant();

        if (!in_array($user['role_name'] ?? '', ['admin', 'director'])) {
            http_response_code(403);
            echo "Acesso negado";
            exit;
        }

        $settings = null;
        try {
            $settings = db_fetch_one(
                "SELECT * FROM tenant_asaas_settings WHERE tenant_id = ?",
                [$tenant['id']]
            );
        } catch (\Exception $e) {
            // Table may not exist yet
        }

        // Get stats from local DB
        $stats = [
            'total_revenue' => 0,
            'this_month' => 0,
            'pending' => 0,
            'transactions' => 0,
        ];

        try {
            $stats['total_revenue'] = db_fetch_one(
                "SELECT COALESCE(SUM(amount_cents), 0) as total FROM asaas_payments WHERE tenant_id = ? AND status IN ('CONFIRMED', 'RECEIVED')",
                [$tenant['id']]
            )['total'] ?? 0;

            $stats['this_month'] = db_fetch_one(
                "SELECT COALESCE(SUM(amount_cents), 0) as total FROM asaas_payments WHERE tenant_id = ? AND status IN ('CONFIRMED', 'RECEIVED') AND MONTH(paid_at) = MONTH(NOW()) AND YEAR(paid_at) = YEAR(NOW())",
                [$tenant['id']]
            )['total'] ?? 0;

            $stats['pending'] = db_fetch_one(
                "SELECT COALESCE(SUM(amount_cents), 0) as total FROM asaas_payments WHERE tenant_id = ? AND status = 'PENDING'",
                [$tenant['id']]
            )['total'] ?? 0;

            $stats['transactions'] = db_fetch_one(
                "SELECT COUNT(*) as total FROM asaas_payments WHERE tenant_id = ?",
                [$tenant['id']]
            )['total'] ?? 0;
        } catch (\Exception $e) {
            // Tables may not exist
        }

        // Get recent payments
        $recentPayments = [];
        try {
            $recentPayments = db_fetch_all(
                "SELECT p.*, u.name as payer_name 
                 FROM asaas_payments p 
                 LEFT JOIN users u ON p.user_id = u.id 
                 WHERE p.tenant_id = ? 
                 ORDER BY p.created_at DESC LIMIT 20",
                [$tenant['id']]
            );
        } catch (\Exception $e) {}

        // Get Asaas balance if connected
        $balance = null;
        if ($settings && $settings['is_connected']) {
            $asaas = AsaasService::fromTenant($tenant['id']);
            if ($asaas) {
                $balance = $asaas->getBalance();
            }
        }

        View::render('admin/payments/index', [
            'tenant' => $tenant,
            'user' => $user,
            'settings' => $settings,
            'stats' => $stats,
            'balance' => $balance,
            'recentPayments' => $recentPayments,
            'pageTitle' => 'Pagamentos',
            'pageIcon' => 'payments',
        ]);
    }

    /**
     * Connect Asaas account (simplified flow - API Key input)
     */
    public function connect(): void
    {
        $user = App::user();
        $tenant = App::tenant();

        if (!in_array($user['role_name'] ?? '', ['admin', 'director'])) {
            http_response_code(403);
            exit;
        }

        $apiKey = trim($_POST['asaas_api_key'] ?? '');
        $environment = $_POST['environment'] ?? 'sandbox';

        if (empty($apiKey)) {
            $this->jsonResponse(['error' => 'API Key é obrigatória'], 400);
            return;
        }

        // Validate the API key by trying to get account info
        $asaas = new AsaasService($apiKey, $environment);
        $accountInfo = $asaas->getBalance();

        if (!$accountInfo || isset($accountInfo['error'])) {
            $this->jsonResponse(['error' => 'API Key inválida. Verifique e tente novamente.'], 400);
            return;
        }

        // Save or update settings
        $existing = db_fetch_one(
            "SELECT id FROM tenant_asaas_settings WHERE tenant_id = ?",
            [$tenant['id']]
        );

        $data = [
            'asaas_api_key' => $apiKey,
            'environment' => $environment,
            'is_connected' => 1,
            'connected_at' => date('Y-m-d H:i:s'),
        ];

        if ($existing) {
            db_update('tenant_asaas_settings', $data, 'id = ?', [$existing['id']]);
        } else {
            $data['tenant_id'] = $tenant['id'];
            db_insert('tenant_asaas_settings', $data);
        }

        $this->jsonResponse([
            'success' => true,
            'message' => '🎉 Asaas conectado com sucesso!',
        ]);
    }

    /**
     * Disconnect Asaas account
     */
    public function disconnect(): void
    {
        $user = App::user();
        $tenant = App::tenant();

        if (!in_array($user['role_name'] ?? '', ['admin', 'director'])) {
            http_response_code(403);
            exit;
        }

        $existing = db_fetch_one(
            "SELECT id FROM tenant_asaas_settings WHERE tenant_id = ?",
            [$tenant['id']]
        );

        if ($existing) {
            db_update('tenant_asaas_settings', [
                'asaas_api_key' => null,
                'is_connected' => 0,
                'connected_at' => null,
            ], 'id = ?', [$existing['id']]);
        }

        $this->jsonResponse([
            'success' => true,
            'message' => 'Conta Asaas desconectada.',
        ]);
    }

    /**
     * Update payment settings (installments, methods, etc.)
     */
    public function updateSettings(): void
    {
        $user = App::user();
        $tenant = App::tenant();

        if (!in_array($user['role_name'] ?? '', ['admin', 'director'])) {
            http_response_code(403);
            exit;
        }

        $data = [
            'installment_enabled' => isset($_POST['installment_enabled']) ? 1 : 0,
            'max_installments' => max(1, min(21, (int)($_POST['max_installments'] ?? 12))),
            'installment_interest_rate' => max(0, min(100, (float)($_POST['installment_interest_rate'] ?? 0))),
            'pix_enabled' => isset($_POST['pix_enabled']) ? 1 : 0,
            'boleto_enabled' => isset($_POST['boleto_enabled']) ? 1 : 0,
            'credit_card_enabled' => isset($_POST['credit_card_enabled']) ? 1 : 0,
        ];

        $existing = db_fetch_one(
            "SELECT id FROM tenant_asaas_settings WHERE tenant_id = ?",
            [$tenant['id']]
        );

        if ($existing) {
            db_update('tenant_asaas_settings', $data, 'id = ?', [$existing['id']]);
        }

        $this->jsonResponse([
            'success' => true,
            'message' => 'Configurações salvas!',
        ]);
    }

    /**
     * Create a payment charge
     */
    public function createCharge(): void
    {
        $user = App::user();
        $tenant = App::tenant();

        if (!in_array($user['role_name'] ?? '', ['admin', 'director'])) {
            http_response_code(403);
            exit;
        }

        $asaas = AsaasService::fromTenant($tenant['id']);
        if (!$asaas) {
            $this->jsonResponse(['error' => 'Asaas não conectado'], 400);
            return;
        }

        $targetUserId = (int)($_POST['user_id'] ?? 0);
        $amount = (float)($_POST['amount'] ?? 0);
        $description = $_POST['description'] ?? '';
        $billingType = $_POST['billing_type'] ?? 'UNDEFINED';
        $installments = (int)($_POST['installments'] ?? 1);
        $type = $_POST['type'] ?? 'custom';
        $referenceId = (int)($_POST['reference_id'] ?? 0);

        if ($amount <= 0) {
            $this->jsonResponse(['error' => 'Valor deve ser maior que zero'], 400);
            return;
        }

        // Get target user
        $targetUser = db_fetch_one("SELECT * FROM users WHERE id = ? AND tenant_id = ?", [$targetUserId, $tenant['id']]);
        if (!$targetUser) {
            $this->jsonResponse(['error' => 'Usuário não encontrado'], 404);
            return;
        }

        // Ensure customer exists in Asaas
        $customerId = $asaas->ensureCustomer($tenant['id'], $targetUser);
        if (!$customerId) {
            $this->jsonResponse(['error' => 'Erro ao criar cliente no Asaas'], 500);
            return;
        }

        // Calculate installment with buyer interest
        $settings = db_fetch_one("SELECT * FROM tenant_asaas_settings WHERE tenant_id = ?", [$tenant['id']]);
        $customRate = ($settings['installment_interest_rate'] ?? 0) > 0 ? (float)$settings['installment_interest_rate'] : null;
        $calc = $asaas->calculateInstallments($amount, $installments, $customRate);

        // Create payment
        $paymentOptions = [
            'customer' => $customerId,
            'billingType' => $billingType,
            'value' => $calc['totalValue'],
            'description' => $description,
            'externalReference' => "{$type}_{$referenceId}_tenant_{$tenant['id']}",
        ];

        if ($installments > 1) {
            $paymentOptions['installmentCount'] = $installments;
            $paymentOptions['totalValue'] = $calc['totalValue'];
        }

        $result = $asaas->createPayment($paymentOptions);

        if (!$result || isset($result['error'])) {
            $this->jsonResponse(['error' => 'Erro ao criar cobrança: ' . ($result['message'] ?? 'Desconhecido')], 500);
            return;
        }

        // Save to local DB
        db_insert('asaas_payments', [
            'tenant_id' => $tenant['id'],
            'user_id' => $targetUserId,
            'asaas_payment_id' => $result['id'],
            'type' => $type,
            'reference_id' => $referenceId ?: null,
            'description' => $description,
            'amount_cents' => (int)($calc['totalValue'] * 100),
            'installment_count' => $installments,
            'billing_type' => $billingType,
            'status' => $result['status'] ?? 'PENDING',
            'payment_link' => $result['invoiceUrl'] ?? null,
            'due_date' => $result['dueDate'] ?? null,
        ]);

        $this->jsonResponse([
            'success' => true,
            'message' => 'Cobrança criada com sucesso!',
            'paymentLink' => $result['invoiceUrl'] ?? null,
            'paymentId' => $result['id'],
        ]);
    }

    // ==========================================
    // Subscriptions (Mensalidades)
    // ==========================================

    /**
     * Subscriptions management page
     */
    public function subscriptions(): void
    {
        $user = App::user();
        $tenant = App::tenant();

        if (!in_array($user['role_name'] ?? '', ['admin', 'director'])) {
            http_response_code(403);
            exit;
        }

        $plans = [];
        try {
            $plans = db_fetch_all(
                "SELECT p.*, 
                    (SELECT COUNT(*) FROM user_subscriptions us WHERE us.plan_id = p.id AND us.status = 'ACTIVE') as active_subscribers
                 FROM payment_plans p 
                 WHERE p.tenant_id = ? 
                 ORDER BY p.created_at DESC",
                [$tenant['id']]
            );
        } catch (\Exception $e) {}

        View::render('admin/payments/subscriptions', [
            'tenant' => $tenant,
            'user' => $user,
            'plans' => $plans,
            'pageTitle' => 'Mensalidades',
            'pageIcon' => 'autorenew',
        ]);
    }

    /**
     * Create a subscription plan
     */
    public function createPlan(): void
    {
        $user = App::user();
        $tenant = App::tenant();

        if (!in_array($user['role_name'] ?? '', ['admin', 'director'])) {
            http_response_code(403);
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $amount = (float)($_POST['amount'] ?? 0);
        $cycle = $_POST['cycle'] ?? 'MONTHLY';

        if (empty($name) || $amount <= 0) {
            $this->jsonResponse(['error' => 'Nome e valor são obrigatórios'], 400);
            return;
        }

        db_insert('payment_plans', [
            'tenant_id' => $tenant['id'],
            'name' => $name,
            'description' => $description,
            'amount_cents' => (int)($amount * 100),
            'billing_cycle' => $cycle,
            'is_active' => 1,
        ]);

        $this->jsonResponse([
            'success' => true,
            'message' => 'Plano criado com sucesso!',
        ]);
    }

    /**
     * Assign a subscription to a member
     */
    public function subscribeMember(): void
    {
        $user = App::user();
        $tenant = App::tenant();

        if (!in_array($user['role_name'] ?? '', ['admin', 'director'])) {
            http_response_code(403);
            exit;
        }

        $planId = (int)($_POST['plan_id'] ?? 0);
        $memberId = (int)($_POST['user_id'] ?? 0);

        $plan = db_fetch_one("SELECT * FROM payment_plans WHERE id = ? AND tenant_id = ?", [$planId, $tenant['id']]);
        $member = db_fetch_one("SELECT * FROM users WHERE id = ? AND tenant_id = ?", [$memberId, $tenant['id']]);

        if (!$plan || !$member) {
            $this->jsonResponse(['error' => 'Plano ou membro não encontrado'], 404);
            return;
        }

        $asaas = AsaasService::fromTenant($tenant['id']);
        if (!$asaas) {
            $this->jsonResponse(['error' => 'Asaas não conectado'], 400);
            return;
        }

        // Ensure customer
        $customerId = $asaas->ensureCustomer($tenant['id'], $member);
        if (!$customerId) {
            $this->jsonResponse(['error' => 'Erro ao criar cliente'], 500);
            return;
        }

        // Create subscription in Asaas
        $result = $asaas->createSubscription([
            'customer' => $customerId,
            'billingType' => 'UNDEFINED', // Let user choose at payment time
            'value' => $plan['amount_cents'] / 100,
            'cycle' => $plan['billing_cycle'],
            'description' => $plan['name'],
            'externalReference' => "plan_{$planId}_user_{$memberId}_tenant_{$tenant['id']}",
        ]);

        if (!$result || isset($result['error'])) {
            $this->jsonResponse(['error' => 'Erro ao criar assinatura: ' . ($result['message'] ?? 'Desconhecido')], 500);
            return;
        }

        // Save locally
        db_insert('user_subscriptions', [
            'tenant_id' => $tenant['id'],
            'user_id' => $memberId,
            'plan_id' => $planId,
            'asaas_subscription_id' => $result['id'],
            'status' => 'ACTIVE',
            'next_due_date' => $result['nextDueDate'] ?? null,
        ]);

        $this->jsonResponse([
            'success' => true,
            'message' => "Mensalidade atribuída a {$member['name']}!",
        ]);
    }

    // ==========================================
    // Store (Produtos)
    // ==========================================

    /**
     * Store management page
     */
    public function store(): void
    {
        $user = App::user();
        $tenant = App::tenant();

        if (!in_array($user['role_name'] ?? '', ['admin', 'director'])) {
            http_response_code(403);
            exit;
        }

        $products = [];
        try {
            $products = db_fetch_all(
                "SELECT * FROM store_products WHERE tenant_id = ? ORDER BY created_at DESC",
                [$tenant['id']]
            );
        } catch (\Exception $e) {}

        $orders = [];
        try {
            $orders = db_fetch_all(
                "SELECT o.*, u.name as buyer_name 
                 FROM store_orders o 
                 LEFT JOIN users u ON o.user_id = u.id 
                 WHERE o.tenant_id = ? 
                 ORDER BY o.created_at DESC LIMIT 50",
                [$tenant['id']]
            );
        } catch (\Exception $e) {}

        View::render('admin/payments/store', [
            'tenant' => $tenant,
            'user' => $user,
            'products' => $products,
            'orders' => $orders,
            'pageTitle' => 'Loja do Clube',
            'pageIcon' => 'storefront',
        ]);
    }

    /**
     * Create a product
     */
    public function createProduct(): void
    {
        $user = App::user();
        $tenant = App::tenant();

        if (!in_array($user['role_name'] ?? '', ['admin', 'director'])) {
            http_response_code(403);
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $category = trim($_POST['category'] ?? '');
        $stock = isset($_POST['stock']) && $_POST['stock'] !== '' ? (int)$_POST['stock'] : null;

        if (empty($name) || $price <= 0) {
            $this->jsonResponse(['error' => 'Nome e preço são obrigatórios'], 400);
            return;
        }

        // Handle image upload
        $imageUrl = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = BASE_PATH . '/public/uploads/products/' . $tenant['id'] . '/';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0755, true);
            }
            $filename = time() . '_' . basename($_FILES['image']['name']);
            move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename);
            $imageUrl = '/uploads/products/' . $tenant['id'] . '/' . $filename;
        }

        db_insert('store_products', [
            'tenant_id' => $tenant['id'],
            'name' => $name,
            'description' => $description,
            'price_cents' => (int)($price * 100),
            'image_url' => $imageUrl,
            'category' => $category ?: null,
            'stock' => $stock,
            'is_active' => 1,
        ]);

        $this->jsonResponse([
            'success' => true,
            'message' => 'Produto cadastrado!',
        ]);
    }

    // ==========================================
    // Webhook
    // ==========================================

    /**
     * Handle Asaas webhook
     */
    public function webhook(): void
    {
        $body = file_get_contents('php://input');

        if (empty($body)) {
            http_response_code(400);
            echo json_encode(['error' => 'Empty body']);
            return;
        }

        $tenant = App::tenant();

        $asaas = AsaasService::fromTenant($tenant['id']);
        if (!$asaas) {
            http_response_code(200); // Acknowledge to prevent retries
            echo json_encode(['status' => 'no_integration']);
            return;
        }

        $webhook = $asaas->processWebhook($body);

        if (isset($webhook['error'])) {
            error_log("AsaasController::webhook - Error: " . $webhook['error']);
            http_response_code(200);
            echo json_encode(['status' => 'error']);
            return;
        }

        $event = $webhook['event'] ?? '';
        $paymentId = $webhook['paymentId'] ?? null;
        $status = $webhook['status'] ?? null;

        error_log("AsaasController::webhook - Event: {$event}, PaymentID: {$paymentId}, Status: {$status}");

        if ($paymentId) {
            // Update local payment record
            $localPayment = db_fetch_one(
                "SELECT id FROM asaas_payments WHERE asaas_payment_id = ? AND tenant_id = ?",
                [$paymentId, $tenant['id']]
            );

            if ($localPayment) {
                $updateData = ['status' => $status];

                if (in_array($status, ['CONFIRMED', 'RECEIVED'])) {
                    $updateData['paid_at'] = date('Y-m-d H:i:s');
                    $updateData['net_amount_cents'] = isset($webhook['netValue']) ? (int)($webhook['netValue'] * 100) : null;
                }

                db_update('asaas_payments', $updateData, 'id = ?', [$localPayment['id']]);
            }

            // Update store order if applicable
            $ref = $webhook['externalReference'] ?? '';
            if (str_starts_with($ref, 'order_')) {
                $orderId = (int)explode('_', $ref)[1];
                if ($orderId && in_array($status, ['CONFIRMED', 'RECEIVED'])) {
                    try {
                        db_update('store_orders', ['status' => 'paid'], 'id = ?', [$orderId]);
                    } catch (\Exception $e) {}
                }
            }
        }

        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'ok']);
    }

    // ==========================================
    // Installment Calculator API
    // ==========================================

    /**
     * Get installment options for a given amount (AJAX)
     */
    public function installmentOptions(): void
    {
        $tenant = App::tenant();

        $amount = (float)($_GET['amount'] ?? 0);
        if ($amount <= 0) {
            $this->jsonResponse(['error' => 'Valor inválido'], 400);
            return;
        }

        $settings = db_fetch_one(
            "SELECT * FROM tenant_asaas_settings WHERE tenant_id = ?",
            [$tenant['id']]
        );

        $maxInstallments = $settings['max_installments'] ?? 12;
        $customRate = ($settings['installment_interest_rate'] ?? 0) > 0 ? (float)$settings['installment_interest_rate'] : null;

        $asaas = new AsaasService('dummy', 'sandbox'); // Calculator doesn't need API
        $options = $asaas->getInstallmentOptions($amount, $maxInstallments, $customRate);

        $this->jsonResponse([
            'success' => true,
            'options' => $options,
        ]);
    }

    // ==========================================
    // Helpers
    // ==========================================

    private function jsonResponse(array $data, int $code = 200): void
    {
        if (ob_get_length()) ob_clean();
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
