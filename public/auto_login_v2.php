<?php
require_once __DIR__ . '/../bootstrap/bootstrap.php';

try {
    $tenant = db_fetch_one("SELECT id FROM tenants WHERE slug='clube-demo'");
    if (!$tenant) {
        die("Tenant 'clube-demo' not found.");
    }
    
    $user = db_fetch_one("
        SELECT u.id, u.tenant_id 
        FROM users u 
        WHERE u.email = 'fabriciooliveiraofficial@gmail.com' AND u.tenant_id = ?
    ", [$tenant['id']]);

    if ($user) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['tenant_id'] = $user['tenant_id'];
        
        // Return success info if called via AJAX or just redirect
        if (isset($_GET['ajax'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'user_id' => $user['id']]);
            exit;
        }
        
        header("Location: /clube-demo/admin/usuarios");
        exit;
    } else {
        die("User 'fabriciooliveiraofficial@gmail.com' not found in tenant 'clube-demo'.");
    }

} catch (\Exception $e) {
    die("Error during auto-login: " . $e->getMessage());
}
