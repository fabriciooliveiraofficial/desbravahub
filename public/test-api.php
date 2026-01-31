<?php
/**
 * Test App API
 * Upload to public/test-api.php
 */

require_once dirname(__DIR__) . '/bootstrap/bootstrap.php';

// Manually register autoloader if not already done by bootstrap
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = BASE_PATH . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) === 0) {
        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require $file;
        }
    }
});

use App\Controllers\ApiController;

echo "<h1>🧪 App API Direct Test</h1>";

echo "<h2>System Check</h2>";
echo "BASE_PATH: " . (defined('BASE_PATH') ? BASE_PATH : "UNDEFINED") . "<br>";
echo ".env exists in BASE_PATH: " . (file_exists(BASE_PATH . '/.env') ? "✅" : "❌") . "<br>";
echo "Environment loaded: " . ($GLOBALS['__env_loaded'] ? "✅" : "❌") . "<br>";
echo "Loaded variables: " . count($GLOBALS['__env_cache']) . "<br>";

if (count($GLOBALS['__env_cache']) > 0) {
    echo "<h3>Check DB Vars:</h3>";
    echo "DB_HOST: " . (env('DB_HOST') ?: "NULL") . "<br>";
    echo "DB_USERNAME: " . (env('DB_USERNAME') ?: "NULL") . "<br>";
}

try {
    echo "Instantiating ApiController... ";
    $controller = new ApiController();
    echo "✅<br>";

    echo "Calling clubs() method...<br>";
    echo "<hr>";
    $controller->clubs();
    echo "<hr>";
    echo "✅ Completed successfully";

} catch (Throwable $e) {
    echo "❌ <strong>FAILED:</strong><br>";
    echo "Message: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
