<?php
/**
 * Support Controller
 * 
 * Handles support tickets for authenticated users.
 */

namespace App\Controllers;

use App\Core\App;

class SupportController
{
    /**
     * List user's support tickets
     */
    public function index(): void
    {
        $user = App::user();
        $tenant = App::tenant();

        $tickets = db_fetch_all(
            "SELECT * FROM support_tickets 
             WHERE tenant_id = ? AND user_id = ?
             ORDER BY updated_at DESC",
            [$tenant['id'], $user['id']]
        );

        require BASE_PATH . '/views/dashboard/support/index.php';
    }

    /**
     * Show create ticket form
     */
    public function create(): void
    {
        $user = App::user();
        $tenant = App::tenant();

        require BASE_PATH . '/views/dashboard/support/create.php';
    }

    /**
     * Store new ticket
     */
    public function store(): void
    {
        $user = App::user();
        $tenant = App::tenant();

        $category = $_POST['category'] ?? 'question';
        $priority = $_POST['priority'] ?? 'medium';
        $subject = trim($_POST['subject'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $relatedModule = $_POST['related_module'] ?? null;
        $relatedUrl = $_SERVER['HTTP_REFERER'] ?? null;

        if (empty($subject) || empty($description)) {
            $this->json(['error' => 'Assunto e descrição são obrigatórios'], 400);
            return;
        }

        $ticketId = db_insert('support_tickets', [
            'tenant_id' => $tenant['id'],
            'user_id' => $user['id'],
            'category' => $category,
            'priority' => $priority,
            'subject' => $subject,
            'description' => $description,
            'related_module' => $relatedModule,
            'related_url' => $relatedUrl,
            'status' => 'open',
        ]);

        // Create initial message
        db_insert('support_messages', [
            'ticket_id' => $ticketId,
            'sender_type' => 'user',
            'sender_id' => $user['id'],
            'sender_name' => $user['name'],
            'message' => $description,
            'is_internal' => 0,
        ]);

        // Handle file uploads
        if (!empty($_FILES['attachments']['name'][0])) {
            $this->handleAttachments($ticketId, null, $_FILES['attachments']);
        }

        $this->json([
            'success' => true,
            'message' => 'Chamado criado com sucesso!',
            'redirect' => base_url($tenant['slug'] . '/suporte/' . $ticketId)
        ]);
    }

    /**
     * Show ticket details
     */
    public function show(array $params): void
    {
        $user = App::user();
        $tenant = App::tenant();
        $ticketId = (int) $params['id'];

        $ticket = db_fetch_one(
            "SELECT * FROM support_tickets 
             WHERE id = ? AND tenant_id = ? AND user_id = ?",
            [$ticketId, $tenant['id'], $user['id']]
        );

        if (!$ticket) {
            http_response_code(404);
            echo "Chamado não encontrado";
            return;
        }

        $messages = db_fetch_all(
            "SELECT * FROM support_messages 
             WHERE ticket_id = ? AND is_internal = 0
             ORDER BY created_at ASC",
            [$ticketId]
        );

        $attachments = db_fetch_all(
            "SELECT * FROM support_attachments WHERE ticket_id = ?",
            [$ticketId]
        );

        require BASE_PATH . '/views/dashboard/support/show.php';
    }

    /**
     * Reply to ticket
     */
    public function reply(array $params): void
    {
        $user = App::user();
        $tenant = App::tenant();
        $ticketId = (int) $params['id'];

        // Verify ownership
        $ticket = db_fetch_one(
            "SELECT id FROM support_tickets 
             WHERE id = ? AND tenant_id = ? AND user_id = ?",
            [$ticketId, $tenant['id'], $user['id']]
        );

        if (!$ticket) {
            $this->json(['error' => 'Chamado não encontrado'], 404);
            return;
        }

        $message = trim($_POST['message'] ?? '');
        if (empty($message)) {
            $this->json(['error' => 'Mensagem é obrigatória'], 400);
            return;
        }

        $messageId = db_insert('support_messages', [
            'ticket_id' => $ticketId,
            'sender_type' => 'user',
            'sender_id' => $user['id'],
            'sender_name' => $user['name'],
            'message' => $message,
            'is_internal' => 0,
        ]);

        // Update ticket status
        db_update('support_tickets', ['status' => 'open'], 'id = ?', [$ticketId]);

        // Handle file uploads
        if (!empty($_FILES['attachments']['name'][0])) {
            $this->handleAttachments($ticketId, $messageId, $_FILES['attachments']);
        }

        $this->json(['success' => true, 'message' => 'Resposta enviada!']);
    }

    /**
     * Handle file attachments
     */
    private function handleAttachments(int $ticketId, ?int $messageId, array $files): void
    {
        $uploadDir = BASE_PATH . '/public/storage/support/' . $ticketId . '/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        foreach ($files['name'] as $i => $filename) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK)
                continue;

            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            $newName = uniqid() . '.' . $ext;
            $path = $uploadDir . $newName;

            if (move_uploaded_file($files['tmp_name'][$i], $path)) {
                db_insert('support_attachments', [
                    'ticket_id' => $ticketId,
                    'message_id' => $messageId,
                    'filename' => $filename,
                    'path' => 'support/' . $ticketId . '/' . $newName,
                    'size' => $files['size'][$i],
                    'mime_type' => $files['type'][$i],
                ]);
            }
        }
    }

    private function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}
