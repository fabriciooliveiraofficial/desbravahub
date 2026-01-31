<?php
/**
 * Unit Service
 * 
 * Manages units (unidades) within a club.
 */

namespace App\Services;

use App\Core\App;

class UnitService
{
    /**
     * Get all units for current tenant
     */
    public static function getAll(): array
    {
        $tenantId = App::tenantId();

        return db_fetch_all(
            "SELECT u.*, 
                    (SELECT COUNT(*) FROM users WHERE unit_id = u.id AND deleted_at IS NULL) as member_count,
                    (SELECT COUNT(*) FROM unit_counselors WHERE unit_id = u.id) as counselor_count
             FROM units u 
             WHERE u.tenant_id = ? AND u.status = 'active'
             ORDER BY u.name ASC",
            [$tenantId]
        );
    }

    /**
     * Get unit by ID
     */
    public static function getById(int $id): ?array
    {
        $tenantId = App::tenantId();

        return db_fetch_one(
            "SELECT * FROM units WHERE id = ? AND tenant_id = ?",
            [$id, $tenantId]
        );
    }

    /**
     * Create a new unit
     */
    public static function create(array $data): int
    {
        $tenantId = App::tenantId();

        return db_insert('units', [
            'tenant_id' => $tenantId,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'color' => $data['color'] ?? null,
            'mascot' => $data['mascot'] ?? null,
            'motto' => $data['motto'] ?? null,
            'status' => 'active',
        ]);
    }

    /**
     * Update a unit
     */
    public static function update(int $id, array $data): void
    {
        $tenantId = App::tenantId();

        db_update('units', [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'color' => $data['color'] ?? null,
            'mascot' => $data['mascot'] ?? null,
            'motto' => $data['motto'] ?? null,
        ], 'id = ? AND tenant_id = ?', [$id, $tenantId]);
    }

    /**
     * Delete (deactivate) a unit
     */
    public static function delete(int $id): void
    {
        $tenantId = App::tenantId();

        db_update('units', [
            'status' => 'inactive',
        ], 'id = ? AND tenant_id = ?', [$id, $tenantId]);

        // Remove unit_id from users
        db_query(
            "UPDATE users SET unit_id = NULL WHERE unit_id = ? AND tenant_id = ?",
            [$id, $tenantId]
        );
    }

    /**
     * Get counselors for a unit
     */
    public static function getCounselors(int $unitId): array
    {
        return db_fetch_all(
            "SELECT u.id, u.name, u.email, u.avatar_url, uc.is_primary
             FROM unit_counselors uc
             JOIN users u ON uc.user_id = u.id
             WHERE uc.unit_id = ?
             ORDER BY uc.is_primary DESC, u.name ASC",
            [$unitId]
        );
    }

    /**
     * Get members (pathfinders) for a unit
     */
    public static function getMembers(int $unitId): array
    {
        return db_fetch_all(
            "SELECT u.id, u.name, u.email, u.avatar_url, u.xp_points
             FROM users u
             WHERE u.unit_id = ? AND u.deleted_at IS NULL
             ORDER BY u.name ASC",
            [$unitId]
        );
    }

    /**
     * Assign counselor to unit
     */
    public static function assignCounselor(int $unitId, int $userId, bool $isPrimary = false): void
    {
        // Check if already assigned
        $existing = db_fetch_one(
            "SELECT * FROM unit_counselors WHERE unit_id = ? AND user_id = ?",
            [$unitId, $userId]
        );

        if ($existing) {
            // Update is_primary
            db_update('unit_counselors', [
                'is_primary' => $isPrimary ? 1 : 0,
            ], 'unit_id = ? AND user_id = ?', [$unitId, $userId]);
        } else {
            db_insert('unit_counselors', [
                'unit_id' => $unitId,
                'user_id' => $userId,
                'is_primary' => $isPrimary ? 1 : 0,
            ]);
        }
    }

    /**
     * Remove counselor from unit
     */
    public static function removeCounselor(int $unitId, int $userId): void
    {
        db_query(
            "DELETE FROM unit_counselors WHERE unit_id = ? AND user_id = ?",
            [$unitId, $userId]
        );
    }

    /**
     * Assign pathfinder to unit
     */
    public static function assignMember(int $unitId, int $userId): void
    {
        $tenantId = App::tenantId();

        db_query(
            "UPDATE users SET unit_id = ? WHERE id = ? AND tenant_id = ?",
            [$unitId, $userId, $tenantId]
        );
    }

    /**
     * Remove pathfinder from unit
     */
    public static function removeMember(int $userId): void
    {
        $tenantId = App::tenantId();

        db_query(
            "UPDATE users SET unit_id = NULL WHERE id = ? AND tenant_id = ?",
            [$userId, $tenantId]
        );
    }

    /**
     * Get available counselors (not assigned to any unit)
     */
    public static function getAvailableCounselors(): array
    {
        $tenantId = App::tenantId();

        return db_fetch_all(
            "SELECT u.id, u.name, u.email
             FROM users u
             JOIN roles r ON u.role_id = r.id
             WHERE u.tenant_id = ? 
               AND u.deleted_at IS NULL
               AND r.name = 'counselor'
             ORDER BY u.name ASC",
            [$tenantId]
        );
    }

    /**
     * Get available pathfinders (not assigned to any unit)
     */
    public static function getAvailablePathfinders(): array
    {
        $tenantId = App::tenantId();

        return db_fetch_all(
            "SELECT u.id, u.name, u.email
             FROM users u
             JOIN roles r ON u.role_id = r.id
             WHERE u.tenant_id = ? 
               AND u.deleted_at IS NULL
               AND u.unit_id IS NULL
               AND r.name = 'pathfinder'
             ORDER BY u.name ASC",
            [$tenantId]
        );
    }

    /**
     * Get unit for a counsel (user)
     */
    public static function getUnitForCounselor(int $userId): ?array
    {
        return db_fetch_one(
            "SELECT u.* FROM units u
             JOIN unit_counselors uc ON u.id = uc.unit_id
             WHERE uc.user_id = ?",
            [$userId]
        );
    }
}
