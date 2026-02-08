<?php
/**
 * Home Controller
 * 
 * Handles the global home page for club registration.
 */

namespace App\Controllers;

class HomeController
{
    /**
     * Show global home page
     */
    public function index(): void
    {
        require BASE_PATH . '/views/home.php';
    }

    /**
     * Show club registration form
     */
    public function showRegisterClub(): void
    {
        require BASE_PATH . '/views/register-club.php';
    }

    /**
     * Handle club registration
     */
    public function registerClub(): void
    {
        $clubName = trim($_POST['club_name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $adminEmail = trim($_POST['admin_email'] ?? '');
        $adminPassword = $_POST['admin_password'] ?? '';
        $adminName = trim($_POST['admin_name'] ?? '');

        // Validate
        $errors = [];

        if (empty($clubName)) {
            $errors[] = 'Nome do clube é obrigatório';
        }

        if (empty($slug)) {
            $errors[] = 'Slug do clube é obrigatório';
        } elseif (!preg_match('/^[a-z0-9-]+$/', $slug)) {
            $errors[] = 'Slug deve conter apenas letras minúsculas, números e hífens';
        }

        if (empty($adminEmail) || !filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email válido é obrigatório';
        }

        if (strlen($adminPassword) < 8) {
            $errors[] = 'Senha deve ter no mínimo 8 caracteres';
        }

        if (empty($adminName)) {
            $errors[] = 'Nome do administrador é obrigatório';
        }

        // Check if slug exists
        $existing = db_fetch_one("SELECT id FROM tenants WHERE slug = ?", [$slug]);
        if ($existing) {
            $errors[] = 'Este slug já está em uso';
        }

        if (!empty($errors)) {
            $this->json(['success' => false, 'errors' => $errors], 400);
            return;
        }

        try {
            // Create tenant
            $tenantId = db_insert('tenants', [
                'slug' => $slug,
                'name' => $clubName,
                'status' => 'active',
            ]);

            // Setup all official roles for the new club
            $syncStats = \App\Services\RoleService::syncTenant($tenantId);
            $roleId = $syncStats['roles']['admin'] ?? 0;

            // Create admin user
            db_insert('users', [
                'tenant_id' => $tenantId,
                'role_id' => $roleId,
                'email' => $adminEmail,
                'password_hash' => password_hash($adminPassword, PASSWORD_DEFAULT),
                'name' => $adminName,
                'status' => 'active',
                'xp_points' => 0,
            ]);

            $this->json([
                'success' => true,
                'message' => 'Clube cadastrado com sucesso!',
                'redirect' => base_url($slug . '/login'),
            ]);

        } catch (\Exception $e) {
            $this->json(['success' => false, 'error' => 'Erro ao cadastrar: ' . $e->getMessage()], 500);
        }
    }

    private function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}
