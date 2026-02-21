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
        $user = App::user();

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

            // Redirect to home/dashboard if standard web request
            $_SESSION['flash_error'] = 'Acesso negado à área de Super Admin.';
            
            // Try to redirect back to the tenant dashboard if tenant exists in URL
            $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            preg_match('#^/([^/]+)/#', $uri, $matches);
            
            if (!empty($matches[1]) && $matches[1] !== 'super-admin') {
                $tenant = $matches[1];
                header("Location: /{$tenant}/admin/dashboard");
            } else {
                header("Location: /");
            }
            return false;
        }

        return true;
    }
}
