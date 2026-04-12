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
            App::jsonResponse(['success' => false, 'error' => 'Invalid tenant'], 400);
            return;
        }

        // Get credentials from request
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validate input
        if (empty($email) || empty($password)) {
            App::jsonResponse(['success' => false, 'error' => 'Email and password are required'], 400);
            return;
        }

        // Attempt authentication
        $user = $this->authService->attempt($email, $password, $tenant['id']);

        if (!$user) {
            App::jsonResponse(['success' => false, 'error' => 'Invalid credentials'], 401);
            return;
        }

        // Create session
        $token = $this->authService->createSession($user['id']);
        $cookiePath = '/' . $tenant['slug'] . '/';
        $this->authService->setAuthCookie($token, $cookiePath, $tenant['slug']);

        // Determine redirect based on role
        $isAdmin = in_array($user['role_name'], ['admin', 'director', 'instructor']);
        $redirectPath = $isAdmin ? '/admin/dashboard' : '/dashboard';

        // Return success (include token for per-tab sessionStorage)
        App::jsonResponse([
            'success' => true,
            'message' => 'Login successful',
            'token' => $token,
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
        $tenant = App::tenant();
        $token = $this->authService->getTokenFromRequest($tenant['slug'] ?? null);
        $cookiePath = $tenant ? '/' . $tenant['slug'] . '/' : '/';

        if ($token) {
            $this->authService->destroySession($token);
            // Clear tenant-specific cookie
            if ($tenant) {
                $this->authService->clearAuthCookie($cookiePath, $tenant['slug']);
                // Also clear legacy global cookie for this tenant to prevent stale sessions
                $this->authService->clearAuthCookie('/', $tenant['slug']);
            }
            // Clear any global legacy cookie
            $this->authService->clearAuthCookie('/');
        }

        $redirectUrl = $tenant ? base_url($tenant['slug'] . '/login') : base_url();

        // POST from JS fetch: return JSON redirect URL.
        // Do NOT send Clear-Site-Data here — the header aborts fetch() response processing in some browsers.
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            App::jsonResponse(['success' => true, 'redirect' => $redirectUrl]);
            return;
        }

        // For full-page navigations: clear HTTP cache on the way out.
        header('Clear-Site-Data: "cache"');

        // HTMX boost intercepts GET links: respond with HX-Redirect so HTMX does a
        // full-page navigation instead of a partial swap.
        if (!empty($_SERVER['HTTP_HX_REQUEST'])) {
            header('HX-Redirect: ' . $redirectUrl);
            http_response_code(200);
            exit;
        }

        // GET fallback (direct URL or non-HTMX browsers)
        header('Location: ' . $redirectUrl);
        exit;
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
            App::jsonResponse(['success' => false, 'error' => 'Invalid tenant'], 400);
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
            App::jsonResponse(['success' => false, 'error' => $errors[0], 'errors' => $errors], 400);
            return;
        }

        // Get pathfinder role
        $role = db_fetch_one(
            "SELECT id FROM roles WHERE tenant_id = ? AND name = 'pathfinder'",
            [$tenant['id']]
        );

        if (!$role) {
            App::jsonResponse(['success' => false, 'error' => 'Registration not available'], 500);
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

        // Referral System: Track conversion if this email was invited
        try {
            \App\Services\ReferralService::handleRegistration($userId, $email, $tenant['id']);
        } catch (\Exception $e) {
            error_log("Referral tracking error (register): " . $e->getMessage());
        }

        // Auto-login
        $cookiePath = '/' . $tenant['slug'] . '/';
        $token = $this->authService->createSession($userId);
        $this->authService->setAuthCookie($token, $cookiePath, $tenant['slug']);

        App::jsonResponse([
            'success' => true,
            'message' => 'Registration successful',
            'token' => $token,
            'user' => ['id' => $userId],
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

}
