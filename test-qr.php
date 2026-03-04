<?php
require_once __DIR__ . '/bootstrap/bootstrap.php';
use App\Core\App;

header('Content-Type: text/plain');

echo "QR Code Diagnostic\n";
echo "==================\n\n";

// 1. Check Tables
echo "Tables in database:\n";
try {
    $stmt = db()->query("SHOW TABLES");
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        echo " - " . $row[0] . "\n";
    }
} catch (Exception $e) {
    echo "ERROR Listing Tables: " . $e->getMessage() . "\n";
}
echo "\n";

// 2. Check BASE_PATH
echo "BASE_PATH: " . BASE_PATH . "\n";
$uploadDir = BASE_PATH . '/public/uploads/qrcodes/';
echo "Target Upload Dir: " . $uploadDir . "\n";
echo "Dir Exists? " . (is_dir($uploadDir) ? "YES" : "NO") . "\n";
echo "Dir Writable? " . (is_writable($uploadDir) ? "YES" : "NO") . "\n\n";

// 2. Check Club Profiles
echo "Club Profiles:\n";
$profiles = db_fetch_all("SELECT tenant_id, slug FROM club_profiles");
foreach ($profiles as $p) {
    echo " - Tenant ID: {$p['tenant_id']}, Slug: '{$p['slug']}'\n";
}
echo "\n";

// 3. Check Growth Tools
echo "Growth Tools:\n";
$growth = db_fetch_all("SELECT tenant_id, qr_code_path FROM club_growth_tools");
foreach ($growth as $g) {
    echo " - Tenant ID: {$g['tenant_id']}, Path: '{$g['qr_code_path']}'\n";
}
echo "\n";

// 4. Test External API
$testUrl = "https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=test";
echo "Testing API: {$testUrl}\n";
$content = @file_get_contents($testUrl);
if ($content === false) {
    $error = error_get_last();
    echo "API FAIL: " . ($error['message'] ?? 'Unknown error') . "\n";
} else {
    echo "API SUCCESS: Got " . strlen($content) . " bytes\n";
}
