<?php
/**
 * Stripe Controller
 * 
 * Gerencia integração Stripe Connect com SDK oficial.
 */

namespace App\Controllers;

use App\Core\App;
use App\Services\StripeConnect;

class StripeController
{
    /**
     * Página de configurações do Stripe
     */
    public function settings(): void
    {
        $user = App::user();
        $tenant = App::tenant();

        // Apenas diretores podem acessar
        if (!in_array($user['role_name'] ?? '', ['admin', 'director'])) {
            error_log("StripeController::settings - Access Denied: User " . ($user['id'] ?? 'unknown') . " with role " . ($user['role_name'] ?? 'none') . " tried to access Stripe settings.");
            http_response_code(403);
            echo "Acesso negado";
            exit;
        }

        $stripe = StripeConnect::getInstance();
        $isConfigured = $stripe->isConfigured();

        // Verifica se a migration foi executada
        $migrationNeeded = false;
        $settings = null;

        try {
            $settings = db_fetch_one(
                "SELECT * FROM tenant_stripe_settings WHERE tenant_id = ?",
                [$tenant['id']]
            );
        } catch (\Exception $e) {
            $migrationNeeded = true;
        }

        // Se conectado, atualiza status da conta no Stripe
        // OTIMIZAÇÃO: Removida chamada síncrona que causava lentidão (2s). 
        // A atualização agora depende apenas de Webhooks ou ações manuais explícitas.
        /*
        if ($settings && $settings['stripe_account_id'] && $isConfigured) {
            $account = $stripe->getAccount($settings['stripe_account_id']);
             ...
        }
        */

        // Estatísticas de pagamentos (OTIMIZAÇÃO: Removido temporariamente para teste de performance)
        $stats = [
            'total_revenue' => 0,
            'this_month' => 0,
            'pending' => 0,
            'transactions' => 0,
        ];

        try {
            $stats['total_revenue'] = db_fetch_one(
                "SELECT COALESCE(SUM(amount_cents), 0) as total FROM payments WHERE tenant_id = ? AND status = 'completed'",
                [$tenant['id']]
            )['total'] ?? 0;

            $stats['this_month'] = db_fetch_one(
                "SELECT COALESCE(SUM(amount_cents), 0) as total FROM payments WHERE tenant_id = ? AND status = 'completed' AND MONTH(paid_at) = MONTH(NOW()) AND YEAR(paid_at) = YEAR(NOW())",
                [$tenant['id']]
            )['total'] ?? 0;

            $stats['pending'] = db_fetch_one(
                "SELECT COALESCE(SUM(amount_cents), 0) as total FROM payments WHERE tenant_id = ? AND status = 'pending'",
                [$tenant['id']]
            )['total'] ?? 0;

            $stats['transactions'] = db_fetch_one(
                "SELECT COUNT(*) as total FROM payments WHERE tenant_id = ?",
                [$tenant['id']]
            )['total'] ?? 0;
        } catch (\Exception $e) {
            // Tables may not exist
        }

        \App\Core\View::render('admin/stripe/settings', [
            'tenant' => $tenant,
            'user' => $user,
            'stripe' => $stripe,
            'settings' => $settings,
            'isConfigured' => $isConfigured,
            'stats' => $stats,
            'pageTitle' => 'Financeiro',
            'pageIcon' => 'payments'
        ]);
    }

    /**
     * Inicia conexão com Stripe Connect
     */
    public function connect(): void
    {
        $user = App::user();
        $tenant = App::tenant();

        if (!in_array($user['role_name'] ?? '', ['admin', 'director'])) {
            http_response_code(403);
            exit;
        }

        $stripe = StripeConnect::getInstance();

        if (!$stripe->isConfigured()) {
            $_SESSION['flash_error'] = 'Stripe não está configurado. Adicione as chaves API no .env';
            header('Location: ' . base_url($tenant['slug'] . '/admin/pagamentos'));
            return;
        }

        // Verifica se já tem conta
        $settings = db_fetch_one(
            "SELECT * FROM tenant_stripe_settings WHERE tenant_id = ?",
            [$tenant['id']]
        );

        $accountId = null;

        if ($settings && $settings['stripe_account_id']) {
            // Já tem conta, só gera novo link
            $accountId = $settings['stripe_account_id'];
        } else {
            // Cria nova conta Express
            $account = $stripe->createConnectedAccount($tenant);

            if (!$account || !isset($account['id'])) {
                $_SESSION['flash_error'] = 'Erro ao criar conta Stripe. Verifique as configurações.';
                header('Location: ' . base_url($tenant['slug'] . '/admin/pagamentos'));
                return;
            }

            $accountId = $account['id'];

            // Salva no banco
            if ($settings) {
                db_update('tenant_stripe_settings', [
                    'stripe_account_id' => $accountId,
                ], 'id = ?', [$settings['id']]);
            } else {
                db_insert('tenant_stripe_settings', [
                    'tenant_id' => $tenant['id'],
                    'stripe_account_id' => $accountId,
                ]);
            }
        }

        // Cria Account Link para onboarding
        $accountLink = $stripe->createAccountLink($accountId, $tenant['slug']);

        if (!$accountLink || !isset($accountLink['url'])) {
            $_SESSION['flash_error'] = 'Erro ao gerar link de configuração. Tente novamente.';
            header('Location: ' . base_url($tenant['slug'] . '/admin/pagamentos'));
            return;
        }

        // Redireciona ao Stripe
        header('Location: ' . $accountLink['url']);
    }

