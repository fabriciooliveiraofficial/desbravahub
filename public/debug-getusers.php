<?php
/**
 * Diagnostic: Test getUsers query for program assignment
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Debug getUsers Query</h1>";

$configPath = dirname(__DIR__) . '/config.php';
if (!file_exists($configPath)) {
    die("❌ config.php not found");
}
require_once $configPath;

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "<p>✅ Database connected</p>";
} catch (PDOException $e) {
    die("❌ Connection failed: " . $e->getMessage());
}

// Get tenant ID
$tenantSlug = 'clube-demo';
$tenant = $pdo->query("SELECT * FROM tenants WHERE slug = '$tenantSlug'")->fetch(PDO::FETCH_ASSOC);
echo "<p>Tenant: " . ($tenant ? $tenant['name'] : 'NOT FOUND') . "</p>";

if (!$tenant)
    die("❌ Tenant not found");

// Check roles table
echo "<h2>📋 Roles in this tenant:</h2>";
$roles = $pdo->query("SELECT * FROM roles WHERE tenant_id = {$tenant['id']}")->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>" . print_r($roles, true) . "</pre>";

// Check if there's a pathfinder role
echo "<h2>🔎 Looking for pathfinder/desbravador role:</h2>";
$pathfinderRoles = $pdo->query("SELECT * FROM roles WHERE tenant_id = {$tenant['id']} AND name IN ('pathfinder', 'desbravador')")->fetchAll(PDO::FETCH_ASSOC);
if (empty($pathfinderRoles)) {
    echo "<p>⚠️ No pathfinder/desbravador role found! Role names are:</p>";
    foreach ($roles as $r) {
        echo "<li>" . $r['name'] . " (ID: " . $r['id'] . ")</li>";
    }
} else {
    echo "<pre>" . print_r($pathfinderRoles, true) . "</pre>";
}

// Check users table structure
echo "<h2>📋 Users table columns:</h2>";
$cols = $pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_ASSOC);
$colNames = array_column($cols, 'Field');
echo "<p>" . implode(', ', $colNames) . "</p>";

// Check if profile_picture column exists
if (!in_array('profile_picture', $colNames)) {
    echo "<p>⚠️ Column 'profile_picture' does NOT exist. The query will fail!</p>";
    echo "<p>Available columns: <code>" . implode('</code>, <code>', $colNames) . "</code></p>";
} else {
    echo "<p>✅ profile_picture column exists</p>";
}

// Try the actual query
echo "<h2>🧪 Testing the actual query:</h2>";
$programId = 1;
try {
    $stmt = $pdo->prepare("
        SELECT u.id, u.name, u.profile_picture,
               CASE WHEN upp.id IS NOT NULL THEN 1 ELSE 0 END as already_assigned
        FROM users u
        LEFT JOIN user_program_progress upp ON upp.user_id = u.id AND upp.program_id = ?
        WHERE u.tenant_id = ? AND u.role_id IN (
            SELECT id FROM roles WHERE name IN ('pathfinder', 'desbravador')
        )
        ORDER BY u.name
    ");
    $stmt->execute([$programId, $tenant['id']]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<p>✅ Query executed successfully!</p>";
    echo "<p>Found " . count($users) . " users</p>";
    echo "<pre>" . print_r($users, true) . "</pre>";
} catch (PDOException $e) {
    echo "<p>❌ Query error: " . $e->getMessage() . "</p>";
}

echo "<hr><p><a href='javascript:history.back()'>← Back</a></p>";
