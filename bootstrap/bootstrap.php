<?php
/**
 * DesbravaHub Bootstrap
 * 
 * Initializes the application environment, loads helpers, and sets up
 * error handling based on the current environment.
 */

// Define base path constant
define('BASE_PATH', dirname(__DIR__));

// Load environment helper first (needed by config)
require_once BASE_PATH . '/helpers/env.php';

// Load environment variables
env_load();

// Composer autoload temporarily disabled for debugging
// Composer autoload
$composerAutoload = BASE_PATH . '/vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}

// Load core helpers
require_once BASE_PATH . '/helpers/config.php';
require_once BASE_PATH . '/helpers/url.php';
require_once BASE_PATH . '/helpers/database.php';
require_once BASE_PATH . '/helpers/auth.php';
require_once BASE_PATH . '/helpers/lang.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set timezone
date_default_timezone_set(config('app.timezone', 'America/Sao_Paulo'));

// Configure error reporting based on environment
if (is_debug()) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
}

// Set default character encoding (if mbstring extension is available)
if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding('UTF-8');
}

/**
 * Custom error handler for production
 */
function desbravahub_error_handler(int $errno, string $errstr, string $errfile, int $errline): bool
{
    // Log errors in production
    if (!is_debug()) {
        $message = sprintf(
            "[%s] Error %d: %s in %s on line %d",
            date('Y-m-d H:i:s'),
            $errno,
            $errstr,
            $errfile,
            $errline
        );
        error_log($message);
    }

    // Let PHP handle fatal errors
    return false;
}

/**
 * Custom exception handler
 */
function desbravahub_exception_handler(Throwable $exception): void
{
    $message = sprintf(
        "[%s] Exception: %s in %s on line %d\nStack trace:\n%s",
        date('Y-m-d H:i:s'),
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine(),
        $exception->getTraceAsString()
    );

    error_log($message);

    if (is_debug()) {
        echo "<pre>$message</pre>";
    } else {
        // Show user-friendly error page in production (pt-BR)
        http_response_code(500);
        echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>Erro</title></head><body style="font-family:sans-serif;text-align:center;padding:50px;">';
        echo '<h1>Ops! Ocorreu um erro</h1>';
        echo '<p>Pedimos desculpas pelo inconveniente. Por favor, tente novamente mais tarde.</p>';
        echo '</body></html>';
    }

    exit(1);
}

// Set custom error handlers
set_error_handler('desbravahub_error_handler');
set_exception_handler('desbravahub_exception_handler');

/**
 * Application is ready
 * All helpers are loaded and environment is configured.
 */
define('APP_READY', true);