    /**
     * Callback do Stripe após onboarding
     */
    public function callback(): void
    {
        $user = App::user();
        $tenant = App::tenant();

        if (!$user) {
            header('Location: ' . base_url($tenant['slug'] . '/login'));
            return;
        }

        $stripe = StripeConnect::getInstance();

        $settings = db_fetch_one(
            "SELECT * FROM tenant_stripe_settings WHERE tenant_id = ?",
            [$tenant['id']]
        );

        if ($settings && $settings['stripe_account_id']) {
            $account = $stripe->getAccount($settings['stripe_account_id']);

            if ($account) {
                $isConnected = $account['details_submitted'] && $account['charges_enabled'];

                db_update('tenant_stripe_settings', [
                    'is_connected' => $isConnected ? 1 : 0,
                    'details_submitted' => $account['details_submitted'] ? 1 : 0,
                    'charges_enabled' => $account['charges_enabled'] ? 1 : 0,
                    'payouts_enabled' => $account['payouts_enabled'] ? 1 : 0,
                    'connected_at' => $isConnected ? date('Y-m-d H:i:s') : null,
                ], 'id = ?', [$settings['id']]);

                if ($isConnected) {
                    $_SESSION['flash_success'] = '🎉 Conta Stripe conectada! Você já pode receber pagamentos.';
                } else {
                    $_SESSION['flash_warning'] = 'Complete a configuração no Stripe para receber pagamentos.';
                }
            }
        }

        header('Location: ' . base_url($tenant['slug'] . '/admin/pagamentos'));
    }

    /**
     * Desconecta conta Stripe
     */
    public function disconnect(): void
    {
        $user = App::user();
        $tenant = App::tenant();

        if (!in_array($user['role_name'] ?? '', ['admin', 'director'])) {
            http_response_code(403);
            exit;
        }

        $settings = db_fetch_one(
            "SELECT * FROM tenant_stripe_settings WHERE tenant_id = ?",
            [$tenant['id']]
        );

        if ($settings) {
            db_update('tenant_stripe_settings', [
                'stripe_account_id' => null,
                'is_connected' => 0,
                'details_submitted' => 0,
                'charges_enabled' => 0,
                'payouts_enabled' => 0,
                'connected_at' => null,
            ], 'id = ?', [$settings['id']]);
        }

        $_SESSION['flash_success'] = 'Conta Stripe desconectada.';
        header('Location: ' . base_url($tenant['slug'] . '/admin/pagamentos'));
    }

    /**
     * Abre o dashboard da conta conectada no Stripe
     */
    public function dashboard(): void
    {
        $user = App::user();
        $tenant = App::tenant();

        if (!in_array($user['role_name'] ?? '', ['admin', 'director'])) {
            http_response_code(403);
            exit;
        }

        $stripe = StripeConnect::getInstance();

        $settings = db_fetch_one(
            "SELECT * FROM tenant_stripe_settings WHERE tenant_id = ?",
            [$tenant['id']]
        );

        if ($settings && $settings['stripe_account_id']) {
            $loginUrl = $stripe->createLoginLink($settings['stripe_account_id']);

            if ($loginUrl) {
                header('Location: ' . $loginUrl);
                return;
            }
        }

        $_SESSION['flash_error'] = 'Não foi possível acessar o dashboard.';
        header('Location: ' . base_url($tenant['slug'] . '/admin/pagamentos'));
    }

    /**
     * Lista histórico de pagamentos
     */
    public function history(): void
    {
        $user = App::user();
        $tenant = App::tenant();

        if (!in_array($user['role_name'] ?? '', ['admin', 'director', 'associate_director'])) {
            http_response_code(403);
            exit;
        }

        $payments = [];
        try {
            $payments = db_fetch_all(
                "SELECT p.*, u.name as payer_user_name 
                 FROM payments p 
                 LEFT JOIN users u ON p.user_id = u.id 
                 WHERE p.tenant_id = ? 
                 ORDER BY p.created_at DESC 
                 LIMIT 100",
                [$tenant['id']]
            );
        } catch (\Exception $e) {
            // Table may not exist
        }

        \App\Core\View::render('admin/stripe/history', [
            'tenant' => $tenant,
            'user' => $user,
            'payments' => $payments,
            'pageTitle' => 'Histórico de Pagamentos',
            'pageIcon' => 'receipt_long'
        ]);
    }
}
