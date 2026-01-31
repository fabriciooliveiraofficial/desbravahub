<?php
// Mock server vars for CLI
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/';

require_once __DIR__ . '/../bootstrap/bootstrap.php';

use App\Services\SpecialtyService;

echo "Starting migration...\n";

// Get all tenants
$tenants = db_fetch_all("SELECT * FROM tenants");
echo "Found " . count($tenants) . " tenants.\n";

$legacyCats = SpecialtyService::getCategories();
$legacyClasses = [
    ['id' => 'amigo', 'name' => 'Amigo', 'color' => '#4CAF50', 'icon' => '🌱'],
    ['id' => 'companheiro', 'name' => 'Companheiro', 'color' => '#2196F3', 'icon' => '🌿'],
    ['id' => 'pesquisador', 'name' => 'Pesquisador', 'color' => '#9C27B0', 'icon' => '🔍'],
    ['id' => 'pioneiro', 'name' => 'Pioneiro', 'color' => '#FF9800', 'icon' => '🏕️'],
    ['id' => 'excursionista', 'name' => 'Excursionista', 'color' => '#F44336', 'icon' => '🥾'],
    ['id' => 'guia', 'name' => 'Guia', 'color' => '#00BCD4', 'icon' => '🧭'],
];

foreach ($tenants as $tenant) {
    echo "Processing tenant: {$tenant['slug']}...\n";
    $tid = $tenant['id'];
    
    // Import Specialties
    foreach ($legacyCats as $c) {
        $exists = db_fetch_one("SELECT id FROM learning_categories WHERE tenant_id = ? AND name = ?", [$tid, $c['name']]);
        if (!$exists) {
            db_insert('learning_categories', [
                'tenant_id' => $tid,
                'name' => $c['name'],
                'color' => $c['color'],
                'icon' => $c['icon'] ?? '📁',
                'type' => 'specialty',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
    }
    
    // Import Classes
    foreach ($legacyClasses as $c) {
        $exists = db_fetch_one("SELECT id FROM learning_categories WHERE tenant_id = ? AND name = ?", [$tid, $c['name']]);
        if (!$exists) {
            db_insert('learning_categories', [
                'tenant_id' => $tid,
                'name' => $c['name'],
                'color' => $c['color'],
                'icon' => $c['icon'],
                'type' => 'class',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
    }
}

echo "Migration complete.\n";
