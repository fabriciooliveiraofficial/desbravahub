<?php
require __DIR__ . '/app/bootstrap.php';

echo "--- TABLES ---\n";
$tables = db_fetch_all("SELECT name FROM sqlite_master WHERE type='table'");
foreach ($tables as $t) {
    echo "- " . $t['name'] . "\n";
}

echo "\n--- user_requirement_progress (sample) ---\n";
try {
    $rows = db_fetch_all("SELECT * FROM user_requirement_progress LIMIT 3");
    var_dump($rows);
} catch (Exception $e) { echo "Error: " . $e->getMessage() . "\n"; }

echo "\n--- assignment_requirements (sample) ---\n";
try {
    $rows = db_fetch_all("SELECT * FROM assignment_requirements LIMIT 3");
    var_dump($rows);
} catch (Exception $e) { echo "Error: " . $e->getMessage() . "\n"; }

echo "\n--- specialty_assignments (sample) ---\n";
try {
    $rows = db_fetch_all("SELECT id, specialty_id, user_id, status FROM specialty_assignments LIMIT 3");
    var_dump($rows);
} catch (Exception $e) { echo "Error: " . $e->getMessage() . "\n"; }
