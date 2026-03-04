<?php
require_once __DIR__ . '/../bootstrap/bootstrap.php';
use App\Controllers\AdminController;
use App\Core\App;

header('Content-Type: text/plain; charset=UTF-8');

error_reporting(E_ALL);
ini_set('display_errors', 1);

$targetId = $_GET['id'] ?? 11;
echo "Debugging viewUserProfile for user $targetId\n";
echo "====================================\n\n";

// Get tenant 2
$tenant = db_fetch_one("SELECT * FROM tenants WHERE id = ? OR slug = ?", [2, 'clube-demo']);
if (!$tenant) {
    die("Tenant not found\n");
}
App::setTenant($tenant);
echo "Tenant: " . $tenant['slug'] . " (ID: " . $tenant['id'] . ")\n";

// Get any admin user for this tenant
$admin = db_fetch_one("SELECT u.*, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.tenant_id = ? AND r.name = 'admin' LIMIT 1", [$tenant['id']]);
if (!$admin) {
    die("No admin user found for tenant\n");
}
App::setUser($admin);
echo "Acting as Admin: " . $admin['email'] . " (ID: " . $admin['id'] . ")\n\n";

$controller = new AdminController();

// Capture output
ob_start();
try {
    $controller->viewUserProfile(['id' => $targetId]);
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
$output = ob_get_clean();

echo "Raw Output:\n";
echo $output . "\n\n";

echo "Check for validity:\n";
$decoded = json_decode($output, true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo "Output is VALID JSON\n";
    print_r($decoded);
} else {
    echo "Output is NOT valid JSON! Error: " . json_last_error_msg() . "\n";
}
