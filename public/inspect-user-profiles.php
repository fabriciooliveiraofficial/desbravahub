<?php
require_once __DIR__ . '/../bootstrap/bootstrap.php';

header('Content-Type: text/plain; charset=UTF-8');

try {
    echo "Inspecting 'user_profiles' table:\n";
    echo "===============================\n\n";
    
    $res = db_query("DESCRIBE user_profiles");
    $columns = $res->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $col) {
        printf("%-20s | %-15s | %-10s | %-5s\n", 
            $col['Field'], 
            $col['Type'], 
            $col['Null'], 
            $col['Key']
        );
    }
    
    echo "\n\nChecking sample data:\n";
    $sample = db_fetch_one("SELECT * FROM user_profiles LIMIT 1");
    if ($sample) {
        print_r($sample);
    } else {
        echo "No records found in 'user_profiles'.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    
    echo "\nChecking if table exists in information_schema:\n";
    try {
        $exists = db_fetch_one("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_NAME = 'user_profiles'");
        if ($exists) {
            echo "Table 'user_profiles' EXISTS according to information_schema.\n";
        } else {
            echo "Table 'user_profiles' DOES NOT EXIST.\n";
        }
    } catch (Exception $e2) {
        echo "Error checking information_schema: " . $e2->getMessage() . "\n";
    }
}
