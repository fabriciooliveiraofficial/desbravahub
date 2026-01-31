<?php
/**
 * Feature Flag Service
 * 
 * Manages feature flags for gradual rollouts.
 */

namespace App\Services;

use App\Core\App;

class FeatureFlagService
{
    private static array $cache = [];

    /**
     * Check if a feature is enabled for current context
     */
    public function isEnabled(string $key): bool
    {
        $tenantId = App::tenantId();
        $userId = App::user()['id'] ?? null;

        return $this->check($key, $tenantId, $userId);
    }

    /**
     * Check feature flag for specific context
     */
    public function check(string $key, ?int $tenantId = null, ?int $userId = null): bool
    {
        $cacheKey = "{$key}_{$tenantId}_{$userId}";

        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        // Get global flag
        $flag = db_fetch_one(
            "SELECT * FROM feature_flags WHERE key = ?",
            [$key]
        );

        if (!$flag) {
            self::$cache[$cacheKey] = false;
            return false;
        }

        // Check if globally enabled
        if ($flag['default_enabled']) {
            self::$cache[$cacheKey] = true;
            return true;
        }

        // Check tenant-specific override
        if ($tenantId) {
            $tenantFlag = db_fetch_one(
                "SELECT enabled FROM tenant_feature_flags WHERE feature_flag_id = ? AND tenant_id = ?",
                [$flag['id'], $tenantId]
            );

            if ($tenantFlag) {
                self::$cache[$cacheKey] = (bool) $tenantFlag['enabled'];
                return self::$cache[$cacheKey];
            }
        }

        // No override, use default
        self::$cache[$cacheKey] = (bool) ($flag['default_enabled'] ?? false);
        return self::$cache[$cacheKey];
    }

    /**
     * Get all feature flags
     */
    public function getAll(): array
    {
        return db_fetch_all(
            "SELECT * FROM feature_flags ORDER BY name"
        );
    }

    /**
     * Get flags for a tenant (with overrides)
     */
    public function getTenantFlags(int $tenantId): array
    {
        return db_fetch_all(
            "SELECT 
                ff.*, 
                ff.default_enabled as is_enabled,
                0 as rollout_percentage,
                tff.enabled as tenant_enabled, 
                tff.id as override_id
             FROM feature_flags ff
             LEFT JOIN tenant_feature_flags tff ON ff.id = tff.feature_flag_id AND tff.tenant_id = ?
             ORDER BY ff.name",
            [$tenantId]
        );
    }

    /**
     * Create a new feature flag
     */
    public function create(array $data): int
    {
        return db_insert('feature_flags', [
            'key' => $data['key'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'group_name' => $data['group_name'] ?? 'general',
            'is_enabled' => $data['is_enabled'] ?? 0,
            'rollout_percentage' => $data['rollout_percentage'] ?? 0,
        ]);
    }

    /**
     * Update a feature flag
     */
    public function update(int $id, array $data): bool
    {
        $allowedFields = ['name', 'description', 'group_name', 'is_enabled', 'rollout_percentage'];
        $updateData = array_intersect_key($data, array_flip($allowedFields));

        if (empty($updateData)) {
            return false;
        }

        $result = db_update('feature_flags', $updateData, 'id = ?', [$id]) > 0;

        // Clear cache
        self::$cache = [];

        return $result;
    }

    /**
     * Set tenant-specific override
     */
    public function setTenantOverride(int $tenantId, int $flagId, bool $enabled): void
    {
        $existing = db_fetch_one(
            "SELECT id FROM tenant_feature_flags WHERE tenant_id = ? AND feature_flag_id = ?",
            [$tenantId, $flagId]
        );

        if ($existing) {
            db_update('tenant_feature_flags', [
                'enabled' => $enabled ? 1 : 0,
            ], 'id = ?', [$existing['id']]);
        } else {
            db_insert('tenant_feature_flags', [
                'tenant_id' => $tenantId,
                'feature_flag_id' => $flagId,
                'enabled' => $enabled ? 1 : 0,
            ]);
        }

        // Clear cache
        self::$cache = [];
    }

    /**
     * Remove tenant override (inherit from global)
     */
    public function removeTenantOverride(int $tenantId, int $flagId): void
    {
        db_delete('tenant_feature_flags', 'tenant_id = ? AND feature_flag_id = ?', [$tenantId, $flagId]);
        self::$cache = [];
    }

    /**
     * Gradually increase rollout percentage
     */
    public function increaseRollout(int $flagId, int $increment = 10): array
    {
        $flag = db_fetch_one("SELECT * FROM feature_flags WHERE id = ?", [$flagId]);

        if (!$flag) {
            return ['success' => false, 'error' => 'Flag not found'];
        }

        $newPercentage = min(100, $flag['rollout_percentage'] + $increment);

        db_update('feature_flags', [
            'rollout_percentage' => $newPercentage,
        ], 'id = ?', [$flagId]);

        self::$cache = [];

        return [
            'success' => true,
            'previous' => $flag['rollout_percentage'],
            'new' => $newPercentage,
        ];
    }

    /**
     * Get feature flag by key
     */
    public function getByKey(string $key): ?array
    {
        return db_fetch_one("SELECT * FROM feature_flags WHERE key = ?", [$key]);
    }
}
