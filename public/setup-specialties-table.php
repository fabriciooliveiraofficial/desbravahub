<?php
/**
 * Setup: Create specialties table for tenant-specific specialties
 * 
 * This script creates the database table for storing custom specialties
 * created by club admins.
 * 
 * URL: /setup-specialties-table.php
 * DELETE AFTER USE!
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

$config = [
    'db_host' => 'localhost',
    'db_name' => 'u714643564_db_desbravahub',
    'db_user' => 'u714643564_user_desbravah',
    'db_pass' => 'Fdm399788896528168172@#$%',
];

try {
    $pdo = new PDO(
        "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4",
        $config['db_user'],
        $config['db_pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("<h1>❌ Database connection error</h1><pre>{$e->getMessage()}</pre>");
}

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Setup Specialties Table</title>";
echo "<style>body{font-family:system-ui;max-width:800px;margin:40px auto;padding:20px;background:#1a1a2e;color:#fff}";
echo "h1{color:#00d9ff}.success{color:#00ff88}.error{color:#ff6b6b}pre{background:#0d0d1a;padding:15px;border-radius:8px}";
echo "button{padding:15px 30px;background:linear-gradient(135deg,#00d9ff,#00ff88);color:#1a1a2e;border:none;border-radius:8px;cursor:pointer;font-weight:bold;font-size:1.1rem}</style></head><body>";

echo "<h1>🗄️ Setup Specialties Table</h1>";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "<p>This will create the <code>specialties</code> table for storing tenant-specific specialties.</p>";
    echo "<form method='POST'><button type='submit'>🚀 Create Table</button></form>";
    echo "</body></html>";
    exit;
}

// Create table
$sql = "
CREATE TABLE IF NOT EXISTS specialties (
    id VARCHAR(50) PRIMARY KEY,
    tenant_id INT NOT NULL,
    category_id VARCHAR(50) NOT NULL,
    name VARCHAR(255) NOT NULL,
    badge_icon VARCHAR(10) DEFAULT '📘',
    type ENUM('indoor', 'outdoor', 'mixed') DEFAULT 'indoor',
    duration_hours INT DEFAULT 4,
    difficulty INT DEFAULT 1,
    xp_reward INT DEFAULT 100,
    description TEXT,
    status ENUM('draft', 'active', 'archived') DEFAULT 'active',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant (tenant_id),
    INDEX idx_category (category_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

try {
    $pdo->exec($sql);
    echo "<p class='success'>✅ Table <code>specialties</code> created successfully!</p>";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'already exists') !== false) {
        echo "<p class='success'>✅ Table <code>specialties</code> already exists.</p>";
    } else {
        echo "<p class='error'>❌ Error: {$e->getMessage()}</p>";
    }
}

// Add index on specialty_requirements if not exists
try {
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_specialty_id ON specialty_requirements(specialty_id)");
    echo "<p class='success'>✅ Index on specialty_requirements added.</p>";
} catch (PDOException $e) {
    echo "<p class='success'>✅ Index already exists or not needed.</p>";
}

// Verify table structure
echo "<h2>📋 Table Structure</h2>";
$cols = $pdo->query("DESCRIBE specialties")->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
foreach ($cols as $col) {
    echo "{$col['Field']} - {$col['Type']} {$col['Null']} {$col['Default']}\n";
}
echo "</pre>";

echo "<div style='background:#1a3d1a;border:2px solid #00ff88;border-radius:8px;padding:20px;margin-top:20px'>";
echo "<h2>✅ Done!</h2>";
echo "<p>The specialties table is ready. Admins can now create custom specialties.</p>";
echo "</div>";

echo "<div style='background:#3d1a1a;border:2px solid #ff6b6b;border-radius:8px;padding:20px;margin-top:20px'>";
echo "<h2>⚠️ Delete this file!</h2>";
echo "<pre>rm public/setup-specialties-table.php</pre>";
echo "</div>";

echo "</body></html>";
