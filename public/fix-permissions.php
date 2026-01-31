<?php
/**
 * Fix Permissions Script
 * Attempts to set correct write permissions for Laravel/Storage folders.
 */

header('Content-Type: text/plain');

$dirs = [
    '../storage',
    '../storage/logs',
    '../storage/framework',
    '../storage/framework/views',
    '../storage/framework/cache',
    '../storage/framework/sessions',
    '../bootstrap/cache'
];

echo "Fixing permissions...\n\n";

foreach ($dirs as $dir) {
    if (!file_exists($dir)) {
        echo "Creating $dir...";
        if (mkdir($dir, 0755, true)) {
            echo " [OK]\n";
        } else {
            echo " [FAILED]\n";
        }
    }
    
    echo "Chmod $dir...";
    if (@chmod($dir, 0775)) {
        echo " [OK]\n";
    } else {
        echo " [FAILED] (Server may restricts chmod via PHP)\n";
    }
}

echo "\nDone.";
