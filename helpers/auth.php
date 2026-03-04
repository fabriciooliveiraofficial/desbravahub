<?php
/**
 * Authentication Helper Functions
 * 
 * Global helpers for auth and permissions.
 */

use App\Core\App;
use App\Services\PermissionService;

/**
 * Get the current authenticated user
 * 
 * @return array|null User data or null if not authenticated
 */
function auth(): ?array
{
    return App::user();
}

/**
 * Get the current tenant
 * 
 * @return array|null Tenant data or null if not set
 */
function tenant(): ?array
{
    return App::tenant();
}

/**
 * Get the current tenant ID
 * 
 * @return int|null Tenant ID or null if not set
 */
function tenant_id(): ?int
{
    return App::tenantId();
}

/**
 * Check if user is logged in
 * 
 * @return bool
 */
function is_logged_in(): bool
{
    return App::isAuthenticated();
}

/**
 * Check if user has a permission
 * 
 * @param string $permission Permission key
 * @return bool
 */
function can(string $permission): bool
{
    static $service = null;
    if ($service === null) {
        $service = new PermissionService();
    }
    return $service->can($permission);
}

/**
 * Check if user has any of the given permissions
 * 
 * @param array $permissions Permission keys
 * @return bool
 */
function can_any(array $permissions): bool
{
    static $service = null;
    if ($service === null) {
        $service = new PermissionService();
    }
    return $service->canAny($permissions);
}

/**
 * Check if user has all of the given permissions
 * 
 * @param array $permissions Permission keys
 * @return bool
 */
function can_all(array $permissions): bool
{
    static $service = null;
    if ($service === null) {
        $service = new PermissionService();
    }
    return $service->canAll($permissions);
}

/**
 * Check if user has a specific role
 * 
 * @param string $role Role name
 * @return bool
 */
function has_role(string $role): bool
{
    $user = auth();
    if (!$user) {
        return false;
    }
    return ($user['role_name'] ?? '') === $role;
}

/**
 * Check if user is admin
 */
function is_admin(): bool
{
    return has_role('admin');
}

/**
 * Check if user is director
 */
function is_director(): bool
{
    return has_role('director');
}

/**
 * Check if user is pathfinder
 */
function is_pathfinder(): bool
{
    return has_role('pathfinder');
}

/**
 * Check if a user's profile is complete (Mandatory for Pathfinders)
 */
function is_profile_complete(?array $user = null): bool
{
    $user = $user ?: auth();
    if (!$user) return false;
    
    // Only Pathfinders are required to complete the full registry book for access
    if (!is_pathfinder()) return true;

    // Standard mandatory fields in 'users' table
    if (empty($user['name']) || empty($user['birth_date']) || empty($user['phone'])) {
        return false;
    }

    // Extended mandatory fields in 'user_profiles' table
    try {
        $profile = db_fetch_one("SELECT * FROM user_profiles WHERE user_id = ?", [$user['id']]);
        if (!$profile) return false;

        $mandatory = [
            'address', 
            'phone_emergency', 
            'school_grade', 
            'blood_type', 
            'rh_factor', 
            'tetanus_vaccine'
        ];

        foreach ($mandatory as $field) {
            if (empty(trim($profile[$field] ?? ''))) {
                return false;
            }
        }

        return true;
    } catch (\Exception $e) {
        return false;
    }
}

/**
 * Get CSRF token
 */
function csrf_token(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Generate CSRF hidden field
 */
function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . htmlspecialchars(csrf_token()) . '">';
}

/**
 * Verify CSRF token
 */
function verify_csrf(string $token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
