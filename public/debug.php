<?php
/**
 * Debug páginas novas
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once dirname(__DIR__) . '/bootstrap/bootstrap.php';

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = BASE_PATH . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0)
        return;
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file))
        require $file;
});

echo "<pre style='font-family:monospace; background:#1a1a2e; color:#e0e0e0; padding:20px;'>";
echo "<h1 style='color:#00d9ff;'>Debug Novas Páginas</h1>\n\n";

try {
    // 1. Verificar Controllers
    echo "1. Verificando Controllers...\n";

    $controllers = [
        'LandingController' => 'app/Controllers/LandingController.php',
        'EventController' => 'app/Controllers/EventController.php',
    ];

    foreach ($controllers as $name => $path) {
        $fullPath = BASE_PATH . '/' . $path;
        if (file_exists($fullPath)) {
            echo "   <span style='color:#00ff88'>✓</span> $name existe\n";
        } else {
            echo "   <span style='color:#ff6b6b'>✗</span> $name NÃO EXISTE: $fullPath\n";
        }
    }

    // 2. Verificar Views
    echo "\n2. Verificando Views...\n";

    $views = [
        'landing.php' => 'views/public/landing.php',
        'events.php' => 'views/dashboard/events.php',
        'proofs.php' => 'views/dashboard/proofs.php',
    ];

    foreach ($views as $name => $path) {
        $fullPath = BASE_PATH . '/' . $path;
        if (file_exists($fullPath)) {
            echo "   <span style='color:#00ff88'>✓</span> $name existe\n";
        } else {
            echo "   <span style='color:#ff6b6b'>✗</span> $name NÃO EXISTE: $fullPath\n";
        }
    }

    // 3. Testar rotas
    echo "\n3. Verificando rotas...\n";
    $routeFile = BASE_PATH . '/routes/web.php';
    $routeContent = file_get_contents($routeFile);

    $routes = ['LandingController', 'EventController', '/eventos', '/provas'];
    foreach ($routes as $route) {
        if (strpos($routeContent, $route) !== false) {
            echo "   <span style='color:#00ff88'>✓</span> Rota '$route' encontrada\n";
        } else {
            echo "   <span style='color:#ff6b6b'>✗</span> Rota '$route' NÃO encontrada\n";
        }
    }

    // 4. Testar tenant
    echo "\n4. Verificando tenant...\n";
    $tenant = db_fetch_one("SELECT * FROM tenants WHERE slug = 'estrela-guia'");
    if ($tenant) {
        echo "   <span style='color:#00ff88'>✓</span> Tenant: {$tenant['name']}\n";
    } else {
        echo "   <span style='color:#ff6b6b'>✗</span> Tenant 'estrela-guia' não encontrado\n";
    }

    echo "\n<span style='color:#00ff88'>✅ DEBUG COMPLETO</span>\n";

} catch (Exception $e) {
    echo "\n<span style='color:#ff6b6b'>✗ ERRO: " . $e->getMessage() . "</span>\n";
    echo $e->getTraceAsString();
}

echo "</pre>";
echo "<br><a href='/estrela-guia' style='padding:12px 24px; background:#00d9ff; color:#000; text-decoration:none; border-radius:8px;'>Testar Landing Page</a>";
