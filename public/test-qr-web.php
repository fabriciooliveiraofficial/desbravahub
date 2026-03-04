<?php
require_once __DIR__ . '/../bootstrap/bootstrap.php';
header('Content-Type: text/plain; charset=UTF-8');

echo "QR Code Web Diagnostic\n";
echo "======================\n\n";

// 1. Check Driver
echo "PDO Drivers: " . implode(', ', PDO::getAvailableDrivers()) . "\n";
echo "MySQL Driver? " . (in_array('mysql', PDO::getAvailableDrivers()) ? "YES" : "NO") . "\n\n";

// 2. Check Database Content
echo "Club Profiles:\n";
try {
    $profiles = db_fetch_all("SELECT tenant_id, slug FROM club_profiles");
    foreach ($profiles as $p) {
        echo " - Tenant ID: {$p['tenant_id']}, Slug: '{$p['slug']}'\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
echo "\n";

echo "Growth Tools:\n";
try {
    $growth = db_fetch_all("SELECT tenant_id, qr_code_path FROM club_growth_tools");
    foreach ($growth as $g) {
        echo " - Tenant ID: {$g['tenant_id']}, Path: '{$g['qr_code_path']}'\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
echo "Tenants:\n";
try {
    $tenants = db_fetch_all("SELECT id, name, slug FROM tenants");
    foreach ($tenants as $t) {
        echo " - ID: {$t['id']}, Name: {$t['name']}, Slug: {$t['slug']}\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
echo "\n";

// 3. Check Base Config
echo "APP_BASE_URL: " . config('app.base_url') . "\n";
echo "BASE_PATH: " . BASE_PATH . "\n";
$uploadDir = BASE_PATH . '/public/uploads/qrcodes/';
echo "Target Upload Dir: " . $uploadDir . "\n";
echo "Dir Exists? " . (is_dir($uploadDir) ? "YES" : "NO") . "\n";
echo "Dir Writable? " . (is_writable($uploadDir) ? "YES" : "NO") . "\n\n";

// 4. Test External API via Web Server
$testUrl = "https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=test";
echo "Testing API: {$testUrl}\n";
$content = @file_get_contents($testUrl);
if ($content === false) {
    $error = error_get_last();
    echo "API FAIL: " . ($error['message'] ?? 'Unknown error') . "\n";
} else {
    echo "API SUCCESS: Got " . strlen($content) . " bytes\n";
    
    // 5. Test File Save
    $testFile = $uploadDir . 'test_save.png';
    $bytes = file_put_contents($testFile, $content);
    if ($bytes === false) {
        $error = error_get_last();
        echo "FILE SAVE FAIL: " . ($error['message'] ?? 'Unknown error') . "\n";
    } else {
        echo "FILE SAVE SUCCESS: Written {$bytes} bytes to {$testFile}\n";
        unlink($testFile);
    }
}
