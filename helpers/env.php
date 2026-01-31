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

    $envPath = $path ?? dirname(__DIR__) . '/.env';

    if (!file_exists($envPath)) {
        $GLOBALS['__env_loaded'] = true;
        return;
    }

    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Parse key=value
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Remove surrounding quotes
            if (preg_match('/^(["\'])(.*)\\1$/', $value, $matches)) {
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

            // Also set in $_ENV and putenv for compatibility
            $_ENV[$key] = $value;
            putenv("$key=$value");
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
    return is_env('dev');
}

/**
 * Check if debug mode is enabled
 */
function is_debug(): bool
{
    return (bool) env('APP_DEBUG', false);
}
