<?php
/**
 * Authentication Middleware
 * 
 * Validates user session and enforces tenant isolation.
 */

namespace App\Middleware;

use App\Core\App;
use App\Services\AuthService;

class AuthMiddleware
{
    /**
     * Handle the middleware
     */
    public function handle(array $params, array $mwParams = []): bool
    {
        $authService = new AuthService();

        // Get token from request
        $tenant = App::tenant();
        $token = $authService->getTokenFromRequest($tenant['slug'] ?? null);

        if (!$token) {
            $this->unauthorized('Authentication required');
            return false;
        }

        // Validate session
        $user = $authService->validateSession($token);

        if (!$user) {
            $this->unauthorized('Invalid or expired session');
            return false;
        }

        // Refresh session to keep it alive (Rolling Session for PWA persistence)
        $authService->refreshSession(
            $token, 
            $tenant ? '/' . $tenant['slug'] . '/' : '/', 
            $tenant['slug'] ?? null
        );

        // Verify user belongs to current tenant
        $tenant = App::tenant();
        if ($tenant && $user['tenant_id'] != $tenant['id']) {
            error_log("AuthMiddleware - Tenant mismatch (session isolation). User tenant: " . $user['tenant_id'] . ", Requested tenant: " . $tenant['id'] . " (User: " . ($user['email'] ?? 'unknown') . ")");
            
            // For AJAX/HTMX requests, return a 401 to trigger re-authentication
            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) 
                    || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
                    || !empty($_SERVER['HTTP_HX_REQUEST']);
            
            if ($isAjax) {
                http_response_code(401);
                header('Content-Type: application/json');
                $response = ['error' => 'Sessão inválida para este clube. Faça login novamente.'];
                if (!$this->isBackgroundRequest()) {
                    $response['redirect'] = base_url($tenant['slug'] . '/login');
                }
                echo json_encode($response);
                return false;
            }
            
            // For normal page requests, redirect to the tenant's login page
            header('Location: ' . base_url($tenant['slug'] . '/login'));
            exit;
        }

        // Set user in application context
        App::setUser($user);

        // Prevent browser from caching authenticated pages
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Pathfinder Profile Lock:
        // If user is a pathfinder and has not completed the registry book,
        // redirect them to the profile page automatically.
        if (is_pathfinder() && !is_profile_complete($user)) {
            $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $slug = $tenant['slug'];
            
            // Define whitelisted paths that can be accessed even with incomplete profile
            $allowedPaths = [
                "/{$slug}/perfil",
                "/{$slug}/perfil/salvar",
                "/{$slug}/logout",
                "/{$slug}/api/sos/trigger"
            ];

            // Clean up URI for comparison (remove trailing slashes)
            $cleanUri = rtrim($uri, '/');

            if (!in_array($cleanUri, $allowedPaths)) {
                header("Location: " . base_url("{$slug}/perfil?incomplete=1"));
                exit;
            }
        }

        return true;
    }

    /**
     * Send unauthorized response
     */
    private function unauthorized(string $message): void
    {
        if ($this->isAjaxRequest()) {
            http_response_code(401);
            header('Content-Type: application/json');

            $tenant = App::tenant();
            $redirect = $tenant ? base_url($tenant['slug'] . '/login') : base_url('login');

            $response = ['error' => $message];
            if (!$this->isBackgroundRequest()) {
                $response['redirect'] = $redirect;
            }
            echo json_encode($response);
            exit;
        }

        $tenant = App::tenant();
        $redirect = $tenant ? base_url($tenant['slug'] . '/login') : base_url('login');
        header('Location: ' . $redirect);
        exit;
    }

    /**
     * Send forbidden response
     */
    private function forbidden(string $message): void
    {
        if ($this->isAjaxRequest()) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => $message]);
            exit;
        }

        // Send a simple forbidden page or redirect to dashboard with an error parameter
        $tenant = App::tenant();
        $redirect = $tenant ? base_url($tenant['slug'] . '/dashboard') : base_url();
        header('Location: ' . $redirect);
        exit;
    }

    /**
     * Determine if request is AJAX/HTMX
     */
    private function isAjaxRequest(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
            || !empty($_SERVER['HTTP_HX_REQUEST']);
    }

    /**
     * Determine if this is a background/fire-and-forget request (e.g. notification read receipts).
     * Background requests should receive a 401 error WITHOUT a redirect URL so the client
     * never performs a window-level navigation on a background failure.
     */
    private function isBackgroundRequest(): bool
    {
        if (!empty($_SERVER['HTTP_X_BACKGROUND_REQUEST'])) {
            return true;
        }
        
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        return strpos($uri, '/api/notifications') !== false 
            || strpos($uri, '/api/push') !== false 
            || substr($uri, -7) === '/unread';
    }
}
