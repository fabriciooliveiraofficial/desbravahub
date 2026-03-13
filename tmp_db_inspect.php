<?php
require 'bootstrap/bootstrap.php';

function inspect($table) {
    echo "\n--- $table ---\n";
    try {
        $cols = db_fetch_all("DESCRIBE $table");
        foreach ($cols as $c) {
            echo "{$c['Field']} | {$c['Type']} | {$c['Null']} | {$c['Key']} | {$c['Default']}\n";
        }
    } catch (Exception $e) {
        echo "Error or table not found: " . $e->getMessage() . "\n";
    }
}

inspect('user_step_responses');
inspect('activity_proofs');

echo "\n--- TABLES ---\n";
$tables = db_fetch_all("SHOW TABLES");
foreach ($tables as $t) {
    $name = array_values($t)[0];
    if (stripos($name, 'curat') !== false || stripos($name, 'high') !== false) {
        echo "$name\n";
    }
}
