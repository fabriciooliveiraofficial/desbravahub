<?php
/**
 * Migration: Add points column to program_questions table
 * 
 * Run this script once to add the missing points column.
 * URL: /your-slug/add-question-points.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔧 Migration: Add Points Column</h1>";

// Load config
$configPath = dirname(__DIR__) . '/config.php';
if (!file_exists($configPath)) {
    die("❌ config.php not found");
}
require_once $configPath;

// Connect to database
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "<p>✅ Connected to database</p>";
} catch (PDOException $e) {
    die("❌ Connection failed: " . $e->getMessage());
}

// Check if column already exists
$stmt = $pdo->query("SHOW COLUMNS FROM program_questions LIKE 'points'");
if ($stmt->rowCount() > 0) {
    echo "<p>✅ Column 'points' already exists - nothing to do</p>";
} else {
    // Add the column
    try {
        $pdo->exec("ALTER TABLE program_questions ADD COLUMN points INT UNSIGNED NOT NULL DEFAULT 10 AFTER correct_answer");
        echo "<p>✅ Column 'points' added successfully!</p>";
    } catch (PDOException $e) {
        echo "<p>❌ Error adding column: " . $e->getMessage() . "</p>";
    }
}

// Also check for single_choice and true_false types
$stmt = $pdo->query("SHOW COLUMNS FROM program_questions LIKE 'type'");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row) {
    $typeDefinition = $row['Type'];
    echo "<p>📋 Current type column: " . htmlspecialchars($typeDefinition) . "</p>";

    // Check if we need to expand the ENUM
    if (strpos($typeDefinition, 'single_choice') === false) {
        try {
            $pdo->exec("ALTER TABLE program_questions MODIFY COLUMN type ENUM('text', 'single_choice', 'multiple_choice', 'true_false', 'file_upload', 'url', 'manual') NOT NULL DEFAULT 'text'");
            echo "<p>✅ Type column updated to include new question types!</p>";
        } catch (PDOException $e) {
            echo "<p>❌ Error updating type column: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p>✅ Type column already has all question types</p>";
    }
}

echo "<h2>✨ Migration Complete!</h2>";
echo "<p><a href='javascript:history.back()'>← Go Back</a></p>";
