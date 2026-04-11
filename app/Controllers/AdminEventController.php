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
        $tenant = App::tenant();
        $achievements = db_fetch_all("SELECT id, name FROM achievements WHERE tenant_id = ? ORDER BY name ASC", [$tenant['id']]);
        
        View::render('admin/events/create', [
            'tenant' => $tenant,
            'user' => App::user(),
            'achievements' => $achievements,
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
            'start_datetime' => !empty($_POST['start_datetime']) ? str_replace('T', ' ', $_POST['start_datetime']) : null,
            'end_datetime' => !empty($_POST['end_datetime']) ? str_replace('T', ' ', $_POST['end_datetime']) : null,
            'max_participants' => isset($_POST['max_participants']) && $_POST['max_participants'] !== '' ? (int) $_POST['max_participants'] : null,
            'registration_deadline' => !empty($_POST['registration_deadline']) ? str_replace('T', ' ', $_POST['registration_deadline']) : null,
            'xp_reward' => (int) ($_POST['xp_reward'] ?? 0),
            'status' => $_POST['status'] ?? 'upcoming',
            'is_paid' => isset($_POST['is_paid']) ? 1 : 0,
            'price' => isset($_POST['price']) && $_POST['price'] !== '' ? (float) $_POST['price'] : null,
            'payment_link' => trim($_POST['payment_link'] ?? ''),
            'achievement_id' => isset($_POST['achievement_id']) && $_POST['achievement_id'] !== '' ? (int) $_POST['achievement_id'] : null,
            'cover_image_url' => trim($_POST['cover_image_url'] ?? ''),
            'type' => $_POST['type'] ?? 'regular',
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
            $eventId = db_insert('events', $data);

            // Broadcast Notification if Published (upcoming/ongoing)
            if (in_array($data['status'], ['upcoming', 'ongoing']) && isset($_POST['notify_users'])) {
                $this->notificationService->broadcast(
                    'event_created',
                    'Novo Evento: ' . $data['title'],
                    'Um novo evento foi marcado para ' . date('d/m/Y', strtotime($data['start_datetime'])),
                    [
                        'event_id' => $eventId,
                        'channels' => ['push', 'toast', 'email'],
                        'data' => [
                            'link' => base_url($tenant['slug'] . '/eventos/' . $data['slug']),
                            'event_id' => $eventId
                        ]
                    ]
                );
            }

            clear_club_landing_cache($tenant['id']);

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
        $user = App::user();
        $id = (int) $params['id'];

        $event = db_fetch_one("SELECT * FROM events WHERE id = ? AND tenant_id = ?", [$id, $tenant['id']]);

        if (!$event) {
            header('Location: ' . base_url($tenant['slug'] . '/admin/eventos'));
            exit;
        }

        $achievements = db_fetch_all("SELECT id, name FROM achievements WHERE tenant_id = ? ORDER BY name ASC", [$tenant['id']]);

        View::render('admin/events/edit', [
            'tenant' => $tenant,
            'user' => $user,
            'event' => $event,
            'achievements' => $achievements,
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
            'start_datetime' => !empty($_POST['start_datetime']) ? str_replace('T', ' ', $_POST['start_datetime']) : null,
            'end_datetime' => !empty($_POST['end_datetime']) ? str_replace('T', ' ', $_POST['end_datetime']) : null,
            'max_participants' => isset($_POST['max_participants']) && $_POST['max_participants'] !== '' ? (int) $_POST['max_participants'] : null,
            'registration_deadline' => !empty($_POST['registration_deadline']) ? str_replace('T', ' ', $_POST['registration_deadline']) : null,
            'xp_reward' => (int) ($_POST['xp_reward'] ?? 0),
            'status' => $_POST['status'] ?? 'upcoming',
            'is_paid' => isset($_POST['is_paid']) ? 1 : 0,
            'price' => isset($_POST['price']) && $_POST['price'] !== '' ? (float) $_POST['price'] : null,
            'payment_link' => trim($_POST['payment_link'] ?? ''),
            'achievement_id' => isset($_POST['achievement_id']) && $_POST['achievement_id'] !== '' ? (int) $_POST['achievement_id'] : null,
            'cover_image_url' => trim($_POST['cover_image_url'] ?? ''),
            'type' => $_POST['type'] ?? 'regular'
        ];

        if (empty($data['title']) || empty($data['start_datetime'])) {
            $this->jsonError('Título e data de início são obrigatórios.');
            return;
        }

        try {
            db_update('events', $data, 'id = ? AND tenant_id = ?', [$id, $tenant['id']]);

            clear_club_landing_cache($tenant['id']);

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
     * View event enrollees
     */
    public function enrollees(array $params): void
    {
        $this->requireAdmin();
        $tenant = App::tenant();
        $id = (int) $params['id'];

        $event = db_fetch_one("SELECT * FROM events WHERE id = ? AND tenant_id = ?", [$id, $tenant['id']]);

        if (!$event) {
            header('Location: ' . base_url($tenant['slug'] . '/admin/eventos'));
            exit;
        }

        $enrollees = db_fetch_all(
            "SELECT ee.*, u.name, u.avatar_url, u.email,
                (SELECT name FROM roles WHERE id = u.role_id) as role_name
             FROM event_enrollments ee
             JOIN users u ON ee.user_id = u.id
             WHERE ee.event_id = ?
             ORDER BY ee.enrolled_at DESC",
            [$id]
        );

        View::render('admin/events/enrollees', [
            'tenant' => $tenant,
            'user' => App::user(),
            'event' => $event,
            'enrollees' => $enrollees,
            'pageTitle' => 'Inscritos: ' . $event['title'],
            'pageIcon' => 'people'
        ]);
    }

    /**
     * Mark attendance for a user
     */
    public function markAttendance(): void
    {
        $this->requireAdmin();
        $tenant = App::tenant();
        
        $enrollmentId = (int) ($_POST['enrollment_id'] ?? 0);
        $status = $_POST['status'] ?? 'enrolled'; // enrolled, attended, no_show, cancelled

        $enrollment = db_fetch_one(
            "SELECT ee.*, e.xp_reward, e.title as event_title
             FROM event_enrollments ee
             JOIN events e ON ee.event_id = e.id
             WHERE ee.id = ? AND ee.tenant_id = ?",
            [$enrollmentId, $tenant['id']]
        );

        if (!$enrollment) {
            $this->jsonError('Inscrição não encontrada.');
            return;
        }

        try {
            $data = ['status' => $status];
            
            if ($status === 'attended' && $enrollment['status'] !== 'attended') {
                $data['attended_at'] = date('Y-m-d H:i:s');
                $data['xp_earned'] = $enrollment['xp_reward'];
                
                // Award XP
                $progression = new \App\Services\ProgressionService();
                $progression->addXp(
                    (int) $enrollment['user_id'], 
                    (int) $enrollment['xp_reward'], 
                    'event_attendance', 
                    (int) $enrollment['event_id']
                );

                // Generate Certificate
                \App\Services\CertificateService::generateForEvent((int) $enrollmentId, $tenant['id']);

                // Award Badge if exists
                if (!empty($event['achievement_id'])) {
                    $progressionService->awardAchievement(
                        (int) $enrollment['user_id'], 
                        (int) $event['achievement_id'], 
                        $tenant['id']
                    );
                }
            }

            db_update('event_enrollments', $data, 'id = ?', [$enrollmentId]);

            $this->json([
                'success' => true, 
                'message' => 'Status atualizado com sucesso!',
                'new_status' => $status
            ]);
        } catch (\Exception $e) {
            $this->jsonError('Erro ao atualizar status: ' . $e->getMessage());
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
            clear_club_landing_cache($tenant['id']);
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

    /**
     * Show checkin status page (after scanning QR)
     */
    public function checkinStatus(array $params): void
    {
        $this->requireAdmin();
        $tenant = App::tenant();
        $token = $params['token'];

        $enrollment = db_fetch_one("
            SELECT ee.*, u.name as user_name, u.avatar_url, e.title as event_title, e.start_datetime
            FROM event_enrollments ee
            JOIN users u ON ee.user_id = u.id
            JOIN events e ON ee.event_id = e.id
            WHERE ee.checkin_token = ? AND ee.tenant_id = ?
        ", [$token, $tenant['id']]);

        if (!$enrollment) {
            View::render('admin/events/checkin_status', [
                'error' => 'Código de check-in inválido ou não encontrado.',
                'tenant' => $tenant,
                'pageTitle' => 'Erro de Check-in'
            ]);
            return;
        }

        View::render('admin/events/checkin_status', [
            'enrollment' => $enrollment,
            'tenant' => $tenant,
            'user' => App::user(),
            'pageTitle' => 'Confirmar Check-in'
        ]);
    }

    /**
     * Process checkin via token
     */
    public function checkinByToken(array $params): void
    {
        $this->requireAdmin();
        $tenant = App::tenant();
        $token = $params['token'];

        $enrollment = db_fetch_one("
            SELECT ee.*, e.xp_reward, e.achievement_id
            FROM event_enrollments ee
            JOIN events e ON ee.event_id = e.id
            WHERE ee.checkin_token = ? AND ee.tenant_id = ?
        ", [$token, $tenant['id']]);

        if (!$enrollment) {
            $this->json(['error' => 'Check-in inválido'], 404);
            return;
        }

        if ($enrollment['status'] === 'attended') {
            $this->json(['error' => 'Este participante já realizou o check-in.'], 400);
            return;
        }

        try {
            db_update('event_enrollments', [
                'status' => 'attended',
                'attended_at' => date('Y-m-d H:i:s'),
                'xp_earned' => $enrollment['xp_reward']
            ], 'id = ?', [$enrollment['id']]);

            $progressionService = new \App\Services\ProgressionService();
            
            // Award XP
            $progressionService->addXp((int) $enrollment['user_id'], (int) $enrollment['xp_reward'], 'event_attendance', (int) $enrollment['event_id']);

            // Generate Certificate
            \App\Services\CertificateService::generateForEvent((int) $enrollment['id'], $tenant['id']);

            // Award Badge if exists
            if (!empty($enrollment['achievement_id'])) {
                $progressionService->awardAchievement((int) $enrollment['user_id'], (int) $enrollment['achievement_id'], $tenant['id']);
            }

            $this->json([
                'success' => true,
                'message' => 'Check-in realizado com sucesso! Recompensas concedidas.'
            ]);
        } catch (\Exception $e) {
            $this->json(['error' => 'Erro ao processar check-in: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Mark all enrollees as attended
     */
    public function markAllAttendance(array $params): void
    {
        $this->requireAdmin();
        $tenant = App::tenant();
        $eventId = (int) $params['id'];

        $event = db_fetch_one("SELECT * FROM events WHERE id = ? AND tenant_id = ?", [$eventId, $tenant['id']]);
        if (!$event) {
            $this->json(['error' => 'Evento não encontrado'], 404);
            return;
        }

        $enrollees = db_fetch_all(
            "SELECT id, user_id FROM event_enrollments WHERE event_id = ? AND status = 'enrolled'",
            [$eventId]
        );

        $progressionService = new \App\Services\ProgressionService();
        $count = 0;

        foreach ($enrollees as $enrollee) {
            db_update('event_enrollments', [
                'status' => 'attended',
                'attended_at' => date('Y-m-d H:i:s'),
                'xp_earned' => $event['xp_reward']
            ], 'id = ?', [$enrollee['id']]);

            // Award XP
            $progressionService->addXp((int) $enrollee['user_id'], (int) $event['xp_reward'], 'event_attendance', $eventId);

            // Generate Certificate
            \App\Services\CertificateService::generateForEvent((int) $enrollee['id'], $tenant['id']);

            // Award Badge if exists
            if (!empty($event['achievement_id'])) {
                $progressionService->awardAchievement((int) $enrollee['user_id'], (int) $event['achievement_id'], $tenant['id']);
            }

            $count++;
        }

        $this->json([
            'success' => true,
            'message' => "{$count} participantes marcados com presença!",
            'redirect' => base_url($tenant['slug'] . '/admin/eventos/' . $eventId . '/inscritos')
        ]);
    /**
     * Show gallery management
     */
    public function gallery(array $params): void
    {
        $this->requireAdmin();
        $tenant = App::tenant();
        $id = (int) $params['id'];

        $event = db_fetch_one("SELECT * FROM events WHERE id = ? AND tenant_id = ?", [$id, $tenant['id']]);
        if (!$event) {
            header('Location: ' . base_url($tenant['slug'] . '/admin/eventos'));
            exit;
        }

        $gallery = db_fetch_all("SELECT * FROM event_gallery WHERE event_id = ? ORDER BY created_at DESC", [$id]);

        View::render('admin/events/gallery', [
            'tenant' => $tenant,
            'user' => App::user(),
            'event' => $event,
            'gallery' => $gallery,
            'pageTitle' => 'Galeria do Evento',
            'pageIcon' => 'photo_library'
        ]);
    }

    /**
     * Add image to gallery
     */
    public function addGalleryImage(array $params): void
    {
        $this->requireAdmin();
        $tenant = App::tenant();
        $id = (int) $params['id'];

        $imageUrl = trim($_POST['image_url'] ?? '');
        $caption = trim($_POST['caption'] ?? '');

        if (empty($imageUrl)) {
            $this->json(['error' => 'URL da imagem é obrigatória'], 400);
            return;
        }

        try {
            db_insert('event_gallery', [
                'event_id' => $id,
                'tenant_id' => $tenant['id'],
                'image_url' => $imageUrl,
                'caption' => $caption
            ]);

            $this->json(['success' => true, 'message' => 'Imagem adicionada com sucesso!']);
        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove image from gallery
     */
    public function removeGalleryImage(array $params): void
    {
        $this->requireAdmin();
        $tenant = App::tenant();
        $imageId = (int) $params['id'];

        try {
            db_query("DELETE FROM event_gallery WHERE id = ? AND tenant_id = ?", [$imageId, $tenant['id']]);
            $this->json(['success' => true, 'message' => 'Imagem removida!']);
        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
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
