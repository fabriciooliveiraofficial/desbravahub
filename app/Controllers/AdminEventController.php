<?php
/**
 * Admin Event Controller
 * 
 * Manages the creation and administration of events for a Club (Tenant).
 */

namespace App\Controllers;

use App\Core\View;
use App\Core\App;
use App\Services\NotificationService;

class AdminEventController
{
    private NotificationService $notificationService;

    public function __construct()
    {
        $this->notificationService = new NotificationService();
    }

    /**
     * List events for admin management
     */
    public function index(): void
    {
        $this->requireAdmin();
        $tenant = App::tenant();
        $user = App::user();

        $events = db_fetch_all(
            "SELECT e.*, 
                (SELECT COUNT(*) FROM event_enrollments WHERE event_id = e.id) as enrolled_count
             FROM events e 
             WHERE e.tenant_id = ?
             ORDER BY e.start_datetime DESC",
            [$tenant['id']]
        );

        View::render('admin/events/index', [
            'tenant' => $tenant,
            'user' => $user,
            'events' => $events,
            'pageTitle' => 'Gerenciar Eventos',
            'pageIcon' => 'event'
        ]);
    }

    /**
     * Show create form
     */
    public function create(): void
    {
        $this->requireAdmin();
        
        View::render('admin/events/create', [
            'tenant' => App::tenant(),
            'user' => App::user(),
            'pageTitle' => 'Novo Evento',
            'pageIcon' => 'add_box'
        ]);
    }

    /**
     * Store a new event
     */
    public function store(): void
    {
        $this->requireAdmin();
        $tenant = App::tenant();
        $user = App::user();

        $data = [
            'tenant_id' => $tenant['id'],
            'title' => trim($_POST['title'] ?? ''),
            'slug' => trim($_POST['slug'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'location' => trim($_POST['location'] ?? ''),
            'start_datetime' => $_POST['start_datetime'] ?? null,
            'end_datetime' => !empty($_POST['end_datetime']) ? $_POST['end_datetime'] : null,
            'max_participants' => !empty($_POST['max_participants']) ? (int) $_POST['max_participants'] : null,
            'registration_deadline' => !empty($_POST['registration_deadline']) ? $_POST['registration_deadline'] : null,
            'xp_reward' => (int) ($_POST['xp_reward'] ?? 0),
            'status' => $_POST['status'] ?? 'upcoming',
            'is_paid' => isset($_POST['is_paid']) ? 1 : 0,
            'price' => !empty($_POST['price']) ? (float) $_POST['price'] : null,
            'payment_link' => trim($_POST['payment_link'] ?? ''),
            'created_by' => $user['id']
        ];

        if (empty($data['title']) || empty($data['start_datetime'])) {
            $this->jsonError('Título e data de início são obrigatórios.');
            return;
        }

        if (empty($data['slug'])) {
            $data['slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['title']), '-'));
        }

        try {
            db_insert('events', $data);
            $eventId = db_last_insert_id();

            // Broadcast Notification if Published (upcoming/ongoing)
            if (in_array($data['status'], ['upcoming', 'ongoing']) && isset($_POST['notify_users'])) {
                $this->notificationService->broadcast(
                    'event_created',
                    'Novo Evento: ' . $data['title'],
                    'Um novo evento foi marcado para ' . date('d/m/Y', strtotime($data['start_datetime'])),
                    [
                        'event_id' => $eventId,
                        'channels' => ['push', 'toast']
                    ]
                );
            }

            $this->json([
                'success' => true,
                'message' => 'Evento criado com sucesso!',
                'redirect' => base_url($tenant['slug'] . '/admin/eventos')
            ]);
        } catch (\Exception $e) {
            $this->jsonError('Erro ao criar evento: ' . $e->getMessage());
        }
    }

    /**
     * Show edit form
     */
    public function edit(array $params): void
    {
        $this->requireAdmin();
        $tenant = App::tenant();
        $id = (int) $params['id'];

        $event = db_fetch_one("SELECT * FROM events WHERE id = ? AND tenant_id = ?", [$id, $tenant['id']]);

        if (!$event) {
            header('Location: ' . base_url($tenant['slug'] . '/admin/eventos'));
            exit;
        }

        View::render('admin/events/edit', [
            'tenant' => $tenant,
            'user' => App::user(),
            'event' => $event,
            'pageTitle' => 'Editar Evento',
            'pageIcon' => 'edit'
        ]);
    }

    /**
     * Update an event
     */
    public function update(array $params): void
    {
        $this->requireAdmin();
        $tenant = App::tenant();
        $id = (int) $params['id'];

        $data = [
            'title' => trim($_POST['title'] ?? ''),
            'slug' => trim($_POST['slug'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'location' => trim($_POST['location'] ?? ''),
            'start_datetime' => $_POST['start_datetime'] ?? null,
            'end_datetime' => !empty($_POST['end_datetime']) ? $_POST['end_datetime'] : null,
            'max_participants' => !empty($_POST['max_participants']) ? (int) $_POST['max_participants'] : null,
            'registration_deadline' => !empty($_POST['registration_deadline']) ? $_POST['registration_deadline'] : null,
            'xp_reward' => (int) ($_POST['xp_reward'] ?? 0),
            'status' => $_POST['status'] ?? 'upcoming',
            'is_paid' => isset($_POST['is_paid']) ? 1 : 0,
            'price' => !empty($_POST['price']) ? (float) $_POST['price'] : null,
            'payment_link' => trim($_POST['payment_link'] ?? '')
        ];

        if (empty($data['title']) || empty($data['start_datetime'])) {
            $this->jsonError('Título e data de início são obrigatórios.');
            return;
        }

        try {
            db_update('events', $data, 'id = ? AND tenant_id = ?', [$id, $tenant['id']]);

            $this->json([
                'success' => true,
                'message' => 'Evento atualizado com sucesso!',
                'redirect' => base_url($tenant['slug'] . '/admin/eventos')
            ]);
        } catch (\Exception $e) {
            $this->jsonError('Erro ao atualizar evento: ' . $e->getMessage());
        }
    }

    /**
     * Delete an event
     */
    public function delete(array $params): void
    {
        $this->requireAdmin();
        $tenant = App::tenant();
        $id = (int) $params['id'];

        try {
            db_query("DELETE FROM events WHERE id = ? AND tenant_id = ?", [$id, $tenant['id']]);
            $this->json(['success' => true, 'message' => 'Evento removido com sucesso.']);
        } catch (\Exception $e) {
            $this->jsonError('Erro ao remover evento: ' . $e->getMessage());
        }
    }

    private function requireAdmin(): void
    {
        $user = App::user();
        $roleName = $user['role_name'] ?? '';

        if (!in_array($roleName, ['admin', 'director', 'associate_director', 'counselor'])) {
            http_response_code(403);
            echo "Acesso negado";
            exit;
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
