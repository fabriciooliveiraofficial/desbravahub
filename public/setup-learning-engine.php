<?php
/**
 * Dynamic Learning Engine - Database Setup (Standalone)
 * 
 * This script loads only the minimal required components
 * to create the database tables.
 * 
 * Run once: /setup-learning-engine.php
 */

// Show all errors
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Load only what we need
require_once BASE_PATH . '/helpers/env.php';
env_load();
require_once BASE_PATH . '/helpers/config.php';
require_once BASE_PATH . '/helpers/database.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Setup - Dynamic Learning Engine</title>
    <style>
        body {
            font-family: system-ui, sans-serif;
            background: #1a1a2e;
            color: #e0e0e0;
            padding: 40px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        h1 {
            color: #00d9ff;
        }

        .success {
            color: #00ff88;
        }

        .error {
            color: #ff6b6b;
        }

        pre {
            background: #0d0d1a;
            padding: 16px;
            border-radius: 8px;
            overflow-x: auto;
        }

        .table-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            padding: 16px;
            margin: 16px 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>🚀 Dynamic Learning Engine Setup</h1>

        <?php
        try {
            $pdo = db();
            echo "<p class='success'>✅ Database connection OK</p>";
        } catch (Exception $e) {
            echo "<p class='error'>❌ Database connection failed: " . $e->getMessage() . "</p>";
            exit;
        }

        $results = [];
        $tables = [
            'learning_categories' => "
        CREATE TABLE IF NOT EXISTS learning_categories (
            id INT PRIMARY KEY AUTO_INCREMENT,
            tenant_id INT NOT NULL,
            name VARCHAR(100) NOT NULL,
            type ENUM('specialty', 'class', 'both') DEFAULT 'specialty',
            color VARCHAR(7) DEFAULT '#00D9FF',
            icon VARCHAR(10) DEFAULT '📚',
            description TEXT,
            sort_order INT DEFAULT 0,
            status ENUM('active', 'archived') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_tenant (tenant_id),
            INDEX idx_type (type),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
            'learning_programs' => "
        CREATE TABLE IF NOT EXISTS learning_programs (
            id INT PRIMARY KEY AUTO_INCREMENT,
            tenant_id INT NOT NULL,
            category_id INT,
            type ENUM('specialty', 'class') NOT NULL DEFAULT 'specialty',
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(100),
            icon VARCHAR(10) DEFAULT '📘',
            description TEXT,
            is_outdoor BOOLEAN DEFAULT FALSE,
            duration_hours INT DEFAULT 4,
            difficulty TINYINT DEFAULT 1,
            xp_reward INT DEFAULT 100,
            status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_tenant (tenant_id),
            INDEX idx_category (category_id),
            INDEX idx_type (type),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
            'program_versions' => "
        CREATE TABLE IF NOT EXISTS program_versions (
            id INT PRIMARY KEY AUTO_INCREMENT,
            program_id INT NOT NULL,
            version_number INT NOT NULL DEFAULT 1,
            status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
            changelog TEXT,
            published_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_program (program_id),
            INDEX idx_status (status),
            UNIQUE KEY uk_program_version (program_id, version_number)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
            'program_steps' => "
        CREATE TABLE IF NOT EXISTS program_steps (
            id INT PRIMARY KEY AUTO_INCREMENT,
            version_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            instructions TEXT,
            sort_order INT DEFAULT 0,
            is_required BOOLEAN DEFAULT TRUE,
            points INT DEFAULT 10,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_version (version_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
            'program_questions' => "
        CREATE TABLE IF NOT EXISTS program_questions (
            id INT PRIMARY KEY AUTO_INCREMENT,
            step_id INT NOT NULL,
            type ENUM('text', 'single_choice', 'multiple_choice', 'true_false', 'file_upload', 'url', 'manual') NOT NULL DEFAULT 'text',
            question_text TEXT NOT NULL,
            options JSON,
            correct_answer TEXT,
            points INT UNSIGNED NOT NULL DEFAULT 10,
            validation_rules JSON,
            is_required BOOLEAN DEFAULT TRUE,
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_step (step_id),
            INDEX idx_type (type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
            'user_program_progress' => "
        CREATE TABLE IF NOT EXISTS user_program_progress (
            id INT PRIMARY KEY AUTO_INCREMENT,
            tenant_id INT NOT NULL,
            user_id INT NOT NULL,
            program_id INT NOT NULL,
            version_id INT NOT NULL,
            status ENUM('not_started', 'in_progress', 'submitted', 'completed') DEFAULT 'not_started',
            progress_percent TINYINT DEFAULT 0,
            started_at TIMESTAMP NULL,
            submitted_at TIMESTAMP NULL,
            completed_at TIMESTAMP NULL,
            xp_earned INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_tenant (tenant_id),
            INDEX idx_user (user_id),
            INDEX idx_program (program_id),
            INDEX idx_status (status),
            UNIQUE KEY uk_user_program (user_id, program_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
            'user_step_responses' => "
        CREATE TABLE IF NOT EXISTS user_step_responses (
            id INT PRIMARY KEY AUTO_INCREMENT,
            progress_id INT NOT NULL,
            step_id INT NOT NULL,
            question_id INT,
            response_text TEXT,
            response_file VARCHAR(500),
            response_url VARCHAR(500),
            status ENUM('not_started', 'in_progress', 'submitted', 'approved', 'rejected') DEFAULT 'not_started',
            feedback TEXT,
            reviewed_by INT,
            reviewed_at TIMESTAMP NULL,
            submitted_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_progress (progress_id),
            INDEX idx_step (step_id),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
            'approval_logs' => "
        CREATE TABLE IF NOT EXISTS approval_logs (
            id INT PRIMARY KEY AUTO_INCREMENT,
            tenant_id INT NOT NULL,
            response_id INT NOT NULL,
            action ENUM('submitted', 'approved', 'rejected') NOT NULL,
            performed_by INT NOT NULL,
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_tenant (tenant_id),
            INDEX idx_response (response_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    "
        ];

        echo "<h2>📋 Creating Tables</h2>";

        $successCount = 0;
        $errorCount = 0;

        foreach ($tables as $tableName => $sql) {
            try {
                $pdo->exec($sql);
                $results[] = ['table' => $tableName, 'status' => 'success', 'message' => 'Created successfully'];
                $successCount++;
            } catch (PDOException $e) {
                $results[] = ['table' => $tableName, 'status' => 'error', 'message' => $e->getMessage()];
                $errorCount++;
            }
        }

        foreach ($results as $result) {
            $icon = $result['status'] === 'success' ? '✅' : '❌';
            $class = $result['status'] === 'success' ? 'success' : 'error';
            echo "<div class='table-card'>";
            echo "<strong class='$class'>$icon {$result['table']}</strong><br>";
            echo "<small>{$result['message']}</small>";
            echo "</div>";
        }

        echo "<h2>📊 Summary</h2>";
        echo "<p class='success'>✅ $successCount tables created successfully</p>";
        if ($errorCount > 0) {
            echo "<p class='error'>❌ $errorCount tables failed</p>";
        }

        // Show table structure
        echo "<h2>🗄️ Table Structure</h2><pre>";
        foreach (array_keys($tables) as $table) {
            try {
                $stmt = $pdo->query("DESCRIBE $table");
                echo "\n<strong>$table</strong>\n" . str_repeat('-', 50) . "\n";
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo sprintf("%-25s %-20s\n", $row['Field'], $row['Type']);
                }
            } catch (PDOException $e) {
                echo "<span class='error'>$table: Not found</span>\n";
            }
        }
        echo "</pre>";
        ?>

        <h2>🔗 Next Steps</h2>
        <ol>
            <li>Delete this file after setup</li>
            <li>Proceed to Phase 2: Category Management</li>
        </ol>

    </div>
</body>

</html>