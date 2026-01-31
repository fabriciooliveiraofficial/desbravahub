<?php
/**
 * Stripe Connect Service
 * 
 * Integração completa com Stripe Connect para hospedagem compartilhada.
 * Usa o SDK oficial do Stripe para máxima compatibilidade.
 */

namespace App\Services;

class StripeConnect
{
    private static ?StripeConnect $instance = null;
    private string $secretKey;
    private string $publishableKey;
    private string $webhookSecret;
    private bool $isConfigured = false;

    private function __construct()
    {
        // LAZY LOAD: We don't load the SDK here anymore to prevent I/O blocking on every page load
        $this->secretKey = env('STRIPE_SECRET_KEY', '');
        $this->publishableKey = env('STRIPE_PUBLISHABLE_KEY', '');
        $this->webhookSecret = env('STRIPE_WEBHOOK_SECRET', '');

        // Basic config check (without loading class yet)
        if (!empty($this->secretKey)) {
            $this->isConfigured = true;
        }
    }

    private function initStripe(): void
    {
        if (class_exists('\Stripe\Stripe')) {
            return;
        }

        $stripeAutoload = BASE_PATH . '/vendor/stripe/stripe-php/init.php';
        if (file_exists($stripeAutoload)) {
            require_once $stripeAutoload;
        }

        if (class_exists('\Stripe\Stripe') && !empty($this->secretKey)) {
            \Stripe\Stripe::setApiKey($this->secretKey);
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Verifica se o Stripe está configurado
     */
    public function isConfigured(): bool
    {
        return $this->isConfigured;
    }

    public function getPublishableKey(): string
    {
        return $this->publishableKey;
    }

    /**
     * Constrói a URL base do tenant dinamicamente
     */
    public static function getTenantBaseUrl(string $tenantSlug): string
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        return "{$protocol}://{$host}/{$tenantSlug}";
    }

    // =========================================================================
    // STRIPE CONNECT - CRIAÇÃO E ONBOARDING DE CONTAS
    // =========================================================================

    /**
     * Cria uma conta Express conectada ao Stripe
     */
    public function createConnectedAccount(array $tenant): ?array
    {
        $this->initStripe();
        try {
            $account = \Stripe\Account::create([
                'type' => 'express',
                'country' => 'BR',
                'email' => $tenant['email'] ?? null,
                'business_type' => 'non_profit',
                'capabilities' => [
                    'card_payments' => ['requested' => true],
                    'transfers' => ['requested' => true],
                ],
                'business_profile' => [
                    'name' => $tenant['name'],
                    'product_description' => 'Clube de Desbravadores - Gestão de eventos e mensalidades',
                    'mcc' => '8641', // Civic/Social Organizations
                ],
                'metadata' => [
                    'tenant_id' => $tenant['id'],
                    'tenant_slug' => $tenant['slug'],
                ],
            ]);

            return $account->toArray();
        } catch (\Stripe\Exception\ApiErrorException $e) {
            error_log("Stripe Create Account Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Cria um Account Link para onboarding
     * URL é construída dinamicamente baseada no tenant atual
     */
    public function createAccountLink(string $accountId, string $tenantSlug): ?array
    {
        $this->initStripe();
        try {
            $baseUrl = self::getTenantBaseUrl($tenantSlug);

            $accountLink = \Stripe\AccountLink::create([
                'account' => $accountId,
                'refresh_url' => "{$baseUrl}/admin/pagamentos/conectar",
                'return_url' => "{$baseUrl}/admin/pagamentos/callback",
                'type' => 'account_onboarding',
            ]);

            return $accountLink->toArray();
        } catch (\Stripe\Exception\ApiErrorException $e) {
            error_log("Stripe Account Link Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Recupera detalhes de uma conta conectada
     */
    public function getAccount(string $accountId): ?array
    {
        $this->initStripe();
        try {
            $account = \Stripe\Account::retrieve($accountId);
            return $account->toArray();
        } catch (\Stripe\Exception\ApiErrorException $e) {
            error_log("Stripe Get Account Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Cria um login link para o dashboard da conta conectada
     */
    public function createLoginLink(string $accountId): ?string
    {
        $this->initStripe();
        try {
            $loginLink = \Stripe\Account::createLoginLink($accountId);
            return $loginLink->url;
        } catch (\Stripe\Exception\ApiErrorException $e) {
            error_log("Stripe Login Link Error: " . $e->getMessage());
            return null;
        }
    }

    // =========================================================================
    // CHECKOUT E PAGAMENTOS
    // =========================================================================

    /**
     * Cria uma Checkout Session para pagamento único
     * Com split de pagamento (application_fee)
     */
    public function createCheckoutSession(array $params): ?array
    {
        $this->initStripe();
        try {
            $connectedAccountId = $params['connected_account_id'];
            $tenantSlug = $params['tenant_slug'];
            $baseUrl = self::getTenantBaseUrl($tenantSlug);

            // Monta os line items
            $lineItems = [];
            $totalAmount = 0;

            foreach ($params['items'] as $item) {
                $itemTotal = $item['amount_cents'] * ($item['quantity'] ?? 1);
                $totalAmount += $itemTotal;

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

            // Calcula a taxa da plataforma (se houver)
            $platformFeePercent = $params['platform_fee_percent'] ?? 0;
            $applicationFee = 0;
            if ($platformFeePercent > 0) {
                $applicationFee = (int) round($totalAmount * ($platformFeePercent / 100));
            }

            $sessionParams = [
                'mode' => 'payment',
                'line_items' => $lineItems,
                'success_url' => $params['success_url'] ?? "{$baseUrl}/pagamento/sucesso?session_id={CHECKOUT_SESSION_ID}",
                'cancel_url' => $params['cancel_url'] ?? "{$baseUrl}/pagamento/cancelado",
                'customer_email' => $params['customer_email'] ?? null,
                'metadata' => array_merge($params['metadata'] ?? [], [
                    'tenant_id' => $params['tenant_id'] ?? null,
                    'type' => $params['type'] ?? 'other',
                    'reference_id' => $params['reference_id'] ?? null,
                ]),
                'payment_intent_data' => [
                    'metadata' => $params['metadata'] ?? [],
                ],
            ];

            // Adiciona taxa da plataforma se configurada
            if ($applicationFee > 0) {
                $sessionParams['payment_intent_data']['application_fee_amount'] = $applicationFee;
            }

            // Cria a sessão na conta conectada
            $session = \Stripe\Checkout\Session::create($sessionParams, [
                'stripe_account' => $connectedAccountId,
            ]);

            return $session->toArray();

        } catch (\Stripe\Exception\ApiErrorException $e) {
            error_log("Stripe Checkout Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Recupera uma Checkout Session
     */
    public function getCheckoutSession(string $sessionId, string $connectedAccountId): ?array
    {
        $this->initStripe();
        try {
            $session = \Stripe\Checkout\Session::retrieve($sessionId, [
                'stripe_account' => $connectedAccountId,
            ]);
            return $session->toArray();
        } catch (\Stripe\Exception\ApiErrorException $e) {
            error_log("Stripe Get Session Error: " . $e->getMessage());
            return null;
        }
    }

    // =========================================================================
    // WEBHOOKS
    // =========================================================================

    /**
     * Valida a assinatura do webhook
     */
    public function constructWebhookEvent(string $payload, string $signature): ?\Stripe\Event
    {
        $this->initStripe();
        try {
            return \Stripe\Webhook::constructEvent(
                $payload,
                $signature,
                $this->webhookSecret
            );
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            error_log("Webhook Signature Error: " . $e->getMessage());
            return null;
        } catch (\UnexpectedValueException $e) {
            error_log("Webhook Payload Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Processa eventos de webhook recebidos
     */
    public function handleWebhookEvent(\Stripe\Event $event): array
    {
        $result = [
            'handled' => false,
            'message' => 'Event type not handled',
            'data' => null,
        ];

        switch ($event->type) {
            case 'checkout.session.completed':
                $result = $this->handleCheckoutCompleted($event->data->object);
                break;

            case 'payment_intent.succeeded':
                $result = $this->handlePaymentSucceeded($event->data->object);
                break;

            case 'payment_intent.payment_failed':
                $result = $this->handlePaymentFailed($event->data->object);
                break;

            case 'account.updated':
                $result = $this->handleAccountUpdated($event->data->object);
                break;
        }

        return $result;
    }

    private function handleCheckoutCompleted(\Stripe\Checkout\Session $session): array
    {
        $checkoutId = $session->id;
        $metadata = $session->metadata->toArray();
        $tenantId = $metadata['tenant_id'] ?? null;

        if ($tenantId && $session->payment_status === 'paid') {
            try {
                db_update('payments', [
                    'status' => 'completed',
                    'paid_at' => date('Y-m-d H:i:s'),
                    'stripe_payment_id' => $session->payment_intent,
                ], 'stripe_checkout_id = ? AND tenant_id = ?', [$checkoutId, $tenantId]);

                return [
                    'handled' => true,
                    'message' => "Payment completed for checkout {$checkoutId}",
                    'data' => ['tenant_id' => $tenantId],
                ];
            } catch (\Exception $e) {
                error_log("Webhook DB Error: " . $e->getMessage());
            }
        }

        return ['handled' => true, 'message' => 'Checkout processed', 'data' => null];
    }

    private function handlePaymentSucceeded(\Stripe\PaymentIntent $paymentIntent): array
    {
        $paymentId = $paymentIntent->id;
        $metadata = $paymentIntent->metadata->toArray();
        $tenantId = $metadata['tenant_id'] ?? null;

        if ($tenantId) {
            try {
                db_update('payments', [
                    'status' => 'completed',
                    'paid_at' => date('Y-m-d H:i:s'),
                ], 'stripe_payment_id = ? AND tenant_id = ?', [$paymentId, $tenantId]);
            } catch (\Exception $e) {
                error_log("Webhook Payment Update Error: " . $e->getMessage());
            }
        }

        return ['handled' => true, 'message' => 'Payment succeeded', 'data' => null];
    }

    private function handlePaymentFailed(\Stripe\PaymentIntent $paymentIntent): array
    {
        $paymentId = $paymentIntent->id;
        $metadata = $paymentIntent->metadata->toArray();
        $tenantId = $metadata['tenant_id'] ?? null;

        if ($tenantId) {
            try {
                db_update('payments', [
                    'status' => 'failed',
                ], 'stripe_payment_id = ? AND tenant_id = ?', [$paymentId, $tenantId]);
            } catch (\Exception $e) {
                error_log("Webhook Payment Failed Update Error: " . $e->getMessage());
            }
        }

        return ['handled' => true, 'message' => 'Payment failed recorded', 'data' => null];
    }

    private function handleAccountUpdated(\Stripe\Account $account): array
    {
        $accountId = $account->id;

        try {
            $settings = db_fetch_one(
                "SELECT * FROM tenant_stripe_settings WHERE stripe_account_id = ?",
                [$accountId]
            );

            if ($settings) {
                $isConnected = $account->details_submitted && $account->charges_enabled;

                db_update('tenant_stripe_settings', [
                    'is_connected' => $isConnected ? 1 : 0,
                    'details_submitted' => $account->details_submitted ? 1 : 0,
                    'charges_enabled' => $account->charges_enabled ? 1 : 0,
                    'payouts_enabled' => $account->payouts_enabled ? 1 : 0,
                    'connected_at' => $isConnected ? date('Y-m-d H:i:s') : null,
                ], 'id = ?', [$settings['id']]);

                return [
                    'handled' => true,
                    'message' => "Account {$accountId} updated",
                    'data' => ['tenant_id' => $settings['tenant_id']],
                ];
            }
        } catch (\Exception $e) {
            error_log("Webhook Account Update Error: " . $e->getMessage());
        }

        return ['handled' => true, 'message' => 'Account update processed', 'data' => null];
    }

    // =========================================================================
    // UTILIDADES
    // =========================================================================

    /**
     * Formata valor em centavos para exibição
     */
    public static function formatAmount(int $cents, string $currency = 'BRL'): string
    {
        $amount = $cents / 100;

        if (strtoupper($currency) === 'BRL') {
            return 'R$ ' . number_format($amount, 2, ',', '.');
        }

        return number_format($amount, 2) . ' ' . strtoupper($currency);
    }

    /**
     * Converte valor string para centavos
     */
    public static function parseToCents(string|float $amount): int
    {
        if (is_string($amount)) {
            // Remove R$, espaços
            $amount = preg_replace('/[R$\s]/', '', $amount);
            // Substitui vírgula por ponto
            $amount = str_replace(',', '.', $amount);
        }

        return (int) round((float) $amount * 100);
    }
}
