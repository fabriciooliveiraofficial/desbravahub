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
        $token = $authService->getTokenFromRequest();

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

        // Verify user belongs to current tenant
        $tenant = App::tenant();
        if ($tenant && $user['tenant_id'] != $tenant['id']) {
            error_log("AuthMiddleware: Tenant mismatch. User tenant: " . $user['tenant_id'] . ", Requested tenant: " . $tenant['id'] . " (User: " . ($user['email'] ?? 'unknown') . ")");
            $this->forbidden('Acesso negado a este clube (Tenant mismatch)');
            return false;
        }

        // Set user in application context
        App::setUser($user);

        return true;
    }

    /**
     * Send unauthorized response
     */
    private function unauthorized(string $message): void
    {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['error' => $message]);
    }

    /**
     * Send forbidden response
     */
    private function forbidden(string $message): void
    {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['error' => $message]);
    }
}
