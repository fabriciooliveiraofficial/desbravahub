<?php
/**
 * Diagnostic script for Login Modal Club Loading Error
 * Run this on the server to identify the root cause.
 */

define('DIAG_MODE', true);
require_once __DIR__ . '/bootstrap/bootstrap.php';

header('Content-Type: text/plain; charset=UTF-8');

echo "=== DesbravaHub Diagnostic: Login Club Loading ===\n\n";

// 1. Check Environment
echo "[1] Environment Check:\n";
echo "APP_ENV: " . env('APP_ENV', 'not set') . "\n";
echo "APP_DEBUG: " . (env('APP_DEBUG') ? 'true' : 'false') . "\n";
echo "APP_BASE_URL: " . env('APP_BASE_URL', 'not set') . "\n";
echo "BASE_PATH: " . BASE_PATH . "\n";
echo ".env exists: " . (file_exists(BASE_PATH . '/.env') ? 'YES' : 'NO') . "\n";

// 2. Check Database Configuration (redacted password)
echo "\n[2] Database Configuration:\n";
$dbConfig = config('database.connections.mysql');
echo "Host: " . ($dbConfig['host'] ?? 'N/A') . "\n";
echo "Database: " . ($dbConfig['database'] ?? 'N/A') . "\n";
echo "Username: " . ($dbConfig['username'] ?? 'N/A') . "\n";
echo "Password set: " . (!empty($dbConfig['password']) ? 'YES' : 'NO') . "\n";

// 3. Test Database Connection
echo "\n[3] Database Connection Test:\n";
try {
    $db = db();
    echo "SUCCESS: Connected to database.\n";
    
    // Check tenants table
    echo "\n[4] Querying Tenants Table:\n";
    $clubs = db_fetch_all("SELECT id, name, slug, status FROM tenants LIMIT 5");
    echo "Found " . count($clubs) . " clubs.\n";
    foreach ($clubs as $club) {
        echo " - [{$club['id']}] {$club['name']} ({$club['slug']}) | Status: {$club['status']}\n";
    }
    
    if (count($clubs) === 0) {
        echo "WARNING: No active clubs found in 'tenants' table.\n";
    }

} catch (PDOException $e) {
    echo "FAILURE: Could not connect to database.\n";
    echo "Error Code: " . $e->getCode() . "\n";
    echo "Error Message: " . $e->getMessage() . "\n";
    
    // Check if it's a specific Hostinger issue
    if (str_contains($e->getMessage(), 'Access denied')) {
        echo "Tip: Verify DB_USERNAME and DB_PASSWORD in .env\n";
    } elseif (str_contains($e->getMessage(), 'getaddrinfo failed') || str_contains($e->getMessage(), 'Connection refused')) {
        echo "Tip: Verify DB_HOST. On Hostinger, 'localhost' is usually correct, but sometimes you need the IP or specific hostname.\n";
    }
} catch (Exception $e) {
    echo "FAILURE: An unexpected error occurred.\n";
    echo "Error Message: " . $e->getMessage() . "\n";
}

echo "\n=== End of Diagnostic ===\n";
