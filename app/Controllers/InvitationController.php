<?php
/**
 * Invitation Controller
 * 
 * Manages leadership invitations for directors, associate directors, counselors and instructors.
 */

namespace App\Controllers;

use App\Core\App;
use App\Services\AuthService;
use App\Services\EmailService;

class InvitationController
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }
    /**
     * List all invitations
     */
    public function index(): void
    {
        $this->requireDirector();

        $tenant = App::tenant();
        $user = App::user();

        // Check if migration needed
        $migrationNeeded = false;
        try {
            db_fetch_one("SELECT 1 FROM leadership_invitations LIMIT 1");
        } catch (\Exception $e) {
            $migrationNeeded = true;
        }

        // Get all invitations for this tenant (with error handling)
        try {
            $invitations = db_fetch_all(
                "SELECT i.*, u.name as invited_by_name 
                 FROM leadership_invitations i 
                 LEFT JOIN users u ON i.invited_by = u.id 
                 WHERE i.tenant_id = ? 
                 ORDER BY i.created_at DESC",
                [$tenant['id']]
            );
        } catch (\Exception $e) {
            $invitations = [];
        }

        // Separate pending and accepted
        $pending = array_filter($invitations, fn($i) => $i['accepted_at'] === null && strtotime($i['expires_at']) > time());
        $expired = array_filter($invitations, fn($i) => $i['accepted_at'] === null && strtotime($i['expires_at']) <= time());
        $accepted = array_filter($invitations, fn($i) => $i['accepted_at'] !== null);

        // Get available roles
        $roles = [
            'associate_director' => 'Diretor Associado',
            'counselor' => 'Conselheiro',
            'instructor' => 'Instrutor'
        ];

        \App\Core\View::render('admin/invitations/index', [
            'tenant' => $tenant,
            'user' => $user,
            'invitations' => $invitations,
            'pending' => $pending,
            'accepted' => $accepted,
            'expired' => $expired,
            'roles' => $roles,
            'pageTitle' => 'Convites de Liderança',
            'pageIcon' => 'mail',
            'migrationNeeded' => $migrationNeeded
        ]);
    }

    /**
     * Show create invitation form
     */
    public function create(): void
    {
        $this->requireDirector();

        $tenant = App::tenant();
        $user = App::user();

        $roles = [
            'associate_director' => 'Diretor Associado',
            'counselor' => 'Conselheiro',
            'instructor' => 'Instrutor'
        ];

        \App\Core\View::render('admin/invitations/create', [
            'tenant' => $tenant,
            'user' => $user,
            'roles' => $roles,
            'pageTitle' => 'Novo Convite',
            'pageIcon' => 'send'
        ]);
    }

    /**
     * Send invitation
     */
    public function store(): void
    {
        $this->requireDirector();

        $tenant = App::tenant();
        $user = App::user();

        $email = trim($_POST['email'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $roleName = $_POST['role_name'] ?? '';

        // Validate
        $errors = [];

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email inválido';
        }

        if (!in_array($roleName, ['associate_director', 'counselor', 'instructor'])) {
            $errors[] = 'Cargo inválido';
        }

        // Check if user already exists
        $existingUser = db_fetch_one(
            "SELECT id FROM users WHERE email = ? AND tenant_id = ?",
            [$email, $tenant['id']]
        );

        if ($existingUser) {
            $errors[] = 'Já existe um usuário com este email neste clube';
        }

        // Check for pending invitation
        $existingInvite = db_fetch_one(
            "SELECT id FROM leadership_invitations 
             WHERE email = ? AND tenant_id = ? AND accepted_at IS NULL AND expires_at > NOW()",
            [$email, $tenant['id']]
        );

        if ($existingInvite) {
            $errors[] = 'Já existe um convite pendente para este email';
        }

        if (!empty($errors)) {
            $_SESSION['flash_error'] = implode(', ', $errors);
            header('Location: ' . base_url($tenant['slug'] . '/admin/convites/novo'));
            return;
        }

        // Generate unique token
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+7 days'));

        // Create invitation
        db_insert('leadership_invitations', [
            'tenant_id' => $tenant['id'],
            'invited_by' => $user['id'],
            'email' => $email,
            'name' => $name ?: null,
            'role_name' => $roleName,
            'token' => $token,
            'expires_at' => $expiresAt
        ]);

        // Send invitation email
        $this->sendInvitationEmail($tenant, $user, $email, $name, $roleName, $token);

        $_SESSION['flash_success'] = 'Convite enviado com sucesso para ' . $email;
        header('Location: ' . base_url($tenant['slug'] . '/admin/convites'));
    }

    /**
     * Resend invitation
     */
    public function resend(array $params): void
    {
        $this->requireDirector();

        $tenant = App::tenant();
        $user = App::user();
        $inviteId = $params['id'] ?? 0;

        $invitation = db_fetch_one(
            "SELECT * FROM leadership_invitations WHERE id = ? AND tenant_id = ? AND accepted_at IS NULL",
            [$inviteId, $tenant['id']]
        );

        if (!$invitation) {
            $_SESSION['flash_error'] = 'Convite não encontrado';
            header('Location: ' . base_url($tenant['slug'] . '/admin/convites'));
            return;
        }

        // Generate new token and extend expiry
        $newToken = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+7 days'));

        db_update('leadership_invitations', [
            'token' => $newToken,
            'expires_at' => $expiresAt
        ], 'id = ?', [$inviteId]);

        // Resend email
        $this->sendInvitationEmail(
            $tenant,
            $user,
            $invitation['email'],
            $invitation['name'],
            $invitation['role_name'],
            $newToken
        );

        $_SESSION['flash_success'] = 'Convite reenviado com sucesso';
        header('Location: ' . base_url($tenant['slug'] . '/admin/convites'));
    }

    /**
     * Revoke invitation
     */
    public function revoke(array $params): void
    {
        $this->requireDirector();

        $tenant = App::tenant();
        $inviteId = $params['id'] ?? 0;

        $deleted = db_delete('leadership_invitations', 'id = ? AND tenant_id = ? AND accepted_at IS NULL', [$inviteId, $tenant['id']]);

        if ($deleted) {
            $_SESSION['flash_success'] = 'Convite revogado';
        } else {
            $_SESSION['flash_error'] = 'Convite não encontrado ou já aceito';
        }

        header('Location: ' . base_url($tenant['slug'] . '/admin/convites'));
    }

    /**
     * Accept invitation (public page)
     */
    public function accept(array $params): void
    {
        $token = $params['token'] ?? '';
        $tenant = App::tenant();

        $invitation = db_fetch_one(
            "SELECT i.*, t.name as club_name, t.slug as club_slug
             FROM leadership_invitations i
             JOIN tenants t ON i.tenant_id = t.id
             WHERE i.token = ? AND i.tenant_id = ? AND i.accepted_at IS NULL",
            [$token, $tenant['id']]
        );

        if (!$invitation) {
            $error = 'Convite inválido ou expirado';
            require BASE_PATH . '/views/auth/invitation-error.php';
            return;
        }

        if (strtotime($invitation['expires_at']) <= time()) {
            $error = 'Este convite expirou. Solicite um novo convite ao diretor do clube.';
            require BASE_PATH . '/views/auth/invitation-error.php';
            return;
        }

        $roleLabels = [
            'associate_director' => 'Diretor Associado',
            'counselor' => 'Conselheiro',
            'instructor' => 'Instrutor'
        ];

        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processAcceptance($invitation, $tenant);
            return;
        }

        require BASE_PATH . '/views/auth/accept-invitation.php';
    }

    /**
     * Process invitation acceptance
     */
    private function processAcceptance(array $invitation, array $tenant): void
    {
        $name = trim($_POST['name'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        $errors = [];

        if (empty($name)) {
            $errors[] = 'Nome é obrigatório';
        }

        if (strlen($password) < 6) {
            $errors[] = 'Senha deve ter pelo menos 6 caracteres';
        }

        if ($password !== $passwordConfirm) {
            $errors[] = 'As senhas não coincidem';
        }

        if (!empty($errors)) {
            $error = implode(', ', $errors);
            $roleLabels = [
                'associate_director' => 'Diretor Associado',
                'counselor' => 'Conselheiro',
                'instructor' => 'Instrutor'
            ];
            require BASE_PATH . '/views/auth/accept-invitation.php';
            return;
        }

        // Ensure all official roles exist for this tenant
        \App\Services\RoleService::syncTenant($tenant['id']);

        // Get role ID
        $role = db_fetch_one(
            "SELECT id FROM roles WHERE tenant_id = ? AND name = ?",
            [$tenant['id'], $invitation['role_name']]
        );

        if (!$role) {
            $error = 'Configuração de cargos do clube inválida. Contate o administrador.';
            require BASE_PATH . '/views/auth/invitation-error.php';
            return;
        }
        $roleId = $role['id'];

        // Create user
        $userId = db_insert('users', [
            'tenant_id' => $tenant['id'],
            'role_id' => $roleId,
            'email' => $invitation['email'],
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'name' => $name,
            'status' => 'active',
            'email_verified_at' => date('Y-m-d H:i:s')
        ]);

        // Mark invitation as accepted
        db_update('leadership_invitations', [
            'accepted_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$invitation['id']]);

        // Log the user in (Token-based)
        $token = $this->authService->createSession($userId);
        $this->authService->setAuthCookie($token);

        $_SESSION['flash_success'] = 'Conta criada com sucesso! Bem-vindo ao ' . $tenant['name'];
        header('Location: ' . base_url($tenant['slug'] . '/admin'));
    }

    /**
     * Send invitation email
     */
    private function sendInvitationEmail(array $tenant, array $inviter, string $email, ?string $name, string $roleName, string $token): void
    {
        $roleLabels = [
            'associate_director' => 'Diretor Associado',
            'counselor' => 'Conselheiro',
            'instructor' => 'Instrutor'
        ];

        $roleLabel = $roleLabels[$roleName] ?? $roleName;
        $inviteUrl = base_url($tenant['slug'] . '/convite/' . $token);
        $greeting = $name ? "Olá {$name}," : "Olá,";

        // Define expiry date for template
        $expiresAt = date('Y-m-d', strtotime('+7 days'));

        $subject = "Convite para Liderança - {$tenant['name']}";

        // Capture view content
        ob_start();
        $customMessage = null; // No custom message for leadership invite by default
        require BASE_PATH . '/views/emails/invitation.php';
        $htmlBody = ob_get_clean();

        try {
            $emailService = EmailService::getInstance();
            $emailService->send($email, $subject, $htmlBody);
        } catch (\Exception $e) {
            error_log("Failed to send invitation email: " . $e->getMessage());
        }
    }

    /**
     * Send member invitation email
     */
    private function sendMemberInvitationEmail(array $tenant, array $inviter, string $email, ?string $name, string $roleName, ?string $customMessage, string $token): void
    {
        $roleLabels = [
            'pathfinder' => 'Desbravador',
            'parent' => 'Pai/Responsável',
            'instructor' => 'Instrutor',
            'counselor' => 'Conselheiro',
            'associate_director' => 'Diretor Associado',
            'director' => 'Diretor'
        ];
        $roleLabel = $roleLabels[$roleName] ?? 'Membro';

        $inviteUrl = base_url($tenant['slug'] . '/entrar/' . $token);
        $greeting = $name ? "Olá {$name}!" : "Olá!";

        // Define expiry for template
        $expiresAt = date('Y-m-d', strtotime('+30 days'));

        $subject = "Você foi convidado para o {$tenant['name']}!";

        // Capture view content
        ob_start();
        require BASE_PATH . '/views/emails/invitation.php';
        $htmlBody = ob_get_clean();

        try {
            $emailService = EmailService::getInstance();
            $emailService->send($email, $subject, $htmlBody);
        } catch (\Exception $e) {
            error_log("Failed to send member invitation email: " . $e->getMessage());
        }
    }

    /**
     * Require director role
     */
    private function requireDirector(): void
    {
        $user = App::user();
        if (!$user) {
            header('Location: ' . base_url(App::tenant()['slug'] . '/login'));
            exit;
        }

        $role = $user['role_name'] ?? '';
        if (!in_array($role, ['admin', 'director'])) {
            http_response_code(403);
            echo "Acesso negado. Apenas diretores podem gerenciar convites.";
            exit;
        }
    }

    /**
     * Require leadership role (director, associate, counselor)
     */
    private function requireLeadership(): void
    {
        $user = App::user();
        if (!$user) {
            header('Location: ' . base_url(App::tenant()['slug'] . '/login'));
            exit;
        }

        $role = $user['role_name'] ?? '';
        if (!in_array($role, ['admin', 'director', 'associate_director', 'counselor', 'instructor'])) {
            http_response_code(403);
            echo "Acesso negado.";
            exit;
        }
    }
    /**
     * List all MEMBER invitations
     */
    public function membersIndex(): void
    {
        $this->requireLeadership();

        $tenant = App::tenant();
        $user = App::user();

        // Get member invitations
        try {
            $invitations = db_fetch_all(
                "SELECT i.*, u.name as invited_by_name 
                 FROM member_invitations i 
                 LEFT JOIN users u ON i.invited_by = u.id 
                 WHERE i.tenant_id = ? 
                 ORDER BY i.created_at DESC",
                [$tenant['id']]
            );
        } catch (\Exception $e) {
            $invitations = [];
        }

        // Separate pending and accepted
        $pending = array_filter($invitations, fn($i) => $i['accepted_at'] === null && strtotime($i['expires_at']) > time());
        $expired = array_filter($invitations, fn($i) => $i['accepted_at'] === null && strtotime($i['expires_at']) <= time());
        $accepted = array_filter($invitations, fn($i) => $i['accepted_at'] !== null);

        $roles = [
            'pathfinder' => 'Desbravador',
            'parent' => 'Pai/Responsável'
        ];

        \App\Core\View::render('admin/invitations/members', [
            'tenant' => $tenant,
            'user' => $user,
            'invitations' => $invitations,
            'pending' => $pending,
            'accepted' => $accepted,
            'expired' => $expired,
            'roles' => $roles,
            'pageTitle' => 'Convites de Membros',
            'pageIcon' => 'users'
        ]);
    }

    /**
     * Show create MEMBER invitation form
     */
    public function membersCreate(): void
    {
        $this->requireLeadership();

        $tenant = App::tenant();
        $user = App::user();

        $roles = [
            'pathfinder' => 'Desbravador',
            'parent' => 'Pai/Responsável'
        ];

        \App\Core\View::render('admin/invitations/members-create', [
            'tenant' => $tenant,
            'user' => $user,
            'roles' => $roles,
            'pageTitle' => 'Convidar Membro',
            'pageIcon' => 'user-plus'
        ]);
    }

    /**
     * Store MEMBER invitation
     */
    public function membersStore(): void
    {
        $this->requireLeadership();

        $tenant = App::tenant();
        $user = App::user();

        $email = trim($_POST['email'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $roleName = $_POST['role_name'] ?? '';
        $customMessage = trim($_POST['custom_message'] ?? '');

        // Validate
        $errors = [];

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email inválido';
        }

        if (!in_array($roleName, ['pathfinder', 'parent'])) {
            $errors[] = 'Função inválida';
        }

        // Check user existence
        $existingUser = db_fetch_one(
            "SELECT id FROM users WHERE email = ? AND tenant_id = ?",
            [$email, $tenant['id']]
        );

        if ($existingUser) {
            $errors[] = 'Já existe um usuário com este email neste clube';
        }

        // Check pending invite
        $existingInvite = db_fetch_one(
            "SELECT id FROM member_invitations 
             WHERE email = ? AND tenant_id = ? AND accepted_at IS NULL AND expires_at > NOW()",
            [$email, $tenant['id']]
        );

        if ($existingInvite) {
            $errors[] = 'Já existe um convite pendente para este email';
        }

        if (!empty($errors)) {
            $_SESSION['flash_error'] = implode(', ', $errors);
            header('Location: ' . base_url($tenant['slug'] . '/admin/convites/membros/novo'));
            return;
        }

        // Generate token
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days')); // Members have 30 days

        db_insert('member_invitations', [
            'tenant_id' => $tenant['id'],
            'invited_by' => $user['id'],
            'email' => $email,
            'name' => $name ?: null,
            'role_name' => $roleName,
            'custom_message' => $customMessage ?: null,
            'token' => $token,
            'expires_at' => $expiresAt
        ]);

        // Send email
        $this->sendMemberInvitationEmail($tenant, $user, $email, $name, $roleName, $customMessage, $token);

        $_SESSION['flash_success'] = 'Convite enviado com sucesso para ' . $email;
        header('Location: ' . base_url($tenant['slug'] . '/admin/convites/membros'));
    }

    /**
     * Resend MEMBER invitation
     */
    public function membersResend(array $params): void
    {
        $this->requireLeadership();

        $tenant = App::tenant();
        $user = App::user();
        $inviteId = $params['id'] ?? 0;

        $invitation = db_fetch_one(
            "SELECT * FROM member_invitations WHERE id = ? AND tenant_id = ? AND accepted_at IS NULL",
            [$inviteId, $tenant['id']]
        );

        if (!$invitation) {
            $_SESSION['flash_error'] = 'Convite não encontrado';
            header('Location: ' . base_url($tenant['slug'] . '/admin/convites/membros'));
            return;
        }

        $newToken = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));

        db_update('member_invitations', [
            'token' => $newToken,
            'expires_at' => $expiresAt
        ], 'id = ?', [$inviteId]);

        $this->sendMemberInvitationEmail(
            $tenant,
            $user,
            $invitation['email'],
            $invitation['name'],
            $invitation['role_name'],
            $invitation['custom_message'],
            $newToken
        );

        $_SESSION['flash_success'] = 'Convite reenviado com sucesso';
        header('Location: ' . base_url($tenant['slug'] . '/admin/convites/membros'));
    }

    /**
     * Revoke MEMBER invitation
     */
    public function membersRevoke(array $params): void
    {
        $this->requireLeadership();

        $tenant = App::tenant();
        $inviteId = $params['id'] ?? 0;

        $deleted = db_delete('member_invitations', 'id = ? AND tenant_id = ? AND accepted_at IS NULL', [$inviteId, $tenant['id']]);

        if ($deleted) {
            $_SESSION['flash_success'] = 'Convite revogado';
        } else {
            $_SESSION['flash_error'] = 'Convite não encontrado ou já aceito';
        }

        header('Location: ' . base_url($tenant['slug'] . '/admin/convites/membros'));
    }

    /**
     * Accept MEMBER invitation (public page)
     * This method handles the public facing acceptance page for members
     */
    public function membersAccept(array $params): void
    {
        $token = $params['token'] ?? '';
        $tenant = App::tenant();

        $invitation = db_fetch_one(
            "SELECT i.*, t.name as club_name, t.slug as club_slug
             FROM member_invitations i
             JOIN tenants t ON i.tenant_id = t.id
             WHERE i.token = ? AND i.tenant_id = ? AND i.accepted_at IS NULL",
            [$token, $tenant['id']]
        );

        if (!$invitation) {
            $error = 'Convite inválido ou expirado';
            require BASE_PATH . '/views/auth/invitation-error.php';
            return;
        }

        if (strtotime($invitation['expires_at']) <= time()) {
            $error = 'Este convite expirou.';
            require BASE_PATH . '/views/auth/invitation-error.php';
            return;
        }

        // Handle POST submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processMemberAcceptance($invitation, $tenant);
            return;
        }

        require BASE_PATH . '/views/auth/accept-member-invitation.php';
    }

    /**
     * Process member acceptance
     */
    private function processMemberAcceptance(array $invitation, array $tenant): void
    {
        $name = trim($_POST['name'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';
        $birthDate = $_POST['birth_date'] ?? null;

        $errors = [];

        if (empty($name)) {
            $errors[] = 'Nome é obrigatório';
        }
        if (strlen($password) < 6) {
            $errors[] = 'Senha deve ter pelo menos 6 caracteres';
        }
        if ($password !== $passwordConfirm) {
            $errors[] = 'As senhas não coincidem';
        }

        if (!empty($errors)) {
            $error = implode(', ', $errors);
            require BASE_PATH . '/views/auth/accept-member-invitation.php';
            return;
        }

        // Ensure all official roles exist for this tenant
        \App\Services\RoleService::syncTenant($tenant['id']);

        // Get Role
        $roleName = $invitation['role_name']; // pathfinder or parent
        $role = db_fetch_one("SELECT id FROM roles WHERE tenant_id = ? AND name = ?", [$tenant['id'], $roleName]);
        
        if (!$role) {
            $error = 'Configuração de cargos do clube inválida.';
            require BASE_PATH . '/views/auth/accept-member-invitation.php';
            return;
        }
        $roleId = $role['id'];

        // Create User
        $userId = db_insert('users', [
            'tenant_id' => $tenant['id'],
            'role_id' => $roleId,
            'email' => $invitation['email'],
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'name' => $name,
            'birth_date' => $birthDate,
            'status' => 'active',
            'email_verified_at' => date('Y-m-d H:i:s')
        ]);

        // Update Invitation
        db_update('member_invitations', ['accepted_at' => date('Y-m-d H:i:s')], 'id = ?', [$invitation['id']]);

        // Log in (Token-based)
        $token = $this->authService->createSession($userId);
        $this->authService->setAuthCookie($token);
        
        $_SESSION['flash_success'] = 'Bem-vindo ao Clube!';
        header('Location: ' . base_url($tenant['slug'] . '/dashboard'));
    }

}
