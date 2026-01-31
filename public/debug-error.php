<?php
/**
 * Diagnostic Script - Full Request Simulation
 * 
 * Access: /debug-error.php
 * DELETE THIS FILE after debugging!
 */

// Force display all errors BEFORE any other code
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Override error handler to show errors
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    echo "<div style='background:#ff6b6b;color:white;padding:10px;margin:10px 0;border-radius:4px;'>";
    echo "<strong>Error $errno:</strong> $errstr<br>";
    echo "<strong>File:</strong> $errfile:$errline";
    echo "</div>";
    return true;
});

set_exception_handler(function ($e) {
    echo "<div style='background:#ff6b6b;color:white;padding:10px;margin:10px 0;border-radius:4px;'>";
    echo "<strong>Exception:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>File:</strong> " . $e->getFile() . ":" . $e->getLine() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
});

define('BASE_PATH', dirname(__DIR__));

echo "<h1>🔍 Full Request Simulation</h1>";
echo "<pre style='background:#1a1a2e;color:#e0e0e0;padding:20px;border-radius:8px;'>";

try {
    echo "1. Loading bootstrap (but with errors visible)...\n";

    // Load helpers
    require_once BASE_PATH . '/helpers/env.php';
    env_load();
    require_once BASE_PATH . '/helpers/config.php';
    require_once BASE_PATH . '/helpers/url.php';
    require_once BASE_PATH . '/helpers/database.php';
    require_once BASE_PATH . '/helpers/auth.php';
    require_once BASE_PATH . '/helpers/lang.php';
    echo "   ✅ Helpers loaded\n";

    // Start session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    echo "   ✅ Session started\n";

    // Set timezone
    date_default_timezone_set(config('app.timezone', 'America/Sao_Paulo'));
    echo "   ✅ Timezone set\n";

    define('APP_READY', true);
    echo "   ✅ App ready\n\n";

    echo "2. Loading autoloader...\n";
    spl_autoload_register(function ($class) {
        $prefix = 'App\\';
        $baseDir = BASE_PATH . '/app/';
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }
        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require $file;
        }
    });
    echo "   ✅ Autoloader registered\n\n";

    echo "3. Simulating /admin/classes request...\n";

    // Mock the tenant detection
    $_SERVER['REQUEST_URI'] = '/clube-demo/admin/classes';
    echo "   URI: " . $_SERVER['REQUEST_URI'] . "\n";

    echo "\n4. Loading routes...\n";
    $router = require BASE_PATH . '/routes/web.php';
    echo "   ✅ Routes loaded\n\n";

    echo "5. Testing route match...\n";
    // Just test that the controller can be instantiated
    $controller = new \App\Controllers\AdminController();
    echo "   ✅ AdminController instantiated\n\n";

    echo "6. Manually calling classes() method...\n";
    echo "</pre>";

    // Mock App tenant and user
    \App\Core\App::setTenant(['id' => 1, 'slug' => 'clube-demo', 'name' => 'Clube Demo']);
    \App\Core\App::setUser(['id' => 1, 'role_name' => 'admin', 'name' => 'Admin']);

    ob_start();
    $controller->classes();
    $output = ob_get_clean();

    echo "<pre style='background:#1a1a2e;color:#e0e0e0;padding:20px;border-radius:8px;'>";
    echo "   ✅ classes() executed successfully!\n";
    echo "   Output length: " . strlen($output) . " bytes\n";

} catch (Exception $e) {
    echo "\n❌ EXCEPTION: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString();
} catch (Error $e) {
    echo "\n❌ FATAL ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString();
}

echo "</pre>";
echo "<p style='color:red;font-weight:bold;'>⚠️ DELETE THIS FILE AFTER DEBUGGING!</p>";
