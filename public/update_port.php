<?php
// Update Port to 465
ini_set('display_errors', 1);
require_once dirname(__DIR__) . '/bootstrap/bootstrap.php';

use App\Core\App;

$slug = 'demo-club';
$tenant = db_fetch_one("SELECT * FROM tenants WHERE slug = ?", [$slug]);
db_update('tenant_smtp_settings', ['smtp_port' => 465], 'tenant_id = ?', [$tenant['id']]);

echo "Port updated to 465 for tenant {$tenant['id']}\n";
