<?php
/**
 * Asaas Service
 * 
 * Central service for all Asaas API interactions.
 * Handles: Customers, Payments, Subscriptions, Installments, Webhooks.
 * 
 * Docs: https://docs.asaas.com/reference
 */

namespace App\Services;

class AsaasService
{
    private string $apiKey;
    private string $baseUrl;
    private string $environment;

    // Asaas installment interest rates (approximate market rates)
    // These represent the % added per installment to cover card processing fees
    private const INSTALLMENT_INTEREST_RATES = [
        1  => 0.00,
        2  => 3.49,  3  => 4.99,  4  => 6.49,  5  => 7.99,
        6  => 9.49,  7  => 10.99, 8  => 12.49, 9  => 13.99,
        10 => 15.49, 11 => 16.99, 12 => 18.49,
        // Extended (Visa/Mastercard only)
        13 => 19.99, 14 => 21.49, 15 => 22.99, 16 => 24.49,
        17 => 25.99, 18 => 27.49, 19 => 28.99, 20 => 30.49,
        21 => 31.99,
    ];

    public function __construct(string $apiKey, string $environment = 'sandbox')
    {
        $this->apiKey = $apiKey;
        $this->environment = $environment;
        $this->baseUrl = $environment === 'production'
            ? 'https://api.asaas.com/v3'
            : 'https://sandbox.asaas.com/api/v3';
    }

    /**
     * Create instance from tenant settings
     */
    public static function fromTenant(int $tenantId): ?self
    {
        $settings = db_fetch_one(
            "SELECT * FROM tenant_asaas_settings WHERE tenant_id = ?",
            [$tenantId]
        );

        if (!$settings || !$settings['asaas_api_key']) {
            return null;
        }

        return new self($settings['asaas_api_key'], $settings['environment'] ?? 'sandbox');
    }

    // ==========================================
    // HTTP Client
    // ==========================================

