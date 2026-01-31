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
