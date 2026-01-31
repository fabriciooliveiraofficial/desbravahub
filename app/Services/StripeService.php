<?php
/**
 * Stripe Service
 * 
 * Handles all Stripe API interactions including Connect onboarding,
 * checkout sessions, and webhook processing.
 */

namespace App\Services;

class StripeService
{
    private static ?StripeService $instance = null;
    private string $secretKey;
    private string $publishableKey;
    private string $webhookSecret;
    private string $apiBase = 'https://api.stripe.com/v1';

    private function __construct()
    {
        $this->secretKey = env('STRIPE_SECRET_KEY', '');
        $this->publishableKey = env('STRIPE_PUBLISHABLE_KEY', '');
        $this->webhookSecret = env('STRIPE_WEBHOOK_SECRET', '');
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Check if Stripe is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->secretKey);
    }

    public function getPublishableKey(): string
    {
        return $this->publishableKey;
    }

    /**
     * Create a Stripe Connect Express account
     */
    public function createConnectedAccount(array $tenant): ?array
    {
        $response = $this->request('POST', '/accounts', [
            'type' => 'express',
            'country' => 'BR',
            'email' => $tenant['email'] ?? null,
            'business_type' => 'non_profit',
            'capabilities' => [
                'card_payments' => ['requested' => 'true'],
                'transfers' => ['requested' => 'true'],
            ],
            'business_profile' => [
                'name' => $tenant['name'],
                'product_description' => 'Clube de Desbravadores - Gestão de eventos e mensalidades',
            ],
            'metadata' => [
                'tenant_id' => $tenant['id'],
                'tenant_slug' => $tenant['slug'],
            ],
        ]);

        return $response;
    }

    /**
     * Create an account link for onboarding
     */
    public function createAccountLink(string $accountId, string $returnUrl, string $refreshUrl): ?array
    {
        return $this->request('POST', '/account_links', [
            'account' => $accountId,
            'refresh_url' => $refreshUrl,
            'return_url' => $returnUrl,
            'type' => 'account_onboarding',
        ]);
    }

    /**
     * Retrieve account details
     */
    public function getAccount(string $accountId): ?array
    {
        return $this->request('GET', '/accounts/' . $accountId);
    }

    /**
     * Create a Checkout Session for one-time payment
     */
    public function createCheckoutSession(array $params): ?array
    {
        $connectedAccountId = $params['connected_account_id'];
        $tenantSettings = $params['tenant_settings'] ?? [];

        $lineItems = [];
        foreach ($params['items'] as $item) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => $params['currency'] ?? 'brl',
                    'unit_amount' => $item['amount_cents'],
                    'product_data' => [
                        'name' => $item['name'],
                        'description' => $item['description'] ?? null,
                    ],
                ],
                'quantity' => $item['quantity'] ?? 1,
            ];
        }

        $sessionParams = [
            'mode' => 'payment',
            'line_items' => $lineItems,
            'success_url' => $params['success_url'],
            'cancel_url' => $params['cancel_url'],
            'customer_email' => $params['customer_email'] ?? null,
            'metadata' => $params['metadata'] ?? [],
            'payment_intent_data' => [
                'metadata' => $params['metadata'] ?? [],
            ],
        ];

        // Calculate platform fee if configured
        $platformFeePercent = $tenantSettings['platform_fee_percent'] ?? 0;
        if ($platformFeePercent > 0) {
            $totalAmount = array_sum(array_map(fn($i) => $i['amount_cents'] * ($i['quantity'] ?? 1), $params['items']));
            $platformFee = (int) round($totalAmount * ($platformFeePercent / 100));

            if ($platformFee > 0) {
                $sessionParams['payment_intent_data']['application_fee_amount'] = $platformFee;
            }
        }

        return $this->request('POST', '/checkout/sessions', $sessionParams, [
            'Stripe-Account' => $connectedAccountId,
        ]);
    }

    /**
     * Retrieve a Checkout Session
     */
    public function getCheckoutSession(string $sessionId, string $connectedAccountId): ?array
    {
        return $this->request('GET', '/checkout/sessions/' . $sessionId, [], [
            'Stripe-Account' => $connectedAccountId,
        ]);
    }

    /**
     * Delete/disconnect a connected account
     */
    public function deleteAccount(string $accountId): bool
    {
        $response = $this->request('DELETE', '/accounts/' . $accountId);
        return $response !== null && isset($response['deleted']) && $response['deleted'];
    }

    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        if (empty($this->webhookSecret)) {
            return false;
        }

        $signatureParts = [];
        foreach (explode(',', $signature) as $part) {
            list($key, $value) = explode('=', $part, 2);
            $signatureParts[$key] = $value;
        }

        $timestamp = $signatureParts['t'] ?? '';
        $v1Signature = $signatureParts['v1'] ?? '';

        // Check timestamp tolerance (5 minutes)
        if (abs(time() - (int) $timestamp) > 300) {
            return false;
        }

        $signedPayload = $timestamp . '.' . $payload;
        $expectedSignature = hash_hmac('sha256', $signedPayload, $this->webhookSecret);

        return hash_equals($expectedSignature, $v1Signature);
    }

    /**
     * Make an API request to Stripe
     */
    private function request(string $method, string $endpoint, array $data = [], array $headers = []): ?array
    {
        $url = $this->apiBase . $endpoint;

        $ch = curl_init();

        $curlHeaders = [
            'Authorization: Bearer ' . $this->secretKey,
            'Content-Type: application/x-www-form-urlencoded',
        ];

        foreach ($headers as $key => $value) {
            $curlHeaders[] = "{$key}: {$value}";
        }

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $curlHeaders,
            CURLOPT_TIMEOUT => 30,
        ]);

        switch ($method) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $this->buildQueryString($data));
                break;
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
            case 'GET':
            default:
                if (!empty($data)) {
                    $url .= '?' . http_build_query($data);
                    curl_setopt($ch, CURLOPT_URL, $url);
                }
                break;
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            error_log("Stripe API Error: {$error}");
            return null;
        }

        $decoded = json_decode($response, true);

        if ($httpCode >= 400) {
            error_log("Stripe API Error ({$httpCode}): " . ($decoded['error']['message'] ?? $response));
            return null;
        }

        return $decoded;
    }

    /**
     * Build query string with nested arrays (Stripe format)
     */
    private function buildQueryString(array $data, string $prefix = ''): string
    {
        $result = [];

        foreach ($data as $key => $value) {
            $fullKey = $prefix ? "{$prefix}[{$key}]" : $key;

            if (is_array($value)) {
                $result[] = $this->buildQueryString($value, $fullKey);
            } else {
                $result[] = urlencode($fullKey) . '=' . urlencode((string) $value);
            }
        }

        return implode('&', array_filter($result));
    }

    /**
     * Format amount for display (cents to currency)
     */
    public static function formatAmount(int $cents, string $currency = 'BRL'): string
    {
        $amount = $cents / 100;

        if ($currency === 'BRL') {
            return 'R$ ' . number_format($amount, 2, ',', '.');
        }

        return number_format($amount, 2) . ' ' . strtoupper($currency);
    }

    /**
     * Parse amount from user input to cents
     */
    public static function parseToCents(string $amount): int
    {
        // Remove currency symbols and spaces
        $amount = preg_replace('/[R$\s]/', '', $amount);
        // Convert comma to dot for decimal
        $amount = str_replace(',', '.', $amount);
        // Remove thousand separators
        $amount = str_replace('.', '', substr($amount, 0, -3)) . substr($amount, -3);

        return (int) round((float) $amount * 100);
    }
}
