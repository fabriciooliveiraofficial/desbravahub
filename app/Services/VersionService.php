<?php
/**
 * Version Service
 * 
 * Manages application versions and tenant version rollouts.
 */

namespace App\Services;

use App\Core\App;

class VersionService
{
    /**
     * Get current global (latest stable) version
     */
    public function getCurrentVersion(): ?array
    {
        return db_fetch_one(
            "SELECT * FROM app_versions WHERE is_active = 1 ORDER BY released_at DESC LIMIT 1"
        );
    }

    /**
     * Get version for a specific tenant
     */
    public function getTenantVersion(int $tenantId): array
    {
        // Check tenant-specific version
        $tenantVersion = db_fetch_one(
            "SELECT tv.*, av.version_code, av.release_notes
             FROM tenant_versions tv
             JOIN app_versions av ON tv.app_version_id = av.id
             WHERE tv.tenant_id = ? AND tv.rollout_status = 'active'
             ORDER BY tv.activated_at DESC LIMIT 1",
            [$tenantId]
        );

        if ($tenantVersion) {
            return $tenantVersion;
        }

        // Fallback to global stable
        $global = $this->getCurrentVersion();
        return $global ?? ['version_code' => '1.0.0', 'id' => 1];
    }

    /**
     * Get all versions
     */
    public function getAllVersions(): array
    {
        return db_fetch_all(
            "SELECT 
                *,
                version_code as version,
                release_notes as changelog,
                IF(is_active = 1, 'stable', 'deprecated') as status
             FROM app_versions 
             ORDER BY created_at DESC"
        );
    }

    /**
     * Create a new version
     */
    public function createVersion(array $data): int
    {
        return db_insert('app_versions', [
            'version' => $data['version'],
            'changelog' => $data['changelog'] ?? null,
            'breaking_changes' => $data['breaking_changes'] ?? null,
            'min_required_version' => $data['min_required_version'] ?? null,
            'status' => 'beta', // Start as beta
        ]);
    }

    /**
     * Promote version status
     */
    public function promoteVersion(int $versionId, string $newStatus): bool
    {
        if (!in_array($newStatus, ['beta', 'canary', 'stable', 'deprecated'])) {
            return false;
        }

        return db_update('app_versions', [
            'status' => $newStatus,
            'released_at' => $newStatus === 'stable' ? date('Y-m-d H:i:s') : null,
        ], 'id = ?', [$versionId]) > 0;
    }

    /**
     * Roll out version to a tenant
     */
    public function rolloutToTenant(int $tenantId, int $versionId): array
    {
        // Deactivate current version
        db_query(
            "UPDATE tenant_versions SET status = 'inactive' WHERE tenant_id = ? AND status = 'active'",
            [$tenantId]
        );

        // Get previous version for rollback
        $previousVersion = db_fetch_one(
            "SELECT app_version_id FROM tenant_versions WHERE tenant_id = ? ORDER BY activated_at DESC LIMIT 1",
            [$tenantId]
        );

        // Activate new version
        $id = db_insert('tenant_versions', [
            'tenant_id' => $tenantId,
            'app_version_id' => $versionId,
            'status' => 'active',
            'rollback_version_id' => $previousVersion['app_version_id'] ?? null,
        ]);

        return ['success' => true, 'tenant_version_id' => $id];
    }

    /**
     * Rollback tenant to previous version
     */
    public function rollbackTenant(int $tenantId): array
    {
        $current = db_fetch_one(
            "SELECT * FROM tenant_versions WHERE tenant_id = ? AND status = 'active'",
            [$tenantId]
        );

        if (!$current || !$current['rollback_version_id']) {
            return ['success' => false, 'error' => 'No rollback version available'];
        }

        // Deactivate current
        db_update('tenant_versions', ['status' => 'rolled_back'], 'id = ?', [$current['id']]);

        // Activate rollback version
        $id = db_insert('tenant_versions', [
            'tenant_id' => $tenantId,
            'app_version_id' => $current['rollback_version_id'],
            'status' => 'active',
        ]);

        return ['success' => true, 'tenant_version_id' => $id];
    }

    /**
     * Canary release to percentage of tenants
     */
    public function canaryRelease(int $versionId, int $percentage): array
    {
        // Get all active tenants
        $tenants = db_fetch_all(
            "SELECT id FROM tenants WHERE status = 'active'"
        );

        // Calculate how many to update
        $count = (int) ceil(count($tenants) * ($percentage / 100));
        $selected = array_slice($tenants, 0, $count);

        $rolled = [];
        foreach ($selected as $tenant) {
            $result = $this->rolloutToTenant($tenant['id'], $versionId);
            if ($result['success']) {
                $rolled[] = $tenant['id'];
            }
        }

        return [
            'success' => true,
            'total_tenants' => count($tenants),
            'rolled_out' => count($rolled),
            'percentage' => $percentage,
        ];
    }

    /**
     * Check if update is available for client
     */
    public function checkForUpdate(int $tenantId, string $currentVersion): array
    {
        $tenantVersion = $this->getTenantVersion($tenantId);

        $needsUpdate = version_compare($tenantVersion['version'], $currentVersion, '>');

        return [
            'needs_update' => $needsUpdate,
            'current_version' => $currentVersion,
            'latest_version' => $tenantVersion['version'],
            'changelog' => $tenantVersion['changelog'] ?? null,
            'breaking_changes' => $tenantVersion['breaking_changes'] ?? null,
            'force_update' => $this->requiresForceUpdate($currentVersion, $tenantVersion),
        ];
    }

    /**
     * Check if update must be forced
     */
    private function requiresForceUpdate(string $currentVersion, array $targetVersion): bool
    {
        if (empty($targetVersion['min_required_version'])) {
            return false;
        }

        return version_compare($currentVersion, $targetVersion['min_required_version'], '<');
    }
}
