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

    /**
     * Clean JSON Response
     * 
     * Silences notices and clears buffer to ensure pure JSON delivery.
     */
    public static function jsonResponse(array $data, int $httpCode = 200): void
    {
        // Suppress any further error output
        error_reporting(0);
        ini_set('display_errors', 0);
        
        // Clear anything already in the buffer (notices, whitespace)
        if (ob_get_length()) ob_clean();
        
        http_response_code($httpCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
