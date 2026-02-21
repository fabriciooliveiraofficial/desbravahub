<?php
/**
 * Tenant Resolution Middleware
 * 
 * Extracts tenant from URL and validates it.
 */

namespace App\Middleware;

use App\Core\App;
use App\Services\TenantService;

class TenantMiddleware
{
    /**
     * Handle the middleware
     */
    public function handle(array $params, array $mwParams = []): bool
    {
        // Bypass for system/migration routes
        $uri = $_SERVER['REQUEST_URI'];
        if (strpos($uri, 'run-migration') !== false || strpos($uri, 'database/') !== false) {
            return true;
        }

        // Check if we have a tenant slug in the route params
        $slug = $params['tenant'] ?? null;

        if (!$slug) {
            // Try to extract from URL path
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $segments = explode('/', trim($path, '/'));
            $slug = $segments[0] ?? null;
        }

        if (!$slug) {
            $this->forbid('No tenant specified');
            return false;
        }

        $tenantService = new TenantService();
        $tenant = $tenantService->findBySlug($slug);

        if (!$tenant) {
            $this->forbid('Invalid tenant');
            return false;
        }

        if ($tenant['status'] !== 'active') {
            $this->forbid('Tenant is not active');
            return false;
        }

        // Set tenant in application context
        App::setTenant($tenant);

        return true;
    }

    /**
     * Send forbidden response
     */
    private function forbid(string $message): void
    {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['error' => $message]);
    }
}
