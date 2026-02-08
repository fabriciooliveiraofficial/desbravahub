<?php

require_once __DIR__ . '/../bootstrap/bootstrap.php';

use App\Services\RoleService;

echo "<pre>";
echo "🚀 Starting Global Role Synchronization...\n";
echo "========================================\n\n";

try {
    $tenants = db_fetch_all("SELECT id, name, slug FROM tenants");
    
    if (empty($tenants)) {
        echo "❌ No tenants found.\n";
        exit;
    }

    foreach ($tenants as $tenant) {
        echo "📦 Processing Tenant: {$tenant['name']} ({$tenant['slug']})...\n";
        
        $stats = RoleService::syncTenant($tenant['id']);
        
        echo "   ✓ Roles created: {$stats['created']}\n";
        echo "   ✓ Roles updated: {$stats['updated']}\n";
        
        foreach ($stats['roles'] as $name => $id) {
            echo "   - $name: ID $id\n";
        }
        echo "\n";
    }

    echo "========================================\n";
    echo "✅ Synchronization complete!\n";

} catch (\Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}

echo "</pre>";
