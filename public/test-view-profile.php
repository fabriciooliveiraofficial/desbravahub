<?php
require_once __DIR__ . '/../bootstrap/bootstrap.php';
use App\Controllers\AdminController;
use App\Core\App;

header('Content-Type: application/json; charset=UTF-8');

// Mock tenant 2 (clube-demo)
$tenant = db_fetch_one("SELECT * FROM tenants WHERE id = ? OR slug = ?", [2, 'clube-demo']);
App::setTenant($tenant);

// Mock admin user for permission check
$user = db_fetch_one("SELECT u.*, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.tenant_id = ? AND r.name = 'admin' LIMIT 1", [$tenant['id']]);
App::setUser($user);

$controller = new AdminController();

try {
    $userId = $_GET['id'] ?? 11;
    $controller->viewUserProfile(['id' => $userId]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
