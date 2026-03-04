<?php
/**
 * Club Public Event Controller
 * 
 * Manages the public viewing and enrollment of events for a Club (Tenant).
 */

namespace App\Controllers;

use App\Core\View;
use App\Core\App;

class ClubPublicEventController
{
    /**
     * List public events for the club
     */
    public function index(): void
    {
        $tenant = App::tenant();
        $user = App::user(); // May be null if not logged in

        // Get upcoming and ongoing events
        $events = db_fetch_all(
            "SELECT * FROM events 
             WHERE tenant_id = ? AND status IN ('upcoming', 'ongoing')
             ORDER BY start_datetime ASC",
            [$tenant['id']]
        );

        View::render('public/events/index', [
            'tenant' => $tenant,
            'user' => $user,
            'events' => $events,
            'pageTitle' => 'Eventos do Clube'
        ], 'public');
    }

    /**
     * Show event details
     */
    public function show(array $params): void
    {
        $tenant = App::tenant();
        $user = App::user(); // May be null
        $slug = $params[0]; // from regex /([A-Za-z0-9-]+)

        $event = db_fetch_one("SELECT * FROM events WHERE slug = ? AND tenant_id = ?", [$slug, $tenant['id']]);

        if (!$event) {
            header('Location: ' . base_url($tenant['slug'] . '/eventos'));
            exit;
        }

        $enrolled = false;
        if ($user) {
            $enrollment = db_fetch_one("SELECT id FROM event_enrollments WHERE event_id = ? AND user_id = ?", [$event['id'], $user['id']]);
            $enrolled = (bool) $enrollment;
        }

        // Check enrollment count
        $enrollmentCountCount = db_fetch_one("SELECT COUNT(*) as c FROM event_enrollments WHERE event_id = ?", [$event['id']]);
        $enrollmentCount = $enrollmentCountCount['c'] ?? 0;

        View::render('public/events/show', [
            'tenant' => $tenant,
            'user' => $user,
            'event' => $event,
            'enrolled' => $enrolled,
            'enrollmentCount' => $enrollmentCount,
            'pageTitle' => $event['title']
        ], 'public'); // Using public layout
    }

    /**
     * Enroll in a free event (or redirect to payment)
     */
    public function enroll(array $params): void
    {
        $tenant = App::tenant();
        $user = App::user();
        $slug = $params[0];

        if (!$user) {
            $this->jsonError('Você precisa estar logado para se inscrever.', 401);
            return;
        }

        $event = db_fetch_one("SELECT * FROM events WHERE slug = ? AND tenant_id = ?", [$slug, $tenant['id']]);

        if (!$event) {
            $this->jsonError('Evento não encontrado', 404);
            return;
        }

        if ($event['status'] !== 'upcoming' && $event['status'] !== 'ongoing') {
            $this->jsonError('Inscrições encerradas para este evento.', 400);
            return;
        }

        // Check if already enrolled
        $enrollment = db_fetch_one("SELECT id FROM event_enrollments WHERE event_id = ? AND user_id = ?", [$event['id'], $user['id']]);
        if ($enrollment) {
            $this->jsonError('Você já está inscrito neste evento.', 400);
            return;
        }

        // Check max participants
        if ($event['max_participants'] > 0) {
            $enrollmentCount = db_fetch_one("SELECT COUNT(*) as c FROM event_enrollments WHERE event_id = ?", [$event['id']]);
            if ($enrollmentCount['c'] >= $event['max_participants']) {
                $this->jsonError('As vagas para este evento esgotaram.', 400);
                return;
            }
        }

        // Check deadline
        if (!empty($event['registration_deadline']) && strtotime($event['registration_deadline']) < time()) {
            $this->jsonError('O prazo de inscrição para este evento já passou.', 400);
            return;
        }

        // If paid, must go through payment gateway
        if ($event['is_paid']) {
            $this->jsonError('Este evento é pago e requer processamento financeiro.', 400);
            return;
        }

        try {
            db_insert('event_enrollments', [
                'event_id' => $event['id'],
                'user_id' => $user['id'],
                'tenant_id' => $tenant['id'],
                'status' => 'enrolled'
            ]);

            $this->json([
                'success' => true,
                'message' => 'Inscrição realizada com sucesso!',
                'redirect' => base_url($tenant['slug'] . '/eventos/' . $event['slug'])
            ]);
        } catch (\Exception $e) {
            $this->jsonError('Erro ao realizar inscrição: ' . $e->getMessage());
        }
    }

    /**
     * Generate Asaas Checkout for a paid event
     */
    public function checkoutAsaas(array $params): void
    {
        $tenant = App::tenant();
        $user = App::user();
        $slug = $params[0];

        if (!$user) {
            $this->jsonError('Você precisa estar logado.', 401);
            return;
        }

        $event = db_fetch_one("SELECT * FROM events WHERE slug = ? AND tenant_id = ?", [$slug, $tenant['id']]);

        if (!$event || !$event['is_paid'] || !$event['price']) {
            $this->jsonError('Evento inválido ou não cobrado.', 400);
            return;
        }

        try {
            $asaas = \App\Services\AsaasService::fromTenant($tenant['id']);
            if (!$asaas) {
                $this->jsonError('Gateway de pagamento não configurado pelo clube.', 400);
                return;
            }

            $customerId = $asaas->ensureCustomer($tenant['id'], $user);
            if (!$customerId) {
                $this->jsonError('Erro ao registrar cliente no gateway de pagamento.', 500);
                return;
            }

            $payment = $asaas->createPayment([
                'customer' => $customerId,
                'billingType' => 'UNDEFINED', // Let Asaas checkout decide
                'value' => (float) $event['price'],
                'dueDate' => date('Y-m-d', strtotime('+3 days')),
                'description' => 'Inscrição: ' . $event['title'],
                'externalReference' => 'event_' . $event['id'] . '_user_' . $user['id']
            ]);

            if (isset($payment['error'])) {
                $this->jsonError('Erro Asaas: ' . $payment['message']);
                return;
            }

            // Retrieve the invoice URL (Checkout Link)
            $invoiceUrl = $asaas->getPaymentLink($payment['id']) ?? $payment['invoiceUrl'] ?? null;

            // Optional: Save pending enrollment directly marked as pending_payment
            $existing = db_fetch_one("SELECT id FROM event_enrollments WHERE event_id = ? AND user_id = ?", [$event['id'], $user['id']]);
            if (!$existing) {
                db_insert('event_enrollments', [
                    'event_id' => $event['id'],
                    'user_id' => $user['id'],
                    'tenant_id' => $tenant['id'],
                    'status' => 'enrolled' // Assuming 'enrolled' for now, should ideally be 'pending_payment' if schema allowed
                ]);
            }

            $this->json([
                'success' => true,
                'checkoutUrl' => $invoiceUrl
            ]);

        } catch (\Exception $e) {
            $this->jsonError('Erro inesperado: ' . $e->getMessage());
        }
    }

    private function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    private function jsonError(string $message, int $code = 400): void
    {
        http_response_code($code);
        $this->json(['error' => $message]);
    }
}
