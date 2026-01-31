<?php
/**
 * Notifications System - Database Setup
 * 
 * Creates the notifications table for the learning engine.
 * Run once: /setup-notifications.php
 */

// Show all errors
ini_set('display_errors', 1);
error_reporting(E_ALL);

define('BASE_PATH', dirname(__DIR__));

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
    <title>Setup - Notifications</title>
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
        <h1>🔔 Notifications Setup</h1>

        <?php
        try {
            $pdo = db();
            echo "<p class='success'>✅ Database connection OK</p>";

            // Notifications table
            $sql = "CREATE TABLE IF NOT EXISTS notifications (
                id INT AUTO_INCREMENT PRIMARY KEY,
                tenant_id INT NOT NULL,
                user_id INT NOT NULL,
                type VARCHAR(50) NOT NULL,
                title VARCHAR(255) NOT NULL,
                message TEXT,
                link VARCHAR(255),
                icon VARCHAR(10) DEFAULT '📌',
                is_read TINYINT(1) DEFAULT 0,
                email_sent TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                read_at TIMESTAMP NULL,
                INDEX idx_notifications_user (tenant_id, user_id),
                INDEX idx_notifications_unread (tenant_id, user_id, is_read),
                INDEX idx_notifications_type (type)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $pdo->exec($sql);
            echo "<div class='table-card'>";
            echo "<strong class='success'>✅ notifications</strong><br>";
            echo "<small>Table created successfully</small>";
            echo "</div>";

            // User notification preferences table
            $sql2 = "CREATE TABLE IF NOT EXISTS user_notification_preferences (
                id INT AUTO_INCREMENT PRIMARY KEY,
                tenant_id INT NOT NULL,
                user_id INT NOT NULL,
                email_enabled TINYINT(1) DEFAULT 1,
                email_on_assignment TINYINT(1) DEFAULT 1,
                email_on_approval TINYINT(1) DEFAULT 1,
                email_on_rejection TINYINT(1) DEFAULT 1,
                email_on_completion TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY unique_user_prefs (tenant_id, user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $pdo->exec($sql2);
            echo "<div class='table-card'>";
            echo "<strong class='success'>✅ user_notification_preferences</strong><br>";
            echo "<small>Table created successfully</small>";
            echo "</div>";

            // Show structure
            echo "<h2>🗄️ Table Structure</h2><pre>";
            foreach (['notifications', 'user_notification_preferences'] as $table) {
                $stmt = $pdo->query("DESCRIBE $table");
                echo "\n<strong>$table</strong>\n" . str_repeat('-', 50) . "\n";
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo sprintf("%-25s %-20s\n", $row['Field'], $row['Type']);
                }
            }
            echo "</pre>";

        } catch (PDOException $e) {
            echo "<p class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        ?>

        <h2>✅ Done!</h2>
        <p>Delete this file after setup.</p>
    </div>
</body>

</html>