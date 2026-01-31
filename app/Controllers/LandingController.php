<?php
/**
 * Landing Controller
 * 
 * Handles public pages for tenants.
 */

namespace App\Controllers;

use App\Core\App;

class LandingController
{
    /**
     * Show public landing page for tenant
     */
    public function index(array $params): void
    {
        $tenant = App::tenant();

        if (!$tenant) {
            http_response_code(404);
            echo "Clube não encontrado";
            return;
        }

        require BASE_PATH . '/views/public/landing.php';
    }
}
