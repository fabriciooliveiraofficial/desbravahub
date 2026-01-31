<?php
/**
 * Server Diagnostics Script
 * Upload this to /public/server-diag.php and access via browser.
 * DELETE AFTER USE!
 */

// 1. Basic Setup
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/html; charset=utf-8');

echo "<h1>🕵️‍♂️ DesbravaHub Server Diagnostics</h1>";

// 2. Check File Structure
$rootDir = dirname(__DIR__);
echo "<h2>1. File Structure</h2>";
echo "Root Dir: " . $rootDir . "<br>";

$envPath = $rootDir . '/.env';
if (file_exists($envPath)) {
    echo "✅ .env file found at: $envPath<br>";
    
    // Check permissions
    if (is_readable($envPath)) {
        echo "✅ .env file is readable<br>";
        
        // 3. Load Environment (Simple manual parse to verify contents without app logic)
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        echo "<h3>2. Environment Variables (.env content)</h3>";
        echo "<pre style='background:#f4f4f4; padding:10px; border:1px solid #ccc;'>";
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            
            $parts = explode('=', $line, 2);
            if (count($parts) === 2) {
                $key = trim($parts[0]);
                $val = trim($parts[1]);
                
                // Mask secrets
                if (stripos($key, 'PASSWORD') !== false || stripos($key, 'KEY') !== false || stripos($key, 'SECRET') !== false) {
                    $val = '********';
                }
                
                echo "$key = $val\n";
                // Set for local usage in this script
                putenv("$key=$parts[1]");
                $_ENV[$key] = $parts[1];
            }
        }
        echo "</pre>";
        
    } else {
        echo "❌ .env file exists but is NOT readable (Check Permissions)<br>";
    }
} else {
    echo "❌ .env file NOT found at: $envPath<br>";
}

// 4. Database Connection Test
echo "<h2>3. Database Connection Test</h2>";

$host = getenv('DB_HOST') ?: $_ENV['DB_HOST'] ?? 'NOT_SET';
$db   = getenv('DB_DATABASE') ?: $_ENV['DB_DATABASE'] ?? 'NOT_SET';
$user = getenv('DB_USERNAME') ?: $_ENV['DB_USERNAME'] ?? 'NOT_SET';
$pass = getenv('DB_PASSWORD') ?: $_ENV['DB_PASSWORD'] ?? 'NOT_SET'; // Unmasked for connection, but don't print

echo "Attempting to connect to:<br>";
echo "Host: <strong>$host</strong><br>";
echo "Database: <strong>$db</strong><br>";
echo "User: <strong>$user</strong><br>";
echo "Password: <strong>" . ($pass === 'NOT_SET' ? 'NOT_SET' : '********') . "</strong><br><br>";

try {
    $dsn = "mysql:host=$host;port=3306;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5
    ]);
    
    echo "✅ <strong>Connection Successful!</strong> 🚀<br>";
    
    // 5. Schema Test
    echo "<h2>4. Schema Check</h2>";
    $stm = $pdo->query("SHOW TABLES");
    $tables = $stm->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($tables) > 0) {
        echo "✅ Found " . count($tables) . " tables:<br>";
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>$table</li>";
        }
        echo "</ul>";
        
        // Check Tenants specifically
        if (in_array('tenants', $tables)) {
            $stmt = $pdo->query("SELECT * FROM tenants");
            $tenants = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "<h3>Tenants Data (" . count($tenants) . "):</h3>";
            if (count($tenants) > 0) {
                echo "<pre>" . print_r($tenants, true) . "</pre>";
            } else {
                echo "⚠️ 'tenants' table is empty. Need to run Seed.<br>";
            }
        } else {
            echo "❌ 'tenants' table MISSING. Need to run Schema.<br>";
        }
        
    } else {
        echo "⚠️ Database is empty (No tables found).<br>";
    }
    
} catch (PDOException $e) {
    echo "❌ <strong>Connection Failed:</strong><br>";
    echo "<div style='color:red; background:#ffe0e0; padding:15px; border:1px solid red;'>";
    echo "Code: " . $e->getCode() . "<br>";
    echo "Message: " . $e->getMessage() . "<br>";
    echo "</div>";
    
    // Hints
    if ($e->getCode() == 1045) {
        echo "<br>💡 <strong>Dica:</strong> Erro de Senha ou Usuário. Verifique se o DB_USERNAME e DB_PASSWORD estão exatos no arquivo .env.";
    } elseif ($e->getCode() == 2002) {
        echo "<br>💡 <strong>Dica:</strong> Não encontrou o Host. Verifique se DB_HOST é 'localhost' (para Hostinger) ou se o IP está correto.";
    } elseif ($e->getCode() == 1049) {
        echo "<br>💡 <strong>Dica:</strong> Banco de dados não existe. Verifique se o nome DB_DATABASE está correto.";
    }
}
