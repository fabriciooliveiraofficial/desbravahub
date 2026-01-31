<?php
/**
 * Webhook Global - Stripe
 * 
 * Endpoint centralizado para receber webhooks de todas as contas conectadas.
 * URL: https://seusite.com/webhook-stripe.php
 */

// Load application bootstrap
require_once __DIR__ . '/bootstrap/bootstrap.php';

use App\Services\StripeConnect;

// Apenas aceita POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Log para debug (remover em produção)
$logFile = __DIR__ . '/storage/logs/stripe_webhooks.log';

try {
    $stripe = StripeConnect::getInstance();

    if (!$stripe->isConfigured()) {
        http_response_code(500);
        echo json_encode(['error' => 'Stripe not configured']);
        exit;
    }

    // Lê o payload bruto
    $payload = file_get_contents('php://input');
    $signature = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

    if (empty($signature)) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing signature']);
        exit;
    }

    // Valida e constrói o evento
    $event = $stripe->constructWebhookEvent($payload, $signature);

    if (!$event) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid signature or payload']);
        exit;
    }

    // Log do evento recebido
    $logEntry = sprintf(
        "[%s] Event: %s | ID: %s\n",
        date('Y-m-d H:i:s'),
        $event->type,
        $event->id
    );
    @file_put_contents($logFile, $logEntry, FILE_APPEND);

    // Processa o evento
    $result = $stripe->handleWebhookEvent($event);

    // Log do resultado
    $resultLog = sprintf(
        "[%s] Result: %s | Handled: %s\n",
        date('Y-m-d H:i:s'),
        $result['message'],
        $result['handled'] ? 'yes' : 'no'
    );
    @file_put_contents($logFile, $resultLog, FILE_APPEND);

    // Retorna sucesso
    http_response_code(200);
    echo json_encode([
        'received' => true,
        'event_type' => $event->type,
        'handled' => $result['handled'],
    ]);

} catch (\Exception $e) {
    error_log("Stripe Webhook Fatal Error: " . $e->getMessage());

    $errorLog = sprintf(
        "[%s] ERROR: %s\n",
        date('Y-m-d H:i:s'),
        $e->getMessage()
    );
    @file_put_contents($logFile, $errorLog, FILE_APPEND);

    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
