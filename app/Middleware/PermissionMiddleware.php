<?php
/**
 * Permission Middleware
 * 
 * Checks user has required permissions.
 */

namespace App\Middleware;

use App\Services\PermissionService;

class PermissionMiddleware
{
    /**
     * Handle the middleware
     * 
     * @param array $params Route parameters
     * @param array $mwParams Middleware parameters (required permissions)
     */
    public function handle(array $params, array $mwParams = []): bool
    {
        if (empty($mwParams)) {
            return true; // No permissions required
        }

        $permissionService = new PermissionService();

        // Check if user has any of the required permissions
        if (!$permissionService->canAny($mwParams)) {
            error_log("PermissionMiddleware: Insufficient permissions. Required: " . implode(', ', $mwParams));
            $this->forbidden('Insufficient permissions', $mwParams);
            return false;
        }

        return true;
    }

    /**
     * Send forbidden response
     */
    private function forbidden(string $message, array $required = []): void
    {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => $message,
            'required_permissions' => $required
        ]);
    }
}
