<?php
/**
 * Migration: V3.1 Core Setup
 * Creates pedagogical, growth, and gamification tables, and updates existing ones.
 * 
 * Run via CLI: php database/migrate_v3_1_core.php
 * Or via browser: http://localhost/database/migrate_v3_1_core.php
 */

require_once __DIR__ . '/../bootstrap/bootstrap.php';

try {
    $pdo = db();
    echo "Database connection successful.\n";
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Official Specialties (Master)
    echo "Creating official_specialties...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `official_specialties` (
            `id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
            `name` VARCHAR(255) NOT NULL,
            `category` ENUM('Natureza', 'Artes', 'Habilidades', 'Espiritual', 'Saúde', 'Recreação', 'Atividades Agrícolas', 'Atividades Missionárias', 'Ciência e Saúde', 'Estudos da Natureza', 'Habilidades Domésticas', 'Mestrados') NOT NULL,
            `official_icon_url` VARCHAR(255),
            `hex_color` VARCHAR(7),
            `difficulty_tier` ENUM('1_basic', '2_intermediate', '3_advanced', '4_master') NOT NULL,
            `fixed_total_xp` INT NOT NULL DEFAULT 500,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 2. Official Requirements
    echo "Creating official_requirements...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `official_requirements` (
            `id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
            `specialty_id` INT UNSIGNED NOT NULL,
            `order_index` INT NOT NULL,
            `description` TEXT NOT NULL,
            `is_mandatory` BOOLEAN DEFAULT TRUE,
            FOREIGN KEY (`specialty_id`) REFERENCES `official_specialties`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 3. Club Specialties (Instances)
    echo "Creating club_specialties...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `club_specialties` (
            `id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
            `tenant_id` INT UNSIGNED NOT NULL,
            `official_specialty_id` INT UNSIGNED NOT NULL,
            `status` ENUM('draft', 'published', 'archived') DEFAULT 'draft',
            `started_at` DATE,
            FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`official_specialty_id`) REFERENCES `official_specialties`(`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 4. Club Activities (The "HOW" for constraints)
    echo "Creating club_activities...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `club_activities` (
            `id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
            `club_specialty_id` INT UNSIGNED NOT NULL,
            `official_requirement_id` INT UNSIGNED NOT NULL,
            `response_type` ENUM('text', 'multiple_choice', 'video_url', 'photo_upload', 'file') NOT NULL, 
            `custom_instructions` TEXT,
            `calculated_xp_share` INT NOT NULL,
            FOREIGN KEY (`club_specialty_id`) REFERENCES `club_specialties`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`official_requirement_id`) REFERENCES `official_requirements`(`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 5. Club Profiles (Landing Page Data)
    echo "Creating club_profiles...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `club_profiles` (
            `tenant_id` INT UNSIGNED PRIMARY KEY,
            `display_name` VARCHAR(100),
            `slug` VARCHAR(100) UNIQUE,
            `logo_url` VARCHAR(255),
            `cover_image_url` VARCHAR(255),
            `meeting_address` VARCHAR(255),
            `meeting_time` VARCHAR(100),
            `social_instagram` VARCHAR(100),
            `social_whatsapp_group` VARCHAR(255),
            `welcome_message` TEXT,
            `leaders_json` JSON,
            `seo_meta_description` VARCHAR(160),
            FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 6. Club Growth Tools (QR Code, etc.)
    echo "Creating club_growth_tools...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `club_growth_tools` (
            `id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
            `tenant_id` INT UNSIGNED NOT NULL,
            `qr_code_path` VARCHAR(255),
            `campaign_source` VARCHAR(50) DEFAULT 'general',
            `visits_count` INT DEFAULT 0,
            FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 7. Gamification - XP Log
    echo "Creating user_xp_log...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `user_xp_log` (
            `id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
            `user_id` INT UNSIGNED NOT NULL,
            `amount` INT NOT NULL,
            `source_type` VARCHAR(50),
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 8. Gamification - User Inventory
    echo "Creating user_inventory...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `user_inventory` (
            `user_id` INT UNSIGNED PRIMARY KEY,
            `coins_balance` INT DEFAULT 0,
            `streak_days` INT DEFAULT 0,
            `last_login` DATE,
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");


    // 9. Altering Events Table (Adding V3.1 fields if they don't exist)
    echo "Altering events table...\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM `events` LIKE 'slug'");
    if ($stmt->rowCount() === 0) {
        $pdo->exec("
            ALTER TABLE `events`
            ADD COLUMN `slug` VARCHAR(150) NULL AFTER `title`,
            ADD COLUMN `location_link` VARCHAR(255) NULL AFTER `location`,
            ADD COLUMN `is_paid` BOOLEAN DEFAULT FALSE AFTER `registration_deadline`,
            ADD COLUMN `price` DECIMAL(10, 2) DEFAULT 0.00 AFTER `is_paid`,
            ADD COLUMN `payment_link` VARCHAR(255) NULL AFTER `price`,
            ADD COLUMN `whatsapp_contact` VARCHAR(20) NULL AFTER `payment_link`,
            ADD COLUMN `banner_url` VARCHAR(255) NULL AFTER `whatsapp_contact`;
        ");
        echo "Events table altered.\n";
    } else {
        echo "Events table already has V3.1 fields.\n";
    }

    // 10. Altering Event Enrollments (Guest Support)
    echo "Altering event_enrollments table...\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM `event_enrollments` LIKE 'guest_name'");
    if ($stmt->rowCount() === 0) {
        // Change user_id to allow NULL for guests, add guest fields
        
        // Check if there are foreign keys we need to drop to modify user_id. 
        // In current schema: fk_enrollments_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
        // To allow NULL, we need to alter column `user_id`.
        
        // We'll drop the foreign key, alter the column to allow NULL, then re-add the foreign key.
        // Wait, MySQL allows NULL in columns with foreign keys! So we just ALTER the column.
        $pdo->exec("
            ALTER TABLE `event_enrollments`
            MODIFY COLUMN `user_id` INT UNSIGNED NULL,
            ADD COLUMN `guest_name` VARCHAR(100) NULL AFTER `user_id`,
            ADD COLUMN `guest_phone` VARCHAR(20) NULL AFTER `guest_name`;
        ");
        echo "event_enrollments table altered to support guests.\n";
    } else {
        echo "event_enrollments already has guest fields.\n";
    }

    echo "\n✅ Migration completed successfully!\n";

} catch (PDOException $e) {
    echo "\n❌ Database Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
} catch (Exception $e) {
    echo "\n❌ General Error: " . $e->getMessage() . "\n";
}
