<?php
/**
 * Super Admin Middleware
 * 
 * Protects routes that require `is_superadmin` = 1.
 */

namespace App\Middleware;

use App\Core\App;

class SuperAdminMiddleware
{
    /**
     * Handle the request
     */
    public function handle(array $params = [], array $mwParams = []): bool
    {
        $authService = new \App\Services\AuthService();
        $token = $authService->getTokenFromRequest();
        $user = null;

        if ($token) {
            $user = $authService->validateSession($token);
            if ($user) {
                App::setUser($user);
            }
        } else {
            // Fallback to check if AuthMiddleware already set it (just in case they are stacked accidentally)
            $user = App::user();
        }

        // Check if user is logged in and has the super admin flag
        if (!$user || !isset($user['is_superadmin']) || $user['is_superadmin'] != 1) {
            
            // Return JSON response for API/AJAX requests
            if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'error' => 'Acesso negado. Você precisa ser um Super Admin.'
                ]);
                return false;
            }

            // Redirect to standalone login for standard web request
            $_SESSION['flash_error'] = 'Acesso exclusivo para Super Admin.';
            header("Location: /super-admin/login");
            return false;
        }

        return true;
    }
}
