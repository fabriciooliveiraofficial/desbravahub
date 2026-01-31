<?php
/**
 * Migration: Add Specialty E-Learning Tables
 * 
 * Run this script to add the new tables for the e-learning specialty system.
 * php database/migrate_specialty_elearning.php
 */

require_once __DIR__ . '/../bootstrap/bootstrap.php';

echo "Starting migration: Specialty E-Learning System\n";
echo "================================================\n\n";

// Table 1: specialty_assignments
echo "Creating table: specialty_assignments... ";
try {
    db_query("
        CREATE TABLE IF NOT EXISTS `specialty_assignments` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `tenant_id` INT UNSIGNED NOT NULL,
            `specialty_id` VARCHAR(50) NOT NULL COMMENT 'References specialties_repository.json',
            `user_id` INT UNSIGNED NOT NULL,
            `assigned_by` INT UNSIGNED NOT NULL,
            `status` ENUM('pending', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
            `due_date` DATE NULL,
            `instructions` TEXT NULL,
            `xp_earned` INT UNSIGNED NOT NULL DEFAULT 0,
            `started_at` TIMESTAMP NULL,
            `completed_at` TIMESTAMP NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_specialty_assignment` (`tenant_id`, `specialty_id`, `user_id`),
            KEY `idx_specialty_assignments_user` (`user_id`),
            KEY `idx_specialty_assignments_status` (`tenant_id`, `status`),
            CONSTRAINT `fk_specialty_assignments_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_specialty_assignments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_specialty_assignments_assigned_by` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "OK\n";
} catch (PDOException $e) {
    echo "SKIP (already exists or error: " . $e->getMessage() . ")\n";
}

// Table 2: specialty_requirements
echo "Creating table: specialty_requirements... ";
try {
    db_query("
        CREATE TABLE IF NOT EXISTS `specialty_requirements` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `specialty_id` VARCHAR(50) NOT NULL COMMENT 'References specialties_repository.json',
            `order_num` INT UNSIGNED NOT NULL DEFAULT 1,
            `type` ENUM('text', 'multiple_choice', 'checkbox', 'file_upload', 'practical') NOT NULL DEFAULT 'text',
            `title` VARCHAR(500) NOT NULL,
            `description` TEXT NULL,
            `options` JSON NULL COMMENT 'For multiple_choice/checkbox types',
            `correct_answer` JSON NULL COMMENT 'Expected answer(s)',
            `points` INT UNSIGNED NOT NULL DEFAULT 10,
            `is_required` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_specialty_requirements_specialty` (`specialty_id`),
            KEY `idx_specialty_requirements_order` (`specialty_id`, `order_num`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "OK\n";
} catch (PDOException $e) {
    echo "SKIP (already exists or error: " . $e->getMessage() . ")\n";
}

// Table 3: user_requirement_progress
echo "Creating table: user_requirement_progress... ";
try {
    db_query("
        CREATE TABLE IF NOT EXISTS `user_requirement_progress` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `assignment_id` INT UNSIGNED NOT NULL,
            `requirement_id` INT UNSIGNED NOT NULL,
            `status` ENUM('pending', 'answered', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
            `answer` TEXT NULL COMMENT 'User response (text, JSON for choices)',
            `file_path` VARCHAR(500) NULL COMMENT 'For file_upload type',
            `answered_at` TIMESTAMP NULL,
            `reviewed_by` INT UNSIGNED NULL,
            `reviewed_at` TIMESTAMP NULL,
            `feedback` TEXT NULL COMMENT 'Instructor feedback',
            `points_earned` INT UNSIGNED NOT NULL DEFAULT 0,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_user_requirement_progress` (`assignment_id`, `requirement_id`),
            KEY `idx_user_requirement_progress_status` (`status`),
            CONSTRAINT `fk_user_requirement_progress_assignment` FOREIGN KEY (`assignment_id`) REFERENCES `specialty_assignments` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_user_requirement_progress_requirement` FOREIGN KEY (`requirement_id`) REFERENCES `specialty_requirements` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_user_requirement_progress_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "OK\n";
} catch (PDOException $e) {
    echo "SKIP (already exists or error: " . $e->getMessage() . ")\n";
}

echo "\n================================================\n";
echo "Migration completed!\n";
