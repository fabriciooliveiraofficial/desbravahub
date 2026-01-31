<?php
/**
 * Configuration Helper Functions
 * 
 * Provides global access to configuration values.
 */

/**
 * Global configuration cache
 */
$GLOBALS['__config_cache'] = [];

/**
 * Get a configuration value using dot notation
 * 
 * @param string $key Configuration key (e.g., 'app.base_url')
 * @param mixed $default Default value if key not found
 * @return mixed
 * 
 * Usage:
 *   config('app.base_url')           // Returns base URL
 *   config('database.connections.mysql.host')  // Returns DB host
 *   config('app.nonexistent', 'default')       // Returns 'default'
 */
function config(string $key, $default = null)
{
    // Check cache first
    if (isset($GLOBALS['__config_cache'][$key])) {
        return $GLOBALS['__config_cache'][$key];
    }

    $parts = explode('.', $key);
    $file = array_shift($parts);

    // Load config file if not cached
    $configPath = dirname(__DIR__) . '/config/' . $file . '.php';

    if (!file_exists($configPath)) {
        return $default;
    }

    // Cache the entire config file
    if (!isset($GLOBALS['__config_files'][$file])) {
        $GLOBALS['__config_files'][$file] = require $configPath;
    }

    $value = $GLOBALS['__config_files'][$file];

    // Navigate through nested keys
    foreach ($parts as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return $default;
        }
        $value = $value[$part];
    }

    // Cache the final value
    $GLOBALS['__config_cache'][$key] = $value;

    return $value;
}

/**
 * Set a configuration value at runtime
 * 
 * @param string $key Configuration key
 * @param mixed $value Value to set
 */
function config_set(string $key, $value): void
{
    $GLOBALS['__config_cache'][$key] = $value;
}

/**
 * Clear configuration cache
 */
function config_clear(): void
{
    $GLOBALS['__config_cache'] = [];
    $GLOBALS['__config_files'] = [];
}
