<?php
/**
 * Permission Service
 * 
 * Handles role-based access control (RBAC).
 */

namespace App\Services;

use App\Core\App;

class PermissionService
{
    private array $permissionCache = [];

    /**
     * Check if current user has a permission
     */
    public function can(string $permission): bool
    {
        $user = App::user();
        if (!$user) {
            return false;
        }

        return $this->userCan($user['id'], $permission);
    }

    /**
     * Check if user has a specific permission
     */
    public function userCan(int $userId, string $permission): bool
    {
        $permissions = $this->getUserPermissions($userId);
        return in_array($permission, $permissions);
    }

    /**
     * Check if user has any of the given permissions
     */
    public function canAny(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->can($permission)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if user has all of the given permissions
     */
    public function canAll(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->can($permission)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get all permissions for a user
     */
    public function getUserPermissions(int $userId): array
    {
        if (isset($this->permissionCache[$userId])) {
            return $this->permissionCache[$userId];
        }

        $rows = db_fetch_all(
            "SELECT p.key 
             FROM permissions p
             JOIN role_permissions rp ON p.id = rp.permission_id
             JOIN users u ON u.role_id = rp.role_id
             WHERE u.id = ?",
            [$userId]
        );

        $permissions = array_column($rows, 'key');
        $this->permissionCache[$userId] = $permissions;

        return $permissions;
    }

    /**
     * Get all permissions for a role
     */
    public function getRolePermissions(int $roleId): array
    {
        $rows = db_fetch_all(
            "SELECT p.key, p.name, p.group 
             FROM permissions p
             JOIN role_permissions rp ON p.id = rp.permission_id
             WHERE rp.role_id = ?
             ORDER BY p.group, p.name",
            [$roleId]
        );

        return $rows;
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $roleName): bool
    {
        $user = App::user();
        if (!$user) {
            return false;
        }

        return ($user['role_name'] ?? '') === $roleName;
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user is director
     */
    public function isDirector(): bool
    {
        return $this->hasRole('director');
    }

    /**
     * Check if user is pathfinder
     */
    public function isPathfinder(): bool
    {
        return $this->hasRole('pathfinder');
    }

    /**
     * Clear permission cache
     */
    public function clearCache(): void
    {
        $this->permissionCache = [];
    }
}
