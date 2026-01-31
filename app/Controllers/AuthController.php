<?php
/**
 * Auth Controller
 * 
 * Handles login, logout, and registration.
 */

namespace App\Controllers;

use App\Core\App;
use App\Services\AuthService;
use App\Services\TenantService;

class AuthController
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    /**
     * Show login page
     */
    public function showLogin(array $params): void
    {
        $tenant = App::tenant();
        require BASE_PATH . '/views/auth/login.php';
    }

    /**
     * Handle login attempt
     */
    public function login(array $params): void
    {
        $tenant = App::tenant();

        if (!$tenant) {
            $this->jsonError('Invalid tenant', 400);
            return;
        }

        // Get credentials from request
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validate input
        if (empty($email) || empty($password)) {
            $this->jsonError('Email and password are required', 400);
            return;
        }

        // Attempt authentication
        $user = $this->authService->attempt($email, $password, $tenant['id']);

        if (!$user) {
            $this->jsonError('Invalid credentials', 401);
            return;
        }

        // Create session
        $token = $this->authService->createSession($user['id']);
        $this->authService->setAuthCookie($token);

        // Determine redirect based on role
        $isAdmin = in_array($user['role_name'], ['admin', 'director', 'instructor']);
        $redirectPath = $isAdmin ? '/admin/dashboard' : '/dashboard';

        // Return success
        $this->jsonSuccess([
            'message' => 'Login successful',
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role_name'],
            ],
            'redirect' => base_url($tenant['slug'] . $redirectPath),
        ]);
    }

    /**
     * Handle logout
     */
    public function logout(array $params): void
    {
        $token = $this->authService->getTokenFromRequest();

        if ($token) {
            $this->authService->destroySession($token);
            $this->authService->clearAuthCookie();
        }

        $tenant = App::tenant();
        $redirectUrl = $tenant ? base_url($tenant['slug'] . '/login') : base_url();

        // Handle both JSON and redirect responses
        if ($this->isJsonRequest()) {
            $this->jsonSuccess(['message' => 'Logged out successfully']);
        } else {
            header('Location: ' . $redirectUrl);
            exit;
        }
    }

    /**
     * Show registration page
     */
    public function showRegister(array $params): void
    {
        $tenant = App::tenant();
        require BASE_PATH . '/views/auth/register.php';
    }

    /**
     * Handle registration
     */
    public function register(array $params): void
    {
        $tenant = App::tenant();

        if (!$tenant) {
            $this->jsonError('Invalid tenant', 400);
            return;
        }

        // Get form data
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        // Validate input
        $errors = $this->validateRegistration($name, $email, $password, $passwordConfirm, $tenant['id']);

        if (!empty($errors)) {
            $this->jsonError($errors[0], 400, ['errors' => $errors]);
            return;
        }

        // Get pathfinder role
        $role = db_fetch_one(
            "SELECT id FROM roles WHERE tenant_id = ? AND name = 'pathfinder'",
            [$tenant['id']]
        );

        if (!$role) {
            $this->jsonError('Registration not available', 500);
            return;
        }

        // Create user
        $userId = db_insert('users', [
            'tenant_id' => $tenant['id'],
            'role_id' => $role['id'],
            'email' => $email,
            'password_hash' => $this->authService->hashPassword($password),
            'name' => $name,
            'xp_points' => 0,
            'level_id' => 1, // Iniciante
            'status' => 'active',
        ]);

        // Auto-login
        $token = $this->authService->createSession($userId);
        $this->authService->setAuthCookie($token);

        $this->jsonSuccess([
            'message' => 'Registration successful',
            'redirect' => base_url($tenant['slug'] . '/dashboard'),
        ]);
    }

    /**
     * Validate registration data
     */
    private function validateRegistration(string $name, string $email, string $password, string $passwordConfirm, int $tenantId): array
    {
        $errors = [];

        if (empty($name) || strlen($name) < 2) {
            $errors[] = 'Name must be at least 2 characters';
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email is required';
        }

        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters';
        }

        if ($password !== $passwordConfirm) {
            $errors[] = 'Passwords do not match';
        }

        // Check email uniqueness within tenant
        if (!empty($email)) {
            $existing = db_fetch_one(
                "SELECT id FROM users WHERE email = ? AND tenant_id = ?",
                [$email, $tenantId]
            );
            if ($existing) {
                $errors[] = 'Email already registered';
            }
        }

        return $errors;
    }

    /**
     * Check if request expects JSON
     */
    private function isJsonRequest(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        return str_contains($accept, 'application/json') || !empty($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    /**
     * Send JSON success response
     */
    private function jsonSuccess(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode(array_merge(['success' => true], $data));
    }

    /**
     * Send JSON error response
     */
    private function jsonError(string $message, int $code = 400, array $extra = []): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(array_merge(['success' => false, 'error' => $message], $extra));
    }
}
