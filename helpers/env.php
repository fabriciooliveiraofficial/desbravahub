<?php
/**
 * Environment Helper Functions
 * 
 * Loads and provides access to environment variables from .env file.
 */

/**
 * Environment variables cache
 */
$GLOBALS['__env_cache'] = [];
$GLOBALS['__env_loaded'] = false;

/**
 * Load environment variables from .env file
 * 
 * @param string|null $path Custom path to .env file
 */
function env_load(?string $path = null): void
{
    if ($GLOBALS['__env_loaded']) {
        return;
    }

    // Use BASE_PATH if defined, otherwise calculate relative to this file
    $basePath = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__);
    $envPath = $path ?? $basePath . '/.env';

    if (!file_exists($envPath) || !is_readable($envPath)) {
        $GLOBALS['__env_loaded'] = true;
        error_log("Env: .env file not found or not readable at " . $envPath);
        return;
    }

    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if ($lines === false) {
        $GLOBALS['__env_loaded'] = true;
        error_log("Env: Failed to read .env file at " . $envPath);
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        
        // Skip comments and empty lines
        if (empty($line) || str_starts_with($line, '#')) {
            continue;
        }

        // Parse key=value
        if (str_contains($line, '=')) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Remove surrounding quotes
            if (preg_match('/^(["\'])(.*)\1$/', $value, $matches)) {
                $value = $matches[2];
            }

            // Convert special values
            $value = match (strtolower($value)) {
                'true', '(true)' => true,
                'false', '(false)' => false,
                'null', '(null)' => null,
                'empty', '(empty)' => '',
                default => $value,
            };

            $GLOBALS['__env_cache'][$key] = $value;
            $_ENV[$key] = $value;
            
            // Only putenv strings
            if (is_string($value) || is_numeric($value)) {
                putenv("$key=$value");
            }
        }
    }

    $GLOBALS['__env_loaded'] = true;
}

/**
 * Get an environment variable value
 * 
 * @param string $key Environment variable name
 * @param mixed $default Default value if not found
 * @return mixed
 * 
 * Usage:
 *   env('APP_DEBUG')           // Returns boolean
 *   env('DB_HOST', 'localhost') // Returns value or default
 */
function env(string $key, $default = null)
{
    // Ensure environment is loaded
    if (!$GLOBALS['__env_loaded']) {
        env_load();
    }

    // Check our cache first
    if (array_key_exists($key, $GLOBALS['__env_cache'])) {
        return $GLOBALS['__env_cache'][$key];
    }

    // Check $_ENV
    if (array_key_exists($key, $_ENV)) {
        return $_ENV[$key];
    }

    // Check getenv
    $value = getenv($key);
    if ($value !== false) {
        return $value;
    }

    return $default;
}

/**
 * Check if running in specific environment
 * 
 * @param string $environment Environment to check
 * @return bool
 */
function is_env(string $environment): bool
{
    return env('APP_ENV', 'production') === $environment;
}

/**
 * Check if running in production
 */
function is_production(): bool
{
    return is_env('production');
}

/**
 * Check if running in development
 */
function is_dev(): bool
{
    $env = env('APP_ENV', 'production');
    return $env === 'dev' || $env === 'local';
}

/**
 * Check if debug mode is enabled
 */
function is_debug(): bool
{
    return (bool) env('APP_DEBUG', false);
}
