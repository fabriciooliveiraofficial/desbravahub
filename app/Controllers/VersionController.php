<?php
/**
 * Version Controller
 * 
 * Handles version check endpoints and admin management.
 */

namespace App\Controllers;

use App\Core\App;
use App\Services\VersionService;
use App\Services\FeatureFlagService;

class VersionController
{
    private VersionService $versionService;
    private FeatureFlagService $featureFlagService;

    public function __construct()
    {
        $this->versionService = new VersionService();
        $this->featureFlagService = new FeatureFlagService();
    }

    /**
     * Public endpoint: Check for updates
     */
    public function check(): void
    {
        $tenant = App::tenant();
        $currentVersion = $_GET['version'] ?? '1.0.0';

        $result = $this->versionService->checkForUpdate($tenant['id'], $currentVersion);

        $this->json($result);
    }

    /**
     * Get current version info
     */
    public function current(): void
    {
        $tenant = App::tenant();
        $version = $this->versionService->getTenantVersion($tenant['id']);

        $this->json([
            'version' => $version['version'],
            'changelog' => $version['changelog'] ?? null,
        ]);
    }

    /**
     * Admin: List all versions
     */
    public function index(): void
    {
        if (!can('versions.view')) {
            $this->jsonError('Permissão negada', 403);
            return;
        }

        $versions = $this->versionService->getAllVersions();
        $this->json(['versions' => $versions]);
    }

    /**
     * Admin: Create new version
     */
    public function create(): void
    {
        if (!can('versions.create')) {
            $this->jsonError('Permissão negada', 403);
            return;
        }

        $version = $_POST['version'] ?? '';
        if (empty($version)) {
            $this->jsonError('Version required', 400);
            return;
        }

        $id = $this->versionService->createVersion([
            'version' => $version,
            'changelog' => $_POST['changelog'] ?? null,
            'breaking_changes' => $_POST['breaking_changes'] ?? null,
            'min_required_version' => $_POST['min_required_version'] ?? null,
        ]);

        $this->json(['success' => true, 'id' => $id]);
    }

    /**
     * Admin: Promote version
     */
    public function promote(array $params): void
    {
        if (!can('versions.promote')) {
            $this->jsonError('Permissão negada', 403);
            return;
        }

        $versionId = (int) $params['id'];
        $status = $_POST['status'] ?? '';

        if (!$this->versionService->promoteVersion($versionId, $status)) {
            $this->jsonError('Failed to promote version', 400);
            return;
        }

        $this->json(['success' => true]);
    }

    /**
     * Admin: Rollout to tenant
     */
    public function rollout(): void
    {
        if (!can('versions.rollout')) {
            $this->jsonError('Permissão negada', 403);
            return;
        }

        $tenantId = (int) ($_POST['tenant_id'] ?? 0);
        $versionId = (int) ($_POST['version_id'] ?? 0);

        if (!$tenantId || !$versionId) {
            $this->jsonError('Tenant and version required', 400);
            return;
        }

        $result = $this->versionService->rolloutToTenant($tenantId, $versionId);
        $this->json($result);
    }

    /**
     * Admin: Rollback tenant
     */
    public function rollback(): void
    {
        if (!can('versions.rollout')) {
            $this->jsonError('Permissão negada', 403);
            return;
        }

        $tenantId = (int) ($_POST['tenant_id'] ?? App::tenantId());

        $result = $this->versionService->rollbackTenant($tenantId);
        $this->json($result);
    }

    /**
     * Admin: Canary release
     */
    public function canary(): void
    {
        if (!can('versions.rollout')) {
            $this->jsonError('Permissão negada', 403);
            return;
        }

        $versionId = (int) ($_POST['version_id'] ?? 0);
        $percentage = (int) ($_POST['percentage'] ?? 10);

        $result = $this->versionService->canaryRelease($versionId, $percentage);
        $this->json($result);
    }

    /**
     * Get feature flags for current tenant
     */
    public function featureFlags(): void
    {
        $tenantId = App::tenantId();
        $flags = $this->featureFlagService->getTenantFlags($tenantId);

        // Only return enabled flags to non-admins
        if (!can('features.manage')) {
            $flags = array_filter($flags, function ($f) {
                return $f['tenant_enabled'] ?? $f['is_enabled'];
            });
            $flags = array_map(function ($f) {
                return ['key' => $f['key'], 'enabled' => true];
            }, $flags);
        }

        $this->json(['flags' => array_values($flags)]);
    }

    /**
     * Check single feature flag
     */
    public function checkFeature(array $params): void
    {
        $key = $params['key'] ?? '';
        $enabled = $this->featureFlagService->isEnabled($key);

        $this->json(['key' => $key, 'enabled' => $enabled]);
    }

    /**
     * Admin: Update feature flag
     */
    public function updateFeature(array $params): void
    {
        if (!can('features.manage')) {
            $this->jsonError('Permissão negada', 403);
            return;
        }

        $flagId = (int) $params['id'];

        $result = $this->featureFlagService->update($flagId, [
            'is_enabled' => isset($_POST['is_enabled']) ? (bool) $_POST['is_enabled'] : null,
            'rollout_percentage' => isset($_POST['rollout_percentage']) ? (int) $_POST['rollout_percentage'] : null,
        ]);

        $this->json(['success' => $result]);
    }

    /**
     * Admin: Set tenant feature override
     */
    public function setTenantFeature(): void
    {
        if (!can('features.manage')) {
            $this->jsonError('Permissão negada', 403);
            return;
        }

        $tenantId = (int) ($_POST['tenant_id'] ?? App::tenantId());
        $flagId = (int) ($_POST['flag_id'] ?? 0);
        $enabled = (bool) ($_POST['enabled'] ?? false);

        $this->featureFlagService->setTenantOverride($tenantId, $flagId, $enabled);

        $this->json(['success' => true]);
    }

    private function json(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    private function jsonError(string $message, int $code = 400): void
    {
        http_response_code($code);
        $this->json(['error' => $message]);
    }
}
