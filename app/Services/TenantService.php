<?php
/**
 * Tenant Service
 * 
 * Handles tenant resolution and management.
 */

namespace App\Services;

class TenantService
{
    /**
     * Find tenant by slug
     */
    public function findBySlug(string $slug): ?array
    {
        return db_fetch_one(
            "SELECT * FROM tenants WHERE slug = ? AND status = 'active'",
            [$slug]
        );
    }

    /**
     * Find tenant by ID
     */
    public function findById(int $id): ?array
    {
        return db_fetch_one(
            "SELECT * FROM tenants WHERE id = ?",
            [$id]
        );
    }

    /**
     * Check if tenant exists and is active
     */
    public function isActive(string $slug): bool
    {
        $tenant = $this->findBySlug($slug);
        return $tenant !== null && $tenant['status'] === 'active';
    }

    /**
     * Get tenant settings
     */
    public function getSettings(int $tenantId): array
    {
        $tenant = $this->findById($tenantId);
        if (!$tenant || empty($tenant['settings'])) {
            return [];
        }
        return json_decode($tenant['settings'], true) ?? [];
    }
}
