<?php
/**
 * Developer Support Controller
 * 
 * Global panel for platform developers to manage support tickets.
 */

namespace App\Controllers;

class DevSupportController
{
    /**
     * Check developer authentication
     */
    private function requireDev(): ?array
    {
        session_start();

        if (empty($_SESSION['dev_id'])) {
            header('Location: ' . base_url('dev/login'));
            exit;
        }

        return db_fetch_one("SELECT * FROM developers WHERE id = ?", [$_SESSION['dev_id']]);
    }

    /**
     * Developer login form
     */
    public function showLogin(): void
    {
        require BASE_PATH . '/views/dev/login.php';
    }

    /**
     * Handle developer login
     */
    public function login(): void
    {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $dev = db_fetch_one("SELECT * FROM developers WHERE email = ? AND status = 'active'", [$email]);

        if (!$dev || !password_verify($password, $dev['password_hash'])) {
            $this->json(['error' => 'Credenciais inválidas'], 401);
            return;
        }

        session_start();
        $_SESSION['dev_id'] = $dev['id'];
        $_SESSION['dev_name'] = $dev['name'];

        // Update last login
        db_update('developers', ['last_login_at' => date('Y-m-d H:i:s')], 'id = ?', [$dev['id']]);

        $this->json(['success' => true, 'redirect' => base_url('dev/suporte')]);
    }

    /**
     * Developer logout
     */
    public function logout(): void
    {
        session_start();
        session_destroy();
        header('Location: ' . base_url('dev/login'));
        exit;
    }

    /**
     * Support dashboard - list all tickets
     */
    public function dashboard(): void
    {
        $dev = $this->requireDev();

        $status = $_GET['status'] ?? '';
        $category = $_GET['category'] ?? '';
        $priority = $_GET['priority'] ?? '';

        $sql = "SELECT t.*, tn.name as tenant_name, u.name as user_name
                FROM support_tickets t
                LEFT JOIN tenants tn ON t.tenant_id = tn.id
                LEFT JOIN users u ON t.user_id = u.id
                WHERE 1=1";
        $params = [];

        if ($status) {
            $sql .= " AND t.status = ?";
            $params[] = $status;
        }
        if ($category) {
            $sql .= " AND t.category = ?";
            $params[] = $category;
        }
        if ($priority) {
            $sql .= " AND t.priority = ?";
            $params[] = $priority;
        }

        $sql .= " ORDER BY 
            CASE t.priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END,
            t.updated_at DESC";

        $tickets = db_fetch_all($sql, $params);

        // Stats
        $stats = [
            'open' => db_fetch_column("SELECT COUNT(*) FROM support_tickets WHERE status = 'open'"),
            'in_progress' => db_fetch_column("SELECT COUNT(*) FROM support_tickets WHERE status = 'in_progress'"),
            'waiting' => db_fetch_column("SELECT COUNT(*) FROM support_tickets WHERE status = 'waiting'"),
            'resolved' => db_fetch_column("SELECT COUNT(*) FROM support_tickets WHERE status = 'resolved'"),
        ];

        require BASE_PATH . '/views/dev/support/dashboard.php';
    }

    /**
     * View single ticket
     */
    public function show(array $params): void
    {
        $dev = $this->requireDev();
        $ticketId = (int) $params['id'];

        $ticket = db_fetch_one(
            "SELECT t.*, tn.name as tenant_name, tn.slug as tenant_slug, u.name as user_name, u.email as user_email
             FROM support_tickets t
             LEFT JOIN tenants tn ON t.tenant_id = tn.id
             LEFT JOIN users u ON t.user_id = u.id
             WHERE t.id = ?",
            [$ticketId]
        );

        if (!$ticket) {
            http_response_code(404);
            echo "Ticket não encontrado";
            return;
        }

        $messages = db_fetch_all(
            "SELECT * FROM support_messages WHERE ticket_id = ? ORDER BY created_at ASC",
            [$ticketId]
        );

        $attachments = db_fetch_all(
            "SELECT * FROM support_attachments WHERE ticket_id = ?",
            [$ticketId]
        );

        require BASE_PATH . '/views/dev/support/ticket.php';
    }

    /**
     * Reply to ticket
     */
    public function reply(array $params): void
    {
        $dev = $this->requireDev();
        $ticketId = (int) $params['id'];

        $message = trim($_POST['message'] ?? '');
        $isInternal = isset($_POST['is_internal']) ? 1 : 0;
        $newStatus = $_POST['status'] ?? null;

        if (empty($message)) {
            $this->json(['error' => 'Mensagem é obrigatória'], 400);
            return;
        }

        db_insert('support_messages', [
            'ticket_id' => $ticketId,
            'sender_type' => 'developer',
            'sender_id' => $dev['id'],
            'sender_name' => $dev['name'],
            'message' => $message,
            'is_internal' => $isInternal,
        ]);

        // Update ticket status
        $updates = ['updated_at' => date('Y-m-d H:i:s')];
        if ($newStatus) {
            $updates['status'] = $newStatus;
            if ($newStatus === 'resolved') {
                $updates['resolved_at'] = date('Y-m-d H:i:s');
            }
        } else {
            $updates['status'] = 'in_progress';
        }

        db_update('support_tickets', $updates, 'id = ?', [$ticketId]);

        $this->json(['success' => true, 'message' => 'Resposta enviada!']);
    }

    /**
     * Update ticket status
     */
    public function updateStatus(array $params): void
    {
        $this->requireDev();
        $ticketId = (int) $params['id'];
        $status = $_POST['status'] ?? '';

        $valid = ['open', 'in_progress', 'waiting', 'resolved', 'closed'];
        if (!in_array($status, $valid)) {
            $this->json(['error' => 'Status inválido'], 400);
            return;
        }

        $updates = ['status' => $status];
        if ($status === 'resolved') {
            $updates['resolved_at'] = date('Y-m-d H:i:s');
        }

        db_update('support_tickets', $updates, 'id = ?', [$ticketId]);

        $this->json(['success' => true]);
    }

    private function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}
