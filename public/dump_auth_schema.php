<?php
require_once __DIR__ . '/../bootstrap/bootstrap.php';

function dump_table_schema($tableName) {
    echo "\n--- Schema for table: $tableName ---\n";
    try {
        $columns = db_fetch_all("SHOW COLUMNS FROM `$tableName` ");
        foreach ($columns as $col) {
            echo "{$col['Field']} | {$col['Type']} | {$col['Null']} | {$col['Key']} | {$col['Default']} | {$col['Extra']}\n";
        }
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

dump_table_schema('roles');
dump_table_schema('permissions');
dump_table_schema('role_permissions');