    private function request(string $method, string $endpoint, array $data = []): ?array
    {
        $url = $this->baseUrl . $endpoint;

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'access_token: ' . $this->apiKey,
                'User-Agent: DesbravaHub/1.0',
            ],
            CURLOPT_TIMEOUT => 30,
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        } elseif ($method === 'GET' && !empty($data)) {
            curl_setopt($ch, CURLOPT_URL, $url . '?' . http_build_query($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            error_log("AsaasService::request - cURL Error: {$error}");
            return null;
        }

        $result = json_decode($response, true);

        if ($httpCode >= 400) {
            $errorMsg = $result['errors'][0]['description'] ?? ($result['message'] ?? 'Unknown error');
            error_log("AsaasService::request - HTTP {$httpCode}: {$errorMsg} | Endpoint: {$endpoint}");
            return ['error' => true, 'httpCode' => $httpCode, 'message' => $errorMsg, 'data' => $result];
        }

        return $result;
    }

    private function get(string $endpoint, array $params = []): ?array
    {
        return $this->request('GET', $endpoint, $params);
    }

    private function post(string $endpoint, array $data): ?array
    {
        return $this->request('POST', $endpoint, $data);
    }

    private function put(string $endpoint, array $data): ?array
    {
        return $this->request('PUT', $endpoint, $data);
    }

    private function delete(string $endpoint): ?array
    {
        return $this->request('DELETE', $endpoint);
    }

    // ==========================================
    // Subaccount (Asaas Connect)
    // ==========================================

    /**
     * Create a subaccount for a tenant (club)
     * This creates a new Asaas account linked to the platform's master account.
     */
    public function createSubaccount(array $tenantData): ?array
    {
        return $this->post('/accounts', [
            'name' => $tenantData['name'],
            'email' => $tenantData['email'],
            'cpfCnpj' => $tenantData['cpf_cnpj'] ?? '',
            'mobilePhone' => $tenantData['phone'] ?? '',
            'address' => $tenantData['address'] ?? '',
            'addressNumber' => $tenantData['address_number'] ?? '',
            'province' => $tenantData['province'] ?? '',
            'postalCode' => $tenantData['postal_code'] ?? '',
            'companyType' => 'ASSOCIATION',
        ]);
    }

    // ==========================================
    // Customers
    // ==========================================

    /**
     * Create or retrieve an Asaas customer for a user
     */
    public function ensureCustomer(int $tenantId, array $user): ?string
    {
        // Check if already mapped
        $existing = db_fetch_one(
            "SELECT asaas_customer_id FROM asaas_customers WHERE tenant_id = ? AND user_id = ?",
            [$tenantId, $user['id']]
        );

        if ($existing) {
            return $existing['asaas_customer_id'];
        }

        // Create in Asaas
        $result = $this->post('/customers', [
            'name' => $user['name'],
            'email' => $user['email'] ?? null,
            'cpfCnpj' => $user['cpf'] ?? null,
            'mobilePhone' => $user['phone'] ?? null,
            'externalReference' => "user_{$user['id']}_tenant_{$tenantId}",
        ]);

        if (!$result || isset($result['error'])) {
            error_log("AsaasService::ensureCustomer - Failed for user {$user['id']}: " . ($result['message'] ?? 'Unknown'));
            return null;
        }

        $customerId = $result['id'];

        // Save mapping
        db_insert('asaas_customers', [
            'tenant_id' => $tenantId,
            'user_id' => $user['id'],
            'asaas_customer_id' => $customerId,
        ]);

        return $customerId;
    }

    // ==========================================
    // Payments
    // ==========================================

    /**
     * Create a single payment (PIX, Boleto, or Credit Card)
     * 
     * @param array $options [
     *   'customer' => string (Asaas customer ID),
     *   'billingType' => 'BOLETO'|'CREDIT_CARD'|'PIX',
     *   'value' => float (amount in BRL),
     *   'dueDate' => string (YYYY-MM-DD),
     *   'description' => string,
     *   'installmentCount' => int (1-21),
     *   'installmentValue' => float (value per installment),
     *   'externalReference' => string,
     *   'split' => array (optional, for payment split),
     *   'creditCard' => array (optional, card details),
     *   'creditCardHolderInfo' => array (optional, holder info),
     * ]
     */
    public function createPayment(array $options): ?array
    {
        $payload = [
            'customer' => $options['customer'],
            'billingType' => $options['billingType'],
            'value' => $options['value'],
            'dueDate' => $options['dueDate'] ?? date('Y-m-d', strtotime('+3 days')),
            'description' => $options['description'] ?? 'Pagamento DesbravaHub',
            'externalReference' => $options['externalReference'] ?? null,
        ];

        // Installments
        if (($options['installmentCount'] ?? 1) > 1) {
            $payload['installmentCount'] = $options['installmentCount'];
            if (isset($options['installmentValue'])) {
                $payload['installmentValue'] = $options['installmentValue'];
            } else {
                $payload['totalValue'] = $options['value'];
            }
            unset($payload['value']); // Use totalValue for installments
        }

        // Credit card details (for direct charge)
        if (isset($options['creditCard'])) {
            $payload['creditCard'] = $options['creditCard'];
            $payload['creditCardHolderInfo'] = $options['creditCardHolderInfo'] ?? [];
        }

        // Payment split
        if (!empty($options['split'])) {
            $payload['split'] = $options['split'];
        }

        return $this->post('/payments', $payload);
    }

    /**
     * Get payment link (invoice URL) for a payment
     */
    public function getPaymentLink(string $paymentId): ?string
    {
        $result = $this->get("/payments/{$paymentId}/identificationField");
        return $result['invoiceUrl'] ?? null;
    }

    /**
     * Get PIX QR Code for a payment
     */
    public function getPixQrCode(string $paymentId): ?array
    {
        return $this->get("/payments/{$paymentId}/pixQrCode");
    }

    /**
     * Get payment status
     */
    public function getPayment(string $paymentId): ?array
    {
        return $this->get("/payments/{$paymentId}");
    }

    /**
     * List payments
     */
    public function listPayments(array $filters = []): ?array
    {
        return $this->get('/payments', $filters);
    }

    // ==========================================
    // Installment Calculator (Buyer Interest)
    // ==========================================

    /**
     * Calculate installment values with buyer interest
     * The club pays nothing extra; the buyer absorbs the interest.
     * 
     * @param float $baseValue Original product/service value in BRL
     * @param int $installments Number of installments (1-21)
     * @param float|null $customRate Custom monthly interest rate (%), null = use default
     * @return array ['totalValue', 'installmentValue', 'interestRate', 'interestAmount']
     */
    public function calculateInstallments(float $baseValue, int $installments, ?float $customRate = null): array
    {
        $installments = max(1, min(21, $installments));

        if ($installments <= 1) {
            return [
                'totalValue' => $baseValue,
                'installmentValue' => $baseValue,
                'interestRate' => 0,
                'interestAmount' => 0,
                'installmentCount' => 1,
            ];
        }

        $rate = $customRate ?? (self::INSTALLMENT_INTEREST_RATES[$installments] ?? 0);
        $interestAmount = round($baseValue * ($rate / 100), 2);
        $totalValue = round($baseValue + $interestAmount, 2);
        $installmentValue = round($totalValue / $installments, 2);

        // Adjust last installment for rounding
        $adjustedTotal = $installmentValue * ($installments - 1);
        $lastInstallment = round($totalValue - $adjustedTotal, 2);

        return [
            'totalValue' => $totalValue,
            'installmentValue' => $installmentValue,
            'lastInstallmentValue' => $lastInstallment,
            'interestRate' => $rate,
            'interestAmount' => $interestAmount,
            'installmentCount' => $installments,
        ];
    }

    /**
     * Get installment options for display (all available installments)
     */
    public function getInstallmentOptions(float $baseValue, int $maxInstallments = 12, ?float $customRate = null): array
    {
        $options = [];
        $maxInstallments = max(1, min(21, $maxInstallments));

        for ($i = 1; $i <= $maxInstallments; $i++) {
            $calc = $this->calculateInstallments($baseValue, $i, $customRate);
            $options[] = $calc;
        }

        return $options;
    }

    // ==========================================
    // Subscriptions (Mensalidades)
    // ==========================================

    /**
     * Create a subscription in Asaas
     */
    public function createSubscription(array $options): ?array
    {
        $payload = [
            'customer' => $options['customer'],
            'billingType' => $options['billingType'] ?? 'UNDEFINED',
            'value' => $options['value'],
            'cycle' => $options['cycle'] ?? 'MONTHLY',
            'description' => $options['description'] ?? 'Mensalidade',
            'nextDueDate' => $options['nextDueDate'] ?? date('Y-m-d', strtotime('+1 month')),
            'externalReference' => $options['externalReference'] ?? null,
        ];

        if (isset($options['maxPayments'])) {
            $payload['maxPayments'] = $options['maxPayments'];
        }

        return $this->post('/subscriptions', $payload);
    }

    /**
     * Cancel a subscription
     */
    public function cancelSubscription(string $subscriptionId): ?array
    {
        return $this->delete("/subscriptions/{$subscriptionId}");
    }

    /**
     * Get subscription details
     */
    public function getSubscription(string $subscriptionId): ?array
    {
        return $this->get("/subscriptions/{$subscriptionId}");
    }

    // ==========================================
    // Balance & Financial
    // ==========================================

    /**
     * Get account balance
     */
    public function getBalance(): ?array
    {
        return $this->get('/finance/balance');
    }

    /**
     * Get financial statistics
     */
    public function getStatistics(string $startDate = null, string $endDate = null): ?array
    {
        $params = [];
        if ($startDate) $params['startDate'] = $startDate;
        if ($endDate) $params['finishDate'] = $endDate;

        return $this->get('/finance/payment/statistics', $params);
    }

    // ==========================================
    // Webhook Processing
    // ==========================================

    /**
     * Process incoming webhook from Asaas
     * 
     * @return array ['event', 'paymentId', 'status']
     */
    public function processWebhook(string $body): array
    {
        $data = json_decode($body, true);

        if (!$data || !isset($data['event'])) {
            return ['error' => 'Invalid webhook payload'];
        }

        return [
            'event' => $data['event'],
            'paymentId' => $data['payment']['id'] ?? null,
            'subscriptionId' => $data['payment']['subscription'] ?? null,
            'status' => $data['payment']['status'] ?? null,
            'value' => $data['payment']['value'] ?? null,
            'netValue' => $data['payment']['netValue'] ?? null,
            'billingType' => $data['payment']['billingType'] ?? null,
            'externalReference' => $data['payment']['externalReference'] ?? null,
            'raw' => $data,
        ];
    }
}
