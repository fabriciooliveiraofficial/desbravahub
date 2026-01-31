<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

class TenantTest extends TestCase
{
    /**
     * A basic test to verify tenant identification.
     */
    public function test_tenant_identification_from_url(): void
    {
        // This is a placeholder for actual integration test logic.
        // In a real MVC test, we would mock the request or use a test client.
        
        $slug = 'clube-demo';
        $uri = "/{$slug}/login";
        
        // Simulating the routing logic in index.php
        $parts = explode('/', ltrim($uri, '/'));
        $identifiedTenant = $parts[0] ?? null;

        $this->assertEquals($slug, $identifiedTenant);
    }
}
