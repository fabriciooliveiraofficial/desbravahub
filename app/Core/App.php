<?php
/**
 * Application Container
 * 
 * Simple service container for dependency injection.
 */

namespace App\Core;

class App
{
    private static array $instances = [];
    private static ?int $tenantId = null;
    private static ?array $tenant = null;
    private static ?array $user = null;

    /**
     * Set a shared instance
     */
    public static function set(string $key, mixed $value): void
    {
        self::$instances[$key] = $value;
    }

    /**
     * Get a shared instance
     */
    public static function get(string $key): mixed
    {
        return self::$instances[$key] ?? null;
    }

    /**
     * Set current tenant
     */
    public static function setTenant(?array $tenant): void
    {
        self::$tenant = $tenant;
        self::$tenantId = $tenant['id'] ?? null;
    }

    /**
     * Get current tenant
     */
    public static function tenant(): ?array
    {
        return self::$tenant;
    }

    /**
     * Get current tenant ID
     */
    public static function tenantId(): ?int
    {
        return self::$tenantId;
    }

    /**
     * Set authenticated user
     */
    public static function setUser(?array $user): void
    {
        self::$user = $user;
    }

    /**
     * Get authenticated user
     */
    public static function user(): ?array
    {
        return self::$user;
    }

    /**
     * Check if user is authenticated
     */
    public static function isAuthenticated(): bool
    {
        return self::$user !== null;
    }
}
