// Load helpers
require_once __DIR__ . '/../helpers/env.php';
require_once __DIR__ . '/../helpers/config.php';
require_once __DIR__ . '/../helpers/database.php';

$pdo = db();


echo "Migrating Units tables...\n";

// Units table
$sql = "CREATE TABLE IF NOT EXISTS units (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    color VARCHAR(20) DEFAULT '#00d9ff',
    mascot VARCHAR(50),
    motto VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
)";

try {
    $pdo->exec($sql);
    echo "Created 'units' table.\n";
} catch (PDOException $e) {
    echo "Error creating 'units' table: " . $e->getMessage() . "\n";
}

// Unit Counselors table (Many-to-Many)
$sql = "CREATE TABLE IF NOT EXISTS unit_counselors (
    unit_id INT NOT NULL,
    user_id INT NOT NULL,
    is_primary BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (unit_id, user_id),
    FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

try {
    $pdo->exec($sql);
    echo "Created 'unit_counselors' table.\n";
} catch (PDOException $e) {
    echo "Error creating 'unit_counselors' table: " . $e->getMessage() . "\n";
}

// Add unit_id to users if not exists
try {
    $check = $pdo->query("SHOW COLUMNS FROM users LIKE 'unit_id'");
    if ($check->rowCount() == 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN unit_id INT NULL DEFAULT NULL AFTER role_id");
        $pdo->exec("ALTER TABLE users ADD FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE SET NULL");
        echo "Added 'unit_id' column to 'users' table.\n";
    } else {
        echo "'unit_id' column already exists in 'users' table.\n";
    }
} catch (PDOException $e) {
    echo "Error altering 'users' table: " . $e->getMessage() . "\n";
}

echo "Migration completed.\n";
